<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SIPEGA - BPMP Kaltim') }}</title>

        <!-- Fonts: Outfit & Montserrat (Official Branding) -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Montserrat:wght@700;800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
             body { 
                font-family: 'Outfit', sans-serif; 
                background-color: #f1f5f9;
                background-image: 
                    radial-gradient(at 0% 0%, hsla(210,100%,98%,1) 0, transparent 50%), 
                    radial-gradient(at 50% 0%, hsla(220,100%,97%,1) 0, transparent 50%), 
                    radial-gradient(at 100% 0%, hsla(210,100%,98%,1) 0, transparent 50%);
                background-attachment: fixed;
             }
             .font-montserrat { font-family: 'Montserrat', sans-serif; }
             .card-premium {
                background: rgba(255, 255, 255, 0.9);
                backdrop-filter: blur(20px);
                box-shadow: 0 40px 100px -20px rgba(0, 30, 60, 0.1);
                border: 1px solid rgba(255, 255, 255, 1);
             }
             .official-divider {
                width: 1.5px;
                height: 40px;
                background-color: #003366;
                opacity: 0.1;
                margin: 0 24px;
             }
             .soft-glow-1 {
                position: absolute;
                top: -10%;
                right: -10%;
                width: 600px;
                height: 600px;
                background: radial-gradient(circle, rgba(0, 51, 102, 0.03) 0%, transparent 70%);
                z-index: 0;
             }
             .soft-glow-2 {
                position: absolute;
                bottom: -10%;
                left: -10%;
                width: 600px;
                height: 600px;
                background: radial-gradient(circle, rgba(255, 140, 0, 0.03) 0%, transparent 70%);
                z-index: 0;
             }
        </style>
    </head>
    <body class="antialiased min-h-screen flex items-center justify-center p-6 relative overflow-hidden">
        
        <!-- Decoration Backgrounds -->
        <div class="soft-glow-1"></div>
        <div class="soft-glow-2"></div>

        <div class="w-full max-w-xl relative z-10">
            <div class="rounded-[4rem] card-premium p-12 lg:p-20 flex flex-col items-center">
                
                <!-- Official Header Branding -->
                <div class="mb-14 text-center w-full flex items-center justify-center">
                    <img src="{{ asset('images/Logo Kemendikdasmen BPMP Kaltim.png') }}" class="h-14 w-auto drop-shadow-sm" alt="Logo Kemendikdasmen">
                    <div class="official-divider"></div>
                    <img src="{{ asset('images/jenaman SIPEGA.png') }}" class="h-10 w-auto opacity-100" alt="Jenaman SIPEGA">
                </div>

                <div class="w-full">
                    {{ $slot }}
                </div>

                <!-- Footer Logos -->
                <div class="mt-14 w-full border-t border-gray-100 pt-12">
                    <div class="flex justify-center items-center gap-10 opacity-70">
                         <img src="{{ asset('images/ramah bermutu.png') }}" class="h-12 w-auto grayscale hover:grayscale-0 transition-all cursor-pointer">
                    </div>
                    
                    <p class="text-center text-[10px] text-gray-400 font-bold uppercase tracking-[0.4em] leading-relaxed mt-10">
                        &copy; 2026 BPMP KALIMANTAN TIMUR &bull; PORTAL INVENTARISASI KINERJA
                    </p>
                </div>
            </div>

            <div class="mt-10 text-center">
                <a href="/" class="text-[10px] font-bold text-gray-400 hover:text-[#003366] transition-colors uppercase tracking-[0.4em] bg-white/50 backdrop-blur-sm py-3 px-10 rounded-full shadow-sm hover:shadow-md border border-white/50">
                    &larr; Beranda Utama
                </a>
            </div>
        </div>
    </body>
</html>
