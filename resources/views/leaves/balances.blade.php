<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <a href="{{ route('leaves.index') }}" class="text-xs text-sipega-navy font-bold hover:underline">← Kembali ke Data Cuti</a>
                    <span class="text-xs text-gray-400">•</span>
                    <span class="text-xs text-sipega-orange font-bold uppercase tracking-wider">Rekapitulasi Saldo Kuota Cuti</span>
                </div>
                <h2 class="text-2xl font-black text-sipega-navy tracking-tight">Kelola Saldo & Kuota Cuti Pegawai (Tahun {{ $year }})</h2>
            </div>
            <div class="flex items-center gap-2">
                <form method="GET" action="{{ route('leaves.balances') }}" class="flex items-center gap-2">
                    <label class="text-xs font-bold text-gray-600">Pilih Tahun:</label>
                    <select name="year" onchange="this.form.submit()" class="text-xs rounded-xl border-gray-200 focus:border-sipega-navy focus:ring-sipega-navy font-bold">
                        @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
            <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-xl text-xs text-emerald-800 font-bold">
                ✓ {{ session('success') }}
            </div>
            @endif

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-gray-600">
                        <thead class="bg-slate-50 border-b border-gray-100 text-[10px] font-black uppercase tracking-wider text-sipega-navy">
                            <tr>
                                <th class="px-5 py-4">Pegawai ASN</th>
                                <th class="px-5 py-4 text-center">Status</th>
                                <th class="px-5 py-4 text-center">Masa Kerja</th>
                                <th class="px-5 py-4 text-center">N-2 ({{ $year - 2 }})</th>
                                <th class="px-5 py-4 text-center">N-1 ({{ $year - 1 }})</th>
                                <th class="px-5 py-4 text-center">Hak N ({{ $year }})</th>
                                <th class="px-5 py-4 text-center">Terpakai</th>
                                <th class="px-5 py-4 text-center">Sisa N</th>
                                <th class="px-5 py-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($users as $user)
                                @php $bal = $balances[$user->id] ?? \App\Models\LeaveBalance::getOrCreateForUser($user, $year); @endphp
                                <tr class="hover:bg-slate-50/70 transition" x-data="{ editing: false }">
                                    <td class="px-5 py-4">
                                        <div class="font-black text-sipega-navy">{{ $user->name }}</div>
                                        <div class="text-[11px] text-gray-500">NIP. {{ $user->nip ?? '-' }}</div>
                                        <div class="text-[10px] text-gray-400 italic">{{ $user->position ?? '-' }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-center whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $user->effective_status_kepegawaian === 'PPPK' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                                            {{ $user->effective_status_kepegawaian }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-center font-medium">
                                        {{ $user->getMasaKerjaText() }}
                                    </td>
                                    
                                    <!-- View Mode -->
                                    <template x-if="!editing">
                                        <td class="px-5 py-4 text-center font-bold text-amber-600">{{ $bal->remaining_n2 }} hari</td>
                                    </template>
                                    <template x-if="!editing">
                                        <td class="px-5 py-4 text-center font-bold text-emerald-600">{{ $bal->remaining_n1 }} hari</td>
                                    </template>
                                    <template x-if="!editing">
                                        <td class="px-5 py-4 text-center font-bold text-gray-700">{{ $bal->initial_quota }} hari</td>
                                    </template>
                                    <template x-if="!editing">
                                        <td class="px-5 py-4 text-center font-bold text-rose-600">{{ $bal->used_quota }} hari</td>
                                    </template>
                                    <template x-if="!editing">
                                        <td class="px-5 py-4 text-center font-black text-sipega-navy text-sm">{{ $bal->remaining_quota }} hari</td>
                                    </template>
                                    <template x-if="!editing">
                                        <td class="px-5 py-4 text-center whitespace-nowrap">
                                            <button @click="editing = true" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-sipega-navy font-bold rounded-lg text-[10px] transition">
                                                Edit Kuota
                                            </button>
                                        </td>
                                    </template>

                                    <!-- Edit Mode Form Inline -->
                                    <template x-if="editing">
                                        <td colspan="6" class="px-5 py-4 bg-amber-50/50">
                                            <form method="POST" action="{{ route('leaves.update_balance', $bal->id) }}" class="flex flex-wrap items-center justify-between gap-3">
                                                @csrf
                                                @method('PUT')
                                                <div class="flex items-center gap-2">
                                                    <div>
                                                        <label class="block text-[9px] font-bold text-gray-500">N-2</label>
                                                        <input type="number" name="remaining_n2" value="{{ $bal->remaining_n2 }}" min="0" max="12" class="w-16 text-xs rounded border-gray-300">
                                                    </div>
                                                    <div>
                                                        <label class="block text-[9px] font-bold text-gray-500">N-1</label>
                                                        <input type="number" name="remaining_n1" value="{{ $bal->remaining_n1 }}" min="0" max="12" class="w-16 text-xs rounded border-gray-300">
                                                    </div>
                                                    <div>
                                                        <label class="block text-[9px] font-bold text-gray-500">Hak N</label>
                                                        <input type="number" name="initial_quota" value="{{ $bal->initial_quota }}" min="0" max="12" class="w-16 text-xs rounded border-gray-300">
                                                    </div>
                                                    <div>
                                                        <label class="block text-[9px] font-bold text-gray-500">Terpakai</label>
                                                        <input type="number" name="used_quota" value="{{ $bal->used_quota }}" min="0" max="12" class="w-16 text-xs rounded border-gray-300">
                                                    </div>
                                                    <div>
                                                        <label class="block text-[9px] font-bold text-gray-500">Sisa N</label>
                                                        <input type="number" name="remaining_quota" value="{{ $bal->remaining_quota }}" min="0" max="12" class="w-16 text-xs rounded border-gray-300">
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-1.5">
                                                    <button type="submit" class="px-3 py-1.5 bg-emerald-600 text-white font-bold rounded text-xs">Simpan</button>
                                                    <button type="button" @click="editing = false" class="px-2 py-1.5 bg-gray-200 text-gray-700 rounded text-xs">Batal</button>
                                                </div>
                                            </form>
                                        </td>
                                    </template>

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($users->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $users->links() }}
                </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
