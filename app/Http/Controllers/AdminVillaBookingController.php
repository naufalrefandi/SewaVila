<?php

namespace App\Http\Controllers;

use App\Mail\VillaBookingPaidMail;
use App\Models\VillaBooking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AdminVillaBookingController extends Controller
{
    /**
     * Display list of villa bookings
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $isAgent = $user && $user->isAgent();

        $query = VillaBooking::with(['property.agent', 'agent'])->latest();

        // If logged-in user is an agent, scope to properties managed by this agent
        if ($isAgent && $user->agent_id) {
            $query->where(function ($q) use ($user) {
                $q->where('agent_id', $user->agent_id)
                    ->orWhereHas('property', fn ($pq) => $pq->where('agent_id', $user->agent_id));
            });
        }

        // Search
        if ($request->filled('q')) {
            $keyword = trim((string) $request->input('q'));
            $query->where(function ($q) use ($keyword) {
                $q->where('booking_code', 'like', "%{$keyword}%")
                    ->orWhere('customer_name', 'like', "%{$keyword}%")
                    ->orWhere('customer_email', 'like', "%{$keyword}%")
                    ->orWhere('customer_phone', 'like', "%{$keyword}%")
                    ->orWhereHas('property', fn ($pq) => $pq->where('title', 'like', "%{$keyword}%"));
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $status = $request->input('status');
            if (in_array($status, ['pending_payment', 'pending'])) {
                $query->where('payment_status', 'pending');
            } elseif ($status === 'paid') {
                $query->where('payment_status', 'paid');
            } else {
                $query->where('booking_status', $status);
            }
        }

        $baseCountQuery = clone $query;

        $stats = [
            'total' => (clone $baseCountQuery)->count(),
            'pending_payment' => (clone $baseCountQuery)->where('payment_status', 'pending')->count(),
            'confirmed' => (clone $baseCountQuery)->where('booking_status', 'confirmed')->count(),
            'revenue' => (clone $baseCountQuery)->where('payment_status', 'paid')->sum('grand_total'),
        ];

        $bookings = $query->paginate(15)->withQueryString();

        return view('admin.villa-bookings.index', compact('bookings', 'stats', 'isAgent'));
    }

    /**
     * Show booking detail
     */
    public function show(VillaBooking $booking): View
    {
        $user = auth()->user();
        if ($user && $user->isAgent() && $user->agent_id) {
            if ($booking->agent_id !== $user->agent_id && $booking->property?->agent_id !== $user->agent_id) {
                abort(403, 'Anda tidak memiliki akses ke pesanan vila ini.');
            }
        }

        $booking->load(['property.agent', 'agent', 'user']);

        return view('admin.villa-bookings.show', compact('booking'));
    }

    /**
     * Confirm QRIS payment for booking
     */
    public function confirmPayment(Request $request, VillaBooking $booking): RedirectResponse
    {
        $booking->update([
            'payment_status' => 'paid',
            'booking_status' => 'confirmed',
            'paid_at' => now(),
            'confirmed_at' => now(),
            'admin_notes' => $request->input('admin_notes', $booking->admin_notes),
        ]);

        if (!empty($booking->customer_email)) {
            try {
                Mail::to($booking->customer_email)->send(new VillaBookingPaidMail($booking));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return back()->with('success', "Pembayaran untuk Kode {$booking->booking_code} berhasil dikonfirmasi LUNAS!");
    }

    /**
     * Update booking status
     */
    public function updateStatus(Request $request, VillaBooking $booking): RedirectResponse
    {
        $request->validate([
            'booking_status' => 'required|in:pending,confirmed,checked_in,completed,cancelled',
            'payment_status' => 'nullable|in:pending,paid,failed,refunded',
            'admin_notes' => 'nullable|string|max:1000',
            'cancellation_reason' => 'nullable|string|max:1000',
        ]);

        $updates = [
            'booking_status' => $request->input('booking_status'),
            'admin_notes' => $request->input('admin_notes', $booking->admin_notes),
        ];

        if ($request->filled('payment_status')) {
            $updates['payment_status'] = $request->input('payment_status');
            if ($updates['payment_status'] === 'paid' && !$booking->paid_at) {
                $updates['paid_at'] = now();
            }
        }

        if ($updates['booking_status'] === 'confirmed' && !$booking->confirmed_at) {
            $updates['confirmed_at'] = now();
        }

        if ($updates['booking_status'] === 'cancelled') {
            $updates['cancelled_at'] = now();
            $updates['cancellation_reason'] = $request->input('cancellation_reason');
        }

        $booking->update($updates);

        return back()->with('success', "Status reservasi {$booking->booking_code} berhasil diperbarui.");
    }

    /**
     * Delete booking
     */
    public function destroy(VillaBooking $booking): RedirectResponse
    {
        $code = $booking->booking_code;
        $booking->delete();

        return redirect()->route('admin.villa-bookings.index')
            ->with('success', "Data reservasi {$code} berhasil dihapus.");
    }
}

