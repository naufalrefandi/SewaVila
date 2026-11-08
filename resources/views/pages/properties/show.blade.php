@extends('layouts.app')

@php
    $siteNameMeta = \App\Models\Setting::getSiteName();
    $coverImgUrl = $property->primary_image;
    if ($coverImgUrl && !str_starts_with($coverImgUrl, 'http://') && !str_starts_with($coverImgUrl, 'https://')) {
        $coverImgUrl = url($coverImgUrl);
    }
    $ogTitleText = $property->title . ' - ' . $property->formatted_price;
    $ogDescText = trim(($property->district ? $property->district . ', ' : '') . $property->city . ' • ' . Str::limit(strip_tags($property->description), 130));
@endphp

@section('title', $property->title . ' - ' . $siteNameMeta)
@section('meta_description', $ogDescText)
@section('og_type', 'article')
@section('og_title', $ogTitleText)
@section('og_description', $ogDescText)
@section('og_image', $coverImgUrl)
@section('og_url', request()->fullUrl())

@section('content')
@php
    $formattedImages = $property->formatted_images;
    $images = [];
    foreach ($formattedImages as $item) {
        $images[] = is_array($item) ? ($item['url'] ?? '') : \App\Models\Property::getImageUrl((string) $item);
    }
    $images = array_values(array_filter($images));
    if (empty($images)) {
        $images = \App\Models\Property::getDefaultImagesForType($property->property_type);
    }
    $firstImage = $images[0];
    $videoInfo = $property->video_embed_info;
    $videoUrl = $videoInfo['direct_url'] ?? $property->video_url;
    $embedVideoUrl = $videoInfo['embed_url'] ?? null;
    $isVideoEmbed = in_array($videoInfo['type'] ?? '', ['youtube', 'tiktok', 'instagram']) && !empty($embedVideoUrl);
    $effectiveAgent = $activeAgent ?? $property->agent;
    $effectiveAgentName = $effectiveAgent ? $effectiveAgent->name : \App\Models\Setting::getOfficialAgentName();
    $effectiveAgentPhone = $effectiveAgent ? $effectiveAgent->phone : \App\Models\Setting::getOfficialAgentPhone();
    $effectiveAgentCleanPhone = $effectiveAgent ? $effectiveAgent->clean_phone : \App\Models\Setting::getCleanOfficialAgentPhone();
    $effectiveAgentOffice = $effectiveAgent ? $effectiveAgent->office_branch : \App\Models\Setting::getOfficialAgentOffice();
    $effectiveAgentPosition = $effectiveAgent ? $effectiveAgent->position : 'Property Consultant';
    $effectiveAgentPhoto = $effectiveAgent ? $effectiveAgent->photo : 'https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=200&q=80';

    // Short share link using property code (redirects to full canonical slug)
    $shareUrl = $property->getShortShareUrl($effectiveAgent);

    // Direct WhatsApp buyer enquiry message
    $waInquiryText = urlencode("Halo {$effectiveAgentName}, saya tertarik dengan info listing: *" . $property->share_headline . "* (Harga: " . $property->formatted_price . ") - " . $shareUrl);
    $waDirectChatUrl = "https://wa.me/{$effectiveAgentCleanPhone}?text=" . $waInquiryText;

    // Clean WhatsApp Broadcast text (matching real broker format)
    $waBroadcastText = $property->generateCleanWaBroadcastText($effectiveAgent, true, $shareUrl);
    $waBroadcastTextWithoutLink = $property->generateCleanWaBroadcastText($effectiveAgent, false, $shareUrl);

    // Multi-channel Share URLs
    $shareTitle = urlencode('*' . $property->share_headline . '* (' . $property->formatted_price . ')');
    $twitterUrl = "https://twitter.com/intent/tweet?text=" . $shareTitle . "&url=" . urlencode($shareUrl);
    $threadsUrl = "https://threads.net/intent/post?text=" . urlencode('*' . $property->share_headline . "* " . $property->formatted_price . " " . $shareUrl);
    $whatsappShareUrl = "https://api.whatsapp.com/send?text=" . urlencode($waBroadcastText);

    // Auto-Generated Captions for Instagram & TikTok
    $cleanCityTag = strtolower(str_replace(' ', '', $property->city));
    $cleanTypeTag = strtolower($property->property_type);
    $igCaption = "✨ " . strtoupper($property->listing_type) . ": " . $property->title . " ✨\n\n"
        . "💰 Harga: " . $property->formatted_price . "\n"
        . ($property->full_unit_info ? "🏢 Unit: " . $property->full_unit_info . "\n" : "")
        . "📍 Lokasi: " . $property->district . ", " . $property->city . "\n\n"
        . "SPESIFIKASI UNIT:\n"
        . ($property->bedrooms ? "🛏️ " . $property->bedrooms . " Kamar Tidur\n" : "")
        . ($property->bathrooms ? "🚿 " . $property->bathrooms . " Kamar Mandi\n" : "")
        . ($property->building_area ? "📐 LB: " . $property->building_area . " m²\n" : "")
        . ($property->land_area ? "🌳 LT: " . $property->land_area . " m²\n" : "")
        . "🛋️ Kondisi: " . $property->furnished_status_label . "\n"
        . "📜 Sertifikat: " . ($property->certificate ?? 'Lengkap') . "\n\n"
        . "Fasilitas & Keunggulan:\n"
        . (is_array($property->facilities) ? "✅ " . implode("\n✅ ", array_slice($property->facilities, 0, 4)) . "\n\n" : "")
        . "Info detail & Jadwal Survey Unit:\n"
        . "📞 Hubungi: " . $effectiveAgentName . " (" . $effectiveAgentPhone . ")\n"
        . "🏢 Kantor: " . $effectiveAgentOffice . "\n"
        . "🔗 Link Listing: " . $shareUrl . "\n\n"
        . "#" . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', \App\Models\Setting::getSiteName())) . " #propertiindonesia #jualproperti #sewaproperti #" . $cleanTypeTag . $cleanCityTag . " #properti" . $cleanCityTag . " #investasiproperti";

    $detailGroups = [];
    $normType = strtolower(trim($property->property_type ?? 'rumah'));

    if (in_array($normType, ['tanah', 'tanah_residensial', 'tanah_komersial'])) {
        // === TANAH ===
        $group1 = [];
        if ($property->land_area) {
            $group1[] = ['label' => 'Luas Tanah', 'value' => $property->land_area . ' m²'];
        }
        if ($property->dimension) {
            $group1[] = ['label' => 'Dimensi (P x L)', 'value' => $property->dimension];
        }
        if ($property->price_per_meter) {
            $group1[] = ['label' => 'Estimasi Harga / m²', 'value' => $property->price_per_meter];
        }

        $group2 = [];
        $group2[] = ['label' => 'Sertifikat / Legalitas', 'value' => $property->certificate ?? 'SHM'];
        if ($property->facing) {
            $cleanFacing = trim(preg_replace('/^hadap\s+/i', '', trim($property->facing)));
            $group2[] = ['label' => 'Arah Hadap', 'value' => $cleanFacing ?: $property->facing];
        }
        if ($property->road_width) {
            $group2[] = ['label' => 'Lebar Akses Jalan', 'value' => $property->road_access_label];
        }

        $detailGroups = array_values(array_filter([$group1, $group2]));
    } elseif (in_array($normType, ['gudang', 'pabrik'])) {
        // === GUDANG & PABRIK ===
        $group1 = [];
        if ($property->building_area) {
            $group1[] = ['label' => 'Luas Bangunan', 'value' => $property->building_area . ' m²'];
        }
        if ($property->land_area) {
            $group1[] = ['label' => 'Luas Tanah', 'value' => $property->land_area . ' m²'];
        }
        if ($property->dimension) {
            $group1[] = ['label' => 'Dimensi (P x L)', 'value' => $property->dimension];
        }
        if ($property->floors) {
            $group1[] = ['label' => 'Jumlah Lantai / Office', 'value' => $property->floors . ' Lantai'];
        }

        $group2 = [];
        if ($property->electricity) {
            $cleanWatt = preg_replace('/[^0-9]/', '', (string) $property->electricity);
            $group2[] = ['label' => 'Daya Listrik', 'value' => ($cleanWatt ? number_format((float) $cleanWatt, 0, ',', '.') : $property->electricity) . ' Watt'];
        }
        if ($property->bathrooms) {
            $group2[] = ['label' => 'Kamar Mandi / Toilet', 'value' => $property->bathrooms_label . ' KM'];
        }
        if ($property->road_width) {
            $group2[] = ['label' => 'Akses Jalan / Kontainer', 'value' => $property->road_width];
        } elseif ($property->carports) {
            $group2[] = ['label' => 'Akses Kontainer / Parkir', 'value' => $property->carports . ' Truk / Mobil'];
        }

        $group3 = [];
        $group3[] = ['label' => 'Sertifikat / Legalitas', 'value' => $property->certificate ?? 'HGB'];
        if ($property->facing) {
            $cleanFacing = trim(preg_replace('/^hadap\s+/i', '', trim($property->facing)));
            $group3[] = ['label' => 'Arah Hadap', 'value' => $cleanFacing ?: $property->facing];
        }

        $detailGroups = array_values(array_filter([$group1, $group2, $group3]));
    } elseif ($normType === 'apartemen') {
        // === APARTEMEN ===
        $group1 = [];
        if ($property->unit_type) {
            $group1[] = ['label' => 'Tipe Unit', 'value' => str_replace(' ', '', $property->unit_type)];
        }
        if ($property->building_area) {
            $group1[] = ['label' => 'Luas Bangunan / Semigross', 'value' => $property->building_area . ' m²'];
        }
        if ($property->bedrooms !== null && $property->bedrooms !== '') {
            $group1[] = [
                'label' => 'Kamar Tidur',
                'value' => $property->bedrooms == 0 ? 'Studio' : ($property->bedrooms_label . ' KT'),
            ];
        }
        if ($property->bathrooms) {
            $group1[] = ['label' => 'Kamar Mandi', 'value' => $property->bathrooms_label . ' KM'];
        }

        $group2 = [];
        if ($property->unit_tower) {
            $cleanTower = trim(preg_replace('/^tower\s+/i', '', trim($property->unit_tower)));
            $group2[] = ['label' => 'Tower', 'value' => $cleanTower ?: $property->unit_tower];
        }
        if ($property->unit_floor) {
            $cleanFloor = trim(preg_replace('/^(lantai|lt\.?)\s+/i', '', trim((string) $property->unit_floor)));
            $group2[] = ['label' => 'Lantai Ke', 'value' => $cleanFloor ?: $property->unit_floor];
        }
        if ($property->unit_number) {
            $cleanNo = trim(preg_replace('/^(unit|no\.?)\s+/i', '', trim((string) $property->unit_number)));
            $group2[] = ['label' => 'Nomor Unit', 'value' => $cleanNo ?: $property->unit_number];
        }
        if ($property->unit_view) {
            $cleanView = trim(preg_replace('/^view\s+/i', '', trim($property->unit_view)));
            $group2[] = ['label' => 'Pemandangan / View', 'value' => $cleanView ?: $property->unit_view];
        }
        if ($property->facing) {
            $cleanFacing = trim(preg_replace('/^hadap\s+/i', '', trim($property->facing)));
            $group2[] = ['label' => 'Arah Hadap', 'value' => $cleanFacing ?: $property->facing];
        }

        $group3 = [];
        if ($property->furnished_status) {
            $group3[] = ['label' => 'Kondisi Perabotan', 'value' => $property->furnished_status_label];
        }
        if ($property->electricity) {
            $cleanWatt = preg_replace('/[^0-9]/', '', (string) $property->electricity);
            $group3[] = ['label' => 'Daya Listrik', 'value' => ($cleanWatt ?: $property->electricity) . ' Watt'];
        }
        if ($property->certificate) {
            $group3[] = ['label' => 'Sertifikat / Legalitas', 'value' => $property->certificate];
        }
        if ($property->ipl_info) {
            $group3[] = ['label' => 'Biaya IPL', 'value' => $property->ipl_info];
        }
        if ($property->transfer_fee_info) {
            $group3[] = ['label' => 'Biaya Peralihan', 'value' => $property->transfer_fee_info];
        }

        $detailGroups = array_values(array_filter([$group1, $group2, $group3]));
    } elseif (in_array($normType, ['ruko', 'kantor', 'tempat_usaha', 'gedung'])) {
        // === RUKO / KANTOR / TEMPAT USAHA / GEDUNG ===
        $group1 = [];
        if ($property->building_area) {
            $group1[] = ['label' => 'Luas Bangunan', 'value' => $property->building_area . ' m²'];
        }
        if ($property->land_area) {
            $group1[] = ['label' => 'Luas Tanah', 'value' => $property->land_area . ' m²'];
        }
        if ($property->dimension) {
            $group1[] = ['label' => 'Dimensi (P x L)', 'value' => $property->dimension];
        }
        if ($property->floors) {
            $group1[] = ['label' => 'Jumlah Lantai', 'value' => $property->floors . ' Lantai'];
        }

        $group2 = [];
        if ($property->bedrooms) {
            $group2[] = ['label' => 'Kamar / Ruangan', 'value' => $property->bedrooms_label . ' Ruang'];
        }
        if ($property->bathrooms) {
            $group2[] = ['label' => 'Kamar Mandi / Toilet', 'value' => $property->bathrooms_label . ' KM'];
        }
        if ($property->carports) {
            $group2[] = ['label' => 'Kapasitas Parkir', 'value' => $property->carports . ' Mobil'];
        }
        if ($property->furnished_status) {
            $group2[] = ['label' => 'Kondisi Bangunan', 'value' => $property->furnished_status_label];
        }

        $group3 = [];
        if ($property->certificate) {
            $group3[] = ['label' => 'Sertifikat / Legalitas', 'value' => $property->certificate];
        }
        if ($property->electricity) {
            $cleanWatt = preg_replace('/[^0-9]/', '', (string) $property->electricity);
            $group3[] = ['label' => 'Daya Listrik', 'value' => ($cleanWatt ?: $property->electricity) . ' Watt'];
        }
        if ($property->facing) {
            $cleanFacing = trim(preg_replace('/^hadap\s+/i', '', trim($property->facing)));
            $group3[] = ['label' => 'Arah Hadap', 'value' => $cleanFacing ?: $property->facing];
        }
        if ($property->road_width) {
            $group3[] = ['label' => 'Lebar Jalan / Row', 'value' => $property->road_access_label];
        }

        $detailGroups = array_values(array_filter([$group1, $group2, $group3]));
    } else {
        // === RUMAH / VILLA / KOST / HOTEL ===
        $group1 = [];
        if ($property->land_area) {
            $group1[] = ['label' => 'Luas Tanah', 'value' => $property->land_area . ' m²'];
        }
        if ($property->building_area) {
            $group1[] = ['label' => 'Luas Bangunan', 'value' => $property->building_area . ' m²'];
        }
        if ($property->dimension) {
            $group1[] = ['label' => 'Dimensi (P x L)', 'value' => $property->dimension];
        }
        if ($property->floors) {
            $group1[] = ['label' => 'Jumlah Lantai', 'value' => $property->floors . ' Lantai'];
        }

        $group2 = [];
        if ($property->bedrooms) {
            $ktVal = $property->bedrooms_label . ' KT';
            if ($property->maid_bedrooms) {
                $ktVal .= ' (' . $property->bedrooms . ' Utama + ' . $property->maid_bedrooms . ' ART)';
            }
            $group2[] = ['label' => 'Kamar Tidur', 'value' => $ktVal];
        }
        if ($property->bathrooms) {
            $kmVal = $property->bathrooms_label . ' KM';
            if ($property->maid_bathrooms) {
                $kmVal .= ' (' . $property->bathrooms . ' Utama + ' . $property->maid_bathrooms . ' ART)';
            }
            $group2[] = ['label' => 'Kamar Mandi', 'value' => $kmVal];
        }
        if ($property->carports) {
            $group2[] = ['label' => 'Garasi / Carport', 'value' => $property->carports . ' Mobil'];
        }
        if ($property->furnished_status) {
            $group2[] = ['label' => 'Kondisi Perabotan', 'value' => $property->furnished_status_label];
        }

        $group3 = [];
        if ($property->certificate) {
            $group3[] = ['label' => 'Sertifikat / Legalitas', 'value' => $property->certificate];
        }
        if ($property->electricity) {
            $cleanWatt = preg_replace('/[^0-9]/', '', (string) $property->electricity);
            $group3[] = ['label' => 'Daya Listrik', 'value' => ($cleanWatt ?: $property->electricity) . ' Watt'];
        }
        if ($property->facing) {
            $cleanFacing = trim(preg_replace('/^hadap\s+/i', '', trim($property->facing)));
            $group3[] = ['label' => 'Arah Hadap', 'value' => $cleanFacing ?: $property->facing];
        }
        if ($property->road_width) {
            $group3[] = ['label' => 'Lebar Jalan / Row', 'value' => $property->road_access_label];
        }
        if ($property->flooring_material) {
            $group3[] = ['label' => 'Material Lantai', 'value' => $property->flooring_material];
        }

        $detailGroups = array_values(array_filter([$group1, $group2, $group3]));
    }

    $storyData = [
        'title' => $property->title,
        'price' => $property->formatted_price,
        'listingType' => $property->listing_type,
        'propertyType' => $property->property_type,
        'city' => $property->city,
        'district' => $property->district,
        'location' => ($property->district ? $property->district . ', ' : '') . $property->city,
        'bedrooms' => $property->bedrooms,
        'bathrooms' => $property->bathrooms,
        'landArea' => $property->land_area,
        'buildingArea' => $property->building_area,
        'certificate' => $property->certificate,
        'propertyCode' => $property->property_code,
        'primaryImage' => $property->primary_image,
        'agentName' => $effectiveAgentName,
        'agentPhone' => $effectiveAgentPhone,
        'agentOffice' => $effectiveAgentOffice,
        'agentPosition' => $effectiveAgentPosition,
        'agentPhoto' => $effectiveAgentPhoto,
        'shareUrl' => $shareUrl,
        'slug' => $property->slug,
        'detailGroups' => $detailGroups,
    ];
