<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentTransaction extends Model
{
    protected $fillable = [
        'agent_id',
        'user_id',
        'property_id',
        'type', // 'quota_topup', 'featured_listing'
        'amount',
        'quota_added',
        'duration_days',
        'payment_proof',
        'status', // 'pending', 'approved', 'rejected'
        'notes',
        'admin_notes',
        'approved_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'quota_added' => 'integer',
        'duration_days' => 'integer',
        'approved_at' => 'datetime',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
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

    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'quota_topup' => 'Topup Kuota Listing',
            'featured_listing' => 'Upgrade Properti Unggulan (Featured)',
            default => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu Verifikasi',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default => strtoupper($this->status),
        };
    }
}

