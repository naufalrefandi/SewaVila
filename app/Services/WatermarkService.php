<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WatermarkService
{
    /**
     * Apply watermark to a stored image in the public storage disk.
     *
     * @param string $relativePath Path relative to public disk (e.g. 'properties/images/abc.jpg')
     * @return string Returns the original relative path
     */
    public static function apply(string $relativePath): string
    {
        if (!extension_loaded('gd')) {
            return $relativePath;
        }

        try {
            $disk = Storage::disk('public');
            if (!$disk->exists($relativePath)) {
                return $relativePath;
            }

            $fullPath = $disk->path($relativePath);
            static::processImageFile($fullPath);
        } catch (\Throwable $e) {
            Log::warning('Gagal menambahkan watermark pada gambar: ' . $e->getMessage(), [
                'file' => $relativePath,
            ]);
        }

        return $relativePath;
    }

    /**
     * Process an image file on disk directly.
     */
    public static function processImageFile(string $filePath): bool
    {
        $imageInfo = @getimagesize($filePath);
        if (!$imageInfo) {
            return false;
        }

        [$width, $height, $type] = $imageInfo;

        // Skip very small images (e.g. icons, thumbnails under 200px)
        if ($width < 250 || $height < 200) {
            return false;
        }

        $mainImage = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($filePath),
            IMAGETYPE_PNG => @imagecreatefrompng($filePath),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($filePath) : null,
            default => null,
        };

        if (!$mainImage) {
            return false;
        }

        imagealphablending($mainImage, true);
        imagesavealpha($mainImage, true);

        // Check if custom logo is set in Settings
        $logoRelativePath = Setting::get('site_logo');
        $watermarkPosition = Setting::getWatermarkPosition();
        $applied = false;

        if ($logoRelativePath && Storage::disk('public')->exists($logoRelativePath)) {
            $logoFullPath = Storage::disk('public')->path($logoRelativePath);
            $applied = static::applyLogoWatermark($mainImage, $width, $height, $logoFullPath, $watermarkPosition);
        }

        // If no logo file is uploaded, fallback to brand text watermark
        if (!$applied) {
            $brandText = Setting::getSiteName();
            static::applyTextWatermark($mainImage, $width, $height, $brandText, $watermarkPosition);
        }

        // Save back with high quality
        $saved = match ($type) {
            IMAGETYPE_JPEG => imagejpeg($mainImage, $filePath, 90),
            IMAGETYPE_PNG => imagepng($mainImage, $filePath, 8),
            IMAGETYPE_WEBP => function_exists('imagewebp') ? imagewebp($mainImage, $filePath, 90) : false,
            default => false,
        };

        imagedestroy($mainImage);

        return $saved;
    }

    /**
     * Apply image logo watermark onto the main image canvas.
     */
    protected static function applyLogoWatermark(&$mainImage, int $mainWidth, int $mainHeight, string $logoPath, string $position = 'center'): bool
    {
        $logoInfo = @getimagesize($logoPath);
        if (!$logoInfo) {
            return false;
        }

        [$logoW, $logoH, $logoType] = $logoInfo;
        if ($logoW <= 0 || $logoH <= 0) {
            return false;
        }

        $logoImage = match ($logoType) {
            IMAGETYPE_PNG => @imagecreatefrompng($logoPath),
            IMAGETYPE_JPEG => @imagecreatefromjpeg($logoPath),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($logoPath) : null,
            default => null,
        };

        if (!$logoImage) {
            return false;
        }

        imagealphablending($logoImage, true);
        imagesavealpha($logoImage, true);

        // 1. Auto-detect and trim outer white/transparent borders so only the actual logo is used
        [$cropX, $cropY, $cropW, $cropH] = static::findContentBoundingBox($logoImage, $logoW, $logoH);

        // 2. Proportional watermark size in the center: ~33% of photo width (increased +40%, bounded between 145px and 365px)
        $targetW = (int) round($mainWidth * 0.33);
        $targetW = max(145, min($targetW, 365));
        $targetH = (int) round($cropH * ($targetW / $cropW));

        // Ensure watermark height does not exceed 29.5% of main photo height (increased +40%)
        $maxH = (int) round($mainHeight * 0.295);
        if ($targetH > $maxH && $maxH > 0) {
            $targetH = max(63, min($targetH, $maxH));
            $targetW = (int) round($cropW * ($targetH / $cropH));
        }

        // Create resampled logo canvas with full alpha transparency
        $resizedLogo = imagecreatetruecolor($targetW, $targetH);
        imagealphablending($resizedLogo, false);
        imagesavealpha($resizedLogo, true);
        $transparentColor = imagecolorallocatealpha($resizedLogo, 0, 0, 0, 127);
        imagefilledrectangle($resizedLogo, 0, 0, $targetW, $targetH, $transparentColor);
        imagecopyresampled($resizedLogo, $logoImage, 0, 0, $cropX, $cropY, $targetW, $targetH, $cropW, $cropH);
        imagedestroy($logoImage);

        // 3. Remove white/off-white background completely & scale opacity of logo elements to ~25% (tipis & halus)
        $targetOpacity = 25;
        for ($x = 0; $x < $targetW; $x++) {
            for ($y = 0; $y < $targetH; $y++) {
                $color = imagecolorat($resizedLogo, $x, $y);
                $alpha = ($color >> 24) & 0x7F; // 0 (solid) to 127 (transparent)

                if ($alpha >= 126) {
                    continue; // Already transparent
                }

                $r = ($color >> 16) & 0xFF;
                $g = ($color >> 8) & 0xFF;
                $b = $color & 0xFF;

                $whiteness = min($r, $g, $b);
                $chroma = max($r, $g, $b) - $whiteness;

                // Strip white and near-white background pixels entirely
                if ($whiteness >= 235 && $chroma <= 18) {
                    $newColor = imagecolorallocatealpha($resizedLogo, 0, 0, 0, 127);
                    imagesetpixel($resizedLogo, $x, $y, $newColor);
                    continue;
                }

                // Feather anti-aliased edge pixels between logo and white background (whiteness 205-234)
                if ($whiteness >= 205 && $chroma <= 18) {
                    $fadeFactor = ($whiteness - 205) / (235 - 205);
                    $featherAlpha = 127 - (int) round((127 - $alpha) * (1 - $fadeFactor));
                    $alpha = max($alpha, $featherAlpha);
                    if ($alpha >= 126) {
                        $newColor = imagecolorallocatealpha($resizedLogo, 0, 0, 0, 127);
                        imagesetpixel($resizedLogo, $x, $y, $newColor);
                        continue;
                    }
                }

                // Make the remaining actual logo elements subtle / translucent (tipis)
                $newAlpha = min(127, max(0, (int) round(127 - ((127 - $alpha) * ($targetOpacity / 100)))));
                $newColor = imagecolorallocatealpha($resizedLogo, $r, $g, $b, $newAlpha);
                imagesetpixel($resizedLogo, $x, $y, $newColor);
            }
        }

        // 4. Calculate destination coordinates in the center of the image
        $margin = max(16, (int) round($mainWidth * 0.025));
        [$destX, $destY] = static::calculateCoordinates($mainWidth, $mainHeight, $targetW, $targetH, $margin, $position);

        // 5. Blend only the logo elements onto the main photo (white background is completely excluded)
        imagealphablending($mainImage, true);
        imagecopy($mainImage, $resizedLogo, $destX, $destY, 0, 0, $targetW, $targetH);
        imagedestroy($resizedLogo);

        return true;
    }

    /**
     * Detect the actual content bounding box of an image, ignoring outer white and transparent borders.
     *
     * @return array{0: int, 1: int, 2: int, 3: int} [cropX, cropY, cropW, cropH]
     */
    protected static function findContentBoundingBox($image, int $w, int $h): array
    {
        $minX = $w;
        $minY = $h;
        $maxX = 0;
        $maxY = 0;
        $found = false;

        $step = max(1, (int) floor(min($w, $h) / 300));

        for ($y = 0; $y < $h; $y += $step) {
            for ($x = 0; $x < $w; $x += $step) {
                $color = imagecolorat($image, $x, $y);
                $alpha = ($color >> 24) & 0x7F;
                if ($alpha >= 120) {
                    continue; // Transparent
                }

                $r = ($color >> 16) & 0xFF;
                $g = ($color >> 8) & 0xFF;
                $b = $color & 0xFF;

                $whiteness = min($r, $g, $b);
                $chroma = max($r, $g, $b) - $whiteness;

                // If not white background
                if (!($whiteness >= 235 && $chroma <= 18)) {
                    $found = true;
                    if ($x < $minX) $minX = $x;
                    if ($x > $maxX) $maxX = $x;
                    if ($y < $minY) $minY = $y;
                    if ($y > $maxY) $maxY = $y;
                }
            }
        }

        if (!$found || $maxX < $minX || $maxY < $minY) {
            return [0, 0, $w, $h];
        }

        $pad = max(2, (int) round($step * 1.5));
        $cropX = max(0, $minX - $pad);
        $cropY = max(0, $minY - $pad);
        $cropW = min($w - $cropX, ($maxX - $minX + 1) + ($pad * 2));
        $cropH = min($h - $cropY, ($maxY - $minY + 1) + ($pad * 2));

        return [$cropX, $cropY, max(1, $cropW), max(1, $cropH)];
    }

    /**
     * Fallback: Apply text watermark if no custom logo is uploaded yet.
     */
    protected static function applyTextWatermark(&$mainImage, int $mainWidth, int $mainHeight, string $text, string $position = 'center'): void
    {
        $margin = max(16, (int) round($mainWidth * 0.025));
        $fontFile = static::findTtfFont();

        if ($fontFile && function_exists('imagettftext') && function_exists('imagettfbbox')) {
            // Proportional font size: ~4.0% - 4.1% of photo width (increased +40%, between 24pt and 44pt)
            $fontSize = max(24, min(44, (int) round($mainWidth * 0.040)));
            $bbox = @imagettfbbox($fontSize, 0, $fontFile, $text);

            if ($bbox) {
                // Exact bounding box dimensions
                $minX = min($bbox[0], $bbox[6]);
                $maxX = max($bbox[2], $bbox[4]);
                $minY = min($bbox[5], $bbox[7]); // negative in GD (above baseline)
                $maxY = max($bbox[1], $bbox[3]); // positive in GD (below baseline)

                $textW = $maxX - $minX;
                $textH = $maxY - $minY;

                if ($position === 'center') {
                    // Exact dead center vertically and horizontally
                    $destX = (int) round(($mainWidth / 2) - (($minX + $maxX) / 2));
                    $baseY = (int) round(($mainHeight / 2) - (($minY + $maxY) / 2));
                } else {
                    [$boxX, $boxY] = static::calculateCoordinates($mainWidth, $mainHeight, $textW, $textH, $margin, $position);
                    $destX = $boxX - $minX;
                    $baseY = $boxY - $minY;
                }

                imagealphablending($mainImage, true);

                // Faint subtle dark shadow (+1px, ~12% opacity) for contrast on bright photos
                $shadowColor = imagecolorallocatealpha($mainImage, 0, 0, 0, 112);
                @imagettftext($mainImage, $fontSize, 0, $destX + 1, $baseY + 1, $shadowColor, $fontFile, $text);

                // Subtle translucent white text (~25% opacity, "tipis")
                $textColor = imagecolorallocatealpha($mainImage, 255, 255, 255, 95);
                @imagettftext($mainImage, $fontSize, 0, $destX, $baseY, $textColor, $fontFile, $text);

                return;
            }
        }

        // Pure GD bitmap fallback if TTF not available: scale text cleanly and make it translucent
        $font = 5;
        $textLen = strlen($text);
        $fontW = imagefontwidth($font) * $textLen;
        $fontH = imagefontheight($font);

        $scale = max(2, min(4, (int) round($mainWidth / 320)));
        $scaledW = $fontW * $scale;
        $scaledH = $fontH * $scale;

        $tempCanvas = imagecreatetruecolor($fontW, $fontH);
        imagealphablending($tempCanvas, false);
        imagesavealpha($tempCanvas, true);
        $trans = imagecolorallocatealpha($tempCanvas, 0, 0, 0, 127);
        imagefilledrectangle($tempCanvas, 0, 0, $fontW, $fontH, $trans);
        $white = imagecolorallocate($tempCanvas, 255, 255, 255);
        imagestring($tempCanvas, $font, 0, 0, $text, $white);

        $scaledCanvas = imagecreatetruecolor($scaledW, $scaledH);
        imagealphablending($scaledCanvas, false);
        imagesavealpha($scaledCanvas, true);
        imagefilledrectangle($scaledCanvas, 0, 0, $scaledW, $scaledH, $trans);
        imagecopyresampled($scaledCanvas, $tempCanvas, 0, 0, 0, 0, $scaledW, $scaledH, $fontW, $fontH);
        imagedestroy($tempCanvas);

        // Make subtle / tipis (~25% opacity)
        for ($x = 0; $x < $scaledW; $x++) {
            for ($y = 0; $y < $scaledH; $y++) {
                $c = imagecolorat($scaledCanvas, $x, $y);
                $a = ($c >> 24) & 0x7F;
                if ($a >= 127) {
                    continue;
                }
                $newA = min(127, max(0, (int) round(127 - ((127 - $a) * 0.25))));
                $r = ($c >> 16) & 0xFF;
                $g = ($c >> 8) & 0xFF;
                $b = $c & 0xFF;
                imagesetpixel($scaledCanvas, $x, $y, imagecolorallocatealpha($scaledCanvas, $r, $g, $b, $newA));
            }
        }

        [$destX, $destY] = static::calculateCoordinates($mainWidth, $mainHeight, $scaledW, $scaledH, $margin, $position);

        imagealphablending($mainImage, true);
        imagecopy($mainImage, $scaledCanvas, $destX, $destY, 0, 0, $scaledW, $scaledH);
        imagedestroy($scaledCanvas);
    }

    /**
     * Find an available TrueType font file on the host system.
     */
    protected static function findTtfFont(): ?string
    {
        $candidates = [
            'C:\\Windows\\Fonts\\arialbd.ttf',
            'C:\\Windows\\Fonts\\arial.ttf',
            'C:\\Windows\\Fonts\\segoeuib.ttf',
            'C:\\Windows\\Fonts\\segoeui.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            '/usr/share/fonts/truetype/freefont/FreeSansBold.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
        ];

        foreach ($candidates as $fontPath) {
            if (@file_exists($fontPath)) {
                return $fontPath;
            }
        }

        return null;
    }

    /**
     * Compute X and Y coordinates based on position setting.
     *
     * @return array{0: int, 1: int}
     */
    protected static function calculateCoordinates(int $canvasW, int $canvasH, int $itemW, int $itemH, int $margin, string $position = 'center'): array
    {
        // Enforce center horizontal and center vertical by default or if legacy bottom-right
        if ($position === 'center' || $position === 'bottom-right' || empty($position)) {
            return [
                (int) round(($canvasW - $itemW) / 2),
                (int) round(($canvasH - $itemH) / 2),
            ];
        }

        return match ($position) {
            'bottom-left' => [
                $margin,
                $canvasH - $itemH - $margin,
            ],
            default => [ // 'center'
                (int) round(($canvasW - $itemW) / 2),
                (int) round(($canvasH - $itemH) / 2),
            ],
        };
    }
}

