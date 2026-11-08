@extends('errors.layout')

@section('title', '503 - Sedang Pemeliharaan Sistem')

@section('content')
<div class="relative">
    <!-- Background Watermark Code -->
    <div class="text-[120px] sm:text-[180px] font-black text-pink-100/70 select-none leading-none tracking-tighter absolute -top-10 sm:-top-16 inset-x-0 z-0 pointer-events-none">
        503
    </div>

    <!-- Error Card -->
    <div class="relative z-10 bg-white/95 backdrop-blur-md rounded-3xl border border-pink-100 p-6 sm:p-10 shadow-xl shadow-pink-500/5">
        <!-- Icon Badge -->
        <div class="w-20 h-20 sm:w-24 sm:h-24 mx-auto rounded-3xl bg-gradient-to-tr from-pink-600 to-rose-500 text-white flex items-center justify-center shadow-lg shadow-pink-600/30 mb-6">
            <i class="fa-solid fa-screwdriver-wrench text-3xl sm:text-4xl"></i>
        </div>

        <span class="inline-block px-3 py-1 rounded-full bg-pink-100 text-pink-700 text-xs font-black uppercase tracking-wider mb-3">
            Status 503 &bull; Maintenance Mode
        </span>

        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight leading-tight">
            Sistem Sedang Dalam Pemeliharaan
        </h1>

        <p class="mt-3 text-xs sm:text-sm text-slate-500 leading-relaxed max-w-md mx-auto">
            Kami sedang melakukan peningkatan performa dan pembaruan sistem untuk memberikan layanan direktori properti yang lebih cepat dan andal. Mohon tunggu beberapa saat.
        </p>

        <!-- Action CTA Buttons -->
        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
            <button type="button" onclick="window.location.reload()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-pink-600 hover:bg-pink-700 text-white text-xs sm:text-sm font-bold shadow-md shadow-pink-600/25 transition-all cursor-pointer">
                <i class="fa-solid fa-rotate-right text-xs"></i>
                <span>Cek Kembali Halaman</span>
            </button>

            @php
                $hotlineNum = class_exists(\App\Models\Setting::class) ? \App\Models\Setting::getCleanSiteHotline() : '6281298765432';
                $isWa = str_starts_with($hotlineNum, '08') || str_starts_with($hotlineNum, '62');
                $waTarget = (str_starts_with($hotlineNum, '0') ? '62' . substr($hotlineNum, 1) : $hotlineNum);
            @endphp
            @if($isWa)
                <a href="https://wa.me/{{ $waTarget }}?text={{ urlencode('Halo admin, apakah website sedang dalam maintenance?') }}" target="_blank" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-xs sm:text-sm font-bold transition-all">
                    <i class="fa-brands fa-whatsapp text-sm text-emerald-600"></i>
                    <span>Tanya Hotline WA</span>
                </a>
            @endif
        </div>
    </div>
</div>
@endsection

