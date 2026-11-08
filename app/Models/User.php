<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'agent_id', 'must_change_password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'agent_id' => 'integer',
            'must_change_password' => 'boolean',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function clientRequests(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ClientRequest::class);
    }

    public function isAgent(): bool
    {
        return $this->role === 'agent' && !empty($this->agent_id);
    }

    public function isLeader(): bool
    {
        return $this->isAgent() && $this->agent && $this->agent->isLeader();
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
