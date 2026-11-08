<!DOCTYPE html>
<html lang="id" class="h-full bg-[#fdfafb]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Login Administrator - {{ \App\Models\Setting::getSiteName() }}</title>

    @php
        $loginLogo = \App\Models\Setting::getSiteLogo();
        $loginSiteName = \App\Models\Setting::getSiteName();
    @endphp
    @if($loginLogo)
        <link rel="icon" href="{{ $loginLogo }}">
        <link rel="shortcut icon" href="{{ $loginLogo }}">
        <link rel="apple-touch-icon" href="{{ $loginLogo }}">
    @endif

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 sm:p-6 bg-gradient-to-br from-pink-50 via-white to-pink-50/50 text-slate-800">
    <div class="w-full max-w-md">
        <!-- Brand Logo -->
        <div class="text-center mb-8">
            @php
                $loginLogo = \App\Models\Setting::getSiteLogo();
                $loginSiteName = \App\Models\Setting::getSiteName();
            @endphp
            <a href="{{ route('home') }}" class="inline-flex items-center gap-3 group">
                @if($loginLogo)
                    <img src="{{ $loginLogo }}" alt="{{ $loginSiteName }}" class="h-11 sm:h-12 w-auto max-w-[200px] object-contain rounded-xl shrink-0">
                    <span class="text-[10px] uppercase font-bold tracking-widest text-pink-600 border-l border-slate-200 pl-3">Admin Portal</span>
                @else
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-pink-600 via-rose-500 to-pink-400 flex items-center justify-center text-white shadow-lg shadow-pink-500/25 group-hover:scale-105 transition-transform shrink-0">
                        <i class="fa-solid fa-building-user text-2xl"></i>
                    </div>
                    <div class="text-left">
                        <span class="text-2xl font-black tracking-tight text-slate-900 leading-none block">
                            {{ $loginSiteName }}
                        </span>
                        <span class="text-[10px] uppercase font-bold tracking-widest text-pink-600">Admin Portal</span>
                    </div>
                @endif
            </a>
            <h1 class="text-xl font-extrabold text-slate-900 mt-6">Masuk ke Dashboard Admin</h1>
            <p class="text-xs text-slate-500 mt-1">Silakan masukkan email dan kata sandi Anda</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white border border-pink-100 rounded-3xl p-6 sm:p-8 shadow-xl shadow-pink-500/5">
            @if(session('success'))
                <div class="mb-5 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-600"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-5 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs space-y-1">
                    @foreach($errors->all() as $error)
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-circle-exclamation shrink-0 text-rose-600"></i>
                            <span>{{ $error }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('login.post') }}"
                  method="POST"
                  class="space-y-4"
                  x-data="{
                      email: @js(old('email', '')),
                      password: '',
                      remember: true,
                      show: false,
                      init() {
                          try {
                              const saved = localStorage.getItem('yproperti_saved_login');
                              if (saved) {
                                  const parsed = JSON.parse(saved);
                                  if (!this.email && parsed.email) {
                                      this.email = parsed.email;
                                  }
                                  if (parsed.password) {
                                      this.password = atob(parsed.password);
                                  }
                                  this.remember = true;
                              } else if (!this.email) {
                                  this.email = 'admin@yproperti.com';
                              }
                          } catch (e) {
                              if (!this.email) this.email = 'admin@yproperti.com';
                          }
                      },
                      handleSubmit() {
                          try {
                              if (this.remember) {
                                  localStorage.setItem('yproperti_saved_login', JSON.stringify({
                                      email: this.email,
                                      password: btoa(this.password)
                                  }));
                              } else {
                                  localStorage.removeItem('yproperti_saved_login');
                              }
                          } catch (e) {}
                      }
                  }"
                  @submit="handleSubmit()">
                @csrf

                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                        Email Admin
                    </label>
                    <div class="relative">
                        <i class="fa-solid fa-envelope absolute left-3.5 top-3.5 text-pink-400 text-sm"></i>
                        <input type="email"
                                id="email"
                                name="email"
                                x-model="email"
                                autocomplete="username"
                                required
                                autofocus
                                placeholder="nama@yproperti.com"
                                class="w-full pl-10 pr-4 py-2.5 bg-pink-50/40 border border-pink-100 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:bg-white transition-all">
                    </div>
                </div>

                <!-- Password Input -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Kata Sandi
                        </label>
                    </div>
                    <div class="relative">
                        <i class="fa-solid fa-lock absolute left-3.5 top-3.5 text-pink-400 text-sm"></i>
                        <input :type="show ? 'text' : 'password'"
                                id="password"
                                name="password"
                                x-model="password"
                                autocomplete="current-password"
                                required
                                placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;"
                                class="w-full pl-10 pr-10 py-2.5 bg-pink-50/40 border border-pink-100 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:bg-white transition-all">
                        <button type="button"
                                @click="show = !show"
                                class="absolute right-3.5 top-3.5 text-slate-400 hover:text-pink-600 text-sm focus:outline-none">
                            <i class="fa-solid" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me Checkbox -->
                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 text-slate-600 cursor-pointer select-none">
                        <input type="checkbox"
                               name="remember"
                               value="1"
                               x-model="remember"
                               @change="if (!remember) localStorage.removeItem('yproperti_saved_login')"
                               class="rounded bg-white border-pink-200 text-pink-600 focus:ring-pink-500">
                        <span>Ingat saya di perangkat ini (Simpan Email &amp; Sandi)</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit"
                            class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-pink-600 to-rose-500 hover:from-pink-700 hover:to-rose-600 text-white font-extrabold text-sm shadow-lg shadow-pink-500/25 hover:shadow-xl transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        <span>Masuk ke Admin Portal</span>
                    </button>
                </div>
            </form>

            <!-- Demo Account Info Helper -->
            <div class="mt-6 pt-5 border-t border-pink-100 text-xs">
                <div class="flex items-center gap-2 text-pink-600 font-bold mb-2">
                    <i class="fa-solid fa-key"></i>
                    <span>Akun Default Admin:</span>
                </div>
                <div class="bg-pink-50/60 rounded-xl p-3 border border-pink-100 space-y-1 font-mono text-[11px] text-slate-700">
                    <div><span class="text-slate-500">Email :</span> admin@yproperti.com</div>
                    <div><span class="text-slate-500">Pass  :</span> password123</div>
                </div>
            </div>
        </div>

        <!-- Back to Website -->
        <div class="text-center mt-6">
            <a href="{{ route('home') }}" class="text-xs text-slate-500 hover:text-pink-600 transition-colors inline-flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali ke Beranda Website</span>
            </a>
        </div>
    </div>
</body>
</html>
