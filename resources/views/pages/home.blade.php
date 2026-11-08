@extends('layouts.app')

@section('title', '' . \App\Models\Setting::getSiteName() . ' - Portal Listing Properti Terdepan Indonesia')

@section('content')
<!-- Hero Section with Multi-Tab Search Bar -->
<section class="relative bg-white text-slate-900 overflow-hidden pt-8 sm:pt-12 lg:pt-14 pb-6 sm:pb-8 border-b border-pink-100">
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Hero Title -->
        <div class="text-center max-w-3xl mx-auto pt-1 sm:pt-2">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-pink-50 border border-pink-200 text-pink-700 text-xs sm:text-sm font-bold uppercase tracking-wider mb-3 sm:mb-4 shadow-2xs">
                <i class="fa-solid fa-medal text-pink-600"></i> Platform Properti No. 1 Berstandar Internasional
            </div>
            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight text-slate-900 leading-tight">
                Temukan Properti Impian & <span class="text-pink-600">Investasi Terbaik</span>
            </h1>
            <p class="mt-2.5 sm:mt-3 text-base sm:text-lg text-slate-600 font-normal max-w-2xl mx-auto">
                Eksplorasi ribuan rumah, apartemen, tanah, gudang, ruko, hotel, dan gedung pilihan dengan verifikasi legalitas resmi dan informasi lengkap.
            </p>
        </div>

        <!-- Search Bar Container with Alpine.js -->
        <div class="mt-5 sm:mt-6 max-w-3xl mx-auto bg-white rounded-2xl p-3.5 sm:p-5 shadow-lg border border-pink-200 text-slate-800"
             x-data="{
                activeTab: 'dijual',
                selectedType: '{{ request('property_type', '') }}',
                location: ''
             }">
            <!-- Tabs -->
            <div class="flex items-center gap-2 border-b border-pink-100 pb-3 mb-3.5 overflow-x-auto">
                <button type="button"
                        @click="activeTab = 'dijual'"
                        :class="activeTab === 'dijual' ? 'bg-pink-600 text-white shadow-sm' : 'text-slate-600 hover:text-pink-600 hover:bg-pink-50'"
                        class="px-5 py-2 rounded-xl text-sm font-bold transition-all flex items-center gap-2 whitespace-nowrap">
                    <i class="fa-solid fa-house"></i> Dijual
                </button>
                <button type="button"
                        @click="activeTab = 'disewa'"
                        :class="activeTab === 'disewa' ? 'bg-pink-600 text-white shadow-sm' : 'text-slate-600 hover:text-pink-600 hover:bg-pink-50'"
                        class="px-5 py-2 rounded-xl text-sm font-bold transition-all flex items-center gap-2 whitespace-nowrap">
                    <i class="fa-solid fa-key"></i> Disewa
                </button>
                <button type="button"
                        @click="activeTab = 'proyek-baru'"
                        :class="activeTab === 'proyek-baru' ? 'bg-pink-600 text-white shadow-sm' : 'text-slate-600 hover:text-pink-600 hover:bg-pink-50'"
                        class="px-5 py-2 rounded-xl text-sm font-bold transition-all flex items-center gap-2 whitespace-nowrap">
                    <i class="fa-solid fa-city"></i> Proyek Baru
                    <span class="bg-pink-600 text-white text-[10px] font-extrabold px-1.5 py-0.2 rounded-full">New</span>
                </button>
            </div>

            <!-- Tipe Properti 4x2 Grid (Diletakkan di bawah tab Dijual & Disewa) -->
            <div class="grid grid-cols-4 gap-1 min-[370px]:gap-1.5 sm:gap-2.5 mb-3.5">
                    @php
                        $categoryTypes = [
                            ['id' => 'rumah', 'label' => 'Rumah', 'icon' => 'fa-solid fa-house-chimney'],
                            ['id' => 'apartemen', 'label' => 'Apartemen', 'icon' => 'fa-solid fa-building'],
                            ['id' => 'tanah', 'label' => 'Tanah', 'icon' => 'fa-solid fa-map'],
                            ['id' => 'ruko', 'label' => 'Ruko', 'icon' => 'fa-solid fa-store'],
                            ['id' => 'gudang', 'label' => 'Gudang', 'icon' => 'fa-solid fa-warehouse'],
                            ['id' => 'hotel', 'label' => 'Hotel', 'icon' => 'fa-solid fa-hotel'],
                            ['id' => 'gedung', 'label' => 'Gedung', 'icon' => 'fa-solid fa-city'],
                            ['id' => 'villa', 'label' => 'Villa', 'icon' => 'fa-solid fa-umbrella-beach'],
                        ];
                    @endphp
                    @foreach($categoryTypes as $cat)
                        <button type="button"
                                @click="selectedType = selectedType === '{{ $cat['id'] }}' ? '' : '{{ $cat['id'] }}'"
                                :class="selectedType === '{{ $cat['id'] }}'
                                    ? 'bg-pink-50 border-pink-500 text-pink-700 font-bold shadow-2xs'
                                    : 'bg-slate-50/80 hover:bg-pink-50/60 border-slate-200 text-slate-700 hover:text-pink-600 hover:border-pink-300 font-medium sm:font-semibold'"
                                class="flex flex-col items-center justify-center px-0.5 py-1.5 sm:py-2.5 sm:px-2 rounded-[10px] sm:rounded-xl border text-center transition-all cursor-pointer group min-w-0">
                            <i class="{{ $cat['icon'] }} text-sm sm:text-lg mb-1 group-hover:scale-110 transition-transform"
                               :class="selectedType === '{{ $cat['id'] }}' ? 'text-pink-600' : 'text-slate-500 group-hover:text-pink-600'"></i>
                            <span class="block w-full truncate leading-tight px-0.5 {{ $cat['id'] === 'apartemen' ? 'text-[8.5px] min-[370px]:text-[9.5px] min-[410px]:text-[10.5px] sm:text-xs tracking-tighter' : 'text-[9.5px] min-[370px]:text-[10px] min-[410px]:text-[11px] sm:text-xs tracking-tight sm:tracking-normal' }}">{{ $cat['label'] }}</span>
                        </button>
                    @endforeach
                </div>

            <!-- Form -->
            <form action="{{ route('properties.index') }}" method="GET">
                <input type="hidden" name="listing_type" :value="activeTab === 'proyek-baru' ? 'dijual' : activeTab">
                <input type="hidden" name="is_new_launch" :value="activeTab === 'proyek-baru' ? '1' : ''">
                <input type="hidden" name="property_type" :value="selectedType">

                <div class="flex flex-col sm:flex-row items-center gap-2.5 sm:gap-3">
                    <!-- Keyword / Location Input -->
                    <div class="relative flex-1 w-full">
                        <i class="fa-solid fa-location-dot absolute left-4 top-1/2 -translate-y-1/2 text-pink-500 text-sm"></i>
                        <input type="text"
                               name="q"
                               x-model="location"
                               placeholder="Cari lokasi, kawasan, atau nama properti (contoh: Surabaya, Citraland, Anderson...)"
                               class="w-full pl-10 pr-4 py-3 bg-pink-50/40 border border-pink-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-pink-500 focus:bg-white focus:outline-none transition-all placeholder:text-slate-400">
                    </div>

                    <button type="submit"
                            class="w-full sm:w-auto px-8 py-3 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-extrabold text-sm shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 shrink-0">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <span>Cari Properti</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- New Listing / Listing Terbaru Section (Diatas Unggulan) -->
