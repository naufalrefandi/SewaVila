@extends('layouts.admin')

@section('title', 'Kelola Pesanan Sewa Vila & QRIS - Admin Portal')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-black uppercase tracking-wider text-pink-600">Manajemen Pemesanan</span>
                <span class="text-slate-300">&bull;</span>
                <span class="text-xs font-bold text-slate-500">QRIS Payment Gateway</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <i class="fa-solid fa-umbrella-beach text-pink-600"></i>
                <span>Sewa Vila &amp; Pembayaran QRIS</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Kelola data reservasi vila, verifikasi bukti pembayaran QRIS, dan konfirmasi jadwal menginap tamu.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('villas.index') }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-pink-200 bg-pink-50 hover:bg-pink-100 text-pink-700 font-extrabold text-xs transition-colors">
                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                <span>Lihat Katalog Vila</span>
            </a>
        </div>
    </div>

    <!-- Flash Alert -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm flex items-center gap-3">
            <i class="fa-solid fa-circle-check text-emerald-600 text-lg shrink-0"></i>
            <span class="font-bold">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Stat Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-pink-100 shadow-xs space-y-1">
            <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Total Reservasi</span>
            <div class="text-2xl font-black text-slate-900">{{ number_format($stats['total']) }}</div>
            <span class="text-[10px] text-slate-500">Semua pesanan masuk</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-amber-200 shadow-xs space-y-1 bg-gradient-to-br from-white to-amber-50/50">
            <span class="text-[10px] uppercase font-bold text-amber-700 block tracking-wider flex items-center gap-1">
                <i class="fa-solid fa-clock-rotate-left"></i> Menunggu Bayar / Verifikasi
            </span>
            <div class="text-2xl font-black text-amber-600">{{ number_format($stats['pending_payment']) }}</div>
            <span class="text-[10px] text-amber-700 font-medium">Perlu ditindaklanjuti</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-emerald-200 shadow-xs space-y-1 bg-gradient-to-br from-white to-emerald-50/50">
            <span class="text-[10px] uppercase font-bold text-emerald-700 block tracking-wider flex items-center gap-1">
                <i class="fa-solid fa-circle-check"></i> Terkonfirmasi (Lunas)
            </span>
            <div class="text-2xl font-black text-emerald-600">{{ number_format($stats['confirmed']) }}</div>
            <span class="text-[10px] text-emerald-700 font-medium">Jadwal menginap aman</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-pink-100 shadow-xs space-y-1 bg-gradient-to-br from-white to-pink-50/50">
            <span class="text-[10px] uppercase font-bold text-pink-700 block tracking-wider flex items-center gap-1">
                <i class="fa-solid fa-sack-dollar"></i> Pendapatan Sewa Vila
            </span>
            <div class="text-xl sm:text-2xl font-black text-pink-600 font-mono">
                Rp {{ number_format($stats['revenue'], 0, ',', '.') }}
            </div>
            <span class="text-[10px] text-pink-700 font-medium">Total transaksi lunas</span>
        </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-pink-100 shadow-xs space-y-3">
        <form action="{{ route('admin.villa-bookings.index') }}" method="GET" class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
            <div class="flex-1 relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari Kode Booking, Nama Tamu, WhatsApp, Email, atau Judul Vila..."
                       class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-pink-500">
            </div>

            <div class="flex items-center gap-2">
                <select name="status" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-pink-500">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu Bayar</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Lunas (Terkonfirmasi)</option>
                    <option value="checked_in" {{ request('status') === 'checked_in' ? 'selected' : '' }}>Sedang Menginap</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>

                <button type="submit" class="px-4 py-2 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-extrabold text-xs shadow-xs transition-colors">
                    Filter
                </button>

                @if(request()->anyFilled(['q', 'status']))
                    <a href="{{ route('admin.villa-bookings.index') }}" class="px-3 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold text-xs transition-colors">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table of Bookings -->
    <div class="bg-white rounded-2xl border border-pink-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-pink-50/70 border-b border-pink-100 text-[10.5px] font-extrabold uppercase text-pink-700 tracking-wider">
                        <th class="py-3 px-4">Kode Booking</th>
                        <th class="py-3 px-4">Vila &amp; Lokasi</th>
                        <th class="py-3 px-4">Data Tamu</th>
                        <th class="py-3 px-4">Jadwal Menginap</th>
                        <th class="py-3 px-4">Tagihan (QRIS)</th>
                        <th class="py-3 px-4">Status Bayar</th>
                        <th class="py-3 px-4">Status Reservasi</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($bookings as $booking)
                        <tr class="hover:bg-pink-50/30 transition-colors">
                            <!-- Code -->
                            <td class="py-3.5 px-4 font-mono font-extrabold text-pink-700 whitespace-nowrap">
                                <a href="{{ route('admin.villa-bookings.show', $booking->id) }}" class="hover:underline">
                                    {{ $booking->booking_code }}
                                </a>
                                <span class="block text-[10px] font-sans text-slate-400 font-normal">
                                    {{ $booking->created_at->format('d/m/Y H:i') }}
                                </span>
                            </td>

                            <!-- Villa -->
                            <td class="py-3.5 px-4 max-w-xs">
                                <div class="font-bold text-slate-900 truncate">
                                    <a href="{{ route('villas.show', $booking->property->slug) }}" target="_blank" class="hover:text-pink-600 transition-colors">
                                        {{ $booking->property->title }}
                                    </a>
                                </div>
                                <span class="text-[10px] text-slate-500 block truncate">
                                    <i class="fa-solid fa-location-dot text-pink-500 mr-0.5"></i>
                                    {{ $booking->property->district ? $booking->property->district . ', ' : '' }}{{ $booking->property->city }}
                                </span>
                            </td>

                            <!-- Guest -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-extrabold text-slate-900">{{ $booking->customer_name }}</div>
                                <div class="text-[11px] text-slate-500 font-mono">{{ $booking->customer_phone }}</div>
                                <div class="text-[10px] text-slate-400">{{ $booking->customer_email }}</div>
                            </td>

                            <!-- Schedule -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-bold text-slate-900">
                                    {{ $booking->check_in_date->format('d M') }} - {{ $booking->check_out_date->format('d M Y') }}
                                </div>
                                <div class="text-[10px] text-slate-500">
                                    {{ $booking->total_nights }} Malam &bull; {{ $booking->guests_count }} Tamu
                                </div>
                            </td>

                            <!-- Amount & Proof -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-black text-pink-600 font-mono text-sm">
                                    {{ $booking->formatted_grand_total }}
                                </div>
                                @if($booking->payment_proof)
                                    <a href="{{ asset('storage/' . $booking->payment_proof) }}" target="_blank"
                                       class="inline-flex items-center gap-1 text-[10px] text-emerald-600 font-bold hover:underline">
                                        <i class="fa-solid fa-image"></i> Ada Bukti Transfer
                                    </a>
                                @else
                                    <span class="text-[10px] text-slate-400">Belum upload bukti</span>
                                @endif
                            </td>

                            <!-- Payment Status -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($booking->payment_status === 'paid')
                                    <span class="bg-emerald-100 text-emerald-800 text-[10px] font-black px-2 py-0.5 rounded-full inline-flex items-center gap-1">
                                        <i class="fa-solid fa-circle-check text-emerald-600"></i> Lunas
                                    </span>
                                @else
                                    <span class="bg-rose-100 text-rose-800 text-[10px] font-black px-2 py-0.5 rounded-full inline-flex items-center gap-1">
                                        <i class="fa-solid fa-hourglass-half text-rose-600"></i> Menunggu Bayar
                                    </span>
                                @endif
                            </td>

                            <!-- Booking Status -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @php
                                    $statusBadge = match($booking->booking_status) {
                                        'confirmed' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                                        'checked_in' => 'bg-cyan-50 text-cyan-700 border border-cyan-200',
                                        'completed' => 'bg-slate-100 text-slate-700 border border-slate-200',
                                        'cancelled' => 'bg-rose-50 text-rose-700 border border-rose-200',
                                        default => 'bg-amber-50 text-amber-700 border border-amber-200',
                                    };
                                @endphp
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $statusBadge }}">
                                    {{ $booking->booking_status_label }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.villa-bookings.show', $booking->id) }}"
                                       class="px-2.5 py-1.5 rounded-lg bg-pink-50 hover:bg-pink-100 text-pink-700 font-bold text-[11px] transition-colors" title="Lihat Detail & Bukti Pembayaran">
                                        Detail
                                    </a>

                                    @if($booking->payment_status !== 'paid')
                                        <form action="{{ route('admin.villa-bookings.confirm-payment', $booking->id) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Konfirmasi bahwa pembayaran QRIS untuk kode {{ $booking->booking_code }} telah valid & LUNAS?')">
                                            @csrf
                                            <button type="submit"
                                                    class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] transition-colors cursor-pointer" title="Konfirmasi Lunas">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        </form>
                                    @endif

                                    <a href="{{ $booking->getWhatsAppConfirmUrl() }}" target="_blank"
                                       class="p-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-600 font-bold transition-colors" title="Hubungi Tamu via WhatsApp">
                                        <i class="fa-brands fa-whatsapp text-sm"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-12 text-slate-400">
                                <i class="fa-solid fa-umbrella-beach text-3xl mb-2 text-slate-300 block"></i>
                                Belum ada data reservasi vila yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bookings->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

