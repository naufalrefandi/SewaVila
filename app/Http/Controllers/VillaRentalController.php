<?php

namespace App\Http\Controllers;

use App\Mail\VillaBookingCreatedMail;
use App\Mail\VillaBookingPaidMail;
use App\Models\Property;
use App\Models\Setting;
use App\Models\VillaBooking;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class VillaRentalController extends Controller
{
    /**
     * Villa Rental Catalog & Search Page
     */
    public function index(Request $request): View
    {
        $filters = $request->only([
            'q',
            'city',
            'check_in',
            'check_out',
            'guests',
            'price_min',
            'price_max',
            'bedrooms',
            'has_pool',
            'sort',
        ]);

        $query = Property::with('agent')
            ->active()
            ->villa();

        // Keyword Search (title, district, city, description)
        if (!empty($filters['q'])) {
            $keyword = trim((string) $filters['q']);
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('city', 'like', "%{$keyword}%")
                    ->orWhere('district', 'like', "%{$keyword}%")
                    ->orWhere('address', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        // City / Destination filter
        if (!empty($filters['city'])) {
            $query->where('city', $filters['city']);
        }

        // Min & Max Price
        if (!empty($filters['price_min'])) {
            $query->where('price', '>=', (int) $filters['price_min']);
        }
        if (!empty($filters['price_max'])) {
            $query->where('price', '<=', (int) $filters['price_max']);
        }

        // Bedrooms filter
        if (!empty($filters['bedrooms'])) {
            $query->where('bedrooms', '>=', (int) $filters['bedrooms']);
        }

        // Guests Capacity filter
        if (!empty($filters['guests'])) {
            $minGuests = (int) $filters['guests'];
            $query->where(function ($q) use ($minGuests) {
                $q->whereRaw('(COALESCE(bedrooms, 2) * 2) >= ?', [$minGuests]);
            });
        }

        // Private Pool facility filter
        if (!empty($filters['has_pool'])) {
            $query->where(function ($q) {
                $q->where('facilities', 'like', '%pool%')
                    ->orWhere('facilities', 'like', '%kolam renang%')
                    ->orWhere('title', 'like', '%pool%')
                    ->orWhere('description', 'like', '%kolam renang%');
            });
        }

        // Date Availability Filter (Check-in & Check-out)
        if (!empty($filters['check_in']) && !empty($filters['check_out'])) {
            try {
                $checkIn = Carbon::parse($filters['check_in'])->startOfDay()->format('Y-m-d');
                $checkOut = Carbon::parse($filters['check_out'])->startOfDay()->format('Y-m-d');

                // Exclude properties that have overlapping active bookings
                $bookedPropertyIds = VillaBooking::where('booking_status', '!=', 'cancelled')
                    ->where('check_in_date', '<', $checkOut)
                    ->where('check_out_date', '>', $checkIn)
                    ->pluck('property_id')
                    ->toArray();

                if (!empty($bookedPropertyIds)) {
                    $query->whereNotIn('id', $bookedPropertyIds);
                }
            } catch (\Throwable $e) {
                // If invalid date format, ignore filter
            }
        }

        // Sorting
        $sort = $filters['sort'] ?? 'recommended';
        match ($sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'bedrooms_desc' => $query->orderBy('bedrooms', 'desc'),
            'latest' => $query->latest(),
            default => $query->orderBy('is_featured', 'desc')->latest(),
        };

        $villas = $query->paginate(9)->withQueryString();

        // Popular destinations stats
        $destinations = [
            [
                'name' => 'Bali (Badung & Gianyar)',
                'query_city' => 'Badung',
                'villas_count' => Property::villa()->active()->whereIn('city', ['Badung', 'Gianyar', 'Denpasar'])->count(),
                'image' => 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=600&q=80',
                'desc' => 'Seminyak, Canggu, Ubud, Uluwatu',
            ],
            [
                'name' => 'Puncak & Bogor',
                'query_city' => 'Bogor',
                'villas_count' => Property::villa()->active()->where('city', 'Bogor')->count(),
                'image' => 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=600&q=80',
                'desc' => 'Cisarua, Megamendung, View Gunung',
            ],
            [
                'name' => 'Bandung & Lembang',
                'query_city' => 'Bandung Barat',
                'villas_count' => Property::villa()->active()->whereIn('city', ['Bandung', 'Bandung Barat'])->count(),
                'image' => 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=600&q=80',
                'desc' => 'Lembang, Dago Atas, Nuansa Alam Sejuk',
            ],
            [
                'name' => 'Batu & Malang',
                'query_city' => 'Batu',
                'villas_count' => Property::villa()->active()->whereIn('city', ['Batu', 'Malang'])->count(),
                'image' => 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=600&q=80',
                'desc' => 'Kota Wisata Batu, Private Pool & Karaoke',
            ],
            [
                'name' => 'Yogyakarta',
                'query_city' => 'Sleman',
                'villas_count' => Property::villa()->active()->whereIn('city', ['Sleman', 'Yogyakarta', 'Bantul'])->count(),
                'image' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=600&q=80',
                'desc' => 'Kaliurang, Joglo Modern, Riverside',
            ],
        ];

        $cities = Property::villa()->active()->distinct()->pluck('city')->filter()->values();

        return view('pages.villas.index', compact('villas', 'filters', 'destinations', 'cities'));
    }

    /**
     * Villa Detail Page with Booking Form
     */
    public function show(string $slug, Request $request): View
    {
        $property = Property::with(['agent', 'project'])
            ->where('slug', $slug)
            ->firstOrFail();

        // Increment views
        $property->increment('views_count');

        // Initial dates
        $checkIn = $request->query('check_in', Carbon::tomorrow()->format('Y-m-d'));
        $checkOut = $request->query('check_out', Carbon::tomorrow()->addDays(2)->format('Y-m-d'));
        $guests = (int) $request->query('guests', 2);

        $cleaningFee = (int) Setting::get('villa_cleaning_fee', 150000);
        $securityDeposit = (int) Setting::get('villa_security_deposit', 0);
        $pricePerNight = (int) ($property->rent_price ?: $property->price);

        // Upcoming active bookings to show calendar unavailability
        $bookedDates = VillaBooking::where('property_id', $property->id)
            ->where('booking_status', '!=', 'cancelled')
            ->where('check_out_date', '>=', Carbon::today())
            ->get(['check_in_date', 'check_out_date']);

        $similarVillas = Property::villa()
            ->active()
            ->where('id', '!=', $property->id)
            ->where('city', $property->city)
            ->take(3)
            ->get();

        if ($similarVillas->isEmpty()) {
            $similarVillas = Property::villa()
                ->active()
                ->where('id', '!=', $property->id)
                ->latest()
                ->take(3)
                ->get();
        }

        return view('pages.villas.show', compact(
            'property',
            'checkIn',
            'checkOut',
            'guests',
            'cleaningFee',
            'securityDeposit',
            'pricePerNight',
            'bookedDates',
            'similarVillas'
        ));
    }

    /**
     * Submit Villa Reservation & Create Pending QRIS Booking
     */
    public function book(Request $request, Property $property): RedirectResponse
    {
        $request->validate([
            'customer_name' => 'required|string|max:150',
            'customer_email' => 'required|email|max:150',
            'customer_phone' => 'required|string|max:30',
            'customer_identity_number' => 'nullable|string|max:50',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'guests_count' => 'required|integer|min:1|max:50',
            'special_requests' => 'nullable|string|max:1000',
        ], [
            'customer_name.required' => 'Nama lengkap penyewa wajib diisi.',
            'customer_email.required' => 'Email aktif wajib diisi untuk pengiriman voucher dan tagihan.',
            'customer_email.email' => 'Format email tidak valid.',
            'customer_phone.required' => 'Nomor WhatsApp / HP wajib diisi.',
            'check_in_date.required' => 'Tanggal check-in wajib dipilih.',
            'check_in_date.after_or_equal' => 'Tanggal check-in minimal hari ini.',
            'check_out_date.required' => 'Tanggal check-out wajib dipilih.',
            'check_out_date.after' => 'Tanggal check-out harus setelah tanggal check-in.',
        ]);

        $checkIn = Carbon::parse($request->input('check_in_date'))->startOfDay();
        $checkOut = Carbon::parse($request->input('check_out_date'))->startOfDay();

        // 1. Check Date Availability
        if (!VillaBooking::isDatesAvailable($property->id, $checkIn, $checkOut)) {
            return back()->withInput()->withErrors([
                'check_in_date' => 'Maaf, vila ini sudah dipesan pada rentang tanggal tersebut. Silakan pilih tanggal lain.',
            ]);
        }

        // 2. Calculate Nights & Pricing
        $totalNights = max(1, (int) $checkIn->diffInDays($checkOut));
        $pricePerNight = (int) ($property->rent_price ?: $property->price);
        $totalPrice = $pricePerNight * $totalNights;
        $cleaningFee = (int) Setting::get('villa_cleaning_fee', 150000);
        $securityDeposit = (int) Setting::get('villa_security_deposit', 0);
        $grandTotal = $totalPrice + $cleaningFee + $securityDeposit;

        // 3. Create Booking
        $booking = VillaBooking::create([
            'booking_code' => VillaBooking::generateBookingCode(),
            'property_id' => $property->id,
            'agent_id' => $property->agent_id,
            'user_id' => auth()->id(),
            'customer_name' => $request->input('customer_name'),
            'customer_email' => $request->input('customer_email'),
            'customer_phone' => $request->input('customer_phone'),
            'customer_identity_number' => $request->input('customer_identity_number'),
            'check_in_date' => $checkIn->format('Y-m-d'),
            'check_out_date' => $checkOut->format('Y-m-d'),
            'total_nights' => $totalNights,
            'guests_count' => (int) $request->input('guests_count', 1),
            'special_requests' => $request->input('special_requests'),
            'price_per_night' => $pricePerNight,
            'total_price' => $totalPrice,
            'cleaning_fee' => $cleaningFee,
            'security_deposit' => $securityDeposit,
            'grand_total' => $grandTotal,
            'payment_method' => 'qris',
            'payment_status' => 'pending',
            'booking_status' => 'pending',
            'payment_expired_at' => now()->addHours(24),
        ]);

        // 4. Send Email Notification
        if (!empty($booking->customer_email)) {
            try {
                Mail::to($booking->customer_email)->send(new VillaBookingCreatedMail($booking));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return redirect()
            ->route('villas.payment', $booking->booking_code)
            ->with('success', "Pemesanan berhasil dibuat dengan Kode Booking {$booking->booking_code}. Silakan selesaikan pembayaran via QRIS di bawah ini.");
    }

    /**
     * Show QRIS Payment Invoice Page
     */
    public function showPayment(string $code): View
    {
        $booking = VillaBooking::with(['property.agent', 'agent'])
            ->where('booking_code', $code)
            ->firstOrFail();

        $merchantName = Setting::get('qris_merchant_name', Setting::getSiteName() . ' Official QRIS');
        $qrisImage = $booking->getQrisCodeUrl();
        $bcaAccount = Setting::getBcaAccount();
        $otherBankAccount = Setting::getOtherBankAccount();
        $bankAccounts = Setting::getAllBankAccounts();
        $instructions = Setting::get('payment_instructions', '');

        return view('pages.villas.payment', compact(
            'booking',
            'merchantName',
            'qrisImage',
            'bcaAccount',
            'otherBankAccount',
            'bankAccounts',
            'instructions'
        ));
    }

    /**
     * Upload Payment Proof for Villa Rental
     */
    public function submitPaymentProof(Request $request, string $code): RedirectResponse
    {
        $booking = VillaBooking::where('booking_code', $code)->firstOrFail();

        $request->validate([
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ], [
            'payment_proof.required' => 'Harap unggah screenshot bukti transfer / struk pembayaran QRIS.',
            'payment_proof.image' => 'File bukti pembayaran harus berupa gambar (JPG, PNG, WEBP).',
            'payment_proof.max' => 'Ukuran file bukti pembayaran maksimal 5MB.',
        ]);

        if ($request->hasFile('payment_proof') && $request->file('payment_proof')->isValid()) {
            $proofPath = $request->file('payment_proof')->store('villas/proofs', 'public');
            $booking->update([
                'payment_proof' => $proofPath,
                'payment_status' => 'pending',
            ]);
        }

        return redirect()
            ->route('villas.payment', $booking->booking_code)
            ->with('success', "Bukti pembayaran QRIS berhasil diunggah! Tim kami sedang memverifikasi pembayaran Anda.");
    }

    /**
     * Instant QRIS Simulation for Testing / Demo Purposes
     */
    public function simulateQrisPayment(Request $request, string $code): RedirectResponse
    {
        $booking = VillaBooking::where('booking_code', $code)->firstOrFail();

        $booking->update([
            'payment_status' => 'paid',
            'booking_status' => 'confirmed',
            'paid_at' => now(),
            'confirmed_at' => now(),
        ]);

        if (!empty($booking->customer_email)) {
            try {
                Mail::to($booking->customer_email)->send(new VillaBookingPaidMail($booking));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return redirect()
            ->route('villas.payment', $booking->booking_code)
            ->with('success', "Pembayaran QRIS Berhasil Terverifikasi! Status pemesanan Anda kini Terkonfirmasi (Lunas).");
    }

    /**
     * Public Booking Status Check by Code or Phone/Email
     */
    public function checkBooking(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $booking = null;

        if (!empty($search)) {
            $booking = VillaBooking::with('property')
                ->where('booking_code', $search)
                ->orWhere('customer_phone', $search)
                ->orWhere('customer_email', $search)
                ->latest()
                ->first();
        }

        return view('pages.villas.check-booking', compact('search', 'booking'));
    }

    /**
     * Printable Check-in Voucher
     */
    public function printVoucher(string $code): View
    {
        $booking = VillaBooking::with(['property.agent', 'agent'])
            ->where('booking_code', $code)
            ->firstOrFail();

        return view('pages.villas.voucher', compact('booking'));
    }
}

