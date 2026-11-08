@extends('layouts.admin')

@section('title', 'Detail Penyewa - ' . $rental->tenant_name)
@section('page_title', 'Detail Data Penyewa & Masa Sewa')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Top Nav Back -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.rentals.index') }}" class="text-xs font-bold text-pink-600 hover:underline flex items-center gap-1.5">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Penyewa
        </a>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.rentals.edit', $rental->id) }}" class="px-4 py-2 rounded-xl bg-pink-50 hover:bg-pink-100 text-pink-700 font-bold text-xs transition-all flex items-center gap-1.5">
                <i class="fa-solid fa-pencil"></i> Edit Data
            </a>
            <a href="{{ $rental->whatsapp_reminder_link }}" target="_blank" class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs shadow-xs transition-all flex items-center gap-1.5">
                <i class="fa-brands fa-whatsapp text-sm"></i> Kirim WA Reminder
            </a>
        </div>
    </div>

    <!-- Main Header Card -->
    <div class="bg-white rounded-3xl p-6 border border-pink-100 shadow-xs relative overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-pink-100 text-pink-600 flex items-center justify-center text-2xl font-black shrink-0">
                    {{ strtoupper(substr($rental->tenant_name, 0, 2)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-2xl font-black text-slate-900">{{ $rental->tenant_name }}</h1>
                        @if($rental->agreement_number)
                            <span class="text-xs font-mono px-2 py-0.5 rounded-lg bg-pink-50 text-pink-700 border border-pink-200 font-bold">
                                {{ $rental->agreement_number }}
                            </span>
                        @endif
                    </div>
                    <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500 mt-1">
                        <a href="{{ $rental->whatsapp_link }}" target="_blank" class="font-bold text-emerald-600 hover:underline flex items-center gap-1">
                            <i class="fa-brands fa-whatsapp text-emerald-500"></i> {{ $rental->tenant_phone }}
                        </a>
                        @if($rental->tenant_email)
                            <span>&bull; {{ $rental->tenant_email }}</span>
                        @endif
                        @if($rental->agent)
                            <span>&bull; Agen: <strong class="text-slate-700">{{ $rental->agent->name }}</strong></span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Status Badge -->
            <div class="flex flex-col items-start md:items-end gap-1.5">
                @if($rental->status === 'active')
                    @if($rental->is_overdue)
                        <span class="px-3 py-1 rounded-full text-xs font-black bg-rose-100 text-rose-800 border border-rose-200 animate-pulse">
                            <i class="fa-solid fa-triangle-exclamation"></i> Lewat Jatuh Tempo ({{ abs($rental->days_remaining) }} Hari)
                        </span>
                    @elseif($rental->is_expiring_soon)
                        <span class="px-3 py-1 rounded-full text-xs font-black bg-amber-100 text-amber-800 border border-amber-300">
                            <i class="fa-solid fa-clock"></i> Mau Habis (Sisa {{ $rental->days_remaining }} Hari)
                        </span>
                    @else
                        <span class="px-3 py-1 rounded-full text-xs font-black bg-pink-600 text-white shadow-xs">
                            <i class="fa-solid fa-circle-check"></i> Sewa Aktif (Sisa {{ $rental->days_remaining }} Hari)
                        </span>
                    @endif
                @elseif($rental->status === 'completed')
                    <span class="px-3 py-1 rounded-full text-xs font-black bg-slate-100 text-slate-700 border border-slate-200">
                        <i class="fa-solid fa-check-double text-emerald-600"></i> Sewa Selesai
                    </span>
                @else
                    <span class="px-3 py-1 rounded-full text-xs font-black bg-rose-100 text-rose-700 border border-rose-200">
                        {{ $rental->status_label }}
                    </span>
                @endif

                <span class="text-[11px] text-slate-400">
                    Dicatat pada {{ $rental->created_at->translatedFormat('d F Y, H:i') }}
                </span>
            </div>
        </div>
    </div>

    <!-- 2 Columns Detail -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left: Tenant Info & Documents (5 cols) -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Data Penyewa Card -->
            <div class="bg-white rounded-3xl p-6 border border-pink-100 shadow-xs space-y-4">
                <div class="flex items-center gap-2 pb-3 border-b border-pink-100">
                    <i class="fa-solid fa-id-card text-pink-600"></i>
                    <h3 class="text-sm font-black text-slate-900">Identitas &amp; Kontak Penyewa</h3>
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <div class="text-slate-400 font-bold text-[10px] uppercase">Nama Lengkap</div>
                        <div class="font-extrabold text-slate-900 text-sm mt-0.5">{{ $rental->tenant_name }}</div>
                    </div>

                    <div>
                        <div class="text-slate-400 font-bold text-[10px] uppercase">Nomor WhatsApp / HP</div>
                        <div class="mt-0.5">
                            <a href="{{ $rental->whatsapp_link }}" target="_blank" class="font-bold text-emerald-600 hover:underline flex items-center gap-1.5">
                                <i class="fa-brands fa-whatsapp text-emerald-500 text-sm"></i> {{ $rental->tenant_phone }}
                            </a>
                        </div>
                    </div>

                    <div>
                        <div class="text-slate-400 font-bold text-[10px] uppercase">No. Identitas (KTP / Paspor)</div>
                        <div class="font-bold text-slate-800 mt-0.5">{{ $rental->tenant_id_card ?? '-' }}</div>
                    </div>

                    <div>
                        <div class="text-slate-400 font-bold text-[10px] uppercase">Email</div>
                        <div class="font-bold text-slate-800 mt-0.5">{{ $rental->tenant_email ?? '-' }}</div>
                    </div>

                    <div>
                        <div class="text-slate-400 font-bold text-[10px] uppercase">Kontak Darurat (Emergency)</div>
                        <div class="font-bold text-slate-800 mt-0.5">
                            {{ $rental->emergency_contact_name ?? '-' }}
                            @if($rental->emergency_contact_phone)
                                ({{ $rental->emergency_contact_phone }})
                            @endif
                        </div>
                    </div>

                    <div>
                        <div class="text-slate-400 font-bold text-[10px] uppercase">Alamat Asal Sesuai KTP</div>
                        <div class="text-slate-700 mt-0.5 leading-relaxed">{{ $rental->tenant_address ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <!-- Documents Card -->
            <div class="bg-white rounded-3xl p-6 border border-pink-100 shadow-xs space-y-4">
                <div class="flex items-center gap-2 pb-3 border-b border-pink-100">
                    <i class="fa-solid fa-file-contract text-pink-600"></i>
                    <h3 class="text-sm font-black text-slate-900">Berkas Perjanjian &amp; KTP</h3>
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <div class="text-slate-400 font-bold text-[10px] uppercase">Surat Perjanjian Sewa</div>
                        @if($rental->agreement_document)
                            <a href="{{ asset('storage/' . $rental->agreement_document) }}" target="_blank" class="inline-flex items-center gap-2 mt-1.5 px-3 py-2 rounded-xl bg-pink-50 hover:bg-pink-100 text-pink-700 font-bold text-xs border border-pink-200 transition-all">
                                <i class="fa-solid fa-file-pdf text-rose-500"></i>
                                <span>Unduh / Lihat Dokumen Kontrak</span>
                            </a>
                        @else
                            <div class="text-slate-400 italic mt-0.5">Belum diunggah</div>
                        @endif
                    </div>

                    <div>
                        <div class="text-slate-400 font-bold text-[10px] uppercase">Foto Identitas / KTP</div>
                        @if($rental->id_card_photo)
                            <div class="mt-2">
                                <a href="{{ asset('storage/' . $rental->id_card_photo) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $rental->id_card_photo) }}" alt="KTP Penyewa" class="w-full max-h-48 object-cover rounded-xl border border-pink-100 hover:opacity-90 transition-all">
                                </a>
                            </div>
                        @else
                            <div class="text-slate-400 italic mt-0.5">Belum diunggah</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Lease Details, Property & Deposit (7 cols) -->
        <div class="lg:col-span-7 space-y-6">
            <!-- Property Details Card -->
            <div class="bg-white rounded-3xl p-6 border border-pink-100 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-pink-100">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-building text-pink-600"></i>
                        <h3 class="text-sm font-black text-slate-900">Unit Properti Disewa</h3>
                    </div>
                    @if($rental->property)
                        <a href="{{ route('properties.show', $rental->property->slug) }}" target="_blank" class="text-xs font-bold text-pink-600 hover:underline">
                            Buka Halaman Listing &rarr;
                        </a>
                    @endif
                </div>

                @if($rental->property)
                    <div class="flex items-start gap-4">
                        <img src="{{ $rental->property->primary_image }}" alt="{{ $rental->property->title }}" class="w-24 h-24 rounded-2xl object-cover shrink-0 border border-pink-100">
                        <div class="min-w-0 flex-1 space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-xs font-bold px-2 py-0.5 rounded-lg bg-pink-50 text-pink-700 border border-pink-200">
                                    {{ $rental->property->property_code ?? 'PROP' }}
                                </span>
                                <span class="text-xs font-bold text-slate-500 uppercase">{{ $rental->property->property_type }}</span>
                                <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $rental->property->status === 'rented' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                                    {{ ucfirst($rental->property->status) }}
                                </span>
                            </div>
                            <h4 class="font-black text-slate-900 text-sm line-clamp-2">{{ $rental->property->title }}</h4>
                            <p class="text-xs text-slate-500 line-clamp-1">
                                <i class="fa-solid fa-location-dot text-pink-500"></i> {{ $rental->property->city ?? '-' }} &bull; {{ $rental->property->address }}
                            </p>
                        </div>
                    </div>
                @else
                    <div class="text-slate-400 italic">Data properti tidak tersedia.</div>
                @endif
            </div>

            <!-- Lease Period & Timeline Card -->
            <div class="bg-white rounded-3xl p-6 border border-pink-100 shadow-xs space-y-4">
                <div class="flex items-center gap-2 pb-3 border-b border-pink-100">
                    <i class="fa-solid fa-calendar-days text-pink-600"></i>
                    <h3 class="text-sm font-black text-slate-900">Periode Masa Sewa ("Sampai Kapan")</h3>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs">
                    <div class="p-3 rounded-2xl bg-pink-50/50 border border-pink-100">
                        <div class="text-slate-400 font-bold text-[10px] uppercase">Tanggal Mulai</div>
                        <div class="font-black text-slate-900 text-sm mt-1">
                            {{ \Carbon\Carbon::parse($rental->start_date)->translatedFormat('d F Y') }}
                        </div>
                    </div>

                    <div class="p-3 rounded-2xl bg-pink-50 border border-pink-200">
                        <div class="text-pink-600 font-black text-[10px] uppercase">Berakhir ("Sampai Kapan")</div>
                        <div class="font-black text-pink-700 text-sm mt-1">
                            {{ \Carbon\Carbon::parse($rental->end_date)->translatedFormat('d F Y') }}
                        </div>
                    </div>

                    <div class="p-3 rounded-2xl bg-pink-50/50 border border-pink-100">
                        <div class="text-slate-400 font-bold text-[10px] uppercase">Durasi Total</div>
                        <div class="font-black text-slate-900 text-sm mt-1">
                            {{ $rental->duration }} {{ ucfirst($rental->period_type) }}
                        </div>
                    </div>
                </div>

                @if($rental->actual_end_date)
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 text-xs">
                        <span class="font-bold text-slate-500">Tanggal Pengakhiran Sebenarnya (Check-out):</span>
                        <span class="font-black text-slate-900 ml-1">{{ \Carbon\Carbon::parse($rental->actual_end_date)->translatedFormat('d F Y') }}</span>
                    </div>
                @endif
            </div>

            <!-- Deposit & Financial Card ("termasuk deposit") -->
            <div class="bg-white rounded-3xl p-6 border-2 border-pink-200 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-pink-100">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-vault text-pink-600"></i>
                        <h3 class="text-sm font-black text-slate-900">Uang Jaminan (Deposit) &amp; Biaya Sewa</h3>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black {{ $rental->deposit_status === 'held' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                        {{ $rental->deposit_status_label }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div class="p-4 rounded-2xl bg-pink-50/40 border border-pink-100">
                        <div class="text-slate-400 font-bold text-[10px] uppercase">Harga Sewa Unit</div>
                        <div class="text-xl font-black text-slate-900 mt-1">{{ $rental->formatted_rent_price }}</div>
                        <div class="text-[10px] text-slate-400 mt-0.5">Status Pembayaran: <strong class="text-slate-700">{{ $rental->payment_status_label }}</strong></div>
                    </div>

                    <div class="p-4 rounded-2xl bg-pink-100/60 border border-pink-200">
                        <div class="text-pink-600 font-black text-[10px] uppercase">Uang Deposit Jaminan</div>
                        <div class="text-xl font-black text-pink-700 mt-1">{{ $rental->formatted_deposit_amount }}</div>
                        <div class="text-[10px] text-slate-500 mt-0.5">Status Deposit: <strong class="text-pink-700">{{ $rental->deposit_status_label }}</strong></div>
                    </div>
                </div>

                @if($rental->deposit_refunded_amount || $rental->deposit_notes)
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 text-xs space-y-1">
                        @if($rental->deposit_refunded_amount)
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500 font-bold">Nominal Dikembalikan:</span>
                                <span class="font-black text-emerald-700">{{ $rental->formatted_deposit_refunded_amount }}</span>
                            </div>
                        @endif
                        @if($rental->deposit_notes)
                            <div class="text-slate-600 pt-1">
                                <span class="font-bold text-slate-700">Catatan Pengembalian / Potongan:</span>
                                <p class="mt-0.5 italic text-slate-700">&ldquo;{{ $rental->deposit_notes }}&rdquo;</p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Notes & Handover Card -->
            @if($rental->notes)
                <div class="bg-white rounded-3xl p-6 border border-pink-100 shadow-xs space-y-3">
                    <div class="flex items-center gap-2 pb-2 border-b border-pink-100">
                        <i class="fa-solid fa-clipboard-list text-pink-600"></i>
                        <h3 class="text-sm font-black text-slate-900">Catatan Serah Terima &amp; Ketentuan</h3>
                    </div>
                    <div class="text-xs text-slate-700 leading-relaxed whitespace-pre-line">
                        {{ $rental->notes }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

