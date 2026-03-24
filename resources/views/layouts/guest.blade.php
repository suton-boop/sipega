<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-sipega-navy relative">
            <div class="absolute top-4 left-4">
                <img src="{{ asset('images/Logo Kemendikdasmen BPMP Kaltim.png') }}" class="h-12" alt="Kemendikdasmen" />
            </div>
            
            <div class="flex flex-col items-center">
                <a href="/">
                    <img src="{{ asset('images/logo Sipega.png') }}" class="w-auto h-32 drop-shadow-xl" alt="Logo SIPEGA" />
                </a>
                <img src="{{ asset('images/jenaman SIPEGA.png') }}" class="h-10 mt-6" alt="SIPEGA Text" />
                <p class="text-sipega-orange mt-2 tracking-widest font-semibold uppercase text-sm">Monitoring Performa & Penugasan</p>
            </div>

            <div class="w-full sm:max-w-md mt-8 px-6 py-8 bg-white shadow-2xl overflow-hidden sm:rounded-2xl border-t-8 border-sipega-orange">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
