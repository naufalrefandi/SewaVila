@extends('layouts.admin')

@section('title', 'Pesan & Leads Masuk - Admin ' . \App\Models\Setting::getSiteName() . '')
@section('page_title', 'Pesan Masuk & Permintaan Survey Lokasi')

@section('content')
<div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-slate-400 uppercase tracking-wider font-bold">
                    <th class="p-4">Tanggal</th>
                    <th class="p-4">Calon Pembeli / Pengguna</th>
                    <th class="p-4">Listing Dituju</th>
                    <th class="p-4">Pesan &amp; Tgl Survey</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-center">Aksi / Follow Up</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($inquiries as $inquiry)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="p-4 text-slate-500 whitespace-nowrap">
                            {{ $inquiry->created_at->format('d M Y, H:i') }}
                        </td>
                        <td class="p-4">
                            <div class="font-bold text-slate-900">{{ $inquiry->name }}</div>
                            <div class="text-[11px] text-slate-500 mt-0.5">
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $inquiry->phone) }}" target="_blank" class="text-emerald-600 hover:underline">
                                    <i class="fa-brands fa-whatsapp"></i> {{ $inquiry->phone }}
                                </a>
                            </div>
                            <div class="text-[11px] text-slate-400">{{ $inquiry->email }}</div>
                        </td>
                        <td class="p-4 max-w-xs">
                            @if($inquiry->property)
                                <a href="{{ route('properties.show', $inquiry->property->slug) }}" target="_blank" class="font-bold text-slate-800 hover:text-rose-600 line-clamp-2">
                                    {{ $inquiry->property->title }}
                                </a>
                                <div class="text-[10px] text-rose-600 font-extrabold mt-0.5">{{ $inquiry->property->formatted_price }}</div>
                            @else
                                <span class="text-slate-400">Pertanyaan Umum</span>
                            @endif
                        </td>
                        <td class="p-4 max-w-sm">
                            <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100 text-slate-700 italic">
                                "{{ $inquiry->message }}"
                            </div>
                            @if($inquiry->schedule_survey_date)
                                <div class="mt-1.5 text-[11px] font-bold text-indigo-600 flex items-center gap-1">
                                    <i class="fa-solid fa-calendar-day"></i> Rencana Survey: {{ $inquiry->schedule_survey_date->format('d M Y') }}
                                </div>
                            @endif
                        </td>
                        <td class="p-4">
                            <form action="{{ route('admin.inquiries.status', $inquiry->id) }}" method="POST">
                                @csrf
                                <select name="status" onchange="this.form.submit()" class="px-2.5 py-1 rounded-lg text-xs font-bold border border-slate-200 focus:outline-none {{ $inquiry->status === 'new' ? 'bg-rose-50 text-rose-700 border-rose-200' : ($inquiry->status === 'contacted' ? 'bg-blue-50 text-blue-700' : 'bg-emerald-50 text-emerald-700') }}">
                                    <option value="new" {{ $inquiry->status === 'new' ? 'selected' : '' }}>New (Baru)</option>
                                    <option value="contacted" {{ $inquiry->status === 'contacted' ? 'selected' : '' }}>Contacted (Dihubungi)</option>
                                    <option value="scheduled" {{ $inquiry->status === 'scheduled' ? 'selected' : '' }}>Scheduled (Jadwal Survey)</option>
                                    <option value="closed" {{ $inquiry->status === 'closed' ? 'selected' : '' }}>Closed (Selesai)</option>
                                </select>
                            </form>
                        </td>
                        <td class="p-4 text-center">
                            @php
                                $cleanPhone = preg_replace('/[^0-9]/', '', $inquiry->phone);
                                if (str_starts_with($cleanPhone, '0')) {
                                    $cleanPhone = '62' . substr($cleanPhone, 1);
                                }
                                $replyMsg = urlencode("Halo " . $inquiry->name . ", terima kasih telah menghubungi " . \App\Models\Setting::getSiteName() . " terkait listing: " . ($inquiry->property ? $inquiry->property->title : 'properti impian Anda') . ". Apakah ada yang bisa kami bantu?");
                            @endphp
                            <a href="https://wa.me/{{ $cleanPhone }}?text={{ $replyMsg }}"
                               target="_blank"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition-all">
                                <i class="fa-brands fa-whatsapp text-sm"></i> Balas WA
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-400">
                            Belum ada pesan atau leads masuk.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t border-slate-200">
        {{ $inquiries->links() }}
    </div>
</div>
@endsection

