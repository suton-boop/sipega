<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-sipega-navy leading-tight">
            {{ __('⚙️ Pengaturan Hak Akses (RBAC)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Header Branding SIPEGA -->
            <div class="bg-sipega-navy rounded-t-3xl p-8 flex items-center justify-between shadow-lg">
                <div class="flex items-center gap-4">
                    <div class="bg-white/10 p-4 rounded-2xl backdrop-blur-md">
                        <svg class="w-12 h-12 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04M12 2.944a11.955 11.955 0 01-8.618 3.04M12 2.944v17.056c-3.33 0-6.355-1.122-8.618-3.04M12 2.944c3.33 0 6.355 1.122 8.618 3.04M12 20a11.955 11.955 0 01-8.618-3.04"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-3xl font-extrabold text-white">SIPEGA Matrix Access</h3>
                        <p class="text-gray-300">Konfigurasi dinamis hak akses berdasarkan peran organisasi.</p>
                    </div>
                </div>
                <div>
                     <span class="bg-orange-500 text-white px-4 py-2 rounded-full text-xs font-black uppercase tracking-widest shadow-lg animate-pulse">
                        Admin Mode
                    </span>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-2xl sm:rounded-b-3xl border-x border-b border-gray-100">
                <div class="p-8">
                    
                    @if(session('success'))
                        <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded-lg flex items-center gap-3">
                            <span class="text-2xl">✅</span>
                            <span class="font-bold">{{ session('success') }}</span>
                        </div>
                    @endif

                    <form action="{{ route('rbac.sync') }}" method="POST">
                        @csrf
                        
                        <div class="overflow-x-auto rounded-2xl border border-gray-100 shadow-sm mb-8">
                            <table class="w-full text-left">
                                <thead class="bg-gray-50/50">
                                    <tr>
                                        <th class="p-6 text-sipega-navy font-black tracking-wider uppercase text-sm border-b border-gray-100">Daftar Fitur (Permissions)</th>
                                        @foreach($roles as $role)
                                        <th class="p-6 text-center border-b border-gray-100">
                                            <div class="bg-sipega-navy/5 p-3 rounded-xl">
                                                <div class="text-sipega-navy font-bold text-base">{{ $role->name }}</div>
                                                <div class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">Role Level</div>
                                            </div>
                                        </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @foreach($permissions as $permission)
                                    <tr class="hover:bg-orange-50/30 transition-colors group">
                                        <td class="p-6">
                                            <div class="flex items-center gap-3">
                                                <div class="w-2 h-8 bg-orange-400 rounded-full opacity-0 group-hover:opacity-100 transition"></div>
                                                <div>
                                                    <div class="font-extrabold text-sipega-navy capitalize">{{ str_replace('_', ' ', $permission->name) }}</div>
                                                    <div class="text-xs text-gray-400 font-medium">Izin sistem untuk modul {{ $permission->name }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        @foreach($roles as $role)
                                        <td class="p-6 text-center">
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="permissions[{{ $role->id }}][]" value="{{ $permission->name }}" 
                                                    class="sr-only peer" 
                                                    {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                                                <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-orange-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-orange-500 shadow-inner"></div>
                                            </label>
                                        </td>
                                        @endforeach
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="flex justify-between items-center bg-gray-50 p-6 rounded-2xl border border-gray-100">
                            <div class="text-sm text-gray-500 max-w-lg italic">
                                *Setiap perubahan akan langsung menyinkronkan hak akses untuk semua akun yang menggunakan Role terkait. Harap berhati-hati dalam mengubah izin <strong>manage_rbac</strong>.
                            </div>
                            <button type="submit" class="bg-sipega-navy text-white px-10 py-4 rounded-full font-black text-sm tracking-widest hover:bg-[#002244] hover:shadow-2xl transition-all flex items-center gap-3 active:scale-95 shadow-lg group">
                                <svg class="w-5 h-5 text-orange-400 group-hover:rotate-12 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                </svg>
                                UPDATE SURAT IZIN (PERMISSIONS)
                            </button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>
