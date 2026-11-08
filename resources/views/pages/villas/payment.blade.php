@extends('layouts.app')

@php
    $siteName = \App\Models\Setting::getSiteName();
    $villa = $booking->property;
    $isPaid = $booking->payment_status === 'paid';
    $isConfirmed = $booking->booking_status === 'confirmed';
    $hasProof = !empty($booking->payment_proof);
@endphp

@section('title', 'Pembayaran QRIS Sewa Vila - ' . $booking->booking_code . ' | ' . $siteName)
@section('meta_description', 'Tagihan pembayaran sewa vila ' . $villa->title . ' dengan kode booking ' . $booking->booking_code . ' melalui QRIS resmi.')

@section('content')
<!-- Header Status Bar -->
<div class="bg-gradient-to-r from-pink-900 via-pink-800 to-slate-900 text-white py-8 sm:py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2 flex-wrap">
                    <span class="text-xs font-bold uppercase tracking-wider text-pink-300">Tagihan Sewa Vila</span>
                    <span class="text-pink-400">&bull;</span>
                    <span class="font-mono text-xs font-black bg-pink-950/70 text-pink-200 px-2.5 py-0.5 rounded-md border border-pink-700/50">
                        {{ $booking->booking_code }}
                    </span>
                    @if($isPaid)
                        <span class="bg-emerald-500/20 text-emerald-300 border border-emerald-400/40 text-[11px] font-black px-2.5 py-0.5 rounded-full flex items-center gap-1">
                            <i class="fa-solid fa-circle-check"></i> LUNAS (TERVERIFIKASI)
                        </span>
                    @elseif($hasProof)
                        <span class="bg-amber-500/20 text-amber-300 border border-amber-400/40 text-[11px] font-black px-2.5 py-0.5 rounded-full flex items-center gap-1">
                            <i class="fa-solid fa-clock-rotate-left"></i> MENUNGGU VERIFIKASI ADMIN
                        </span>
                    @else
                        <span class="bg-rose-500/20 text-rose-300 border border-rose-400/40 text-[11px] font-black px-2.5 py-0.5 rounded-full flex items-center gap-1">
                            <i class="fa-solid fa-hourglass-half"></i> MENUNGGU PEMBAYARAN QRIS
                        </span>
                    @endif
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
                    {{ $isPaid ? 'Pembayaran Berhasil & Terkonfirmasi' : 'Selesaikan Pembayaran via QRIS' }}
                </h1>
                <p class="text-xs sm:text-sm text-pink-100/90 mt-1">
                    Pemesanan vila <strong>{{ $villa->title }}</strong> untuk periode {{ $booking->check_in_date->format('d M Y') }} s/d {{ $booking->check_out_date->format('d M Y') }} ({{ $booking->total_nights }} malam).
                </p>
            </div>

            <!-- Action / Voucher Button -->
            <div class="flex items-center gap-3">
                @if($isPaid || $isConfirmed)
                    <a href="{{ route('villas.voucher', $booking->booking_code) }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white hover:bg-pink-50 text-pink-700 font-extrabold text-xs shadow-md transition-all">
                        <i class="fa-solid fa-print"></i>
                        <span>Cetak / Lihat Voucher</span>
                    </a>
                @endif
                <a href="{{ $booking->getWhatsAppConfirmUrl() }}" target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition-colors">
                    <i class="fa-brands fa-whatsapp text-sm"></i>
                    <span>Konfirmasi via WA</span>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
    <!-- Success Flash Notification -->
    @if(session('success'))
        <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm flex items-start gap-3 shadow-xs">
            <i class="fa-solid fa-circle-check text-emerald-600 text-lg shrink-0 mt-0.5"></i>
            <div>
                <p class="font-extrabold text-emerald-900">Berhasil!</p>
                <p class="text-emerald-700 mt-0.5">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <!-- Left Column: Booking & Villa Breakdown (5 cols) -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Villa Preview Card -->
            <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-xs space-y-4">
                <div class="relative aspect-16/10 rounded-2xl overflow-hidden bg-slate-100">
                    <img src="{{ $villa->primary_image }}" alt="{{ $villa->title }}" class="w-full h-full object-cover">
                    <div class="absolute bottom-2.5 left-2.5 bg-black/60 backdrop-blur-xs text-white text-[11px] font-bold px-2.5 py-1 rounded-lg">
                        <i class="fa-solid fa-location-dot text-pink-400 mr-1"></i>
                        {{ $villa->district ? $villa->district . ', ' : '' }}{{ $villa->city }}
                    </div>
                </div>

                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-pink-600">Detail Reservasi Vila</span>
                    <h3 class="text-base sm:text-lg font-bold text-slate-900 mt-0.5">{{ $villa->title }}</h3>
                </div>

                <!-- Schedule Card -->
                <div class="grid grid-cols-2 gap-3 p-3 bg-slate-50 rounded-2xl border border-slate-100 text-xs">
                    <div>
                        <span class="text-[10px] font-bold uppercase text-slate-400 block">Check-in</span>
                        <span class="font-black text-slate-900 text-xs sm:text-sm">{{ $booking->check_in_date->format('d M Y') }}</span>
                        <span class="text-[10px] text-slate-500 block">Mulai 14:00 WIB</span>
                    </div>
                    <div class="border-l border-slate-200 pl-3">
                        <span class="text-[10px] font-bold uppercase text-slate-400 block">Check-out</span>
                        <span class="font-black text-slate-900 text-xs sm:text-sm">{{ $booking->check_out_date->format('d M Y') }}</span>
                        <span class="text-[10px] text-slate-500 block">Maks. 12:00 WIB</span>
                    </div>
                </div>

                <!-- Guest & Nights Stats -->
                <div class="flex items-center justify-between text-xs py-2 px-3 bg-pink-50/50 rounded-xl border border-pink-100 text-slate-700">
                    <span><i class="fa-regular fa-moon text-pink-600 mr-1"></i> Durasi: <strong>{{ $booking->total_nights }} Malam</strong></span>
                    <span><i class="fa-solid fa-users text-pink-600 mr-1"></i> Tamu: <strong>{{ $booking->guests_count }} Orang</strong></span>
                </div>
            </div>

            <!-- Customer Details Card -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xs space-y-3 text-xs">
                <div class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-user text-pink-600"></i> Informasi Tamu / Pemesan
                </div>

                <div class="space-y-2 pt-1 text-slate-600">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Nama Pemesan:</span>
                        <span class="font-bold text-slate-900">{{ $booking->customer_name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">WhatsApp / HP:</span>
                        <span class="font-bold text-slate-900 font-mono">{{ $booking->customer_phone }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Email:</span>
                        <span class="font-bold text-slate-900">{{ $booking->customer_email }}</span>
                    </div>
                    @if($booking->special_requests)
                        <div class="pt-2 border-t border-slate-100">
                            <span class="text-slate-400 block mb-0.5">Catatan Khusus:</span>
                            <span class="font-medium text-slate-800 bg-slate-50 p-2 rounded-lg block">{{ $booking->special_requests }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Invoice Itemized Breakdown -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xs space-y-3 text-xs">
                <div class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-pink-600"></i> Rincian Tagihan
                </div>

                <div class="space-y-2.5 pt-1 text-slate-600">
                    <div class="flex justify-between">
                        <span>Sewa Vila ({{ $booking->total_nights }} malam x Rp {{ number_format($booking->price_per_night, 0, ',', '.') }})</span>
                        <span class="font-bold text-slate-900">{{ $booking->formatted_total_price }}</span>
                    </div>

                    @if($booking->cleaning_fee > 0)
                        <div class="flex justify-between">
                            <span>Biaya Kebersihan &amp; Layanan</span>
                            <span class="font-bold text-slate-900">{{ $booking->formatted_cleaning_fee }}</span>
                        </div>
                    @endif

                    @if($booking->security_deposit > 0)
                        <div class="flex justify-between">
                            <span>Deposit Keamanan (Dapat Dikembalikan)</span>
                            <span class="font-bold text-slate-900">{{ $booking->formatted_security_deposit }}</span>
                        </div>
                    @endif

                    <div class="pt-3 border-t border-slate-200 flex justify-between items-baseline text-sm">
                        <span class="font-black text-slate-900">Total Tagihan (QRIS)</span>
                        <span class="font-black text-pink-600 text-lg">{{ $booking->formatted_grand_total }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: QRIS Payment Showcase (7 cols) -->
        <div class="lg:col-span-7 space-y-6">
            <!-- QRIS Payment Box -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border-2 {{ $isPaid ? 'border-emerald-200 bg-emerald-50/20' : 'border-pink-300' }} shadow-xl relative overflow-hidden">
                <!-- Watermark / Background Glow -->
                <div class="absolute -right-16 -top-16 w-52 h-52 bg-pink-100/40 rounded-full blur-3xl pointer-events-none"></div>

                @if($isPaid)
                    <!-- PAID SUCCESS STATE -->
                    <div class="text-center py-6 space-y-4">
                        <div class="w-20 h-20 mx-auto rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-3xl shadow-inner">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>

                        <div>
                            <span class="text-xs font-black uppercase tracking-wider text-emerald-600">Status Pembayaran</span>
                            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">Pembayaran QRIS Berhasil!</h2>
                            <p class="text-xs sm:text-sm text-slate-600 max-w-md mx-auto mt-1">
                                Tagihan sebesar <strong class="text-emerald-700">{{ $booking->formatted_grand_total }}</strong> telah lunas terverifikasi pada {{ $booking->paid_at ? $booking->paid_at->format('d M Y H:i') : now()->format('d M Y H:i') }}.
                            </p>
                        </div>

                        <!-- Voucher Download / Print Callout -->
                        <div class="p-4 bg-emerald-50 rounded-2xl border border-emerald-200 text-emerald-900 text-xs sm:text-sm max-w-lg mx-auto flex flex-col sm:flex-row items-center justify-between gap-3 text-left">
                            <div>
                                <span class="font-black block">Voucher Reservasi Anda Siap!</span>
                                <span class="text-emerald-700 text-xs">Simpan atau cetak voucher ini untuk ditunjukkan saat check-in di vila.</span>
                            </div>
                            <a href="{{ route('villas.voucher', $booking->booking_code) }}"
                               class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-sm transition-colors shrink-0">
                                <i class="fa-solid fa-ticket mr-1"></i> Buka Voucher
                            </a>
                        </div>
                    </div>
                @else
                    <!-- UNPAID / PENDING QRIS STATE -->
                    <div class="space-y-6">
                        <!-- QRIS Header Banner -->
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-2xl bg-pink-50 text-pink-600 flex items-center justify-center text-2xl border border-pink-100">
                                    <i class="fa-solid fa-qrcode"></i>
                                </div>
                                <div>
                                    <span class="text-[10px] font-black uppercase tracking-wider text-pink-600">Metode Pembayaran Utama</span>
                                    <h2 class="text-lg sm:text-xl font-black text-slate-900">QRIS Standar Indonesia (NMID)</h2>
                                </div>
                            </div>

                            <span class="bg-emerald-100 text-emerald-800 text-[10px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider">
                                Instan &bull; Otomatis
                            </span>
                        </div>

                        <!-- QR Code Display Box -->
                        <div class="bg-gradient-to-b from-slate-50 to-white rounded-3xl p-6 border border-slate-200 text-center space-y-4">
                            <!-- QRIS Merchant Label -->
                            <div>
                                <span class="text-[11px] font-bold uppercase text-slate-400 block tracking-wider">Merchant Resmi</span>
                                <span class="text-sm sm:text-base font-black text-slate-800">{{ $merchantName }}</span>
                            </div>

                            <!-- QR Code Image with Scanning Frame -->
                            <div class="relative inline-block p-4 bg-white rounded-3xl shadow-lg border-2 border-pink-200 group">
                                <img src="{{ $qrisImage }}" alt="QRIS {{ $booking->booking_code }}"
                                     class="w-56 h-56 sm:w-64 sm:h-64 object-contain mx-auto rounded-xl">

                                <!-- Scanning Indicator Corner Accents -->
                                <div class="absolute top-2 left-2 w-4 h-4 border-t-2 border-l-2 border-pink-600 rounded-tl-md"></div>
                                <div class="absolute top-2 right-2 w-4 h-4 border-t-2 border-r-2 border-pink-600 rounded-tr-md"></div>
                                <div class="absolute bottom-2 left-2 w-4 h-4 border-b-2 border-l-2 border-pink-600 rounded-bl-md"></div>
                                <div class="absolute bottom-2 right-2 w-4 h-4 border-b-2 border-r-2 border-pink-600 rounded-br-md"></div>
                            </div>

                            <!-- Nominal Amount with Copy Button -->
                            <div class="max-w-md mx-auto p-4 bg-pink-50/70 rounded-2xl border border-pink-200 flex items-center justify-between gap-3"
                                 x-data="{ copied: false }">
                                <div class="text-left">
                                    <span class="text-[10px] font-bold uppercase text-pink-700 block">Nominal Pembayaran</span>
                                    <span class="text-xl sm:text-2xl font-black text-slate-900 font-mono">{{ $booking->formatted_grand_total }}</span>
                                </div>

                                <button type="button"
                                        @click="navigator.clipboard.writeText('{{ $booking->grand_total }}'); copied = true; setTimeout(() => copied = false, 2500)"
                                        class="px-3.5 py-2 rounded-xl bg-white hover:bg-pink-100 text-pink-700 font-extrabold text-xs border border-pink-300 shadow-2xs transition-colors flex items-center gap-1.5 shrink-0">
                                    <i class="fa-regular" :class="copied ? 'fa-circle-check text-emerald-600' : 'fa-copy'"></i>
                                    <span x-text="copied ? 'Tersalin!' : 'Salin Nominal'"></span>
                                </button>
                            </div>

                            <p class="text-xs text-slate-500 max-w-md mx-auto">
                                Buka aplikasi m-Banking (BCA, Mandiri, BRI, BNI) atau E-Wallet (GoPay, OVO, DANA, ShopeePay), pilih menu <strong>Scan QRIS</strong>, dan arahkan kamera ke barcode di atas.
                            </p>
                        </div>

                        <!-- Instant Simulation / Demo Button -->
                        <div class="p-4 bg-amber-50 rounded-2xl border border-amber-200 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-black text-amber-900 uppercase tracking-wider flex items-center gap-1.5">
                                    <i class="fa-solid fa-bolt text-amber-600"></i> Mode Simulasi Pembayaran QRIS (Demo)
                                </span>
                                <span class="text-[10px] bg-amber-200/80 text-amber-900 font-bold px-2 py-0.5 rounded-md">Instant Test</span>
                            </div>
                            <p class="text-xs text-amber-800">
                                Ingin menguji alur pembayaran tanpa harus scan QRIS sungguhan? Klik tombol simulasi di bawah untuk langsung memverifikasi transaksi sebagai Lunas.
                            </p>
                            <form action="{{ route('villas.payment.simulate', $booking->booking_code) }}" method="POST">
                                @csrf
                                <button type="submit"
                                        class="w-full py-2.5 px-4 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-extrabold text-xs shadow-sm transition-colors flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <span>Simulasi Pembayaran QRIS Berhasil (Instant Demo)</span>
                                </button>
                            </form>
                        </div>

                        <!-- Upload Proof Form -->
                        <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200 space-y-3">
                            <div class="flex items-center justify-between">
                                <h3 class="text-xs font-black uppercase tracking-wider text-slate-800 flex items-center gap-1.5">
                                    <i class="fa-solid fa-upload text-pink-600"></i> Upload Bukti Struk QRIS / Transfer
                                </h3>
                                @if($hasProof)
                                    <span class="text-[10px] bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded-md">
                                        Bukti Sudah Diunggah
                                    </span>
                                @endif
                            </div>

                            @if($hasProof)
                                <div class="p-3 bg-white rounded-xl border border-slate-200 flex items-center justify-between gap-3 text-xs">
                                    <div class="flex items-center gap-2 text-slate-700 truncate">
                                        <i class="fa-solid fa-image text-pink-600"></i>
                                        <span class="truncate">Bukti pembayaran telah kami terima dan sedang diverifikasi admin.</span>
                                    </div>
                                    <a href="{{ asset('storage/' . $booking->payment_proof) }}" target="_blank" class="text-pink-600 font-bold hover:underline shrink-0">
                                        Lihat File
                                    </a>
                                </div>
                            @endif

                            <form action="{{ route('villas.payment.proof', $booking->booking_code) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                                @csrf
                                <div>
                                    <input type="file" name="payment_proof" accept="image/jpeg,image/png,image/webp,image/jpg" required
                                           class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-pink-100 file:text-pink-700 hover:file:bg-pink-200 cursor-pointer">
                                </div>

                                <button type="submit"
                                        class="w-full py-2.5 px-4 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-extrabold text-xs shadow-sm transition-colors flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                    <span>{{ $hasProof ? 'Unggah Ulang Bukti Pembayaran' : 'Kirim Bukti Pembayaran QRIS' }}</span>
                                </button>
                            </form>
                        </div>

                        <!-- Fallback Bank Transfer Info Accordion -->
                        <div x-data="{ openBank: false }" class="bg-white rounded-2xl border border-slate-200 overflow-hidden text-xs">
                            <button type="button" @click="openBank = !openBank"
                                    class="w-full p-4 flex items-center justify-between text-left font-bold text-slate-800 hover:bg-slate-50 transition-colors">
                                <span class="flex items-center gap-2">
                                    <i class="fa-solid fa-building-columns text-slate-500"></i>
                                    <span>Pilihan Lain: Transfer Manual ke Rekening Bank</span>
                                </span>
                                <i class="fa-solid fa-chevron-down text-slate-400 text-xs transition-transform" :class="openBank ? 'rotate-180 text-pink-600' : ''"></i>
                            </button>

                            <div x-show="openBank" x-cloak class="p-4 pt-1 space-y-3 border-t border-slate-100 bg-slate-50/50">
                                @foreach($bankAccounts as $acc)
                                    <div class="p-3 bg-white rounded-xl border border-slate-200 flex items-center justify-between gap-3">
                                        <div>
                                            <span class="text-[10px] font-black uppercase text-pink-600 block">{{ $acc['bank_name'] }}</span>
                                            <span class="text-sm font-extrabold font-mono text-slate-900">{{ $acc['account_number'] }}</span>
                                            <span class="text-[10px] text-slate-500 block">a.n. {{ $acc['account_name'] }}</span>
                                        </div>
                                        <button type="button"
                                                @click="navigator.clipboard.writeText('{{ $acc['account_number'] }}')"
                                                class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 font-bold text-[11px] shrink-0">
                                            Salin No. Rek
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Help / CS Box -->
            <div class="bg-slate-50 rounded-3xl p-5 border border-slate-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-pink-100 text-pink-600 flex items-center justify-center text-lg shrink-0">
                        <i class="fa-solid fa-headset"></i>
                    </div>
                    <div>
                        <span class="font-extrabold text-slate-900 block">Butuh bantuan terkait pembayaran?</span>
                        <span class="text-slate-500">Tim kami siap membantu konfirmasi pembayaran dan pertanyaan reservasi vila.</span>
                    </div>
                </div>

                <a href="{{ $booking->getWhatsAppConfirmUrl() }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition-colors shrink-0">
                    <i class="fa-brands fa-whatsapp text-sm"></i>
                    <span>Hubungi CS</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

