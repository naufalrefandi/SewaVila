<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ClientRequest extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'min_price' => 'integer',
        'max_price' => 'integer',
        'min_bedrooms' => 'integer',
        'min_bathrooms' => 'integer',
        'min_land_area' => 'float',
        'min_building_area' => 'float',
        'last_followed_up_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function cityRef(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function districtRef(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function getCleanPhoneAttribute(): string
    {
        $raw = preg_replace('/[^0-9]/', '', (string) $this->client_phone);
        if (empty($raw)) {
            return '';
        }

        if (str_starts_with($raw, '0')) {
            return '62' . substr($raw, 1);
        }

        if (!str_starts_with($raw, '62')) {
            return '62' . $raw;
        }

        return $raw;
    }

    public function getBudgetLabelAttribute(): string
    {
        if ($this->min_price && $this->max_price) {
            return Property::formatMoney($this->min_price) . ' - ' . Property::formatMoney($this->max_price);
        }

        if ($this->max_price) {
            return 'Maks. ' . Property::formatMoney($this->max_price);
        }

        if ($this->min_price) {
            return 'Min. ' . Property::formatMoney($this->min_price);
        }

        return 'Fleksibel';
    }

    public function getLocationLabelAttribute(): string
    {
        $parts = [];
        if ($this->project) {
            $towerPart = $this->tower ? ' (' . (str_starts_with(strtolower($this->tower), 'tower') ? $this->tower : 'Tower ' . $this->tower) . ')' : '';
            $parts[] = $this->project->name . $towerPart;
        } elseif ($this->tower) {
            $parts[] = str_starts_with(strtolower($this->tower), 'tower') ? $this->tower : 'Tower ' . $this->tower;
        }
        if ($this->district) {
            $parts[] = $this->district;
        }
        if ($this->city) {
            $parts[] = $this->city;
        } elseif ($this->cityRef) {
            $parts[] = $this->cityRef->name;
        }

        return !empty($parts) ? implode(', ', $parts) : 'Semua Lokasi';
    }

    public function getPropertyTypeLabelAttribute(): string
    {
        if (empty($this->property_type) || strtolower($this->property_type) === 'semua') {
            return 'Semua Tipe Properti';
        }

        return ucfirst($this->property_type);
    }

    public function getListingTypeLabelAttribute(): string
    {
        return match (strtolower($this->listing_type ?? 'dijual')) {
            'disewa' => 'Disewa',
            'semua' => 'Dijual / Disewa',
            default => 'Dijual',
        };
    }

    /**
     * Build matching query for active properties that fit this client request criteria.
     */
    public function getMatchingPropertiesQuery(): Builder
    {
        $query = Property::query()->where('status', 'active');

        // Listing Type filter
        if ($this->listing_type === 'dijual') {
            $query->whereIn('listing_type', ['dijual', 'dijual_disewa']);
        } elseif ($this->listing_type === 'disewa') {
            $query->whereIn('listing_type', ['disewa', 'dijual_disewa']);
        }

        // Property Type filter
        if (!empty($this->property_type) && strtolower($this->property_type) !== 'semua') {
            $query->where('property_type', strtolower($this->property_type));
        }

        // City filter
        if (!empty($this->city_id)) {
            $query->where('city_id', $this->city_id);
        } elseif (!empty($this->city)) {
            $query->where('city', 'LIKE', '%' . $this->city . '%');
        }

        // District filter
        if (!empty($this->district_id)) {
            $query->where('district_id', $this->district_id);
        } elseif (!empty($this->district)) {
            $query->where(function ($q) {
                $q->where('district', 'LIKE', '%' . $this->district . '%')
                  ->orWhere('address', 'LIKE', '%' . $this->district . '%');
            });
        }

        // Project / Building filter
        if (!empty($this->project_id)) {
            $query->where('project_id', $this->project_id);
        }

        // Tower filter (Khusus Apartemen / Proyek)
        if (!empty($this->tower)) {
            $cleanTw = trim(preg_replace('/^(?:tower|tw\.?)\s*/i', '', (string)$this->tower));
            $rawTw = trim((string)$this->tower);
            $query->where(function ($q) use ($rawTw, $cleanTw) {
                $q->where('unit_tower', 'LIKE', '%' . $rawTw . '%');
                if ($cleanTw !== '') {
                    $q->orWhere('unit_tower', 'LIKE', '%' . $cleanTw . '%');
                }
            });
        }

        // Price range filter
        $minPrice = $this->min_price;
        $maxPrice = $this->max_price;

        if ($minPrice || $maxPrice) {
            $query->where(function ($q) use ($minPrice, $maxPrice) {
                // Determine price column based on listing type
                if ($this->listing_type === 'disewa') {
                    if ($minPrice) {
                        $q->whereRaw('COALESCE(rent_price, price) >= ?', [$minPrice]);
                    }
                    if ($maxPrice) {
                        $q->whereRaw('COALESCE(rent_price, price) <= ?', [$maxPrice]);
                    }
                } else {
                    if ($minPrice) {
                        $q->whereRaw('COALESCE(sale_price, price) >= ?', [$minPrice]);
                    }
                    if ($maxPrice) {
                        $q->whereRaw('COALESCE(sale_price, price) <= ?', [$maxPrice]);
                    }
                }
            });
        }

        // Bedrooms filter
        if (!empty($this->min_bedrooms)) {
            $query->where('bedrooms', '>=', $this->min_bedrooms);
        }

        // Bathrooms filter
        if (!empty($this->min_bathrooms)) {
            $query->where('bathrooms', '>=', $this->min_bathrooms);
        }

        // Land Area filter
        if (!empty($this->min_land_area)) {
            $query->where('land_area', '>=', $this->min_land_area);
        }

        // Building Area filter
        if (!empty($this->min_building_area)) {
            $query->where('building_area', '>=', $this->min_building_area);
        }

        return $query;
    }

    /**
     * Get real-time count of matching properties.
     */
    public function getMatchingPropertiesCountAttribute(): int
    {
        return $this->getMatchingPropertiesQuery()->count();
    }

    /**
     * Get matching properties collection.
     */
    public function getMatchingProperties(int $limit = 6): Collection
    {
        return $this->getMatchingPropertiesQuery()
            ->with(['agent', 'cityRef', 'project'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Generate personalized WhatsApp broadcast/follow-up message.
     */
    public function generateWaMessage(?iterable $properties = null, ?Agent $senderAgent = null): string
    {
        $agent = $senderAgent ?: ($this->agent ?: (auth()->check() && auth()->user()->agent ? auth()->user()->agent : null));
        $siteName = Setting::getSiteName();
        $agentName = $agent?->name ?: auth()->user()?->name ?: $siteName;
        $agentOffice = $agent?->office_branch ?: $siteName;
        $contactIdentity = trim("{$agentName} ({$agentOffice})");

        $lines = [];
        $lines[] = "Halo *{$this->client_name}*,";
        $lines[] = "";
        $lines[] = "Perkenalkan saya *{$agentName}* dari *{$agentOffice}*.";
        $lines[] = "Menindaklanjuti kebutuhan properti yang sedang Bapak/Ibu cari:";
        $lines[] = "📋 *Kriteria:* " . $this->property_type_label . " (" . $this->listing_type_label . ")";
        $lines[] = "📍 *Lokasi:* " . $this->location_label;
        $lines[] = "💰 *Budget:* " . $this->budget_label;

        if ($this->tower) {
            $lines[] = "🏢 *Tower:* " . (str_starts_with(strtolower($this->tower), 'tower') ? $this->tower : 'Tower ' . $this->tower);
        }

        if ($this->min_bedrooms) {
            $lines[] = "🛏️ *Minimal KT:* " . $this->min_bedrooms . " Kamar";
        }

        $lines[] = "";

        $items = $properties ? collect($properties) : $this->getMatchingProperties(4);

        if ($items->isNotEmpty()) {
            $lines[] = "Berikut beberapa rekomendasi unit terbaik yang sangat cocok untuk Bapak/Ibu:";
            $lines[] = "";

            $index = 1;
            foreach ($items as $prop) {
                /** @var Property $prop */
                $specs = [];
                if ($prop->isApartemen()) {
                    if (!empty($prop->unit_type)) $specs[] = "Tipe {$prop->unit_type}";
                    if (!empty($prop->formatted_tower)) $specs[] = $prop->formatted_tower;
                    if (!empty($prop->formatted_floor_short)) $specs[] = $prop->formatted_floor_short;
                } else {
                    if ($prop->bedrooms) $specs[] = "{$prop->bedrooms} KT";
                    if ($prop->bathrooms) $specs[] = "{$prop->bathrooms} KM";
                    if ($prop->land_area) $specs[] = "LT: {$prop->land_area}m²";
                    if ($prop->building_area) $specs[] = "LB: {$prop->building_area}m²";
                }

                $specText = !empty($specs) ? implode(' • ', $specs) : ($prop->property_type ? ucfirst($prop->property_type) : '');
                $location = trim(($prop->district ? $prop->district . ', ' : '') . $prop->city);
                $url = !empty($prop->slug) ? route('properties.show', $prop->slug) : '';

                $lines[] = "{$index}. *{$prop->title}*";
                $lines[] = "   📍 Lokasi: {$location}";
                $lines[] = "   💵 Harga: *{$prop->formatted_price}*";
                if ($specText) {
                    $lines[] = "   📐 Spek: {$specText}";
                }
                if ($url) {
                    $lines[] = "   🔗 Foto & Detail: {$url}";
                }
                $lines[] = "";
                $index++;
            }

            $lines[] = "Kapan ada waktu luang untuk kita jadwalkan survey lokasi bersama? Silakan hubungi saya untuk info lebih lanjut ya Pak/Bu. Terima kasih! 🙏";
        } else {
            $lines[] = "Saat ini kami sedang memfilter unit terbaik sesuai kriteria di atas.";
            $lines[] = "Begitu unit baru masuk ke database kami, saya akan segera kirimkan detail lengkap dan foto unitnya ke nomor WhatsApp ini ya Pak/Bu. 🙏";
        }

        $lines[] = "";
        $lines[] = "Salam,";
        $lines[] = "*{$contactIdentity}*";
        if ($agent?->phone) {
            $lines[] = "WhatsApp: {$agent->phone}";
        }

        return implode("\n", $lines);
    }
}

