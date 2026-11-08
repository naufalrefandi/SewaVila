<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PropertySubmission extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Generate a unique payment code for owner property submission.
     */
    public static function generatePaymentCode(): string
    {
        do {
            $code = 'PAY-TTP-' . now()->format('ymd') . '-' . strtoupper(Str::random(4));
        } while (static::where('payment_code', $code)->exists());

        return $code;
    }

    protected $casts = [
        'expected_price' => 'integer',
        'fee_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'land_area' => 'integer',
        'building_area' => 'integer',
        'bedrooms' => 'integer',
        'bathrooms' => 'integer',
    ];

    public function getFormattedPriceAttribute(): string
    {
        $price = $this->expected_price;
        if ($price >= 1_000_000_000) {
            return 'Rp ' . number_format($price / 1_000_000_000, 2, ',', '.') . ' Miliar';
        } elseif ($price >= 1_000_000) {
            return 'Rp ' . number_format($price / 1_000_000, 1, ',', '.') . ' Juta';
        }
        return 'Rp ' . number_format($price, 0, ',', '.');
    }

    public function getPaymentProofUrlAttribute(): ?string
    {
        if (!$this->payment_proof) {
            return null;
        }

        if (str_starts_with($this->payment_proof, 'http://') || str_starts_with($this->payment_proof, 'https://')) {
            return $this->payment_proof;
        }

        return asset('storage/' . $this->payment_proof);
    }

    public function getFormattedFeeAttribute(): string
    {
        return 'Rp ' . number_format($this->fee_amount ?: 0, 0, ',', '.');
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            'paid' => 'Lunas (Terverifikasi)',
            'pending' => 'Menunggu Verifikasi',
            'unpaid' => 'Belum Bayar',
            default => ucfirst($this->payment_status ?? 'unpaid'),
        };
    }
}
