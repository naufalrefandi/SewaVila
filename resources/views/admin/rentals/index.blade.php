@extends('layouts.admin')

@section('title', 'Data Penyewa & Deposit - ' . \App\Models\Setting::getSiteName() . '')
@section('page_title', 'Data Penyewa & Manajemen Sewa')

@section('content')
<div class="space-y-6">
    <!-- Header Banner -->
    <div class="bg-white rounded-3xl p-6 border border-pink-100 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black tracking-wide bg-pink-100 text-pink-700 uppercase">
                    <i class="fa-solid fa-key"></i> Real-Estate Lease CRM
                </span>
                <span class="text-xs text-slate-400">Pencatatan Sewa &amp; Uang Jaminan</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 mt-2">Daftar Penyewa &amp; Monitoring Masa Sewa</h1>
            <p class="text-sm text-slate-500 mt-1">Pantau periode sewa ("sampai kapan"), kelola uang deposit jaminan, dan kirim pengingat WhatsApp perpanjangan kontrak.</p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('admin.rentals.create') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-pink-600 hover:bg-pink-700 text-white font-black text-sm shadow-md shadow-pink-600/20 transition-all">
                <i class="fa-solid fa-user-plus"></i>
                <span>+ Catat Penyewa Baru</span>
            </a>
        </div>
    </div>

    <!-- Alert Success -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-pink-50 border border-pink-200 text-pink-800 text-sm font-bold flex items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-2.5">
                <i class="fa-solid fa-circle-check text-pink-600 text-lg"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-pink-400 hover:text-pink-700">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    <!-- 4 KPI Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Penyewa Aktif -->
        <div class="bg-white rounded-2xl p-5 border border-pink-100 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-black text-slate-400 uppercase tracking-wider">Penyewa Aktif</span>
                <div class="w-8 h-8 rounded-xl bg-pink-50 text-pink-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900 mt-2">{{ $stats['total_active'] }}</div>
            <div class="text-[11px] font-bold text-pink-600 mt-1 flex items-center gap-1">
                <i class="fa-solid fa-circle-dot text-[8px]"></i> Sedang menempati unit
            </div>
        </div>

        <!-- 2. Segera Berakhir (< 30 Hari) -->
        <div class="bg-white rounded-2xl p-5 border {{ $stats['expiring_soon'] > 0 ? 'border-amber-300 ring-2 ring-amber-100' : 'border-pink-100' }} shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-black text-slate-400 uppercase tracking-wider">Mau Habis (&le; 30 Hari)</span>
                <div class="w-8 h-8 rounded-xl {{ $stats['expiring_soon'] > 0 ? 'bg-amber-100 text-amber-600' : 'bg-pink-50 text-pink-600' }} flex items-center justify-center text-sm">
                    <i class="fa-solid fa-clock"></i>
                </div>
            </div>
            <div class="text-2xl font-black {{ $stats['expiring_soon'] > 0 ? 'text-amber-600' : 'text-slate-900' }} mt-2">
                {{ $stats['expiring_soon'] }}
            </div>
            <div class="text-[11px] font-bold {{ $stats['expiring_soon'] > 0 ? 'text-amber-600' : 'text-slate-400' }} mt-1">
                Perlu follow-up perpanjangan
            </div>
        </div>

        <!-- 3. Total Deposit Ditahan -->
        <div class="bg-white rounded-2xl p-5 border border-pink-100 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-black text-slate-400 uppercase tracking-wider">Total Deposit Ditahan</span>
                <div class="w-8 h-8 rounded-xl bg-pink-50 text-pink-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-vault"></i>
                </div>
            </div>
            <div class="text-xl font-black text-pink-600 mt-2">
                Rp {{ number_format($stats['total_deposit_held'], 0, ',', '.') }}
            </div>
            <div class="text-[11px] font-bold text-slate-400 mt-1">
                Uang jaminan sewa aktif
            </div>
        </div>

        <!-- 4. Sewa Selesai -->
        <div class="bg-white rounded-2xl p-5 border border-pink-100 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-black text-slate-400 uppercase tracking-wider">Sewa Selesai</span>
                <div class="w-8 h-8 rounded-xl bg-pink-50 text-pink-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900 mt-2">{{ $stats['completed'] }}</div>
            <div class="text-[11px] font-bold text-slate-400 mt-1">
                Riwayat sewa tuntas
            </div>
        </div>
    </div>

    <!-- Filter Tabs & Search Bar -->
    <div class="bg-white rounded-2xl border border-pink-100 p-4 shadow-xs space-y-4">
        <!-- Status Tabs -->
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-pink-100 pb-3">
            <div class="flex flex-wrap items-center gap-1.5">
                <a href="{{ route('admin.rentals.index', array_merge(request()->except('tab', 'page'), ['tab' => 'all'])) }}"
                   class="px-3.5 py-1.5 rounded-xl text-xs font-black transition-all {{ $tab === 'all' ? 'bg-pink-600 text-white shadow-xs' : 'bg-pink-50/60 text-slate-600 hover:bg-pink-100/60' }}">
                    Semua ({{ $stats['all_count'] }})
                </a>
                <a href="{{ route('admin.rentals.index', array_merge(request()->except('tab', 'page'), ['tab' => 'active'])) }}"
                   class="px-3.5 py-1.5 rounded-xl text-xs font-black transition-all {{ $tab === 'active' ? 'bg-pink-600 text-white shadow-xs' : 'bg-pink-50/60 text-slate-600 hover:bg-pink-100/60' }}">
                    Aktif ({{ $stats['total_active'] }})
                </a>
                <a href="{{ route('admin.rentals.index', array_merge(request()->except('tab', 'page'), ['tab' => 'expiring_soon'])) }}"
                   class="px-3.5 py-1.5 rounded-xl text-xs font-black transition-all {{ $tab === 'expiring_soon' ? 'bg-amber-500 text-white shadow-xs' : 'bg-pink-50/60 text-slate-600 hover:bg-pink-100/60' }}">
                    Mau Habis &le; 30 Hari ({{ $stats['expiring_soon'] }})
                </a>
                <a href="{{ route('admin.rentals.index', array_merge(request()->except('tab', 'page'), ['tab' => 'overdue'])) }}"
                   class="px-3.5 py-1.5 rounded-xl text-xs font-black transition-all {{ $tab === 'overdue' ? 'bg-rose-600 text-white shadow-xs' : 'bg-pink-50/60 text-slate-600 hover:bg-pink-100/60' }}">
                    Lewat Jatuh Tempo ({{ $stats['overdue'] }})
                </a>
                <a href="{{ route('admin.rentals.index', array_merge(request()->except('tab', 'page'), ['tab' => 'deposit_held'])) }}"
                   class="px-3.5 py-1.5 rounded-xl text-xs font-black transition-all {{ $tab === 'deposit_held' ? 'bg-pink-600 text-white shadow-xs' : 'bg-pink-50/60 text-slate-600 hover:bg-pink-100/60' }}">
                    Deposit Ditahan
                </a>
                <a href="{{ route('admin.rentals.index', array_merge(request()->except('tab', 'page'), ['tab' => 'completed'])) }}"
                   class="px-3.5 py-1.5 rounded-xl text-xs font-black transition-all {{ $tab === 'completed' ? 'bg-pink-600 text-white shadow-xs' : 'bg-pink-50/60 text-slate-600 hover:bg-pink-100/60' }}">
                    Selesai ({{ $stats['completed'] }})
                </a>
            </div>

            @if(isset($isAgent) && $isAgent && isset($isLeader) && $isLeader)
                <div class="flex items-center gap-1.5 text-xs">
                    <span class="text-slate-400 font-bold">Scope:</span>
                    <a href="{{ route('admin.rentals.index', array_merge(request()->except('scope', 'page'), ['scope' => 'mine'])) }}"
                       class="px-2 py-1 rounded-lg font-bold {{ request('scope', 'mine') === 'mine' ? 'bg-pink-100 text-pink-700' : 'text-slate-500 hover:text-pink-600' }}">Sewa Saya</a>
                    <a href="{{ route('admin.rentals.index', array_merge(request()->except('scope', 'page'), ['scope' => 'team'])) }}"
                       class="px-2 py-1 rounded-lg font-bold {{ request('scope') === 'team' ? 'bg-pink-100 text-pink-700' : 'text-slate-500 hover:text-pink-600' }}">Tim Saya</a>
                </div>
            @endif
        </div>

        <!-- Search & Filter Controls -->
        <form method="GET" action="{{ route('admin.rentals.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <input type="hidden" name="tab" value="{{ $tab }}">
            @if(request('scope')) <input type="hidden" name="scope" value="{{ request('scope') }}"> @endif

            <div class="sm:col-span-6 relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text"
                       name="q"
                       value="{{ request('q') }}"
                       placeholder="Cari nama penyewa, nomor HP/WA, kode atau judul properti..."
                       class="w-full pl-9 pr-4 py-2 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 focus:ring-1 focus:ring-pink-500 bg-white">
            </div>

            <div class="sm:col-span-3">
                <select name="deposit_status" class="w-full px-3 py-2 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white text-slate-700">
                    <option value="">-- Semua Status Deposit --</option>
                    <option value="held" {{ request('deposit_status') === 'held' ? 'selected' : '' }}>Ditahan (Aktif)</option>
                    <option value="refunded" {{ request('deposit_status') === 'refunded' ? 'selected' : '' }}>Dikembalikan Penuh</option>
                    <option value="partially_refunded" {{ request('deposit_status') === 'partially_refunded' ? 'selected' : '' }}>Dikembalikan Sebagian</option>
                    <option value="deducted" {{ request('deposit_status') === 'deducted' ? 'selected' : '' }}>Dipotong Kerusakan</option>
                </select>
            </div>

            @if(!isset($isAgent) || !$isAgent)
                <div class="sm:col-span-2">
                    <select name="agent_id" class="w-full px-3 py-2 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white text-slate-700">
                        <option value="">-- Semua Agen --</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}" {{ request('agent_id') == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="sm:col-span-1 flex items-center gap-1.5">
                <button type="submit" class="w-full py-2 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-xs shadow-xs transition-all flex items-center justify-center gap-1">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                @if(request()->anyFilled(['q', 'deposit_status', 'agent_id']))
                    <a href="{{ route('admin.rentals.index', ['tab' => $tab]) }}" class="px-2.5 py-2 rounded-xl bg-pink-50 text-pink-700 hover:bg-pink-100 font-bold text-xs" title="Reset filter">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Rentals Table -->
    <div class="bg-white rounded-2xl border border-pink-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-pink-50/70 border-b border-pink-100 text-slate-700 uppercase font-black tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3 px-4">Penyewa</th>
                        <th class="py-3 px-4">Properti Disewa</th>
                        <th class="py-3 px-4">Masa Sewa ("Sampai Kapan")</th>
                        <th class="py-3 px-4">Uang Deposit &amp; Biaya</th>
                        <th class="py-3 px-4">Status &amp; Dokumen</th>
                        <th class="py-3 px-4 text-center">Aksi Cepat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-pink-100/60">
                    @forelse($rentals as $rental)
                        <tr class="hover:bg-pink-50/30 transition-all {{ $rental->is_overdue ? 'bg-rose-50/30' : ($rental->is_expiring_soon ? 'bg-amber-50/30' : '') }}">
                            <!-- Tenant Identity -->
                            <td class="py-3 px-4 align-top">
                                <div class="font-extrabold text-slate-900 text-sm flex items-center gap-1.5">
                                    <span>{{ $rental->tenant_name }}</span>
                                    @if($rental->agreement_number)
                                        <span class="text-[9px] font-mono px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200" title="No Perjanjian">
                                            #{{ $rental->agreement_number }}
                                        </span>
                                    @endif
                                </div>
                                <div class="mt-1 flex items-center gap-2">
                                    <a href="{{ $rental->whatsapp_reminder_link }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-600 hover:text-emerald-700 hover:underline" title="Klik untuk kirim pesan WhatsApp pengingat masa sewa">
                                        <i class="fa-brands fa-whatsapp text-emerald-500 text-sm"></i> {{ $rental->tenant_phone }}
                                    </a>
                                </div>
                                @if($rental->tenant_id_card)
                                    <div class="text-[10px] text-slate-400 mt-0.5 flex items-center gap-1">
                                        <i class="fa-solid fa-id-card text-pink-400"></i> KTP: {{ $rental->tenant_id_card }}
                                    </div>
                                @endif
                                @if($rental->agent)
                                    <div class="text-[10px] text-pink-600 font-bold mt-1">
                                        Agen: {{ $rental->agent->name }}
                                    </div>
                                @endif
                            </td>

                            <!-- Property -->
                            <td class="py-3 px-4 align-top">
                                @if($rental->property)
                                    <div class="min-w-0 max-w-xs">
                                        <a href="{{ route('properties.show', $rental->property->slug) }}" target="_blank" class="font-bold text-slate-900 hover:text-pink-600 block line-clamp-2" title="{{ $rental->property->title }}">
                                            {{ $rental->property->title }}
                                        </a>
                                        <div class="text-[10px] text-slate-400 truncate mt-0.5">
                                            {{ $rental->property->city ?? '-' }} &bull; {{ $rental->property->address }}
                                        </div>
                                    </div>
                                @else
                                    <span class="text-slate-400 italic">Properti telah dihapus</span>
                                @endif
                            </td>

                            <!-- Lease Period ("sampai kapan") -->
                            <td class="py-3 px-4 align-top">
                                <div class="text-xs font-bold text-slate-800">
                                    {{ \Carbon\Carbon::parse($rental->start_date)->translatedFormat('d M Y') }}
                                    <span class="text-pink-500 font-black">&rarr;</span>
                                    <span class="font-black text-slate-900">{{ \Carbon\Carbon::parse($rental->end_date)->translatedFormat('d M Y') }}</span>
                                </div>
                                <div class="text-[11px] text-slate-500 mt-0.5">
                                    Durasi: {{ $rental->duration }} {{ ucfirst($rental->period_type) }}
                                </div>

                                <!-- Countdown / Days Remaining Badge -->
                                <div class="mt-1.5">
                                    @if($rental->status === 'completed')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                            <i class="fa-solid fa-check"></i> Selesai pada {{ $rental->actual_end_date ? \Carbon\Carbon::parse($rental->actual_end_date)->translatedFormat('d M Y') : \Carbon\Carbon::parse($rental->end_date)->translatedFormat('d M Y') }}
                                        </span>
                                    @elseif($rental->is_overdue)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-100 text-rose-800 border border-rose-200 animate-pulse">
                                            <i class="fa-solid fa-triangle-exclamation"></i> Lewat {{ abs($rental->days_remaining) }} Hari
                                        </span>
                                    @elseif($rental->is_expiring_soon)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-800 border border-amber-300">
                                            <i class="fa-solid fa-clock"></i> Sisa {{ $rental->days_remaining }} Hari Lagi!
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-pink-50 text-pink-700 border border-pink-200">
                                            <i class="fa-solid fa-calendar-check"></i> Aktif (Sisa {{ $rental->days_remaining }} hari)
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Financial & Deposit -->
                            <td class="py-3 px-4 align-top">
                                <div class="text-slate-900 font-extrabold">
                                    {{ $rental->formatted_rent_price }}
                                    <span class="text-[10px] font-normal text-slate-400">/ {{ $rental->period_type }}</span>
                                </div>

                                <!-- Deposit Details -->
                                <div class="mt-1.5 p-2 rounded-xl bg-pink-50/60 border border-pink-100">
                                    <div class="flex items-center justify-between text-[11px]">
                                        <span class="text-slate-500 font-bold">Uang Deposit:</span>
                                        <span class="font-black text-pink-700">{{ $rental->formatted_deposit_amount }}</span>
                                    </div>
                                    <div class="mt-1 flex items-center justify-between">
                                        <span class="text-[10px] text-slate-400">Status:</span>
                                        @if($rental->deposit_status === 'held')
                                            <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-amber-100 text-amber-800 border border-amber-200">
                                                Ditahan (Aktif)
                                            </span>
                                        @elseif($rental->deposit_status === 'refunded')
                                            <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                Dikembalikan Penuh
                                            </span>
                                        @elseif($rental->deposit_status === 'partially_refunded')
                                            <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-blue-100 text-blue-800 border border-blue-200">
                                                Kembali Sebagian ({{ $rental->formatted_deposit_refunded_amount }})
                                            </span>
                                        @elseif($rental->deposit_status === 'deducted')
                                            <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-rose-100 text-rose-800 border border-rose-200">
                                                Dipotong Kerusakan
                                            </span>
                                        @endif
                                    </div>
                                    @if($rental->deposit_notes)
                                        <div class="text-[9px] text-slate-500 mt-1 italic border-t border-pink-100 pt-0.5">
                                            &ldquo;{{ Str::limit($rental->deposit_notes, 40) }}&rdquo;
                                        </div>
                                    @endif
                                </div>
                            </td>

                            <!-- Status & Documents -->
                            <td class="py-3 px-4 align-top">
                                <div>
                                    @if($rental->status === 'active')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-pink-600 text-white shadow-xs">
                                            <i class="fa-solid fa-circle text-[6px]"></i> Sewa Aktif
                                        </span>
                                    @elseif($rental->status === 'completed')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                            <i class="fa-solid fa-check-double text-emerald-600"></i> Selesai
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-200">
                                            {{ $rental->status_label }}
                                        </span>
                                    @endif
                                </div>

                                <div class="mt-2 space-y-1">
                                    @if($rental->agreement_document)
                                        <a href="{{ asset('storage/' . $rental->agreement_document) }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-bold text-pink-600 hover:underline">
                                            <i class="fa-solid fa-file-pdf"></i> Surat Sewa
                                        </a>
                                    @endif
                                    @if($rental->id_card_photo)
                                        <a href="{{ asset('storage/' . $rental->id_card_photo) }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-bold text-pink-600 hover:underline">
                                            <i class="fa-solid fa-id-card"></i> Foto KTP
                                        </a>
                                    @endif
                                </div>
                            </td>

                            <!-- Quick Actions -->
                            <td class="py-3 px-4 align-top text-center">
                                <div class="flex flex-col items-center gap-1.5">
                                    <div class="flex items-center gap-1 w-full">
                                        <!-- Detail Button -->
                                        <a href="{{ route('admin.rentals.show', $rental->id) }}" class="flex-1 px-2.5 py-1.5 rounded-xl bg-pink-50 hover:bg-pink-100 text-pink-700 font-bold text-xs transition-all flex items-center justify-center gap-1" title="Lihat Detail">
                                            <i class="fa-solid fa-eye"></i> Detail
                                        </a>

                                        <!-- Edit Button -->
                                        <a href="{{ route('admin.rentals.edit', $rental->id) }}" class="px-2.5 py-1.5 rounded-xl bg-pink-50 hover:bg-pink-100 text-pink-700 font-bold text-xs transition-all" title="Edit Data">
                                            <i class="fa-solid fa-pencil"></i>
                                        </a>
                                    </div>

                                    @if($rental->status === 'active')
                                        <!-- Selesaikan & Kelola Deposit Modal Trigger -->
                                        <button type="button"
                                                onclick="openCompleteModal({{ $rental->id }}, '{{ addslashes($rental->tenant_name) }}', {{ (float)$rental->deposit_amount }})"
                                                class="w-full px-2 py-1 rounded-lg border border-pink-200 text-pink-700 hover:bg-pink-50 font-bold text-[10px] transition-all flex items-center justify-center gap-1">
                                            <i class="fa-solid fa-handshake"></i> Selesai / Deposit
                                        </button>

                                        <!-- Perpanjang Modal Trigger -->
                                        <button type="button"
                                                onclick="openExtendModal({{ $rental->id }}, '{{ addslashes($rental->tenant_name) }}', '{{ $rental->end_date->toDateString() }}')"
                                                class="w-full px-2 py-1 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-[10px] transition-all flex items-center justify-center gap-1">
                                            <i class="fa-solid fa-calendar-plus text-pink-500"></i> Perpanjang
                                        </button>
                                    @endif

                                    <!-- Delete Button -->
                                    <form action="{{ route('admin.rentals.destroy', $rental->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data sewa penyewa {{ addslashes($rental->tenant_name) }}?')" class="w-full">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full text-slate-400 hover:text-rose-600 text-[10px] py-0.5 transition-all">
                                            <i class="fa-solid fa-trash-can"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <div class="w-16 h-16 rounded-full bg-pink-50 text-pink-400 flex items-center justify-center mx-auto mb-3 text-2xl">
                                    <i class="fa-solid fa-key"></i>
                                </div>
                                <div class="font-extrabold text-slate-800 text-sm">Belum Ada Data Penyewa</div>
                                <p class="text-xs text-slate-400 mt-1">Mulai catat data penyewa untuk properti sewa Anda beserta uang deposit &amp; masa berakhir kontrak.</p>
                                <a href="{{ route('admin.rentals.create') }}" class="inline-flex items-center gap-2 mt-4 px-4 py-2 rounded-xl bg-pink-600 text-white font-bold text-xs hover:bg-pink-700 transition-all">
                                    <i class="fa-solid fa-plus"></i> Catat Penyewa Pertama
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($rentals->hasPages())
            <div class="p-4 border-t border-pink-100 bg-pink-50/20">
                {{ $rentals->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Selesaikan Sewa & Kelola Deposit -->
<div id="completeModal" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 border border-pink-100 shadow-2xl relative">
        <button type="button" onclick="closeCompleteModal()" class="absolute right-4 top-4 text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>

        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-2xl bg-pink-100 text-pink-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-handshake"></i>
            </div>
            <div>
                <h3 class="font-extrabold text-slate-900 text-base">Selesaikan Masa Sewa &amp; Kelola Deposit</h3>
                <p class="text-xs text-slate-500" id="completeModalTenantName">Penyewa: -</p>
            </div>
        </div>

        <form id="completeForm" method="POST" action="" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Pengakhiran Sewa / Check-out *</label>
                <input type="date" name="actual_end_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Status Pengembalian Uang Deposit *</label>
                <select name="deposit_status" id="completeDepositStatus" onchange="toggleRefundAmount()" required class="w-full px-3 py-2 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white">
                    <option value="refunded">Dikembalikan Penuh (100%)</option>
                    <option value="partially_refunded">Dikembalikan Sebagian (Ada potongan)</option>
                    <option value="deducted">Dipotong / Hangus Penuh (Kerusakan / Tunggakan)</option>
                </select>
            </div>

            <div id="refundAmountGroup" class="hidden">
                <label class="block text-xs font-bold text-slate-700 mb-1">Nominal yang Dikembalikan ke Penyewa (Rp)</label>
                <input type="number" name="deposit_refunded_amount" id="depositRefundedAmount" min="0" placeholder="0" class="w-full px-3 py-2 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Kondisi Unit / Potongan Deposit</label>
                <textarea name="deposit_notes" rows="2" placeholder="Cth: Potongan Rp 250.000 untuk tagihan listrik terakhir, sisa dikembalikan via transfer BCA." class="w-full px-3 py-2 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500"></textarea>
            </div>

            <div class="p-3 rounded-xl bg-pink-50/60 border border-pink-100 flex items-center gap-2">
                <input type="checkbox" name="set_property_active" id="set_property_active" value="1" checked class="rounded text-pink-600 focus:ring-pink-500">
                <label for="set_property_active" class="text-xs font-bold text-slate-700 cursor-pointer">
                    Kembalikan status properti menjadi "Tersedia / Aktif" kembali untuk dipasarkan
                </label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="closeCompleteModal()" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-pink-600 text-white text-xs font-bold hover:bg-pink-700 shadow-xs">
                    Simpan &amp; Selesaikan Sewa
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Perpanjang Sewa -->
<div id="extendModal" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 border border-pink-100 shadow-2xl relative">
        <button type="button" onclick="closeExtendModal()" class="absolute right-4 top-4 text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>

        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-2xl bg-pink-100 text-pink-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-calendar-plus"></i>
            </div>
            <div>
                <h3 class="font-extrabold text-slate-900 text-base">Perpanjang Masa Sewa</h3>
                <p class="text-xs text-slate-500" id="extendModalTenantName">Penyewa: -</p>
            </div>
        </div>

        <form id="extendForm" method="POST" action="" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Masa Sewa Berakhir yang Baru *</label>
                <input type="date" name="new_end_date" id="newEndDateInput" required class="w-full px-3 py-2 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500">
            </div>

            <div class="flex items-center gap-1.5">
                <span class="text-[10px] font-bold text-slate-400">Pilihan Cepat:</span>
                <button type="button" onclick="addMonthsToExtend(1)" class="px-2 py-0.5 rounded bg-pink-50 text-pink-700 text-[10px] font-bold border border-pink-200 hover:bg-pink-100">+1 Bulan</button>
                <button type="button" onclick="addMonthsToExtend(6)" class="px-2 py-0.5 rounded bg-pink-50 text-pink-700 text-[10px] font-bold border border-pink-200 hover:bg-pink-100">+6 Bulan</button>
                <button type="button" onclick="addMonthsToExtend(12)" class="px-2 py-0.5 rounded bg-pink-50 text-pink-700 text-[10px] font-bold border border-pink-200 hover:bg-pink-100">+1 Tahun</button>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Perpanjangan (Opsional)</label>
                <textarea name="extension_notes" rows="2" placeholder="Cth: Perpanjangan disetujui harga tetap sama, sewa dibayar lunas di depan." class="w-full px-3 py-2 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="closeExtendModal()" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-pink-600 text-white text-xs font-bold hover:bg-pink-700 shadow-xs">
                    Perpanjang Sewa
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let currentOriginalDeposit = 0;
    let baseEndDate = '';

    function openCompleteModal(rentalId, tenantName, depositAmount) {
        document.getElementById('completeForm').action = '/admin/rentals/' + rentalId + '/complete';
        document.getElementById('completeModalTenantName').innerText = 'Penyewa: ' + tenantName + ' (Deposit: Rp ' + Number(depositAmount).toLocaleString('id-ID') + ')';
        currentOriginalDeposit = depositAmount;
        document.getElementById('completeDepositStatus').value = 'refunded';
        toggleRefundAmount();
        document.getElementById('completeModal').classList.remove('hidden');
    }

    function closeCompleteModal() {
        document.getElementById('completeModal').classList.add('hidden');
    }

    function toggleRefundAmount() {
        const status = document.getElementById('completeDepositStatus').value;
        const group = document.getElementById('refundAmountGroup');
        const input = document.getElementById('depositRefundedAmount');

        if (status === 'partially_refunded') {
            group.classList.remove('hidden');
            input.value = Math.round(currentOriginalDeposit / 2);
        } else {
            group.classList.add('hidden');
        }
    }

    function openExtendModal(rentalId, tenantName, currentEndDate) {
        document.getElementById('extendForm').action = '/admin/rentals/' + rentalId + '/extend';
        document.getElementById('extendModalTenantName').innerText = 'Penyewa: ' + tenantName + ' (Berakhir saat ini: ' + currentEndDate + ')';
        baseEndDate = currentEndDate;
        document.getElementById('newEndDateInput').value = currentEndDate;
        document.getElementById('extendModal').classList.remove('hidden');
    }

    function closeExtendModal() {
        document.getElementById('extendModal').classList.add('hidden');
    }

    function addMonthsToExtend(months) {
        if (!baseEndDate) return;
        const d = new Date(baseEndDate);
        d.setMonth(d.getMonth() + months);
        const yyyy = d.getFullYear();
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const dd = String(d.getDate()).padStart(2, '0');
        document.getElementById('newEndDateInput').value = `${yyyy}-${mm}-${dd}`;
    }
</script>
@endsection

