@extends('layouts.admin')

@section('title', 'Admin Dashboard - ' . \App\Models\Setting::getSiteName() . '')
@section('page_title', 'Ringkasan Portal & Statistik')

@section('content')
@if(isset($isAgent) && $isAgent && isset($currentAgent) && $currentAgent)
    <!-- Agent Quota Summary Banner -->
    <div class="mb-5 p-4 sm:p-5 rounded-2xl bg-gradient-to-r from-pink-600 via-rose-600 to-pink-500 text-white shadow-md flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-xs flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-cart-plus text-white"></i>
            </div>
            <div>
                <div class="text-[11px] font-extrabold uppercase tracking-wider text-pink-100 flex items-center gap-1.5">
                    <span>Status Kuota Listing Anda</span>
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                </div>
                <div class="text-base sm:text-lg font-black mt-0.5">
                    Tersedia: {{ $currentAgent->remaining_quota }} Slot <span class="font-normal text-xs text-pink-100">(Terpakai {{ $currentAgent->used_quota }} dari {{ $currentAgent->listing_quota }} listing)</span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2 self-start sm:self-auto shrink-0">
            <a href="{{ route('admin.agent.topup') }}"
               class="px-4 py-2 rounded-xl bg-white hover:bg-pink-50 text-pink-700 font-extrabold text-xs shadow-xs transition-all flex items-center gap-1.5">
                <i class="fa-solid fa-bolt text-pink-600"></i> Topup Kuota (+{{ $topupQuota ?? 5 }} Slot)
            </a>
            <a href="{{ route('admin.properties.create') }}"
               class="px-4 py-2 rounded-xl bg-pink-700/80 hover:bg-pink-700 text-white border border-pink-400/40 font-bold text-xs transition-all flex items-center gap-1.5">
                <i class="fa-solid fa-plus"></i> Tambah Listing
            </a>
        </div>
    </div>
@endif

<!-- Stats Grid -->
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
        <div class="text-xs font-bold text-slate-400 uppercase">{{ isset($isAgent) && $isAgent ? 'Listing Saya' : 'Total Listing' }}</div>
        <div class="text-2xl font-black text-slate-900 mt-1">{{ $stats['total_properties'] }}</div>
        <div class="text-[10px] text-emerald-600 font-bold mt-1">{{ $stats['active_properties'] }} Aktif</div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
        <div class="text-xs font-bold text-slate-400 uppercase">Dijual</div>
        <div class="text-2xl font-black text-rose-600 mt-1">{{ $stats['for_sale'] }}</div>
        <div class="text-[10px] text-slate-400 mt-1">Marketplace Jual</div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
        <div class="text-xs font-bold text-slate-400 uppercase">Disewa</div>
        <div class="text-2xl font-black text-amber-500 mt-1">{{ $stats['for_rent'] }}</div>
        <div class="text-[10px] text-slate-400 mt-1">Sewa &amp; Rental</div>
    </div>

    @if(isset($isAgent) && $isAgent)
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
            <div class="text-xs font-bold text-slate-400 uppercase">Jejaring {{ \App\Models\Setting::getSiteName() }}</div>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ \App\Models\Property::count() }}</div>
            <div class="text-[10px] text-blue-600 font-bold mt-1">
                <a href="{{ route('admin.properties', ['scope' => 'all']) }}" class="hover:underline">Semua Listing &rarr;</a>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
            <div class="text-xs font-bold text-slate-400 uppercase">Pesan Masuk</div>
            <div class="text-2xl font-black text-blue-600 mt-1">{{ $stats['total_inquiries'] }}</div>
            <div class="text-[10px] text-rose-600 font-bold mt-1">{{ $stats['new_inquiries'] }} Perlu Dihubungi</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs flex flex-col justify-center items-center text-center">
            <a href="{{ route('admin.properties.create') }}" class="w-full py-2.5 px-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs shadow-md transition-all flex items-center justify-center gap-1.5">
                <i class="fa-solid fa-plus-circle"></i> Tambah Listing
            </a>
            <div class="text-[9px] text-slate-400 mt-1">Listing atas nama Anda</div>
        </div>
    @else
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
            <div class="text-xs font-bold text-slate-400 uppercase">Agen Terdaftar</div>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ $stats['total_agents'] }}</div>
            <div class="text-[10px] text-slate-400 mt-1">Konsultan {{ \App\Models\Setting::getSiteName() }}</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
            <div class="text-xs font-bold text-slate-400 uppercase">Pesan &amp; Leads</div>
            <div class="text-2xl font-black text-blue-600 mt-1">{{ $stats['total_inquiries'] }}</div>
            <div class="text-[10px] text-rose-600 font-bold mt-1">{{ $stats['new_inquiries'] }} Perlu Follow Up</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
            <div class="text-xs font-bold text-slate-400 uppercase">Titip Properti</div>
            <div class="text-2xl font-black text-purple-600 mt-1">{{ $stats['total_submissions'] }}</div>
            <div class="text-[10px] text-amber-600 font-bold mt-1">{{ $stats['pending_submissions'] }} Menunggu Kurasi</div>
        </div>
    @endif
