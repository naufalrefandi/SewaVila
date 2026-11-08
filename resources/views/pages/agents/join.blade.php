@extends('layouts.app')

@section('title', 'Gabung Sebagai Agen Properti - ' . \App\Models\Setting::getSiteName() . '')
@section('meta_description', 'Raih karir sukses dan komisi tanpa batas sebagai konsultan properti profesional bersama jaringan agen berlisensi ' . \App\Models\Setting::getSiteName() . '.')

@section('content')
<div class="bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 text-white py-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-rose-500/20 text-rose-300 text-xs font-bold uppercase tracking-wider mb-4">
            <i class="fa-solid fa-id-card-clip"></i> Peluang Karir Property Consultant
        </span>
        <h1 class="text-3xl sm:text-5xl font-black text-white tracking-tight leading-tight">
            Bergabung Bersama Jaringan Agen <span class="text-transparent bg-clip-text bg-gradient-to-r from-rose-500 to-amber-400">{{ \App\Models\Setting::getSiteName() }}</span>
        </h1>
        <p class="mt-4 text-sm sm:text-base text-slate-300 max-w-2xl mx-auto leading-relaxed">
            Dapatkan komisi terbaik, akses ratusan proyek primary developer ternama, platform listing digital modern, serta pelatihan profesional bersertifikat AREBI.
        </p>

        <!-- 4 Key Advantages -->
        <div class="mt-10 grid grid-cols-2 sm:grid-cols-4 gap-4 text-left">
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/10">
                <i class="fa-solid fa-coins text-amber-400 text-xl mb-2"></i>
                <div class="font-extrabold text-sm text-white">Komisi Maksimal</div>
                <div class="text-[11px] text-slate-300 mt-1">Skema split komisi transparan &amp; reward trip luar negeri.</div>
            </div>
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/10">
                <i class="fa-solid fa-laptop-code text-rose-400 text-xl mb-2"></i>
                <div class="font-extrabold text-sm text-white">Platform Digital</div>
                <div class="text-[11px] text-slate-300 mt-1">Listing langsung terintegrasi dengan share WA, IG, TikTok.</div>
            </div>
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/10">
                <i class="fa-solid fa-graduation-cap text-blue-400 text-xl mb-2"></i>
                <div class="font-extrabold text-sm text-white">{{ \App\Models\Setting::getSiteName() }}Academy</div>
                <div class="text-[11px] text-slate-300 mt-1">Pelatihan negosiasi, legalitas, dan digital marketing gratis.</div>
            </div>
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/10">
                <i class="fa-solid fa-city text-emerald-400 text-xl mb-2"></i>
                <div class="font-extrabold text-sm text-white">Proyek Eksklusif</div>
                <div class="text-[11px] text-slate-300 mt-1">Akses resmi master apartemen &amp; primary launch developer.</div>
            </div>
        </div>
    </div>
