<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\City;
use App\Models\ClientRequest;
use App\Models\Project;
use App\Models\Property;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientRequestController extends Controller
{
    /**
     * Display the Property Matcher & Client Requests database.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $isAgent = $user && $user->isAgent();
        $isLeader = $user && $user->isLeader();

        $activeTab = $request->get('tab', $request->hasAny(['search', 'listing_type', 'property_type', 'city_id', 'city', 'min_price', 'max_price']) ? 'matcher' : 'matcher');

        // Master lists for filters
        $cities = City::orderBy('name')->get();
        $projects = Project::orderBy('name')->get();
        $agents = Agent::where('is_verified', true)->orderBy('name')->get();

        // -------------------------------------------------------------
        // TAB 1: PROPERTY MATCHER & ALTERNATIVES
        // -------------------------------------------------------------
        $hasSearch = $request->filled('search') || $request->hasAny(['listing_type', 'property_type', 'city_id', 'city', 'min_price', 'max_price', 'bedrooms']);
        $exactMatches = collect();
        $alternativeMatches = collect();
        $matcherStats = ['exact_count' => 0, 'alt_count' => 0];

        $criteria = [
            'client_name' => $request->get('client_name', ''),
            'client_phone' => $request->get('client_phone', ''),
            'listing_type' => $request->get('listing_type', 'dijual'),
            'property_type' => $request->get('property_type', ''),
            'city_id' => $request->get('city_id', ''),
            'city' => $request->get('city', ''),
            'district' => $request->get('district', ''),
            'project_id' => $request->get('project_id', ''),
            'tower' => $request->get('tower', ''),
            'min_price' => $request->get('min_price') ? (int) str_replace(['.', ','], '', (string) $request->get('min_price')) : null,
            'max_price' => $request->get('max_price') ? (int) str_replace(['.', ','], '', (string) $request->get('max_price')) : null,
            'bedrooms' => $request->get('bedrooms') ? (int) $request->get('bedrooms') : null,
            'bathrooms' => $request->get('bathrooms') ? (int) $request->get('bathrooms') : null,
            'min_land_area' => $request->get('min_land_area') ? (float) $request->get('min_land_area') : null,
            'min_building_area' => $request->get('min_building_area') ? (float) $request->get('min_building_area') : null,
            'notes' => $request->get('notes', ''),
        ];

        if ($hasSearch) {
            // Build Exact Matches Query
            $exactQuery = Property::query()->where('status', 'active');

            if ($criteria['listing_type'] === 'dijual') {
                $exactQuery->whereIn('listing_type', ['dijual', 'dijual_disewa']);
            } elseif ($criteria['listing_type'] === 'disewa') {
                $exactQuery->whereIn('listing_type', ['disewa', 'dijual_disewa']);
            }

            if (!empty($criteria['property_type']) && $criteria['property_type'] !== 'semua') {
                $exactQuery->where('property_type', strtolower($criteria['property_type']));
            }

            if (!empty($criteria['city_id'])) {
                $exactQuery->where('city_id', $criteria['city_id']);
            } elseif (!empty($criteria['city'])) {
                $exactQuery->where('city', 'LIKE', '%' . $criteria['city'] . '%');
            }

            if (!empty($criteria['district'])) {
                $exactQuery->where(function ($q) use ($criteria) {
                    $q->where('district', 'LIKE', '%' . $criteria['district'] . '%')
                      ->orWhere('address', 'LIKE', '%' . $criteria['district'] . '%');
                });
            }

            if (!empty($criteria['project_id'])) {
                $exactQuery->where('project_id', $criteria['project_id']);
            }

            if (!empty($criteria['tower'])) {
                $cleanTw = trim(preg_replace('/^(?:tower|tw\.?)\s*/i', '', (string)$criteria['tower']));
                $rawTw = trim((string)$criteria['tower']);
                $exactQuery->where(function ($q) use ($rawTw, $cleanTw) {
                    $q->where('unit_tower', 'LIKE', '%' . $rawTw . '%');
                    if ($cleanTw !== '') {
                        $q->orWhere('unit_tower', 'LIKE', '%' . $cleanTw . '%');
                    }
                });
            }

            if ($criteria['min_price'] || $criteria['max_price']) {
                $minP = $criteria['min_price'];
                $maxP = $criteria['max_price'];
                $exactQuery->where(function ($q) use ($minP, $maxP, $criteria) {
                    if ($criteria['listing_type'] === 'disewa') {
                        if ($minP) $q->whereRaw('COALESCE(rent_price, price) >= ?', [$minP]);
                        if ($maxP) $q->whereRaw('COALESCE(rent_price, price) <= ?', [$maxP]);
                    } else {
                        if ($minP) $q->whereRaw('COALESCE(sale_price, price) >= ?', [$minP]);
                        if ($maxP) $q->whereRaw('COALESCE(sale_price, price) <= ?', [$maxP]);
                    }
                });
            }

            if (!empty($criteria['bedrooms'])) {
                $exactQuery->where('bedrooms', '>=', $criteria['bedrooms']);
            }

            if (!empty($criteria['bathrooms'])) {
                $exactQuery->where('bathrooms', '>=', $criteria['bathrooms']);
            }

            if (!empty($criteria['min_land_area'])) {
                $exactQuery->where('land_area', '>=', $criteria['min_land_area']);
            }

            if (!empty($criteria['min_building_area'])) {
                $exactQuery->where('building_area', '>=', $criteria['min_building_area']);
            }

            $exactMatches = $exactQuery->with(['agent', 'cityRef', 'project'])->latest()->take(20)->get();
            $matcherStats['exact_count'] = $exactMatches->count();

            // Build Alternatives (Near match, wider budget +/- 25% or same city/type)
            $exactIds = $exactMatches->pluck('id')->all();

            $altQuery = Property::query()
                ->where('status', 'active')
                ->whereNotIn('id', $exactIds);

            // Broad match by city or type
            if (!empty($criteria['city_id'])) {
                $altQuery->where('city_id', $criteria['city_id']);
            } elseif (!empty($criteria['city'])) {
                $altQuery->where('city', 'LIKE', '%' . $criteria['city'] . '%');
            }

            if ($criteria['listing_type'] === 'dijual') {
                $altQuery->whereIn('listing_type', ['dijual', 'dijual_disewa']);
            } elseif ($criteria['listing_type'] === 'disewa') {
                $altQuery->whereIn('listing_type', ['disewa', 'dijual_disewa']);
            }

            // Flexible budget (+/- 25%)
            if ($criteria['max_price']) {
                $flexibleMax = (int) ($criteria['max_price'] * 1.25);
                $altQuery->where(function ($q) use ($flexibleMax, $criteria) {
                    if ($criteria['listing_type'] === 'disewa') {
                        $q->whereRaw('COALESCE(rent_price, price) <= ?', [$flexibleMax]);
                    } else {
                        $q->whereRaw('COALESCE(sale_price, price) <= ?', [$flexibleMax]);
                    }
                });
            }

            $alternativeMatches = $altQuery->with(['agent', 'cityRef', 'project'])->latest()->take(12)->get();
            $matcherStats['alt_count'] = $alternativeMatches->count();
        }

        // -------------------------------------------------------------
        // TAB 2: CLIENT REQUESTS CRM DATABASE
        // -------------------------------------------------------------
        $requestsQuery = ClientRequest::with(['agent', 'cityRef', 'project', 'user']);

        // Scoping for agents
        if ($isAgent) {
            $scope = $request->get('scope', 'mine');
            if ($scope === 'all') {
                // All requests
            } elseif ($scope === 'team' && $isLeader) {
                $requestsQuery->whereIn('agent_id', $user->agent->allTeamAgentIds());
            } else {
                $requestsQuery->where(function ($q) use ($user) {
                    $q->where('agent_id', $user->agent_id)
                      ->orWhere('user_id', $user->id);
                });
            }
        } elseif ($request->filled('agent_id')) {
            $requestsQuery->where('agent_id', $request->agent_id);
        }

        // Filter by Status
        $statusFilter = $request->get('status', 'active');
        if ($statusFilter !== 'all' && in_array($statusFilter, ['active', 'dealt', 'cancelled'])) {
            $requestsQuery->where('status', $statusFilter);
        }

        // Search text in CRM
        if ($request->filled('q')) {
            $q = $request->get('q');
            $requestsQuery->where(function ($query) use ($q) {
                $query->where('client_name', 'LIKE', "%{$q}%")
                      ->orWhere('client_phone', 'LIKE', "%{$q}%")
                      ->orWhere('city', 'LIKE', "%{$q}%")
                      ->orWhere('district', 'LIKE', "%{$q}%")
                      ->orWhere('notes', 'LIKE', "%{$q}%");
            });
        }

        $clientRequests = $requestsQuery->latest()->paginate(15)->withQueryString();

        // Overall CRM Stats
        $baseStatsQuery = ClientRequest::query();
        if ($isAgent && $request->get('scope', 'mine') !== 'all') {
            $baseStatsQuery->where(function ($q) use ($user) {
                $q->where('agent_id', $user->agent_id)
                  ->orWhere('user_id', $user->id);
            });
        }
        $totalRequests = (clone $baseStatsQuery)->count();
        $activeRequests = (clone $baseStatsQuery)->where('status', 'active')->count();
        $dealtRequests = (clone $baseStatsQuery)->where('status', 'dealt')->count();

        // Exact payload for Alpine.js
        $exactPayload = $exactMatches->take(3)->map(function ($p) {
            return [
                'id' => $p->id,
                'title' => $p->title,
                'price' => $p->formatted_price,
                'location' => trim(($p->district ? $p->district . ', ' : '') . $p->city),
                'specs' => $p->isApartemen() ? ($p->full_unit_info ?: 'Apartemen') : ($p->bedrooms . ' KT • ' . $p->bathrooms . ' KM • LT: ' . ($p->land_area ?: '-') . 'm² • LB: ' . ($p->building_area ?: '-') . 'm²'),
                'url' => route('properties.show', $p->slug),
                'code' => $p->property_code,
            ];
        })->values()->all();

        return view('admin.client-requests.index', compact(
            'activeTab',
            'cities',
            'projects',
            'agents',
            'criteria',
            'hasSearch',
            'exactMatches',
            'alternativeMatches',
            'matcherStats',
            'exactPayload',
            'clientRequests',
            'totalRequests',
            'activeRequests',
            'dealtRequests'
        ));
    }

    /**
     * Store a newly created client request in the CRM database.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_name' => ['required', 'string', 'max:255'],
            'client_phone' => ['required', 'string', 'max:50'],
            'client_email' => ['nullable', 'email', 'max:255'],
            'listing_type' => ['required', 'in:dijual,disewa,semua'],
            'property_type' => ['nullable', 'string', 'max:100'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'city' => ['nullable', 'string', 'max:150'],
            'district' => ['nullable', 'string', 'max:150'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'tower' => ['nullable', 'string', 'max:50'],
            'min_price' => ['nullable'],
            'max_price' => ['nullable'],
            'min_bedrooms' => ['nullable', 'integer', 'min:0'],
            'min_bathrooms' => ['nullable', 'integer', 'min:0'],
            'min_land_area' => ['nullable', 'numeric', 'min:0'],
            'min_building_area' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'agent_id' => ['nullable', 'exists:agents,id'],
        ]);

        $user = auth()->user();
        $agentId = $user && $user->isAgent() ? $user->agent_id : ($request->agent_id ?? null);

        // Sanitize prices
        $minPrice = !empty($validated['min_price']) ? (int) str_replace(['.', ','], '', (string) $validated['min_price']) : null;
        $maxPrice = !empty($validated['max_price']) ? (int) str_replace(['.', ','], '', (string) $validated['max_price']) : null;

        // Auto resolve city name if city_id is set
        $cityName = $validated['city'] ?? null;
        if (!empty($validated['city_id']) && empty($cityName)) {
            $cityObj = City::find($validated['city_id']);
            $cityName = $cityObj?->name;
        }

        $clientRequest = ClientRequest::create([
            'user_id' => $user->id,
            'agent_id' => $agentId,
            'client_name' => trim($validated['client_name']),
            'client_phone' => trim($validated['client_phone']),
            'client_email' => $validated['client_email'] ?? null,
            'listing_type' => $validated['listing_type'],
            'property_type' => $validated['property_type'] ?: null,
            'city_id' => $validated['city_id'] ?? null,
            'city' => $cityName,
            'district' => $validated['district'] ?? null,
            'project_id' => $validated['project_id'] ?? null,
            'tower' => $validated['tower'] ?? null,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'min_bedrooms' => $validated['min_bedrooms'] ?? null,
            'min_bathrooms' => $validated['min_bathrooms'] ?? null,
            'min_land_area' => $validated['min_land_area'] ?? null,
            'min_building_area' => $validated['min_building_area'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => 'active',
        ]);

        return redirect()->route('admin.client-requests.index', ['tab' => 'requests'])
            ->with('success', "Permintaan klien \"{$clientRequest->client_name}\" berhasil disimpan ke database. Sistem otomatis memantau ketersediaan listing yang cocok!");
    }

    /**
     * Display matching details and WhatsApp share generator for a specific client request.
     */
    public function show(ClientRequest $clientRequest): View
    {
        $this->authorizeAccess($clientRequest);

        $clientRequest->load(['agent', 'cityRef', 'project', 'user']);
        $matches = $clientRequest->getMatchingProperties(12);

        // Broad alternatives if matches are few
        $altQuery = Property::query()
            ->where('status', 'active')
            ->whereNotIn('id', $matches->pluck('id')->all());

        if ($clientRequest->city_id) {
            $altQuery->where('city_id', $clientRequest->city_id);
        } elseif ($clientRequest->city) {
            $altQuery->where('city', 'LIKE', '%' . $clientRequest->city . '%');
        }

        if ($clientRequest->listing_type === 'dijual') {
            $altQuery->whereIn('listing_type', ['dijual', 'dijual_disewa']);
        } elseif ($clientRequest->listing_type === 'disewa') {
            $altQuery->whereIn('listing_type', ['disewa', 'dijual_disewa']);
        }

        $alternatives = $altQuery->latest()->take(6)->get();

        $defaultWaMessage = $clientRequest->generateWaMessage($matches->take(3));

        $matchesPayload = $matches->take(3)->map(function ($p) {
            return [
                'id' => $p->id,
                'title' => $p->title,
                'price' => $p->formatted_price,
                'location' => trim(($p->district ? $p->district . ', ' : '') . $p->city),
                'specs' => $p->isApartemen() ? ($p->full_unit_info ?: 'Apartemen') : ($p->bedrooms . ' KT • ' . $p->bathrooms . ' KM • LT: ' . ($p->land_area ?: '-') . 'm² • LB: ' . ($p->building_area ?: '-') . 'm²'),
                'url' => route('properties.show', $p->slug),
                'code' => $p->property_code,
            ];
        })->values()->all();

        return view('admin.client-requests.show', compact('clientRequest', 'matches', 'alternatives', 'defaultWaMessage', 'matchesPayload'));
    }

    /**
     * Update client request information or status.
     */
    public function update(Request $request, ClientRequest $clientRequest): RedirectResponse
    {
        $this->authorizeAccess($clientRequest);

        $validated = $request->validate([
            'client_name' => ['required', 'string', 'max:255'],
            'client_phone' => ['required', 'string', 'max:50'],
            'client_email' => ['nullable', 'email', 'max:255'],
            'listing_type' => ['sometimes', 'required', 'in:dijual,disewa,semua'],
            'property_type' => ['nullable', 'string', 'max:100'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'city' => ['nullable', 'string', 'max:150'],
            'district' => ['nullable', 'string', 'max:150'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'tower' => ['nullable', 'string', 'max:50'],
            'min_price' => ['nullable'],
            'max_price' => ['nullable'],
            'min_bedrooms' => ['nullable', 'integer', 'min:0'],
            'min_bathrooms' => ['nullable', 'integer', 'min:0'],
            'status' => ['sometimes', 'required', 'in:active,dealt,cancelled'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'agent_id' => ['nullable', 'exists:agents,id'],
        ]);

        $minPrice = !empty($validated['min_price']) ? (int) str_replace(['.', ','], '', (string) $validated['min_price']) : null;
        $maxPrice = !empty($validated['max_price']) ? (int) str_replace(['.', ','], '', (string) $validated['max_price']) : null;

        $cityName = $validated['city'] ?? null;
        if (!empty($validated['city_id']) && empty($cityName)) {
            $cityObj = City::find($validated['city_id']);
            $cityName = $cityObj?->name;
        }

        $updateData = [];
        if (isset($validated['client_name'])) $updateData['client_name'] = trim($validated['client_name']);
        if (isset($validated['client_phone'])) $updateData['client_phone'] = trim($validated['client_phone']);
        if (array_key_exists('client_email', $validated)) $updateData['client_email'] = $validated['client_email'];
        if (isset($validated['listing_type'])) $updateData['listing_type'] = $validated['listing_type'];
        if (array_key_exists('property_type', $validated)) $updateData['property_type'] = $validated['property_type'] ?: null;
        if (array_key_exists('city_id', $validated)) $updateData['city_id'] = $validated['city_id'];
        if (isset($cityName)) $updateData['city'] = $cityName;
        if (array_key_exists('district', $validated)) $updateData['district'] = $validated['district'];
        if (array_key_exists('project_id', $validated)) $updateData['project_id'] = $validated['project_id'];
        if (array_key_exists('tower', $validated)) $updateData['tower'] = $validated['tower'] ?: null;
        if (array_key_exists('min_price', $validated)) $updateData['min_price'] = $minPrice;
        if (array_key_exists('max_price', $validated)) $updateData['max_price'] = $maxPrice;
        if (array_key_exists('min_bedrooms', $validated)) $updateData['min_bedrooms'] = $validated['min_bedrooms'];
        if (array_key_exists('min_bathrooms', $validated)) $updateData['min_bathrooms'] = $validated['min_bathrooms'];
        if (isset($validated['status'])) $updateData['status'] = $validated['status'];
        if (array_key_exists('notes', $validated)) $updateData['notes'] = $validated['notes'];

        if (auth()->user()->isAdmin() && isset($validated['agent_id'])) {
            $updateData['agent_id'] = $validated['agent_id'];
        }

        $clientRequest->update($updateData);

        return redirect()->back()->with('success', 'Data permintaan klien berhasil diperbarui.');
    }

    /**
     * Remove client request from database.
     */
    public function destroy(ClientRequest $clientRequest): RedirectResponse
    {
        $this->authorizeAccess($clientRequest);

        $name = $clientRequest->client_name;
        $clientRequest->delete();

        return redirect()->route('admin.client-requests.index', ['tab' => 'requests'])
            ->with('success', "Permintaan klien \"{$name}\" berhasil dihapus.");
    }

    /**
     * Mark that a client request has just been followed up via WhatsApp.
     */
    public function markFollowedUp(ClientRequest $clientRequest): JsonResponse|RedirectResponse
    {
        $this->authorizeAccess($clientRequest);

        $clientRequest->update([
            'last_followed_up_at' => Carbon::now(),
        ]);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Status follow-up berhasil dicatat.',
                'followed_up_at' => $clientRequest->last_followed_up_at->diffForHumans(),
            ]);
        }

        return redirect()->back()->with('success', 'Status follow-up berhasil dicatat.');
    }

    /**
     * Authorize user access to the client request.
     */
    protected function authorizeAccess(ClientRequest $clientRequest): void
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return;
        }

        if ($user->isAgent()) {
            if ($clientRequest->agent_id === $user->agent_id || $clientRequest->user_id === $user->id) {
                return;
            }

            if ($user->isLeader() && in_array($clientRequest->agent_id, $user->agent->allTeamAgentIds())) {
                return;
            }
        }

        abort(403, 'Anda tidak memiliki hak akses untuk data permintaan klien ini.');
    }
}
