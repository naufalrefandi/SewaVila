<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Property;
use App\Models\PropertyRental;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class RentalController extends Controller
{
    /**
     * Display a listing of tenants and rental leases
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $isAgent = $user && $user->isAgent();
        $isLeader = $user && $user->isLeader();

        $query = PropertyRental::with(['property.cityRef', 'agent', 'user']);

        // Agent scoping
        if ($isAgent) {
            $scope = $request->get('scope', 'mine');
            if ($scope === 'all') {
                // View all
            } elseif ($scope === 'team' && $isLeader) {
                $query->whereIn('agent_id', $user->agent->allTeamAgentIds());
            } else {
                $query->where('agent_id', $user->agent_id);
            }
        } elseif ($request->filled('agent_id')) {
            $query->where('agent_id', $request->agent_id);
        }

        // Filter by property
        if ($request->filled('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        // Filter by status tab
        $tab = $request->get('tab', 'all');
        $today = Carbon::today();

        if ($tab === 'active') {
            $query->where('status', 'active');
        } elseif ($tab === 'expiring_soon') {
            $query->where('status', 'active')
                ->whereDate('end_date', '>=', $today)
                ->whereDate('end_date', '<=', $today->copy()->addDays(30));
        } elseif ($tab === 'overdue') {
            $query->where('status', 'active')
                ->whereDate('end_date', '<', $today);
        } elseif ($tab === 'completed') {
            $query->where('status', 'completed');
        } elseif ($tab === 'deposit_held') {
            $query->where('deposit_status', 'held')
                ->where('deposit_amount', '>', 0);
        }

        // Filter by deposit status specifically
        if ($request->filled('deposit_status')) {
            $query->where('deposit_status', $request->deposit_status);
        }

        // Search
        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('tenant_name', 'like', "%{$q}%")
                    ->orWhere('tenant_phone', 'like', "%{$q}%")
                    ->orWhere('tenant_id_card', 'like', "%{$q}%")
                    ->orWhere('agreement_number', 'like', "%{$q}%")
                    ->orWhereHas('property', function ($propQuery) use ($q) {
                        $propQuery->where('title', 'like', "%{$q}%")
                            ->orWhere('property_code', 'like', "%{$q}%")
                            ->orWhere('address', 'like', "%{$q}%");
                    });
            });
        }

        // Clone base query for statistics
        $statsBase = PropertyRental::query();
        if ($isAgent) {
            $statsBase->where('agent_id', $user->agent_id);
        }

        $stats = [
            'total_active' => (clone $statsBase)->where('status', 'active')->count(),
            'expiring_soon' => (clone $statsBase)->where('status', 'active')
                ->whereDate('end_date', '>=', $today)
                ->whereDate('end_date', '<=', $today->copy()->addDays(30))
                ->count(),
            'overdue' => (clone $statsBase)->where('status', 'active')
                ->whereDate('end_date', '<', $today)
                ->count(),
            'total_deposit_held' => (clone $statsBase)->where('status', 'active')
                ->where('deposit_status', 'held')
                ->sum('deposit_amount'),
            'completed' => (clone $statsBase)->where('status', 'completed')->count(),
            'all_count' => (clone $statsBase)->count(),
        ];

        $rentals = $query->orderByRaw("
            CASE 
                WHEN status = 'active' AND end_date < '{$today->toDateString()}' THEN 1
                WHEN status = 'active' AND end_date <= '{$today->copy()->addDays(30)->toDateString()}' THEN 2
                WHEN status = 'active' THEN 3
                ELSE 4
            END ASC
        ")->latest('end_date')->paginate(12)->withQueryString();

        $agents = Agent::orderBy('name')->get();

        return view('admin.rentals.index', compact(
            'rentals',
            'stats',
            'tab',
            'isAgent',
            'isLeader',
            'agents'
        ));
    }

    /**
     * Show form to record a new tenant/lease
     */
    public function create(Request $request): View
    {
        $user = auth()->user();
        $isAgent = $user && $user->isAgent();

        $propertiesQuery = Property::where('listing_type', 'disewa')->orderBy('title');

        if ($isAgent) {
            $propertiesQuery->where('agent_id', $user->agent_id);
        }

        $selectedPropertyId = $request->get('property_id');
        $selectedProperty = $selectedPropertyId ? Property::find($selectedPropertyId) : null;

        $properties = $propertiesQuery->get();

        // If a specific property was passed in query parameter and it's not already in the collection, prepend it
        if ($selectedProperty && !$properties->contains('id', $selectedProperty->id)) {
            $properties->prepend($selectedProperty);
        }

        $agents = Agent::orderBy('name')->get();

        return view('admin.rentals.create', compact(
            'properties',
            'selectedProperty',
            'selectedPropertyId',
            'isAgent',
            'agents'
        ));
    }

    /**
     * Store new tenant and lease record
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'agent_id' => 'nullable|exists:agents,id',
            'tenant_name' => 'required|string|max:255',
            'tenant_phone' => 'required|string|max:50',
            'tenant_email' => 'nullable|email|max:255',
            'tenant_id_card' => 'nullable|string|max:50',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:50',
            'tenant_address' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'period_type' => 'required|string|in:tahun,bulan,hari,custom',
            'duration' => 'required|integer|min:1',
            'rent_price' => 'required|numeric|min:0',
            'deposit_amount' => 'required|numeric|min:0',
            'deposit_status' => 'required|string|in:held,refunded,partially_refunded,deducted',
            'payment_status' => 'required|string|in:paid,partial,unpaid',
            'agreement_number' => 'nullable|string|max:100',
            'agreement_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'id_card_photo' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'notes' => 'nullable|string',
            'mark_property_rented' => 'nullable|boolean',
        ]);

        $user = auth()->user();
        $isAgent = $user && $user->isAgent();

        $property = Property::findOrFail($validated['property_id']);

        $data = $validated;
        $data['user_id'] = $user?->id;

        // Assign agent
        if ($isAgent) {
            $data['agent_id'] = $user->agent_id;
        } elseif (empty($data['agent_id'])) {
            $data['agent_id'] = $property->agent_id;
        }

        $data['status'] = 'active';

        // File uploads
        if ($request->hasFile('agreement_document')) {
            $data['agreement_document'] = $request->file('agreement_document')->store('rental-documents', 'public');
        }

        if ($request->hasFile('id_card_photo')) {
            $data['id_card_photo'] = $request->file('id_card_photo')->store('rental-id-cards', 'public');
        }

        $rental = PropertyRental::create($data);

        // Optionally mark property as rented
        if ($request->boolean('mark_property_rented')) {
            $property->update(['status' => 'rented']);
        }

        return redirect()->route('admin.rentals.index')
            ->with('success', "Data penyewa {$rental->tenant_name} berhasil dicatat dengan masa sewa s/d " . Carbon::parse($rental->end_date)->translatedFormat('d F Y') . "!");
    }

    /**
     * Show detail of a rental lease and tenant
     */
    public function show(PropertyRental $rental): View
    {
        $user = auth()->user();
        if ($user && $user->isAgent() && $rental->agent_id !== $user->agent_id && !$user->isLeader()) {
            abort(403, 'Anda tidak memiliki hak akses ke data penyewa ini.');
        }

        $rental->load(['property.cityRef', 'agent', 'user']);

        return view('admin.rentals.show', compact('rental'));
    }

    /**
     * Show form to edit tenant / lease
     */
    public function edit(PropertyRental $rental): View
    {
        $user = auth()->user();
        $isAgent = $user && $user->isAgent();

        if ($isAgent && $rental->agent_id !== $user->agent_id && !$user->isLeader()) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengedit data ini.');
        }

        $propertiesQuery = Property::where(function ($q) use ($rental) {
            $q->where('listing_type', 'disewa')
              ->orWhere('id', $rental->property_id);
        })->orderBy('title');
        if ($isAgent) {
            $propertiesQuery->where('agent_id', $user->agent_id);
        }
        $properties = $propertiesQuery->get();

        $agents = Agent::orderBy('name')->get();

        return view('admin.rentals.edit', compact('rental', 'properties', 'agents', 'isAgent'));
    }

    /**
     * Update tenant / lease data
     */
    public function update(Request $request, PropertyRental $rental): RedirectResponse
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'agent_id' => 'nullable|exists:agents,id',
            'tenant_name' => 'required|string|max:255',
            'tenant_phone' => 'required|string|max:50',
            'tenant_email' => 'nullable|email|max:255',
            'tenant_id_card' => 'nullable|string|max:50',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:50',
            'tenant_address' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'period_type' => 'required|string|in:tahun,bulan,hari,custom',
            'duration' => 'required|integer|min:1',
            'rent_price' => 'required|numeric|min:0',
            'deposit_amount' => 'required|numeric|min:0',
            'deposit_status' => 'required|string|in:held,refunded,partially_refunded,deducted',
            'deposit_refunded_amount' => 'nullable|numeric|min:0',
            'deposit_notes' => 'nullable|string',
            'payment_status' => 'required|string|in:paid,partial,unpaid',
            'agreement_number' => 'nullable|string|max:100',
            'agreement_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'id_card_photo' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'notes' => 'nullable|string',
            'status' => 'required|string|in:active,completed,terminated',
            'actual_end_date' => 'nullable|date',
            'set_property_active' => 'nullable|boolean',
        ]);

        $data = $validated;

        $targetProperty = Property::find($validated['property_id']);
        if (empty($data['agent_id'])) {
            $data['agent_id'] = $targetProperty?->agent_id ?? $rental->agent_id;
        }

        // Document replacements
        if ($request->hasFile('agreement_document')) {
            if ($rental->agreement_document && Storage::disk('public')->exists($rental->agreement_document)) {
                Storage::disk('public')->delete($rental->agreement_document);
            }
            $data['agreement_document'] = $request->file('agreement_document')->store('rental-documents', 'public');
        }

        if ($request->hasFile('id_card_photo')) {
            if ($rental->id_card_photo && Storage::disk('public')->exists($rental->id_card_photo)) {
                Storage::disk('public')->delete($rental->id_card_photo);
            }
            $data['id_card_photo'] = $request->file('id_card_photo')->store('rental-id-cards', 'public');
        }

        // If status marked completed and date not set
        if ($data['status'] === 'completed' && empty($data['actual_end_date'])) {
            $data['actual_end_date'] = Carbon::today()->toDateString();
        }

        $rental->update($data);

        // Optionally revert property to active if rental is completed
        if ($request->boolean('set_property_active') && $rental->property) {
            $rental->property->update(['status' => 'active']);
        }

        return redirect()->route('admin.rentals.index')
            ->with('success', "Data penyewa {$rental->tenant_name} berhasil diperbarui!");
    }

    /**
     * Mark a lease completed and handle deposit return/deduction
     */
    public function complete(Request $request, PropertyRental $rental): RedirectResponse
    {
        $validated = $request->validate([
            'actual_end_date' => 'required|date',
            'deposit_status' => 'required|in:refunded,partially_refunded,deducted',
            'deposit_refunded_amount' => 'nullable|numeric|min:0',
            'deposit_notes' => 'nullable|string',
            'set_property_active' => 'nullable|boolean',
        ]);

        $refundedAmount = $validated['deposit_status'] === 'refunded'
            ? $rental->deposit_amount
            : ($validated['deposit_refunded_amount'] ?? 0);

        $rental->update([
            'status' => 'completed',
            'actual_end_date' => $validated['actual_end_date'],
            'deposit_status' => $validated['deposit_status'],
            'deposit_refunded_amount' => $refundedAmount,
            'deposit_refunded_at' => Carbon::today()->toDateString(),
            'deposit_notes' => $validated['deposit_notes'] ?? null,
        ]);

        if ($request->boolean('set_property_active') && $rental->property) {
            $rental->property->update(['status' => 'active']);
        }

        return redirect()->route('admin.rentals.index')
            ->with('success', "Masa sewa untuk {$rental->tenant_name} telah selesai dicatat. Deposit: {$rental->deposit_status_label}.");
    }

    /**
     * Extend lease end date
     */
    public function extend(Request $request, PropertyRental $rental): RedirectResponse
    {
        $validated = $request->validate([
            'new_end_date' => 'required|date|after_or_equal:today',
            'additional_duration' => 'nullable|integer|min:1',
            'extension_notes' => 'nullable|string',
        ]);

        $oldEndDate = Carbon::parse($rental->end_date)->translatedFormat('d M Y');
        $newEndDate = Carbon::parse($validated['new_end_date'])->translatedFormat('d M Y');

        $notes = $rental->notes;
        $log = "\n[Perpanjangan Sewa pada " . Carbon::now()->format('d/m/Y H:i') . "]: Masa sewa diperpanjang dari {$oldEndDate} ke {$newEndDate}. " . ($validated['extension_notes'] ?? '');
        $notes .= $log;

        $updateData = [
            'end_date' => $validated['new_end_date'],
            'status' => 'active',
            'notes' => trim($notes),
        ];

        if (!empty($validated['additional_duration'])) {
            $updateData['duration'] = $rental->duration + (int) $validated['additional_duration'];
        }

        $rental->update($updateData);

        return redirect()->route('admin.rentals.index')
            ->with('success', "Masa sewa {$rental->tenant_name} berhasil diperpanjang sampai {$newEndDate}!");
    }

    /**
     * Delete tenant / rental record
     */
    public function destroy(PropertyRental $rental): RedirectResponse
    {
        $user = auth()->user();
        if ($user && $user->isAgent() && $rental->agent_id !== $user->agent_id && !$user->isLeader()) {
            abort(403, 'Anda tidak memiliki hak akses untuk menghapus data ini.');
        }

        $name = $rental->tenant_name;

        // Delete uploaded files if exist
        if ($rental->agreement_document && Storage::disk('public')->exists($rental->agreement_document)) {
            Storage::disk('public')->delete($rental->agreement_document);
        }
        if ($rental->id_card_photo && Storage::disk('public')->exists($rental->id_card_photo)) {
            Storage::disk('public')->delete($rental->id_card_photo);
        }

        $rental->delete();

        return redirect()->route('admin.rentals.index')
            ->with('success', "Data penyewa {$name} berhasil dihapus.");
    }
}

