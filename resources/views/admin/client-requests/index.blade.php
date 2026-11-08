@extends('layouts.admin')

@section('title', 'Carikan Properti & Kebutuhan Klien - ' . \App\Models\Setting::getSiteName() . '')
@section('page_title', 'Carikan Properti & Database Kebutuhan Klien')

@section('content')
@php
    $currentAgent = auth()->user()->agent ?? \App\Models\Agent::first();
    $currentAgentName = $currentAgent ? $currentAgent->name : auth()->user()->name;
    $currentAgentOffice = $currentAgent ? $currentAgent->office_branch : \App\Models\Setting::getSiteName();
    $currentAgentPhone = $currentAgent ? $currentAgent->phone : '';
@endphp

<div x-data="clientRequestMatcherApp()" class="space-y-4">

    <!-- Top Header Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-950 via-slate-900 to-rose-950 p-5 md:p-6 text-white shadow-xl">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="space-y-1.5 max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-0.5 rounded-full bg-pink-500/20 text-pink-300 text-xs font-black tracking-wide border border-pink-400/30 uppercase">
                    <i class="fa-solid fa-wand-magic-sparkles text-pink-400"></i> Smart Property Matcher &amp; Buyer CRM
                </div>
                <h1 class="text-xl md:text-2xl font-black tracking-tight text-white">
                    Carikan Properti &amp; Kebutuhan Klien
                </h1>
                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed font-normal">
                    Pencarian cerdas rekomendasi unit cocok dan alternatif, broadcast penawaran ke WhatsApp calon pembeli/penyewa, serta database permintaan klien yang otomatis memantau listing baru masuk.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <button type="button"
                        @click="openNewClientModal()"
                        class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-pink-600 to-rose-600 text-white font-black text-xs shadow-md shadow-pink-600/30 hover:brightness-110 active:scale-95 transition-all flex items-center gap-2">
                    <i class="fa-solid fa-plus-circle text-xs"></i>
                    <span>Simpan Request Klien Baru</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Overview Ribbon -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="p-3 bg-white rounded-2xl border border-pink-100 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-pink-50 text-pink-600 flex items-center justify-center text-base font-bold shrink-0">
                <i class="fa-solid fa-users-viewfinder"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Permintaan</div>
                <div class="text-lg font-black text-slate-900">{{ number_format($totalRequests) }}</div>
            </div>
        </div>

        <div class="p-3 bg-white rounded-2xl border border-pink-100 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base font-bold shrink-0">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Sedang Mencari</div>
                <div class="text-lg font-black text-amber-600">{{ number_format($activeRequests) }}</div>
            </div>
        </div>

        <div class="p-3 bg-white rounded-2xl border border-pink-100 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base font-bold shrink-0">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Deal / Closing</div>
                <div class="text-lg font-black text-emerald-600">{{ number_format($dealtRequests) }}</div>
            </div>
        </div>

        <div class="p-3 bg-white rounded-2xl border border-pink-100 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-base font-bold shrink-0">
                <i class="fa-solid fa-bell"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Unit Listing Aktif</div>
                <div class="text-lg font-black text-rose-600">{{ \App\Models\Property::where('status', 'active')->count() }}</div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-pink-100 pb-3">
        <button type="button"
                @click="tab = 'matcher'"
                :class="tab === 'matcher' ? 'bg-pink-600 text-white shadow-md shadow-pink-600/20' : 'bg-white text-slate-600 hover:text-pink-600 hover:bg-pink-50 border border-slate-200'"
                class="px-5 py-2.5 rounded-xl font-bold text-xs sm:text-sm flex items-center gap-2 transition-all">
            <i class="fa-solid fa-magnifying-glass-chart"></i>
            <span>Carikan Properti &amp; Rekomendasi WA</span>
            @if($hasSearch && $matcherStats['exact_count'] > 0)
                <span class="bg-white/20 text-white text-[10px] px-1.5 py-0.5 rounded-full font-black">
                    {{ $matcherStats['exact_count'] }} Cocok
                </span>
            @endif
        </button>

        <button type="button"
                @click="tab = 'requests'"
                :class="tab === 'requests' ? 'bg-pink-600 text-white shadow-md shadow-pink-600/20' : 'bg-white text-slate-600 hover:text-pink-600 hover:bg-pink-50 border border-slate-200'"
                class="px-5 py-2.5 rounded-xl font-bold text-xs sm:text-sm flex items-center gap-2 transition-all">
            <i class="fa-solid fa-address-book"></i>
            <span>Database Kebutuhan Klien ({{ $totalRequests }})</span>
            @if($activeRequests > 0)
                <span class="bg-amber-100 text-amber-800 text-[10px] px-1.5 py-0.5 rounded-full font-black border border-amber-200">
                    {{ $activeRequests }} Aktif
                </span>
            @endif
        </button>
    </div>

    <!-- ==================================================================== -->
    <!-- TAB 1: CARIKAN PROPERTI & REKOMENDASI WA                             -->
    <!-- ==================================================================== -->
    <div x-show="tab === 'matcher'" class="space-y-6">

        <!-- Search & Filter Card -->
        <div class="bg-white rounded-3xl p-6 border border-pink-100 shadow-sm space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-pink-100 text-pink-600 flex items-center justify-center font-black">
                        <i class="fa-solid fa-filter"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-black text-slate-900">Form Kriteria Calon Klien</h2>
                        <p class="text-xs text-slate-400">Masukkan kriteria properti yang dicari klien untuk menemukan unit cocok dan rekomendasi alternatif.</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.client-requests.index', ['tab' => 'matcher']) }}"
                       class="px-3 py-1.5 rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 text-xs font-semibold flex items-center gap-1.5 transition-all">
                        <i class="fa-solid fa-rotate-left"></i>
                        <span>Reset Form</span>
                    </a>
                </div>
            </div>

            <form id="matcherForm" action="{{ route('admin.client-requests.index') }}" method="GET" class="space-y-5">
                <input type="hidden" name="tab" value="matcher">
                <input type="hidden" name="search" value="1">

                <!-- Row 1: Informasi Calon Klien (Untuk WA) -->
                <div class="bg-pink-50/50 rounded-2xl p-4 border border-pink-100">
                    <div class="text-[11px] font-extrabold uppercase tracking-wider text-pink-700 mb-3 flex items-center gap-2">
                        <i class="fa-brands fa-whatsapp text-emerald-600 text-sm"></i>
                        <span>Informasi Calon Klien (Untuk Personalisasi Pesan WhatsApp)</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Calon Klien</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-user text-xs"></i>
                                </span>
                                <input type="text"
                                       name="client_name"
                                       x-model="clientName"
                                       value="{{ $criteria['client_name'] }}"
                                       placeholder="Contoh: Bpk. Budi Santoso"
                                       class="w-full pl-9 pr-3 py-2.5 bg-white border border-pink-200 rounded-xl text-xs sm:text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-pink-500/20 focus:border-pink-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">No. WhatsApp Calon Klien</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-emerald-600">
                                    <i class="fa-brands fa-whatsapp text-sm"></i>
                                </span>
                                <input type="text"
                                       name="client_phone"
                                       x-model="clientPhone"
                                       value="{{ $criteria['client_phone'] }}"
                                       placeholder="Contoh: 081234567890"
                                       class="w-full pl-9 pr-3 py-2.5 bg-white border border-pink-200 rounded-xl text-xs sm:text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-pink-500/20 focus:border-pink-500">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 2: Tipe Transaksi & Tipe Properti -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Tipe Transaksi -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tipe Transaksi</label>
                        <select name="listing_type"
                                class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-pink-500/20 focus:border-pink-500">
                            <option value="dijual" {{ $criteria['listing_type'] === 'dijual' ? 'selected' : '' }}>Dijual (Beli)</option>
                            <option value="disewa" {{ $criteria['listing_type'] === 'disewa' ? 'selected' : '' }}>Disewa (Sewa)</option>
                            <option value="semua" {{ $criteria['listing_type'] === 'semua' ? 'selected' : '' }}>Semua (Jual / Sewa)</option>
                        </select>
                    </div>

                    <!-- Tipe Properti -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tipe Properti</label>
                        <select name="property_type"
                                class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-pink-500/20 focus:border-pink-500">
                            <option value="">Semua Tipe Properti</option>
                            <option value="rumah" {{ $criteria['property_type'] === 'rumah' ? 'selected' : '' }}>Rumah</option>
                            <option value="apartemen" {{ $criteria['property_type'] === 'apartemen' ? 'selected' : '' }}>Apartemen</option>
                            <option value="ruko" {{ $criteria['property_type'] === 'ruko' ? 'selected' : '' }}>Ruko / Shophouse</option>
                            <option value="tanah" {{ $criteria['property_type'] === 'tanah' ? 'selected' : '' }}>Tanah Kavling</option>
                            <option value="gudang" {{ $criteria['property_type'] === 'gudang' ? 'selected' : '' }}>Gudang &amp; Pabrik</option>
                            <option value="villa" {{ $criteria['property_type'] === 'villa' ? 'selected' : '' }}>Villa</option>
                            <option value="kantor" {{ $criteria['property_type'] === 'kantor' ? 'selected' : '' }}>Kantor / Office</option>
                        </select>
                    </div>

                    <!-- Kota -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kota</label>
                        <select name="city_id"
                                class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-pink-500/20 focus:border-pink-500">
                            <option value="">Semua Kota</option>
                            @foreach($cities as $c)
                                <option value="{{ $c->id }}" {{ (string)$criteria['city_id'] === (string)$c->id ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Area / Kawasan / Kecamatan -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kecamatan / Area / Kawasan</label>
                        <input type="text"
                               name="district"
                               value="{{ $criteria['district'] }}"
                               placeholder="Cth: Rungkut, Menteng, BSD"
                               class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-pink-500/20 focus:border-pink-500">
                    </div>
                </div>

                <!-- Row 3: Budget Range & Quick Presets -->
                <div class="space-y-2">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <label class="block text-xs font-bold text-slate-700">Rentang Budget Klien</label>
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-[10px] text-slate-400 font-semibold">Preset Cepat:</span>
                            <button type="button" @click="setBudget(0, 500000000)" class="text-[10px] sm:text-xs px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-pink-100 hover:text-pink-700 font-bold text-slate-600 transition-all">&lt; 500 Jt</button>
                            <button type="button" @click="setBudget(500000000, 1000000000)" class="text-[10px] sm:text-xs px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-pink-100 hover:text-pink-700 font-bold text-slate-600 transition-all">500 Jt - 1 M</button>
                            <button type="button" @click="setBudget(1000000000, 2000000000)" class="text-[10px] sm:text-xs px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-pink-100 hover:text-pink-700 font-bold text-slate-600 transition-all">1 M - 2 M</button>
                            <button type="button" @click="setBudget(2000000000, 5000000000)" class="text-[10px] sm:text-xs px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-pink-100 hover:text-pink-700 font-bold text-slate-600 transition-all">2 M - 5 M</button>
                            <button type="button" @click="setBudget(5000000000, null)" class="text-[10px] sm:text-xs px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-pink-100 hover:text-pink-700 font-bold text-slate-600 transition-all">&gt; 5 M</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 font-bold text-xs">Rp</span>
                            <input type="number"
                                   id="inputMinPrice"
                                   name="min_price"
                                   x-model="minPrice"
                                   value="{{ $criteria['min_price'] }}"
                                   placeholder="Budget Minimal (cth: 500000000)"
                                   class="w-full pl-10 pr-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-pink-500/20 focus:border-pink-500">
                        </div>

                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 font-bold text-xs">Rp</span>
                            <input type="number"
                                   id="inputMaxPrice"
                                   name="max_price"
                                   x-model="maxPrice"
                                   value="{{ $criteria['max_price'] }}"
                                   placeholder="Budget Maksimal (cth: 1500000000)"
                                   class="w-full pl-10 pr-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-pink-500/20 focus:border-pink-500">
                        </div>
                    </div>
                </div>

                <!-- Row 4: Spesifikasi Kamar, Luas, Proyek & Tower -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Min. Kamar Tidur</label>
                        <select name="bedrooms"
                                class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-pink-500/20 focus:border-pink-500">
                            <option value="">Bebas</option>
                            <option value="1" {{ (string)$criteria['bedrooms'] === '1' ? 'selected' : '' }}>1+ KT</option>
                            <option value="2" {{ (string)$criteria['bedrooms'] === '2' ? 'selected' : '' }}>2+ KT</option>
                            <option value="3" {{ (string)$criteria['bedrooms'] === '3' ? 'selected' : '' }}>3+ KT</option>
                            <option value="4" {{ (string)$criteria['bedrooms'] === '4' ? 'selected' : '' }}>4+ KT</option>
                            <option value="5" {{ (string)$criteria['bedrooms'] === '5' ? 'selected' : '' }}>5+ KT</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Min. Kamar Mandi</label>
                        <select name="bathrooms"
                                class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-pink-500/20 focus:border-pink-500">
                            <option value="">Bebas</option>
                            <option value="1" {{ (string)$criteria['bathrooms'] === '1' ? 'selected' : '' }}>1+ KM</option>
                            <option value="2" {{ (string)$criteria['bathrooms'] === '2' ? 'selected' : '' }}>2+ KM</option>
                            <option value="3" {{ (string)$criteria['bathrooms'] === '3' ? 'selected' : '' }}>3+ KM</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Min. Luas Tanah (m²)</label>
                        <input type="number"
                               name="min_land_area"
                               value="{{ $criteria['min_land_area'] }}"
                               placeholder="Cth: 90"
                               class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-pink-500/20 focus:border-pink-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Proyek / Gedung</label>
                        <select name="project_id"
                                class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-pink-500/20 focus:border-pink-500">
                            <option value="">Semua Proyek</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}" {{ (string)$criteria['project_id'] === (string)$p->id ? 'selected' : '' }}>
                                    {{ $p->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tower (Khusus Apt)</label>
                        <input type="text"
                               name="tower"
                               value="{{ $criteria['tower'] }}"
                               placeholder="Cth: Tower A"
                               class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-pink-500/20 focus:border-pink-500">
                    </div>
                </div>

                <!-- Action Button Row -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-slate-100">
                    <p class="text-xs text-slate-400">
                        <i class="fa-solid fa-lightbulb text-amber-500 mr-1"></i>
                        Sistem akan mencari unit yang cocok sempurna serta alternatif terdekat.
                    </p>

                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <button type="button"
                                @click="saveDirectToDatabase()"
                                class="flex-1 sm:flex-initial px-4 py-2.5 rounded-xl border border-pink-300 text-pink-700 hover:bg-pink-50 text-xs sm:text-sm font-bold flex items-center justify-center gap-2 transition-all">
                            <i class="fa-solid fa-floppy-disk text-pink-600"></i>
                            <span>Simpan Kebutuhan Klien Ini</span>
                        </button>

                        <button type="submit"
                                class="flex-1 sm:flex-initial px-6 py-2.5 rounded-xl bg-gradient-to-r from-pink-600 to-rose-600 text-white text-xs sm:text-sm font-black shadow-md shadow-pink-600/30 hover:brightness-110 active:scale-95 transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <span>Cari Properti Cocok</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- ============================================================= -->
        <!-- HASIL PENCARIAN & ALTERNATIF                                  -->
        <!-- ============================================================= -->
        @if($hasSearch)
            <div class="space-y-6">

                <!-- Case 1: EXACT MATCHES FOUND -->
                @if($exactMatches->isNotEmpty())
                    <div class="bg-white rounded-3xl p-6 border border-emerald-100 shadow-sm space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-emerald-50 pb-3">
                            <div class="flex items-center gap-2.5">
                                <span class="w-3 h-3 rounded-full bg-emerald-500 animate-ping"></span>
                                <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                                    <span>Rekomendasi Cocok Sempurna (Exact Match)</span>
                                    <span class="bg-emerald-100 text-emerald-800 text-xs px-2 py-0.5 rounded-full font-black">
                                        {{ $exactMatches->count() }} Unit
                                    </span>
                                </h3>
                            </div>
                            <div class="text-xs text-slate-500">
                                Centang unit di bawah untuk memasukkannya ke template pesan WhatsApp.
                            </div>
                        </div>

                        <!-- Cards Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($exactMatches as $prop)
                                @php
                                    $propPayload = [
                                        'id' => $prop->id,
                                        'title' => $prop->title,
                                        'price' => $prop->formatted_price,
                                        'location' => trim(($prop->district ? $prop->district . ', ' : '') . $prop->city),
                                        'specs' => $prop->isApartemen() ? ($prop->full_unit_info ?: 'Apartemen') : ($prop->bedrooms . ' KT • ' . $prop->bathrooms . ' KM • LT: ' . ($prop->land_area ?: '-') . 'm² • LB: ' . ($prop->building_area ?: '-') . 'm²'),
                                        'url' => route('properties.show', $prop->slug),
                                        'code' => $prop->property_code,
                                    ];
                                @endphp
                                <div class="relative group bg-white rounded-2xl border-2 transition-all p-4 flex flex-col justify-between"
                                     :class="isPropertySelected({{ $prop->id }}) ? 'border-pink-600 bg-pink-50/20 shadow-md ring-2 ring-pink-500/20' : 'border-slate-200 hover:border-pink-300'">

                                    <!-- Top Selection Checkbox -->
                                    <div class="flex items-start justify-between gap-3 mb-3">
                                        <label class="flex items-center gap-2 cursor-pointer select-none">
                                            <input type="checkbox"
                                                   @change="toggleProperty({{ json_encode($propPayload) }})"
                                                   :checked="isPropertySelected({{ $prop->id }})"
                                                   class="w-5 h-5 rounded-lg border-slate-300 text-pink-600 focus:ring-pink-500 cursor-pointer">
                                            <span class="text-xs font-black" :class="isPropertySelected({{ $prop->id }}) ? 'text-pink-600' : 'text-slate-700'">
                                                Pilih untuk WA
                                            </span>
                                        </label>

                                        <span class="text-[10px] font-mono font-bold bg-slate-100 text-slate-600 px-2 py-0.5 rounded-md">
                                            {{ $prop->property_code }}
                                        </span>
                                    </div>

                                    <!-- Thumbnail & Info -->
                                    <div class="flex gap-3 mb-3">
                                        <div class="w-24 h-24 rounded-xl overflow-hidden bg-slate-100 shrink-0 relative">
                                            <img src="{{ $prop->primary_image }}" alt="{{ $prop->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-all duration-300">
                                            <span class="absolute top-1 left-1 text-[9px] font-black uppercase px-1.5 py-0.5 rounded-md bg-slate-900/80 text-white">
                                                {{ $prop->listing_type === 'disewa' ? 'Sewa' : 'Jual' }}
                                            </span>
                                        </div>

                                        <div class="min-w-0 flex-1 space-y-1">
                                            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                                                {{ ucfirst($prop->property_type) }} • {{ $prop->city }}
                                            </div>
                                            <h4 class="text-xs sm:text-sm font-black text-slate-900 line-clamp-2 leading-tight">
                                                {{ $prop->title }}
                                            </h4>
                                            <div class="text-sm sm:text-base font-black text-pink-600">
                                                {{ $prop->formatted_price }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Specs -->
                                    <div class="pt-2 border-t border-slate-100 text-[11px] font-medium text-slate-600 flex items-center justify-between">
                                        <div class="truncate mr-2">
                                            @if($prop->isApartemen())
                                                <i class="fa-solid fa-building text-pink-500 mr-1"></i>
                                                {{ $prop->formatted_tower ?: 'Tower' }} • {{ $prop->formatted_floor_short ?: 'Lt' }} • Unit {{ $prop->unit_number ?: '-' }}
                                            @else
                                                <i class="fa-solid fa-bed text-pink-500 mr-1"></i> {{ $prop->bedrooms ?: '-' }} KT
                                                <span class="mx-1">•</span>
                                                <i class="fa-solid fa-bath text-pink-500 mr-1"></i> {{ $prop->bathrooms ?: '-' }} KM
                                                @if($prop->building_area)
                                                    <span class="mx-1">•</span> LB {{ $prop->building_area }}m²
                                                @endif
                                            @endif
                                        </div>

                                        <a href="{{ route('properties.show', $prop->slug) }}" target="_blank" class="text-pink-600 hover:underline font-bold shrink-0 text-[11px] flex items-center gap-1">
                                            <span>Detail</span>
                                            <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Case 2: ALTERNATIVES FOUND -->
                @if($alternativeMatches->isNotEmpty())
                    <div class="bg-white rounded-3xl p-6 border border-amber-100 shadow-sm space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-amber-50 pb-3">
                            <div class="flex items-center gap-2.5">
                                <span class="w-3 h-3 rounded-full bg-amber-400"></span>
                                <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                                    <span>Rekomendasi Alternatif Serupa</span>
                                    <span class="bg-amber-100 text-amber-800 text-xs px-2 py-0.5 rounded-full font-black">
                                        {{ $alternativeMatches->count() }} Pilihan Lain
                                    </span>
                                </h3>
                            </div>
                            <div class="text-xs text-slate-500">
                                Properti di lokasi sama atau rentang harga fleksibel (+25%) sebagai opsi tambahan ke klien.
                            </div>
                        </div>

                        <!-- Cards Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($alternativeMatches as $prop)
                                @php
                                    $propPayload = [
                                        'id' => $prop->id,
                                        'title' => $prop->title,
                                        'price' => $prop->formatted_price,
                                        'location' => trim(($prop->district ? $prop->district . ', ' : '') . $prop->city),
                                        'specs' => $prop->isApartemen() ? ($prop->full_unit_info ?: 'Apartemen') : ($prop->bedrooms . ' KT • ' . $prop->bathrooms . ' KM • LT: ' . ($prop->land_area ?: '-') . 'm² • LB: ' . ($prop->building_area ?: '-') . 'm²'),
                                        'url' => route('properties.show', $prop->slug),
                                        'code' => $prop->property_code,
                                    ];
                                @endphp
                                <div class="relative group bg-white rounded-2xl border-2 transition-all p-4 flex flex-col justify-between"
                                     :class="isPropertySelected({{ $prop->id }}) ? 'border-pink-600 bg-pink-50/20 shadow-md ring-2 ring-pink-500/20' : 'border-slate-200 hover:border-pink-300'">

                                    <div class="flex items-start justify-between gap-3 mb-3">
                                        <label class="flex items-center gap-2 cursor-pointer select-none">
                                            <input type="checkbox"
                                                   @change="toggleProperty({{ json_encode($propPayload) }})"
                                                   :checked="isPropertySelected({{ $prop->id }})"
                                                   class="w-5 h-5 rounded-lg border-slate-300 text-pink-600 focus:ring-pink-500 cursor-pointer">
                                            <span class="text-xs font-black" :class="isPropertySelected({{ $prop->id }}) ? 'text-pink-600' : 'text-slate-700'">
                                                Pilih untuk WA
                                            </span>
                                        </label>

                                        <span class="text-[10px] font-mono font-bold bg-amber-50 text-amber-700 border border-amber-200 px-2 py-0.5 rounded-md">
                                            Alternatif
                                        </span>
                                    </div>

                                    <div class="flex gap-3 mb-3">
                                        <div class="w-24 h-24 rounded-xl overflow-hidden bg-slate-100 shrink-0 relative">
                                            <img src="{{ $prop->primary_image }}" alt="{{ $prop->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-all duration-300">
                                            <span class="absolute top-1 left-1 text-[9px] font-black uppercase px-1.5 py-0.5 rounded-md bg-slate-900/80 text-white">
                                                {{ $prop->listing_type === 'disewa' ? 'Sewa' : 'Jual' }}
                                            </span>
                                        </div>

                                        <div class="min-w-0 flex-1 space-y-1">
                                            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                                                {{ ucfirst($prop->property_type) }} • {{ $prop->city }}
                                            </div>
                                            <h4 class="text-xs sm:text-sm font-black text-slate-900 line-clamp-2 leading-tight">
                                                {{ $prop->title }}
                                            </h4>
                                            <div class="text-sm sm:text-base font-black text-amber-600">
                                                {{ $prop->formatted_price }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="pt-2 border-t border-slate-100 text-[11px] font-medium text-slate-600 flex items-center justify-between">
                                        <div class="truncate mr-2">
                                            @if($prop->isApartemen())
                                                <i class="fa-solid fa-building text-amber-500 mr-1"></i>
                                                {{ $prop->formatted_tower ?: 'Tower' }} • {{ $prop->formatted_floor_short ?: 'Lt' }}
                                            @else
                                                <i class="fa-solid fa-bed text-amber-500 mr-1"></i> {{ $prop->bedrooms ?: '-' }} KT
                                                <span class="mx-1">•</span>
                                                <i class="fa-solid fa-bath text-amber-500 mr-1"></i> {{ $prop->bathrooms ?: '-' }} KM
                                            @endif
                                        </div>

                                        <a href="{{ route('properties.show', $prop->slug) }}" target="_blank" class="text-pink-600 hover:underline font-bold shrink-0 text-[11px] flex items-center gap-1">
                                            <span>Detail</span>
                                            <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Case 3: ZERO RESULTS (TIDAK ADA HASIL) -->
                @if($exactMatches->isEmpty() && $alternativeMatches->isEmpty())
                    <div class="bg-gradient-to-br from-white to-pink-50/50 rounded-3xl p-8 border-2 border-dashed border-pink-200 text-center space-y-4 shadow-sm">
                        <div class="w-16 h-16 rounded-full bg-pink-100 text-pink-600 flex items-center justify-center text-2xl mx-auto shadow-inner">
                            <i class="fa-solid fa-magnifying-glass-chart"></i>
                        </div>
                        <div class="max-w-md mx-auto space-y-2">
                            <h3 class="text-lg font-black text-slate-900">Belum Ada Properti yang Cocok Saat Ini</h3>
                            <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">
                                Jangan khawatir! Simpan kriteria pencarian ini ke database kebutuhan klien. Sistem akan otomatis memantau, dan jika di kemudian hari ada listing baru yang sesuai, Anda bisa langsung menghubungi klien ini via WhatsApp!
                            </p>
                        </div>

                        <div class="pt-2">
                            <button type="button"
                                    @click="saveDirectToDatabase()"
                                    class="px-6 py-3 rounded-2xl bg-gradient-to-r from-pink-600 to-rose-600 text-white font-black text-xs sm:text-sm shadow-lg shadow-pink-600/30 hover:brightness-110 active:scale-95 transition-all inline-flex items-center gap-2">
                                <i class="fa-solid fa-floppy-disk text-base"></i>
                                <span>Simpan Kebutuhan <span x-text="clientName ? clientName : 'Klien'"></span> ke Database</span>
                            </button>
                        </div>
                    </div>
                @endif

                <!-- ============================================================= -->
                <!-- WHATSAPP BROADCAST DRAFT PANEL (LIVE PREVIEW & SHARE)        -->
                <!-- ============================================================= -->
                <div class="bg-white rounded-3xl p-6 border border-pink-200 shadow-lg space-y-4 sticky bottom-4 z-20 ring-4 ring-pink-500/10">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-pink-100 pb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-emerald-500 text-white flex items-center justify-center font-black shadow-md shadow-emerald-500/30">
                                <i class="fa-brands fa-whatsapp text-lg"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-black text-slate-900 flex items-center gap-2">
                                    <span>Pesan Rekomendasi WhatsApp ke Calon Klien</span>
                                    <span class="bg-emerald-100 text-emerald-800 text-[10px] px-2 py-0.5 rounded-full font-black">
                                        <span x-text="selectedProperties.length"></span> Unit Terpilih
                                    </span>
                                </h4>
                                <p class="text-[11px] text-slate-500">Pesan otomatis ter-update saat Anda mencentang unit properti di atas.</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button"
                                    @click="copyWhatsAppText()"
                                    class="px-3 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs flex items-center gap-1.5 transition-all">
                                <i class="fa-solid" :class="copied ? 'fa-check text-emerald-600' : 'fa-copy text-slate-500'"></i>
                                <span x-text="copied ? 'Tersalin!' : 'Salin Teks'"></span>
                            </button>

                            <a :href="whatsappUrl"
                               target="_blank"
                               class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs shadow-md shadow-emerald-600/30 flex items-center gap-2 transition-all">
                                <i class="fa-brands fa-whatsapp text-base"></i>
                                <span>Buka WhatsApp Sekarang</span>
                            </a>
                        </div>
                    </div>

                    <!-- Live Message Preview Box -->
                    <div class="relative">
                        <textarea x-model="generatedMessage"
                                  rows="6"
                                  class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-mono text-slate-800 leading-relaxed focus:bg-white focus:outline-none focus:ring-2 focus:ring-pink-500/20"></textarea>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-slate-400">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>Target Klien: <strong class="text-slate-700" x-text="clientName || 'Calon Klien'"></strong> (<span x-text="clientPhone || 'No WA belum diisi'"></span>)</span>
                        </div>
                        <div class="text-[11px]">
                            Agen: <strong class="text-slate-700">{{ $currentAgentName }}</strong> ({{ $currentAgentOffice }})
                        </div>
                    </div>
                </div>

            </div>
        @endif

    </div>

    <!-- ==================================================================== -->
    <!-- TAB 2: DATABASE KEBUTUHAN KLIEN (CRM & AUTO-MATCHER)                 -->
    <!-- ==================================================================== -->
    <div x-show="tab === 'requests'" class="space-y-6">

        <!-- Filter & Search Toolbar -->
        <div class="bg-white rounded-3xl p-5 border border-pink-100 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('admin.client-requests.index', ['tab' => 'requests', 'status' => 'all']) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all {{ request('status') === 'all' ? 'bg-pink-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-pink-50 hover:text-pink-600' }}">
                    Semua ({{ $totalRequests }})
                </a>
                <a href="{{ route('admin.client-requests.index', ['tab' => 'requests', 'status' => 'active']) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all {{ request('status', 'active') === 'active' ? 'bg-amber-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-amber-50 hover:text-amber-600' }}">
                    Sedang Mencari ({{ $activeRequests }})
                </a>
                <a href="{{ route('admin.client-requests.index', ['tab' => 'requests', 'status' => 'dealt']) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all {{ request('status') === 'dealt' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-emerald-50 hover:text-emerald-600' }}">
                    Deal / Selesai ({{ $dealtRequests }})
                </a>
                <a href="{{ route('admin.client-requests.index', ['tab' => 'requests', 'status' => 'cancelled']) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all {{ request('status') === 'cancelled' ? 'bg-slate-700 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Batal
                </a>
            </div>

            <!-- Search Field -->
            <form action="{{ route('admin.client-requests.index') }}" method="GET" class="flex items-center gap-2 w-full md:w-80">
                <input type="hidden" name="tab" value="requests">
                <input type="hidden" name="status" value="{{ request('status', 'active') }}">
                <div class="relative w-full">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                    <input type="text"
                           name="q"
                           value="{{ request('q') }}"
                           placeholder="Cari nama klien / no WA / kota..."
                           class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-pink-500/20 focus:border-pink-500">
                </div>
                <button type="submit" class="px-3 py-2 bg-pink-600 text-white rounded-xl text-xs font-bold hover:bg-pink-700">
                    Cari
                </button>
            </form>
        </div>

        <!-- Client Requests Table -->
        <div class="bg-white rounded-3xl border border-pink-100 shadow-sm overflow-hidden">
            @if($clientRequests->isEmpty())
                <div class="p-12 text-center space-y-3">
                    <div class="w-14 h-14 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-xl mx-auto">
                        <i class="fa-solid fa-address-book"></i>
                    </div>
                    <h3 class="text-base font-black text-slate-800">Belum Ada Kebutuhan Klien Tersimpan</h3>
                    <p class="text-xs text-slate-400 max-w-md mx-auto">
                        Simpan kebutuhan calon klien dari tab "Carikan Properti" atau klik tombol "Simpan Request Klien Baru" di atas.
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100 text-[10px] font-black uppercase tracking-wider text-slate-400">
                                <th class="py-4 px-5">Calon Klien</th>
                                <th class="py-4 px-5">Kriteria Dicari</th>
                                <th class="py-4 px-5">Rentang Budget</th>
                                <th class="py-4 px-5">Status Unit Cocok (Live Match)</th>
                                <th class="py-4 px-5">Agen / Tim</th>
                                <th class="py-4 px-5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                            @foreach($clientRequests as $req)
                                @php
                                    $matchCount = $req->matching_properties_count;
                                @endphp
                                <tr class="hover:bg-pink-50/30 transition-all">
                                    <!-- Calon Klien -->
                                    <td class="py-4 px-5">
                                        <div class="space-y-1">
                                            <div class="font-black text-slate-900 text-sm flex items-center gap-2">
                                                <span>{{ $req->client_name }}</span>
                                                @if($req->status === 'dealt')
                                                    <span class="bg-emerald-100 text-emerald-800 text-[9px] px-1.5 py-0.2 rounded-full font-black uppercase">Closing</span>
                                                @elseif($req->status === 'cancelled')
                                                    <span class="bg-slate-100 text-slate-500 text-[9px] px-1.5 py-0.2 rounded-full font-black uppercase">Batal</span>
                                                @endif
                                            </div>

                                            <div class="flex items-center gap-2 text-slate-500 font-mono text-[11px]">
                                                <a href="https://api.whatsapp.com/send?phone={{ $req->clean_phone }}" target="_blank" class="text-emerald-600 hover:text-emerald-700 font-bold flex items-center gap-1">
                                                    <i class="fa-brands fa-whatsapp text-xs"></i>
                                                    <span>{{ $req->client_phone }}</span>
                                                </a>
                                            </div>

                                            <div class="text-[10px] text-slate-400">
                                                Input: {{ $req->created_at->format('d M Y') }}
                                                @if($req->last_followed_up_at)
                                                    • Follow-up: <span class="text-emerald-600 font-semibold">{{ $req->last_followed_up_at->diffForHumans() }}</span>
                                                @endif
                                            </div>

                                            @if($req->notes)
                                                <div class="text-[11px] text-slate-500 italic bg-slate-50 p-1.5 rounded-lg border border-slate-100 line-clamp-2 max-w-xs">
                                                    "{{ $req->notes }}"
                                                </div>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Kriteria Dicari -->
                                    <td class="py-4 px-5">
                                        <div class="space-y-1 max-w-xs">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase {{ $req->listing_type === 'disewa' ? 'bg-amber-100 text-amber-800' : 'bg-pink-100 text-pink-800' }}">
                                                    {{ $req->listing_type_label }}
                                                </span>
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700">
                                                    {{ $req->property_type_label }}
                                                </span>
                                                @if($req->tower)
                                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-pink-50 text-pink-700 border border-pink-200">
                                                        {{ str_starts_with(strtolower($req->tower), 'tower') ? $req->tower : 'Tower ' . $req->tower }}
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="text-xs font-bold text-slate-800 flex items-center gap-1">
                                                <i class="fa-solid fa-location-dot text-pink-500 text-[10px]"></i>
                                                <span>{{ $req->location_label }}</span>
                                            </div>

                                            @if($req->min_bedrooms || $req->min_bathrooms)
                                                <div class="text-[10px] text-slate-400">
                                                    @if($req->min_bedrooms) {{ $req->min_bedrooms }}+ KT @endif
                                                    @if($req->min_bathrooms) • {{ $req->min_bathrooms }}+ KM @endif
                                                </div>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Rentang Budget -->
                                    <td class="py-4 px-5 whitespace-nowrap">
                                        <div class="font-black text-slate-900 text-xs sm:text-sm text-pink-600">
                                            {{ $req->budget_label }}
                                        </div>
                                    </td>

                                    <!-- Live Matching Status -->
                                    <td class="py-4 px-5">
                                        @if($matchCount > 0)
                                            <div class="space-y-1.5">
                                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 font-black text-xs border border-emerald-200 shadow-xs">
                                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                                    <span>{{ $matchCount }} Unit Cocok Siap Ditawarkan!</span>
                                                </div>
                                                <div>
                                                    <a href="{{ route('admin.client-requests.show', $req->id) }}"
                                                       class="text-xs font-bold text-pink-600 hover:text-pink-700 hover:underline flex items-center gap-1">
                                                        <span>Lihat Unit &amp; Chat WA</span>
                                                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        @else
                                            <div class="space-y-1">
                                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 font-bold text-[11px] border border-slate-200">
                                                    <i class="fa-solid fa-hourglass-half text-slate-400 text-[10px]"></i>
                                                    <span>Menunggu Listing Baru (0 Cocok)</span>
                                                </div>
                                                <div class="text-[10px] text-slate-400">
                                                    Sistem otomatis mendeteksi saat ada unit masuk
                                                </div>
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Agen Penanggung Jawab -->
                                    <td class="py-4 px-5 whitespace-nowrap">
                                        <div class="text-xs font-bold text-slate-900">
                                            {{ $req->agent ? $req->agent->name : ($req->user ? $req->user->name : 'Admin') }}
                                        </div>
                                        <div class="text-[10px] text-slate-400">
                                            {{ $req->agent ? $req->agent->office_branch : 'Pusat' }}
                                        </div>
                                    </td>

                                    <!-- Aksi -->
                                    <td class="py-4 px-5 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <!-- Tombol Chat WA -->
                                            <a href="{{ route('admin.client-requests.show', $req->id) }}"
                                               class="p-2 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white transition-all font-bold text-xs"
                                               title="Buka Rekomendasi & Chat WA">
                                                <i class="fa-brands fa-whatsapp text-sm"></i>
                                            </a>

                                            <!-- Tombol Edit Modal -->
                                            <button type="button"
                                                    @click="openEditModal({{ json_encode($req) }})"
                                                    class="p-2 rounded-xl bg-slate-100 text-slate-600 hover:bg-pink-50 hover:text-pink-600 transition-all font-bold text-xs"
                                                    title="Edit Kriteria Klien">
                                                <i class="fa-solid fa-pencil text-xs"></i>
                                            </button>

                                            <!-- Tombol Hapus -->
                                            <form action="{{ route('admin.client-requests.destroy', $req->id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus kebutuhan klien {{ addslashes($req->client_name) }}?')"
                                                  class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="p-2 rounded-xl bg-slate-100 text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition-all font-bold text-xs"
                                                        title="Hapus Permintaan">
                                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-slate-100">
                    {{ $clientRequests->links() }}
                </div>
            @endif
        </div>

    </div>

    <!-- ==================================================================== -->
    <!-- MODAL 1: SIMPAN PERMINTAAN KLIEN BARU                                -->
    <!-- ==================================================================== -->
    <div x-show="isNewClientModalOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="isNewClientModalOpen = false"
             class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 space-y-6 shadow-2xl border border-pink-100 max-h-[90vh] overflow-y-auto">

            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-pink-100 text-pink-600 flex items-center justify-center font-black">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900">Simpan Kebutuhan Klien Baru</h3>
                        <p class="text-xs text-slate-400">Database kriteria pencarian properti calon pembeli/penyewa.</p>
                    </div>
                </div>

                <button type="button" @click="isNewClientModalOpen = false" class="text-slate-400 hover:text-slate-600 p-2">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('admin.client-requests.store') }}" method="POST" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Calon Klien <span class="text-rose-500">*</span></label>
                        <input type="text"
                               name="client_name"
                               x-model="clientName"
                               required
                               placeholder="Cth: Bpk. Hendra"
                               class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-pink-500/20 focus:border-pink-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">No. WhatsApp <span class="text-rose-500">*</span></label>
                        <input type="text"
                               name="client_phone"
                               x-model="clientPhone"
                               required
                               placeholder="Cth: 08123456789"
                               class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-pink-500/20 focus:border-pink-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tipe Transaksi <span class="text-rose-500">*</span></label>
                        <select name="listing_type" required class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold">
                            <option value="dijual">Dijual (Beli)</option>
                            <option value="disewa">Disewa (Sewa)</option>
                            <option value="semua">Semua (Jual / Sewa)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tipe Properti</label>
                        <select name="property_type" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold">
                            <option value="">Semua Tipe Properti</option>
                            <option value="rumah">Rumah</option>
                            <option value="apartemen">Apartemen</option>
                            <option value="ruko">Ruko / Shophouse</option>
                            <option value="tanah">Tanah Kavling</option>
                            <option value="gudang">Gudang &amp; Pabrik</option>
                            <option value="villa">Villa</option>
                            <option value="kantor">Kantor</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kota</label>
                        <select name="city_id" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold">
                            <option value="">Semua Kota</option>
                            @foreach($cities as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kecamatan / Area</label>
                        <input type="text" name="district" placeholder="Cth: Surabaya Barat / Rungkut" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Proyek / Kompleks Gedung</label>
                        <select name="project_id" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold">
                            <option value="">Semua Proyek / Bebas</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tower (Khusus Apartemen)</label>
                        <input type="text" name="tower" placeholder="Cth: Tower A" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Budget Minimal (Rp)</label>
                        <input type="number" name="min_price" x-model="minPrice" placeholder="Cth: 500000000" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Budget Maksimal (Rp)</label>
                        <input type="number" name="max_price" x-model="maxPrice" placeholder="Cth: 1500000000" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Min. Kamar Tidur</label>
                        <input type="number" name="min_bedrooms" min="0" placeholder="Cth: 2" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Min. Kamar Mandi</label>
                        <input type="number" name="min_bathrooms" min="0" placeholder="Cth: 1" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Tambahan Preferensi Klien</label>
                    <textarea name="notes" rows="3" placeholder="Cth: Cari hadap timur, butuh carport 2 mobil, siap huni, mau KPR DP 10%..." class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold"></textarea>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="isNewClientModalOpen = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-pink-600 text-white font-black text-xs hover:bg-pink-700 shadow-md shadow-pink-600/30">
                        Simpan ke Database Klien
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================================================================== -->
    <!-- MODAL 2: EDIT PERMINTAAN KLIEN                                       -->
    <!-- ==================================================================== -->
    <div x-show="isEditModalOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="isEditModalOpen = false"
             class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 space-y-6 shadow-2xl border border-pink-100 max-h-[90vh] overflow-y-auto">

            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-pink-100 text-pink-600 flex items-center justify-center font-black">
                        <i class="fa-solid fa-pencil"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900">Edit Kebutuhan Klien</h3>
                        <p class="text-xs text-slate-400">Perbarui kriteria atau status tindak lanjut klien.</p>
                    </div>
                </div>

                <button type="button" @click="isEditModalOpen = false" class="text-slate-400 hover:text-slate-600 p-2">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form :action="'/admin/client-requests/' + (editingRequest ? editingRequest.id : '')" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Calon Klien <span class="text-rose-500">*</span></label>
                        <input type="text" name="client_name" :value="editingRequest ? editingRequest.client_name : ''" required class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">No. WhatsApp <span class="text-rose-500">*</span></label>
                        <input type="text" name="client_phone" :value="editingRequest ? editingRequest.client_phone : ''" required class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status Klien</label>
                        <select name="status" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold">
                            <option value="active" :selected="editingRequest && editingRequest.status === 'active'">Sedang Mencari (Aktif)</option>
                            <option value="dealt" :selected="editingRequest && editingRequest.status === 'dealt'">Closing / Deal</option>
                            <option value="cancelled" :selected="editingRequest && editingRequest.status === 'cancelled'">Batal</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tipe Transaksi</label>
                        <select name="listing_type" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold">
                            <option value="dijual" :selected="editingRequest && editingRequest.listing_type === 'dijual'">Dijual</option>
                            <option value="disewa" :selected="editingRequest && editingRequest.listing_type === 'disewa'">Disewa</option>
                            <option value="semua" :selected="editingRequest && editingRequest.listing_type === 'semua'">Semua</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tipe Properti</label>
                        <select name="property_type" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold">
                            <option value="">Semua</option>
                            <option value="rumah" :selected="editingRequest && editingRequest.property_type === 'rumah'">Rumah</option>
                            <option value="apartemen" :selected="editingRequest && editingRequest.property_type === 'apartemen'">Apartemen</option>
                            <option value="ruko" :selected="editingRequest && editingRequest.property_type === 'ruko'">Ruko</option>
                            <option value="tanah" :selected="editingRequest && editingRequest.property_type === 'tanah'">Tanah</option>
                            <option value="gudang" :selected="editingRequest && editingRequest.property_type === 'gudang'">Gudang</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Proyek / Gedung</label>
                        <select name="project_id" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold">
                            <option value="">Semua Proyek / Bebas</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}" :selected="editingRequest && editingRequest.project_id == {{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tower (Khusus Apartemen)</label>
                        <input type="text" name="tower" :value="editingRequest ? editingRequest.tower : ''" placeholder="Cth: Tower A" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Budget Minimal (Rp)</label>
                        <input type="number" name="min_price" :value="editingRequest ? editingRequest.min_price : ''" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Budget Maksimal (Rp)</label>
                        <input type="number" name="max_price" :value="editingRequest ? editingRequest.max_price : ''" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Klien</label>
                    <textarea name="notes" rows="3" :value="editingRequest ? editingRequest.notes : ''" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold"></textarea>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="isEditModalOpen = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-pink-600 text-white font-black text-xs hover:bg-pink-700 shadow-md shadow-pink-600/30">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<!-- Alpine.js Application Logic -->
<script>
function clientRequestMatcherApp() {
    return {
        tab: '{{ $activeTab }}',
        clientName: @json($criteria['client_name'] ?? ''),
        clientPhone: @json($criteria['client_phone'] ?? ''),
        minPrice: @json($criteria['min_price'] ?? null),
        maxPrice: @json($criteria['max_price'] ?? null),
        selectedProperties: [],
        copied: false,
        isNewClientModalOpen: false,
        isEditModalOpen: false,
        editingRequest: null,

        agentName: @json($currentAgentName),
        agentOffice: @json($currentAgentOffice),
        agentPhone: @json($currentAgentPhone),

        init() {
            // Auto select first 3 exact matches if available
            @if(!empty($exactPayload))
                this.selectedProperties = @json($exactPayload);
            @endif
        },

        setBudget(min, max) {
            this.minPrice = min;
            this.maxPrice = max;
            const minEl = document.getElementById('inputMinPrice');
            const maxEl = document.getElementById('inputMaxPrice');
            if (minEl) minEl.value = min || '';
            if (maxEl) maxEl.value = max || '';
        },

        isPropertySelected(id) {
            return this.selectedProperties.some(p => p.id === id);
        },

        toggleProperty(prop) {
            const index = this.selectedProperties.findIndex(p => p.id === prop.id);
            if (index > -1) {
                this.selectedProperties.splice(index, 1);
            } else {
                this.selectedProperties.push(prop);
            }
        },

        get generatedMessage() {
            const name = this.clientName ? this.clientName : 'Bpk/Ibu';
            const office = this.agentOffice ? this.agentOffice : '{{ \App\Models\Setting::getSiteName() }}';
            const agent = this.agentName ? this.agentName : 'Agent';

            let lines = [];
            lines.push(`Halo *${name}*,`);
            lines.push('');
            lines.push(`Perkenalkan saya *${agent}* dari *${office}*.`);
            lines.push(`Menindaklanjuti kebutuhan properti yang sedang Bapak/Ibu cari, berikut kami rekomendasikan beberapa pilihan unit terbaik:`);
            lines.push('');

            if (this.selectedProperties.length > 0) {
                this.selectedProperties.forEach((p, idx) => {
                    lines.push(`${idx + 1}️⃣ *${p.title}*`);
                    lines.push(`   📍 Lokasi: ${p.location}`);
                    lines.push(`   💵 Harga: *${p.price}*`);
                    if (p.specs) lines.push(`   📐 Spek: ${p.specs}`);
                    if (p.url) lines.push(`   🔗 Detail & Foto: ${p.url}`);
                    lines.push('');
                });
                lines.push(`Kapan ada waktu luang untuk kita jadwalkan survey lokasi bersama? Silakan hubungi saya untuk info lebih lanjut ya Pak/Bu. Terima kasih! 🙏`);
            } else {
                lines.push(`Saat ini kami sedang menyiapkan daftar rekomendasi unit terbaik yang sesuai dengan kriteria yang Bapak/Ibu cari.`);
                lines.push(`Segera setelah unit siap, saya akan kirimkan detail lengkap dan foto unitnya ke nomor WhatsApp ini ya Pak/Bu. 🙏`);
            }

            lines.push('');
            lines.push(`Salam,`);
            lines.push(`*${agent}* (${office})`);
            if (this.agentPhone) {
                lines.push(`WhatsApp: ${this.agentPhone}`);
            }

            return lines.join('\n');
        },

        get whatsappUrl() {
            let phone = (this.clientPhone || '').replace(/[^0-9]/g, '');
            if (phone.startsWith('0')) {
                phone = '62' + phone.substring(1);
            }
            const text = encodeURIComponent(this.generatedMessage);
            return phone ? `https://api.whatsapp.com/send?phone=${phone}&text=${text}` : `https://api.whatsapp.com/send?text=${text}`;
        },

        copyWhatsAppText() {
            navigator.clipboard.writeText(this.generatedMessage).then(() => {
                this.copied = true;
                setTimeout(() => { this.copied = false; }, 2500);
            });
        },

        saveDirectToDatabase() {
            this.isNewClientModalOpen = true;
        },

        openNewClientModal() {
            this.isNewClientModalOpen = true;
        },

        openEditModal(req) {
            this.editingRequest = req;
            this.isEditModalOpen = true;
        }
    };
}
</script>
@endsection
