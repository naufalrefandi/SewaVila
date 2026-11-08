<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PropertyController extends Controller
{
    /**
     * Homepage
     */
    public function index(): View
    {
        $latestProperties = Property::with('agent')
            ->active()
            ->latest()
            ->take(6)
            ->get();

        $featuredProperties = Property::with('agent')
            ->active()
            ->featured()
            ->latest()
            ->take(6)
            ->get();

        $newLaunches = Property::with('agent')
            ->active()
            ->newLaunch()
            ->latest()
            ->take(4)
            ->get();

        $latestSale = Property::with('agent')
            ->active()
            ->forSale()
            ->latest()
            ->take(4)
            ->get();

        $latestRent = Property::with('agent')
            ->active()
            ->forRent()
            ->latest()
            ->take(4)
            ->get();

        $topAgents = Agent::where('is_top_producer', true)
            ->orderBy('rating', 'desc')
            ->take(4)
            ->get();

        $popularCities = [
            ['name' => 'Jakarta Selatan', 'count' => Property::where('city', 'Jakarta Selatan')->count(), 'img' => 'https://images.unsplash.com/photo-1555899433-387038f08960?auto=format&fit=crop&w=600&q=80'],
            ['name' => 'Tangerang Selatan', 'count' => Property::where('city', 'Tangerang Selatan')->count(), 'img' => 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=600&q=80'],
            ['name' => 'Surabaya', 'count' => Property::where('city', 'Surabaya')->count(), 'img' => 'https://images.unsplash.com/photo-1578632767115-351597cf2477?auto=format&fit=crop&w=600&q=80'],
            ['name' => 'Badung (Bali)', 'count' => Property::where('city', 'Badung')->count(), 'img' => 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=600&q=80'],
        ];

        return view('pages.home', compact(
            'latestProperties',
            'featuredProperties',
            'newLaunches',
            'latestSale',
            'latestRent',
            'topAgents',
            'popularCities'
        ));
    }

    /**
     * Property search / catalog list
     */
    public function search(Request $request): View
    {
        $filters = $request->only([
            'q',
            'listing_type',
            'property_type',
            'city',
            'price_min',
            'price_max',
            'bedrooms',
            'bathrooms',
            'certificate',
            'furnished',
            'is_new_launch',
            'sort',
        ]);

        $query = Property::with('agent')->active()->filter($filters);

        $properties = $query->paginate(9)->withQueryString();

        $cities = Property::select('city')->distinct()->pluck('city');
        $propertyTypes = [
            'rumah' => 'Rumah',
            'apartemen' => 'Apartemen',
            'tanah' => 'Tanah',
            'gudang' => 'Gudang',
            'ruko' => 'Ruko',
            'hotel' => 'Hotel',
            'gedung' => 'Gedung',
            'villa' => 'Villa',
        ];

        return view('pages.properties.index', compact(
            'properties',
            'filters',
            'cities',
            'propertyTypes'
        ));
    }

    /**
     * Short URL Redirect (/p/{code}) -> Canonical Property Slug
     */
    public function shortRedirect(string $code, Request $request): RedirectResponse
    {
        $property = Property::where('property_code', $code)
            ->orWhere('slug', $code)
            ->when(is_numeric($code), fn ($q) => $q->orWhere('id', (int) $code))
            ->firstOrFail();

        return redirect()->route('properties.show', array_merge(['slug' => $property->slug], $request->query()));
    }

    /**
     * Property Detail Page
     */
    public function show(string $slug, Request $request): View|RedirectResponse
    {
        $property = Property::with(['agent', 'inquiries', 'project'])
            ->where('slug', $slug)
            ->first();

        if (!$property) {
            $propertyByCode = Property::where('property_code', $slug)
                ->when(is_numeric($slug), fn ($q) => $q->orWhere('id', (int) $slug))
                ->firstOrFail();

            return redirect()->route('properties.show', array_merge(['slug' => $propertyByCode->slug], $request->query()));
        }

        // Increment view count safely
        $property->increment('views_count');

        $activeAgent = null;
        if ($request->filled('agent') || $request->filled('ref')) {
            $agentParam = $request->get('agent', $request->get('ref'));
            $activeAgent = Agent::where('slug', $agentParam)
                ->orWhere('initials', $agentParam)
                ->first();
        }
        if (!$activeAgent) {
            $activeAgent = $property->agent;
        }

        $projectUnits = collect();
        if ($property->project_id) {
            $projectUnits = Property::with('agent')
                ->where('project_id', $property->project_id)
                ->where('id', '!=', $property->id)
                ->active()
                ->get()
                ->sortBy([
                    ['unit_tower', 'asc'],
                    ['floor_number', 'asc'],
                ])
                ->take(8)
                ->values();
        }

        $similarProperties = Property::with('agent')
            ->active()
            ->where('id', '!=', $property->id)
            ->where(function ($query) use ($property) {
                $query->where('city', $property->city)
                      ->orWhere('property_type', $property->property_type);
            })
            ->take(3)
            ->get();

        return view('pages.properties.show', compact('property', 'similarProperties', 'activeAgent', 'projectUnits'));
    }

    /**
     * Proyek Baru / New Launches
     */
    public function newLaunch(): View
    {
        $properties = Property::with('agent')
            ->active()
            ->newLaunch()
            ->latest()
            ->paginate(9);

        return view('pages.properties.new-launch', compact('properties'));
    }

    /**
     * Dedicated Mortgage Calculator (Simulasi KPR)
     */
    public function kpr(Request $request): View
    {
        $defaultPrice = (int) ($request->query('price', 1500000000));
        return view('pages.kpr-calculator', compact('defaultPrice'));
    }

    /**
     * Property Comparison
     */
    public function compare(Request $request): View
    {
        $ids = array_filter(explode(',', (string) $request->query('ids', '')));
        $properties = Property::with('agent')->whereIn('id', $ids)->get();
        $allProperties = Property::active()->select('id', 'title', 'price', 'property_type', 'city')->get();

        return view('pages.properties.compare', compact('properties', 'allProperties', 'ids'));
    }

    /**
     * Favorites / Wishlist
     */
    public function favorites(Request $request): View
    {
        $ids = array_filter(explode(',', (string) $request->query('ids', '')));
        $properties = Property::with('agent')->whereIn('id', $ids)->get();

        return view('pages.properties.favorites', compact('properties', 'ids'));
    }
}
