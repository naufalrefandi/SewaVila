@extends('layouts.admin')

@section('title', 'Catat Penyewa Baru - ' . \App\Models\Setting::getSiteName() . '')
@section('page_title', 'Catat Data Penyewa Baru')

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
            <h1 class="text-2xl font-black text-slate-900 mt-2">Formulir Catat Penyewa &amp; Uang Deposit</h1>
            <p class="text-xs text-slate-500 mt-1">Lengkapi data penyewa, masa periode sewa ("sampai kapan"), serta uang jaminan (deposit).</p>
        </div>
    </div>

    <!-- Error Validation Alert -->
    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold space-y-1">
            <div class="flex items-center gap-2 font-black text-sm">
                <i class="fa-solid fa-triangle-exclamation text-rose-600"></i> Mohon perbaiki data input berikut:
            </div>
            <ul class="list-disc list-inside space-y-0.5 text-rose-700">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.rentals.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- 1. Pilih Properti -->
        <div class="bg-white rounded-3xl p-6 border border-pink-100 shadow-xs space-y-4">
            <div class="flex items-center gap-2.5 pb-3 border-b border-pink-100">
                <div class="w-8 h-8 rounded-xl bg-pink-100 text-pink-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-building"></i>
                </div>
                <div>
                    <h2 class="text-base font-black text-slate-900">1. Unit Properti yang Disewa</h2>
                    <p class="text-[11px] text-slate-400">Pilih unit properti dari daftar listing yang akan ditempati oleh penyewa.</p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Pilih Properti Disewa *</label>
                <select name="property_id" id="propertySelect" required onchange="updatePropertyPreview(this)" class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white text-slate-800">
                    <option value="">-- Pilih Unit Properti (Khusus Listing Disewa) --</option>
                    @foreach($properties as $prop)
                        <option value="{{ $prop->id }}"
                                data-price="{{ $prop->rent_price ?? $prop->price }}"
                                data-code="{{ $prop->property_code }}"
                                data-type="{{ ucfirst($prop->property_type) }}"
                                data-city="{{ $prop->city }}"
                                data-address="{{ $prop->address }}"
                                {{ old('property_id', $selectedPropertyId) == $prop->id ? 'selected' : '' }}>
                            [{{ $prop->property_code ?? 'PROP' }}] {{ $prop->title }} - {{ $prop->city }} (Rp {{ number_format($prop->rent_price ?? $prop->price, 0, ',', '.') }}/thn)
                        </option>
                    @endforeach
                </select>
                @if($properties->isEmpty())
                    <p class="text-[11px] text-amber-600 mt-1.5 flex items-center gap-1 font-semibold">
                        <i class="fa-solid fa-circle-exclamation"></i> Belum ada listing berstatus "Disewa". Silakan tambah atau ubah transaksi listing menjadi Disewa terlebih dahulu.
                    </p>
                @endif
            </div>

            <div class="p-3 rounded-2xl bg-pink-50/60 border border-pink-100 flex items-center gap-2.5">
                <input type="checkbox" name="mark_property_rented" id="mark_property_rented" value="1" checked class="rounded text-pink-600 focus:ring-pink-500">
                <label for="mark_property_rented" class="text-xs font-bold text-slate-700 cursor-pointer">
                    Otomatis tandai status listing properti menjadi <span class="text-pink-600 font-extrabold">&ldquo;Tersewa (Rented)&rdquo;</span>
                </label>
            </div>
        </div>

        <!-- 2. Data Identitas Penyewa -->
        <div class="bg-white rounded-3xl p-6 border border-pink-100 shadow-xs space-y-4">
            <div class="flex items-center gap-2.5 pb-3 border-b border-pink-100">
                <div class="w-8 h-8 rounded-xl bg-pink-100 text-pink-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-id-card"></i>
                </div>
                <div>
                    <h2 class="text-base font-black text-slate-900">2. Data &amp; Identitas Penyewa</h2>
                    <p class="text-[11px] text-slate-400">Informasi penyewa untuk keperluan kontak dan verifikasi administrasi.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Lengkap Penyewa *</label>
                    <input type="text"
                           name="tenant_name"
                           value="{{ old('tenant_name') }}"
                           required
                           placeholder="Cth: Bpk. Hendra Gunawan"
                           class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">No. WhatsApp / HP *</label>
                    <input type="text"
                           name="tenant_phone"
                           value="{{ old('tenant_phone') }}"
                           required
                           placeholder="Cth: 081234567890"
                           class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">No. KTP / Paspor (Opsional)</label>
                    <input type="text"
                           name="tenant_id_card"
                           value="{{ old('tenant_id_card') }}"
                           placeholder="Cth: 3578xxxxxxxxxxxx"
                           class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Email (Opsional)</label>
                    <input type="email"
                           name="tenant_email"
                           value="{{ old('tenant_email') }}"
                           placeholder="Cth: hendra@gmail.com"
                           class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Kontak Darurat (Opsional)</label>
                    <input type="text"
                           name="emergency_contact_name"
                           value="{{ old('emergency_contact_name') }}"
                           placeholder="Cth: Ibu Maria (Istri)"
                           class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">No. Telp Darurat (Opsional)</label>
                    <input type="text"
                           name="emergency_contact_phone"
                           value="{{ old('emergency_contact_phone') }}"
                           placeholder="Cth: 08139876xxxx"
                           class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white">
                </div>

                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Alamat Asal Sesuai Identitas (Opsional)</label>
                    <textarea name="tenant_address"
                              rows="2"
                              placeholder="Cth: Jl. Kertajaya Indah No. 12, Manyar, Surabaya"
                              class="w-full px-3.5 py-2 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white">{{ old('tenant_address') }}</textarea>
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
                    <p class="text-[11px] text-slate-400">Atur tanggal mulai dan tanggal batas akhir sewa unit.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Mulai Sewa *</label>
                    <input type="date"
                           name="start_date"
                           id="startDateInput"
                           value="{{ old('start_date', date('Y-m-d')) }}"
                           required
                           onchange="recalculateEndDate()"
                           class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Berakhir Sewa ("Sampai Kapan") *</label>
                    <input type="date"
                           name="end_date"
                           id="endDateInput"
                           value="{{ old('end_date', date('Y-m-d', strtotime('+1 year'))) }}"
                           required
                           class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white font-extrabold text-pink-700">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Satuan Periode *</label>
                    <select name="period_type" id="periodTypeSelect" onchange="recalculateEndDate()" class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white">
                        <option value="tahun" {{ old('period_type', 'tahun') === 'tahun' ? 'selected' : '' }}>Tahun</option>
                        <option value="bulan" {{ old('period_type') === 'bulan' ? 'selected' : '' }}>Bulan</option>
                        <option value="hari" {{ old('period_type') === 'hari' ? 'selected' : '' }}>Hari</option>
                        <option value="custom" {{ old('period_type') === 'custom' ? 'selected' : '' }}>Custom / Bebas</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Durasi *</label>
                    <input type="number"
                           name="duration"
                           id="durationInput"
                           value="{{ old('duration', 1) }}"
                           min="1"
                           required
                           onchange="recalculateEndDate()"
                           class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white">
                </div>
            </div>

            <!-- Quick Duration Buttons -->
            <div class="p-3 rounded-2xl bg-pink-50/50 border border-pink-100 flex flex-wrap items-center gap-2">
                <span class="text-xs font-bold text-slate-500">Pilihan Cepat Masa Sewa:</span>
                <button type="button" onclick="setQuickDuration('bulan', 1)" class="px-2.5 py-1 rounded-xl bg-white border border-pink-200 text-pink-700 hover:bg-pink-100 text-xs font-bold transition-all shadow-xs">
                    +1 Bulan
                </button>
                <button type="button" onclick="setQuickDuration('bulan', 3)" class="px-2.5 py-1 rounded-xl bg-white border border-pink-200 text-pink-700 hover:bg-pink-100 text-xs font-bold transition-all shadow-xs">
                    +3 Bulan
                </button>
                <button type="button" onclick="setQuickDuration('bulan', 6)" class="px-2.5 py-1 rounded-xl bg-white border border-pink-200 text-pink-700 hover:bg-pink-100 text-xs font-bold transition-all shadow-xs">
                    +6 Bulan
                </button>
                <button type="button" onclick="setQuickDuration('tahun', 1)" class="px-2.5 py-1 rounded-xl bg-white border border-pink-200 text-pink-700 hover:bg-pink-100 text-xs font-bold transition-all shadow-xs">
                    +1 Tahun (Standar)
                </button>
                <button type="button" onclick="setQuickDuration('tahun', 2)" class="px-2.5 py-1 rounded-xl bg-white border border-pink-200 text-pink-700 hover:bg-pink-100 text-xs font-bold transition-all shadow-xs">
                    +2 Tahun
                </button>
            </div>
        </div>

        <!-- 4. Nilai Sewa & Uang Jaminan (Deposit) -->
        <div class="bg-white rounded-3xl p-6 border border-pink-100 shadow-xs space-y-4">
            <div class="flex items-center gap-2.5 pb-3 border-b border-pink-100">
                <div class="w-8 h-8 rounded-xl bg-pink-100 text-pink-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-vault"></i>
                </div>
                <div>
                    <h2 class="text-base font-black text-slate-900">4. Harga Sewa &amp; Uang Deposit Jaminan</h2>
                    <p class="text-[11px] text-slate-400">Pencatatan nominal sewa dan deposit keamanan jaminan kerusakan/listrik.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Harga Sewa Total (Rp) *</label>
                    <input type="number"
                           name="rent_price"
                           id="rentPriceInput"
                           value="{{ old('rent_price', $selectedProperty->rent_price ?? $selectedProperty->price ?? 0) }}"
                           min="0"
                           required
                           placeholder="0"
                           class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white font-black text-slate-900">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        <span class="text-pink-600 font-black">Uang Deposit / Jaminan (Rp) *</span>
                    </label>
                    <input type="number"
                           name="deposit_amount"
                           id="depositAmountInput"
                           value="{{ old('deposit_amount', 5000000) }}"
                           min="0"
                           required
                           placeholder="Cth: 5000000"
                           class="w-full px-3.5 py-2.5 rounded-xl text-xs border-2 border-pink-300 focus:outline-none focus:border-pink-600 bg-pink-50/30 font-black text-pink-700">
                    <span class="text-[10px] text-slate-400 mt-1 block">Uang jaminan yang ditahan selama masa sewa</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Status Deposit *</label>
                    <select name="deposit_status" class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white">
                        <option value="held" {{ old('deposit_status', 'held') === 'held' ? 'selected' : '' }}>Ditahan (Aktif)</option>
                        <option value="refunded" {{ old('deposit_status') === 'refunded' ? 'selected' : '' }}>Sudah Dikembalikan</option>
                        <option value="deducted" {{ old('deposit_status') === 'deducted' ? 'selected' : '' }}>Dipotong / Hangus</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Status Pembayaran Sewa *</label>
                    <select name="payment_status" class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white">
                        <option value="paid" {{ old('payment_status', 'paid') === 'paid' ? 'selected' : '' }}>Lunas</option>
                        <option value="partial" {{ old('payment_status') === 'partial' ? 'selected' : '' }}>Uang Muka / Sebagian</option>
                        <option value="unpaid" {{ old('payment_status') === 'unpaid' ? 'selected' : '' }}>Belum Lunas</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- 5. Dokumen & Catatan Tambahan -->
        <div class="bg-white rounded-3xl p-6 border border-pink-100 shadow-xs space-y-4">
            <div class="flex items-center gap-2.5 pb-3 border-b border-pink-100">
                <div class="w-8 h-8 rounded-xl bg-pink-100 text-pink-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-file-lines"></i>
                </div>
                <div>
                    <h2 class="text-base font-black text-slate-900">5. Dokumen Sewa &amp; Catatan Khusus</h2>
                    <p class="text-[11px] text-slate-400">Simpan nomor surat perjanjian, scan dokumen, dan catatan serah terima unit.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">No. Perjanjian Sewa (Opsional)</label>
                    <input type="text"
                           name="agreement_number"
                           value="{{ old('agreement_number') }}"
                           placeholder="Cth: SPK/RENT/2026/014"
                           class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Upload Scan Perjanjian (PDF / Foto)</label>
                    <input type="file"
                           name="agreement_document"
                           accept=".pdf,.jpg,.jpeg,.png"
                           class="w-full px-3 py-2 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white text-slate-600 file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-pink-50 file:text-pink-700">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Upload Foto KTP Penyewa (Opsional)</label>
                    <input type="file"
                           name="id_card_photo"
                           accept=".jpg,.jpeg,.png"
                           class="w-full px-3 py-2 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white text-slate-600 file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-pink-50 file:text-pink-700">
                </div>

                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Catatan Serah Terima / Ketentuan Sewa</label>
                    <textarea name="notes"
                              rows="3"
                              placeholder="Cth: Kunci diserahkan 2 set, kartu akses 1 pcs. Listrik sistem token ditanggung penyewa. IPL ditanggung pemilik. Deposit akan dikembalikan maksimal 7 hari setelah serah terima unit selesai tanpa tunggakan."
                              class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-pink-200 focus:outline-none focus:border-pink-500 bg-white">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Submit Bar -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('admin.rentals.index') }}" class="px-6 py-3 rounded-2xl border border-slate-200 hover:bg-slate-50 text-slate-600 font-bold text-xs transition-all">
                Batal
            </a>
            <button type="submit" class="px-8 py-3 rounded-2xl bg-pink-600 hover:bg-pink-700 text-white font-black text-sm shadow-md shadow-pink-600/20 transition-all flex items-center gap-2">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Data Penyewa
            </button>
        </div>
    </form>
