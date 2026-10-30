import { AssistantApi } from './api-client.js';
import { AudioCapture } from './audio-capture.js';
import { StreamingPcmPlayer } from './audio-player.js';
import { GeminiLiveClient } from './gemini-live-client.js';
import { KORE_INTRO_PCM_BASE64 } from './kore-intro-pcm.js';

const config = window.VOICE_ASSISTANT_CONFIG;
const app = document.querySelector('.assistant-app');
const api = new AssistantApi(config);

const ui = {
  toast: document.querySelector('.toast'),
  status: document.querySelectorAll('[data-assistant-status]'),
  transcript: document.querySelector('.transcript-messages'),
  keyboard: document.querySelector('.keyboard-panel'),
  prompt: document.querySelector('#prompt'),
  micButtons: [...document.querySelectorAll('.microphone-control, .mobile-mic, .mobile-microphone-button')],
  pdf: document.querySelector('#pdfUpload'),
  dashboardGreeting: document.querySelector('#dashboardGreeting'),
  dashboardLiveTime: document.querySelector('#dashboardLiveTime'),
};

const state = {
  mode: 'IDLE', conversationId: null, session: null, capture: null, live: null,
  listening: false, intentionalClose: false, reconnects: 0, toastTimer: null,
  inputTranscript: '', outputTranscript: '', turnStartedAt: 0, usage: {}, level: 0, visualLevel: 0,
};

const player = new StreamingPcmPlayer({
  onLevel: (level) => setLevel(level),
  onIdle: () => { if (!state.listening && state.mode === 'SPEAKING') setMode('IDLE'); },
});

function fitReferenceCanvas() {
  const isMobile = window.innerWidth <= 991 || (window.innerHeight > window.innerWidth && window.innerWidth < 1100);
  const appEl = document.querySelector('.assistant-app');
  if (!appEl) return;
  if (isMobile) {
    appEl.classList.add('is-mobile-view');
    appEl.classList.remove('is-desktop-view');
    appEl.style.removeProperty('--canvas-scale');
    appEl.style.removeProperty('--canvas-left');
    appEl.style.removeProperty('--canvas-top');
  } else {
    appEl.classList.add('is-desktop-view');
    appEl.classList.remove('is-mobile-view');
    const scale = Math.min(window.innerWidth / 1536, window.innerHeight / 1024);
    appEl.style.setProperty('--canvas-scale', String(scale));
    appEl.style.setProperty('--canvas-left', `${(window.innerWidth - 1536 * scale) / 2}px`);
    appEl.style.setProperty('--canvas-top', `${(window.innerHeight - 1024 * scale) / 2}px`);
  }
}

function togglePanel(panel, trigger = null) {
  if (!panel) return;
  const open = !panel.classList.contains('open');
  document.querySelectorAll('.popover.open, .keyboard-panel.open').forEach((item) => item.classList.remove('open'));
  panel.classList.toggle('open', open);
  panel.setAttribute('aria-hidden', String(!open));
  trigger?.setAttribute('aria-expanded', String(open));
}

