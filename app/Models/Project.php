<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'facilities' => 'array',
        'total_floors' => 'integer',
        'year_completed' => 'integer',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'rumah', 'cluster' => 'Rumah',
            'apartemen', 'kondominium' => 'Apartemen',
            'tanah' => 'Tanah',
            'ruko', 'ruko_complex' => 'Ruko',
            'gudang' => 'Gudang',
            'hotel' => 'Hotel',
            'gedung', 'commercial' => 'Commercial',
            'villa', 'lainnya' => 'Lainnya',
            default => ucfirst(str_replace('_', ' ', $this->type ?? '-')),
        };
    }
}
