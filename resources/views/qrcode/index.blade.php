<x-app-layout>
    <style>
        .designer-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        @media (min-width: 1024px) {
            .designer-container {
                grid-template-columns: 5fr 7fr;
            }
        }
        .pro-card {
            background: white;
            border-radius: 2.5rem;
            padding: 2rem;
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
            border: 1px solid #f1f5f9;
        }
        .label-elite {
            display: block;
            font-size: 10px;
            font-weight: 900;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            margin-bottom: 1rem;
            margin-left: 0.25rem;
        }
    </style>

    <div class="py-12 bg-slate-50 min-h-screen font-sans">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="mb-8 flex items-center justify-between bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
                <div class="flex items-center gap-4">
                    <div class="p-4 bg-sipega-navy rounded-2xl shadow-indigo-200 shadow-lg" style="background-color: #0f172a;">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-black tracking-tight leading-none uppercase italic" style="color: #0f172a;">QR Designer<span class="text-sipega-orange text-xs block not-italic tracking-widest mt-1" style="color: #ea580c;">SIPEGA ELITE TOOLS</span></h2>
                    </div>
                </div>
            </div>

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
                            logoWidth: 260,
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
                    if (!mainCanvas) return;
                    const ctx = mainCanvas.getContext('2d');
                    
                    const qrDrawSize = 800;
                    let canvasWidth = qrDrawSize;
                    let canvasHeight = qrDrawSize;
                    let offsetX = 0;
                    let offsetY = 0;

                    if (this.frameType === 'box') {
                        canvasWidth = qrDrawSize + 160;
                        canvasHeight = qrDrawSize + 160;
                        offsetX = 80;
                        offsetY = 80;
                    } else if (this.frameType === 'scan_me') {
                        canvasWidth = qrDrawSize + 160;
                        canvasHeight = qrDrawSize + 380;
                        offsetX = 80;
                        offsetY = 80;
                    }

                    mainCanvas.width = canvasWidth;
                    mainCanvas.height = canvasHeight;

                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(0, 0, canvasWidth, canvasHeight);

                    if (this.frameType !== 'none') {
                        ctx.fillStyle = this.qrColor;
                        const frameRadius = 60;
                        this.roundRect(ctx, 10, 10, canvasWidth - 20, canvasHeight - 20, frameRadius, true, false);

                        ctx.fillStyle = '#ffffff';
                        const innerRadius = 30;
                        this.roundRect(ctx, 50, 50, qrDrawSize + 60, qrDrawSize + 60, innerRadius, true, false);

                        if (this.frameType === 'scan_me') {
                            ctx.fillStyle = '#ffffff';
                            ctx.font = '900 120px sans-serif';
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';
                            ctx.fillText('SCAN ME', canvasWidth / 2, canvasHeight - 140);
                        }
                    }

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
            }" class="designer-container">
                
                <!-- Settings Panel -->
                <div class="space-y-6">
                    <div class="pro-card">
                        <label class="label-elite">Konten QR Code</label>
                        <textarea 
                            x-model="content" 
                            @input.debounce.500ms="renderQR"
                            rows="4"
                            class="w-full border-slate-200 rounded-2xl shadow-sm focus:ring-sipega-orange focus:border-sipega-orange transition-all duration-300 resize-none text-slate-600 font-medium p-4 border-2"
                            placeholder="Ketikkan link atau teks..."></textarea>
                    </div>

                    <div class="pro-card">
                        <div class="space-y-8">
                            <div>
                                <label class="label-elite">Model Bingkai</label>
                                <div class="grid grid-cols-3 gap-3">
                                    <template x-for="type in ['none', 'box', 'scan_me']">
                                        <button 
                                            @click="frameType = type; renderQR()" 
                                            :style="frameType === type ? 'background-color: #0f172a; color: white;' : 'background-color: #f8fafc; color: #94a3b8;'"
                                            class="py-3 px-2 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all duration-300">
                                            <span x-text="type === 'none' ? 'Polos' : (type === 'box' ? 'Kotak' : 'Scan Me')"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-8">
                                <div>
                                    <label class="label-elite">Warna Tema</label>
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

                                <div>
                                    <label class="label-elite">Siipkan Logo</label>
                                    <button 
                                        @click="showLogo = !showLogo; renderQR()"
                                        :style="showLogo ? 'background-color: #ea580c; color: white;' : 'background-color: #f1f5f9; color: #94a3b8;'"
                                        class="w-full py-3 rounded-xl flex items-center justify-center gap-2 transition-all duration-300 border-none">
                                        <span class="text-[9px] font-black uppercase tracking-widest" x-text="showLogo ? 'Tutwuri ON' : 'Logo OFF'"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Preview Panel -->
                <div class="pro-card flex flex-col items-center justify-center" style="min-height: 500px;">
                    <div class="relative group" style="position: relative;">
                        <!-- Loading Overlay -->
                        <div x-show="isGenerating" class="absolute inset-0 bg-white/80 backdrop-blur-sm flex items-center justify-center rounded-3xl z-50" style="position: absolute; top:0; left:0; width:100%; height:100%; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.8); z-index: 50;">
                            <div class="animate-spin rounded-full h-12 w-12 border-4 border-sipega-navy border-t-sipega-orange" style="border-radius: 9999px; width: 3rem; height: 3rem; border: 4px solid #0f172a; border-top-color: #ea580c; animation: spin 1s linear infinite;"></div>
                        </div>

                        <!-- Main Display Canvas -->
                        <div class="p-6 bg-slate-50 rounded-3xl border-2 border-dashed border-slate-200 transition-colors duration-500 overflow-hidden" style="background: #f8fafc; padding: 1.5rem; border-radius: 1.5rem; border: 2px dashed #e2e8f0;">
                            <canvas id="final-canvas" class="shadow-2xl rounded-2xl max-w-full h-auto mx-auto" style="box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.25); border-radius: 1rem; max-width: 100%; height: auto; display: block; margin: 0 auto;"></canvas>
                        </div>
                    </div>

                    <!-- Hidden Temp QR Source -->
                    <div id="qrcode-temp-container" class="hidden" style="display: none;"></div>

                    <div class="mt-8 w-full" style="max-width: 24rem; width: 100%; margin-top: 2rem;">
                        <button 
                            @click="downloadPNG"
                            class="w-full py-4 rounded-2xl shadow-lg transition-all duration-300 flex items-center justify-center gap-3 active:scale-95 border-none cursor-pointer"
                            style="background-color: #0f172a; color: white; font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.2em; width: 100%;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width: 1rem; height: 1rem;">
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
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .animate-spin { animation: spin 1s linear infinite; }
    </style>
</x-app-layout>
