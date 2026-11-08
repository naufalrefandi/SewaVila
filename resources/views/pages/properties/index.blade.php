@extends('layouts.app')

@section('title', 'Cari Listing Properti - ' . \App\Models\Setting::getSiteName() . '')

@section('content')
<!-- Search Breadcrumbs & Header -->
<div class="bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 sm:py-3.5">
        <nav class="flex text-[11px] sm:text-xs text-slate-500 mb-0.5">
            <a href="{{ route('home') }}" class="hover:text-pink-600">Beranda</a>
            <span class="mx-1.5 text-slate-400">/</span>
            <span class="text-slate-800 font-semibold">Cari Properti</span>
            @if(request('listing_type'))
                <span class="mx-1.5 text-slate-400">/</span>
                <span class="text-pink-600 font-bold capitalize">{{ request('listing_type') }}</span>
            @endif
        </nav>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 sm:gap-4">
            <div>
                <h1 class="text-base sm:text-2xl font-extrabold text-slate-900 tracking-tight leading-tight">
                    @if(request('property_type') === 'tanah')
                        Tanah Kavling Dijual &amp; Disewa
                    @elseif(request('property_type') === 'gudang')
                        Gudang &amp; Pabrik Komersial
                    @elseif(request('property_type') === 'rumah')
                        Rumah Tapak Dijual &amp; Disewa
                    @elseif(request('property_type') === 'apartemen')
                        Apartemen &amp; Kondominium
                    @elseif(request('property_type') === 'ruko')
                        Ruko &amp; Ruang Usaha
                    @elseif(request('property_type') === 'villa')
                        Villa Mewah &amp; Resort
                    @elseif(request('listing_type') === 'disewa')
                        Properti Disewa di Indonesia
                    @elseif(request('listing_type') === 'dijual')
                        Properti Dijual di Indonesia
                    @else
                        Semua Listing Properti Tersedia
                    @endif
                </h1>
                <p class="text-[11px] sm:text-xs text-slate-500 mt-0.5">
                    Menampilkan <span class="font-bold text-slate-800">{{ $properties->total() }}</span> properti terverifikasi
                </p>
            </div>

            <!-- Quick Transaction Type Pills -->
            <div class="flex items-center gap-1 bg-slate-100 p-0.5 rounded-lg self-start sm:self-auto shrink-0">
                <a href="{{ route('properties.index', array_merge(request()->except('listing_type'), ['page' => 1])) }}"
                   class="px-3 py-1 rounded-md text-xs font-bold transition-all {{ !request('listing_type') ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    Semua
                </a>
                <a href="{{ route('properties.index', array_merge(request()->all(), ['listing_type' => 'dijual', 'page' => 1])) }}"
                   class="px-3 py-1 rounded-md text-xs font-bold transition-all {{ request('listing_type') === 'dijual' ? 'bg-pink-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    Dijual
                </a>
                <a href="{{ route('properties.index', array_merge(request()->all(), ['listing_type' => 'disewa', 'page' => 1])) }}"
                   class="px-3 py-1 rounded-md text-xs font-bold transition-all {{ request('listing_type') === 'disewa' ? 'bg-pink-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    Disewa
                </a>
            </div>
        </div>

        <!-- Category Filter Pills Bar (Rumah, Apartemen, Tanah, Gudang, Ruko, Hotel, Gedung) -->
        <div class="mt-1.5 pt-1.5 border-t border-slate-100 flex items-center gap-1.5 overflow-x-auto pb-0.5 scrollbar-none">
            <span class="text-[11px] font-bold text-slate-400 shrink-0 uppercase tracking-wider mr-0.5">Kategori:</span>
            <a href="{{ route('properties.index', array_merge(request()->except('property_type'), ['page' => 1])) }}"
               class="px-2.5 py-1 rounded-lg text-xs font-bold shrink-0 transition-all {{ !request('property_type') ? 'bg-pink-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-pink-50 hover:text-pink-600' }}">
                Semua
            </a>
            <a href="{{ route('properties.index', array_merge(request()->all(), ['property_type' => 'rumah', 'page' => 1])) }}"
               class="px-2.5 py-1 rounded-lg text-xs font-bold shrink-0 transition-all {{ request('property_type') === 'rumah' ? 'bg-pink-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-pink-50 hover:text-pink-600' }}">
                <i class="fa-solid fa-house-chimney mr-1 text-[10px]"></i> Rumah
            </a>
            <a href="{{ route('properties.index', array_merge(request()->all(), ['property_type' => 'apartemen', 'page' => 1])) }}"
               class="px-2.5 py-1 rounded-lg text-xs font-bold shrink-0 transition-all {{ request('property_type') === 'apartemen' ? 'bg-pink-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-pink-50 hover:text-pink-600' }}">
                <i class="fa-solid fa-building mr-1 text-[10px]"></i> Apartemen
            </a>
            <a href="{{ route('properties.index', array_merge(request()->all(), ['property_type' => 'tanah', 'page' => 1])) }}"
               class="px-2.5 py-1 rounded-lg text-xs font-bold shrink-0 transition-all {{ request('property_type') === 'tanah' ? 'bg-pink-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-pink-50 hover:text-pink-600' }}">
                <i class="fa-solid fa-map mr-1 text-[10px]"></i> Tanah
            </a>
            <a href="{{ route('properties.index', array_merge(request()->all(), ['property_type' => 'gudang', 'page' => 1])) }}"
               class="px-2.5 py-1 rounded-lg text-xs font-bold shrink-0 transition-all {{ request('property_type') === 'gudang' ? 'bg-pink-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-pink-50 hover:text-pink-600' }}">
                <i class="fa-solid fa-warehouse mr-1 text-[10px]"></i> Gudang
            </a>
            <a href="{{ route('properties.index', array_merge(request()->all(), ['property_type' => 'ruko', 'page' => 1])) }}"
               class="px-2.5 py-1 rounded-lg text-xs font-bold shrink-0 transition-all {{ request('property_type') === 'ruko' ? 'bg-pink-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-pink-50 hover:text-pink-600' }}">
                <i class="fa-solid fa-store mr-1 text-[10px]"></i> Ruko
            </a>
            <a href="{{ route('properties.index', array_merge(request()->all(), ['property_type' => 'hotel', 'page' => 1])) }}"
               class="px-2.5 py-1 rounded-lg text-xs font-bold shrink-0 transition-all {{ request('property_type') === 'hotel' ? 'bg-pink-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-pink-50 hover:text-pink-600' }}">
                <i class="fa-solid fa-hotel mr-1 text-[10px]"></i> Hotel
            </a>
            <a href="{{ route('properties.index', array_merge(request()->all(), ['property_type' => 'gedung', 'page' => 1])) }}"
               class="px-2.5 py-1 rounded-lg text-xs font-bold shrink-0 transition-all {{ request('property_type') === 'gedung' ? 'bg-pink-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-pink-50 hover:text-pink-600' }}">
                <i class="fa-solid fa-city mr-1 text-[10px]"></i> Gedung
            </a>
            <a href="{{ route('properties.index', array_merge(request()->all(), ['property_type' => 'villa', 'page' => 1])) }}"
               class="px-2.5 py-1 rounded-lg text-xs font-bold shrink-0 transition-all {{ request('property_type') === 'villa' ? 'bg-pink-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-pink-50 hover:text-pink-600' }}">
                <i class="fa-solid fa-umbrella-beach mr-1 text-[10px]"></i> Villa
            </a>
        </div>
    </div>
