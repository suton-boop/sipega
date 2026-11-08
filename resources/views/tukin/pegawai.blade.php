<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-emerald-50 text-emerald-800 text-[10px] font-black uppercase tracking-widest rounded-full mb-1">
                    <span>💰 Perhitungan Tukin Pegawai</span>
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span>Permendikdasmen 14/2025</span>
                </div>
                <h2 class="font-black text-2xl text-sipega-navy leading-tight tracking-tight">
                    {{ __('Tunjangan Kinerja (Tukin)') }}
                </h2>
                <p class="text-xs text-gray-500 font-medium">
                    Rincian perhitungan hak dan potongan tunjangan kinerja individu untuk pegawai <strong>{{ $user->name }}</strong>.
                </p>
            </div>

            <!-- Tombol Aksi Tambahan untuk Admin / Pimpinan / Kepegawaian jika ingin ke menu hitung satker -->
            @if(auth()->user()?->canManageTukin())
            <a href="{{ route('tukin.index', ['period' => $month ?? now('Asia/Makassar')->format('Y-m')]) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-900 border border-emerald-200 rounded-xl font-black text-xs transition">
                <span>📊 Ke Hitung Tukin Satker</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
            @endif
        </div>
    </x-slot>

    @php
        $periodDate = \Carbon\Carbon::parse(($month ?? now('Asia/Makassar')->format('Y-m')) . '-01');
        $periodFormatted = $periodDate->locale('id')->isoFormat('MMMM Y');

        // Nilai dasar dan komponen perhitungan (prioritaskan data resmi tersimpan di database jika ada)
        $isOfficial = !empty($calcItem);
        $baseAmount = $isOfficial ? $calcItem->base_amount : ($tukin['base_tukin'] ?? 0);
        $grade = $isOfficial ? ($calcItem->grade ?: ($user->jobClass?->class_name ?? '-')) : ($tukin['job_class'] ?? ($user->jobClass?->class_name ?? '-'));
        
        $attendancePenaltyPercent = $isOfficial ? $calcItem->attendance_penalty_percent : ($tukin['attendance_penalty_percent'] ?? 0);
        $attendanceDeduction = $isOfficial ? $calcItem->attendance_deduction : ($tukin['attendance_penalty_amount'] ?? 0);
        
        $perfPredicate = $isOfficial ? ($calcItem->performance_predicate ?: ($user->performance_predicate ?? 'Baik')) : ($tukin['performance_predicate'] ?? ($user->performance_predicate ?? 'Baik'));
        $performancePenaltyPercent = $isOfficial ? $calcItem->performance_penalty_percent : ($tukin['performance_penalty_percent'] ?? 0);
        $performanceDeduction = $isOfficial ? $calcItem->performance_deduction : ($tukin['performance_penalty_amount'] ?? 0);
        
        $totalPenaltyPercent = $attendancePenaltyPercent + $performancePenaltyPercent;
        $totalDeduction = $attendanceDeduction + $performanceDeduction;
        
        $taxPercent = $isOfficial ? $calcItem->tax_percent : \App\Models\TukinCalculation::getTaxRateByGolongan($user->golongan);
        $taxAmount = $isOfficial ? $calcItem->tax_amount : round(($taxPercent / 100) * $baseAmount, 2);
        
        $nettoAmount = $isOfficial ? $calcItem->netto_amount : max(0, $baseAmount - $totalDeduction);

        $canManage = auth()->user()?->canManageTukin();
    @endphp

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Alert Notifikasi Flash -->
            @if(session('success'))
            <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-2xl shadow-sm flex items-start gap-3">
                <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div class="text-xs text-emerald-900 font-bold leading-relaxed">
                    {{ session('success') }}
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded-2xl shadow-sm flex items-start gap-3">
                <svg class="w-5 h-5 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div class="text-xs text-red-900 font-bold leading-relaxed">
                    {{ session('error') }}
                </div>
            </div>
            @endif

            <!-- BANNER STATUS APPROVAL (PENCEGAHAN MISKONSEPSI ISI SLIP) -->
            @if(!empty($isApproved))
            <div class="p-5 bg-emerald-50/90 border-2 border-emerald-300 rounded-[2rem] shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-black text-lg shrink-0 shadow-md shadow-emerald-600/20">
                        ✓
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="font-black text-xs sm:text-sm text-emerald-950">Status Slip: Resmi Telah Disetujui (Approved Final)</h4>
                            <span class="px-2 py-0.5 rounded-full text-[8px] font-black bg-emerald-600 text-white uppercase tracking-wider">Valid</span>
                        </div>
                        <p class="text-xs text-emerald-800 font-medium mt-0.5">
                            Disetujui oleh <strong>{{ $approvalInfo['approved_by'] ?? 'Admin / Kepegawaian' }}</strong> pada {{ !empty($approvalInfo['approved_at']) ? \Carbon\Carbon::parse($approvalInfo['approved_at'])->translatedFormat('d F Y H:i') . ' WITA' : '-' }}. Slip Tukin resmi (PDF) dapat diunduh langsung.
                        </p>
                    </div>
                </div>
            </div>
            @else
            <div class="p-5 bg-amber-50/90 border-2 border-amber-300 rounded-[2rem] shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-2xl bg-amber-500 text-white flex items-center justify-center font-black text-lg shrink-0 shadow-md shadow-amber-500/20">
                        ⏳
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="font-black text-xs sm:text-sm text-amber-950">Status Slip: Menunggu Approve Final Admin / Kepegawaian</h4>
                            <span class="px-2 py-0.5 rounded-full text-[8px] font-black bg-amber-200 text-amber-900 uppercase tracking-wider">Belum Final</span>
                        </div>
                        <p class="text-xs text-amber-800 font-medium mt-0.5">
                            Untuk menghindari miskonsepsi isi slip, <strong>Cetak Slip Tukin Resmi (PDF)</strong> hanya bisa dilakukan ketika Admin atau Kepegawaian sudah melakukan <strong>Approve Final</strong> sebagai syarat resmi.
                        </p>
                    </div>
                </div>
            </div>
            @endif
            
            <!-- IDENTITAS PEGAWAI & FILTER PERIODE -->
            <div class="bg-white p-6 sm:p-8 rounded-[2.5rem] shadow-xl border border-gray-100">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 pb-6 border-b border-gray-100">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white flex items-center justify-center text-2xl font-black shadow-lg shadow-emerald-500/20">
                            💰
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-xl font-black text-sipega-navy">{{ $user->name }}</h3>
                                @if(!empty($isApproved))
                                <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black bg-emerald-100 text-emerald-800 border border-emerald-200 uppercase tracking-wider">
                                    ✓ Approved Final
                                </span>
                                @elseif($isOfficial)
                                <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black bg-amber-100 text-amber-800 border border-amber-200 uppercase tracking-wider">
                                    Menunggu Approve Final
                                </span>
                                @else
                                <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black bg-sky-100 text-sky-800 border border-sky-200 uppercase tracking-wider">
                                    Simulasi Presensi
                                </span>
                                @endif
                            </div>
                            <div class="flex flex-wrap items-center gap-3 mt-1 text-xs text-gray-500 font-medium">
                                <span><strong>NIP:</strong> {{ $user->nip ?: '-' }}</span>
                                <span>•</span>
                                <span><strong>Jabatan:</strong> {{ $user->position ?: '-' }}</span>
                                <span>•</span>
                                <span><strong>Pangkat/Gol:</strong> {{ $user->pangkat_golongan ?: ($user->golongan ?: '-') }}</span>
                                <span>•</span>
                                <span class="text-emerald-700 font-bold"><strong>Kelas:</strong> Grade {{ $grade }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Navigasi Cepat -->
                    <div class="flex items-center gap-2 p-1 bg-slate-100 rounded-2xl border border-slate-200/60 shadow-inner">
                        <span class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 text-white text-xs font-black uppercase tracking-wider rounded-xl shadow-md">
                            <span>💰 Tukin</span>
                            <span class="text-[8px] bg-white/20 text-white px-2 py-0.5 rounded-full font-black">AKTIF</span>
                        </span>

                        <a href="{{ route('attendance.recap', ['id' => $user->id, 'month' => $month ?? now('Asia/Makassar')->format('Y-m')]) }}" class="inline-flex items-center gap-2 px-4 py-2.5 text-slate-700 hover:text-sipega-navy hover:bg-white text-xs font-bold uppercase tracking-wider rounded-xl transition">
                            <span>📄 Log Presensi</span>
                        </a>
                    </div>
                </div>

                <!-- Bar Filter Periode & Unduh Dokumen -->
                <div class="pt-6 flex flex-wrap justify-between items-center gap-4">
                    <form method="GET" action="{{ route('tukin.pegawai') }}" class="flex items-center gap-3">
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-wider text-gray-500 mb-1">Periode Perhitungan Bulan & Tahun</label>
                            <div class="flex items-center gap-2">
                                <input type="month" name="month" value="{{ $month ?? now('Asia/Makassar')->format('Y-m') }}" onchange="this.form.submit()" class="text-xs font-black rounded-xl border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <span class="text-xs font-bold text-gray-600 bg-gray-50 px-3 py-2 rounded-xl border border-gray-200">{{ $periodFormatted }}</span>
                            </div>
                        </div>
                    </form>

                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Unduh PDF Slip Tukin (Dengan Syarat Approval) -->
                        @if(!empty($isApproved))
                        <a href="{{ route('tukin.download_slip', ['id' => $user->id, 'period' => $month ?? now('Asia/Makassar')->format('Y-m'), 'month' => $month ?? now('Asia/Makassar')->format('Y-m')]) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-black text-xs uppercase tracking-wider shadow-md shadow-emerald-600/20 transition transform hover:-translate-y-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span>Unduh Slip Tukin Resmi (PDF)</span>
                        </a>
                        @elseif($canManage)
                        <a href="{{ route('tukin.download_slip', ['id' => $user->id, 'period' => $month ?? now('Asia/Makassar')->format('Y-m'), 'month' => $month ?? now('Asia/Makassar')->format('Y-m')]) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-black text-xs uppercase tracking-wider shadow-md shadow-amber-600/20 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span>Pratinjau Slip (Draft - Admin)</span>
                        </a>
                        @else
                        <button type="button" onclick="alert('Cetak Slip Tukin Resmi (PDF) periode {{ $periodFormatted }} belum bisa dilakukan karena belum di-Approve Final oleh Admin atau Tim Kepegawaian.')" class="cursor-not-allowed opacity-75 inline-flex items-center gap-2 px-4 py-2.5 bg-gray-200 text-gray-600 border border-gray-300 rounded-xl font-bold text-xs uppercase tracking-wider">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <span>Cetak Slip Resmi (Menunggu Approve)</span>
                        </button>
                        @endif

                        <!-- Unduh Laporan Harian -->
                        <a href="{{ route('attendance.download_pdf', ['id' => $user->id, 'month' => $month ?? now('Asia/Makassar')->format('Y-m')]) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-sky-700 hover:bg-sky-800 text-white rounded-xl font-black text-xs uppercase tracking-wider shadow-md shadow-sky-700/20 transition transform hover:-translate-y-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span>Unduh Laporan Presensi</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- CARD UTAMA: ESTIMASI BERSIH & KOMPONEN HAK/POTONGAN -->
            <div class="bg-gradient-to-br from-sipega-navy via-[#002244] to-[#001326] p-8 sm:p-12 rounded-[3.5rem] shadow-2xl text-white relative overflow-hidden group">
                <div class="relative z-10">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-2">
                        <span class="text-xs font-black uppercase text-sipega-orange tracking-[.35em]">
                            Take Home Pay (Tukin Bersih) • Periode {{ $periodFormatted }}
                        </span>
                        @if(!empty($isApproved))
                        <span class="self-start sm:self-auto text-[10px] font-black px-3 py-1 bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 rounded-full uppercase tracking-wider">
                            ✓ Hasil Rekapitulasi Final (Disetujui)
                        </span>
                        @else
                        <span class="self-start sm:self-auto text-[10px] font-black px-3 py-1 bg-amber-500/20 text-amber-300 border border-amber-500/30 rounded-full uppercase tracking-wider">
                            ⏳ Rekapitulasi Berjalan (Belum Approve Final)
                        </span>
                        @endif
                    </div>

                    <div class="flex flex-col sm:flex-row items-baseline gap-4 mb-8">
                        <p class="text-5xl sm:text-6xl font-black tracking-tight text-white">
                            Rp {{ number_format($nettoAmount, 0, ',', '.') }}
                        </p>
                        @if($totalPenaltyPercent > 0)
                        <span class="text-xs font-black text-rose-300 bg-rose-500/20 px-3 py-1.5 rounded-full border border-rose-500/30">
                            Total Penalti: -{{ number_format($totalPenaltyPercent, 2) }}% (-Rp {{ number_format($totalDeduction, 0, ',', '.') }})
                        </span>
                        @else
                        <span class="text-xs font-black text-emerald-300 bg-emerald-500/20 px-3 py-1.5 rounded-full border border-emerald-500/30">
                            ⭐ 100% Utuh Tanpa Potongan
                        </span>
                        @endif
                    </div>
                    
                    <!-- Grid 3 Kolom Rincian Komponen -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-8 border-t border-white/10">
                        <!-- 1. Hak Dasar Tukin -->
                        <div class="bg-white/5 p-5 rounded-2xl border border-white/10 flex flex-col justify-between">
                            <div>
                                <p class="text-[10px] font-black uppercase opacity-60 tracking-widest text-sipega-orange mb-1">1. Hak Dasar (Plafon)</p>
                                <p class="text-lg font-black">Rp {{ number_format($baseAmount, 0, ',', '.') }}</p>
                            </div>
                            <p class="text-[10px] text-gray-300 mt-2 font-medium">Berdasarkan Kelas Jabatan <strong>Grade {{ $grade }}</strong> (Perpres 18/2025)</p>
                        </div>

                        <!-- 2. Potongan Disiplin Presensi -->
                        <div class="bg-white/5 p-5 rounded-2xl border border-white/10 flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <p class="text-[10px] font-black uppercase opacity-60 tracking-widest text-sipega-orange">2. Potongan Presensi</p>
                                    <span class="text-xs font-black text-rose-300">-{{ number_format($attendancePenaltyPercent, 2) }}%</span>
                                </div>
                                <p class="text-lg font-black text-rose-300">-Rp {{ number_format($attendanceDeduction, 0, ',', '.') }}</p>
                            </div>
                            <p class="text-[10px] text-gray-300 mt-2 font-medium">Keterlambatan (TL), Pulang Cepat (PSW), dan Alpa</p>
                        </div>

                        <!-- 3. Potongan Evaluasi Kinerja -->
                        <div class="bg-white/5 p-5 rounded-2xl border border-white/10 flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <p class="text-[10px] font-black uppercase opacity-60 tracking-widest text-sipega-orange">3. Potongan Kinerja</p>
                                    <span class="text-xs font-black {{ $performancePenaltyPercent > 0 ? 'text-amber-300' : 'text-emerald-300' }}">
                                        {{ $performancePenaltyPercent > 0 ? '-'.number_format($performancePenaltyPercent, 2).'%' : '0%' }}
                                    </span>
                                </div>
                                <p class="text-lg font-black {{ $performancePenaltyPercent > 0 ? 'text-amber-300' : 'text-emerald-300' }}">
                                    {{ $performancePenaltyPercent > 0 ? '-Rp '.number_format($performanceDeduction, 0, ',', '.') : 'Rp 0' }}
                                </p>
                            </div>
                            <p class="text-[10px] text-gray-300 mt-2 font-medium">Predikat Kinerja: <strong class="text-white">{{ $perfPredicate }}</strong></p>
                        </div>
                    </div>
                </div>

                <!-- Watermark Background -->
                <div class="absolute top-0 right-0 p-4 opacity-5 pointer-events-none text-9xl font-black rotate-12 -mr-16 -mt-16">TUKIN</div>
            </div>

            <!-- ANALISIS RINCIAN PERHITUNGAN (PERMENDIKDASMEN 14/2025) -->
            <div class="bg-white overflow-hidden shadow-2xl sm:rounded-[3rem] p-8 sm:p-10 border border-gray-100">
                <div class="flex items-center justify-between pb-6 border-b border-gray-100 mb-6">
                    <div>
                        <h3 class="text-xl font-black text-sipega-navy flex items-center gap-2.5">
                            <span>📊</span> Rincian Analisis Perhitungan Pegawai
                        </h3>
                        <p class="text-xs text-gray-500 font-medium mt-0.5">
                            Parameter mengacu pada regulasi <strong>Permendikdasmen No. 14 Tahun 2025</strong>.
                        </p>
                    </div>
                    <span class="text-[10px] font-black px-3 py-1 rounded-full bg-slate-100 text-slate-700 uppercase tracking-wider">
                        {{ $periodFormatted }}
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- KELOMPOK 1: KEDISIPLINAN & KEHADIRAN -->
                    <div class="p-6 bg-slate-50/80 rounded-[2rem] border border-gray-100 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-center mb-3">
                                <span class="font-black text-sipega-navy uppercase text-xs tracking-wider flex items-center gap-1.5">
                                    <span>⏱️</span> Kedisiplinan Kehadiran (Presensi)
                                </span>
                                <span class="text-base font-black text-rose-600">-{{ number_format($attendancePenaltyPercent, 2) }}%</span>
                            </div>

                            <p class="text-xs text-gray-500 leading-relaxed mb-4">
                                Keterlambatan (TL) dan Pulang Sebelum Waktu (PSW) dikenakan sanksi pemotongan progresif (0,25% s/d 1,25%) per kejadian sesuai rentang durasi menit.
                            </p>

                            @if($isOfficial)
                            <!-- Matriks Absensi Resmi dari Database -->
                            <div class="grid grid-cols-3 sm:grid-cols-5 gap-2 pt-2 border-t border-gray-200">
                                <div class="bg-white p-2.5 rounded-xl border border-gray-100 text-center">
                                    <p class="text-[9px] font-black text-gray-400 uppercase">Hadir</p>
                                    <p class="text-sm font-black text-emerald-700">{{ $calcItem->hadir_count }}</p>
                                </div>
                                <div class="bg-white p-2.5 rounded-xl border border-gray-100 text-center">
                                    <p class="text-[9px] font-black text-gray-400 uppercase">Dinas Luar</p>
                                    <p class="text-sm font-black text-blue-700">{{ $calcItem->dl_count }}</p>
                                </div>
                                <div class="bg-white p-2.5 rounded-xl border border-gray-100 text-center">
                                    <p class="text-[9px] font-black text-gray-400 uppercase">Cuti</p>
                                    <p class="text-sm font-black text-amber-700">{{ $calcItem->cuti_count }}</p>
                                </div>
                                <div class="bg-white p-2.5 rounded-xl border border-gray-100 text-center">
                                    <p class="text-[9px] font-black text-gray-400 uppercase">Sakit</p>
                                    <p class="text-sm font-black text-purple-700">{{ $calcItem->sakit_count }}</p>
                                </div>
                                <div class="bg-white p-2.5 rounded-xl border border-gray-100 text-center">
                                    <p class="text-[9px] font-black text-gray-400 uppercase">Alpa</p>
                                    <p class="text-sm font-black text-rose-700">{{ $calcItem->alpa_count }}</p>
                                </div>
                            </div>
                            @elseif(!empty($tukin['data']['summary']))
                            <!-- Ringkasan Log Presensi Real-Time -->
                            <div class="grid grid-cols-3 gap-2 pt-2 border-t border-gray-200">
                                <div class="bg-white p-2.5 rounded-xl border border-gray-100 text-center">
                                    <p class="text-[9px] font-black text-gray-400 uppercase">Presensi Hadir</p>
                                    <p class="text-sm font-black text-emerald-700">{{ $tukin['data']['summary']['total_present'] ?? 0 }} hari</p>
                                </div>
                                <div class="bg-white p-2.5 rounded-xl border border-gray-100 text-center">
                                    <p class="text-[9px] font-black text-gray-400 uppercase">Surat Tugas</p>
                                    <p class="text-sm font-black text-blue-700">{{ $tukin['data']['summary']['total_st'] ?? 0 }} hari</p>
                                </div>
                                <div class="bg-white p-2.5 rounded-xl border border-gray-100 text-center">
                                    <p class="text-[9px] font-black text-gray-400 uppercase">Alpa</p>
                                    <p class="text-sm font-black text-rose-700">{{ $tukin['data']['summary']['total_alpa'] ?? 0 }} hari</p>
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="mt-4 pt-3 border-t border-gray-200/80 flex justify-between items-center text-xs font-bold text-gray-600">
                            <span>Nominal Pemotongan Presensi:</span>
                            <span class="text-rose-600 font-black">-Rp {{ number_format($attendanceDeduction, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- KELOMPOK 2: KUALITAS KINERJA & PREDIKAT -->
                    <div class="p-6 bg-slate-50/80 rounded-[2rem] border border-gray-100 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-center mb-3">
                                <span class="font-black text-sipega-navy uppercase text-xs tracking-wider flex items-center gap-1.5">
                                    <span>🏆</span> Kualitas & Evaluasi Kinerja
                                </span>
                                <span class="text-base font-black {{ $performancePenaltyPercent > 0 ? 'text-amber-600' : 'text-emerald-600' }}">
                                    {{ $performancePenaltyPercent > 0 ? '-'.number_format($performancePenaltyPercent, 2).'%' : 'Optimal (100%)' }}
                                </span>
                            </div>

                            <p class="text-xs text-gray-500 leading-relaxed mb-4">
                                Predikat kinerja periodik di bawah <em>"Baik"</em> memicu pemotongan Tukin secara otomatis: Cukup (-20%), Kurang (-40%), Sangat Kurang (-60%).
                            </p>

                            <div class="bg-white p-4 rounded-2xl border border-gray-100 flex items-center justify-between">
                                <div>
                                    <p class="text-[9px] font-black uppercase text-gray-400">Predikat Kinerja Anda</p>
                                    <p class="text-sm font-black text-sipega-navy mt-0.5">{{ $perfPredicate }}</p>
                                </div>
                                <span class="px-3 py-1.5 rounded-xl text-xs font-black {{ $performancePenaltyPercent > 0 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                                    {{ $performancePenaltyPercent > 0 ? 'Potongan '.number_format($performancePenaltyPercent, 0).'%' : 'Tanpa Potongan' }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-t border-gray-200/80 flex justify-between items-center text-xs font-bold text-gray-600">
                            <span>Nominal Pemotongan Kinerja:</span>
                            <span class="{{ $performancePenaltyPercent > 0 ? 'text-amber-600 font-black' : 'text-emerald-600 font-black' }}">
                                {{ $performancePenaltyPercent > 0 ? '-Rp '.number_format($performanceDeduction, 0, ',', '.') : 'Rp 0' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- RINCIAN HARIAN PELANGGARAN JAM KERJA (JIKA ADA DARI SIMULASI) -->
                @if(!empty($tukin['data']['details']) && count($tukin['data']['details']) > 0)
                <div class="mt-8 pt-6 border-t border-gray-100">
                    <h4 class="text-xs font-black uppercase tracking-wider text-sipega-navy mb-3 flex items-center gap-2">
                        <span>⚠️</span> Log Pelanggaran Jam Kerja Periode Berjalan ({{ count($tukin['data']['details']) }} Kejadian)
                    </h4>
                    <div class="overflow-x-auto rounded-2xl border border-gray-100">
                        <table class="min-w-full divide-y divide-gray-100 text-left text-xs">
                            <thead class="bg-gray-50 text-[10px] font-black text-gray-400 uppercase tracking-wider">
                                <tr>
                                    <th class="px-4 py-3">Tanggal</th>
                                    <th class="px-4 py-3">Jenis Potongan</th>
                                    <th class="px-4 py-3 text-right">Persentase</th>
                                    <th class="px-4 py-3 text-right">Nominal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach($tukin['data']['details'] as $detail)
                                <tr>
                                    <td class="px-4 py-2.5 font-bold text-sipega-navy">
                                        {{ \Carbon\Carbon::parse($detail['date'])->translatedFormat('d F Y') }}
                                    </td>
                                    <td class="px-4 py-2.5 text-gray-600">
                                        {{ $detail['type'] == 'TL/PSW' ? 'Pelanggaran Jam Kerja (TL / PSW)' : $detail['type'] }}
                                    </td>
                                    <td class="px-4 py-2.5 text-right font-black text-rose-600">
                                        -{{ number_format($detail['penalty_percent'], 2) }}%
                                    </td>
                                    <td class="px-4 py-2.5 text-right font-bold text-gray-700">
                                        -Rp {{ number_format($detail['amount'], 0, ',', '.') }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <!-- FOOTER CARD: CETAK PDF SLIP RESMI (DENGAN KONDISI APPROVE FINAL) -->
                <div class="mt-8 pt-6 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="text-xs text-gray-400 font-medium">
                        Waktu Perhitungan Sistem: <strong>{{ now('Asia/Makassar')->translatedFormat('d F Y H:i') }} WITA</strong>
                    </div>

                    @if(!empty($isApproved))
                    <a href="{{ route('tukin.download_slip', ['id' => $user->id, 'period' => $month ?? now('Asia/Makassar')->format('Y-m'), 'month' => $month ?? now('Asia/Makassar')->format('Y-m')]) }}" target="_blank" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black rounded-xl shadow-md transition transform hover:-translate-y-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        <span>Cetak Slip Tukin Resmi (PDF)</span>
                    </a>
                    @elseif($canManage)
                    <a href="{{ route('tukin.download_slip', ['id' => $user->id, 'period' => $month ?? now('Asia/Makassar')->format('Y-m'), 'month' => $month ?? now('Asia/Makassar')->format('Y-m')]) }}" target="_blank" class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-black rounded-xl shadow-md transition transform hover:-translate-y-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>Pratinjau Slip (Draft - Admin)</span>
                    </a>
                    @else
                    <button type="button" onclick="alert('Cetak Slip Tukin Resmi (PDF) periode {{ $periodFormatted }} belum bisa dilakukan karena belum di-Approve Final oleh Admin atau Tim Kepegawaian.')" class="cursor-not-allowed opacity-75 inline-flex items-center gap-2 px-5 py-2.5 bg-gray-200 text-gray-600 border border-gray-300 rounded-xl font-bold text-xs uppercase tracking-wider">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span>Cetak Slip Resmi (Menunggu Approve)</span>
                    </button>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