@if(isset($latestProperties) && $latestProperties->count() > 0)
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-7 sm:mt-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-3 mb-3.5 sm:mb-4">
        <div>
            <div class="flex items-center gap-2 text-pink-600 text-xs font-extrabold uppercase tracking-wider">
                <i class="fa-solid fa-sparkles"></i> New Listing
            </div>
            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">
                Listing Properti Terbaru
            </h2>
            <p class="text-sm text-slate-500 mt-0.5">Properti paling anyar yang baru saja diiklankan oleh konsultan properti kami.</p>
        </div>

        <a href="{{ route('properties.index', ['sort' => 'newest']) }}" class="inline-flex items-center gap-2 text-sm font-bold text-pink-600 hover:text-pink-700 group">
            <span>Lihat Semua Listing Terbaru</span>
            <i class="fa-solid fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
        </a>
    </div>

    <!-- Latest Properties Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        @foreach($latestProperties as $property)
            <x-property-card :property="$property" />
        @endforeach
    </div>
</section>
@endif

<!-- Featured / Pilihan Unggulan Section -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-7 sm:mt-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-3 mb-3.5 sm:mb-4">
        <div>
            <div class="flex items-center gap-2 text-rose-600 text-xs font-bold uppercase tracking-wider">
                <i class="fa-solid fa-star"></i> Pilihan Editor {{ \App\Models\Setting::getSiteName() }}
            </div>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">
                Listing Properti Unggulan
            </h2>
            <p class="text-sm text-slate-500 mt-0.5">Koleksi properti premium dengan lokasi strategis dan nilai investasi menjanjikan.</p>
        </div>

        <a href="{{ route('properties.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-rose-600 hover:text-rose-700 group">
            <span>Lihat Semua Properti</span>
            <i class="fa-solid fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
        </a>
    </div>

    <!-- Properties Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        @foreach($featuredProperties as $property)
            <x-property-card :property="$property" />
        @endforeach
    </div>

    <!-- Tombol Lihat Properti Lain di Bawah Properti Unggulan -->
    <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-3">
        <a href="{{ route('properties.index') }}"
           class="inline-flex items-center justify-center gap-2.5 px-6 py-3 rounded-2xl bg-pink-600 hover:bg-pink-700 text-white font-extrabold text-sm shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5 group">
            <i class="fa-solid fa-layer-group text-white transition-colors"></i>
            <span>Lihat Properti Lainnya</span>
            <i class="fa-solid fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
        </a>
        <a href="{{ route('properties.for-rent') }}"
           class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-white hover:bg-pink-50 border border-pink-200 text-slate-800 hover:text-pink-600 font-bold text-sm shadow-xs hover:border-pink-300 transition-all">
            <i class="fa-solid fa-key text-pink-500"></i>
            <span>Cari Properti Disewa</span>
        </a>
    </div>