@endphp

<!-- Detail Page Wrapper -->
<div x-data="propertyDetailComponent()">
    <!-- Detail Header / Breadcrumbs -->
    <div class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-5">
            <!-- Breadcrumbs -->
            <nav class="flex text-xs text-slate-500 mb-3 overflow-x-auto whitespace-nowrap">
                <a href="{{ route('home') }}" class="hover:text-rose-600">Beranda</a>
                <span class="mx-2 text-slate-400">/</span>
                <a href="{{ route('properties.index', ['listing_type' => $property->listing_type]) }}" class="hover:text-rose-600 capitalize">
                    {{ $property->listing_type }}
                </a>
                <span class="mx-2 text-slate-400">/</span>
                <a href="{{ route('properties.index', ['city' => $property->city]) }}" class="hover:text-rose-600">
                    {{ $property->city }}
                </a>
                @if($property->project)
                    <span class="mx-2 text-slate-400">/</span>
                    <span class="text-rose-600 font-bold">{{ $property->project->name }}</span>
                @endif
                <span class="mx-2 text-slate-400">/</span>
                <span class="text-slate-800 font-semibold truncate">{{ Str::limit($property->title, 35) }}</span>
            </nav>

            <!-- Top Title & Action Bar -->
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 lg:gap-6">
                <div class="flex-1 min-w-0">
                    <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-slate-900 leading-snug tracking-[-0.01em]">
                        {{ $property->title }}
                    </h1>
                </div>

                <!-- Right Actions (Price, Share, Favorite, Compare) -->
                <div class="flex flex-col items-start lg:items-end gap-2 shrink-0">
                    <div class="flex flex-col items-start lg:items-end">
                        <div class="flex items-baseline gap-2.5 flex-wrap">
                            <div class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">
                                {{ $property->formatted_price }}
                            </div>
                            @if($property->has_discount && $property->formatted_original_price)
                                <div class="text-base text-slate-400 line-through font-medium">
                                    {{ $property->formatted_original_price }}
                                </div>
                            @endif
                        </div>

                        @if($property->listing_type === 'dijual_disewa' && ($property->sale_price || $property->rent_price))
                            <div class="mt-1.5 flex flex-wrap gap-2 text-xs font-bold">
                                @if($property->sale_price)
                                    <span class="bg-pink-50 text-pink-700 border border-pink-200 px-2.5 py-1 rounded-lg">
                                        <i class="fa-solid fa-tag mr-1"></i> Beli: {{ \App\Models\Property::formatMoney($property->sale_price) }}
                                    </span>
                                @endif
                                @if($property->rent_price)
                                    <span class="bg-pink-50 text-pink-700 border border-pink-200 px-2.5 py-1 rounded-lg">
                                        <i class="fa-regular fa-calendar mr-1"></i> Sewa: {{ \App\Models\Property::formatMoney($property->rent_price, $property->rent_period ?: 'th') }}
                                    </span>
                                @endif
                            </div>
                        @endif

                        @if($property->monthly_rent_price)
                            <div class="mt-1 text-xs font-bold text-pink-700 bg-pink-50 border border-pink-200 px-2.5 py-1 rounded-lg">
                                <i class="fa-regular fa-calendar-check mr-1"></i> Pilihan Sewa Bulanan: Rp {{ number_format($property->monthly_rent_price / 1000000, $property->monthly_rent_price % 1000000 == 0 ? 0 : 1, ',', '.') }} Jt/bln
                            </div>
                        @endif
                    </div>

                    @if($property->property_type === 'tanah' && $property->price_per_meter)
                        <div class="text-xs font-bold text-pink-700 bg-pink-50 border border-pink-200 px-3 py-1 rounded-full">
                            <i class="fa-solid fa-tag text-pink-600 mr-1"></i> Estimasi Harga: <span class="text-slate-900 font-black">{{ $property->price_per_meter }}</span>
                        </div>
                    @endif

                    <!-- Multi-Channel Social Media Share Toolbar (Chat WA = Teks, Story WA = Gambar) -->
                    <div class="mt-1.5 flex flex-wrap items-center gap-2">
                        <!-- WhatsApp Chat (Teks) -->
                        <a href="{{ $whatsappShareUrl }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold flex items-center gap-1.5 shadow-xs transition-all hover:scale-105"
                           title="Share ke Chat WhatsApp (Pesan Teks)">
                            <i class="fa-brands fa-whatsapp text-sm"></i>
                            <span>Chat WA (Teks)</span>
                        </a>

                        <!-- WhatsApp Story / Status (Gambar) -->
                        <button type="button"
                                @click="openStoryModal()"
                                class="px-3.5 py-2 rounded-xl bg-gradient-to-r from-pink-600 to-rose-600 hover:from-pink-700 hover:to-rose-700 text-white text-xs font-bold flex items-center gap-1.5 shadow-xs transition-all hover:scale-105"
                                title="Share ke Status WA & IG Story (Poster Gambar)">
                            <i class="fa-solid fa-photo-film text-sm"></i>
                            <span>Story WA (Gambar)</span>
                        </button>

                        <!-- Twitter / X Direct Share -->
                        <a href="{{ $twitterUrl }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="w-8 h-8 rounded-xl bg-black hover:bg-slate-800 text-white flex items-center justify-center text-xs shadow-xs transition-transform hover:scale-110"
                           title="Share ke X / Twitter">
                            <i class="fa-brands fa-x-twitter"></i>
                        </a>

                        <!-- Threads Direct Share -->
                        <a href="{{ $threadsUrl }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="w-8 h-8 rounded-xl bg-slate-900 hover:bg-slate-800 text-white flex items-center justify-center text-xs shadow-xs transition-transform hover:scale-110"
                           title="Share ke Threads">
                            <i class="fa-brands fa-threads"></i>
                        </a>

                        <!-- Bandingkan -->
                        <button @click="toggleCompare({{ $property->id }}); comp = !comp;"
                                :class="comp ? 'bg-pink-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-pink-50 hover:text-pink-600'"
                                class="px-2.5 py-1.5 rounded-xl text-xs font-bold flex items-center gap-1 transition-all"
                                title="Bandingkan Properti">
                            <i class="fa-solid fa-code-compare"></i>
                        </button>

                        <!-- Favorit -->
                        <button @click="toggleFavorite({{ $property->id }}); fav = !fav;"
                                :class="fav ? 'bg-pink-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-pink-50 hover:text-pink-600'"
                                class="px-2.5 py-1.5 rounded-xl text-xs font-bold flex items-center gap-1 transition-all"
                                title="Simpan Properti">
                            <i :class="fav ? 'fa-solid fa-heart' : 'fa-regular fa-heart'"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Full-Screen Interactive Lightbox Gallery Modal -->
    <div x-show="showGalleryModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-hidden bg-black/95 backdrop-blur-md flex flex-col justify-between select-none">
        
        <!-- Top Bar -->
        <div class="px-3 sm:px-6 py-3 sm:py-4 flex items-center justify-between gap-2 text-white border-b border-white/10 z-20 bg-black/60 backdrop-blur-xs">
            <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                <span class="text-[11px] sm:text-sm font-bold bg-pink-600 px-2.5 sm:px-3 py-1 rounded-xl shrink-0">
                    Foto <span x-text="galleryIndex + 1"></span> / <span x-text="galleryImages.length"></span>
                </span>
                <span class="hidden sm:inline-block text-xs sm:text-sm font-medium text-slate-300 truncate max-w-xs sm:max-w-md">
                    {{ $property->title }}
                </span>
            </div>

            <div class="flex items-center gap-1.5 sm:gap-3 shrink-0">
                <span class="hidden md:inline-block text-[11px] text-slate-400">
                    Gunakan panah ◀ ▶ untuk navigasi, Esc untuk keluar
                </span>
                <button type="button"
                        @click="downloadPhoto(galleryImages[galleryIndex], galleryIndex)"
                        class="px-3 py-1.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white text-xs font-bold flex items-center gap-1.5 transition-colors cursor-pointer shrink-0 shadow-sm"
                        title="Download Foto Ini">
                    <i class="fa-solid fa-download text-xs"></i>
                    <span>Download</span>
                </button>
                <a :href="galleryImages[galleryIndex]" target="_blank" class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center text-xs text-white shrink-0" title="Buka Gambar Resolusi Penuh">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </a>
                <button @click="closeModals()" class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-white/10 hover:bg-rose-600 flex items-center justify-center text-sm text-white transition-colors shrink-0" title="Tutup Galeri">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>

        <!-- Center Stage: Current Image with Prev/Next buttons -->
        <div class="relative flex-1 flex items-center justify-center p-4 z-10 overflow-hidden" @click.self="closeModals()">
            <!-- Prev Button -->
            <button type="button"
                    @click="prevPhoto()"
                    class="absolute left-2 sm:left-6 z-20 w-12 h-12 rounded-full bg-black/60 hover:bg-rose-600 text-white flex items-center justify-center text-lg backdrop-blur-md transition-all hover:scale-110">
                <i class="fa-solid fa-chevron-left"></i>
            </button>

            <!-- Current Full Image (unrestricted aspect ratio) -->
            <div class="max-w-5xl max-h-[75vh] flex items-center justify-center">
                <img :src="galleryImages[galleryIndex]"
                     alt="{{ $property->title }}"
                     class="max-h-[75vh] max-w-full object-contain rounded-xl shadow-2xl transition-all duration-200">
            </div>

            <!-- Next Button -->
            <button type="button"
                    @click="nextPhoto()"
                    class="absolute right-2 sm:right-6 z-20 w-12 h-12 rounded-full bg-black/60 hover:bg-rose-600 text-white flex items-center justify-center text-lg backdrop-blur-md transition-all hover:scale-110">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>

        <!-- Bottom Thumbnail Strip -->
        <div class="px-4 py-3 bg-black/70 border-t border-white/10 overflow-x-auto scrollbar-thin z-20">
            <div class="flex items-center justify-center gap-2 min-w-max mx-auto">
                <template x-for="(img, idx) in galleryImages" :key="idx">
                    <button type="button"
                            @click="galleryIndex = idx"
                            :class="galleryIndex === idx ? 'ring-2 ring-rose-500 scale-105 opacity-100' : 'opacity-50 hover:opacity-90'"
                            class="w-16 h-12 rounded-lg overflow-hidden bg-slate-800 transition-all">
                        <img :src="img" class="w-full h-full object-cover">
                    </button>
                </template>
            </div>
        </div>
    </div>

    <!-- Video Tour Modal -->
    @if($videoUrl)
        <div x-show="showVideoModal"
             x-cloak
             class="fixed inset-0 z-[100] overflow-hidden bg-black/90 backdrop-blur-md flex items-center justify-center p-4">
            <div @click.away="closeModals()" class="max-w-4xl w-full bg-slate-900 rounded-3xl border border-slate-800 overflow-hidden shadow-2xl relative">
                <div class="px-5 py-3.5 bg-slate-950 flex items-center justify-between border-b border-slate-800 text-white">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-play text-rose-500 text-lg"></i>
                        <h3 class="font-extrabold text-sm truncate max-w-md">Video Tour: {{ $property->title }}</h3>
                    </div>
                    <div class="flex items-center gap-2">
                        @if(!empty($videoInfo['direct_url']))
                            <a href="{{ $videoInfo['direct_url'] }}" target="_blank" rel="noopener noreferrer" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-[11px] font-bold text-slate-200 transition-colors flex items-center gap-1.5">
                                <span>Buka di Tab Baru</span>
                                <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                            </a>
                        @endif
                        <button @click="closeModals()" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-rose-600 flex items-center justify-center text-xs transition-colors">
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

    <!-- Modal Social Media Content Generator (WhatsApp Story Gambar, WhatsApp Chat Teks, Instagram & TikTok) -->
    <div x-show="showSocialModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="closeModals()" class="bg-white rounded-3xl border border-slate-200 p-5 sm:p-7 max-w-xl w-full shadow-2xl relative my-8">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-pink-600 to-rose-600 text-white flex items-center justify-center text-base shadow-sm">
                        <i class="fa-solid fa-share-nodes"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-sm">Bagikan Listing Properti</h3>
                        <p class="text-[11px] text-slate-400">Pilih format: <strong>Gambar</strong> untuk Story / Status WA, atau <strong>Teks</strong> untuk Chat WA</p>
                    </div>
                </div>
                <button @click="closeModals()" class="p-1 text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Agent Attribution Bar -->
            <div class="mb-4 p-3 bg-pink-50/70 rounded-2xl border border-pink-100 flex items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-2.5 min-w-0">
                    <img src="{{ $effectiveAgentPhoto }}" alt="{{ $effectiveAgentName }}" class="w-9 h-9 rounded-xl object-cover shrink-0 ring-1 ring-pink-200">
                    <div class="min-w-0">
                        <div class="font-extrabold text-slate-900 truncate flex items-center gap-1">
                            <span>{{ $effectiveAgentName }}</span>
                            <span class="text-[9px] bg-pink-600 text-white px-1.5 py-0.2 rounded-full font-bold">Listing Agent</span>
                        </div>
                        <div class="text-[10px] text-slate-500 truncate">{{ $effectiveAgentOffice }} &bull; WA: {{ $effectiveAgentPhone }}</div>
                    </div>
                </div>
                <a href="{{ $waDirectChatUrl }}" target="_blank" class="shrink-0 px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[10px] flex items-center gap-1 transition-all">
                    <i class="fa-brands fa-whatsapp"></i> Chat Agen
                </a>
            </div>

            <!-- Tab Switcher: Story Gambar vs Chat WA Teks vs Caption Medsos -->
            <div class="flex items-center gap-2 border-b border-slate-200 mb-4 text-xs font-bold">
                <button type="button"
                        @click="shareTab = 'story'; if (!storyImageUrl) generateStoryImage();"
                        class="pb-2.5 px-2 border-b-2 transition-all flex items-center gap-1.5"
                        :class="shareTab === 'story' ? 'border-pink-600 text-pink-700 font-extrabold' : 'border-transparent text-slate-400 hover:text-slate-600'">
                    <i class="fa-solid fa-image text-pink-600"></i>
                    <span>Story / Status WA (Gambar)</span>
                </button>
                <button type="button"
                        @click="shareTab = 'wa'"
                        class="pb-2.5 px-2 border-b-2 transition-all flex items-center gap-1.5"
                        :class="shareTab === 'wa' ? 'border-emerald-600 text-emerald-700 font-extrabold' : 'border-transparent text-slate-400 hover:text-slate-600'">
                    <i class="fa-brands fa-whatsapp text-emerald-600"></i>
                    <span>Chat WA (Teks)</span>
                </button>
                <button type="button"
                        @click="shareTab = 'ig'"
                        class="pb-2.5 px-2 border-b-2 transition-all flex items-center gap-1.5"
                        :class="shareTab === 'ig' ? 'border-purple-600 text-purple-700 font-extrabold' : 'border-transparent text-slate-400 hover:text-slate-600'">
                    <i class="fa-brands fa-instagram text-purple-600"></i>
                    <span>Caption Medsos</span>
                </button>
            </div>

            <!-- TAB 1: STORY / STATUS WA (GAMBAR) -->
            <div x-show="shareTab === 'story'" class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <span class="font-extrabold text-slate-900 text-xs sm:text-sm">Poster Gambar Vertikal (9:16)</span>
                        <p class="text-[11px] text-slate-500">Pas untuk Status WhatsApp, IG Story &amp; Facebook Story</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button"
                                @click="downloadStoryImage()"
                                :disabled="storyGenerating || !storyImageUrl"
                                class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs flex items-center gap-1.5 shadow-sm transition-all disabled:opacity-50"
                                title="Download Gambar PNG Resolusi Tinggi">
                            <i class="fa-solid fa-download text-xs"></i>
                            <span>Download Gambar</span>
                        </button>
                        <button type="button"
                                @click="shareToWhatsAppStory()"
                                :disabled="storyGenerating || !storyImageUrl"
                                class="px-3.5 py-1.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-extrabold text-xs flex items-center gap-1.5 shadow-md transition-all disabled:opacity-50"
                                title="Share ke Status WA via HP">
                            <i class="fa-brands fa-whatsapp text-sm"></i>
                            <span>Share ke Status WA</span>
                        </button>
                    </div>
                </div>

                <!-- Preview Area -->
                <div class="relative bg-slate-950 rounded-2xl p-3 flex justify-center items-center min-h-[360px] max-h-[440px] overflow-hidden">
                    <div x-show="storyGenerating" class="flex flex-col items-center gap-2 text-white text-xs font-semibold">
                        <i class="fa-solid fa-circle-notch fa-spin text-2xl text-pink-500"></i>
                        <span>Sedang membuat gambar poster story...</span>
                    </div>
                    <img x-show="!storyGenerating && storyImageUrl"
                         :src="storyImageUrl"
                         alt="Story Poster Preview"
                         class="max-h-[420px] rounded-xl shadow-2xl object-contain border border-slate-800">
                </div>

                <div class="p-3 bg-pink-50/70 rounded-xl border border-pink-100 flex items-center justify-between gap-3 text-xs">
                    <div class="flex items-center gap-2 text-slate-700">
                        <i class="fa-solid fa-circle-info text-pink-600"></i>
                        <span class="text-[11px]">Klik <strong>Share ke Status WA</strong> &rarr; pilih WhatsApp &rarr; <strong>Status Saya</strong> (keterangan URL listing otomatis disertakan &amp; disalin ke clipboard).</span>
                    </div>
                    <button type="button"
                            @click="copyLink()"
                            class="shrink-0 text-pink-600 font-bold hover:underline text-[11px] flex items-center gap-1">
                        <i class="fa-solid fa-link text-[10px]"></i>
                        <span x-text="copiedLink ? 'Tersalin!' : 'Salin Link Listing'"></span>
                    </button>
                </div>
            </div>

            <!-- TAB 2: CHAT WA (TEKS) -->
            <div x-show="shareTab === 'wa'" x-cloak class="space-y-3">
                <!-- Cover Image Preview Card for Share -->
                <div class="flex items-center gap-3 p-2.5 bg-white rounded-xl border border-slate-200 shadow-2xs">
                    <img src="{{ $firstImage }}" alt="{{ $property->title }}" class="w-16 h-12 rounded-lg object-cover shrink-0 border border-slate-100">
                    <div class="min-w-0 flex-1">
                        <div class="text-xs font-bold text-slate-900 truncate">{{ $property->title }}</div>
                        <div class="text-[11px] font-semibold text-pink-600">{{ $property->formatted_price }}</div>
                        <div class="text-[10px] text-slate-400">Gambar cover otomatis tampil di preview link WA &amp; bisa dikirim langsung</div>
                    </div>
                    <button type="button"
                            @click="shareCoverWithWaText()"
                            class="shrink-0 px-3 py-1.5 rounded-lg bg-pink-600 hover:bg-pink-700 text-white font-bold text-[11px] flex items-center gap-1.5 shadow-2xs transition-all cursor-pointer"
                            title="Share Foto Cover + Teks Broadcast sekaligus">
                        <i class="fa-solid fa-share-from-square text-xs"></i>
                        <span>Share Foto + Teks</span>
                    </button>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs font-bold text-slate-700">
                    <span class="text-[11px] text-slate-500">Pesan broadcast bersih (clean) siap kirim ke grup WA / pembeli:</span>
                    <div class="flex items-center gap-2">
                        <button type="button"
                                @click="copyWaBroadcast()"
                                :class="copiedWa ? 'bg-emerald-600 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                                class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1 shadow-2xs">
                            <i class="fa-solid" :class="copiedWa ? 'fa-check' : 'fa-copy'"></i>
                            <span x-text="copiedWa ? 'Tersalin!' : 'Salin Teks'"></span>
                        </button>
                        <a :href="currentWaShareUrl"
                           target="_blank"
                           class="px-3.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center gap-1.5 shadow-sm transition-all">
                            <i class="fa-brands fa-whatsapp text-sm"></i>
                            <span>Kirim ke Chat WA</span>
                        </a>
                    </div>
                </div>

                <!-- Toggle Sertakan Link Listing Web -->
                <div class="flex items-center justify-between p-2.5 bg-slate-50 rounded-xl border border-slate-200/80 text-xs">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" x-model="includeWebLink" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                        <span class="text-slate-700 font-medium text-[11px]">Sertakan tautan foto/website di akhir pesan</span>
                    </label>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md transition-colors"
                          :class="includeWebLink ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600'"
                          x-text="includeWebLink ? '+ Link Web' : 'Format Clean'"></span>
                </div>

                <div class="p-3 bg-emerald-50/40 border border-emerald-200/80 rounded-xl max-h-56 overflow-y-auto font-mono text-[11px] text-slate-800 whitespace-pre-wrap leading-relaxed select-all"
                     x-text="currentWaText">
                </div>
            </div>

            <!-- TAB 3: CAPTION MEDSOS -->
            <div x-show="shareTab === 'ig'" x-cloak class="space-y-3">
                <div class="flex justify-between items-center text-xs font-bold text-slate-700">
                    <span class="text-[11px] text-slate-500">Format Caption Instagram &amp; TikTok dengan Hashtag:</span>
                    <button type="button"
                            @click="copyCaption()"
                            :class="copiedIg ? 'bg-emerald-600 text-white' : 'bg-slate-900 hover:bg-pink-600 text-white'"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1 shadow-xs">
                        <i class="fa-solid" :class="copiedIg ? 'fa-check' : 'fa-copy'"></i>
                        <span x-text="copiedIg ? 'Caption Tersalin!' : 'Salin Caption Medsos'"></span>
                    </button>
                </div>

                <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl max-h-56 overflow-y-auto font-mono text-[11px] text-slate-700 whitespace-pre-wrap leading-relaxed">
{{ $igCaption }}
                </div>

                <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-400">Buka aplikasi untuk posting:</span>
                    <div class="flex items-center gap-2">
                        <a href="https://instagram.com" target="_blank" class="px-3 py-1 rounded-lg bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold text-xs flex items-center gap-1">
                            <i class="fa-brands fa-instagram"></i> Instagram
                        </a>
                        <a href="https://tiktok.com" target="_blank" class="px-3 py-1 rounded-lg bg-black text-white font-bold text-xs flex items-center gap-1">
                            <i class="fa-brands fa-tiktok"></i> TikTok
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Body & Agent Column -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6 items-start">
            <!-- Left Content Column -->
            <div class="lg:col-span-8 space-y-6 sm:space-y-7">
                <!-- Main Photo Gallery (Swipe Left/Right + Thin Bullets, tanpa Thumbnail di bawah) -->
                <div>
                    <div class="relative aspect-[16/10] sm:aspect-[16/9] max-h-[500px] rounded-3xl overflow-hidden bg-slate-100 shadow-md cursor-pointer group select-none"
                         @click="openGallery(activeImgIndex)"
                         @touchstart.passive="touchStartX = $event.changedTouches[0].screenX"
                         @touchend.passive="touchEndX = $event.changedTouches[0].screenX; handleMainSwipe()">
                        
                        <!-- Main photo (Clean object-cover) -->
                        <img :src="activeImg"
                             alt="{{ $property->title }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">

                        <!-- Left / Right Swipe Navigation Buttons -->
                        @if(count($images) > 1)
                            <button type="button"
                                    @click.prevent.stop="prevMainPhoto()"
                                    class="absolute left-3 sm:left-4 top-1/2 -translate-y-1/2 z-10 w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-black/45 hover:bg-pink-600 text-white flex items-center justify-center text-xs sm:text-sm backdrop-blur-xs shadow-md transition-all cursor-pointer pointer-events-auto"
                                    title="Foto Sebelumnya">
                                <i class="fa-solid fa-chevron-left"></i>
                            </button>
                            <button type="button"
                                    @click.prevent.stop="nextMainPhoto()"
                                    class="absolute right-3 sm:right-4 top-1/2 -translate-y-1/2 z-10 w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-black/45 hover:bg-pink-600 text-white flex items-center justify-center text-xs sm:text-sm backdrop-blur-xs shadow-md transition-all cursor-pointer pointer-events-auto"
                                    title="Foto Berikutnya">
                                <i class="fa-solid fa-chevron-right"></i>
                            </button>

                            <!-- Thin Small Bullet Indicators at Bottom Center -->
                            <div class="absolute bottom-3.5 left-1/2 -translate-x-1/2 z-10 flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-black/30 backdrop-blur-xs pointer-events-auto"
                                 @click.stop>
                                @foreach($images as $idx => $img)
                                    <button type="button"
                                            @click.prevent.stop="selectMainPhoto('{{ $img }}', {{ $idx }})"
                                            :class="activeImgIndex === {{ $idx }} ? 'w-4 bg-white opacity-100' : 'w-1.5 bg-white/55 hover:bg-white/80'"
                                            class="h-1 rounded-full transition-all duration-300 cursor-pointer"
                                            title="Foto {{ $idx + 1 }}"></button>
                                @endforeach
                            </div>
                        @endif

                        <!-- Ribbon / Pita Status Transaksi & Kode Listing (Persis Card Properti) -->
                        <div class="absolute top-0 left-0 z-10 flex items-center">
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 sm:px-3.5 sm:py-2 rounded-br-2xl bg-black/35 backdrop-blur-md text-white shadow-sm border-r border-b border-white/15">
                                <!-- Label Dijual / Disewa -->
                                <span class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-black uppercase tracking-wider text-white">
                                    <i class="fa-solid {{ $property->listing_type === 'disewa' ? 'fa-key' : 'fa-tag' }} text-[10px] sm:text-xs text-pink-300"></i>
                                    <span>{{ $property->listing_type === 'disewa' ? 'Disewa' : ($property->listing_type === 'dijual_disewa' ? 'Jual & Sewa' : 'Dijual') }}</span>
                                </span>

                                <!-- Kode Listing -->
                                @if($property->property_code)
                                    <button type="button"
                                            @click.prevent.stop="navigator.clipboard.writeText('{{ $property->property_code }}'); copiedCode = true; setTimeout(() => copiedCode = false, 1600)"
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-white/15 hover:bg-white/25 border border-white/20 backdrop-blur-xs text-white font-mono text-[11px] sm:text-xs font-bold tracking-wider transition-colors cursor-pointer pointer-events-auto"
                                            title="Klik untuk salin Kode: {{ $property->property_code }}">
                                        <i class="fa-solid fa-barcode text-pink-200 text-[10px]"></i>
                                        <span x-show="!copiedCode">{{ $property->property_code }}</span>
                                        <span x-show="copiedCode" x-cloak class="text-emerald-300 font-sans font-extrabold flex items-center gap-0.5">
                                            <i class="fa-solid fa-check text-[9px]"></i> Tersalin!
                                        </span>
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- Top Right Actions (Compare & Favorite - Persis Card Properti) -->
                        <div class="absolute top-3 right-3 sm:top-4 sm:right-4 flex items-center gap-2 z-10">
                            <button type="button"
                                    @click.prevent.stop="toggleCompare({{ $property->id }}); comp = !comp;"
                                    :class="comp ? 'bg-pink-600 text-white' : 'bg-white/90 text-slate-700 hover:bg-white hover:text-pink-600'"
                                    class="w-9 h-9 sm:w-10 sm:h-10 rounded-full flex items-center justify-center shadow-md backdrop-blur-xs transition-colors cursor-pointer pointer-events-auto"
                                    title="Bandingkan Properti">
                                <i class="fa-solid fa-code-compare text-xs sm:text-sm"></i>
                            </button>
                            <button type="button"
                                    @click.prevent.stop="toggleFavorite({{ $property->id }}); fav = !fav;"
                                    :class="fav ? 'bg-pink-600 text-white' : 'bg-white/90 text-slate-700 hover:bg-white hover:text-pink-600'"
                                    class="w-9 h-9 sm:w-10 sm:h-10 rounded-full flex items-center justify-center shadow-md backdrop-blur-xs transition-colors cursor-pointer pointer-events-auto"
                                    title="Simpan Properti">
                                <i :class="fav ? 'fa-solid fa-heart text-xs sm:text-sm' : 'fa-regular fa-heart text-xs sm:text-sm'"></i>
                            </button>
                        </div>

                        <!-- Bottom Right Image Indicator: Video & Photos count (Persis Card Properti) -->
                        <div class="absolute bottom-3 right-3 sm:bottom-4 sm:right-4 flex items-center gap-2 text-xs text-white drop-shadow-md z-10">
                            @if($videoUrl)
                                <button type="button"
                                        @click.stop="openVideo()"
                                        class="bg-slate-900/80 hover:bg-slate-900 text-white backdrop-blur-xs px-3 py-1.5 rounded-full font-bold flex items-center gap-1.5 text-xs shadow-md transition-transform hover:scale-105 pointer-events-auto cursor-pointer">
                                    <i class="fa-solid fa-play text-[9px]"></i> Video
                                </button>
                            @endif
                            @if(count($images) > 1)
                                <button type="button"
                                        @click.stop="openGallery(activeImgIndex)"
                                        class="bg-slate-900/80 hover:bg-slate-900 text-white backdrop-blur-xs px-3 py-1.5 rounded-full font-medium flex items-center gap-1.5 text-xs shadow-md transition-transform hover:scale-105 pointer-events-auto cursor-pointer">
                                    <i class="fa-regular fa-images text-xs"></i> {{ count($images) }}
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Detail Informasi Properti (Grouped logically by category per property_type) -->
                <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-6 shadow-xs">
                    <h2 class="text-base sm:text-lg font-bold text-slate-900 mb-3.5">
                        Detail Informasi Properti
                    </h2>

                    @if(count($detailGroups) > 0)
                        <div class="space-y-3">
                            @foreach($detailGroups as $chunk)
                                <div class="rounded-xl border border-slate-200 overflow-hidden bg-white">
                                    @foreach($chunk as $row)
                                        <div class="grid grid-cols-2 items-stretch text-xs sm:text-sm {{ !$loop->last ? 'border-b border-slate-200' : '' }}">
                                            <div class="px-4 py-2.5 sm:py-3 text-slate-600 font-medium border-r border-slate-200 flex items-center"
                                                 style="background-color: #f1f5f9;">
                                                {{ $row['label'] }}
                                            </div>
                                            <div class="px-4 py-2.5 sm:py-3 bg-white text-slate-900 font-bold flex items-center">
                                                {{ $row['value'] }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            <!-- Video Tour Player Section (if video exists) -->
            @if($videoUrl)
                <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-xs space-y-3">
                    <div class="flex items-center justify-between">
                        <h2 class="text-base sm:text-lg font-extrabold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-video text-rose-600"></i> Video Listing
                        </h2>
                        @if(!empty($videoInfo['direct_url']))
                            <a href="{{ $videoInfo['direct_url'] }}" target="_blank" rel="noopener noreferrer" class="text-xs font-bold text-rose-600 hover:text-rose-700 flex items-center gap-1.5 transition-colors">
                                <span>Buka Video</span>
                                <i class="fa-solid fa-arrow-up-right-from-square text-[11px]"></i>
                            </a>
                        @endif
                    </div>
                    <div class="aspect-video w-full rounded-2xl overflow-hidden bg-black shadow-inner">
                        @if($isVideoEmbed)
                            <iframe src="{{ $embedVideoUrl }}"
                                    class="w-full h-full"
                                    frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                    allowfullscreen></iframe>
                        @else
                            <video src="{{ $videoUrl }}"
                                   controls
                                   playsinline
                                   preload="metadata"
                                   class="w-full h-full object-contain"></video>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Description -->
            @if(!empty($property->description))
            <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-xs">
                <h2 class="text-base sm:text-lg font-extrabold text-slate-900 mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-align-left text-rose-600"></i> Deskripsi Lengkap
                </h2>
                <div class="text-xs sm:text-sm text-slate-700 leading-relaxed whitespace-pre-line space-y-3">
                    {{ $property->description }}
                </div>
            </div>
            @endif

            <!-- Facilities & Amenities -->
            @if(is_array($property->facilities) && count($property->facilities) > 0)
                <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-xs">
                    <h2 class="text-base sm:text-lg font-extrabold text-slate-900 mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-600"></i> Fasilitas Unit &amp; Kawasan Gedung
                    </h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 sm:gap-3">
                        @foreach($property->facilities as $fac)
                            <div class="flex items-center gap-2.5 p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-xs font-semibold text-slate-800">
                                <span class="w-5 h-5 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 text-[10px]">
                                    <i class="fa-solid fa-check"></i>
                                </span>
                                <span>{{ $fac }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Points of Interest / Akses Lokasi -->
            @if(is_array($property->nearby_points) && count($property->nearby_points) > 0)
                <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-xs">
                    <h2 class="text-base sm:text-lg font-extrabold text-slate-900 mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-map-location-dot text-rose-600"></i> Akses &amp; Fasilitas Terdekat (POI)
                    </h2>
                    <div class="space-y-2">
                        @foreach($property->nearby_points as $point)
                            <div class="flex items-center gap-2.5 p-2.5 rounded-xl bg-rose-50/50 border border-rose-100 text-xs font-medium text-slate-800">
                                <i class="fa-solid fa-diamond-turn-right text-rose-600 text-xs shrink-0"></i>
                                <span>{{ $point }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Embedded Interactive KPR Simulator -->
            @if($property->listing_type === 'dijual')
                <div class="bg-gradient-to-br from-slate-900 to-indigo-950 text-white rounded-2xl p-4 sm:p-6 shadow-md"
                     x-data="{
                        price: {{ $property->price }},
                        dpPercent: 20,
                        interest: 5.5,
                        tenor: 20,
                        get dpAmount() { return (this.price * this.dpPercent) / 100; },
                        get loanAmount() { return this.price - this.dpAmount; },
                        get monthlyInstallment() {
                            const r = (this.interest / 100) / 12;
                            const n = this.tenor * 12;
                            if (r === 0) return Math.round(this.loanAmount / n);
                            return Math.round((this.loanAmount * r) / (1 - Math.pow(1 + r, -n)));
                        },
                        formatIdr(num) {
                            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(num);
                        }
                     }">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <span class="text-xs font-bold text-amber-400 uppercase tracking-wider">
                                {{ $property->property_type === 'tanah' ? 'Simulasi Pembiayaan Tanah (KPT)' : ($property->property_type === 'gudang' ? 'Simulasi Kredit Investasi / Gudang' : 'Simulasi Cicilan KPR') }}
                            </span>
                            <h3 class="text-xl font-extrabold text-white mt-1">
                                {{ $property->property_type === 'tanah' ? 'Kalkulator KPT / Kredit Tanah' : ($property->property_type === 'gudang' ? 'Kalkulator Kredit Komersial' : 'Kalkulator KPR Properti Ini') }}
                            </h3>
                        </div>
                        <a href="{{ route('kpr', ['price' => $property->price]) }}" class="text-xs text-rose-400 hover:text-white underline">
                            Detail KPR &rarr;
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <div class="flex justify-between text-xs font-semibold mb-1">
                                <span class="text-slate-300">Uang Muka (DP):</span>
                                <span class="text-amber-400" x-text="dpPercent + '% (' + formatIdr(dpAmount) + ')'"></span>
                            </div>
                            <input type="range" min="10" max="50" step="5" x-model="dpPercent" class="w-full accent-rose-500">
                        </div>

                        <div>
                            <div class="flex justify-between text-xs font-semibold mb-1">
                                <span class="text-slate-300">Suku Bunga KPR:</span>
                                <span class="text-amber-400" x-text="interest + '% p.a.'"></span>
                            </div>
                            <input type="range" min="3.0" max="10.0" step="0.25" x-model="interest" class="w-full accent-rose-500">
                        </div>

                        <div>
                            <div class="flex justify-between text-xs font-semibold mb-1">
                                <span class="text-slate-300">Tenor Pinjaman:</span>
                                <span class="text-amber-400" x-text="tenor + ' Tahun'"></span>
                            </div>
                            <input type="range" min="5" max="30" step="1" x-model="tenor" class="w-full accent-rose-500">
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-slate-700/80 flex flex-col sm:flex-row items-center justify-between gap-4 bg-slate-800/60 rounded-xl p-4">
                        <div>
                            <div class="text-xs text-slate-400">Estimasi Cicilan per Bulan:</div>
                            <div class="text-2xl font-black text-emerald-400" x-text="formatIdr(monthlyInstallment) + ' /bln'"></div>
                        </div>
                        <a href="https://wa.me/{{ $effectiveAgentCleanPhone }}?text={{ urlencode('Halo ' . $effectiveAgentName . ', saya ingin konsultasi pengajuan KPR untuk listing: ' . $property->title . ' (' . $shareUrl . ')') }}"
                           target="_blank"
                           class="px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md transition-all flex items-center gap-2">
                            <i class="fa-brands fa-whatsapp"></i> Ajukan KPR ke Bank Rekanan
                        </a>
                    </div>
                </div>
            @endif

            <!-- Other Ready Units in this Apartment Building -->
            @if(isset($projectUnits) && $projectUnits->isNotEmpty())
                <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-xs space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2.5 border-b border-slate-100">
                        <div>
                            <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-hotel text-rose-600"></i>
                                <span>Ketersediaan Unit Lain di {{ $property->project->name }}</span>
                            </h2>
                            <p class="text-xs text-slate-500 mt-0.5">Pilihan lantai, tower &amp; tipe unit ready lainnya yang siap huni atau disewa.</p>
                        </div>
                        <span class="text-xs font-bold text-rose-600 bg-rose-50 px-2.5 py-0.5 rounded-full border border-rose-100 w-fit">
                            {{ $projectUnits->count() }} Pilihan Ready
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider text-[10px] border-b border-slate-100">
                                    <th class="p-3">Tower &amp; Lantai</th>
                                    <th class="p-3">Tipe &amp; Luas</th>
                                    <th class="p-3">Kondisi</th>
                                    <th class="p-3">Harga</th>
                                    <th class="p-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                @foreach($projectUnits as $pUnit)
                                    @php
                                        $unitLink = $effectiveAgent ? route('properties.show', ['slug' => $pUnit->slug, 'agent' => $effectiveAgent->slug]) : route('properties.show', $pUnit->slug);
                                        $unitWaText = "Halo {$effectiveAgentName}, saya tertarik info unit ready di {$property->project->name}: " . $pUnit->title . " (Harga: " . $pUnit->formatted_price . ") - " . $unitLink;
                                    @endphp
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="p-3">
                                            <div class="font-extrabold text-slate-900 flex items-center gap-1.5 flex-wrap">
                                                <span>{{ $pUnit->formatted_tower ?: ($pUnit->unit_tower ?: 'Tower -') }}</span>
                                                @if($pUnit->formatted_floor_short)
                                                    <span class="font-mono text-rose-600 bg-rose-50 px-1.5 py-0.5 rounded text-[11px] font-black">
                                                        {{ $pUnit->formatted_floor_short }}
                                                    </span>
                                                @endif
                                            </div>
                                            @if($pUnit->formatted_unit_number)
                                                <div class="text-[11px] text-slate-500 font-semibold mt-0.5">{{ $pUnit->formatted_unit_number }}</div>
                                            @endif
                                        </td>
                                        <td class="p-3">
                                            <div class="font-bold text-slate-800">{{ $pUnit->unit_type ?: ($pUnit->bedrooms ? $pUnit->bedrooms . ' BR' : 'Unit') }}</div>
                                            <div class="text-[10px] text-slate-400">{{ $pUnit->building_area ? $pUnit->building_area . ' m²' : '' }}</div>
                                        </td>
                                        <td class="p-3">
                                            <div class="text-slate-700 text-[11px]">{{ $pUnit->furnished_status_label }}</div>
                                            <div class="text-[10px] text-slate-400">{{ $pUnit->unit_view ?: '-' }}</div>
                                        </td>
                                        <td class="p-3">
                                            <div class="font-black text-slate-900 text-xs">{{ $pUnit->formatted_price }}</div>
                                            <span class="text-[9px] font-bold uppercase px-1.5 py-0.2 rounded bg-pink-100 text-pink-700 border border-pink-200">
                                                {{ $pUnit->listing_type }}
                                            </span>
                                        </td>
                                        <td class="p-3 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <a href="{{ $unitLink }}" class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] transition-all">
                                                    Lihat
                                                </a>
                                                <a href="https://wa.me/{{ $effectiveAgentCleanPhone }}?text={{ urlencode($unitWaText) }}" target="_blank" class="p-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-600 text-emerald-700 hover:text-white transition-all" title="Tanya Unit Ini via WhatsApp">
                                                    <i class="fa-brands fa-whatsapp text-xs"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <!-- Right Sticky Column: Agent Card & Contact Form -->
        <aside class="lg:col-span-4 space-y-4 sm:space-y-5 lg:sticky lg:top-28">
            <!-- Agent Card -->
            <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-sm">
                @if(request()->filled('agent') || request()->filled('ref'))
                    <div class="mb-3 px-3 py-1.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-[11px] font-bold flex items-center gap-1.5">
                        <i class="fa-solid fa-user-check text-rose-600"></i>
                        <span>Direferensikan oleh Agen Pilihan Anda</span>
                    </div>
                @endif
                <div class="flex items-center gap-4 pb-3.5 border-b border-slate-100">
                    <img src="{{ $effectiveAgentPhoto }}"
                         alt="{{ $effectiveAgentName }}"
                         class="w-14 h-14 rounded-2xl object-cover ring-2 ring-slate-100">
                    <div>
                        <div class="flex items-center gap-1.5">
                            <h3 class="font-extrabold text-slate-900 text-base">
                                {{ $effectiveAgentName }}
                            </h3>
                            <span class="w-4 h-4 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[8px]" title="Agen Terverifikasi">
                                <i class="fa-solid fa-check"></i>
                            </span>
                        </div>
                        <p class="text-xs text-rose-600 font-bold mt-0.5">{{ $effectiveAgentPosition }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $effectiveAgentOffice }}</p>
                    </div>
                </div>

                <!-- Fast Contact Buttons -->
                <div class="mt-3.5 space-y-2">
                    <a href="{{ $waDirectChatUrl }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs sm:text-sm flex items-center justify-center gap-2 shadow-md transition-all">
                        <i class="fa-brands fa-whatsapp text-lg"></i>
                        <span>Chat WhatsApp Agen</span>
                    </a>

                    <a href="tel:{{ $effectiveAgentPhone }}"
                       class="w-full py-2 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs flex items-center justify-center gap-2 transition-all">
                        <i class="fa-solid fa-phone"></i>
                        <span>Telepon: {{ $effectiveAgentPhone }}</span>
                    </a>
                </div>
            </div>

            <!-- Inquiry Lead Capture Form -->
            <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-sm">
                <h3 class="font-extrabold text-slate-900 text-base mb-1">
                    Jadwalkan Survey Lokasi
                </h3>
                <p class="text-xs text-slate-500 mb-4">
                    Isi formulir berikut dan agen kami akan mengatur jadwal kunjungan ke lokasi properti.
                </p>

                <form action="{{ route('inquiry.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <input type="hidden" name="property_id" value="{{ $property->id }}">
                    <input type="hidden" name="agent_id" value="{{ $effectiveAgent?->id ?? $property->agent_id }}">
                    <input type="hidden" name="inquiry_type" value="survey">

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap *</label>
                        <input type="text"
                               name="name"
                               required
                               placeholder="Nama Anda"
                               value="{{ old('name') }}"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:bg-white focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor WhatsApp *</label>
                        <input type="text"
                               name="phone"
                               required
                               placeholder="0812xxxxxxx"
                               value="{{ old('phone') }}"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:bg-white focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Email *</label>
                        <input type="email"
                               name="email"
                               required
                               placeholder="email@anda.com"
                               value="{{ old('email') }}"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:bg-white focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Rencana Tanggal Survey</label>
                        <input type="date"
                               name="schedule_survey_date"
                               value="{{ old('schedule_survey_date') }}"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:bg-white focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pesan / Pertanyaan Tambahan</label>
                        <textarea name="message"
                                  rows="3"
                                  required
                                  class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:bg-white focus:outline-none">{{ old('message', 'Halo, saya tertarik dengan listing ini dan ingin info selengkapnya.') }}</textarea>
                    </div>

                    <button type="submit"
                            class="w-full py-3 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs shadow-md transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>Kirim Permintaan Survey</span>
                    </button>
                </form>
            </div>
        </aside>
    </div>

    <!-- Similar Properties Section -->
    @if($similarProperties->count() > 0)
        <div class="mt-20 pt-10 border-t border-slate-200">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-2xl font-extrabold text-slate-900">Properti Serupa di Area Ini</h2>
                    <p class="text-xs text-slate-500 mt-1">Pilihan alternatif di kota atau kategori yang serupa.</p>
                </div>
                <a href="{{ route('properties.index', ['city' => $property->city]) }}" class="text-xs font-bold text-rose-600 hover:underline">
                    Lihat Semua di {{ $property->city }} &rarr;
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($similarProperties as $similar)
                    <x-property-card :property="$similar" />
                @endforeach
            </div>
        </div>
    @endif
    </div>
</div>

@push('scripts')
<script>
function propertyDetailComponent() {
    return {
        activeImg: '{{ $firstImage }}',
        activeImgIndex: 0,
        touchStartX: 0,
        touchEndX: 0,
        fav: isFavorite({{ $property->id }}),
        comp: isCompared({{ $property->id }}),
        showSocialModal: false,
        showGalleryModal: false,
        showVideoModal: false,
        shareTab: 'story',
        galleryIndex: 0,
        galleryImages: @js($images),
        storyData: @js($storyData),
        storyImageUrl: null,
        storyBlob: null,
        storyGenerating: false,
        copiedWa: false,
        copiedIg: false,
        copiedCode: false,
        copiedLink: false,
        waCleanText: @js($waBroadcastTextWithoutLink),
        waTextWithLink: @js($waBroadcastText),
        includeWebLink: true,

        get currentWaText() {
            return this.includeWebLink ? this.waTextWithLink : this.waCleanText;
        },
        get currentWaShareUrl() {
            return 'https://api.whatsapp.com/send?text=' + encodeURIComponent(this.currentWaText);
        },

        init() {
            window.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    if (this.showGalleryModal || this.showVideoModal || this.showSocialModal) {
                        this.closeModals();
                    }
                }
                if (e.key === 'ArrowRight' && this.showGalleryModal) {
                    this.nextPhoto();
                }
                if (e.key === 'ArrowLeft' && this.showGalleryModal) {
                    this.prevPhoto();
                }
            });

            window.addEventListener('popstate', () => {
                if (this.showGalleryModal || this.showVideoModal || this.showSocialModal) {
                    this.closeModals(true);
                }
            });
        },

        openGallery(index = 0) {
            this.galleryIndex = index;
            if (!this.showGalleryModal && !this.showVideoModal && !this.showSocialModal) {
                window.history.pushState({ detailModal: true }, '');
            }
            this.showGalleryModal = true;
        },
        openVideo() {
            if (!this.showGalleryModal && !this.showVideoModal && !this.showSocialModal) {
                window.history.pushState({ detailModal: true }, '');
            }
            this.showVideoModal = true;
        },
        closeModals(fromPopState = false) {
            const wasOpen = this.showGalleryModal || this.showVideoModal || this.showSocialModal;
            this.showGalleryModal = false;
            this.showVideoModal = false;
            this.showSocialModal = false;
            if (wasOpen && !fromPopState && window.history.state && window.history.state.detailModal) {
                window.history.back();
            }
        },
        nextPhoto() {
            this.galleryIndex = (this.galleryIndex + 1) % this.galleryImages.length;
        },
        prevPhoto() {
            this.galleryIndex = (this.galleryIndex - 1 + this.galleryImages.length) % this.galleryImages.length;
        },
        selectMainPhoto(url, idx) {
            this.activeImg = url;
            this.activeImgIndex = idx;
        },
        nextMainPhoto() {
            if (!this.galleryImages.length) return;
            this.activeImgIndex = (this.activeImgIndex + 1) % this.galleryImages.length;
            this.activeImg = this.galleryImages[this.activeImgIndex];
        },
        prevMainPhoto() {
            if (!this.galleryImages.length) return;
            this.activeImgIndex = (this.activeImgIndex - 1 + this.galleryImages.length) % this.galleryImages.length;
            this.activeImg = this.galleryImages[this.activeImgIndex];
        },
        handleMainSwipe() {
            const diff = this.touchStartX - this.touchEndX;
            if (Math.abs(diff) > 40) {
                if (diff > 0) {
                    this.nextMainPhoto();
                } else {
                    this.prevMainPhoto();
                }
            }
        },
        async downloadPhoto(url, idx) {
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
        },
        openStoryModal() {
            this.shareTab = 'story';
            if (!this.showGalleryModal && !this.showVideoModal && !this.showSocialModal) {
                window.history.pushState({ detailModal: true }, '');
            }
            this.showSocialModal = true;
            if (!this.storyImageUrl) {
                this.$nextTick(() => {
                    this.generateStoryImage();
                });
            }
        },
        async generateStoryImage() {
            if (this.storyGenerating) return;
            this.storyGenerating = true;
            try {
                const canvas = document.createElement('canvas');
                canvas.width = 1080;
                canvas.height = 1920;
                const ctx = canvas.getContext('2d');

                const fontFam = "'Plus Jakarta Sans', Figtree, ui-sans-serif, system-ui, sans-serif";
                if (document.fonts && document.fonts.ready) {
                    try { await document.fonts.ready; } catch (e) {}
                }

                // Background clean light slate
                ctx.fillStyle = '#f8fafc';
                ctx.fillRect(0, 0, 1080, 1920);

                // Pink Header Banner (Compact)
                ctx.fillStyle = '#db2777';
                ctx.fillRect(0, 0, 1080, 114);

                // Logo Brand
                ctx.fillStyle = '#ffffff';
                ctx.font = `800 38px ${fontFam}`;
                ctx.textAlign = 'left';
                ctx.fillText('{{ \App\Models\Setting::getSiteName() }}', 60, 73);

                ctx.font = `700 18px ${fontFam}`;
                ctx.fillStyle = '#fce7f3';
                ctx.fillText('REAL ESTATE NETWORK', 300, 69);

                if (this.storyData.propertyCode) {
                    ctx.textAlign = 'right';
                    ctx.font = `700 22px ${fontFam}`;
                    ctx.fillStyle = '#ffffff';
                    ctx.fillText(this.storyData.propertyCode, 1020, 71);
                }

                // Primary Photo Box
                const imgX = 60;
                const imgY = 144;
                const imgW = 960;
                const imgH = 460;

                const img = new Image();
                img.crossOrigin = 'anonymous';
                img.src = this.storyData.primaryImage;

                await new Promise(res => {
                    img.onload = res;
                    img.onerror = res;
                    setTimeout(res, 2500);
                });

                // Draw photo with rounded corners
                ctx.save();
                this.canvasRoundRect(ctx, imgX, imgY, imgW, imgH, 28);
                ctx.clip();
                if (img.complete && img.naturalWidth > 0) {
                    const scale = Math.max(imgW / img.naturalWidth, imgH / img.naturalHeight);
                    const nw = img.naturalWidth * scale;
                    const nh = img.naturalHeight * scale;
                    const nx = imgX + (imgW - nw) / 2;
                    const ny = imgY + (imgH - nh) / 2;
                    ctx.drawImage(img, nx, ny, nw, nh);
                } else {
                    ctx.fillStyle = '#e2e8f0';
                    ctx.fillRect(imgX, imgY, imgW, imgH);
                }
                ctx.restore();

                // Title & Price Section (Matching Current Property Detail Header & Font Family)
                let curY = 668;

                // Title (Left aligned, large bold matching detail page, up to 3 lines)
                ctx.fillStyle = '#0f172a';
                ctx.font = `700 44px ${fontFam}`;
                ctx.textAlign = 'left';
                const titleWords = (this.storyData.title || '').split(' ');
                let tLine = '';
                let tLines = [];
                for (let n = 0; n < titleWords.length; n++) {
                    const testLine = tLine + titleWords[n] + ' ';
                    if (ctx.measureText(testLine).width > 960 && n > 0) {
                        tLines.push(tLine.trim());
                        tLine = titleWords[n] + ' ';
                        if (tLines.length >= 2) {
                            let rem = titleWords.slice(n).join(' ');
                            while (ctx.measureText(rem + '...').width > 960 && rem.length > 0) {
                                rem = rem.substring(0, rem.length - 1);
                            }
                            tLines.push(rem + (rem.length < titleWords.slice(n).join(' ').length ? '...' : ''));
                            tLine = '';
                            break;
                        }
                    } else {
                        tLine = testLine;
                    }
                }
                if (tLine.trim()) tLines.push(tLine.trim());
                tLines.forEach((line, idx) => {
                    ctx.fillText(line, 60, curY);
                    curY += (idx < tLines.length - 1) ? 58 : 0;
                });

                // Price (Black, bold, well-spaced below title)
                curY += 74;
                ctx.fillStyle = '#0f172a';
                ctx.font = `700 50px ${fontFam}`;
                ctx.textAlign = 'left';
                ctx.fillText(this.storyData.price, 60, curY);

                // Detail Informasi Properti Section (Grouped Tables with Gray Label & White Value)
                curY += 64;
                ctx.fillStyle = '#0f172a';
                ctx.font = `700 28px ${fontFam}`;
                ctx.textAlign = 'left';
                ctx.fillText('Detail Informasi Properti', 60, curY);

                curY += 28;
                const groups = Array.isArray(this.storyData.detailGroups) ? this.storyData.detailGroups : [];
                const totalRows = groups.reduce((acc, g) => acc + (Array.isArray(g) ? g.length : 0), 0);
                const maxAvailH = 1690 - curY;
                const groupGap = 18;
                const totalGapH = Math.max(0, groups.length - 1) * groupGap;
                const rowH = totalRows > 0 ? Math.min(54, Math.max(40, Math.floor((maxAvailH - totalGapH) / totalRows))) : 48;
                const fontSize = rowH < 46 ? 21 : 23;

                groups.forEach((group) => {
                    if (!Array.isArray(group) || group.length === 0) return;
                    const boxH = group.length * rowH;
                    const boxX = 60;
                    const boxW = 960;
                    const halfW = 480;

                    ctx.save();
                    this.canvasRoundRect(ctx, boxX, curY, boxW, boxH, 20);
                    ctx.clip();

                    group.forEach((row, rIdx) => {
                        const rY = curY + rIdx * rowH;

                        // Left cell background (#f1f5f9)
                        ctx.fillStyle = '#f1f5f9';
                        ctx.fillRect(boxX, rY, halfW, rowH);

                        // Right cell background (#ffffff)
                        ctx.fillStyle = '#ffffff';
                        ctx.fillRect(boxX + halfW, rY, halfW, rowH);

                        // Center vertical divider
                        ctx.strokeStyle = '#e2e8f0';
                        ctx.lineWidth = 2;
                        ctx.beginPath();
                        ctx.moveTo(boxX + halfW, rY);
                        ctx.lineTo(boxX + halfW, rY + rowH);
                        ctx.stroke();

                        // Bottom row divider
                        if (rIdx < group.length - 1) {
                            ctx.beginPath();
                            ctx.moveTo(boxX, rY + rowH);
                            ctx.lineTo(boxX + boxW, rY + rowH);
                            ctx.stroke();
                        }

                        // Label text (left)
                        ctx.fillStyle = '#475569';
                        ctx.font = `500 ${fontSize}px ${fontFam}`;
                        ctx.textAlign = 'left';
                        ctx.textBaseline = 'middle';
                        ctx.fillText(String(row.label || ''), boxX + 26, rY + rowH / 2);

                        // Value text (right, bold)
                        ctx.fillStyle = '#0f172a';
                        ctx.font = `700 ${fontSize}px ${fontFam}`;
                        ctx.fillText(String(row.value || ''), boxX + halfW + 26, rY + rowH / 2);
                    });

                    ctx.restore();

                    // Box outer border
                    ctx.strokeStyle = '#cbd5e1';
                    ctx.lineWidth = 2;
                    this.canvasRoundRect(ctx, boxX, curY, boxW, boxH, 20);
                    ctx.stroke();

                    curY += boxH + groupGap;
                });

                ctx.textBaseline = 'alphabetic';

                // Compact Agent Profile Bar (Bottom)
                const agentCardY = 1720;
                const agentCardH = 126;
                ctx.fillStyle = '#ffffff';
                ctx.strokeStyle = '#e2e8f0';
                ctx.lineWidth = 2;
                this.canvasRoundRect(ctx, 60, agentCardY, 960, agentCardH, 24);
                ctx.fill();
                ctx.stroke();

                // Agent Avatar (Smaller: radius 42px)
                const agentImg = new Image();
                agentImg.crossOrigin = 'anonymous';
                agentImg.src = this.storyData.agentPhoto;
                await new Promise(res => {
                    agentImg.onload = res;
                    agentImg.onerror = res;
                    setTimeout(res, 2000);
                });

                const avX = 88;
                const avY = agentCardY + 21;
                const avR = 42;
                ctx.save();
                ctx.beginPath();
                ctx.arc(avX + avR, avY + avR, avR, 0, Math.PI * 2);
                ctx.clip();
                if (agentImg.complete && agentImg.naturalWidth > 0) {
                    ctx.drawImage(agentImg, avX, avY, avR * 2, avR * 2);
                } else {
                    ctx.fillStyle = '#ec4899';
                    ctx.fillRect(avX, avY, avR * 2, avR * 2);
                }
                ctx.restore();
                ctx.strokeStyle = '#db2777';
                ctx.lineWidth = 3;
                ctx.beginPath();
                ctx.arc(avX + avR, avY + avR, avR, 0, Math.PI * 2);
                ctx.stroke();

                // Agent text info (Compact)
                ctx.textAlign = 'left';
                ctx.fillStyle = '#0f172a';
                ctx.font = `700 26px ${fontFam}`;
                ctx.fillText(this.storyData.agentName, 195, agentCardY + 52);

                ctx.fillStyle = '#64748b';
                ctx.font = `500 20px ${fontFam}`;
                ctx.fillText((this.storyData.agentOffice || '{{ \App\Models\Setting::getSiteName() }}') + ' • ' + (this.storyData.agentPosition || 'Property Consultant'), 195, agentCardY + 88);

                // Right side of Agent Bar: WhatsApp Phone Pill
                ctx.textAlign = 'right';
                ctx.fillStyle = '#059669';
                ctx.font = `700 24px ${fontFam}`;
                ctx.fillText('WA: ' + this.storyData.agentPhone, 990, agentCardY + 70);

                // Footer short link note
                ctx.textAlign = 'center';
                ctx.fillStyle = '#64748b';
                ctx.font = `500 20px ${fontFam}`;
                ctx.fillText(this.storyData.shareUrl || 'Hubungi agen untuk jadwal survey & info lengkap properti ini', 540, 1885);

                this.storyImageUrl = canvas.toDataURL('image/png');
                canvas.toBlob(blob => {
                    this.storyBlob = blob;
                    this.storyGenerating = false;
                }, 'image/png');
            } catch (e) {
                console.error(e);
                this.storyGenerating = false;
            }
        },
        async shareToWhatsAppStory() {
            if (!this.storyBlob && !this.storyImageUrl) {
                await this.generateStoryImage();
            }

            const storyCaption = this.storyData.title + ' (' + this.storyData.price + ")\nLink Listing: " + this.storyData.shareUrl;

            // Auto-copy URL/caption to clipboard as backup in case OS/WhatsApp strips text when sharing image file
            try {
                await navigator.clipboard.writeText(this.storyData.shareUrl);
                this.copiedLink = true;
                setTimeout(() => this.copiedLink = false, 3000);
            } catch (e) {}

            if (this.storyBlob) {
                const file = new File([this.storyBlob], 'story-' + this.storyData.slug + '.png', { type: 'image/png' });
                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                    try {
                        await navigator.share({
                            files: [file],
                            title: this.storyData.title,
                            text: storyCaption,
                            url: this.storyData.shareUrl
                        });
                        return;
                    } catch (err) {
                        if (err.name !== 'AbortError') {
                            console.warn(err);
                        } else {
                            return;
                        }
                    }
                }
            }

            // Fallback: Download image and copy link
            this.downloadStoryImage();
            this.copyLink();
            alert('Gambar Story berhasil di-download & Link Listing (' + this.storyData.shareUrl + ') otomatis disalin ke clipboard! Silakan buka WhatsApp -> Status Saya, lalu tempel (paste) link pada keterangan.');
        },
        downloadStoryImage() {
            if (!this.storyImageUrl) return;
            const a = document.createElement('a');
            a.href = this.storyImageUrl;
            a.download = 'story-properti-' + this.storyData.slug + '.png';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        },
        canvasRoundRect(ctx, x, y, width, height, radius) {
            if (ctx.roundRect) {
                ctx.beginPath();
                ctx.roundRect(x, y, width, height, radius);
                return;
            }
            ctx.beginPath();
            ctx.moveTo(x + radius, y);
            ctx.lineTo(x + width - radius, y);
            ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
            ctx.lineTo(x + width, y + height - radius);
            ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
            ctx.lineTo(x + radius, y + height);
            ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
            ctx.lineTo(x, y + radius);
            ctx.quadraticCurveTo(x, y, x + radius, y);
            ctx.closePath();
        },
        canvasWrapText(ctx, text, x, y, maxWidth, lineHeight, maxLines = 2) {
            const words = (text || '').split(' ');
            let line = '';
            let lineCount = 0;
            for (let n = 0; n < words.length; n++) {
                const testLine = line + words[n] + ' ';
                const metrics = ctx.measureText(testLine);
                if (metrics.width > maxWidth && n > 0) {
                    ctx.fillText(line.trim(), x, y);
                    line = words[n] + ' ';
                    y += lineHeight;
                    lineCount++;
                    if (lineCount >= maxLines - 1) {
                        let remaining = words.slice(n).join(' ');
                        while (ctx.measureText(remaining + '...').width > maxWidth && remaining.length > 0) {
                            remaining = remaining.substring(0, remaining.length - 1);
                        }
                        ctx.fillText(remaining + '...', x, y);
                        return;
                    }
                } else {
                    line = testLine;
                }
            }
            ctx.fillText(line.trim(), x, y);
        },
        async shareCoverWithWaText() {
            try {
                const res = await fetch('{{ $firstImage }}');
                const blob = await res.blob();
                const file = new File([blob], 'cover-' + this.storyData.slug + '.jpg', { type: blob.type || 'image/jpeg' });
                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                    await navigator.share({
                        files: [file],
                        title: this.storyData.title,
                        text: this.currentWaText
                    });
                    return;
                }
            } catch (e) {
                if (e && e.name === 'AbortError') return;
            }
            window.open(this.currentWaShareUrl, '_blank');
        },
        copyWaBroadcast() {
            navigator.clipboard.writeText(this.currentWaText);
            this.copiedWa = true;
            setTimeout(() => this.copiedWa = false, 2500);
        },
        copyCaption() {
            navigator.clipboard.writeText(`{{ addslashes($igCaption) }}`);
            this.copiedIg = true;
            setTimeout(() => this.copiedIg = false, 2500);
        },
        copyLink() {
            navigator.clipboard.writeText('{{ $shareUrl }}');
            this.copiedLink = true;
            setTimeout(() => this.copiedLink = false, 2500);
        }
    };
}
</script>
@endpush
@endsection