</div>

<script>
    function updatePropertyPreview(select) {
        const option = select.options[select.selectedIndex];
        if (option && option.dataset.price) {
            const price = option.dataset.price;
            const rentInput = document.getElementById('rentPriceInput');
            if (rentInput && (!rentInput.value || rentInput.value === '0')) {
                rentInput.value = price;
            }
        }
    }

    function setQuickDuration(type, count) {
        document.getElementById('periodTypeSelect').value = type;
        document.getElementById('durationInput').value = count;
        recalculateEndDate();
    }

    function recalculateEndDate() {
        const startVal = document.getElementById('startDateInput').value;
        if (!startVal) return;

        const periodType = document.getElementById('periodTypeSelect').value;
        const duration = parseInt(document.getElementById('durationInput').value) || 1;

        const startDate = new Date(startVal);

        if (periodType === 'tahun') {
            startDate.setFullYear(startDate.getFullYear() + duration);
        } else if (periodType === 'bulan') {
            startDate.setMonth(startDate.getMonth() + duration);
        } else if (periodType === 'hari') {
            startDate.setDate(startDate.getDate() + duration);
        }

        const yyyy = startDate.getFullYear();
        const mm = String(startDate.getMonth() + 1).padStart(2, '0');
        const dd = String(startDate.getDate()).padStart(2, '0');

        document.getElementById('endDateInput').value = `${yyyy}-${mm}-${dd}`;
    }
</script>
@endsection

