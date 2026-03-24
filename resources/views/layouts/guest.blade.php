<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SIPEGA BPMP') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,900&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-slate-50 min-h-screen flex flex-col justify-center items-center py-6 sm:py-12 relative overflow-hidden">
        
        <!-- Subtle backdrops -->
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0 pointer-events-none">
            <div class="absolute -top-[20%] -right-[10%] w-[50%] h-[50%] rounded-full bg-blue-100 blur-[120px]"></div>
            <div class="absolute bottom-[0%] -left-[10%] w-[40%] h-[40%] rounded-full bg-orange-100 blur-[100px]"></div>
        </div>

        <!-- Master Container -->
        <div class="relative z-10 w-full max-w-6xl px-6 lg:px-12 flex flex-col md:flex-row items-center justify-center md:justify-around gap-12 sm:gap-24">
            
            <!-- Left Side: Branding Visuals -->
            <div class="text-center md:text-left flex flex-col items-center md:items-start text-sipega-navy max-w-lg mb-8 md:mb-0">
                <div class="mb-8 flex gap-4 items-center">
                    <img src="{{ asset('images/Logo Kemendikdasmen BPMP Kaltim.png') }}" class="h-12 sm:h-16 object-contain drop-shadow-sm" alt="Kemendikdasmen BPMP Kaltim" />
                </div>
                
                <img src="{{ asset('images/logo Sipega.png') }}" class="w-32 sm:w-44 h-auto drop-shadow-2xl mb-6 transition-transform hover:scale-105" alt="Icon S-Compass SIPEGA" />
                
                <img src="{{ asset('images/jenaman SIPEGA.png') }}" class="h-10 sm:h-12 w-auto mb-2" alt="SIPEGA Text" />
                
                <p class="text-xl sm:text-2xl text-sipega-orange font-bold mb-5 italic tracking-wide">Navigasi Performa, Akurasi Penugasan.</p>
                <div class="h-1.5 w-24 bg-sipega-navy rounded-full mb-6"></div>
                <p class="text-gray-600 font-medium leading-relaxed">
                    Sistem Manajemen Kinerja BPMP Provinsi Kalimantan Timur. Didesain untuk kemudahan memonitor agenda dan pendataan tugas yang akurat.
                </p>
                
                <div class="mt-10">
                    <img src="{{ asset('images/ramah bermutu.png') }}" class="h-10 sm:h-14 opacity-95 hover:opacity-100 transition-opacity drop-shadow-sm" alt="Ramah Bermutu" />
                </div>
            </div>

            <!-- Right Side: Auth Card -->
            <div class="w-full md:w-[450px]">
                <div class="bg-white px-8 py-10 sm:px-10 sm:py-12 shadow-2xl rounded-[2.5rem] border-t-[10px] border-sipega-navy">
                    <div class="mb-10 text-center">
                        <h2 class="text-3xl font-extrabold text-sipega-navy">Log Masuk</h2>
                        <p class="text-gray-500 font-medium mt-2">Gunakan Kredensial SSO Anda</p>
                    </div>
                    {{ $slot }}
                </div>
                <!-- Privacy Ticker -->
                <p class="text-center text-xs text-gray-500 mt-8 font-semibold tracking-wide">
                    &copy; 2026 BPMP Provinsi Kalimantan Timur. Hak Cipta Dilindungi.
                </p>
            </div>

        </div>
    </body>
</html>
