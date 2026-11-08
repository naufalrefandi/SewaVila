<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class VillaBooking extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'payment_expired_at' => 'datetime',
        'paid_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'total_nights' => 'integer',
        'guests_count' => 'integer',
        'price_per_night' => 'integer',
        'total_price' => 'integer',
        'cleaning_fee' => 'integer',
        'security_deposit' => 'integer',
        'grand_total' => 'integer',
    ];

    /**
     * Relationship to the booked Property (Villa)
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Relationship to the listing Agent
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    /**
     * Relationship to the user account (if logged in)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for pending payment bookings
     */
    public function scopePendingPayment(Builder $query): Builder
    {
        return $query->where('payment_status', 'pending');
    }

    /**
     * Scope for confirmed bookings
     */
    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('booking_status', 'confirmed');
    }

    /**
     * Scope for active bookings (not cancelled)
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('booking_status', '!=', 'cancelled');
    }

    /**
     * Generate unique booking code e.g. VLA-2611-X7K9P
     */
    public static function generateBookingCode(): string
    {
        do {
            $code = 'VLA-' . date('ym') . '-' . strtoupper(Str::random(5));
        } while (static::where('booking_code', $code)->exists());

        return $code;
    }

    /**
     * Check if specific dates are available for a given villa
     */
    public static function isDatesAvailable(
        int $propertyId,
        string|Carbon $checkIn,
        string|Carbon $checkOut,
        ?int $excludeBookingId = null
    ): bool {
        $checkInDate = Carbon::parse($checkIn)->startOfDay()->format('Y-m-d');
        $checkOutDate = Carbon::parse($checkOut)->startOfDay()->format('Y-m-d');

        return !static::where('property_id', $propertyId)
            ->where('booking_status', '!=', 'cancelled')
            ->where(function (Builder $query) use ($checkInDate, $checkOutDate) {
                // An existing booking overlaps if its check_in is before the requested check_out
                // AND its check_out is after the requested check_in
                $query->where('check_in_date', '<', $checkOutDate)
                    ->where('check_out_date', '>', $checkInDate);
            })
            ->when($excludeBookingId, fn ($q) => $q->where('id', '!=', $excludeBookingId))
            ->exists();
    }

    /**
     * Formatted price per night
     */
    public function getFormattedPricePerNightAttribute(): string
    {
        return 'Rp ' . number_format($this->price_per_night, 0, ',', '.') . '/malam';
    }

    /**
     * Formatted total stay price
     */
    public function getFormattedTotalPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->total_price, 0, ',', '.');
    }

    /**
     * Formatted grand total price
     */
    public function getFormattedGrandTotalAttribute(): string
    {
        return 'Rp ' . number_format($this->grand_total, 0, ',', '.');
    }

    /**
     * Formatted cleaning fee
     */
    public function getFormattedCleaningFeeAttribute(): string
    {
        return 'Rp ' . number_format($this->cleaning_fee, 0, ',', '.');
    }

    /**
     * Formatted security deposit
     */
    public function getFormattedSecurityDepositAttribute(): string
    {
        return 'Rp ' . number_format($this->security_deposit, 0, ',', '.');
    }

    /**
     * Human-readable Payment Status Label
     */
    public function getPaymentStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            'paid' => 'Lunas',
            'pending' => 'Menunggu Pembayaran',
            'failed' => 'Gagal',
            'refunded' => 'Dikembalikan',
            default => ucfirst($this->payment_status),
        };
    }

    /**
     * Human-readable Booking Status Label
     */
    public function getBookingStatusLabelAttribute(): string
    {
        return match ($this->booking_status) {
            'pending' => 'Menunggu Pembayaran',
            'confirmed' => 'Terkonfirmasi',
            'checked_in' => 'Sedang Menginap',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($this->booking_status),
        };
    }

    /**
     * Generate QRIS dynamic scan URL
     */
    public function getQrisCodeUrl(): string
    {
        $customQrisImage = Setting::get('qris_image');
        if ($customQrisImage) {
            if (str_starts_with($customQrisImage, 'http://') || str_starts_with($customQrisImage, 'https://')) {
                return $customQrisImage;
            }
            return asset('storage/' . $customQrisImage);
        }

        // Generate dynamic QR Code containing the official QRIS spec & booking reference
        $merchant = Setting::get('qris_merchant_name', Setting::getSiteName() . ' Official QRIS');
        $payload = 'QRIS-PAY:' . $this->booking_code . '|NOMINAL:' . $this->grand_total . '|MERCHANT:' . $merchant . '|TIMESTAMP:' . now()->timestamp;

        return 'https://api.qrserver.com/v1/create-qr-code/?size=380x380&margin=10&data=' . urlencode($payload);
    }

    /**
     * WhatsApp URL to contact support/host regarding this booking
     */
    public function getWhatsAppConfirmUrl(): string
    {
        $phone = $this->agent?->clean_phone ?: Setting::getCleanOfficialAgentPhone();
        $siteName = Setting::getSiteName();

        $message = "Halo {$siteName}, saya ingin konfirmasi sewa vila:\n"
            . "• Kode Booking: *{$this->booking_code}*\n"
            . "• Vila: *{$this->property->title}*\n"
            . "• Atas Nama: {$this->customer_name}\n"
            . "• Periode: " . $this->check_in_date->format('d M Y') . " s/d " . $this->check_out_date->format('d M Y') . " ({$this->total_nights} malam)\n"
            . "• Total Pembayaran: {$this->formatted_grand_total} (via QRIS)\n"
            . "• Status: {$this->payment_status_label}\n\n"
            . "Mohon info ketersediaan dan check-in nya ya. Terima kasih!";

        return 'https://wa.me/' . $phone . '?text=' . urlencode($message);
    }
}

