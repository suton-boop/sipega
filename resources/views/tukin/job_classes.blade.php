<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-sipega-navy leading-tight font-sans tracking-wide">
            {{ __('Konfigurasi Kelas Jabatan (Tukin)') }} ⚙️
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <div class="bg-white p-10 rounded-[3rem] shadow-2xl border border-gray-100 flex flex-col md:flex-row justify-between items-center gap-10">
                <div class="md:w-1/2">
                    <h3 class="text-3xl font-black mb-1 text-sipega-navy">Atur Besaran Tukin</h3>
                    <p class="text-gray-400 font-bold text-sm tracking-tight mb-6">Tambah daftar kelas jabatan agar estimasi Tukin otomatis dihitung secara akurat berdasarkan grade jabatan.</p>
                </div>
                <form action="{{ route('tukin.classes.store') }}" method="POST" class="w-full md:w-1/2 bg-gray-50 p-6 rounded-3xl border border-gray-200">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="text-[10px] font-black uppercase text-gray-500 tracking-widest pl-2">Nama Kelas Jabatan</label>
                            <input type="text" name="class_name" placeholder="Contoh: Kelas 10" class="block w-full mt-1 border-gray-100 focus:border-sipega-orange focus:ring-sipega-orange rounded-2xl shadow-inner font-bold text-sm" required>
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase text-gray-500 tracking-widest pl-2">Besaran Dasar (Base Amount)</label>
                            <input type="number" name="base_amount" placeholder="Contoh: 5900000" class="block w-full mt-1 border-gray-100 focus:border-sipega-orange focus:ring-sipega-orange rounded-2xl shadow-inner font-bold text-sm" required>
                        </div>
                        <button type="submit" class="w-full bg-sipega-orange hover:bg-orange-600 text-white font-black py-4 rounded-2xl shadow-xl transition transform active:scale-95 uppercase tracking-widest text-xs">
                            TAMBAH KELAS ➕
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-2xl sm:rounded-[3rem] border border-gray-100">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-sipega-navy text-white text-[10px] font-black uppercase tracking-widest">
                            <th class="px-8 py-5">Kelas Jabatan</th>
                            <th class="px-8 py-5 text-right">Besaran Dasar Tukin</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($jobClasses as $jc)
                            <tr class="hover:bg-gray-50">
                                <td class="px-8 py-5 font-black text-sipega-navy">{{ $jc->class_name }}</td>
                                <td class="px-8 py-5 text-right font-black text-emerald-600 italic">Rp {{ number_format($jc->base_amount, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-8 py-10 text-center text-gray-400 font-bold italic">Belum ada kelas jabatan yang dikonfigurasi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>
