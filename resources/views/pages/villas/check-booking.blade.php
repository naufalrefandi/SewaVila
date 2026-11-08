@extends('layouts.app')

@section('title', 'Cek Status Pemesanan Vila | ' . \App\Models\Setting::getSiteName())
@section('meta_description', 'Lacak status reservasi dan pembayaran QRIS sewa vila Anda dengan memasukkan kode booking atau nomor WhatsApp.')

@section('content')
<div class="bg-gradient-to-br from-pink-900 via-pink-800 to-slate-900 text-white py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-3">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-pink-500/20 text-pink-200 border border-pink-400/30 text-xs font-bold uppercase tracking-wider">
            <i class="fa-solid fa-ticket text-amber-300"></i> Lacak Reservasi
        </span>
        <h1 class="text-3xl sm:text-4xl font-black tracking-tight">Cek Status Pemesanan Vila</h1>
        <p class="text-xs sm:text-sm text-pink-100 max-w-xl mx-auto">
            Masukkan Kode Booking (contoh: <span class="font-mono bg-pink-950/60 px-1.5 py-0.5 rounded text-pink-200">VLA-2611-XXXXX</span>) atau Nomor WhatsApp yang Anda gunakan saat memesan.
        </p>

        <!-- Search Form -->
        <div class="mt-6 max-w-xl mx-auto">
            <form action="{{ route('villas.check') }}" method="GET" class="flex items-center gap-2 bg-white p-2 rounded-2xl shadow-xl border border-pink-200 text-slate-800">
                <div class="relative flex-1">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input type="text" name="q" value="{{ $search }}" placeholder="Kode Booking atau No. WhatsApp / Email..." required
                           class="w-full pl-10 pr-3 py-2.5 text-xs sm:text-sm font-semibold text-slate-900 focus:outline-none placeholder:text-slate-400">
                </div>
                <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-extrabold text-xs sm:text-sm shadow-md transition-colors shrink-0">
                    <span>Lacak</span>
                </button>
            </form>
        </div>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    @if(!empty($search))
        @if($booking)
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-md space-y-6">
                <!-- Header Card -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-mono text-sm font-black bg-pink-50 text-pink-700 border border-pink-200 px-2.5 py-0.5 rounded-lg">
                                {{ $booking->booking_code }}
                            </span>
                            @if($booking->payment_status === 'paid')
                                <span class="bg-emerald-100 text-emerald-800 text-xs font-black px-2.5 py-0.5 rounded-full">
                                    <i class="fa-solid fa-circle-check text-emerald-600 mr-1"></i> Lunas
                                </span>
                            @else
                                <span class="bg-rose-100 text-rose-800 text-xs font-black px-2.5 py-0.5 rounded-full">
                                    <i class="fa-solid fa-hourglass-half text-rose-600 mr-1"></i> Menunggu Bayar QRIS
                                </span>
                            @endif
                        </div>
                        <h2 class="text-xl font-black text-slate-900">{{ $booking->property->title }}</h2>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $booking->property->district }}, {{ $booking->property->city }}</p>
                    </div>

                    <div class="text-left sm:text-right">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Total Tagihan</span>
                        <span class="text-xl font-black text-pink-600">{{ $booking->formatted_grand_total }}</span>
                    </div>
                </div>

                <!-- Schedule & Guest Info -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-1.5">
                        <span class="text-[10px] font-bold uppercase text-slate-400 block">Jadwal Menginap</span>
                        <div class="font-extrabold text-slate-900 text-sm">
                            {{ $booking->check_in_date->format('d M Y') }} s/d {{ $booking->check_out_date->format('d M Y') }}
                        </div>
                        <div class="text-slate-600">
                            {{ $booking->total_nights }} Malam &bull; {{ $booking->guests_count }} Tamu
                        </div>
                    </div>

                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-1.5">
                        <span class="text-[10px] font-bold uppercase text-slate-400 block">Data Pemesan</span>
                        <div class="font-extrabold text-slate-900 text-sm">{{ $booking->customer_name }}</div>
                        <div class="text-slate-600 font-mono">{{ $booking->customer_phone }} &bull; {{ $booking->customer_email }}</div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="pt-2 flex flex-col sm:flex-row items-center justify-end gap-3">
                    <a href="{{ $booking->getWhatsAppConfirmUrl() }}" target="_blank"
                       class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition-colors text-center">
                        <i class="fa-brands fa-whatsapp mr-1"></i> Hubungi Host
                    </a>

                    @if($booking->payment_status === 'paid' || $booking->booking_status === 'confirmed')
                        <a href="{{ route('villas.voucher', $booking->booking_code) }}"
                           class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-extrabold text-xs shadow-md transition-all text-center">
                            <i class="fa-solid fa-print mr-1"></i> Buka / Cetak Voucher
                        </a>
                    @else
                        <a href="{{ route('villas.payment', $booking->booking_code) }}"
                           class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-extrabold text-xs shadow-md transition-all text-center">
                            <i class="fa-solid fa-qrcode mr-1"></i> Buka Halaman Pembayaran QRIS
                        </a>
                    @endif
                </div>
            </div>
        @else
            <div class="bg-white rounded-3xl p-8 border border-slate-200 text-center space-y-3">
                <div class="w-14 h-14 mx-auto rounded-full bg-rose-50 text-rose-500 flex items-center justify-center text-2xl">
                    <i class="fa-solid fa-circle-question"></i>
                </div>
                <h3 class="text-base font-bold text-slate-900">Pemesanan Tidak Ditemukan</h3>
                <p class="text-xs text-slate-500 max-w-md mx-auto">
                    Tidak ditemukan data reservasi vila dengan kata kunci "<strong>{{ $search }}</strong>". Pastikan kode booking atau nomor HP/email yang Anda masukkan sudah benar.
                </p>
                <div class="pt-2">
                    <a href="{{ route('villas.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors">
                        <span>Lihat Daftar Vila</span>
                    </a>
                </div>
            </div>
        @endif
    @else
        <!-- Information Box when no query is typed -->
        <div class="bg-white rounded-3xl p-8 border border-slate-200 text-center space-y-4">
            <div class="w-14 h-14 mx-auto rounded-full bg-pink-50 text-pink-600 flex items-center justify-center text-2xl">
                <i class="fa-solid fa-receipt"></i>
            </div>
            <h3 class="text-base font-bold text-slate-900">Belum Punya Kode Booking?</h3>
            <p class="text-xs text-slate-500 max-w-md mx-auto">
                Kode booking otomatis Anda dapatkan saat memesan vila melalui website kami. Kode juga dikirimkan langsung ke email Anda dan dapat dilihat di halaman pembayaran QRIS.
            </p>
            <div class="pt-2">
                <a href="{{ route('villas.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-extrabold text-xs shadow-md shadow-pink-600/20 transition-all">
                    <i class="fa-solid fa-umbrella-beach"></i>
                    <span>Cari &amp; Sewa Vila Sekarang</span>
                </a>
            </div>
        </div>
    @endif
</div>
@endsection