</div>

<!-- Category Distribution Quick Links -->
<div class="mt-4 grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
    <a href="{{ route('admin.properties', ['type' => 'rumah']) }}" class="p-3 bg-white rounded-2xl border border-slate-200 hover:border-pink-500 hover:shadow-xs transition-all flex flex-col sm:flex-row items-center gap-3 group">
        <div class="w-12 h-12 rounded-xl bg-pink-50 text-pink-600 flex items-center justify-center text-xl group-hover:bg-pink-600 group-hover:text-white transition-all shrink-0 shadow-2xs">
            <i class="fa-solid fa-house-chimney"></i>
        </div>
        <div class="min-w-0 text-center sm:text-left">
            <div class="text-xs font-bold text-slate-800 group-hover:text-pink-600 truncate">Rumah</div>
            <div class="text-[10px] text-slate-400">Listing &rarr;</div>
        </div>
    </a>

    <a href="{{ route('admin.properties', ['type' => 'apartemen']) }}" class="p-3 bg-white rounded-2xl border border-slate-200 hover:border-pink-500 hover:shadow-xs transition-all flex flex-col sm:flex-row items-center gap-3 group">
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl group-hover:bg-blue-600 group-hover:text-white transition-all shrink-0 shadow-2xs">
            <i class="fa-solid fa-building"></i>
        </div>
        <div class="min-w-0 text-center sm:text-left">
            <div class="text-xs font-bold text-slate-800 group-hover:text-blue-600 truncate">Apartemen</div>
            <div class="text-[10px] text-slate-400">Listing &rarr;</div>
        </div>
    </a>

    <a href="{{ route('admin.properties', ['type' => 'tanah']) }}" class="p-3 bg-white rounded-2xl border border-slate-200 hover:border-pink-500 hover:shadow-xs transition-all flex flex-col sm:flex-row items-center gap-3 group">
        <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl group-hover:bg-amber-600 group-hover:text-white transition-all shrink-0 shadow-2xs">
            <i class="fa-solid fa-map"></i>
        </div>
        <div class="min-w-0 text-center sm:text-left">
            <div class="text-xs font-bold text-slate-800 group-hover:text-amber-600 truncate">Tanah</div>
            <div class="text-[10px] text-pink-600 font-bold">{{ $stats['tanah_count'] ?? 0 }} Listing</div>
        </div>
    </a>

    <a href="{{ route('admin.properties', ['type' => 'gudang']) }}" class="p-3 bg-white rounded-2xl border border-slate-200 hover:border-pink-500 hover:shadow-xs transition-all flex flex-col sm:flex-row items-center gap-3 group">
        <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl group-hover:bg-purple-600 group-hover:text-white transition-all shrink-0 shadow-2xs">
            <i class="fa-solid fa-warehouse"></i>
        </div>
        <div class="min-w-0 text-center sm:text-left">
            <div class="text-xs font-bold text-slate-800 group-hover:text-purple-600 truncate">Gudang</div>
            <div class="text-[10px] text-pink-600 font-bold">{{ $stats['gudang_count'] ?? 0 }} Listing</div>
        </div>
    </a>

    <a href="{{ route('admin.properties', ['type' => 'ruko']) }}" class="p-3 bg-white rounded-2xl border border-slate-200 hover:border-pink-500 hover:shadow-xs transition-all flex flex-col sm:flex-row items-center gap-3 group">
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl group-hover:bg-emerald-600 group-hover:text-white transition-all shrink-0 shadow-2xs">
            <i class="fa-solid fa-store"></i>
        </div>
        <div class="min-w-0 text-center sm:text-left">
            <div class="text-xs font-bold text-slate-800 group-hover:text-emerald-600 truncate">Ruko</div>
            <div class="text-[10px] text-slate-400">Listing &rarr;</div>
        </div>
    </a>

    <a href="{{ route('admin.properties', ['type' => 'hotel']) }}" class="p-3 bg-white rounded-2xl border border-slate-200 hover:border-pink-500 hover:shadow-xs transition-all flex flex-col sm:flex-row items-center gap-3 group">
        <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl group-hover:bg-indigo-600 group-hover:text-white transition-all shrink-0 shadow-2xs">
            <i class="fa-solid fa-hotel"></i>
        </div>
        <div class="min-w-0 text-center sm:text-left">
            <div class="text-xs font-bold text-slate-800 group-hover:text-indigo-600 truncate">Hotel</div>
            <div class="text-[10px] text-slate-400">Listing &rarr;</div>
        </div>
    </a>

    <a href="{{ route('admin.properties', ['type' => 'gedung']) }}" class="p-3 bg-white rounded-2xl border border-slate-200 hover:border-pink-500 hover:shadow-xs transition-all flex flex-col sm:flex-row items-center gap-3 group">
        <div class="w-12 h-12 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center text-xl group-hover:bg-cyan-600 group-hover:text-white transition-all shrink-0 shadow-2xs">
            <i class="fa-solid fa-city"></i>
        </div>
        <div class="min-w-0 text-center sm:text-left">
            <div class="text-xs font-bold text-slate-800 group-hover:text-cyan-600 truncate">Gedung</div>
            <div class="text-[10px] text-slate-400">Listing &rarr;</div>
        </div>
    </a>

    <a href="{{ route('admin.properties', ['type' => 'villa']) }}" class="p-3 bg-white rounded-2xl border border-slate-200 hover:border-pink-500 hover:shadow-xs transition-all flex flex-col sm:flex-row items-center gap-3 group">
        <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl group-hover:bg-rose-600 group-hover:text-white transition-all shrink-0 shadow-2xs">
            <i class="fa-solid fa-umbrella-beach"></i>
        </div>
        <div class="min-w-0 text-center sm:text-left">
            <div class="text-xs font-bold text-slate-800 group-hover:text-rose-600 truncate">Villa</div>
            <div class="text-[10px] text-slate-400">Listing &rarr;</div>
        </div>
    </a>
