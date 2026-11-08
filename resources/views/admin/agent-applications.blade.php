@extends('layouts.admin')

@section('title', 'Pendaftaran Agen Properti - Admin ' . \App\Models\Setting::getSiteName() . '')
@section('page_title', 'Review Pendaftaran Agen Properti Baru')

@section('content')
<div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-slate-400 uppercase tracking-wider font-bold">
                    <th class="p-4">Tanggal</th>
                    <th class="p-4">Calon Agen</th>
                    <th class="p-4">Kota &amp; Kantor Cabang</th>
                    <th class="p-4">Pengalaman &amp; Lisensi</th>
                    <th class="p-4">Bukti Pembayaran</th>
                    <th class="p-4">Motivasi &amp; Bio</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-center">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($applications as $app)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="p-4 text-slate-500 whitespace-nowrap">
                            {{ $app->created_at->format('d M Y, H:i') }}
                        </td>
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                <img src="{{ $app->photo ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&q=80' }}"
                                     alt="{{ $app->name }}"
                                     class="w-10 h-10 rounded-full object-cover ring-1 ring-slate-200 shrink-0">
                                <div>
                                    <div class="font-bold text-slate-900">{{ $app->name }}</div>
                                    <div class="text-[11px] text-emerald-600 mt-0.5">
                                        <a href="https://wa.me/{{ $app->clean_phone }}" target="_blank" class="hover:underline">
                                            <i class="fa-brands fa-whatsapp"></i> {{ $app->phone }}
                                        </a>
                                    </div>
                                    <div class="text-[10px] text-slate-400">{{ $app->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="p-4">
                            <div class="font-bold text-slate-800">{{ $app->city }}</div>
                            <div class="text-[11px] text-slate-500 mt-0.5">{{ $app->office_branch_preference ?: 'Kantor Terdekat' }}</div>
                        </td>
                        <td class="p-4 text-slate-700">
                            <span class="inline-block px-2 py-0.5 rounded font-bold text-[10px] {{ $app->experience_level === 'diatas_3_tahun' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                {{ $app->experience_label }}
                            </span>
                            @if($app->license_number)
                                <div class="text-[10px] font-semibold text-blue-600 mt-1">
                                    <i class="fa-solid fa-certificate"></i> AREBI: {{ $app->license_number }}
                                </div>
                            @endif
                        </td>
                        <td class="p-4">
                            <div class="space-y-1.5">
                                @if($app->payment_code)
                                    <div class="font-mono text-[11px] font-extrabold text-slate-900 bg-slate-100 px-2 py-0.5 rounded inline-block">
                                        {{ $app->payment_code }}
                                    </div>
                                @endif
                                <div>
                                    @if($app->payment_proof)
                                        <a href="{{ $app->payment_proof_url }}" target="_blank" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 font-bold text-[11px] border border-rose-200 transition-colors shadow-2xs">
                                            <i class="fa-solid fa-receipt text-rose-600"></i>
                                            <span>Lihat Struk</span>
                                        </a>
                                    @else
                                        <span class="text-slate-400 text-[11px] italic">Belum Upload Struk</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="p-4 max-w-xs">
                            <p class="text-xs text-slate-600 line-clamp-2 italic">
                                "{{ $app->motivation_bio ?: 'Ingin bergabung menjadi konsultan properti.' }}"
                            </p>
                        </td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase {{ $app->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : ($app->status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                {{ $app->status }}
                            </span>
                        </td>
                        <td class="p-4 text-center">
                            @if($app->status === 'pending')
                                <div class="flex items-center justify-center gap-1.5">
                                    <form action="{{ route('admin.agent-applications.approve', $app->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition-all flex items-center gap-1">
                                            <i class="fa-solid fa-check"></i> Terima
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.agent-applications.reject', $app->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-600 font-bold text-xs transition-all">
                                            Tolak
                                        </button>
                                    </form>
                                </div>
                            @else
                                <span class="text-[10px] text-slate-400 font-medium">Selesai</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="p-8 text-center text-slate-400">
                            Belum ada calon agen yang mendaftar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t border-slate-200">
        {{ $applications->links() }}
    </div>
</div>
@endsection

