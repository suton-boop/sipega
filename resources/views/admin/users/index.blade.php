<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center whitespace-nowrap" x-data="{ open: false }">
            <div>
                <h2 class="font-extrabold text-2xl text-sipega-navy leading-none tracking-tighter uppercase">
                    Kontrol Hak Akses
                </h2>
                <p class="text-[10px] font-bold text-sipega-orange uppercase tracking-[0.3em] mt-1">SIPEGA RBAC &bull; DEVICE BINDING</p>
            </div>
            
            <div class="flex items-center gap-3">
                <!-- Tombol Tambah Pegawai Manual -->
                <button @click="open = true" class="bg-sipega-navy hover:bg-black text-white font-black py-2.5 px-6 rounded-2xl transition shadow text-[10px] uppercase tracking-widest flex items-center gap-2">
                    <span class="text-lg leading-none">+</span> Tambah Pegawai
                </button>
                <a href="{{ route('dashboard') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-400 hover:text-gray-800 font-black py-2.5 px-6 rounded-2xl transition shadow text-[10px] uppercase tracking-widest border border-gray-200">Kembali ke Dasbor</a>
            </div>

            <!-- Modal Form Tambah User -->
            <div x-show="open" 
                 class="fixed inset-0 z-50 overflow-y-auto" 
                 x-cloak
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 transition-opacity bg-sipega-navy/80 backdrop-blur-sm" @click="open = false"></div>

                    <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-[3rem] shadow-2xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="px-8 pt-10 pb-12 bg-white">
                            <h3 class="text-3xl font-black text-sipega-navy mb-2 tracking-tighter uppercase">Pendaftaran Pegawai</h3>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-8">Input Data Pegawai Secara Manual</p>

                            <form action="{{ route('users.store') }}" method="POST" class="space-y-6">
                                @csrf
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Nama Lengkap</label>
                                    <input type="text" name="name" required class="w-full bg-gray-50 border-none rounded-2xl p-4 text-sm font-bold focus:ring-2 focus:ring-sipega-navy">
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Alamat Email</label>
                                        <input type="email" name="email" required class="w-full bg-gray-50 border-none rounded-2xl p-4 text-sm font-bold focus:ring-2 focus:ring-sipega-navy">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Nomor Induk Pegawai (NIP)</label>
                                        <input type="text" name="nip" required class="w-full bg-gray-50 border-none rounded-2xl p-4 text-sm font-bold focus:ring-2 focus:ring-sipega-navy">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Hak Akses (Role)</label>
                                    <select name="role" required class="w-full bg-gray-50 border-none rounded-2xl p-4 text-sm font-bold focus:ring-2 focus:ring-sipega-navy uppercase tracking-widest">
                                        <option value="Pegawai">Pegawai</option>
                                        <option value="Operator">Operator</option>
                                        <option value="Kasubag">Kasubag</option>
                                        <option value="Pimpinan">Pimpinan</option>
                                        <option value="Admin">Admin</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Password Default</label>
                                    <input type="password" name="password" required value="sipega123" class="w-full bg-gray-50 border-none rounded-2xl p-4 text-sm font-bold focus:ring-2 focus:ring-sipega-navy">
                                    <p class="text-[9px] text-gray-400 mt-2 font-bold italic">*Password ini akan digunakan pegawai untuk login pertama kali</p>
                                </div>

                                <div class="pt-4 flex gap-3">
                                    <button type="button" @click="open = false" class="flex-1 bg-gray-100 text-gray-400 font-extrabold py-5 rounded-3xl uppercase tracking-widest text-xs hover:bg-gray-200 transition">Batal</button>
                                    <button type="submit" class="flex-2 bg-sipega-orange text-white font-extrabold py-5 px-10 rounded-3xl uppercase tracking-widest text-xs shadow-xl shadow-orange-500/20 hover:bg-orange-600 transition">Daftarkan 🚀</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="bg-green-50 text-green-700 p-6 rounded-3xl mb-10 font-bold border border-green-100 flex items-center gap-3 shadow-sm">
                    <span class="bg-green-500 text-white w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-black italic">!</span> {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 text-red-700 p-6 rounded-3xl mb-10 font-bold border border-red-100 flex items-center gap-3 shadow-sm">
                    <span class="bg-red-500 text-white w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-black italic">!</span> {{ session('error') }}
                </div>
            @endif

            <!-- 1. EXCEL OPERATIONS (Import & Template) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
                <div class="bg-white p-8 rounded-[2.5rem] shadow-xl border border-gray-100">
                    <h3 class="text-xl font-black text-sipega-navy mb-4 flex items-center gap-3">📥 Import Data Pegawai</h3>
                    <form action="{{ route('users.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="flex flex-col gap-4">
                            <input type="file" name="file" class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-[10px] file:font-black file:uppercase file:bg-gray-100 file:text-sipega-navy hover:file:bg-gray-200 cursor-pointer" required>
                            <button type="submit" class="bg-sipega-navy text-white text-[10px] font-black py-4 px-8 rounded-2xl shadow-lg hover:bg-black transition-all hover:-translate-y-1 uppercase tracking-widest">
                                Proses Upload Excel 🚀
                            </button>
                        </div>
                    </form>
                </div>
                <div class="bg-sipega-orange p-8 rounded-[2.5rem] shadow-xl text-white flex flex-col justify-center">
                    <h3 class="text-xl font-black mb-2 flex items-center gap-3">📋 Template Excel</h3>
                    <p class="text-xs font-bold text-white/80 mb-6">Gunakan template ini agar format data (Kolom Nama, Email, NIP, dsb) sesuai dengan sistem SIPEGA.</p>
                    <a href="{{ route('users.template') }}" class="inline-block text-center bg-white text-sipega-orange text-[10px] font-black py-4 px-8 rounded-2xl shadow-lg hover:bg-gray-50 transition-all hover:-translate-y-1 uppercase tracking-widest">
                        Download Template CSV ⬇️
                    </a>
                </div>
            </div>

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
