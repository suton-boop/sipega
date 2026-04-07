<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-sipega-navy leading-tight">
            {{ __('📸 QR Code Presensi Rapat SIPEGA') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-2xl sm:rounded-[3rem] border-t-[12px] border-sipega-navy p-12 text-center">
                
                <h3 class="text-4xl font-black text-sipega-navy mb-2">{{ $meeting->title }}</h3>
                <p class="text-gray-400 font-bold uppercase tracking-widest text-sm mb-10">BPMP KALTIM - DIGITAL ATTENDANCE</p>

                <div class="flex justify-center mb-10 p-6 bg-gray-50 rounded-[3rem] border-4 border-dashed border-gray-200 inline-block mx-auto relative group">
                    <div class="bg-white p-8 rounded-[2rem] shadow-xl group-hover:scale-105 transition-transform duration-500">
                        {!! $qrCode !!}
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-left max-w-lg mx-auto mb-10">
                    <div class="bg-sipega-navy/5 p-6 rounded-3xl border border-sipega-navy/10">
                        <p class="text-[10px] font-black text-sipega-navy uppercase tracking-widest opacity-60">📍 Lokasi GPS</p>
                        <p class="font-bold text-gray-800">{{ $meeting->gps_lat }}, {{ $meeting->gps_lng }}</p>
                    </div>
                    <div class="bg-orange-50 p-6 rounded-3xl border border-orange-200">
                        <p class="text-[10px] font-black text-sipega-orange uppercase tracking-widest opacity-60">📏 Geofence Radius</p>
                        <p class="font-bold text-gray-800">{{ $meeting->geofence_radius ?? 50 }} Meter</p>
                    </div>
                </div>

                <div class="bg-sipega-navy p-6 rounded-3xl text-white flex items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-white/10 rounded-2xl flex items-center justify-center text-2xl animate-spin-slow">🔄</div>
                        <div class="text-left">
                            <p class="text-xs font-bold opacity-80 uppercase tracking-widest">Dynamic Token Active</p>
                            <p class="text-lg font-black tracking-tighter">{{ substr($meeting->current_qr_token, 0, 16) }}...</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-black opacity-60 italic">Scan via Android SIPEGA</p>
                        <p class="text-xs font-black text-orange-400 uppercase tracking-widest">Anti-Cheat Mode ON</p>
                    </div>
                </div>

                <p class="mt-8 text-gray-400 text-xs italic">Sistem ini memverifikasi posisi koordinat peserta secara real-time. Melakukan absensi di luar radius 50m akan ditolak otomatis.</p>

            </div>
        </div>
    </div>

    <style>
        @keyframes spin-slow {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .animate-spin-slow {
            animation: spin-slow 8s linear infinite;
        }
    </style>
</x-app-layout>
