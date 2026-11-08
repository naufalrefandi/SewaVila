@extends('layouts.app')

@section('title', 'Titip Jual & Sewa Properti Anda - ' . \App\Models\Setting::getSiteName() . '')
@section('meta_description', 'Pasang iklan dan titipkan penjualan atau penyewaan rumah, apartemen, ruko, dan tanah Anda ke jaringan 1.500+ agen profesional ' . \App\Models\Setting::getSiteName() . '.')

@section('content')
<div class="bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 text-white py-8 sm:py-10">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-500/20 text-rose-300 text-xs font-bold uppercase tracking-wider mb-2.5">
            <i class="fa-solid fa-handshake"></i> Layanan Khusus Pemilik Properti
        </span>
        <h1 class="text-2xl sm:text-4xl font-black text-white tracking-tight">
            Titip Jual / Sewa Properti Anda
        </h1>
        <p class="mt-2 text-xs sm:text-sm text-slate-300 max-w-2xl mx-auto leading-relaxed">
            Daftarkan properti Anda dengan mudah dan cepat. Tim kurasi dan agen profesional {{ \App\Models\Setting::getSiteName() }} siap memasarkan listing Anda ke ratusan ribu calon pembeli potensial.
        </p>

        <!-- 3 Key Guarantees -->
        <div class="mt-5 sm:mt-6 grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 max-w-3xl mx-auto text-left">
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-3.5 border border-white/10">
                <i class="fa-solid fa-bolt text-amber-400 text-base mb-1.5"></i>
                <div class="font-bold text-xs sm:text-sm text-white">Kurasi Cepat &amp; Terverifikasi</div>
                <div class="text-[11px] text-slate-300 mt-0.5">Listing diprioritaskan dan diverifikasi agen berlisensi resmi.</div>
            </div>
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-3.5 border border-white/10">
                <i class="fa-solid fa-bullhorn text-rose-400 text-base mb-1.5"></i>
                <div class="font-bold text-xs sm:text-sm text-white">Promosi Multi-Kanal</div>
                <div class="text-[11px] text-slate-300 mt-0.5">Dipromosikan di web, medsos, dan database pembeli.</div>
            </div>
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-3.5 border border-white/10">
                <i class="fa-solid fa-shield-halved text-emerald-400 text-base mb-1.5"></i>
                <div class="font-bold text-xs sm:text-sm text-white">Agen Berlisensi Resmi</div>
                <div class="text-[11px] text-slate-300 mt-0.5">Didampingi negosiasi hingga akad notaris PPAT.</div>
            </div>
        </div>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 -mt-4 mb-8 sm:mb-10 relative z-10">
    @if(isset($submission) && $submission)
        <!-- STEP 2: KODE PEMBAYARAN, RINCIAN PEMBAYARAN & UPLOAD BUKTI TRANSFER TITIP PROPERTI -->
        <div class="bg-white rounded-3xl border border-slate-200 p-5 sm:p-8 shadow-xl space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-50 text-rose-700 text-[11px] font-extrabold uppercase tracking-wider mb-2">
                        <i class="fa-solid fa-file-invoice-dollar"></i> Tahap Pembayaran Titip Properti
                    </span>
                    <h2 class="text-xl font-black text-slate-900">Tagihan &amp; Kode Pembayaran Titip Properti</h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Rincian pembayaran ini juga telah dikirimkan ke email <strong class="text-slate-800">{{ $submission->owner_email }}</strong>.
                    </p>
                </div>
                <div class="shrink-0">
                    @if($submission->payment_status === 'paid' || $submission->status === 'listed')
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-extrabold">
                            <i class="fa-solid fa-circle-check"></i> Pembayaran Terverifikasi
                        </span>
                    @elseif($submission->payment_status === 'pending')
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-amber-100 text-amber-800 text-xs font-extrabold">
                            <i class="fa-solid fa-clock"></i> Menunggu Verifikasi Admin
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-rose-100 text-rose-800 text-xs font-extrabold">
                            <i class="fa-solid fa-circle-exclamation"></i> Menunggu Pembayaran
                        </span>
                    @endif
                </div>
            </div>

            <!-- Box Kode Pembayaran & Ringkasan Properti -->
            <div class="bg-gradient-to-br from-rose-50 via-amber-50/50 to-slate-50 rounded-2xl p-5 border-2 border-dashed border-rose-300 flex flex-col sm:flex-row sm:items-center justify-between gap-4" x-data="{ copiedCode: false }">
                <div class="space-y-1">
                    <div class="text-[11px] font-extrabold uppercase tracking-wider text-rose-700">Kode Pembayaran Resmi Anda</div>
                    <div class="flex items-center gap-2.5">
                        <span class="font-mono text-2xl sm:text-3xl font-black text-slate-900 tracking-wider">{{ $submission->payment_code }}</span>
                        <button type="button"
                                @click="navigator.clipboard.writeText('{{ $submission->payment_code }}'); copiedCode = true; setTimeout(() => copiedCode = false, 1500)"
                                class="px-2.5 py-1 rounded-lg bg-white border border-rose-200 hover:bg-rose-50 text-[11px] font-bold text-rose-700 shadow-2xs transition-all flex items-center gap-1 cursor-pointer">
                            <i class="fa-solid" :class="copiedCode ? 'fa-check text-emerald-600' : 'fa-copy'"></i>
                            <span x-text="copiedCode ? 'Tersalin!' : 'Salin Kode'"></span>
                        </button>
                    </div>
                    <div class="text-xs text-slate-600 pt-1">
                        Properti: <strong>{{ $submission->property_title }}</strong> ({{ $submission->city }}) &bull; Pemilik: <strong>{{ $submission->owner_name }}</strong>
                    </div>
                </div>
                <div class="sm:text-right bg-white px-4 py-3 rounded-xl border border-rose-200/80 shadow-2xs shrink-0">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Nominal Biaya Kurasi</div>
                    <div class="text-xl font-black text-rose-600 mt-0.5">
                        Rp {{ number_format($submissionFee > 0 ? $submissionFee : 50000, 0, ',', '.') }}
                    </div>
                </div>
            </div>

            <!-- Metode Pembayaran Sesuai Setting Admin -->
            <div class="bg-gradient-to-br from-rose-50/70 via-white to-amber-50/60 rounded-3xl p-5 sm:p-6 border border-rose-200/80 shadow-xs space-y-4">
                <div class="flex items-center gap-2 border-b border-rose-100 pb-3">
                    <span class="w-6 h-6 rounded-full bg-rose-600 text-white flex items-center justify-center text-xs font-bold">
                        <i class="fa-solid fa-wallet text-[10px]"></i>
                    </span>
                    <h3 class="font-extrabold text-slate-900 text-sm">Metode Pembayaran (QRIS &amp; Transfer Bank Resmi)</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                    <!-- QRIS Barcode Box -->
                    <div class="md:col-span-5 flex flex-col items-center p-4 bg-white rounded-2xl border-2 border-dashed border-rose-300 shadow-sm text-center">
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-rose-600 mb-2">
                            <i class="fa-solid fa-qrcode mr-1"></i> QRIS Resmi
                        </span>
                        <div class="w-44 h-44 bg-white p-2 rounded-xl shadow-inner border border-slate-200 flex items-center justify-center">
                            <img src="{{ $qrisImage }}" alt="QRIS Pembayaran" class="w-full h-full object-contain">
                        </div>
                        <span class="text-xs font-extrabold text-slate-900 mt-2.5">{{ $merchantName }}</span>
                        <span class="text-[10px] text-slate-400">Scan via m-Banking / E-Wallet</span>
                    </div>

                    <!-- Bank Accounts -->
                    <div class="md:col-span-7 space-y-3">
                        <div class="p-3.5 bg-white rounded-2xl border border-slate-200 text-xs shadow-xs" x-data="{ copiedBca: false }">
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center gap-1.5 font-bold text-slate-900">
                                    <span class="px-2 py-0.5 rounded-md bg-blue-100 text-blue-800 text-[10px] font-black">BCA</span>
                                    <span>{{ $bcaAccount['bank_name'] ?? 'BCA (Bank Central Asia)' }}</span>
                                </span>
                                <button type="button"
                                        @click="navigator.clipboard.writeText('{{ $bcaAccount['account_number'] ?? $bankAccNum }}'); copiedBca = true; setTimeout(() => copiedBca = false, 1500)"
                                        class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-[10px] font-bold text-slate-700 transition-all flex items-center gap-1 cursor-pointer">
                                    <i class="fa-solid" :class="copiedBca ? 'fa-check text-emerald-600' : 'fa-copy'"></i>
                                    <span x-text="copiedBca ? 'Tersalin!' : 'Salin No. Rek'"></span>
                                </button>
                            </div>
                            <div class="mt-1 font-mono text-base font-extrabold text-slate-900 tracking-wider">
                                {{ $bcaAccount['account_number'] ?? $bankAccNum }}
                            </div>
                            <div class="text-[11px] text-slate-500 mt-0.5">
                                a/n {{ $bcaAccount['account_name'] ?? $bankAccName }}
                            </div>
                        </div>

                        @if(!empty($otherBankAccount))
                            <div class="p-3.5 bg-white rounded-2xl border border-slate-200 text-xs shadow-xs" x-data="{ copiedOther: false }">
                                <div class="flex items-center justify-between">
                                    <span class="inline-flex items-center gap-1.5 font-bold text-slate-900">
                                        <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 text-[10px] font-black">BANK</span>
                                        <span>{{ $otherBankAccount['bank_name'] }}</span>
                                    </span>
                                    <button type="button"
                                            @click="navigator.clipboard.writeText('{{ $otherBankAccount['account_number'] }}'); copiedOther = true; setTimeout(() => copiedOther = false, 1500)"
                                            class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-[10px] font-bold text-slate-700 transition-all flex items-center gap-1 cursor-pointer">
                                        <i class="fa-solid" :class="copiedOther ? 'fa-check text-emerald-600' : 'fa-copy'"></i>
                                        <span x-text="copiedOther ? 'Tersalin!' : 'Salin No. Rek'"></span>
                                    </button>
                                </div>
                                <div class="mt-1 font-mono text-base font-extrabold text-slate-900 tracking-wider">
                                    {{ $otherBankAccount['account_number'] }}
                                </div>
                                <div class="text-[11px] text-slate-500 mt-0.5">
                                    a/n {{ $otherBankAccount['account_name'] }}
                                </div>
                            </div>
                        @endif

                        @if($instructions)
                            <div class="text-[11px] text-slate-600 bg-white/80 rounded-xl p-3 border border-rose-200/60">
                                <i class="fa-solid fa-circle-info text-amber-500 mr-1"></i>
                                {{ $instructions }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Form Upload Bukti Transfer -->
            <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200 space-y-4" x-data="{ proofPreview: null }">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Konfirmasi &amp; Unggah Bukti Pembayaran</h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Setelah bukti transfer Anda diverifikasi oleh Admin, notifikasi konfirmasi penayangan akan dikirimkan ke <strong>{{ $submission->owner_email }}</strong>.
                    </p>
                </div>

                @if($submission->payment_proof)
                    <div class="flex items-center gap-3 p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-xs">
                        <a href="{{ $submission->payment_proof_url }}" target="_blank" class="shrink-0">
                            <img src="{{ $submission->payment_proof_url }}" alt="Bukti Transfer" class="w-14 h-14 object-cover rounded-lg border border-emerald-300">
                        </a>
                        <div>
                            <div class="font-extrabold text-emerald-800">
                                <i class="fa-solid fa-circle-check mr-1"></i> Bukti pembayaran telah diunggah
                            </div>
                            <p class="text-[11px] text-emerald-700 mt-0.5">
                                Status saat ini: <strong>{{ $submission->payment_status_label }}</strong>. Anda dapat mengunggah ulang di bawah ini jika ingin mengganti bukti transfer.
                            </p>
                        </div>
                    </div>
                @endif

                <form action="{{ route('titip-properti.payment', $submission->payment_code) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block font-bold text-slate-800 text-xs mb-1.5">
                            Pilih Foto / Screenshot Struk Bukti Transfer <span class="text-rose-500">*</span>
                        </label>
                        <input type="file"
                               name="payment_proof"
                               required
                               accept="image/*"
                               @change="proofPreview = URL.createObjectURL($event.target.files[0])"
                               class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-rose-50 file:text-rose-700 hover:file:bg-rose-100 cursor-pointer bg-white p-2 rounded-xl border border-slate-300 focus:outline-none">
                        @error('payment_proof')
                            <p class="text-[11px] text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                        @enderror

                        <template x-if="proofPreview">
                            <div class="mt-2.5 flex items-center gap-3 p-2.5 bg-white rounded-xl border border-slate-200">
                                <img :src="proofPreview" class="w-12 h-12 object-cover rounded-lg border border-slate-200">
                                <div class="text-xs">
                                    <div class="font-bold text-emerald-700"><i class="fa-solid fa-circle-check"></i> Foto struk siap dikirim</div>
                                    <div class="text-[10px] text-slate-400">Pastikan nominal &amp; rekening tujuan terlihat jelas</div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2">
                        <a href="{{ route('titip-properti') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800">
                            &larr; Kembali ke Formulir Titip Properti
                        </a>
                        <button type="submit" class="px-6 py-3 rounded-2xl bg-gradient-to-r from-rose-600 to-rose-700 hover:from-rose-700 hover:to-rose-800 text-white font-extrabold text-xs shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <span>{{ $submission->payment_proof ? 'Perbarui Bukti Pembayaran' : 'Kirim Bukti Pembayaran' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @else
        <!-- STEP 1: FORMULIR DATA TITIP PROPERTI (SUBMIT TERLEBIH DAHULU) -->
        <div class="bg-white rounded-3xl border border-slate-200 p-5 sm:p-7 shadow-xl">
            <div class="border-b border-slate-100 pb-4 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h2 class="text-lg font-extrabold text-slate-900">Formulir Pendaftaran Titip Properti</h2>
                    <p class="text-xs text-slate-500 mt-1">Isi dan kirim data properti Anda terlebih dahulu untuk mendapatkan <strong>Kode Pembayaran</strong> di layar &amp; melalui email.</p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-50 text-rose-700 text-[11px] font-bold self-start sm:self-auto">
                    <i class="fa-solid fa-circle-1"></i> Langkah 1 dari 2
                </span>
            </div>

            <form action="{{ route('titip-properti.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- Section 1: Data Pemilik -->
                <div>
                    <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-3">
                        <span class="w-6 h-6 rounded-full bg-rose-600 text-white flex items-center justify-center text-xs">1</span>
                        Data Pemilik / Penanggung Jawab
                    </h2>
                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap *</label>
                            <input type="text"
                                   name="owner_name"
                                   required
                                   placeholder="Contoh: Bpk. Haryanto"
                                   value="{{ old('owner_name') }}"
                                   class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:bg-white focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nomor WhatsApp Aktif *</label>
                            <input type="text"
                                   name="owner_phone"
                                   required
                                   placeholder="Contoh: 08123456789"
                                   value="{{ old('owner_phone') }}"
                                   class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:bg-white focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Email *</label>
                            <input type="email"
                                   name="owner_email"
                                   required
                                   placeholder="email@anda.com"
                                   value="{{ old('owner_email') }}"
                                   class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:bg-white focus:outline-none">
                            <p class="text-[10px] text-slate-400 mt-1">Kode &amp; instruksi pembayaran akan dikirim ke email ini.</p>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Data Properti -->
                <div>
                    <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-3">
                        <span class="w-6 h-6 rounded-full bg-rose-600 text-white flex items-center justify-center text-xs">2</span>
                        Informasi &amp; Lokasi Properti
                    </h2>
                    <div class="mt-4 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Tipe Transaksi *</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <label class="cursor-pointer">
                                        <input type="radio" name="listing_type" value="dijual" {{ old('listing_type', 'dijual') === 'dijual' ? 'checked' : '' }} class="sr-only peer">
                                        <div class="p-2.5 text-center text-xs font-bold rounded-xl border border-slate-200 peer-checked:bg-rose-600 peer-checked:text-white peer-checked:border-rose-600 hover:bg-slate-50 transition-all">
                                            <i class="fa-solid fa-house mr-1"></i> Dijual
                                        </div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="listing_type" value="disewa" {{ old('listing_type') === 'disewa' ? 'checked' : '' }} class="sr-only peer">
                                        <div class="p-2.5 text-center text-xs font-bold rounded-xl border border-slate-200 peer-checked:bg-amber-500 peer-checked:text-slate-950 peer-checked:border-amber-500 hover:bg-slate-50 transition-all">
                                            <i class="fa-solid fa-key mr-1"></i> Disewa
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Kategori Properti *</label>
                                <select name="property_type" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:bg-white focus:outline-none">
                                    <option value="rumah" {{ old('property_type') === 'rumah' ? 'selected' : '' }}>Rumah</option>
                                    <option value="apartemen" {{ old('property_type') === 'apartemen' ? 'selected' : '' }}>Apartemen</option>
                                    <option value="tanah" {{ old('property_type') === 'tanah' ? 'selected' : '' }}>Tanah</option>
                                    <option value="gudang" {{ old('property_type') === 'gudang' ? 'selected' : '' }}>Gudang</option>
                                    <option value="ruko" {{ old('property_type') === 'ruko' ? 'selected' : '' }}>Ruko</option>
                                    <option value="hotel" {{ old('property_type') === 'hotel' ? 'selected' : '' }}>Hotel</option>
                                    <option value="gedung" {{ old('property_type') === 'gedung' ? 'selected' : '' }}>Gedung</option>
                                    <option value="villa" {{ old('property_type') === 'villa' ? 'selected' : '' }}>Villa</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 mb-1">Judul / Nama Properti *</label>
                                <input type="text"
                                       name="property_title"
                                       required
                                       placeholder="Contoh: Rumah 2 Lantai Minimalis Modern di Cluster Greenwich BSD"
                                       value="{{ old('property_title') }}"
                                       class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:bg-white focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Harga yang Diharapkan (Rp) *</label>
                                <input type="number"
                                       name="expected_price"
                                       required
                                       placeholder="Cth: 2500000000"
                                       value="{{ old('expected_price') }}"
                                       class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:ring-2 focus:ring-rose-500 focus:bg-white focus:outline-none">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Kota / Kabupaten *</label>
                                <input type="text"
                                       name="city"
                                       required
                                       placeholder="Cth: Jakarta Selatan"
                                       value="{{ old('city') }}"
                                       class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:bg-white focus:outline-none">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Lengkap / Nama Kompleks *</label>
                                <input type="text"
                                       name="address"
                                       required
                                       placeholder="Jl. Raya Utama No. XX, Nama Cluster / Apartemen"
                                       value="{{ old('address') }}"
                                       class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:bg-white focus:outline-none">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Spesifikasi -->
                <div>
                    <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-3">
                        <span class="w-6 h-6 rounded-full bg-rose-600 text-white flex items-center justify-center text-xs">3</span>
                        Spesifikasi &amp; Detail Bangunan
                    </h2>
                    <div class="mt-4 grid grid-cols-2 sm:grid-cols-5 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Luas Tanah (m²)</label>
                            <input type="number"
                                   name="land_area"
                                   placeholder="LT m²"
                                   value="{{ old('land_area') }}"
                                   class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:bg-white focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Luas Bangunan (m²)</label>
                            <input type="number"
                                   name="building_area"
                                   placeholder="LB m²"
                                   value="{{ old('building_area') }}"
                                   class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:bg-white focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kamar Tidur</label>
                            <input type="number"
                                   name="bedrooms"
                                   placeholder="Jml KT"
                                   value="{{ old('bedrooms') }}"
                                   class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:bg-white focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kamar Mandi</label>
                            <input type="number"
                                   name="bathrooms"
                                   placeholder="Jml KM"
                                   value="{{ old('bathrooms') }}"
                                   class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:bg-white focus:outline-none">
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Sertifikat</label>
                            <select name="certificate" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:bg-white focus:outline-none">
                                <option value="SHM" {{ old('certificate') === 'SHM' ? 'selected' : '' }}>SHM</option>
                                <option value="HGB" {{ old('certificate') === 'HGB' ? 'selected' : '' }}>HGB</option>
                                <option value="Strata Title" {{ old('certificate') === 'Strata Title' ? 'selected' : '' }}>Strata Title</option>
                                <option value="Girik/PPJB" {{ old('certificate') === 'Girik/PPJB' ? 'selected' : '' }}>PPJB / Lainnya</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Tambahan / Keunggulan Properti</label>
                        <textarea name="notes"
                                  rows="3"
                                  placeholder="Ceritakan kelebihan properti Anda (contoh: baru selesai renovasi, dekat gerbang tol, kitchen set baru, bonus AC)..."
                                  class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:bg-white focus:outline-none">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <p class="text-xs text-slate-500">
                        <i class="fa-solid fa-envelope-circle-check text-rose-600 mr-1"></i>
                        Setelah klik kirim, <strong>Kode Pembayaran</strong> &amp; instruksi transfer akan muncul di layar dan dikirim ke email Anda.
                    </p>
                    <button type="submit"
                            class="w-full sm:w-auto px-8 py-3.5 rounded-2xl bg-gradient-to-r from-rose-600 to-rose-700 hover:from-rose-700 hover:to-rose-800 text-white font-extrabold text-xs sm:text-sm shadow-lg shadow-rose-600/30 transition-all flex items-center justify-center gap-2 shrink-0 cursor-pointer">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>Kirim Data Properti &amp; Lihat Kode Pembayaran</span>
                    </button>
                </div>
            </form>

            <!-- Cek Kode Pembayaran yang Sudah Ada -->
            <div class="mt-6 pt-5 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                <span class="text-slate-500 font-medium">Sudah mendaftarkan properti sebelumnya dan ingin melihat tagihan / unggah bukti bayar?</span>
                <form action="{{ route('titip-properti') }}" method="GET" class="flex items-center gap-2">
                    <input type="text"
                           name="code"
                           placeholder="Masukkan Kode (cth: PAY-TTP-...)"
                           required
                           class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-bold focus:outline-none focus:border-rose-500">
                    <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs cursor-pointer">
                        Cek Kode
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection

