<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>SIPEGA - Korporat & Institusional</title>
        
        <!-- Premium Typography -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@200;300;400;500;600;700;800;900&family=Montserrat:wght@800;900&display=swap" rel="stylesheet">
        
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            :root {
                --sipega-navy: #001a33;
                --sipega-deep: #000c1a;
                --sipega-orange: #ff8c00;
                --sipega-glow: rgba(255, 140, 0, 0.25);
            }
            body { 
                font-family: 'Outfit', sans-serif; 
                scroll-behavior: smooth; 
                background-color: var(--sipega-deep);
                color: #ffffff;
            }
            .font-montserrat { font-family: 'Montserrat', sans-serif; }
            
            /* Enhanced White Glass Header for Colored Logos */
            .glass-header {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(20px);
                border: 1px solid rgba(255, 255, 255, 1);
                border-radius: 2.5rem;
                margin: 1.5rem auto;
                width: 95%;
                max-width: 1200px;
                box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.4);
            }

            .official-divider {
                width: 1.5px;
                height: 32px;
                background-color: var(--sipega-navy);
                opacity: 0.1;
                margin: 0 24px;
            }

            /* Elite Hero Gradient */
            .hero-elite {
                background: radial-gradient(circle at 70% 30%, #00264d 0%, var(--sipega-deep) 70%);
            }

            .card-elite {
                background: rgba(255, 255, 255, 0.01);
                border: 1px solid rgba(255, 255, 255, 0.03);
                backdrop-filter: blur(10px);
                transition: all 0.5s ease;
            }

            .text-outline {
                -webkit-text-stroke: 1px rgba(255, 255, 255, 0.05);
                color: transparent;
            }

            .btn-glow-animate {
                box-shadow: 0 0 0 0 rgba(255, 140, 0, 0.4);
                animation: pulse-orange 2s infinite;
            }

            @keyframes pulse-orange {
                0% { box-shadow: 0 0 0 0 rgba(255, 140, 0, 0.4); }
                70% { box-shadow: 0 0 0 20px rgba(255, 140, 0, 0); }
                100% { box-shadow: 0 0 0 0 rgba(255, 140, 0, 0); }
            }
        </style>
    </head>
    <body class="antialiased selection:bg-sipega-orange selection:text-white">
        
        <!-- Standardized Colored Logo Header (Responsive All Devices) -->
        <header class="fixed top-0 left-0 right-0 z-[100] px-3 sm:px-6">
            <div class="glass-header py-3 sm:py-4 px-4 sm:px-8 flex justify-between items-center">
                <div class="flex items-center">
                    <img src="{{ asset('images/logo-kemendikdasmen-bpmp-kaltim.png') }}" class="h-8 sm:h-10 w-auto" alt="Kemendikdasmen">
                    <div class="official-divider !mx-3 sm:!mx-6"></div>
                    <img src="{{ asset('images/jenaman-sipega.png') }}" class="h-6 sm:h-8 w-auto" alt="SIPEGA">
                </div>
                
                <nav class="flex items-center gap-3 sm:gap-6 md:gap-8">
                    <a href="#about" class="hidden md:inline-block text-[10px] font-bold uppercase tracking-[0.3em] text-slate-500 hover:text-[#001a33] transition-all">Filosofi</a>
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" 
                            class="inline-flex items-center gap-2 px-4 py-2 sm:px-6 sm:py-2.5 bg-[#001a33] hover:bg-[#002b55] text-white text-[10px] sm:text-xs font-black uppercase tracking-[0.2em] rounded-xl sm:rounded-2xl shadow-xl transition-all transform hover:scale-105 active:scale-95">
                            <span>MASUK</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-sipega-orange" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    @endif
                </nav>
            </div>
        </header>

        <!-- Dynamic Hero Section (Responsive All Devices) -->
        <section class="relative min-h-screen flex flex-col justify-center items-center overflow-hidden hero-elite pt-24 sm:pt-28 md:pt-32 pb-12 sm:pb-16 px-4">
            <div class="absolute inset-0 z-0 opacity-5 pointer-events-none flex items-center justify-center">
                <span class="text-[25vw] font-montserrat font-black uppercase tracking-tighter text-outline">SIPEGA</span>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 relative z-10 text-center">
                <div class="inline-flex items-center gap-2.5 px-4 sm:px-5 py-1 sm:py-1.5 bg-white/5 border border-white/10 rounded-full text-[9px] sm:text-[10px] font-bold uppercase tracking-[0.3em] mb-4 sm:mb-6 md:mb-8 text-sipega-orange">
                    <span class="w-1.5 h-1.5 rounded-full bg-sipega-orange shadow-lg shadow-sipega-orange"></span>
                    INSTITUTIONAL CORE v2.4
                </div>

                <h1 class="text-3xl sm:text-5xl md:text-6xl lg:text-7xl xl:text-8xl font-black font-montserrat tracking-tighter uppercase leading-[0.95] sm:leading-[0.9] mb-4 sm:mb-6 md:mb-8">
                    Akurasi data, <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-sipega-orange to-amber-500 italic">Ekselensi Kinerja.</span>
                </h1>

                <p class="text-xs sm:text-sm md:text-lg lg:text-xl text-white/50 font-light max-w-3xl mx-auto leading-relaxed mb-6 sm:mb-8 md:mb-10 tracking-wide px-2">
                    Membangun standar baru profesionalisme aparatur melalui <br class="hidden sm:inline"> integrasi data kehadiran yang presisi dan transparan.
                </p>

                <div class="flex items-center justify-center">
                    <a href="{{ route('login') }}" 
                        class="btn-glow-animate inline-flex items-center gap-2.5 px-7 py-3 sm:px-10 sm:py-3.5 md:px-12 md:py-4 rounded-full font-black text-xs sm:text-sm uppercase tracking-[0.25em] text-white bg-[#ff8c00] hover:bg-[#ff9d26] transition-all transform hover:scale-105 hover:-translate-y-1 shadow-2xl shadow-orange-500/40">
                        <span>MASUK KE SISTEM</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                </div>
            </div>

            <div class="absolute bottom-6 sm:bottom-10 left-1/2 -translate-x-1/2 opacity-20">
                <div class="w-px h-16 sm:h-24 bg-gradient-to-b from-transparent via-white to-transparent"></div>
            </div>
        </section>

        <!-- Institutional Footer -->
        <footer class="py-24 bg-sipega-deep border-t border-white/5 relative">
            <div class="max-w-7xl mx-auto px-6 text-center">
                <div class="flex flex-col items-center gap-10">
                    <img src="{{ asset('images/logo-sipega.png') }}" class="h-16 w-auto opacity-40 grayscale -mb-10 invert">
                    <p class="text-[9px] font-bold text-white/10 uppercase tracking-[1em]">
                        &copy; 2026 BPMP KALIMANTAN TIMUR
                    </p>
                </div>
            </div>
        </footer>
    </body>
</html>
