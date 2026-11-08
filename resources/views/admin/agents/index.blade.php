@extends('layouts.admin')

@section('title', 'Kelola Agen & Struktur Tim Leader - ' . \App\Models\Setting::getSiteName() . '')
@section('page_title', 'Kelola Konsultan Agen & Struktur Tim Leader')

@section('content')
<div class="space-y-6" x-data="{
    showEditModal: false,
    showPasswordModal: false,
    selectedAgent: {},
    openEdit(agent) {
        this.selectedAgent = agent;
        this.showEditModal = true;
    },
    openPassword(agent) {
        this.selectedAgent = agent;
        this.showPasswordModal = true;
    }
}">
    <!-- Header Stats Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
            <div class="text-xs font-bold text-slate-400 uppercase">Total Agen Terdaftar</div>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ $agents->count() }}</div>
            <div class="text-[10px] text-emerald-600 font-bold mt-1">Konsultan Aktif</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
            <div class="text-xs font-bold text-slate-400 uppercase">Team Leader</div>
            <div class="text-2xl font-black text-amber-600 mt-1">{{ $agents->where('is_leader', true)->count() }}</div>
            <div class="text-[10px] text-amber-700 font-bold mt-1">Pemimpin Tim Agen</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
            <div class="text-xs font-bold text-slate-400 uppercase">Anggota Tim (Member)</div>
            <div class="text-2xl font-black text-blue-600 mt-1">{{ $agents->whereNotNull('leader_id')->count() }}</div>
            <div class="text-[10px] text-blue-700 font-bold mt-1">Di Bawah Leader</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
            <div class="text-xs font-bold text-slate-400 uppercase">Total Listing Agen</div>
            <div class="text-2xl font-black text-rose-600 mt-1">{{ $agents->sum('properties_count') }}</div>
            <div class="text-[10px] text-slate-400 mt-1">Properti Dipasarkan</div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-users-gear text-rose-600"></i> Direktori Konsultan Agen &amp; Tim
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">Kelola penugasan Leader, struktur tim, kredensial login, dan pantau listingan masing-masing agen.</p>
            </div>
            <a href="{{ route('agents.join') }}" target="_blank" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold flex items-center gap-1.5 transition-all">
                <i class="fa-solid fa-user-plus text-rose-600"></i> + Form Pendaftaran Agen Baru
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-400 uppercase tracking-wider font-bold">
                        <th class="p-4">Konsultan Agen</th>
                        <th class="p-4">Struktur / Status Leader</th>
                        <th class="p-4">Kantor Cabang</th>
                        <th class="p-4 text-center">Total Listing</th>
                        <th class="p-4 text-center">Kuota Listing</th>
                        <th class="p-4 text-center">Lihat Listingan</th>
                        <th class="p-4 text-center">Aksi / Password</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($agents as $agent)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <!-- Agen Info -->
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $agent->photo }}" alt="{{ $agent->name }}" class="w-12 h-12 rounded-2xl object-cover ring-2 ring-slate-100 shrink-0">
                                    <div class="min-w-0">
                                        <div class="font-extrabold text-slate-900 text-sm flex items-center gap-1.5 flex-wrap">
                                            <span>{{ $agent->name }}</span>
                                            <span class="px-1.5 py-0.5 rounded-md bg-pink-50 border border-pink-200 text-pink-700 font-mono font-bold text-[10px]" title="Inisial Kode Listing">
                                                {{ $agent->initials }}
                                            </span>
                                            @if($agent->is_leader)
                                                <span class="w-4 h-4 rounded-full bg-amber-500 text-white flex items-center justify-center text-[8px]" title="Team Leader">
                                                    <i class="fa-solid fa-crown"></i>
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-slate-500 flex items-center gap-2 mt-0.5">
                                            <a href="https://wa.me/{{ $agent->clean_phone }}" target="_blank" class="text-emerald-600 font-bold hover:underline flex items-center gap-1">
                                                <i class="fa-brands fa-whatsapp"></i> {{ $agent->phone }}
                                            </a>
                                            <span>&bull;</span>
                                            <span class="text-slate-400 truncate">{{ $agent->email }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Status Leader & Tim -->
                            <td class="p-4">
                                @if($agent->is_leader)
                                    <div>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-50 border border-amber-200 text-amber-900 font-black text-[11px]">
                                            <i class="fa-solid fa-crown text-amber-500"></i> Team Leader
                                        </span>
                                        <div class="text-[10px] text-slate-500 mt-1 font-semibold flex items-center gap-1">
                                            <i class="fa-solid fa-users text-slate-400 text-[9px]"></i>
                                            <span>{{ $agent->teamMembers->count() }} Anggota Tim</span>
                                        </div>
                                    </div>
                                @elseif($agent->leader)
                                    <div>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-50 border border-blue-200 text-blue-800 font-bold text-[11px]">
                                            <i class="fa-solid fa-user-check text-blue-500"></i> Member Tim
                                        </span>
                                        <div class="text-[10px] text-slate-500 mt-1">
                                            Leader: <strong class="text-slate-700">{{ $agent->leader->name }}</strong>
                                        </div>
                                    </div>
                                @else
                                    <span class="inline-block px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 font-semibold text-[11px]">
                                        Konsultan Mandiri
                                    </span>
                                @endif
                            </td>

                            <!-- Kantor Cabang -->
                            <td class="p-4">
                                <div class="font-bold text-slate-800">{{ $agent->office_branch }}</div>
                                <div class="text-[10px] text-slate-400 mt-0.5">{{ $agent->position }}</div>
                            </td>

                            <!-- Total Listing -->
                            <td class="p-4 text-center">
                                <div class="font-black text-sm text-slate-900">{{ $agent->properties_count }}</div>
                                <div class="text-[10px] text-slate-400 font-medium">Listing Pribadi</div>
                                @if($agent->is_leader && $agent->teamMembers->count() > 0)
                                    @php
                                        $teamListingsCount = \App\Models\Property::whereIn('agent_id', $agent->allTeamAgentIds())->count();
                                    @endphp
                                    <div class="mt-1 inline-block px-2 py-0.5 rounded bg-amber-100 text-amber-900 text-[10px] font-bold">
                                        Total Tim: {{ $teamListingsCount }}
                                    </div>
                                @endif
                            </td>

                            <!-- Kuota Listing -->
                            <td class="p-4 text-center">
                                <div class="font-black text-sm {{ $agent->remaining_quota > 0 ? 'text-slate-900' : 'text-rose-600' }}">
                                    {{ $agent->remaining_quota }}
                                    <span class="text-xs font-normal text-slate-400">/ {{ $agent->listing_quota }}</span>
                                </div>
                                <div class="text-[10px] text-slate-400 font-medium">
                                    Sisa Slot
                                </div>
                                @if($agent->remaining_quota == 0)
                                    <span class="mt-1 inline-block px-1.5 py-0.2 rounded bg-rose-100 text-rose-700 text-[9px] font-bold">
                                        Habis
                                    </span>
                                @endif
                            </td>

                            <!-- Lihat Listingan Cepat (Sesuai Permintaan User) -->
                            <td class="p-4 text-center">
                                <div class="flex flex-col gap-1.5 items-center justify-center">
                                    <a href="{{ route('admin.properties', ['agent_id' => $agent->id]) }}"
                                       class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-rose-600 text-white font-bold text-xs shadow-xs transition-all flex items-center gap-1.5 whitespace-nowrap">
                                        <i class="fa-solid fa-house-chimney text-xs"></i>
                                        <span>Listing Agen Ini ({{ $agent->properties_count }})</span>
                                    </a>

                                    @if($agent->is_leader && $agent->teamMembers->count() > 0)
                                        <a href="{{ route('admin.properties', ['leader_id' => $agent->id]) }}"
                                           class="px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-xs shadow-xs transition-all flex items-center gap-1.5 whitespace-nowrap">
                                            <i class="fa-solid fa-network-wired text-xs"></i>
                                            <span>Listing Semua Tim ({{ $teamListingsCount }})</span>
                                        </a>
                                    @endif
                                </div>
                            </td>

                            <!-- Aksi & Password -->
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button"
                                            @click="openEdit({{ $agent->toJson() }})"
                                            class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition-all"
                                            title="Edit Data & Struktur Leader">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>

                                    <button type="button"
                                            @click="openPassword({{ $agent->toJson() }})"
                                            class="px-2.5 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs transition-all flex items-center gap-1"
                                            title="Ubah / Reset Password Login Agen">
                                        <i class="fa-solid fa-key text-[10px]"></i>
                                        <span>Reset Password</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400">
                                Belum ada agen terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal 1: Edit Data & Penugasan Leader Agent -->
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showEditModal = false" class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 max-w-lg w-full shadow-2xl relative">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-xl bg-rose-600 text-white flex items-center justify-center text-base">
                        <i class="fa-solid fa-user-pen"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-sm">Edit Agen &amp; Struktur Leader</h3>
                        <p class="text-[11px] text-slate-400">Atur penugasan sebagai Team Leader atau anggota tim</p>
                    </div>
                </div>
                <button @click="showEditModal = false" class="p-1 text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form :action="'{{ url('admin/agents') }}/' + selectedAgent.id" method="POST" class="space-y-4 text-xs">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 mb-1">Nama Lengkap Agen *</label>
                        <input type="text" name="name" :value="selectedAgent.name" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Inisial Listing</label>
                        <input type="text" name="agent_code" :value="selectedAgent.agent_code || selectedAgent.initials" maxlength="10" placeholder="Cth: AG / AS" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl font-mono font-bold uppercase text-slate-900 focus:outline-none">
                        <span class="text-[9px] text-slate-400 mt-0.5 block">Awalan kode listing</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nomor WhatsApp *</label>
                        <input type="text" name="phone" :value="selectedAgent.phone" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Jabatan / Posisi</label>
                        <input type="text" name="position" :value="selectedAgent.position" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Kantor Cabang Agensi *</label>
                    <input type="text" name="office_branch" :value="selectedAgent.office_branch" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:outline-none">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Total Kuota Listing Properti (Slot) *</label>
                    <input type="number" name="listing_quota" :value="selectedAgent.listing_quota ?? 3" min="0" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:outline-none">
                    <span class="text-[10px] text-slate-400 mt-1 block">Admin dapat menambah atau menyesuaikan kuota slot listing agen secara manual di sini.</span>
                </div>

                <!-- Leader Setup Box -->
                <div class="p-4 bg-amber-50/60 rounded-2xl border border-amber-200 space-y-3">
                    <div class="font-black text-amber-900 text-xs flex items-center gap-1.5">
                        <i class="fa-solid fa-crown text-amber-600"></i> Pengaturan Struktur Leader &amp; Tim
                    </div>

                    <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-800">
                        <input type="checkbox" name="is_leader" value="1" :checked="selectedAgent.is_leader" class="rounded text-amber-600 focus:ring-amber-500">
                        <span>Jadikan Agen Ini Sebagai <strong>Team Leader</strong></span>
                    </label>

                    <div class="pt-2 border-t border-amber-200/80">
                        <label class="block font-bold text-slate-700 mb-1">Atau Pilih Team Leader (Jika Sebagai Anggota Tim):</label>
                        <select name="leader_id" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl font-medium focus:outline-none">
                            <option value="">-- Tidak Terikat Leader (Mandiri) --</option>
                            @foreach($leaders as $ldr)
                                <option value="{{ $ldr->id }}" :selected="selectedAgent.leader_id == {{ $ldr->id }}">
                                    {{ $ldr->name }} ({{ $ldr->office_branch }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 font-bold">Batal</button>
                    <button type="submit" class="px-6 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-extrabold shadow-md">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 2: Reset Password Agen (Sesuai Permintaan User: "password agent gimana?") -->
    <div x-show="showPasswordModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showPasswordModal = false" class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 max-w-md w-full shadow-2xl relative">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-rose-600 to-amber-500 text-white flex items-center justify-center text-base">
                        <i class="fa-solid fa-key"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-sm">Set / Reset Password Agen</h3>
                        <p class="text-[11px] text-slate-400">Atur password baru untuk akun login agen</p>
                    </div>
                </div>
                <button @click="showPasswordModal = false" class="p-1 text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="mb-4 p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs">
                <div class="font-bold text-slate-800" x-text="selectedAgent.name"></div>
                <div class="text-[11px] text-slate-500 mt-0.5">Email Login: <strong class="text-rose-600" x-text="selectedAgent.email"></strong></div>
                <div class="text-[10px] text-slate-400 mt-1">Default password awal: <code class="bg-slate-200 px-1.5 py-0.5 rounded font-mono font-bold text-slate-800">password123</code></div>
            </div>

            <form :action="'{{ url('admin/agents') }}/' + selectedAgent.id + '/reset-password'" method="POST" class="space-y-4 text-xs">
                @csrf

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Password Baru (Minimal 6 Karakter) *</label>
                    <input type="password" name="password" required minlength="6" placeholder="Masukkan password baru" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Konfirmasi Password Baru *</label>
                    <input type="password" name="password_confirmation" required minlength="6" placeholder="Ulangi password baru" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none">
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                    <button type="button" @click="showPasswordModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 font-bold">Batal</button>
                    <button type="submit" class="px-6 py-2 rounded-xl bg-slate-900 hover:bg-rose-600 text-white font-extrabold shadow-md">Simpan Password Baru</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

