@extends('errors.layout')

@section('title', '429 - Terlalu Banyak Permintaan')

@section('content')
<div class="relative">
    <!-- Background Watermark Code -->
    <div class="text-[120px] sm:text-[180px] font-black text-amber-100/70 select-none leading-none tracking-tighter absolute -top-10 sm:-top-16 inset-x-0 z-0 pointer-events-none">
        429
    </div>

    <!-- Error Card -->
    <div class="relative z-10 bg-white/95 backdrop-blur-md rounded-3xl border border-amber-100 p-6 sm:p-10 shadow-xl shadow-amber-500/5">
        <!-- Icon Badge -->
        <div class="w-20 h-20 sm:w-24 sm:h-24 mx-auto rounded-3xl bg-gradient-to-tr from-amber-500 to-orange-500 text-white flex items-center justify-center shadow-lg shadow-amber-500/30 mb-6">
            <i class="fa-solid fa-gauge-high text-3xl sm:text-4xl"></i>
        </div>

        <span class="inline-block px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-black uppercase tracking-wider mb-3">
            Error 429 &bull; Too Many Requests
        </span>

        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight leading-tight">
            Terlalu Banyak Permintaan
        </h1>

        <p class="mt-3 text-xs sm:text-sm text-slate-500 leading-relaxed max-w-md mx-auto">
            Sistem kami mendeteksi terlalu banyak permintaan dari perangkat atau jaringan Anda dalam waktu singkat. Mohon istirahat sejenak dan coba kembali dalam 1-2 menit.
        </p>

        <!-- Action CTA Buttons -->
        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
            <button type="button" onclick="window.location.reload()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-pink-600 hover:bg-pink-700 text-white text-xs sm:text-sm font-bold shadow-md shadow-pink-600/25 transition-all cursor-pointer">
                <i class="fa-solid fa-rotate-right text-xs"></i>
                <span>Coba Lagi Sekarang</span>
            </button>

            <a href="{{ url('/') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs sm:text-sm font-bold shadow-xs transition-all">
                <i class="fa-solid fa-house text-xs"></i>
                <span>Kembali ke Beranda</span>
            </a>
        </div>
    </div>
</div>
@endsection

