<x-guest-layout>
    <!-- Header Text -->
    <div class="text-center mb-6 sm:mb-8">
        <h2 class="text-xl sm:text-2xl font-montserrat font-extrabold text-[#003366] tracking-tight uppercase italic mb-1.5">PROSES AUTENTIKASI</h2>
        <div class="h-1.5 w-12 bg-sipega-orange rounded-full mx-auto mb-3"></div>
        <p class="text-[9px] sm:text-[10px] text-[#003366] font-extrabold uppercase tracking-[0.25em] max-w-xs mx-auto leading-relaxed">Sistem Informasi Performa dan Penugasan</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4 sm:space-y-5">
        @csrf

        <!-- NIP atau Email -->
        <div class="group text-left">
            <label for="email" class="block text-[10px] font-black text-[#003366]/70 uppercase tracking-widest mb-1.5 px-1">NIP / Email Pegawai</label>
            <div class="flex items-center h-12 sm:h-14 bg-gray-50/50 border border-gray-300 rounded-2xl sm:rounded-3xl px-4 sm:px-5 focus-within:bg-white focus-within:border-blue-500 focus-within:ring-4 focus-within:ring-blue-100 transition-all shadow-sm">
                <input id="email" type="text" name="email" :value="old('email')" required autofocus autocomplete="username" 
                    placeholder="Masukkan NIP atau Email"
                    class="flex-grow bg-transparent border-none focus:ring-0 text-sm font-semibold text-[#003366] placeholder-gray-400 p-0">
                
                <div class="ml-2 text-gray-400 group-focus-within:text-[#003366] group-focus-within:scale-110 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-left px-1 text-[10px] font-bold" />
        </div>

        <!-- Password -->
        <div class="group text-left">
            <div class="flex justify-between items-center mb-1.5 px-1">
                <label for="password" class="block text-[10px] font-black text-[#003366]/70 uppercase tracking-widest">Kata Sandi</label>
                @if (Route::has('password.request'))
                    <a class="text-[9px] font-black text-blue-600 hover:text-blue-800 uppercase tracking-widest transition-colors" href="{{ route('password.request') }}">
                        Lupa?
                    </a>
                @endif
            </div>
            <div class="flex items-center h-12 sm:h-14 bg-gray-50/50 border border-gray-300 rounded-2xl sm:rounded-3xl px-4 sm:px-5 focus-within:bg-white focus-within:border-blue-500 focus-within:ring-4 focus-within:ring-blue-100 transition-all shadow-sm">
                <div class="mr-2 text-gray-400 hover:text-[#003366] cursor-pointer transition-colors" onclick="const p = document.getElementById('password'); p.type = p.type === 'password' ? 'text' : 'password'">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </div>

                <input id="password" type="password" name="password" required autocomplete="current-password" 
                    placeholder="••••••••"
                    class="flex-grow bg-transparent border-none focus:ring-0 text-sm font-semibold text-[#003366] placeholder-gray-400 p-0">
                
                <div class="ml-2 text-gray-400 group-focus-within:text-[#003366] group-focus-within:scale-110 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-left px-1 text-[10px] font-bold" />
            <p class="text-[9px] text-[#003366]/60 mt-1.5 px-1 font-bold italic text-left">*Login dapat menggunakan NIP (Username & Password = NIP).</p>
        </div>

        <!-- Remember Me -->
        <div class="flex items-center px-1">
            <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                <input id="remember_me" type="checkbox" name="remember" class="w-5 h-5 rounded-lg border-gray-300 text-blue-600 shadow-sm focus:ring-blue-100 transition-all cursor-pointer bg-gray-50/50">
                <span class="ms-2.5 text-[10px] font-bold text-gray-500 group-hover:text-blue-600 transition-colors uppercase tracking-widest leading-none">Ingat Perangkat</span>
            </label>
        </div>

        <!-- Submit Button: Clean & Highly Visible -->
        <div class="pt-2">
            <button type="submit" 
                class="w-full h-12 sm:h-14 bg-[#003366] hover:bg-[#002244] active:scale-[0.99] text-white rounded-2xl font-black text-xs uppercase tracking-[0.25em] shadow-lg shadow-blue-900/25 transition-all flex items-center justify-center gap-2 font-montserrat">
                <span>MASUK</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </button>
        </div>
    </form>
</x-guest-layout>
