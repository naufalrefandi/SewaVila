@extends('layouts.admin')

@section('title', 'Quick Stock & Share Unit Apartemen - ' . \App\Models\Setting::getSiteName() . '')
@section('page_title', 'Stock Ketersediaan Unit Apartemen & Quick Share WA')

@section('content')
@php
    $currentAgent = $agent ?? auth()->user()->agent ?? \App\Models\Agent::first();
    $agentName = $currentAgent ? $currentAgent->name : (auth()->user()->name ?? 'Official Agent');
    $agentPhone = $currentAgent ? $currentAgent->phone : '021-5098-8888';
    $agentCleanPhone = $currentAgent ? $currentAgent->clean_phone : '6281298765432';
    $agentOffice = $currentAgent ? $currentAgent->office_branch : \App\Models\Setting::getSiteName();
    $projectName = $selectedProject ? $selectedProject->name : 'Seluruh Apartemen ' . \App\Models\Setting::getSiteName();

    // Prepare JSON payload for Alpine.js
    $propertiesPayload = $properties->map(function($p) use ($currentAgent, $agentName, $agentPhone, $agentCleanPhone, $agentOffice) {
        $rentVal = $p->annual_rent_price;
        $saleVal = $p->direct_sale_price;
        $rentStr = $rentVal ? 'Rp ' . number_format($rentVal, 0, ',', '.') . ' / thn' : '-';
        $saleStr = $saleVal ? 'Rp ' . number_format($saleVal, 0, ',', '.') : '-';
        $monthlyStr = $p->monthly_rent_price ? ' (Rp ' . number_format($p->monthly_rent_price, 0, ',', '.') . '/bln)' : '';

        $shareUrl = $currentAgent ? route('properties.show', ['slug' => $p->slug, 'agent' => $currentAgent->slug]) : route('properties.show', $p->slug);

        return [
            'id' => $p->id,
            'title' => $p->title,
            'tower' => $p->formatted_tower ?: ($p->unit_tower ?: 'Tower A'),
            'tower_short' => $p->short_tower ?: 'A',
            'floor' => $p->formatted_floor ?: ($p->unit_floor ? 'Lantai ' . $p->unit_floor : 'Lantai ' . ($p->floors ?: '-')),
            'floor_num' => $p->floor_number ?: 0,
            'unit_number' => $p->formatted_unit_number ?: ($p->unit_number ?: '-'),
            'type' => $p->unit_type ?: ($p->property_type === 'ruko' ? 'Ruko' : ($p->bedrooms ? $p->bedrooms . ' BR' : 'Unit')),
            'area' => $p->building_area ? (float)$p->building_area : null,
            'cond' => $p->furnished_status_label,
            'view' => $p->unit_view ?: '-',
            'listing_type' => $p->listing_type,
            'rent_raw' => $rentVal,
            'rent_formatted' => $rentStr . $monthlyStr,
            'sale_raw' => $saleVal,
            'sale_formatted' => $saleStr,
            'status' => $p->status,
            'status_label' => strtoupper($p->status),
            'url' => $shareUrl,
            'project_name' => $p->project ? $p->project->name : 'Apartemen',
            'agent_name' => $agentName,
            'agent_phone' => $agentPhone,
            'agent_clean_phone' => $agentCleanPhone,
            'agent_office' => $agentOffice,
        ];
    })->values();
@endphp

