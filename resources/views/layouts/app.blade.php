<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $metaSiteName = \App\Models\Setting::getSiteName();
        $metaSiteLogo = \App\Models\Setting::getSiteLogo();
        $defaultTitle = $metaSiteName . ' - Portal Properti Terdepan Indonesia | Beli, Sewa, Proyek Baru';
        $defaultDesc = 'Situs jual beli dan sewa properti terpercaya di Indonesia. Temukan rumah impian, apartemen, ruko, tanah kavling, dan villa mewah dengan simulasi KPR cepat.';
        $ogImage = trim($__env->yieldContent('og_image')) ?: ($metaSiteLogo ?: asset('favicon.ico'));
    @endphp
    <title>@yield('title', $defaultTitle)</title>
    <meta name="description" content="@yield('meta_description', $defaultDesc)">

    <!-- Open Graph / WhatsApp / Facebook / Telegram Link Preview -->
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="{{ $metaSiteName }}">
    <meta property="og:title" content="@yield('og_title', trim($__env->yieldContent('title')) ?: $defaultTitle)">
    <meta property="og:description" content="@yield('og_description', trim($__env->yieldContent('meta_description')) ?: $defaultDesc)">
    <meta property="og:url" content="@yield('og_url', url()->current())">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:secure_url" content="{{ $ogImage }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta itemprop="image" content="{{ $ogImage }}">
    <link rel="image_src" href="{{ $ogImage }}">

    <!-- Twitter / X Card Preview -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('og_title', trim($__env->yieldContent('title')) ?: $defaultTitle)">
    <meta name="twitter:description" content="@yield('og_description', trim($__env->yieldContent('meta_description')) ?: $defaultDesc)">
    <meta name="twitter:image" content="{{ $ogImage }}">

    <!-- Favicon (dynamic: from site logo setting) -->
    @if($metaSiteLogo)
        <link rel="icon" type="image/png" href="{{ $metaSiteLogo }}">
        <link rel="apple-touch-icon" href="{{ $metaSiteLogo }}">
    @else
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @endif

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">

    <!-- Font Awesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        html {
            font-size: 17px;
        }
        body {
            font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
            font-size: 1rem;
            line-height: 1.45;
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="flex flex-col min-h-screen bg-[#f0f2f5] text-slate-800 antialiased"
      x-data="{ 
          mobileMenuOpen: false, 
          favoriteCount: 0, 
          compareCount: 0,
          toggleMobileMenu() {
              if (this.mobileMenuOpen) {
                  this.closeMobileMenu();
              } else {
                  this.openMobileMenu();
              }
          },
          openMobileMenu() {
              this.mobileMenuOpen = true;
              if (!history.state || !history.state.mobileNavOpen) {
                  history.pushState({ mobileNavOpen: true }, '');
              }
          },
          closeMobileMenu(fromPopstate = false) {
              if (!this.mobileMenuOpen) return;
              this.mobileMenuOpen = false;
              if (!fromPopstate && history.state && history.state.mobileNavOpen) {
                  history.back();
              }
          }
      }"
      x-init="
          const favs = JSON.parse(localStorage.getItem('pn123_favs') || '[]');
          favoriteCount = favs.length;
          const comps = JSON.parse(localStorage.getItem('pn123_compare') || '[]');
          compareCount = comps.length;

          window.addEventListener('popstate', (e) => {
              if (mobileMenuOpen) {
                  closeMobileMenu(true);
              }
          });
      ">
    @php
        $pubSiteLogo = \App\Models\Setting::getSiteLogo();
        $pubSiteName = \App\Models\Setting::getSiteName();
        $pubSiteTagline = \App\Models\Setting::getSiteTagline();
        $pubSiteHotline = \App\Models\Setting::getSiteHotline();
        $pubCleanHotline = \App\Models\Setting::getCleanSiteHotline();
        $isHotlineWa = str_starts_with($pubCleanHotline, '08') || str_starts_with($pubCleanHotline, '62');
        $hotlineLink = $isHotlineWa
            ? 'https://wa.me/' . (str_starts_with($pubCleanHotline, '0') ? '62' . substr($pubCleanHotline, 1) : $pubCleanHotline) . '?text=' . urlencode('Halo ' . $pubSiteName . ', saya ingin konsultasi properti.')
            : 'tel:' . $pubCleanHotline;
    @endphp

    <!-- Top Announcement Bar -->
    <div class="bg-pink-700 text-pink-50 text-xs py-1.5 sm:py-2 px-3 sm:px-4 border-b border-pink-800/40 relative z-40">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row justify-between items-start sm:items-center gap-1.5 sm:gap-2">
            <!-- Promo text (Desktop & Mobile) -->
            <div class="flex items-center gap-2 sm:gap-4 flex-wrap w-full sm:w-auto justify-between sm:justify-start">
                <span class="inline-flex items-center gap-1.5 text-white font-bold text-[10.5px] sm:text-xs">
                    <i class="fa-solid fa-bolt text-amber-300"></i> PROMO KPR 2026: Suku Bunga Mulai 2.75% Fixed 3 Tahun
                </span>
                <!-- Desktop Hotline -->
                <span class="hidden md:inline-block text-pink-300/60">|</span>
                <a href="{{ $hotlineLink }}" target="{{ $isHotlineWa ? '_blank' : '_self' }}" class="hidden md:inline-flex items-center gap-1 text-pink-100 hover:text-white transition-colors" title="Hubungi Hotline Konsultasi">
                    <i class="fa-solid fa-headset text-pink-200"></i> Hotline Konsultasi: <span class="font-bold underline decoration-pink-300/40">{{ $pubSiteHotline }}</span>
                </a>
            </div>

            <!-- Mobile Baris 2: Hotline Konsultasi (Kiri) & Login/Auth (Kanan) -->
            <div class="w-full sm:w-auto flex items-center justify-between sm:justify-end gap-3 text-[10.5px] sm:text-xs pt-1 sm:pt-0 border-t border-pink-600/50 sm:border-t-0">
                <!-- Mobile Hotline Link (Tampil di Layar HP!) -->
                <a href="{{ $hotlineLink }}" target="{{ $isHotlineWa ? '_blank' : '_self' }}" class="inline-flex md:hidden items-center gap-1 text-pink-100 hover:text-white font-bold transition-colors" title="Hubungi Hotline Konsultasi">
                    <i class="fa-solid fa-headset text-pink-200"></i> Hotline: <span class="underline decoration-pink-300/40">{{ $pubSiteHotline }}</span>
                </a>

                <!-- Auth / Login Links -->
                <div class="flex items-center gap-2.5">
                    @auth
                        @if(auth()->user()->isAgent())
                            <a href="{{ route('admin.dashboard') }}" class="text-white hover:text-pink-200 transition-colors flex items-center gap-1 font-bold">
                                <i class="fa-solid fa-id-badge text-[11px] text-pink-200"></i> Portal Agen ({{ auth()->user()->agent?->name ?? auth()->user()->name }})
                            </a>
                        @else
                            <a href="{{ route('admin.dashboard') }}" class="text-pink-100 hover:text-white transition-colors flex items-center gap-1 font-semibold">
                                <i class="fa-solid fa-user-shield text-[11px] text-pink-200"></i> Admin Portal
                            </a>
                        @endif
                        <span class="text-pink-300/60">|</span>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="hover:text-white transition-colors text-pink-200 text-[10.5px] sm:text-xs font-medium cursor-pointer">Keluar</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="hover:text-white transition-colors flex items-center gap-1 text-pink-100 font-medium">
                            <i class="fa-solid fa-right-to-bracket text-[11px] text-pink-200"></i> Login Agen / Admin
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    <!-- Main Navigation Bar -->
    <header class="sticky top-0 z-50 bg-pink-600 text-white border-b border-pink-700 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16 sm:h-18">
                <!-- Logo -->
                <div class="flex items-center gap-8">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5 sm:gap-3.5 group shrink-0 py-1">
                        @if($pubSiteLogo)
                            <div class="flex flex-col justify-center">
                                <img src="{{ $pubSiteLogo }}" alt="{{ $pubSiteName }}" class="h-8 xs:h-9 sm:h-11 md:h-12 w-auto max-w-[150px] xs:max-w-[185px] sm:max-w-[240px] md:max-w-[280px] object-contain drop-shadow-xs shrink-0 rounded-lg">
                                @if($pubSiteTagline)
                                    <span class="sm:hidden text-[7.5px] xs:text-[8px] uppercase font-bold tracking-wider text-pink-100/90 leading-none mt-1 truncate max-w-[150px] xs:max-w-[185px]">
                                        {{ $pubSiteTagline }}
                                    </span>
                                @endif
                            </div>
                            @if($pubSiteTagline)
                                <div class="hidden sm:flex flex-col justify-center border-l border-white/30 pl-3 sm:pl-3.5 py-0.5">
                                    <span class="text-[9.5px] sm:text-[10.5px] md:text-[11px] font-bold uppercase tracking-wider text-pink-100/95 leading-tight max-w-[180px] sm:max-w-[250px] line-clamp-2">
                                        {{ $pubSiteTagline }}
                                    </span>
                                </div>
                            @endif
                        @else
                            <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-white text-pink-600 flex items-center justify-center shadow-md shadow-pink-900/20 group-hover:scale-105 transition-transform shrink-0">
                                <i class="fa-solid fa-building-user text-lg sm:text-xl"></i>
                            </div>
                            <div class="flex flex-col justify-center min-w-0">
                                <span class="text-base sm:text-2xl font-black tracking-tight text-white leading-none truncate">
                                    @if(str_contains(strtolower($pubSiteName), 'yproperti'))
                                        YProperti<span class="text-pink-200 font-extrabold">.com</span>
                                    @else
                                        {{ $pubSiteName }}
                                    @endif
                                </span>
                                <span class="text-[8px] sm:text-[10px] uppercase font-bold tracking-wider text-pink-100/90 leading-tight mt-0.5 truncate max-w-[170px] xs:max-w-[230px] sm:max-w-none">
                                    {{ $pubSiteTagline ?: 'Portal Properti & Marketplace' }}
                                </span>
                            </div>
                        @endif
                    </a>

                    <!-- Desktop Nav Links -->
                    <nav class="hidden lg:flex items-center gap-1">
                        <!-- Dropdown Dijual -->
                        <div class="relative group" x-data="{ openSale: false }" @mouseenter="openSale = true" @mouseleave="openSale = false">
                            <a href="{{ route('properties.for-sale') }}"
                               class="px-3.5 py-2 rounded-xl text-sm font-semibold flex items-center gap-1.5 transition-colors {{ request()->routeIs('properties.for-sale') || request('listing_type') === 'dijual' ? 'text-white bg-pink-700 font-bold shadow-xs' : 'text-pink-50 hover:text-white hover:bg-pink-700/60' }}">
                                <span>Dijual</span>
                                <i class="fa-solid fa-chevron-down text-[10px] text-pink-200 transition-transform duration-200" :class="openSale ? 'rotate-180 text-white' : ''"></i>
                            </a>

                            <div x-show="openSale"
                                 x-cloak
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 translate-y-0"
                                 x-transition:leave-end="opacity-0 translate-y-1"
                                 @click.away="openSale = false"
                                 class="absolute left-0 top-full pt-1.5 w-60 z-50">
                                <div class="bg-white rounded-2xl shadow-xl border border-pink-100 p-2 text-xs font-medium space-y-0.5 text-slate-800">
                                    <a href="{{ route('properties.for-sale') }}"
                                       class="flex items-center justify-between p-2 rounded-xl text-slate-900 hover:bg-pink-50 hover:text-pink-600 font-bold border-b border-pink-100 mb-1 transition-colors">
                                        <div class="flex items-center gap-2">
                                            <i class="fa-solid fa-tag text-pink-600"></i>
                                            <span>Semua Properti Dijual</span>
                                        </div>
                                        <i class="fa-solid fa-arrow-right text-[10px] text-slate-400"></i>
                                    </a>
                                    <a href="{{ route('properties.index', ['listing_type' => 'dijual', 'property_type' => 'rumah']) }}"
                                       class="flex items-center gap-2.5 p-2 rounded-xl text-slate-700 hover:bg-pink-50 hover:text-pink-600 transition-colors {{ request('listing_type') === 'dijual' && request('property_type') === 'rumah' ? 'bg-pink-50 text-pink-600 font-bold' : '' }}">
                                        <span class="w-6 h-6 rounded-lg bg-pink-50 text-pink-600 flex items-center justify-center text-xs"><i class="fa-solid fa-house"></i></span>
                                        <span>Rumah Dijual</span>
                                    </a>
                                    <a href="{{ route('properties.index', ['listing_type' => 'dijual', 'property_type' => 'apartemen']) }}"
                                       class="flex items-center gap-2.5 p-2 rounded-xl text-slate-700 hover:bg-pink-50 hover:text-pink-600 transition-colors {{ request('listing_type') === 'dijual' && request('property_type') === 'apartemen' ? 'bg-pink-50 text-pink-600 font-bold' : '' }}">
                                        <span class="w-6 h-6 rounded-lg bg-pink-50 text-pink-600 flex items-center justify-center text-xs"><i class="fa-solid fa-building"></i></span>
                                        <span>Apartemen Dijual</span>
                                    </a>
                                    <a href="{{ route('properties.index', ['listing_type' => 'dijual', 'property_type' => 'ruko']) }}"
                                       class="flex items-center gap-2.5 p-2 rounded-xl text-slate-700 hover:bg-pink-50 hover:text-pink-600 transition-colors {{ request('listing_type') === 'dijual' && request('property_type') === 'ruko' ? 'bg-pink-50 text-pink-600 font-bold' : '' }}">
                                        <span class="w-6 h-6 rounded-lg bg-pink-50 text-pink-600 flex items-center justify-center text-xs"><i class="fa-solid fa-store"></i></span>
                                        <span>Ruko Dijual</span>
                                    </a>
                                    <a href="{{ route('properties.index', ['listing_type' => 'dijual', 'property_type' => 'villa']) }}"
                                       class="flex items-center gap-2.5 p-2 rounded-xl text-slate-700 hover:bg-pink-50 hover:text-pink-600 transition-colors {{ request('listing_type') === 'dijual' && request('property_type') === 'villa' ? 'bg-pink-50 text-pink-600 font-bold' : '' }}">
                                        <span class="w-6 h-6 rounded-lg bg-pink-50 text-pink-600 flex items-center justify-center text-xs"><i class="fa-solid fa-umbrella-beach"></i></span>
                                        <span>Villa Dijual</span>
                                    </a>
                                    <a href="{{ route('properties.index', ['listing_type' => 'dijual', 'property_type' => 'tanah']) }}"
                                       class="flex items-center gap-2.5 p-2 rounded-xl text-slate-700 hover:bg-pink-50 hover:text-pink-600 transition-colors {{ request('listing_type') === 'dijual' && request('property_type') === 'tanah' ? 'bg-pink-50 text-pink-600 font-bold' : '' }}">
                                        <span class="w-6 h-6 rounded-lg bg-pink-50 text-pink-600 flex items-center justify-center text-xs"><i class="fa-solid fa-map"></i></span>
                                        <span>Tanah Kavling Dijual</span>
                                    </a>
                                    <a href="{{ route('properties.index', ['listing_type' => 'dijual', 'property_type' => 'gudang']) }}"
                                       class="flex items-center gap-2.5 p-2 rounded-xl text-slate-700 hover:bg-pink-50 hover:text-pink-600 transition-colors {{ request('listing_type') === 'dijual' && request('property_type') === 'gudang' ? 'bg-pink-50 text-pink-600 font-bold' : '' }}">
                                        <span class="w-6 h-6 rounded-lg bg-pink-50 text-pink-600 flex items-center justify-center text-xs"><i class="fa-solid fa-warehouse"></i></span>
                                        <span>Gudang &amp; Pabrik Dijual</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Dropdown Disewa -->
                        <div class="relative group" x-data="{ openRent: false }" @mouseenter="openRent = true" @mouseleave="openRent = false">
                            <a href="{{ route('properties.for-rent') }}"
                               class="px-3.5 py-2 rounded-xl text-sm font-semibold flex items-center gap-1.5 transition-colors {{ request()->routeIs('properties.for-rent') || request('listing_type') === 'disewa' ? 'text-white bg-pink-700 font-bold shadow-xs' : 'text-pink-50 hover:text-white hover:bg-pink-700/60' }}">
                                <span>Disewa</span>
                                <i class="fa-solid fa-chevron-down text-[10px] text-pink-200 transition-transform duration-200" :class="openRent ? 'rotate-180 text-white' : ''"></i>
                            </a>

                            <div x-show="openRent"
                                 x-cloak
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 translate-y-0"
                                 x-transition:leave-end="opacity-0 translate-y-1"
                                 @click.away="openRent = false"
                                 class="absolute left-0 top-full pt-1.5 w-60 z-50">
                                <div class="bg-white rounded-2xl shadow-xl border border-pink-100 p-2 text-xs font-medium space-y-0.5 text-slate-800">
                                    <a href="{{ route('properties.for-rent') }}"
                                       class="flex items-center justify-between p-2 rounded-xl text-slate-900 hover:bg-pink-50 hover:text-pink-600 font-bold border-b border-pink-100 mb-1 transition-colors">
                                        <div class="flex items-center gap-2">
                                            <i class="fa-solid fa-key text-pink-600"></i>
                                            <span>Semua Properti Disewa</span>
                                        </div>
                                        <i class="fa-solid fa-arrow-right text-[10px] text-slate-400"></i>
                                    </a>
                                    <a href="{{ route('properties.index', ['listing_type' => 'disewa', 'property_type' => 'rumah']) }}"
                                       class="flex items-center gap-2.5 p-2 rounded-xl text-slate-700 hover:bg-pink-50 hover:text-pink-600 transition-colors {{ request('listing_type') === 'disewa' && request('property_type') === 'rumah' ? 'bg-pink-50 text-pink-600 font-bold' : '' }}">
                                        <span class="w-6 h-6 rounded-lg bg-pink-50 text-pink-600 flex items-center justify-center text-xs"><i class="fa-solid fa-house"></i></span>
                                        <span>Rumah Disewa</span>
                                    </a>
                                    <a href="{{ route('properties.index', ['listing_type' => 'disewa', 'property_type' => 'apartemen']) }}"
                                       class="flex items-center gap-2.5 p-2 rounded-xl text-slate-700 hover:bg-pink-50 hover:text-pink-600 transition-colors {{ request('listing_type') === 'disewa' && request('property_type') === 'apartemen' ? 'bg-pink-50 text-pink-600 font-bold' : '' }}">
                                        <span class="w-6 h-6 rounded-lg bg-pink-50 text-pink-600 flex items-center justify-center text-xs"><i class="fa-solid fa-building"></i></span>
                                        <span>Apartemen Disewa</span>
                                    </a>
                                    <a href="{{ route('properties.index', ['listing_type' => 'disewa', 'property_type' => 'ruko']) }}"
                                       class="flex items-center gap-2.5 p-2 rounded-xl text-slate-700 hover:bg-pink-50 hover:text-pink-600 transition-colors {{ request('listing_type') === 'disewa' && request('property_type') === 'ruko' ? 'bg-pink-50 text-pink-600 font-bold' : '' }}">
                                        <span class="w-6 h-6 rounded-lg bg-pink-50 text-pink-600 flex items-center justify-center text-xs"><i class="fa-solid fa-store"></i></span>
                                        <span>Ruko Disewa</span>
                                    </a>
                                    <a href="{{ route('properties.index', ['listing_type' => 'disewa', 'property_type' => 'villa']) }}"
                                       class="flex items-center gap-2.5 p-2 rounded-xl text-slate-700 hover:bg-pink-50 hover:text-pink-600 transition-colors {{ request('listing_type') === 'disewa' && request('property_type') === 'villa' ? 'bg-pink-50 text-pink-600 font-bold' : '' }}">
                                        <span class="w-6 h-6 rounded-lg bg-pink-50 text-pink-600 flex items-center justify-center text-xs"><i class="fa-solid fa-umbrella-beach"></i></span>
                                        <span>Villa Disewa</span>
                                    </a>
                                    <a href="{{ route('properties.index', ['listing_type' => 'disewa', 'property_type' => 'tanah']) }}"
                                       class="flex items-center gap-2.5 p-2 rounded-xl text-slate-700 hover:bg-pink-50 hover:text-pink-600 transition-colors {{ request('listing_type') === 'disewa' && request('property_type') === 'tanah' ? 'bg-pink-50 text-pink-600 font-bold' : '' }}">
                                        <span class="w-6 h-6 rounded-lg bg-pink-50 text-pink-600 flex items-center justify-center text-xs"><i class="fa-solid fa-map"></i></span>
                                        <span>Tanah Disewa</span>
                                    </a>
                                    <a href="{{ route('properties.index', ['listing_type' => 'disewa', 'property_type' => 'gudang']) }}"
                                       class="flex items-center gap-2.5 p-2 rounded-xl text-slate-700 hover:bg-pink-50 hover:text-pink-600 transition-colors {{ request('listing_type') === 'disewa' && request('property_type') === 'gudang' ? 'bg-pink-50 text-pink-600 font-bold' : '' }}">
                                        <span class="w-6 h-6 rounded-lg bg-pink-50 text-pink-600 flex items-center justify-center text-xs"><i class="fa-solid fa-warehouse"></i></span>
                                        <span>Gudang &amp; Pabrik Disewa</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('properties.new-launch') }}" class="px-3.5 py-2 rounded-xl text-sm font-semibold {{ request()->routeIs('properties.new-launch') ? 'text-white bg-pink-700 font-bold shadow-xs' : 'text-pink-50 hover:text-white hover:bg-pink-700/60' }} transition-colors flex items-center gap-1.5">
                            Proyek Baru
                            <span class="bg-white text-pink-700 text-[10px] font-extrabold px-1.5 py-0.5 rounded-full uppercase tracking-wider shadow-xs">Hot</span>
                        </a>
                        <a href="{{ route('kpr') }}" class="px-3.5 py-2 rounded-xl text-sm font-semibold {{ request()->routeIs('kpr') ? 'text-white bg-pink-700 font-bold shadow-xs' : 'text-pink-50 hover:text-white hover:bg-pink-700/60' }} transition-colors">
                            Simulasi KPR
                        </a>
                        <a href="{{ route('agents.index') }}" class="px-3.5 py-2 rounded-xl text-sm font-semibold {{ request()->routeIs('agents.*') && !request()->routeIs('agents.join') ? 'text-white bg-pink-700 font-bold shadow-xs' : 'text-pink-50 hover:text-white hover:bg-pink-700/60' }} transition-colors">
                            Cari Agen
                        </a>
                        <a href="{{ route('agents.join') }}" class="px-3.5 py-2 rounded-xl text-sm font-semibold {{ request()->routeIs('agents.join') ? 'text-white bg-pink-700 font-bold shadow-xs' : 'text-pink-50 hover:text-white hover:bg-pink-700/60' }} transition-colors flex items-center gap-1.5">
                            <i class="fa-solid fa-user-plus text-pink-200 text-xs"></i>
                            <span>Gabung Agen</span>
                        </a>
                    </nav>
                </div>

                <!-- Right Action Buttons -->
                <div class="hidden sm:flex items-center gap-3">
                    <!-- Bandingkan Properti -->
                    <a href="{{ route('properties.compare') }}" class="relative p-2.5 rounded-xl text-white hover:bg-pink-700/70 transition-colors" title="Bandingkan Properti">
                        <i class="fa-solid fa-code-compare text-lg"></i>
                        <span x-show="compareCount > 0" x-text="compareCount" class="absolute -top-1 -right-1 w-5 h-5 bg-white text-pink-700 text-xs font-black rounded-full flex items-center justify-center shadow-xs"></span>
                    </a>

                    <!-- Favorit -->
                    <a href="{{ route('properties.favorites') }}" class="relative p-2.5 rounded-xl text-white hover:bg-pink-700/70 transition-colors" title="Properti Favorit">
                        <i class="fa-regular fa-heart text-lg"></i>
                        <span x-show="favoriteCount > 0" x-text="favoriteCount" class="absolute -top-1 -right-1 w-5 h-5 bg-white text-pink-700 text-xs font-black rounded-full flex items-center justify-center shadow-xs"></span>
                    </a>

                    <!-- Pasang Iklan / Titip Properti Button -->
                    <a href="{{ route('titip-properti') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white hover:bg-pink-50 text-pink-700 text-sm font-extrabold shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5">
                        <i class="fa-solid fa-plus-circle text-pink-600"></i>
                        <span>Titip Properti</span>
                    </a>
                </div>

                <!-- Mobile Hamburger Button -->
                <div class="flex items-center gap-2 sm:hidden">
                    <a href="{{ route('properties.favorites') }}" class="relative p-2 text-white hover:bg-pink-700/60 rounded-xl transition-colors">
                        <i class="fa-regular fa-heart text-lg"></i>
                        <span x-show="favoriteCount > 0" x-text="favoriteCount" class="absolute -top-1 -right-1 w-4 h-4 bg-white text-pink-700 text-[10px] font-black rounded-full flex items-center justify-center shadow-xs"></span>
                    </a>
                    <button @click="toggleMobileMenu()" class="p-2.5 rounded-xl text-white hover:bg-pink-700/60 focus:outline-none transition-colors" aria-label="Toggle Menu">
                        <i class="fa-solid" :class="mobileMenuOpen ? 'fa-xmark text-xl' : 'fa-bars text-xl'"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Drawer -->
        <div x-show="mobileMenuOpen" x-cloak @click.away="closeMobileMenu()" class="lg:hidden border-t border-pink-700 bg-white px-4 pt-3 pb-6 space-y-2 shadow-2xl text-slate-800">
            <!-- Mobile Dijual Accordion -->
            <div x-data="{ openMobileSale: false }" class="rounded-xl overflow-hidden bg-slate-50/70 border border-slate-100">
                <div class="flex items-center justify-between px-4 py-2.5 text-sm font-semibold text-slate-800 cursor-pointer hover:bg-pink-50 hover:text-pink-600 transition-colors"
                     @click="openMobileSale = !openMobileSale">
                    <div class="flex items-center gap-2.5">
                        <i class="fa-solid fa-house-chimney w-5 text-pink-600"></i>
                        <span>Properti Dijual</span>
                    </div>
                    <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform duration-200"
                       :class="openMobileSale ? 'rotate-180 text-pink-600' : ''"></i>
                </div>
                <div x-show="openMobileSale" x-cloak class="px-4 pb-3 pt-1 space-y-1 bg-white border-t border-slate-100 text-xs">
                    <a href="{{ route('properties.for-sale') }}" class="block py-1.5 font-bold text-pink-600 hover:underline">
                        Lihat Semua Properti Dijual &rarr;
                    </a>
                    <a href="{{ route('properties.index', ['listing_type' => 'dijual', 'property_type' => 'rumah']) }}" class="flex items-center gap-2 py-1.5 text-slate-600 hover:text-pink-600">
                        <i class="fa-solid fa-house text-slate-400 text-[10px] w-4"></i> Rumah Dijual
                    </a>
                    <a href="{{ route('properties.index', ['listing_type' => 'dijual', 'property_type' => 'apartemen']) }}" class="flex items-center gap-2 py-1.5 text-slate-600 hover:text-pink-600">
                        <i class="fa-solid fa-building text-slate-400 text-[10px] w-4"></i> Apartemen Dijual
                    </a>
                    <a href="{{ route('properties.index', ['listing_type' => 'dijual', 'property_type' => 'ruko']) }}" class="flex items-center gap-2 py-1.5 text-slate-600 hover:text-pink-600">
                        <i class="fa-solid fa-store text-slate-400 text-[10px] w-4"></i> Ruko Dijual
                    </a>
                    <a href="{{ route('properties.index', ['listing_type' => 'dijual', 'property_type' => 'villa']) }}" class="flex items-center gap-2 py-1.5 text-slate-600 hover:text-pink-600">
                        <i class="fa-solid fa-umbrella-beach text-slate-400 text-[10px] w-4"></i> Villa Dijual
                    </a>
                    <a href="{{ route('properties.index', ['listing_type' => 'dijual', 'property_type' => 'tanah']) }}" class="flex items-center gap-2 py-1.5 text-slate-600 hover:text-pink-600">
                        <i class="fa-solid fa-map text-pink-500 text-[10px] w-4"></i> Tanah Kavling Dijual
                    </a>
                    <a href="{{ route('properties.index', ['listing_type' => 'dijual', 'property_type' => 'gudang']) }}" class="flex items-center gap-2 py-1.5 text-slate-600 hover:text-pink-600">
                        <i class="fa-solid fa-warehouse text-slate-500 text-[10px] w-4"></i> Gudang &amp; Pabrik Dijual
                    </a>
                </div>
            </div>

            <!-- Mobile Disewa Accordion -->
            <div x-data="{ openMobileRent: false }" class="rounded-xl overflow-hidden bg-slate-50/70 border border-slate-100">
                <div class="flex items-center justify-between px-4 py-2.5 text-sm font-semibold text-slate-800 cursor-pointer hover:bg-pink-50 hover:text-pink-600 transition-colors"
                     @click="openMobileRent = !openMobileRent">
                    <div class="flex items-center gap-2.5">
                        <i class="fa-solid fa-key w-5 text-pink-600"></i>
                        <span>Properti Disewa</span>
                    </div>
                    <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform duration-200"
                       :class="openMobileRent ? 'rotate-180 text-pink-600' : ''"></i>
                </div>
                <div x-show="openMobileRent" x-cloak class="px-4 pb-3 pt-1 space-y-1 bg-white border-t border-slate-100 text-xs">
                    <a href="{{ route('properties.for-rent') }}" class="block py-1.5 font-bold text-pink-600 hover:underline">
                        Lihat Semua Properti Disewa &rarr;
                    </a>
                    <a href="{{ route('properties.index', ['listing_type' => 'disewa', 'property_type' => 'rumah']) }}" class="flex items-center gap-2 py-1.5 text-slate-600 hover:text-pink-600">
                        <i class="fa-solid fa-house text-slate-400 text-[10px] w-4"></i> Rumah Disewa
                    </a>
                    <a href="{{ route('properties.index', ['listing_type' => 'disewa', 'property_type' => 'apartemen']) }}" class="flex items-center gap-2 py-1.5 text-slate-600 hover:text-pink-600">
                        <i class="fa-solid fa-building text-slate-400 text-[10px] w-4"></i> Apartemen Disewa
                    </a>
                    <a href="{{ route('properties.index', ['listing_type' => 'disewa', 'property_type' => 'ruko']) }}" class="flex items-center gap-2 py-1.5 text-slate-600 hover:text-pink-600">
                        <i class="fa-solid fa-store text-slate-400 text-[10px] w-4"></i> Ruko Disewa
                    </a>
                    <a href="{{ route('properties.index', ['listing_type' => 'disewa', 'property_type' => 'villa']) }}" class="flex items-center gap-2 py-1.5 text-slate-600 hover:text-pink-600">
                        <i class="fa-solid fa-umbrella-beach text-slate-400 text-[10px] w-4"></i> Villa Disewa
                    </a>
                    <a href="{{ route('properties.index', ['listing_type' => 'disewa', 'property_type' => 'tanah']) }}" class="flex items-center gap-2 py-1.5 text-slate-600 hover:text-pink-600">
                        <i class="fa-solid fa-map text-pink-500 text-[10px] w-4"></i> Tanah Disewa
                    </a>
                    <a href="{{ route('properties.index', ['listing_type' => 'disewa', 'property_type' => 'gudang']) }}" class="flex items-center gap-2 py-1.5 text-slate-600 hover:text-pink-600">
                        <i class="fa-solid fa-warehouse text-slate-500 text-[10px] w-4"></i> Gudang &amp; Pabrik Disewa
                    </a>
                </div>
            </div>

            <a href="{{ route('properties.new-launch') }}" class="block px-4 py-2.5 rounded-lg text-sm font-semibold text-slate-800 hover:bg-pink-50 hover:text-pink-600">
                <i class="fa-solid fa-city w-6 text-pink-600"></i> Proyek Baru
            </a>
            <a href="{{ route('kpr') }}" class="block px-4 py-2.5 rounded-lg text-sm font-semibold text-slate-800 hover:bg-pink-50 hover:text-pink-600">
                <i class="fa-solid fa-calculator w-6 text-pink-600"></i> Simulasi KPR
            </a>
            <a href="{{ route('agents.index') }}" class="block px-4 py-2.5 rounded-lg text-sm font-semibold text-slate-800 hover:bg-pink-50 hover:text-pink-600">
                <i class="fa-solid fa-id-card-clip w-6 text-pink-600"></i> Cari Agen
            </a>
            <a href="{{ route('agents.join') }}" class="block px-4 py-2.5 rounded-lg text-sm font-semibold text-slate-800 hover:bg-pink-50 hover:text-pink-600">
                <i class="fa-solid fa-user-plus w-6 text-pink-600"></i> Gabung Jadi Agen {{ $pubSiteName }}
            </a>
            <a href="{{ route('properties.compare') }}" class="block px-4 py-2.5 rounded-lg text-sm font-semibold text-slate-800 hover:bg-pink-50 hover:text-pink-600">
                <i class="fa-solid fa-code-compare w-6 text-pink-600"></i> Bandingkan Properti
            </a>
            <div class="pt-2 border-t border-slate-100 space-y-2">
                <a href="{{ $hotlineLink }}" target="{{ $isHotlineWa ? '_blank' : '_self' }}" class="w-full flex items-center justify-between px-4 py-2.5 rounded-xl bg-pink-50 border border-pink-200 text-pink-700 hover:bg-pink-100 transition-colors text-xs font-bold">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-headset text-pink-600"></i> Hotline Konsultasi
                    </span>
                    <span class="font-extrabold text-pink-800">{{ $pubSiteHotline }}</span>
                </a>
                <a href="{{ route('titip-properti') }}" class="w-full flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold shadow-md">
                    <i class="fa-solid fa-plus-circle"></i> Titip Properti Anda
                </a>
            </div>
        </div>
    </header>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-4 shadow-sm flex items-start gap-3">
                <i class="fa-solid fa-circle-check text-emerald-600 text-xl mt-0.5"></i>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-emerald-900">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="rounded-xl bg-rose-50 border border-rose-200 p-4 shadow-sm flex items-start gap-3">
                <i class="fa-solid fa-circle-exclamation text-rose-600 text-xl mt-0.5"></i>
                <div class="flex-1">
                    <p class="text-sm font-bold text-rose-900">Terdapat beberapa kesalahan pada isian Anda:</p>
                    <ul class="mt-1 list-disc list-inside text-xs text-rose-700">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Main Page Content -->
    <main class="flex-1 bg-[#f0f2f5]">
        @yield('content')
    </main>

    <!-- Global Footer -->
    <footer class="bg-white text-slate-600 mt-10 sm:mt-12 border-t border-pink-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 sm:pt-12 pb-8 sm:pb-10">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 sm:gap-8">
                <!-- Col 1: Brand Info -->
                <div class="lg:col-span-2 space-y-4">
                    <div class="flex items-center gap-3">
                        @if($pubSiteLogo)
                            <div class="flex flex-col justify-center">
                                <img src="{{ $pubSiteLogo }}" alt="{{ $pubSiteName }}" class="h-9 sm:h-11 w-auto max-w-[180px] sm:max-w-[240px] object-contain shrink-0 rounded-lg">
                                @if($pubSiteTagline)
                                    <span class="sm:hidden text-[9px] uppercase font-bold tracking-wider text-slate-400 mt-1 truncate max-w-[180px]">
                                        {{ $pubSiteTagline }}
                                    </span>
                                @endif
                            </div>
                            @if($pubSiteTagline)
                                <div class="hidden sm:flex flex-col justify-center border-l border-slate-200 pl-3 py-0.5">
                                    <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 leading-tight block max-w-[220px]">
                                        {{ $pubSiteTagline }}
                                    </span>
                                </div>
                            @endif
                        @else
                            <div class="w-10 h-10 rounded-xl bg-pink-600 flex items-center justify-center text-white shadow-md shadow-pink-500/20 shrink-0">
                                <i class="fa-solid fa-building-user text-lg"></i>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 leading-none">
                                    @if(str_contains(strtolower($pubSiteName), 'yproperti'))
                                        YProperti<span class="text-pink-600 font-extrabold">.com</span>
                                    @else
                                        {{ $pubSiteName }}
                                    @endif
                                </span>
                                <span class="text-[9px] sm:text-[10px] uppercase font-bold tracking-widest text-slate-400 leading-tight mt-0.5">
                                    {{ $pubSiteTagline ?: 'Portal Properti & Marketplace' }}
                                </span>
                            </div>
                        @endif
                    </div>
                    <p class="text-sm text-slate-500 leading-relaxed max-w-sm">
                        Platform direktori properti komprehensif {{ $pubSiteName }} (yproperti.com) yang memadukan ekosistem jaringan agen profesional dengan kemudahan pencarian rumah dan simulasi KPR.
                    </p>
                    <div class="pt-2 flex items-center gap-3">
                        <a href="#" class="w-9 h-9 rounded-lg bg-pink-50 hover:bg-pink-600 text-pink-600 hover:text-white border border-pink-100 flex items-center justify-center transition-colors">
                            <i class="fa-brands fa-instagram"></i>
                        </a>
                        <a href="#" class="w-9 h-9 rounded-lg bg-pink-50 hover:bg-pink-600 text-pink-600 hover:text-white border border-pink-100 flex items-center justify-center transition-colors">
                            <i class="fa-brands fa-youtube"></i>
                        </a>
                        <a href="#" class="w-9 h-9 rounded-lg bg-pink-50 hover:bg-pink-600 text-pink-600 hover:text-white border border-pink-100 flex items-center justify-center transition-colors">
                            <i class="fa-brands fa-tiktok"></i>
                        </a>
                        <a href="#" class="w-9 h-9 rounded-lg bg-pink-50 hover:bg-pink-600 text-pink-600 hover:text-white border border-pink-100 flex items-center justify-center transition-colors">
                            <i class="fa-brands fa-linkedin-in"></i>
                        </a>
                    </div>
                    <div class="pt-1">
                        <a href="{{ $hotlineLink }}" target="{{ $isHotlineWa ? '_blank' : '_self' }}" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-pink-50 hover:bg-pink-100 text-pink-700 text-xs font-bold transition-colors border border-pink-200">
                            <i class="fa-solid fa-headset text-pink-600"></i>
                            <span>Hotline Konsultasi: <strong>{{ $pubSiteHotline }}</strong></span>
                        </a>
                    </div>
                </div>

                <!-- Col 2: Properti Populer -->
                <div>
                    <h3 class="text-slate-900 text-sm font-bold uppercase tracking-wider mb-4">Properti Populer</h3>
                    <ul class="space-y-2.5 text-sm text-slate-500">
                        <li><a href="{{ route('properties.index', ['city' => 'Jakarta Selatan']) }}" class="hover:text-pink-600 transition-colors">Rumah Jakarta Selatan</a></li>
                        <li><a href="{{ route('properties.index', ['city' => 'Tangerang Selatan']) }}" class="hover:text-pink-600 transition-colors">Cluster BSD City</a></li>
                        <li><a href="{{ route('properties.index', ['city' => 'Surabaya']) }}" class="hover:text-pink-600 transition-colors">Mansion Surabaya Barat</a></li>
                        <li><a href="{{ route('properties.index', ['city' => 'Badung']) }}" class="hover:text-pink-600 transition-colors">Luxury Villa Bali</a></li>
                        <li><a href="{{ route('properties.index', ['property_type' => 'apartemen']) }}" class="hover:text-pink-600 transition-colors">Apartemen SCBD & Menteng</a></li>
                    </ul>
                </div>

                <!-- Col 3: Layanan Kami -->
                <div>
                    <h3 class="text-slate-900 text-sm font-bold uppercase tracking-wider mb-4">Layanan & Fitur</h3>
                    <ul class="space-y-2.5 text-sm text-slate-500">
                        <li><a href="{{ route('properties.for-sale') }}" class="hover:text-pink-600 transition-colors">Cari Rumah Dijual</a></li>
                        <li><a href="{{ route('properties.for-rent') }}" class="hover:text-pink-600 transition-colors">Sewa Rumah & Apartemen</a></li>
                        <li><a href="{{ route('properties.new-launch') }}" class="hover:text-pink-600 transition-colors">Proyek Baru Developer</a></li>
                        <li><a href="{{ route('kpr') }}" class="hover:text-pink-600 transition-colors">Simulasi Cicilan KPR</a></li>
                        <li><a href="{{ route('titip-properti') }}" class="hover:text-pink-600 transition-colors">Titip Jual / Sewa Properti</a></li>
                        <li><a href="{{ route('agents.index') }}" class="hover:text-pink-600 transition-colors">Direktori Agen Resmi</a></li>
                    </ul>
                </div>

                <!-- Col 4: Mitra Bank KPR -->
                <div>
                    <h3 class="text-slate-900 text-sm font-bold uppercase tracking-wider mb-4">Mitra Perbankan KPR</h3>
                    <p class="text-xs text-slate-500 mb-3">Bekerja sama dengan bank terkemuka untuk persetujuan KPR tercepat:</p>
                    <div class="grid grid-cols-2 gap-2 text-xs font-semibold text-slate-700">
                        <div class="bg-pink-50/70 px-2.5 py-1.5 rounded-lg border border-pink-100 text-center">Bank BCA</div>
                        <div class="bg-pink-50/70 px-2.5 py-1.5 rounded-lg border border-pink-100 text-center">Bank Mandiri</div>
                        <div class="bg-pink-50/70 px-2.5 py-1.5 rounded-lg border border-pink-100 text-center">Bank BRI</div>
                        <div class="bg-pink-50/70 px-2.5 py-1.5 rounded-lg border border-pink-100 text-center">CIMB Niaga</div>
                        <div class="bg-pink-50/70 px-2.5 py-1.5 rounded-lg border border-pink-100 text-center">Bank BNI</div>
                        <div class="bg-pink-50/70 px-2.5 py-1.5 rounded-lg border border-pink-100 text-center">Bank BTN</div>
                    </div>
                </div>
            </div>

            <!-- Bottom Copyright -->
            <div class="mt-12 pt-8 border-t border-pink-100 text-xs text-slate-400 flex flex-col sm:flex-row justify-between items-center gap-4">
                <p>&copy; {{ date('Y') }} {{ $pubSiteName }}. Hak Cipta Dilindungi Undang-Undang.</p>
                <div class="flex items-center gap-6">
                    <a href="#" class="hover:text-pink-600 transition-colors">Kebijakan Privasi</a>
                    <a href="#" class="hover:text-pink-600 transition-colors">Syarat & Ketentuan</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Shared JS Helpers for Wishlist and Compare -->
    <script>
        function toggleFavorite(id) {
            let favs = JSON.parse(localStorage.getItem('pn123_favs') || '[]');
            const index = favs.indexOf(id);
            if (index > -1) {
                favs.splice(index, 1);
            } else {
                favs.push(id);
            }
            localStorage.setItem('pn123_favs', JSON.stringify(favs));
            window.dispatchEvent(new CustomEvent('favorites-updated', { detail: favs }));
        }

        function isFavorite(id) {
            let favs = JSON.parse(localStorage.getItem('pn123_favs') || '[]');
            return favs.includes(id);
        }

        function toggleCompare(id) {
            let comps = JSON.parse(localStorage.getItem('pn123_compare') || '[]');
            const index = comps.indexOf(id);
            if (index > -1) {
                comps.splice(index, 1);
            } else {
                if (comps.length >= 3) {
                    alert('Maksimal membandingkan 3 properti sekaligus.');
                    return;
                }
                comps.push(id);
            }
            localStorage.setItem('pn123_compare', JSON.stringify(comps));
            window.dispatchEvent(new CustomEvent('compare-updated', { detail: comps }));
        }

        function isCompared(id) {
            let comps = JSON.parse(localStorage.getItem('pn123_compare') || '[]');
            return comps.includes(id);
        }

        window.addEventListener('favorites-updated', (e) => {
            const countEl = document.querySelector('[x-text="favoriteCount"]');
            if (countEl && window.Alpine) {
                Alpine.raw(countEl);
            }
            // Reload if on favorite page
            if (window.location.pathname === '/favorit') {
                window.location.reload();
            }
        });
    </script>
    @stack('scripts')
</body>
</html>

