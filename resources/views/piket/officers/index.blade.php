<x-app-layout>
    <div class="bg-slate-100 min-h-screen py-6" x-data="{ showAddModal: false, editModal: false, selectedOfficer: null }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- SUBNAVIGASI PIKET -->
            @include('piket.partials.nav')

            <!-- FLASH MESSAGE -->
            @if(session('success'))
            <div class="bg-emerald-50 border-l-4 border-emerald-600 p-4 rounded-xl text-xs text-emerald-900 font-bold shadow-sm">
                ✓ {{ session('success') }}
            </div>
            @endif

            <!-- HEADER -->
            <div class="bg-white rounded-2xl p-6 shadow-md border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xl">👥</span>
                        <h1 class="text-xl font-black text-slate-900 uppercase">Data Petugas Piket ULT & Perpustakaan</h1>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Kelola daftar pegawai SIM SPP yang ditugaskan sebagai petugas pelayanan publik dan petugas cadangan.</p>
                </div>

                <button type="button" @click="showAddModal = true" class="px-4 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wider text-white shadow-md transition flex items-center gap-2" style="background-color: #003366;">
                    <span>+</span>
                    <span>Tambah Petugas Piket</span>
                </button>
            </div>

            <!-- FILTER & SEARCH BAR -->
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200 flex flex-wrap items-center justify-between gap-3 text-xs">
                <form method="GET" action="{{ route('piket.officers.index') }}" class="flex flex-wrap items-center gap-2.5 flex-1">
                    <select name="unit_id" class="text-xs rounded-xl border-slate-300 font-medium py-1.5 focus:border-blue-600" onchange="this.form.submit()">
                        <option value="">-- Semua Unit --</option>
                        @foreach($units as $u)
                            <option value="{{ $u->id }}" {{ $unitId == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>

                    <select name="assignment_role" class="text-xs rounded-xl border-slate-300 font-medium py-1.5 focus:border-blue-600" onchange="this.form.submit()">
                        <option value="">-- Semua Peran --</option>
                        <option value="utama" {{ $role === 'utama' ? 'selected' : '' }}>Petugas Utama</option>
                        <option value="cadangan" {{ $role === 'cadangan' ? 'selected' : '' }}>Petugas Cadangan</option>
                        <option value="keduanya" {{ $role === 'keduanya' ? 'selected' : '' }}>Utama & Cadangan</option>
                    </select>

                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari Nama, NIP, Jabatan..." class="text-xs rounded-xl border-slate-300 py-1.5 px-3 min-w-[200px] focus:border-blue-600">

                    <button type="submit" class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl font-bold">
                        Cari
                    </button>

                    @if($unitId || $role || $search)
                        <a href="{{ route('piket.officers.index') }}" class="text-rose-600 hover:underline font-bold text-xs">Reset Filter</a>
                    @endif
                </form>
            </div>

            <!-- TABEL PETUGAS -->
            <div class="bg-white rounded-2xl shadow-md border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="text-white text-xs font-bold" style="background-color: #003366;">
                            <tr>
                                <th class="px-3 py-3.5 text-center w-10 border-b border-blue-900">No.</th>
                                <th class="px-4 py-3.5 border-b border-blue-900">Nama Lengkap & NIP</th>
                                <th class="px-4 py-3.5 border-b border-blue-900">Unit Kerja / Gugus Mutu</th>
                                <th class="px-4 py-3.5 border-b border-blue-900">Jabatan</th>
                                <th class="px-4 py-3.5 border-b border-blue-900">Unit Piket</th>
                                <th class="px-3 py-3.5 border-b border-blue-900 text-center">Peran Piket</th>
                                <th class="px-3 py-3.5 border-b border-blue-900 text-center">Maks. Shift/Bln</th>
                                <th class="px-3 py-3.5 border-b border-blue-900 text-center">Status Kesiapan</th>
                                <th class="px-4 py-3.5 border-b border-blue-900 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($officers as $index => $item)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-3 py-3.5 text-center font-bold text-slate-400">
                                        {{ $officers->firstItem() + $index }}.
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <div class="font-extrabold text-slate-900">{{ $item->user->name }}</div>
                                        <div class="text-[10px] text-slate-500 font-mono">NIP: {{ $item->user->nip }}</div>
                                        @if($item->user->phone)
                                            <div class="text-[9px] text-blue-600 font-sans">📞 {{ $item->user->phone }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-slate-700">
                                        {{ $item->user->gugus_mutu ?: ($item->user->unit_kerja ?: '-') }}
                                    </td>
                                    <td class="px-4 py-3.5 text-slate-600">
                                        {{ $item->user->position ?: '-' }}
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-black {{ $item->unit->code === 'ULT' ? 'bg-blue-100 text-blue-900' : 'bg-emerald-100 text-emerald-900' }}">
                                            {{ $item->unit->code }} ({{ $item->unit->name }})
                                        </span>
                                    </td>
                                    <td class="px-3 py-3.5 text-center whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                            @if($item->assignment_role === 'utama') bg-emerald-100 text-emerald-800
                                            @elseif($item->assignment_role === 'cadangan') bg-amber-100 text-amber-800
                                            @else bg-purple-100 text-purple-800 @endif">
                                            {{ $item->assignment_role }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3.5 text-center font-bold text-slate-800 font-mono">
                                        {{ $item->max_shifts_per_month }}x
                                    </td>
                                    <td class="px-3 py-3.5 text-center whitespace-nowrap">
                                        @if($item->is_available)
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                ✓ Siap Ditugaskan
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800" title="{{ $item->unavailability_reason }}">
                                                ✕ Tidak Siap
                                            </span>
                                            @if($item->unavailability_reason)
                                                <div class="text-[9px] text-slate-400 mt-0.5 max-w-[120px] truncate">{{ $item->unavailability_reason }}</div>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button type="button" 
                                                @click="editModal = true; selectedOfficer = {{ json_encode($item) }}"
                                                class="px-2.5 py-1 bg-slate-100 hover:bg-blue-50 text-blue-700 font-bold rounded-lg text-[10px] border border-slate-300 transition">
                                                Edit Status
                                            </button>
                                            <form method="POST" action="{{ route('piket.officers.destroy', $item->id) }}" onsubmit="return confirm('Keluarkan pegawai ini dari daftar petugas piket unit?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-2.5 py-1 bg-slate-100 hover:bg-rose-50 text-rose-700 font-bold rounded-lg text-[10px] border border-slate-300 transition">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-12 text-center text-slate-400 italic">
                                        Belum ada data petugas piket yang sesuai filter.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($officers->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50">
                    {{ $officers->links() }}
                </div>
                @endif
            </div>

        </div>

        <!-- ================= MODAL TAMBAH PETUGAS PIKET ================= -->
        <div x-show="showAddModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showAddModal" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showAddModal = false"></div>
                <div x-show="showAddModal" x-transition.scale class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200 p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-white shadow" style="background-color: #003366;">➕</span>
                            <h3 class="text-sm font-black uppercase text-slate-900">Tambah Petugas Piket</h3>
                        </div>
                        <button @click="showAddModal = false" class="text-slate-400 hover:text-slate-700 font-bold text-lg">✕</button>
                    </div>

                    <form method="POST" action="{{ route('piket.officers.store') }}" class="space-y-4 text-xs">
                        @csrf
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Pilih Pegawai SIM SPP</label>
                            <select name="user_id" class="w-full text-xs rounded-xl border-slate-300 font-medium" required>
                                <option value="">-- Pilih Pegawai --</option>
                                @foreach($availableEmployees as $emp)
                                    <option value="{{ $emp->id }}">
                                        {{ $emp->name }} (NIP: {{ $emp->nip }} - {{ $emp->gugus_mutu ?? 'Non-GM' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Unit Pelayanan</label>
                            <select name="piket_unit_id" class="w-full text-xs rounded-xl border-slate-300 font-medium" required>
                                @foreach($units as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->code }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Peran Penugasan</label>
                                <select name="assignment_role" class="w-full text-xs rounded-xl border-slate-300 font-medium" required>
                                    <option value="utama">Petugas Utama</option>
                                    <option value="cadangan">Petugas Cadangan</option>
                                    <option value="keduanya">Utama & Cadangan</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Maks. Shift per Bulan</label>
                                <input type="number" name="max_shifts_per_month" value="4" min="1" max="20" class="w-full text-xs rounded-xl border-slate-300 font-mono font-bold text-center" required>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                            <button type="button" @click="showAddModal = false" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl font-bold">Batal</button>
                            <button type="submit" class="px-5 py-2 text-white rounded-xl font-bold shadow transition" style="background-color: #003366;">Simpan Petugas</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ================= MODAL EDIT STATUS PETUGAS ================= -->
        <div x-show="editModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="editModal" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="editModal = false"></div>
                <div x-show="editModal" x-transition.scale class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200 p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="text-sm font-black uppercase text-slate-900">Perbarui Status Petugas Piket</h3>
                        <button @click="editModal = false" class="text-slate-400 hover:text-slate-700 font-bold text-lg">✕</button>
                    </div>

                    <template x-if="selectedOfficer">
                        <form method="POST" :action="'/piket/officers/' + selectedOfficer.id" class="space-y-4 text-xs">
                            @csrf
                            @method('PUT')

                            <div class="bg-slate-50 p-3 rounded-xl border border-slate-200">
                                <div class="font-extrabold text-slate-900" x-text="selectedOfficer.user ? selectedOfficer.user.name : ''"></div>
                                <div class="text-[10px] text-slate-500" x-text="'Unit: ' + (selectedOfficer.unit ? selectedOfficer.unit.name : '')"></div>
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Peran Penugasan</label>
                                <select name="assignment_role" x-model="selectedOfficer.assignment_role" class="w-full text-xs rounded-xl border-slate-300 font-medium">
                                    <option value="utama">Petugas Utama</option>
                                    <option value="cadangan">Petugas Cadangan</option>
                                    <option value="keduanya">Utama & Cadangan</option>
                                </select>
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Status Kesiapan Piket</label>
                                <select name="is_available" x-model="selectedOfficer.is_available" class="w-full text-xs rounded-xl border-slate-300 font-medium">
                                    <option :value="1">✓ Siap Ditugaskan (Tersedia)</option>
                                    <option :value="0">✕ Sementara Tidak Dapat Dijadwalkan</option>
                                </select>
                            </div>

                            <div x-show="!selectedOfficer.is_available">
                                <label class="block font-bold text-slate-700 mb-1">Alasan Tidak Dapat Dijadwalkan</label>
                                <input type="text" name="unavailability_reason" x-model="selectedOfficer.unavailability_reason" placeholder="Contoh: Sakit jangka panjang / Tugas Khusus..." class="w-full text-xs rounded-xl border-slate-300">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Maks. Shift per Bulan</label>
                                <input type="number" name="max_shifts_per_month" x-model="selectedOfficer.max_shifts_per_month" min="1" max="20" class="w-full text-xs rounded-xl border-slate-300 font-mono font-bold text-center">
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                                <button type="button" @click="editModal = false" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl font-bold">Batal</button>
                                <button type="submit" class="px-5 py-2 text-white rounded-xl font-bold shadow" style="background-color: #003366;">Simpan Perubahan</button>
                            </div>
                        </form>
                    </template>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
