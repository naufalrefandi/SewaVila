<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyRental extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'deposit_refunded_at' => 'date',
        'actual_end_date' => 'date',
        'rent_price' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'deposit_refunded_amount' => 'decimal:2',
        'duration' => 'integer',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for active leases
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for leases expiring within specified days (default 30 days)
     */
    public function scopeExpiringSoon(Builder $query, int $days = 30): Builder
    {
        $today = Carbon::today();
        $targetDate = Carbon::today()->addDays($days);

        return $query->where('status', 'active')
            ->whereDate('end_date', '>=', $today)
            ->whereDate('end_date', '<=', $targetDate);
    }

    /**
     * Scope for overdue active leases
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->whereDate('end_date', '<', Carbon::today());
    }

    /**
     * Scope for completed leases
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for active deposits being held
     */
    public function scopeDepositHeld(Builder $query): Builder
    {
        return $query->where('deposit_status', 'held')
            ->where('deposit_amount', '>', 0);
    }

    public function getDaysRemainingAttribute(): int
    {
        if (!$this->end_date) {
            return 0;
        }

        $today = Carbon::today();
        $endDate = Carbon::parse($this->end_date)->startOfDay();

        return (int) $today->diffInDays($endDate, false);
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        return $this->status === 'active' && $this->days_remaining >= 0 && $this->days_remaining <= 30;
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'active' && $this->days_remaining < 0;
    }

    public function getFormattedRentPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->rent_price ?? 0, 0, ',', '.');
    }

    public function getFormattedDepositAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->deposit_amount ?? 0, 0, ',', '.');
    }

    public function getFormattedDepositRefundedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->deposit_refunded_amount ?? 0, 0, ',', '.');
    }

    public function getCleanTenantPhoneAttribute(): string
    {
        $phone = preg_replace('/[^0-9]/', '', (string) $this->tenant_phone);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }
        return $phone;
    }

    public function getWhatsappLinkAttribute(): string
    {
        return 'https://wa.me/' . $this->clean_tenant_phone;
    }

    public function getWhatsappReminderLinkAttribute(): string
    {
        $agentName = $this->agent?->name ?? 'Tim Agen Properti';
        $propTitle = $this->property?->title ?? 'Unit Properti';
        $propCode = $this->property?->property_code ? " ({$this->property->property_code})" : '';
        $endDateFormatted = $this->end_date ? Carbon::parse($this->end_date)->translatedFormat('d F Y') : '-';
        $depositFormatted = $this->formatted_deposit_amount;

        $message = "Halo Bpk/Ibu {$this->tenant_name},\n\n";
        $message .= "Kami dari {$agentName} ingin mengonfirmasi terkait sewa properti:\n";
        $message .= "🏠 Properti: {$propTitle}{$propCode}\n";
        $message .= "📅 Masa Sewa Berakhir: {$endDateFormatted}\n";
        $message .= "🔒 Uang Deposit Jaminan: {$depositFormatted}\n\n";

        if ($this->is_overdue) {
            $message .= "Masa sewa telah selesai. Mohon konfirmasi apakah Bapak/Ibu berencana memperpanjang sewa atau menjadwalkan pengecekan unit dan pengembalian uang deposit.\n\n";
        } elseif ($this->is_expiring_soon) {
            $message .= "Masa sewa Anda akan segera berakhir dalam {$this->days_remaining} hari lagi. Apakah Bapak/Ibu berencana memperpanjang masa sewa unit ini?\n\n";
        } else {
            $message .= "Berikut catatan informasi masa sewa Anda yang sedang aktif berjalan.\n\n";
        }

        $message .= "Terima kasih.";

        return 'https://wa.me/' . $this->clean_tenant_phone . '?text=' . urlencode($message);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active' => 'Aktif',
            'completed' => 'Selesai',
            'terminated' => 'Dibatalkan / Putus Sewa',
            default => ucfirst($this->status),
        };
    }

    public function getDepositStatusLabelAttribute(): string
    {
        return match ($this->deposit_status) {
            'held' => 'Ditahan (Aktif)',
            'refunded' => 'Dikembalikan Penuh',
            'partially_refunded' => 'Dikembalikan Sebagian',
            'deducted' => 'Dipotong / Hangus',
            default => ucfirst(str_replace('_', ' ', $this->deposit_status)),
        };
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            'paid' => 'Lunas',
            'partial' => 'Uang Muka / Sebagian',
            'unpaid' => 'Belum Lunas',
            default => ucfirst($this->payment_status),
        };
    }
}

