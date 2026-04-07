<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-sipega-navy leading-tight font-sans tracking-wide">
            {{ __('Monitoring & Penilaian Kinerja') }} 🏆
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-2xl relative mb-4 font-bold text-sm shadow-sm" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('leader.agenda.bulk-evaluate') }}" method="POST">
                @csrf
                <div class="bg-white overflow-hidden shadow-2xl sm:rounded-3xl border-t-[10px] border-sipega-navy relative">
                    <div class="p-8">
                        <div class="flex justify-between items-center mb-8">
                            <h3 class="text-2xl font-black text-sipega-navy italic">📋 Daftar Realisasi Pegawai (Pending Penilaian)</h3>
                            
                            <!-- Bulk Controls (Desktop) -->
                            <div id="bulkControls" class="hidden flex items-center gap-4 bg-orange-50 p-4 rounded-2xl border-2 border-orange-200">
                                <div class="flex items-center gap-2">
                                    <label class="text-[10px] font-black uppercase text-orange-600">Nilai Massal:</label>
                                    <input type="number" name="bulk_rating" value="100" min="0" max="100" class="w-20 border-gray-200 rounded-lg text-center font-bold" placeholder="0-100">
                                </div>
                                <button type="submit" class="bg-sipega-orange text-white text-[10px] px-6 py-2 rounded-xl font-black uppercase shadow-md hover:bg-orange-600 transition">
                                    Eksekusi Terpilih ⚡
                                </button>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-gray-50 text-sipega-navy uppercase text-[10px] font-black tracking-widest border-b border-gray-100">
                                        <th class="p-4 w-12 text-center">
                                            <input type="checkbox" id="checkAll" class="rounded text-sipega-orange focus:ring-sipega-orange">
                                        </th>
                                        <th class="p-4">Pegawai</th>
                                        <th class="p-4">Tanggal Agenda</th>
                                        <th class="p-4">Rincian Kegiatan</th>
                                        <th class="p-4">Status & Waktu</th>
                                        <th class="p-4">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="text-sm">
                                    @forelse($pendingAgendas as $agenda)
                                    <tr class="border-b border-gray-50 hover:bg-orange-50 transition-colors">
                                        <td class="p-4 text-center">
                                            @if(!$agenda->leader_rating)
                                                <input type="checkbox" name="agenda_ids[]" value="{{ $agenda->id }}" class="agenda-checkbox rounded text-sipega-orange focus:ring-sipega-orange">
                                            @else
                                                <span class="text-green-500">✅</span>
                                            @endif
                                        </td>
                                        <td class="p-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full bg-sipega-navy flex items-center justify-center text-white font-bold">{{ substr($agenda->user->name, 0, 1) }}</div>
                                                <div>
                                                    <p class="font-bold text-gray-800">{{ $agenda->user->name }}</p>
                                                    <p class="text-[10px] text-gray-400 uppercase font-bold">{{ $agenda->user->role }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="p-4">
                                            <span class="font-bold text-gray-600">{{ \Carbon\Carbon::parse($agenda->date)->format('d/m/Y') }}</span>
                                        </td>
                                        <td class="p-4">
                                            <ul class="list-disc pl-4 space-y-1">
                                                @foreach($agenda->items as $item)
                                                <li>
                                                    <span class="text-xs font-medium">{{ $item->plan_description }}</span>
                                                    <span class="ml-1 px-2 py-0.5 rounded-full text-[8px] font-black uppercase {{ $item->status == 'completed' ? 'bg-green-100 text-green-700' : ($item->status == 'changed' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700') }}">
                                                        {{ $item->status }}
                                                    </span>
                                                </li>
                                                @endforeach
                                            </ul>
                                        </td>
                                        <td class="p-4 text-center">
                                            @if($agenda->leader_rating)
                                                <span class="text-lg font-black text-green-600">{{ $agenda->leader_rating }}</span>
                                                <p class="text-[9px] text-green-500 font-black uppercase tracking-tighter">Verified ✅</p>
                                            @else
                                                <span class="text-gray-300 font-black">---</span>
                                                <p class="text-[9px] text-orange-400 font-black uppercase tracking-tighter">Waiting Evaluation</p>
                                            @endif
                                        </td>
                                        <td class="p-4">
                                            <button type="button" onclick="openEvalModal('{{ $agenda->id }}', '{{ $agenda->user->name }}')" class="bg-sipega-navy hover:bg-black text-white text-[10px] px-4 py-2 rounded-full font-black uppercase tracking-widest shadow-lg transition">
                                                Beri Nilai ✍️
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="p-12 text-center text-gray-400 italic font-medium">Belum ada agenda yang selesai direalisasikan untuk dinilai.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-8">
                            {{ $pendingAgendas->links() }}
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Evaluation Modal -->
    <div id="evalModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-[2rem] w-full max-w-lg shadow-2xl transform transition-all">
            <form action="" id="evalForm" method="POST" class="p-10">
                @csrf
                <h3 class="text-2xl font-black text-sipega-navy mb-2 italic">🏆 Penilaian Performa</h3>
                <p class="text-sm text-gray-500 mb-8 font-medium">Pegawai: <span id="evalUserName" class="text-sipega-orange font-bold">...</span></p>
                
                <div class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-3 ml-2">Skor Kinerja (0-100)</label>
                        <input type="number" name="rating" value="100" min="0" max="100" required class="w-full border-2 border-gray-100 focus:border-sipega-orange focus:ring-0 rounded-2xl p-4 text-2xl font-black text-center" placeholder="Masukan Nilai...">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-3 ml-2">Masukan / Feedback Pimpinan</label>
                        <textarea name="feedback" rows="3" class="w-full border-2 border-gray-100 focus:border-sipega-orange focus:ring-0 rounded-2xl p-4 text-sm font-medium" placeholder="Tulis catatan evaluasi di sini..."></textarea>
                    </div>
                </div>

                <div class="mt-10 flex gap-4">
                    <button type="button" onclick="closeEvalModal()" class="flex-1 bg-gray-100 text-gray-500 font-black py-4 rounded-2xl hover:bg-gray-200 transition">BATAL</button>
                    <button type="submit" class="flex-2 bg-sipega-navy text-white font-black py-4 px-10 rounded-2xl shadow-xl hover:bg-black transition">SIMPAN PENILAIAN 🏁</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEvalModal(id, name) {
            document.getElementById('evalUserName').innerText = name;
            document.getElementById('evalForm').action = `/leader/agenda/${id}/evaluate`;
            document.getElementById('evalModal').classList.remove('hidden');
        }
        function closeEvalModal() {
            document.getElementById('evalModal').classList.add('hidden');
        }

        // Bulk Selection Logic
        const checkAll = document.getElementById('checkAll');
        const checkboxes = document.querySelectorAll('.agenda-checkbox');
        const bulkControls = document.getElementById('bulkControls');

        checkAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = checkAll.checked);
            toggleBulkControls();
        });

        checkboxes.forEach(cb => {
            cb.addEventListener('change', toggleBulkControls);
        });

        function toggleBulkControls() {
            const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
            if (anyChecked) {
                bulkControls.classList.remove('hidden');
            } else {
                bulkControls.classList.add('hidden');
            }
        }
    </script>
</x-app-layout>
