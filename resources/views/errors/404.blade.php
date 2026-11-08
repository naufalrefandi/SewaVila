@extends('errors.layout')

@section('title', '404 - Halaman Tidak Ditemukan')

@section('content')
<div class="relative">
    <!-- Background Watermark Code -->
    <div class="text-[120px] sm:text-[180px] font-black text-pink-100/70 select-none leading-none tracking-tighter absolute -top-10 sm:-top-16 inset-x-0 z-0 pointer-events-none">
        404
    </div>

    <!-- Error Card -->
    <div class="relative z-10 bg-white/95 backdrop-blur-md rounded-3xl border border-pink-100 p-6 sm:p-10 shadow-xl shadow-pink-500/5">
        <!-- Icon Badge -->
        <div class="w-20 h-20 sm:w-24 sm:h-24 mx-auto rounded-3xl bg-gradient-to-tr from-pink-500 to-rose-500 text-white flex items-center justify-center shadow-lg shadow-pink-500/30 mb-6">
            <i class="fa-solid fa-map-location-dot text-3xl sm:text-4xl"></i>
        </div>

        <span class="inline-block px-3 py-1 rounded-full bg-pink-100 text-pink-700 text-xs font-black uppercase tracking-wider mb-3">
            Error 404 &bull; Not Found
        </span>

        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight leading-tight">
            Halaman atau Properti Tidak Ditemukan
        </h1>

        <p class="mt-3 text-xs sm:text-sm text-slate-500 leading-relaxed max-w-md mx-auto">
            Halaman yang Anda cari mungkin telah dipindahkan, tautannya kedaluwarsa, atau listing properti tersebut sudah laku terjual.
        </p>

        <!-- Action CTA Buttons -->
        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ url('/') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-pink-600 hover:bg-pink-700 text-white text-xs sm:text-sm font-bold shadow-md shadow-pink-600/25 transition-all">
                <i class="fa-solid fa-house text-xs"></i>
                <span>Kembali ke Beranda</span>
            </a>

            <a href="{{ url('/properti') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs sm:text-sm font-bold shadow-xs transition-all">
                <i class="fa-solid fa-magnifying-glass text-xs"></i>
                <span>Eksplor Properti Lain</span>
            </a>

            <button type="button" onclick="window.history.back()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs sm:text-sm font-bold transition-all">
                <i class="fa-solid fa-arrow-left text-xs"></i>
                <span>Kembali</span>
            </button>
        </div>

        <!-- Helpful Shortcuts -->
        <div class="mt-8 pt-6 border-t border-slate-100">
            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2.5">
                Pencarian Cepat Properti:
            </div>
            <div class="flex items-center justify-center gap-2 flex-wrap text-xs">
                <a href="{{ url('/properti?listing_type=dijual') }}" class="px-2.5 py-1 rounded-lg bg-pink-50 hover:bg-pink-100 text-pink-700 font-semibold transition-colors">
                    Rumah Dijual
                </a>
                <a href="{{ url('/properti?property_type=apartemen') }}" class="px-2.5 py-1 rounded-lg bg-pink-50 hover:bg-pink-100 text-pink-700 font-semibold transition-colors">
                    Apartemen
                </a>
                <a href="{{ url('/properti-baru') }}" class="px-2.5 py-1 rounded-lg bg-pink-50 hover:bg-pink-100 text-pink-700 font-semibold transition-colors">
                    Proyek Baru
                </a>
                <a href="{{ url('/simulasi-kpr') }}" class="px-2.5 py-1 rounded-lg bg-pink-50 hover:bg-pink-100 text-pink-700 font-semibold transition-colors">
                    Simulasi KPR
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