</div>

<!-- Main Content Area: Sidebar + Grid -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-2.5 pb-6 sm:py-6" x-data="{ mobileFilterOpen: false }">
    <!-- Mobile Filter Trigger Button -->
    <div class="lg:hidden mb-2">
        <button @click="mobileFilterOpen = !mobileFilterOpen"
                class="w-full flex items-center justify-center gap-2 py-1.5 px-3 rounded-xl bg-white border border-slate-200 shadow-xs font-bold text-xs text-slate-700">
            <i class="fa-solid fa-sliders text-pink-600 text-xs"></i>
            <span>Filter Pencarian Properti</span>
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-3.5 lg:gap-6 items-start">
        <!-- Sidebar Filter (Desktop + Mobile drawer) -->
        <aside class="lg:col-span-4 xl:col-span-3 bg-white rounded-2xl border border-slate-200 p-5 shadow-xs"
               :class="mobileFilterOpen ? 'block' : 'hidden lg:block'">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h2 class="font-extrabold text-slate-900 text-base flex items-center gap-2">
                    <i class="fa-solid fa-filter text-pink-600"></i> Filter Properti
                </h2>
                <a href="{{ route('properties.index') }}" class="text-xs font-bold text-pink-600 hover:text-pink-700">
                    Reset
                </a>
            </div>

            <form action="{{ route('properties.index') }}" method="GET" class="space-y-5 mt-5">
                <!-- Preserve Listing Type if set -->
                @if(request('listing_type'))
                    <input type="hidden" name="listing_type" value="{{ request('listing_type') }}">
                @endif

                <!-- Keyword / Search Input -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Kata Kunci / Area</label>
                    <div class="relative">
                        <input type="text"
                               name="q"
                               value="{{ request('q') }}"
                               placeholder="Cth: Pondok Indah, BSD..."
                               class="w-full pl-8 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-pink-500 focus:bg-white focus:outline-none">
                        <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-3 text-slate-400 text-xs"></i>
                    </div>
                </div>

                <!-- City / Region Filter -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Kota / Wilayah</label>
                    <select name="city" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-pink-500 focus:bg-white focus:outline-none">
                        <option value="">Semua Kota</option>
                        @foreach($cities as $city)
                            <option value="{{ $city }}" {{ request('city') == $city ? 'selected' : '' }}>{{ $city }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Property Type Filter -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tipe Properti</label>
                    <select name="property_type" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-pink-500 focus:bg-white focus:outline-none">
                        <option value="">Semua Tipe</option>
                        @foreach($propertyTypes as $key => $label)
                            <option value="{{ $key }}" {{ request('property_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Price Range Filter -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Kisaran Harga (Rp)</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="number"
                               name="price_min"
                               value="{{ request('price_min') }}"
                               placeholder="Harga Min"
                               class="w-full px-2.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-pink-500 focus:bg-white focus:outline-none">
                        <input type="number"
                               name="price_max"
                               value="{{ request('price_max') }}"
                               placeholder="Harga Max"
                               class="w-full px-2.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-pink-500 focus:bg-white focus:outline-none">
                    </div>
                </div>

                <!-- Bedrooms Filter -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Kamar Tidur Minimal</label>
                    <div class="grid grid-cols-5 gap-1.5 text-center">
                        @foreach(['', '1', '2', '3', '4'] as $bed)
                            <label class="cursor-pointer">
                                <input type="radio" name="bedrooms" value="{{ $bed }}" {{ request('bedrooms') == $bed ? 'checked' : '' }} class="sr-only peer">
                                <div class="py-1.5 text-xs font-bold rounded-lg border border-slate-200 peer-checked:bg-pink-600 peer-checked:text-white peer-checked:border-pink-600 hover:bg-slate-50 transition-all">
                                    {{ $bed === '' ? 'Semua' : $bed . '+' }}
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Furnished Filter -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Kondisi Perabotan</label>
                    <select name="furnished" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-pink-500 focus:bg-white focus:outline-none">
                        <option value="">Semua Kondisi</option>
                        <option value="fully-furnished" {{ request('furnished') == 'fully-furnished' ? 'selected' : '' }}>Fully Furnished</option>
                        <option value="semi-furnished" {{ request('furnished') == 'semi-furnished' ? 'selected' : '' }}>Semi Furnished</option>
                        <option value="unfurnished" {{ request('furnished') == 'unfurnished' ? 'selected' : '' }}>Unfurnished</option>
                    </select>
                </div>

                <!-- Certificate Filter -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Sertifikat</label>
                    <select name="certificate" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-pink-500 focus:bg-white focus:outline-none">
                        <option value="">Semua Sertifikat</option>
                        <option value="SHM" {{ request('certificate') == 'SHM' ? 'selected' : '' }}>SHM (Sertifikat Hak Milik)</option>
                        <option value="HGB" {{ request('certificate') == 'HGB' ? 'selected' : '' }}>HGB (Hak Guna Bangunan)</option>
                        <option value="Strata Title" {{ request('certificate') == 'Strata Title' ? 'selected' : '' }}>Strata Title</option>
                    </select>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-xs shadow-md transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-check"></i> Terapkan Filter
                </button>
            </form>
        </aside>

        <!-- Properties Results Grid -->
        <main class="lg:col-span-8 xl:col-span-9">
            <!-- Top Bar: Sorting & Active Filters -->
            <div class="bg-white rounded-xl border border-slate-200 px-3 py-2 sm:px-4 sm:py-2.5 mb-2.5 sm:mb-4 shadow-xs flex items-center justify-between gap-2 flex-wrap">
                <div class="text-xs text-slate-600 font-medium">
                    Menampilkan <span class="font-bold text-slate-900">{{ $properties->firstItem() ?? 0 }}-{{ $properties->lastItem() ?? 0 }}</span> dari <span class="font-bold text-slate-900">{{ $properties->total() }}</span> properti
                </div>

                <!-- Sorting Dropdown -->
                <form action="{{ route('properties.index') }}" method="GET" class="flex items-center gap-2 self-end sm:self-auto">
                    @foreach(request()->except('sort') as $key => $value)
                        @if(is_string($value))
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <label class="text-xs text-slate-500 font-semibold whitespace-nowrap">Urutkan:</label>
                    <select name="sort"
                            onchange="this.form.submit()"
                            class="px-2.5 py-1 sm:px-3 sm:py-1.5 bg-slate-50 border border-slate-200 rounded-lg sm:rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:ring-1 focus:ring-pink-500">
                        <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Terbaru</option>
                        <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Harga Terendah</option>
                        <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Harga Tertinggi</option>
                        <option value="land_desc" {{ request('sort') == 'land_desc' ? 'selected' : '' }}>Luas Tanah Terbesar</option>
                        <option value="building_desc" {{ request('sort') == 'building_desc' ? 'selected' : '' }}>Luas Bangunan Terbesar</option>
                    </select>
                </form>
            </div>

            <!-- Property Cards Grid -->
            @if($properties->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3.5 sm:gap-5">
                    @foreach($properties as $property)
                        <x-property-card :property="$property" />
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-8">
                    {{ $properties->links() }}
                </div>
            @else
                <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-pink-50 text-pink-500 flex items-center justify-center text-2xl mx-auto mb-4">
                        <i class="fa-solid fa-house-circle-xmark"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">Tidak ada properti yang cocok</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                        Coba sesuaikan filter pencarian Anda atau reset filter untuk melihat semua listing yang tersedia.
                    </p>
                    <a href="{{ route('properties.index') }}" class="mt-4 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-pink-600 text-white font-bold text-xs shadow-sm hover:bg-pink-700 transition-all">
                        Reset Semua Filter
                    </a>
                </div>
            @endif
        </main>
    </div>
</div>
@endsection

