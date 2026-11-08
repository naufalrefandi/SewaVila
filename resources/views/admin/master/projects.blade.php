@extends('layouts.admin')

@section('title', 'Master Data Proyek & Kawasan - Admin ' . \App\Models\Setting::getSiteName() . '')
@section('page_title', 'Master Data Proyek, Gedung & Kawasan Properti')

@section('content')
<div class="space-y-6" x-data="{ showAddModal: false }">
    <!-- Header with Add Button -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-xs flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-city text-pink-600"></i> Direktori Master Proyek &amp; Kawasan
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">
                Data induk gedung apartemen, perumahan cluster, kompleks ruko, kawasan industri, dan kawasan properti lainnya.
            </p>
        </div>

        <button @click="showAddModal = true" class="px-4 py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-extrabold text-xs shadow-sm hover:shadow-md transition-all flex items-center gap-2 shrink-0">
            <i class="fa-solid fa-plus"></i> Tambah Proyek / Kawasan Baru
        </button>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
        <form method="GET" action="{{ route('admin.master.projects') }}" class="flex flex-col sm:flex-row gap-3 items-center">
            <!-- Keyword Search -->
            <div class="relative flex-1 w-full">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text"
                       name="q"
                       value="{{ request('q') }}"
                       placeholder="Cari nama proyek, developer, alamat, atau kota..."
                       class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-pink-500 focus:bg-white focus:outline-none transition-all">
            </div>

            <!-- Tipe Properti Dropdown Filter -->
            <div class="w-full sm:w-60">
                <select name="type" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-pink-500 focus:outline-none transition-all">
                    <option value="">Semua Tipe Properti</option>
                    @foreach($types as $typeKey => $typeLabel)
                        <option value="{{ $typeKey }}" {{ request('type') === $typeKey ? 'selected' : '' }}>
                            {{ $typeLabel }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2 w-full sm:w-auto shrink-0">
                <button type="submit" class="flex-1 sm:flex-initial px-5 py-2.5 bg-slate-900 hover:bg-black text-white text-xs font-extrabold rounded-xl transition-all shadow-xs flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-filter text-[11px]"></i> Filter
                </button>
                @if(request()->filled('q') || request()->filled('type'))
                    <a href="{{ route('admin.master.projects') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition-all flex items-center justify-center gap-1">
                        <i class="fa-solid fa-rotate-left text-[11px]"></i> Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Clean Projects List / Table (No Images) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 font-extrabold uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-4 w-14 text-center">No</th>
                        <th class="py-3.5 px-4">Nama Proyek / Kawasan</th>
                        <th class="py-3.5 px-4">Tipe Properti</th>
                        <th class="py-3.5 px-4">Kota &amp; Wilayah</th>
                        <th class="py-3.5 px-4">Alamat</th>
                        <th class="py-3.5 px-4 text-center">Unit Listing</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                    @forelse($projects as $proj)
                        <tr class="hover:bg-pink-50/30 transition-colors">
                            <!-- No -->
                            <td class="py-3.5 px-4 text-center font-bold text-slate-400">
                                {{ $loop->iteration + ($projects->currentPage() - 1) * $projects->perPage() }}
                            </td>

                            <!-- Nama & Developer -->
                            <td class="py-3.5 px-4">
                                <div class="font-extrabold text-slate-900 text-sm">
                                    {{ $proj->name }}
                                </div>
                                <div class="flex items-center gap-2 mt-0.5 text-[11px] text-slate-500">
                                    @if($proj->developer)
                                        <span><i class="fa-regular fa-building text-slate-400 mr-1"></i>Developer: <strong class="text-slate-700">{{ $proj->developer }}</strong></span>
                                    @endif
                                    @if($proj->total_floors)
                                        <span>&bull; {{ $proj->total_floors }} Lantai</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Tipe Properti -->
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-pink-50 text-pink-700 border border-pink-200 shadow-2xs">
                                    <i class="fa-solid fa-tag text-[10px] text-pink-500"></i>
                                    {{ $proj->type_label }}
                                </span>
                            </td>

                            <!-- Kota & Wilayah -->
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-800 flex items-center gap-1.5">
                                    <i class="fa-solid fa-location-dot text-pink-600 text-[11px]"></i>
                                    <span>{{ $proj->city ? $proj->city->name : '-' }}</span>
                                </div>
                                @if($proj->district)
                                    <div class="text-[11px] text-slate-400 pl-4 mt-0.5">
                                        {{ $proj->district->name }}
                                    </div>
                                @endif
                            </td>

                            <!-- Alamat -->
                            <td class="py-3.5 px-4 max-w-xs">
                                <p class="text-[11px] text-slate-600 truncate" title="{{ $proj->address }}">
                                    {{ $proj->address ?: '-' }}
                                </p>
                            </td>

                            <!-- Unit Listing -->
                            <td class="py-3.5 px-4 text-center">
                                <a href="{{ route('admin.properties', ['project_id' => $proj->id]) }}"
                                   class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-extrabold {{ $proj->properties_count > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500' }} transition-colors">
                                    <span>{{ $proj->properties_count }}</span>
                                    <span class="font-medium text-[10px]">Unit</span>
                                </a>
                            </td>

                            <!-- Aksi -->
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('admin.properties', ['project_id' => $proj->id]) }}"
                                       class="px-2.5 py-1 rounded-lg bg-pink-50 hover:bg-pink-100 text-pink-700 font-bold text-[11px] transition-colors flex items-center gap-1"
                                       title="Lihat Unit Listing">
                                        <i class="fa-solid fa-list-check"></i>
                                        <span>Unit</span>
                                    </a>

                                    <form action="{{ route('admin.master.projects.destroy', $proj->id) }}"
                                          method="POST"
                                          onsubmit="return confirm('Hapus master proyek / kawasan {{ $proj->name }} ini?');"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                                                title="Hapus Master Proyek">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <i class="fa-solid fa-city text-3xl text-slate-300"></i>
                                    <p class="font-bold text-slate-600">Tidak ada proyek / kawasan properti yang ditemukan.</p>
                                    <p class="text-xs text-slate-400">Coba ubah kata kunci pencarian atau filter tipe properti.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $projects->links() }}
    </div>

    <!-- Modal Tambah Proyek / Kawasan Baru -->
    <div x-show="showAddModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showAddModal = false" class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 max-w-xl w-full shadow-2xl relative my-8">
            <div class="flex items-center justify-between pb-3.5 border-b border-slate-100 mb-5">
                <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-city text-pink-600"></i> Daftarkan Proyek / Kawasan Baru
                </h3>
                <button @click="showAddModal = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('admin.master.projects.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Pilih Kota *</label>
                        <select name="city_id" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:outline-none focus:ring-2 focus:ring-pink-500">
                            <option value="">Pilih Kota</option>
                            @foreach($cities as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->province ? $c->province->name : '' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tipe Properti *</label>
                        <select name="type" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:outline-none focus:ring-2 focus:ring-pink-500">
                            @foreach($types as $k => $lbl)
                                <option value="{{ $k }}">{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Proyek / Gedung / Kawasan *</label>
                    <input type="text"
                           name="name"
                           required
                           placeholder="Contoh: Apartemen Pakuwon Mall Mansion, Citraland Utama, Safe 'n' Lock"
                           class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:ring-2 focus:ring-pink-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nama Developer / Pengembang</label>
                        <input type="text"
                               name="developer"
                               placeholder="Contoh: Pakuwon Group, Ciputra, Sinarmas"
                               class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:ring-2 focus:ring-pink-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Total Lantai (Opsional)</label>
                        <input type="number"
                               name="total_floors"
                               placeholder="Cth: 45"
                               class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:ring-2 focus:ring-pink-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Alamat Gedung / Proyek</label>
                    <input type="text"
                           name="address"
                           placeholder="Jl. Raya Mayjen Sungkono No. XX, Surabaya"
                           class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:ring-2 focus:ring-pink-500 focus:outline-none">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Fasilitas Proyek / Kawasan (Opsional, Dipisahkan Koma)</label>
                    <textarea name="facilities_text"
                              rows="2"
                              placeholder="Direct Mall Access, Swimming Pool, Gym, Club House, 24h Security"
                              class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:ring-2 focus:ring-pink-500 focus:outline-none"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="showAddModal = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-extrabold shadow-sm hover:shadow-md transition-all">
                        Simpan ke Master
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
