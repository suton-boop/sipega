<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-sipega-navy leading-tight">
            {{ __('🎁 SIPEGA Reward Center - Recognition & Appreciation') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-12">
            
            <!-- 🏆 HERO: WALL OF FAME (Top 3) -->
            <div class="relative">
                <div class="absolute inset-0 bg-gradient-to-r from-sipega-navy to-blue-900 rounded-[3rem] shadow-2xl skew-y-1"></div>
                <div class="relative p-12 text-white">
                    <div class="text-center mb-12">
                        <h3 class="text-5xl font-black mb-2 tracking-tighter">🌟 WALL OF FAME 🌟</h3>
                        <p class="text-orange-400 font-extrabold uppercase tracking-[0.3em] text-sm italic">Pegawai Teladan BPMP Kaltim Terpilih</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-end">
                        @foreach($wallOfFame as $index => $user)
                        <div class="bg-white/10 backdrop-blur-lg rounded-[2.5rem] p-8 border border-white/20 text-center relative group hover:bg-white/20 transition-all {{ $index === 0 ? 'scale-110 z-20 shadow-orange-500/20 shadow-2xl border-orange-400' : 'scale-95' }}">
                            <!-- Badge Rank -->
                            <div class="absolute -top-6 left-1/2 -translate-x-1/2 w-12 h-12 rounded-2xl bg-{{ $index === 0 ? 'orange-500' : ($index === 1 ? 'gray-300' : 'orange-800') }} flex items-center justify-center text-xl font-black shadow-xl">
                                {{ $index + 1 }}
                            </div>
                            
                            <div class="w-24 h-24 rounded-full bg-{{ strtolower($user->performance_color) }}-100 mx-auto mb-6 flex items-center justify-center border-4 border-white shadow-lg overflow-hidden">
                                <span class="text-3xl font-black text-{{ strtolower($user->performance_color) }}-700">{{ substr($user->name, 0, 1) }}</span>
                            </div>

                            <h4 class="text-xl font-black mb-1 leading-tight">{{ $user->name }}</h4>
                            <p class="text-[10px] font-bold text-orange-400 uppercase tracking-widest mb-4">{{ $user->role }}</p>

                            <div class="flex justify-between items-center bg-black/20 rounded-2xl p-4 border border-white/5">
                                <div class="text-left">
                                    <p class="text-[8px] font-black opacity-60 uppercase">Performance</p>
                                    <p class="font-black text-lg text-blue-300">{{ number_format($user->performance_score, 1) }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[8px] font-black opacity-60 uppercase">Appreciation</p>
                                    <p class="font-black text-lg text-orange-400">{{ $user->received_votes_count }} 🗳️</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                <!-- 🗳️ VOTING PANEL -->
                <div class="bg-white rounded-[2.5rem] shadow-2xl p-10 border-t-8 border-sipega-orange flex flex-col justify-between">
                    <div>
                        <h3 class="text-3xl font-black text-sipega-navy mb-2">🗳️ Dukung Rekan Anda</h3>
                        <p class="text-gray-500 text-sm mb-8 italic">Bantu kami menemukan bintang SIPEGA bulan ini melalui Peer-to-Peer recognition.</p>

                        @if(session('success'))
                            <div class="bg-emerald-50 text-emerald-700 p-4 rounded-2xl mb-6 font-bold flex items-center gap-3 border border-emerald-100 italic text-sm">
                                <span>🚀</span> {{ session('success') }}
                            </div>
                        @endif

                        @if($myVote)
                            <div class="bg-sipega-navy p-8 rounded-[2rem] text-white shadow-xl relative overflow-hidden group">
                                <div class="absolute -right-4 -bottom-4 opacity-10 group-hover:scale-110 transition">🗳️</div>
                                <p class="text-xs font-black text-orange-400 uppercase tracking-widest mb-4">Suara Anda Bulan Ini</p>
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-sipega-navy font-black text-xl">
                                        {{ substr($myVote->target->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <h4 class="font-black text-lg">{{ $myVote->target->name }}</h4>
                                        <p class="text-[10px] opacity-60">"{{ $myVote->comment ?? 'Pilihan Terbaik' }}"</p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <form action="{{ route('reward.vote') }}" method="POST" class="space-y-6">
                                @csrf
                                <div>
                                    <label class="block text-sm font-black text-sipega-navy uppercase tracking-widest mb-2">Siapa Inspirasi Anda?</label>
                                    <select name="target_id" required class="w-full rounded-2xl border-gray-200 focus:ring-sipega-orange p-4 text-sm font-bold text-gray-700">
                                        <option value="">Pilih Rekan Kerja...</option>
                                        @foreach($allPegawai as $p)
                                            <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->performance_color }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-black text-sipega-navy uppercase tracking-widest mb-2">Pesan Singkat (Opsional)</label>
                                    <input type="text" name="comment" maxlength="100" class="w-full rounded-2xl border-gray-200 focus:ring-sipega-orange p-4 text-sm" placeholder="Contoh: Sangat membantu tim, disiplin tinggi...">
                                </div>
                                <button type="submit" class="w-full bg-sipega-orange hover:bg-orange-600 text-white font-black py-4 rounded-full shadow-lg transition-all active:scale-95 flex items-center justify-center gap-3">
                                    KIRIM DUKUNGAN 🚀
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <!-- 🐌 WALL OF SHAME (Perlu Pembinaan) -->
                <div class="bg-gray-900 rounded-[2.5rem] shadow-2xl p-10 text-white relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-red-600/20 rounded-full -mr-10 -mt-10 blur-2xl animate-pulse"></div>
                    <h3 class="text-3xl font-black mb-2 text-red-500 tracking-tighter">🐌 Monitor Pembinaan</h3>
                    <p class="text-gray-500 text-xs font-bold uppercase tracking-widest mb-8">Daftar Pegawai Performa Merah (Peringatan)</p>
                    
                    <div class="space-y-4">
                        @forelse($wallOfShame as $user)
                        <div class="flex items-center justify-between p-5 bg-white/5 border border-white/5 rounded-3xl group hover:bg-white/10 transition cursor-not-allowed">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-2xl bg-gray-800 flex items-center justify-center text-red-500 font-black border border-red-500/30">!</div>
                                <div>
                                    <h4 class="font-bold text-sm">{{ $user->name }}</h4>
                                    <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">Skor: {{ $user->performance_score }}</p>
                                </div>
                            </div>
                            <span class="bg-red-500/20 text-red-400 text-[10px] px-3 py-1 rounded-full font-black uppercase tracking-tighter">Tunda SK ❌</span>
                        </div>
                        @empty
                        <div class="text-center py-12 border-2 border-dashed border-white/5 rounded-3xl">
                            <p class="text-gray-500 italic text-sm italic font-medium">Luar Biasa! Tidak ada pegawai di zona Merah bulan ini. 🎉</p>
                        </div>
                        @endforelse
                    </div>

                    <div class="mt-8 p-6 bg-red-500/10 rounded-2xl border border-red-500/20">
                        <p class="text-[10px] font-bold text-red-400 leading-relaxed italic">
                            *Setiap pegawai di panel ini akan secara otomatis diblokir dari daftar seleksi penugasan kolektif (ST) sampai skor pulih ke status Kuning/Hijau.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
