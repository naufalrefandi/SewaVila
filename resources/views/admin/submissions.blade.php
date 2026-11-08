@extends('layouts.admin')

@section('title', 'Titip Properti Masuk - Admin ' . \App\Models\Setting::getSiteName() . '')
@section('page_title', 'Pendaftaran Listing Properti dari Pemilik (Titip Properti)')

@section('content')
<div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden" x-data="{ previewImg: null }">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-slate-400 uppercase tracking-wider font-bold">
                    <th class="p-4">Tanggal</th>
                    <th class="p-4">Data Pemilik</th>
                    <th class="p-4">Properti Dititipkan</th>
                    <th class="p-4">Harga Harapan</th>
                    <th class="p-4">Biaya &amp; Bukti QRIS</th>
                    <th class="p-4">Spesifikasi</th>
                    <th class="p-4">Status Kurasi</th>
                    <th class="p-4 text-center">Hubungi Pemilik</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($submissions as $sub)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="p-4 text-slate-500 whitespace-nowrap">
                            {{ $sub->created_at->format('d M Y, H:i') }}
                        </td>
                        <td class="p-4">
                            <div class="font-bold text-slate-900">{{ $sub->owner_name }}</div>
                            <div class="text-[11px] text-emerald-600 mt-0.5">
                                <i class="fa-brands fa-whatsapp"></i> {{ $sub->owner_phone }}
                            </div>
                            @if($sub->owner_email)
                                <div class="text-[10px] text-slate-400">{{ $sub->owner_email }}</div>
                            @endif
                        </td>
                        <td class="p-4 max-w-xs">
                            <div class="font-bold text-slate-800">{{ $sub->property_title }}</div>
                            <div class="text-[11px] text-slate-500 mt-0.5">
                                <span class="uppercase font-bold text-rose-600">{{ $sub->listing_type }}</span> &bull;
                                <span class="capitalize">{{ $sub->property_type }}</span> &bull;
                                <span>{{ $sub->city }}</span>
                            </div>
                            <div class="text-[10px] text-slate-400 mt-0.5">{{ $sub->address }}</div>
                        </td>
                        <td class="p-4 font-black text-rose-600">
                            {{ $sub->formatted_price }}
                        </td>
                        <td class="p-4">
                            <div class="space-y-1.5">
                                @if($sub->payment_code)
                                    <div class="font-mono text-[11px] font-extrabold text-slate-900 bg-slate-100 px-2 py-0.5 rounded inline-block">
                                        {{ $sub->payment_code }}
                                    </div>
                                @endif
                                @if($sub->fee_amount > 0)
                                    <div class="font-bold text-slate-900 text-[11px]">{{ $sub->formatted_fee }}</div>
                                    <form action="{{ route('admin.submissions.verify-payment', $sub->id) }}" method="POST">
                                        @csrf
                                        <select name="payment_status" onchange="this.form.submit()"
                                                class="px-2 py-0.5 rounded text-[10px] font-black uppercase border focus:outline-none cursor-pointer {{ $sub->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : ($sub->payment_status === 'pending' ? 'bg-amber-100 text-amber-800 border-amber-300' : 'bg-slate-100 text-slate-700 border-slate-300') }}">
                                            <option value="pending" {{ $sub->payment_status === 'pending' ? 'selected' : '' }}>⏳ Menunggu Cek</option>
                                            <option value="paid" {{ $sub->payment_status === 'paid' ? 'selected' : '' }}>✓ Lunas (Paid)</option>
                                            <option value="unpaid" {{ $sub->payment_status === 'unpaid' ? 'selected' : '' }}>Belum Bayar</option>
                                        </select>
                                    </form>

                                    @if($sub->payment_proof)
                                        <button type="button"
                                                @click="previewImg = '{{ $sub->payment_proof_url }}'"
                                                class="inline-flex items-center gap-1 text-[10px] text-rose-600 hover:text-rose-800 font-bold hover:underline">
                                            <i class="fa-solid fa-receipt"></i>
                                            <span>Lihat Struk QRIS</span>
                                        </button>
                                    @else
                                        <span class="text-slate-400 text-[10px] italic block">Belum Upload Struk</span>
                                    @endif
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">
                                        GRATIS
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="p-4 text-slate-600">
                            <div>LT: {{ $sub->land_area ?? '-' }}m² | LB: {{ $sub->building_area ?? '-' }}m²</div>
                            <div class="text-[10px] text-slate-400 mt-0.5">
                                {{ $sub->bedrooms ?? '-' }} KT &bull; {{ $sub->bathrooms ?? '-' }} KM &bull; {{ $sub->certificate ?? 'SHM' }}
                            </div>
                            @if($sub->notes)
                                <div class="mt-1 text-[10px] italic text-slate-500">"{{ $sub->notes }}"</div>
                            @endif
                        </td>
                        <td class="p-4">
                            <form action="{{ route('admin.submissions.status', $sub->id) }}" method="POST">
                                @csrf
                                <select name="status" onchange="this.form.submit()" class="px-2.5 py-1 rounded-lg text-xs font-bold border border-slate-200 focus:outline-none {{ $sub->status === 'pending' ? 'bg-amber-50 text-amber-800 border-amber-200' : ($sub->status === 'listed' ? 'bg-emerald-50 text-emerald-800' : 'bg-slate-100 text-slate-700') }}">
                                    <option value="pending" {{ $sub->status === 'pending' ? 'selected' : '' }}>Pending (Menunggu)</option>
                                    <option value="reviewed" {{ $sub->status === 'reviewed' ? 'selected' : '' }}>Reviewed (Ditinjau)</option>
                                    <option value="listed" {{ $sub->status === 'listed' ? 'selected' : '' }}>Listed (Tayang)</option>
                                    <option value="rejected" {{ $sub->status === 'rejected' ? 'selected' : '' }}>Rejected (Ditolak)</option>
                                </select>
                            </form>
                        </td>
                        <td class="p-4 text-center">
                            @php
                                $cleanPhone = preg_replace('/[^0-9]/', '', $sub->owner_phone);
                                if (str_starts_with($cleanPhone, '0')) {
                                    $cleanPhone = '62' . substr($cleanPhone, 1);
                                }
                                $ownerMsg = urlencode("Halo Bpk/Ibu " . $sub->owner_name . ", kami dari " . \App\Models\Setting::getSiteName() . " telah menerima pendaftaran titip properti Anda: " . $sub->property_title . " (Status Pembayaran: " . $sub->payment_status_label . "). Kami ingin mengonfirmasi detail listing Anda.");
                            @endphp
                            <a href="https://wa.me/{{ $cleanPhone }}?text={{ $ownerMsg }}"
                               target="_blank"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition-all">
                                <i class="fa-brands fa-whatsapp text-sm"></i> WhatsApp
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="p-8 text-center text-slate-400">
                            Belum ada pendaftaran properti dari pemilik.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Lightbox Modal Bukti Transfer QRIS -->
    <div x-show="previewImg"
         x-cloak
         @click.away="previewImg = null"
         class="fixed inset-0 z-50 bg-black/80 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-4 shadow-2xl relative">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-rose-600"></i> Bukti Pembayaran Titip Properti
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

    <div class="p-4 border-t border-slate-200">
        {{ $submissions->links() }}
    </div>
</div>
@endsection

