<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-sipega-navy leading-tight italic uppercase tracking-wider">
                📄 Kelola Dokumen ST & SK
            </h2>
            @if(in_array(auth()->user()->role, ['Admin', 'Pimpinan', 'Kasubag', 'Operator', 'Sekpri']))
            <a href="{{ route('letters.create') }}" class="bg-sipega-orange hover:bg-orange-600 text-white font-black py-3 px-6 rounded-2xl shadow-xl shadow-orange-200 transition-all uppercase tracking-widest text-[11px] flex items-center gap-2">
                <span>➕</span> Buat Baru
            </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <p class="font-bold">Berhasil</p>
                <p>{{ session('success') }}</p>
            </div>
            @endif

            <div class="bg-white overflow-hidden shadow-2xl sm:rounded-[2rem] border-t-[8px] border-sipega-navy relative">
                <!-- Soft Background Accents -->
                <div class="absolute top-0 right-0 w-64 h-64 bg-blue-50/50 rounded-full blur-3xl -z-10 translate-x-1/2 -translate-y-1/2"></div>
                <div class="absolute bottom-0 left-0 w-96 h-96 bg-orange-50/50 rounded-full blur-3xl -z-10 -translate-x-1/2 translate-y-1/2"></div>
                
                <div class="p-8 pb-10">
                    <div class="flex items-center gap-4 mb-8 border-b pb-6">
                        <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center text-3xl">🗂️</div>
                        <div>
                            <h3 class="text-2xl font-black text-sipega-navy leading-none italic uppercase">Arsip Perizinan</h3>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">Daftar Surat Tugas & Keputusan (Kolektif/Satuan)</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto relative z-10">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b-2 border-gray-100">
                                    <th class="p-4 text-[10px] font-black uppercase text-gray-400 tracking-widest bg-gray-50 rounded-tl-xl rounded-bl-xl">Tipe</th>
                                    <th class="p-4 text-[10px] font-black uppercase text-gray-400 tracking-widest bg-gray-50">Nomor Registrasi</th>
                                    <th class="p-4 text-[10px] font-black uppercase text-gray-400 tracking-widest bg-gray-50">Perihal</th>
                                    <th class="p-4 text-[10px] font-black uppercase text-gray-400 tracking-widest bg-gray-50">Tanggal</th>
                                    <th class="p-4 text-[10px] font-black uppercase text-gray-400 tracking-widest bg-gray-50 text-center">Peserta</th>
                                    <th class="p-4 text-[10px] font-black uppercase text-gray-400 tracking-widest bg-gray-50 text-center">Status</th>
                                    <th class="p-4 text-[10px] font-black uppercase text-gray-400 tracking-widest bg-gray-50 rounded-tr-xl rounded-br-xl text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($letters as $letter)
                                <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                                    <td class="p-4">
                                        <span class="inline-block px-3 py-1 rounded-lg text-xs font-black {{ $letter->type == 'ST' ? 'bg-orange-100 text-orange-600' : 'bg-blue-100 text-blue-600' }}">
                                            {{ $letter->type }}
                                        </span>
                                    </td>
                                    <td class="p-4 font-bold text-sm text-gray-700">
                                        {{ $letter->number ?? 'DRAFT-XXXX' }}
                                    </td>
                                    <td class="p-4">
                                        <p class="font-bold text-sm text-sipega-navy">{{ $letter->title }}</p>
                                        <p class="text-[10px] text-gray-400 mt-1 uppercase tracking-wider font-semibold">📍 {{ $letter->location ?? 'Tidak ada lokasi spesifik' }}</p>
                                    </td>
                                    <td class="p-4 text-xs font-bold text-gray-600">
                                        {{ $letter->date_start ? \Carbon\Carbon::parse($letter->date_start)->format('d/m/Y') : '-' }} 
                                        s.d 
                                        {{ $letter->date_end ? \Carbon\Carbon::parse($letter->date_end)->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="p-4 text-center">
                                        <div class="flex items-center justify-center -space-x-3">
                                            @foreach($letter->users->take(3) as $user)
                                                <div class="w-8 h-8 rounded-full bg-sipega-navy text-white flex items-center justify-center text-[10px] font-black border-2 border-white shadow-sm" title="{{ $user->name }}">
                                                    {{ substr($user->name, 0, 2) }}
                                                </div>
                                            @endforeach
                                            @if($letter->users->count() > 3)
                                                <div class="w-8 h-8 rounded-full bg-gray-200 text-gray-600 flex items-center justify-center text-[10px] font-black border-2 border-white shadow-sm">
                                                    +{{ $letter->users->count() - 3 }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="p-4 text-center">
                                        @if($letter->status === 'Approved')
                                            <span class="text-xs font-black bg-green-100 text-green-600 px-3 py-1 rounded-lg">AKTIF</span>
                                        @elseif($letter->status === 'Pending')
                                            <span class="text-xs font-black bg-yellow-100 text-yellow-600 px-3 py-1 rounded-lg border-2 border-yellow-200 border-dashed">PROSES</span>
                                        @else
                                            <span class="text-xs font-black bg-red-100 text-red-600 px-3 py-1 rounded-lg">BATAL</span>
                                        @endif
                                    </td>
                                    <td class="p-4 text-center">
                                        <div class="flex flex-wrap items-center justify-center gap-2 max-w-[200px] mx-auto">
                                            @if($letter->status == 'Approved')
                                                <a href="{{ route('letters.pdf_st', $letter->id) }}" target="_blank" class="bg-white border hover:border-red-500 text-red-500 rounded px-2 py-1 text-[10px] font-bold uppercase tracking-widest hover:bg-red-50 transition-all flex items-center shadow-sm" title="Surat Tugas">
                                                    📄 Cetak ST
                                                </a>
                                                <a href="{{ route('letters.pdf_sk', $letter->id) }}" target="_blank" class="bg-white border hover:border-purple-500 text-purple-500 rounded px-2 py-1 text-[10px] font-bold uppercase tracking-widest hover:bg-purple-50 transition-all flex items-center shadow-sm" title="Surat Keputusan (Panitia)">
                                                    📜 Cetak SK
                                                </a>
                                                <button class="bg-white border hover:border-blue-500 text-blue-500 rounded px-2 py-1 text-[10px] font-bold uppercase tracking-widest hover:bg-blue-50 transition-all flex items-center shadow-sm opacity-60 cursor-not-allowed" title="Upload/Unduh Dokumen KAK" disabled>
                                                    📎 KAK
                                                </button>
                                            @else
                                                @if(auth()->user() && in_array(auth()->user()->role, ['Admin', 'Pimpinan', 'Kasubag']))
                                                    <form action="{{ route('letters.approve', $letter->id) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="bg-white border hover:border-green-500 text-green-500 rounded px-2 py-1 text-[10px] font-bold uppercase tracking-widest hover:bg-green-50 transition-all flex items-center shadow-sm">
                                                            ✅ Setujui
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('letters.reject', $letter->id) }}" method="POST" class="inline" onsubmit="return confirm('Tolak dokumen ini?')">
                                                        @csrf
                                                        <button type="submit" class="bg-white border hover:border-red-500 text-red-500 rounded px-2 py-1 text-[10px] font-bold uppercase tracking-widest hover:bg-red-50 transition-all flex items-center shadow-sm">
                                                            ❌ Tolak
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-[10px] font-bold text-orange-500 uppercase flex items-center gap-1 bg-orange-50 px-2 py-1 rounded-lg border border-orange-100">
                                                        ⏳ Menunggu Pimpinan
                                                    </span>
                                                @endif
                                            @endif
                                            
                                            @if(in_array(auth()->user()->role, ['Admin', 'Pimpinan', 'Kasubag', 'Operator']))
                                            <a href="{{ route('letters.edit', $letter->id) }}" class="bg-white border hover:border-blue-500 text-blue-500 rounded px-2 py-1 text-[10px] font-bold uppercase tracking-widest hover:bg-blue-50 transition-all flex items-center shadow-sm" title="Edit">
                                                ✏️
                                            </a>
                                            <form action="{{ route('letters.destroy', $letter->id) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus dokumen ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="bg-white border hover:border-gray-800 text-gray-800 rounded px-2 py-1 text-[10px] font-bold uppercase tracking-widest hover:bg-gray-100 transition-all flex items-center shadow-sm" title="Hapus">
                                                    🗑️
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="p-10 text-center">
                                        <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center text-4xl mx-auto mb-4 grayscale opacity-50">📂</div>
                                        <p class="font-bold text-gray-500 text-xl">Arsip Kosong</p>
                                        <p class="text-gray-400 text-xs mt-2 uppercase tracking-widest font-semibold">Belum ada Surat Tugas atau Surat Keputusan yang terdaftar.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</x-app-layout>
