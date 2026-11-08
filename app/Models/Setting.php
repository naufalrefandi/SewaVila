<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    /**
     * Default Fallback Settings
     */
    protected static array $defaults = [
        // Identitas Website
        'site_name' => 'YProperti.com',
        'site_tagline' => 'Portal Properti & Marketplace',
        'site_hotline' => '021-5098-8888',
        'site_logo' => null,
        'watermark_enabled' => 1,
        'watermark_position' => 'center',

        // Official Agent Contact Info (Fallback for listings/broadcasts when no agent is assigned)
        'official_agent_name' => null,
        'official_agent_phone' => null,
        'official_agent_office' => null,

        // Titip Jual (Owner Submissions)
        'submission_fee' => 50000,
        'submission_fee_enabled' => 1,

        // Agent Registration (Daftar Jadi Agen)
        'agent_registration_fee' => 100000,
        'agent_registration_fee_enabled' => 1,

        // Agent Listing Quota
        'agent_free_quota' => 3,
        'agent_topup_price' => 100000,
        'agent_topup_quota' => 5,

        // Featured Listing
        'featured_listing_price' => 50000,
        'featured_duration_days' => 30,

        // Sewa Villa
        'villa_cleaning_fee' => 150000,
        'villa_security_deposit' => 0,

        // QRIS Info
        'qris_image' => null,
        'qris_merchant_name' => 'YProperti Official QRIS',

        // Rekening BCA (Utama)
        'bca_bank_name' => 'BCA (Bank Central Asia)',
        'bca_account_number' => '8290-9988-7766',
        'bca_account_name' => 'YPROPERTI INDONESIA',

        // Rekening Bank Lain (Opsional)
        'other_bank_name' => 'Bank Mandiri',
        'other_bank_account_number' => '142-00-9988-7766',
        'other_bank_account_name' => 'YPROPERTI INDONESIA',

        // Legacy compatibility
        'bank_name' => 'BCA (Bank Central Asia)',
        'bank_account_number' => '8290-9988-7766',
        'bank_account_name' => 'YPROPERTI INDONESIA',

        'payment_instructions' => 'Scan QRIS di atas menggunakan m-Banking (BCA, Mandiri, BRI, BNI) atau E-Wallet (GoPay, OVO, Dana, ShopeePay). Anda juga dapat transfer langsung ke Rekening BCA resmi atau Rekening Bank Lain yang tertera. Simpan dan unggah struk/bukti transfer untuk verifikasi admin.',

        // Email / SMTP Settings (Optional override over .env)
        'mail_mailer' => null,
        'mail_host' => null,
        'mail_port' => null,
        'mail_username' => null,
        'mail_password' => null,
        'mail_encryption' => null,
        'mail_from_address' => null,
        'mail_from_name' => null,
    ];

    /**
     * Get setting value by key with optional default
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        if ($setting !== null && $setting->value !== null) {
            return $setting->value;
        }

        if ($default !== null) {
            return $default;
        }

        return static::$defaults[$key] ?? null;
    }

    /**
     * Set or update setting value
     */
    public static function set(string $key, mixed $value, string $group = 'general'): static
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value, 'group' => $group]
        );
    }

    /**
     * Get BCA Account Details
     */
    public static function getBcaAccount(): array
    {
        return [
            'bank_name' => static::get('bca_bank_name', 'BCA (Bank Central Asia)'),
            'account_number' => static::get('bca_account_number', static::get('bank_account_number', '8290-9988-7766')),
            'account_name' => static::get('bca_account_name', static::get('bank_account_name', 'YPROPERTI INDONESIA')),
        ];
    }

    /**
     * Get Other Bank Account Details
     */
    public static function getOtherBankAccount(): ?array
    {
        $accNum = static::get('other_bank_account_number');
        if (empty($accNum)) {
            return null;
        }

        return [
            'bank_name' => static::get('other_bank_name', 'Bank Lain'),
            'account_number' => $accNum,
            'account_name' => static::get('other_bank_account_name', static::get('bca_account_name', 'YPROPERTI INDONESIA')),
        ];
    }

    /**
     * Get All Active Bank Accounts
     */
    public static function getAllBankAccounts(): array
    {
        $accounts = [
            static::getBcaAccount(),
        ];

        $other = static::getOtherBankAccount();
        if ($other) {
            $accounts[] = $other;
        }

        return $accounts;
    }

    /**
     * Get QRIS Image Full URL
     */
    public static function getQrisUrl(): string
    {
        $path = static::get('qris_image');
        if ($path) {
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path;
            }
            return asset('storage/' . $path);
        }

        // Elegant fallback placeholder QRIS code
        return 'https://api.qrserver.com/v1/create-qr-code/?size=350x350&data=' . urlencode('00020101021226670014ID.LINKAJA.WWW01189360091100212345670210000000000151440014ID.GO.QRIS.WWW0215ID10200212345670303UME5204581253033605802ID5919YPROPERTI INDONESIA6007JAKARTA61051219062070703A016304E641');
    }

    /**
     * Get Website Name
     */
    public static function getSiteName(): string
    {
        return (string) static::get('site_name', 'YProperti');
    }

    /**
     * Get Website Tagline
     */
    public static function getSiteTagline(): string
    {
        return (string) static::get('site_tagline', 'Portal Properti & Marketplace');
    }

    /**
     * Get Website Hotline Konsultasi
     */
    public static function getSiteHotline(): string
    {
        return (string) static::get('site_hotline', '021-5098-8888');
    }

    /**
     * Get Clean Digits-Only Hotline for links
     */
    public static function getCleanSiteHotline(): string
    {
        $raw = static::getSiteHotline();
        return (string) preg_replace('/[^0-9]/', '', $raw);
    }

    /**
     * Get Website Logo Full URL (or null if not uploaded)
     */
    public static function getSiteLogo(): ?string
    {
        $path = static::get('site_logo');
        if (!empty($path)) {
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path;
            }
            return asset('storage/' . $path);
        }
        return null;
    }

    /**
     * Get Official Agent Name
     */
    public static function getOfficialAgentName(): string
    {
        $val = static::get('official_agent_name');
        return !empty($val) ? (string) $val : static::getSiteName() . ' Official Agent';
    }

    /**
     * Get Official Agent Phone (formatted)
     */
    public static function getOfficialAgentPhone(): string
    {
        $val = static::get('official_agent_phone');
        return !empty($val) ? (string) $val : static::getSiteHotline();
    }

    /**
     * Get Clean Official Agent Phone (for WhatsApp link: 628...)
     */
    public static function getCleanOfficialAgentPhone(): string
    {
        $raw = static::getOfficialAgentPhone();
        $digits = (string) preg_replace('/[^0-9]/', '', $raw);
        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        }
        return !empty($digits) ? $digits : '6281298765432';
    }

    /**
     * Get Official Agent Office Branch
     */
    public static function getOfficialAgentOffice(): string
    {
        $val = static::get('official_agent_office');
        return !empty($val) ? (string) $val : static::getSiteName() . ' (Kantor Pusat)';
    }

    /**
     * Get Watermark Position (always returns 'center' by default, auto-migrates legacy bottom-right)
     */
    public static function getWatermarkPosition(): string
    {
        $val = static::get('watermark_position');
        if (empty($val) || $val === 'bottom-right') {
            return 'center';
        }
        return (string) $val;
    }
}

