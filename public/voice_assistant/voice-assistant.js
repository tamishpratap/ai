import { AssistantApi } from './api-client.js';
import { AudioCapture } from './audio-capture.js';
import { StreamingPcmPlayer } from './audio-player.js';
import { GeminiLiveClient } from './gemini-live-client.js';

const config = window.VOICE_ASSISTANT_CONFIG;
const app = document.querySelector('.assistant-app');
const api = new AssistantApi(config);

const ui = {
  toast: document.querySelector('.toast'),
  status: document.querySelectorAll('[data-assistant-status]'),
  transcript: document.querySelector('.transcript-messages'),
  keyboard: document.querySelector('.keyboard-panel'),
  prompt: document.querySelector('#prompt'),
  micButtons: [...document.querySelectorAll('.microphone-control, .mobile-mic')],
  canvas: document.querySelector('#liveWaveform'),
  pdf: document.querySelector('#pdfUpload'),

  // Gemini API Key elements
  openModalBtn: document.querySelector('#openGeminiModalBtn'),
  settingsKeyBadge: document.querySelector('#settingsKeyBadge'),
  modalOverlay: document.querySelector('#geminiModalOverlay'),
  modalCloseBtn: document.querySelector('#closeGeminiModalBtn'),
  keyInput: document.querySelector('#geminiApiKeyInput'),
  toggleEyeBtn: document.querySelector('#toggleKeyVisibilityBtn'),
  statusCard: document.querySelector('#keyStatusCard'),
  statusTitle: document.querySelector('#statusTitle'),
  statusDesc: document.querySelector('#statusDesc'),
  keyFeedback: document.querySelector('#keyFeedback'),
  testBtn: document.querySelector('#testKeyBtn'),
  testSpinner: document.querySelector('#testSpinner'),
  testBtnText: document.querySelector('#testBtnText'),
  saveBtn: document.querySelector('#saveKeyBtn'),
  removeBtn: document.querySelector('#removeKeyBtn'),

  // Edit Profile elements
  openProfileModalBtn: document.querySelector('#openProfileModalBtn'),
  profileModalOverlay: document.querySelector('#profileModalOverlay'),
  closeProfileModalBtn: document.querySelector('#closeProfileModalBtn'),
  editProfileForm: document.querySelector('#editProfileForm'),
  profileImageInput: document.querySelector('#profileImageInput'),
  btnTriggerPhotoUpload: document.querySelector('#btnTriggerPhotoUpload'),
  modalAvatarPreview: document.querySelector('#modalAvatarPreview'),
  previewAvatarImg: document.querySelector('#previewAvatarImg'),
  previewAvatarInitial: document.querySelector('#previewAvatarInitial'),
  editProfileName: document.querySelector('#editProfileName'),
  editProfileEmail: document.querySelector('#editProfileEmail'),
  editProfileMobile: document.querySelector('#editProfileMobile'),
  editProfilePassword: document.querySelector('#editProfilePassword'),
  profileFeedback: document.querySelector('#profileFeedback'),
  saveProfileBtn: document.querySelector('#saveProfileBtn'),
  profileSaveSpinner: document.querySelector('#profileSaveSpinner'),
  profileSaveBtnText: document.querySelector('#profileSaveBtnText'),

  // Dashboard profile display elements
  dashboardGreeting: document.querySelector('#dashboardGreeting'),
  dashboardProfileName: document.querySelector('#dashboardProfileName'),
  menuProfileName: document.querySelector('#menuProfileName'),
  dashboardAvatar: document.querySelector('#dashboardAvatar'),
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
  const scale = Math.min(window.innerWidth / 1536, window.innerHeight / 1024);
  app.style.setProperty('--canvas-scale', String(scale));
  app.style.setProperty('--canvas-left', `${(window.innerWidth - 1536 * scale) / 2}px`);
  app.style.setProperty('--canvas-top', `${(window.innerHeight - 1024 * scale) / 2}px`);
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
// Gemini API Key Modal & Key Management
// ----------------------------------------------------
function updateApiKeyStatus() {
  const key = api.getApiKey();
  const hasKey = Boolean(key && key.trim().length > 0);

  if (ui.settingsKeyBadge) {
    ui.settingsKeyBadge.classList.toggle('active', hasKey);
  }

  if (ui.statusCard) {
    ui.statusCard.classList.toggle('active', hasKey);
  }

  if (ui.statusTitle) {
    ui.statusTitle.textContent = hasKey ? 'API Key Configured & Ready' : 'API Key Not Set';
  }

  if (ui.statusDesc) {
    ui.statusDesc.textContent = hasKey
      ? `Active Key: ${key.slice(0, 7)}••••••••${key.slice(-4)}`
      : 'Please add your Google Gemini API key to activate voice and assistant capabilities.';
  }

  if (ui.removeBtn) {
    ui.removeBtn.style.display = hasKey ? 'inline-flex' : 'none';
  }

  if (ui.keyInput && !ui.modalOverlay?.classList.contains('open')) {
    ui.keyInput.value = hasKey ? key : '';
  }
}

function openGeminiModal() {
  document.querySelectorAll('.popover.open, .keyboard-panel.open').forEach((item) => item.classList.remove('open'));
  const key = api.getApiKey();
  if (ui.keyInput) {
    ui.keyInput.value = key || '';
  }
  hideKeyFeedback();
  updateApiKeyStatus();
  ui.modalOverlay?.classList.add('open');
  ui.modalOverlay?.setAttribute('aria-hidden', 'false');
  setTimeout(() => ui.keyInput?.focus(), 150);
}

function closeGeminiModal() {
  ui.modalOverlay?.classList.remove('open');
  ui.modalOverlay?.setAttribute('aria-hidden', 'true');
  hideKeyFeedback();
}

function showKeyFeedback(message, type = 'success') {
  if (!ui.keyFeedback) return;
  ui.keyFeedback.textContent = message;
  ui.keyFeedback.className = `key-validation-feedback ${type}`;
  ui.keyFeedback.style.display = 'block';
}

function hideKeyFeedback() {
  if (!ui.keyFeedback) return;
  ui.keyFeedback.style.display = 'none';
  ui.keyFeedback.textContent = '';
}

function toggleKeyVisibility() {
  if (!ui.keyInput) return;
  const isPass = ui.keyInput.type === 'password';
  ui.keyInput.type = isPass ? 'text' : 'password';
  const openIcon = ui.toggleEyeBtn?.querySelector('.icon-eye-open');
  const closedIcon = ui.toggleEyeBtn?.querySelector('.icon-eye-closed');
  if (openIcon) openIcon.style.display = isPass ? 'none' : 'block';
  if (closedIcon) closedIcon.style.display = isPass ? 'block' : 'none';
}

async function handleTestKey() {
  const key = (ui.keyInput?.value || '').trim();
  if (!key) {
    showKeyFeedback('Please enter an API key to test.', 'error');
    return;
  }

  if (ui.testSpinner) ui.testSpinner.style.display = 'inline-block';
  if (ui.testBtnText) ui.testBtnText.textContent = 'Testing...';
  if (ui.testBtn) ui.testBtn.disabled = true;
  hideKeyFeedback();

  try {
    const result = await api.verifyKey(key);
    const modelInfo = result?.active_model ? ` (${result.active_model})` : '';
    showKeyFeedback((result?.message || 'Gemini API Key is valid and active!') + modelInfo, 'success');
  } catch (error) {
    showKeyFeedback(error.message || 'Verification failed. Please check the API key.', 'error');
  } finally {
    if (ui.testSpinner) ui.testSpinner.style.display = 'none';
    if (ui.testBtnText) ui.testBtnText.textContent = 'Test Connection';
    if (ui.testBtn) ui.testBtn.disabled = false;
  }
}

function handleSaveKey() {
  const key = (ui.keyInput?.value || '').trim();
  if (!key) {
    showKeyFeedback('Please enter a valid Gemini API Key.', 'error');
    return;
  }

  localStorage.setItem('user_gemini_api_key', key);
  updateApiKeyStatus();
  showKeyFeedback('Gemini API Key saved successfully!', 'success');
  toast('Gemini API Key saved.');

  state.live?.close();
  state.live = null;
  state.conversationId = null;

  setTimeout(() => {
    closeGeminiModal();
  }, 900);
}

function handleRemoveKey() {
  localStorage.removeItem('user_gemini_api_key');
  if (ui.keyInput) ui.keyInput.value = '';
  updateApiKeyStatus();
  showKeyFeedback('Gemini API key has been removed.', 'error');
  toast('Gemini API Key removed.');

  state.live?.close();
  state.live = null;
  state.conversationId = null;
}

// ----------------------------------------------------
// Edit Profile Modal & Photo Upload
// ----------------------------------------------------
function openProfileModal() {
  document.querySelectorAll('.popover.open, .keyboard-panel.open').forEach((item) => item.classList.remove('open'));
  hideProfileFeedback();
  ui.profileModalOverlay?.classList.add('open');
  ui.profileModalOverlay?.setAttribute('aria-hidden', 'false');
  setTimeout(() => ui.editProfileName?.focus(), 150);
}

function closeProfileModal() {
  ui.profileModalOverlay?.classList.remove('open');
  ui.profileModalOverlay?.setAttribute('aria-hidden', 'true');
  hideProfileFeedback();
}

function showProfileFeedback(message, type = 'success') {
  if (!ui.profileFeedback) return;
  ui.profileFeedback.textContent = message;
  ui.profileFeedback.className = `key-validation-feedback ${type}`;
  ui.profileFeedback.style.display = 'block';
}

function hideProfileFeedback() {
  if (!ui.profileFeedback) return;
  ui.profileFeedback.style.display = 'none';
  ui.profileFeedback.textContent = '';
}

function handleAvatarPreview(file) {
  if (!file) return;
  const reader = new FileReader();
  reader.onload = (e) => {
    if (ui.previewAvatarImg) {
      ui.previewAvatarImg.src = e.target.result;
      ui.previewAvatarImg.style.display = 'block';
    }
    if (ui.previewAvatarInitial) {
      ui.previewAvatarInitial.style.display = 'none';
    }
  };
  reader.readAsDataURL(file);
}

async function handleProfileSubmit(event) {
  event.preventDefault();
  const form = ui.editProfileForm;
  if (!form) return;

  hideProfileFeedback();
  if (ui.profileSaveSpinner) ui.profileSaveSpinner.style.display = 'inline-block';
  if (ui.profileSaveBtnText) ui.profileSaveBtnText.textContent = 'Saving...';
  if (ui.saveProfileBtn) ui.saveProfileBtn.disabled = true;

  const formData = new FormData(form);
  const endpoint = config?.endpoints?.profile || '/api/assistant/profile';

  try {
    const response = await fetch(endpoint, {
      method: 'POST',
      body: formData,
      headers: {
        'X-CSRF-TOKEN': config.csrfToken,
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
      },
    });

    const data = await response.json();
    if (!response.ok || !data.success) {
      throw new Error(data.message || 'Could not update profile.');
    }

    showProfileFeedback('Profile updated successfully!', 'success');
    toast('Profile and photo updated!');

    // Update real-time UI elements
    const member = data.member;
    const hour = new Date().getHours();
    const timeOfDay = hour < 12 ? 'Morning' : (hour < 17 ? 'Afternoon' : 'Evening');

    if (ui.dashboardGreeting) {
      ui.dashboardGreeting.textContent = `Good ${timeOfDay}, ${member.name} 👋`;
    }
    if (ui.dashboardProfileName) {
      ui.dashboardProfileName.textContent = member.name;
    }
    if (ui.menuProfileName) {
      ui.menuProfileName.textContent = member.name;
    }

    // Update avatar on dashboard
    if (ui.dashboardAvatar) {
      if (member.avatar_url) {
        ui.dashboardAvatar.innerHTML = `<img src="${member.avatar_url}" alt="${member.name}">`;
      } else {
        ui.dashboardAvatar.innerHTML = `<span>${member.avatar_initial}</span>`;
      }
    }

    if (ui.editProfilePassword) {
      ui.editProfilePassword.value = '';
    }

    setTimeout(() => {
      closeProfileModal();
    }, 1200);
  } catch (error) {
    showProfileFeedback(error.message, 'error');
  } finally {
    if (ui.profileSaveSpinner) ui.profileSaveSpinner.style.display = 'none';
    if (ui.profileSaveBtnText) ui.profileSaveBtnText.textContent = 'Save Profile';
    if (ui.saveProfileBtn) ui.saveProfileBtn.disabled = false;
  }
}

// ----------------------------------------------------
// Mode & State Management
// ----------------------------------------------------
function setMode(mode, detail = '') {
  state.mode = mode;
  app.dataset.assistantState = mode.toLowerCase();
  const labels = {
    IDLE: 'Tap to speak to JARVIS', CONNECTING: 'Connecting to JARVIS...', LISTENING: 'JARVIS is listening...',
    PROCESSING: 'JARVIS is processing...', THINKING: 'JARVIS is thinking...', SPEAKING: 'JARVIS is speaking...',
    INTERRUPTED: 'Interrupted — JARVIS listening...', ERROR: 'Something went wrong. Try again.',
    DISCONNECTED: 'Disconnected. Reconnecting...',
  };
  ui.status.forEach((element) => { element.textContent = detail || labels[mode] || mode; });
  ui.micButtons.forEach((button) => {
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
  if (state.listening) await stopListening(); else await startListening();
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
  const utterance = new SpeechSynthesisUtterance(text.replace(/[*#`]/g, ''));
  utterance.lang = /[\u0900-\u097F]/.test(text) ? 'hi-IN' : 'en-IN';
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

function setLevel(level) { state.level = level; app.style.setProperty('--audio-level', String(level)); }

function drawWaveform() {
  const canvas = ui.canvas;
  if (canvas) {
    const context = canvas.getContext('2d');
    const width = canvas.width = canvas.clientWidth * devicePixelRatio;
    const height = canvas.height = canvas.clientHeight * devicePixelRatio;
    context.clearRect(0, 0, width, height);
    const bars = 72;
    const playback = player.getVisualData(bars);
    const targetLevel = state.mode === 'SPEAKING' ? playback.level : state.level;
    const smoothing = targetLevel > state.visualLevel ? 0.38 : 0.12;
    state.visualLevel += (targetLevel - state.visualLevel) * smoothing;

    for (let i = 0; i < bars; i += 1) {
      if (Math.abs(i - bars / 2) < 8) continue;
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
  }
  requestAnimationFrame(drawWaveform);
}

// ----------------------------------------------------
// Event Listeners
// ----------------------------------------------------
ui.micButtons.forEach((button) => button.addEventListener('click', toggleListening));

// Gemini API Key Modal Controls
ui.openModalBtn?.addEventListener('click', (event) => {
  event.stopPropagation();
  openGeminiModal();
});
ui.modalCloseBtn?.addEventListener('click', closeGeminiModal);
ui.toggleEyeBtn?.addEventListener('click', toggleKeyVisibility);
ui.testBtn?.addEventListener('click', handleTestKey);
ui.saveBtn?.addEventListener('click', handleSaveKey);
ui.removeBtn?.addEventListener('click', handleRemoveKey);
ui.modalOverlay?.addEventListener('click', (event) => {
  if (event.target === ui.modalOverlay) closeGeminiModal();
});

// Edit Profile Modal Controls
ui.openProfileModalBtn?.addEventListener('click', (event) => {
  event.stopPropagation();
  openProfileModal();
});
ui.closeProfileModalBtn?.addEventListener('click', closeProfileModal);
ui.btnTriggerPhotoUpload?.addEventListener('click', () => ui.profileImageInput?.click());
ui.modalAvatarPreview?.addEventListener('click', () => ui.profileImageInput?.click());
ui.profileImageInput?.addEventListener('change', () => {
  if (ui.profileImageInput.files && ui.profileImageInput.files[0]) {
    handleAvatarPreview(ui.profileImageInput.files[0]);
  }
});
ui.editProfileForm?.addEventListener('submit', handleProfileSubmit);
ui.profileModalOverlay?.addEventListener('click', (event) => {
  if (event.target === ui.profileModalOverlay) closeProfileModal();
});

document.querySelector('.profile-control')?.addEventListener('click', (event) => {
  event.stopPropagation(); togglePanel(document.querySelector('.profile-menu'), event.currentTarget);
});
document.querySelector('.settings-control')?.addEventListener('click', (event) => {
  event.stopPropagation(); togglePanel(document.querySelector('.settings-menu'));
});
document.querySelector('.keyboard-control')?.addEventListener('click', (event) => {
  event.stopPropagation(); togglePanel(ui.keyboard); setTimeout(() => ui.prompt?.focus(), 150);
});
document.querySelector('.stop-audio')?.addEventListener('click', () => { player.stop(); setMode(state.listening ? 'LISTENING' : 'IDLE'); });
document.querySelector('.notification-control')?.addEventListener('click', () => toast('No new notifications.'));
document.querySelector('.language-control')?.addEventListener('click', () => toast('Auto language: English, Hindi, and Hinglish.'));

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

app.addEventListener('click', (event) => {
  if (!event.target.closest('.popover,.keyboard-panel,.profile-control,.settings-control,.keyboard-control,#geminiModalOverlay,#profileModalOverlay')) {
    document.querySelectorAll('.popover.open,.keyboard-panel.open').forEach((panel) => panel.classList.remove('open'));
  }
});

document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape') {
    document.querySelectorAll('.popover.open,.keyboard-panel.open').forEach((panel) => panel.classList.remove('open'));
    if (ui.modalOverlay?.classList.contains('open')) closeGeminiModal();
    if (ui.profileModalOverlay?.classList.contains('open')) closeProfileModal();
  }
});

// Initialization
fitReferenceCanvas();
updateApiKeyStatus();
setMode('IDLE');
refreshStats();
drawWaveform();