</section>

<!-- Proyek Baru / New Launch Banner Section -->
@if($newLaunches->count() > 0)
<section class="bg-slate-100/60 text-slate-900 mt-8 sm:mt-9 py-8 sm:py-10 border-y border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-3 mb-4 sm:mb-5">
            <div>
                <span class="bg-pink-100 text-pink-700 border border-pink-200 text-xs font-extrabold px-3 py-1 rounded-full uppercase tracking-wider">
                    Primary Market
                </span>
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 mt-2">
                    Proyek Baru Developer Terpercaya
                </h2>
                <p class="text-sm text-slate-600 mt-1">Dapatkan promo pre-launch, cicilan DP 0%, dan subsidi KPR khusus pembelian proyek perdana.</p>
            </div>

            <a href="{{ route('properties.new-launch') }}" class="inline-flex items-center gap-2 text-sm font-bold text-pink-600 hover:text-pink-700 group">
                <span>Eksplorasi Proyek Baru</span>
                <i class="fa-solid fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($newLaunches as $launch)
                <div class="bg-white rounded-2xl border border-slate-200/90 overflow-hidden shadow-xs hover:shadow-xl hover:border-pink-300 hover:-translate-y-1 transition-all flex flex-col justify-between"
                     x-data="{ copiedCode: false }">
                    <div class="relative aspect-[16/10] overflow-hidden">
                        <img src="{{ $launch->primary_image }}" alt="{{ $launch->title }}" class="w-full h-full object-cover">
                        <span class="absolute top-3 left-3 bg-pink-600 text-white text-[10px] font-bold px-2 py-0.5 rounded uppercase shadow-xs">
                            New Launch
                        </span>
                    </div>
                    <div class="p-4 flex-1 flex flex-col justify-between">
                        <div>
                            @if($launch->property_code)
                                <div class="mb-2">
                                    <button type="button"
                                            @click.prevent.stop="navigator.clipboard.writeText('{{ $launch->property_code }}'); copiedCode = true; setTimeout(() => copiedCode = false, 1600)"
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-pink-50 hover:bg-pink-100 text-pink-700 font-mono text-[10px] font-black tracking-wider border border-pink-200/80 transition-colors cursor-pointer"
                                            title="Klik untuk salin Kode: {{ $launch->property_code }}">
                                        <i class="fa-solid fa-barcode text-pink-500 text-[9px]"></i>
                                        <span x-show="!copiedCode">{{ $launch->property_code }}</span>
                                        <span x-show="copiedCode" x-cloak class="text-emerald-600 flex items-center gap-0.5">
                                            <i class="fa-solid fa-check text-[8px]"></i> Tersalin!
                                        </span>
                                    </button>
                                </div>
                            @endif
                            <div class="text-lg font-extrabold text-pink-600">{{ $launch->formatted_price }}</div>
                            <h3 class="mt-1 text-sm font-bold text-slate-900 line-clamp-2 hover:text-pink-600">
                                <a href="{{ route('properties.show', $launch->slug) }}">{{ $launch->title }}</a>
                            </h3>
                            <p class="mt-1 text-xs text-slate-500 flex items-center gap-1">
                                <i class="fa-solid fa-location-dot text-pink-400"></i>
                                <span>{{ $launch->district }}, {{ $launch->city }}</span>
                            </p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-pink-100 flex items-center justify-between">
                            <span class="text-xs text-emerald-600 font-semibold"><i class="fa-solid fa-certificate"></i> Siap Huni</span>
                            <a href="{{ route('properties.show', $launch->slug) }}" class="text-xs font-bold text-pink-600 hover:text-pink-700">
                                Info Detail &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Explore by City Section (Compact / Dibuat Kecil) -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-7 sm:mt-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-2 mb-3">
        <div>
            <div class="flex items-center gap-1.5 text-pink-600 text-xs font-extrabold uppercase tracking-wider">
                <i class="fa-solid fa-location-dot"></i> Lokasi Populer
            </div>
            <h2 class="text-xl sm:text-2xl font-black text-slate-900 mt-0.5">
                Jelajahi Kota Favorit
            </h2>
        </div>
        <p class="text-xs text-slate-500">Temukan hunian strategis di berbagai kota pilihan.</p>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
        @foreach($popularCities as $city)
            <a href="{{ route('properties.index', ['city' => $city['name']]) }}"
               class="group relative rounded-xl overflow-hidden h-24 sm:h-28 shadow-2xs hover:shadow-md transition-all">
                <img src="{{ $city['img'] }}" alt="{{ $city['name'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/85 via-slate-950/30 to-transparent"></div>
                <div class="absolute bottom-2.5 left-3 right-3 text-white">
                    <h3 class="text-xs sm:text-sm font-bold group-hover:text-pink-300 transition-colors truncate">{{ $city['name'] }}</h3>
                    <p class="text-[10px] text-slate-300 font-medium">{{ $city['count'] }} Listing Aktif</p>
                </div>
            </a>
        @endforeach
    </div>
