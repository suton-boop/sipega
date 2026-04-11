<x-app-layout>
    <div class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="mb-8 flex items-center justify-between bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
                <div class="flex items-center gap-4">
                    <div class="p-4 bg-sipega-navy rounded-2xl shadow-indigo-200 shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-sipega-navy tracking-tight leading-none uppercase italic">QR Generator<span class="text-sipega-orange text-xs block not-italic tracking-widest mt-1">SIPEGA ELITE TOOLS</span></h2>
                    </div>
                </div>
            </div>

            <!-- Generator Content -->
            <div x-data="{ 
                content: '',
                isGenerating: false,
                qrcode: null,
                logoPath: '{{ asset('images/Tutwuri.png') }}',
                
                init() {
                    // Cek ketersediaan library setiap 500ms
                    const checkInterval = setInterval(() => {
                        if (typeof QRCode !== 'undefined') {
                            clearInterval(checkInterval);
                            this.renderQR();
                        }
                    }, 500);
                },

                renderQR() {
                    const container = document.getElementById('qrcode-container');
                    if (!container) return;
                    
                    this.isGenerating = true;
                    container.innerHTML = ''; 
                    
                    // Preload Image untuk mencegah hang jika logo tidak ditemukan
                    const img = new Image();
                    img.onload = () => this.generateWithLogo(true);
                    img.onerror = () => {
                        console.warn('Logo tidak ditemukan, generate tanpa logo...');
                        this.generateWithLogo(false);
                    };
                    img.src = this.logoPath;
                },

                generateWithLogo(useLogo) {
                    const container = document.getElementById('qrcode-container');
                    
                    try {
                        const options = {
                            text: this.content || 'SIPEGA',
                            width: 500,
                            height: 500,
                            colorDark : '#000000',
                            colorLight : '#ffffff',
                            correctLevel : QRCode.CorrectLevel.H,
                            onRenderingEnd: () => {
                                this.isGenerating = false;
                            }
                        };

                        if (useLogo) {
                            options.logo = this.logoPath;
                            options.logoWidth = 100;
                            options.logoHeight = 100;
                            options.logoBackgroundColor = '#ffffff';
                            options.logoBackgroundTransparent = false;
                        }

                        this.qrcode = new QRCode(container, options);

                        // Safety Timeout
                        setTimeout(() => {
                            if (this.isGenerating) this.isGenerating = false;
                        }, 2500);

                    } catch (e) {
                        console.error('QR Render Error:', e);
                        this.isGenerating = false;
                    }
                },

                updateQR() {
                    this.renderQR();
                },

                downloadPNG() {
                    const canvas = document.querySelector('#qrcode-container canvas');
                    if (canvas) {
                        const link = document.createElement('a');
                        link.download = 'qrcode_' + Date.now() + '.png';
                        link.href = canvas.toDataURL('image/png');
                        link.click();
                    } else {
                        alert('Gambar belum siap.');
                    }
                }
            }" class="grid md:grid-cols-2 gap-8 items-start">
                
                <!-- Input Panel -->
                <div class="bg-white p-8 rounded-[2.5rem] shadow-xl shadow-slate-200 border border-slate-100">
                    <div class="mb-6">
                        <label for="qr_data" class="block text-[10px] font-black text-sipega-navy uppercase tracking-[0.2em] mb-3 ms-1">Link atau Data Teks</label>
                        <textarea 
                            id="qr_data" 
                            x-model="content" 
                            @input.debounce.700ms="updateQR"
                            rows="5"
                            class="w-full border-slate-200 rounded-2xl shadow-sm focus:ring-sipega-orange focus:border-sipega-orange transition-all duration-300 resize-none text-slate-600 font-medium p-4 border-2"
                            placeholder="Ketikkan link URL atau informasi teks di sini untuk membuat QR Code..."></textarea>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="p-4 bg-orange-50 rounded-2xl border border-orange-100">
                            <h4 class="text-[10px] font-black text-sipega-orange uppercase tracking-wider mb-1 flex items-center gap-2">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Mode Offline & Logo
                            </h4>
                            <p class="text-[11px] text-slate-600 leading-relaxed font-medium">Library sekarang dimuat secara lokal. Jika logo tidak ditemukan, QR Code akan tetap muncul tanpa logo agar tidak terjadi hambatan loading.</p>
                        </div>
                    </div>
                </div>

                <!-- Preview Panel -->
                <div class="bg-white p-8 rounded-[2.5rem] shadow-xl shadow-slate-200 border border-slate-100 flex flex-col items-center justify-center min-h-[400px]">
                    <div class="relative group">
                        <!-- Loading Overlay -->
                        <div x-show="isGenerating" class="absolute inset-0 bg-white/80 backdrop-blur-sm flex items-center justify-center rounded-3xl z-10 transition-all duration-300">
                            <div class="animate-spin rounded-full h-12 w-12 border-4 border-sipega-navy border-t-sipega-orange"></div>
                        </div>

                        <!-- QR Code Container -->
                        <div class="p-6 bg-slate-50 rounded-3xl border-2 border-dashed border-slate-200 group-hover:border-sipega-orange/30 transition-colors duration-500 min-w-[300px] min-h-[300px] flex items-center justify-center">
                            <div id="qrcode-container" class="shadow-2xl rounded-xl overflow-hidden bg-white p-2"></div>
                        </div>
                    </div>

                    <div class="mt-8 w-full">
                        <button 
                            @click="downloadPNG"
                            class="w-full bg-sipega-navy text-white text-[11px] font-black uppercase tracking-[0.2em] py-4 rounded-2xl shadow-lg shadow-indigo-100 hover:shadow-indigo-200 hover:bg-sipega-navy/90 transition-all duration-300 flex items-center justify-center gap-3 active:scale-95">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Unduh Dokumen PNG
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- EasyQRCodeJS Library - Sekarang Lokal -->
    <script src="{{ asset('assets/js/easy.qrcode.min.js') }}"></script>
    <style>
        #qrcode-container canvas {
            display: block;
            margin: 0 auto;
            max-width: 100%;
            height: auto !important;
        }
    </style>
</x-app-layout>