</div>

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 mb-16 relative z-10">
    @if(isset($application) && $application)
        <!-- STEP 2: KODE PEMBAYARAN, RINCIAN PEMBAYARAN & UPLOAD BUKTI TRANSFER -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-10 shadow-xl space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-[11px] font-extrabold uppercase tracking-wider mb-2">
                        <i class="fa-solid fa-file-invoice-dollar"></i> Tahap Pembayaran Pendaftaran Agen
                    </span>
                    <h2 class="text-xl font-black text-slate-900">Tagihan &amp; Kode Pembayaran Agen</h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Rincian pembayaran ini juga telah dikirimkan ke email <strong class="text-slate-800">{{ $application->email }}</strong>.
                    </p>
                </div>
                <div class="shrink-0">
                    @if($application->payment_status === 'verified' || $application->status === 'approved')
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-extrabold">
                            <i class="fa-solid fa-circle-check"></i> Pembayaran Terverifikasi
                        </span>
                    @elseif($application->payment_status === 'pending')
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

            <!-- Box Kode Pembayaran & Ringkasan Pendaftar -->
            <div class="bg-gradient-to-br from-blue-50 via-indigo-50/60 to-slate-50 rounded-2xl p-5 border-2 border-dashed border-blue-300 flex flex-col sm:flex-row sm:items-center justify-between gap-4" x-data="{ copiedCode: false }">
                <div class="space-y-1">
                    <div class="text-[11px] font-extrabold uppercase tracking-wider text-blue-700">Kode Pembayaran Resmi Anda</div>
                    <div class="flex items-center gap-2.5">
                        <span class="font-mono text-2xl sm:text-3xl font-black text-slate-900 tracking-wider">{{ $application->payment_code }}</span>
                        <button type="button"
                                @click="navigator.clipboard.writeText('{{ $application->payment_code }}'); copiedCode = true; setTimeout(() => copiedCode = false, 1500)"
                                class="px-2.5 py-1 rounded-lg bg-white border border-blue-200 hover:bg-blue-50 text-[11px] font-bold text-blue-700 shadow-2xs transition-all flex items-center gap-1 cursor-pointer">
                            <i class="fa-solid" :class="copiedCode ? 'fa-check text-emerald-600' : 'fa-copy'"></i>
                            <span x-text="copiedCode ? 'Tersalin!' : 'Salin Kode'"></span>
                        </button>
                    </div>
                    <div class="text-xs text-slate-600 pt-1">
                        Pendaftar: <strong>{{ $application->name }}</strong> ({{ $application->phone }} &bull; {{ $application->city }})
                    </div>
                </div>
                <div class="sm:text-right bg-white px-4 py-3 rounded-xl border border-blue-200/80 shadow-2xs">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Nominal Pembayaran</div>
                    <div class="text-xl font-black text-rose-600 mt-0.5">
                        Rp {{ number_format($registrationFee > 0 ? $registrationFee : 100000, 0, ',', '.') }}
                    </div>
                </div>
            </div>

            <!-- Metode Pembayaran Sesuai Setting Admin -->
            <div class="bg-rose-50/60 rounded-3xl p-5 sm:p-6 border border-rose-200 space-y-4">
                <div class="flex items-center gap-2 border-b border-rose-200/60 pb-3">
                    <span class="w-6 h-6 rounded-full bg-rose-600 text-white flex items-center justify-center text-xs font-bold">
                        <i class="fa-solid fa-wallet text-[10px]"></i>
                    </span>
                    <h3 class="font-extrabold text-slate-900 text-sm">Metode Pembayaran (QRIS &amp; Transfer Bank Resmi)</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-5 items-center">
                    <!-- QRIS Box -->
                    <div class="md:col-span-5 flex flex-col items-center p-4 bg-white rounded-2xl border-2 border-dashed border-rose-300 shadow-sm text-center">
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-rose-600 mb-1.5">
                            <i class="fa-solid fa-qrcode mr-1"></i> QRIS Pembayaran Agen
                        </span>
                        <div class="w-40 h-40 bg-white p-2 rounded-xl shadow-inner border border-slate-200 flex items-center justify-center">
                            <img src="{{ $qrisImage }}" alt="QRIS Pembayaran Agen" class="w-full h-full object-contain">
                        </div>
                        <span class="text-xs font-extrabold text-slate-900 mt-2">{{ $merchantName }}</span>
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
                                        @click="navigator.clipboard.writeText('{{ $bcaAccount['account_number'] }}'); copiedBca = true; setTimeout(() => copiedBca = false, 1500)"
                                        class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-[10px] font-bold text-slate-700 transition-all flex items-center gap-1 cursor-pointer">
                                    <i class="fa-solid" :class="copiedBca ? 'fa-check text-emerald-600' : 'fa-copy'"></i>
                                    <span x-text="copiedBca ? 'Tersalin!' : 'Salin No. Rek'"></span>
                                </button>
                            </div>
                            <div class="mt-1 font-mono text-base font-extrabold text-slate-900 tracking-wider">
                                {{ $bcaAccount['account_number'] }}
                            </div>
                            <div class="text-[11px] text-slate-500 mt-0.5">
                                a/n {{ $bcaAccount['account_name'] }}
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
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900">Konfirmasi &amp; Unggah Bukti Pembayaran</h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Setelah pembayaran Anda diverifikasi dan disetujui oleh Admin, <strong>email dan password sementara</strong> akan langsung dikirimkan ke <strong>{{ $application->email }}</strong>.
                        </p>
                    </div>
                </div>

                @if($application->payment_proof)
                    <div class="flex items-center gap-3 p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-xs">
                        <a href="{{ $application->payment_proof_url }}" target="_blank" class="shrink-0">
                            <img src="{{ $application->payment_proof_url }}" alt="Bukti Transfer" class="w-14 h-14 object-cover rounded-lg border border-emerald-300">
                        </a>
                        <div>
                            <div class="font-extrabold text-emerald-800">
                                <i class="fa-solid fa-circle-check mr-1"></i> Bukti pembayaran telah diunggah
                            </div>
                            <p class="text-[11px] text-emerald-700 mt-0.5">
                                Status saat ini: <strong>{{ $application->payment_status_label }}</strong>. Anda dapat mengunggah ulang di bawah ini jika ingin mengganti bukti transfer.
                            </p>
                        </div>
                    </div>
                @endif

                <form action="{{ route('agents.join.payment', $application->payment_code) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
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
                        <a href="{{ route('agents.join') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800">
                            &larr; Kembali ke Formulir Pendaftaran
                        </a>
                        <button type="submit" class="px-6 py-3 rounded-2xl bg-gradient-to-r from-rose-600 to-rose-700 hover:from-rose-700 hover:to-rose-800 text-white font-extrabold text-xs shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <span>{{ $application->payment_proof ? 'Perbarui Bukti Pembayaran' : 'Kirim Bukti Pembayaran' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @else
        <!-- STEP 1: FORMULIR PENDAFTARAN BIODATA AGEN (SUBMIT TERLEBIH DAHULU) -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-10 shadow-xl">
            <div class="border-b border-slate-100 pb-4 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h2 class="text-lg font-extrabold text-slate-900">Formulir Pendaftaran Agen Properti Baru</h2>
                    <p class="text-xs text-slate-500 mt-1">Isi dan kirim data diri Anda terlebih dahulu untuk mendapatkan <strong>Kode Pembayaran</strong> di layar &amp; melalui email.</p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-[11px] font-bold self-start sm:self-auto">
                    <i class="fa-solid fa-circle-1"></i> Langkah 1 dari 2
                </span>
            </div>

            <form action="{{ route('agents.join.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5 text-xs">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nama Lengkap Sesuai KTP *</label>
                        <input type="text"
                               name="name"
                               required
                               placeholder="Nama Lengkap Anda"
                               value="{{ old('name') }}"
                               class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:ring-2 focus:ring-rose-500 focus:bg-white focus:outline-none">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nomor WhatsApp Aktif *</label>
                        <input type="text"
                               name="phone"
                               required
                               placeholder="Contoh: 08123456789"
                               value="{{ old('phone') }}"
                               class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:ring-2 focus:ring-rose-500 focus:bg-white focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Alamat Email *</label>
                        <input type="email"
                               name="email"
                               required
                               placeholder="email@anda.com"
                               value="{{ old('email') }}"
                               class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:ring-2 focus:ring-rose-500 focus:bg-white focus:outline-none">
                        <p class="text-[10px] text-slate-400 mt-1">Kode pembayaran &amp; akses login akan dikirimkan ke email ini.</p>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Kota Domisili *</label>
                        <select name="city" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:outline-none">
                            <option value="">Pilih Kota Domisili</option>
                            @foreach($cities as $c)
                                <option value="{{ $c->name }}" {{ old('city') === $c->name ? 'selected' : '' }}>{{ $c->name }} ({{ $c->province ? $c->province->name : '' }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Pengalaman di Bidang Properti *</label>
                        <select name="experience_level" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:outline-none">
                            <option value="baru" {{ old('experience_level') === 'baru' ? 'selected' : '' }}>Fresh / Baru Ingin Memulai</option>
                            <option value="1-3_tahun" {{ old('experience_level') === '1-3_tahun' ? 'selected' : '' }}>1 - 3 Tahun</option>
                            <option value="diatas_3_tahun" {{ old('experience_level') === 'diatas_3_tahun' ? 'selected' : '' }}>Lebih dari 3 Tahun (Berpengalaman)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nomor Lisensi AREBI (Jika Ada)</label>
                        <input type="text"
                               name="license_number"
                               placeholder="No. Sertifikasi AREBI"
                               value="{{ old('license_number') }}"
                               class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Pilihan Kantor Cabang Agensi</label>
                    <input type="text"
                           name="office_branch_preference"
                           placeholder="Contoh: Kantor Cabang Surabaya West, Grand CBD Jakarta, BSD Office"
                           value="{{ old('office_branch_preference') }}"
                           class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:outline-none">
                </div>

                <!-- Upload Foto Profil Formal (Attach File & Live Preview) -->
                <div x-data="{
                    photoPreview: null,
                    fileName: '',
                    fileSize: '',
                    handlePhotoChange(e) {
                        const file = e.target.files[0];
                        if (file) {
                            this.fileName = file.name;
                            this.fileSize = (file.size / 1024 / 1024).toFixed(2) + ' MB';
                            const reader = new FileReader();
                            reader.onload = (event) => {
                                this.photoPreview = event.target.result;
                            };
                            reader.readAsDataURL(file);
                        }
                    },
                    removePhoto() {
                        this.photoPreview = null;
                        this.fileName = '';
                        this.fileSize = '';
                        const inp = document.getElementById('photo_file_input');
                        if (inp) inp.value = '';
                    }
                }" class="bg-slate-50/80 rounded-2xl p-4 border border-slate-200 space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="block font-extrabold text-slate-800 text-xs">
                                <i class="fa-solid fa-camera text-rose-600 mr-1"></i> Lampirkan Pas Foto Profil Formal (Upload File)
                            </label>
                            <p class="text-[11px] text-slate-500 mt-0.5">Format: JPG, JPEG, PNG, WEBP (Maks. 5MB). Dianjurkan foto setengah badan mengenakan pakaian formal/rapi.</p>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center gap-4">
                        <div class="relative shrink-0">
                            <template x-if="photoPreview">
                                <img :src="photoPreview" alt="Preview Foto" class="w-20 h-20 rounded-2xl object-cover ring-2 ring-rose-500 shadow-md">
                            </template>
                            <template x-if="!photoPreview">
                                <div class="w-20 h-20 rounded-2xl bg-white border-2 border-dashed border-slate-300 flex flex-col items-center justify-center text-slate-400">
                                    <i class="fa-solid fa-user-tie text-2xl"></i>
                                    <span class="text-[9px] mt-1 font-bold">Pas Foto</span>
                                </div>
                            </template>
                            <button type="button"
                                    x-show="photoPreview"
                                    @click="removePhoto()"
                                    class="absolute -top-1.5 -right-1.5 w-6 h-6 rounded-full bg-red-600 text-white flex items-center justify-center text-xs shadow hover:bg-red-700"
                                    title="Hapus foto">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>

                        <div class="flex-1 w-full">
                            <label class="relative flex flex-col items-center justify-center p-4 border-2 border-dashed border-slate-300 hover:border-rose-400 bg-white rounded-xl cursor-pointer transition-colors group">
                                <input type="file"
                                       id="photo_file_input"
                                       name="photo_file"
                                       accept="image/jpeg,image/png,image/jpg,image/webp"
                                       @change="handlePhotoChange($event)"
                                       class="sr-only">
                                <div class="flex items-center gap-2 text-slate-600 group-hover:text-rose-600">
                                    <i class="fa-solid fa-paperclip text-base text-rose-500"></i>
                                    <span class="text-xs font-bold" x-text="fileName ? 'Ganti Foto: ' + fileName + ' (' + fileSize + ')' : 'Pilih / Lampirkan Foto dari Perangkat (Galeri/Kamera)'"></span>
                                </div>
                                <span class="text-[10px] text-slate-400 mt-0.5">Klik untuk browse atau seret file ke area ini</span>
                            </label>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-200/80">
                        <label class="block text-[11px] text-slate-500 font-medium mb-1">Atau cantumkan link URL foto (opsional):</label>
                        <input type="url"
                               name="photo"
                               placeholder="https://... (opsional jika sudah upload file di atas)"
                               value="{{ old('photo') }}"
                               class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Motivasi &amp; Sekilas Tentang Diri Anda</label>
                    <textarea name="motivation_bio"
                              rows="3"
                              placeholder="Ceritakan minat Anda dalam dunia properti atau pengalaman transaksi sebelumnya..."
                              class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:outline-none">{{ old('motivation_bio') }}</textarea>
                </div>

                <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <span class="text-[11px] text-slate-500">
                        <i class="fa-solid fa-envelope-circle-check text-blue-600 mr-1"></i>
                        Setelah klik kirim, <strong>Kode Pembayaran</strong> &amp; instruksi transfer akan muncul di layar dan dikirim ke email Anda.
                    </span>
                    <button type="submit" class="px-7 py-3 rounded-2xl bg-gradient-to-r from-rose-600 to-rose-700 hover:from-rose-700 hover:to-rose-800 text-white font-extrabold text-xs shadow-md transition-all flex items-center justify-center gap-2 shrink-0 cursor-pointer">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>Kirim Pendaftaran &amp; Lihat Kode Pembayaran</span>
                    </button>
                </div>
            </form>

            <!-- Cek Kode Pembayaran yang Sudah Ada -->
            <div class="mt-6 pt-5 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                <span class="text-slate-500 font-medium">Sudah mendaftar sebelumnya dan ingin melihat tagihan / unggah bukti bayar?</span>
                <form action="{{ route('agents.join') }}" method="GET" class="flex items-center gap-2">
                    <input type="text"
                           name="code"
                           placeholder="Masukkan Kode (cth: PAY-AGN-...)"
                           required
                           class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-bold focus:outline-none focus:border-blue-500">
                    <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs cursor-pointer">
                        Cek Kode
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection

