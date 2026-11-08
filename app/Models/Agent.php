<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Agent extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $appends = [
        'initials',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'is_top_producer' => 'boolean',
        'is_leader' => 'boolean',
        'listing_quota' => 'integer',
        'rating' => 'float',
        'experience_years' => 'integer',
        'active_listings_count' => 'integer',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function leader(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'leader_id');
    }

    public function teamMembers(): HasMany
    {
        return $this->hasMany(Agent::class, 'leader_id');
    }

    public function isLeader(): bool
    {
        return (bool) $this->is_leader;
    }

    public function allTeamAgentIds(): array
    {
        return $this->teamMembers()->pluck('id')->push($this->id)->all();
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    public function clientRequests(): HasMany
    {
        return $this->hasMany(ClientRequest::class);
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(AgentTransaction::class);
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(PropertyRental::class);
    }

    public function getRemainingQuotaAttribute(): int
    {
        $totalQuota = $this->listing_quota ?? (int) Setting::get('agent_free_quota', 3);
        $used = $this->properties()->count();
        return max(0, $totalQuota - $used);
    }

    public function getUsedQuotaAttribute(): int
    {
        return $this->properties()->count();
    }

    public function canCreateListing(): bool
    {
        return $this->remaining_quota > 0;
    }

    public function getCleanPhoneAttribute(): string
    {
        $phone = preg_replace('/[^0-9]/', '', $this->phone);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }
        return $phone;
    }

    public function getWhatsappLinkAttribute(): string
    {
        $phone = $this->clean_phone;
        return "https://wa.me/{$phone}";
    }

    /**
     * Get agent initials / code prefix for property codes (e.g. AG, AS, BS)
     */
    public function getInitialsAttribute(): string
    {
        if (!empty($this->agent_code)) {
            return strtoupper(trim($this->agent_code));
        }

        return static::generateInitials($this->name);
    }

    /**
     * Generate short 2-3 letter initials from full name
     * E.g. "Agus" -> "AG"
     * "Agus Setiawan" -> "AS"
     * "Budi Santoso" -> "BS"
     * "Agus Tri Prasetyo" -> "ATP"
     */
    public static function generateInitials(?string $name): string
    {
        if (empty($name)) {
            return 'AG';
        }

        $clean = trim(preg_replace('/[^a-zA-Z\s]/', '', $name));
        $words = array_values(array_filter(explode(' ', $clean)));

        if (count($words) === 0) {
            return 'AG';
        }

        if (count($words) === 1) {
            return strtoupper(substr($words[0], 0, 2));
        }

        $initials = '';
        foreach (array_slice($words, 0, 3) as $w) {
            $initials .= strtoupper($w[0]);
        }

        return substr($initials, 0, 3);
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
}
