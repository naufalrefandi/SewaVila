<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#faf7f9]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Dashboard - ' . \App\Models\Setting::getSiteName())</title>

    <!-- Favicon (dynamic: from site logo setting) -->
    @php $faviconUrl = \App\Models\Setting::getSiteLogo(); @endphp
    @if($faviconUrl)
        <link rel="icon" type="image/png" href="{{ $faviconUrl }}">
        <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
    @else
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @endif

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">

    <!-- Font Awesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

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
        .sidebar-scroll { scrollbar-width: thin; scrollbar-color: rgba(244, 114, 182, 0.3) transparent; }
        .sidebar-scroll::-webkit-scrollbar { width: 5px; }
        .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: rgba(244, 114, 182, 0.25); border-radius: 9999px; }
        .sidebar-scroll::-webkit-scrollbar-thumb:hover { background: rgba(236, 72, 153, 0.5); }
    </style>
</head>
<body class="flex min-h-screen text-slate-800 antialiased"
      x-data="{ 
          sidebarOpen: false,
          toggleSidebar() {
              if (this.sidebarOpen) {
                  this.closeSidebar();
              } else {
                  this.openSidebar();
              }
          },
          openSidebar() {
              this.sidebarOpen = true;
              if (!history.state || !history.state.adminSidebarOpen) {
                  history.pushState({ adminSidebarOpen: true }, '');
              }
          },
          closeSidebar(fromPopstate = false) {
              if (!this.sidebarOpen) return;
              this.sidebarOpen = false;
              if (!fromPopstate && history.state && history.state.adminSidebarOpen) {
                  history.back();
              }
          }
      }"
      x-init="
          window.addEventListener('popstate', (e) => {
              if (sidebarOpen) {
                  closeSidebar(true);
              }
          });
      ">
    <!-- Admin Sidebar -->
    <aside class="fixed inset-y-0 left-0 z-50 w-64 h-screen bg-white text-slate-700 flex flex-col justify-between transition-transform duration-300 lg:translate-x-0 border-r border-pink-100 shadow-xs"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
        <!-- Admin / Agent Logo Header (Fixed Top) -->
        <div class="h-16 shrink-0 flex items-center gap-3 px-5 border-b border-pink-100 bg-white">
            @php
                $adminSiteLogo = \App\Models\Setting::getSiteLogo();
                $adminSiteName = \App\Models\Setting::getSiteName();
            @endphp
            @if($adminSiteLogo)
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 min-w-0">
                    <img src="{{ $adminSiteLogo }}" alt="{{ $adminSiteName }}" class="h-8 sm:h-9 w-auto max-w-[145px] object-contain rounded-lg shrink-0">
                    <span class="text-[9px] text-pink-600 font-bold uppercase tracking-wider block border-l border-slate-200 pl-2 shrink-0">
                        {{ auth()->user() && auth()->user()->isAgent() ? 'Portal Agen' : 'Admin Portal' }}
                    </span>
                </a>
            @else
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-pink-600 flex items-center justify-center text-white font-bold shadow-md shadow-pink-600/20 shrink-0">
                        <i class="fa-solid fa-building-user text-lg"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="text-base font-black tracking-tight text-slate-900 leading-tight truncate">
                            {{ $adminSiteName }}
                        </div>
                        <span class="text-[10px] text-pink-600 font-bold uppercase tracking-wider block truncate">
                            {{ auth()->user() && auth()->user()->isAgent() ? 'Portal Agen' : 'Admin Portal' }}
                        </span>
                    </div>
                </a>
            @endif
        </div>

        <!-- Middle: Scrollable Nav Links -->
        <div class="flex-1 overflow-y-auto min-h-0 py-3 px-3 sidebar-scroll">
            <nav class="space-y-1 text-sm font-semibold">
                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-pink-600 text-white shadow-md shadow-pink-500/20' : 'text-slate-600 hover:text-pink-600 hover:bg-pink-50/70' }}">
                    <i class="fa-solid fa-chart-pie w-6 text-base text-center"></i>
                    <span>Dashboard</span>
                </a>

                <div class="pt-3 pb-1 px-4 text-[10px] font-extrabold text-pink-500 uppercase tracking-wider">Listing &amp; Properti</div>

                @if(auth()->user() && auth()->user()->isAgent())
                    <a href="{{ route('admin.properties') }}"
                       class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.properties') && !request('scope') && !request('type') && !request()->routeIs('admin.properties.create') ? 'bg-pink-600 text-white shadow-md shadow-pink-500/20' : 'text-slate-600 hover:text-pink-600 hover:bg-pink-50/70' }}">
                        <i class="fa-solid fa-user-tag w-6 text-base text-center text-pink-500"></i>
                        <span>Listing Saya</span>
                    </a>

                    <a href="{{ route('admin.agent.topup') }}"
                       class="flex items-center justify-between px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.agent.topup') ? 'bg-pink-600 text-white shadow-md shadow-pink-500/20' : 'text-slate-600 hover:text-pink-600 hover:bg-pink-50/70' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-cart-plus w-6 text-base text-center text-emerald-500"></i>
                            <span>Topup Kuota Listing</span>
                        </div>
                        @if(auth()->user()->agent)
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold {{ auth()->user()->agent->remaining_quota > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                {{ auth()->user()->agent->remaining_quota }} Slot
                            </span>
                        @endif
                    </a>

                    @if(auth()->user()->isLeader())
                        <a href="{{ route('admin.properties', ['scope' => 'team']) }}"
                           class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request('scope') === 'team' ? 'bg-amber-600 text-white shadow-md' : 'text-slate-600 hover:text-pink-600 hover:bg-pink-50/70' }}">
                            <i class="fa-solid fa-crown w-6 text-base text-center text-amber-500"></i>
                            <span>Listing Tim Saya</span>
                        </a>
                    @endif

                    <a href="{{ route('admin.properties', ['scope' => 'all']) }}"
                       class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request('scope') === 'all' ? 'bg-pink-50 text-pink-700 font-bold border border-pink-200' : 'text-slate-600 hover:text-pink-600 hover:bg-pink-50/70' }}">
                        <i class="fa-solid fa-network-wired w-6 text-base text-center text-pink-400"></i>
                        <span>Semua Listing Agen</span>
                    </a>
                @else
                    <a href="{{ route('admin.properties') }}"
                       class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.properties') && !request('type') && !request()->routeIs('admin.properties.create') ? 'bg-pink-600 text-white shadow-md shadow-pink-500/20' : 'text-slate-600 hover:text-pink-600 hover:bg-pink-50/70' }}">
                        <i class="fa-solid fa-house-chimney w-6 text-base text-center"></i>
                        <span>Semua Listing</span>
                    </a>
                @endif

                <a href="{{ route('admin.apartment-stock') }}"
                   class="flex items-center justify-between px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.apartment-stock*') ? 'bg-pink-600 text-white shadow-md shadow-pink-600/20' : 'text-slate-600 hover:text-pink-600 hover:bg-pink-50/70' }}">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-bolt text-pink-500 w-6 text-base text-center"></i>
                        <span>Quick Stock Apartemen</span>
                    </div>
                    <span class="text-[9px] bg-pink-100 text-pink-700 border border-pink-200 px-1.5 py-0.5 rounded-full font-bold uppercase">WA Share</span>
                </a>

                <a href="{{ route('admin.client-requests.index') }}"
                   class="flex items-center justify-between px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.client-requests*') ? 'bg-pink-600 text-white shadow-md shadow-pink-600/20' : 'text-slate-600 hover:text-pink-600 hover:bg-pink-50/70' }}">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-wand-magic-sparkles text-pink-500 w-6 text-base text-center"></i>
                        <span>Carikan Properti &amp; Klien</span>
                    </div>
                    <span class="text-[9px] bg-pink-100 text-pink-700 border border-pink-200 px-1.5 py-0.5 rounded-full font-bold uppercase">Matcher &amp; WA</span>
                </a>

                <a href="{{ route('admin.rentals.index') }}"
                   class="flex items-center justify-between px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.rentals*') ? 'bg-pink-600 text-white shadow-md shadow-pink-600/20' : 'text-slate-600 hover:text-pink-600 hover:bg-pink-50/70' }}">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-key text-pink-500 w-6 text-base text-center"></i>
                        <span>Data Penyewa &amp; Deposit</span>
                    </div>
                    @php
                        $expiringCount = \App\Models\PropertyRental::when(auth()->user() && auth()->user()->isAgent(), fn($q) => $q->where('agent_id', auth()->user()->agent_id))->where('status', 'active')->whereDate('end_date', '<=', \Carbon\Carbon::today()->addDays(30))->count();
                    @endphp
                    @if($expiringCount > 0)
                        <span class="text-[9px] bg-pink-100 text-pink-700 border border-pink-200 px-1.5 py-0.5 rounded-full font-bold uppercase">{{ $expiringCount }} Mau Habis</span>
                    @endif
                </a>

                <a href="{{ route('admin.properties', ['type' => 'tanah']) }}"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request('type') === 'tanah' ? 'bg-pink-600 text-white shadow-md' : 'text-slate-600 hover:text-pink-600 hover:bg-pink-50/70' }}">
                    <i class="fa-solid fa-map w-6 text-base text-center text-pink-500"></i>
                    <span>Tanah Kavling</span>
                </a>

                <a href="{{ route('admin.properties', ['type' => 'gudang']) }}"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request('type') === 'gudang' ? 'bg-pink-600 text-white shadow-md' : 'text-slate-600 hover:text-pink-600 hover:bg-pink-50/70' }}">
                    <i class="fa-solid fa-warehouse w-6 text-base text-center text-pink-500"></i>
                    <span>Gudang &amp; Pabrik</span>
                </a>

                <a href="{{ route('admin.properties.create') }}"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.properties.create') ? 'bg-pink-600 text-white shadow-md shadow-pink-500/20' : 'text-slate-600 hover:text-pink-600 hover:bg-pink-50/70' }}">
                    <i class="fa-solid fa-plus-circle w-6 text-base text-center"></i>
                    <span>Tambah Properti</span>
                </a>

                @if(!auth()->user() || !auth()->user()->isAgent())
                    <div class="pt-3 pb-1 px-4 text-[10px] font-extrabold text-pink-500 uppercase tracking-wider">Master Data</div>

                    <a href="{{ route('admin.master.locations') }}"
                       class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.master.locations*') ? 'bg-pink-600 text-white shadow-md shadow-pink-500/20' : 'text-slate-600 hover:text-pink-600 hover:bg-pink-50/70' }}">
                        <i class="fa-solid fa-map-location-dot w-6 text-base text-center"></i>
                        <span>Provinsi &amp; Kota</span>
                    </a>

                    <a href="{{ route('admin.master.projects') }}"
                       class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.master.projects*') ? 'bg-pink-600 text-white shadow-md shadow-pink-500/20' : 'text-slate-600 hover:text-pink-600 hover:bg-pink-50/70' }}">
                        <i class="fa-solid fa-city w-6 text-base text-center"></i>
                        <span>Proyek &amp; Kawasan</span>
                    </a>
                @endif

                <div class="pt-3 pb-1 px-4 text-[10px] font-extrabold text-pink-500 uppercase tracking-wider">Agen &amp; Klien</div>

                @if(!auth()->user() || !auth()->user()->isAgent())
                    <a href="{{ route('admin.agents.index') }}"
                       class="flex items-center justify-between px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.agents.*') ? 'bg-pink-600 text-white shadow-md shadow-pink-500/20' : 'text-slate-600 hover:text-pink-600 hover:bg-pink-50/70' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-users-gear w-6 text-base text-center text-pink-500"></i>
                            <span>Kelola Agen &amp; Tim</span>
                        </div>
                    </a>

                    <a href="{{ route('admin.agent-applications') }}"
                       class="flex items-center justify-between px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.agent-applications*') ? 'bg-pink-600 text-white shadow-md shadow-pink-500/20' : 'text-slate-600 hover:text-pink-600 hover:bg-pink-50/70' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-id-card-clip w-6 text-base text-center"></i>
                            <span>Pendaftaran Agen</span>
                        </div>
                        @php $pendingAgentsCount = \App\Models\AgentApplication::where('status', 'pending')->count(); @endphp
                        @if($pendingAgentsCount > 0)
                            <span class="bg-pink-600 text-white text-[10px] font-extrabold px-1.5 py-0.2 rounded-full">{{ $pendingAgentsCount }}</span>
                        @endif
                    </a>
                @endif

                <a href="{{ route('admin.inquiries') }}"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.inquiries*') ? 'bg-pink-600 text-white shadow-md shadow-pink-500/20' : 'text-slate-600 hover:text-pink-600 hover:bg-pink-50/70' }}">
                    <i class="fa-solid fa-envelope-open-text w-6 text-base text-center"></i>
                    <span>{{ auth()->user() && auth()->user()->isAgent() ? 'Pesan Masuk Listing Saya' : 'Pesan & Survey' }}</span>
                </a>

                @if(!auth()->user() || !auth()->user()->isAgent())
                    <a href="{{ route('admin.submissions') }}"
                       class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.submissions*') ? 'bg-pink-600 text-white shadow-md shadow-pink-500/20' : 'text-slate-600 hover:text-pink-600 hover:bg-pink-50/70' }}">
                        <i class="fa-solid fa-tags w-6 text-base text-center"></i>
                        <span>Titip Properti</span>
                    </a>

                    <div class="pt-3 pb-1 px-4 text-[10px] font-extrabold text-pink-500 uppercase tracking-wider">Keuangan &amp; QRIS</div>

                    <a href="{{ route('admin.agent-transactions') }}"
                       class="flex items-center justify-between px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.agent-transactions*') ? 'bg-pink-600 text-white shadow-md shadow-pink-500/20' : 'text-slate-600 hover:text-pink-600 hover:bg-pink-50/70' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-receipt w-6 text-base text-center text-emerald-500"></i>
                            <span>Transaksi &amp; Topup Agen</span>
                        </div>
                        @php $pendingTransactionsCount = \App\Models\AgentTransaction::where('status', 'pending')->count(); @endphp
                        @if($pendingTransactionsCount > 0)
                            <span class="bg-pink-600 text-white text-[10px] font-extrabold px-1.5 py-0.2 rounded-full">{{ $pendingTransactionsCount }}</span>
                        @endif
                    </a>

                    <a href="{{ route('admin.settings') }}"
                       class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.settings*') ? 'bg-pink-600 text-white shadow-md shadow-pink-500/20' : 'text-slate-600 hover:text-pink-600 hover:bg-pink-50/70' }}">
                        <i class="fa-solid fa-qrcode w-6 text-base text-center text-purple-500"></i>
                        <span>Pengaturan Biaya &amp; QRIS</span>
                    </a>
                @endif
            </nav>
        </div>

        <!-- Sidebar Footer (Fixed Bottom) -->
        <div class="shrink-0 p-4 border-t border-pink-100 space-y-2 bg-white">
            <a href="{{ route('admin.profile') }}"
               class="flex items-center justify-between p-2 rounded-xl bg-pink-50/60 hover:bg-pink-50 border border-pink-100 text-slate-700 hover:text-pink-700 text-xs font-bold transition-all {{ request()->routeIs('admin.profile*') ? 'ring-2 ring-pink-500 text-pink-700 bg-pink-50' : '' }}">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-lg bg-pink-600 text-white flex items-center justify-center font-extrabold text-[11px] shrink-0 shadow-xs">
                        {{ strtoupper(substr(Auth::user()->name ?? 'AD', 0, 2)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="font-bold text-slate-900 truncate flex items-center gap-1">
                            <span>{{ Auth::user()->name ?? 'Administrator' }}</span>
                            @if(Auth::user() && Auth::user()->isLeader())
                                <i class="fa-solid fa-crown text-amber-500 text-[9px]" title="Team Leader"></i>
                            @endif
                        </div>
                        <div class="text-[10px] text-slate-500 truncate">Profil &amp; Password</div>
                    </div>
                </div>
                <i class="fa-solid fa-gear text-slate-400 text-xs"></i>
            </a>

            <div class="grid grid-cols-2 gap-2 pt-1">
                <a href="{{ route('home') }}" target="_blank" class="flex items-center justify-center gap-1.5 py-2 px-2 rounded-xl bg-pink-50 hover:bg-pink-100 text-pink-700 text-[11px] font-bold border border-pink-200/60 transition-all" title="Buka Website Publik">
                    <i class="fa-solid fa-globe"></i>
                    <span>Website</span>
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-1.5 py-2 px-2 rounded-xl bg-pink-50 hover:bg-pink-100 text-pink-600 text-[11px] font-bold border border-pink-200/60 transition-all" title="Logout dari Admin">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Mobile Backdrop for Admin Sidebar -->
    <div x-show="sidebarOpen"
         x-cloak
         @click="closeSidebar()"
         class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-xs lg:hidden transition-opacity"></div>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 lg:pl-64">
        <!-- Top Admin Bar -->
        <header class="h-16 bg-white border-b border-pink-100 px-4 sm:px-6 flex items-center justify-between sticky top-0 z-40">
            <div class="flex items-center gap-3">
                <button @click="toggleSidebar()" class="lg:hidden p-2 text-slate-600 hover:bg-slate-100 rounded-lg" aria-label="Toggle Sidebar">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <h1 class="text-base sm:text-lg font-black text-slate-900">@yield('page_title', 'Admin Dashboard')</h1>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.properties.create') }}" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 bg-pink-50 text-pink-600 hover:bg-pink-100 border border-pink-200/60 rounded-xl text-xs font-bold transition-all">
                    <i class="fa-solid fa-plus"></i> Listing Baru
                </a>

                <div class="flex items-center gap-3 pl-3 border-l border-pink-100">
                    <a href="{{ route('admin.profile') }}" class="flex items-center gap-2 hover:opacity-85 transition-opacity" title="Lihat Profil & Ganti Password">
                        <div class="w-8 h-8 rounded-full bg-pink-600 text-white flex items-center justify-center font-bold text-xs shadow-sm">
                            {{ strtoupper(substr(Auth::user()->name ?? 'AD', 0, 2)) }}
                        </div>
                        <div class="hidden sm:block text-xs">
                            <div class="font-bold text-slate-900 flex items-center gap-1">
                                <span>{{ Auth::user()->name ?? 'Administrator' }}</span>
                                @if(Auth::user() && Auth::user()->isLeader())
                                    <i class="fa-solid fa-crown text-amber-500 text-[10px]" title="Team Leader"></i>
                                @endif
                            </div>
                            <div class="text-[10px] text-slate-400">{{ Auth::user()->email ?? 'admin@yproperti.com' }}</div>
                        </div>
                    </a>

                    <a href="{{ route('admin.profile') }}" class="p-2 text-slate-400 hover:text-pink-600 hover:bg-pink-50 rounded-xl transition-all" title="Profil & Ganti Password">
                        <i class="fa-solid fa-gear text-base"></i>
                    </a>

                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="p-2 text-slate-400 hover:text-pink-600 hover:bg-pink-50 rounded-xl transition-all" title="Logout">
                            <i class="fa-solid fa-right-from-bracket text-base"></i>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <!-- Flash Messages -->
        @if(Auth::check() && Auth::user()->must_change_password && !request()->routeIs('admin.profile'))
            <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 pt-3">
                <div class="rounded-xl bg-amber-50 border border-amber-300 p-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2 shadow-2xs">
                    <div class="flex items-center gap-2.5">
                        <i class="fa-solid fa-triangle-exclamation text-amber-600 text-base"></i>
                        <p class="text-xs font-bold text-amber-900">
                            Anda masih menggunakan password sementara. Demi keamanan akun, harap ganti password Anda sesegera mungkin.
                        </p>
                    </div>
                    <a href="{{ route('admin.profile') }}" class="px-3 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-xs font-extrabold shrink-0 self-start sm:self-auto">
                        Ganti Password Sekarang
                    </a>
                </div>
            </div>
        @endif

        @if(session('success'))
            <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 pt-3">
                <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-3 flex items-center gap-3 shadow-2xs">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                    <p class="text-xs font-bold text-emerald-900">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 pt-3">
                <div class="rounded-xl bg-rose-50 border border-rose-200 p-3 shadow-2xs">
                    <ul class="text-xs font-semibold text-rose-700 list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- Main Body -->
        <main class="flex-1 p-3.5 sm:p-4 md:p-5 max-w-7xl w-full mx-auto">
            @yield('content')
        </main>
    </div>
</body>
</html>
