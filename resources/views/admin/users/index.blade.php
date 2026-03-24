<x-app-layout>
    <div class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="mb-8 flex justify-between items-center bg-white p-6 rounded-3xl shadow-lg border-l-8 border-sipega-navy">
                <div>
                    <h2 class="text-3xl font-extrabold text-sipega-navy tracking-tight">User Management (RBAC)</h2>
                    <p class="text-gray-500 mt-1 font-medium">Platform kendali Role, Binding Device, Google Drive, dan Status Aktif Pegawai.</p>
                </div>
                <a href="{{ route('dashboard') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-6 rounded-full transition shadow text-sm">Kembali ke Dasbor</a>
            </div>

            @if(session('success'))
                <div class="bg-green-100 text-green-800 p-4 rounded-xl mb-6 font-bold shadow-sm border border-green-300 flex items-center gap-3">
                    <span class="text-xl">✔️</span> {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-2xl sm:rounded-3xl p-6 md:p-8">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-100/70 border-b border-gray-200">
                                <th class="py-4 px-4 font-extrabold text-sipega-navy rounded-tl-xl truncate">Pegawai / Email</th>
                                <th class="py-4 px-4 font-extrabold text-sipega-navy">NIP</th>
                                <th class="py-4 px-4 font-extrabold text-sipega-navy text-center">Device Terkunci</th>
                                <th class="py-4 px-4 font-extrabold text-sipega-navy whitespace-nowrap">Status Aktif</th>
                                <th class="py-4 px-4 font-extrabold text-sipega-navy text-center rounded-tr-xl">Aksi (Kendali)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($users as $u)
                                <tr class="hover:bg-blue-50/50 transition duration-150">
                                    <td class="py-4 px-4 align-top">
                                        <div class="font-bold text-gray-800">{{ $u->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $u->email }}</div>
                                        <div class="mt-2 text-xs font-semibold px-2 py-1 bg-blue-100 text-blue-800 inline-block rounded-md"><span class="opacity-60">Role:</span> {{ $u->role }}</div>
                                    </td>
                                    <td class="py-4 px-4 text-sm font-medium text-gray-600 align-top">
                                        {{ $u->nip ?? '-' }}
                                    </td>
                                    <td class="py-4 px-4 text-center align-top">
                                        @if($u->device_id)
                                            <span class="inline-flex items-center justify-center p-2 bg-red-100 text-red-700 rounded-full" title="Terkunci di HP tertentu">📱 Terkunci</span>
                                        @else
                                            <span class="inline-flex items-center justify-center p-2 bg-green-100 text-green-700 rounded-full">🔓 Bebas</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 align-top">
                                        @if($u->is_active)
                                            <div class="h-4 w-4 bg-green-500 rounded-full shadow-[0_0_10px_rgba(34,197,94,0.6)]" title="Aktif"></div>
                                        @else
                                            <div class="h-4 w-4 bg-gray-400 rounded-full" title="Non-Aktif (Pensiun/Mutasi)"></div>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 align-top">
                                        <form action="{{ route('users.update', $u->id) }}" method="POST" class="bg-gray-50 p-4 rounded-2xl border border-gray-200 grid gap-4 w-[350px]">
                                            @csrf
                                            @method('PUT')
                                            
                                            <!-- Drive -->
                                            <div>
                                                <label class="text-xs font-bold text-gray-500 block mb-1">Link Google Drive Personal</label>
                                                <input type="url" name="drive_folder_url" value="{{ $u->drive_folder_url }}" class="w-full h-8 text-sm rounded-lg border-gray-300 focus:ring-sipega-navy focus:border-sipega-navy" placeholder="https://drive.google.com/...">
                                            </div>

                                            <div class="grid grid-cols-2 gap-2">
                                                <!-- Role Dropdown -->
                                                <div>
                                                    <label class="text-xs font-bold text-gray-500 block mb-1">Tukar Peran (Role)</label>
                                                    <select name="role" class="w-full h-8 px-2 py-1 text-sm rounded-lg border-gray-300 focus:ring-sipega-navy">
                                                        <option value="Admin" {{ $u->role == 'Admin' ? 'selected' : '' }}>Admin</option>
                                                        <option value="Pimpinan" {{ $u->role == 'Pimpinan' ? 'selected' : '' }}>Pimpinan</option>
                                                        <option value="Kasubag" {{ $u->role == 'Kasubag' ? 'selected' : '' }}>Kasubag</option>
                                                        <option value="Pegawai" {{ $u->role == 'Pegawai' ? 'selected' : '' }}>Pegawai</option>
                                                    </select>
                                                </div>
                                                
                                                <!-- Status Aktif Checkbox -->
                                                <div class="flex flex-col justify-end pb-1 pl-2">
                                                    <label class="flex items-center gap-2 cursor-pointer">
                                                        <input type="checkbox" name="is_active" value="1" {{ $u->is_active ? 'checked' : '' }} class="rounded text-green-600 focus:ring-green-500 w-4 h-4">
                                                        <span class="text-xs font-bold text-gray-700">Status Aktif</span>
                                                    </label>
                                                </div>
                                            </div>

                                            <!-- Reset Buttons -->
                                            <div class="grid grid-cols-2 gap-2 mt-1 border-t border-gray-200 pt-3">
                                                <label class="flex items-center gap-2 cursor-pointer border border-red-200 bg-red-50 p-1.5 rounded-lg hover:bg-red-100">
                                                    <input type="checkbox" name="reset_device" value="1" class="rounded text-red-600 focus:ring-red-500 w-4 h-4">
                                                    <span class="text-[10px] font-bold text-red-800 uppercase">Reset HP</span>
                                                </label>
                                                <label class="flex items-center gap-2 cursor-pointer border border-orange-200 bg-orange-50 p-1.5 rounded-lg hover:bg-orange-100">
                                                    <input type="checkbox" name="reset_password" value="1" class="rounded text-orange-600 focus:ring-orange-500 w-4 h-4">
                                                    <span class="text-[10px] font-bold text-orange-800 uppercase">Reset Pass</span>
                                                </label>
                                            </div>

                                            <!-- Save Button -->
                                            <button type="submit" class="w-full bg-sipega-navy text-white text-xs font-bold py-2 rounded-xl mt-1 shadow hover:-translate-y-0.5 hover:shadow-md transition">TERAPKAN KENDALI</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            
                            @if($users->isEmpty())
                            <tr>
                                <td colspan="5" class="text-center py-8 text-gray-400 font-bold">Tidak ada data pegawai lain.</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
