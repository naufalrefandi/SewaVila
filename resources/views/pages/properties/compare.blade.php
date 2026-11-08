@extends('layouts.app')

@section('title', 'Bandingkan Properti Side-by-Side - ' . \App\Models\Setting::getSiteName() . '')

@section('content')
<div class="bg-white border-b border-slate-200" x-data="{
    compIds: JSON.parse(localStorage.getItem('pn123_compare') || '[]'),
    reloadWithIds() {
        if (this.compIds.length > 0) {
            window.location.href = '{{ route('properties.compare') }}?ids=' + this.compIds.join(',');
        } else {
            window.location.href = '{{ route('properties.compare') }}';
        }
    },
    removeCompare(id) {
        this.compIds = this.compIds.filter(item => item != id);
        localStorage.setItem('pn123_compare', JSON.stringify(this.compIds));
        this.reloadWithIds();
    },
    addCompare(e) {
        const selectedId = e.target.value;
        if (!selectedId) return;
        if (!this.compIds.includes(parseInt(selectedId))) {
            if (this.compIds.length >= 3) {
                alert('Maksimal membandingkan 3 properti.');
                return;
            }
            this.compIds.push(parseInt(selectedId));
            localStorage.setItem('pn123_compare', JSON.stringify(this.compIds));
            this.reloadWithIds();
        }
    }
}" x-init="
    // If URL has no IDs but localStorage has IDs, sync automatically
    const urlParams = new URLSearchParams(window.location.search);
    if (!urlParams.get('ids') && compIds.length > 0) {
        reloadWithIds();
    }
