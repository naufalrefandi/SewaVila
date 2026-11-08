@extends('layouts.app')

@section('title', $agent->name . ' - Profil Agen ' . \App\Models\Setting::getSiteName() . '')
@section('meta_description', 'Lihat daftar listing properti dijual dan disewa oleh agen ' . $agent->name . ' dari ' . $agent->office_branch . '.')

@section('content')
<div class="bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 sm:py-6">
        <!-- Breadcrumbs -->
        <nav class="flex text-xs text-slate-500 mb-3 sm:mb-4">
            <a href="{{ route('home') }}" class="hover:text-rose-600">Beranda</a>
            <span class="mx-2 text-slate-400">/</span>
            <a href="{{ route('agents.index') }}" class="hover:text-rose-600">Direktori Agen</a>
            <span class="mx-2 text-slate-400">/</span>
            <span class="text-slate-800 font-semibold">{{ $agent->name }}</span>
        </nav>

        <div class="flex flex-col md:flex-row items-start gap-6">
            <div class="relative shrink-0">
                <img src="{{ $agent->photo ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=300&q=80' }}"
                     alt="{{ $agent->name }}"
                     class="w-28 h-28 sm:w-36 sm:h-36 rounded-3xl object-cover ring-4 ring-slate-100 shadow-md">
                @if($agent->is_verified)
                    <span class="absolute -bottom-1 -right-1 w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center text-sm ring-4 ring-white" title="Verified Agent">
                        <i class="fa-solid fa-check"></i>
                    </span>
                @endif
            </div>

            <div class="flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900">{{ $agent->name }}</h1>
                    @if($agent->is_top_producer)
                        <span class="bg-amber-100 text-amber-800 text-xs font-extrabold px-2.5 py-0.5 rounded-full flex items-center gap-1">
                            <i class="fa-solid fa-crown text-amber-600 text-xs"></i> Top Producer
                        </span>
                    @endif
                </div>

                <p class="text-sm font-bold text-rose-600 mt-1">{{ $agent->position }} &bull; {{ $agent->office_branch }}</p>

                <div class="mt-3 flex flex-wrap items-center gap-4 text-xs text-slate-600">
                    <div class="flex items-center gap-1 font-bold text-amber-600">
                        <i class="fa-solid fa-star"></i> {{ number_format($agent->rating, 1) }} / 5.0 Rating
                    </div>
                    <div>&bull;</div>
                    <div><span class="font-bold text-slate-900">{{ $agent->experience_years }} Tahun</span> Pengalaman</div>
                    <div>&bull;</div>
                    <div><span class="font-bold text-slate-900">{{ $properties->total() }}</span> Listing Aktif</div>
                </div>

                @if($agent->bio)
                    <p class="mt-3 text-xs sm:text-sm text-slate-600 leading-relaxed max-w-3xl">
                        {{ $agent->bio }}
                    </p>
                @endif

                <div class="mt-5 flex flex-wrap items-center gap-3">
                    <a href="https://wa.me/{{ $agent->clean_phone }}?text={{ urlencode('Halo ' . $agent->name . ', saya tertarik dengan listing properti Anda di ' . \App\Models\Setting::getSiteName() . '.') }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition-all flex items-center gap-2">
                        <i class="fa-brands fa-whatsapp text-sm"></i> Chat WhatsApp
                    </a>
                    <a href="tel:{{ $agent->phone }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs rounded-xl transition-all flex items-center gap-2">
                        <i class="fa-solid fa-phone text-xs"></i> {{ $agent->phone }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-extrabold text-slate-900">Listing Properti Oleh {{ $agent->name }}</h2>
        <span class="text-xs text-slate-500 font-semibold">{{ $properties->total() }} Listing</span>
    </div>

    @if($properties->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
            @foreach($properties as $property)
                <x-property-card :property="$property" />
            @endforeach
        </div>

        <div class="mt-8">
            {{ $properties->links() }}
        </div>
    @else
        <div class="bg-white rounded-3xl border border-slate-200 p-8 text-center text-xs text-slate-500">
            Saat ini belum ada listing aktif lainnya dari agen ini.
        </div>
    @endif
</div>
@endsection

