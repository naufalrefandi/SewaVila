@extends('layouts.admin')

@section('title', 'Topup Kuota Listing Agen - ' . \App\Models\Setting::getSiteName() . '')
@section('page_title', 'Topup Kuota Listing')

@section('content')
<div class="space-y-6" x-data="{ copiedBca: false, copiedOther: false }">
    <!-- Header & Breadcrumb -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-cart-plus text-pink-600"></i> Topup Kuota Listing Agen
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Tambah slot kuota listing properti Anda untuk menjangkau lebih banyak calon pembeli dan penyewa.
            </p>
        </div>
        <a href="{{ route('admin.properties') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:text-pink-600 text-xs font-bold shadow-2xs self-start">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Listing Saya
        </a>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm flex items-start gap-3">
            <i class="fa-solid fa-circle-check text-emerald-600 text-base mt-0.5 shrink-0"></i>
            <div>
                <strong>Berhasil!</strong> {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm flex items-start gap-3">
            <i class="fa-solid fa-circle-exclamation text-rose-600 text-base mt-0.5 shrink-0"></i>
            <div>
                <strong>Pemberitahuan:</strong> {{ session('error') }}
            </div>
        </div>
    @endif

    <!-- Quota Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-2xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-pink-50 text-pink-600 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-box-archive"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Sisa Kuota Listing</div>
                <div class="text-2xl font-black {{ $agent->remaining_quota > 0 ? 'text-pink-600' : 'text-rose-600' }} mt-0.5">
                    {{ $agent->remaining_quota }} <span class="text-xs font-bold text-slate-400">Slot Tersedia</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-2xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-layer-group"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Kuota Terpakai</div>
                <div class="text-2xl font-black text-slate-900 mt-0.5">
                    {{ $agent->used_quota }} <span class="text-xs font-bold text-slate-400">Listing Aktif</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-2xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Kuota Maksimal</div>
                <div class="text-2xl font-black text-purple-700 mt-0.5">
                    {{ $agent->listing_quota }} <span class="text-xs font-bold text-slate-400">Slot Akun</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Section: Package & Payment Details + Form -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left Column: Topup Package & Payment Info -->
        <div class="lg:col-span-7 space-y-4">
            <!-- Paket Card -->
            <div class="p-5 rounded-2xl bg-gradient-to-r from-pink-500 via-rose-500 to-pink-600 text-white shadow-md relative overflow-hidden">
                <div class="absolute -right-6 -bottom-6 w-32 h-32 rounded-full bg-white/10 pointer-events-none"></div>
                <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <span class="inline-block px-2.5 py-0.5 rounded-full bg-white/20 text-white text-[10px] font-extrabold uppercase tracking-wider">
                            Paket Topup Kuota Resmi
                        </span>
                        <div class="text-2xl font-black mt-1">
                            +{{ $topupQuota }} Slot Listing Properti
                        </div>
                        <p class="text-xs text-pink-100 mt-0.5">
                            Slot listing aktif permanen &amp; tidak memiliki batas kadaluarsa.
                        </p>
                    </div>
                    <div class="text-left sm:text-right shrink-0">
                        <div class="text-2xl font-black text-white">
                            Rp {{ number_format($topupPrice, 0, ',', '.') }}
                        </div>
                        <span class="text-[10px] bg-white text-pink-700 font-extrabold px-2 py-0.5 rounded-full uppercase tracking-wider">
                            Sekali Bayar
                        </span>
                    </div>
                </div>
            </div>

            <!-- Payment Methods: QRIS + BCA + Bank Lain -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-2xs space-y-4">
                <h3 class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-wallet text-pink-600"></i> Rekening Resmi &amp; QRIS Pembayaran
                </h3>

                <!-- QRIS & Merchant -->
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 flex flex-col sm:flex-row items-center gap-4">
                    @if($qrisImage)
                        <div class="bg-white p-2 rounded-xl border border-slate-200 shadow-2xs shrink-0 text-center">
                            <img src="{{ $qrisImage }}" alt="QRIS Resmi" class="w-36 h-36 object-contain mx-auto">
                            <span class="text-[9px] font-bold text-slate-400 mt-1 block">Scan Bebas Biaya Admin</span>
                        </div>
                    @endif
                    <div class="space-y-1.5 text-xs w-full text-center sm:text-left">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-pink-600">QRIS Instan Semua Bank &amp; E-Wallet</div>
                        <div class="font-extrabold text-slate-900 text-sm">{{ $merchantName }}</div>
                        <p class="text-[11px] text-slate-500 leading-relaxed">
                            Buka aplikasi BCA Mobile, Livin, BRImo, BNI, GoPay, OVO, Dana, atau ShopeePay lalu scan kode QR di samping untuk transfer instan.
                        </p>
                    </div>
                </div>

                <!-- Bank Accounts Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <!-- Rekening BCA Utama -->
                    @if($bcaAccount && !empty($bcaAccount['number']))
                        <div class="p-3.5 rounded-xl border border-blue-200 bg-blue-50/50 space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-extrabold text-blue-700 uppercase tracking-wider flex items-center gap-1">
                                    <i class="fa-solid fa-building-columns"></i> {{ $bcaAccount['bank_name'] }} (Utama)
                                </span>
                                <span class="text-[9px] bg-blue-100 text-blue-800 font-bold px-1.5 py-0.5 rounded">Prioritas</span>
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-mono text-base font-black text-slate-900 tracking-wide">{{ $bcaAccount['number'] }}</span>
                                <button type="button"
                                        @click="navigator.clipboard.writeText('{{ $bcaAccount['number'] }}'); copiedBca = true; setTimeout(() => copiedBca = false, 2000)"
                                        class="px-2.5 py-1 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-[11px] font-bold transition-all flex items-center gap-1">
                                    <i class="fa-solid" :class="copiedBca ? 'fa-check' : 'fa-copy'"></i>
                                    <span x-text="copiedBca ? 'Tersalin' : 'Salin'"></span>
                                </button>
                            </div>
                            <div class="text-[11px] text-slate-500">
                                a.n <strong class="text-slate-800">{{ $bcaAccount['name'] }}</strong>
                            </div>
                        </div>
                    @endif

                    <!-- Rekening Bank Lain -->
                    @if($otherBankAccount && !empty($otherBankAccount['number']))
                        <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-extrabold text-slate-700 uppercase tracking-wider flex items-center gap-1">
                                    <i class="fa-solid fa-building-columns"></i> {{ $otherBankAccount['bank_name'] }}
                                </span>
                                <span class="text-[9px] bg-slate-200 text-slate-700 font-bold px-1.5 py-0.5 rounded">Alternatif</span>
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-mono text-base font-black text-slate-900 tracking-wide">{{ $otherBankAccount['number'] }}</span>
                                <button type="button"
                                        @click="navigator.clipboard.writeText('{{ $otherBankAccount['number'] }}'); copiedOther = true; setTimeout(() => copiedOther = false, 2000)"
                                        class="px-2.5 py-1 rounded-lg bg-slate-700 hover:bg-slate-800 text-white text-[11px] font-bold transition-all flex items-center gap-1">
                                    <i class="fa-solid" :class="copiedOther ? 'fa-check' : 'fa-copy'"></i>
                                    <span x-text="copiedOther ? 'Tersalin' : 'Salin'"></span>
                                </button>
                            </div>
                            <div class="text-[11px] text-slate-500">
                                a.n <strong class="text-slate-800">{{ $otherBankAccount['name'] }}</strong>
                            </div>
                        </div>
                    @endif
                </div>

                @if($paymentInstructions)
                    <div class="p-3 rounded-xl bg-amber-50/70 border border-amber-200 text-amber-900 text-xs space-y-1">
                        <div class="font-bold flex items-center gap-1 text-amber-800">
                            <i class="fa-solid fa-circle-info"></i> Petunjuk Pembayaran:
                        </div>
                        <div class="text-[11px] text-amber-800 leading-relaxed">
                            {!! nl2br(e($paymentInstructions)) !!}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Column: Form Upload Bukti Transfer -->
        <div class="lg:col-span-5">
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-2xs space-y-4 sticky top-6">
                <div>
                    <h3 class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                        <i class="fa-solid fa-upload text-pink-600"></i> Konfirmasi Pembayaran Topup
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">
                        Unggah struk atau screenshot bukti transfer Anda di bawah ini untuk diverifikasi admin.
                    </p>
                </div>

                <form action="{{ route('admin.agent.topup-quota') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Bukti Struk Transfer / Scan QRIS <span class="text-rose-500">*</span>
                        </label>
                        <input type="file"
                               name="payment_proof"
                               accept="image/jpeg,image/png,image/jpg,image/webp"
                               required
                               class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-pink-100 file:text-pink-700 hover:file:bg-pink-200 cursor-pointer border border-slate-200 rounded-xl bg-slate-50 p-1.5 focus:outline-none">
                        <span class="text-[10px] text-slate-400 mt-1 block">Format gambar: JPG, PNG, WEBP (Maksimal 5MB)</span>
                        @error('payment_proof')
                            <span class="text-[10px] text-rose-500 font-bold mt-0.5 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Catatan Tambahan (Opsional)
                        </label>
                        <input type="text"
                               name="notes"
                               value="{{ old('notes') }}"
                               placeholder="Cth: Transfer via BCA a.n {{ $agent->name }}"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-pink-500 focus:bg-white focus:outline-none">
                        @error('notes')
                            <span class="text-[10px] text-rose-500 font-bold mt-0.5 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="pt-2">
                        <button type="submit"
                                class="w-full py-3 rounded-xl bg-gradient-to-r from-pink-600 to-rose-600 hover:from-pink-700 hover:to-rose-700 text-white font-extrabold text-xs sm:text-sm shadow-md transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-paper-plane"></i> Kirim Permohonan Topup
                        </button>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 text-[11px] text-slate-500 space-y-1">
                        <div class="font-bold text-slate-700 flex items-center gap-1">
                            <i class="fa-solid fa-clock-rotate-left text-pink-500"></i> Proses Verifikasi Cepat
                        </div>
                        <p class="leading-relaxed">
                            Setelah mengirim bukti transfer, admin akan memverifikasi dalam waktu singkat. Kuota listing akan langsung otomatis bertambah ke akun Anda.
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bottom Section: Transaction History -->
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-2xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                <i class="fa-solid fa-receipt text-pink-600"></i> Riwayat Transaksi Topup Kuota Saya
            </h3>
            <span class="text-xs text-slate-400">Total: {{ $transactions->total() }} Permintaan</span>
        </div>

        @if($transactions->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="text-[10px] text-slate-400 uppercase tracking-wider bg-slate-50 border-y border-slate-100">
                        <tr>
                            <th class="px-4 py-2.5">Tanggal</th>
                            <th class="px-4 py-2.5">Paket Topup</th>
                            <th class="px-4 py-2.5">Nominal</th>
                            <th class="px-4 py-2.5">Bukti Struk</th>
                            <th class="px-4 py-2.5">Status</th>
                            <th class="px-4 py-2.5">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @foreach($transactions as $trx)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap text-slate-500">
                                    {{ $trx->created_at->translatedFormat('d M Y, H:i') }}
                                </td>
                                <td class="px-4 py-3 font-extrabold text-slate-900">
                                    +{{ $trx->quota_added }} Slot Listing
                                </td>
                                <td class="px-4 py-3 font-bold text-slate-900">
                                    Rp {{ number_format($trx->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3">
                                    @if($trx->payment_proof)
                                        <a href="{{ asset('storage/' . $trx->payment_proof) }}" target="_blank" class="inline-flex items-center gap-1 text-pink-600 hover:text-pink-700 font-bold text-xs underline">
                                            <i class="fa-solid fa-file-image"></i> Lihat Struk
                                        </a>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($trx->status === 'approved')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            <i class="fa-solid fa-circle-check text-[9px]"></i> Disetujui
                                        </span>
                                    @elseif($trx->status === 'rejected')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-800 border border-rose-200">
                                            <i class="fa-solid fa-circle-xmark text-[9px]"></i> Ditolak
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 border border-amber-200">
                                            <i class="fa-solid fa-clock text-[9px]"></i> Menunggu Verifikasi
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-slate-500 text-[11px] max-w-xs truncate">
                                    {{ $trx->notes ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pt-3">
                {{ $transactions->links() }}
            </div>
        @else
            <div class="py-8 text-center text-slate-400 space-y-1">
                <i class="fa-solid fa-receipt text-2xl text-slate-300"></i>
                <p class="text-xs font-semibold">Belum ada riwayat permohonan topup kuota.</p>
            </div>
        @endif
    </div>
</div>
@endsection

