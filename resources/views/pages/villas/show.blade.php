@extends('layouts.app')

@php
    $siteName = \App\Models\Setting::getSiteName();
    $cover = $property->primary_image;
    $images = $property->formatted_images;
    $imgUrls = [];
    foreach ($images as $im) {
        $imgUrls[] = is_array($im) ? ($im['url'] ?? '') : \App\Models\Property::getImageUrl((string)$im);
    }
    $imgUrls = array_values(array_filter($imgUrls));
    if (empty($imgUrls)) {
        $imgUrls = \App\Models\Property::getDefaultImagesForType('villa');
    }

    $rate = (int) ($property->rent_price ?: $property->price);
    $agent = $property->agent;
    $agentName = $agent ? $agent->name : \App\Models\Setting::getOfficialAgentName();
    $agentPhone = $agent ? $agent->phone : \App\Models\Setting::getOfficialAgentPhone();
    $agentCleanPhone = $agent ? $agent->clean_phone : \App\Models\Setting::getCleanOfficialAgentPhone();
    $agentPhoto = $agent ? $agent->photo : 'https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=200&q=80';
    $agentOffice = $agent ? $agent->office_branch : \App\Models\Setting::getOfficialAgentOffice();

    $waBookText = urlencode("Halo {$agentName}, saya tertarik untuk sewa *{$property->title}* ({$property->formatted_price}). Apakah tersedia untuk tanggal tertentu?");
    $waDirectUrl = "https://wa.me/{$agentCleanPhone}?text=" . $waBookText;
@endphp

@section('title', $property->title . ' - Sewa Villa ' . $property->city . ' | ' . $siteName)
@section('meta_description', 'Sewa ' . $property->title . ' di ' . $property->city . '. Fasilitas lengkap, private pool, booking cepat dan pembayaran aman via QRIS.')
@section('og_image', $cover)