<div x-data="apartmentStockApp()" class="space-y-6">

    <!-- Header Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-950 via-slate-900 to-rose-950 p-6 md:p-8 text-white shadow-xl">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-400/20 text-amber-300 text-xs font-black tracking-wide border border-amber-400/30 uppercase">
                    <i class="fa-solid fa-bolt text-amber-400"></i>
                    <span>Tabel Ketersediaan Unit &amp; Quick Share WhatsApp</span>
                </div>
                <h1 class="text-2xl md:text-3xl font-black tracking-tight text-white flex items-center gap-3">
                    <span>Stock Sheet: {{ $projectName }}</span>
                </h1>
                <p class="text-xs md:text-sm text-slate-300 max-w-2xl leading-relaxed">
                    Lihat ready lantai berapa, unit apa, kondisi &amp; harga sewa/jual dengan cepat. Centang unit yang cocok dengan permintaan klien, lalu 1-klik share rekap ke WhatsApp!
                </p>
            </div>

            <div class="flex items-center gap-3 flex-wrap">
                <button type="button"
                        @click="openQuickAddModal()"
                        class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-rose-600 to-amber-500 hover:from-rose-500 hover:to-amber-400 text-white font-extrabold text-xs shadow-lg transition-all flex items-center gap-2">
                    <i class="fa-solid fa-plus-circle"></i>
                    <span>+ Tambah Unit Cepat</span>
                </button>
                <a href="{{ route('admin.properties') }}" class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs border border-white/20 transition-all flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Semua Listing</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Stats Metric Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg font-bold">
                <i class="fa-solid fa-building"></i>
            </div>
            <div>
                <span class="text-[11px] font-semibold text-slate-400 block">Total Unit Terdaftar</span>
                <span class="text-xl font-black text-slate-900">{{ $totalUnits }} Unit</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg font-bold">
                <i class="fa-solid fa-key"></i>
            </div>
            <div>
                <span class="text-[11px] font-semibold text-slate-400 block">Unit Siap Sewa</span>
                <span class="text-xl font-black text-emerald-600">{{ $forRentCount }} Unit</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg font-bold">
                <i class="fa-solid fa-tag"></i>
            </div>
            <div>
                <span class="text-[11px] font-semibold text-slate-400 block">Unit Siap Jual</span>
                <span class="text-xl font-black text-rose-600">{{ $forSaleCount }} Unit</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg font-bold">
                <i class="fa-solid fa-store"></i>
            </div>
            <div>
                <span class="text-[11px] font-semibold text-slate-400 block">Ruko / Komersil</span>
                <span class="text-xl font-black text-purple-700">{{ $rukoCount }} Unit</span>
            </div>
        </div>
    </div>

    <!-- Filter & Project Selector Bar -->
    <div class="bg-white rounded-3xl border border-slate-200 p-5 shadow-xs space-y-4">
        <!-- Project Switcher Tabs / Dropdown -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center text-xs font-black">
                    <i class="fa-solid fa-hotel"></i>
                </span>
                <div>
                    <h3 class="font-extrabold text-slate-900 text-sm">Pilih Proyek / Gedung Apartemen:</h3>
                    <p class="text-[11px] text-slate-400">Pilih proyek untuk melihat stock sheet unit yang tersedia</p>
                </div>
            </div>

            <form action="{{ route('admin.apartment-stock') }}" method="GET" class="flex items-center gap-2">
                <select name="project_id"
                        onchange="this.form.submit()"
                        class="px-4 py-2.5 bg-slate-50 hover:bg-slate-100 border border-slate-300 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-500 transition-all cursor-pointer">
                    <option value="all" {{ $selectedProjectId === 'all' ? 'selected' : '' }}>🏢 Seluruh Proyek Apartemen (Semua)</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ (string)$selectedProjectId === (string)$p->id ? 'selected' : '' }}>
                            {{ $p->name }} ({{ $p->apartments_count }} unit ready)
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

        <!-- Secondary Filters (Tower, Floor, Tipe, Transaksi, Kondisi) -->
        <form action="{{ route('admin.apartment-stock') }}" method="GET" class="flex flex-wrap items-center gap-2 text-xs">
            <input type="hidden" name="project_id" value="{{ $selectedProjectId }}">

            <!-- Tower Filter -->
            @if($availableTowers->isNotEmpty())
                <select name="tower" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl font-semibold text-slate-700 focus:outline-none">
                    <option value="">Semua Tower</option>
                    @foreach($availableTowers as $tow)
                        <option value="{{ $tow }}" {{ request('tower') === $tow ? 'selected' : '' }}>{{ $tow }}</option>
                    @endforeach
                </select>
            @endif

            <!-- Unit Type Filter -->
            <select name="unit_type" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl font-semibold text-slate-700 focus:outline-none">
                <option value="">Semua Tipe Unit</option>
                <option value="Studio" {{ request('unit_type') === 'Studio' ? 'selected' : '' }}>Studio</option>
                <option value="1 BR" {{ request('unit_type') === '1 BR' ? 'selected' : '' }}>1 BR</option>
                <option value="2 BR" {{ request('unit_type') === '2 BR' ? 'selected' : '' }}>2 BR</option>
                <option value="3 BR" {{ request('unit_type') === '3 BR' ? 'selected' : '' }}>3 BR</option>
                <option value="Ruko Apartemen 3 Lantai" {{ request('unit_type') === 'Ruko Apartemen 3 Lantai' ? 'selected' : '' }}>Ruko 3 Lantai</option>
                <option value="Ruko Apartemen 4 Lantai" {{ request('unit_type') === 'Ruko Apartemen 4 Lantai' ? 'selected' : '' }}>Ruko 4 Lantai</option>
            </select>

            <!-- Listing Type Filter -->
            <select name="listing_type" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl font-semibold text-slate-700 focus:outline-none">
                <option value="">Semua Transaksi</option>
                <option value="disewa" {{ request('listing_type') === 'disewa' ? 'selected' : '' }}>Disewa</option>
                <option value="dijual" {{ request('listing_type') === 'dijual' ? 'selected' : '' }}>Dijual</option>
            </select>

            <!-- Furnished Filter -->
            <select name="furnished_status" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl font-semibold text-slate-700 focus:outline-none">
                <option value="">Semua Kondisi</option>
                <option value="fully-furnished" {{ request('furnished_status') === 'fully-furnished' ? 'selected' : '' }}>Full Furnished</option>
                <option value="semi-furnished" {{ request('furnished_status') === 'semi-furnished' ? 'selected' : '' }}>Semi Furnished</option>
                <option value="unfurnished" {{ request('furnished_status') === 'unfurnished' ? 'selected' : '' }}>Kosongan (Unfurnished)</option>
            </select>

            <!-- Sort By -->
            <select name="sort" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl font-semibold text-slate-700 focus:outline-none">
                <option value="floor_asc" {{ request('sort', 'floor_asc') === 'floor_asc' ? 'selected' : '' }}>Urut: Lantai Terendah (5 &rarr; 39)</option>
                <option value="floor_desc" {{ request('sort') === 'floor_desc' ? 'selected' : '' }}>Urut: Lantai Tertinggi (39 &rarr; 5)</option>
                <option value="rent_asc" {{ request('sort') === 'rent_asc' ? 'selected' : '' }}>Urut: Sewa Termurah</option>
                <option value="rent_desc" {{ request('sort') === 'rent_desc' ? 'selected' : '' }}>Urut: Sewa Tertinggi</option>
                <option value="sale_asc" {{ request('sort') === 'sale_asc' ? 'selected' : '' }}>Urut: Jual Termurah</option>
            </select>

            @if(request()->hasAny(['tower', 'unit_type', 'listing_type', 'furnished_status', 'sort']))
                <a href="{{ route('admin.apartment-stock', ['project_id' => $selectedProjectId]) }}"
                   class="px-3 py-2 text-rose-600 hover:text-rose-700 font-bold transition-colors">
                    <i class="fa-solid fa-rotate-left mr-1"></i>Reset Filter
                </a>
            @endif
        </form>
    </div>

    <!-- Stock Sheet Table (Mirip Spreadsheet Screenshot Klien!) -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-md overflow-hidden">
        <!-- Table Toolbar -->
        <div class="p-4 bg-slate-900 text-white flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-rose-600 flex items-center justify-center font-bold text-xs">
                    <i class="fa-solid fa-table-cells"></i>
                </div>
                <div>
                    <div class="font-extrabold text-sm text-white flex items-center gap-2">
                        <span>Matrix Stock Ketersediaan Unit</span>
                        <span class="text-[10px] bg-slate-800 text-slate-300 px-2 py-0.5 rounded-full border border-slate-700 font-mono">
                            {{ $properties->count() }} Record
                        </span>
                    </div>
                    <div class="text-[11px] text-slate-400">Centang baris unit untuk share rekap ke calon buyer/penyewa via WhatsApp</div>
                </div>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                <button type="button"
                        @click="toggleSelectAll()"
                        class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-bold transition-all border border-slate-700 flex items-center gap-1.5">
                    <i class="fa-solid" :class="isAllSelected ? 'fa-square-check text-rose-400' : 'fa-check-double'"></i>
                    <span x-text="isAllSelected ? 'Batal Pilih Semua' : 'Pilih Semua Unit'"></span>
                </button>

                <button type="button"
                        @click="openWaModal()"
                        :disabled="selectedUnitIds.length === 0"
                        class="px-4 py-1.5 rounded-xl text-xs font-extrabold transition-all flex items-center gap-1.5 shadow-md"
                        :class="selectedUnitIds.length > 0 ? 'bg-emerald-600 hover:bg-emerald-500 text-white cursor-pointer animate-pulse' : 'bg-slate-800 text-slate-500 cursor-not-allowed'">
                    <i class="fa-brands fa-whatsapp text-sm"></i>
                    <span>Share Rekap WA (<span x-text="selectedUnitIds.length"></span>)</span>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-950 text-slate-200 uppercase tracking-wider font-extrabold border-b border-slate-800 text-[11px]">
                        <th class="p-3.5 text-center w-12">
                            <input type="checkbox"
                                   @change="toggleSelectAll()"
                                   :checked="isAllSelected"
                                   class="rounded text-rose-600 focus:ring-rose-500 w-4 h-4 cursor-pointer accent-rose-600">
                        </th>
                        <th class="p-3.5">Tower</th>
                        <th class="p-3.5 font-black text-rose-400">LT (Lantai)</th>
                        <th class="p-3.5">UNIT</th>
                        <th class="p-3.5">Tipe &amp; LB</th>
                        <th class="p-3.5">Kondisi &amp; View</th>
                        <th class="p-3.5 text-emerald-400">Harga Sewa / Thn</th>
                        <th class="p-3.5 text-amber-300">Harga Jual</th>
                        <th class="p-3.5">Status</th>
                        <th class="p-3.5 text-center">Share Cepat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    <template x-for="item in properties" :key="item.id">
                        <tr class="transition-colors hover:bg-rose-50/40"
                            :class="isSelected(item.id) ? 'bg-rose-50/70 border-l-4 border-l-rose-600' : ''">
                            
                            <!-- Checkbox -->
                            <td class="p-3 text-center">
                                <input type="checkbox"
                                       :value="item.id"
                                       @change="toggleUnit(item.id)"
                                       :checked="isSelected(item.id)"
                                       class="rounded text-rose-600 focus:ring-rose-500 w-4 h-4 cursor-pointer accent-rose-600">
                            </td>

                            <!-- Tower -->
                            <td class="p-3 font-bold">
                                <span class="px-2 py-1 rounded-lg text-xs font-black shadow-2xs"
                                      :class="item.tower.includes('B') ? 'bg-blue-100 text-blue-900 border border-blue-200' : 'bg-purple-100 text-purple-900 border border-purple-200'"
                                      x-text="item.tower">
                                </span>
                            </td>

                            <!-- Lantai (LT) -->
                            <td class="p-3">
                                <span class="text-sm font-black text-slate-900 font-mono bg-slate-100 px-2 py-0.5 rounded border border-slate-200"
                                      x-text="item.floor.replace(/lantai/i, 'Lt.').trim()">
                                </span>
                            </td>

                            <!-- UNIT (No Unit) -->
                            <td class="p-3">
                                <span class="font-extrabold text-slate-800 font-mono text-xs"
                                      x-text="item.unit_number || '-'">
                                </span>
                            </td>

                            <!-- Tipe & LB -->
                            <td class="p-3">
                                <div class="font-bold text-slate-900" x-text="item.type"></div>
                                <div class="text-[10px] text-slate-400" x-text="item.area ? item.area + ' m²' : '-'"></div>
                            </td>

                            <!-- Kondisi & View -->
                            <td class="p-3">
                                <div class="text-[11px] font-semibold text-slate-700" x-text="item.cond"></div>
                                <div class="text-[10px] text-slate-400 mt-0.5 flex items-center gap-1">
                                    <i class="fa-solid fa-mountain-city text-[9px]"></i>
                                    <span x-text="item.view"></span>
                                </div>
                            </td>

                            <!-- Harga Sewa / Thn -->
                            <td class="p-3">
                                <div class="font-black text-emerald-700 text-xs" x-text="item.rent_formatted"></div>
                            </td>

                            <!-- Harga Jual -->
                            <td class="p-3">
                                <div class="font-black text-slate-900 text-xs" x-text="item.sale_formatted"></div>
                            </td>

                            <!-- Status -->
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase"
                                      :class="item.status === 'active' ? 'bg-emerald-100 text-emerald-800' : (item.status === 'rented' ? 'bg-amber-100 text-amber-800' : 'bg-slate-200 text-slate-700')"
                                      x-text="item.status === 'active' ? 'READY' : (item.status === 'rented' ? 'TERSEWA' : item.status_label)">
                                </span>
                            </td>

                            <!-- Share Cepat & Aksi -->
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- 1-Click WhatsApp Direct Share -->
                                    <a :href="generateSingleWaUrl(item)"
                                       target="_blank"
                                       class="p-2 rounded-lg bg-emerald-50 hover:bg-emerald-600 text-emerald-700 hover:text-white transition-all shadow-2xs"
                                       title="Kirim 1 Unit Ini Langsung ke WhatsApp">
                                        <i class="fa-brands fa-whatsapp text-sm"></i>
                                    </a>

                                    <!-- 1-Click Copy Single Text -->
                                    <button type="button"
                                            @click="copySingleUnitText(item)"
                                            class="p-2 rounded-lg transition-all shadow-2xs"
                                            :class="copiedRowId === item.id ? 'bg-emerald-600 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                                            title="Salin Format Teks Unit Ini">
                                        <i class="fa-solid" :class="copiedRowId === item.id ? 'fa-check' : 'fa-copy'"></i>
                                    </button>

                                    <!-- Catat Penyewa Unit Ini -->
                                    <template x-if="item.listing_type === 'disewa'">
                                        <a :href="'{{ url('admin/rentals/create') }}?property_id=' + item.id"
                                           class="p-2 rounded-lg bg-pink-50 hover:bg-pink-600 hover:text-white text-pink-700 transition-all shadow-2xs"
                                           title="Catat Penyewa &amp; Deposit Unit Ini">
                                            <i class="fa-solid fa-key"></i>
                                        </a>
                                    </template>

                                    <!-- Quick Edit Modal Trigger -->
                                    <button type="button"
                                            @click="openQuickEditModal(item)"
                                            class="p-2 rounded-lg bg-slate-100 hover:bg-amber-500 hover:text-white text-slate-600 transition-all shadow-2xs"
                                            title="Edit Cepat (Harga/Status)">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>

                                    <!-- View on Website -->
                                    <a :href="item.url" target="_blank"
                                       class="p-2 rounded-lg bg-slate-100 hover:bg-blue-600 hover:text-white text-slate-600 transition-all shadow-2xs"
                                       title="Buka Halaman Web">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <template x-if="properties.length === 0">
                        <tr>
                            <td colspan="10" class="p-12 text-center text-slate-400">
                                <div class="max-w-md mx-auto space-y-3">
                                    <i class="fa-solid fa-hotel text-4xl text-slate-300"></i>
                                    <p class="font-bold text-slate-700">Tidak ada unit apartemen yang cocok dengan filter ini.</p>
                                    <p class="text-xs text-slate-400">Silakan ubah filter atau klik tombol "Tambah Unit Cepat" di atas.</p>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Floating Sticky Action Bar (Shows when 1 or more units selected) -->
    <div x-show="selectedUnitIds.length > 0"
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-6"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-6"
         class="fixed bottom-6 inset-x-0 z-40 max-w-2xl mx-auto px-4">
        
        <div class="bg-slate-950/95 backdrop-blur-md rounded-2xl p-4 shadow-2xl border border-slate-700 text-white flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-gradient-to-tr from-rose-600 to-amber-500 flex items-center justify-center font-extrabold text-white text-xs shadow-md">
                    <span x-text="selectedUnitIds.length"></span>
                </span>
                <div>
                    <div class="font-bold text-xs text-white">
                        <span x-text="selectedUnitIds.length"></span> Unit Terpilih
                    </div>
                    <div class="text-[10px] text-slate-400">Siap di-generate menjadi pesan rekap WhatsApp</div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="button"
                        @click="clearSelection()"
                        class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition-all">
                    Batal
                </button>
                <button type="button"
                        @click="openWaModal()"
                        class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black transition-all flex items-center gap-2 shadow-lg shadow-emerald-900/40">
                    <i class="fa-brands fa-whatsapp text-sm"></i>
                    <span>Generate Rekap WhatsApp &rarr;</span>
                </button>
            </div>
        </div>
    </div>

    <!-- WhatsApp Recap Generator Modal -->
    <div x-show="showWaModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-xs flex items-center justify-center p-4">
        
        <div @click.away="showWaModal = false"
             class="bg-white rounded-3xl max-w-2xl w-full border border-slate-200 shadow-2xl overflow-hidden space-y-0 relative">
            
            <!-- Modal Header -->
            <div class="p-6 bg-gradient-to-r from-emerald-700 to-emerald-900 text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-lg">
                        <i class="fa-brands fa-whatsapp"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold">Preview Rekap WhatsApp (<span x-text="selectedUnitIds.length"></span> Unit)</h3>
                        <p class="text-xs text-emerald-200">Format rapi dikelompokkan per Tower &amp; Lantai siap kirim ke klien</p>
                    </div>
                </div>
                <button @click="showWaModal = false" class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center text-sm transition-colors">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Customization Checkbox Options -->
            <div class="px-6 py-3 bg-slate-50 border-b border-slate-200 flex flex-wrap items-center gap-4 text-xs font-bold text-slate-700">
                <span class="text-slate-400 text-[10px] uppercase tracking-wider">Opsi Format:</span>
                <label class="flex items-center gap-1.5 cursor-pointer">
                    <input type="checkbox" x-model="includeRentPrice" class="rounded text-emerald-600 accent-emerald-600">
                    <span>Harga Sewa</span>
                </label>
                <label class="flex items-center gap-1.5 cursor-pointer">
                    <input type="checkbox" x-model="includeSalePrice" class="rounded text-emerald-600 accent-emerald-600">
                    <span>Harga Jual</span>
                </label>
                <label class="flex items-center gap-1.5 cursor-pointer">
                    <input type="checkbox" x-model="includeWebLink" class="rounded text-emerald-600 accent-emerald-600">
                    <span>Link Foto Web</span>
                </label>
            </div>

            <!-- WhatsApp Text Box Preview -->
            <div class="p-6 space-y-4">
                <div class="relative">
                    <textarea readonly
                              x-model="generatedWaText"
                              rows="12"
                              class="w-full p-4 rounded-2xl bg-emerald-50/50 border border-emerald-200 font-mono text-xs text-slate-800 focus:outline-none leading-relaxed select-all scrollbar-thin"></textarea>
                    
                    <button type="button"
                            @click="copyGeneratedText()"
                            class="absolute top-3 right-3 px-3 py-1.5 rounded-xl font-bold text-xs transition-all shadow-sm flex items-center gap-1.5"
                            :class="copiedBulk ? 'bg-emerald-600 text-white' : 'bg-white hover:bg-emerald-50 text-emerald-800 border border-emerald-300'">
                        <i class="fa-solid" :class="copiedBulk ? 'fa-check' : 'fa-copy'"></i>
                        <span x-text="copiedBulk ? 'Teks Tersalin!' : 'Salin Teks'"></span>
                    </button>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
                    <div class="text-xs text-slate-500 flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-info text-rose-500"></i>
                        <span>Otomatis menyertakan kontak &amp; nama agen: <strong>{{ $agentName }}</strong></span>
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <button type="button"
                                @click="copyGeneratedText()"
                                class="flex-1 sm:flex-none px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-copy"></i>
                            <span>Salin ke Clipboard</span>
                        </button>
                        <a :href="directWaSendUrl"
                           target="_blank"
                           class="flex-1 sm:flex-none px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-md transition-all flex items-center justify-center gap-2">
                            <i class="fa-brands fa-whatsapp text-base"></i>
                            <span>Buka di WhatsApp &rarr;</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Add Unit Modal -->
    <div x-show="showQuickAddModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-xs flex items-center justify-center p-4">
        
        <div @click.away="showQuickAddModal = false"
             class="bg-white rounded-3xl max-w-xl w-full border border-slate-200 shadow-2xl overflow-hidden space-y-0">
            
            <div class="p-6 bg-slate-950 text-white flex items-center justify-between border-b border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-600 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-plus"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold">Tambah Unit Cepat ke Stock Sheet</h3>
                        <p class="text-xs text-slate-400">Input unit apartemen baru yang siap dipasarkan</p>
                    </div>
                </div>
                <button @click="showQuickAddModal = false" class="w-8 h-8 rounded-xl bg-slate-800 hover:bg-rose-600 flex items-center justify-center text-sm transition-colors">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="{{ route('admin.apartment-stock.store') }}" method="POST" class="p-6 space-y-4 text-xs font-semibold">
                @csrf
                
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Pilih Proyek Apartemen *</label>
                    <select name="project_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-rose-500">
                        @foreach($projects as $proj)
                            <option value="{{ $proj->id }}" {{ (string)$selectedProjectId === (string)$proj->id ? 'selected' : '' }}>
                                {{ $proj->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-slate-700 font-bold mb-1">Tower *</label>
                        <input type="text" name="unit_tower" required placeholder="Contoh: Tower A" value="Tower A"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-rose-500">
                    </div>
                    <div>
                        <label class="block text-slate-700 font-bold mb-1">Lantai (LT) *</label>
                        <input type="text" name="unit_floor" required placeholder="Contoh: 18"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-rose-500">
                    </div>
                    <div>
                        <label class="block text-slate-700 font-bold mb-1">No. Unit</label>
                        <input type="text" name="unit_number" placeholder="Contoh: Unit 05"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-rose-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-700 font-bold mb-1">Tipe Unit *</label>
                        <select name="unit_type" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-rose-500">
                            <option value="Studio">Studio</option>
                            <option value="1 BR">1 BR</option>
                            <option value="2 BR" selected>2 BR</option>
                            <option value="3 BR">3 BR</option>
                            <option value="Ruko Apartemen 3 Lantai">Ruko Apartemen 3 Lantai</option>
                            <option value="Ruko Apartemen 4 Lantai">Ruko Apartemen 4 Lantai</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-700 font-bold mb-1">Luas Bangunan (m²)</label>
                        <input type="number" step="0.1" name="building_area" placeholder="Contoh: 48"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-rose-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-700 font-bold mb-1">Kondisi Perabotan *</label>
                        <select name="furnished_status" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-rose-500">
                            <option value="fully-furnished">Full Furnished</option>
                            <option value="semi-furnished">Semi Furnished</option>
                            <option value="unfurnished">Kosongan (Unfurnished)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-700 font-bold mb-1">View Unit</label>
                        <input type="text" name="unit_view" placeholder="City View / Pool View" value="City View"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-rose-500">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-slate-700 font-bold mb-1">Jenis Transaksi *</label>
                        <select name="listing_type" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-rose-500">
                            <option value="disewa" selected>Disewa</option>
                            <option value="dijual">Dijual</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-700 font-bold mb-1">Harga Sewa / Thn</label>
                        <input type="number" name="rent_price" placeholder="Contoh: 35000000"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-rose-500">
                    </div>
                    <div>
                        <label class="block text-slate-700 font-bold mb-1">Harga Jual</label>
                        <input type="number" name="sale_price" placeholder="Contoh: 250000000"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-rose-500">
                    </div>
                </div>

                <div>
                    <label class="block text-slate-700 font-bold mb-1">Status Ketersediaan *</label>
                    <select name="status" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-rose-500">
                        <option value="active" selected>Ready (Aktif)</option>
                        <option value="rented">Sudah Tersewa</option>
                        <option value="sold">Sudah Terjual</option>
                    </select>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="showQuickAddModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-bold shadow-md">
                        Simpan Unit Baru
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Edit Unit Modal -->
    <div x-show="showQuickEditModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-xs flex items-center justify-center p-4">
        
        <div @click.away="showQuickEditModal = false"
             class="bg-white rounded-3xl max-w-lg w-full border border-slate-200 shadow-2xl overflow-hidden space-y-0">
            
            <div class="p-6 bg-slate-900 text-white flex items-center justify-between">
                <div>
                    <h3 class="text-base font-extrabold">Edit Cepat Unit</h3>
                    <p class="text-xs text-slate-400" x-text="editUnitData.title"></p>
                </div>
                <button @click="showQuickEditModal = false" class="w-8 h-8 rounded-xl bg-slate-800 hover:bg-rose-600 flex items-center justify-center text-sm transition-colors">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form :action="'{{ url('admin/apartment-stock') }}/' + editUnitData.id + '/quick-update'" method="POST" class="p-6 space-y-4 text-xs font-semibold">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-slate-700 font-bold mb-1">Tower</label>
                        <input type="text" name="unit_tower" x-model="editUnitData.tower"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl">
                    </div>
                    <div>
                        <label class="block text-slate-700 font-bold mb-1">Lantai (LT)</label>
                        <input type="text" name="unit_floor" x-model="editUnitData.floor"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl">
                    </div>
                    <div>
                        <label class="block text-slate-700 font-bold mb-1">No Unit</label>
                        <input type="text" name="unit_number" x-model="editUnitData.unit_number"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-700 font-bold mb-1">Harga Sewa / Thn</label>
                        <input type="number" name="rent_price" x-model="editUnitData.rent_raw"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl">
                    </div>
                    <div>
                        <label class="block text-slate-700 font-bold mb-1">Harga Jual</label>
                        <input type="number" name="sale_price" x-model="editUnitData.sale_raw"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl">
                    </div>
                </div>

                <div>
                    <label class="block text-slate-700 font-bold mb-1">Status Ketersediaan</label>
                    <select name="status" x-model="editUnitData.status" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl">
                        <option value="active">READY (Aktif)</option>
                        <option value="rented">TERSEWA (Rented)</option>
                        <option value="sold">TERJUAL (Sold)</option>
                        <option value="draft">Draft (Disembunyikan)</option>
                    </select>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="showQuickEditModal = false" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl font-bold">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-bold shadow-md">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function apartmentStockApp() {
    return {
        properties: @json($propertiesPayload),
        selectedUnitIds: [],
        showWaModal: false,
        showQuickAddModal: false,
        showQuickEditModal: false,
        editUnitData: {},
        copiedBulk: false,
        copiedRowId: null,

        // Options
        includeRentPrice: true,
        includeSalePrice: true,
        includeWebLink: true,

        get isAllSelected() {
            return this.properties.length > 0 && this.selectedUnitIds.length === this.properties.length;
        },

        isSelected(id) {
            return this.selectedUnitIds.includes(id);
        },

        toggleUnit(id) {
            const idx = this.selectedUnitIds.indexOf(id);
            if (idx > -1) {
                this.selectedUnitIds.splice(idx, 1);
            } else {
                this.selectedUnitIds.push(id);
            }
        },

        toggleSelectAll() {
            if (this.isAllSelected) {
                this.selectedUnitIds = [];
            } else {
                this.selectedUnitIds = this.properties.map(p => p.id);
            }
        },

        clearSelection() {
            this.selectedUnitIds = [];
        },

        openWaModal() {
            if (this.selectedUnitIds.length === 0) return;
            this.showWaModal = true;
        },

        openQuickAddModal() {
            this.showQuickAddModal = true;
        },

        openQuickEditModal(item) {
            this.editUnitData = { ...item };
            this.showQuickEditModal = true;
        },

        // Format Single Unit WhatsApp text
        generateSingleWaText(item) {
            const floorClean = item.floor.replace(/lantai/i, 'Lt.').trim();
            const unitClean = item.unit_number && item.unit_number !== '-' ? ' / ' + item.unit_number : '';
            const areaClean = item.area ? ', ' + item.area + 'm²' : '';
            
            let text = `🏢 *READY UNIT - ${item.project_name.toUpperCase()}*\n`
                     + `📍 ${item.tower} - ${floorClean}${unitClean} (${item.type}${areaClean})\n`
                     + `🏙 *View:* ${item.view}\n`
                     + `🛋 *Kondisi:* ${item.cond}\n`;

            if (item.rent_raw) {
                text += `🔑 *Harga Sewa:* ${item.rent_formatted}\n`;
            }
            if (item.sale_raw) {
                text += `💰 *Harga Jual:* ${item.sale_formatted}\n`;
            }

            text += `\n📸 *Foto & Info Lengkap:* ${item.url}\n\n`
                  + `━━━━━━━━━━━━━━━━━━━━━\n`
                  + `👤 *${item.agent_name}* - ${item.agent_office}\n`
                  + `📞 *WA / Telp:* ${item.agent_phone}\n`
                  + `👉 Chat Langsung: https://wa.me/${item.agent_clean_phone}?text=${encodeURIComponent('Halo ' + item.agent_name + ', info unit ' + item.project_name + ' ' + item.tower + ' ' + floorClean)}\n`
                  + `━━━━━━━━━━━━━━━━━━━━━`;

            return text;
        },

        generateSingleWaUrl(item) {
            const text = this.generateSingleWaText(item);
            return 'https://api.whatsapp.com/send?text=' + encodeURIComponent(text);
        },

        copySingleUnitText(item) {
            const text = this.generateSingleWaText(item);
            navigator.clipboard.writeText(text);
            this.copiedRowId = item.id;
            setTimeout(() => {
                if (this.copiedRowId === item.id) this.copiedRowId = null;
            }, 2000);
        },

        // Generate Bulk WhatsApp Message grouped by Tower
        get generatedWaText() {
            const selectedItems = this.properties.filter(p => this.selectedUnitIds.includes(p.id));
            if (selectedItems.length === 0) return '';

            const projectName = selectedItems[0]?.project_name || 'Apartemen';
            const todayStr = new Intl.DateTimeFormat('id-ID', { dateStyle: 'long' }).format(new Date());

            // Group by Tower
            const grouped = {};
            selectedItems.forEach(item => {
                const towerKey = item.tower || 'General';
                if (!grouped[towerKey]) grouped[towerKey] = [];
                grouped[towerKey].push(item);
            });

            let text = `🏢 *DAFTAR READY UNIT - ${projectName.toUpperCase()}*\n`
                     + `📅 Update: ${todayStr}\n\n`
                     + `Halo Pak/Bu, berikut ketersediaan unit ready yang bisa disurvey:\n\n`;

            for (const [towerName, items] of Object.entries(grouped)) {
                text += `🔹 *${towerName.toUpperCase()}*\n`;
                items.forEach(item => {
                    const floorClean = item.floor.replace(/lantai/i, 'Lt.').trim();
                    const unitClean = item.unit_number && item.unit_number !== '-' ? ' / ' + item.unit_number : '';
                    const areaClean = item.area ? `, ${item.area}m²` : '';

                    text += `• ${floorClean}${unitClean} (${item.type}${areaClean})\n`
                          + `  - Kondisi: ${item.cond} | ${item.view}\n`;

                    if (this.includeRentPrice && item.rent_raw) {
                        text += `  - Sewa: ${item.rent_formatted}\n`;
                    }
                    if (this.includeSalePrice && item.sale_raw) {
                        text += `  - Jual: ${item.sale_formatted}\n`;
                    }
                    if (this.includeWebLink && item.url) {
                        text += `  - Foto: ${item.url}\n`;
                    }
                });
                text += `\n`;
            }

            const first = selectedItems[0];
            text += `━━━━━━━━━━━━━━━━━━━━━\n`
                  + `👉 Mau survey unit yang mana Pak/Bu? Kunci ready, jadwal survey fleksibel:\n`
                  + `👤 *${first.agent_name}* - ${first.agent_office}\n`
                  + `📞 *WhatsApp / Telp:* ${first.agent_phone}\n`
                  + `💬 *Klik Chat WA:* https://wa.me/${first.agent_clean_phone}?text=${encodeURIComponent('Halo ' + first.agent_name + ', saya mau tanya unit ready di ' + projectName)}\n`
                  + `━━━━━━━━━━━━━━━━━━━━━`;

            return text;
        },

        get directWaSendUrl() {
            return 'https://api.whatsapp.com/send?text=' + encodeURIComponent(this.generatedWaText);
        },

        copyGeneratedText() {
            navigator.clipboard.writeText(this.generatedWaText);
            this.copiedBulk = true;
            setTimeout(() => {
                this.copiedBulk = false;
            }, 2500);
        }
    };
}
</script>
@endsection

