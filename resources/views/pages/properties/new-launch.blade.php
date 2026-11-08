@extends('layouts.app')

@section('title', 'Proyek Baru Developer - Primary Market Launch | ' . \App\Models\Setting::getSiteName() . '')
@section('meta_description', 'Kumpulan proyek hunian baru perumahan cluster dan apartemen langsung dari developer ternama Indonesia dengan promo pre-launch eksklusif.')

@section('content')
<div class="bg-gradient-to-r from-slate-950 via-indigo-950 to-slate-900 text-white py-6 sm:py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center max-w-3xl">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-400/20 text-amber-300 text-xs font-bold uppercase tracking-wider mb-2">
            <i class="fa-solid fa-sparkles"></i> Primary Project Launch 2026
        </span>
        <h1 class="text-2xl sm:text-4xl font-black text-white tracking-tight">
            Proyek Baru Developer Terpercaya
        </h1>
        <p class="mt-2 text-xs sm:text-sm text-slate-300 leading-relaxed">
            Dapatkan harga perdana early-bird, subsidi bunga KPR, bebas biaya BPHTB &amp; notaris untuk pembelian cluster perumahan baru di kota-kota berkembang.
        </p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
        @forelse($properties as $property)
            <x-property-card :property="$property" />
        @empty
            <div class="col-span-full bg-white rounded-3xl border border-slate-200 p-12 text-center text-xs text-slate-500">
                Belum ada proyek baru yang tersedia saat ini.
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $properties->links() }}
    </div>
</div>
@endsection

