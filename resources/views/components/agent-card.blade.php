@props(['agent'])

<div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between h-full">
    <div>
        <div class="flex items-start gap-4">
            <div class="relative shrink-0">
                <img src="{{ $agent->photo ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=200&q=80' }}"
                     alt="{{ $agent->name }}"
                     class="w-16 h-16 rounded-2xl object-cover ring-2 ring-slate-100 shadow-sm">
                @if($agent->is_verified)
                    <span class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[10px] ring-2 ring-white" title="Agen Terverifikasi">
                        <i class="fa-solid fa-check"></i>
                    </span>
                @endif
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-1.5 flex-wrap">
                    <h3 class="text-base font-bold text-slate-900 truncate hover:text-rose-600 transition-colors">
                        <a href="{{ route('agents.show', $agent->slug) }}">{{ $agent->name }}</a>
                    </h3>
                    @if($agent->is_top_producer)
                        <span class="bg-amber-100 text-amber-800 text-[10px] font-bold px-1.5 py-0.5 rounded-full flex items-center gap-1">
                            <i class="fa-solid fa-crown text-[9px]"></i> Top
                        </span>
                    @endif
                </div>
                <p class="text-xs font-semibold text-rose-600 truncate mt-0.5">{{ $agent->position }}</p>
                <p class="text-xs text-slate-500 truncate flex items-center gap-1 mt-0.5">
                    <i class="fa-solid fa-building text-[10px] text-slate-400"></i>
                    <span>{{ $agent->office_branch }}</span>
                </p>
            </div>
        </div>

        <div class="mt-4 pt-3 border-t border-slate-100 grid grid-cols-3 gap-2 text-center text-xs text-slate-600">
            <div class="bg-slate-50 rounded-lg p-2">
                <div class="font-extrabold text-slate-900">{{ $agent->active_listings_count }}</div>
                <div class="text-[10px] text-slate-400">Listing</div>
            </div>
            <div class="bg-slate-50 rounded-lg p-2">
                <div class="font-extrabold text-slate-900">{{ $agent->experience_years }} Thn</div>
                <div class="text-[10px] text-slate-400">Pengalaman</div>
            </div>
            <div class="bg-slate-50 rounded-lg p-2">
                <div class="font-extrabold text-amber-600 flex items-center justify-center gap-0.5">
                    <i class="fa-solid fa-star text-[10px]"></i> {{ number_format($agent->rating, 1) }}
                </div>
                <div class="text-[10px] text-slate-400">Rating</div>
            </div>
        </div>

        @if($agent->bio)
            <p class="mt-3 text-xs text-slate-500 line-clamp-2 leading-relaxed">
                {{ $agent->bio }}
            </p>
        @endif
    </div>

    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center gap-2">
        <a href="{{ route('agents.show', $agent->slug) }}" class="flex-1 text-center py-2 px-3 rounded-xl border border-slate-200 hover:border-rose-600 hover:bg-rose-50 text-slate-700 hover:text-rose-600 text-xs font-bold transition-all">
            Lihat Listing
        </a>
        <a href="https://wa.me/{{ $agent->clean_phone }}?text={{ urlencode('Halo ' . $agent->name . ', saya ingin konsultasi properti di ' . \App\Models\Setting::getSiteName() . '.') }}"
           target="_blank"
           rel="noopener noreferrer"
           class="flex-1 text-center py-2 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all flex items-center justify-center gap-1.5 shadow-sm">
            <i class="fa-brands fa-whatsapp text-sm"></i> WhatsApp
        </a>
    </div>
</div>

