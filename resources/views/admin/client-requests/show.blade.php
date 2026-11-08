@extends('layouts.admin')

@section('title', 'Detail Kebutuhan Klien: ' . $clientRequest->client_name . ' - ' . \App\Models\Setting::getSiteName() . '')
@section('page_title', 'Detail Kebutuhan Klien & Rekomendasi Unit')

@section('content')
@php
    $currentAgent = $clientRequest->agent ?? auth()->user()->agent ?? \App\Models\Agent::first();
    $currentAgentName = $currentAgent ? $currentAgent->name : auth()->user()->name;
    $currentAgentOffice = $currentAgent ? $currentAgent->office_branch : \App\Models\Setting::getSiteName();
    $currentAgentPhone = $currentAgent ? $currentAgent->phone : '';
@endphp

<div x-data="clientRequestDetailApp()" class="space-y-6">

    <!-- Top Navigation & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <a href="{{ route('admin.client-requests.index', ['tab' => 'requests']) }}"
           class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-pink-600 transition-all">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Kembali ke Database Kebutuhan Klien</span>
        </a>

        <div class="flex items-center gap-2 flex-wrap">
            <!-- Mark Follow-up form -->
            <form action="{{ route('admin.client-requests.follow-up', $clientRequest->id) }}" method="POST" class="inline">
                @csrf
                <button type="submit"
                        class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 font-bold text-xs flex items-center gap-1.5 transition-all">
                    <i class="fa-solid fa-check-double text-emerald-600"></i>
                    <span>Tandai Sudah Follow-up Hari Ini</span>
                </button>
            </form>

            <!-- Status Dropdown Form -->
            <form action="{{ route('admin.client-requests.update', $clientRequest->id) }}" method="POST" class="inline">
                @csrf
                @method('PUT')
                <input type="hidden" name="client_name" value="{{ $clientRequest->client_name }}">
                <input type="hidden" name="client_phone" value="{{ $clientRequest->client_phone }}">
                <input type="hidden" name="listing_type" value="{{ $clientRequest->listing_type }}">
                <input type="hidden" name="property_type" value="{{ $clientRequest->property_type }}">
                <input type="hidden" name="city_id" value="{{ $clientRequest->city_id }}">
                <input type="hidden" name="min_price" value="{{ $clientRequest->min_price }}">
                <input type="hidden" name="max_price" value="{{ $clientRequest->max_price }}">
                <input type="hidden" name="notes" value="{{ $clientRequest->notes }}">

                @if($clientRequest->status !== 'dealt')
                    <input type="hidden" name="status" value="dealt">
                    <button type="submit"
                            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/30 flex items-center gap-1.5 transition-all">
                        <i class="fa-solid fa-handshake"></i>
                        <span>Tandai Deal / Closing</span>
                    </button>
                @else
                    <input type="hidden" name="status" value="active">
                    <button type="submit"
                            class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-md shadow-amber-500/30 flex items-center gap-1.5 transition-all">
                        <i class="fa-solid fa-rotate-left"></i>
                        <span>Aktifkan Kembali</span>
                    </button>
                @endif
            </form>
        </div>
    </div>

    <!-- Client Profile Header Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-pink-100 shadow-sm space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 border-b border-slate-100 pb-6">
            <div class="flex items-start gap-4">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-pink-500 to-rose-600 text-white flex items-center justify-center text-2xl font-black shadow-lg shadow-pink-500/30 shrink-0">
                    {{ strtoupper(substr($clientRequest->client_name, 0, 2)) }}
                </div>
                <div class="space-y-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="text-xl sm:text-2xl font-black text-slate-900">{{ $clientRequest->client_name }}</h1>
                        @if($clientRequest->status === 'active')
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-amber-100 text-amber-800 border border-amber-200 uppercase">Sedang Mencari</span>
                        @elseif($clientRequest->status === 'dealt')
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-200 uppercase">Closing / Deal</span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-slate-100 text-slate-600 border border-slate-200 uppercase">Batal</span>
                        @endif
                    </div>

                    <div class="flex items-center gap-4 text-xs text-slate-500 flex-wrap">
                        <a href="https://api.whatsapp.com/send?phone={{ $clientRequest->clean_phone }}" target="_blank" class="text-emerald-600 font-bold hover:underline flex items-center gap-1">
                            <i class="fa-brands fa-whatsapp text-sm"></i>
                            <span>{{ $clientRequest->client_phone }}</span>
                        </a>

                        <span>•</span>
                        <span>Dicatat: {{ $clientRequest->created_at->format('d M Y, H:i') }}</span>

                        @if($clientRequest->last_followed_up_at)
                            <span>•</span>
                            <span>Terakhir Follow-up: <strong class="text-emerald-700">{{ $clientRequest->last_followed_up_at->diffForHumans() }}</strong></span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Match status pill -->
            <div class="flex items-center gap-3">
                @if($matches->isNotEmpty())
                    <div class="px-4 py-2.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-2.5 shadow-xs">
                        <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
                        <div class="text-left">
                            <div class="text-[10px] font-black uppercase tracking-wider text-emerald-600">Status Pencocokan</div>
                            <div class="text-sm font-black text-emerald-900">{{ $matches->count() }} Unit Cocok Siap Ditawarkan</div>
                        </div>
                    </div>
                @else
                    <div class="px-4 py-2.5 rounded-2xl bg-slate-50 border border-slate-200 text-slate-600 flex items-center gap-2.5">
                        <i class="fa-solid fa-hourglass-half text-slate-400"></i>
                        <div class="text-left">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Status Pencocokan</div>
                            <div class="text-xs font-bold text-slate-700">Menunggu Listing Baru Masuk</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Criteria Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 bg-slate-50/70 p-4 rounded-2xl border border-slate-100 text-xs">
            <div>
                <div class="text-[10px] font-bold uppercase text-slate-400">Tipe Transaksi</div>
                <div class="font-black text-slate-800 mt-0.5">{{ $clientRequest->listing_type_label }}</div>
            </div>

            <div>
                <div class="text-[10px] font-bold uppercase text-slate-400">Tipe Properti</div>
                <div class="font-black text-slate-800 mt-0.5">{{ $clientRequest->property_type_label }}</div>
            </div>

            <div>
                <div class="text-[10px] font-bold uppercase text-slate-400">Lokasi / Area</div>
                <div class="font-black text-slate-800 mt-0.5">{{ $clientRequest->location_label }}</div>
            </div>

            <div>
                <div class="text-[10px] font-bold uppercase text-slate-400">Rentang Budget</div>
                <div class="font-black text-pink-600 mt-0.5">{{ $clientRequest->budget_label }}</div>
            </div>

            @if($clientRequest->min_bedrooms)
                <div>
                    <div class="text-[10px] font-bold uppercase text-slate-400">Min. Kamar Tidur</div>
                    <div class="font-black text-slate-800 mt-0.5">{{ $clientRequest->min_bedrooms }}+ KT</div>
                </div>
            @endif

            @if($clientRequest->min_bathrooms)
                <div>
                    <div class="text-[10px] font-bold uppercase text-slate-400">Min. Kamar Mandi</div>
                    <div class="font-black text-slate-800 mt-0.5">{{ $clientRequest->min_bathrooms }}+ KM</div>
                </div>
            @endif

            @if($clientRequest->tower)
                <div>
                    <div class="text-[10px] font-bold uppercase text-slate-400">Tower (Apartemen)</div>
                    <div class="font-black text-slate-800 mt-0.5">{{ str_starts_with(strtolower($clientRequest->tower), 'tower') ? $clientRequest->tower : 'Tower ' . $clientRequest->tower }}</div>
                </div>
            @endif

            @if($clientRequest->notes)
                <div class="col-span-2 sm:col-span-4 pt-2 border-t border-slate-200/60">
                    <div class="text-[10px] font-bold uppercase text-slate-400">Catatan Khusus Klien</div>
                    <div class="text-xs text-slate-700 italic mt-0.5">"{{ $clientRequest->notes }}"</div>
                </div>
            @endif
        </div>
    </div>

    <!-- ==================================================================== -->
    <!-- MATCHING PROPERTIES LIST                                             -->
    <!-- ==================================================================== -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                <span>Daftar Unit Cocok Sesuai Kriteria</span>
                <span class="bg-emerald-100 text-emerald-800 text-xs px-2 py-0.5 rounded-full font-black">
                    {{ $matches->count() }} Unit
                </span>
            </h2>
            <div class="text-xs text-slate-400">
                Centang unit untuk memasukkannya ke template WhatsApp di bawah
            </div>
        </div>

        @if($matches->isEmpty())
            <div class="bg-white rounded-3xl p-8 border border-slate-200 text-center space-y-3">
                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-lg mx-auto">
                    <i class="fa-solid fa-building-circle-xmark"></i>
                </div>
                <h3 class="text-sm font-black text-slate-800">Belum Ada Listing Baru yang Cocok Persis</h3>
                <p class="text-xs text-slate-400 max-w-md mx-auto">
                    Ketika listing baru masuk ke sistem dengan kriteria ini, halaman ini akan otomatis menampilkannya dan siap dikirim via WhatsApp.
                </p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($matches as $prop)
                    @php
                        $propPayload = [
                            'id' => $prop->id,
                            'title' => $prop->title,
                            'price' => $prop->formatted_price,
                            'location' => trim(($prop->district ? $prop->district . ', ' : '') . $prop->city),
                            'specs' => $prop->isApartemen() ? ($prop->full_unit_info ?: 'Apartemen') : ($prop->bedrooms . ' KT • ' . $prop->bathrooms . ' KM • LT: ' . ($prop->land_area ?: '-') . 'm² • LB: ' . ($prop->building_area ?: '-') . 'm²'),
                            'url' => route('properties.show', $prop->slug),
                            'code' => $prop->property_code,
                        ];
                    @endphp
                    <div class="relative group bg-white rounded-2xl border-2 transition-all p-4 flex flex-col justify-between"
                         :class="isPropertySelected({{ $prop->id }}) ? 'border-pink-600 bg-pink-50/20 shadow-md ring-2 ring-pink-500/20' : 'border-slate-200 hover:border-pink-300'">

                        <div class="flex items-start justify-between gap-3 mb-3">
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox"
                                       @change="toggleProperty({{ json_encode($propPayload) }})"
                                       :checked="isPropertySelected({{ $prop->id }})"
                                       class="w-5 h-5 rounded-lg border-slate-300 text-pink-600 focus:ring-pink-500 cursor-pointer">
                                <span class="text-xs font-black" :class="isPropertySelected({{ $prop->id }}) ? 'text-pink-600' : 'text-slate-700'">
                                    Pilih untuk WA
                                </span>
                            </label>

                            <span class="text-[10px] font-mono font-bold bg-slate-100 text-slate-600 px-2 py-0.5 rounded-md">
                                {{ $prop->property_code }}
                            </span>
                        </div>

                        <div class="flex gap-3 mb-3">
                            <div class="w-24 h-24 rounded-xl overflow-hidden bg-slate-100 shrink-0 relative">
                                <img src="{{ $prop->primary_image }}" alt="{{ $prop->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-all duration-300">
                                <span class="absolute top-1 left-1 text-[9px] font-black uppercase px-1.5 py-0.5 rounded-md bg-slate-900/80 text-white">
                                    {{ $prop->listing_type === 'disewa' ? 'Sewa' : 'Jual' }}
                                </span>
                            </div>

                            <div class="min-w-0 flex-1 space-y-1">
                                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                                    {{ ucfirst($prop->property_type) }} • {{ $prop->city }}
                                </div>
                                <h4 class="text-xs sm:text-sm font-black text-slate-900 line-clamp-2 leading-tight">
                                    {{ $prop->title }}
                                </h4>
                                <div class="text-sm sm:text-base font-black text-pink-600">
                                    {{ $prop->formatted_price }}
                                </div>
                            </div>
                        </div>

                        <div class="pt-2 border-t border-slate-100 text-[11px] font-medium text-slate-600 flex items-center justify-between">
                            <div class="truncate mr-2">
                                @if($prop->isApartemen())
                                    <i class="fa-solid fa-building text-pink-500 mr-1"></i>
                                    {{ $prop->formatted_tower ?: 'Tower' }} • {{ $prop->formatted_floor_short ?: 'Lt' }} • Unit {{ $prop->unit_number ?: '-' }}
                                @else
                                    <i class="fa-solid fa-bed text-pink-500 mr-1"></i> {{ $prop->bedrooms ?: '-' }} KT
                                    <span class="mx-1">•</span>
                                    <i class="fa-solid fa-bath text-pink-500 mr-1"></i> {{ $prop->bathrooms ?: '-' }} KM
                                @endif
                            </div>

                            <a href="{{ route('properties.show', $prop->slug) }}" target="_blank" class="text-pink-600 hover:underline font-bold shrink-0 text-[11px] flex items-center gap-1">
                                <span>Detail</span>
                                <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- ==================================================================== -->
    <!-- WHATSAPP BROADCAST DRAFT PANEL                                       -->
    <!-- ==================================================================== -->
    <div class="bg-white rounded-3xl p-6 border border-pink-200 shadow-lg space-y-4 sticky bottom-4 z-20 ring-4 ring-pink-500/10">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-pink-100 pb-3">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-500 text-white flex items-center justify-center font-black shadow-md shadow-emerald-500/30">
                    <i class="fa-brands fa-whatsapp text-lg"></i>
                </div>
                <div>
                    <h4 class="text-sm font-black text-slate-900 flex items-center gap-2">
                        <span>Pesan WhatsApp Follow-up ke {{ $clientRequest->client_name }}</span>
                        <span class="bg-emerald-100 text-emerald-800 text-[10px] px-2 py-0.5 rounded-full font-black">
                            <span x-text="selectedProperties.length"></span> Unit Terpilih
                        </span>
                    </h4>
                    <p class="text-[11px] text-slate-500">Pesan otomatis ter-update dan siap dikirimkan ke {{ $clientRequest->client_phone }}.</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="button"
                        @click="copyWhatsAppText()"
                        class="px-3 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs flex items-center gap-1.5 transition-all">
                    <i class="fa-solid" :class="copied ? 'fa-check text-emerald-600' : 'fa-copy text-slate-500'"></i>
                    <span x-text="copied ? 'Tersalin!' : 'Salin Teks'"></span>
                </button>

                <a :href="whatsappUrl"
                   target="_blank"
                   class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs shadow-md shadow-emerald-600/30 flex items-center gap-2 transition-all">
                    <i class="fa-brands fa-whatsapp text-base"></i>
                    <span>Kirim ke WhatsApp</span>
                </a>
            </div>
        </div>

        <div class="relative">
            <textarea x-model="generatedMessage"
                      rows="6"
                      class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-mono text-slate-800 leading-relaxed focus:bg-white focus:outline-none focus:ring-2 focus:ring-pink-500/20"></textarea>
        </div>

        <div class="flex items-center justify-between text-xs text-slate-400">
            <div>
                Target: <strong class="text-slate-700">{{ $clientRequest->client_name }}</strong> ({{ $clientRequest->client_phone }})
            </div>
            <div class="text-[11px]">
                Agen: <strong class="text-slate-700">{{ $currentAgentName }}</strong> ({{ $currentAgentOffice }})
            </div>
        </div>
    </div>

