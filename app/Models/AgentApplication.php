<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AgentApplication extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Generate a unique payment code for agent registration.
     */
    public static function generatePaymentCode(): string
    {
        do {
            $code = 'PAY-AGN-' . now()->format('ymd') . '-' . strtoupper(Str::random(4));
        } while (static::where('payment_code', $code)->exists());

        return $code;
    }

    public function getCleanPhoneAttribute(): string
    {
        $phone = preg_replace('/[^0-9]/', '', $this->phone);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }
        return $phone;
    }

    public function getExperienceLabelAttribute(): string
    {
        return match ($this->experience_level) {
            'baru' => 'Fresh / Baru Memulai',
            '1-3_tahun' => '1 - 3 Tahun',
            'diatas_3_tahun' => 'Lebih dari 3 Tahun',
            default => ucfirst($this->experience_level),
        };
    }

    public function getPhotoAttribute($value): string
    {
        if (empty($value)) {
            return 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=300&q=80';
        }
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }
        return asset('storage/' . ltrim($value, '/'));
    }

    public function getPaymentProofUrlAttribute(): ?string
    {
        if (empty($this->payment_proof)) {
            return null;
        }
        if (str_starts_with($this->payment_proof, 'http://') || str_starts_with($this->payment_proof, 'https://')) {
            return $this->payment_proof;
        }
        return asset('storage/' . ltrim($this->payment_proof, '/'));
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            'verified' => 'Terverifikasi',
            'pending' => 'Menunggu Verifikasi',
            default => 'Belum Bayar',
        };
    }
}
