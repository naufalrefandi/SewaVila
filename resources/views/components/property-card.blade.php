@props(['property', 'actions' => null])

@php
    $waText = urlencode("Halo, saya tertarik dengan listing: " . $property->title . " (Harga: " . $property->formatted_price . ") di " . route('properties.show', $property->slug));
    $waNumber = $property->agent ? $property->agent->clean_phone : '6281298765432';

    $formattedImages = $property->formatted_images;
    $cardImages = [];
    foreach ($formattedImages as $item) {
        $cardImages[] = is_array($item) ? ($item['url'] ?? '') : \App\Models\Property::getImageUrl((string) $item);
    }
    $cardImages = array_values(array_filter($cardImages));
    if (empty($cardImages)) {
        $cardImages = [$property->primary_image];
    }

    $videoInfo = $property->video_embed_info;
    $videoUrl = $videoInfo['direct_url'] ?? $property->video_url;
    $embedVideoUrl = $videoInfo['embed_url'] ?? null;
    $isVideoEmbed = in_array($videoInfo['type'] ?? '', ['youtube', 'tiktok', 'instagram']) && !empty($embedVideoUrl);
@endphp

<div class="group bg-white rounded-2xl border border-slate-200/90 overflow-hidden shadow-sm hover:shadow-xl hover:border-pink-300 hover:-translate-y-1 transition-all duration-300 flex flex-col h-auto md:h-full"
     style="font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;"
     x-data="{
        fav: typeof isFavorite === 'function' ? isFavorite({{ $property->id }}) : false,
        comp: typeof isCompared === 'function' ? isCompared({{ $property->id }}) : false,
        copiedCode: false,
        showGalleryModal: false,
        showVideoModal: false,
        galleryIndex: 0,
        galleryImages: @js($cardImages),
        openGallery(idx = 0) {
            this.galleryIndex = idx;
            if (!this.showGalleryModal && !this.showVideoModal) {
                window.history.pushState({ cardModal: {{ $property->id }} }, '');
            }
            this.showGalleryModal = true;
        },
        openVideo() {
            if (!this.showGalleryModal && !this.showVideoModal) {
                window.history.pushState({ cardModal: {{ $property->id }} }, '');
            }
            this.showVideoModal = true;
        },
        closeModals(fromPopState = false) {
            const wasOpen = this.showGalleryModal || this.showVideoModal;
            this.showGalleryModal = false;
            this.showVideoModal = false;
            if (wasOpen && !fromPopState && window.history.state && window.history.state.cardModal === {{ $property->id }}) {
                window.history.back();
            }
        },
        prevPhoto() {
            this.galleryIndex = (this.galleryIndex - 1 + this.galleryImages.length) % this.galleryImages.length;
        },
        nextPhoto() {
            this.galleryIndex = (this.galleryIndex + 1) % this.galleryImages.length;
        },
        async downloadImage(url, idx) {
            try {
                const res = await fetch(url);
                const blob = await res.blob();
                const blobUrl = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = blobUrl;
                a.download = '{{ $property->slug ?: 'properti' }}-foto-' + (idx + 1) + '.jpg';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(blobUrl);
            } catch (e) {
                window.open(url, '_blank');
            }
        }
     }"
     @popstate.window="if (showGalleryModal || showVideoModal) closeModals(true)"
     @keydown.window.escape="if (showGalleryModal || showVideoModal) closeModals()"
     @keydown.window.arrow-left="if (showGalleryModal) prevPhoto()"
     @keydown.window.arrow-right="if (showGalleryModal) nextPhoto()">
    <!-- Image Preview -->
    <div class="relative aspect-[16/10] overflow-hidden bg-slate-100 cursor-pointer"
         @click="openGallery(0)">
        <img src="{{ $property->primary_image }}"
             alt="{{ $property->title }}"
             loading="lazy"
             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">

        <!-- Ribbon / Pita Status Transaksi & Kode Listing (Transparan / Frosted Glass Tipis) -->
        <div class="absolute top-0 left-0 z-10 flex items-center" @click.stop>
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-br-2xl bg-black/35 backdrop-blur-md text-white shadow-sm border-r border-b border-white/15">
                <!-- Label Dijual / Disewa -->
                <span class="inline-flex items-center gap-1.5 text-[11px] font-black uppercase tracking-wider text-white">
                    <i class="fa-solid {{ $property->listing_type === 'disewa' ? 'fa-key' : 'fa-tag' }} text-[9px] text-pink-300"></i>
                    <span>{{ $property->listing_type === 'disewa' ? 'Disewa' : ($property->listing_type === 'dijual_disewa' ? 'Jual & Sewa' : 'Dijual') }}</span>
                </span>

                <!-- Kode Listing di sebelah label (Alpha Pill Transparan Halus) -->
                @if($property->property_code)
                    <button type="button"
                            @click.prevent.stop="navigator.clipboard.writeText('{{ $property->property_code }}'); copiedCode = true; setTimeout(() => copiedCode = false, 1600)"
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-white/15 hover:bg-white/25 border border-white/20 backdrop-blur-xs text-white font-mono text-[10px] font-bold tracking-wider transition-colors cursor-pointer pointer-events-auto"
                            title="Klik untuk salin Kode: {{ $property->property_code }}">
                        <i class="fa-solid fa-barcode text-pink-200 text-[9px]"></i>
                        <span x-show="!copiedCode">{{ $property->property_code }}</span>
                        <span x-show="copiedCode" x-cloak class="text-emerald-300 font-sans font-extrabold flex items-center gap-0.5">
                            <i class="fa-solid fa-check text-[8px]"></i> Tersalin!
                        </span>
                    </button>
                @endif
            </div>
        </div>

        <!-- Top Right Actions (Favorite & Compare) -->
        <div class="absolute top-3 right-3 flex items-center gap-1.5 z-10" @click.stop>
            <button type="button"
                    @click.prevent.stop="if (typeof toggleCompare === 'function') toggleCompare({{ $property->id }}); comp = !comp;"
                    :class="comp ? 'bg-pink-600 text-white' : 'bg-white/90 text-slate-700 hover:bg-white hover:text-pink-600'"
                    class="w-8 h-8 rounded-full flex items-center justify-center shadow-md backdrop-blur-xs transition-colors cursor-pointer"
                    title="Bandingkan Properti">
                <i class="fa-solid fa-code-compare text-xs"></i>
            </button>
            <button type="button"
                    @click.prevent.stop="if (typeof toggleFavorite === 'function') toggleFavorite({{ $property->id }}); fav = !fav;"
                    :class="fav ? 'bg-pink-600 text-white' : 'bg-white/90 text-slate-700 hover:bg-white hover:text-pink-600'"
                    class="w-8 h-8 rounded-full flex items-center justify-center shadow-md backdrop-blur-xs transition-colors cursor-pointer"
                    title="Simpan Properti">
                <i :class="fav ? 'fa-solid fa-heart text-xs' : 'fa-regular fa-heart text-xs'"></i>
            </button>
        </div>

        <!-- Bottom Right Image Indicator: Video & Photos count (Clickable Popup) -->
        <div class="absolute bottom-2.5 right-3 flex items-center gap-1.5 text-xs text-white drop-shadow-md z-10" @click.stop>
            @if($videoUrl)
                <button type="button"
                        @click.prevent.stop="openVideo()"
                        class="bg-slate-900/80 hover:bg-slate-900 backdrop-blur-xs px-2.5 py-1 rounded-full font-bold flex items-center gap-1 text-[10px] transition-transform hover:scale-105 cursor-pointer shadow-sm"
                        title="Putar Video Properti">
                    <i class="fa-solid fa-play text-[8px]"></i> Video
                </button>
            @endif
            @if(count($cardImages) > 0)
                <button type="button"
                        @click.prevent.stop="openGallery(0)"
                        class="bg-slate-900/80 hover:bg-slate-900 backdrop-blur-xs px-2.5 py-1 rounded-full font-medium flex items-center gap-1 text-[10px] transition-transform hover:scale-105 cursor-pointer shadow-sm"
                        title="Lihat & Download Foto">
                    <i class="fa-regular fa-images text-[10px]"></i> {{ count($cardImages) }}
                </button>
            @endif
        </div>
    </div>

    <!-- Card Content -->
    <div class="p-3.5 sm:p-4 flex-1 flex flex-col">
        <div>
            <!-- Marketing Badges Row -->
            @if($property->is_featured || $property->is_urgent || $property->is_verified || $property->has_discount || $property->is_new_launch)
                <div class="flex items-center gap-1.5 flex-wrap mb-1.5">
                    @if($property->is_featured)
                        <span class="bg-pink-600 text-white text-[11px] font-black px-2 py-0.5 rounded flex items-center gap-1 shadow-2xs">
                            <i class="fa-solid fa-star text-white text-[9px]"></i> Unggulan
                        </span>
                    @endif

                    @if($property->is_urgent)
                        <span class="bg-pink-600 text-white text-[11px] font-black px-2 py-0.5 rounded flex items-center gap-1 shadow-2xs">
                            <i class="fa-solid fa-fire text-white text-[9px]"></i> BU
                        </span>
                    @endif

                    @if($property->is_verified)
                        <span class="bg-pink-50 text-pink-700 border border-pink-200 text-[11px] font-bold px-2 py-0.5 rounded flex items-center gap-1">
                            <i class="fa-solid fa-circle-check text-pink-600 text-[9px]"></i> Verified
                        </span>
                    @endif

                    @if($property->has_discount)
                        <span class="bg-pink-50 text-pink-700 border border-pink-200 text-[11px] font-bold px-2 py-0.5 rounded flex items-center gap-1">
                            <i class="fa-solid fa-arrow-trend-down text-pink-600 text-[9px]"></i> Turun
                        </span>
                    @endif

                    @if($property->is_new_launch)
                        <span class="bg-pink-50 text-pink-700 border border-pink-200 text-[11px] font-bold px-2 py-0.5 rounded">
                            Baru
                        </span>
                    @endif
                </div>
            @endif

            <!-- Title -->
            <h3 class="text-[15px] sm:text-[17px] font-semibold text-slate-900 line-clamp-2 leading-[1.35] tracking-[-0.01em] min-h-[2.6rem] sm:min-h-[2.9rem]">
                {{ $property->title }}
            </h3>

            <!-- Price (Di bawah Judul) -->
            <div class="mt-1.5 flex items-baseline gap-2 flex-wrap">
                <div class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight leading-tight">
                    {{ $property->formatted_price }}
                </div>
                @if($property->has_discount && $property->formatted_original_price)
                    <div class="text-xs text-slate-400 line-through font-medium">
                        {{ $property->formatted_original_price }}
                    </div>
                @endif
            </div>

            @if(in_array($property->property_type, ['tanah', 'tanah_residensial', 'tanah_komersial']) && $property->price_per_meter)
                <div class="mt-0.5 flex items-center gap-1.5 text-xs text-slate-500 font-medium">
                    <span class="text-pink-600 font-semibold flex items-center gap-1">
                        <i class="fa-solid fa-tag text-[10px]"></i> Estimasi:
                    </span>
                    <span class="font-semibold text-slate-800">{{ $property->price_per_meter }}</span>
                </div>
            @elseif($property->monthly_rent_price && $property->listing_type !== 'dijual')
                <div class="mt-0.5 flex items-center gap-1.5 text-xs text-slate-500 font-medium">
                    <span class="text-pink-600 font-semibold flex items-center gap-1">
                        <i class="fa-regular fa-calendar-check text-[10px]"></i> Bisa Bulanan:
                    </span>
                    <span class="font-semibold text-slate-800">Rp {{ number_format($property->monthly_rent_price / 1000000, $property->monthly_rent_price % 1000000 == 0 ? 0 : 1, ',', '.') }} Jt/bln</span>
                </div>
            @endif

            <!-- Location & Tanggal Listing Row -->
            <div class="mt-1.5 flex items-center justify-between gap-2">
                <p class="text-[13px] sm:text-sm font-normal text-slate-500 flex items-center gap-1.5 truncate min-w-0">
                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                    <span class="truncate">{{ $property->district ? $property->district . ', ' : '' }}{{ $property->city }}</span>
                </p>
                <div class="text-xs font-normal text-slate-400 flex items-center gap-1 shrink-0" title="Tanggal Listing">
                    <i class="fa-regular fa-calendar-days text-[11px]"></i>
                    <span>{{ $property->created_at ? $property->created_at->translatedFormat('d M Y') : now()->translatedFormat('d M Y') }}</span>
                </div>
            </div>
        </div>

        <!-- Agent & WhatsApp Row -->
        <div class="mt-auto pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
            @if($property->agent)
                <a href="{{ route('agents.show', $property->agent->slug) }}" class="flex items-center gap-2.5 group/agent min-w-0">
                    <img src="{{ $property->agent->photo ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&q=80' }}"
                         alt="{{ $property->agent->name }}"
                         class="w-8 h-8 rounded-full object-cover ring-1 ring-slate-200 shrink-0">
                    <div class="truncate">
                        <div class="text-xs sm:text-[13px] font-bold text-slate-800 truncate group-hover/agent:text-rose-600 transition-colors">
                            {{ $property->agent->name }}
                        </div>
                        <div class="text-[11px] text-slate-400 truncate">
                            {{ $property->agent->agency_name }}
                        </div>
                    </div>
                </a>
            @else
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-500 text-xs shrink-0">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <div class="text-xs sm:text-[13px] font-semibold text-slate-700 truncate">Official Agent</div>
                </div>
            @endif

            <!-- Direct WhatsApp CTA -->
            <a href="https://wa.me/{{ $waNumber }}?text={{ $waText }}"
               target="_blank"
               rel="noopener noreferrer"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-pink-50 hover:bg-pink-600 text-pink-700 hover:text-white border border-pink-200 text-xs font-bold transition-all shrink-0">
                <i class="fa-brands fa-whatsapp text-sm"></i>
                <span>WA</span>
            </a>
        </div>

        <!-- Full-Width View Details Button (or Custom Dashboard Actions) -->
        @if(isset($actions) && $actions->isNotEmpty())
            <div class="mt-2.5 pt-2.5 border-t border-slate-100">
                {{ $actions }}
            </div>
        @else
            <a href="{{ route('properties.show', $property->slug) }}"
               class="mt-2.5 w-full py-2 px-4 rounded-xl bg-pink-600 hover:bg-pink-700 text-white text-xs sm:text-[13px] font-bold text-center transition-colors shadow-2xs block">
                View Details
            </a>
        @endif
    </div>

    <!-- Teleported Modals to Body (Avoids card overflow/transform clipping) -->
    <template x-teleport="body">
        <div>
            <!-- Lightbox Photo Gallery Popup Modal -->
            <div x-show="showGalleryModal"
                 x-cloak
                 class="fixed inset-0 z-[100] overflow-hidden bg-black/95 backdrop-blur-md flex flex-col justify-between select-none"
                 style="font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;">
                <!-- Top Bar -->
                <div class="px-3 sm:px-6 py-3 sm:py-3.5 flex items-center justify-between gap-2 text-white border-b border-white/10 z-20 bg-black/60 backdrop-blur-xs">
                    <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                        <span class="text-[11px] sm:text-xs font-bold bg-pink-600 px-2.5 sm:px-3 py-1 rounded-xl shrink-0">
                            Foto <span x-text="galleryIndex + 1"></span> / <span x-text="galleryImages.length"></span>
                        </span>
                        <span class="hidden sm:inline-block text-xs sm:text-sm font-semibold text-slate-200 truncate">
                            {{ $property->title }}
                        </span>
                    </div>

                    <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
                        <!-- Download Current Image Button -->
                        <button type="button"
                                @click="downloadImage(galleryImages[galleryIndex], galleryIndex)"
                                class="px-3 py-1.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white text-xs font-bold flex items-center gap-1.5 transition-colors cursor-pointer shrink-0 shadow-sm"
                                title="Download Foto Ini">
                            <i class="fa-solid fa-download text-xs"></i>
                            <span>Download</span>
                        </button>
                        <a :href="galleryImages[galleryIndex]" target="_blank" class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center text-xs text-white shrink-0" title="Buka Resolusi Penuh">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        </a>
                        <button type="button" @click="closeModals()" class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-white/10 hover:bg-rose-600 flex items-center justify-center text-sm text-white transition-colors cursor-pointer shrink-0" title="Tutup">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>

                <!-- Center Image Stage -->
                <div class="relative flex-1 flex items-center justify-center p-4 z-10 overflow-hidden" @click.self="closeModals()">
                    <template x-if="galleryImages.length > 1">
                        <button type="button"
                                @click.stop="prevPhoto()"
                                class="absolute left-2 sm:left-6 z-20 w-11 h-11 rounded-full bg-black/60 hover:bg-pink-600 text-white flex items-center justify-center text-base backdrop-blur-md transition-all cursor-pointer">
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>
                    </template>

                    <div class="max-w-5xl max-h-[75vh] flex items-center justify-center">
                        <img :src="galleryImages[galleryIndex]"
                             alt="{{ $property->title }}"
                             class="max-h-[75vh] max-w-full object-contain rounded-xl shadow-2xl">
                    </div>

                    <template x-if="galleryImages.length > 1">
                        <button type="button"
                                @click.stop="nextPhoto()"
                                class="absolute right-2 sm:right-6 z-20 w-11 h-11 rounded-full bg-black/60 hover:bg-pink-600 text-white flex items-center justify-center text-base backdrop-blur-md transition-all cursor-pointer">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </template>
                </div>

                <!-- Bottom Thumbnail Strip -->
                <div class="px-4 py-3 bg-black/70 border-t border-white/10 overflow-x-auto scrollbar-thin z-20">
                    <div class="flex items-center justify-center gap-2 min-w-max mx-auto">
                        <template x-for="(img, idx) in galleryImages" :key="idx">
                            <button type="button"
                                    @click="galleryIndex = idx"
                                    :class="galleryIndex === idx ? 'ring-2 ring-pink-500 scale-105 opacity-100' : 'opacity-50 hover:opacity-90'"
                                    class="w-16 h-12 rounded-lg overflow-hidden bg-slate-800 transition-all cursor-pointer">
                                <img :src="img" class="w-full h-full object-cover">
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Video Tour Popup Modal -->
            @if($videoUrl)
                <div x-show="showVideoModal"
                     x-cloak
                     class="fixed inset-0 z-[100] overflow-hidden bg-black/90 backdrop-blur-md flex items-center justify-center p-4"
                     style="font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;">
                    <div @click.away="closeModals()" class="max-w-4xl w-full bg-slate-900 rounded-3xl border border-slate-800 overflow-hidden shadow-2xl relative">
                        <div class="px-5 py-3.5 bg-slate-950 flex items-center justify-between border-b border-slate-800 text-white">
                            <div class="flex items-center gap-2 min-w-0">
                                <i class="fa-solid fa-circle-play text-pink-500 text-lg shrink-0"></i>
                                <h3 class="font-bold text-sm truncate">{{ $property->title }}</h3>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                @if(!empty($videoInfo['direct_url']))
                                    <a href="{{ $videoInfo['direct_url'] }}" target="_blank" rel="noopener noreferrer" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-[11px] font-bold text-slate-200 transition-colors flex items-center gap-1.5">
                                        <span>Buka di Tab Baru</span>
                                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                    </a>
                                @endif
                                <button type="button" @click="closeModals()" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-rose-600 flex items-center justify-center text-xs transition-colors cursor-pointer">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        </div>
                        <div class="aspect-video w-full bg-black flex items-center justify-center">
                            <template x-if="showVideoModal">
                                @if($isVideoEmbed)
                                    <iframe src="{{ $embedVideoUrl }}"
                                            class="w-full h-full"
                                            frameborder="0"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                            allowfullscreen></iframe>
                                @else
                                    <video src="{{ $videoUrl }}"
                                           controls
                                           autoplay
                                           playsinline
                                           class="w-full h-full object-contain"></video>
                                @endif
                            </template>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </template>
</div>

