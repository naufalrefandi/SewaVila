@extends('layouts.admin')

@section('title', 'Konfirmasi Pembayaran & Topup Agen - ' . \App\Models\Setting::getSiteName() . '')
@section('page_title', 'Konfirmasi Pembayaran Transaksi Agen (Topup Kuota & Featured)')

@section('content')
<div class="space-y-6" x-data="{ previewImg: null, rejectModal: false, activeTransId: null }">

    <!-- Header Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-semibold block">Menunggu Konfirmasi</span>
                <span class="text-2xl font-black text-amber-500 mt-1 block">{{ $pendingCount }} Transaksi</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-semibold block">Berhasil Disetujui</span>
                <span class="text-2xl font-black text-emerald-600 mt-1 block">{{ $approvedCount }} Transaksi</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-semibold block">Total Transaksi</span>
                <span class="text-2xl font-black text-slate-900 mt-1 block">{{ $transactions->total() }}</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center text-xl">
                <i class="fa-solid fa-receipt"></i>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs flex flex-wrap items-center justify-between gap-3 text-xs">
        <form action="{{ route('admin.agent-transactions') }}" method="GET" class="flex flex-wrap items-center gap-2">
            <select name="type" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:outline-none">
                <option value="">Semua Jenis Transaksi</option>
                <option value="quota_topup" {{ request('type') === 'quota_topup' ? 'selected' : '' }}>Topup Kuota Listing</option>
                <option value="featured_listing" {{ request('type') === 'featured_listing' ? 'selected' : '' }}>Upgrade Featured Listing</option>
            </select>

            <select name="status" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:outline-none">
                <option value="">Semua Status</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>⏳ Menunggu Konfirmasi</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>✓ Disetujui (Approved)</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>✕ Ditolak (Rejected)</option>
            </select>

            @if(request()->hasAny(['type', 'status']))
                <a href="{{ route('admin.agent-transactions') }}" class="px-3 py-2 text-rose-600 hover:text-rose-700 font-bold">
                    <i class="fa-solid fa-rotate-left mr-1"></i>Reset
                </a>
            @endif
        </form>

        <a href="{{ route('admin.settings') }}" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-bold transition-all flex items-center gap-1.5">
            <i class="fa-solid fa-gear"></i>
            <span>Pengaturan Biaya &amp; QRIS</span>
        </a>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-400 uppercase tracking-wider font-bold">
                        <th class="p-4">Tanggal</th>
                        <th class="p-4">Agen Pemohon</th>
                        <th class="p-4">Jenis Transaksi</th>
                        <th class="p-4">Detail Paket / Properti</th>
                        <th class="p-4">Nominal Transfer</th>
                        <th class="p-4">Bukti QRIS</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-center">Tindakan Admin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $trx)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="p-4 text-slate-500 whitespace-nowrap">
                                {{ $trx->created_at->format('d M Y, H:i') }}
                            </td>
                            <td class="p-4">
                                <div class="font-bold text-slate-900">{{ $trx->agent->name ?? 'Agen' }}</div>
                                <div class="text-[10px] text-slate-400">{{ $trx->agent->office_branch ?? 'Cabang' }}</div>
                                <div class="text-[10px] text-emerald-600 font-mono mt-0.5">
                                    <i class="fa-brands fa-whatsapp"></i> {{ $trx->agent->phone ?? '-' }}
                                </div>
                            </td>
                            <td class="p-4">
                                @if($trx->type === 'quota_topup')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black bg-blue-100 text-blue-800">
                                        <i class="fa-solid fa-layer-group"></i> Topup Kuota (+{{ $trx->quota_added }})
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-100 text-amber-800">
                                        <i class="fa-solid fa-star"></i> Featured ({{ $trx->duration_days }} Hari)
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 max-w-xs">
                                @if($trx->type === 'quota_topup')
                                    <div class="font-bold text-slate-800">Paket Tambahan Kuota Listing</div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">+{{ $trx->quota_added }} Kuota Listing Baru</div>
                                @else
                                    @if($trx->property)
                                        <a href="{{ route('properties.show', $trx->property->slug) }}" target="_blank" class="font-bold text-slate-900 hover:text-rose-600 line-clamp-1">
                                            {{ $trx->property->title }}
                                        </a>
                                        <div class="text-[10px] text-slate-400 mt-0.5">{{ $trx->property->district }}, {{ $trx->property->city }}</div>
                                    @else
                                        <span class="text-slate-400 italic">Properti tidak ditemukan</span>
                                    @endif
                                @endif
                                @if($trx->notes)
                                    <div class="text-[10px] italic text-slate-400 mt-1">"{{ $trx->notes }}"</div>
                                @endif
                            </td>
                            <td class="p-4 font-black text-slate-900">
                                {{ $trx->formatted_amount }}
                            </td>
                            <td class="p-4">
                                @if($trx->payment_proof)
                                    <button type="button"
                                            @click="previewImg = '{{ $trx->payment_proof_url }}'"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 text-[11px] font-bold transition-all border border-rose-200">
                                        <i class="fa-solid fa-receipt"></i>
                                        <span>Lihat Struk</span>
                                    </button>
                                @else
                                    <span class="text-[10px] text-slate-400 italic">Tidak ada struk</span>
                                @endif
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase"
                                      class="{{ $trx->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : ($trx->status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                    {{ $trx->status_label }}
                                </span>
                                @if($trx->admin_notes)
                                    <div class="text-[9px] text-slate-400 mt-0.5 italic">Catatan: {{ $trx->admin_notes }}</div>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                @if($trx->status === 'pending')
                                    <div class="flex items-center justify-center gap-2">
                                        <!-- Approve Form -->
                                        <form action="{{ route('admin.agent-transactions.approve', $trx->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menyetujui transaksi ini?');">
                                            @csrf
                                            <button type="submit"
                                                    class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition-all flex items-center gap-1">
                                                <i class="fa-solid fa-check"></i>
                                                <span>Setujui</span>
                                            </button>
                                        </form>

                                        <!-- Reject Form -->
                                        <form action="{{ route('admin.agent-transactions.reject', $trx->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menolak transaksi ini?');">
                                            @csrf
                                            <button type="submit"
                                                    class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-rose-600 hover:text-white text-slate-600 font-bold text-xs transition-all flex items-center gap-1">
                                                <i class="fa-solid fa-xmark"></i>
                                                <span>Tolak</span>
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-slate-400 text-xs italic">Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-10 text-center text-slate-400">
                                <div class="space-y-2">
                                    <i class="fa-solid fa-receipt text-3xl text-slate-300"></i>
                                    <p class="font-bold text-slate-600">Belum ada transaksi pembayaran dari agen.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200">
            {{ $transactions->links() }}
        </div>
    </div>

    <!-- Lightbox Modal Bukti Transfer QRIS -->
    <div x-show="previewImg"
         x-cloak
         @click.away="previewImg = null"
         class="fixed inset-0 z-50 bg-black/80 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-4 shadow-2xl relative">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-rose-600"></i> Bukti Pembayaran QRIS Agen
                </h3>
                <button type="button" @click="previewImg = null" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="max-h-[70vh] overflow-auto flex items-center justify-center rounded-2xl bg-slate-100 p-2">
                <img :src="previewImg" alt="Struk Pembayaran" class="max-h-[65vh] object-contain rounded-xl shadow">
            </div>
            <div class="text-right">
                <button type="button" @click="previewImg = null" class="px-4 py-2 bg-slate-900 text-white font-bold text-xs rounded-xl">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