</div>

<script>
function clientRequestDetailApp() {
    return {
        clientName: @json($clientRequest->client_name),
        clientPhone: @json($clientRequest->clean_phone),
        selectedProperties: [],
        copied: false,
        agentName: @json($currentAgentName),
        agentOffice: @json($currentAgentOffice),
        agentPhone: @json($currentAgentPhone),

        init() {
            @if(!empty($matchesPayload))
                this.selectedProperties = @json($matchesPayload);
            @endif
        },

        isPropertySelected(id) {
            return this.selectedProperties.some(p => p.id === id);
        },

        toggleProperty(prop) {
            const index = this.selectedProperties.findIndex(p => p.id === prop.id);
            if (index > -1) {
                this.selectedProperties.splice(index, 1);
            } else {
                this.selectedProperties.push(prop);
            }
        },

        get generatedMessage() {
            const name = this.clientName || 'Bpk/Ibu';
            const office = this.agentOffice || '{{ \App\Models\Setting::getSiteName() }}';
            const agent = this.agentName || 'Agent';

            let lines = [];
            lines.push(`Halo *${name}*,`);
            lines.push('');
            lines.push(`Saya *${agent}* dari *${office}*.`);
            lines.push(`Kabar gembira! Baru saja kami menemukan rekomendasi unit properti yang sangat cocok dengan kriteria yang Bapak/Ibu cari sebelumnya:`);
            lines.push('');

            if (this.selectedProperties.length > 0) {
                this.selectedProperties.forEach((p, idx) => {
                    lines.push(`${idx + 1}️⃣ *${p.title}*`);
                    lines.push(`   📍 Lokasi: ${p.location}`);
                    lines.push(`   💵 Harga: *${p.price}*`);
                    if (p.specs) lines.push(`   📐 Spek: ${p.specs}`);
                    if (p.url) lines.push(`   🔗 Detail & Foto: ${p.url}`);
                    lines.push('');
                });
                lines.push(`Apakah masih mencari properti dengan kriteria tersebut? Jika berminat survey lokasi, silakan hubungi saya ya Pak/Bu. Terima kasih! 🙏`);
            } else {
                lines.push(`Saat ini kami sedang mencarikan rekomendasi listing terbaru untuk Bapak/Ibu.`);
            }

            lines.push('');
            lines.push(`Salam,`);
            lines.push(`*${agent}* (${office})`);
            if (this.agentPhone) {
                lines.push(`WhatsApp: ${this.agentPhone}`);
            }

            return lines.join('\n');
        },

        get whatsappUrl() {
            let phone = this.clientPhone;
            const text = encodeURIComponent(this.generatedMessage);
            return phone ? `https://api.whatsapp.com/send?phone=${phone}&text=${text}` : `https://api.whatsapp.com/send?text=${text}`;
        },

        copyWhatsAppText() {
            navigator.clipboard.writeText(this.generatedMessage).then(() => {
                this.copied = true;
                setTimeout(() => { this.copied = false; }, 2500);
            });
        }
    };
}
</script>
@endsection
