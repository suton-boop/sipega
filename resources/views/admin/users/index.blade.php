<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center whitespace-nowrap">
            <div>
                <h2 class="font-extrabold text-2xl text-sipega-navy leading-none tracking-tighter uppercase">
                    Kontrol Hak Akses
                </h2>
                <p class="text-[10px] font-bold text-sipega-orange uppercase tracking-[0.3em] mt-1">SIPEGA RBAC &bull; DEVICE BINDING</p>
            </div>
            <a href="{{ route('dashboard') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-400 hover:text-gray-800 font-black py-2.5 px-6 rounded-2xl transition shadow text-[10px] uppercase tracking-widest border border-gray-200">Kembali ke Dasbor</a>
        </div>
    </x-slot>

    <div class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="bg-green-50 text-green-700 p-6 rounded-3xl mb-10 font-bold border border-green-100 flex items-center gap-3 shadow-sm">
                    <span class="bg-green-500 text-white w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-black italic">!</span> {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-2xl rounded-[40px] border border-gray-100 p-2 md:p-6 lg:p-10">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50">
                                <th class="py-6 px-6 font-black text-[10px] text-gray-400 uppercase tracking-widest rounded-tl-3xl">Pegawai Terdaftar</th>
                                <th class="py-6 px-6 font-black text-[10px] text-gray-400 uppercase tracking-widest">Identitas NIP</th>
                                <th class="py-6 px-6 font-black text-[10px] text-gray-400 uppercase tracking-widest text-center">Security Status</th>
                                <th class="py-6 px-6 font-black text-[10px] text-gray-400 uppercase tracking-widest text-center rounded-tr-3xl">Kendali Operasional</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($users as $u)
                                <tr class="hover:bg-gray-50 transition duration-150 group">
                                    <td class="py-8 px-6 align-top">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 bg-sipega-navy flex items-center justify-center rounded-2xl text-white font-black text-lg">
                                                {{ substr($u->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="font-black text-sipega-navy text-lg leading-none mb-1">{{ $u->name }}</div>
                                                <div class="text-xs text-gray-400 font-bold tracking-tight lowercase">{{ $u->email }}</div>
                                                <div class="mt-2 inline-flex items-center gap-2 px-3 py-1 bg-yellow-400/10 text-yellow-700 rounded-lg text-[10px] font-black uppercase tracking-widest">
                                                    {{ $u->role }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-8 px-6 text-sm font-black text-gray-500 align-top tracking-tighter">
                                        {{ $u->nip ?? '-' }}
                                    </td>
                                    <td class="py-8 px-6 text-center align-top">
                                        <div class="flex flex-col items-center gap-4">
                                            @if($u->device_id)
                                                <span class="inline-flex items-center gap-2 px-4 py-1.5 bg-red-100 text-red-700 rounded-full text-[10px] font-black uppercase tracking-widest border border-red-200" title="Terkunci di HP">
                                                    Locked
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-2 px-4 py-1.5 bg-green-100 text-green-700 rounded-full text-[10px] font-black uppercase tracking-widest border border-green-200">
                                                    Unbound
                                                </span>
                                            @endif
                                            
                                            <div class="flex items-center gap-2">
                                                <div class="h-2.5 w-2.5 {{ $u->is_active ? 'bg-green-500 animate-pulse' : 'bg-gray-300' }} rounded-full" title="Aktif"></div>
                                                <span class="text-[10px] font-black uppercase tracking-widest {{ $u->is_active ? 'text-green-600' : 'text-gray-400' }}">
                                                    {{ $u->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-8 px-6 align-top">
                                        <form action="{{ route('users.update', $u->id) }}" method="POST" class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-xl group-hover:border-gray-200 transition-all flex flex-col gap-4">
                                            @csrf
                                            @method('PUT')
                                            
                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest block mb-1 px-1">Tukar Peran</label>
                                                    <select name="role" class="w-full text-xs font-black p-3 bg-gray-50 rounded-xl border-none focus:ring-sipega-navy focus:bg-white transition-all uppercase tracking-widest">
                                                        <option value="Admin" {{ $u->role == 'Admin' ? 'selected' : '' }}>Admin</option>
                                                        <option value="Pimpinan" {{ $u->role == 'Pimpinan' ? 'selected' : '' }}>Pimpinan</option>
                                                        <option value="Kasubag" {{ $u->role == 'Kasubag' ? 'selected' : '' }}>Kasubag</option>
                                                        <option value="Operator" {{ $u->role == 'Operator' ? 'selected' : '' }}>Operator</option>
                                                        <option value="Pegawai" {{ $u->role == 'Pegawai' ? 'selected' : '' }}>Pegawai</option>
                                                    </select>
                                                </div>
                                                <div class="flex items-center justify-center">
                                                     <label class="flex items-center gap-3 cursor-pointer group/toggle">
                                                        <input type="checkbox" name="is_active" value="1" {{ $u->is_active ? 'checked' : '' }} class="rounded-lg text-green-600 focus:ring-green-500 w-6 h-6 border-gray-100 bg-gray-50">
                                                        <span class="text-[10px] font-black uppercase tracking-widest text-gray-400 group-hover/toggle:text-green-600 transition-colors">Aktif</span>
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-2">
                                                <label class="flex-1 flex items-center justify-center gap-2 cursor-pointer bg-red-50 p-2.5 rounded-xl border border-red-50 hover:bg-red-100 transition-colors">
                                                    <input type="checkbox" name="reset_device" value="1" class="rounded text-red-600 focus:ring-red-500 w-5 h-5 border-none">
                                                    <span class="text-[9px] font-black text-red-800 uppercase tracking-tighter leading-none">Reset HP</span>
                                                </label>
                                                <label class="flex-1 flex items-center justify-center gap-2 cursor-pointer bg-orange-50 p-2.5 rounded-xl border border-orange-50 hover:bg-orange-100 transition-colors">
                                                    <input type="checkbox" name="reset_password" value="1" class="rounded text-orange-600 focus:ring-orange-500 w-5 h-5 border-none">
                                                    <span class="text-[9px] font-black text-orange-800 uppercase tracking-tighter leading-none">Reset Pass</span>
                                                </label>
                                            </div>

                                            <button type="submit" class="w-full bg-sipega-navy hover:bg-black text-white text-[10px] font-black py-4 rounded-xl mt-2 shadow-lg transition-all uppercase tracking-widest hover:-translate-y-1">Simpan Kendali</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
