(() => {
  const app = document.querySelector('.nexus-dashboard, .assistant-shell, .assistant-app');
  if (!app) return;

  const els = {
    form: document.getElementById('questionForm'),
    input: document.getElementById('questionInput'),
    send: document.getElementById('sendButton'),
    status: document.getElementById('voiceStatus'),
    statusCopy: document.querySelector('[data-status-copy]'),
    messages: document.getElementById('messages'),
    response: document.getElementById('responsePopover'),
    responseLabel: document.getElementById('responseLabel'),
    closeResponse: document.getElementById('closeResponse'),
    stop: document.getElementById('stopSpeaking'),
    language: document.getElementById('languageSelect'),
    date: document.getElementById('currentDate'),
    time: document.getElementById('currentTime'),
    toast: document.getElementById('toast'),
    voiceTriggers: [...document.querySelectorAll('[data-voice-trigger]')],
    keyboard: document.querySelector('.keyboard-panel'),
    profileMenu: document.querySelector('.profile-menu'),
    settingsMenu: document.querySelector('.settings-menu'),
    continuousPill: document.getElementById('continuousPill'),
    toggleContinuousBtn: document.getElementById('toggleContinuousBtn')
  };
  const Recognition = window.SpeechRecognition || window.webkitSpeechRecognition;
  let recognition = null;
  let listening = false;
  let sending = false;
  let continuousMode = true;
  let consecutiveSilences = 0;
  let autoListenTimer = null;
  let toastTimer = null;
  let blastTimer = null;
  let submitTimer = null;
  let sessionTranscript = '';
  let latestTranscript = '';
  let availableVoices = [];

  fitReferenceCanvas();
  updateClock();
  updateContinuousUI();
  window.setInterval(updateClock, 1000);
  window.addEventListener('resize', fitReferenceCanvas, { passive: true });

  if (Recognition) {
    recognition = new Recognition();
    recognition.lang = els.language?.value || 'hi-IN';
    recognition.interimResults = true;
    recognition.continuous = false;
    recognition.maxAlternatives = 5;
    recognition.onstart = () => {
      sessionTranscript = '';
      latestTranscript = '';
      window.clearTimeout(submitTimer);
      window.clearTimeout(autoListenTimer);
      setListening(true);
    };
    recognition.onresult = (event) => {
      let interimTranscript = '';
      sessionTranscript = '';
      for (let index = 0; index < event.results.length; index += 1) {
        const result = event.results[index];
        let best = result[0];
        for (let alternative = 1; alternative < result.length; alternative += 1) {
          if ((result[alternative].confidence || 0) > (best.confidence || 0)) best = result[alternative];
        }
        if (result.isFinal) sessionTranscript += `${best.transcript} `;
        else interimTranscript += best.transcript;
      }
      const transcript = `${sessionTranscript}${interimTranscript}`.replace(/\s+/g, ' ').trim();
      const typedTranscript = toRomanEnglish(transcript);
      latestTranscript = transcript;
      els.input.value = typedTranscript;
      els.status.textContent = typedTranscript ? `Sun raha hoon: “${typedTranscript}”` : 'Sun raha hoon... clear boliye';

      if (transcript) {
        consecutiveSilences = 0;
        window.clearTimeout(submitTimer);
        submitTimer = window.setTimeout(() => {
          if (listening && !sending) recognition.stop();
        }, 1400);
      }
    };
    recognition.onerror = (event) => {
      const isNoSpeech = event.error === 'no-speech';
      const isNotAllowed = event.error === 'not-allowed';
      const isAborted = event.error === 'aborted';

      listening = false;
      sessionTranscript = '';
      latestTranscript = '';
      window.clearTimeout(submitTimer);
      updateVoiceButtons(false);

      if (isNotAllowed) {
        continuousMode = false;
        updateContinuousUI();
        const message = 'Microphone permission allow kijiye.';
        setState('error', message);
        showToast(message);
        return;
      }

      if (isAborted) return;

      if (isNoSpeech) {
        if (continuousMode) {
          consecutiveSilences += 1;
          if (consecutiveSilences < 2) {
            setState('listening', 'Sun raha hoon... agla sawal boliye');
            window.clearTimeout(autoListenTimer);
            autoListenTimer = window.setTimeout(() => {
              if (continuousMode && !sending && !listening) startListening();
            }, 600);
            return;
          } else {
            continuousMode = false;
            consecutiveSilences = 0;
            updateContinuousUI();
            setState('idle', 'Continuous listening paused. Tap mic to speak.');
            showToast('Voice pause ho gayi. Dobara bolne ke liye mic par tap karein.');
            return;
          }
        }
      }

      const message = isNoSpeech
        ? 'Awaaz nahi mili. Mic ke paas thoda clear boliye.'
        : 'Voice samajh nahi aayi. Dobara try kijiye.';
      setState('error', message);
      showToast(message);
      window.setTimeout(() => { if (!sending && !listening) setState('idle'); }, 2200);
    };
    recognition.onend = () => {
      listening = false;
      updateVoiceButtons(false);
      window.clearTimeout(submitTimer);
      const originalQuestion = (sessionTranscript || latestTranscript).trim();
      const finalQuestion = toRomanEnglish(originalQuestion);
      if (finalQuestion && !sending) {
        consecutiveSilences = 0;
        submitTimer = window.setTimeout(() => ask(finalQuestion, originalQuestion), 180);
      } else if (!sending && continuousMode && consecutiveSilences < 2 && app.dataset.state !== 'error') {
        window.clearTimeout(autoListenTimer);
        autoListenTimer = window.setTimeout(() => {
          if (continuousMode && !sending && !listening) startListening();
        }, 600);
      } else if (!sending && app.dataset.state !== 'error') {
        setState('idle');
      }
    };
  } else {
    setState('error', 'Is browser mein voice recognition available nahi hai; text box use kijiye.');
  }

  if ('speechSynthesis' in window) {
    const refreshVoices = () => { availableVoices = window.speechSynthesis.getVoices(); };
    refreshVoices();
    window.speechSynthesis.addEventListener?.('voiceschanged', refreshVoices);
  }

  els.voiceTriggers.forEach((button) => button.addEventListener('click', (event) => {
    triggerVoiceBlast(event.currentTarget);
    toggleListening();
  }));
  els.continuousPill?.addEventListener('click', () => setContinuousMode(!continuousMode));
  els.toggleContinuousBtn?.addEventListener('click', () => setContinuousMode(!continuousMode));
  document.querySelector('.keyboard-control')?.addEventListener('click', (event) => togglePanel(els.keyboard, event.currentTarget));
  document.querySelector('.profile-control')?.addEventListener('click', (event) => togglePanel(els.profileMenu, event.currentTarget));
  document.querySelector('.settings-control')?.addEventListener('click', (event) => togglePanel(els.settingsMenu, event.currentTarget));
  document.querySelector('.language-control')?.addEventListener('click', () => {
    if (!els.language) return;
    els.language.value = els.language.value === 'hi-IN' ? 'en-IN' : 'hi-IN';
    if (recognition) recognition.lang = els.language.value;
    showToast(els.language.value === 'hi-IN' ? 'Hindi voice selected.' : 'English voice selected.');
  });
  els.form.addEventListener('submit', (event) => {
    event.preventDefault();
    ask(els.input.value);
  });
  els.stop.addEventListener('click', () => {
    continuousMode = false;
    consecutiveSilences = 0;
    window.clearTimeout(autoListenTimer);
    window.speechSynthesis?.cancel();
    stopListening();
    updateContinuousUI();
    if (!sending) setState('idle', 'Assistant paused. Tap mic to speak.');
    showToast('Continuous conversation stopped.');
  });
  els.closeResponse.addEventListener('click', () => toggleResponse(false));
  els.language?.addEventListener('change', () => {
    if (recognition) recognition.lang = els.language.value;
  });
  document.querySelectorAll('[data-question]').forEach((button) => button.addEventListener('click', () => {
    els.input.value = button.dataset.question;
    continuousMode = true;
    updateContinuousUI();
    ask(button.dataset.question);
  }));
  document.addEventListener('click', (event) => {
    if (!event.target.closest('.popover,.keyboard-panel,.profile-control,.settings-control,.keyboard-control')) {
      document.querySelectorAll('.popover.open').forEach((panel) => panel.classList.remove('open'));
    }
  });

  function startListening() {
    if (!recognition || sending || listening) return;
    try {
      recognition.start();
    } catch (error) {
      window.clearTimeout(autoListenTimer);
      autoListenTimer = window.setTimeout(() => {
        if (!listening && !sending && continuousMode) {
          try { recognition.start(); } catch (_) {}
        }
      }, 400);
    }
  }

  function stopListening() {
    if (!recognition) return;
    window.clearTimeout(autoListenTimer);
    try {
      recognition.stop();
    } catch (_) {}
    listening = false;
    updateVoiceButtons(false);
  }

  function toggleListening() {
    if (!recognition || sending) return;

    if (window.speechSynthesis && window.speechSynthesis.speaking) {
      // Barge-in: interrupt speaking and start listening immediately
      window.speechSynthesis.cancel();
      continuousMode = true;
      consecutiveSilences = 0;
      updateContinuousUI();
      window.clearTimeout(autoListenTimer);
      autoListenTimer = window.setTimeout(() => startListening(), 250);
      return;
    }

    if (listening) {
      // User explicitly stopped listening
      continuousMode = false;
      consecutiveSilences = 0;
      updateContinuousUI();
      stopListening();
      setState('idle', 'Continuous listening paused. Tap to speak.');
      showToast('Continuous conversation paused.');
    } else {
      // User explicitly started listening
      continuousMode = true;
      consecutiveSilences = 0;
      updateContinuousUI();
      startListening();
    }
  }

  function setContinuousMode(enabled, notify = true) {
    continuousMode = !!enabled;
    consecutiveSilences = 0;
    updateContinuousUI();
    if (notify) {
      showToast(continuousMode ? 'Hands-Free continuous mode: ON' : 'Hands-Free continuous mode: OFF');
    }
    if (continuousMode && !listening && !sending && (!window.speechSynthesis || !window.speechSynthesis.speaking)) {
      startListening();
    }
  }

  function updateContinuousUI() {
    if (els.continuousPill) {
      els.continuousPill.classList.toggle('is-paused', !continuousMode);
      els.continuousPill.setAttribute('aria-pressed', String(continuousMode));
      const label = els.continuousPill.querySelector('[data-continuous-label]');
      if (label) label.textContent = continuousMode ? 'ON' : 'OFF';
    }
    if (els.toggleContinuousBtn) {
      els.toggleContinuousBtn.textContent = continuousMode ? 'Continuous Mode: Active (ON)' : 'Continuous Mode: Paused (OFF)';
    }
  }

  function triggerVoiceBlast(button) {
    const zone = button.closest('.voice-zone');
    if (!zone) return;
    window.clearTimeout(blastTimer);
    zone.classList.remove('is-blasting');
    void zone.offsetWidth;
    zone.classList.add('is-blasting');
    blastTimer = window.setTimeout(() => zone.classList.remove('is-blasting'), 1100);
  }

  async function ask(rawQuestion, originalSpeech = '') {
    const question = String(rawQuestion || '').trim();
    if (!question || sending) return;
    sending = true;
    if (listening) recognition?.stop();
    window.speechSynthesis?.cancel();
    els.input.value = '';
    els.send.disabled = true;
    toggleResponse(true);
    appendMessage(question, 'user-message');
    const pending = appendMessage('Aapka verified data check kar raha hoon...', 'assistant-message typing');
    setState('processing', 'Project data check ho raha hai...');

    try {
      const response = await fetch(app.dataset.endpoint, {
        method: 'POST',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        credentials: 'same-origin',
        body: JSON.stringify({
          message: question,
          spoken_message: String(originalSpeech || '').trim()
        })
      });
      const payload = await response.json().catch(() => ({}));
      if (!response.ok || !payload.success) {
        throw new Error(payload.answer || payload.message || 'Data load nahi ho saka.');
      }
      pending.className = 'message assistant-message';
      pending.querySelector('p').textContent = cleanText(payload.answer);
      setState('responding', 'Jawab taiyaar hai');
      speak(payload.answer);
    } catch (error) {
      pending.className = 'message error-message';
      pending.querySelector('p').textContent = error.message;
      setState('error', 'Request complete nahi ho saki');
      showToast(error.message);
    } finally {
      sending = false;
      els.send.disabled = false;
      els.input.focus();
    }
  }

  function appendMessage(text, className) {
    const article = document.createElement('article');
    article.className = `message ${className}`;
    const paragraph = document.createElement('p');
    paragraph.textContent = text;
    article.appendChild(paragraph);
    els.messages.appendChild(article);
    els.messages.scrollTop = els.messages.scrollHeight;
    return article;
  }

  function setListening(active) {
    listening = active;
    updateVoiceButtons(active);
    setState(active ? 'listening' : 'idle', active ? 'LISTENING • Sun raha hoon... clear boliye' : undefined);
  }

  function updateVoiceButtons(active) {
    els.voiceTriggers.forEach((button) => {
      button.classList.toggle('listening', active);
      button.setAttribute('aria-pressed', String(active));
      button.setAttribute('aria-label', active ? 'Stop voice input' : 'Start voice input');
    });
  }

  function setState(state, message) {
    app.dataset.state = state;
    app.dataset.assistantState = ({ processing: 'thinking', responding: 'speaking' }[state] || state);
    const copy = {
      idle: ['Tap karke bolna shuru karein', 'Ready for\nyour voice..'],
      listening: ['LISTENING • Sun raha hoon... clear boliye', 'Listening clearly to\nyour voice..'],
      processing: ['Project data check ho raha hai...', 'Checking your\nproject data..'],
      responding: ['Jawab taiyaar hai', 'Answer ready\nfor you..'],
      error: ['Voice service mein problem aayi', 'Please try\nagain..']
    }[state] || [];
    els.status.textContent = message || copy[0] || '';
    if (els.statusCopy) els.statusCopy.textContent = copy[1] || '';
  }

  function toggleResponse(open) {
    els.response.classList.toggle('open', open);
    els.response.setAttribute('aria-hidden', String(!open));
    els.keyboard?.classList.toggle('open', open);
    els.keyboard?.setAttribute('aria-hidden', String(!open));
  }

  function cleanText(text) {
    return String(text).replace(/\*\*/g, '').replace(/^#+\s*/gm, '').trim();
  }

  function toRomanEnglish(text) {
    const source = String(text || '').trim();
    if (!/[\u0900-\u097f]/.test(source)) return source;

    const commonWords = {
      'मेरी': 'meri', 'मेरा': 'mera', 'मेरे': 'mere', 'मुझे': 'mujhe', 'हमारी': 'hamari',
      'आज': 'aaj', 'कल': 'kal', 'टुडे': 'today', 'इनकम': 'income', 'कमाई': 'kamai',
      'बताओ': 'batao', 'बताइए': 'bataiye', 'कितनी': 'kitni', 'कितना': 'kitna', 'क्या': 'kya',
      'बिजनेस': 'business', 'टीम': 'team', 'वॉलेट': 'wallet', 'बैलेंस': 'balance',
      'प्रोफाइल': 'profile', 'अकाउंट': 'account', 'समरी': 'summary', 'पूरी': 'puri',
      'जानकारी': 'jankari', 'ट्रांजैक्शन': 'transaction', 'विड्रॉल': 'withdrawal',
      'ऑर्डर': 'order', 'टिकट': 'ticket', 'एक्टिवेशन': 'activation', 'नाम': 'naam',
      'आईडी': 'id', 'का': 'ka', 'की': 'ki', 'के': 'ke', 'है': 'hai', 'हैं': 'hain'
    };
    const vowels = { 'अ':'a','आ':'aa','इ':'i','ई':'i','उ':'u','ऊ':'u','ऋ':'ri','ए':'e','ऐ':'ai','ओ':'o','औ':'au' };
    const consonants = {
      'क':'k','ख':'kh','ग':'g','घ':'gh','ङ':'n','च':'ch','छ':'chh','ज':'j','झ':'jh','ञ':'n',
      'ट':'t','ठ':'th','ड':'d','ढ':'dh','ण':'n','त':'t','थ':'th','द':'d','ध':'dh','न':'n',
      'प':'p','फ':'ph','ब':'b','भ':'bh','म':'m','य':'y','र':'r','ल':'l','व':'v','श':'sh',
      'ष':'sh','स':'s','ह':'h','क़':'q','ख़':'kh','ग़':'g','ज़':'z','ड़':'d','ढ़':'dh','फ़':'f'
    };
    const matras = { 'ा':'aa','ि':'i','ी':'i','ु':'u','ू':'u','ृ':'ri','े':'e','ै':'ai','ो':'o','ौ':'au' };

    const transliterateWord = (word) => {
      if (commonWords[word]) return commonWords[word];
      let output = '';
      const chars = [...word];
      for (let index = 0; index < chars.length; index += 1) {
        const char = chars[index];
        if (vowels[char]) { output += vowels[char]; continue; }
        if (consonants[char]) {
          output += consonants[char];
          const next = chars[index + 1];
          if (next === '़') { index += 1; }
          const suffix = chars[index + 1];
          if (matras[suffix]) { output += matras[suffix]; index += 1; }
          else if (suffix === '्') { index += 1; }
          else { output += 'a'; }
          continue;
        }
        if (matras[char]) output += matras[char];
        else if (char === 'ं' || char === 'ँ') output += 'n';
        else if (char !== '्' && char !== '़') output += char;
      }
      return output.length > 3 && output.endsWith('a') ? output.slice(0, -1) : output;
    };

    return source
      .split(/(\s+|[,.!?।])/)
      .map((part) => /[\u0900-\u097f]/.test(part) ? transliterateWord(part) : part)
      .join('')
      .replace(/।/g, '.')
      .replace(/\s+/g, ' ')
      .trim();
  }

  function cleanTextForVoice(text) {
    if (!text) return '';
    return String(text)
      .replace(/\*\*/g, '')
      .replace(/__+/g, '')
      .replace(/^#+\s*/gm, '')
      .replace(/\$(\d+(?:,\d+)*(?:\.\d+)?)/g, '$1 dollars')
      .replace(/₹\s*(\d+(?:,\d+)*(?:\.\d+)?)/g, '$1 rupees')
      .replace(/\bRs\.?\s*(\d+)/gi, '$1 rupees')
      .replace(/\.00\b/g, '')
      .replace(/3X/gi, '3X')
      .replace(/[•|]/g, ', ')
      .replace(/^\s*[-*]\s+/gm, ', ')
      .replace(/:\s+/g, ', ')
      .replace(/\s+/g, ' ')
      .trim();
  }

  function getKoreVoice(selectedLang) {
    const voices = (availableVoices && availableVoices.length) ? availableVoices : (window.speechSynthesis?.getVoices() || []);
    if (!voices.length) return null;

    const isHindi = (selectedLang || els.language?.value || 'hi-IN').toLowerCase().startsWith('hi');

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
          /natural/i
        ];

    for (const pattern of neuralPriority) {
      const match = voices.find((v) => pattern.test(v.name) && !/desktop|david|mark|george|ravi/i.test(v.name));
      if (match) return match;
    }

    // 3. Any online/natural female voice matching language
    const langPrefix = isHindi ? 'hi' : 'en';
    const naturalVoice = voices.find((v) =>
      v.lang.toLowerCase().startsWith(langPrefix) &&
      /online|natural|neural/i.test(v.name) &&
      !/desktop|male|david|mark/i.test(v.name)
    );
    if (naturalVoice) return naturalVoice;

    // 4. Any female voice in target language
    const femaleVoice = voices.find((v) =>
      v.lang.toLowerCase().startsWith(langPrefix) &&
      /female|woman/i.test(v.name)
    );
    if (femaleVoice) return femaleVoice;

    // 5. First matching language voice
    return voices.find((v) => v.lang.toLowerCase().startsWith(langPrefix)) || voices[0] || null;
  }

  function speak(text) {
    if (!('speechSynthesis' in window)) {
      setState('idle');
      return;
    }
    window.speechSynthesis.cancel();

    const spokenText = cleanTextForVoice(text);
    if (!spokenText) {
      setState('idle');
      return;
    }

    const utterance = new SpeechSynthesisUtterance(spokenText);
    const selectedLang = els.language?.value || 'hi-IN';
    utterance.lang = selectedLang;

    // Smooth, human-like cadence matching Kore's natural delivery
    utterance.rate = 0.98;
    utterance.pitch = 1.02;
    utterance.volume = 1;

    const koreVoice = getKoreVoice(selectedLang);
    if (koreVoice) {
      utterance.voice = koreVoice;
    }

    // Chrome audio keepalive guard for longer utterances
    let keepAliveTimer = null;
    utterance.onstart = () => {
      setState('responding', 'Kore AI bol raha hai...');
      window.clearInterval(keepAliveTimer);
      keepAliveTimer = window.setInterval(() => {
        if (!window.speechSynthesis.speaking) {
          window.clearInterval(keepAliveTimer);
        } else {
          window.speechSynthesis.pause();
          window.speechSynthesis.resume();
        }
      }, 10000);
    };

    utterance.onend = () => {
      window.clearInterval(keepAliveTimer);
      if (continuousMode && !sending) {
        setState('listening', 'Sun raha hoon... agla sawal boliye');
        window.clearTimeout(autoListenTimer);
        autoListenTimer = window.setTimeout(() => {
          if (continuousMode && !sending && !listening) {
            startListening();
          }
        }, 700);
      } else if (!sending && !listening) {
        setState('idle');
      }
    };

    utterance.onerror = () => {
      window.clearInterval(keepAliveTimer);
      if (continuousMode && !sending) {
        window.clearTimeout(autoListenTimer);
        autoListenTimer = window.setTimeout(() => {
          if (continuousMode && !sending && !listening) {
            startListening();
          }
        }, 600);
      } else if (!sending && !listening) {
        setState('idle');
      }
    };

    window.speechSynthesis.speak(utterance);
  }

  function updateClock() {
    const now = new Date();
    els.date.textContent = new Intl.DateTimeFormat('en-IN', { day: '2-digit', month: 'short', year: 'numeric' }).format(now);
    els.time.textContent = new Intl.DateTimeFormat('en-IN', { hour: '2-digit', minute: '2-digit', hour12: true }).format(now);
  }

  function showToast(message) {
    window.clearTimeout(toastTimer);
    els.toast.textContent = message;
    els.toast.classList.add('show');
    toastTimer = window.setTimeout(() => els.toast.classList.remove('show'), 3500);
  }

  function togglePanel(panel, trigger) {
    if (!panel) return;
    const open = !panel.classList.contains('open');
    document.querySelectorAll('.popover.open, .keyboard-panel.open').forEach((item) => item.classList.remove('open'));
    panel.classList.toggle('open', open);
    panel.setAttribute('aria-hidden', String(!open));
    trigger?.setAttribute('aria-expanded', String(open));
    if (open && panel === els.keyboard) els.input.focus();
  }

  function fitReferenceCanvas() {
    if (!app.classList.contains('assistant-app')) return;
    const scale = Math.min(window.innerWidth / 1536, window.innerHeight / 1024);
    app.style.setProperty('--canvas-scale', String(scale));
    app.style.setProperty('--canvas-left', `${(window.innerWidth - 1536 * scale) / 2}px`);
    app.style.setProperty('--canvas-top', `${(window.innerHeight - 1024 * scale) / 2}px`);
  }
})();
