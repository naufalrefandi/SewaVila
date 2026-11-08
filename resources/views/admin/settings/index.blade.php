@extends('layouts.admin')

@section('title', 'Pengaturan Biaya, Kuota Listing & QRIS - ' . \App\Models\Setting::getSiteName() . '')
@section('page_title', 'Pengaturan Biaya & QRIS Pembayaran')

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{ qrisPreview: '{{ $settings['qris_image_url'] }}', logoPreview: '{{ $settings['site_logo_url'] ?? '' }}', removeLogo: false }">

    <!-- Header Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-950 via-slate-900 to-rose-950 p-6 md:p-8 text-white shadow-xl">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-400/20 text-amber-300 text-xs font-black tracking-wide border border-amber-400/30 uppercase">
                    <i class="fa-solid fa-gears text-amber-400"></i>
                    <span>Sistem Pengaturan Finansial, Identitas &amp; Listing</span>
                </div>
                <h1 class="text-2xl md:text-3xl font-black tracking-tight text-white flex items-center gap-3">
                    Pengaturan Identitas Web, Biaya &amp; QRIS
                </h1>
                <p class="text-xs md:text-sm text-slate-300 max-w-2xl leading-relaxed">
                    Atur logo dan nama website agensi, nominal biaya titip jual properti, kuota listing awal agen, paket topup kuota, biaya featured listing, dan upload QRIS pembayaran resmi.
                </p>
            </div>

            <div>
                <a href="{{ route('admin.agent-transactions') }}" class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs border border-white/20 transition-all flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-amber-400"></i>
                    <span>Konfirmasi Pembayaran Agen</span>
                </a>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- Card 0: Identitas Website & Logo Agensi -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs space-y-5">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-pink-100 text-pink-600 flex items-center justify-center text-base">
                        <i class="fa-solid fa-paintbrush"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-sm">Identitas Website &amp; Logo Agensi</h3>
                        <p class="text-[11px] text-slate-400">Atur nama brand agensi, slogan, dan logo resmi yang tampil di navbar publik, admin portal, &amp; footer.</p>
                    </div>
                </div>
                <span class="text-[10px] font-bold text-pink-600 bg-pink-50 px-2.5 py-0.5 rounded-full border border-pink-200 uppercase tracking-wider">Branding</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
                <!-- Logo Upload & Live Preview (Cols 4) -->
                <div class="md:col-span-4 flex flex-col items-center p-4 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200 text-center">
                    <div class="w-full h-32 bg-white rounded-xl shadow-xs border border-slate-200 flex items-center justify-center p-3 overflow-hidden relative">
                        <template x-if="logoPreview && !removeLogo">
                            <img :src="logoPreview" alt="Logo Preview" class="max-h-full max-w-full object-contain">
                        </template>
                        <template x-if="!logoPreview || removeLogo">
                            <div class="text-center text-slate-400 space-y-1">
                                <i class="fa-solid fa-image text-3xl text-slate-300"></i>
                                <div class="text-[10px] font-semibold text-slate-400">Belum ada logo khusus</div>
                                <span class="text-[9px] text-slate-300 block">(Menggunakan teks nama web)</span>
                            </div>
                        </template>
                    </div>

                    <div class="mt-3 flex items-center gap-2 flex-wrap justify-center">
                        <label class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-pink-600 hover:bg-pink-700 text-white rounded-xl text-xs font-bold cursor-pointer transition-all shadow-xs">
                            <i class="fa-solid fa-cloud-arrow-up text-xs"></i>
                            <span>Upload Logo</span>
                            <input type="file"
                                   name="site_logo_file"
                                   accept="image/*"
                                   @change="if ($event.target.files.length) { logoPreview = URL.createObjectURL($event.target.files[0]); removeLogo = false; }"
                                   class="hidden">
                        </label>
                    </div>
                    @if(!empty($settings['site_logo_url']))
                        <label class="mt-2.5 inline-flex items-center gap-2 text-[11px] text-rose-600 font-bold cursor-pointer hover:underline">
                            <input type="checkbox" name="remove_logo" value="1" x-model="removeLogo" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                            <span>Hapus logo (gunakan teks)</span>
                        </label>
                    @endif
                    <p class="text-[10px] text-slate-400 mt-2 leading-relaxed">
                        Format: PNG, SVG, WEBP, atau JPG (Maks 3 MB). Disarankan latar belakang transparan.
                    </p>
                </div>

                <!-- Nama Website & Tagline (Cols 8) -->
                <div class="md:col-span-8 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Nama Website / Agensi Properti <span class="text-rose-500">*</span>
                        </label>
                        <input type="text"
                               name="site_name"
                               required
                               value="{{ old('site_name', $settings['site_name']) }}"
                               placeholder="Contoh: {{ \App\Models\Setting::getSiteName() }}"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm font-bold text-slate-900 focus:ring-2 focus:ring-pink-500 focus:bg-white focus:outline-none">
                        <span class="text-[10px] text-slate-400 mt-1 block">Nama ini akan tampil di navbar, footer, halaman login admin, dan judul halaman browser.</span>
                    </div>

                    <!-- Tagline & Hotline (Cols 2) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" x-data="{ tagline: '{{ addslashes($settings['site_tagline']) }}' }">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                                <span>Slogan / Tagline Agensi (Opsional)</span>
                                <span class="text-[10px] font-semibold" :class="tagline.length > 40 ? 'text-amber-600 font-bold' : 'text-slate-400'" x-text="tagline.length + '/45'"></span>
                            </label>
                            <input type="text"
                                   name="site_tagline"
                                   x-model="tagline"
                                   maxlength="45"
                                   value="{{ old('site_tagline', $settings['site_tagline']) }}"
                                   placeholder="Contoh: Portal Properti & Marketplace"
                                   class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:ring-2 focus:ring-pink-500 focus:bg-white focus:outline-none">
                            <span class="text-[10px] text-slate-400 mt-1 block">Tampil di bawah logo navbar. Dibatasi maks. 45 karakter agar pas &amp; cantik di layar HP / mobileview.</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                                <span>Hotline Konsultasi <span class="text-rose-500">*</span></span>
                                <span class="text-[10px] text-pink-600 font-bold"><i class="fa-solid fa-headset mr-0.5"></i> Call / WA</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-headset text-xs text-pink-500"></i>
                                </span>
                                <input type="text"
                                       name="site_hotline"
                                       required
                                       value="{{ old('site_hotline', $settings['site_hotline']) }}"
                                       placeholder="Contoh: 021-5098-8888 atau 0812-3456-7890"
                                       class="w-full pl-9 pr-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm font-bold text-slate-900 focus:ring-2 focus:ring-pink-500 focus:bg-white focus:outline-none">
                            </div>
                            <span class="text-[10px] text-slate-400 mt-1 block">Tampil di pita atas &amp; drawer menu mobile. Pengunjung dapat langsung klik untuk telepon atau chat WA.</span>
                        </div>
                    </div>

                    <!-- Watermark Logo Otomatis Box -->
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/90 space-y-3" x-data="{ wmEnabled: {{ $settings['watermark_enabled'] ? 'true' : 'false' }} }">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-pink-100 text-pink-600 flex items-center justify-center text-sm">
                                    <i class="fa-solid fa-stamp"></i>
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-slate-800 block">Watermark Otomatis pada Foto Properti</span>
                                    <span class="text-[10px] text-slate-400">Tempelkan logo agensi secara otomatis pada setiap foto yang diunggah agen/admin.</span>
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="watermark_enabled" value="1" x-model="wmEnabled" class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-pink-600"></div>
                            </label>
                        </div>

                        <div x-show="wmEnabled" x-cloak class="pt-2 border-t border-slate-200/80 grid grid-cols-1 sm:grid-cols-2 gap-3 items-center">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                    Posisi Watermark pada Foto
                                </label>
                                <select name="watermark_position" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:ring-2 focus:ring-pink-500 focus:outline-none">
                                    <option value="center" {{ in_array($settings['watermark_position'] ?? 'center', ['center', 'bottom-right', '']) ? 'selected' : '' }}>Tengah Gambar (Center Vertikal &amp; Horizontal - Rekomendasi)</option>
                                    <option value="bottom-left" {{ ($settings['watermark_position'] ?? '') === 'bottom-left' ? 'selected' : '' }}>Sudut Kiri Bawah</option>
                                </select>
                            </div>
                            <div class="text-[10px] text-slate-500 bg-white p-2.5 rounded-xl border border-slate-200/80">
                                <i class="fa-solid fa-circle-info text-pink-500 mr-1"></i>
                                Watermark otomatis menggunakan file logo resmi yang Anda upload di samping. Jika logo belum diupload, sistem akan menggunakan watermark teks <strong>{{ $settings['site_name'] }}</strong>.
                            </div>
                        </div>
                    </div>

                    <!-- Kontak Official Agent (Fallback Listing & Share) -->
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/90 space-y-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-pink-100 text-pink-600 flex items-center justify-center text-sm">
                                <i class="fa-solid fa-user-tie"></i>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-slate-800 block">Kontak Official Agent (Fallback Listing &amp; Share)</span>
                                <span class="text-[10px] text-slate-400">Kontak yang otomatis dipakai pada share broadcast &amp; halaman detail untuk properti yang tidak ditugaskan ke agen spesifik.</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2 border-t border-slate-200/80">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                    Nama Official Agent
                                </label>
                                <input type="text"
                                       name="official_agent_name"
                                       value="{{ old('official_agent_name', $settings['official_agent_name'] ?? '') }}"
                                       placeholder="Contoh: {{ $settings['site_name'] }} Official Agent"
                                       class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:ring-2 focus:ring-pink-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                    No. WhatsApp / HP
                                </label>
                                <input type="text"
                                       name="official_agent_phone"
                                       value="{{ old('official_agent_phone', $settings['official_agent_phone'] ?? '') }}"
                                       placeholder="Contoh: 0812-3456-7890"
                                       class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:ring-2 focus:ring-pink-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                    Kantor Cabang / Pusat
                                </label>
                                <input type="text"
                                       name="official_agent_office"
                                       value="{{ old('official_agent_office', $settings['official_agent_office'] ?? '') }}"
                                       placeholder="Contoh: Kantor Pusat"
                                       class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:ring-2 focus:ring-pink-500 focus:outline-none">
                            </div>
                        </div>
                    </div>

                    <div class="p-3 bg-pink-50/60 rounded-xl border border-pink-100 flex items-start gap-2.5 text-xs text-pink-900">
                        <i class="fa-solid fa-lightbulb text-pink-500 text-sm mt-0.5 shrink-0"></i>
                        <p class="text-[11px] leading-relaxed">
                            Jika logo diunggah, logo gambar akan otomatis menggantikan ikon default di <strong>Navbar Utama</strong>, <strong>Sidebar Admin Portal</strong>, <strong>Halaman Login</strong>, dan disematkan sebagai <strong>Watermark Foto</strong>.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left Column: QRIS & Rekening Bank (Col 5) -->
            <div class="lg:col-span-5 space-y-6">
                <!-- QRIS Box -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <h3 class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                            <i class="fa-solid fa-qrcode text-rose-600"></i>
                            Barcode QRIS Resmi
                        </h3>
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Scan Bayar</span>
                    </div>

                    <div class="flex flex-col items-center p-4 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200 text-center">
                        <div class="w-48 h-48 bg-white p-2 rounded-xl shadow-md border border-slate-200 flex items-center justify-center overflow-hidden">
                            <img :src="qrisPreview" alt="QRIS Preview" class="w-full h-full object-contain">
                        </div>

                        <label class="mt-3 inline-flex items-center gap-2 px-4 py-2 bg-slate-900 hover:bg-rose-600 text-white rounded-xl text-xs font-bold cursor-pointer transition-all shadow-sm">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <span>Upload Gambar QRIS</span>
                            <input type="file"
                                   name="qris_image_file"
                                   accept="image/*"
                                   @change="qrisPreview = URL.createObjectURL($event.target.files[0])"
                                   class="hidden">
                        </label>
                        <p class="text-[10px] text-slate-400 mt-1.5">Mendukung format JPG, PNG, WEBP (Maks 5 MB)</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Merchant QRIS *</label>
                        <input type="text"
                               name="qris_merchant_name"
                               required
                               value="{{ old('qris_merchant_name', $settings['qris_merchant_name']) }}"
                               placeholder="Contoh: Official QRIS"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:bg-white focus:outline-none">
                    </div>

                    <div class="pt-3 border-t border-slate-100 space-y-4">
                        <!-- Rekening BCA Utama -->
                        <div class="bg-blue-50/50 p-4 rounded-2xl border border-blue-200/80 space-y-3">
                            <div class="flex items-center justify-between pb-2 border-b border-blue-200/60">
                                <h4 class="text-xs font-black text-blue-900 flex items-center gap-1.5">
                                    <i class="fa-solid fa-building-columns text-blue-600"></i>
                                    Rekening Utama (BCA)
                                </h4>
                                <span class="px-2 py-0.5 rounded-md bg-blue-600 text-white text-[10px] font-black uppercase">Utama</span>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Nama Bank *</label>
                                <input type="text"
                                       name="bca_bank_name"
                                       required
                                       value="{{ old('bca_bank_name', $settings['bca_bank_name']) }}"
                                       placeholder="BCA (Bank Central Asia)"
                                       class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-blue-500">
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Nomor Rekening BCA *</label>
                                <input type="text"
                                       name="bca_account_number"
                                       required
                                       value="{{ old('bca_account_number', $settings['bca_account_number']) }}"
                                       placeholder="Contoh: 8290-9988-7766"
                                       class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-mono font-bold text-slate-900 focus:ring-2 focus:ring-blue-500">
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Atas Nama Rekening BCA *</label>
                                <input type="text"
                                       name="bca_account_name"
                                       required
                                       value="{{ old('bca_account_name', $settings['bca_account_name']) }}"
                                       placeholder="Contoh: PT PROPERTI INDONESIA"
                                       class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>

                        <!-- Rekening Bank Lain (Alternatif / Tambahan) -->
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 space-y-3">
                            <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                                <h4 class="text-xs font-black text-slate-800 flex items-center gap-1.5">
                                    <i class="fa-solid fa-money-check text-slate-600"></i>
                                    Rekening Bank Lain (Opsional)
                                </h4>
                                <span class="px-2 py-0.5 rounded-md bg-slate-200 text-slate-700 text-[10px] font-bold uppercase">Tambahan</span>
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Nama Bank Lain</label>
                                <input type="text"
                                       name="other_bank_name"
                                       value="{{ old('other_bank_name', $settings['other_bank_name']) }}"
                                       placeholder="Contoh: Bank Mandiri / BRI / BNI"
                                       class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-rose-500">
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Nomor Rekening Bank Lain</label>
                                <input type="text"
                                       name="other_bank_account_number"
                                       value="{{ old('other_bank_account_number', $settings['other_bank_account_number']) }}"
                                       placeholder="Kosongkan jika hanya menggunakan BCA"
                                       class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-mono font-medium focus:ring-2 focus:ring-rose-500">
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Atas Nama Rekening Bank Lain</label>
                                <input type="text"
                                       name="other_bank_account_name"
                                       value="{{ old('other_bank_account_name', $settings['other_bank_account_name']) }}"
                                       placeholder="Nama pemilik rekening bank lain"
                                       class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-rose-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-500 mb-1">Petunjuk Pembayaran (QRIS &amp; Transfer)</label>
                            <textarea name="payment_instructions"
                                      rows="3"
                                      placeholder="Instruksi transfer bagi pembayar..."
                                      class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-rose-500">{{ old('payment_instructions', $settings['payment_instructions']) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Pricing & Quota Controls (Col 7) -->
            <div class="lg:col-span-7 space-y-6">

                <!-- 1. Biaya Titip Jual (Owner Property Submission) -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xs font-bold">
                                <i class="fa-solid fa-handshake"></i>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-slate-900 text-sm">Biaya Titip Jual / Sewa (Pemilik)</h3>
                                <p class="text-[11px] text-slate-400">Pengaturan biaya pendaftaran properti dari pemilik di halaman publik</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                            Titip Jual
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Status Biaya Titip Jual *</label>
                            <div class="flex items-center gap-2 pt-1">
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="submission_fee_enabled" value="1" {{ $settings['submission_fee_enabled'] ? 'checked' : '' }} class="sr-only peer">
                                    <div class="p-2.5 text-center text-xs font-bold rounded-xl border border-slate-200 peer-checked:bg-rose-600 peer-checked:text-white peer-checked:border-rose-600 transition-all">
                                        <i class="fa-solid fa-lock mr-1"></i> Wajib Bayar
                                    </div>
                                </label>
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="submission_fee_enabled" value="0" {{ !$settings['submission_fee_enabled'] ? 'checked' : '' }} class="sr-only peer">
                                    <div class="p-2.5 text-center text-xs font-bold rounded-xl border border-slate-200 peer-checked:bg-slate-900 peer-checked:text-white peer-checked:border-slate-900 transition-all">
                                        <i class="fa-solid fa-gift mr-1"></i> Gratis (Free)
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nominal Biaya Titip Jual (Rp) *</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center font-bold text-xs text-slate-400">Rp</span>
                                <input type="number"
                                       name="submission_fee"
                                       required
                                       min="0"
                                       step="1000"
                                       value="{{ old('submission_fee', $settings['submission_fee']) }}"
                                       placeholder="50000"
                                       class="w-full pl-10 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-bold focus:ring-2 focus:ring-rose-500 focus:bg-white focus:outline-none">
                            </div>
                            <span class="text-[10px] text-slate-400 mt-1 block">Pemilik diwajibkan bayar via QRIS / Rekening BCA &amp; upload bukti transfer</span>
                        </div>
                    </div>
                </div>

                <!-- 2. Biaya Pendaftaran Calon Agen Baru (Daftar Jadi Agen) -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xs font-bold">
                                <i class="fa-solid fa-id-card-clip"></i>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-slate-900 text-sm">Biaya Pendaftaran Agen Baru</h3>
                                <p class="text-[11px] text-slate-400">Pengaturan biaya registrasi / starter pack calon agen di formulir /gabung-agen</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800">
                            Daftar Agen
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Status Biaya Pendaftaran Agen *</label>
                            <div class="flex items-center gap-2 pt-1">
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="agent_registration_fee_enabled" value="1" {{ $settings['agent_registration_fee_enabled'] ? 'checked' : '' }} class="sr-only peer">
                                    <div class="p-2.5 text-center text-xs font-bold rounded-xl border border-slate-200 peer-checked:bg-purple-600 peer-checked:text-white peer-checked:border-purple-600 transition-all">
                                        <i class="fa-solid fa-lock mr-1"></i> Wajib Bayar
                                    </div>
                                </label>
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="agent_registration_fee_enabled" value="0" {{ !$settings['agent_registration_fee_enabled'] ? 'checked' : '' }} class="sr-only peer">
                                    <div class="p-2.5 text-center text-xs font-bold rounded-xl border border-slate-200 peer-checked:bg-slate-900 peer-checked:text-white peer-checked:border-slate-900 transition-all">
                                        <i class="fa-solid fa-gift mr-1"></i> Gratis (Free)
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nominal Biaya Registrasi Agen (Rp)</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center font-bold text-xs text-slate-400">Rp</span>
                                <input type="number"
                                       name="agent_registration_fee"
                                       min="0"
                                       step="1000"
                                       value="{{ old('agent_registration_fee', $settings['agent_registration_fee']) }}"
                                       placeholder="100000"
                                       class="w-full pl-10 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-bold focus:ring-2 focus:ring-purple-500 focus:bg-white focus:outline-none">
                            </div>
                            <span class="text-[10px] text-slate-400 mt-1 block">Calon agen membayar via QRIS / BCA dan melampirkan foto bukti transfer</span>
                        </div>
                    </div>
                </div>

                <!-- 2. Kuota Listing Agen & Paket Topup -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">
                                <i class="fa-solid fa-user-tie"></i>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-slate-900 text-sm">Kuota Listing Agen &amp; Paket Topup</h3>
                                <p class="text-[11px] text-slate-400">Batas listing gratis awal saat agen daftar &amp; biaya paket kuota berikutnya</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">
                            Kuota Agen
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kuota Gratis Awal Agen *</label>
                            <div class="relative">
                                <input type="number"
                                       name="agent_free_quota"
                                       required
                                       min="0"
                                       value="{{ old('agent_free_quota', $settings['agent_free_quota']) }}"
                                       placeholder="3"
                                       class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-bold focus:ring-2 focus:ring-rose-500">
                            </div>
                            <span class="text-[10px] text-slate-400 mt-1 block">Diberikan saat pendaftaran agen disetujui (default 3 free)</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Harga Topup Kuota (Rp) *</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center font-bold text-xs text-slate-400">Rp</span>
                                <input type="number"
                                       name="agent_topup_price"
                                       required
                                       min="0"
                                       step="1000"
                                       value="{{ old('agent_topup_price', $settings['agent_topup_price']) }}"
                                       placeholder="100000"
                                       class="w-full pl-10 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-bold focus:ring-2 focus:ring-rose-500">
                            </div>
                            <span class="text-[10px] text-slate-400 mt-1 block">Biaya paket topup yang dibayar agen</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tambahan Kuota Listing *</label>
                            <div class="relative">
                                <input type="number"
                                       name="agent_topup_quota"
                                       required
                                       min="1"
                                       value="{{ old('agent_topup_quota', $settings['agent_topup_quota']) }}"
                                       placeholder="5"
                                       class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-bold focus:ring-2 focus:ring-rose-500">
                            </div>
                            <span class="text-[10px] text-slate-400 mt-1 block">Jml kuota listing didapat per paket</span>
                        </div>
                    </div>
                </div>

                <!-- 3. Biaya Featured Listing (Properti Unggulan) -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xs font-bold">
                                <i class="fa-solid fa-star"></i>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-slate-900 text-sm">Biaya Featured Listing (Properti Unggulan)</h3>
                                <p class="text-[11px] text-slate-400">Biaya yang dikenakan jika agen ingin memprioritaskan listing-nya di homepage &amp; pencarian</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                            Featured
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Biaya Upgrade Featured (Rp) *</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center font-bold text-xs text-slate-400">Rp</span>
                                <input type="number"
                                       name="featured_listing_price"
                                       required
                                       min="0"
                                       step="1000"
                                       value="{{ old('featured_listing_price', $settings['featured_listing_price']) }}"
                                       placeholder="50000"
                                       class="w-full pl-10 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-bold focus:ring-2 focus:ring-rose-500">
                            </div>
                            <span class="text-[10px] text-slate-400 mt-1 block">Dikenakan per properti yang diajukan</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Masa Aktif Featured (Hari) *</label>
                            <div class="relative">
                                <input type="number"
                                       name="featured_duration_days"
                                       required
                                       min="1"
                                       value="{{ old('featured_duration_days', $settings['featured_duration_days']) }}"
                                       placeholder="30"
                                       class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-bold focus:ring-2 focus:ring-rose-500">
                            </div>
                            <span class="text-[10px] text-slate-400 mt-1 block">Durasi tayang lencana featured (contoh: 30 hari)</span>
                        </div>
                    </div>
                </div>

                <!-- 4. Pengaturan Email & SMTP (Pengiriman Kode Pembayaran & Password Agen) -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs font-bold">
                                <i class="fa-solid fa-envelope-open-text"></i>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-slate-900 text-sm">Pengaturan Email &amp; SMTP Server</h3>
                                <p class="text-[11px] text-slate-400">Digunakan untuk mengirim Kode Pembayaran (Agen &amp; Titip Properti) serta Password Login Agen</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800">
                            SMTP / Email
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Driver Pengiriman</label>
                            <select name="mail_mailer" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:ring-2 focus:ring-rose-500">
                                <option value="smtp" {{ old('mail_mailer', $settings['mail_mailer']) === 'smtp' ? 'selected' : '' }}>SMTP (Gmail / Hosting / Mailtrap)</option>
                                <option value="log" {{ old('mail_mailer', $settings['mail_mailer']) === 'log' ? 'selected' : '' }}>Log Lokal (Testing storage/logs)</option>
                                <option value="sendmail" {{ old('mail_mailer', $settings['mail_mailer']) === 'sendmail' ? 'selected' : '' }}>Sendmail Server</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">SMTP Host</label>
                            <input type="text"
                                   name="mail_host"
                                   value="{{ old('mail_host', $settings['mail_host']) }}"
                                   placeholder="Cth: smtp.gmail.com"
                                   class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-medium focus:ring-2 focus:ring-rose-500">
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">SMTP Port</label>
                                <input type="number"
                                       name="mail_port"
                                       value="{{ old('mail_port', $settings['mail_port']) }}"
                                       placeholder="587"
                                       class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-bold focus:ring-2 focus:ring-rose-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Enkripsi</label>
                                <select name="mail_encryption" class="w-full px-2.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:ring-2 focus:ring-rose-500">
                                    <option value="tls" {{ old('mail_encryption', $settings['mail_encryption']) === 'tls' ? 'selected' : '' }}>TLS (587)</option>
                                    <option value="ssl" {{ old('mail_encryption', $settings['mail_encryption']) === 'ssl' ? 'selected' : '' }}>SSL (465)</option>
                                    <option value="null" {{ old('mail_encryption', $settings['mail_encryption']) === 'null' ? 'selected' : '' }}>Tanpa Enkripsi</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">SMTP Username / Email Akun</label>
                            <input type="text"
                                   name="mail_username"
                                   value="{{ old('mail_username', $settings['mail_username']) }}"
                                   placeholder="Cth: admin@domainanda.com"
                                   class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-medium focus:ring-2 focus:ring-rose-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">SMTP Password / App Password</label>
                            <input type="password"
                                   name="mail_password"
                                   placeholder="{{ !empty($settings['mail_password']) ? '•••••••• (Tersimpan — isi jika ingin mengganti)' : 'Masukkan Password SMTP / App Password Gmail' }}"
                                   class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-medium focus:ring-2 focus:ring-rose-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-100">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Email Pengirim (From Address)</label>
                            <input type="email"
                                   name="mail_from_address"
                                   value="{{ old('mail_from_address', $settings['mail_from_address']) }}"
                                   placeholder="Cth: noreply@yproperti.com"
                                   class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-rose-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Pengirim Email (From Name)</label>
                            <input type="text"
                                   name="mail_from_name"
                                   value="{{ old('mail_from_name', $settings['mail_from_name']) }}"
                                   placeholder="Cth: YProperti Official"
                                   class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-rose-500">
                        </div>
                    </div>
                </div>

                <!-- Submit Button Bar -->
                <div class="p-6 bg-slate-900 rounded-3xl text-white flex flex-col sm:flex-row items-center justify-between gap-4 shadow-xl">
                    <div class="text-xs text-slate-300 flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-400 text-base"></i>
                        <span>Pengaturan langsung berlaku di seluruh formulir website dan portal agen.</span>
                    </div>

                    <button type="submit"
                            class="w-full sm:w-auto px-8 py-3 bg-gradient-to-r from-rose-600 to-amber-500 hover:from-rose-500 hover:to-amber-400 text-white font-extrabold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Simpan Pengaturan</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

