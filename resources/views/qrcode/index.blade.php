<x-app-layout>
    <div class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="mb-8 flex items-center justify-between bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
                <div class="flex items-center gap-4">
                    <div class="p-4 bg-sipega-navy rounded-2xl shadow-indigo-200 shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-sipega-navy tracking-tight leading-none uppercase italic">QR Designer<span class="text-sipega-orange text-xs block not-italic tracking-widest mt-1">SIPEGA ELITE TOOLS</span></h2>
                    </div>
                </div>
            </div>

            <!-- Designer Content -->
            <div x-data="{ 
                content: '',
                isGenerating: false,
                frameType: 'none',
                qrColor: '#000000',
                showLogo: true,
                logoPath: '{{ asset('images/Tutwuri.png') }}',
                
                init() {
                    const checkInterval = setInterval(() => {
                        if (typeof QRCode !== 'undefined') {
                            clearInterval(checkInterval);
                            this.renderQR();
                        }
                    }, 500);
                },

                renderQR() {
                    if (this.isGenerating) return;
                    this.isGenerating = true;

                    const container = document.getElementById('qrcode-temp-container');
                    container.innerHTML = ''; 
                    
                    if (this.showLogo) {
                        const img = new Image();
                        img.onload = () => this.generateRawQR(true);
                        img.onerror = () => this.generateRawQR(false);
                        img.src = this.logoPath;
                    } else {
                        this.generateRawQR(false);
                    }
                },

                generateRawQR(useLogo) {
                    const tempRoot = document.getElementById('qrcode-temp-container');
                    
                    try {
                        new QRCode(tempRoot, {
                            text: this.content || 'SIPEGA',
                            width: 1000, 
                            height: 1000,
                            colorDark : this.qrColor,
                            colorLight : '#ffffff',
                            correctLevel : QRCode.CorrectLevel.H,
                            logo: useLogo ? this.logoPath : null,
                            logoWidth: 260, // Perbesar logo ke ~26%
                            logoHeight: 260,
                            logoBackgroundColor: '#ffffff',
                            logoBackgroundTransparent: false,
                            onRenderingEnd: (options, dataURL) => {
                                this.drawFrame(tempRoot.querySelector('canvas'));
                            }
                        });
                    } catch (e) {
                        console.error('QR Render Error:', e);
                        this.isGenerating = false;
                    }
                },

                drawFrame(qrCanvas) {
                    const mainCanvas = document.getElementById('final-canvas');
                    const ctx = mainCanvas.getContext('2d');
                    
                    // Master dimensions (Proposional ke 1000px QR source)
                    const qrDrawSize = 800;
                    let canvasWidth = qrDrawSize;
                    let canvasHeight = qrDrawSize;
                    let offsetX = 0;
                    let offsetY = 0;

                    if (this.frameType === 'box') {
                        canvasWidth = qrDrawSize + 160; // Extra padding
                        canvasHeight = qrDrawSize + 160;
                        offsetX = 80;
                        offsetY = 80;
                    } else if (this.frameType === 'scan_me') {
                        canvasWidth = qrDrawSize + 160;
                        canvasHeight = qrDrawSize + 380; // Area bawah lebih luas untk teks
                        offsetX = 80;
                        offsetY = 80;
                    }

                    mainCanvas.width = canvasWidth;
                    mainCanvas.height = canvasHeight;

                    // Clear & Fill BG
                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(0, 0, canvasWidth, canvasHeight);

                    if (this.frameType !== 'none') {
                        // Draw Main Outer Frame
                        ctx.fillStyle = this.qrColor;
                        const frameRadius = 60;
                        this.roundRect(ctx, 10, 10, canvasWidth - 20, canvasHeight - 20, frameRadius, true, false);

                        // Draw White QR Area (Inner Box)
                        ctx.fillStyle = '#ffffff';
                        const innerRadius = 30;
                        this.roundRect(ctx, 50, 50, qrDrawSize + 60, qrDrawSize + 60, innerRadius, true, false);

                        if (this.frameType === 'scan_me') {
                            ctx.fillStyle = '#ffffff';
                            // Gunakan font yang lebih tebal dan besar
                            ctx.font = '900 120px Inter, system-ui, -apple-system, sans-serif';
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';
                            ctx.letterSpacing = '10px';
                            ctx.fillText('SCAN ME', canvasWidth / 2, canvasHeight - 140);
                        }
                    }

                    // Draw actual QR image
                    ctx.drawImage(qrCanvas, offsetX, offsetY, qrDrawSize, qrDrawSize);
                    
                    this.isGenerating = false;
                },

                roundRect(ctx, x, y, width, height, radius, fill, stroke) {
                    if (typeof radius === 'number') {
                        radius = {tl: radius, tr: radius, br: radius, bl: radius};
                    }
                    ctx.beginPath();
                    ctx.moveTo(x + radius.tl, y);
                    ctx.lineTo(x + width - radius.tr, y);
                    ctx.quadraticCurveTo(x + width, y, x + width, y + radius.tr);
                    ctx.lineTo(x + width, y + height - radius.br);
                    ctx.quadraticCurveTo(x + width, y + height, x + width - radius.br, y + height);
                    ctx.lineTo(x + radius.bl, y + height);
                    ctx.quadraticCurveTo(x, y + height, x, y + height - radius.bl);
                    ctx.lineTo(x, y + radius.tl);
                    ctx.quadraticCurveTo(x, y, x + radius.tl, y);
                    ctx.closePath();
                    if (fill) ctx.fill();
                    if (stroke) ctx.stroke();
                },

                downloadPNG() {
                    const canvas = document.getElementById('final-canvas');
                    const link = document.createElement('a');
                    link.download = 'qrcode_sipega_pro_' + Date.now() + '.png';
                    link.href = canvas.toDataURL('image/png');
                    link.click();
                }
            }" class="grid lg:grid-cols-12 gap-8 items-start">
                
                <!-- Settings Panel -->
                <div class="lg:col-span-5 space-y-6">
                    <!-- Text Data -->
                    <div class="bg-white p-8 rounded-[2.5rem] shadow-xl shadow-slate-200 border border-slate-100">
                        <label class="block text-[10px] font-black text-sipega-navy uppercase tracking-[0.2em] mb-4 ms-1">Konten QR Code</label>
                        <textarea 
                            x-model="content" 
                            @input.debounce.500ms="renderQR"
                            rows="4"
                            class="w-full border-slate-200 rounded-2xl shadow-sm focus:ring-sipega-orange focus:border-sipega-orange transition-all duration-300 resize-none text-slate-600 font-medium p-4 border-2"
                            placeholder="Ketikkan link atau teks di sini..."></textarea>
                    </div>

                    <!-- Look & Feel -->
                    <div class="bg-white p-8 rounded-[2.5rem] shadow-xl shadow-slate-200 border border-slate-100">
                        <div class="space-y-8">
                            <!-- Bingkai -->
                            <div>
                                <label class="block text-[10px] font-black text-sipega-navy uppercase tracking-[0.2em] mb-4 ms-1">Model Bingkai</label>
                                <div class="grid grid-cols-3 gap-3">
                                    <template x-for="type in ['none', 'box', 'scan_me']">
                                        <button 
                                            @click="frameType = type; renderQR()" 
                                            :class="frameType === type ? 'bg-sipega-navy text-white shadow-lg' : 'bg-slate-50 text-slate-400 hover:bg-slate-100'"
                                            class="py-3 px-2 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all duration-300">
                                            <span x-text="type === 'none' ? 'Polos' : (type === 'box' ? 'Kotak' : 'Scan Me')"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-8">
                                <!-- Warna -->
                                <div>
                                    <label class="block text-[10px] font-black text-sipega-navy uppercase tracking-[0.2em] mb-4 ms-1">Warna Tema</label>
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="color in ['#000000', '#1e293b', '#dc2626', '#16a34a', '#2563eb', '#ea580c']">
                                            <button 
                                                @click="qrColor = color; renderQR()"
                                                class="w-8 h-8 rounded-lg shadow-sm border-2 transition-transform hover:scale-110 active:scale-95"
                                                :style="'background-color: ' + color + '; border-color: ' + (qrColor === color ? 'white' : 'transparent')"></button>
                                        </template>
                                        <input type="color" x-model="qrColor" @input="renderQR" class="w-8 h-8 p-0 rounded-lg border-none cursor-pointer bg-transparent">
                                    </div>
                                </div>

                                <!-- Logo Toggle -->
                                <div>
                                    <label class="block text-[10px] font-black text-sipega-navy uppercase tracking-[0.2em] mb-4 ms-1">Siipkan Logo</label>
                                    <button 
                                        @click="showLogo = !showLogo; renderQR()"
                                        :class="showLogo ? 'bg-sipega-orange text-white' : 'bg-slate-100 text-slate-400'"
                                        class="w-full py-3 rounded-xl flex items-center justify-center gap-2 transition-all duration-300">
                                        <div :class="showLogo ? 'translate-x-0' : '-translate-x-1'" class="w-10 h-5 bg-white/20 rounded-full relative transition-all">
                                            <div :class="showLogo ? 'right-1' : 'left-1'" class="absolute top-1 w-3 h-3 bg-white rounded-full transition-all"></div>
                                        </div>
                                        <span class="text-[9px] font-black uppercase tracking-widest" x-text="showLogo ? 'Tutwuri ON' : 'Logo OFF'"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Preview Panel -->
                <div class="lg:col-span-7 bg-white p-8 rounded-[2.5rem] shadow-xl shadow-slate-200 border border-slate-100 flex flex-col items-center justify-center min-h-[600px]">
                    <div class="relative group">
                        <!-- Loading Overlay -->
                        <div x-show="isGenerating" class="absolute inset-0 bg-white/80 backdrop-blur-sm flex items-center justify-center rounded-3xl z-50">
                            <div class="animate-spin rounded-full h-12 w-12 border-4 border-sipega-navy border-t-sipega-orange"></div>
                        </div>

                        <!-- Main Display Canvas -->
                        <div class="p-6 bg-slate-50 rounded-3xl border-2 border-dashed border-slate-200 transition-colors duration-500 overflow-hidden">
                            <canvas id="final-canvas" class="shadow-2xl rounded-2xl max-w-full h-auto mx-auto"></canvas>
                        </div>
                    </div>

                    <!-- Hidden Temp QR Source -->
                    <div id="qrcode-temp-container" class="hidden"></div>

                    <div class="mt-8 w-full max-w-sm">
                        <button 
                            @click="downloadPNG"
                            class="w-full bg-sipega-navy text-white text-[11px] font-black uppercase tracking-[0.2em] py-4 rounded-2xl shadow-lg hover:shadow-indigo-200 hover:bg-sipega-navy/90 transition-all duration-300 flex items-center justify-center gap-3 active:scale-95">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Download PNG
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- EasyQRCodeJS -->
    <script src="{{ asset('assets/js/easy.qrcode.min.js') }}"></script>
    <style>
        [x-cloak] { display: none !important; }
        #final-canvas {
            max-height: 500px;
        }
    </style>
</x-app-layout>
