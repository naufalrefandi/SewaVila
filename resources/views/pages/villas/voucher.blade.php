<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Voucher Reservasi Vila - {{ $booking->booking_code }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&family=JetBrains+Mono:wght@600;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @vite(['resources/css/app.css'])

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            .voucher-card { border: none !important; box-shadow: none !important; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 p-4 sm:p-8 antialiased min-h-screen flex flex-col justify-center items-center">
    <!-- Top Action Bar (hidden on print) -->
    <div class="max-w-3xl w-full mb-4 flex items-center justify-between no-print">
        <a href="{{ route('villas.payment', $booking->booking_code) }}"
           class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 hover:text-pink-600 transition-colors">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Kembali ke Halaman Tagihan</span>
        </a>

        <div class="flex items-center gap-2">
            <button onclick="window.print()"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-extrabold text-xs shadow-md shadow-pink-600/30 transition-all cursor-pointer">
                <i class="fa-solid fa-print"></i>
                <span>Cetak / Simpan PDF</span>
            </button>
            <a href="{{ $booking->getWhatsAppConfirmUrl() }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition-colors">
                <i class="fa-brands fa-whatsapp text-sm"></i>
                <span>Hubungi Host</span>
            </a>
        </div>
    </div>

    <!-- Voucher Printable Container -->
    <div class="max-w-3xl w-full bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden voucher-card">
        <!-- Voucher Header -->
        <div class="bg-gradient-to-r from-pink-800 via-pink-700 to-slate-900 text-white p-6 sm:p-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-pink-300 block mb-1">
                        {{ \App\Models\Setting::getSiteName() }} &bull; VOUCHER RESERVASI VILA
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight">Bukti Reservasi Resmi</h1>
                    <p class="text-xs text-pink-200 mt-0.5">Tunjukkan voucher ini kepada pengelola vila saat check-in.</p>
                </div>

                <div class="sm:text-right">
                    <span class="text-[10px] uppercase font-bold text-pink-300 block">Kode Booking</span>
                    <span class="font-mono text-xl sm:text-2xl font-black bg-white/10 px-3 py-1 rounded-xl border border-white/20 inline-block mt-0.5">
                        {{ $booking->booking_code }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Status & QR Code Bar -->
        <div class="bg-emerald-50 px-6 sm:px-8 py-3.5 border-b border-emerald-100 flex items-center justify-between gap-4 flex-wrap">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-xs font-black uppercase tracking-wider text-emerald-900">
                    Status: {{ $booking->booking_status === 'confirmed' ? 'TERKONFIRMASI (LUNAS)' : strtoupper($booking->booking_status_label) }}
                </span>
            </div>

            <span class="text-xs font-bold text-emerald-800">
                Metode Pembayaran: <strong class="uppercase">{{ $booking->payment_method }} (QRIS Resmi)</strong>
            </span>
        </div>

        <!-- Voucher Body -->
        <div class="p-6 sm:p-8 space-y-6">
            <!-- Villa & Host Info -->
            <div class="flex flex-col sm:flex-row gap-5 pb-6 border-b border-slate-100">
                <img src="{{ $booking->property->primary_image }}" alt="{{ $booking->property->title }}"
                     class="w-full sm:w-44 h-32 rounded-2xl object-cover shrink-0 bg-slate-100">

                <div class="flex-1 space-y-2">
                    <span class="text-[10px] font-black uppercase text-pink-600 tracking-wider">Unit Vila</span>
                    <h2 class="text-lg sm:text-xl font-bold text-slate-900 leading-snug">{{ $booking->property->title }}</h2>
                    <p class="text-xs text-slate-500">
                        <i class="fa-solid fa-location-dot text-pink-600 mr-1"></i>
                        {{ $booking->property->address }}, {{ $booking->property->district }}, {{ $booking->property->city }}
                    </p>
                    <div class="text-xs text-slate-600 flex items-center gap-3 pt-1">
                        <span><i class="fa-solid fa-bed text-pink-600 mr-1"></i> {{ $booking->property->bedrooms ?: 1 }} Kamar</span>
                        <span><i class="fa-solid fa-bath text-pink-600 mr-1"></i> {{ $booking->property->bathrooms ?: 1 }} Kamar Mandi</span>
                        <span><i class="fa-solid fa-users text-pink-600 mr-1"></i> Maks. {{ $booking->property->max_guests }} Orang</span>
                    </div>
                </div>
            </div>

            <!-- Check-in & Check-out Schedule Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-5 bg-slate-50 rounded-2xl border border-slate-200 text-center text-xs">
                <div>
                    <span class="text-[10px] font-bold uppercase text-slate-400 block mb-0.5">Jadwal Check-in</span>
                    <span class="text-sm font-black text-slate-900 block">{{ $booking->check_in_date->format('d M Y') }}</span>
                    <span class="text-[11px] text-pink-600 font-bold">Mulai 14:00 WIB</span>
                </div>
                <div class="border-y sm:border-y-0 sm:border-x border-slate-200 py-2 sm:py-0">
                    <span class="text-[10px] font-bold uppercase text-slate-400 block mb-0.5">Jadwal Check-out</span>
                    <span class="text-sm font-black text-slate-900 block">{{ $booking->check_out_date->format('d M Y') }}</span>
                    <span class="text-[11px] text-pink-600 font-bold">Maksimal 12:00 WIB</span>
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase text-slate-400 block mb-0.5">Durasi &amp; Tamu</span>
                    <span class="text-sm font-black text-slate-900 block">{{ $booking->total_nights }} Malam</span>
                    <span class="text-[11px] text-slate-500">{{ $booking->guests_count }} Orang Tamu</span>
                </div>
            </div>

            <!-- Guest & Payment Summary 2-Col -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-xs">
                <div class="space-y-2">
                    <h3 class="font-extrabold text-slate-900 text-sm flex items-center gap-1.5">
                        <i class="fa-solid fa-user-check text-pink-600"></i> Data Tamu Terdaftar
                    </h3>
                    <div class="p-4 bg-white rounded-2xl border border-slate-200 space-y-1.5 text-slate-600">
                        <div class="flex justify-between">
                            <span>Nama Lengkap:</span>
                            <strong class="text-slate-900">{{ $booking->customer_name }}</strong>
                        </div>
                        <div class="flex justify-between">
                            <span>No. WhatsApp:</span>
                            <strong class="text-slate-900 font-mono">{{ $booking->customer_phone }}</strong>
                        </div>
                        <div class="flex justify-between">
                            <span>Email:</span>
                            <strong class="text-slate-900">{{ $booking->customer_email }}</strong>
                        </div>
                        @if($booking->special_requests)
                            <div class="pt-1 border-t border-slate-100">
                                <span class="text-slate-400 block">Permintaan Khusus:</span>
                                <span class="text-slate-800">{{ $booking->special_requests }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="space-y-2">
                    <h3 class="font-extrabold text-slate-900 text-sm flex items-center gap-1.5">
                        <i class="fa-solid fa-receipt text-pink-600"></i> Rincian Pembayaran
                    </h3>
                    <div class="p-4 bg-white rounded-2xl border border-slate-200 space-y-1.5 text-slate-600">
                        <div class="flex justify-between">
                            <span>Biaya Sewa ({{ $booking->total_nights }} malam):</span>
                            <span>{{ $booking->formatted_total_price }}</span>
                        </div>
                        @if($booking->cleaning_fee > 0)
                            <div class="flex justify-between">
                                <span>Biaya Layanan &amp; Kebersihan:</span>
                                <span>{{ $booking->formatted_cleaning_fee }}</span>
                            </div>
                        @endif
                        <div class="pt-2 border-t border-slate-200 flex justify-between items-baseline text-sm">
                            <strong class="text-slate-900">Total Dibayar:</strong>
                            <strong class="text-pink-600 text-base font-black">{{ $booking->formatted_grand_total }}</strong>
                        </div>
                        <div class="text-[10px] text-emerald-700 font-bold text-right pt-0.5">
                            <i class="fa-solid fa-check-double"></i> Terbayar via QRIS Resmi
                        </div>
                    </div>
                </div>
            </div>

            <!-- Check-in QR & Instructions -->
            <div class="p-5 bg-pink-50/50 rounded-2xl border border-pink-100 flex flex-col sm:flex-row items-center gap-5 text-xs text-slate-600">
                <div class="p-2 bg-white rounded-xl border border-pink-200 shadow-2xs shrink-0 text-center">
                    <img src="{{ $booking->getQrisCodeUrl() }}" alt="QR {{ $booking->booking_code }}" class="w-24 h-24 object-contain mx-auto">
                    <span class="text-[9px] font-mono font-bold text-slate-400 block mt-1">Scan Check-in</span>
                </div>

                <div class="space-y-1.5 text-center sm:text-left">
                    <span class="font-extrabold text-slate-900 block text-sm">Petunjuk Check-in Vila</span>
                    <p>• Silakan hubungi host / penjaga vila 2 jam sebelum kedatangan untuk konfirmasi serah terima kunci.</p>
                    <p>• Tunjukkan voucher ini dan kartu identitas asli (KTP/Paspor) saat check-in.</p>
                    <p>• Waktu check-out maksimal pukul 12:00 WIB untuk pembersihan unit bagi tamu berikutnya.</p>
                </div>
            </div>
        </div>

        <!-- Voucher Footer -->
        <div class="bg-slate-50 px-6 sm:px-8 py-4 border-t border-slate-200 text-center text-[11px] text-slate-400">
            Diterbitkan oleh sistem <strong>{{ \App\Models\Setting::getSiteName() }}</strong> pada {{ now()->format('d M Y H:i') }} WIB &bull; Kode Keamanan: {{ hash('crc32', $booking->booking_code) }}
        </div>
    </div>
</body>
</html>

