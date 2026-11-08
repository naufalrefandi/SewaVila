@extends('errors.layout')

@section('title', '403 - Akses Ditolak')

@section('content')
<div class="relative">
    <!-- Background Watermark Code -->
    <div class="text-[120px] sm:text-[180px] font-black text-rose-100/70 select-none leading-none tracking-tighter absolute -top-10 sm:-top-16 inset-x-0 z-0 pointer-events-none">
        403
    </div>

    <!-- Error Card -->
    <div class="relative z-10 bg-white/95 backdrop-blur-md rounded-3xl border border-rose-100 p-6 sm:p-10 shadow-xl shadow-rose-500/5">
        <!-- Icon Badge -->
        <div class="w-20 h-20 sm:w-24 sm:h-24 mx-auto rounded-3xl bg-gradient-to-tr from-amber-500 via-rose-500 to-red-500 text-white flex items-center justify-center shadow-lg shadow-rose-500/30 mb-6">
            <i class="fa-solid fa-shield-halved text-3xl sm:text-4xl"></i>
        </div>

        <span class="inline-block px-3 py-1 rounded-full bg-rose-100 text-rose-700 text-xs font-black uppercase tracking-wider mb-3">
            Error 403 &bull; Akses Ditolak
        </span>

        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight leading-tight">
            Akses Terbatas / Ditolak
        </h1>

        <p class="mt-3 text-xs sm:text-sm text-slate-500 leading-relaxed max-w-md mx-auto">
            {{ $exception?->getMessage() ?: 'Anda tidak memiliki hak akses atau kredensial yang memadai untuk membuka halaman ini. Silakan pastikan Anda login dengan akun yang memiliki otorisasi.' }}
        </p>

        <!-- Action CTA Buttons -->
        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ url('/') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-pink-600 hover:bg-pink-700 text-white text-xs sm:text-sm font-bold shadow-md shadow-pink-600/25 transition-all">
                <i class="fa-solid fa-house text-xs"></i>
                <span>Kembali ke Beranda</span>
            </a>

            @guest
                <a href="{{ route('login') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs sm:text-sm font-bold shadow-xs transition-all">
                    <i class="fa-solid fa-right-to-bracket text-xs"></i>
                    <span>Masuk ke Akun</span>
                </a>
            @else
                <a href="{{ route('admin.dashboard') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs sm:text-sm font-bold shadow-xs transition-all">
                    <i class="fa-solid fa-gauge text-xs"></i>
                    <span>Ke Dashboard Saya</span>
                </a>
            @endguest

            <button type="button" onclick="window.history.back()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs sm:text-sm font-bold transition-all">
                <i class="fa-solid fa-arrow-left text-xs"></i>
                <span>Kembali</span>
            </button>
        </div>

        <div class="mt-8 pt-6 border-t border-slate-100 text-xs text-slate-500">
            Butuh bantuan terkait izin akses akun? Silakan hubungi admin atau hotline kami.
        </div>
    </div>
</div>
@endsection

