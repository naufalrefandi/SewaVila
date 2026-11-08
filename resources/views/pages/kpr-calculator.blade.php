@extends('layouts.app')

@section('title', 'Kalkulator Simulasi KPR - Hitung Cicilan Rumah Akurat | ' . \App\Models\Setting::getSiteName() . '')
@section('meta_description', 'Hitung estimasi cicilan KPR bulanan rumah impian Anda secara akurat dengan bunga bank terbaru dari BCA, Mandiri, BRI, dan bank rekanan ' . \App\Models\Setting::getSiteName() . '.')

@section('content')
<div class="bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 sm:py-6 text-center max-w-3xl">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-50 text-rose-600 text-xs font-bold uppercase tracking-wider mb-2.5">
            <i class="fa-solid fa-calculator"></i> Fitur Populer Simulasi KPR &amp; {{ \App\Models\Setting::getSiteName() }}
        </span>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
            Kalkulator Simulasi KPR Rumah
        </h1>
        <p class="mt-1.5 text-xs sm:text-sm text-slate-500 leading-relaxed">
            Rencanakan pembelian hunian idaman dengan menghitung estimasi angsuran pokok dan bunga secara transparan berdasarkan suku bunga bank terkini.
        </p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8"
     x-data="{
        price: {{ $defaultPrice ?? 1500000000 }},
        dpPercent: 20,
        fixedRate: 5.5,
        fixedYears: 3,
        tenorYears: 20,
        floatingRate: 11.0,

        get dpAmount() {
            return (this.price * this.dpPercent) / 100;
        },
        get loanAmount() {
            return Math.max(0, this.price - this.dpAmount);
        },
        get monthlyFixed() {
            const r = (this.fixedRate / 100) / 12;
            const n = this.tenorYears * 12;
            if (r === 0) return Math.round(this.loanAmount / n);
            return Math.round((this.loanAmount * r) / (1 - Math.pow(1 + r, -n)));
        },
        get monthlyFloating() {
            // Rough estimate during floating period
            const r = (this.floatingRate / 100) / 12;
            const remainingMonths = Math.max(12, (this.tenorYears - this.fixedYears) * 12);
            // Approx remaining principal after fixed period
            const remainingPrincipal = this.loanAmount * 0.9;
            return Math.round((remainingPrincipal * r) / (1 - Math.pow(1 + r, -remainingMonths)));
        },
        get totalPayment() {
            return this.monthlyFixed * (this.tenorYears * 12);
        },
        get totalInterest() {
            return Math.max(0, this.totalPayment - this.loanAmount);
        },
        get minIncomeRequired() {
            // Installment should be max 35% of take-home pay
            return Math.round(this.monthlyFixed / 0.35);
        },
        formatIdr(num) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(num);
        }
     }">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <!-- Input Form (Left 7 Cols) -->
        <div class="lg:col-span-7 bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6">
            <h2 class="text-lg font-extrabold text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                <i class="fa-solid fa-sliders text-rose-600"></i> Parameter Pinjaman KPR
            </h2>

            <!-- Harga Properti -->
            <div>
                <div class="flex justify-between items-center text-xs font-bold text-slate-700 mb-2">
                    <span>Harga Properti</span>
                    <span class="text-rose-600 text-sm font-extrabold" x-text="formatIdr(price)"></span>
                </div>
                <div class="relative mb-2">
                    <span class="absolute left-3.5 top-2.5 text-xs font-bold text-slate-400">Rp</span>
                    <input type="number"
                           x-model.number="price"
                           step="10000000"
                           class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:ring-2 focus:ring-rose-500 focus:bg-white focus:outline-none">
                </div>
                <input type="range" min="200000000" max="15000000000" step="50000000" x-model.number="price" class="w-full accent-rose-600">
                <div class="flex justify-between text-[10px] text-slate-400 mt-1">
                    <span>Rp 200 Juta</span>
                    <span>Rp 5 Miliar</span>
                    <span>Rp 15 Miliar</span>
                </div>
            </div>

            <!-- Uang Muka (DP) -->
            <div>
                <div class="flex justify-between items-center text-xs font-bold text-slate-700 mb-2">
                    <span>Uang Muka (DP)</span>
                    <span class="text-slate-900 font-extrabold">
                        <span x-text="dpPercent + '%'"></span> (<span class="text-rose-600" x-text="formatIdr(dpAmount)"></span>)
                    </span>
                </div>
                <div class="grid grid-cols-5 gap-2 mb-2">
                    @foreach([10, 15, 20, 30, 40] as $pct)
                        <button type="button"
                                @click="dpPercent = {{ $pct }}"
                                :class="dpPercent === {{ $pct }} ? 'bg-rose-600 text-white font-extrabold' : 'bg-slate-100 text-slate-700 font-semibold hover:bg-slate-200'"
                                class="py-2 text-xs rounded-xl transition-all">
                            {{ $pct }}%
                        </button>
                    @endforeach
                </div>
                <input type="range" min="5" max="50" step="1" x-model.number="dpPercent" class="w-full accent-rose-600">
            </div>

            <!-- Tenor (Jangka Waktu) -->
            <div>
                <div class="flex justify-between items-center text-xs font-bold text-slate-700 mb-2">
                    <span>Jangka Waktu Pinjaman (Tenor)</span>
                    <span class="text-rose-600 font-extrabold text-sm" x-text="tenorYears + ' Tahun (' + (tenorYears * 12) + ' Bulan)'"></span>
                </div>
                <div class="grid grid-cols-5 gap-2 mb-2">
                    @foreach([5, 10, 15, 20, 25] as $yr)
                        <button type="button"
                                @click="tenorYears = {{ $yr }}"
                                :class="tenorYears === {{ $yr }} ? 'bg-slate-900 text-white font-extrabold' : 'bg-slate-100 text-slate-700 font-semibold hover:bg-slate-200'"
                                class="py-2 text-xs rounded-xl transition-all">
                            {{ $yr }} Thn
                        </button>
                    @endforeach
                </div>
                <input type="range" min="5" max="30" step="1" x-model.number="tenorYears" class="w-full accent-slate-900">
            </div>

            <!-- Suku Bunga Bank -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Suku Bunga Fixed (% p.a.)</label>
                    <input type="number"
                           step="0.05"
                           x-model.number="fixedRate"
                           class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:ring-2 focus:ring-rose-500 focus:bg-white focus:outline-none">
                    <span class="text-[10px] text-slate-400 mt-0.5 block">Suku bunga promo di awal masa kredit</span>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Masa Suku Bunga Fixed</label>
                    <select x-model.number="fixedYears" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:ring-2 focus:ring-rose-500 focus:bg-white focus:outline-none">
                        <option value="1">1 Tahun</option>
                        <option value="2">2 Tahun</option>
                        <option value="3">3 Tahun (Terpopuler)</option>
                        <option value="5">5 Tahun</option>
                    </select>
                    <span class="text-[10px] text-slate-400 mt-0.5 block">Durasi bunga tetap sebelum floating</span>
                </div>
            </div>
        </div>

        <!-- Result Card (Right 5 Cols) -->
        <div class="lg:col-span-5 space-y-6">
            <div class="bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 text-white rounded-3xl p-6 sm:p-8 shadow-xl border border-slate-800">
                <span class="bg-rose-600 text-white text-[10px] font-extrabold px-2.5 py-1 rounded-md uppercase tracking-wider">
                    Hasil Perhitungan KPR
                </span>

                <div class="mt-5">
                    <span class="text-xs text-slate-400 font-medium">Estimasi Cicilan per Bulan (Masa Fixed):</span>
                    <div class="text-3xl sm:text-4xl font-black text-emerald-400 mt-1 tracking-tight" x-text="formatIdr(monthlyFixed) + ' /bln'"></div>
                </div>

                <!-- Breakdown Info -->
                <div class="mt-6 pt-6 border-t border-slate-800 space-y-3 text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Plafon Pinjaman Pokok:</span>
                        <span class="font-bold text-white" x-text="formatIdr(loanAmount)"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Uang Muka (DP) yang Dibayar:</span>
                        <span class="font-bold text-white" x-text="formatIdr(dpAmount)"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Estimasi Total Bunga:</span>
                        <span class="font-bold text-amber-400" x-text="formatIdr(totalInterest)"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Total Pembayaran (Pokok + Bunga):</span>
                        <span class="font-bold text-white" x-text="formatIdr(totalPayment)"></span>
                    </div>
                </div>

                <!-- Income Requirement -->
                <div class="mt-6 p-4 rounded-2xl bg-white/5 border border-white/10">
                    <div class="flex items-center gap-2 text-xs font-bold text-blue-300">
                        <i class="fa-solid fa-circle-info"></i> Rekomendasi Penghasilan:
                    </div>
                    <p class="text-xs text-slate-300 mt-1">
                        Disarankan penghasilan gabungan minimal <span class="font-bold text-white" x-text="formatIdr(minIncomeRequired) + ' /bulan'"></span> agar rasio DSR (Debt Service Ratio) bank lolos persetujuan.
                    </p>
                </div>

                <!-- CTA WhatsApp -->
                <div class="mt-6">
                    <a href="https://wa.me/6281298765432?text={{ urlencode('Halo ' . \App\Models\Setting::getSiteName() . ', saya ingin konsultasi pengajuan KPR dengan simulasi cicilan sekitar Rp ') }}"
                       target="_blank"
                       class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-rose-600 to-rose-700 hover:from-rose-700 hover:to-rose-800 text-white font-extrabold text-xs shadow-lg transition-all flex items-center justify-center gap-2">
                        <i class="fa-brands fa-whatsapp text-base"></i>
                        <span>Konsultasi &amp; Pengajuan KPR Gratis</span>
                    </a>
                </div>
            </div>

            <!-- Bank Partners Table -->
            <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
                <h3 class="font-extrabold text-slate-900 text-sm mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-building-columns text-rose-600"></i> Rekanan Suku Bunga KPR 2026
                </h3>
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between items-center p-2 rounded-xl bg-slate-50">
                        <span class="font-bold text-slate-800">Bank BCA</span>
                        <span class="font-extrabold text-emerald-600">2.75% Fixed 3 Thn</span>
                    </div>
                    <div class="flex justify-between items-center p-2 rounded-xl bg-slate-50">
                        <span class="font-bold text-slate-800">Bank Mandiri</span>
                        <span class="font-extrabold text-emerald-600">3.88% Fixed 3 Thn</span>
                    </div>
                    <div class="flex justify-between items-center p-2 rounded-xl bg-slate-50">
                        <span class="font-bold text-slate-800">Bank BRI</span>
                        <span class="font-extrabold text-emerald-600">3.75% Fixed 3 Thn</span>
                    </div>
                    <div class="flex justify-between items-center p-2 rounded-xl bg-slate-50">
                        <span class="font-bold text-slate-800">CIMB Niaga</span>
                        <span class="font-extrabold text-emerald-600">3.99% Fixed 5 Thn</span>
                    </div>
                    <div class="flex justify-between items-center p-2 rounded-xl bg-slate-50">
                        <span class="font-bold text-slate-800">Bank BNI</span>
                        <span class="font-extrabold text-emerald-600">4.25% Fixed 3 Thn</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Requirements Guide Section -->
    <div class="mt-16 bg-white rounded-3xl border border-slate-200 p-8 shadow-sm">
        <h2 class="text-xl font-extrabold text-slate-900 mb-6 flex items-center gap-2">
            <i class="fa-solid fa-file-circle-check text-rose-600"></i> Syarat &amp; Dokumen Pengajuan KPR
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-xs leading-relaxed text-slate-600">
            <!-- Karyawan -->
            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 space-y-3">
                <div class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-rose-600 text-white flex items-center justify-center text-xs">
                        <i class="fa-solid fa-briefcase"></i>
                    </span>
                    Karyawan / Pegawai Tetap
                </div>
                <ul class="space-y-2 list-disc list-inside">
                    <li>Fotokopi KTP Pemohon &amp; Pasangan (jika menikah)</li>
                    <li>Fotokopi Kartu Keluarga (KK) &amp; Akta Nikah / Cerai</li>
                    <li>Fotokopi NPWP Pribadi &amp; SPT PPh 21</li>
                    <li>Slip Gaji 3 Bulan Terakhir asli berstempel perusahaan</li>
                    <li>Surat Keterangan Kerja Asli (masa kerja min. 1-2 tahun)</li>
                    <li>Rekening Koran / Buku Tabungan 3 bulan terakhir</li>
                </ul>
            </div>

            <!-- Wiraswasta -->
            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 space-y-3">
                <div class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-xs">
                        <i class="fa-solid fa-store"></i>
                    </span>
                    Pengusaha / Wiraswasta / Profesional
                </div>
                <ul class="space-y-2 list-disc list-inside">
                    <li>Fotokopi KTP Pemohon &amp; Pasangan</li>
                    <li>Fotokopi Kartu Keluarga &amp; Akta Nikah</li>
                    <li>Fotokopi NPWP Pribadi &amp; NPWP Perusahaan</li>
                    <li>Legalitas Usaha: SIUP, NIB, TDP, Akta Pendirian &amp; Perubahan</li>
                    <li>Laporan Keuangan Usaha 2 tahun terakhir</li>
                    <li>Rekening Koran Operasional Usaha 6 bulan terakhir</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

