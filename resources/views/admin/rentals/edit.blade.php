@extends('layouts.admin')

@section('title', 'Edit Data Penyewa - ' . \App\Models\Setting::getSiteName() . '')
@section('page_title', 'Edit Data Penyewa & Masa Sewa')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-3xl p-6 border border-pink-100 shadow-xs flex items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.rentals.index') }}" class="text-xs font-bold text-pink-600 hover:underline flex items-center gap-1">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Data Penyewa
                </a>
            </div>
            <h1 class="text-2xl font-black text-slate-900 mt-2">Edit Data Penyewa: {{ $rental->tenant_name }}</h1>
            <p class="text-xs text-slate-500 mt-1">Perbarui rincian masa sewa, status pembayaran, atau kelola uang deposit jaminan.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.rentals.show', $rental->id) }}" class="px-4 py-2 rounded-xl bg-pink-50 hover:bg-pink-100 text-pink-700 font-bold text-xs transition-all">
                <i class="fa-solid fa-eye"></i> Lihat Detail
            </a>
        </div>
    </div>

    <!-- Error Alert -->
    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold space-y-1">
            <div class="flex items-center gap-2 font-black text-sm">
                <i class="fa-solid fa-triangle-exclamation text-rose-600"></i> Mohon perbaiki data input:
            </div>
            <ul class="list-disc list-inside space-y-0.5 text-rose-700">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.rentals.update', $rental->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- 1. Properti -->
        <div class="bg-white rounded-3xl p-6 border border-pink-100 shadow-xs space-y-4">
            <div class="flex items-center gap-2.5 pb-3 border-b border-pink-100">
                <div class="w-8 h-8 rounded-xl bg-pink-100 text-pink-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-building"></i>
                </div>
                <div>
                    <h2 class="text-base font-black text-slate-900">1. Unit Properti Disewa</h2>
                    <p class="text-[11px] text-slate-400">Unit properti yang sedang/pernah ditempati.</p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Properti Disewa *</label>
                <select name="property_id" required class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white text-slate-800">
                    @foreach($properties as $prop)
                        <option value="{{ $prop->id }}" {{ old('property_id', $rental->property_id) == $prop->id ? 'selected' : '' }}>
                            [{{ $prop->property_code ?? 'PROP' }}] {{ $prop->title }} - {{ $prop->city }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- 2. Data Penyewa -->
        <div class="bg-white rounded-3xl p-6 border border-pink-100 shadow-xs space-y-4">
            <div class="flex items-center gap-2.5 pb-3 border-b border-pink-100">
                <div class="w-8 h-8 rounded-xl bg-pink-100 text-pink-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-id-card"></i>
                </div>
                <div>
                    <h2 class="text-base font-black text-slate-900">2. Data &amp; Identitas Penyewa</h2>
                    <p class="text-[11px] text-slate-400">Kontak utama dan data identitas penyewa.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Lengkap Penyewa *</label>
                    <input type="text"
                           name="tenant_name"
                           value="{{ old('tenant_name', $rental->tenant_name) }}"
                           required
                           class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">No. WhatsApp / HP *</label>
                    <input type="text"
                           name="tenant_phone"
                           value="{{ old('tenant_phone', $rental->tenant_phone) }}"
                           required
                           class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">No. KTP / Paspor</label>
                    <input type="text"
                           name="tenant_id_card"
                           value="{{ old('tenant_id_card', $rental->tenant_id_card) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Email</label>
                    <input type="email"
                           name="tenant_email"
                           value="{{ old('tenant_email', $rental->tenant_email) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Kontak Darurat</label>
                    <input type="text"
                           name="emergency_contact_name"
                           value="{{ old('emergency_contact_name', $rental->emergency_contact_name) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">No. Telp Darurat</label>
                    <input type="text"
                           name="emergency_contact_phone"
                           value="{{ old('emergency_contact_phone', $rental->emergency_contact_phone) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white">
                </div>

                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Alamat Asal Sesuai Identitas</label>
                    <textarea name="tenant_address"
                              rows="2"
                              class="w-full px-3.5 py-2 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white">{{ old('tenant_address', $rental->tenant_address) }}</textarea>
                </div>
            </div>
        </div>

        <!-- 3. Periode Sewa ("Sampai Kapan") -->
        <div class="bg-white rounded-3xl p-6 border border-pink-100 shadow-xs space-y-4">
            <div class="flex items-center gap-2.5 pb-3 border-b border-pink-100">
                <div class="w-8 h-8 rounded-xl bg-pink-100 text-pink-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-calendar-days"></i>
                </div>
                <div>
                    <h2 class="text-base font-black text-slate-900">3. Periode Masa Sewa ("Sampai Kapan")</h2>
                    <p class="text-[11px] text-slate-400">Atur batas waktu sewa atau perpanjangan kontrak.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Mulai Sewa *</label>
                    <input type="date"
                           name="start_date"
                           id="startDateInput"
                           value="{{ old('start_date', $rental->start_date?->toDateString()) }}"
                           required
                           class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Berakhir Sewa *</label>
                    <input type="date"
                           name="end_date"
                           id="endDateInput"
                           value="{{ old('end_date', $rental->end_date?->toDateString()) }}"
                           required
                           class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white font-extrabold text-pink-700">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Satuan Periode *</label>
                    <select name="period_type" id="periodTypeSelect" class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white">
                        <option value="tahun" {{ old('period_type', $rental->period_type) === 'tahun' ? 'selected' : '' }}>Tahun</option>
                        <option value="bulan" {{ old('period_type', $rental->period_type) === 'bulan' ? 'selected' : '' }}>Bulan</option>
                        <option value="hari" {{ old('period_type', $rental->period_type) === 'hari' ? 'selected' : '' }}>Hari</option>
                        <option value="custom" {{ old('period_type', $rental->period_type) === 'custom' ? 'selected' : '' }}>Custom / Bebas</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Durasi *</label>
                    <input type="number"
                           name="duration"
                           id="durationInput"
                           value="{{ old('duration', $rental->duration) }}"
                           min="1"
                           required
                           class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white">
                </div>
            </div>
        </div>

        <!-- 4. Nilai Sewa & Uang Jaminan (Deposit) -->
        <div class="bg-white rounded-3xl p-6 border border-pink-100 shadow-xs space-y-4">
            <div class="flex items-center gap-2.5 pb-3 border-b border-pink-100">
                <div class="w-8 h-8 rounded-xl bg-pink-100 text-pink-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-vault"></i>
                </div>
                <div>
                    <h2 class="text-base font-black text-slate-900">4. Harga Sewa, Pembayaran &amp; Uang Deposit</h2>
                    <p class="text-[11px] text-slate-400">Pengelolaan uang jaminan deposit dan status pengembalian.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Harga Sewa Total (Rp) *</label>
                    <input type="number"
                           name="rent_price"
                           value="{{ old('rent_price', $rental->rent_price) }}"
                           min="0"
                           required
                           class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white font-black text-slate-900">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        <span class="text-pink-600 font-black">Uang Deposit / Jaminan (Rp) *</span>
                    </label>
                    <input type="number"
                           name="deposit_amount"
                           value="{{ old('deposit_amount', $rental->deposit_amount) }}"
                           min="0"
                           required
                           class="w-full px-3.5 py-2.5 rounded-xl text-xs border-2 border-pink-300 focus:outline-none focus:border-pink-600 bg-pink-50/30 font-black text-pink-700">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Status Deposit *</label>
                    <select name="deposit_status" class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white">
                        <option value="held" {{ old('deposit_status', $rental->deposit_status) === 'held' ? 'selected' : '' }}>Ditahan (Aktif)</option>
                        <option value="refunded" {{ old('deposit_status', $rental->deposit_status) === 'refunded' ? 'selected' : '' }}>Dikembalikan Penuh</option>
                        <option value="partially_refunded" {{ old('deposit_status', $rental->deposit_status) === 'partially_refunded' ? 'selected' : '' }}>Dikembalikan Sebagian</option>
                        <option value="deducted" {{ old('deposit_status', $rental->deposit_status) === 'deducted' ? 'selected' : '' }}>Dipotong Kerusakan / Hangus</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Status Pembayaran Sewa *</label>
                    <select name="payment_status" class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white">
                        <option value="paid" {{ old('payment_status', $rental->payment_status) === 'paid' ? 'selected' : '' }}>Lunas</option>
                        <option value="partial" {{ old('payment_status', $rental->payment_status) === 'partial' ? 'selected' : '' }}>Uang Muka / Sebagian</option>
                        <option value="unpaid" {{ old('payment_status', $rental->payment_status) === 'unpaid' ? 'selected' : '' }}>Belum Lunas</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nominal Deposit Dikembalikan (Rp)</label>
                    <input type="number"
                           name="deposit_refunded_amount"
                           value="{{ old('deposit_refunded_amount', $rental->deposit_refunded_amount) }}"
                           min="0"
                           placeholder="0"
                           class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white">
                </div>

                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Catatan Khusus Uang Deposit</label>
                    <input type="text"
                           name="deposit_notes"
                           value="{{ old('deposit_notes', $rental->deposit_notes) }}"
                           placeholder="Cth: Potong Rp 300.000 untuk service AC dan listrik, sisa telah ditransfer ke BCA penyewa."
                           class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white">
                </div>
            </div>
        </div>

        <!-- 5. Status Sewa & Pengakhiran -->
        <div class="bg-white rounded-3xl p-6 border border-pink-100 shadow-xs space-y-4">
            <div class="flex items-center gap-2.5 pb-3 border-b border-pink-100">
                <div class="w-8 h-8 rounded-xl bg-pink-100 text-pink-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-flag-checkered"></i>
                </div>
                <div>
                    <h2 class="text-base font-black text-slate-900">5. Status Masa Sewa &amp; Serah Terima Selesai</h2>
                    <p class="text-[11px] text-slate-400">Pembaruan status operasional sewa unit.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Status Sewa *</label>
                    <select name="status" class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white font-bold text-slate-900">
                        <option value="active" {{ old('status', $rental->status) === 'active' ? 'selected' : '' }}>Aktif (Sedang Berjalan)</option>
                        <option value="completed" {{ old('status', $rental->status) === 'completed' ? 'selected' : '' }}>Selesai (Tuntas / Check-out)</option>
                        <option value="terminated" {{ old('status', $rental->status) === 'terminated' ? 'selected' : '' }}>Dibatalkan / Putus Sewa</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Riil Selesai / Serah Kunci</label>
                    <input type="date"
                           name="actual_end_date"
                           value="{{ old('actual_end_date', $rental->actual_end_date?->toDateString()) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white">
                </div>

                <div class="flex items-center pt-6">
                    <div class="p-3 rounded-2xl bg-pink-50/60 border border-pink-100 flex items-center gap-2 w-full">
                        <input type="checkbox" name="set_property_active" id="set_property_active" value="1" class="rounded text-pink-600 focus:ring-pink-500">
                        <label for="set_property_active" class="text-xs font-bold text-slate-700 cursor-pointer">
                            Kembalikan status listing jadi <span class="text-pink-600 font-extrabold">Aktif / Tersedia</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- 6. Dokumen & Catatan -->
        <div class="bg-white rounded-3xl p-6 border border-pink-100 shadow-xs space-y-4">
            <div class="flex items-center gap-2.5 pb-3 border-b border-pink-100">
                <div class="w-8 h-8 rounded-xl bg-pink-100 text-pink-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-file-lines"></i>
                </div>
                <div>
                    <h2 class="text-base font-black text-slate-900">6. Dokumen &amp; Catatan Khusus</h2>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">No. Perjanjian Sewa</label>
                    <input type="text"
                           name="agreement_number"
                           value="{{ old('agreement_number', $rental->agreement_number) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Ganti Surat Perjanjian (PDF / Foto)</label>
                    <input type="file"
                           name="agreement_document"
                           accept=".pdf,.jpg,.jpeg,.png"
                           class="w-full px-3 py-2 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white text-slate-600 file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-pink-50 file:text-pink-700">
                    @if($rental->agreement_document)
                        <a href="{{ asset('storage/' . $rental->agreement_document) }}" target="_blank" class="text-[10px] text-pink-600 font-bold hover:underline mt-1 inline-block">
                            <i class="fa-solid fa-file-pdf"></i> Lihat Dokumen Saat Ini
                        </a>
                    @endif
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Ganti Foto KTP Penyewa</label>
                    <input type="file"
                           name="id_card_photo"
                           accept=".jpg,.jpeg,.png"
                           class="w-full px-3 py-2 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white text-slate-600 file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-pink-50 file:text-pink-700">
                    @if($rental->id_card_photo)
                        <a href="{{ asset('storage/' . $rental->id_card_photo) }}" target="_blank" class="text-[10px] text-pink-600 font-bold hover:underline mt-1 inline-block">
                            <i class="fa-solid fa-id-card"></i> Lihat KTP Saat Ini
                        </a>
                    @endif
                </div>

                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Catatan Serah Terima &amp; Ketentuan</label>
                    <textarea name="notes"
                              rows="3"
                              class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white">{{ old('notes', $rental->notes) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Submit Bar -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('admin.rentals.index') }}" class="px-6 py-3 rounded-2xl border border-slate-200 hover:bg-slate-50 text-slate-600 font-bold text-xs transition-all">
                Batal
            </a>
            <button type="submit" class="px-8 py-3 rounded-2xl bg-pink-600 hover:bg-pink-700 text-white font-black text-sm shadow-md shadow-pink-600/20 transition-all flex items-center gap-2">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection

