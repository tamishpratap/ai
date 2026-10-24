/**
 * Quantum ARB IRA Voice Navigation Assistant — Master JavaScript Controller
 * Phase 3 Isolated Architecture
 * Single Voice Engine, Single Microphone Controller, Multilingual Voice Navigation
 */

(function () {
    'use strict';

    // Prevent duplicate initializations
    if (window.IRA && window.IRA.initialized) {
        return;
    }

    const IraAssistant = {
        initialized: false,
        config: {},
        currentState: 'READY', // 'READY' | 'STARTING' | 'LISTENING' | 'THINKING' | 'SPEAKING' | 'ERROR'
        mode: 'chat',
        recognition: null,
        isRecognitionRunning: false,
        isRecognitionStarting: false,
        isAbortedByUser: false,
        autoListenTimeout: null,
        selectedLocale: 'hi-IN',
        isFullscreen: false,
        recentContext: {},
        elements: {},
        activeUtterance: null,
        availableVoices: [],
        hindiVoice: null,
        indianEnglishVoice: null,
        fallbackEnglishVoice: null,
        audioCtx: null,
        startingTimer: null,
        silenceTimer: null,
        maxListeningTimer: null,
        sessionFinalTranscript: '',
        sessionInterimTranscript: '',
        hasDispatchedTurn: false,
        guideState: null,

        /**
         * Initialize the IRA assistant
         */
        init: function (options) {
            if (this.initialized) return;

            // Merge server-provided configuration
            const serverConfig = window.IRA_CONFIG || {};
            this.config = Object.assign({
                csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                intentEndpoint: '/ira/intent',
                formEndpoint: '/ira/form-step',
                formsForPageEndpoint: '/ira/forms-for-page',
                role: 'guest',
                userName: 'Visitor',
                isAuthenticated: false,
                defaultLocale: 'hi-IN',
                preferredVoice: 'Kore',
                voiceRate: 1.0,
                voicePitch: 1.05,
                silenceThresholdMs: 1800,
                maxListeningDurationMs: 25000
            }, serverConfig, options || {});

            const SUPPORTED_LOCALES = ['hi-IN', 'en-IN', 'en-US'];
            let savedLocale = null;
            try {
                savedLocale = localStorage.getItem('ira_preferred_locale');
            } catch (e) {}

            if (savedLocale && SUPPORTED_LOCALES.includes(savedLocale)) {
                this.selectedLocale = savedLocale;
            } else {
                const defaultCandidate = (this.config && this.config.defaultLocale) || 'hi-IN';
                this.selectedLocale = SUPPORTED_LOCALES.includes(defaultCandidate) ? defaultCandidate : 'hi-IN';
            }

            this.sessionFinalTranscript = '';
            this.hasDispatchedTurn = false;

            // Cache DOM elements
            if (!this.cacheElements()) {
                console.warn('[IRA] Required DOM elements not found yet. Retrying on DOMContentLoaded.');
                document.addEventListener('DOMContentLoaded', () => this.init(options));
                return;
            }

            // Sync dropdown with active locale
            if (this.elements.langSelect) {
                this.elements.langSelect.value = this.selectedLocale;
            }

            // Initialize Speech Recognition & Synthesis
            this.initSpeechRecognition();
            this.initSpeechSynthesis();

            // Bind Event Listeners
            this.bindEvents();

            // Phase 7 Hotfix: Mount visible AI Form-Fill buttons to eligible registered forms
            this.mountFormFillButtons();

            this.initialized = true;
            console.log('[IRA] Voice Navigation Assistant initialized. Role: ' + this.config.role + ', Locale: ' + this.selectedLocale);
        },

        /**
         * Cache DOM elements
         */
        cacheElements: function () {
            this.elements = {
                modal: document.getElementById('iraModal'),
                modalCard: document.getElementById('iraModalCard'),
                floatingContainer: document.getElementById('iraFloatingContainer'),
                floatingBtn: document.getElementById('iraFloatingBtn'),
                floatingExpandBtn: document.getElementById('iraFloatingExpandBtn'),
                floatingMicIcon: document.getElementById('iraFloatingMicIcon'),
                floatingLabel: document.getElementById('iraFloatingLabel'),
                closeBtn: document.getElementById('iraCloseBtn'),
                expandBtn: document.getElementById('iraExpandBtn'),
                expandSvg: document.getElementById('iraExpandSvg'),
                collapseSvg: document.getElementById('iraCollapseSvg'),
                cardContainer: document.getElementById('iraCardContainer'),
                orbBtn: document.getElementById('iraOrbBtn'),
                orbWrapper: document.getElementById('iraOrbWrapper'),
                orbIcon: document.getElementById('iraOrbIcon'),
                statusPill: document.getElementById('iraStatusPill'),
                statusLabel: document.getElementById('iraStatusLabel'),
                visualizer: document.getElementById('iraWaveVisualizer'),
                mainHeading: document.getElementById('iraMainHeading'),
                subHeading: document.getElementById('iraSubHeading'),
                transcriptArea: document.getElementById('iraTranscriptArea'),
                transcriptUser: document.getElementById('iraTranscriptUser'),
                userText: document.getElementById('iraUserText'),
                transcriptAi: document.getElementById('iraTranscriptAi'),
                aiText: document.getElementById('iraAiText'),
                actionBanner: document.getElementById('iraActionBanner'),
                actionBannerText: document.getElementById('iraActionBannerText'),
                stopBtn: document.getElementById('iraStopBtn'),
                langSelect: document.getElementById('iraLangSelect'),
                contextBadge: document.getElementById('iraContextBadge'),
            };

            return this.elements.modal && this.elements.orbBtn;
        },

        /**
         * Open Assistant Modal (Adaptive Bridge for Bootstrap 5 and Bootstrap 4)
         */
        openModal: function () {
            if (!this.elements.modal) return;

            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const modalInstance = bootstrap.Modal.getOrCreateInstance(this.elements.modal);
                modalInstance.show();
            } else if (window.$ && typeof $(this.elements.modal).modal === 'function') {
                $(this.elements.modal).modal('show');
            } else {
                this.elements.modal.classList.add('show');
                this.elements.modal.style.display = 'block';
            }
            document.body.classList.add('ira-panel-open');
        },

        /**
         * Close Assistant Modal (Adaptive Bridge for Bootstrap 5 and Bootstrap 4)
         */
        closeModal: function () {
            if (!this.elements.modal) return;

            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const modalInstance = bootstrap.Modal.getInstance(this.elements.modal);
                if (modalInstance) modalInstance.hide();
            } else if (window.$ && typeof $(this.elements.modal).modal === 'function') {
                $(this.elements.modal).modal('hide');
            } else {
                this.elements.modal.classList.remove('show');
                this.elements.modal.style.display = 'none';
            }
            document.body.classList.remove('ira-panel-open');
        },

        /**
         * Open Assistant Modal and start listening once the panel is ready and visible
         */
        openModalAndStartListening: function () {
            if (!this.elements.modal) return;

            // Debounce rapid repeated clicks within 500ms
            const now = Date.now();
            if (this._lastExpandClick && (now - this._lastExpandClick < 500)) {
                return;
            }
            this._lastExpandClick = now;

            this.stopSpeaking();

            // Immediate visible status while panel opens and microphone initializes
            this.setVoiceState('STARTING', 'Starting microphone...');

            let listenerStarted = false;
            const startVoiceOnceReady = () => {
                if (listenerStarted) return;
                listenerStarted = true;

                // Clean up listeners
                if (this.elements.modal) {
                    this.elements.modal.removeEventListener('shown.bs.modal', onModalShown);
                    if (window.$) {
                        $(this.elements.modal).off('shown.bs.modal', onModalShown);
                    }
                }

                // Start listening if not already running or starting
                if (!this.isRecognitionRunning && !this.isRecognitionStarting) {
                    this.startListening();
                }
            };

            const onModalShown = () => {
                startVoiceOnceReady();
            };

            // Check if modal is already visible
            const isModalVisible = this.elements.modal.classList.contains('show') &&
                                   window.getComputedStyle(this.elements.modal).display !== 'none';

            if (isModalVisible) {
                startVoiceOnceReady();
            } else {
                // Wait for Bootstrap transition shown.bs.modal event
                this.elements.modal.addEventListener('shown.bs.modal', onModalShown, { once: true });
                if (window.$) {
                    $(this.elements.modal).one('shown.bs.modal', onModalShown);
                }

                // Deterministic fallback timeout (350ms) in case CSS transitions are skipped
                setTimeout(startVoiceOnceReady, 350);

                // Open the modal
                this.openModal();
            }
        },

        /**
         * Play Clear Two-Tone Audio Cue via Web Audio API
         * Short ascending chirp (659Hz -> 880Hz) to signal microphone engagement.
         */
        playListeningCue: function () {
            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtx) return;

                if (!this.audioCtx || this.audioCtx.state === 'closed') {
                    this.audioCtx = new AudioCtx();
                }
                if (this.audioCtx.state === 'suspended') {
                    this.audioCtx.resume().catch((err) => {
                        console.warn('[IRA] AudioContext resume note:', err);
                    });
                }

                const now = this.audioCtx.currentTime;
                const osc = this.audioCtx.createOscillator();
                const gain = this.audioCtx.createGain();

                osc.type = 'sine';
                osc.frequency.setValueAtTime(659.25, now);
                osc.frequency.exponentialRampToValueAtTime(880.00, now + 0.08);

                gain.gain.setValueAtTime(0.0001, now);
                gain.gain.linearRampToValueAtTime(0.12, now + 0.02);
                gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.12);

                osc.connect(gain);
                gain.connect(this.audioCtx.destination);

                osc.start(now);
                osc.stop(now + 0.12);
            } catch (err) {
                console.warn('[IRA] Listening chime skipped:', err);
            }
        },

        /**
         * Play Non-Blocking Navigation Confirmation Cue via Web Audio API
         * Crisp confirmation tone (523Hz -> 784Hz) for instantaneous cross-page transition feedback.
         */
        playNavigationCue: function (onComplete) {
            let callbackTriggered = false;
            const completeTransition = () => {
                if (!callbackTriggered) {
                    callbackTriggered = true;
                    if (typeof onComplete === 'function') {
                        try {
                            onComplete();
                        } catch (e) {
                            console.warn('[IRA] Navigation callback note:', e);
                        }
                    }
                }
            };

            // Bounded safety fallback timeout (150ms) ensures navigation NEVER hangs
            const safetyTimeout = setTimeout(completeTransition, 150);

            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtx) {
                    clearTimeout(safetyTimeout);
                    completeTransition();
                    return;
                }

                if (!this.audioCtx || this.audioCtx.state === 'closed') {
                    this.audioCtx = new AudioCtx();
                }
                if (this.audioCtx.state === 'suspended') {
                    this.audioCtx.resume().catch((err) => {
                        console.warn('[IRA] AudioContext resume note:', err);
                    });
                }

                const now = this.audioCtx.currentTime;
                const osc = this.audioCtx.createOscillator();
                const gain = this.audioCtx.createGain();

                osc.type = 'sine';
                osc.frequency.setValueAtTime(523.25, now);
                osc.frequency.exponentialRampToValueAtTime(783.99, now + 0.08);

                gain.gain.setValueAtTime(0.0001, now);
                gain.gain.linearRampToValueAtTime(0.14, now + 0.02);
                gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.12);

                osc.connect(gain);
                gain.connect(this.audioCtx.destination);

                osc.onended = () => {
                    clearTimeout(safetyTimeout);
                    completeTransition();
                };

                osc.start(now);
                osc.stop(now + 0.13);
            } catch (err) {
                console.warn('[IRA] Navigation chime skipped:', err);
                clearTimeout(safetyTimeout);
                completeTransition();
            }
        },

        /**
         * Initialize Web Speech Recognition
         */
        initSpeechRecognition: function () {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            if (!SpeechRecognition) {
                console.warn('[IRA] SpeechRecognition API not supported in this browser.');
                return;
            }

            this.recognition = new SpeechRecognition();
            this.recognition.continuous = true;
            this.recognition.interimResults = true;
            this.recognition.lang = this.selectedLocale;

            this.recognition.onstart = () => {
                this.clearStartingTimer();
                this.isRecognitionStarting = false;
                this.isRecognitionRunning = true;
                this.sessionFinalTranscript = '';
                this.sessionInterimTranscript = '';
                this.hasDispatchedTurn = false;
                this.clearSilenceTimer();
                this.clearMaxListeningTimer();
                this.armMaxListeningTimer();
                this.setVoiceState('LISTENING');
            };

            this.recognition.onresult = (event) => {
                let interim = '';
                let finals = '';
                for (let i = 0; i < event.results.length; i++) {
                    const resultItem = event.results[i];
                    if (resultItem.isFinal) {
                        finals += resultItem[0].transcript;
                    } else {
                        interim += resultItem[0].transcript;
                    }
                }
                if (finals.trim()) {
                    this.sessionFinalTranscript = finals.trim();
                }
                this.sessionInterimTranscript = interim.trim();
                const displayText = (this.sessionFinalTranscript + ' ' + interim).trim();
                if (displayText) {
                    this.setUserUtterance(displayText);
                    this.resetSilenceTimer();
                }
            };

            this.recognition.onerror = (event) => {
                this.clearStartingTimer();
                this.isRecognitionRunning = false;
                this.isRecognitionStarting = false;
                this.clearSilenceTimer();
                console.warn('[IRA] Speech recognition note:', event.error);

                if (this.isAbortedByUser || event.error === 'aborted') {
                    this.clearMaxListeningTimer();
                    this.sessionFinalTranscript = '';
                    this.sessionInterimTranscript = '';
                    this.setVoiceState('READY');
                    return;
                }

                if (event.error === 'not-allowed' || event.error === 'service-not-allowed') {
                    this.clearMaxListeningTimer();
                    this.sessionFinalTranscript = '';
                    this.sessionInterimTranscript = '';
                    this.hasDispatchedTurn = true;
                    this.setVoiceState('ERROR', 'Microphone access denied. Please allow microphone permissions.');
                } else if (event.error === 'audio-capture') {
                    this.clearMaxListeningTimer();
                    this.sessionFinalTranscript = '';
                    this.sessionInterimTranscript = '';
                    this.hasDispatchedTurn = true;
                    this.setVoiceState('ERROR', 'No microphone detected. Please plug in a microphone.');
                } else if (event.error === 'network') {
                    this.clearMaxListeningTimer();
                    this.sessionFinalTranscript = '';
                    this.sessionInterimTranscript = '';
                    this.hasDispatchedTurn = true;
                    this.setVoiceState('ERROR', 'Speech network connection issue. Tap microphone to retry.');
                } else if (event.error === 'no-speech') {
                    // Chrome VAD silence cutoff: keep LISTENING state intact so onend can re-arm
                    return;
                } else if (event.error !== 'aborted') {
                    this.clearMaxListeningTimer();
                    this.sessionFinalTranscript = '';
                    this.sessionInterimTranscript = '';
                    this.hasDispatchedTurn = true;
                    this.setVoiceState('ERROR', 'Microphone note: ' + event.error + '. Tap to retry.');
                } else {
                    this.clearMaxListeningTimer();
                    this.setVoiceState('READY');
                }
            };

            this.recognition.onend = () => {
                this.clearStartingTimer();
                this.isRecognitionRunning = false;
                this.isRecognitionStarting = false;
                this.clearSilenceTimer();
                const wasAborted = this.isAbortedByUser;
                this.isAbortedByUser = false;

                // CRITICAL FIX: Only use text spoken during THIS active session.
                // Never fall back to stale userText from prior turns!
                const textToProcess = (this.sessionFinalTranscript || this.sessionInterimTranscript || '').trim();

                if (!wasAborted && !this.hasDispatchedTurn && this.currentState === 'LISTENING') {
                    if (textToProcess) {
                        this.clearMaxListeningTimer();
                        this.hasDispatchedTurn = true;
                        this.sessionFinalTranscript = '';
                        this.sessionInterimTranscript = '';
                        this.handleSpokenTurn(textToProcess);
                    } else {
                        // Extended listening: Chrome VAD terminates after ~5s of silence.
                        // Automatically re-arm up to 3 times or until max listening timeout to give user ample time to speak.
                        const elapsed = Date.now() - (this.sessionStartTime || 0);
                        const maxDuration = this.config.maxListeningDurationMs || 25000;
                        if ((this.emptyRestartCount || 0) < 3 && elapsed < (maxDuration - 2500)) {
                            this.emptyRestartCount = (this.emptyRestartCount || 0) + 1;
                            console.log('[IRA] Recognition quiet end at ' + elapsed + 'ms. Re-arming listener (' + this.emptyRestartCount + '/3)...');
                            try {
                                this.isRecognitionStarting = true;
                                this.recognition.start();
                                return;
                            } catch (err) {
                                console.warn('[IRA] Recognition re-arm note:', err);
                                this.isRecognitionStarting = false;
                            }
                        }
                        this.clearMaxListeningTimer();
                        this.setVoiceState('READY');
                    }
                } else if (this.currentState === 'LISTENING') {
                    this.clearMaxListeningTimer();
                    this.setVoiceState('READY');
                }
            };
        },

        /**
         * Reset silence detection timer whenever user speaks
         */
        resetSilenceTimer: function () {
            this.clearSilenceTimer();
            const currentText = (this.sessionFinalTranscript || this.sessionInterimTranscript || '').trim();
            if (!currentText || this.hasDispatchedTurn || this.currentState !== 'LISTENING') {
                return;
            }

            const threshold = this.config.silenceThresholdMs || 1800;
            this.silenceTimer = setTimeout(() => {
                if (this.currentState === 'LISTENING' && !this.hasDispatchedTurn) {
                    console.log('[IRA] Silence detected (' + threshold + 'ms). Finalizing turn.');
                    this.finalizeAndDispatchTurn();
                }
            }, threshold);
        },

        clearSilenceTimer: function () {
            if (this.silenceTimer) {
                clearTimeout(this.silenceTimer);
                this.silenceTimer = null;
            }
        },

        armMaxListeningTimer: function () {
            this.clearMaxListeningTimer();
            const maxDuration = this.config.maxListeningDurationMs || 25000;
            this.maxListeningTimer = setTimeout(() => {
                if (this.currentState === 'LISTENING' && !this.hasDispatchedTurn) {
                    console.log('[IRA] Max listening duration reached (' + maxDuration + 'ms). Finalizing turn.');
                    this.finalizeAndDispatchTurn();
                }
            }, maxDuration);
        },

        clearMaxListeningTimer: function () {
            if (this.maxListeningTimer) {
                clearTimeout(this.maxListeningTimer);
                this.maxListeningTimer = null;
            }
        },

        /**
         * Finalize the recognized speech buffer and dispatch to backend
         */
        finalizeAndDispatchTurn: function () {
            this.clearSilenceTimer();
            this.clearMaxListeningTimer();

            if (this.hasDispatchedTurn || this.currentState !== 'LISTENING') {
                return;
            }

            const textToProcess = (this.sessionFinalTranscript || this.sessionInterimTranscript || '').trim();
            if (textToProcess) {
                this.hasDispatchedTurn = true;
                this.sessionFinalTranscript = '';
                this.sessionInterimTranscript = '';
                this.stopListening();
                this.handleSpokenTurn(textToProcess);
            } else {
                this.stopListening();
            }
        },

        /**
         * Initialize Web Speech Synthesis with Indian Voice Metadata Inspection
         */
        initSpeechSynthesis: function () {
            if (!('speechSynthesis' in window)) {
                console.warn('[IRA] SpeechSynthesis not supported in this browser.');
                return;
            }

            this.updateAvailableVoices();
            if (window.speechSynthesis.onvoiceschanged !== undefined) {
                window.speechSynthesis.onvoiceschanged = () => {
                    this.updateAvailableVoices();
                };
            }
        },

        /**
         * Inspect browser voices using language metadata
         */
        updateAvailableVoices: function () {
            if (!('speechSynthesis' in window)) return;
            const voices = window.speechSynthesis.getVoices() || [];
            if (!voices || voices.length === 0) return;

            this.availableVoices = voices;

            // 1. Locate best Hindi (hi-IN) voice
            let hiVoice = null;
            const hindiVoices = voices.filter(v => {
                const lang = (v.lang || '').replace('_', '-').toLowerCase();
                return lang === 'hi-in' || lang.startsWith('hi');
            });

            if (hindiVoices.length > 0) {
                hiVoice = hindiVoices.find(v => {
                    const n = (v.name || '').toLowerCase();
                    return n.includes('natural') || n.includes('swara') || n.includes('kalpana') || n.includes('हिन्दी') || n.includes('female');
                }) || hindiVoices[0];
            }

            // 2. Locate best Indian English (en-IN) voice
            let enInVoice = null;
            const indianEngVoices = voices.filter(v => {
                const lang = (v.lang || '').replace('_', '-').toLowerCase();
                return lang === 'en-in';
            });

            if (indianEngVoices.length > 0) {
                enInVoice = indianEngVoices.find(v => {
                    const n = (v.name || '').toLowerCase();
                    return n.includes('natural') || n.includes('neerja') || n.includes('heera') || n.includes('female');
                }) || indianEngVoices[0];
            }

            // 3. Fallback clean English voice
            let fallbackVoice = null;
            const generalEngVoices = voices.filter(v => {
                const lang = (v.lang || '').replace('_', '-').toLowerCase();
                return lang.startsWith('en');
            });

            if (generalEngVoices.length > 0) {
                fallbackVoice = generalEngVoices.find(v => {
                    const n = (v.name || '').toLowerCase();
                    return n.includes('female') || n.includes('zira') || n.includes('samantha') || n.includes('google us english');
                }) || generalEngVoices[0];
            }

            this.hindiVoice = hiVoice;
            this.indianEnglishVoice = enInVoice;
            this.fallbackEnglishVoice = fallbackVoice;
        },

        /**
         * Get the most appropriate voice and locale for a specific text utterance.
         */
        resolveSpeechVoiceAndLocale: function (text) {
            if (!this.availableVoices || this.availableVoices.length === 0) {
                this.updateAvailableVoices();
            }

            const clean = (text || '').trim();
            const hasDevanagari = /[\u0900-\u097F]/u.test(clean);
            const hasHinglish = /\b(hai|hain|karein|karo|kar\s+do|chahiye|aapki|aapka|humare|hamare|paas|mein|ke\s+liye|hoga|hogi|batao|dikhao|kripya|uplabdh|sahi|theek|pehli|agli|namaste|shukriya|kaun|kaunse|saare|sabhi)\b/i.test(clean);

            let targetLocale = 'en-IN';
            let chosenVoice = null;

            if (hasDevanagari || (this.selectedLocale === 'hi-IN' && (hasHinglish || !/^[A-Za-z0-9\s.,!?'"()-]+$/.test(clean))) || hasHinglish) {
                targetLocale = 'hi-IN';
                chosenVoice = this.hindiVoice || this.indianEnglishVoice || this.fallbackEnglishVoice;
                if (!chosenVoice && this.availableVoices && this.availableVoices.length > 0) {
                    chosenVoice = this.availableVoices[0];
                }
            } else if (this.selectedLocale === 'en-US') {
                targetLocale = 'en-US';
                chosenVoice = (this.availableVoices || []).find(v => {
                    const l = (v.lang || '').replace('_', '-').toLowerCase();
                    return l === 'en-us' && (v.name.toLowerCase().includes('female') || v.name.toLowerCase().includes('zira'));
                }) || this.fallbackEnglishVoice || (this.availableVoices && this.availableVoices[0]);
            } else {
                targetLocale = 'en-IN';
                chosenVoice = this.indianEnglishVoice || this.fallbackEnglishVoice || (this.availableVoices && this.availableVoices[0]);
            }

            // CRITICAL FIX: SAPI Voice/Language Mismatch Prevention
            // If chosen voice is an English fallback voice, utterance.lang MUST match the voice's supported language.
            // Setting utterance.lang = 'hi-IN' on an English voice causes Windows/Chromium SAPI to abort synthesis with
            // 'language-unavailable' or silent failure!
            let effectiveLocale = targetLocale;
            if (chosenVoice && chosenVoice.lang) {
                const voiceLang = chosenVoice.lang.replace('_', '-');
                effectiveLocale = voiceLang;
            }

            return { voice: chosenVoice, locale: effectiveLocale };
        },

        /**
         * Bind UI Events
         */
        bindEvents: function () {
            // Main IRA Voice AI floating button -> Open modal and start voice listening
            if (this.elements.floatingBtn) {
                this.elements.floatingBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    if (this.currentState === 'LISTENING') {
                        // User tapped while listening -> finalize recognized speech
                        this.finalizeAndDispatchTurn();
                    } else if (this.currentState === 'SPEAKING') {
                        this.stopSpeaking();
                    } else {
                        // Open modal and start voice listening
                        this.openModalAndStartListening();
                    }
                });
            }

            // Dedicated Expand Button -> Open modal and start voice listening once visible
            if (this.elements.floatingExpandBtn) {
                this.elements.floatingExpandBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    this.openModalAndStartListening();
                });
            }

            // Central Orb Button -> Toggle Voice Listening
            if (this.elements.orbBtn) {
                this.elements.orbBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (this.currentState === 'LISTENING') {
                        // User tapped Orb while listening -> immediately finalize recognized speech
                        this.finalizeAndDispatchTurn();
                    } else if (this.currentState === 'SPEAKING') {
                        this.stopSpeaking();
                    } else {
                        this.startListening();
                    }
                });
            }

            // Stop Button -> Explicit user cancel
            if (this.elements.stopBtn) {
                this.elements.stopBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.isAbortedByUser = true;
                    this.stopAll();
                });
            }

            // Language Select dropdown
            if (this.elements.langSelect) {
                this.elements.langSelect.addEventListener('change', (e) => {
                    this.selectedLocale = e.target.value;
                    try {
                        localStorage.setItem('ira_preferred_locale', this.selectedLocale);
                    } catch (err) {}

                    if (this.recognition) {
                        this.recognition.lang = this.selectedLocale;
                    }

                    console.log('[IRA] Recognition locale updated:', this.selectedLocale);
                });
            }

            // Fullscreen Toggle Button
            if (this.elements.expandBtn) {
                this.elements.expandBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.toggleFullscreen();
                });
            }

            // Close Button
            if (this.elements.closeBtn) {
                this.elements.closeBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.isAbortedByUser = true;
                    this.stopAll();
                    this.closeModal();
                });
            }

            // Listen to other sidebar / trigger buttons with .ira-open-assistant-trigger
            document.querySelectorAll('.ira-open-assistant-trigger').forEach((trigger) => {
                if (trigger === this.elements.floatingExpandBtn) return;
                trigger.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.openModalAndStartListening();
                });
            });

            // Handle bootstrap modal events
            if (this.elements.modal) {
                // When modal closes via backdrop or Esc
                this.elements.modal.addEventListener('hidden.bs.modal', () => {
                    if (this.formFillMode) {
                        return;
                    }
                    this.isAbortedByUser = true;
                    this.stopAll();
                    this.clearFormFillMode();
                    document.body.classList.remove('ira-panel-open');
                });

                if (window.$) {
                    $(this.elements.modal).on('hidden.bs.modal', () => {
                        if (this.formFillMode) {
                            return;
                        }
                        this.isAbortedByUser = true;
                        this.stopAll();
                        this.clearFormFillMode();
                        document.body.classList.remove('ira-panel-open');
                    });
                }
            }

            // Stop voice on beforeunload to avoid background noise
            window.addEventListener('beforeunload', () => {
                this.stopAll();
            });
        },

        /**
         * Start Speech Recognition
         */
        startListening: function () {
            // Visual panel must be visible whenever speech listening starts
            this.openModal();

            if (!this.recognition) {
                this.setVoiceState('ERROR', 'Voice recognition is not available in this browser. Please use Chrome or Edge.');
                this.setAiUtterance('Voice recognition is not supported in this browser. Please use Google Chrome or Microsoft Edge.');
                return;
            }

            if (this.isRecognitionRunning || this.isRecognitionStarting) {
                return;
            }

            this.stopSpeaking();
            this.clearAutoListenTimeout();
            this.clearSilenceTimer();
            this.clearMaxListeningTimer();
            this.clearStartingTimer();
            this.isAbortedByUser = false;
            this.hasDispatchedTurn = false;
            this.emptyRestartCount = 0;
            this.sessionStartTime = Date.now();
            this.sessionFinalTranscript = '';
            this.sessionInterimTranscript = '';

            // Immediate visible status while microphone initialization is in progress
            this.setVoiceState('STARTING', 'Starting microphone...');

            try {
                this.playListeningCue();
                this.isRecognitionStarting = true;
                this.recognition.lang = this.selectedLocale;

                // Safety timeout: if browser permission prompt is dismissed or audio device fails without onerror
                this.startingTimer = setTimeout(() => {
                    if (this.isRecognitionStarting && this.currentState === 'STARTING') {
                        this.isRecognitionStarting = false;
                        console.warn('[IRA] Microphone initialization timed out.');
                        this.setVoiceState('ERROR', 'Microphone initialization timed out. Please check permissions and tap microphone.');
                    }
                }, 8000);

                this.recognition.start();
                // Visible status will transition to 'LISTENING' inside this.recognition.onstart
                // once speech recognition hardware connection is actually established.
            } catch (err) {
                this.clearStartingTimer();
                this.isRecognitionStarting = false;
                if (err.name === 'InvalidStateError') {
                    console.log('[IRA] Recognition already starting or active');
                } else {
                    console.warn('[IRA] Recognition start error:', err);
                    this.setVoiceState('ERROR', 'Could not start microphone: ' + (err.message || 'Please check mic permissions.'));
                }
            }
        },

        /**
         * Stop Speech Recognition
         */
        stopListening: function () {
            this.clearStartingTimer();
            this.clearSilenceTimer();
            this.clearMaxListeningTimer();
            this.clearAutoListenTimeout();
            if (this.recognition && this.isRecognitionRunning) {
                try {
                    this.recognition.stop();
                } catch (e) {}
            }
            this.isRecognitionRunning = false;
            this.isRecognitionStarting = false;
            if (this.currentState === 'LISTENING' || this.currentState === 'STARTING') {
                this.setVoiceState('READY');
            }
        },

        /**
         * Stop Speech Synthesis
         */
        stopSpeaking: function () {
            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel();
            }
            this.activeUtterance = null;
            if (this.currentState === 'SPEAKING') {
                this.setVoiceState('READY');
            }
        },

        /**
         * Stop All Voice Activity
         */
        stopAll: function () {
            this.clearStartingTimer();
            this.clearSilenceTimer();
            this.clearMaxListeningTimer();
            this.clearAutoListenTimeout();
            this.stopListening();
            this.stopSpeaking();
            this.setVoiceState('READY');
        },

        clearStartingTimer: function () {
            if (this.startingTimer) {
                clearTimeout(this.startingTimer);
                this.startingTimer = null;
            }
        },

        clearAutoListenTimeout: function () {
            if (this.autoListenTimeout) {
                clearTimeout(this.autoListenTimeout);
                this.autoListenTimeout = null;
            }
        },

        /**
         * Handle a finalized spoken turn
         */
        handleSpokenTurn: function (spokenText) {
            const clean = (spokenText || '').trim();
            if (!clean) {
                this.setVoiceState('READY');
                return;
            }

            this.setUserUtterance(clean);

            // Phase 7 Hotfix: Route active form session directly to form-step endpoint
            if (this.formFillMode && this.activeFormKey) {
                this.sendFormStep(clean, this.activeFormKey);
            } else {
                this.processIntentTurn(clean);
            }
        },

        /**
         * Process Intent Turn via POST /ira/intent
         */
        processIntentTurn: async function (spokenText) {
            this.setVoiceState('THINKING');

            const intentEndpoint = this.config.intentEndpoint || '/ira/intent';
            const currentPage = window.location.pathname;

            // Phase 7: Detect active page form and scrape non-sensitive fields
            const activeForm = this.detectActiveForm();
            const currentFields = activeForm ? this.scrapeCurrentFormFields(activeForm.formEl) : {};
            const contextPayload = Object.assign({}, this.recentContext || {}, {
                active_form_key: activeForm ? activeForm.key : null,
                current_fields: currentFields
            });

            try {
                const response = await fetch(intentEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.config.csrfToken
                    },
                    body: JSON.stringify({
                        speech: spokenText,
                        current_page: currentPage,
                        current_mode: this.mode,
                        context: contextPayload
                    })
                });

                if (!response.ok) {
                    throw new Error('Intent resolution HTTP ' + response.status);
                }

                const result = await response.json();
                const speechToSay = result.speech || "Theek hai, main samajh gayi.";

                // Update recent conversation context
                if (result.context && typeof result.context === 'object' && Object.keys(result.context).length > 0) {
                    this.recentContext = Object.assign({}, result.context, {
                        last_intent: result.intent,
                        last_destination: result.destination_key || null,
                        last_utterance: spokenText
                    });
                } else if (result.intent === 'navigate' || result.intent === 'cancel') {
                    this.recentContext = {};
                } else {
                    this.recentContext = {
                        last_intent: result.intent,
                        last_destination: result.destination_key || null,
                        last_utterance: spokenText
                    };
                }

                // Display response in transcript
                this.setAiUtterance(speechToSay);

                // Render visible interactive page list / cards for section discovery or options
                this.renderVisualCard(result);

                // Handle Close Assistant Intent
                if (result.intent === 'close_assistant') {
                    this.recentContext = {};
                    this.speak(speechToSay, () => {
                        this.stopAll();
                        this.closeModal();
                    });
                    return true;
                }

                // Handle Go Back Intent
                if (result.intent === 'go_back') {
                    if (window.history.length > 1) {
                        if (this.elements.floatingLabel) {
                            this.elements.floatingLabel.textContent = 'Going back...';
                        }
                        if (this.elements.actionBanner && this.elements.actionBannerText) {
                            this.elements.actionBanner.classList.remove('d-none');
                            this.elements.actionBannerText.textContent = 'Pichhe ja rahe hain...';
                        }
                        this.speak(speechToSay, () => {
                            this.playNavigationCue(() => {
                                window.history.back();
                            });
                        });
                    } else {
                        const noHistoryMsg = (this.selectedLocale && this.selectedLocale.startsWith('en'))
                            ? "Previous page is not available."
                            : "Pichhla page available nahi hai.";
                        this.setAiUtterance(noHistoryMsg);
                        this.speak(noHistoryMsg);
                    }
                    return true;
                }

                // Handle Go Forward Intent
                if (result.intent === 'go_forward') {
                    if (this.elements.floatingLabel) {
                        this.elements.floatingLabel.textContent = 'Going forward...';
                    }
                    if (this.elements.actionBanner && this.elements.actionBannerText) {
                        this.elements.actionBanner.classList.remove('d-none');
                        this.elements.actionBannerText.textContent = 'Aage ja rahe hain...';
                    }
                    this.speak(speechToSay, () => {
                        this.playNavigationCue(() => {
                            window.history.forward();
                        });
                    });
                    return true;
                }

                // Handle Logout Confirmation Prompt Intent
                if (result.intent === 'logout_prompt') {
                    this.speak(speechToSay, () => {
                        this.autoListenTimeout = setTimeout(() => {
                            this.startListening();
                        }, 400);
                    });
                    return true;
                }

                // Handle Cancel Intent
                if (result.intent === 'cancel') {
                    this.recentContext = {};
                    this.speak(speechToSay);
                    return true;
                }

                // Handle Clarification Question
                if (result.intent === 'clarify') {
                    this.speak(speechToSay, () => {
                        // Wait for user's clarification answer
                        this.autoListenTimeout = setTimeout(() => {
                            this.startListening();
                        }, 400);
                    });
                    return true;
                }

                // Phase 7: Handle Form Filling Intent
                if (result.intent === 'form_fill') {
                    if (Array.isArray(result.form_updates) && result.form_updates.length > 0) {
                        this.applyFormUpdates(result.form_updates, result.form_selector);
                    }
                    this.speak(speechToSay);
                    return true;
                }

                // Handle Navigation Intent
                if (result.intent === 'navigate' && result.target_url) {
                    const destinationName = result.destination_key || 'destination';
                    let isSamePage = false;
                    let isSafeUrl = false;

                    try {
                        const targetObj = new URL(result.target_url, window.location.origin);
                        // Security check: Must strictly stay on current application origin
                        if (targetObj.origin === window.location.origin) {
                            isSafeUrl = true;
                            isSamePage = (targetObj.pathname === window.location.pathname);
                        }
                    } catch (e) {
                        isSafeUrl = false;
                    }

                    if (!isSafeUrl) {
                        console.warn('[IRA] Blocked navigation to unauthorized external or invalid URL:', result.target_url);
                        this.speak("Safety note: Navigation to that external URL was blocked.");
                        return false;
                    }

                    if (this.elements.actionBanner && this.elements.actionBannerText) {
                        this.elements.actionBanner.classList.remove('d-none');
                        this.elements.actionBannerText.textContent = 'Navigating to: ' + destinationName;
                    }
                    if (this.elements.floatingLabel) {
                        this.elements.floatingLabel.textContent = 'Navigating...';
                    }

                    if (isSamePage) {
                        // Already on this page; speak response naturally without unloading
                        this.speak(speechToSay);
                    } else {
                        // Cross-page navigation: play synchronized chime and transition
                        this.playNavigationCue(() => {
                            window.location.href = result.target_url;
                        });
                    }
                    return true;
                }

                // Handle Unauthorized Intent
                if (result.intent === 'unauthorized') {
                    this.speak(speechToSay, () => {
                        if (result.target_url) {
                            setTimeout(() => {
                                window.location.href = result.target_url;
                            }, 1200);
                        }
                    });
                    return true;
                }

                // General conversational question or fallback
                this.speak(speechToSay);
                return true;

            } catch (err) {
                console.error('[IRA] Intent turn error:', err);
                const fallbackMsg = "Kripya connection check karein aur punah prayas karein.";
                this.setAiUtterance(fallbackMsg);
                this.speak(fallbackMsg);
                this.setVoiceState('ERROR', 'Connection issue. Tap mic to retry.');

                // Visible error card with retry button
                if (this.elements.cardContainer) {
                    this.elements.cardContainer.classList.remove('d-none');
                    this.elements.cardContainer.innerHTML = `
                        <div class="alert alert-danger py-2 px-3 small d-flex align-items-center justify-content-between mb-0" role="alert">
                            <div class="d-flex align-items-center gap-2">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" class="flex-shrink-0" aria-hidden="true">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                                </svg>
                                <span>Network request failed. Tap retry to submit again.</span>
                            </div>
                            <button type="button" class="btn btn-sm btn-danger py-0 px-2 flex-shrink-0" id="iraRetryTurnBtn">Retry</button>
                        </div>
                    `;
                    const retryBtn = document.getElementById('iraRetryTurnBtn');
                    if (retryBtn) {
                        retryBtn.addEventListener('click', () => {
                            this.processIntentTurn(spokenText);
                        });
                    }
                    this.scrollTranscriptToBottom();
                }
                return false;
            }
        },

        /**
         * Centralized Speech Synthesis Output (Chromium GC & SAPI Mismatch Resistant)
         */
        speak: function (text, onComplete) {
            this.scrollTranscriptToBottom();
            const cleanText = (text || '').trim();
            if (!cleanText) {
                this.setVoiceState('READY');
                if (typeof onComplete === 'function') onComplete();
                return;
            }

            if (!('speechSynthesis' in window)) {
                console.warn('[IRA] SpeechSynthesis is not supported in this browser.');
                this.setVoiceState('READY');
                if (typeof onComplete === 'function') onComplete();
                return;
            }

            // Unpause if suspended
            try {
                if (window.speechSynthesis.paused) {
                    window.speechSynthesis.resume();
                }
            } catch (e) {}

            // If already synthesizing, cancel cleanly
            let wasSpeaking = false;
            if (window.speechSynthesis.speaking || window.speechSynthesis.pending) {
                wasSpeaking = true;
                try {
                    window.speechSynthesis.cancel();
                } catch (e) {}
            }

            const { voice, locale } = this.resolveSpeechVoiceAndLocale(cleanText);

            const utterance = new SpeechSynthesisUtterance(cleanText);
            utterance.rate = this.config.voiceRate || 1.0;
            utterance.pitch = this.config.voicePitch || 1.02;
            utterance.lang = locale;
            if (voice) {
                utterance.voice = voice;
            }

            // Keep reference to prevent V8 GC mid-speech
            window.__iraActiveUtterance = utterance;
            this.activeUtterance = utterance;

            let completed = false;
            const finish = () => {
                if (!completed) {
                    completed = true;
                    this.activeUtterance = null;
                    window.__iraActiveUtterance = null;
                    if (this.currentState === 'SPEAKING') {
                        this.setVoiceState('READY');
                    }
                    if (typeof onComplete === 'function') {
                        try {
                            onComplete();
                        } catch (e) {
                            console.warn('[IRA] Speak completion callback note:', e);
                        }
                    }
                }
            };

            utterance.onstart = () => {
                this.setVoiceState('SPEAKING');
            };

            utterance.onend = finish;
            utterance.onerror = (e) => {
                console.warn('[IRA] Speech synthesis error note:', e.error || e);
                finish();
            };

            // Chromium GC heartbeat: periodic resume to prevent stall on longer responses
            const resumeInterval = setInterval(() => {
                if (!window.speechSynthesis.speaking) {
                    clearInterval(resumeInterval);
                } else {
                    window.speechSynthesis.resume();
                }
            }, 2500);

            // Safe dispatch window (40ms if cancelled, 0ms otherwise) to avoid Chromium cancel race condition
            const dispatchDelay = wasSpeaking ? 40 : 0;
            setTimeout(() => {
                try {
                    window.speechSynthesis.speak(utterance);
                } catch (err) {
                    console.warn('[IRA] window.speechSynthesis.speak failed:', err);
                    finish();
                }
            }, dispatchDelay);
        },

        /**
         * Update UI Voice State
         */
        setVoiceState: function (state, message) {
            this.currentState = state;

            // Status label mapping
            const labelMap = {
                READY: 'Ready',
                STARTING: 'Starting...',
                LISTENING: 'Listening...',
                THINKING: 'Thinking...',
                SPEAKING: 'Speaking...',
                ERROR: 'Notice'
            };

            // Update Header Status Pill
            if (this.elements.statusPill) {
                const pillClass = state === 'STARTING' ? 'thinking' : state.toLowerCase();
                this.elements.statusPill.className = 'ira-status-pill ' + pillClass;
            }
            if (this.elements.statusLabel) {
                this.elements.statusLabel.textContent = message || labelMap[state] || state;
            }

            // Update Floating Button States
            if (this.elements.floatingBtn) {
                const floatClass = state === 'STARTING' ? 'thinking' : state.toLowerCase();
                this.elements.floatingBtn.className = 'ira-floating-btn ' + floatClass;
            }
            if (this.elements.floatingLabel) {
                this.elements.floatingLabel.textContent = labelMap[state] ? 'IRA ' + labelMap[state] : 'IRA Voice AI';
            }

            // Update Central Orb Button (State classes animate gradient & glowing pulse rings)
            if (this.elements.orbBtn) {
                const orbClass = state === 'STARTING' ? 'thinking' : state.toLowerCase();
                this.elements.orbBtn.className = 'ira-orb-btn ' + orbClass;
            }

            // Update Waveform Visualizer
            if (this.elements.visualizer) {
                if (state === 'LISTENING' || state === 'SPEAKING' || state === 'STARTING') {
                    this.elements.visualizer.classList.add('active');
                } else {
                    this.elements.visualizer.classList.remove('active');
                }
            }

            // Update Headings
            if (this.elements.mainHeading) {
                if (state === 'STARTING') {
                    this.elements.mainHeading.textContent = message || 'Starting microphone...';
                } else if (state === 'LISTENING') {
                    this.elements.mainHeading.textContent = 'Listening...';
                } else if (state === 'THINKING') {
                    this.elements.mainHeading.textContent = 'Thinking...';
                } else if (state === 'SPEAKING') {
                    this.elements.mainHeading.textContent = 'Speaking...';
                } else if (state === 'ERROR') {
                    this.elements.mainHeading.textContent = 'Notice';
                } else {
                    this.elements.mainHeading.textContent = 'Tap to Speak';
                }
            }
            if (this.elements.subHeading) {
                if (state === 'ERROR' && message) {
                    this.elements.subHeading.textContent = message;
                } else if (state === 'READY') {
                    this.elements.subHeading.innerHTML = 'Say &ldquo;Dashboard kholo&rdquo;, &ldquo;Open Staking&rdquo;, or &ldquo;Show Wallet&rdquo;';
                }
            }
        },

        /**
         * Render Visible Section Pages List or Option Cards in the UI
         */
        renderVisualCard: function (result) {
            if (!this.elements.cardContainer) return;

            // 1. Section Discovery Pages List (Preserves exact registered ordering)
            if (result.section_discovery || (result.context && result.context.pending_options)) {
                const discovery = result.section_discovery || {};
                const options = discovery.options || (result.context && result.context.pending_options) || {};
                const pages = discovery.pages || Object.keys(options);
                const sectionName = discovery.section_name || result.context.pending_section || 'Section';

                if (pages.length > 0) {
                    const ordinalHints = ['Pehla wala', 'Doosra wala', 'Teesra wala', 'Chautha wala', 'Panchva wala'];

                    let html = `
                        <div class="ira-section-card">
                            <div class="ira-section-card-header">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="ira-section-card-icon" aria-hidden="true">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <rect x="3" y="3" width="7" height="7"></rect>
                                            <rect x="14" y="3" width="7" height="7"></rect>
                                            <rect x="14" y="14" width="7" height="7"></rect>
                                            <rect x="3" y="14" width="7" height="7"></rect>
                                        </svg>
                                    </span>
                                    <strong>${this.escapeHtml(sectionName)} Pages</strong>
                                </div>
                                <span class="badge bg-light text-primary border" style="font-size: 0.7rem;">${pages.length} Pages</span>
                            </div>
                            <div class="ira-page-list">
                    `;

                    pages.forEach((pageName, idx) => {
                        const hint = ordinalHints[idx] ? `Say "${ordinalHints[idx]}" or tap to open` : `Say "${pageName}" or tap to open`;
                        html += `
                            <div class="ira-page-item" data-page-name="${this.escapeHtml(pageName)}" role="button" tabindex="0">
                                <div class="d-flex align-items-center gap-2 min-w-0">
                                    <span class="ira-page-num">${idx + 1}</span>
                                    <div class="min-w-0">
                                        <div class="ira-page-name">${this.escapeHtml(pageName)}</div>
                                        <small class="ira-page-hint">${hint}</small>
                                    </div>
                                </div>
                                <span class="ira-page-arrow" aria-hidden="true">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <polyline points="9 18 15 12 9 6"></polyline>
                                    </svg>
                                </span>
                            </div>
                        `;
                    });

                    html += `
                            </div>
                        </div>
                    `;

                    this.elements.cardContainer.innerHTML = html;
                    this.elements.cardContainer.classList.remove('d-none');

                    // Bind click handlers to page items for immediate voice/touch navigation
                    this.elements.cardContainer.querySelectorAll('.ira-page-item').forEach((item) => {
                        const pageName = item.getAttribute('data-page-name');
                        item.addEventListener('click', () => {
                            this.setUserUtterance(pageName);
                            this.processIntentTurn(pageName);
                        });
                        item.addEventListener('keydown', (e) => {
                            if (e.key === 'Enter' || e.key === ' ') {
                                e.preventDefault();
                                this.setUserUtterance(pageName);
                                this.processIntentTurn(pageName);
                            }
                        });
                    });

                    this.scrollTranscriptToBottom();
                    return;
                }
            }

            // 2. Clarification Option Chips (e.g. Income choice, Wallet choice)
            if (result.options && Array.isArray(result.options) && result.options.length > 0) {
                let html = `
                    <div class="ira-section-card">
                        <div class="ira-section-card-header">
                            <strong>Suggested Options:</strong>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mt-1">
                `;

                result.options.forEach((opt) => {
                    html += `
                        <button type="button" class="btn btn-sm btn-outline-primary ira-option-chip" data-choice="${this.escapeHtml(opt)}">
                            ${this.escapeHtml(opt)}
                        </button>
                    `;
                });

                html += `
                        </div>
                    </div>
                `;

                this.elements.cardContainer.innerHTML = html;
                this.elements.cardContainer.classList.remove('d-none');

                this.elements.cardContainer.querySelectorAll('.ira-option-chip').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        const choice = btn.getAttribute('data-choice');
                        this.setUserUtterance(choice);
                        this.processIntentTurn(choice);
                    });
                });

                this.scrollTranscriptToBottom();
                return;
            }

            // 3. Unauthorized Warning Card
            if (result.intent === 'unauthorized') {
                this.elements.cardContainer.innerHTML = `
                    <div class="alert alert-warning py-2 px-3 small d-flex align-items-center gap-2 mb-0">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" class="text-warning flex-shrink-0" aria-hidden="true">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                        </svg>
                        <div>
                            <strong>Access Restricted:</strong> ${this.escapeHtml(result.speech || 'You do not have permission to view this page.')}
                        </div>
                    </div>
                `;
                this.elements.cardContainer.classList.remove('d-none');
                this.scrollTranscriptToBottom();
                return;
            }

            // Otherwise, clear card container
            this.elements.cardContainer.innerHTML = '';
            this.elements.cardContainer.classList.add('d-none');
        },

        escapeHtml: function (str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        },

        setUserUtterance: function (text) {
            if (this.elements.transcriptUser && this.elements.userText) {
                this.elements.userText.textContent = text;
                this.elements.transcriptUser.classList.remove('d-none');
                this.scrollTranscriptToBottom();
            }
        },

        setAiUtterance: function (text) {
            if (this.elements.aiText) {
                this.elements.aiText.textContent = text;
                if (this.elements.transcriptAi) {
                    this.elements.transcriptAi.classList.remove('d-none');
                }
                this.scrollTranscriptToBottom();
            }
        },

        scrollTranscriptToBottom: function () {
            if (this.elements.transcriptArea) {
                this.elements.transcriptArea.scrollTop = this.elements.transcriptArea.scrollHeight;
            }
        },

        /**
         * Phase 7: Comprehensive Registry of All Eligible Application Forms
         */
        getFormRules: function () {
            return [
                // Member Portal Forms
                { key: 'member_wallet_withdrawal', selector: 'form[action*="initWithdrawal"]', title: 'Withdrawal Form' },
                { key: 'member_p2p_transfer', selector: 'form[action*="fundTrans"]', title: 'P2P Transfer' },
                { key: 'member_wallet_to_p2p', selector: 'form[action*="walletP2pTransfer"]', title: 'Wallet to P2P Transfer' },
                { key: 'member_staking', selector: '#stakingForm, form[action*="createStaking"]', title: 'Staking Package' },
                { key: 'member_fund_import', selector: '#myForm, form[action*="instantFunds"]', title: 'Deposit Funds' },
                { key: 'member_support_create', selector: 'form[action*="createTicket"]', title: 'Support Ticket' },
                { key: 'member_support_reply', selector: 'form[action*="MbReplyTicket"]', title: 'Ticket Reply' },
                { key: 'member_profile_user_summary', selector: 'form[action*="insertProfile"]', title: 'Profile Summary' },
                { key: 'member_profile_wallet_address', selector: 'form[action*="insertWalletAddress"]', title: 'Wallet Address' },
                { key: 'member_security_txn_password', selector: 'form[action*="txnpassChange"]', title: 'Transaction Password' },
                { key: 'member_security_login_password', selector: 'form[action*="passChange"]', title: 'Login Password' },
                { key: 'member_security_email', selector: 'form[action*="emailChange"]', title: 'Change Email' },
                { key: 'member_team_geneology_search', selector: 'form[action*="searchGenelogy"]', title: 'Geneology Search' },

                // Admin Portal Forms
                { key: 'admin_funds_add', selector: 'form[action*="addMemFunds"]', title: 'Add Member Funds' },
                { key: 'admin_funds_deduct', selector: 'form[action*="deductMemFunds"]', title: 'Deduct Member Funds' },
                { key: 'admin_set_rates', selector: 'form[action*="rateUpdate"]', title: 'Exchange Rates' },
                { key: 'admin_free_pin_account', selector: 'form[action*="PinUserAccount"]', title: 'Free Pin Account' },
                { key: 'admin_dash_messages', selector: 'form[action*="dashMsg"]', title: 'Dashboard Message' },
                { key: 'admin_wallet_address', selector: 'form[action*="updateWallet"]', title: 'Admin Wallet Address' },
                { key: 'admin_popup_image', selector: 'form[action*="popupImageUpdate"]', title: 'Popup Announcement' },
                { key: 'admin_achievers_image', selector: 'form[action*="acheivImage"]', title: 'Achievers Image' },
                { key: 'admin_withdrawal_accept_comment', selector: 'form[action*="withdrawal.accept"]', title: 'Withdrawal Accept' },
                { key: 'admin_withdrawal_cancel_comment', selector: 'form[action*="withdrawal.cancel"]', title: 'Withdrawal Reject' },
                { key: 'admin_date_filter', selector: '#advancedDateFilterForm', title: 'History Date Filter' },
                { key: 'admin_support_reply', selector: 'form[action*="Ticket"]', title: 'Admin Ticket Reply' },
                { key: 'admin_member_update', selector: 'form[action*="updateDetails"]', title: 'Member Profile Update' },
                { key: 'admin_member_security', selector: 'form[action*="securityUpdate"]', title: 'Member Security Override' },
                { key: 'admin_profile_email', selector: 'form[action*="changeEmail"]', title: 'Admin Email Update' },

                // Public / Guest Portal Forms
                { key: 'public_member_signup', selector: '#signupForm, form[action*="userRegister"]', title: 'Member Sign Up' },
                { key: 'public_member_signin', selector: 'form[action*="userLogin"]', title: 'Member Sign In' },
                { key: 'public_admin_login', selector: 'form[action*="adminLogin"]', title: 'Admin Login' },
                { key: 'public_member_forget_password', selector: 'form[action*="memretrivePassword"]', title: 'Recover Password' }
            ];
        },

        getFormRule: function (key) {
            return this.getFormRules().find(r => r.key === key) || null;
        },

        /**
         * Detect active registered form on current page
         */
        detectActiveForm: function () {
            if (this.activeFormKey) {
                const rule = this.getFormRule(this.activeFormKey);
                const el = this.activeFormEl || (rule ? document.querySelector(rule.selector) : null);
                if (el) {
                    return { key: this.activeFormKey, selector: rule ? rule.selector : '', formEl: el };
                }
            }

            const formRules = this.getFormRules();
            for (const rule of formRules) {
                const el = document.querySelector(rule.selector);
                if (el) {
                    return { key: rule.key, selector: rule.selector, formEl: el };
                }
            }
            return null;
        },

        /**
         * Mount visible AI Form-Fill button to every eligible registered form on the page
         */
        mountFormFillButtons: function () {
            const rules = this.getFormRules();

            rules.forEach(rule => {
                const formEl = document.querySelector(rule.selector);
                if (!formEl) return;

                // Check if button already exists in the DOM for this form
                let existingBtn = document.querySelector(`.ira-form-fill-btn[data-ira-form="${rule.key}"]`);
                if (existingBtn) {
                    this.bindFormFillButton(existingBtn, rule.key);
                    return;
                }

                if (formEl.dataset.iraFormMounted === 'true') return;

                // Create the visible button
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'ira-form-fill-btn';
                btn.setAttribute('data-ira-form', rule.key);
                btn.setAttribute('title', `Fill ${rule.title} with IRA Voice AI`);
                btn.innerHTML = `
                    <svg class="ira-form-fill-icon" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
                        <path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>
                    </svg>
                    <span>AI Fill Form</span>
                `;

                // Smart insertion into card header, panel heading, or top of form
                const parentPanel = formEl.closest('.card, .member-wallet-panel, .panel, .box');
                const header = parentPanel ? parentPanel.querySelector('.card-header, .card-title, .member-wallet-form__top, .box-header') : null;

                if (header) {
                    if (header.classList.contains('member-wallet-form__top')) {
                        header.appendChild(btn);
                    } else if (header.classList.contains('card-header') || header.classList.contains('box-header')) {
                        if (!header.classList.contains('d-flex')) {
                            header.classList.add('d-flex', 'align-items-center', 'justify-content-between', 'gap-2');
                        }
                        header.appendChild(btn);
                    } else {
                        header.parentNode.insertBefore(btn, header.nextSibling);
                    }
                } else {
                    const topHeading = formEl.querySelector('h2, h3, h4, h5, legend, .card-title');
                    if (topHeading) {
                        const wrapper = document.createElement('div');
                        wrapper.className = 'd-flex justify-content-between align-items-center mb-3 gap-2 flex-wrap';
                        topHeading.parentNode.insertBefore(wrapper, topHeading);
                        wrapper.appendChild(topHeading);
                        wrapper.appendChild(btn);
                    } else {
                        const container = document.createElement('div');
                        container.className = 'ira-form-fill-container';
                        container.appendChild(btn);
                        formEl.insertBefore(container, formEl.firstChild);
                    }
                }

                formEl.dataset.iraFormMounted = 'true';
                this.bindFormFillButton(btn, rule.key);
            });
        },

        bindFormFillButton: function (btn, formKey) {
            if (!btn || btn.dataset.iraBound === 'true') return;
            btn.dataset.iraBound = 'true';

            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.handleFormFillButtonClick(formKey);
            });
        },

        handleFormFillButtonClick: async function (formKey) {
            const rule = this.getFormRule(formKey);
            const formTitle = rule ? rule.title : 'Form';
            const formEl = rule ? document.querySelector(rule.selector) : null;

            this.activeFormKey = formKey;
            this.activeFormEl = formEl;
            this.formFillMode = true;

            // Apply non-obstructive compact dock styling
            if (this.elements.modal) {
                this.elements.modal.classList.add('ira-form-mode');
            }
            document.body.classList.add('ira-form-mode-active');

            // Display active form banner inside the assistant modal
            this.showActiveFormBanner(formTitle, formKey);

            // Open modal to show assistant UI
            this.openModal();

            const currentFields = this.scrapeCurrentFormFields(formEl);
            const endpoint = this.config.formEndpoint || '/ira/form-step';

            this.setVoiceState('THINKING');

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.config.csrfToken
                    },
                    body: JSON.stringify({
                        action: 'init',
                        form_key: formKey,
                        current_page: window.location.pathname,
                        current_fields: currentFields
                    })
                });

                if (!response.ok) {
                    throw new Error('Form init HTTP ' + response.status);
                }

                const result = await response.json();
                this.guideState = result.guide_state || null;

                const speech = result.speech || `Main ${formTitle} bharnay ke liye taiyyar hoon.`;
                this.setAiUtterance(speech);

                // Highlight active field if specified
                if (result.active_field && result.active_field.selector) {
                    const targetEl = formEl ? formEl.querySelector(result.active_field.selector) : document.querySelector(result.active_field.selector);
                    if (targetEl) {
                        targetEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        targetEl.classList.add('ira-field-highlight');
                        setTimeout(() => targetEl.classList.remove('ira-field-highlight'), 1800);
                    }
                }

                if (result.status === 'all_sensitive' || result.action === 'complete') {
                    this.speak(speech);
                    this.setVoiceState('READY');
                } else {
                    // Continuous conversational flow: speak prompt and listen when finished
                    this.speak(speech, () => {
                        this.startListening();
                    });
                }
            } catch (err) {
                console.error('[IRA] handleFormFillButtonClick error:', err);
                const fallbackGreeting = `Main ${formTitle} bharnay ke liye taiyyar hoon. Kripya boliye aap kya bharna chahte hain.`;
                this.setAiUtterance(fallbackGreeting);
                this.openModalAndStartListening();
            }
        },

        showActiveFormBanner: function (formTitle, formKey) {
            let banner = document.getElementById('iraActiveFormBanner');
            if (!banner && this.elements.transcriptArea) {
                banner = document.createElement('div');
                banner.id = 'iraActiveFormBanner';
                banner.className = 'ira-active-form-banner';
                this.elements.transcriptArea.parentNode.insertBefore(banner, this.elements.transcriptArea);
            }
            if (banner) {
                banner.innerHTML = `
                    <span><svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor" class="me-1" style="vertical-align:-1px;"><path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/><path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/></svg> Form Mode: <strong>${formTitle}</strong></span>
                    <button type="button" class="ira-exit-form-mode" title="Exit form mode">&times;</button>
                `;
                banner.classList.remove('d-none');

                const exitBtn = banner.querySelector('.ira-exit-form-mode');
                if (exitBtn) {
                    exitBtn.onclick = (e) => {
                        e.stopPropagation();
                        this.clearFormFillMode();
                        this.speak('Form mode se bahar aa gaye hain. Ordinary navigation ke liye bol sakte hain.');
                    };
                }
            }
        },

        clearFormFillMode: function () {
            this.activeFormKey = null;
            this.activeFormEl = null;
            this.formFillMode = false;
            this.guideState = null;
            if (this.elements.modal) {
                this.elements.modal.classList.remove('ira-form-mode');
            }
            document.body.classList.remove('ira-form-mode-active');
            const banner = document.getElementById('iraActiveFormBanner');
            if (banner) banner.classList.add('d-none');
        },

        /**
         * Scrape current non-sensitive field values from DOM
         */
        scrapeCurrentFormFields: function (formEl) {
            if (!formEl) return {};
            const data = {};
            const elements = formEl.querySelectorAll('input, select, textarea');

            elements.forEach(el => {
                const name = el.getAttribute('name');
                if (!name) return;

                const type = (el.getAttribute('type') || el.tagName).toLowerCase();
                // Strictly exclude passwords, PINs, OTPs, captchas, file inputs
                if (['password', 'file', 'hidden'].includes(type) || name.includes('password') || name.includes('pin') || name.includes('captcha') || name.includes('token')) {
                    return;
                }

                if (type === 'checkbox') {
                    data[name] = el.checked;
                } else if (type === 'radio') {
                    if (el.checked) data[name] = el.value;
                } else {
                    data[name] = el.value;
                }
            });

            return data;
        },

        /**
         * Apply structured field updates to DOM inputs without auto-submission
         */
        applyFormUpdates: function (updates, formSelector) {
            if (!Array.isArray(updates) || updates.length === 0) {
                return { success: true, updatedCount: 0, failedFields: [] };
            }

            const form = formSelector ? document.querySelector(formSelector) : (this.detectActiveForm()?.formEl || document);
            let firstScrolled = false;
            let updatedCount = 0;
            const failedFields = [];

            updates.forEach(update => {
                if (!update || !update.name) return;

                // Never voice-fill sensitive fields
                const nameLower = update.name.toLowerCase();
                if (nameLower.includes('password') || nameLower.includes('pin') || nameLower.includes('captcha') || nameLower.includes('otp')) {
                    console.warn('[IRA] Blocked attempt to voice-fill sensitive field:', update.name);
                    failedFields.push({ field: update.field || update.name, reason: 'sensitive' });
                    return;
                }

                let target = null;
                if (update.selector) {
                    target = form ? form.querySelector(update.selector) : document.querySelector(update.selector);
                }
                if (!target && form) {
                    target = form.querySelector(`[name="${update.name}"]`);
                }
                if (!target) {
                    target = document.querySelector(`[name="${update.name}"]`);
                }

                if (!target) {
                    console.warn('[IRA] Field not found in DOM for update:', update.name);
                    failedFields.push({ field: update.field || update.name, reason: 'not_found' });
                    return;
                }

                const tag = target.tagName.toLowerCase();
                const type = (target.getAttribute('type') || tag).toLowerCase();

                if (type === 'file') {
                    console.warn('[IRA] Programmatic file input filling blocked by browser security policy.');
                    failedFields.push({ field: update.field || update.name, reason: 'file_input' });
                    return;
                }

                let domUpdateVerified = false;

                if (tag === 'select') {
                    let matched = false;
                    const cleanTargetVal = String(update.value).trim().toLowerCase();

                    for (let i = 0; i < target.options.length; i++) {
                        const opt = target.options[i];
                        const optVal = opt.value.trim().toLowerCase();
                        const optText = opt.textContent.trim().toLowerCase();

                        if (optVal === cleanTargetVal || optText === cleanTargetVal || optText.includes(cleanTargetVal) || cleanTargetVal.includes(optVal)) {
                            target.selectedIndex = i;
                            target.value = opt.value;
                            matched = true;
                            break;
                        }
                    }

                    if (!matched) {
                        target.value = update.value;
                    }

                    target.dispatchEvent(new Event('change', { bubbles: true }));
                    if (window.$ && typeof $(target).trigger === 'function') {
                        $(target).trigger('change');
                    }
                    domUpdateVerified = (target.selectedIndex >= 0 && target.value !== '');
                } else if (type === 'radio') {
                    const radios = (form || document).querySelectorAll(`input[type="radio"][name="${update.name}"]`);
                    radios.forEach(radio => {
                        if (String(radio.value).trim().toLowerCase() === String(update.value).trim().toLowerCase()) {
                            radio.checked = true;
                            radio.dispatchEvent(new Event('change', { bubbles: true }));
                            if (window.$ && typeof $(radio).trigger === 'function') {
                                $(radio).trigger('change');
                            }
                            domUpdateVerified = true;
                        }
                    });
                } else if (type === 'checkbox') {
                    target.checked = Boolean(update.value);
                    target.dispatchEvent(new Event('change', { bubbles: true }));
                    if (window.$ && typeof $(target).trigger === 'function') {
                        $(target).trigger('change');
                    }
                    domUpdateVerified = true;
                } else {
                    target.value = update.value;
                    target.dispatchEvent(new Event('input', { bubbles: true }));
                    target.dispatchEvent(new Event('change', { bubbles: true }));
                    if (window.$ && typeof $(target).trigger === 'function') {
                        $(target).trigger('input').trigger('change');
                    }
                    domUpdateVerified = (String(target.value).trim() === String(update.value).trim());
                }

                if (domUpdateVerified) {
                    updatedCount++;
                    // Scroll first updated element into view
                    if (!firstScrolled) {
                        target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstScrolled = true;
                    }

                    // Visual flash glow
                    target.classList.add('ira-field-highlight');
                    setTimeout(() => {
                        target.classList.remove('ira-field-highlight');
                    }, 1800);
                } else {
                    failedFields.push({ field: update.field || update.name, reason: 'dom_verification_failed' });
                }
            });

            return {
                success: failedFields.length === 0,
                updatedCount: updatedCount,
                failedFields: failedFields
            };
        },

        /**
         * Dedicated voice form-step execution method via POST /ira/form-step
         */
        sendFormStep: async function (spokenText, formKey) {
            const endpoint = this.config.formEndpoint || '/ira/form-step';
            const activeForm = this.detectActiveForm();
            const targetKey = formKey || (activeForm ? activeForm.key : null);

            if (!targetKey) {
                this.speak('Is page par koi active form nahi mila.');
                return false;
            }

            // Quick check for explicit cancel/close
            if (/^(cancel|close|band\s*karo|hatao|exit)$/i.test(spokenText.trim())) {
                this.speak('Form filling cancel kiya gaya.');
                this.clearFormFillMode();
                this.closeModal();
                return true;
            }

            this.setVoiceState('THINKING');

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.config.csrfToken
                    },
                    body: JSON.stringify({
                        action: 'turn',
                        speech: spokenText,
                        form_key: targetKey,
                        current_page: window.location.pathname,
                        current_fields: this.scrapeCurrentFormFields(activeForm?.formEl),
                        guide_state: this.guideState || {}
                    })
                });

                if (!response.ok) {
                    throw new Error('Form step HTTP ' + response.status);
                }

                const result = await response.json();

                // If user said something meant for general navigation while in form mode, fall back to processIntentTurn
                if (result.status === 'no_match' && /\b(dashboard|wallet|profile|logout|history|income|team|page|jao|kholo|open)\b/i.test(spokenText)) {
                    this.clearFormFillMode();
                    return this.processIntentTurn(spokenText);
                }

                let domUpdateSuccess = true;
                if (result.form_updates && result.form_updates.length > 0) {
                    const domResult = this.applyFormUpdates(result.form_updates, result.form_selector || activeForm?.selector);
                    if (domResult && domResult.success === false) {
                        domUpdateSuccess = false;
                        console.warn('[IRA] DOM update verification failed for fields:', domResult.failedFields);
                    }
                }

                if (result.guide_state) {
                    // If DOM update failed on the targeted field, do not advance to next field prematurely
                    if (!domUpdateSuccess && this.guideState && this.guideState.current_field) {
                        result.guide_state.current_field = this.guideState.current_field;
                    }
                    this.guideState = result.guide_state;
                }

                if (result.active_field && result.active_field.selector) {
                    const form = activeForm?.formEl || document;
                    const targetEl = form.querySelector(result.active_field.selector);
                    if (targetEl) {
                        targetEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        targetEl.classList.add('ira-field-highlight');
                        setTimeout(() => targetEl.classList.remove('ira-field-highlight'), 1800);
                    }
                }

                const speech = result.speech || 'Form update ho gaya hai.';
                this.setAiUtterance(speech);

                if (result.action === 'complete') {
                    this.speak(speech);
                    this.setVoiceState('READY');
                } else if (result.action === 'exit') {
                    this.clearFormFillMode();
                    this.speak(speech);
                    this.setVoiceState('READY');
                } else {
                    // Continuous guided turn: re-arm microphone after speaking prompt
                    this.speak(speech, () => {
                        this.startListening();
                    });
                }
                return true;
            } catch (err) {
                console.error('[IRA] sendFormStep error:', err);
                this.setVoiceState('IDLE');
                this.speak('Form update karne mein dikkat aayi.');
                return false;
            }
        },

        toggleFullscreen: function () {
            this.isFullscreen = !this.isFullscreen;
            if (this.elements.modal) {
                if (this.isFullscreen) {
                    this.elements.modal.classList.add('ira-fullscreen');
                    if (this.elements.expandSvg) this.elements.expandSvg.classList.add('d-none');
                    if (this.elements.collapseSvg) this.elements.collapseSvg.classList.remove('d-none');
                } else {
                    this.elements.modal.classList.remove('ira-fullscreen');
                    if (this.elements.expandSvg) this.elements.expandSvg.classList.remove('d-none');
                    if (this.elements.collapseSvg) this.elements.collapseSvg.classList.add('d-none');
                }
            }
        }
    };

    // Attach to global window object
    window.IRA = IraAssistant;

    // Auto-initialize on load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => IraAssistant.init());
    } else {
        IraAssistant.init();
    }

    // Re-check mounting once all page resources (stylesheets, deferred plugins) finish loading
    window.addEventListener('load', () => {
        if (window.IRA && typeof window.IRA.mountFormFillButtons === 'function') {
            window.IRA.mountFormFillButtons();
        }
    });
})();