@section('content')
<!-- Breadcrumbs & Header -->
<div class="bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <nav class="flex items-center gap-2 text-xs text-slate-500 font-semibold flex-wrap">
            <a href="{{ route('home') }}" class="hover:text-pink-600 transition-colors">Home</a>
            <i class="fa-solid fa-chevron-right text-[9px] text-slate-300"></i>
            <a href="{{ route('villas.index') }}" class="hover:text-pink-600 transition-colors">Sewa Vila</a>
            <i class="fa-solid fa-chevron-right text-[9px] text-slate-300"></i>
            <a href="{{ route('villas.index', ['city' => $property->city]) }}" class="hover:text-pink-600 transition-colors">{{ $property->city }}</a>
            <i class="fa-solid fa-chevron-right text-[9px] text-slate-300"></i>
            <span class="text-slate-800 truncate max-w-xs">{{ $property->title }}</span>
        </nav>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
    <!-- Title & Location Header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-2 mb-2 flex-wrap">
                <span class="px-2.5 py-1 rounded-full bg-pink-100 text-pink-700 font-black text-xs uppercase tracking-wider">
                    <i class="fa-solid fa-umbrella-beach mr-1"></i> Vila Mewah
                </span>
                <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold text-xs">
                    <i class="fa-solid fa-circle-check mr-1 text-emerald-600"></i> Terverifikasi
                </span>
                <span class="px-2.5 py-1 rounded-full bg-purple-100 text-purple-800 font-bold text-xs">
                    <i class="fa-solid fa-qrcode mr-1 text-purple-600"></i> Bayar QRIS Instan
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl md:text-4xl font-black text-slate-900 tracking-tight">
                {{ $property->title }}
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 flex items-center gap-1.5">
                <i class="fa-solid fa-location-dot text-pink-600"></i>
                <span>{{ $property->address }}, {{ $property->district }}, {{ $property->city }}, {{ $property->province }}</span>
            </p>
        </div>

        <!-- Quick Share & Fav Actions -->
        <div class="flex items-center gap-2">
            <a href="{{ $waDirectUrl }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition-colors">
                <i class="fa-brands fa-whatsapp text-sm"></i>
                <span>Tanya Host</span>
            </a>
            <a href="{{ route('villas.check') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs transition-colors">
                <i class="fa-solid fa-ticket text-pink-600"></i>
                <span>Cek Booking</span>
            </a>
        </div>
    </div>

    <!-- Photo Gallery Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-2 sm:gap-3 rounded-3xl overflow-hidden mb-8 shadow-sm">
        <div class="md:col-span-2 aspect-16/10 md:aspect-auto md:h-[420px] overflow-hidden bg-slate-100 relative group cursor-pointer">
            <img src="{{ $imgUrls[0] ?? $cover }}" alt="{{ $property->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
        </div>
        <div class="hidden md:grid md:col-span-2 grid-cols-2 gap-2 sm:gap-3 h-[420px]">
            @for($i = 1; $i <= 4; $i++)
                @php $imgSrc = $imgUrls[$i] ?? ($imgUrls[0] ?? $cover); @endphp
                <div class="aspect-4/3 overflow-hidden bg-slate-100 relative group cursor-pointer">
                    <img src="{{ $imgSrc }}" alt="{{ $property->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                </div>
            @endfor
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <!-- Left Column: Details & Facilities (7 cols) -->
        <div class="lg:col-span-7 space-y-8">
            <!-- Key Specs Card -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xs">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                    <div class="p-3 bg-pink-50/50 rounded-2xl border border-pink-100">
                        <i class="fa-solid fa-bed text-pink-600 text-lg mb-1"></i>
                        <div class="text-sm font-black text-slate-900">{{ $property->bedrooms ?: 1 }} Kamar</div>
                        <div class="text-[10px] text-slate-500">Tempat Tidur</div>
                    </div>
                    <div class="p-3 bg-pink-50/50 rounded-2xl border border-pink-100">
                        <i class="fa-solid fa-bath text-pink-600 text-lg mb-1"></i>
                        <div class="text-sm font-black text-slate-900">{{ $property->bathrooms ?: 1 }} Kamar</div>
                        <div class="text-[10px] text-slate-500">Kamar Mandi</div>
                    </div>
                    <div class="p-3 bg-pink-50/50 rounded-2xl border border-pink-100">
                        <i class="fa-solid fa-users text-pink-600 text-lg mb-1"></i>
                        <div class="text-sm font-black text-slate-900">s/d {{ $property->max_guests }} Orang</div>
                        <div class="text-[10px] text-slate-500">Kapasitas Maksimal</div>
                    </div>
                    <div class="p-3 bg-pink-50/50 rounded-2xl border border-pink-100">
                        <i class="fa-solid fa-car text-pink-600 text-lg mb-1"></i>
                        <div class="text-sm font-black text-slate-900">{{ $property->carports ?: 2 }} Mobil</div>
                        <div class="text-[10px] text-slate-500">Area Parkir</div>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-4">
                <h2 class="text-lg sm:text-xl font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-circle-info text-pink-600"></i> Tentang Vila Ini
                </h2>
                <div class="text-sm text-slate-700 leading-relaxed space-y-3 prose max-w-none">
                    {!! nl2br(e($property->description)) !!}
                </div>
            </div>

            <!-- Facilities -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-4">
                <h2 class="text-lg sm:text-xl font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-pink-600"></i> Fasilitas Vila
                </h2>

                @php
                    $defaultVilaFacilities = [
                        'Kolam Renang Privat',
                        'WiFi Internet Kecepatan Tinggi',
                        'Dapur Lengkap & Peralatan Masak',
                        'AC di Setiap Kamar Tidur',
                        'Water Heater (Air Hangat)',
                        'Smart TV & Akses Netflix',
                        'Area BBQ & Grill Luar Ruang',
                        'Pemandangan Alam / Gunung / Sungai',
                        'Area Parkir Luas & Aman',
                        'Dispenser & Air Mineral Gratis',
                        'Handuk & Perlengkapan Mandi',
                        'Penjaga Vila 24 Jam',
                    ];
                    $facilities = is_array($property->facilities) && count($property->facilities) > 0
                        ? $property->facilities
                        : $defaultVilaFacilities;
                @endphp

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                    @foreach($facilities as $f)
                        <div class="flex items-center gap-3 p-3 rounded-2xl bg-slate-50 border border-slate-100 text-xs font-semibold text-slate-800">
                            <span class="w-7 h-7 rounded-lg bg-pink-100 text-pink-600 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-check text-xs"></i>
                            </span>
                            <span>{{ $f }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- House Rules / Kebijakan Menginap -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-4">
                <h2 class="text-lg sm:text-xl font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-clock text-pink-600"></i> Aturan &amp; Kebijakan Menginap
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs text-slate-700">
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-1">
                        <span class="font-extrabold text-slate-900 block text-sm">Waktu Check-in &amp; Check-out</span>
                        <p class="text-slate-600">• Check-in: Mulai pukul 14:00 WIB</p>
                        <p class="text-slate-600">• Check-out: Maksimal pukul 12:00 WIB</p>
                    </div>

                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-1">
                        <span class="font-extrabold text-slate-900 block text-sm">Ketentuan Tamu</span>
                        <p class="text-slate-600">• Wajib menunjukkan identitas saat check-in</p>
                        <p class="text-slate-600">• Dilarang membawa narkoba &amp; senjata tajam</p>
                    </div>
                </div>
            </div>

            <!-- Host / Agent Profile -->
            <div class="bg-gradient-to-r from-pink-50 to-slate-50 rounded-3xl p-6 border border-pink-100 flex items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <img src="{{ $agentPhoto }}" alt="{{ $agentName }}" class="w-16 h-16 rounded-2xl object-cover border-2 border-white shadow-md shrink-0">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-wider text-pink-600 block">Host &bull; Vila Consultant</span>
                        <h3 class="text-base font-extrabold text-slate-900">{{ $agentName }}</h3>
                        <p class="text-xs text-slate-500">{{ $agentOffice }}</p>
                    </div>
                </div>

                <a href="{{ $waDirectUrl }}" target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition-colors shrink-0">
                    <i class="fa-brands fa-whatsapp text-sm"></i>
                    <span>Chat Host</span>
                </a>
            </div>
        </div>

        <!-- Right Column: Interactive Booking Widget with QRIS (5 cols) -->
        <div class="lg:col-span-5 lg:sticky lg:top-24 space-y-4">
            <div class="bg-white rounded-3xl border-2 border-pink-200 p-6 shadow-xl"
                 x-data="{
                     checkIn: '{{ old('check_in_date', $checkIn) }}',
                     checkOut: '{{ old('check_out_date', $checkOut) }}',
                     guests: {{ old('guests_count', $guests) }},
                     pricePerNight: {{ $pricePerNight }},
                     cleaningFee: {{ $cleaningFee }},
                     securityDeposit: {{ $securityDeposit }},
                     get nights() {
                         if (!this.checkIn || !this.checkOut) return 1;
                         const d1 = new Date(this.checkIn);
                         const d2 = new Date(this.checkOut);
                         const diff = Math.ceil((d2 - d1) / (1000 * 60 * 60 * 24));
                         return diff > 0 ? diff : 1;
                     },
                     get subtotal() {
                         return this.pricePerNight * this.nights;
                     },
                     get grandTotal() {
                         return this.subtotal + this.cleaningFee + this.securityDeposit;
                     },
                     formatRupiah(num) {
                         return 'Rp ' + Number(num).toLocaleString('id-ID');
                     }
                 }">

                <!-- Pricing Header -->
                <div class="flex items-baseline justify-between pb-4 border-b border-slate-100">
                    <div>
                        <span class="text-xs text-slate-400 font-bold uppercase block">Harga Sewa</span>
                        <div class="text-2xl sm:text-3xl font-black text-pink-600">
                            Rp {{ number_format($pricePerNight, 0, ',', '.') }}
                            <span class="text-xs font-bold text-slate-500">/ malam</span>
                        </div>
                    </div>
                    <span class="bg-pink-100 text-pink-700 text-[10px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider">
                        <i class="fa-solid fa-qrcode text-pink-600 mr-1"></i> Bayar QRIS
                    </span>
                </div>

                <!-- Error Messages -->
                @if($errors->any())
                    <div class="mt-4 p-3 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs space-y-1">
                        @foreach($errors->all() as $err)
                            <div class="flex items-center gap-1.5 font-medium">
                                <i class="fa-solid fa-circle-exclamation text-rose-500 shrink-0"></i>
                                <span>{{ $err }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Booking Form -->
                <form action="{{ route('villas.book', $property->id) }}" method="POST" class="mt-5 space-y-4">
                    @csrf

                    <!-- Dates & Guests Selection -->
                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 space-y-3 text-xs">
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">
                                    <i class="fa-regular fa-calendar-check text-pink-600 mr-0.5"></i> Check-in
                                </label>
                                <input type="date" name="check_in_date" x-model="checkIn" min="{{ date('Y-m-d') }}" required
                                       class="w-full bg-white border border-slate-200 rounded-xl px-2.5 py-2 font-semibold text-slate-800 text-xs focus:ring-2 focus:ring-pink-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">
                                    <i class="fa-regular fa-calendar-xmark text-pink-600 mr-0.5"></i> Check-out
                                </label>
                                <input type="date" name="check_out_date" x-model="checkOut" min="{{ date('Y-m-d', strtotime('+1 day')) }}" required
                                       class="w-full bg-white border border-slate-200 rounded-xl px-2.5 py-2 font-semibold text-slate-800 text-xs focus:ring-2 focus:ring-pink-500 focus:outline-none">
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">
                                <i class="fa-solid fa-users text-pink-600 mr-0.5"></i> Jumlah Tamu (Maks. {{ $property->max_guests }} Orang)
                            </label>
                            <select name="guests_count" x-model="guests" required
                                    class="w-full bg-white border border-slate-200 rounded-xl px-2.5 py-2 font-semibold text-slate-800 text-xs focus:ring-2 focus:ring-pink-500 focus:outline-none">
                                @for($g = 1; $g <= $property->max_guests; $g++)
                                    <option value="{{ $g }}">{{ $g }} Tamu</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <!-- Guest Contact Details -->
                    <div class="space-y-2.5 pt-2">
                        <div class="text-xs font-extrabold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fa-solid fa-id-card text-pink-600"></i> Data Pemesan
                        </div>

                        <div>
                            <input type="text" name="customer_name" value="{{ old('customer_name', auth()->user()?->name) }}" placeholder="Nama Lengkap Sesuai KTP *" required
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-pink-500 focus:outline-none">
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <input type="tel" name="customer_phone" value="{{ old('customer_phone') }}" placeholder="No. WhatsApp/HP *" required
                                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-pink-500 focus:outline-none">
                            </div>
                            <div>
                                <input type="email" name="customer_email" value="{{ old('customer_email', auth()->user()?->email) }}" placeholder="Email Aktif *" required
                                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-pink-500 focus:outline-none">
                            </div>
                        </div>

                        <div>
                            <textarea name="special_requests" rows="2" placeholder="Catatan atau permintaan khusus (opsional, misal: perkiraan jam tiba, permohonan extra bed)"
                                      class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-pink-500 focus:outline-none">{{ old('special_requests') }}</textarea>
                        </div>
                    </div>

                    <!-- Live Price Breakdown -->
                    <div class="pt-3 border-t border-slate-100 space-y-2 text-xs">
                        <div class="flex justify-between text-slate-600">
                            <span>Sewa Vila (<span x-text="nights"></span> Malam)</span>
                            <span class="font-bold text-slate-800" x-text="formatRupiah(subtotal)"></span>
                        </div>

                        <template x-if="cleaningFee > 0">
                            <div class="flex justify-between text-slate-600">
                                <span>Biaya Kebersihan &amp; Layanan</span>
                                <span class="font-bold text-slate-800" x-text="formatRupiah(cleaningFee)"></span>
                            </div>
                        </template>

                        <div class="pt-2 border-t border-dashed border-slate-200 flex justify-between items-baseline text-sm">
                            <span class="font-black text-slate-900">Total Pembayaran QRIS</span>
                            <span class="font-black text-pink-600 text-lg" x-text="formatRupiah(grandTotal)"></span>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit"
                            class="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-pink-600 to-rose-600 hover:from-pink-700 hover:to-rose-700 text-white font-black text-sm shadow-lg shadow-pink-600/30 transition-all transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-qrcode text-lg"></i>
                        <span>Sewa Sekarang &bull; Bayar via QRIS</span>
                    </button>
                </form>

                <!-- Guarantee Footer -->
                <div class="mt-4 pt-3 border-t border-slate-100 text-center space-y-1">
                    <div class="text-[11px] text-slate-500 font-medium flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-shield-halved text-emerald-600"></i>
                        <span>Pembayaran Aman &bull; Kode QRIS Resmi Standar Nasional</span>
                    </div>
                    <div class="text-[10px] text-slate-400">
                        Voucher reservasi otomatis diterbitkan setelah pembayaran diverifikasi.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

