<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OVERTHINK.IN - Haunted Arcade Cabinet</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Masukkan koin trauma kamu untuk memulai simulator overthinking retro 8-bit paling absurd dan ngena. Ukur level delusi kamu sekarang!">
    <meta name="keywords" content="overthinking, game retro, arcade, kuis absurd, indonesia, mental battery">
    
    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js from CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="crt bg-retro-bg text-retro-green min-h-screen flex flex-col justify-between p-4 selection:bg-retro-magenta selection:text-white"
      x-data="quizApp()"
      x-init="initApp()"
      :class="{ 'shake': shakeScreen }">

    <!-- Scanline overlays for CRT simulation -->
    <div class="scanline-overlay"></div>
    <div class="crt-vignette"></div>

    <!-- Header -->
    <header class="w-full max-w-2xl mx-auto flex justify-between items-center py-4 border-b-2 border-retro-green/30">
        <div class="flex items-center space-x-2">
            <span class="w-3 h-3 bg-retro-green blink-fast"></span>
            <h1 class="font-arcade text-sm md:text-base tracking-widest text-retro-green glow-green">
                OVERTHINK.IN
            </h1>
        </div>
        <div class="font-share text-xs md:text-sm text-retro-amber glow-amber tracking-wider">
            SYSTEM: OK
        </div>
    </header>

    <!-- Main Content Cabinet -->
    <main class="w-full max-w-2xl mx-auto my-auto flex flex-col justify-center items-center py-8 px-4 md:px-8 bg-retro-darkgreen/20 border-double-retro border-retro-green border-glow-green relative min-h-[450px]">
        
        <!-- Error Banner -->
        <template x-if="errorMsg">
            <div class="absolute top-4 left-4 right-4 bg-retro-magenta/20 border-2 border-retro-magenta text-retro-magenta p-2 font-share text-sm text-center tracking-wider z-20">
                [SYSTEM FAILURE] <span x-text="errorMsg"></span>
                <button @click="errorMsg = ''" class="ml-2 underline font-bold hover:text-white">CLEAR</button>
            </div>
        </template>

        <!-- 1. LANDING SCREEN -->
        <template x-if="step === 'landing'">
            <div class="text-center flex flex-col items-center justify-center space-y-8 py-6">
                <!-- Arcade Title -->
                <div class="space-y-2">
                    <div class="font-arcade text-3xl md:text-4xl text-retro-magenta glow-magenta tracking-wider leading-none">
                        OVERTHINK
                    </div>
                    <div class="font-arcade text-lg md:text-xl text-retro-green glow-green tracking-widest">
                        HAUNTED CABINET
                    </div>
                </div>

                <!-- Insert Coin Button -->
                <div class="py-6">
                    <button @click="startOverthinking()" 
                            class="font-arcade text-xs md:text-sm bg-retro-green text-retro-bg px-6 py-4 border-4 border-white hover:bg-retro-magenta hover:text-white transition-colors duration-150 cursor-pointer shadow-[6px_6px_0px_#082208] active:translate-y-1 active:shadow-[2px_2px_0px_#082208] group relative">
                        <span class="absolute -top-1 -left-1 text-white text-[8px] opacity-60">10c</span>
                        INSERT COIN TO START OVERTHINKING
                    </button>
                </div>

                <!-- Flickering Prompt -->
                <p class="font-share text-sm text-retro-amber glow-amber uppercase tracking-widest blink">
                    -- PRESS BUTTON TO INSERT TRAUMA --
                </p>

                <!-- Footer Text -->
                <div class="font-share text-xs text-retro-green/50 tracking-wider max-w-sm">
                    WARNING: MEMBACA KELUH KESAH DAPAT MENYEBABKAN ANXIETY AKUT, OVERTHINKING LEVEL 99, DAN GANGGUAN TIDUR SEJAKET.
                </div>
            </div>
        </template>

        <!-- 2. HANDSHAKE LOADING SCREEN -->
        <template x-if="step === 'loading'">
            <div class="flex flex-col items-center justify-center space-y-6">
                <!-- Spinning pixel block -->
                <div class="font-arcade text-lg text-retro-amber animate-pulse">
                    [■] [□] [■] [□]
                </div>
                <div class="font-share text-lg tracking-widest text-retro-amber uppercase">
                    <span x-text="loadingText"></span><span class="blink">_</span>
                </div>
                <div class="font-share text-xs text-retro-green/40">
                    ESTABLISHING ANONYMOUS ENCRYPTION...
                </div>
            </div>
        </template>

        <!-- 3. QUESTION FLOW SCREEN -->
        <template x-if="step === 'quiz'">
            <div class="w-full flex flex-col justify-between min-h-[350px] space-y-6">
                <!-- Level progress -->
                <div class="flex justify-between items-center font-share text-sm text-retro-amber">
                    <span class="tracking-wider uppercase">STAGE: <span x-text="String(currentQuestionIndex + 1).padStart(2, '0')"></span> / <span x-text="String(questions.length).padStart(2, '0')"></span></span>
                    <span class="tracking-wider" x-text="questions[currentQuestionIndex] ? questions[currentQuestionIndex].category.toUpperCase() : ''"></span>
                </div>

                <!-- Progress bar -->
                <div class="w-full bg-retro-darkgreen/50 border border-retro-green/30 h-4 p-0.5 font-share text-xs flex">
                    <template x-for="i in questions.length">
                        <div class="flex-1 h-full mr-0.5 last:mr-0 transition-all duration-150"
                             :class="i - 1 <= currentQuestionIndex ? 'bg-retro-green' : 'bg-transparent'"></div>
                    </template>
                </div>

                <!-- Question Text (Crawl Effect) -->
                <div class="border-2 border-retro-green/30 p-4 min-h-[90px] flex items-center bg-retro-bg relative">
                    <div class="font-share text-lg md:text-xl text-white tracking-wide leading-relaxed">
                        &gt; <span x-text="typewritedText"></span><span class="blink font-bold text-retro-green">█</span>
                    </div>
                </div>

                <!-- Answer options -->
                <div class="flex flex-col space-y-3 pt-2">
                    <template x-for="(option, index) in (questions[currentQuestionIndex] ? questions[currentQuestionIndex].options : [])" :key="index">
                        <button @click="selectOption(option.index)"
                                class="w-full text-left font-share text-base md:text-lg border-2 border-retro-green hover:border-retro-magenta hover:bg-retro-darkmagenta/30 hover:text-retro-magenta p-3 transition-colors duration-100 cursor-pointer flex items-center justify-between group">
                            <span>
                                <span class="text-retro-amber font-bold mr-3" x-text="['[A]', '[B]', '[C]', '[D]'][index] || '[?]' "></span>
                                <span x-text="option.text"></span>
                            </span>
                            <span class="opacity-0 group-hover:opacity-100 font-arcade text-xs text-retro-magenta">&lt; SELECT</span>
                        </button>
                    </template>
                </div>

                <!-- Prev Button / Back to start -->
                <div class="flex justify-between items-center pt-4 border-t border-retro-green/20 font-share text-sm">
                    <button @click="prevQuestion()" 
                            class="text-retro-green/70 hover:text-retro-magenta transition-colors tracking-widest uppercase cursor-pointer"
                            :disabled="currentQuestionIndex === 0"
                            :class="{ 'opacity-30 cursor-not-allowed': currentQuestionIndex === 0 }">
                        &lt; PREV STAGE
                    </button>
                    <button @click="resetSession()" class="text-retro-magenta/70 hover:text-white transition-colors tracking-widest uppercase cursor-pointer">
                        RESET COIN
                    </button>
                </div>
            </div>
        </template>

        <!-- 4. COMPUTING SCREEN -->
        <template x-if="step === 'processing'">
            <div class="w-full flex flex-col justify-center items-center py-8 space-y-6 text-center">
                <!-- Title computing blinking -->
                <div class="font-arcade text-lg md:text-xl text-retro-magenta blink glow-magenta uppercase">
                    COMPUTING SKORES
                </div>

                <!-- Glitch box for strings looping -->
                <div class="w-full max-w-md border-2 border-retro-amber p-4 bg-retro-bg font-share text-sm md:text-base text-retro-amber min-h-[80px] flex items-center justify-center tracking-wider border-glow-amber">
                    <div class="leading-relaxed">
                        &gt; <span x-text="processingTexts[processingTextIndex]"></span><span class="blink">█</span>
                    </div>
                </div>

                <!-- Progress text -->
                <div class="font-share text-xs text-retro-green/50 animate-pulse tracking-widest">
                    SYSTEM AT LEVEL 99 DELUSION / CORRUPTED SAVE STATE DETECTED
                </div>
            </div>
        </template>

    </main>

    <!-- Footer Copyright -->
    <footer class="w-full max-w-2xl mx-auto text-center py-4 border-t-2 border-retro-green/30 font-share text-xs text-retro-green/40 tracking-wider">
        © 2026 OVERTHINK.IN. POWERED BY GROQ & 8-BIT DEPRESSION.
    </footer>

    <!-- Audio / Interaction Script -->
    <script>
        function quizApp() {
            return {
                step: 'landing',
                loadingText: 'INITIALIZING CORE ENGINE...',
                questions: [],
                currentQuestionIndex: 0,
                answers: [],
                sessionId: '',
                shakeScreen: false,
                errorMsg: '',

                // Typewriter/Retro crawl states
                typewritedText: '',
                typewriterInterval: null,

                // Processing strings
                processingTexts: [
                    "INSERTING TRAUMA TO COIN SLOT...",
                    "LOADING DELUSION LEVEL 99...",
                    "READING CHATS YOU PREVIOUSLY DELETED...",
                    "CALLING THE OVERTHINKING BOSS FIGHT...",
                    "GENERATING ANXIETY MANIFESTO...",
                    "CALIBRATING IMPOSING IMPOSTOR SYNDROME...",
                    "PARSING SLEEP DEPRIVATION DATA...",
                    "ANALYZING OVERTHINK_SESS_INTEGRITY..."
                ],
                processingTextIndex: 0,
                processingInterval: null,

                initApp() {
                    // Check cache on load
                    const savedToken = localStorage.getItem('overthink_session_token');
                    if (savedToken) {
                        this.sessionId = savedToken;
                    }
                },

                // Start typewriter animation for text crawl
                startTypewriter(text) {
                    if (this.typewriterInterval) clearInterval(this.typewriterInterval);
                    this.typewritedText = '';
                    let i = 0;
                    this.typewriterInterval = setInterval(() => {
                        if (i < text.length) {
                            this.typewritedText += text.charAt(i);
                            i++;
                        } else {
                            clearInterval(this.typewriterInterval);
                        }
                    }, 25);
                },

                // Handshake and load questions
                async startOverthinking() {
                    this.errorMsg = '';
                    this.step = 'loading';
                    this.loadingText = 'CONNECTING TO BACKEND PORT...';

                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        
                        // Handshake
                        const handshakeResponse = await fetch('/api/start-session', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            }
                        });

                        if (!handshakeResponse.ok) throw new Error('Token allocation error.');

                        const handshakeData = await handshakeResponse.json();
                        this.sessionId = handshakeData.session_id;
                        localStorage.setItem('overthink_session_token', this.sessionId);

                        // Fetch questions
                        this.loadingText = 'RETRIEVING STATIC ASSESSMENT MATRIX...';
                        const questionsResponse = await fetch('/api/questions', {
                            headers: {
                                'Accept': 'application/json',
                                'X-Session-ID': this.sessionId
                            }
                        });

                        if (!questionsResponse.ok) throw new Error('Failed to download questions.');

                        const questionsData = await questionsResponse.json();
                        this.questions = questionsData.questions;
                        this.answers = [];
                        this.currentQuestionIndex = 0;
                        
                        // Proceed to Quiz View
                        this.step = 'quiz';
                        this.startTypewriter(this.questions[0].text);

                    } catch (err) {
                        this.errorMsg = err.message || 'CABINET OFFLINE. INSERT COIN AGAIN.';
                        this.step = 'landing';
                    }
                },

                selectOption(optIndex) {
                    const activeQ = this.questions[this.currentQuestionIndex];
                    
                    // Save response
                    this.answers[this.currentQuestionIndex] = {
                        question_id: activeQ.id,
                        selected_option_index: optIndex
                    };

                    // Soundless screen shake for micro-interaction feedback on option selected
                    this.shakeScreen = true;
                    setTimeout(() => { this.shakeScreen = false; }, 300);

                    // Move to next question or submit
                    if (this.currentQuestionIndex < this.questions.length - 1) {
                        this.currentQuestionIndex++;
                        this.startTypewriter(this.questions[this.currentQuestionIndex].text);
                    } else {
                        this.submitAnswers();
                    }
                },

                prevQuestion() {
                    if (this.currentQuestionIndex > 0) {
                        this.currentQuestionIndex--;
                        this.startTypewriter(this.questions[this.currentQuestionIndex].text);
                    }
                },

                resetSession() {
                    localStorage.removeItem('overthink_session_token');
                    this.sessionId = '';
                    this.step = 'landing';
                    this.errorMsg = '';
                },

                // Submit answer payload
                async submitAnswers() {
                    this.errorMsg = '';
                    this.step = 'processing';

                    // Start random loading script text crawl loop
                    this.processingTextIndex = 0;
                    this.processingInterval = setInterval(() => {
                        this.processingTextIndex = (this.processingTextIndex + 1) % this.processingTexts.length;
                    }, 1500);

                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        
                        const response = await fetch('/api/submit-answers', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Session-ID': this.sessionId
                            },
                            body: JSON.stringify({
                                session_id: this.sessionId,
                                answers: this.answers
                            })
                        });

                        clearInterval(this.processingInterval);

                        if (!response.ok) {
                            const errorData = await response.json();
                            throw new Error(errorData.message || 'Calculations failed.');
                        }

                        const data = await response.json();
                        // Redirect to the result sharing route
                        window.location.href = `/result/${data.uuid}`;

                    } catch (err) {
                        clearInterval(this.processingInterval);
                        this.errorMsg = err.message || 'AI ENGINE COLLAPSED. RETRYING...';
                        this.step = 'quiz';
                    }
                }
            };
        }
    </script>
</body>
</html>
