@extends('errors.layout')

@section('title', '500 - Gangguan Server')

@section('content')
<div class="relative">
    <!-- Background Watermark Code -->
    <div class="text-[120px] sm:text-[180px] font-black text-slate-200/60 select-none leading-none tracking-tighter absolute -top-10 sm:-top-16 inset-x-0 z-0 pointer-events-none">
        500
    </div>

    <!-- Error Card -->
    <div class="relative z-10 bg-white/95 backdrop-blur-md rounded-3xl border border-slate-200 p-6 sm:p-10 shadow-xl shadow-slate-900/5">
        <!-- Icon Badge -->
        <div class="w-20 h-20 sm:w-24 sm:h-24 mx-auto rounded-3xl bg-gradient-to-tr from-rose-600 via-pink-600 to-amber-500 text-white flex items-center justify-center shadow-lg shadow-pink-600/30 mb-6">
            <i class="fa-solid fa-triangle-exclamation text-3xl sm:text-4xl"></i>
        </div>

        <span class="inline-block px-3 py-1 rounded-full bg-rose-100 text-rose-800 text-xs font-black uppercase tracking-wider mb-3">
            Error 500 &bull; Internal Server Error
        </span>

        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight leading-tight">
            Terjadi Kendala pada Sistem
        </h1>

        <p class="mt-3 text-xs sm:text-sm text-slate-500 leading-relaxed max-w-md mx-auto">
            Mohon maaf atas ketidaknyamanannya. Terjadi kesalahan internal saat sistem memproses permintaan Anda. Tim kami telah mencatat aktivitas ini untuk ditangani secepatnya.
        </p>

        <!-- Action CTA Buttons -->
        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
            <button type="button" onclick="window.location.reload()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-pink-600 hover:bg-pink-700 text-white text-xs sm:text-sm font-bold shadow-md shadow-pink-600/25 transition-all cursor-pointer">
                <i class="fa-solid fa-rotate-right text-xs"></i>
                <span>Muat Ulang Halaman</span>
            </button>

            <a href="{{ url('/') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs sm:text-sm font-bold shadow-xs transition-all">
                <i class="fa-solid fa-house text-xs"></i>
                <span>Kembali ke Beranda</span>
            </a>

            @php
                $hotlineNum = class_exists(\App\Models\Setting::class) ? \App\Models\Setting::getCleanSiteHotline() : '6281298765432';
                $isWa = str_starts_with($hotlineNum, '08') || str_starts_with($hotlineNum, '62');
                $waTarget = (str_starts_with($hotlineNum, '0') ? '62' . substr($hotlineNum, 1) : $hotlineNum);
            @endphp
            @if($isWa)
                <a href="https://wa.me/{{ $waTarget }}?text={{ urlencode('Halo admin, saya mengalami kendala teknis Error 500 di website: ' . url()->current()) }}" target="_blank" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-xs sm:text-sm font-bold transition-all">
                    <i class="fa-brands fa-whatsapp text-sm text-emerald-600"></i>
                    <span>Lapor Masalah</span>
                </a>
            @endif
        </div>

        <div class="mt-8 pt-6 border-t border-slate-100 text-xs text-slate-400">
            Jika kendala terus berlanjut setelah memuat ulang, silakan coba beberapa saat lagi atau hubungi layanan pelanggan kami.
        </div>
    </div>
</div>
@endsection