// ----------------------------------------------------
// Live Clock & Date Display
// ----------------------------------------------------
function updateLiveClock() {
  const timeEls = document.querySelectorAll('#dashboardLiveTime, .dashboard-live-clock');
  if (!timeEls.length) return;
  const now = new Date();
  const timeStr = now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
  const dateStr = now.toLocaleDateString('en-US', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
  timeEls.forEach((el) => {
    el.innerHTML = `${timeStr}<small>${dateStr}</small>`;
  });
}
updateLiveClock();
setInterval(updateLiveClock, 1000);

// ----------------------------------------------------
// Mode & State Management
// ----------------------------------------------------
function setMode(mode, detail = '') {
  state.mode = mode;
  if (app) app.dataset.assistantState = mode.toLowerCase();
  const labels = {
    IDLE: 'Tap to speak to JARVIS', CONNECTING: 'Connecting to JARVIS...', LISTENING: 'JARVIS is listening...',
    PROCESSING: 'JARVIS is processing...', THINKING: 'JARVIS is thinking...', SPEAKING: 'JARVIS is speaking...',
    INTERRUPTED: 'Interrupted — JARVIS listening...', ERROR: 'Something went wrong. Try again.',
    DISCONNECTED: 'Disconnected. Reconnecting...',
  };
  const activeStatusEls = document.querySelectorAll('[data-assistant-status]');
  activeStatusEls.forEach((element) => { element.textContent = detail || labels[mode] || mode; });
  const micBtns = document.querySelectorAll('.microphone-control, .mobile-mic, .mobile-microphone-button');
  micBtns.forEach((button) => {
    button.classList.toggle('listening', mode === 'LISTENING');
    button.setAttribute('aria-pressed', String(state.listening));
    button.setAttribute('aria-label', state.listening ? 'Stop listening' : 'Start listening');
  });
}

function toast(message) {
  ui.toast.textContent = message;
  ui.toast.classList.add('show');
  clearTimeout(state.toastTimer);
  state.toastTimer = setTimeout(() => ui.toast.classList.remove('show'), 3500);
}

function appendTranscript(role, text) {
  if (!text || !ui.transcript) return;
  const row = document.createElement('p');
  const label = document.createElement('strong');
  label.textContent = role === 'user' ? 'You' : 'JARVIS';
  row.className = `transcript-${role}`;
  row.append(label, document.createTextNode(text));
  ui.transcript.appendChild(row);
  ui.transcript.scrollTop = ui.transcript.scrollHeight;
}

function mergeTranscript(current, incoming) {
  const next = String(incoming || '').trim();
  if (!next) return current;
  if (next.startsWith(current)) return next;
  if (current.endsWith(next)) return current;
  return `${current}${current ? ' ' : ''}${next}`;
}

function makeLiveClient() {
  return new GeminiLiveClient({
    onOpen: () => { state.reconnects = 0; setMode(state.listening ? 'LISTENING' : 'IDLE'); },
    onMessage: handleLiveMessage,
    onError: (error) => { console.warn('Voice session error:', error.message); },
    onClose: () => { if (!state.intentionalClose) reconnect(); },
  });
}

async function ensureLiveSession() {
  if (state.live?.socket?.readyState === WebSocket.OPEN) return;
  setMode('CONNECTING');
  const session = await api.createLiveSession(state.conversationId);
  state.conversationId = session.conversation.id;
  state.session = session;
  state.intentionalClose = false;

  // Render past conversation history into transcript if not already loaded
  if (session.messages?.length && ui.transcript && ui.transcript.children.length === 0) {
    session.messages.forEach((msg) => {
      if (msg.content) appendTranscript(msg.role === 'assistant' ? 'assistant' : 'user', msg.content);
    });
  }

  state.live = makeLiveClient();
  await state.live.connect(session.live, session.history || []);
}

let browserRecognition = null;
let speechLevelTimer = null;
let hasGivenIntro = false;
let availableVoices = [];

function refreshVoices() {
  if (typeof window !== 'undefined' && 'speechSynthesis' in window) {
    availableVoices = window.speechSynthesis.getVoices() || [];
  }
}
refreshVoices();
if (typeof window !== 'undefined' && 'speechSynthesis' in window) {
  window.speechSynthesis.onvoiceschanged = refreshVoices;
}

function getKoreVoice(selectedLang = 'en-US') {
  const voices = (availableVoices && availableVoices.length) ? availableVoices : (window.speechSynthesis?.getVoices() || []);
  if (!voices.length) return null;

  const isHindi = String(selectedLang).toLowerCase().startsWith('hi');

  // 1. Direct match for 'kore' if available in system or extensions
  const directKore = voices.find((v) => /kore/i.test(v.name));
  if (directKore) return directKore;

  // 2. High-fidelity Smooth Neural / Natural Voices (Kore signature sound: warm, clear, calm female)
  const neuralPriority = isHindi
    ? [
        /swara.*online.*natural/i,
        /swara.*natural/i,
        /swara.*online/i,
        /swara/i,
        /google.*हिन्दी/i,
        /google.*hindi/i,
        /kalpana/i,
        /heera.*natural/i,
        /heera/i,
        /neerja.*natural/i,
        /neerja.*online/i,
        /neerja/i
      ]
    : [
        /neerja.*online.*natural/i,
        /neerja.*natural/i,
        /neerja.*online/i,
        /jenny.*online.*natural/i,
        /jenny.*natural/i,
        /aria.*online.*natural/i,
        /aria.*natural/i,
        /google.*uk.*female/i,
        /google.*us.*female/i,
        /google.*india/i,
        /zira/i,
        /natural/i
      ];

  for (const pattern of neuralPriority) {
    const match = voices.find((v) => pattern.test(v.name) && !/desktop.*david|male|david|george|mark|ravi/i.test(v.name));
    if (match) return match;
  }

  // 3. Any online/natural female voice matching language
  const langPrefix = isHindi ? 'hi' : 'en';
  const naturalVoice = voices.find((v) =>
    v.lang.toLowerCase().startsWith(langPrefix) &&
    /online|natural|neural/i.test(v.name) &&
    !/male|david|george|mark|ravi/i.test(v.name)
  );
  if (naturalVoice) return naturalVoice;

  // 4. Any female voice in target language
  const femaleVoice = voices.find((v) =>
    v.lang.toLowerCase().startsWith(langPrefix) &&
    /female|woman|zira/i.test(v.name) &&
    !/male|david|george|mark|ravi/i.test(v.name)
  );
  if (femaleVoice) return femaleVoice;

  // 5. Any female voice available across the system
  const anyFemale = voices.find((v) => /female|woman|zira/i.test(v.name) && !/male|david|george|mark|ravi/i.test(v.name));
  if (anyFemale) return anyFemale;

  return voices.find((v) => v.lang.toLowerCase().startsWith(langPrefix) && !/male|david|george|mark/i.test(v.name)) 
    || voices.find((v) => v.lang.toLowerCase().startsWith(langPrefix)) 
    || voices[0] 
    || null;
}

function speakWithBrowserKore(introText, cleanup) {
  if (!('speechSynthesis' in window)) {
    cleanup();
    return;
  }
  try { window.speechSynthesis.cancel(); } catch (_) {}
  const utterance = new SpeechSynthesisUtterance(introText);
  utterance.lang = 'en-US';
  utterance.rate = 0.98;
  utterance.pitch = 1.02;
  utterance.volume = 1;

  const koreVoice = getKoreVoice('en-US');
  if (koreVoice) utterance.voice = koreVoice;

  let visualTimer = setInterval(() => {
    setLevel(0.45 + Math.random() * 0.4);
  }, 100);

  const done = () => {
    clearInterval(visualTimer);
    cleanup();
  };

  utterance.onend = done;
  utterance.onerror = done;
  setTimeout(done, 8500);
  window.speechSynthesis.speak(utterance);
}

function playAssistantIntro() {
  return new Promise((resolve) => {
    const introText = "Hey! I’m here whenever you need me. Ask me something, tell me what you need, or simply start talking. I’m listening.";

    setMode('SPEAKING', 'Kore: "Hey! I’m here whenever you need me..."');
    appendTranscript('assistant', introText);

    let resolved = false;
    const cleanup = () => {
      if (resolved) return;
      resolved = true;
      setLevel(0);
      resolve();
    };

    // 1. Play real Google Gemini "Kore" Voice via StreamingPcmPlayer (Web Audio API)
    try {
      if (KORE_INTRO_PCM_BASE64 && player) {
        const originalOnIdle = player.onIdle;
        player.onIdle = () => {
          player.onIdle = originalOnIdle;
          originalOnIdle?.();
          cleanup();
        };
        player.enqueue(KORE_INTRO_PCM_BASE64).catch((err) => {
          console.warn('PCM enqueue error:', err);
          speakWithBrowserKore(introText, cleanup);
        });
        setTimeout(cleanup, 8500);
        return;
      }
    } catch (pcmErr) {
      console.warn('PCM playback failed:', pcmErr);
    }

    // 2. Fallback to WAV audio file with real Kore voice
    try {
      const audio = new Audio('/voice_assistant/kore-intro.wav');
      audio.onended = cleanup;
      audio.onerror = () => speakWithBrowserKore(introText, cleanup);
      audio.play().catch(() => speakWithBrowserKore(introText, cleanup));
      setTimeout(cleanup, 8500);
      return;
    } catch (_) {}

    // 3. Fallback to SpeechSynthesis
    speakWithBrowserKore(introText, cleanup);
  });
}

function startBrowserSpeechRecognition() {
  const SpeechRec = window.SpeechRecognition || window.webkitSpeechRecognition;
  if (!SpeechRec) {
    throw new Error('Speech recognition not supported in this browser. Please use Chrome or Edge.');
  }

  if (browserRecognition) {
    try { browserRecognition.abort(); } catch (_) {}
  }

  browserRecognition = new SpeechRec();
  browserRecognition.continuous = false;
  browserRecognition.interimResults = true;
  browserRecognition.maxAlternatives = 1;
  browserRecognition.lang = 'hi-IN';

  let hasSpoken = false;
  let accumulatedFinal = '';

  browserRecognition.onstart = () => {
    state.listening = true;
    state.turnStartedAt = Date.now();
    setMode('LISTENING', 'JARVIS is listening... Speak now');
    if (speechLevelTimer) clearInterval(speechLevelTimer);
    speechLevelTimer = setInterval(() => {
      setLevel(Math.random() * 0.45 + 0.15);
    }, 120);
  };

  browserRecognition.onresult = (event) => {
    hasSpoken = true;
    let interim = '';
    for (let i = event.resultIndex; i < event.results.length; ++i) {
      if (event.results[i].isFinal) {
        accumulatedFinal += event.results[i][0].transcript;
      } else {
        interim += event.results[i][0].transcript;
      }
    }
    const current = (accumulatedFinal + ' ' + interim).trim();
    if (current) {
      ui.status.forEach((el) => { el.textContent = `“${current}”`; });
      setLevel(0.75);
    }
  };

  browserRecognition.onerror = (event) => {
    console.warn('Speech recognition event error:', event.error);
    if (speechLevelTimer) clearInterval(speechLevelTimer);
    setLevel(0);
    state.listening = false;
    if (event.error === 'not-allowed') {
      handleError(new Error('Microphone permission was denied. Please allow microphone access in your browser.'));
    } else if (event.error !== 'no-speech') {
      handleError(new Error('Voice recognition error: ' + event.error));
    } else {
      setMode('IDLE');
    }
  };

  browserRecognition.onend = () => {
    if (speechLevelTimer) clearInterval(speechLevelTimer);
    setLevel(0);
    state.listening = false;
    const finalQuery = accumulatedFinal.trim();
    if (finalQuery) {
      setMode('THINKING');
      sendText(finalQuery);
    } else if (!hasSpoken) {
      setMode('IDLE');
    }
  };

  browserRecognition.start();
}

async function startListening() {
  try {
    player.stop();
    if (window.speechSynthesis) speechSynthesis.cancel();

    let liveStarted = false;
    try {
      await ensureLiveSession();
      state.capture = new AudioCapture({
        workletUrl: config.workletUrl,
        onAudio: (bytes) => {
          if (state.listening && state.live?.socket?.readyState === WebSocket.OPEN) state.live.sendAudio(bytes);
        },
        onLevel: setLevel,
        onError: (error) => handleError(error),
      });
      await state.capture.start();
      state.listening = true;
      state.turnStartedAt = Date.now();
      setMode('LISTENING');
      liveStarted = true;
    } catch (liveErr) {
      console.info('Live WebSocket unavailable, using browser speech recognition:', liveErr.message);
    }

    if (!liveStarted) {
      startBrowserSpeechRecognition();
    }
  } catch (error) {
    handleError(error, error.name === 'NotAllowedError' ? 'Microphone permission was denied.' : null);
  }
}

async function stopListening() {
  state.listening = false;
  if (speechLevelTimer) clearInterval(speechLevelTimer);
  setLevel(0);
  if (browserRecognition) {
    try { browserRecognition.stop(); } catch (_) {}
  }
  await state.capture?.stop();
  state.capture = null;
  try { state.live?.endAudio(); } catch (_) {}
  setMode('PROCESSING');
}

async function toggleListening() {
  if (state.listening) {
    await stopListening();
    return;
  }

  // User mic click karne par pehle AI apna intro bolega
  if (!hasGivenIntro) {
    hasGivenIntro = true;
    await playAssistantIntro();
  }

  await startListening();
}

async function handleLiveMessage(message) {
  if (message.usageMetadata) state.usage = message.usageMetadata;
  const content = message.serverContent;
  if (!content) return;

  if (content.interrupted) {
    player.stop();
    state.outputTranscript = '';
    setMode('INTERRUPTED');
  }
  if (content.inputTranscription?.text) {
    state.inputTranscript = mergeTranscript(state.inputTranscript, content.inputTranscription.text);
    setMode('THINKING');
  }
  if (content.outputTranscription?.text) {
    state.outputTranscript = mergeTranscript(state.outputTranscript, content.outputTranscription.text);
  }
  for (const part of content.modelTurn?.parts || []) {
    if (part.inlineData?.data) {
      setMode('SPEAKING');
      await player.enqueue(part.inlineData.data);
    }
  }
  if (content.turnComplete) await completeTurn();
}

async function completeTurn() {
  const userText = state.inputTranscript.trim();
  const assistantText = state.outputTranscript.trim();
  if (userText) appendTranscript('user', userText);
  if (assistantText) appendTranscript('assistant', assistantText);
  if (userText || assistantText) {
    api.saveLiveTurn({
      conversation_id: state.conversationId,
      turn_id: crypto.randomUUID(),
      user_transcript: userText || null,
      assistant_transcript: assistantText || null,
      duration_ms: state.turnStartedAt ? Date.now() - state.turnStartedAt : null,
      usage: state.usage,
    }).then(refreshStats).catch((error) => console.warn('Could not persist voice turn:', error.message));
  }
  state.inputTranscript = state.outputTranscript = '';
  state.usage = {};
  state.turnStartedAt = Date.now();
  if (!player.sources.size) setMode(state.listening ? 'LISTENING' : 'IDLE');
}

async function reconnect() {
  if (state.reconnects >= 3) {
    setMode('ERROR', 'Connection lost. Tap the microphone to retry.');
    state.live = null;
    return;
  }
  state.reconnects += 1;
  setMode('DISCONNECTED', `Disconnected. Reconnecting (${state.reconnects}/3)...`);
  await new Promise((resolve) => setTimeout(resolve, state.reconnects * 750));
  try { await ensureLiveSession(); } catch (error) { reconnect(); }
}

async function sendText(text, quickAction = null) {
  if (!text && !quickAction) return;
  const display = text || config.quickActions[quickAction] || quickAction;
  appendTranscript('user', display);
  setMode('THINKING');
  try {
    await ensureLiveSession();
    state.inputTranscript = '';
    state.turnStartedAt = Date.now();
    state.live.sendText(display);
  } catch (liveError) {
    try {
      const result = await api.sendText(text, state.conversationId, quickAction);
      state.conversationId = result.conversation.id;
      appendTranscript('assistant', result.assistant_message.content);
      speakFallback(result.assistant_message.content);
      setMode('IDLE');
      refreshStats();
    } catch (error) { handleError(error); }
  }
}

function speakFallback(text) {
  if (!window.speechSynthesis) return;
  speechSynthesis.cancel();
  const clean = String(text || '').replace(/[*#`]/g, '');
  const utterance = new SpeechSynthesisUtterance(clean);
  const lang = /[\u0900-\u097F]/.test(clean) ? 'hi-IN' : 'en-US';
  utterance.lang = lang;
  utterance.rate = 0.98;
  utterance.pitch = 1.02;
  const koreVoice = getKoreVoice(lang);
  if (koreVoice) utterance.voice = koreVoice;
  speechSynthesis.speak(utterance);
}

async function summarizePdf(file) {
  setMode('THINKING', 'Reading PDF...');
  try {
    const result = await api.summarizePdf(file, state.conversationId);
    state.conversationId = result.conversation.id;
    appendTranscript('user', `Summarize PDF: ${file.name}`);
    appendTranscript('assistant', result.assistant_message.content);
    speakFallback(result.assistant_message.content);
    setMode('IDLE');
    refreshStats();
  } catch (error) { handleError(error); }
}

function handleError(error, override = null) {
  const message = override || error.message || 'Something went wrong. Try again.';
  console.warn('Assistant:', message);
  setMode('ERROR', message);
  toast(message);
}

async function refreshStats() {
  try {
    const stats = await api.stats();
    document.querySelectorAll('[data-stat]').forEach((element) => {
      const key = element.dataset.stat;
      const value = stats[key];
      element.textContent = key === 'usage_minutes' ? `${value}m` : (key === 'success_rate' ? (value === null ? '—' : `${value}%`) : value);
    });
  } catch (_) {}
}

function setLevel(level) {
  state.level = level;
  if (app) app.style.setProperty('--audio-level', String(level));
  document.querySelectorAll('.assistant-app').forEach(el => el.style.setProperty('--audio-level', String(level)));
}

function drawWaveform() {
  const canvases = document.querySelectorAll('.live-waveform');
  canvases.forEach((canvas) => {
    if (canvas.offsetParent === null) return; // Skip hidden canvas
    const context = canvas.getContext('2d');
    const dpr = window.devicePixelRatio || 1;
    const width = canvas.width = canvas.clientWidth * dpr;
    const height = canvas.height = canvas.clientHeight * dpr;
    context.clearRect(0, 0, width, height);
    const bars = 72;
    const playback = player.getVisualData(bars);
    const targetLevel = state.mode === 'SPEAKING' ? playback.level : state.level;
    const smoothing = targetLevel > state.visualLevel ? 0.38 : 0.12;
    state.visualLevel += (targetLevel - state.visualLevel) * smoothing;

    for (let i = 0; i < bars; i += 1) {
      if (Math.abs(i - bars / 2) < 6) continue;
      const center = Math.abs(i - bars / 2) / (bars / 2);
      const frequency = state.mode === 'SPEAKING' ? playback.bars[i] : 0;
      const liveActivity = Math.max(state.visualLevel, frequency * 0.9);
      const idleActivity = state.mode === 'IDLE' ? 0.035 : 0.06;
      const texture = 0.7 + Math.sin(performance.now() * 0.012 + i * 0.82) * 0.3;
      const barHeight = Math.max(2, (1 - center * .42) * Math.max(idleActivity, liveActivity) * texture * height * .92);
      const x = (i / bars) * width;
      const gradient = context.createLinearGradient(0, 0, width, 0);
      gradient.addColorStop(0, '#00e5ff'); gradient.addColorStop(.5, '#9b4dff'); gradient.addColorStop(1, '#ef4dff');
      context.fillStyle = gradient;
      context.fillRect(x, (height - barHeight) / 2, Math.max(2, width / bars / 2), barHeight);
    }
  });
  requestAnimationFrame(drawWaveform);
}

// ----------------------------------------------------
// Event Listeners
// ----------------------------------------------------
document.querySelectorAll('.microphone-control, .mobile-mic, .mobile-microphone-button').forEach((button) => {
  button.addEventListener('click', toggleListening);
});

document.querySelectorAll('.settings-control').forEach((button) => {
  button.addEventListener('click', (event) => {
    event.stopPropagation();
    togglePanel(document.querySelector('.settings-menu'), button);
  });
});

document.querySelectorAll('.keyboard-control').forEach((button) => {
  button.addEventListener('click', (event) => {
    event.stopPropagation();
    togglePanel(ui.keyboard, button);
    setTimeout(() => ui.prompt?.focus(), 150);
  });
});

document.querySelector('.stop-audio')?.addEventListener('click', () => { player.stop(); setMode(state.listening ? 'LISTENING' : 'IDLE'); });

document.querySelector('.keyboard-panel')?.addEventListener('submit', (event) => {
  event.preventDefault();
  const text = ui.prompt.value.trim();
  if (text) { ui.prompt.value = ''; sendText(text); }
});

document.querySelectorAll('[data-action]').forEach((button) => button.addEventListener('click', async () => {
  const action = button.dataset.action;
  if (action === 'pdf') return ui.pdf.click();
  if (action === 'image') {
    try { await api.generateImage(); } catch (error) { toast(error.message); }
    return;
  }
  sendText('', action);
}));

ui.pdf?.addEventListener('change', () => { if (ui.pdf.files[0]) summarizePdf(ui.pdf.files[0]); ui.pdf.value = ''; });
window.addEventListener('beforeunload', () => { state.intentionalClose = true; state.live?.close(); state.capture?.stop(); player.close(); });
window.addEventListener('resize', fitReferenceCanvas, { passive: true });
window.addEventListener('orientationchange', fitReferenceCanvas, { passive: true });

document.addEventListener('click', (event) => {
  if (!event.target.closest('.popover,.keyboard-panel,.settings-control,.keyboard-control')) {
    document.querySelectorAll('.popover.open,.keyboard-panel.open').forEach((panel) => panel.classList.remove('open'));
  }
});

document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape') {
    document.querySelectorAll('.popover.open,.keyboard-panel.open').forEach((panel) => panel.classList.remove('open'));
  }
});

// Initialization
fitReferenceCanvas();
setMode('IDLE');
refreshStats();
drawWaveform();
