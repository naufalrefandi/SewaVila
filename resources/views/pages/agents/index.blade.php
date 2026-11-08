@extends('layouts.app')

@section('title', 'Direktori Agen Properti Resmi - ' . \App\Models\Setting::getSiteName() . '')
@section('meta_description', 'Cari dan hubungi agen properti terpercaya bersertifikasi AREBI di Jakarta, Tangerang, Surabaya, Bali, dan kota besar lainnya.')

@section('content')
<div class="bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-7 text-center max-w-3xl">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-50 text-rose-600 text-xs font-bold uppercase tracking-wider mb-2.5">
            <i class="fa-solid fa-users"></i> {{ \App\Models\Setting::getSiteName() }}Real Estate Network
        </span>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
            Direktori Agen Properti Profesional
        </h1>
        <p class="mt-1.5 text-xs sm:text-sm text-slate-500 leading-relaxed">
            Konsultasikan jual, beli, dan sewa properti Anda dengan konsultan berlisensi AREBI yang memiliki rekam jejak terbukti dan pemahaman mendalam tentang pasar lokal.
        </p>

        <!-- Search Bar -->
        <form action="{{ route('agents.index') }}" method="GET" class="mt-4 sm:mt-5 flex flex-col sm:flex-row gap-2 max-w-xl mx-auto">
            <input type="text"
                   name="q"
                   value="{{ $search }}"
                   placeholder="Cari nama agen atau kantor cabang..."
                   class="flex-1 px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:bg-white focus:outline-none">
            <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl shadow-sm transition-all flex items-center justify-center gap-2">
                <i class="fa-solid fa-magnifying-glass"></i> Cari Agen
            </button>
        </form>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
    @if($agents->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
            @foreach($agents as $agent)
                <x-agent-card :agent="$agent" />
            @endforeach
        </div>

        <div class="mt-8">
            {{ $agents->links() }}
        </div>
    @else
        <div class="bg-white rounded-3xl border border-slate-200 p-12 text-center max-w-md mx-auto">
            <i class="fa-solid fa-user-xmark text-4xl text-slate-300 mb-3"></i>
            <h3 class="text-base font-bold text-slate-800">Tidak ada agen ditemukan</h3>
            <p class="text-xs text-slate-500 mt-1">Coba gunakan kata kunci pencarian yang berbeda.</p>
            <a href="{{ route('agents.index') }}" class="mt-4 inline-block px-4 py-2 bg-rose-600 text-white text-xs font-bold rounded-xl">
                Reset Pencarian
            </a>
        </div>
    @endif
</div>
@endsection

