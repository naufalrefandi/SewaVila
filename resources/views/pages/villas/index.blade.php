@extends('layouts.app')

@section('title', 'Sewa Villa Liburan & Staycation Eksklusif | Pembayaran QRIS Instan')
@section('meta_description', 'Temukan dan sewa villa mewah di Bali, Puncak, Lembang Bandung, Yogyakarta, dan Batu Malang dengan kolam renang privat. Pembayaran mudah, aman, dan instan via QRIS.')

@section('content')
<!-- Hero Banner & Search Section -->
<div class="relative bg-gradient-to-br from-pink-900 via-pink-800 to-slate-900 text-white overflow-hidden py-12 md:py-16">
    <div class="absolute inset-0 opacity-20 bg-[radial-gradient(#f472b6_1px,transparent_1px)] [background-size:16px_16px]"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center max-w-3xl mx-auto mb-8 md:mb-10">
            <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-pink-500/20 text-pink-200 border border-pink-400/30 text-xs font-bold uppercase tracking-wider mb-3">
                <i class="fa-solid fa-umbrella-beach text-amber-300"></i> Sewa Vila Eksklusif &bull; Bayar QRIS Instan
            </span>
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-black tracking-tight text-white leading-tight">
                Liburan Impian di Vila Pilihan Terbaik
            </h1>
            <p class="mt-3 text-sm sm:text-base text-pink-100 max-w-2xl mx-auto">
                Pilihan vila estetik dengan private pool, pemandangan alam spektakuler di Bali, Puncak, Bandung, Jogja, dan Batu. Booking instan & pembayaran praktis menggunakan QRIS.
            </p>
        </div>

        <!-- Search & Filter Card -->
        <div class="bg-white rounded-3xl p-4 sm:p-6 shadow-2xl border border-pink-100 text-slate-800 max-w-5xl mx-auto">
            <form action="{{ route('villas.index') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                    <!-- Destination / City -->
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">
                            <i class="fa-solid fa-location-dot text-pink-600 mr-1"></i> Destinasi / Kota
                        </label>
                        <select name="city" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-pink-500 focus:outline-none">
                            <option value="">Semua Destinasi</option>
                            @foreach($cities as $c)
                                <option value="{{ $c }}" {{ request('city') === $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Check-In Date -->
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">
                            <i class="fa-regular fa-calendar-check text-pink-600 mr-1"></i> Check-in
                        </label>
                        <input type="date" name="check_in" value="{{ request('check_in') }}" min="{{ date('Y-m-d') }}"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm font-medium text-slate-800 focus:ring-2 focus:ring-pink-500 focus:outline-none">
                    </div>

                    <!-- Check-Out Date -->
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">
                            <i class="fa-regular fa-calendar-xmark text-pink-600 mr-1"></i> Check-out
                        </label>
                        <input type="date" name="check_out" value="{{ request('check_out') }}" min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm font-medium text-slate-800 focus:ring-2 focus:ring-pink-500 focus:outline-none">
                    </div>

                    <!-- Guests -->
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">
                            <i class="fa-solid fa-users text-pink-600 mr-1"></i> Jumlah Tamu
                        </label>
                        <select name="guests" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-pink-500 focus:outline-none">
                            <option value="">Berapa saja</option>
                            <option value="2" {{ request('guests') == '2' ? 'selected' : '' }}>2+ Tamu (Couple / Pasangan)</option>
                            <option value="4" {{ request('guests') == '4' ? 'selected' : '' }}>4+ Tamu (Keluarga Kecil)</option>
                            <option value="8" {{ request('guests') == '8' ? 'selected' : '' }}>8+ Tamu (Rombongan / Family)</option>
                            <option value="12" {{ request('guests') == '12' ? 'selected' : '' }}>12+ Tamu (Grup Besar)</option>
                        </select>
                    </div>
                </div>

                <!-- Secondary Filters: Pool, Price Range, Search, Buttons -->
                <div class="pt-2 border-t border-slate-100 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3 text-xs">
                    <div class="flex flex-wrap items-center gap-3">
                        <label class="inline-flex items-center gap-2 cursor-pointer bg-pink-50 px-3 py-2 rounded-xl border border-pink-200 hover:bg-pink-100/70 transition-colors">
                            <input type="checkbox" name="has_pool" value="1" {{ request('has_pool') ? 'checked' : '' }} class="rounded text-pink-600 focus:ring-pink-500 h-4 w-4">
                            <span class="font-bold text-pink-800"><i class="fa-solid fa-water-ladder text-pink-600"></i> Kolam Renang Privat</span>
                        </label>

                        <div class="flex items-center gap-2">
                            <span class="text-slate-500 font-semibold">Urutkan:</span>
                            <select name="sort" class="bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs font-bold text-slate-700 focus:outline-none">
                                <option value="recommended" {{ request('sort') == 'recommended' ? 'selected' : '' }}>Rekomendasi</option>
                                <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Harga Termurah</option>
                                <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Harga Tertinggi</option>
                                <option value="bedrooms_desc" {{ request('sort') == 'bedrooms_desc' ? 'selected' : '' }}>Kamar Terbanyak</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        @if(request()->anyFilled(['city', 'check_in', 'check_out', 'guests', 'has_pool', 'q', 'sort']))
                            <a href="{{ route('villas.index') }}" class="px-3 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold transition-colors">
                                Reset
                            </a>
                        @endif

                        <button type="submit" class="flex-1 md:flex-initial inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-extrabold shadow-md shadow-pink-600/30 transition-all transform hover:-translate-y-0.5">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <span>Cari Vila</span>
                        </button>

                        <a href="{{ route('villas.check') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition-colors" title="Cek status pemesanan Anda">
                            <i class="fa-solid fa-ticket text-pink-600"></i>
                            <span class="hidden sm:inline">Cek Status</span>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-12">
    <!-- Destinasi Liburan Populer Carousel / Grid -->
    <section>
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                    <i class="fa-solid fa-compass text-pink-600"></i> Destinasi Liburan Populer
                </h2>
                <p class="text-xs sm:text-sm text-slate-500">Pilih kawasan liburan favorit untuk pengalaman menginap tak terlupakan</p>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
            @foreach($destinations as $dest)
                <a href="{{ route('villas.index', ['city' => $dest['query_city']]) }}"
                   class="group relative rounded-2xl overflow-hidden aspect-4/3 sm:aspect-square border border-slate-200 shadow-sm hover:shadow-lg transition-all duration-300">
                    <img src="{{ $dest['image'] }}" alt="{{ $dest['name'] }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent"></div>
                    <div class="absolute bottom-3 left-3 right-3 text-white">
                        <div class="font-extrabold text-sm sm:text-base leading-tight drop-shadow-sm group-hover:text-pink-300 transition-colors">
                            {{ $dest['name'] }}
                        </div>
                        <div class="text-[10px] sm:text-xs text-pink-200/90 font-medium truncate mt-0.5">
                            {{ $dest['desc'] }}
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </section>

    <!-- Villa Listing Catalog -->
    <section>
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-6 pb-3 border-b border-slate-200">
            <div>
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                    <i class="fa-solid fa-hotel text-pink-600"></i> Daftar Vila Siap Disewa
                </h2>
                <p class="text-xs sm:text-sm text-slate-500">
                    Menampilkan <span class="font-bold text-slate-900">{{ $villas->total() }}</span> pilihan vila siap sewa
                    @if(request('city')) di <span class="font-bold text-pink-600">{{ request('city') }}</span> @endif
                </p>
            </div>

            <!-- Payment Badge Notice -->
            <div class="inline-flex items-center gap-2 bg-emerald-50 text-emerald-800 border border-emerald-200 px-3 py-1.5 rounded-xl text-xs font-bold">
                <i class="fa-solid fa-qrcode text-emerald-600"></i>
                <span>Didukung Pembayaran QRIS Nasional (Semua Bank &amp; E-Wallet)</span>
            </div>
        </div>

        @if($villas->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                @foreach($villas as $villa)
                    @php
                        $cover = $villa->primary_image;
                        $pricePerNight = (int) ($villa->rent_price ?: $villa->price);
                        $formattedPrice = 'Rp ' . number_format($pricePerNight, 0, ',', '.') . ' / malam';
                        $maxCapacity = $villa->bedrooms ? max(2, $villa->bedrooms * 2) : 6;
                        $hasPool = is_array($villa->facilities) && (in_array('Kolam Renang', $villa->facilities) || in_array('Private Pool', $villa->facilities) || in_array('Swimming Pool', $villa->facilities));
                    @endphp
                    <div class="bg-white rounded-3xl overflow-hidden border border-slate-200 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col group">
                        <!-- Cover Image with Badges -->
                        <div class="relative aspect-16/10 overflow-hidden bg-slate-100">
                            <a href="{{ route('villas.show', $villa->slug) }}">
                                <img src="{{ $cover }}" alt="{{ $villa->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            </a>

                            <!-- Badges -->
                            <div class="absolute top-3 left-3 flex flex-wrap gap-1.5">
                                <span class="bg-pink-600 text-white text-[10px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider shadow-md">
                                    <i class="fa-solid fa-umbrella-beach mr-1"></i> Vila
                                </span>
                                @if($hasPool)
                                    <span class="bg-cyan-600 text-white text-[10px] font-bold px-2 py-1 rounded-full shadow-md">
                                        <i class="fa-solid fa-water-ladder mr-1"></i> Private Pool
                                    </span>
                                @endif
                            </div>

                            <div class="absolute top-3 right-3">
                                <span class="bg-black/60 backdrop-blur-xs text-white text-[11px] font-extrabold px-2.5 py-1 rounded-full">
                                    <i class="fa-solid fa-qrcode text-pink-400 mr-1"></i> QRIS Ready
                                </span>
                            </div>

                            <div class="absolute bottom-3 left-3 right-3 text-white drop-shadow-md">
                                <div class="text-xs font-semibold flex items-center gap-1.5 bg-black/40 backdrop-blur-xs px-2.5 py-1 rounded-lg w-fit">
                                    <i class="fa-solid fa-location-dot text-pink-400"></i>
                                    <span>{{ $villa->district ? $villa->district . ', ' : '' }}{{ $villa->city }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-5 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-slate-900 group-hover:text-pink-600 transition-colors line-clamp-1 mb-2">
                                    <a href="{{ route('villas.show', $villa->slug) }}">{{ $villa->title }}</a>
                                </h3>

                                <!-- Specs & Capacity -->
                                <div class="grid grid-cols-3 gap-2 py-2.5 px-3 bg-slate-50 rounded-2xl border border-slate-100 text-center text-xs text-slate-600 mb-4">
                                    <div>
                                        <span class="font-extrabold text-slate-900 block">{{ $villa->bedrooms ?: 1 }}</span>
                                        <span class="text-[10px] text-slate-500">Kamar Tidur</span>
                                    </div>
                                    <div class="border-x border-slate-200">
                                        <span class="font-extrabold text-slate-900 block">{{ $villa->bathrooms ?: 1 }}</span>
                                        <span class="text-[10px] text-slate-500">Kamar Mandi</span>
                                    </div>
                                    <div>
                                        <span class="font-extrabold text-slate-900 block">s/d {{ $maxCapacity }}</span>
                                        <span class="text-[10px] text-slate-500">Kapasitas Tamu</span>
                                    </div>
                                </div>

                                <p class="text-xs text-slate-500 line-clamp-2 mb-4">
                                    {{ strip_tags($villa->description) }}
                                </p>
                            </div>

                            <!-- Footer Price & Action -->
                            <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Harga Sewa</span>
                                    <span class="text-base sm:text-lg font-black text-pink-600 leading-tight">
                                        {{ $formattedPrice }}
                                    </span>
                                </div>

                                <a href="{{ route('villas.show', $villa->slug) }}"
                                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-extrabold text-xs shadow-sm hover:shadow-md transition-all">
                                    <span>Sewa Vila</span>
                                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $villas->links() }}
            </div>
        @else
            <div class="text-center py-16 bg-white rounded-3xl border border-slate-200 p-8">
                <div class="w-16 h-16 mx-auto rounded-full bg-pink-50 text-pink-600 flex items-center justify-center text-2xl mb-4">
                    <i class="fa-solid fa-umbrella-beach"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-800">Tidak ada vila yang cocok dengan kriteria pencarian</h3>
                <p class="text-sm text-slate-500 max-w-md mx-auto mt-1 mb-6">
                    Silakan ubah filter tanggal, lokasi, atau harga sewa untuk menemukan vila yang tersedia.
                </p>
                <a href="{{ route('villas.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-pink-600 text-white font-bold text-xs hover:bg-pink-700 transition-colors">
                    <i class="fa-solid fa-rotate-left"></i>
                    <span>Tampilkan Semua Vila</span>
                </a>
            </div>
        @endif
    </section>

    <!-- Why Rent With Us / QRIS Benefit Section -->
    <section class="bg-gradient-to-r from-pink-50 via-rose-50 to-amber-50 rounded-3xl p-6 sm:p-10 border border-pink-100">
        <div class="max-w-4xl mx-auto text-center space-y-3 mb-8">
            <span class="text-xs font-black uppercase tracking-wider text-pink-600">Keunggulan Sewa Vila Bersama Kami</span>
            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Booking Mudah, Staycation Tanpa Ribet</h2>
            <p class="text-xs sm:text-sm text-slate-600">Seluruh vila dikurasi secara profesional dan terverifikasi untuk menjamin kenyamanan liburan Anda.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-2xl border border-pink-100 shadow-xs space-y-2">
                <div class="w-12 h-12 rounded-xl bg-pink-100 text-pink-600 flex items-center justify-center text-xl mb-3">
                    <i class="fa-solid fa-qrcode"></i>
                </div>
                <h3 class="font-bold text-slate-900 text-base">Pembayaran Praktis via QRIS</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Scan langsung barcode QRIS menggunakan mobile banking apa saja (BCA, Mandiri, BRI, BNI) atau dompet digital (GoPay, OVO, DANA, ShopeePay).
                </p>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-pink-100 shadow-xs space-y-2">
                <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-xl mb-3">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <h3 class="font-bold text-slate-900 text-base">Vila 100% Terverifikasi</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Setiap unit vila telah dicek keaslian fasilitas, kebersihan, dan foto lokasinya oleh tim agen profesional kami.
                </p>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-pink-100 shadow-xs space-y-2">
                <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl mb-3">
                    <i class="fa-solid fa-headset"></i>
                </div>
                <h3 class="font-bold text-slate-900 text-base">Voucher &amp; Bantuan 24/7</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Setelah pembayaran terverifikasi, Anda langsung mendapatkan voucher reservasi digital resmi dan kontak langsung dengan host/villa manager.
                </p>
            </div>
        </div>
    </section>
</div>
@endsection

