<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DIAGNOSTIC REPORT - OVERTHINK.IN</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Hasil analisis Overthinking: {{ $result->result_title }}. Cek tingkat delusion level dan mental battery kamu sekarang!">
    <meta name="keywords" content="overthinking, retro, arcade, diagnosa, mental battery, delusion level">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js from CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="crt bg-retro-bg text-retro-green min-h-screen flex flex-col justify-between p-4 selection:bg-retro-magenta selection:text-white"
      x-data="resultPage()">

    <!-- Scanline overlays for CRT simulation -->
    <div class="scanline-overlay"></div>
    <div class="crt-vignette"></div>

    <!-- Header -->
    <header class="w-full max-w-2xl mx-auto flex justify-between items-center py-4 border-b-2 border-retro-green/30">
        <div class="flex items-center space-x-2">
            <span class="w-3 h-3 bg-retro-magenta blink-fast"></span>
            <a href="/" class="font-arcade text-sm md:text-base tracking-widest text-retro-green hover:text-retro-magenta transition-colors glow-green">
                &lt; OVERTHINK.IN
            </a>
        </div>
        <div class="font-share text-xs md:text-sm text-retro-magenta glow-magenta tracking-wider uppercase">
            REPORT ID: {{ substr($result->uuid, 0, 8) }}
        </div>
    </header>

    <!-- Main Results Display -->
    <main class="w-full max-w-2xl mx-auto my-auto py-8 space-y-6">
        
        <!-- Diagnostic Summary Card -->
        <div class="bg-retro-darkgreen/15 border-double-retro border-retro-magenta border-glow-magenta p-6 relative">
            <div class="absolute -top-3.5 left-4 bg-retro-bg px-2 font-arcade text-xs text-retro-magenta tracking-wider uppercase">
                DIAGNOSTIC ANALYSIS
            </div>

            <!-- Title -->
            <div class="text-center py-4">
                <span class="font-share text-xs text-retro-amber glow-amber tracking-widest uppercase block mb-1">
                    OVERTHINKER SPECTRUM DETECTED:
                </span>
                <h2 class="font-arcade text-lg md:text-2xl text-white glow-magenta uppercase leading-tight">
                    {{ $result->result_title }}
                </h2>
            </div>

            <!-- Text Description Box -->
            <div class="border border-retro-magenta/30 bg-retro-darkmagenta/10 p-4 font-share text-base md:text-lg text-retro-green leading-relaxed text-justify mb-4">
                &gt; {{ $result->result_text }}<span class="blink">█</span>
            </div>

            <!-- Stress Score Meter -->
            <div class="space-y-2 pt-2 border-t border-retro-magenta/20">
                <div class="flex justify-between font-share text-sm text-retro-amber">
                    <span>STRESS CALCULATOR:</span>
                    <span class="font-bold">{{ $result->stress_score }}% DANGER LEVEL</span>
                </div>
                <!-- Retro visual bar gauge -->
                <div class="w-full bg-retro-darkgreen/60 border border-retro-magenta/40 h-6 p-1 flex relative">
                    @php
                        $blocksCount = min(10, max(0, round($result->stress_score / 10)));
                    @endphp
                    @for ($i = 0; $i < 10; $i++)
                        <div class="flex-1 h-full mr-0.5 last:mr-0 {{ $i < $blocksCount ? 'bg-retro-magenta' : 'bg-transparent' }}"></div>
                    @endfor
                </div>
            </div>
        </div>

        <!-- Metric Details Panel -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Delusion & Battery Card -->
            <div class="border-2 border-retro-green/40 border-glow-green p-4 relative bg-retro-darkgreen/10">
                <div class="absolute -top-3 left-3 bg-retro-bg px-1 font-share text-xs text-retro-green tracking-wider uppercase">
                    METRIC VALUES
                </div>
                <div class="space-y-4 pt-2 font-share">
                    <div class="flex justify-between items-center border-b border-retro-green/20 pb-2">
                        <span class="text-retro-amber">DELUSION LEVEL:</span>
                        <span class="text-white font-bold uppercase">{{ $result->metadata['delusion_level'] ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between items-center pb-2">
                        <span class="text-retro-amber">MENTAL BATTERY:</span>
                        <span class="text-retro-magenta font-bold uppercase">{{ $result->metadata['mental_battery'] ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <!-- Recommendation Action Card -->
            <div class="border-2 border-retro-amber/40 border-glow-amber p-4 relative bg-retro-bg">
                <div class="absolute -top-3 left-3 bg-retro-bg px-1 font-share text-xs text-retro-amber tracking-wider uppercase">
                    AI PRESCRIPTION
                </div>
                <div class="pt-2 font-share text-sm text-retro-amber leading-relaxed">
                    <span class="font-bold uppercase text-white block mb-1">RECOMMENDED ACTION:</span>
                    {{ $result->metadata['recommended_action'] ?? 'N/A' }}
                </div>
            </div>
        </div>

        <!-- Actions panel -->
        <div class="flex flex-col items-center justify-center space-y-4 pt-4">
            <!-- Share URL copy section -->
            <div class="w-full flex items-center bg-retro-bg border-2 border-retro-green/30 p-1 font-share text-sm">
                <span class="text-retro-amber px-2 select-none uppercase">LINK:</span>
                <input type="text" 
                       value="{{ request()->fullUrl() }}" 
                       readonly 
                       class="flex-1 bg-transparent text-white focus:outline-none px-2 select-all font-mono text-xs overflow-ellipsis"
                       x-ref="shareUrlInput">
                <button @click="copyShareLink()"
                        class="bg-retro-green text-retro-bg hover:bg-retro-magenta hover:text-white px-4 py-1.5 transition-colors font-share font-bold uppercase cursor-pointer"
                        x-text="copyBtnText">
                    COPY
                </button>
            </div>

            <!-- Play Again Button -->
            <a href="/" 
               class="font-arcade text-xs md:text-sm bg-retro-green text-retro-bg px-6 py-4 border-4 border-white hover:bg-retro-magenta hover:text-white transition-colors duration-150 cursor-pointer shadow-[6px_6px_0px_#082208] active:translate-y-1 active:shadow-[2px_2px_0px_#082208] text-center w-full max-w-sm block">
                INSERT COIN & PLAY AGAIN
            </a>

            <!-- Screenshot Clout Blinking Text -->
            <div class="font-share text-xs text-retro-amber glow-amber uppercase tracking-widest blink-fast py-2">
                -- TAKE SCREENSHOT & POST FOR CLOUT --
            </div>
        </div>

    </main>

    <!-- Footer Copyright -->
    <footer class="w-full max-w-2xl mx-auto text-center py-4 border-t-2 border-retro-green/30 font-share text-xs text-retro-green/40 tracking-wider">
        © 2026 OVERTHINK.IN. PUBLIC DIAGNOSTIC SHARE SYSTEM.
    </footer>

    <!-- Alpine result control logic -->
    <script>
        function resultPage() {
            return {
                copyBtnText: 'COPY',
                
                async copyShareLink() {
                    try {
                        this.$refs.shareUrlInput.select();
                        await navigator.clipboard.writeText(this.$refs.shareUrlInput.value);
                        this.copyBtnText = 'COPIED!';
                        setTimeout(() => { this.copyBtnText = 'COPY'; }, 2000);
                    } catch (err) {
                        this.copyBtnText = 'FAILED';
                        setTimeout(() => { this.copyBtnText = 'COPY'; }, 2000);
                    }
                }
            };
        }
    </script>
</body>
</html>
