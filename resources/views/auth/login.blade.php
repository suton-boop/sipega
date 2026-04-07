<x-guest-layout>
    <!-- Header Text -->
    <div class="text-center mb-10">
        <h2 class="text-2xl font-montserrat font-extrabold text-[#003366] tracking-tight uppercase italic mb-2">PROSES AUTENTIKASI</h2>
        <div class="h-1.5 w-12 bg-sipega-orange rounded-full mx-auto mb-5"></div>
        <p class="text-[10px] text-[#003366] font-extrabold uppercase tracking-[0.25em] max-w-xs mx-auto leading-relaxed">Sistem Informasi Performa dan Penugasan</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <div class="group text-left">
            <label for="email" class="block text-[10px] font-black text-[#003366]/40 uppercase tracking-widest mb-3 px-2">Email</label>
            <div class="flex items-center h-16 bg-gray-50/30 border border-gray-300 rounded-3xl px-6 focus-within:bg-white focus-within:border-blue-500 focus-within:ring-8 focus-within:ring-blue-100/30 transition-all shadow-sm">
                <input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" 
                    placeholder="nama@kemdikbud.go.id"
                    class="flex-grow bg-transparent border-none focus:ring-0 text-[15px] font-semibold text-[#003366] placeholder-gray-300 p-0">
                
                <div class="ml-3 text-gray-400 group-focus-within:text-[#003366] group-focus-within:scale-110 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-left px-2 text-[10px] font-bold" />
        </div>

        <!-- Password -->
        <div class="group mt-6 text-left">
            <div class="flex justify-between items-center mb-3 px-2">
                <label for="password" class="block text-[10px] font-black text-[#003366]/40 uppercase tracking-widest">Kata Sandi</label>
                @if (Route::has('password.request'))
                    <a class="text-[9px] font-black text-blue-600 hover:text-blue-800 uppercase tracking-widest transition-colors" href="{{ route('password.request') }}">
                        Lupa?
                    </a>
                @endif
            </div>
            <div class="flex items-center h-16 bg-gray-50/30 border border-gray-300 rounded-3xl px-6 focus-within:bg-white focus-within:border-blue-500 focus-within:ring-8 focus-within:ring-blue-100/30 transition-all shadow-sm">
                <div class="mr-3 text-gray-400 hover:text-gray-400 cursor-pointer transition-colors" onclick="const p = document.getElementById('password'); p.type = p.type === 'password' ? 'text' : 'password'">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </div>

                <input id="password" type="password" name="password" required autocomplete="current-password" 
                    placeholder="••••••••"
                    class="flex-grow bg-transparent border-none focus:ring-0 text-[15px] font-semibold text-[#003366] placeholder-gray-300 p-0">
                
                <div class="ml-3 text-gray-400 group-focus-within:text-[#003366] group-focus-within:scale-110 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-left px-2 text-[10px] font-bold" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center px-4">
            <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                <input id="remember_me" type="checkbox" name="remember" class="w-6 h-6 rounded-xl border-gray-300 text-blue-600 shadow-sm focus:ring-blue-100 transition-all cursor-pointer bg-gray-50/50">
                <span class="ms-3 text-[10px] font-bold text-gray-400 group-hover:text-blue-500 transition-colors uppercase tracking-widest leading-none">Ingat Perangkat</span>
            </label>
        </div>

        <!-- Submit Button: Forced Inline CSS (Official Navy) -->
        <div class="pt-4">
            <button type="submit" 
                style="background-color: #003366 !important; color: white !important; display: flex; align-items: center; justify-content: center; width: 100%; height: 60px; border-radius: 1.5rem; font-weight: 800; text-transform: uppercase; letter-spacing: 3px; font-size: 11px; border: none; box-shadow: 0 10px 30px -5px rgba(0, 51, 102, 0.3); font-family: 'Montserrat', sans-serif;"
                class="hover:brightness-125 transition-all shadow-xl">
                MASUK
            </button>
        </div>
    </form>
</x-guest-layout>
