<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'images' => 'array',
        'facilities' => 'array',
        'nearby_points' => 'array',
        'price' => 'integer',
        'sale_price' => 'integer',
        'rent_price' => 'integer',
        'monthly_rent_price' => 'integer',
        'original_price' => 'integer',
        'bedrooms' => 'integer',
        'maid_bedrooms' => 'integer',
        'bathrooms' => 'integer',
        'maid_bathrooms' => 'integer',
        'land_area' => 'float',
        'building_area' => 'float',
        'carports' => 'integer',
        'floors' => 'integer',
        'electricity' => 'integer',
        'year_built' => 'integer',
        'is_featured' => 'boolean',
        'is_urgent' => 'boolean',
        'is_new_launch' => 'boolean',
        'is_verified' => 'boolean',
        'views_count' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Property $property) {
            $rawCode = $property->getAttributes()['property_code'] ?? null;
            if (empty($rawCode) || static::where('property_code', $rawCode)->exists()) {
                $agent = null;
                if (!empty($property->agent_id)) {
                    $agent = Agent::find($property->agent_id);
                } elseif ($property->relationLoaded('agent')) {
                    $agent = $property->agent;
                }

                $property->property_code = static::generateCode(
                    $property->property_type ?? 'apartemen',
                    $property->listing_type ?? 'dijual',
                    $property->code_source ?? 'A',
                    $agent
                );
            }
        });
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function provinceRef(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    public function cityRef(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function districtRef(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function rentals(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PropertyRental::class);
    }

    public function activeRental(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PropertyRental::class)->where('status', 'active')->latestOfMany();
    }

    public function villaBookings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(VillaBooking::class);
    }

    public function isApartemen(): bool
    {
        return strtolower((string) ($this->property_type ?? '')) === 'apartemen';
    }

    public function isVilla(): bool
    {
        return strtolower((string) ($this->property_type ?? '')) === 'villa';
    }

    public function getMaxGuestsAttribute(): int
    {
        if ($this->bedrooms && $this->bedrooms > 0) {
            return max(2, (int) $this->bedrooms * 2);
        }

        return 6;
    }

    public function getPropertyCodeAttribute(?string $value): ?string
    {
        if (!empty($value)) {
            return $value;
        }

        $typeMap = [
            'apartemen' => 'APT',
            'rumah' => 'RMH',
            'tanah' => 'TNH',
            'tanah_residensial' => 'TNH',
            'tanah_komersial' => 'TNH',
            'ruko' => 'RUKO',
            'gudang' => 'GDG',
            'villa' => 'VLA',
            'kantor' => 'KTR',
            'tempat_usaha' => 'USHA',
            'pabrik' => 'PBRK',
            'kost' => 'KOST',
            'hotel' => 'HTL',
            'gedung' => 'GDN',
        ];
        $typeCode = $typeMap[strtolower($this->property_type ?? 'apartemen')] ?? 'RMH';
        $seqStr = sprintf('%04d', $this->id ?? 1);
        $tx = ($this->listing_type === 'disewa') ? 'S' : 'J';

        $prefix = 'A';
        if ($this->agent) {
            $prefix = $this->agent->initials;
        } elseif (!empty($this->agent_id)) {
            $prefix = Agent::find($this->agent_id)?->initials ?? 'A';
        }

        return "{$prefix}-{$typeCode}-{$seqStr}-{$tx}";
    }

    public function getFormattedTowerAttribute(): ?string
    {
        if (!$this->unit_tower) return null;
        $tw = trim((string) $this->unit_tower);
        $twClean = trim(preg_replace('/^(?:tower|tw\.?)\s*/i', '', $tw));
        if ($twClean === '') return 'Tower';
        return 'Tower ' . (strlen($twClean) <= 2 ? strtoupper($twClean) : ucwords(strtolower($twClean)));
    }

    public function getFormattedFloorAttribute(): ?string
    {
        $fl = trim((string) ($this->unit_floor ?: $this->floors));
        if ($fl === '') return null;
        $flClean = trim(preg_replace('/^(?:lantai|lt\.?)\s*/i', '', $fl));
        if ($flClean === '') return null;
        return 'Lantai ' . $flClean;
    }

    public function getFormattedFloorShortAttribute(): ?string
    {
        $fl = trim((string) ($this->unit_floor ?: $this->floors));
        if ($fl === '') return null;
        $flClean = trim(preg_replace('/^(?:lantai|lt\.?)\s*/i', '', $fl));
        if ($flClean === '') return null;
        return 'Lt. ' . $flClean;
    }

    public function getFormattedUnitNumberAttribute(): ?string
    {
        if (!$this->unit_number) return null;
        $un = trim((string) $this->unit_number);
        $unClean = trim(preg_replace('/^(?:unit|no\.?)\s*/i', '', $un));
        if ($unClean === '') return null;
        return 'Unit ' . $unClean;
    }

    public function getFormattedViewAttribute(): ?string
    {
        if (!$this->unit_view) return null;
        $vw = trim((string) $this->unit_view, " \t\n\r\0\x0B()");
        if ($vw === '') return null;
        $vwClean = trim(preg_replace('/^view\s*/i', '', $vw));
        if ($vwClean === '') return null;
        return 'View ' . ucwords(strtolower($vwClean));
    }

    public function getFullUnitInfoAttribute(): ?string
    {
        $parts = [];
        if ($this->unit_type) {
            $parts[] = str_replace(' ', '', $this->unit_type);
        }
        if ($this->formatted_tower) $parts[] = $this->formatted_tower;
        if ($this->formatted_floor) $parts[] = $this->formatted_floor;
        if ($this->formatted_unit_number) $parts[] = $this->formatted_unit_number;
        if ($this->formatted_view) $parts[] = '(' . $this->formatted_view . ')';

        return count($parts) > 0 ? implode(' • ', $parts) : null;
    }

    public function getTowerAttribute(): ?string
    {
        return $this->unit_tower;
    }

    public function getFloorAttribute(): ?string
    {
        return $this->unit_floor;
    }

    public function getFloorNumberAttribute(): ?int
    {
        if ($this->unit_floor && preg_match('/\d+/', (string) $this->unit_floor, $matches)) {
            return (int) $matches[0];
        }
        return $this->floors ?: null;
    }

    public function getShortTowerAttribute(): ?string
    {
        if (!$this->unit_tower) return null;
        return trim(str_ireplace('tower', '', $this->unit_tower));
    }

    public function getAnnualRentPriceAttribute(): ?float
    {
        if ($this->rent_price && $this->rent_price > 0) {
            return (float) $this->rent_price;
        }
        if ($this->listing_type === 'disewa' || $this->listing_type === 'dijual_disewa') {
            return (float) $this->price;
        }
        return null;
    }

    public function getDirectSalePriceAttribute(): ?float
    {
        if ($this->sale_price && $this->sale_price > 0) {
            return (float) $this->sale_price;
        }
        if ($this->listing_type === 'dijual' || $this->listing_type === 'dijual_disewa') {
            return (float) $this->price;
        }
        return null;
    }

    public function getViewAttribute(): ?string
    {
        return $this->unit_view;
    }

    public function getBedroomsLabelAttribute(): string
    {
        if ($this->maid_bedrooms && $this->maid_bedrooms > 0) {
            return ($this->bedrooms ?: '0') . '+' . $this->maid_bedrooms;
        }

        return (string) ($this->bedrooms ?: '-');
    }

    public function getBathroomsLabelAttribute(): string
    {
        if ($this->maid_bathrooms && $this->maid_bathrooms > 0) {
            return ($this->bathrooms ?: '0') . '+' . $this->maid_bathrooms;
        }

        return (string) ($this->bathrooms ?: '-');
    }

    public function getBedroomsDetailAttribute(): string
    {
        if ($this->maid_bedrooms && $this->maid_bedrooms > 0) {
            return ($this->bedrooms ?: 0) . ' KT Utama + ' . $this->maid_bedrooms . ' KT ART/Pembantu';
        }

        return ($this->bedrooms ? $this->bedrooms . ' Kamar Tidur' : '-');
    }

    public function getBathroomsDetailAttribute(): string
    {
        if ($this->maid_bathrooms && $this->maid_bathrooms > 0) {
            return ($this->bathrooms ?: 0) . ' KM Utama + ' . $this->maid_bathrooms . ' KM ART/Pembantu';
        }

        return ($this->bathrooms ? $this->bathrooms . ' Kamar Mandi' : '-');
    }

    public function getRoadAccessLabelAttribute(): string
    {
        if ($this->road_width) {
            return $this->road_width;
        }

        if ($this->carports) {
            return 'Row ' . $this->carports . ' Mobil';
        }

        return 'Jalan Lebar';
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class);
    }

    public static function formatMoney(int|float|null $price, ?string $period = null): string
    {
        if (!$price || $price <= 0) {
            return 'Rp 0';
        }

        $periodSuffix = $period ? '/' . $period : '';

        if ($price >= 1_000_000_000_000) {
            $formatted = number_format($price / 1_000_000_000_000, 2, ',', '.') . ' Triliun';
        } elseif ($price >= 1_000_000_000) {
            $val = $price / 1_000_000_000;
            $formatted = ($val == floor($val) ? number_format($val, 0) : number_format($val, 2, ',', '.')) . ' Miliar';
        } elseif ($price >= 1_000_000) {
            $val = $price / 1_000_000;
            $formatted = ($val == floor($val) ? number_format($val, 0) : number_format($val, 1, ',', '.')) . ' Juta';
        } else {
            $formatted = number_format($price, 0, ',', '.');
        }

        return 'Rp ' . $formatted . $periodSuffix;
    }

    /**
     * Format clean price shorthand (e.g. 2.3M, 18M, 750jt, 4.5jt)
     */
    public static function formatCleanPrice(int|float|null $amount): string
    {
        if (!$amount || $amount <= 0) {
            return '0';
        }

        if ($amount >= 1_000_000_000_000) {
            $val = $amount / 1_000_000_000_000;
            $str = ($val == floor($val)) ? number_format($val, 0) : rtrim(rtrim(number_format($val, 2, '.', ''), '0'), '.');
            return "{$str}T";
        }

        if ($amount >= 1_000_000_000) {
            $val = $amount / 1_000_000_000;
            $str = ($val == floor($val)) ? number_format($val, 0) : rtrim(rtrim(number_format($val, 2, '.', ''), '0'), '.');
            return "{$str}M";
        }

        if ($amount >= 1_000_000) {
            $val = $amount / 1_000_000;
            $str = ($val == floor($val)) ? number_format($val, 0) : rtrim(rtrim(number_format($val, 2, '.', ''), '0'), '.');
            return "{$str}jt";
        }

        return number_format($amount, 0, ',', '.');
    }

    /**
     * Generate clean WhatsApp broadcast text matching real-world property broker format
     *
     * Example:
     * Dijual Murah Rumah Model Minimalis
     * Citraland Utama
     *
     * LT 105 ( 7 x 15 )
     * LB 105 ( 2 lantai )
     * KT 4 | KM 3
     * Kosongan
     * Listrik 5500w
     * Carport 2 Mobil
     * SHM
     *
     * Harga Rp  2.3M nego
     *
     * Hubungi :
     * Teja WinMax Property
     * Whatsapp/ Call
     * 083831160600
     * wa.me/6283831160600
     */
    public function getShareHeadlineAttribute(): string
    {
        $jenisJual = ($this->listing_type === 'disewa') ? 'Disewa' : (($this->listing_type === 'dijual_disewa') ? 'Dijual / Disewa' : 'Dijual');
        $typeMap = [
            'apartemen' => 'Apartemen',
            'rumah' => 'Rumah',
            'tanah' => 'Tanah',
            'tanah_residensial' => 'Tanah',
            'tanah_komersial' => 'Tanah',
            'ruko' => 'Ruko',
            'gudang' => 'Gudang',
            'villa' => 'Villa',
            'kantor' => 'Kantor',
            'tempat_usaha' => 'Tempat Usaha',
            'pabrik' => 'Pabrik',
            'kost' => 'Kost',
            'hotel' => 'Hotel',
            'gedung' => 'Gedung'
        ];
        $tipeProperti = $typeMap[strtolower($this->property_type ?? 'rumah')] ?? ucwords(str_replace('_', ' ', $this->property_type ?? 'Properti'));

        $location = null;
        if ($this->relationLoaded('project') && $this->project) {
            $location = $this->project->name;
            if (!empty($this->city) && !str_contains(strtolower($location), strtolower($this->city))) {
                $location .= ', ' . $this->city;
            }
        } elseif (!empty($this->project_id)) {
            $proj = Project::find($this->project_id);
            if ($proj) {
                $location = $proj->name;
                if (!empty($this->city) && !str_contains(strtolower($location), strtolower($this->city))) {
                    $location .= ', ' . $this->city;
                }
            }
        }

        if (empty($location)) {
            if (!empty($this->district) && !empty($this->city)) {
                $location = $this->district . ', ' . $this->city;
            } elseif (!empty($this->district)) {
                $location = $this->district;
            } else {
                $location = $this->city;
            }
        }

        return trim("{$jenisJual} {$tipeProperti} " . ($location ?? ''));
    }

    public function generateCleanWaBroadcastText(?Agent $agent = null, bool $includeLink = true, ?string $shareUrl = null): string
    {
        $lines = [];

        // 1. Top Header: Kode Properti ditaruh di paling atas sendiri
        if (!empty($this->property_code)) {
            $lines[] = "Kode: {$this->property_code}";
        }

        // 2. Headline: Jenis Jual Tipe Properti Lokasi (Bold)
        $lines[] = "*{$this->share_headline}*";

        // Empty line between header and specs
        $lines[] = '';

        // 4. Attributes / Specs according to Property Type (Grouped logically with blank line between groups)
        $normType = strtolower(trim($this->property_type ?? 'rumah'));
        $specGroups = [];

        if (in_array($normType, ['tanah', 'tanah_residensial', 'tanah_komersial'])) {
            // === TANAH ===
            // Group 1: Ukuran & Dimensi Tanah
            $group1 = [];
            if ($this->land_area) {
                $lt = (float) $this->land_area == (int) $this->land_area ? (int) $this->land_area : (float) $this->land_area;
                $dimStr = '';
                if (!empty($this->dimension)) {
                    $cleanDim = trim(preg_replace('/^\((.*)\)$/', '$1', trim($this->dimension)));
                    $dimStr = " ( {$cleanDim} )";
                }
                $group1[] = "LT {$lt}{$dimStr}";
            }
            if ($this->price_per_meter) {
                $group1[] = "Harga {$this->price_per_meter}";
            }

            // Group 2: Legalitas, Hadap & Akses Jalan
            $group2 = [];
            if (!empty($this->certificate)) {
                $group2[] = strtoupper($this->certificate);
            }
            if (!empty($this->facing)) {
                $facing = trim($this->facing);
                if (!str_starts_with(strtolower($facing), 'hadap')) {
                    $facing = "Hadap {$facing}";
                }
                $group2[] = $facing;
            }
            if (!empty($this->road_width)) {
                $group2[] = "Row {$this->road_width}";
            }

            $specGroups = array_values(array_filter([$group1, $group2]));
        } elseif ($normType === 'apartemen') {
            // === APARTEMEN ===
            // Group 1: Spesifikasi Ruang & Kamar (Tipe Unit, LB, KT, KM masing-masing 1 baris)
            $group1 = [];
            if (!empty($this->unit_type)) {
                $group1[] = "Tipe " . str_replace(' ', '', $this->unit_type);
            }
            if ($this->building_area) {
                $lb = (float) $this->building_area == (int) $this->building_area ? (int) $this->building_area : (float) $this->building_area;
                $group1[] = "LB {$lb} m²";
            }
            if ($this->bedrooms) {
                $group1[] = "KT {$this->bedrooms}";
            }
            if ($this->bathrooms) {
                $group1[] = "KM {$this->bathrooms}";
            }

            // Group 2: Posisi Unit & Orientasi (Tower, Lantai, Nomor Unit, View, Hadap masing-masing 1 baris)
            $group2 = [];
            if ($this->formatted_tower) {
                $group2[] = $this->formatted_tower;
            }
            if ($this->formatted_floor) {
                $group2[] = $this->formatted_floor;
            }
            if ($this->formatted_unit_number) {
                $group2[] = $this->formatted_unit_number;
            }
            if (!empty($this->unit_view)) {
                $cleanView = trim(preg_replace('/^view\s+/i', '', trim($this->unit_view)));
                $group2[] = "View " . ($cleanView ?: $this->unit_view);
            }
            if (!empty($this->facing)) {
                $facing = trim($this->facing);
                if (!str_starts_with(strtolower($facing), 'hadap')) {
                    $facing = "Hadap {$facing}";
                }
                $group2[] = $facing;
            }

            // Group 3: Kondisi Perabotan, Listrik & Legalitas
            $group3 = [];
            $furn = match ($this->furnished_status) {
                'fully-furnished', 'full' => 'Full Furnished',
                'semi-furnished', 'semi' => 'Semi Furnished',
                'unfurnished', 'kosong', 'kosongan' => 'Kosongan',
                default => !empty($this->furnished_status) ? ucfirst($this->furnished_status) : null,
            };
            if ($furn) {
                $group3[] = $furn;
            }
            if ($this->electricity) {
                $cleanWatt = preg_replace('/[^0-9]/', '', (string) $this->electricity);
                if ($cleanWatt) {
                    $group3[] = "Listrik {$cleanWatt}w";
                }
            }
            if (!empty($this->certificate)) {
                $group3[] = strtoupper($this->certificate);
            }

            $specGroups = array_values(array_filter([$group1, $group2, $group3]));
        } else {
            // === RUMAH / VILLA / RUKO / GUDANG / PABRIK / LAINNYA ===
            // Group 1: Ukuran Tanah, Bangunan, Dimensi & Lantai
            $group1 = [];
            if ($this->land_area) {
                $lt = (float) $this->land_area == (int) $this->land_area ? (int) $this->land_area : (float) $this->land_area;
                $dimStr = '';
                if (!empty($this->dimension)) {
                    $cleanDim = trim(preg_replace('/^\((.*)\)$/', '$1', trim($this->dimension)));
                    $dimStr = " ( {$cleanDim} )";
                }
                $group1[] = "LT {$lt}{$dimStr}";
            }

            if ($this->building_area) {
                $lb = (float) $this->building_area == (int) $this->building_area ? (int) $this->building_area : (float) $this->building_area;
                $floorStr = (!empty($this->floors) && $this->floors > 0) ? " ( {$this->floors} lantai )" : '';
                $group1[] = "LB {$lb}{$floorStr}";
            }

            // Group 2: Kamar Tidur, Kamar Mandi, Carport & Kondisi Perabotan (KT & KM masing-masing 1 baris)
            $group2 = [];
            if ($this->bedrooms) {
                $kt = (string) $this->bedrooms;
                if (!empty($this->maid_bedrooms) && $this->maid_bedrooms > 0) {
                    $kt .= '+' . $this->maid_bedrooms;
                }
                $group2[] = "KT {$kt}";
            }
            if ($this->bathrooms) {
                $km = (string) $this->bathrooms;
                if (!empty($this->maid_bathrooms) && $this->maid_bathrooms > 0) {
                    $km .= '+' . $this->maid_bathrooms;
                }
                $group2[] = "KM {$km}";
            }

            if ($this->carports && $this->carports > 0) {
                $group2[] = "Carport {$this->carports} Mobil";
            }

            $furn = match ($this->furnished_status) {
                'fully-furnished', 'full' => 'Full Furnished',
                'semi-furnished', 'semi' => 'Semi Furnished',
                'unfurnished', 'kosong', 'kosongan' => 'Kosongan',
                default => !empty($this->furnished_status) ? ucfirst($this->furnished_status) : null,
            };
            if ($furn) {
                $group2[] = $furn;
            }

            // Group 3: Legalitas, Listrik, Hadap & Row Jalan
            $group3 = [];
            if (!empty($this->certificate)) {
                $group3[] = strtoupper($this->certificate);
            }

            if ($this->electricity) {
                $cleanWatt = preg_replace('/[^0-9]/', '', (string) $this->electricity);
                if ($cleanWatt) {
                    $group3[] = "Listrik {$cleanWatt}w";
                }
            }

            if (!empty($this->facing)) {
                $facing = trim($this->facing);
                if (!str_starts_with(strtolower($facing), 'hadap')) {
                    $facing = "Hadap {$facing}";
                }
                $group3[] = $facing;
            }

            if (!empty($this->road_width)) {
                $group3[] = "Row {$this->road_width}";
            }

            $specGroups = array_values(array_filter([$group1, $group2, $group3]));
        }

        foreach ($specGroups as $idx => $groupLines) {
            if ($idx > 0) {
                $lines[] = '';
            }
            foreach ($groupLines as $line) {
                $lines[] = $line;
            }
        }

        // Empty line before price
        $lines[] = '';

        // 5. Harga (Price Line - Bold)
        $negoSuffix = !empty($this->price_prefix) ? ' ' . strtolower($this->price_prefix) : ' nego';

        if ($this->listing_type === 'dijual_disewa') {
            $saleAmt = self::formatCleanPrice($this->sale_price ?: $this->price);
            $rentAmt = self::formatCleanPrice($this->rent_price ?: $this->price);
            $period = $this->rent_period ?: 'tahun';
            $lines[] = "*Harga Jual Rp  {$saleAmt}{$negoSuffix}*";
            $lines[] = "*Harga Sewa Rp  {$rentAmt} / {$period}*";
        } elseif ($this->listing_type === 'disewa') {
            $rentAmt = self::formatCleanPrice($this->rent_price ?: $this->price);
            $period = $this->rent_period ?: 'tahun';
            $lines[] = "*Sewa Rp  {$rentAmt} / {$period}{$negoSuffix}*";
        } else {
            // Dijual
            $saleAmt = self::formatCleanPrice($this->sale_price ?: $this->price);
            $lines[] = "*Harga Rp  {$saleAmt}{$negoSuffix}*";
        }

        // Empty line after price before Hubungi
        $lines[] = '';

        // Resolve shareUrl if not provided
        if (empty($shareUrl) && (!empty($this->property_code) || !empty($this->slug))) {
            $shareUrl = $this->getShortShareUrl($agent);
        }

        // 6. Contact Section
        $agent = $agent ?: $this->agent;
        if ($agent) {
            $agentName = $agent->name;
            $agentOffice = $agent->office_branch ?: Setting::getSiteName();
            $contactIdentity = trim("{$agentName} {$agentOffice}");
            $phone = $agent->phone ?: Setting::getOfficialAgentPhone();
        } else {
            $agentName = Setting::getOfficialAgentName();
            $agentOffice = Setting::getOfficialAgentOffice();
            $contactIdentity = trim("{$agentName} {$agentOffice}");
            $phone = Setting::getOfficialAgentPhone();
        }

        $lines[] = 'Hubungi :';
        $lines[] = $contactIdentity;
        $lines[] = $phone;

        // 7. Link Listingan ditaruh di paling bawah sendiri
        if ($includeLink && !empty($shareUrl)) {
            $lines[] = '';
            $lines[] = 'Link Listing:';
            $lines[] = $shareUrl;
        }

        return implode("\n", $lines);
    }

    /**
     * Generate a short, clean share URL using the property code (e.g., https://domain.com/p/A-APT-0001-S)
     * that redirects to the full canonical property detail slug.
     */
    public function getShortShareUrl(?Agent $agent = null): string
    {
        $code = $this->property_code ?: $this->slug ?: (string) $this->id;
        $params = ['code' => $code];

        // Only append ?ref= if an overriding agent (different from the property's own agent) is sharing
        if ($agent && $this->agent_id && (int) $agent->id !== (int) $this->agent_id) {
            $params['ref'] = $agent->initials ?: $agent->slug;
        }

        return route('properties.short', $params);
    }

    /**
     * Format Indonesian currency (e.g., Rp 2,5 Miliar or Rp 750 Juta)
     */
    public function getFormattedPriceAttribute(): string
    {
        if ($this->listing_type === 'dijual_disewa') {
            $sale = $this->sale_price ?: $this->price;
            $rent = $this->rent_price ?: $this->price;
            $saleStr = self::formatMoney($sale);
            $rentStr = self::formatMoney($rent, $this->rent_period ?: 'th');
            return "Jual: {$saleStr} • Sewa: {$rentStr}";
        }

        $period = $this->listing_type === 'disewa' && $this->rent_period ? $this->rent_period : null;
        return self::formatMoney($this->price, $period);
    }

    public function getFormattedOriginalPriceAttribute(): ?string
    {
        if (!$this->original_price || $this->original_price <= $this->price) {
            return null;
        }

        return self::formatMoney($this->original_price);
    }

    public function getHasDiscountAttribute(): bool
    {
        return !empty($this->original_price) && $this->original_price > $this->price;
    }

    /**
     * Estimated KPR monthly installment
     * Formula: Loan = 80% price, Tenor = 20 years (240 months), Interest = 5.5% p.a.
     */
    public function getEstimatedKprInstallmentAttribute(): string
    {
        if ($this->listing_type === 'disewa') {
            return '';
        }

        $principal = $this->price * 0.8; // 80% loan
        $annualRate = 0.055; // 5.5%
        $monthlyRate = $annualRate / 12;
        $months = 240; // 20 years

        if ($monthlyRate > 0) {
            $monthlyInstallment = ($principal * $monthlyRate) / (1 - pow(1 + $monthlyRate, -$months));
        } else {
            $monthlyInstallment = $principal / $months;
        }

        if ($monthlyInstallment >= 1_000_000_000) {
            return 'Rp ' . number_format($monthlyInstallment / 1_000_000_000, 2, ',', '.') . ' M/bln';
        } elseif ($monthlyInstallment >= 1_000_000) {
            return 'Rp ' . number_format($monthlyInstallment / 1_000_000, 1, ',', '.') . ' Jt/bln';
        }

        return 'Rp ' . number_format($monthlyInstallment, 0, ',', '.') . '/bln';
    }

    /**
     * Price per square meter (useful for tanah, gudang, etc.)
     */
    public function getPricePerMeterAttribute(): ?string
    {
        if ($this->listing_type === 'disewa') {
            return null;
        }

        $area = $this->property_type === 'tanah' ? $this->land_area : ($this->building_area ?: $this->land_area);

        if (!$area || $area <= 0) {
            return null;
        }

        $pricePerMeter = $this->price / $area;

        if ($pricePerMeter >= 1_000_000_000) {
            return 'Rp ' . number_format($pricePerMeter / 1_000_000_000, 2, ',', '.') . ' M/m²';
        } elseif ($pricePerMeter >= 1_000_000) {
            $val = $pricePerMeter / 1_000_000;
            return 'Rp ' . ($val == floor($val) ? number_format($val, 0) : number_format($val, 1, ',', '.')) . ' Jt/m²';
        }

        return 'Rp ' . number_format($pricePerMeter, 0, ',', '.') . '/m²';
    }

    public static function getImageUrl(?string $path): string
    {
        if (!$path) {
            return 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1200&q=80';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, 'storage/')) {
            return asset($path);
        }

        return asset('storage/' . ltrim($path, '/'));
    }

    public static function getDefaultImagesForType(?string $propertyType = null): array
    {
        $type = strtolower(trim((string) $propertyType));

        return match ($type) {
            'apartemen' => [
                'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=1200&q=80',
            ],
            'tanah', 'tanah_residensial', 'tanah_komersial' => [
                'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1200&q=80',
            ],
            'ruko' => [
                'https://images.unsplash.com/photo-1577495508048-b635879837f1?auto=format&fit=crop&w=1200&q=80',
            ],
            'gudang', 'pabrik' => [
                'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=1200&q=80',
            ],
            'hotel' => [
                'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80',
            ],
            'gedung', 'kantor', 'tempat_usaha' => [
                'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1200&q=80',
            ],
            'villa' => [
                'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=1200&q=80',
            ],
            'kost' => [
                'https://images.unsplash.com/photo-1555854877-bab0e564b8d5?auto=format&fit=crop&w=1200&q=80',
            ],
            default => [
                'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1200&q=80',
            ],
        };
    }

    public static function getDefaultImageForType(?string $propertyType = null): string
    {
        return self::getDefaultImagesForType($propertyType)[0];
    }

    public function getPrimaryImageAttribute(): string
    {
        $images = $this->images;
        if (is_array($images) && count($images) > 0) {
            return self::getImageUrl($images[0]);
        }

        return self::getDefaultImageForType($this->property_type);
    }

    public function getFormattedImagesAttribute(): array
    {
        $images = $this->images;
        if (!is_array($images) || count($images) === 0) {
            $default = self::getDefaultImageForType($this->property_type);
            return [
                [
                    'raw' => $default,
                    'url' => $default,
                ]
            ];
        }

        return array_map(function ($img) {
            if (is_array($img)) {
                $raw = $img['raw'] ?? $img['url'] ?? '';
                return [
                    'raw' => (string) $raw,
                    'url' => self::getImageUrl((string) $raw),
                ];
            }

            return [
                'raw' => (string) $img,
                'url' => self::getImageUrl((string) $img),
            ];
        }, $images);
    }

    public function getVideoUrlAttribute(): ?string
    {
        if (!$this->video) {
            return null;
        }

        if (str_starts_with($this->video, 'http://') || str_starts_with($this->video, 'https://')) {
            return $this->video;
        }

        if (str_starts_with($this->video, 'storage/')) {
            return asset($this->video);
        }

        return asset('storage/' . ltrim($this->video, '/'));
    }

    /**
     * Parse video URL / file to structured embed data (YouTube, Shorts, TikTok, IG Reels, direct file)
     *
     * @return array{type: ?string, id: ?string, embed_url: ?string, direct_url: ?string, raw_url: ?string}
     */
    public function getVideoEmbedInfoAttribute(): array
    {
        $raw = trim($this->video ?? '');
        if (empty($raw)) {
            return ['type' => null, 'id' => null, 'embed_url' => null, 'direct_url' => null, 'raw_url' => null];
        }

        $url = $this->video_url;

        // 1. YouTube (Supports watch?v=, youtu.be/, shorts/, embed/, live/, with any query parameters)
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?|shorts|live)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i', $raw, $m)) {
            $ytId = $m[1];
            return [
                'type' => 'youtube',
                'id' => $ytId,
                'embed_url' => "https://www.youtube-nocookie.com/embed/{$ytId}?autoplay=1&rel=0&modestbranding=1",
                'direct_url' => "https://www.youtube.com/watch?v={$ytId}",
                'raw_url' => $raw,
            ];
        }

        // 2. TikTok (e.g. tiktok.com/@user/video/1234567890)
        if (preg_match('/tiktok\.com\/(?:@[^\/]+\/video\/|v\/)(\d+)/i', $raw, $m)) {
            $ttId = $m[1];
            return [
                'type' => 'tiktok',
                'id' => $ttId,
                'embed_url' => "https://www.tiktok.com/embed/v2/{$ttId}",
                'direct_url' => $raw,
                'raw_url' => $raw,
            ];
        }

        // 3. Instagram Reels / Video
        if (preg_match('/instagram\.com\/(?:p|reel|tv)\/([a-zA-Z0-9_-]+)/i', $raw, $m)) {
            $igId = $m[1];
            return [
                'type' => 'instagram',
                'id' => $igId,
                'embed_url' => "https://www.instagram.com/p/{$igId}/embed/",
                'direct_url' => "https://www.instagram.com/reel/{$igId}/",
                'raw_url' => $raw,
            ];
        }

        // 4. Direct video files (.mp4, .webm, .mov or stored locally in storage)
        $isDirectFile = preg_match('/\.(mp4|webm|mov|ogg|m4v)(\?.*)?$/i', $url) || str_starts_with($raw, 'properties/videos');
        if ($isDirectFile) {
            return [
                'type' => 'direct',
                'id' => null,
                'embed_url' => null,
                'direct_url' => $url,
                'raw_url' => $raw,
            ];
        }

        // 5. Fallback for other external video URLs
        return [
            'type' => 'external',
            'id' => null,
            'embed_url' => null,
            'direct_url' => $url,
            'raw_url' => $raw,
        ];
    }

    public function getPropertyTypeLabelAttribute(): string
    {
        if ($this->property_type === 'ruko' && $this->project_id) {
            return 'Ruko Apartemen';
        }

        return match ($this->property_type) {
            'rumah' => 'Rumah',
            'apartemen' => 'Apartemen',
            'ruko' => 'Ruko / Komersial',
            'villa' => 'Villa',
            'tanah' => 'Tanah',
            'gudang' => 'Gudang',
            default => ucfirst($this->property_type),
        };
    }

    public function getListingTypeLabelAttribute(): string
    {
        return match ($this->listing_type) {
            'dijual' => 'Dijual',
            'disewa' => 'Disewa',
            'dijual_disewa' => 'Jual & Sewa',
            default => ucfirst($this->listing_type),
        };
    }

    public function getFurnishedStatusLabelAttribute(): string
    {
        return match ($this->furnished_status) {
            'fully-furnished' => 'Fully Furnished',
            'semi-furnished' => 'Semi Furnished',
            default => 'Unfurnished',
        };
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeUrgent(Builder $query): Builder
    {
        return $query->where('is_urgent', true);
    }

    public function scopeNewLaunch(Builder $query): Builder
    {
        return $query->where('is_new_launch', true);
    }

    public function scopeForSale(Builder $query): Builder
    {
        return $query->whereIn('listing_type', ['dijual', 'dijual_disewa']);
    }

    public function scopeForRent(Builder $query): Builder
    {
        return $query->whereIn('listing_type', ['disewa', 'dijual_disewa']);
    }

    public function scopeVilla(Builder $query): Builder
    {
        return $query->where('property_type', 'villa');
    }

    /**
     * Auto-generate property code based on rule:
     * [Prefix]-[Type]-[0000]-[J/S]
     * - Prefix: Inisial Agen (e.g. AG, AS, BS), V = Vendor, A = Agensi
     * - Type: APT, RMH, TNH, RUKO, GDG, VLA
     * - 0000: 4-digit sequential number per property type
     * - J = Dijual / S = Disewa
     */
    public static function generateCode(?string $propertyType = 'apartemen', ?string $listingType = 'dijual', mixed $source = 'A', mixed $agent = null): string
    {
        $src = strtoupper(trim((string) ($source ?? 'A')));

        // 1. If explicitly set to Vendor 'V', use 'V'
        if ($src === 'V') {
            $prefix = 'V';
        } else {
            // 2. Determine prefix from Agent initials or custom initials
            $prefix = null;
            if ($agent instanceof Agent) {
                $prefix = $agent->initials;
            } elseif (is_numeric($agent) && $agent > 0) {
                $foundAgent = Agent::find($agent);
                if ($foundAgent) {
                    $prefix = $foundAgent->initials;
                }
            }

            if (empty($prefix)) {
                // If source is a custom agent initial string (2-5 letters)
                if (preg_match('/^[A-Z0-9]{2,5}$/', $src) && !in_array($src, ['V', 'A'])) {
                    $prefix = $src;
                } else {
                    $prefix = 'A';
                }
            }
        }

        $typeMap = [
            'apartemen' => 'APT',
            'rumah' => 'RMH',
            'tanah' => 'TNH',
            'tanah_residensial' => 'TNH',
            'tanah_komersial' => 'TNH',
            'ruko' => 'RUKO',
            'gudang' => 'GDG',
            'villa' => 'VLA',
            'kantor' => 'KTR',
            'tempat_usaha' => 'USHA',
            'pabrik' => 'PBRK',
            'kost' => 'KOST',
            'hotel' => 'HTL',
            'gedung' => 'GDN',
        ];
        $normType = strtolower(trim($propertyType ?? 'apartemen'));
        $typeCode = $typeMap[$normType] ?? 'APT';

        $normListing = strtolower(trim($listingType ?? 'dijual'));
        $listingCode = (str_starts_with($normListing, 'sewa') || $normListing === 'disewa') ? 'S' : 'J';

        $existingCodes = static::where('property_code', 'like', "%-{$typeCode}-%")
            ->pluck('property_code');

        $maxSeq = 0;
        foreach ($existingCodes as $c) {
            if (preg_match('/-(\d{3,4})(-[A-Z]+)?$/i', $c, $m)) {
                $val = (int)$m[1];
                if ($val > $maxSeq) {
                    $maxSeq = $val;
                }
            }
        }
        $nextSeq = $maxSeq + 1;
        $seqStr = sprintf('%04d', $nextSeq);

        $code = "{$prefix}-{$typeCode}-{$seqStr}-{$listingCode}";

        while (static::where('property_code', $code)->exists()) {
            $nextSeq++;
            $seqStr = sprintf('%04d', $nextSeq);
            $code = "{$prefix}-{$typeCode}-{$seqStr}-{$listingCode}";
        }

        return $code;
    }

    /**
     * Get next sequence numbers for all property types
     */
    public static function getNextSequences(): array
    {
        $types = [
            'apartemen' => 'APT',
            'rumah' => 'RMH',
            'tanah' => 'TNH',
            'tanah_residensial' => 'TNH',
            'tanah_komersial' => 'TNH',
            'ruko' => 'RUKO',
            'gudang' => 'GDG',
            'villa' => 'VLA',
            'kantor' => 'KTR',
            'tempat_usaha' => 'USHA',
            'pabrik' => 'PBRK',
            'kost' => 'KOST',
            'hotel' => 'HTL',
            'gedung' => 'GDN',
        ];

        $result = [];
        foreach ($types as $typeKey => $typeCode) {
            $existingCodes = static::where('property_code', 'like', "%-{$typeCode}-%")
                ->pluck('property_code');
            $maxSeq = 0;
            foreach ($existingCodes as $c) {
                if (preg_match('/-(\d{3,4})(-[A-Z]+)?$/i', $c, $m)) {
                    $val = (int)$m[1];
                    if ($val > $maxSeq) {
                        $maxSeq = $val;
                    }
                }
            }
            $result[$typeKey] = $maxSeq + 1;
        }

        return $result;
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (!empty($filters['q'])) {
            $rawQuery = trim($filters['q']);
            $terms = preg_split('/[\s,]+/', $rawQuery, -1, PREG_SPLIT_NO_EMPTY);

            if (!empty($terms)) {
                $query->where(function (Builder $outerQuery) use ($rawQuery, $terms) {
                    // 1. Direct match on property_code or exact phrase match
                    $outerQuery->where('property_code', 'like', "%{$rawQuery}%")
                        ->orWhere('title', 'like', "%{$rawQuery}%")
                        ->orWhere('city', 'like', "%{$rawQuery}%")
                        ->orWhere('district', 'like', "%{$rawQuery}%")
                        ->orWhere('address', 'like', "%{$rawQuery}%")
                        ->orWhereHas('project', function ($pq) use ($rawQuery) {
                            $pq->where('name', 'like', "%{$rawQuery}%");
                        });

                    // 2. Multi-word search: each term must match somewhere across property attributes
                    if (count($terms) > 1) {
                        $outerQuery->orWhere(function (Builder $andQuery) use ($terms) {
                            foreach ($terms as $term) {
                                $termLike = "%{$term}%";
                                $andQuery->where(function (Builder $sub) use ($termLike) {
                                    $sub->where('title', 'like', $termLike)
                                        ->orWhere('property_code', 'like', $termLike)
                                        ->orWhere('property_type', 'like', $termLike)
                                        ->orWhere('city', 'like', $termLike)
                                        ->orWhere('district', 'like', $termLike)
                                        ->orWhere('address', 'like', $termLike)
                                        ->orWhere('province', 'like', $termLike)
                                        ->orWhere('description', 'like', $termLike)
                                        ->orWhereHas('project', function ($pq) use ($termLike) {
                                            $pq->where('name', 'like', $termLike);
                                        });
                                });
                            }
                        });
                    }
                });
            }
        }

        if (!empty($filters['listing_type'])) {
            if ($filters['listing_type'] === 'dijual') {
                $query->whereIn('listing_type', ['dijual', 'dijual_disewa']);
            } elseif ($filters['listing_type'] === 'disewa') {
                $query->whereIn('listing_type', ['disewa', 'dijual_disewa']);
            } else {
                $query->where('listing_type', $filters['listing_type']);
            }
        }

        if (!empty($filters['property_type'])) {
            $query->where('property_type', $filters['property_type']);
        }

        if (!empty($filters['city'])) {
            $query->where('city', 'like', '%' . $filters['city'] . '%');
        }

        if (!empty($filters['price_min'])) {
            $query->where('price', '>=', (int) $filters['price_min']);
        }

        if (!empty($filters['price_max'])) {
            $query->where('price', '<=', (int) $filters['price_max']);
        }

        if (!empty($filters['bedrooms'])) {
            $query->where('bedrooms', '>=', (int) $filters['bedrooms']);
        }

        if (!empty($filters['bathrooms'])) {
            $query->where('bathrooms', '>=', (int) $filters['bathrooms']);
        }

        if (!empty($filters['certificate'])) {
            $query->where('certificate', $filters['certificate']);
        }

        if (!empty($filters['furnished'])) {
            $query->where('furnished_status', $filters['furnished']);
        }

        if (!empty($filters['is_new_launch'])) {
            $query->where('is_new_launch', true);
        }

        // Sorting
        $sort = $filters['sort'] ?? 'latest';
        return match ($sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'land_desc' => $query->orderBy('land_area', 'desc'),
            'building_desc' => $query->orderBy('building_area', 'desc'),
            default => $query->orderBy('created_at', 'desc'),
        };
    }
}
