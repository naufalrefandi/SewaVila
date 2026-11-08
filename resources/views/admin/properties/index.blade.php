@extends('layouts.admin')

@section('title', 'Kelola Listing Properti - Admin ' . \App\Models\Setting::getSiteName() . '')
@section('page_title', 'Kelola Semua Listing Properti')

@section('content')
<div class="space-y-3.5 sm:space-y-4" x-data="{ openTopupModal: false, openFeaturedModal: false, activeFeaturedPropId: null, activeFeaturedPropTitle: '' }">
    @if(isset($isAgent) && $isAgent && isset($currentAgent) && $currentAgent)
        <!-- Agent Quota Card Banner (Clean White & Pink) -->
        <div class="bg-white text-slate-800 rounded-2xl p-4 sm:p-5 border border-pink-200 shadow-xs relative overflow-hidden">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full bg-pink-50 text-pink-700 border border-pink-200 text-[10px] font-extrabold uppercase tracking-wider">
                            <i class="fa-solid fa-layer-group mr-1"></i> Kuota Listing Agen
                        </span>
                        @if($currentAgent->remaining_quota > 0)
                            <span class="px-2 py-0.5 rounded-full bg-pink-100 text-pink-800 border border-pink-200 text-[10px] font-bold">
                                Aktif (Tersisa {{ $currentAgent->remaining_quota }})
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full bg-pink-50 text-pink-700 border border-pink-200 text-[10px] font-bold">
                                Kuota Habis (0 Tersisa)
                            </span>
                        @endif
                    </div>
                    <div class="flex items-baseline gap-2">
                        <div class="text-2xl sm:text-3xl font-black">
                            <span class="text-pink-600">{{ $currentAgent->remaining_quota }}</span>
                            <span class="text-xs font-semibold text-slate-400">/ {{ $currentAgent->listing_quota }} listing tersisa</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 max-w-xl">
                        Tiap akun agen baru mendapatkan gratis {{ \App\Models\Setting::get('agent_free_quota', 3) }} listing. Terpakai: <strong class="text-slate-900">{{ $currentAgent->used_quota }}</strong> properti. Topup kuota listing kapan saja menggunakan scan QRIS resmi.
                    </p>
                </div>

                <div class="flex items-center gap-2.5 shrink-0">
                    <button type="button"
                            @click="openTopupModal = true"
                            class="px-4 py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white text-xs font-extrabold shadow-sm transition-all flex items-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-cart-plus"></i>
                        <span>Beli Tambahan Kuota</span>
                    </button>
                </div>
            </div>

            <!-- Progress bar -->
            <div class="mt-4 pt-3 border-t border-pink-100 flex items-center gap-3 text-[11px] text-slate-500">
                <div class="w-full bg-pink-50 border border-pink-100 rounded-full h-2 overflow-hidden">
                    @php
                        $percentUsed = $currentAgent->listing_quota > 0 ? min(100, round(($currentAgent->used_quota / $currentAgent->listing_quota) * 100)) : 100;
                    @endphp
                    <div class="bg-pink-600 h-2 rounded-full transition-all" style="width: {{ $percentUsed }}%"></div>
                </div>
                <span class="shrink-0 text-slate-500 font-bold">{{ $percentUsed }}% terpakai</span>
            </div>
        </div>
    @endif

    @if(isset($isAgent) && $isAgent)
        <!-- Agent Scope Switcher -->
        <div class="flex flex-wrap items-center gap-2 p-1 bg-white border border-slate-200 rounded-2xl w-fit text-xs font-bold shadow-xs">
            <a href="{{ route('admin.properties', request()->except(['scope', 'page'])) }}"
               class="px-4 py-2 rounded-xl transition-all flex items-center gap-1.5 {{ request('scope') !== 'all' && request('scope') !== 'team' ? 'bg-rose-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                <i class="fa-solid fa-user-tag text-xs"></i>
                <span>Listing Saya</span>
            </a>

            @if(isset($isLeader) && $isLeader)
                <a href="{{ route('admin.properties', array_merge(request()->except('page'), ['scope' => 'team'])) }}"
                   class="px-4 py-2 rounded-xl transition-all flex items-center gap-1.5 {{ request('scope') === 'team' ? 'bg-amber-500 text-white shadow-xs' : 'text-amber-700 bg-amber-50/70 hover:bg-amber-100' }}">
                    <i class="fa-solid fa-crown text-xs"></i>
                    <span>Listing Tim Saya</span>
                </a>
            @endif

            <a href="{{ route('admin.properties', array_merge(request()->except('page'), ['scope' => 'all'])) }}"
               class="px-4 py-2 rounded-xl transition-all flex items-center gap-1.5 {{ request('scope') === 'all' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                <i class="fa-solid fa-network-wired text-xs"></i>
                <span>Semua Listing Jejaring {{ \App\Models\Setting::getSiteName() }}</span>
            </a>
        </div>
    @endif

    <!-- Active Filter Alert / Banner -->
    @if(isset($selectedAgent) && $selectedAgent)
        <div class="flex items-center justify-between p-3.5 bg-rose-50 border border-rose-200 rounded-2xl text-xs text-rose-900 shadow-xs">
            <div class="flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-lg bg-rose-600 text-white flex items-center justify-center font-bold text-xs">
                    <i class="fa-solid fa-user-check"></i>
                </span>
                <div>
                    <span>Menampilkan properti milik agen: <strong>{{ $selectedAgent->name }}</strong> ({{ $selectedAgent->office_branch }})</span>
                    @if($selectedAgent->leader)
                        <span class="text-slate-500 ml-1.5">• Tim Leader: <strong>{{ $selectedAgent->leader->name }}</strong></span>
                    @endif
                </div>
            </div>
            <a href="{{ route('admin.properties', request()->except(['agent_id', 'page'])) }}" class="px-2.5 py-1 bg-white hover:bg-rose-100 text-rose-700 font-bold rounded-lg border border-rose-200 transition-all flex items-center gap-1 text-[11px]">
                <i class="fa-solid fa-xmark"></i> Hapus Filter Agen
            </a>
        </div>
    @elseif(isset($selectedLeader) && $selectedLeader)
        <div class="flex items-center justify-between p-3.5 bg-amber-50 border border-amber-200 rounded-2xl text-xs text-amber-950 shadow-xs">
            <div class="flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-lg bg-amber-500 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                    <i class="fa-solid fa-crown"></i>
                </span>
                <div>
                    <span>Menampilkan seluruh properti <strong>Tim Leader: {{ $selectedLeader->name }}</strong> (Termasuk {{ $selectedLeader->teamMembers->count() }} agen dalam tim binaannya)</span>
                </div>
            </div>
            <a href="{{ route('admin.properties', request()->except(['leader_id', 'page'])) }}" class="px-2.5 py-1 bg-white hover:bg-amber-100 text-amber-800 font-bold rounded-lg border border-amber-200 transition-all flex items-center gap-1 text-[11px]">
                <i class="fa-solid fa-xmark"></i> Hapus Filter Leader
            </a>
        </div>
    @elseif(isset($isLeader) && $isLeader && request('scope') === 'team')
        <div class="flex items-center justify-between p-3.5 bg-amber-50 border border-amber-200 rounded-2xl text-xs text-amber-950 shadow-xs">
            <div class="flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-lg bg-amber-500 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                    <i class="fa-solid fa-crown"></i>
                </span>
                <div>
                    <span>👑 <strong>Mode Listing Tim:</strong> Menampilkan properti gabungan Anda dan seluruh anggota tim yang Anda pimpin.</span>
                </div>
            </div>
            <a href="{{ route('admin.properties') }}" class="px-2.5 py-1 bg-white hover:bg-amber-100 text-amber-800 font-bold rounded-lg border border-amber-200 transition-all flex items-center gap-1 text-[11px]">
                Kembali ke Listing Saya
            </a>
        </div>
    @endif

    <!-- Top Filter & Search Bar -->
    <div class="bg-white rounded-3xl border border-slate-200 p-4 sm:p-5 shadow-xs space-y-3.5">
        <!-- Row 1: Quick Action Buttons & Listing Summary -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-pink-50 text-pink-600 flex items-center justify-center text-sm font-bold">
                    <i class="fa-solid fa-layer-group"></i>
                </span>
                <div>
                    <h2 class="text-sm font-black text-slate-900 leading-tight">Daftar Listing Properti</h2>
                    <p class="text-[11px] text-slate-400">Total <span class="font-bold text-slate-700">{{ $properties->total() }}</span> properti terdaftar</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if(isset($isAgent) && $isAgent && isset($currentAgent) && $currentAgent)
                    <button type="button" @click="openTopupModal = true" class="px-3.5 py-2 rounded-xl bg-pink-50 hover:bg-pink-100 text-pink-700 border border-pink-200 font-bold text-xs shadow-2xs transition-all flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                        <i class="fa-solid fa-cart-plus text-pink-600"></i> Topup (Sisa: {{ $currentAgent->remaining_quota }})
                    </button>
                @endif
                <a href="{{ route('admin.apartment-stock') }}" class="px-3.5 py-2 rounded-xl bg-pink-50 hover:bg-pink-100 text-pink-700 border border-pink-200 font-bold text-xs shadow-2xs transition-all flex items-center gap-1.5 whitespace-nowrap" title="Buka Tabel Stock Ketersediaan Unit Apartemen & Share Cepat WA">
                    <i class="fa-solid fa-bolt text-pink-600"></i> Quick Stock Apartemen
                </a>
                <a href="{{ route('admin.properties.create') }}" class="px-4 py-2 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-xs shadow-sm transition-all flex items-center gap-2 whitespace-nowrap">
                    <i class="fa-solid fa-plus"></i> Tambah Listing Baru
                </a>
            </div>
        </div>

        <!-- Row 2: Search Input & Filter Dropdowns -->
        <form action="{{ route('admin.properties') }}" method="GET" class="flex flex-wrap items-center gap-2">
            @if(request('type'))
                <input type="hidden" name="type" value="{{ request('type') }}">
            @endif
            @if(request('scope'))
                <input type="hidden" name="scope" value="{{ request('scope') }}">
            @endif

            <div class="relative flex-1 min-w-[180px] sm:min-w-[220px]">
                <input type="text"
                       name="q"
                       value="{{ request('q') }}"
                       placeholder="Cari judul, kode, kota..."
                       class="w-full pl-8 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-pink-500 focus:bg-white focus:outline-none">
                <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-slate-400 text-xs"></i>
            </div>

            <select name="type" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-pink-500">
                <option value="">Semua Tipe</option>
                <option value="rumah" {{ request('type') == 'rumah' ? 'selected' : '' }}>Rumah</option>
                <option value="apartemen" {{ request('type') == 'apartemen' ? 'selected' : '' }}>Apartemen</option>
                <option value="ruko" {{ request('type') == 'ruko' ? 'selected' : '' }}>Ruko</option>
                <option value="villa" {{ request('type') == 'villa' ? 'selected' : '' }}>Villa</option>
                <option value="tanah" {{ request('type') == 'tanah' ? 'selected' : '' }}>Tanah</option>
                <option value="gudang" {{ request('type') == 'gudang' ? 'selected' : '' }}>Gudang</option>
                <option value="hotel" {{ request('type') == 'hotel' ? 'selected' : '' }}>Hotel</option>
                <option value="gedung" {{ request('type') == 'gedung' ? 'selected' : '' }}>Gedung</option>
            </select>

            <select name="listing" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-pink-500">
                <option value="">Semua Transaksi</option>
                <option value="dijual" {{ request('listing') == 'dijual' ? 'selected' : '' }}>Dijual</option>
                <option value="disewa" {{ request('listing') == 'disewa' ? 'selected' : '' }}>Disewa</option>
            </select>

            @if(!isset($isAgent) || !$isAgent || request('scope') === 'all')
                <!-- Filter Master Agen (Bisa Diketik) -->
                @if(isset($agents) && $agents->isNotEmpty())
                    <div class="relative" x-data="{ open: false, search: '{{ $selectedAgent ? addslashes($selectedAgent->name) : '' }}', selectedId: '{{ request('agent_id', '') }}' }" @click.outside="open = false">
                        <input type="hidden" name="agent_id" :value="selectedId">
                        <div class="relative min-w-[130px] max-w-[170px]">
                            <input type="text"
                                   x-model="search"
                                   @focus="open = true"
                                   @input="open = true; selectedId = ''"
                                   placeholder="Semua Agen..."
                                   class="w-full pl-6 pr-6 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-pink-500 focus:bg-white truncate"
                                   title="Ketik nama agen untuk mencari">
                            <i class="fa-solid fa-user absolute left-2 top-2.5 text-slate-400 text-[10px]"></i>
                            <button type="button" x-show="selectedId || search" @click="selectedId = ''; search = ''; $nextTick(() => $el.closest('form').submit())" class="absolute right-1.5 top-2 text-slate-400 hover:text-slate-600 p-0.5">
                                <i class="fa-solid fa-xmark text-xs"></i>
                            </button>
                        </div>
                        <div x-show="open" x-cloak class="absolute left-0 top-full mt-1 w-56 max-h-52 overflow-y-auto bg-white rounded-xl shadow-xl border border-slate-200 z-50 py-1 text-xs divide-y divide-slate-50">
                            <button type="button" @click="selectedId = ''; search = ''; open = false; $nextTick(() => $el.closest('form').submit())" class="w-full text-left px-3 py-1.5 hover:bg-slate-50 text-slate-500 font-semibold cursor-pointer">
                                -- Semua Agen --
                            </button>
                            @foreach($agents as $ag)
                                <button type="button"
                                        x-show="!search || '{{ strtolower(addslashes($ag->name)) }}'.includes(search.toLowerCase())"
                                        @click="selectedId = '{{ $ag->id }}'; search = '{{ addslashes($ag->name) }}'; open = false; $nextTick(() => $el.closest('form').submit())"
                                        class="w-full text-left px-3 py-1.5 hover:bg-pink-50 hover:text-pink-600 flex items-center justify-between text-slate-700 transition cursor-pointer">
                                    <span>{{ $ag->name }} {{ $ag->is_leader ? '👑' : '' }}</span>
                                    <span class="text-[10px] text-slate-400 font-normal">{{ $ag->office_branch }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Filter Master Tim Leader (Bisa Diketik) -->
                @if(isset($leaders) && $leaders->isNotEmpty())
                    <div class="relative" x-data="{ open: false, search: '{{ $selectedLeader ? addslashes($selectedLeader->name) : '' }}', selectedId: '{{ request('leader_id', '') }}' }" @click.outside="open = false">
                        <input type="hidden" name="leader_id" :value="selectedId">
                        <div class="relative min-w-[130px] max-w-[170px]">
                            <input type="text"
                                   x-model="search"
                                   @focus="open = true"
                                   @input="open = true; selectedId = ''"
                                   placeholder="Semua Leader..."
                                   class="w-full pl-6 pr-6 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-pink-500 focus:bg-white truncate"
                                   title="Ketik nama tim leader untuk mencari">
                            <i class="fa-solid fa-crown absolute left-2 top-2.5 text-amber-500 text-[10px]"></i>
                            <button type="button" x-show="selectedId || search" @click="selectedId = ''; search = ''; $nextTick(() => $el.closest('form').submit())" class="absolute right-1.5 top-2 text-slate-400 hover:text-slate-600 p-0.5">
                                <i class="fa-solid fa-xmark text-xs"></i>
                            </button>
                        </div>
                        <div x-show="open" x-cloak class="absolute left-0 top-full mt-1 w-56 max-h-52 overflow-y-auto bg-white rounded-xl shadow-xl border border-slate-200 z-50 py-1 text-xs divide-y divide-slate-50">
                            <button type="button" @click="selectedId = ''; search = ''; open = false; $nextTick(() => $el.closest('form').submit())" class="w-full text-left px-3 py-1.5 hover:bg-slate-50 text-slate-500 font-semibold cursor-pointer">
                                -- Semua Tim Leader --
                            </button>
                            @foreach($leaders as $ld)
                                <button type="button"
                                        x-show="!search || '{{ strtolower(addslashes($ld->name)) }}'.includes(search.toLowerCase())"
                                        @click="selectedId = '{{ $ld->id }}'; search = '{{ addslashes($ld->name) }}'; open = false; $nextTick(() => $el.closest('form').submit())"
                                        class="w-full text-left px-3 py-1.5 hover:bg-amber-50 hover:text-amber-800 flex items-center justify-between text-slate-700 transition cursor-pointer">
                                    <span class="font-bold">👑 Tim {{ $ld->name }}</span>
                                    <span class="text-[10px] text-slate-400 font-normal">{{ $ld->teamMembers->count() }} agen</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif

            <!-- Filter Master Apartemen / Proyek (Bisa Diketik) -->
            @if(isset($projects) && $projects->isNotEmpty())
                @php
                    $activeProj = request('project_id') ? $projects->firstWhere('id', request('project_id')) : null;
                @endphp
                <div class="relative" x-data="{ open: false, search: '{{ $activeProj ? addslashes($activeProj->name) : '' }}', selectedId: '{{ request('project_id', '') }}' }" @click.outside="open = false">
                    <input type="hidden" name="project_id" :value="selectedId">
                    <div class="relative min-w-[130px] max-w-[170px]">
                        <input type="text"
                               x-model="search"
                               @focus="open = true"
                               @input="open = true; selectedId = ''"
                               placeholder="Semua Master Proyek..."
                               class="w-full pl-6 pr-6 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-pink-500 focus:bg-white truncate"
                               title="Ketik nama apartemen/perumahan dari master">
                        <i class="fa-solid fa-city absolute left-2 top-2.5 text-pink-500 text-[10px]"></i>
                        <button type="button" x-show="selectedId || search" @click="selectedId = ''; search = ''; $nextTick(() => $el.closest('form').submit())" class="absolute right-1.5 top-2 text-slate-400 hover:text-slate-600 p-0.5">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                    </div>
                    <div x-show="open" x-cloak class="absolute left-0 top-full mt-1 w-64 max-h-52 overflow-y-auto bg-white rounded-xl shadow-xl border border-slate-200 z-50 py-1 text-xs divide-y divide-slate-50">
                        <button type="button" @click="selectedId = ''; search = ''; open = false; $nextTick(() => $el.closest('form').submit())" class="w-full text-left px-3 py-1.5 hover:bg-slate-50 text-slate-500 font-semibold cursor-pointer">
                            -- Semua Kawasan / Proyek --
                        </button>
                        @foreach($projects as $pj)
                            <button type="button"
                                    x-show="!search || '{{ strtolower(addslashes($pj->name)) }}'.includes(search.toLowerCase())"
                                    @click="selectedId = '{{ $pj->id }}'; search = '{{ addslashes($pj->name) }}'; open = false; $nextTick(() => $el.closest('form').submit())"
                                    class="w-full text-left px-3 py-1.5 hover:bg-pink-50 hover:text-pink-600 flex items-center justify-between text-slate-700 transition cursor-pointer">
                                <span class="font-bold">{{ $pj->name }}</span>
                                <span class="text-[10px] text-slate-400 font-normal">{{ $pj->city?->name }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition-colors cursor-pointer">
                Filter
            </button>

            @if(request()->hasAny(['q', 'type', 'listing', 'agent_id', 'leader_id', 'project_id']))
                <a href="{{ route('admin.properties', request('scope') ? ['scope' => request('scope')] : []) }}"
                   class="px-3 py-2 text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-xl text-xs font-bold transition-colors" title="Reset Semua Filter">
                    <i class="fa-solid fa-rotate-left mr-1"></i>Reset
                </a>
            @endif
        </form>

        <!-- Row 3: Quick Category Filter Pills (No Scroll, Responsive Flex Wrap, No Icons on Tanah/Gudang) -->
        <div class="pt-2.5 border-t border-slate-100 flex flex-wrap items-center gap-1.5 text-xs font-bold">
            <span class="text-slate-400 text-[10px] uppercase tracking-wider mr-1 shrink-0">Filter Tipe:</span>
            <a href="{{ route('admin.properties', array_merge(request()->except('type'), ['page' => 1])) }}"
               class="px-3 py-1.5 rounded-lg transition-all {{ !request('type') ? 'bg-pink-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-pink-50 hover:text-pink-600' }}">
                Semua
            </a>
            <a href="{{ route('admin.properties', array_merge(request()->all(), ['type' => 'rumah', 'page' => 1])) }}"
               class="px-3 py-1.5 rounded-lg transition-all {{ request('type') === 'rumah' ? 'bg-pink-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-pink-50 hover:text-pink-600' }}">
                Rumah
            </a>
            <a href="{{ route('admin.properties', array_merge(request()->all(), ['type' => 'apartemen', 'page' => 1])) }}"
               class="px-3 py-1.5 rounded-lg transition-all {{ request('type') === 'apartemen' ? 'bg-pink-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-pink-50 hover:text-pink-600' }}">
                Apartemen
            </a>
            <a href="{{ route('admin.properties', array_merge(request()->all(), ['type' => 'ruko', 'page' => 1])) }}"
               class="px-3 py-1.5 rounded-lg transition-all {{ request('type') === 'ruko' ? 'bg-pink-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-pink-50 hover:text-pink-600' }}">
                Ruko
            </a>
            <a href="{{ route('admin.properties', array_merge(request()->all(), ['type' => 'tanah', 'page' => 1])) }}"
               class="px-3 py-1.5 rounded-lg transition-all {{ request('type') === 'tanah' ? 'bg-pink-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-pink-50 hover:text-pink-600' }}">
                Tanah
            </a>
            <a href="{{ route('admin.properties', array_merge(request()->all(), ['type' => 'gudang', 'page' => 1])) }}"
               class="px-3 py-1.5 rounded-lg transition-all {{ request('type') === 'gudang' ? 'bg-pink-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-pink-50 hover:text-pink-600' }}">
                Gudang
            </a>
            <a href="{{ route('admin.properties', array_merge(request()->all(), ['type' => 'villa', 'page' => 1])) }}"
               class="px-3 py-1.5 rounded-lg transition-all {{ request('type') === 'villa' ? 'bg-pink-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-pink-50 hover:text-pink-600' }}">
                Villa
            </a>
            <a href="{{ route('admin.properties', array_merge(request()->all(), ['type' => 'hotel', 'page' => 1])) }}"
               class="px-3 py-1.5 rounded-lg transition-all {{ request('type') === 'hotel' ? 'bg-pink-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-pink-50 hover:text-pink-600' }}">
                Hotel
            </a>
            <a href="{{ route('admin.properties', array_merge(request()->all(), ['type' => 'gedung', 'page' => 1])) }}"
               class="px-3 py-1.5 rounded-lg transition-all {{ request('type') === 'gedung' ? 'bg-pink-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-pink-50 hover:text-pink-600' }}">
                Gedung
            </a>
        </div>
    </div>

    <!-- Properties Grid (Card Properti Layout with Dashboard Actions) -->
    <div>
        @if($properties->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
                @foreach($properties as $property)
                    @php
                        $rowAgent = $property->agent ?? (auth()->user()->isAgent() ? auth()->user()->agent : null);
                        $rowShareUrl = $property->getShortShareUrl($rowAgent);
                        $rowDetailUrl = $rowAgent?->slug ? route('properties.show', ['slug' => $property->slug, 'agent' => $rowAgent->slug]) : route('properties.show', $property->slug);
                        $rowWaText = $property->generateCleanWaBroadcastText($rowAgent, true, $rowShareUrl);
                        $canManage = auth()->user()->isAdmin() || (auth()->user()->isAgent() && $property->agent_id === auth()->user()->agent_id);
                    @endphp

                    <x-property-card :property="$property">
                        <x-slot:actions>
                            <div class="space-y-2" x-data="{ copiedRow: false, openDup: false, openShare: false, copiedShareLink: false, copiedShareWa: false }">
                                <!-- Main Action Buttons (Replacing View Details) -->
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <!-- Tombol Share Listing (WhatsApp, Salin Link, Web Share) -->
                                        <div class="relative">
                                            <button @click="openShare = !openShare; openDup = false"
                                                    type="button"
                                                    class="w-9 h-9 rounded-xl border transition-all shadow-2xs flex items-center justify-center cursor-pointer hover:scale-105 active:scale-95"
                                                    :class="openShare ? 'bg-emerald-600 text-white border-emerald-600 shadow-xs' : 'bg-emerald-50 text-emerald-700 border-emerald-200/80 hover:bg-emerald-600 hover:text-white hover:border-emerald-600'"
                                                    title="Bagikan Listing (WhatsApp, Link, Sosmed)">
                                                <i class="fa-solid fa-share-nodes text-xs"></i>
                                            </button>

                                            <!-- Share Dropdown Menu -->
                                            <div x-show="openShare"
                                                 x-cloak
                                                 @click.away="openShare = false"
                                                 class="absolute left-0 bottom-full mb-2 w-64 bg-white rounded-2xl shadow-xl border border-slate-200 p-2 z-40 text-left text-xs font-semibold space-y-1">
                                                
                                                <div class="px-2.5 py-1.5 flex items-center justify-between border-b border-slate-100">
                                                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Bagikan Listing</span>
                                                    <span class="text-[9px] font-mono px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 font-bold">{{ $property->property_code }}</span>
                                                </div>

                                                <!-- 1. Kirim Langsung ke WhatsApp -->
                                                <a href="https://api.whatsapp.com/send?text={{ rawurlencode($rowWaText) }}"
                                                   target="_blank"
                                                   @click="openShare = false"
                                                   class="w-full px-2.5 py-2 hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 rounded-xl flex items-center gap-2.5 transition">
                                                    <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                                                        <i class="fa-brands fa-whatsapp text-sm"></i>
                                                    </div>
                                                    <div class="min-w-0">
                                                        <div class="font-bold text-xs text-slate-900 leading-tight">Kirim ke WhatsApp</div>
                                                        <div class="text-[10px] text-slate-400 font-normal">Buka chat WA dengan format rapi</div>
                                                    </div>
                                                </a>

                                                <!-- 2. Salin Link Listing -->
                                                <button type="button"
                                                        @click="navigator.clipboard.writeText('{{ $rowShareUrl }}'); copiedShareLink = true; setTimeout(() => { copiedShareLink = false; openShare = false; }, 1500)"
                                                        class="w-full text-left px-2.5 py-2 hover:bg-blue-50 text-slate-700 hover:text-blue-700 rounded-xl flex items-center gap-2.5 transition cursor-pointer">
                                                    <div class="w-7 h-7 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                                                        <i class="fa-solid" :class="copiedShareLink ? 'fa-check text-blue-600' : 'fa-link text-xs'"></i>
                                                    </div>
                                                    <div class="min-w-0 flex-1">
                                                        <div class="font-bold text-xs text-slate-900 leading-tight" x-text="copiedShareLink ? 'Link Berhasil Disalin!' : 'Salin Link Listing'">Salin Link Listing</div>
                                                        <div class="text-[10px] text-slate-400 font-normal truncate">{{ $rowShareUrl }}</div>
                                                    </div>
                                                </button>

                                                <!-- 3. Salin Teks Broadcast Lengkap -->
                                                <button type="button"
                                                        @click="navigator.clipboard.writeText(`{{ addslashes($rowWaText) }}`); copiedShareWa = true; setTimeout(() => { copiedShareWa = false; openShare = false; }, 1500)"
                                                        class="w-full text-left px-2.5 py-2 hover:bg-sky-50 text-slate-700 hover:text-sky-700 rounded-xl flex items-center gap-2.5 transition cursor-pointer">
                                                    <div class="w-7 h-7 rounded-lg bg-sky-100 text-sky-600 flex items-center justify-center shrink-0">
                                                        <i class="fa-solid" :class="copiedShareWa ? 'fa-check text-sky-600' : 'fa-copy text-xs'"></i>
                                                    </div>
                                                    <div class="min-w-0 flex-1">
                                                        <div class="font-bold text-xs text-slate-900 leading-tight" x-text="copiedShareWa ? 'Teks Broadcast Disalin!' : 'Salin Teks Broadcast'">Salin Teks Broadcast</div>
                                                        <div class="text-[10px] text-slate-400 font-normal">Teks lengkap + spek + link</div>
                                                    </div>
                                                </button>

                                                <!-- 4. Web Share Native (HP / Browser) -->
                                                <button type="button"
                                                        x-show="'share' in navigator"
                                                        @click="if (navigator.share) { navigator.share({ title: `{{ addslashes($property->share_headline) }}`, text: `{{ addslashes($property->share_headline . ' - ' . $property->formatted_price) }}`, url: '{{ $rowShareUrl }}' }).catch(() => {}); openShare = false; }"
                                                        class="w-full text-left px-2.5 py-2 hover:bg-purple-50 text-slate-700 hover:text-purple-700 rounded-xl flex items-center gap-2.5 transition cursor-pointer">
                                                    <div class="w-7 h-7 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center shrink-0">
                                                        <i class="fa-solid fa-share-from-square text-xs"></i>
                                                    </div>
                                                    <div class="min-w-0">
                                                        <div class="font-bold text-xs text-slate-900 leading-tight">Menu Share Perangkat</div>
                                                        <div class="text-[10px] text-slate-400 font-normal">Kirim via aplikasi lain / medsos</div>
                                                    </div>
                                                </button>

                                                <!-- 5. Buka Halaman Share & Generator Poster Story -->
                                                <a href="{{ $rowDetailUrl }}#share"
                                                   target="_blank"
                                                   @click="openShare = false"
                                                   class="w-full px-2.5 py-2 hover:bg-pink-50 text-slate-700 hover:text-pink-700 rounded-xl flex items-center gap-2.5 transition border-t border-slate-100 mt-1 pt-2">
                                                    <div class="w-7 h-7 rounded-lg bg-pink-100 text-pink-600 flex items-center justify-center shrink-0">
                                                        <i class="fa-solid fa-wand-magic-sparkles text-xs"></i>
                                                    </div>
                                                    <div class="min-w-0">
                                                        <div class="font-bold text-xs text-slate-900 leading-tight">Buat Poster Story</div>
                                                        <div class="text-[10px] text-slate-400 font-normal">Poster gambar IG &amp; Status WA</div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>

                                        <!-- Salin Teks WA Broadcast -->
                                        <button type="button"
                                                @click="navigator.clipboard.writeText(`{{ addslashes($rowWaText) }}`); copiedRow = true; setTimeout(() => copiedRow = false, 2000)"
                                                class="w-9 h-9 rounded-xl border transition-all shadow-2xs flex items-center justify-center cursor-pointer hover:scale-105 active:scale-95"
                                                :class="copiedRow ? 'bg-sky-600 text-white border-sky-600 shadow-xs' : 'bg-sky-50 text-sky-700 border-sky-200/80 hover:bg-sky-600 hover:text-white hover:border-sky-600'"
                                                title="Salin Teks Broadcast WA">
                                            <i :class="copiedRow ? 'fa-solid fa-check text-sm text-white' : 'fa-regular fa-copy text-sm'"></i>
                                        </button>

                                        <!-- Quick Duplicate Listing Button & Dropdown -->
                                        <div class="relative">
                                            <button @click="openDup = !openDup"
                                                    type="button"
                                                    class="w-9 h-9 rounded-xl bg-amber-50 text-amber-700 border border-amber-200/80 hover:bg-amber-500 hover:text-white hover:border-amber-500 transition-all shadow-2xs flex items-center justify-center cursor-pointer hover:scale-105 active:scale-95"
                                                    title="Duplikat Listing (Jual / Sewa)">
                                                <i class="fa-solid fa-clone text-xs"></i>
                                            </button>
                                            <div x-show="openDup"
                                                 x-cloak
                                                 @click.away="openDup = false"
                                                 class="absolute left-0 bottom-full mb-2 w-52 bg-white rounded-2xl shadow-xl border border-slate-200 py-1.5 z-30 text-left text-xs font-semibold">
                                                <div class="px-3.5 py-1.5 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-100">
                                                    Duplikat Listing Ke:
                                                </div>
                                                <form action="{{ route('admin.properties.duplicate', $property->id) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="target_type" value="{{ $property->listing_type === 'dijual' ? 'disewa' : 'dijual' }}">
                                                    <button type="submit" class="w-full text-left px-3.5 py-2 hover:bg-rose-50 hover:text-rose-600 flex items-center gap-2.5 transition cursor-pointer">
                                                        <i class="fa-solid fa-repeat text-rose-500 text-xs"></i>
                                                        <span>{{ $property->listing_type === 'dijual' ? 'Duplikat ke DISEWA' : 'Duplikat ke DIJUAL' }}</span>
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.properties.duplicate', $property->id) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="target_type" value="{{ $property->listing_type }}">
                                                    <button type="submit" class="w-full text-left px-3.5 py-2 hover:bg-slate-50 text-slate-700 hover:text-slate-900 flex items-center gap-2.5 transition cursor-pointer">
                                                        <i class="fa-solid fa-clone text-slate-400 text-xs"></i>
                                                        <span>Duplikat Jenis Sama</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- Lihat Listing di Website -->
                                        <a href="{{ $rowDetailUrl }}" target="_blank"
                                           class="w-9 h-9 rounded-xl bg-slate-100 text-slate-700 border border-slate-200 hover:text-blue-600 hover:bg-blue-50 hover:border-blue-200 transition-all shadow-2xs flex items-center justify-center hover:scale-105 active:scale-95"
                                           title="Lihat Website">
                                            <i class="fa-solid fa-eye text-xs"></i>
                                        </a>

                                        <!-- Catat Sewa & Deposit (Khusus Disewa) -->
                                        @if($property->listing_type === 'disewa')
                                            <a href="{{ route('admin.rentals.create', ['property_id' => $property->id]) }}"
                                               class="w-9 h-9 rounded-xl bg-pink-50 text-pink-700 border border-pink-200/80 hover:bg-pink-600 hover:text-white hover:border-pink-600 transition-all shadow-2xs flex items-center justify-center hover:scale-105 active:scale-95"
                                               title="Catat Penyewa &amp; Deposit Unit Ini">
                                                <i class="fa-solid fa-key text-xs"></i>
                                            </a>
                                        @endif

                                        @if($canManage)
                                            <!-- Edit Listing -->
                                            <a href="{{ route('admin.properties.edit', $property->id) }}"
                                               class="w-9 h-9 rounded-xl bg-slate-100 text-slate-700 border border-slate-200 hover:text-pink-600 hover:bg-pink-50 hover:border-pink-200 transition-all shadow-2xs flex items-center justify-center hover:scale-105 active:scale-95"
                                               title="Edit Listing">
                                                <i class="fa-solid fa-pen-to-square text-xs"></i>
                                            </a>

                                            <!-- Hapus Listing -->
                                            <form action="{{ route('admin.properties.destroy', $property->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus listing ini?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="w-9 h-9 rounded-xl bg-slate-100 text-slate-700 border border-slate-200 hover:text-red-600 hover:bg-red-50 hover:border-red-200 transition-all shadow-2xs flex items-center justify-center cursor-pointer hover:scale-105 active:scale-95"
                                                        title="Hapus Listing">
                                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                                </button>
                                            </form>
                                        @else
                                            <!-- Terkunci -->
                                            <span class="w-9 h-9 rounded-xl bg-slate-50 text-slate-300 border border-slate-200 flex items-center justify-center cursor-not-allowed"
                                                  title="Listing milik agen lain (Hanya baca &amp; share)">
                                                <i class="fa-solid fa-lock text-xs"></i>
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Status & Upgrade Featured Pill -->
                                    <div class="flex flex-col items-end gap-1 shrink-0">
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold {{ $property->status === 'active' ? 'bg-pink-100 text-pink-800 border border-pink-200' : 'bg-slate-200 text-slate-700' }}">
                                            {{ strtoupper($property->status) }}
                                        </span>

                                        @if(isset($pendingFeaturedPropertyIds) && in_array($property->id, $pendingFeaturedPropertyIds) && !$property->is_featured)
                                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-pink-50 text-pink-700 border border-pink-200 inline-flex items-center gap-1" title="Menunggu verifikasi admin untuk dijadikan Featured Listing">
                                                <i class="fa-solid fa-clock text-[8px]"></i> Pending
                                            </span>
                                        @elseif($canManage && isset($isAgent) && $isAgent && !$property->is_featured)
                                            <button type="button"
                                                    @click="activeFeaturedPropId = {{ $property->id }}; activeFeaturedPropTitle = '{{ addslashes($property->title) }}'; openFeaturedModal = true;"
                                                    class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-pink-50 hover:bg-pink-100 text-pink-700 border border-pink-200 inline-flex items-center gap-1 transition-all cursor-pointer"
                                                    title="Promosikan listing ini menjadi Featured Property di homepage">
                                                <i class="fa-solid fa-rocket text-[8px]"></i> Upgrade
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </x-slot:actions>
                    </x-property-card>
                @endforeach
            </div>

            <div class="mt-5 bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
                {{ $properties->links() }}
            </div>
        @else
            <div class="bg-white rounded-3xl border border-slate-200 p-10 text-center text-slate-400 shadow-xs">
                Belum ada properti ditemukan. Silakan tambah listing baru.
            </div>
        @endif
    </div>

    <!-- Modal 1: Topup Kuota Listing Agen -->
    <div x-show="openTopupModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="openTopupModal = false" class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 max-w-lg w-full shadow-2xl relative text-xs">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-xl bg-purple-600 text-white flex items-center justify-center text-base">
                        <i class="fa-solid fa-cart-plus"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-sm">Topup Kuota Listing Agen</h3>
                        <p class="text-[11px] text-slate-400">Tambah slot kuota untuk mempublikasikan lebih banyak properti</p>
                    </div>
                </div>
                <button @click="openTopupModal = false" class="p-1 text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="p-4 bg-gradient-to-r from-purple-50 to-pink-50 rounded-2xl border border-purple-200 mb-4 flex items-center justify-between">
                <div>
                    <div class="text-[10px] uppercase font-bold text-purple-700">Paket Tambahan Kuota</div>
                    <div class="text-base font-extrabold text-slate-900 mt-0.5">+{{ $topupQuota ?? 5 }} Slot Listing Baru</div>
                    <div class="text-[11px] text-slate-500">Masa aktif selamanya / tidak hangus</div>
                </div>
                <div class="text-right">
                    <div class="text-lg font-black text-purple-700">Rp {{ number_format($topupPrice ?? 100000, 0, ',', '.') }}</div>
                    <span class="text-[10px] text-emerald-600 font-bold bg-emerald-100 px-2 py-0.5 rounded-full">Sekali Bayar</span>
                </div>
            </div>

            <!-- Payment QRIS & Bank Info -->
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 mb-4 space-y-3" x-data="{ modalBcaCopied: false, modalOtherCopied: false }">
                <div class="font-bold text-slate-800 flex items-center gap-1.5">
                    <i class="fa-solid fa-wallet text-pink-600"></i> Rekening Resmi &amp; QRIS Pembayaran:
                </div>
                <div class="flex flex-col sm:flex-row items-center gap-3">
                    @if($qrisImage)
                        <div class="bg-white p-2 rounded-xl border border-slate-200 shadow-2xs shrink-0 text-center">
                            <img src="{{ $qrisImage }}" alt="QRIS" class="w-28 h-28 object-contain mx-auto">
                            <span class="text-[9px] font-bold text-slate-400 mt-0.5 block">Scan Bebas Biaya</span>
                        </div>
                    @endif
                    <div class="space-y-1.5 text-xs w-full">
                        <div>
                            <span class="text-slate-400 block text-[10px]">Merchant QRIS:</span>
                            <strong class="text-slate-800 text-xs">{{ $merchantName }}</strong>
                        </div>

                        <!-- Rekening BCA -->
                        @if(isset($bcaAccount) && !empty($bcaAccount['number']))
                            <div class="p-2 bg-blue-50/70 border border-blue-200 rounded-lg flex items-center justify-between gap-2">
                                <div>
                                    <span class="text-[9px] font-bold text-blue-700 uppercase block">{{ $bcaAccount['bank_name'] }} (Utama)</span>
                                    <span class="font-mono font-bold text-slate-900 text-xs">{{ $bcaAccount['number'] }}</span>
                                    <span class="text-[10px] text-slate-500 block">a.n {{ $bcaAccount['name'] }}</span>
                                </div>
                                <button type="button"
                                        @click="navigator.clipboard.writeText('{{ $bcaAccount['number'] }}'); modalBcaCopied = true; setTimeout(() => modalBcaCopied = false, 1800)"
                                        class="px-2 py-1 rounded-md bg-blue-600 text-white text-[10px] font-bold shrink-0">
                                    <span x-text="modalBcaCopied ? 'Tersalin!' : 'Salin'"></span>
                                </button>
                            </div>
                        @endif

                        <!-- Rekening Bank Lain -->
                        @if(isset($otherBankAccount) && !empty($otherBankAccount['number']))
                            <div class="p-2 bg-slate-100 border border-slate-200 rounded-lg flex items-center justify-between gap-2">
                                <div>
                                    <span class="text-[9px] font-bold text-slate-700 uppercase block">{{ $otherBankAccount['bank_name'] }}</span>
                                    <span class="font-mono font-bold text-slate-900 text-xs">{{ $otherBankAccount['number'] }}</span>
                                    <span class="text-[10px] text-slate-500 block">a.n {{ $otherBankAccount['name'] }}</span>
                                </div>
                                <button type="button"
                                        @click="navigator.clipboard.writeText('{{ $otherBankAccount['number'] }}'); modalOtherCopied = true; setTimeout(() => modalOtherCopied = false, 1800)"
                                        class="px-2 py-1 rounded-md bg-slate-700 text-white text-[10px] font-bold shrink-0">
                                    <span x-text="modalOtherCopied ? 'Tersalin!' : 'Salin'"></span>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
                @if($paymentInstructions)
                    <div class="text-[10px] text-slate-500 pt-2 border-t border-slate-200 leading-relaxed">
                        {!! nl2br(e($paymentInstructions)) !!}
                    </div>
                @endif
            </div>

            <!-- Upload Form -->
            <form action="{{ route('admin.agent.topup-quota') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Unggah Bukti Struk Transfer / Scan QRIS *</label>
                    <input type="file" name="payment_proof" accept="image/*,.pdf" required class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-100 file:text-purple-700 hover:file:bg-purple-200 cursor-pointer border border-slate-200 rounded-xl bg-slate-50 p-1">
                    <span class="text-[10px] text-slate-400 mt-1 block">Format: JPG, PNG, atau PDF (Maks 5MB)</span>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                    <input type="text" name="notes" placeholder="Contoh: Transfer atas nama Budi Santoso" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:outline-none">
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                    <button type="button" @click="openTopupModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 font-bold">Batal</button>
                    <button type="submit" class="px-6 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold shadow-md flex items-center gap-1.5">
                        <i class="fa-solid fa-paper-plane"></i> Kirim Konfirmasi Topup
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 2: Upgrade Featured Listing Properti -->
    <div x-show="openFeaturedModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="openFeaturedModal = false" class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 max-w-lg w-full shadow-2xl relative text-xs">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center text-base">
                        <i class="fa-solid fa-star"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-sm">Upgrade ke Featured Listing</h3>
                        <p class="text-[11px] text-slate-400">Tampilkan listing Anda di posisi teratas homepage</p>
                    </div>
                </div>
                <button @click="openFeaturedModal = false" class="p-1 text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="mb-4 p-3 bg-slate-50 rounded-xl border border-slate-200">
                <div class="text-[10px] text-slate-400 uppercase font-bold">Properti yang dipromosikan:</div>
                <div class="font-extrabold text-slate-900 text-xs mt-0.5" x-text="activeFeaturedPropTitle"></div>
            </div>

            <div class="p-4 bg-gradient-to-r from-amber-50 to-orange-50 rounded-2xl border border-amber-200 mb-4 flex items-center justify-between">
                <div>
                    <div class="text-[10px] uppercase font-bold text-amber-800">Biaya Upgrade Featured</div>
                    <div class="text-base font-extrabold text-slate-900 mt-0.5">Tampil Unggulan {{ $featuredDuration ?? 30 }} Hari</div>
                    <div class="text-[11px] text-slate-500">Mendapatkan lencana khusus ⭐ FEATURED</div>
                </div>
                <div class="text-right">
                    <div class="text-lg font-black text-amber-600">Rp {{ number_format($featuredPrice ?? 50000, 0, ',', '.') }}</div>
                    <span class="text-[10px] text-amber-800 font-bold bg-amber-100 px-2 py-0.5 rounded-full">Per Listing</span>
                </div>
            </div>

            <!-- Payment QRIS & Bank Info -->
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 mb-4 space-y-3">
                <div class="font-bold text-slate-800 flex items-center gap-1.5">
                    <i class="fa-solid fa-qrcode text-amber-600"></i> Cara Pembayaran QRIS / Transfer:
                </div>
                <div class="flex flex-col sm:flex-row items-center gap-4">
                    @if($qrisImage)
                        <img src="{{ $qrisImage }}" alt="QRIS" class="w-32 h-32 object-contain bg-white p-2 rounded-xl border border-slate-200 shrink-0">
                    @endif
                    <div class="space-y-1.5 text-[11px] text-slate-600 w-full">
                        <div>
                            <span class="text-slate-400 block text-[10px]">Merchant QRIS:</span>
                            <strong class="text-slate-800">{{ $merchantName }}</strong>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px]">Transfer Bank Rekening Resmi:</span>
                            <div class="font-extrabold text-slate-900 font-mono text-xs">{{ $bankAccNum }}</div>
                            <div class="text-slate-500 text-[10px]">{{ $bankName }} a.n {{ $bankAccName }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upload Form -->
            <form :action="'{{ url('admin/properties') }}/' + activeFeaturedPropId + '/request-featured'" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Unggah Bukti Struk Transfer / Scan QRIS *</label>
                    <input type="file" name="payment_proof" accept="image/*,.pdf" required class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-amber-100 file:text-amber-800 hover:file:bg-amber-200 cursor-pointer border border-slate-200 rounded-xl bg-slate-50 p-1">
                    <span class="text-[10px] text-slate-400 mt-1 block">Format: JPG, PNG, atau PDF (Maks 5MB)</span>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                    <input type="text" name="notes" placeholder="Contoh: Pembayaran upgrade listing Citraland" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:outline-none">
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                    <button type="button" @click="openFeaturedModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 font-bold">Batal</button>
                    <button type="submit" class="px-6 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-extrabold shadow-md flex items-center gap-1.5">
                        <i class="fa-solid fa-paper-plane"></i> Ajukan Featured Listing
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