</div>

<!-- Quick CTA Bar -->
<div class="mt-4 bg-white rounded-2xl border border-pink-200 p-4 shadow-xs flex flex-wrap items-center justify-between gap-4">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-pink-100 text-pink-600 flex items-center justify-center text-lg">
            <i class="fa-solid fa-key"></i>
        </div>
        <div>
            <div class="flex items-center gap-2">
                <h3 class="text-sm font-extrabold text-slate-900">Data Penyewa &amp; Uang Deposit</h3>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-pink-50 text-pink-700 border border-pink-200">
                    {{ $stats['total_rentals'] ?? 0 }} Penyewa Aktif
                </span>
                @if(($stats['expiring_rentals'] ?? 0) > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                        <i class="fa-solid fa-bell"></i> {{ $stats['expiring_rentals'] }} Masa Sewa Mau Habis
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-500 mt-0.5">Pantau tanggal jatuh tempo sewa properti, pengembalian uang deposit, dan kirim pengingat via WhatsApp.</p>
        </div>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.rentals.create') }}" class="px-3.5 py-2 rounded-xl bg-white border border-pink-300 text-pink-700 hover:bg-pink-50 font-bold text-xs shadow-xs transition-all flex items-center gap-1.5">
            <i class="fa-solid fa-user-plus"></i> Catat Penyewa
        </a>
        <a href="{{ route('admin.rentals.index') }}" class="px-3.5 py-2 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-xs shadow-xs transition-all flex items-center gap-1.5">
            <i class="fa-solid fa-table-list"></i> Buka Manajemen Sewa &rarr;
        </a>
    </div>
</div>

<div class="mt-4 flex flex-wrap items-center justify-between gap-4 bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg">
            <i class="fa-solid fa-house-medical"></i>
        </div>
        <div>
            <h3 class="text-sm font-extrabold text-slate-900">Kelola Listing Properti Anda</h3>
            <p class="text-xs text-slate-400">Tambah listing baru dengan foto, spesifikasi lengkap, dan integrasi WhatsApp agen.</p>
        </div>
    </div>

    <div class="flex items-center gap-2">
        <a href="{{ route('admin.properties.create') }}" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-sm transition-all flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> Tambah Listing Baru
        </a>
    </div>
</div>

<!-- Two Columns: Recent Listings & Recent Inquiries -->
<div class="mt-8 grid grid-cols-1 lg:grid-cols-12 gap-8">
    <!-- Recent Listings (7 cols) -->
    <div class="lg:col-span-7 bg-white rounded-3xl border border-slate-200 p-6 shadow-xs">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
            <h3 class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                <i class="fa-solid fa-house-chimney text-pink-600 text-base"></i> Listing Properti Terbaru
            </h3>
            <a href="{{ route('admin.properties') }}" class="text-xs font-bold text-pink-600 hover:underline">
                Lihat Semua &rarr;
            </a>
        </div>

        <div class="space-y-3">
            @forelse($recentProperties as $prop)
                <div class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-50 border border-slate-100 hover:border-pink-200 hover:bg-white transition-all shadow-2xs">
                    <div class="flex items-center gap-3.5 min-w-0">
                        <img src="{{ $prop->primary_image }}" alt="{{ $prop->title }}" class="w-14 h-14 rounded-xl object-cover shrink-0 border border-slate-200 shadow-2xs">
                        <div class="min-w-0">
                            <h4 class="text-xs font-bold text-slate-900 truncate hover:text-pink-600">
                                <a href="{{ route('properties.show', $prop->slug) }}" target="_blank">{{ $prop->title }}</a>
                            </h4>
                            <div class="text-[11px] text-slate-500 flex items-center gap-2 mt-1">
                                <span class="font-extrabold text-pink-600">{{ $prop->formatted_price }}</span>
                                <span>&bull;</span>
                                <span class="uppercase text-[10px] font-bold bg-white px-1.5 py-0.5 rounded border border-slate-200 text-slate-600">{{ $prop->property_type }}</span>
                                <span>&bull;</span>
                                <span class="truncate"><i class="fa-solid fa-location-dot text-[10px] text-slate-400 mr-0.5"></i>{{ $prop->city }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        @php
                            $dashAgent = $prop->agent ?? (auth()->user()->isAgent() ? auth()->user()->agent : null);
                            $dashShareUrl = $prop->getShortShareUrl($dashAgent);
                            $dashWaText = $prop->generateCleanWaBroadcastText($dashAgent, true, $dashShareUrl);
                        @endphp
                        <a href="https://api.whatsapp.com/send?text={{ rawurlencode($dashWaText) }}" target="_blank" class="p-2.5 rounded-xl bg-white border border-slate-200 text-emerald-600 hover:text-white hover:bg-emerald-600 hover:border-emerald-600 transition shadow-2xs" title="Share ke WhatsApp">
                            <i class="fa-brands fa-whatsapp text-sm"></i>
                        </a>
                        <a href="{{ route('properties.show', $prop->slug) }}" target="_blank" class="p-2.5 rounded-xl bg-white border border-slate-200 text-slate-600 hover:text-blue-600 hover:bg-blue-50 transition shadow-2xs" title="Lihat Website">
                            <i class="fa-solid fa-eye text-sm"></i>
                        </a>
                        <a href="{{ route('admin.properties.edit', $prop->id) }}" class="p-2.5 rounded-xl bg-white border border-slate-200 text-slate-600 hover:text-pink-600 hover:bg-pink-50 transition shadow-2xs" title="Edit Listing">
                            <i class="fa-solid fa-pen-to-square text-sm"></i>
                        </a>
                    </div>
                </div>
            @empty
                <p class="text-xs text-slate-400 text-center py-4">Belum ada properti terdaftar.</p>
            @endforelse
        </div>
    </div>

    <!-- Recent Inquiries (5 cols) -->
    <div class="lg:col-span-5 bg-white rounded-3xl border border-slate-200 p-6 shadow-xs">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
            <h3 class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                <i class="fa-solid fa-envelope text-blue-600 text-base"></i> Leads &amp; Survey Terbaru
            </h3>
            <a href="{{ route('admin.inquiries') }}" class="text-xs font-bold text-blue-600 hover:underline">
                Kelola Leads &rarr;
            </a>
        </div>

        <div class="space-y-3">
            @forelse($recentInquiries as $inq)
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 hover:border-blue-200 hover:bg-white transition-all shadow-2xs">
                    <div class="flex justify-between items-start gap-2">
                        <div>
                            <span class="font-bold text-xs text-slate-900">{{ $inq->name }}</span>
                            <div class="text-[10px] text-slate-400 mt-0.5">{{ $inq->phone }} &bull; {{ $inq->email }}</div>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            @if(!empty($inq->phone))
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $inq->phone) }}" target="_blank" class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition shadow-2xs" title="Chat WhatsApp">
                                    <i class="fa-brands fa-whatsapp text-sm"></i>
                                </a>
                            @endif
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $inq->status === 'new' ? 'bg-rose-100 text-rose-700' : 'bg-slate-200 text-slate-700' }}">
                                {{ strtoupper($inq->status) }}
                            </span>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-slate-600 line-clamp-2">"{{ $inq->message }}"</p>
                    <div class="mt-2 text-[10px] text-slate-400">
                        Properti: <span class="font-semibold text-slate-700">{{ $inq->property ? $inq->property->title : '-' }}</span>
                    </div>
                </div>
            @empty
                <p class="text-xs text-slate-400 text-center py-4">Belum ada leads masuk.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