</section>

<!-- Top Agents Showcase Section (Compact / Dibuat Kecil) -->

<!-- "Titip Properti / Mau Jual Properti?" Banner -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 sm:mt-10 mb-8">
    <div class="bg-white rounded-3xl p-6 sm:p-8 text-slate-900 border border-pink-200 shadow-md flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="max-w-xl">
            <span class="bg-pink-50 text-pink-700 border border-pink-200 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">
                Layanan Pemilik Properti
            </span>
            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 mt-2 leading-tight">
                Mau Jual atau Sewakan Properti Anda Lebih Cepat?
            </h2>
            <p class="text-sm text-slate-600 mt-1.5 leading-relaxed">
                Titipkan listing Anda ke {{ \App\Models\Setting::getSiteName() }} (yproperti.com). Jaringan agen profesional kami siap membantu pemasaran, promosi digital, negosiasi harga, hingga proses notaris PPAT.
            </p>
        </div>

        <div class="shrink-0 flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
            <a href="{{ route('titip-properti') }}" class="px-6 py-3 rounded-2xl bg-pink-600 hover:bg-pink-700 text-white font-extrabold text-sm shadow-md transition-all text-center">
                <i class="fa-solid fa-plus-circle mr-1"></i> Titip Properti
            </a>
            <a href="https://wa.me/6281298765432?text={{ urlencode('Halo ' . \App\Models\Setting::getSiteName() . ', saya ingin konsultasi menitipkan properti untuk dijual/disewakan.') }}"
               target="_blank"
               class="px-5 py-3 rounded-2xl bg-pink-50 hover:bg-pink-100 border border-pink-200 text-pink-700 font-bold text-sm transition-all text-center flex items-center justify-center gap-2">
                <i class="fa-brands fa-whatsapp text-lg"></i> Chat Konsultan
            </a>
        </div>
    </div>
</section>
@endsection

