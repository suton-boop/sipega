<x-app-layout>
    <div class="bg-slate-100 min-h-screen py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- SUBNAVIGASI PIKET -->
            @include('piket.partials.nav')

            <!-- FLASH MESSAGE -->
            @if(session('error'))
            <div class="bg-rose-50 border-l-4 border-rose-600 p-4 rounded-xl text-xs text-rose-900 font-bold shadow-sm">
                ✕ {{ session('error') }}
            </div>
            @endif

            <!-- CARD PENERBITAN -->
            <div class="bg-white rounded-2xl p-6 shadow-md border border-slate-200 space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-white text-lg shadow" style="background-color: #003366;">
                            📜
                        </div>
                        <div>
                            <h1 class="text-lg font-black uppercase text-slate-900">Penerbitan Jadwal Piket Resmi (SK Pelayanan)</h1>
                            <p class="text-xs text-slate-500">Mengesahkan paket jadwal dinas menjadi dokumen resmi operasional BPMP Kalimantan Timur.</p>
                        </div>
                    </div>
                    <a href="{{ route('piket.schedules.show', $schedule->id) }}" class="text-xs text-slate-500 hover:text-slate-800 font-bold">← Batal</a>
                </div>

                <div class="bg-blue-50/60 p-4 rounded-2xl border border-blue-200/80 text-xs space-y-2">
                    <div class="font-extrabold text-blue-950 uppercase text-[10px]">Informasi Paket Jadwal yang Akan Diterbitkan:</div>
                    <div class="grid grid-cols-2 gap-2 text-slate-700">
                        <div>Judul: <strong>{{ $schedule->title }}</strong></div>
                        <div>Unit: <strong>{{ $schedule->unit->name }}</strong></div>
                        <div>Periode: <strong>{{ $schedule->start_date->translatedFormat('d M Y') }} - {{ $schedule->end_date->translatedFormat('d M Y') }}</strong></div>
                        <div>Total Shift: <strong>{{ $schedule->assignments()->count() }} Shift Petugas</strong></div>
                    </div>
                </div>

                <form method="POST" action="{{ route('piket.schedules.publish', $schedule->id) }}" class="space-y-4 text-xs">
                    @csrf

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nomor Surat Keputusan / Dokumen Resmi Penugasan</label>
                        <input type="text" name="document_number" value="{{ $schedule->publication->document_number ?? '0452/B7.5/KP.01.00/' . date('Y') }}" class="w-full text-xs rounded-xl border-slate-300 font-mono font-bold" required>
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Format: Nomor Naskah Dinas / Kode Klasifikasi Satker / Tahun.</span>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tanggal Mulai Berlaku Efektif</label>
                        <input type="date" name="effective_date" value="{{ $schedule->publication->effective_date ? $schedule->publication->effective_date->toDateString() : $schedule->start_date->toDateString() }}" class="w-full text-xs rounded-xl border-slate-300 font-bold" required>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Nama Pejabat Penandatangan / Pengesah</label>
                            <input type="text" name="signatory_name" value="{{ $schedule->publication->signatory_name ?? ($kepala->name ?? 'Dr. Jarwoko, M.Pd') }}" class="w-full text-xs rounded-xl border-slate-300 font-bold" required>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">NIP Pejabat</label>
                            <input type="text" name="signatory_nip" value="{{ $schedule->publication->signatory_nip ?? ($kepala->nip ?? '196812151994031003') }}" class="w-full text-xs rounded-xl border-slate-300 font-mono" required>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Jabatan Penandatangan</label>
                        <input type="text" name="signatory_position" value="{{ $schedule->publication->signatory_position ?? 'Kepala Balai Penjaminan Mutu Pendidikan Provinsi Kalimantan Timur' }}" class="w-full text-xs rounded-xl border-slate-300 font-bold" required>
                    </div>

                    <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-[11px] text-amber-900 leading-relaxed">
                        ⚠️ <strong>Pemberitahuan:</strong> Setelah jadwal diterbitkan, status berubah menjadi <strong>RESMI DITERBITKAN</strong>. Seluruh pegawai dapat melihat jadwal resmi dan mengunduh berkas SK PDF. Perubahan jadwal selanjutnya harus melalui mekanisme Pergantian Petugas atau Tukar Jadwal.
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                        <a href="{{ route('piket.schedules.show', $schedule->id) }}" class="px-5 py-2.5 rounded-xl bg-slate-100 text-slate-700 font-bold">Batal</a>
                        <button type="submit" class="px-6 py-2.5 rounded-xl font-black text-xs uppercase tracking-wider text-white shadow-lg transition" style="background: linear-gradient(135deg, #003366 0%, #005a9c 100%);">
                            Mengesahkan & Terbitkan Jadwal Sekarang
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
