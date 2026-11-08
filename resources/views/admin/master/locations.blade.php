@extends('layouts.admin')

@section('title', 'Master Data Wilayah - Admin ' . \App\Models\Setting::getSiteName() . '')
@section('page_title', 'Master Data Wilayah (Provinsi, Kota, Kecamatan)')

@section('content')
<div class="space-y-8" x-data="{ activeTab: 'cities' }">
    <!-- Tab Controls -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-3">
        <button type="button"
                @click="activeTab = 'provinces'"
                :class="activeTab === 'provinces' ? 'bg-rose-600 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-50'"
                class="px-5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 border border-slate-200">
            <i class="fa-solid fa-map"></i> Master Provinsi ({{ $provinces->count() }})
        </button>
        <button type="button"
                @click="activeTab = 'cities'"
                :class="activeTab === 'cities' ? 'bg-rose-600 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-50'"
                class="px-5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 border border-slate-200">
            <i class="fa-solid fa-city"></i> Master Kota / Kabupaten ({{ $cities->count() }})
        </button>
        <button type="button"
                @click="activeTab = 'districts'"
                :class="activeTab === 'districts' ? 'bg-rose-600 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-50'"
                class="px-5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 border border-slate-200">
            <i class="fa-solid fa-location-dot"></i> Master Kecamatan
        </button>
    </div>

    <!-- 1. Provinces Panel -->
    <div x-show="activeTab === 'provinces'" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <!-- Add Province Form -->
        <div class="lg:col-span-4 bg-white rounded-3xl border border-slate-200 p-6 shadow-xs">
            <h3 class="font-extrabold text-slate-900 text-sm mb-4 flex items-center gap-2">
                <i class="fa-solid fa-plus-circle text-rose-600"></i> Tambah Provinsi Baru
            </h3>
            <form action="{{ route('admin.master.provinces.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Provinsi *</label>
                    <input type="text"
                           name="name"
                           required
                           placeholder="Contoh: Jawa Timur, DKI Jakarta"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none">
                </div>
                <button type="submit" class="w-full py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl shadow-sm transition-all">
                    Simpan Provinsi
                </button>
            </form>
        </div>

        <!-- Provinces List Table -->
        <div class="lg:col-span-8 bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-400 uppercase font-bold">
                        <th class="p-4">No</th>
                        <th class="p-4">Nama Provinsi</th>
                        <th class="p-4">Slug</th>
                        <th class="p-4">Jumlah Kota</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($provinces as $index => $prov)
                        <tr class="hover:bg-slate-50/50">
                            <td class="p-4 text-slate-400 font-bold">{{ $index + 1 }}</td>
                            <td class="p-4 font-bold text-slate-900">{{ $prov->name }}</td>
                            <td class="p-4 text-slate-500 font-mono">{{ $prov->slug }}</td>
                            <td class="p-4 font-semibold text-rose-600">{{ $prov->cities->count() }} Kota</td>
                            <td class="p-4 text-center">
                                <form action="{{ route('admin.master.provinces.destroy', $prov->id) }}" method="POST" onsubmit="return confirm('Hapus provinsi ini beserta kotanya?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg bg-slate-100 text-slate-600 hover:text-rose-600 hover:bg-rose-50" title="Hapus">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- 2. Cities Panel -->
    <div x-show="activeTab === 'cities'" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <!-- Add City Form -->
        <div class="lg:col-span-4 bg-white rounded-3xl border border-slate-200 p-6 shadow-xs">
            <h3 class="font-extrabold text-slate-900 text-sm mb-4 flex items-center gap-2">
                <i class="fa-solid fa-plus-circle text-rose-600"></i> Tambah Kota / Kabupaten
            </h3>
            <form action="{{ route('admin.master.cities.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Provinsi *</label>
                    <select name="province_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:outline-none">
                        <option value="">Pilih Provinsi Induk</option>
                        @foreach($provinces as $prov)
                            <option value="{{ $prov->id }}">{{ $prov->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Kota / Kabupaten *</label>
                    <input type="text"
                           name="name"
                           required
                           placeholder="Contoh: Surabaya, Jakarta Selatan"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none">
                </div>

                <button type="submit" class="w-full py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl shadow-sm transition-all">
                    Simpan Kota
                </button>
            </form>
        </div>

        <!-- Cities List Table -->
        <div class="lg:col-span-8 bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-400 uppercase font-bold">
                        <th class="p-4">No</th>
                        <th class="p-4">Kota / Kabupaten</th>
                        <th class="p-4">Provinsi</th>
                        <th class="p-4">Kecamatan</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($cities as $index => $city)
                        <tr class="hover:bg-slate-50/50">
                            <td class="p-4 text-slate-400 font-bold">{{ $index + 1 }}</td>
                            <td class="p-4 font-bold text-slate-900">{{ $city->name }}</td>
                            <td class="p-4 text-slate-600">{{ $city->province ? $city->province->name : '-' }}</td>
                            <td class="p-4 font-semibold text-rose-600">{{ $city->districts->count() }} Kecamatan</td>
                            <td class="p-4 text-center">
                                <form action="{{ route('admin.master.cities.destroy', $city->id) }}" method="POST" onsubmit="return confirm('Hapus kota ini?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg bg-slate-100 text-slate-600 hover:text-rose-600 hover:bg-rose-50" title="Hapus">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- 3. Districts Panel -->
    <div x-show="activeTab === 'districts'" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <!-- Add District Form -->
        <div class="lg:col-span-4 bg-white rounded-3xl border border-slate-200 p-6 shadow-xs">
            <h3 class="font-extrabold text-slate-900 text-sm mb-4 flex items-center gap-2">
                <i class="fa-solid fa-plus-circle text-rose-600"></i> Tambah Kecamatan Baru
            </h3>
            <form action="{{ route('admin.master.districts.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Kota / Kabupaten *</label>
                    <select name="city_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:outline-none">
                        <option value="">Pilih Kota Induk</option>
                        @foreach($cities as $city)
                            <option value="{{ $city->id }}">{{ $city->name }} ({{ $city->province ? $city->province->name : '' }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Kecamatan *</label>
                    <input type="text"
                           name="name"
                           required
                           placeholder="Contoh: Dukuh Pakis, Tegalsari, Gubeng"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none">
                </div>

                <button type="submit" class="w-full py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl shadow-sm transition-all">
                    Simpan Kecamatan
                </button>
            </form>
        </div>

        <!-- Districts List by City -->
        <div class="lg:col-span-8 bg-white rounded-3xl border border-slate-200 p-6 shadow-xs space-y-6">
            <h3 class="font-extrabold text-slate-900 text-sm flex items-center gap-2 pb-3 border-b border-slate-100">
                <i class="fa-solid fa-list text-rose-600"></i> Daftar Kecamatan per Kota
            </h3>

            <div class="space-y-4">
                @foreach($cities as $c)
                    <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-bold text-slate-900 text-xs flex items-center gap-1.5">
                                <i class="fa-solid fa-city text-rose-500"></i> {{ $c->name }} ({{ $c->province ? $c->province->name : '' }})
                            </span>
                            <span class="text-[10px] bg-white px-2 py-0.5 rounded-full font-bold text-slate-600 border border-slate-200">
                                {{ $c->districts->count() }} Kecamatan
                            </span>
                        </div>

                        <div class="flex flex-wrap gap-1.5">
                            @forelse($c->districts as $d)
                                <span class="inline-flex items-center gap-1 bg-white px-2.5 py-1 rounded-lg border border-slate-200 text-xs font-medium text-slate-700">
                                    {{ $d->name }}
                                    <form action="{{ route('admin.master.districts.destroy', $d->id) }}" method="POST" onsubmit="return confirm('Hapus kecamatan ini?');" class="inline ml-1">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-slate-300 hover:text-rose-600">
                                            <i class="fa-solid fa-xmark text-[10px]"></i>
                                        </button>
                                    </form>
                                </span>
                            @empty
                                <span class="text-xs text-slate-400 italic">Belum ada kecamatan terdaftar di kota ini.</span>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

