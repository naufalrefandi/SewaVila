@extends('layouts.app')

@section('title', 'Properti Favorit Tersimpan - ' . \App\Models\Setting::getSiteName() . '')

@section('content')
<div class="bg-white border-b border-slate-200" x-data="{
    favIds: JSON.parse(localStorage.getItem('pn123_favs') || '[]'),
    init() {
        const urlParams = new URLSearchParams(window.location.search);
        const currentIds = urlParams.get('ids') ? urlParams.get('ids').split(',').map(Number) : [];
        const storedIds = this.favIds;

        // Check if sets match
        const isMatch = currentIds.length === storedIds.length && storedIds.every(id => currentIds.includes(id));
        if (!isMatch && storedIds.length > 0) {
            window.location.href = '{{ route('properties.favorites') }}?ids=' + storedIds.join(',');
        }
    }
}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 sm:py-6">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-50 text-rose-600 text-xs font-bold uppercase tracking-wider mb-2">
            <i class="fa-solid fa-heart"></i> Koleksi Favorit Saya
        </span>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">
            Daftar Properti Disimpan
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Pantau dan bandingkan properti yang telah Anda tandai sebagai favorit untuk dikunjungi atau dihubungi.
        </p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
    @if(isset($properties) && $properties->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
            @foreach($properties as $property)
                <x-property-card :property="$property" />
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-3xl border border-slate-200 p-12 text-center max-w-lg mx-auto">
            <div class="w-16 h-16 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center text-2xl mx-auto mb-4">
                <i class="fa-regular fa-heart"></i>
            </div>
            <h3 class="text-base font-bold text-slate-900">Belum ada properti yang disimpan</h3>
            <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                Tandai rumah, apartemen, atau villa yang Anda sukai dengan menekan tombol hati <i class="fa-regular fa-heart text-rose-500"></i> pada listing.
            </p>
            <a href="{{ route('properties.index') }}" class="mt-4 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-rose-600 text-white font-bold text-xs shadow-sm hover:bg-rose-700 transition-all">
                Mulai Cari Properti
            </a>
        </div>
    @endif
</div>
@endsection

