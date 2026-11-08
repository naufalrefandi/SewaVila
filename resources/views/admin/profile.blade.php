@extends('layouts.admin')

@section('title', 'Profil Akun & Ganti Password - ' . \App\Models\Setting::getSiteName() . '')
@section('page_title', 'Profil Akun')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    @if($user->must_change_password)
        <div class="rounded-2xl bg-amber-50 border-2 border-amber-300 p-4 flex items-start gap-3 shadow-xs">
            <i class="fa-solid fa-triangle-exclamation text-amber-600 text-lg mt-0.5"></i>
            <div class="text-xs text-amber-950 space-y-1">
                <div class="font-extrabold text-sm text-amber-900">Penting: Harap Segera Ganti Password Sementara Anda!</div>
                <p class="leading-relaxed">
                    Anda saat ini login menggunakan password sementara dari sistem. Demi keamanan akun dan listing Anda, silakan buat password baru pada formulir <strong>Ganti Password Login</strong> di bawah ini sekarang juga.
                </p>
            </div>
        </div>
    @endif

    <!-- Header Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-slate-800 to-rose-950 p-6 md:p-8 text-white shadow-xl">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                <div class="w-16 h-16 md:w-20 md:h-20 rounded-2xl bg-gradient-to-tr from-rose-600 to-amber-500 flex items-center justify-center text-white font-extrabold text-2xl md:text-3xl shadow-lg border-2 border-white/20">
                    @if($user->agent && $user->agent->photo)
                        <img src="{{ $user->agent->photo }}" alt="{{ $user->name }}" class="w-full h-full object-cover rounded-2xl">
                    @else
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    @endif
                </div>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h2 class="text-xl md:text-2xl font-black">{{ $user->name }}</h2>
                        @if($user->role === 'admin')
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-400 text-slate-950">
                                <i class="fa-solid fa-shield-halved mr-1"></i>Administrator
                            </span>
                        @elseif($user->isLeader())
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-500 text-white">
                                <i class="fa-solid fa-crown mr-1"></i>Team Leader
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-500 text-white">
                                <i class="fa-solid fa-user-tie mr-1"></i>Konsultan Agen
                            </span>
                        @endif
                    </div>
                    <p class="text-sm text-slate-300 mt-1 flex items-center gap-2">
                        <i class="fa-regular fa-envelope"></i>
                        <span>{{ $user->email }}</span>
                    </p>
                    @if($user->agent)
                        <p class="text-xs text-rose-300 mt-1 font-semibold flex items-center gap-2">
                            <i class="fa-solid fa-building"></i>
                            <span>{{ $user->agent->office_branch }}</span>
                        </p>
                        <p class="text-xs text-rose-200 mt-1 font-medium flex items-center gap-2">
                            <i class="fa-solid fa-barcode text-rose-400"></i>
                            <span>Inisial Listing: <strong class="text-white font-mono bg-white/20 px-2 py-0.5 rounded tracking-wider">{{ $user->agent->initials }}</strong> <span class="text-white/60">(Format: {{ $user->agent->initials }}-RMH-0001-J)</span></span>
                        </p>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-3">
                @if($user->isAgent())
                    <a href="{{ route('admin.properties') }}" class="px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white text-xs font-bold rounded-xl transition-all border border-white/20">
                        <i class="fa-solid fa-list-check mr-1.5"></i>Kelola Listing Saya
                    </a>
                @else
                    <a href="{{ route('admin.agents.index') }}" class="px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white text-xs font-bold rounded-xl transition-all border border-white/20">
                        <i class="fa-solid fa-users-gear mr-1.5"></i>Kelola Agen & Tim
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
        <!-- Account Info Card -->
        <div class="md:col-span-5 space-y-6">
            <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-5">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <h3 class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                        <i class="fa-solid fa-id-badge text-rose-600"></i>
                        Informasi Akun
                    </h3>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Read Only</span>
                </div>

                <div class="space-y-4 text-xs">
                    <div>
                        <span class="text-slate-400 font-semibold block mb-0.5">Nama Lengkap</span>
                        <div class="font-bold text-slate-800 text-sm">{{ $user->name }}</div>
                    </div>

                    <div>
                        <span class="text-slate-400 font-semibold block mb-0.5">Email Login</span>
                        <div class="font-bold text-slate-800 text-sm">{{ $user->email }}</div>
                    </div>

                    <div>
                        <span class="text-slate-400 font-semibold block mb-0.5">Hak Akses (Role)</span>
                        <div class="font-semibold text-slate-700 capitalize">{{ $user->role }}</div>
                    </div>

                    @if($user->agent)
                        <div class="pt-3 border-t border-slate-100 space-y-3">
                            <div>
                                <span class="text-slate-400 font-semibold block mb-0.5">Nomor WhatsApp Agen</span>
                                <div class="font-bold text-slate-800">{{ $user->agent->phone }}</div>
                            </div>

                            <div>
                                <span class="text-slate-400 font-semibold block mb-0.5">Kantor Cabang</span>
                                <div class="font-semibold text-slate-700">{{ $user->agent->office_branch }}</div>
                            </div>

                            <div>
                                <span class="text-slate-400 font-semibold block mb-0.5">Struktur Tim</span>
                                @if($user->agent->isLeader())
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 border border-amber-200 text-amber-900 font-bold rounded-xl text-xs">
                                        <i class="fa-solid fa-crown text-amber-600"></i>
                                        Team Leader ({{ $user->agent->teamMembers->count() }} Anggota Tim)
                                    </div>
                                    @if($user->agent->teamMembers->isNotEmpty())
                                        <div class="mt-2 space-y-1 pl-2 border-l-2 border-amber-300">
                                            @foreach($user->agent->teamMembers as $member)
                                                <div class="text-[11px] text-slate-600 flex items-center justify-between">
                                                    <span>{{ $member->name }}</span>
                                                    <span class="text-slate-400 text-[10px]">{{ $member->properties_count ?? $member->properties()->count() }} properti</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                @elseif($user->agent->leader)
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 border border-blue-200 text-blue-900 font-bold rounded-xl text-xs">
                                        <i class="fa-solid fa-user-group text-blue-600"></i>
                                        Anggota Tim: {{ $user->agent->leader->name }}
                                    </div>
                                @else
                                    <span class="text-slate-500 font-medium">Agen Mandiri</span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Security Note -->
            <div class="bg-rose-50/60 rounded-3xl p-5 border border-rose-100 text-xs text-rose-950 space-y-2">
                <div class="font-bold flex items-center gap-2 text-rose-800">
                    <i class="fa-solid fa-shield-halved"></i>
                    Tips Keamanan
                </div>
                <p class="text-rose-800/80 leading-relaxed text-[11px]">
                    Gunakan kombinasi minimal 6 karakter dengan huruf dan angka. Jangan bagikan password Anda kepada pihak lain untuk melindungi data listing dan klien Anda.
                </p>
            </div>
        </div>

        <!-- Password Change Card -->
        <div class="md:col-span-7">
            <div class="bg-white rounded-3xl p-6 md:p-8 border border-slate-200 shadow-sm space-y-6">
                <div>
                    <h3 class="font-extrabold text-slate-900 text-base flex items-center gap-2">
                        <i class="fa-solid fa-lock text-rose-600"></i>
                        Ganti Password Login
                    </h3>
                    <p class="text-xs text-slate-500 mt-1">
                        Perbarui kata sandi akun Anda untuk meningkatkan keamanan.
                    </p>
                </div>

                <form action="{{ route('admin.profile.password') }}" method="POST" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Password Saat Ini <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                                <i class="fa-solid fa-key text-xs"></i>
                            </span>
                            <input type="password" name="current_password" required
                                   placeholder="Masukkan password yang saat ini digunakan"
                                   class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:border-rose-500 focus:ring-2 focus:ring-rose-200 transition-all font-mono">
                        </div>
                        @error('current_password')
                            <p class="text-[11px] text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Password Baru <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                                <i class="fa-solid fa-lock text-xs"></i>
                            </span>
                            <input type="password" name="password" required minlength="6"
                                   placeholder="Minimal 6 karakter"
                                   class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:border-rose-500 focus:ring-2 focus:ring-rose-200 transition-all font-mono">
                        </div>
                        @error('password')
                            <p class="text-[11px] text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Konfirmasi Password Baru <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                                <i class="fa-solid fa-check-double text-xs"></i>
                            </span>
                            <input type="password" name="password_confirmation" required minlength="6"
                                   placeholder="Ketik ulang password baru"
                                   class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:border-rose-500 focus:ring-2 focus:ring-rose-200 transition-all font-mono">
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-6 py-2.5 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white font-bold text-xs rounded-xl shadow-md shadow-rose-200 hover:shadow-lg transition-all">
                            <i class="fa-solid fa-floppy-disk"></i>
                            <span>Simpan Perubahan Password</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

