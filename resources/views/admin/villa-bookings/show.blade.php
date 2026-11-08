@extends('layouts.admin')

@section('title', 'Detail Reservasi Vila ' . $booking->booking_code . ' - Admin Portal')

@section('content')
<div class="space-y-6">
    <!-- Top Back & Actions Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('admin.villa-bookings.index') }}"
               class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-pink-600 transition-colors mb-1">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali ke Daftar Pesanan Vila</span>
            </a>
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight font-mono">
                    {{ $booking->booking_code }}
                </h1>
                @if($booking->payment_status === 'paid')
                    <span class="bg-emerald-100 text-emerald-800 text-xs font-black px-3 py-1 rounded-full flex items-center gap-1">
                        <i class="fa-solid fa-circle-check text-emerald-600"></i> LUNAS
                    </span>
                @else
                    <span class="bg-rose-100 text-rose-800 text-xs font-black px-3 py-1 rounded-full flex items-center gap-1">
                        <i class="fa-solid fa-hourglass-half text-rose-600"></i> MENUNGGU BAYAR
                    </span>
                @endif
                <span class="text-xs font-bold px-3 py-1 rounded-full bg-slate-100 text-slate-700">
                    {{ $booking->booking_status_label }}
                </span>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('villas.voucher', $booking->booking_code) }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs transition-colors">
                <i class="fa-solid fa-print"></i>
                <span>Cetak Voucher</span>
            </a>

            <a href="{{ $booking->getWhatsAppConfirmUrl() }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-sm transition-colors">
                <i class="fa-brands fa-whatsapp text-sm"></i>
                <span>WhatsApp Tamu</span>
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

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Left: Villa, Guest & Invoice Details (7 cols) -->
        <div class="lg:col-span-7 space-y-6">
            <!-- Villa Information Card -->
            <div class="bg-white rounded-2xl p-6 border border-pink-100 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <span class="text-xs font-black uppercase tracking-wider text-pink-600">Unit Vila yang Dipesan</span>
                    <a href="{{ route('villas.show', $booking->property->slug) }}" target="_blank" class="text-xs text-pink-600 font-bold hover:underline">
                        Lihat Listing &rarr;
                    </a>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 items-start">
                    <img src="{{ $booking->property->primary_image }}" alt="{{ $booking->property->title }}"
                         class="w-full sm:w-40 h-28 rounded-xl object-cover bg-slate-100 shrink-0">

                    <div class="space-y-1.5 flex-1 min-w-0">
                        <h2 class="text-base sm:text-lg font-bold text-slate-900 leading-snug">{{ $booking->property->title }}</h2>
                        <p class="text-xs text-slate-500">
                            <i class="fa-solid fa-location-dot text-pink-500 mr-1"></i>
                            {{ $booking->property->address }}, {{ $booking->property->district }}, {{ $booking->property->city }}
                        </p>
                        <div class="text-xs text-slate-600 flex items-center gap-3 pt-1">
                            <span><i class="fa-solid fa-bed text-pink-500 mr-1"></i> {{ $booking->property->bedrooms ?: 1 }} Kamar</span>
                            <span><i class="fa-solid fa-bath text-pink-500 mr-1"></i> {{ $booking->property->bathrooms ?: 1 }} KM</span>
                            <span><i class="fa-solid fa-users text-pink-500 mr-1"></i> Maks. {{ $booking->property->max_guests }} Tamu</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Schedule & Guest Data Card -->
            <div class="bg-white rounded-2xl p-6 border border-pink-100 shadow-xs space-y-4 text-xs">
                <span class="text-xs font-black uppercase tracking-wider text-pink-600 block pb-2 border-b border-slate-100">
                    Jadwal Menginap &amp; Kontak Tamu
                </span>

                <div class="grid grid-cols-2 gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Check-in</span>
                        <span class="text-sm font-black text-slate-900">{{ $booking->check_in_date->format('d M Y') }}</span>
                        <span class="text-[10px] text-slate-500 block">Mulai 14:00 WIB</span>
                    </div>
                    <div class="border-l border-slate-200 pl-3">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Check-out</span>
                        <span class="text-sm font-black text-slate-900">{{ $booking->check_out_date->format('d M Y') }}</span>
                        <span class="text-[10px] text-slate-500 block">Maksimal 12:00 WIB</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 text-slate-700">
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Nama Tamu</span>
                        <span class="font-extrabold text-slate-900 text-sm">{{ $booking->customer_name }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">No. WhatsApp / HP</span>
                        <span class="font-mono font-bold text-slate-900">{{ $booking->customer_phone }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Email</span>
                        <span class="font-medium text-slate-800">{{ $booking->customer_email }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Jumlah Tamu</span>
                        <span class="font-bold text-slate-900">{{ $booking->guests_count }} Orang ({{ $booking->total_nights }} Malam)</span>
                    </div>
                </div>

                @if($booking->special_requests)
                    <div class="pt-2 border-t border-slate-100">
                        <span class="text-slate-400 block text-[10px] uppercase font-bold mb-1">Catatan / Permintaan Khusus:</span>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-slate-800">
                            {{ $booking->special_requests }}
                        </div>
                    </div>
                @endif
            </div>

            <!-- Financial Breakdown Card -->
            <div class="bg-white rounded-2xl p-6 border border-pink-100 shadow-xs space-y-3 text-xs">
                <span class="text-xs font-black uppercase tracking-wider text-pink-600 block pb-2 border-b border-slate-100">
                    Rincian Biaya &amp; Pembayaran QRIS
                </span>

                <div class="space-y-2 text-slate-700 pt-1">
                    <div class="flex justify-between">
                        <span>Biaya Sewa Vila ({{ $booking->total_nights }} malam x Rp {{ number_format($booking->price_per_night, 0, ',', '.') }})</span>
                        <span class="font-mono font-bold text-slate-900">{{ $booking->formatted_total_price }}</span>
                    </div>

                    @if($booking->cleaning_fee > 0)
                        <div class="flex justify-between">
                            <span>Biaya Layanan &amp; Kebersihan</span>
                            <span class="font-mono font-bold text-slate-900">{{ $booking->formatted_cleaning_fee }}</span>
                        </div>
                    @endif

                    @if($booking->security_deposit > 0)
                        <div class="flex justify-between">
                            <span>Deposit Keamanan</span>
                            <span class="font-mono font-bold text-slate-900">{{ $booking->formatted_security_deposit }}</span>
                        </div>
                    @endif

                    <div class="pt-3 border-t border-slate-200 flex justify-between items-baseline text-sm">
                        <span class="font-black text-slate-900">Total Tagihan (QRIS)</span>
                        <span class="font-black text-pink-600 text-lg font-mono">{{ $booking->formatted_grand_total }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: QRIS Proof & Admin Actions (5 cols) -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Payment Proof Box -->
            <div class="bg-white rounded-2xl p-6 border border-pink-100 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <span class="text-xs font-black uppercase tracking-wider text-pink-600 flex items-center gap-1.5">
                        <i class="fa-solid fa-receipt"></i> Bukti Pembayaran QRIS
                    </span>
                    @if($booking->payment_status === 'paid')
                        <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">
                            Terverifikasi
                        </span>
                    @endif
                </div>

                @if($booking->payment_proof)
                    <div class="space-y-3">
                        <div class="rounded-xl overflow-hidden border border-slate-200 bg-slate-50 max-h-96 flex items-center justify-center p-2">
                            <a href="{{ asset('storage/' . $booking->payment_proof) }}" target="_blank" title="Klik untuk membuka ukuran penuh">
                                <img src="{{ asset('storage/' . $booking->payment_proof) }}" alt="Bukti Transfer" class="max-h-80 w-auto rounded-lg object-contain shadow-2xs hover:opacity-90 transition-opacity">
                            </a>
                        </div>
                        <div class="text-center">
                            <a href="{{ asset('storage/' . $booking->payment_proof) }}" target="_blank"
                               class="inline-flex items-center gap-1 text-xs text-pink-600 font-bold hover:underline">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                <span>Buka Bukti Ukuran Asli</span>
                            </a>
                        </div>
                    </div>
                @else
                    <div class="py-8 text-center bg-slate-50 rounded-xl border border-dashed border-slate-200 text-slate-400 space-y-1">
                        <i class="fa-solid fa-image text-3xl text-slate-300 block mb-1"></i>
                        <span class="font-bold text-xs text-slate-600 block">Belum ada bukti pembayaran</span>
                        <span class="text-[10px] text-slate-400">Tamu belum mengunggah struk/screenshot pembayaran QRIS.</span>
                    </div>
                @endif

                <!-- One-click Quick Confirm Button if not paid -->
                @if($booking->payment_status !== 'paid')
                    <form action="{{ route('admin.villa-bookings.confirm-payment', $booking->id) }}" method="POST"
                          onsubmit="return confirm('Konfirmasi bahwa pembayaran QRIS ini sudah diterima & valid?')">
                        @csrf
                        <button type="submit"
                                class="w-full py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-circle-check text-sm"></i>
                            <span>Konfirmasi Pembayaran Lunas (QRIS Terverifikasi)</span>
                        </button>
                    </form>
                @endif
            </div>

            <!-- Status Management Card -->
            <div class="bg-white rounded-2xl p-6 border border-pink-100 shadow-xs space-y-4">
                <span class="text-xs font-black uppercase tracking-wider text-pink-600 block pb-2 border-b border-slate-100">
                    Perbarui Status Reservasi
                </span>

                <form action="{{ route('admin.villa-bookings.status', $booking->id) }}" method="POST" class="space-y-3 text-xs">
                    @csrf

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Status Reservasi</label>
                        <select name="booking_status" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-pink-500">
                            <option value="pending" {{ $booking->booking_status === 'pending' ? 'selected' : '' }}>Menunggu Pembayaran</option>
                            <option value="confirmed" {{ $booking->booking_status === 'confirmed' ? 'selected' : '' }}>Terkonfirmasi (Jadwal Aman)</option>
                            <option value="checked_in" {{ $booking->booking_status === 'checked_in' ? 'selected' : '' }}>Sedang Menginap (Checked-In)</option>
                            <option value="completed" {{ $booking->booking_status === 'completed' ? 'selected' : '' }}>Selesai (Checked-Out)</option>
                            <option value="cancelled" {{ $booking->booking_status === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Status Pembayaran</label>
                        <select name="payment_status" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-pink-500">
                            <option value="pending" {{ $booking->payment_status === 'pending' ? 'selected' : '' }}>Pending (Menunggu Bayar)</option>
                            <option value="paid" {{ $booking->payment_status === 'paid' ? 'selected' : '' }}>Lunas (Paid via QRIS)</option>
                            <option value="failed" {{ $booking->payment_status === 'failed' ? 'selected' : '' }}>Gagal (Failed)</option>
                            <option value="refunded" {{ $booking->payment_status === 'refunded' ? 'selected' : '' }}>Dikembalikan (Refunded)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Catatan Internal Admin (Opsional)</label>
                        <textarea name="admin_notes" rows="2" placeholder="Catatan internal..."
                                  class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-pink-500">{{ $booking->admin_notes }}</textarea>
                    </div>

                    <button type="submit"
                            class="w-full py-2.5 px-4 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-extrabold text-xs shadow-xs transition-colors">
                        Simpan Perubahan Status
                    </button>
                </form>
            </div>

            <!-- Danger Zone Delete -->
            <div class="bg-rose-50/50 rounded-2xl p-4 border border-rose-100 flex items-center justify-between text-xs">
                <div>
                    <span class="font-extrabold text-rose-900 block">Hapus Reservasi</span>
                    <span class="text-[10px] text-rose-700">Hapus data pesanan ini dari sistem secara permanen.</span>
                </div>
                <form action="{{ route('admin.villa-bookings.destroy', $booking->id) }}" method="POST"
                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus pesanan {{ $booking->booking_code }}?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs transition-colors cursor-pointer">
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