">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 sm:py-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-50 text-blue-600 text-xs font-bold uppercase tracking-wider mb-2">
                    <i class="fa-solid fa-code-compare"></i> Komparasi Properti
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">
                    Bandingkan Properti Pilihan Anda
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Bandingkan spesifikasi, harga, cicilan KPR, dan legalitas hingga 3 properti secara berdampingan.
                </p>
            </div>

            <!-- Quick Add Dropdown -->
            <div class="flex items-center gap-2">
                <select @change="addCompare($event)" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    <option value="">+ Tambah Properti untuk Dibandingkan</option>
                    @foreach($allProperties as $p)
                        <option value="{{ $p->id }}">{{ $p->title }} ({{ $p->city }})</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
    @if($properties->count() > 0)
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[700px]">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/70">
                        <th class="p-4 sm:p-6 w-1/4 text-xs font-bold uppercase tracking-wider text-slate-400">Parameter</th>
                        @foreach($properties as $property)
                            <th class="p-4 sm:p-6 w-1/4 align-top">
                                <div class="relative">
                                    <button onclick="toggleCompare({{ $property->id }}); window.location.href='{{ route('properties.compare') }}?ids=' + JSON.parse(localStorage.getItem('pn123_compare') || '[]').join(',');"
                                            class="absolute -top-2 -right-2 w-6 h-6 rounded-full bg-rose-100 hover:bg-rose-600 text-rose-600 hover:text-white flex items-center justify-center text-xs transition-colors"
                                            title="Hapus dari perbandingan">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                    <img src="{{ $property->primary_image }}" alt="{{ $property->title }}" class="w-full h-32 object-cover rounded-xl shadow-xs">
                                    <h3 class="mt-2 text-xs font-bold text-slate-900 line-clamp-2">
                                        <a href="{{ route('properties.show', $property->slug) }}" class="hover:text-rose-600">
                                            {{ $property->title }}
                                        </a>
                                    </h3>
                                    <div class="mt-1 text-sm font-extrabold text-rose-600">
                                        {{ $property->formatted_price }}
                                    </div>
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    <tr>
                        <td class="p-4 font-bold text-slate-600 bg-slate-50/50">Tipe &amp; Transaksi</td>
                        @foreach($properties as $p)
                            <td class="p-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $p->listing_type === 'dijual' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $p->listing_type }}
                                </span>
                                <span class="font-semibold text-slate-800 ml-1">{{ $p->property_type_label }}</span>
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="p-4 font-bold text-slate-600 bg-slate-50/50">Estimasi Cicilan KPR</td>
                        @foreach($properties as $p)
                            <td class="p-4 font-extrabold text-slate-900">
                                {{ $p->estimated_kpr_installment ?: '-' }}
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="p-4 font-bold text-slate-600 bg-slate-50/50">Lokasi</td>
                        @foreach($properties as $p)
                            <td class="p-4 text-slate-700">
                                {{ $p->district }}, {{ $p->city }}
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="p-4 font-bold text-slate-600 bg-slate-50/50">Luas Tanah (LT)</td>
                        @foreach($properties as $p)
                            <td class="p-4 font-bold text-slate-900">
                                {{ $p->land_area ? $p->land_area . ' m²' : '-' }}
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="p-4 font-bold text-slate-600 bg-slate-50/50">Luas Bangunan (LB)</td>
                        @foreach($properties as $p)
                            <td class="p-4 font-bold text-slate-900">
                                {{ $p->building_area ? $p->building_area . ' m²' : '-' }}
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="p-4 font-bold text-slate-600 bg-slate-50/50">Kamar Tidur (KT)</td>
                        @foreach($properties as $p)
                            <td class="p-4 font-bold text-slate-900">
                                {{ $p->bedrooms ?? '-' }} KT
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="p-4 font-bold text-slate-600 bg-slate-50/50">Kamar Mandi (KM)</td>
                        @foreach($properties as $p)
                            <td class="p-4 font-bold text-slate-900">
                                {{ $p->bathrooms ?? '-' }} KM
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="p-4 font-bold text-slate-600 bg-slate-50/50">Carport / Garasi</td>
                        @foreach($properties as $p)
                            <td class="p-4 text-slate-700">
                                {{ $p->carports ? $p->carports . ' Mobil' : '-' }}
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="p-4 font-bold text-slate-600 bg-slate-50/50">Sertifikat</td>
                        @foreach($properties as $p)
                            <td class="p-4 font-semibold text-slate-800">
                                {{ $p->certificate ?? '-' }}
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="p-4 font-bold text-slate-600 bg-slate-50/50">Status Perabotan</td>
                        @foreach($properties as $p)
                            <td class="p-4 text-slate-700">
                                {{ $p->furnished_status_label }}
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="p-4 font-bold text-slate-600 bg-slate-50/50">Aksi</td>
                        @foreach($properties as $p)
                            <td class="p-4">
                                <a href="{{ route('properties.show', $p->slug) }}" class="block text-center py-2 px-3 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-sm transition-all mb-1.5">
                                    Lihat Detail
                                </a>
                                <a href="https://wa.me/{{ $p->agent ? $p->agent->clean_phone : '6281298765432' }}?text={{ urlencode('Halo, saya tertarik dengan listing: ' . $p->title . ' di ' . route('properties.show', $p->slug)) }}"
                                   target="_blank"
                                   class="block text-center py-1.5 px-3 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white font-bold text-xs transition-all">
                                    <i class="fa-brands fa-whatsapp"></i> Chat WA
                                </a>
                            </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
    @else
        <div class="bg-white rounded-3xl border border-slate-200 p-12 text-center max-w-lg mx-auto">
            <div class="w-16 h-16 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl mx-auto mb-4">
                <i class="fa-solid fa-code-compare"></i>
            </div>
            <h3 class="text-base font-bold text-slate-900">Belum ada properti yang dibandingkan</h3>
            <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                Klik tombol ikon perbandingan <i class="fa-solid fa-code-compare text-blue-600"></i> pada kartu properti untuk membandingkan spesifikasinya di sini.
            </p>
            <a href="{{ route('properties.index') }}" class="mt-4 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-rose-600 text-white font-bold text-xs shadow-sm hover:bg-rose-700 transition-all">
                Cari Properti Sekarang
            </a>
        </div>
    @endif
</div>
@endsection

