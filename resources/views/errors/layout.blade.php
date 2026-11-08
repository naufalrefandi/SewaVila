<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') - {{ class_exists(\App\Models\Setting::class) ? \App\Models\Setting::getSiteName() : 'YProperti.com' }}</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">

    <!-- Font Awesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
        }
    </style>
</head>
<body class="min-h-full flex flex-col justify-between text-slate-800 antialiased bg-radial-[at_top] from-pink-50/70 via-slate-50 to-slate-100">

    @php
        $siteName = 'YProperti.com';
        $siteLogo = null;
        $siteHotline = '021-5098-8888';
        $cleanHotline = '0215098888';
        try {
            if (class_exists(\App\Models\Setting::class)) {
                $siteName = \App\Models\Setting::getSiteName();
                $siteLogo = \App\Models\Setting::getSiteLogo();
                $siteHotline = \App\Models\Setting::getSiteHotline();
                $cleanHotline = \App\Models\Setting::getCleanSiteHotline();
            }
        } catch (\Throwable $e) {
            // Safe fallback if database is unreachable in 500 error
        }
        $isWa = str_starts_with($cleanHotline, '08') || str_starts_with($cleanHotline, '62');
        $hotlineUrl = $isWa
            ? 'https://wa.me/' . (str_starts_with($cleanHotline, '0') ? '62' . substr($cleanHotline, 1) : $cleanHotline) . '?text=' . urlencode('Halo ' . $siteName . ', saya mengalami kendala di website.')
            : 'tel:' . $cleanHotline;
    @endphp

    <!-- Header Navbar Minimal -->
    <header class="py-4 px-4 sm:px-8 border-b border-pink-100/80 bg-white/80 backdrop-blur-md sticky top-0 z-20">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center gap-2.5 group">
                @if($siteLogo)
                    <img src="{{ $siteLogo }}" alt="{{ $siteName }}" class="h-9 w-auto max-w-[42px] object-contain rounded-lg">
                @else
                    <div class="w-9 h-9 rounded-xl bg-pink-600 text-white flex items-center justify-center shadow-md shadow-pink-600/20 group-hover:scale-105 transition-transform shrink-0">
                        <i class="fa-solid fa-building-user text-base"></i>
                    </div>
                @endif
                <div class="flex flex-col">
                    <span class="text-xl font-black tracking-tight text-slate-900 leading-none">
                        @if(str_contains(strtolower($siteName), 'yproperti.com'))
                            YProperti<span class="text-pink-600 font-extrabold">.com</span>
                        @else
                            {{ $siteName }}
                        @endif
                    </span>
                    <span class="text-[9px] uppercase font-bold tracking-widest text-slate-400 leading-tight mt-0.5">
                        Portal Properti &amp; Marketplace
                    </span>
                </div>
            </a>

            <div class="flex items-center gap-3">
                <a href="{{ $hotlineUrl }}" target="{{ $isWa ? '_blank' : '_self' }}" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-pink-50 hover:bg-pink-100 text-pink-700 text-xs font-bold border border-pink-200 transition-colors">
                    <i class="fa-solid fa-headset text-pink-600"></i>
                    <span>Bantuan: {{ $siteHotline }}</span>
                </a>
                <a href="{{ url('/') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-pink-600 text-white text-xs font-bold transition-all shadow-xs">
                    <i class="fa-solid fa-house text-[11px]"></i>
                    <span>Beranda</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Error Content Container -->
    <main class="flex-1 flex items-center justify-center p-4 sm:p-8">
        <div class="max-w-xl w-full text-center py-6 sm:py-10">
            @yield('content')
        </div>
    </main>

    <!-- Minimal Footer -->
    <footer class="py-4 px-4 text-center text-xs text-slate-400 border-t border-slate-200/80 bg-white/50">
        <div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2">
            <p>&copy; {{ date('Y') }} {{ $siteName }}. Seluruh hak cipta dilindungi.</p>
            <div class="flex items-center gap-4 text-slate-500">
                <a href="{{ url('/') }}" class="hover:text-pink-600 transition-colors">Beranda</a>
                <span>&bull;</span>
                <a href="{{ url('/properti') }}" class="hover:text-pink-600 transition-colors">Cari Properti</a>
                <span>&bull;</span>
                <a href="{{ $hotlineUrl }}" target="{{ $isWa ? '_blank' : '_self' }}" class="hover:text-pink-600 transition-colors">Hubungi Hotline</a>
            </div>
        </div>
    </footer>

</body>
</html>

