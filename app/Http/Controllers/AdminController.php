<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\AgentApplication;
use App\Models\AgentTransaction;
use App\Models\City;
use App\Models\District;
use App\Models\Inquiry;
use App\Models\Project;
use App\Models\Property;
use App\Models\PropertyRental;
use App\Models\PropertySubmission;
use App\Models\Province;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Admin Overview Dashboard
     */
    public function dashboard(): View
    {
        $user = auth()->user();
        $isAgent = $user && $user->isAgent();
        $agentId = $isAgent ? $user->agent_id : null;

        if ($isAgent) {
            $stats = [
                'total_properties' => Property::where('agent_id', $agentId)->count(),
                'active_properties' => Property::where('agent_id', $agentId)->active()->count(),
                'for_sale' => Property::where('agent_id', $agentId)->forSale()->count(),
                'for_rent' => Property::where('agent_id', $agentId)->forRent()->count(),
                'total_agents' => Agent::count(),
                'total_inquiries' => Inquiry::where('agent_id', $agentId)->count(),
                'new_inquiries' => Inquiry::where('agent_id', $agentId)->where('status', 'new')->count(),
                'total_submissions' => PropertySubmission::count(),
                'pending_submissions' => PropertySubmission::where('status', 'pending')->count(),
                'total_projects' => Project::count(),
                'pending_agent_apps' => 0,
                'tanah_count' => Property::where('agent_id', $agentId)->where('property_type', 'tanah')->count(),
                'gudang_count' => Property::where('agent_id', $agentId)->where('property_type', 'gudang')->count(),
                'total_rentals' => PropertyRental::where('agent_id', $agentId)->where('status', 'active')->count(),
                'expiring_rentals' => PropertyRental::where('agent_id', $agentId)->where('status', 'active')->whereDate('end_date', '<=', \Carbon\Carbon::today()->addDays(30))->count(),
            ];

            $recentProperties = Property::where('agent_id', $agentId)->with(['agent', 'project'])->latest()->take(5)->get();
            $recentInquiries = Inquiry::where('agent_id', $agentId)->with(['property', 'agent'])->latest()->take(5)->get();
            $recentSubmissions = collect();
            $recentAgentApps = collect();
        } else {
            $stats = [
                'total_properties' => Property::count(),
                'active_properties' => Property::active()->count(),
                'for_sale' => Property::forSale()->count(),
                'for_rent' => Property::forRent()->count(),
                'total_agents' => Agent::count(),
                'total_inquiries' => Inquiry::count(),
                'new_inquiries' => Inquiry::where('status', 'new')->count(),
                'total_submissions' => PropertySubmission::count(),
                'pending_submissions' => PropertySubmission::where('status', 'pending')->count(),
                'total_projects' => Project::count(),
                'pending_agent_apps' => AgentApplication::where('status', 'pending')->count(),
                'tanah_count' => Property::where('property_type', 'tanah')->count(),
                'gudang_count' => Property::where('property_type', 'gudang')->count(),
                'total_rentals' => PropertyRental::where('status', 'active')->count(),
                'expiring_rentals' => PropertyRental::where('status', 'active')->whereDate('end_date', '<=', \Carbon\Carbon::today()->addDays(30))->count(),
            ];

            $recentProperties = Property::with(['agent', 'project'])->latest()->take(5)->get();
            $recentInquiries = Inquiry::with(['property', 'agent'])->latest()->take(5)->get();
            $recentSubmissions = PropertySubmission::latest()->take(5)->get();
            $recentAgentApps = AgentApplication::latest()->take(4)->get();
        }

        $currentAgent = $isAgent && $user ? $user->agent : null;
        $topupPrice = (float) Setting::get('agent_topup_price', 100000);
        $topupQuota = (int) Setting::get('agent_topup_quota', 5);

        return view('admin.dashboard', compact(
            'stats',
            'recentProperties',
            'recentInquiries',
            'recentSubmissions',
            'recentAgentApps',
            'isAgent',
            'currentAgent',
            'topupPrice',
            'topupQuota'
        ));
    }

    /**
     * Properties list in admin
     */
    public function properties(Request $request): View
    {
        $user = auth()->user();
        $isAgent = $user && $user->isAgent();
        $isLeader = $user && $user->isLeader();
        $query = Property::with(['agent.leader', 'project', 'cityRef']);

        if ($isAgent) {
            $scope = $request->get('scope');
            if ($scope === 'all') {
                // Browse all listings in network
            } elseif ($scope === 'team' && $isLeader) {
                // View all team listings (team members + self)
                $query->whereIn('agent_id', $user->agent->allTeamAgentIds());
            } else {
                // Default: my own listings
                $query->where('agent_id', $user->agent_id);
            }
        }

        // Filter by specific Agent
        if ($request->filled('agent_id')) {
            $query->where('agent_id', $request->agent_id);
        }

        // Filter by Leader Agent (shows leader + all their team members)
        if ($request->filled('leader_id')) {
            $leader = Agent::with('teamMembers')->find($request->leader_id);
            if ($leader) {
                $query->whereIn('agent_id', $leader->allTeamAgentIds());
            }
        }

        if ($request->has('q') && !empty($request->q)) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('city', 'like', "%{$q}%")
                    ->orWhere('district', 'like', "%{$q}%")
                    ->orWhere('property_code', 'like', "%{$q}%");
            });
        }

        if ($request->has('type') && !empty($request->type)) {
            $query->where('property_type', $request->type);
        }

        if ($request->has('listing') && !empty($request->listing)) {
            $query->where('listing_type', $request->listing);
        }

        if ($request->has('project_id') && !empty($request->project_id)) {
            $query->where('project_id', $request->project_id);
        }

        $properties = $query->latest()->paginate(10)->withQueryString();
        $projects = Project::orderBy('name')->get();
        $agents = Agent::with('leader')->orderBy('name')->get();
        $leaders = Agent::where('is_leader', true)->with('teamMembers')->orderBy('name')->get();
        $selectedAgent = $request->filled('agent_id') ? Agent::find($request->agent_id) : null;
        $selectedLeader = $request->filled('leader_id') ? Agent::find($request->leader_id) : null;

        $currentAgent = ($user && $user->isAgent()) ? $user->agent : null;
        $pendingFeaturedPropertyIds = ($isAgent && $user->agent) ? AgentTransaction::where('agent_id', $user->agent_id)->where('type', 'featured_listing')->where('status', 'pending')->pluck('property_id')->toArray() : [];

        $topupPrice = (float) Setting::get('agent_topup_price', 100000);
        $topupQuota = (int) Setting::get('agent_topup_quota', 5);
        $featuredPrice = (float) Setting::get('featured_listing_price', 50000);
        $featuredDuration = (int) Setting::get('featured_duration_days', 30);
        $qrisImage = Setting::getQrisUrl();
        $merchantName = Setting::get('qris_merchant_name', Setting::getSiteName() . ' Official QRIS');
        $bankName = Setting::get('bank_name', 'BCA (Bank Central Asia)');
        $bankAccNum = Setting::get('bank_account_number', '8290-9988-7766');
        $bankAccName = Setting::get('bank_account_name', 'YPROPERTI INDONESIA');
        $paymentInstructions = Setting::get('payment_instructions', '');
        $bcaAccount = Setting::getBcaAccount();
        $otherBankAccount = Setting::getOtherBankAccount();

        return view('admin.properties.index', compact(
            'properties',
            'projects',
            'agents',
            'leaders',
            'selectedAgent',
            'selectedLeader',
            'isAgent',
            'isLeader',
            'currentAgent',
            'pendingFeaturedPropertyIds',
            'topupPrice',
            'topupQuota',
            'featuredPrice',
            'featuredDuration',
            'qrisImage',
            'merchantName',
            'bankName',
            'bankAccNum',
            'bankAccName',
            'bcaAccount',
            'otherBankAccount',
            'paymentInstructions'
        ));
    }

    /**
     * Create property form
     */
    public function createProperty(): View|RedirectResponse
    {
        $user = auth()->user();
        if ($user && $user->isAgent()) {
            $currentAgent = $user->agent;
            if ($currentAgent && !$currentAgent->canCreateListing()) {
                return redirect()->route('admin.properties')
                    ->with('quota_exhausted', true)
                    ->with('error', "Kuota listing Anda telah mencapai batas maksimal ({$currentAgent->used_quota}/{$currentAgent->listing_quota} listing). Silakan lakukan topup kuota listing untuk dapat menambah listing baru.");
            }
        } else {
            $currentAgent = null;
        }

        $agents = Agent::orderBy('name')->get();
        $provinces = Province::with('cities.districts')->orderBy('name')->get();
        $cities = City::with(['districts', 'projects'])->orderBy('name')->get();
        $projects = Project::with('city')->orderBy('name')->get();
        $nextSequences = Property::getNextSequences();

        return view('admin.properties.create', compact('agents', 'provinces', 'cities', 'projects', 'currentAgent', 'nextSequences'));
    }

    /**
     * Generate property code via AJAX
     */
    public function generatePropertyCode(Request $request): \Illuminate\Http\JsonResponse
    {
        $code = Property::generateCode(
            $request->input('property_type', 'apartemen'),
            $request->input('listing_type', 'dijual'),
            $request->input('source', 'A'),
            $request->input('agent_id')
        );
        return response()->json(['code' => $code]);
    }

    /**
     * Store new property
     */
    public function storeProperty(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'property_code' => 'nullable|string|max:50',
            'owner_name' => 'nullable|string|max:100',
            'owner_phone' => 'nullable|string|max:50',
            'owner_type' => 'nullable|string|in:vendor,agent',
            'owner_office' => 'nullable|string|max:150',
            'agent_id' => 'nullable|exists:agents,id',
            'project_id' => 'nullable',
            'custom_project_name' => 'nullable|string|max:200',
            'province_id' => 'nullable|exists:provinces,id',
            'city_id' => 'nullable',
            'custom_city' => 'nullable|string|max:100',
            'district_id' => 'nullable',
            'title' => 'required|string|max:255',
            'title_suffix' => 'nullable|string|max:200',
            'code_source' => 'nullable|string|in:A,V',
            'property_type' => 'required|string|in:rumah,apartemen,ruko,villa,tanah,gudang,tanah_residensial,tanah_komersial,tempat_usaha,kantor,pabrik,kost,hotel,gedung',
            'listing_type' => 'required|string|in:dijual,disewa,dijual_disewa',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'rent_price' => 'nullable|numeric|min:0',
            'monthly_rent_price' => 'nullable|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'price_prefix' => 'nullable|string|max:50',
            'rent_period' => 'nullable|string|max:50',
            'ipl_info' => 'nullable|string|max:100',
            'transfer_fee_info' => 'nullable|string|max:100',

            // Unit detail
            'unit_type' => 'nullable|string|max:100',
            'unit_tower' => 'nullable|string|max:100',
            'unit_floor' => 'nullable|string|max:50',
            'unit_number' => 'nullable|string|max:50',
            'unit_view' => 'nullable|string|max:100',
            'dimension' => 'nullable|string|max:50',

            'bedrooms' => 'nullable|integer|min:0',
            'maid_bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'maid_bathrooms' => 'nullable|integer|min:0',
            'land_area' => 'nullable|numeric|min:0',
            'building_area' => 'nullable|numeric|min:0',
            'carports' => 'nullable|integer|min:0',
            'floors' => 'nullable|integer|min:1',
            'certificate' => 'nullable|string|max:50',
            'electricity' => 'nullable|integer',
            'facing' => 'nullable|string|max:100',
            'road_width' => 'nullable|string|max:100',
            'furnished_status' => 'nullable|string|in:unfurnished,semi-furnished,fully-furnished',
            'flooring_material' => 'nullable|string|max:100',
            'year_built' => 'nullable|integer',
            'province' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'description' => 'nullable|string',
            'internal_notes' => 'nullable|string',
            'image_files' => 'nullable|array',
            'image_files.*' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:10240',
            'images_text' => 'nullable|string',
            'video_file' => 'nullable|file|mimes:mp4,mov,webm|max:51200',
            'video_url' => 'nullable|string|max:500',
            'facilities_text' => 'nullable|string',
            'is_featured' => 'nullable|boolean',
            'is_urgent' => 'nullable|boolean',
            'is_new_launch' => 'nullable|boolean',
            'is_verified' => 'nullable|boolean',
            'status' => 'required|string|in:active,sold,rented,pending',
        ]);

        // 1. Process custom city input or resolve existing city
        $customCity = trim($request->input('custom_city') ?? '');
        if ($customCity !== '') {
            $city = City::whereRaw('LOWER(name) = ?', [strtolower($customCity)])->first();
            if (!$city) {
                $province = Province::first() ?? Province::create(['name' => 'Jawa Timur', 'slug' => 'jawa-timur']);
                $city = City::create([
                    'name' => ucwords(strtolower($customCity)),
                    'slug' => Str::slug($customCity . '-' . uniqid()),
                    'province_id' => $province->id,
                ]);
            }
            $validated['city_id'] = $city->id;
            $validated['city'] = $city->name;
            $validated['province'] = $city->province ? $city->province->name : 'Jawa Timur';
            $validated['province_id'] = $city->province_id;
        } elseif (!empty($validated['city_id']) && is_numeric($validated['city_id'])) {
            $city = City::with('province')->find($validated['city_id']);
            if ($city) {
                $validated['city'] = $city->name;
                $validated['province'] = $city->province ? $city->province->name : ($validated['province'] ?? 'Indonesia');
                $validated['province_id'] = $city->province_id;
            }
        }

        // 2. Process custom project/apartemen input or resolve existing project
        $customProject = trim($request->input('custom_project_name') ?? '');
        if ($customProject !== '') {
            $cityId = $validated['city_id'] ?? null;
            $projectType = $validated['property_type'] === 'apartemen' ? 'apartemen' : 'residensial';
            $project = Project::whereRaw('LOWER(name) = ?', [strtolower($customProject)])
                ->when($cityId, fn($q) => $q->where('city_id', $cityId))
                ->first();

            if (!$project) {
                $project = Project::create([
                    'name' => ucwords(strtolower($customProject)),
                    'slug' => Str::slug($customProject . '-' . uniqid()),
                    'type' => $projectType,
                    'city_id' => $cityId,
                    'developer' => null,
                ]);
            }
            $validated['project_id'] = $project->id;
        } elseif (!empty($validated['project_id']) && !is_numeric($validated['project_id'])) {
            $projName = trim($validated['project_id']);
            if ($projName !== '' && $projName !== '__custom__') {
                $cityId = $validated['city_id'] ?? null;
                $projectType = $validated['property_type'] === 'apartemen' ? 'apartemen' : 'residensial';
                $project = Project::firstOrCreate(
                    ['name' => $projName, 'city_id' => $cityId],
                    ['slug' => Str::slug($projName . '-' . uniqid()), 'type' => $projectType]
                );
                $validated['project_id'] = $project->id;
            } else {
                $validated['project_id'] = null;
            }
        }

        if (!empty($validated['district_id']) && is_numeric($validated['district_id'])) {
            $district = District::find($validated['district_id']);
            if ($district) {
                $validated['district'] = $district->name;
            }
        } else {
            $validated['district_id'] = null;
        }

        // Lock agent_id & enforce quota if user is an agent
        $user = auth()->user();
        if ($user && $user->isAgent()) {
            $agent = $user->agent;
            if ($agent && !$agent->canCreateListing()) {
                return redirect()->route('admin.properties')
                    ->with('quota_exhausted', true)
                    ->with('error', "Kuota listing Anda telah mencapai batas maksimal ({$agent->used_quota}/{$agent->listing_quota} listing). Silakan lakukan topup kuota listing.");
            }
            $validated['agent_id'] = $user->agent_id;
        } else {
            $validated['agent_id'] = !empty($validated['agent_id']) ? $validated['agent_id'] : ($user?->agent_id ?? $user?->agent?->id ?? null);
        }

        // Process images
        $images = [];
        if ($request->hasFile('image_files')) {
            $isWatermark = (bool) Setting::get('watermark_enabled', 1);
            foreach ($request->file('image_files') as $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('properties/images', 'public');
                    if ($isWatermark) {
                        \App\Services\WatermarkService::apply($path);
                    }
                    $images[] = $path;
                }
            }
        }

        if (!empty($validated['images_text'])) {
            $urlImages = array_values(array_filter(array_map('trim', explode("\n", $validated['images_text']))));
            $images = array_merge($images, $urlImages);
        }

        if (empty($images)) {
            $images = Property::getDefaultImagesForType($validated['property_type'] ?? 'apartemen');
        }
        // Process video
        $videoPath = null;
        if ($request->hasFile('video_file')) {
            $vFile = $request->file('video_file');
            if ($vFile && $vFile->isValid()) {
                $videoPath = $vFile->store('properties/videos', 'public');
            }
        } elseif (!empty($validated['video_url'])) {
            $videoPath = trim($validated['video_url']);
        }

        $facilities = !empty($validated['facilities_text'])
            ? array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $validated['facilities_text']))))
            : [];

        $slug = Str::slug($validated['title']);
        // Double-submit / rapid duplicate submission guard (within 10 seconds)
        $recentDuplicate = Property::where('title', $validated['title'])
            ->where('agent_id', $validated['agent_id'] ?? null)
            ->where('price', $validated['price'])
            ->where('created_at', '>=', now()->subSeconds(10))
            ->first();

        if ($recentDuplicate) {
            return redirect()->route('admin.properties')->with('success', 'Listing properti baru berhasil ditambahkan!');
        }

        $originalSlug = $slug;
        $count = 1;
        while (Property::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-" . $count++;
        }

        $codeSource = $validated['code_source'] ?? (($user && $user->isAgent()) ? 'A' : 'A');
        $agentForCode = ($codeSource === 'V') ? null : (!empty($validated['agent_id']) ? $validated['agent_id'] : (($user && $user->isAgent()) ? $user->agent_id : null));
        $finalPropertyCode = !empty($validated['property_code'])
            ? trim($validated['property_code'])
            : Property::generateCode($validated['property_type'], $validated['listing_type'], $codeSource, $agentForCode);

        // Ensure property_code uniqueness
        if (empty($finalPropertyCode) || Property::where('property_code', $finalPropertyCode)->exists()) {
            $finalPropertyCode = Property::generateCode($validated['property_type'], $validated['listing_type'], $codeSource, $agentForCode);
        }

        $salePrice = $validated['sale_price'] ?? null;
        $rentPrice = $validated['rent_price'] ?? null;

        if ($validated['property_type'] === 'apartemen') {
            if ($validated['listing_type'] === 'dijual') {
                $salePrice = $validated['price'];
                $rentPrice = null;
            } else {
                $rentPrice = $validated['price'];
                $salePrice = null;
            }
        } else {
            if ($validated['listing_type'] === 'dijual' && empty($salePrice)) {
                $salePrice = $validated['price'];
            }
            if ($validated['listing_type'] === 'disewa' && empty($rentPrice)) {
                $rentPrice = $validated['price'];
            }
        }

        Property::create([
            'property_code' => $finalPropertyCode,
            'owner_type' => $validated['owner_type'] ?? 'vendor',
            'owner_office' => $validated['owner_office'] ?? null,
            'owner_name' => $validated['owner_name'] ?? null,
            'owner_phone' => $validated['owner_phone'] ?? null,
            'agent_id' => $validated['agent_id'] ?? null,
            'project_id' => $validated['project_id'] ?? null,
            'province_id' => $validated['province_id'] ?? null,
            'city_id' => $validated['city_id'] ?? null,
            'district_id' => $validated['district_id'] ?? null,
            'unit_type' => $validated['unit_type'] ?? null,
            'unit_tower' => $validated['unit_tower'] ?? null,
            'unit_floor' => $validated['unit_floor'] ?? null,
            'unit_number' => $validated['unit_number'] ?? null,
            'unit_view' => $validated['unit_view'] ?? null,
            'dimension' => $validated['dimension'] ?? null,
            'title' => $validated['title'],
            'slug' => $slug,
            'property_type' => $validated['property_type'],
            'listing_type' => $validated['listing_type'],
            'price' => $validated['price'],
            'sale_price' => $salePrice,
            'rent_price' => $rentPrice,
            'monthly_rent_price' => $validated['monthly_rent_price'] ?? null,
            'original_price' => $validated['original_price'] ?? null,
            'price_prefix' => $validated['price_prefix'] ?? null,
            'rent_period' => $validated['rent_period'] ?? null,
            'ipl_info' => $validated['ipl_info'] ?? null,
            'transfer_fee_info' => $validated['transfer_fee_info'] ?? null,
            'bedrooms' => $validated['bedrooms'] ?? null,
            'maid_bedrooms' => $validated['maid_bedrooms'] ?? null,
            'bathrooms' => $validated['bathrooms'] ?? null,
            'maid_bathrooms' => $validated['maid_bathrooms'] ?? null,
            'land_area' => $validated['land_area'] ?? null,
            'building_area' => $validated['building_area'] ?? null,
            'carports' => $validated['carports'] ?? null,
            'floors' => $validated['floors'] ?? 1,
            'certificate' => $validated['certificate'] ?? null,
            'electricity' => $validated['electricity'] ?? null,
            'facing' => $validated['facing'] ?? null,
            'road_width' => $validated['road_width'] ?? null,
            'furnished_status' => $validated['furnished_status'] ?? 'unfurnished',
            'flooring_material' => $validated['flooring_material'] ?? null,
            'year_built' => $validated['year_built'] ?? null,
            'province' => $validated['province'] ?? 'Jawa Timur',
            'city' => $validated['city'] ?? 'Surabaya',
            'district' => $validated['district'] ?? null,
            'address' => $validated['address'] ?? null,
            'description' => $validated['description'] ?? null,
            'internal_notes' => $validated['internal_notes'] ?? null,
            'images' => $images,
            'video' => $videoPath,
            'facilities' => $facilities,
            'is_featured' => ($user && $user->isAgent()) ? false : $request->boolean('is_featured'),
            'is_urgent' => $request->boolean('is_urgent'),
            'is_new_launch' => $request->boolean('is_new_launch'),
            'is_verified' => $request->boolean('is_verified', true),
            'status' => $validated['status'],
        ]);

        if ($user && $user->isAgent() && $user->agent) {
            $user->agent->update([
                'active_listings_count' => $user->agent->properties()->count(),
            ]);
        }

        return redirect()->route('admin.properties')->with('success', 'Listing properti baru berhasil ditambahkan!');
    }

    /**
     * Edit property form
     */
    public function editProperty(Property $property): View
    {
        $user = auth()->user();
        if ($user && $user->isAgent() && $property->agent_id !== $user->agent_id) {
            abort(403, 'Akses Ditolak: Anda hanya dapat mengedit listing milik Anda sendiri.');
        }

        $currentAgent = ($user && $user->isAgent()) ? $user->agent : null;
        $agents = Agent::orderBy('name')->get();
        $provinces = Province::orderBy('name')->get();
        $cities = City::with(['districts', 'projects'])->orderBy('name')->get();
        $projects = Project::with('city')->orderBy('name')->get();
        $nextSequences = Property::getNextSequences();

        return view('admin.properties.edit', compact('property', 'agents', 'provinces', 'cities', 'projects', 'currentAgent', 'nextSequences'));
    }

    /**
     * Update property
     */
    public function updateProperty(Request $request, Property $property): RedirectResponse
    {
        $user = auth()->user();
        if ($user && $user->isAgent() && $property->agent_id !== $user->agent_id) {
            abort(403, 'Akses Ditolak: Anda hanya dapat memperbarui listing milik Anda sendiri.');
        }
        $validated = $request->validate([
            'property_code' => 'nullable|string|max:50',
            'owner_name' => 'nullable|string|max:100',
            'owner_phone' => 'nullable|string|max:50',
            'owner_type' => 'nullable|string|in:vendor,agent',
            'owner_office' => 'nullable|string|max:150',
            'agent_id' => 'nullable|exists:agents,id',
            'project_id' => 'nullable',
            'custom_project_name' => 'nullable|string|max:200',
            'province_id' => 'nullable|exists:provinces,id',
            'city_id' => 'nullable',
            'custom_city' => 'nullable|string|max:100',
            'district_id' => 'nullable',
            'title' => 'required|string|max:255',
            'title_suffix' => 'nullable|string|max:200',
            'code_source' => 'nullable|string|in:A,V',
            'property_type' => 'required|string|in:rumah,apartemen,ruko,villa,tanah,gudang,tanah_residensial,tanah_komersial,tempat_usaha,kantor,pabrik,kost,hotel,gedung',
            'listing_type' => 'required|string|in:dijual,disewa,dijual_disewa',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'rent_price' => 'nullable|numeric|min:0',
            'monthly_rent_price' => 'nullable|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'price_prefix' => 'nullable|string|max:50',
            'rent_period' => 'nullable|string|max:50',
            'ipl_info' => 'nullable|string|max:100',
            'transfer_fee_info' => 'nullable|string|max:100',
            'unit_type' => 'nullable|string|max:100',
            'unit_tower' => 'nullable|string|max:100',
            'unit_floor' => 'nullable|string|max:50',
            'unit_number' => 'nullable|string|max:50',
            'unit_view' => 'nullable|string|max:100',
            'dimension' => 'nullable|string|max:50',
            'bedrooms' => 'nullable|integer|min:0',
            'maid_bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'maid_bathrooms' => 'nullable|integer|min:0',
            'land_area' => 'nullable|numeric|min:0',
            'building_area' => 'nullable|numeric|min:0',
            'carports' => 'nullable|integer|min:0',
            'floors' => 'nullable|integer|min:1',
            'certificate' => 'nullable|string|max:50',
            'electricity' => 'nullable|integer',
            'facing' => 'nullable|string|max:100',
            'road_width' => 'nullable|string|max:100',
            'furnished_status' => 'nullable|string|in:unfurnished,semi-furnished,fully-furnished',
            'flooring_material' => 'nullable|string|max:100',
            'year_built' => 'nullable|integer',
            'province' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'description' => 'nullable|string',
            'internal_notes' => 'nullable|string',
            'image_files' => 'nullable|array',
            'image_files.*' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:10240',
            'images_text' => 'nullable|string',
            'video_file' => 'nullable|file|mimes:mp4,mov,webm|max:51200',
            'video_url' => 'nullable|string|max:500',
            'facilities_text' => 'nullable|string',
            'is_featured' => 'nullable|boolean',
            'is_urgent' => 'nullable|boolean',
            'is_new_launch' => 'nullable|boolean',
            'is_verified' => 'nullable|boolean',
            'status' => 'required|string|in:active,sold,rented,pending',
        ]);

        // 1. Process custom city input or resolve existing city
        $customCity = trim($request->input('custom_city') ?? '');
        if ($customCity !== '') {
            $city = City::whereRaw('LOWER(name) = ?', [strtolower($customCity)])->first();
            if (!$city) {
                $province = Province::first() ?? Province::create(['name' => 'Jawa Timur', 'slug' => 'jawa-timur']);
                $city = City::create([
                    'name' => ucwords(strtolower($customCity)),
                    'slug' => Str::slug($customCity . '-' . uniqid()),
                    'province_id' => $province->id,
                ]);
            }
            $validated['city_id'] = $city->id;
            $validated['city'] = $city->name;
            $validated['province'] = $city->province ? $city->province->name : 'Jawa Timur';
            $validated['province_id'] = $city->province_id;
        } elseif (!empty($validated['city_id']) && is_numeric($validated['city_id'])) {
            $city = City::with('province')->find($validated['city_id']);
            if ($city) {
                $validated['city'] = $city->name;
                $validated['province'] = $city->province ? $city->province->name : ($validated['province'] ?? $property->province);
                $validated['province_id'] = $city->province_id;
            }
        }

        // 2. Process custom project/apartemen input or resolve existing project
        $customProject = trim($request->input('custom_project_name') ?? '');
        if ($customProject !== '') {
            $cityId = $validated['city_id'] ?? $property->city_id;
            $projectType = $validated['property_type'] === 'apartemen' ? 'apartemen' : 'residensial';
            $project = Project::whereRaw('LOWER(name) = ?', [strtolower($customProject)])
                ->when($cityId, fn($q) => $q->where('city_id', $cityId))
                ->first();

            if (!$project) {
                $project = Project::create([
                    'name' => ucwords(strtolower($customProject)),
                    'slug' => Str::slug($customProject . '-' . uniqid()),
                    'type' => $projectType,
                    'city_id' => $cityId,
                    'developer' => null,
                ]);
            }
            $validated['project_id'] = $project->id;
        } elseif (!empty($validated['project_id']) && !is_numeric($validated['project_id'])) {
            $projName = trim($validated['project_id']);
            if ($projName !== '' && $projName !== '__custom__') {
                $cityId = $validated['city_id'] ?? $property->city_id;
                $projectType = $validated['property_type'] === 'apartemen' ? 'apartemen' : 'residensial';
                $project = Project::firstOrCreate(
                    ['name' => $projName, 'city_id' => $cityId],
                    ['slug' => Str::slug($projName . '-' . uniqid()), 'type' => $projectType]
                );
                $validated['project_id'] = $project->id;
            } else {
                $validated['project_id'] = null;
            }
        }

        if (!empty($validated['district_id']) && is_numeric($validated['district_id'])) {
            $district = District::find($validated['district_id']);
            if ($district) {
                $validated['district'] = $district->name;
            }
        } else {
            $validated['district_id'] = null;
        }

        // Handle existing images and new uploads
        $allowedImages = is_array($property->images) ? $property->images : [];
        if ($request->has('keep_images')) {
            $kept = [];
            foreach ((array) $request->input('keep_images', []) as $imgItem) {
                if (in_array($imgItem, $allowedImages, true)) {
                    $kept[] = $imgItem;
                }
            }
            $images = $kept;
        } elseif ($request->boolean('has_photo_management')) {
            $images = [];
        } else {
            $images = $allowedImages;
        }

        // Upload new files
        if ($request->hasFile('image_files')) {
            $isWatermark = (bool) Setting::get('watermark_enabled', 1);
            foreach ($request->file('image_files') as $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('properties/images', 'public');
                    if ($isWatermark) {
                        \App\Services\WatermarkService::apply($path);
                    }
                    $images[] = $path;
                }
            }
        }

        // Add additional URLs if typed
        if (!empty($validated['images_text'])) {
            $urlImages = array_values(array_filter(array_map('trim', explode("\n", $validated['images_text']))));
            $images = array_unique(array_merge($images, $urlImages));
        }

        if (empty($images)) {
            $images = Property::getDefaultImagesForType($validated['property_type'] ?? $property->property_type);
        }
        // Handle video
        $videoPath = $property->video;
        if ($request->hasFile('video_file')) {
            $vFile = $request->file('video_file');
            if ($vFile && $vFile->isValid()) {
                $videoPath = $vFile->store('properties/videos', 'public');
            }
        } elseif ($request->filled('video_url')) {
            $videoPath = trim($request->video_url);
        }

        $facilities = !empty($validated['facilities_text'])
            ? array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $validated['facilities_text']))))
            : [];

        if ($user && $user->isAgent()) {
            $validated['agent_id'] = $user->agent_id;
        } else {
            $validated['agent_id'] = $validated['agent_id'] ?? $property->agent_id;
        }

        $salePrice = $validated['sale_price'] ?? null;
        $rentPrice = $validated['rent_price'] ?? null;

        if ($validated['property_type'] === 'apartemen') {
            if ($validated['listing_type'] === 'dijual') {
                $salePrice = $validated['price'];
                $rentPrice = null;
            } else {
                $rentPrice = $validated['price'];
                $salePrice = null;
            }
        } else {
            if ($validated['listing_type'] === 'dijual' && empty($salePrice)) {
                $salePrice = $validated['price'];
            }
            if ($validated['listing_type'] === 'disewa' && empty($rentPrice)) {
                $rentPrice = $validated['price'];
            }
        }

        $targetCode = !empty($validated['property_code']) ? trim($validated['property_code']) : $property->property_code;
        if (!empty($targetCode) && Property::where('property_code', $targetCode)->where('id', '!=', $property->id)->exists()) {
            $targetCode = Property::generateCode($validated['property_type'], $validated['listing_type'], $validated['code_source'] ?? 'A', $validated['agent_id']);
        }

        $property->update([
            'property_code' => $targetCode,
            'owner_type' => $validated['owner_type'] ?? ($property->owner_type ?? 'vendor'),
            'owner_office' => array_key_exists('owner_office', $validated) ? $validated['owner_office'] : $property->owner_office,
            'owner_name' => array_key_exists('owner_name', $validated) ? $validated['owner_name'] : $property->owner_name,
            'owner_phone' => array_key_exists('owner_phone', $validated) ? $validated['owner_phone'] : $property->owner_phone,
            'agent_id' => $validated['agent_id'],
            'project_id' => $validated['project_id'] ?? null,
            'province_id' => $validated['province_id'] ?? null,
            'city_id' => $validated['city_id'] ?? null,
            'district_id' => $validated['district_id'] ?? null,
            'unit_type' => $validated['unit_type'] ?? $property->unit_type,
            'unit_tower' => $validated['unit_tower'] ?? null,
            'unit_floor' => $validated['unit_floor'] ?? null,
            'unit_number' => $validated['unit_number'] ?? null,
            'unit_view' => $validated['unit_view'] ?? null,
            'dimension' => $validated['dimension'] ?? $property->dimension,
            'title' => $validated['title'],
            'property_type' => $validated['property_type'],
            'listing_type' => $validated['listing_type'],
            'price' => $validated['price'],
            'sale_price' => $salePrice,
            'rent_price' => $rentPrice,
            'monthly_rent_price' => $validated['monthly_rent_price'] ?? null,
            'original_price' => $validated['original_price'] ?? null,
            'price_prefix' => $validated['price_prefix'] ?? null,
            'rent_period' => $validated['rent_period'] ?? null,
            'ipl_info' => $validated['ipl_info'] ?? null,
            'transfer_fee_info' => $validated['transfer_fee_info'] ?? null,
            'bedrooms' => $validated['bedrooms'] ?? null,
            'maid_bedrooms' => $validated['maid_bedrooms'] ?? null,
            'bathrooms' => $validated['bathrooms'] ?? null,
            'maid_bathrooms' => $validated['maid_bathrooms'] ?? null,
            'land_area' => $validated['land_area'] ?? null,
            'building_area' => $validated['building_area'] ?? null,
            'carports' => $validated['carports'] ?? null,
            'floors' => $validated['floors'] ?? 1,
            'certificate' => $validated['certificate'] ?? null,
            'electricity' => $validated['electricity'] ?? null,
            'facing' => $validated['facing'] ?? null,
            'road_width' => $validated['road_width'] ?? null,
            'furnished_status' => $validated['furnished_status'] ?? 'unfurnished',
            'flooring_material' => $validated['flooring_material'] ?? null,
            'year_built' => $validated['year_built'] ?? null,
            'province' => $validated['province'] ?? $property->province,
            'city' => $validated['city'] ?? $property->city,
            'district' => $validated['district'] ?? ($property->district ?? null),
            'address' => $validated['address'] ?? null,
            'description' => $validated['description'] ?? null,
            'internal_notes' => $validated['internal_notes'] ?? null,
            'images' => $images,
            'video' => $videoPath,
            'facilities' => $facilities,
            'is_featured' => ($user && $user->isAgent()) ? $property->is_featured : $request->boolean('is_featured'),
            'is_urgent' => $request->boolean('is_urgent'),
            'is_new_launch' => $request->boolean('is_new_launch'),
            'is_verified' => $request->boolean('is_verified', true),
            'status' => $validated['status'],
        ]);

        return redirect()->route('admin.properties')->with('success', 'Listing properti berhasil diperbarui!');
    }

    /**
     * Delete property
     */
    public function destroyProperty(Property $property): RedirectResponse
    {
        $user = auth()->user();
        if ($user && $user->isAgent() && $property->agent_id !== $user->agent_id) {
            abort(403, 'Akses Ditolak: Anda hanya dapat menghapus listing milik Anda sendiri.');
        }

        $property->delete();
        return redirect()->route('admin.properties')->with('success', 'Listing properti berhasil dihapus.');
    }

    /**
     * Duplicate property for Jual / Sewa
     */
    public function duplicateProperty(Request $request, Property $property): RedirectResponse
    {
        $user = auth()->user();

        // Target listing type: 'dijual' or 'disewa'
        $targetType = $request->input('target_type');
        if (!in_array($targetType, ['dijual', 'disewa'])) {
            $targetType = $property->listing_type === 'dijual' ? 'disewa' : 'dijual';
        }

        // Adjust title smartly based on target
        $newTitle = $property->title;
        if ($targetType === 'disewa') {
            if (preg_match('/^dijual\s+/i', $newTitle)) {
                $newTitle = preg_replace('/^dijual\s+/i', 'Disewa ', $newTitle);
            } elseif (!preg_match('/disewa/i', $newTitle)) {
                $newTitle = "Disewa " . $newTitle;
            }
        } elseif ($targetType === 'dijual') {
            if (preg_match('/^disewa\s+/i', $newTitle)) {
                $newTitle = preg_replace('/^disewa\s+/i', 'Dijual ', $newTitle);
            } elseif (!preg_match('/dijual/i', $newTitle)) {
                $newTitle = "Dijual " . $newTitle;
            }
        } else {
            $newTitle = $newTitle . ' (Duplikat)';
        }

        // Generate unique slug
        $slug = Str::slug($newTitle);
        $originalSlug = $slug;
        $c = 1;
        while (Property::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-" . $c++;
        }

        // Determine price
        $newPrice = $property->price;
        if ($targetType === 'disewa' && $property->rent_price) {
            $newPrice = $property->rent_price;
        } elseif ($targetType === 'dijual' && $property->sale_price) {
            $newPrice = $property->sale_price;
        }

        // Generate new property code following Pak Yongky's system [V/A]-[Type]-[0000]-[J/S]
        $source = (str_starts_with(strtoupper($property->property_code ?? ''), 'V')) ? 'V' : 'A';
        $newCode = Property::generateCode($property->property_type, $targetType, $source);

        // Assign agent: if agent user, set to current agent; else keep original agent
        $agentId = ($user && $user->isAgent()) ? $user->agent_id : $property->agent_id;

        $duplicate = $property->replicate();
        $duplicate->title = $newTitle;
        $duplicate->slug = $slug;
        $duplicate->listing_type = $targetType;
        $duplicate->price = $newPrice;
        $duplicate->property_code = $newCode;
        $duplicate->agent_id = $agentId;
        $duplicate->status = 'active';
        $duplicate->save();

        $typeName = $targetType === 'dijual' ? 'Dijual' : 'Disewa';
        return redirect()->route('admin.properties.edit', $duplicate->id)
            ->with('success', "Listing berhasil diduplikat sebagai listing {$typeName} dengan Kode {$newCode}! Silakan periksa atau sesuaikan harga dan informasinya.");
    }

    /**
     * Master Data: Wilayah (Provinsi, Kota, Kecamatan)
     */
    public function masterLocations(): View
    {
        $provinces = Province::with(['cities.districts'])->orderBy('name')->get();
        $cities = City::with(['province', 'districts'])->orderBy('name')->get();

        return view('admin.master.locations', compact('provinces', 'cities'));
    }

    public function storeProvince(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:provinces,name',
        ]);
        Province::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);
        return back()->with('success', 'Provinsi baru berhasil ditambahkan!');
    }

    public function destroyProvince(Province $province): RedirectResponse
    {
        $province->delete();
        return back()->with('success', 'Provinsi berhasil dihapus.');
    }

    public function storeCity(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'province_id' => 'required|exists:provinces,id',
            'name' => 'required|string|max:100',
        ]);
        City::create([
            'province_id' => $validated['province_id'],
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name'] . '-' . $validated['province_id']),
        ]);
        return back()->with('success', 'Kota baru berhasil ditambahkan!');
    }

    public function destroyCity(City $city): RedirectResponse
    {
        $city->delete();
        return back()->with('success', 'Kota berhasil dihapus.');
    }

    public function storeDistrict(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'city_id' => 'required|exists:cities,id',
            'name' => 'required|string|max:100',
        ]);
        District::create([
            'city_id' => $validated['city_id'],
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name'] . '-' . $validated['city_id']),
        ]);
        return back()->with('success', 'Kecamatan baru berhasil ditambahkan!');
    }

    public function destroyDistrict(District $district): RedirectResponse
    {
        $district->delete();
        return back()->with('success', 'Kecamatan berhasil dihapus.');
    }

    /**
     * Master Data: Proyek / Kawasan / Apartemen / Gedung / Cluster
     */
    public function masterProjects(Request $request): View
    {
        $query = Project::with(['city.province', 'district'])
            ->withCount('properties');

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('developer', 'like', "%{$q}%")
                  ->orWhere('address', 'like', "%{$q}%")
                  ->orWhereHas('city', function ($cq) use ($q) {
                      $cq->where('name', 'like', "%{$q}%");
                  })
                  ->orWhereHas('district', function ($dq) use ($q) {
                      $dq->where('name', 'like', "%{$q}%");
                  });
            });
        }

        if ($request->filled('type')) {
            $type = $request->type;
            if ($type === 'rumah' || $type === 'cluster') {
                $query->whereIn('type', ['rumah', 'cluster']);
            } elseif ($type === 'apartemen' || $type === 'kondominium') {
                $query->whereIn('type', ['apartemen', 'kondominium']);
            } elseif ($type === 'ruko' || $type === 'ruko_complex') {
                $query->whereIn('type', ['ruko', 'ruko_complex']);
            } elseif ($type === 'villa' || $type === 'lainnya') {
                $query->whereIn('type', ['villa', 'lainnya']);
            } else {
                $query->where('type', $type);
            }
        }

        $projects = $query->orderBy('name')->paginate(20)->withQueryString();

        $cities = City::with('districts')->orderBy('name')->get();

        $types = [
            'rumah' => 'Rumah',
            'apartemen' => 'Apartemen',
            'tanah' => 'Tanah',
            'ruko' => 'Ruko',
            'gudang' => 'Gudang',
            'hotel' => 'Hotel',
            'gedung' => 'Commercial',
            'villa' => 'Lainnya',
        ];

        return view('admin.master.projects', compact('projects', 'cities', 'types'));
    }

    public function storeProject(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'city_id' => 'required|exists:cities,id',
            'district_id' => 'nullable|exists:districts,id',
            'name' => 'required|string|max:200',
            'type' => 'required|string|in:rumah,cluster,apartemen,kondominium,ruko,ruko_complex,gudang,hotel,gedung,tanah,villa,lainnya',
            'developer' => 'nullable|string|max:150',
            'address' => 'nullable|string',
            'total_floors' => 'nullable|integer|min:1',
            'year_completed' => 'nullable|integer',
            'cover_image' => 'nullable|url',
            'facilities_text' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $facilities = !empty($validated['facilities_text'])
            ? array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $validated['facilities_text']))))
            : [];

        $slug = Str::slug($validated['name']);
        $originalSlug = $slug;
        $c = 1;
        while (Project::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-" . $c++;
        }

        Project::create([
            'city_id' => $validated['city_id'],
            'district_id' => $validated['district_id'] ?? null,
            'name' => $validated['name'],
            'slug' => $slug,
            'type' => $validated['type'],
            'developer' => $validated['developer'] ?? null,
            'address' => $validated['address'] ?? null,
            'total_floors' => $validated['total_floors'] ?? null,
            'year_completed' => $validated['year_completed'] ?? null,
            'cover_image' => $validated['cover_image'] ?? 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=800&q=80',
            'facilities' => $facilities,
            'description' => $validated['description'] ?? null,
            'internal_notes' => $validated['internal_notes'] ?? null,
        ]);

        return back()->with('success', 'Proyek / kawasan properti baru berhasil didaftarkan ke master data!');
    }

    public function destroyProject(Project $project): RedirectResponse
    {
        $project->delete();
        return back()->with('success', 'Data apartemen/proyek berhasil dihapus.');
    }

    /**
     * Agent Applications
     */
    public function agentApplications(): View
    {
        $applications = AgentApplication::latest()->paginate(15);
        return view('admin.agent-applications', compact('applications'));
    }

    public function approveAgentApplication(AgentApplication $application): RedirectResponse
    {
        $slug = Str::slug($application->name);
        $originalSlug = $slug;
        $c = 1;
        while (Agent::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-" . $c++;
        }

        $agent = Agent::create([
            'name' => $application->name,
            'slug' => $slug,
            'photo' => $application->getRawOriginal('photo') ?? $application->photo,
            'email' => $application->email,
            'phone' => $application->phone,
            'whatsapp' => $application->phone,
            'agency_name' => Setting::getSiteName(),
            'office_branch' => $application->office_branch_preference ?? ($application->city . ' Office'),
            'position' => 'Property Consultant',
            'experience_years' => $application->experience_level === 'diatas_3_tahun' ? 4 : ($application->experience_level === '1-3_tahun' ? 2 : 1),
            'rating' => 5.0,
            'active_listings_count' => 0,
            'listing_quota' => (int) Setting::get('agent_free_quota', 3),
            'bio' => $application->motivation_bio ?? ('Agen resmi berlisensi ' . Setting::getSiteName() . '.'),
            'is_verified' => true,
            'is_top_producer' => false,
        ]);

        $plainPassword = 'PNX-' . strtoupper(Str::random(6));

        User::updateOrCreate(
            ['email' => $agent->email],
            [
                'name' => $agent->name,
                'password' => \Illuminate\Support\Facades\Hash::make($plainPassword),
                'role' => 'agent',
                'agent_id' => $agent->id,
                'must_change_password' => true,
            ]
        );

        $application->update([
            'status' => 'approved',
            'payment_status' => 'verified',
        ]);

        try {
            \Illuminate\Support\Facades\Mail::to($agent->email)->send(
                new \App\Mail\AgentApprovedCredentialsMail($agent, $plainPassword)
            );
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with(
            'success',
            "Pendaftaran {$application->name} disetujui! Akun agen telah aktif dan akses login (Email: {$agent->email} | Password Sementara: {$plainPassword}) telah dikirimkan ke email agen."
        );
    }

    public function rejectAgentApplication(AgentApplication $application): RedirectResponse
    {
        $application->update(['status' => 'rejected']);
        return back()->with('success', 'Pendaftaran agen telah ditolak.');
    }

    /**
     * Inquiries list
     */
    public function inquiries(): View
    {
        $user = auth()->user();
        $query = Inquiry::with(['property', 'agent']);
        if ($user && $user->isAgent()) {
            $query->where('agent_id', $user->agent_id);
        }
        $inquiries = $query->latest()->paginate(15);
        return view('admin.inquiries', compact('inquiries'));
    }

    public function updateInquiryStatus(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $status = $request->validate(['status' => 'required|string|in:new,contacted,scheduled,closed'])['status'];
        $inquiry->update(['status' => $status]);
        return back()->with('success', 'Status pesan/prospek berhasil diperbarui.');
    }

    /**
     * Owner property submissions
     */
    public function submissions(): View
    {
        $submissions = PropertySubmission::latest()->paginate(15);
        return view('admin.submissions', compact('submissions'));
    }

    public function updateSubmissionStatus(Request $request, PropertySubmission $submission): RedirectResponse
    {
        $status = $request->validate(['status' => 'required|string|in:pending,reviewed,listed,rejected'])['status'];
        $submission->update(['status' => $status]);

        if (in_array($status, ['reviewed', 'listed'], true) && !empty($submission->owner_email)) {
            try {
                \Illuminate\Support\Facades\Mail::to($submission->owner_email)->send(
                    new \App\Mail\PropertySubmissionApprovedMail($submission)
                );
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return back()->with('success', 'Status titip properti berhasil diperbarui.');
    }

    public function verifySubmissionPayment(Request $request, PropertySubmission $submission): RedirectResponse
    {
        $validated = $request->validate([
            'payment_status' => 'required|in:paid,pending,unpaid',
        ]);

        $submission->update([
            'payment_status' => $validated['payment_status'],
            'paid_at' => $validated['payment_status'] === 'paid' ? now() : null,
        ]);

        if ($validated['payment_status'] === 'paid' && !empty($submission->owner_email)) {
            try {
                \Illuminate\Support\Facades\Mail::to($submission->owner_email)->send(
                    new \App\Mail\PropertySubmissionApprovedMail($submission)
                );
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return back()->with('success', "Status pembayaran titip properti '{$submission->property_title}' berhasil diperbarui menjadi: " . $submission->payment_status_label);
    }

    /**
     * Agent & Leader Teams Management
     */
    public function agentsIndex(Request $request): View
    {
        $agents = Agent::with(['leader', 'teamMembers', 'user'])
            ->withCount('properties')
            ->orderBy('name')
            ->get();
        $leaders = Agent::where('is_leader', true)->orderBy('name')->get();

        return view('admin.agents.index', compact('agents', 'leaders'));
    }

    public function updateAgent(Request $request, Agent $agent): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'agent_code' => 'nullable|string|max:10',
            'phone' => 'required|string|max:30',
            'office_branch' => 'required|string|max:150',
            'position' => 'required|string|max:100',
            'listing_quota' => 'nullable|integer|min:0',
            'is_leader' => 'nullable|boolean',
            'leader_id' => 'nullable|exists:agents,id',
        ]);

        $isLeader = $request->boolean('is_leader');
        $leaderId = $request->input('leader_id');

        // Prevent setting self as leader
        if ($leaderId == $agent->id) {
            $leaderId = null;
        }

        $agent->update([
            'name' => $validated['name'],
            'agent_code' => !empty($validated['agent_code']) ? strtoupper(trim($validated['agent_code'])) : null,
            'phone' => $validated['phone'],
            'office_branch' => $validated['office_branch'],
            'position' => $validated['position'],
            'listing_quota' => $validated['listing_quota'] ?? $agent->listing_quota,
            'is_leader' => $isLeader,
            'leader_id' => $isLeader ? null : $leaderId,
        ]);

        if ($agent->user) {
            $agent->user->update(['name' => $agent->name]);
        }

        return back()->with('success', "Data agen {$agent->name} & struktur tim berhasil diperbarui!");
    }

    public function resetAgentPassword(Request $request, Agent $agent): RedirectResponse
    {
        $validated = $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = $agent->user;
        if (!$user) {
            $user = User::create([
                'name' => $agent->name,
                'email' => $agent->email,
                'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
                'role' => 'agent',
                'agent_id' => $agent->id,
                'must_change_password' => true,
            ]);
        } else {
            $user->update([
                'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
                'must_change_password' => true,
            ]);
        }

        return back()->with('success', "Password login untuk agen {$agent->name} ({$agent->email}) berhasil diperbarui!");
    }

    /**
     * User Profile & Password Change
     */
    public function profile(): View
    {
        $user = auth()->user();
        if ($user && $user->agent) {
            $user->load('agent.leader', 'agent.teamMembers');
        }
        return view('admin.profile', compact('user'));
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal harus 6 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $user = auth()->user();

        if (!\Illuminate\Support\Facades\Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini yang Anda masukkan salah.']);
        }

        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
            'must_change_password' => false,
        ]);

        return back()->with('success', 'Password akun Anda berhasil diperbarui! Gunakan password baru ini untuk login berikutnya.');
    }

    /**
     * Apartment Stock Matrix & WhatsApp Quick Share Engine
     */
    public function apartmentStock(Request $request): View
    {
        $user = auth()->user();
        $isAgent = $user && $user->isAgent();
        $agent = $isAgent ? $user->agent : ($user->agent ?? Agent::first());

        // Get all projects that are apartments or have apartment units
        $projects = Project::withCount(['properties as apartments_count' => function ($q) {
            $q->where('property_type', 'apartemen')
              ->orWhereNotNull('unit_tower')
              ->orWhereNotNull('unit_floor');
        }])->orderByDesc('apartments_count')->orderBy('name')->get();

        // Default to Puncak Dharmahusada if present, or first project with units, or all
        $selectedProjectId = $request->input('project_id');
        if ($selectedProjectId === null) {
            $defaultProject = $projects->firstWhere('slug', 'apartemen-puncak-dharmahusada') 
                           ?? $projects->firstWhere('apartments_count', '>', 0) 
                           ?? $projects->first();
            $selectedProjectId = $defaultProject ? (string) $defaultProject->id : 'all';
        }

        $query = Property::query();

        if ($selectedProjectId !== 'all') {
            $query->where('project_id', $selectedProjectId);
        } else {
            $query->where(function ($q) {
                $q->where('property_type', 'apartemen')
                  ->orWhereNotNull('unit_tower')
                  ->orWhereNotNull('unit_floor');
            });
        }

        // Available Towers for filter from the current scope
        $availableTowers = (clone $query)->whereNotNull('unit_tower')->where('unit_tower', '!=', '')
            ->distinct()->pluck('unit_tower')->filter()->values();

        // Filters
        if ($request->filled('tower')) {
            $query->where('unit_tower', $request->input('tower'));
        }

        if ($request->filled('floor')) {
            $query->where('unit_floor', 'like', '%' . $request->input('floor') . '%');
        }

        if ($request->filled('unit_type')) {
            $query->where('unit_type', $request->input('unit_type'));
        }

        if ($request->filled('listing_type')) {
            $query->where('listing_type', $request->input('listing_type'));
        }

        if ($request->filled('furnished_status')) {
            $query->where('furnished_status', $request->input('furnished_status'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Retrieve properties
        $properties = $query->with(['agent', 'project', 'cityRef'])->get();

        // Sorting (database-agnostic using Model accessor floor_number)
        $sortBy = $request->input('sort', 'floor_asc');
        if ($sortBy === 'floor_asc') {
            $properties = $properties->sortBy([
                ['unit_tower', 'asc'],
                ['floor_number', 'asc'],
                ['unit_number', 'asc'],
            ])->values();
        } elseif ($sortBy === 'floor_desc') {
            $properties = $properties->sortBy([
                ['unit_tower', 'asc'],
                ['floor_number', 'desc'],
                ['unit_number', 'asc'],
            ])->values();
        } elseif ($sortBy === 'rent_asc') {
            $properties = $properties->sortBy(fn($p) => $p->annual_rent_price ?? 999999999999)->values();
        } elseif ($sortBy === 'rent_desc') {
            $properties = $properties->sortByDesc(fn($p) => $p->annual_rent_price ?? 0)->values();
        } elseif ($sortBy === 'sale_asc') {
            $properties = $properties->sortBy(fn($p) => $p->direct_sale_price ?? 999999999999)->values();
        } else {
            $properties = $properties->sortByDesc('created_at')->values();
        }

        // Calculate summary stats
        $totalUnits = $properties->count();
        $forRentCount = $properties->whereIn('listing_type', ['disewa', 'dijual_disewa'])->count();
        $forSaleCount = $properties->whereIn('listing_type', ['dijual', 'dijual_disewa'])->count();
        $rukoCount = $properties->filter(fn($p) => str_contains(strtolower($p->unit_type ?? ''), 'ruko') || str_contains(strtolower($p->title), 'ruko'))->count();

        $selectedProject = $selectedProjectId !== 'all' ? $projects->firstWhere('id', (int) $selectedProjectId) : null;

        return view('admin.apartment-stock.index', compact(
            'properties',
            'projects',
            'selectedProjectId',
            'selectedProject',
            'availableTowers',
            'totalUnits',
            'forRentCount',
            'forSaleCount',
            'rukoCount',
            'agent',
            'isAgent'
        ));
    }

    /**
     * Quick Add Apartment Unit
     */
    public function storeQuickApartmentUnit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'unit_tower' => 'nullable|string|max:100',
            'unit_floor' => 'required|string|max:50',
            'unit_number' => 'nullable|string|max:50',
            'unit_type' => 'required|string|max:100',
            'unit_view' => 'nullable|string|max:100',
            'listing_type' => 'required|in:dijual,disewa',
            'rent_price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'price' => 'nullable|numeric|min:0',
            'building_area' => 'nullable|numeric|min:0',
            'furnished_status' => 'required|in:unfurnished,semi-furnished,fully-furnished',
            'status' => 'required|in:active,draft,sold,rented',
        ]);

        $project = Project::findOrFail($validated['project_id']);
        $tower = $validated['unit_tower'] ?: 'Tower A';
        $floorInput = trim($validated['unit_floor']);
        $floor = str_starts_with(strtolower($floorInput), 'lantai') ? $floorInput : 'Lantai ' . $floorInput;
        $unit = $validated['unit_number'] ? ' ' . $validated['unit_number'] : '';
        $type = str_replace(' ', '', $validated['unit_type']);

        $rentPrice = !empty($validated['rent_price']) ? (float)$validated['rent_price'] : null;
        $salePrice = !empty($validated['sale_price']) ? (float)$validated['sale_price'] : null;
        $mainPrice = $validated['listing_type'] === 'dijual' ? ($salePrice ?: $validated['price']) : ($rentPrice ?: $validated['price']);

        $transaksiLabel = $validated['listing_type'] === 'dijual' ? 'Dijual' : 'Disewakan';
        $title = "{$transaksiLabel} Apartemen {$project->name} {$type} {$tower} {$floor}{$unit}";
        $slug = Str::slug($title . '-' . Str::random(5));

        $user = auth()->user();
        $agentId = ($user && $user->agent_id) ? $user->agent_id : Agent::first()->id;
        $codeSource = ($user && $user->isAgent()) ? 'A' : 'A';
        $propertyCode = Property::generateCode('apartemen', $validated['listing_type'], $codeSource);

        Property::create([
            'property_code' => $propertyCode,
            'title' => $title,
            'slug' => $slug,
            'description' => "Unit apartemen {$type} di {$project->name}, {$tower} {$floor}{$unit}. Kondisi {$validated['furnished_status']}, view {$validated['unit_view']}. Siap huni dan lokasi strategis.",
            'property_type' => 'apartemen',
            'listing_type' => $validated['listing_type'],
            'price' => $mainPrice ?: 0,
            'sale_price' => $salePrice,
            'rent_price' => $rentPrice,
            'rent_period' => 'th',
            'project_id' => $project->id,
            'province_id' => $project->province_id,
            'city_id' => $project->city_id,
            'district_id' => $project->district_id,
            'city' => $project->city->name ?? 'Surabaya',
            'district' => $project->district->name ?? 'Mulyorejo',
            'address' => $project->address ?? $project->name,
            'bedrooms' => str_contains($type, '1') ? 1 : (str_contains($type, 'Studio') ? 1 : (str_contains($type, '3') ? 3 : 2)),
            'bathrooms' => 1,
            'building_area' => $validated['building_area'] ?: 36,
            'furnished_status' => $validated['furnished_status'],
            'unit_tower' => $tower,
            'unit_floor' => $floor,
            'unit_number' => $validated['unit_number'],
            'unit_type' => $type,
            'unit_view' => $validated['unit_view'] ?: 'City View',
            'status' => $validated['status'],
            'agent_id' => $agentId,
            'images' => [
                'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=1000&q=80',
                'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=1000&q=80'
            ],
            'facilities' => ['Kolam Renang', 'Gym Center', 'Access Card 24 Jam', 'Parkir Gedung', 'Minimarket'],
        ]);

        return redirect()->route('admin.apartment-stock', ['project_id' => $project->id])
            ->with('success', "Unit baru {$tower} {$floor} ({$validated['unit_number']}) berhasil ditambahkan ke stock sheet apartemen!");
    }

    /**
     * Quick Update Apartment Unit Status / Price
     */
    public function updateQuickApartmentUnit(Request $request, Property $property): RedirectResponse
    {
        $validated = $request->validate([
            'unit_tower' => 'nullable|string|max:100',
            'unit_floor' => 'nullable|string|max:50',
            'unit_number' => 'nullable|string|max:50',
            'rent_price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'furnished_status' => 'nullable|in:unfurnished,semi-furnished,fully-furnished',
            'unit_view' => 'nullable|string|max:100',
            'status' => 'required|in:active,draft,sold,rented',
        ]);

        $updates = ['status' => $validated['status']];
        if (isset($validated['unit_tower'])) $updates['unit_tower'] = $validated['unit_tower'];
        if (isset($validated['unit_floor'])) $updates['unit_floor'] = $validated['unit_floor'];
        if (isset($validated['unit_number'])) $updates['unit_number'] = $validated['unit_number'];
        if (isset($validated['furnished_status'])) $updates['furnished_status'] = $validated['furnished_status'];
        if (isset($validated['unit_view'])) $updates['unit_view'] = $validated['unit_view'];
        if (isset($validated['rent_price'])) {
            $updates['rent_price'] = $validated['rent_price'];
            if ($property->listing_type === 'disewa') {
                $updates['price'] = $validated['rent_price'];
            }
        }
        if (isset($validated['sale_price'])) {
            $updates['sale_price'] = $validated['sale_price'];
            if ($property->listing_type === 'dijual') {
                $updates['price'] = $validated['sale_price'];
            }
        }

        $property->update($updates);

        return back()->with('success', "Unit {$property->unit_tower} {$property->unit_floor} ({$property->unit_number}) berhasil diperbarui!");
    }

    /**
     * Global Settings Panel (Pricing, QRIS, Quotas)
     */
    public function settings(): View
    {
        $user = auth()->user();
        if ($user && $user->isAgent()) {
            abort(403, 'Akses Ditolak: Hanya Administrator yang dapat mengakses pengaturan sistem.');
        }

        $bca = Setting::getBcaAccount();
        $otherBank = Setting::getOtherBankAccount();

        $settings = [
            'site_name' => Setting::getSiteName(),
            'site_tagline' => Setting::getSiteTagline(),
            'site_hotline' => Setting::getSiteHotline(),
            'official_agent_name' => Setting::get('official_agent_name', ''),
            'official_agent_phone' => Setting::get('official_agent_phone', ''),
            'official_agent_office' => Setting::get('official_agent_office', ''),
            'site_logo_url' => Setting::getSiteLogo(),
            'watermark_enabled' => (bool) Setting::get('watermark_enabled', 1),
            'watermark_position' => Setting::getWatermarkPosition(),
            'submission_fee' => (float) Setting::get('submission_fee', 50000),
            'submission_fee_enabled' => (bool) Setting::get('submission_fee_enabled', 1),
            'agent_registration_fee' => (float) Setting::get('agent_registration_fee', 100000),
            'agent_registration_fee_enabled' => (bool) Setting::get('agent_registration_fee_enabled', 1),
            'agent_free_quota' => (int) Setting::get('agent_free_quota', 3),
            'agent_topup_price' => (float) Setting::get('agent_topup_price', 100000),
            'agent_topup_quota' => (int) Setting::get('agent_topup_quota', 5),
            'featured_listing_price' => (float) Setting::get('featured_listing_price', 50000),
            'featured_duration_days' => (int) Setting::get('featured_duration_days', 30),
            'qris_image_url' => Setting::getQrisUrl(),
            'qris_merchant_name' => Setting::get('qris_merchant_name', Setting::getSiteName() . ' Official QRIS'),
            'bca_bank_name' => $bca['bank_name'],
            'bca_account_number' => $bca['account_number'],
            'bca_account_name' => $bca['account_name'],
            'other_bank_name' => $otherBank ? $otherBank['bank_name'] : Setting::get('other_bank_name', 'Bank Mandiri'),
            'other_bank_account_number' => $otherBank ? $otherBank['account_number'] : Setting::get('other_bank_account_number', ''),
            'other_bank_account_name' => $otherBank ? $otherBank['account_name'] : Setting::get('other_bank_account_name', ''),
            'payment_instructions' => Setting::get('payment_instructions', ''),
            'mail_mailer' => Setting::get('mail_mailer', config('mail.default', 'smtp')),
            'mail_host' => Setting::get('mail_host', config('mail.mailers.smtp.host', '')),
            'mail_port' => Setting::get('mail_port', config('mail.mailers.smtp.port', 587)),
            'mail_username' => Setting::get('mail_username', config('mail.mailers.smtp.username', '')),
            'mail_password' => Setting::get('mail_password', config('mail.mailers.smtp.password', '')),
            'mail_encryption' => Setting::get('mail_encryption', 'tls'),
            'mail_from_address' => Setting::get('mail_from_address', config('mail.from.address', '')),
            'mail_from_name' => Setting::get('mail_from_name', config('mail.from.name', Setting::getSiteName())),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update Global Settings
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $user = auth()->user();
        if ($user && $user->isAgent()) {
            abort(403, 'Akses Ditolak.');
        }

        $validated = $request->validate([
            'site_name' => 'nullable|string|max:100',
            'site_tagline' => 'nullable|string|max:60',
            'site_hotline' => 'nullable|string|max:50',
            'official_agent_name' => 'nullable|string|max:100',
            'official_agent_phone' => 'nullable|string|max:50',
            'official_agent_office' => 'nullable|string|max:100',
            'watermark_enabled' => 'nullable|boolean',
            'watermark_position' => 'nullable|string|in:bottom-right,bottom-left,center',
            'site_logo_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:3072',
            'remove_logo' => 'nullable|boolean',
            'submission_fee' => 'required|numeric|min:0',
            'submission_fee_enabled' => 'nullable|boolean',
            'agent_registration_fee' => 'nullable|numeric|min:0',
            'agent_registration_fee_enabled' => 'nullable|boolean',
            'agent_free_quota' => 'required|integer|min:0',
            'agent_topup_price' => 'required|numeric|min:0',
            'agent_topup_quota' => 'required|integer|min:1',
            'featured_listing_price' => 'required|numeric|min:0',
            'featured_duration_days' => 'required|integer|min:1',
            'qris_merchant_name' => 'nullable|string|max:150',
            'bca_bank_name' => 'nullable|string|max:100',
            'bca_account_number' => 'nullable|string|max:50',
            'bca_account_name' => 'nullable|string|max:100',
            'other_bank_name' => 'nullable|string|max:100',
            'other_bank_account_number' => 'nullable|string|max:50',
            'other_bank_account_name' => 'nullable|string|max:100',
            'payment_instructions' => 'nullable|string|max:1500',
            'qris_image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'mail_mailer' => 'nullable|string|in:smtp,log,sendmail',
            'mail_host' => 'nullable|string|max:150',
            'mail_port' => 'nullable|integer|min:1|max:65535',
            'mail_username' => 'nullable|string|max:150',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|string|in:tls,ssl,null',
            'mail_from_address' => 'nullable|email|max:150',
            'mail_from_name' => 'nullable|string|max:150',
        ]);

        Setting::set('submission_fee', $validated['submission_fee'], 'pricing');
        Setting::set('submission_fee_enabled', $request->boolean('submission_fee_enabled') ? '1' : '0', 'pricing');
        Setting::set('agent_registration_fee', $validated['agent_registration_fee'] ?? 0, 'pricing');
        Setting::set('agent_registration_fee_enabled', $request->boolean('agent_registration_fee_enabled') ? '1' : '0', 'pricing');
        Setting::set('agent_free_quota', $validated['agent_free_quota'], 'pricing');
        Setting::set('agent_topup_price', $validated['agent_topup_price'], 'pricing');
        Setting::set('agent_topup_quota', $validated['agent_topup_quota'], 'pricing');
        Setting::set('featured_listing_price', $validated['featured_listing_price'], 'pricing');
        Setting::set('featured_duration_days', $validated['featured_duration_days'], 'pricing');

        if (!empty($validated['qris_merchant_name'])) Setting::set('qris_merchant_name', $validated['qris_merchant_name'], 'payment');

        // BCA Setting
        $bcaBankName = !empty($validated['bca_bank_name']) ? $validated['bca_bank_name'] : 'BCA (Bank Central Asia)';
        Setting::set('bca_bank_name', $bcaBankName, 'payment');
        Setting::set('bank_name', $bcaBankName, 'payment'); // sync legacy
        if (!empty($validated['bca_account_number'])) {
            Setting::set('bca_account_number', $validated['bca_account_number'], 'payment');
            Setting::set('bank_account_number', $validated['bca_account_number'], 'payment'); // sync legacy
        }
        if (!empty($validated['bca_account_name'])) {
            Setting::set('bca_account_name', $validated['bca_account_name'], 'payment');
            Setting::set('bank_account_name', $validated['bca_account_name'], 'payment'); // sync legacy
        }

        // Other Bank Setting
        if (isset($validated['other_bank_name'])) Setting::set('other_bank_name', $validated['other_bank_name'], 'payment');
        if (isset($validated['other_bank_account_number'])) Setting::set('other_bank_account_number', $validated['other_bank_account_number'], 'payment');
        if (isset($validated['other_bank_account_name'])) Setting::set('other_bank_account_name', $validated['other_bank_account_name'], 'payment');

        if (isset($validated['payment_instructions'])) Setting::set('payment_instructions', $validated['payment_instructions'], 'payment');

        if ($request->hasFile('qris_image_file') && $request->file('qris_image_file')->isValid()) {
            $path = $request->file('qris_image_file')->store('settings/qris', 'public');
            Setting::set('qris_image', $path, 'payment');
        }

        // Website Identity (Logo & Nama Agensi)
        if (!empty($validated['site_name'])) {
            Setting::set('site_name', trim($validated['site_name']), 'identity');
        }
        if (isset($validated['site_tagline'])) {
            Setting::set('site_tagline', trim($validated['site_tagline']), 'identity');
        }
        if (isset($validated['site_hotline'])) {
            Setting::set('site_hotline', trim($validated['site_hotline']), 'identity');
        }
        if (isset($validated['official_agent_name'])) {
            Setting::set('official_agent_name', trim((string) $validated['official_agent_name']), 'identity');
        }
        if (isset($validated['official_agent_phone'])) {
            Setting::set('official_agent_phone', trim((string) $validated['official_agent_phone']), 'identity');
        }
        if (isset($validated['official_agent_office'])) {
            Setting::set('official_agent_office', trim((string) $validated['official_agent_office']), 'identity');
        }
        Setting::set('watermark_enabled', $request->boolean('watermark_enabled') ? '1' : '0', 'identity');
        if (!empty($validated['watermark_position'])) {
            $pos = $validated['watermark_position'];
            if ($pos === 'bottom-right') {
                $pos = 'center';
            }
            Setting::set('watermark_position', $pos, 'identity');
        }
        if ($request->boolean('remove_logo')) {
            Setting::set('site_logo', null, 'identity');
        } elseif ($request->hasFile('site_logo_file') && $request->file('site_logo_file')->isValid()) {
            $logoPath = $request->file('site_logo_file')->store('settings/logo', 'public');
            Setting::set('site_logo', $logoPath, 'identity');
        }

        // Email / SMTP Settings
        if (isset($validated['mail_mailer'])) {
            Setting::set('mail_mailer', $validated['mail_mailer'], 'mail');
        }
        if (isset($validated['mail_host'])) {
            Setting::set('mail_host', trim((string) $validated['mail_host']), 'mail');
        }
        if (isset($validated['mail_port'])) {
            Setting::set('mail_port', $validated['mail_port'], 'mail');
        }
        if (isset($validated['mail_username'])) {
            Setting::set('mail_username', trim((string) $validated['mail_username']), 'mail');
        }
        if (!empty($validated['mail_password'])) {
            Setting::set('mail_password', $validated['mail_password'], 'mail');
        }
        if (isset($validated['mail_encryption'])) {
            Setting::set('mail_encryption', $validated['mail_encryption'], 'mail');
        }
        if (isset($validated['mail_from_address'])) {
            Setting::set('mail_from_address', trim((string) $validated['mail_from_address']), 'mail');
        }
        if (isset($validated['mail_from_name'])) {
            Setting::set('mail_from_name', trim((string) $validated['mail_from_name']), 'mail');
        }

        return back()->with('success', 'Pengaturan identitas website, biaya listing, rekening bank, QRIS, dan SMTP Email berhasil disimpan!');
    }

    /**
     * Agent Quota Topup Page
     */
    public function agentTopup(Request $request): View
    {
        $user = auth()->user();
        if (!$user || !$user->isAgent() || !$user->agent) {
            abort(403, 'Aksi ini hanya dapat diakses oleh akun agen berlisensi.');
        }

        $agent = $user->agent;
        $topupPrice = (float) Setting::get('agent_topup_price', 100000);
        $topupQuota = (int) Setting::get('agent_topup_quota', 5);
        $qrisImage = Setting::getQrisUrl();
        $merchantName = Setting::get('qris_merchant_name', Setting::getSiteName() . ' Official QRIS');
        $bcaAccount = Setting::getBcaAccount();
        $otherBankAccount = Setting::getOtherBankAccount();
        $paymentInstructions = Setting::get('payment_instructions', '');
        $transactions = AgentTransaction::where('agent_id', $agent->id)
            ->where('type', 'quota_topup')
            ->latest()
            ->paginate(10);

        return view('admin.agent.topup', compact(
            'agent',
            'topupPrice',
            'topupQuota',
            'qrisImage',
            'merchantName',
            'bcaAccount',
            'otherBankAccount',
            'paymentInstructions',
            'transactions'
        ));
    }

    /**
     * Agent Quota Topup Request
     */
    public function requestQuotaTopup(Request $request): RedirectResponse
    {
        $user = auth()->user();
        if (!$user || !$user->isAgent() || !$user->agent) {
            abort(403, 'Aksi ini hanya dapat dilakukan oleh akun agen.');
        }

        $validated = $request->validate([
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'notes' => 'nullable|string|max:500',
        ]);

        $proofPath = $request->file('payment_proof')->store('agents/proofs', 'public');
        $topupPrice = (float) Setting::get('agent_topup_price', 100000);
        $topupQuota = (int) Setting::get('agent_topup_quota', 5);

        AgentTransaction::create([
            'agent_id' => $user->agent_id,
            'user_id' => $user->id,
            'type' => 'quota_topup',
            'amount' => $topupPrice,
            'quota_added' => $topupQuota,
            'payment_proof' => $proofPath,
            'status' => 'pending',
            'notes' => $validated['notes'] ?? 'Permohonan topup kuota listing +' . $topupQuota,
        ]);

        return back()->with('success', "Permintaan topup kuota listing (+{$topupQuota} listing) berhasil dikirim! Menunggu konfirmasi verifikasi admin.");
    }

    /**
     * Agent Featured Listing Request
     */
    public function requestFeaturedListing(Request $request, Property $property): RedirectResponse
    {
        $user = auth()->user();
        if (!$user || !$user->isAgent() || !$user->agent) {
            abort(403, 'Aksi ini hanya dapat dilakukan oleh akun agen.');
        }

        if ($property->agent_id !== $user->agent_id) {
            abort(403, 'Anda hanya dapat mengajukan featured untuk listing milik Anda sendiri.');
        }

        $validated = $request->validate([
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $proofPath = $request->file('payment_proof')->store('properties/featured_proofs', 'public');
        $featuredPrice = (float) Setting::get('featured_listing_price', 50000);
        $featuredDuration = (int) Setting::get('featured_duration_days', 30);

        AgentTransaction::create([
            'agent_id' => $user->agent_id,
            'user_id' => $user->id,
            'property_id' => $property->id,
            'type' => 'featured_listing',
            'amount' => $featuredPrice,
            'duration_days' => $featuredDuration,
            'payment_proof' => $proofPath,
            'status' => 'pending',
            'notes' => "Pengajuan properti unggulan (featured): {$property->title}",
        ]);

        return back()->with('success', "Permohonan Upgrade Properti Unggulan (Featured) untuk '{$property->title}' berhasil dikirim! Menunggu verifikasi admin.");
    }

    /**
     * Agent Transactions Management (Admin)
     */
    public function agentTransactions(Request $request): View
    {
        $user = auth()->user();
        if ($user && $user->isAgent()) {
            abort(403, 'Akses Ditolak.');
        }

        $query = AgentTransaction::with(['agent', 'property', 'user']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $transactions = $query->latest()->paginate(15)->withQueryString();
        $pendingCount = AgentTransaction::where('status', 'pending')->count();
        $approvedCount = AgentTransaction::where('status', 'approved')->count();

        return view('admin.agent-transactions.index', compact('transactions', 'pendingCount', 'approvedCount'));
    }

    /**
     * Approve Agent Transaction
     */
    public function approveAgentTransaction(Request $request, AgentTransaction $transaction): RedirectResponse
    {
        $user = auth()->user();
        if ($user && $user->isAgent()) {
            abort(403);
        }

        if ($transaction->status !== 'pending') {
            return back()->with('error', 'Transaksi ini sudah diproses sebelumnya.');
        }

        if ($transaction->type === 'quota_topup') {
            $transaction->agent->increment('listing_quota', $transaction->quota_added);
        } elseif ($transaction->type === 'featured_listing' && $transaction->property) {
            $transaction->property->update(['is_featured' => true]);
        }

        $transaction->update([
            'status' => 'approved',
            'approved_at' => now(),
            'admin_notes' => $request->input('admin_notes', 'Disetujui oleh Administrator.'),
        ]);

        return back()->with('success', "Transaksi {$transaction->type_label} untuk agen {$transaction->agent->name} berhasil disetujui!");
    }

    /**
     * Reject Agent Transaction
     */
    public function rejectAgentTransaction(Request $request, AgentTransaction $transaction): RedirectResponse
    {
        $user = auth()->user();
        if ($user && $user->isAgent()) {
            abort(403);
        }

        $transaction->update([
            'status' => 'rejected',
            'admin_notes' => $request->input('admin_notes', 'Ditolak oleh Administrator.'),
        ]);

        return back()->with('success', "Transaksi telah ditolak.");
    }
}
