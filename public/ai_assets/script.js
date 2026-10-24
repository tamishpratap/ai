(function () {
  'use strict';

  // DOM Elements
  const welcomePanel = document.getElementById('welcomePanel');
  const chatMessages = document.getElementById('chatMessages');
  const promptForm = document.getElementById('promptForm');
  const promptInput = document.getElementById('promptInput');
  const sendButton = document.getElementById('sendButton');
  const newChatButton = document.getElementById('newChatButton');
  const suggestionGrid = document.getElementById('suggestionGrid');
  const quickChipsBar = document.getElementById('quickChipsBar');
  const voiceDictationBtn = document.getElementById('voiceDictationBtn');
  const clearInputBtn = document.getElementById('clearInputBtn');
  const toast = document.getElementById('toast');
  const toastMessage = toast ? toast.querySelector('.toast-message') || toast : null;
  const canvas = document.getElementById('neuralCanvas');

  // Backend Integration (100% Intact)
  const endpoint = window.DEMO_PROJECT_AI_ENDPOINT || '/project-ai/message';
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  // State
  let chatHistory = [];
  let isSending = false;
  let toastTimer = null;
  let isListening = false;
  let recognition = null;
  let currentSpeakingButton = null;

  // ========================================================
  // 1. NEURAL PARTICLE CANVAS BACKGROUND
  // ========================================================
  if (canvas && canvas.getContext) {
    const ctx = canvas.getContext('2d');
    let width = (canvas.width = window.innerWidth);
    let height = (canvas.height = window.innerHeight);
    let mouse = { x: null, y: null, maxDist: 120 };

    window.addEventListener('resize', () => {
      width = canvas.width = window.innerWidth;
      height = canvas.height = window.innerHeight;
    });

    window.addEventListener('mousemove', (e) => {
      mouse.x = e.clientX;
      mouse.y = e.clientY;
    });

    window.addEventListener('mouseleave', () => {
      mouse.x = null;
      mouse.y = null;
    });

    // Particle pool
    const particleCount = Math.min(Math.floor((width * height) / 18000), 55);
    const particles = [];

    class Particle {
      constructor() {
        this.reset();
      }
      reset() {
        this.x = Math.random() * width;
        this.y = Math.random() * height;
        this.vx = (Math.random() - 0.5) * 0.45;
        this.vy = (Math.random() - 0.5) * 0.45;
        this.radius = Math.random() * 1.6 + 0.6;
        this.alpha = Math.random() * 0.5 + 0.2;
        this.color = Math.random() > 0.5 ? '0, 245, 255' : '168, 85, 247';
      }
      update() {
        this.x += this.vx;
        this.y += this.vy;

        if (this.x < 0 || this.x > width) this.vx = -this.vx;
        if (this.y < 0 || this.y > height) this.vy = -this.vy;

        // Mouse reaction
        if (mouse.x !== null) {
          const dx = mouse.x - this.x;
          const dy = mouse.y - this.y;
          const dist = Math.sqrt(dx * dx + dy * dy);
          if (dist < mouse.maxDist) {
            const force = (mouse.maxDist - dist) / mouse.maxDist;
            this.x -= (dx / dist) * force * 1.5;
            this.y -= (dy / dist) * force * 1.5;
          }
        }
      }
      draw() {
        ctx.beginPath();
        ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
        ctx.fillStyle = `rgba(${this.color}, ${this.alpha})`;
        ctx.fill();
      }
    }

    for (let i = 0; i < particleCount; i++) {
      particles.push(new Particle());
    }

    function animateParticles() {
      ctx.clearRect(0, 0, width, height);

      // Draw connection lines
      for (let i = 0; i < particles.length; i++) {
        for (let j = i + 1; j < particles.length; j++) {
          const dx = particles[i].x - particles[j].x;
          const dy = particles[i].y - particles[j].y;
          const dist = Math.sqrt(dx * dx + dy * dy);
          if (dist < 110) {
            const alpha = (1 - dist / 110) * 0.16;
            ctx.beginPath();
            ctx.moveTo(particles[i].x, particles[i].y);
            ctx.lineTo(particles[j].x, particles[j].y);
            ctx.strokeStyle = `rgba(99, 102, 241, ${alpha})`;
            ctx.lineWidth = 0.8;
            ctx.stroke();
          }
        }
      }

      // Draw particles
      particles.forEach((p) => {
        p.update();
        p.draw();
      });

      requestAnimationFrame(animateParticles);
    }

    requestAnimationFrame(animateParticles);
  }

  // ========================================================
  // 2. UI NOTIFICATIONS & HELPERS
  // ========================================================
  function showToast(message) {
    if (!toast) return;
    if (toastMessage) {
      toastMessage.textContent = message;
    } else {
      toast.textContent = message;
    }
    toast.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
      toast.classList.remove('show');
    }, 2800);
  }

  function formatTime(date = new Date()) {
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  }

  function updateSendButton() {
    const text = promptInput.value.trim();
    const hasText = text.length > 0;
    sendButton.setAttribute('aria-disabled', String(!hasText || isSending));
    
    if (clearInputBtn) {
      clearInputBtn.style.display = hasText ? 'flex' : 'none';
    }
  }

  function autoResizeTextarea() {
    promptInput.style.height = 'auto';
    promptInput.style.height = Math.min(promptInput.scrollHeight, 160) + 'px';
  }

  function escapeHtml(text) {
    const map = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, (m) => map[m]);
  }

  // ========================================================
  // 3. ADVANCED MARKDOWN & CODE PARSER
  // ========================================================
  function formatMarkdown(text) {
    if (!text) return '';
    let html = escapeHtml(text);

    // Code blocks with syntax toolbar & individual copy button
    html = html.replace(/```([a-zA-Z0-9_-]*)\n([\s\S]*?)```/g, function (_, lang, code) {
      const displayLang = lang || 'CODE';
      const cleanCode = code.trim();
      return `
        <div class="code-block-container">
          <div class="code-block-header">
            <div class="code-dots">
              <span class="code-dot dot-red"></span>
              <span class="code-dot dot-yellow"></span>
              <span class="code-dot dot-green"></span>
            </div>
            <span class="code-lang-tag">${displayLang}</span>
            <button type="button" class="code-copy-btn" data-code="${encodeURIComponent(cleanCode)}">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
              </svg>
              <span>Copy Code</span>
            </button>
          </div>
          <pre><code>${cleanCode}</code></pre>
        </div>
      `;
    });

    // Inline code
    html = html.replace(/`([^`]+)`/g, '<code>$1</code>');

    // Headers
    html = html.replace(/^### (.*$)/gim, '<h3>$1</h3>');
    html = html.replace(/^## (.*$)/gim, '<h2>$1</h2>');
    html = html.replace(/^# (.*$)/gim, '<h1>$1</h1>');

    // Bold & italic
    html = html.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
    html = html.replace(/\*([^*]+)\*/g, '<em>$1</em>');

    // Blockquotes
    html = html.replace(/^\> (.*$)/gim, '<blockquote>$1</blockquote>');

    // Bullet points
    html = html.replace(/^\s*[-*]\s+(.*)$/gim, '<li>$1</li>');
    html = html.replace(/(<li>.*<\/li>)/gims, function (match) {
      return '<ul>' + match + '</ul>';
    });
    html = html.replace(/<\/ul>\s*<ul>/g, '');

    // Paragraphs
    const lines = html.split('\n');
    let parsed = '';
    for (let line of lines) {
      line = line.trim();
      if (!line) continue;
      if (
        line.startsWith('<h') ||
        line.startsWith('<div class="code-block-container"') ||
        line.startsWith('<ul>') ||
        line.startsWith('<li>') ||
        line.startsWith('</ul') ||
        line.startsWith('<blockquote>')
      ) {
        parsed += line;
      } else {
        parsed += '<p>' + line + '</p>';
      }
    }

    return parsed || html;
  }

  function scrollToBottom() {
    const scrollContainer = document.querySelector('.conversation-area');
    if (scrollContainer) {
      scrollContainer.scrollTo({
        top: scrollContainer.scrollHeight,
        behavior: 'smooth'
      });
    }
  }

  // ========================================================
  // 4. MESSAGE RENDERING (USER & AI)
  // ========================================================
  function appendUserMessage(text) {
    if (welcomePanel) {
      welcomePanel.style.display = 'none';
    }

    const row = document.createElement('div');
    row.className = 'message-row user';
    const currentTime = formatTime();

    row.innerHTML = `
      <div class="message-bubble">
        <p>${escapeHtml(text)}</p>
        <div class="message-time" style="margin-top: 6px; text-align: right; opacity: 0.8; font-size: 11px;">${currentTime}</div>
      </div>
      <div class="message-avatar" title="You">ME</div>
    `;
    chatMessages.appendChild(row);
    scrollToBottom();
  }

  function appendTypingIndicator() {
    const row = document.createElement('div');
    row.className = 'message-row assistant typing-row';
    row.id = 'typingIndicator';
    row.innerHTML = `
      <div class="message-avatar" title="Project AI">AI</div>
      <div class="message-bubble">
        <span class="typing-synth-label">Project AI is reasoning...</span>
        <div class="typing-dots">
          <div class="typing-dot"></div>
          <div class="typing-dot"></div>
          <div class="typing-dot"></div>
        </div>
      </div>
    `;
    chatMessages.appendChild(row);
    scrollToBottom();
  }

  function removeTypingIndicator() {
    const indicator = document.getElementById('typingIndicator');
    if (indicator) {
      indicator.remove();
    }
  }

  function appendAiMessage(rawText) {
    removeTypingIndicator();

    const row = document.createElement('div');
    row.className = 'message-row assistant';

    const formatted = formatMarkdown(rawText);
    const bubble = document.createElement('div');
    bubble.className = 'message-bubble';
    bubble.innerHTML = formatted;

    // Actions deck: Copy Text + Listen/Speak + Timestamp
    const actions = document.createElement('div');
    actions.className = 'bubble-actions';

    // 1. Copy Button
    const copyBtn = document.createElement('button');
    copyBtn.className = 'action-tool-btn copy-btn';
    copyBtn.type = 'button';
    copyBtn.innerHTML = `
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
      </svg>
      <span>Copy</span>
    `;
    copyBtn.addEventListener('click', async () => {
      try {
        await navigator.clipboard.writeText(rawText);
        showToast('Response copied to clipboard');
        copyBtn.querySelector('span').textContent = 'Copied!';
        setTimeout(() => {
          copyBtn.querySelector('span').textContent = 'Copy';
        }, 2000);
      } catch (err) {
        showToast('Failed to copy response');
      }
    });

    // 2. Text-to-Speech (Audio Readout) Button
    const speakBtn = document.createElement('button');
    speakBtn.className = 'action-tool-btn speak-btn';
    speakBtn.type = 'button';
    speakBtn.innerHTML = `
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
        <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
      </svg>
      <span>Listen</span>
    `;

    speakBtn.addEventListener('click', () => {
      toggleSpeech(rawText, speakBtn);
    });

    // 3. Time label
    const timeSpan = document.createElement('span');
    timeSpan.className = 'message-time';
    timeSpan.textContent = formatTime();

    actions.appendChild(copyBtn);
    if ('speechSynthesis' in window) {
      actions.appendChild(speakBtn);
    }
    actions.appendChild(timeSpan);
    bubble.appendChild(actions);

    row.innerHTML = `<div class="message-avatar" title="Project AI">AI</div>`;
    row.appendChild(bubble);

    chatMessages.appendChild(row);

    // Attach listener for code block copy buttons inside this bubble
    bubble.querySelectorAll('.code-copy-btn').forEach((btn) => {
      btn.addEventListener('click', async () => {
        const encoded = btn.getAttribute('data-code');
        if (encoded) {
          const codeText = decodeURIComponent(encoded);
          try {
            await navigator.clipboard.writeText(codeText);
            btn.querySelector('span').textContent = 'Copied!';
            showToast('Code copied');
            setTimeout(() => {
              btn.querySelector('span').textContent = 'Copy Code';
            }, 2000);
          } catch (e) {
            showToast('Failed to copy code');
          }
        }
      });
    });

    scrollToBottom();
  }

  // ========================================================
  // 5. CLIENT-SIDE SPEECH SYNTHESIS (LISTEN TO AI)
  // ========================================================
  function toggleSpeech(text, button) {
    if (!('speechSynthesis' in window)) {
      showToast('Speech synthesis not supported in this browser');
      return;
    }

    if (window.speechSynthesis.speaking) {
      window.speechSynthesis.cancel();
      if (currentSpeakingButton) {
        currentSpeakingButton.classList.remove('active');
        currentSpeakingButton.innerHTML = `
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2">
            <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
            <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
          </svg>
          <span>Listen</span>
        `;
      }
      if (currentSpeakingButton === button) {
        currentSpeakingButton = null;
        return;
      }
    }

    // Clean plain text for speech
    const cleanSpeech = text
      .replace(/```[\s\S]*?```/g, 'Code block omitted.')
      .replace(/[*#`_~]/g, '')
      .replace(/https?:\/\/\S+/g, 'link');

    const utterance = new SpeechSynthesisUtterance(cleanSpeech);
    utterance.rate = 1.0;
    utterance.pitch = 1.0;

    button.classList.add('active');
    button.innerHTML = `
      <div class="wave-bars">
        <span class="wave-bar"></span>
        <span class="wave-bar"></span>
        <span class="wave-bar"></span>
      </div>
      <span>Speaking...</span>
    `;
    currentSpeakingButton = button;

    utterance.onend = () => {
      button.classList.remove('active');
      button.innerHTML = `
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2">
          <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
          <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
        </svg>
        <span>Listen</span>
      `;
      currentSpeakingButton = null;
    };

    utterance.onerror = () => {
      button.classList.remove('active');
      button.innerHTML = `
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2">
          <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
          <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
        </svg>
        <span>Listen</span>
      `;
      currentSpeakingButton = null;
    };

    window.speechSynthesis.speak(utterance);
  }

  // ========================================================
  // 6. CLIENT-SIDE VOICE RECOGNITION (DICTATION)
  // ========================================================
  const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
  if (SpeechRecognition && voiceDictationBtn) {
    recognition = new SpeechRecognition();
    recognition.continuous = false;
    recognition.interimResults = true;
    recognition.lang = 'en-US';

    recognition.onstart = () => {
      isListening = true;
      voiceDictationBtn.classList.add('listening');
      showToast('Listening... Speak now');
    };

    recognition.onresult = (e) => {
      let transcript = '';
      for (let i = e.resultIndex; i < e.results.length; i++) {
        transcript += e.results[i][0].transcript;
      }
      promptInput.value = (promptInput.value + ' ' + transcript).trim();
      updateSendButton();
      autoResizeTextarea();
    };

    recognition.onerror = () => {
      isListening = false;
      voiceDictationBtn.classList.remove('listening');
      showToast('Voice dictation error');
    };

    recognition.onend = () => {
      isListening = false;
      voiceDictationBtn.classList.remove('listening');
      promptInput.focus();
    };

    voiceDictationBtn.addEventListener('click', () => {
      if (isListening) {
        recognition.stop();
      } else {
        recognition.start();
      }
    });
  } else if (voiceDictationBtn) {
    voiceDictationBtn.addEventListener('click', () => {
      showToast('Speech recognition is not supported in this browser.');
    });
  }

  // ========================================================
  // 7. CORE SEND MESSAGE TO BACKEND (100% UNCHANGED CONTRACT)
  // ========================================================
  async function sendMessage(text) {
    const cleanText = text.trim();
    if (!cleanText || isSending) return;

    isSending = true;
    updateSendButton();
    promptInput.value = '';
    autoResizeTextarea();

    appendUserMessage(cleanText);
    appendTypingIndicator();

    try {
      const response = await fetch(endpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
          message: cleanText,
          history: chatHistory.slice(-10)
        })
      });

      const data = await response.json();

      if (data.success && data.answer) {
        appendAiMessage(data.answer);

        // Record history for multi-turn conversations
        chatHistory.push({
          role: 'user',
          parts: [{ text: cleanText }]
        });
        chatHistory.push({
          role: 'model',
          parts: [{ text: data.answer }]
        });
      } else {
        const errorMsg = data.message || 'Unable to process your request at this moment.';
        appendAiMessage('⚠️ ' + errorMsg);
        showToast('Response error');
      }
    } catch (error) {
      console.error('Project AI Network Error:', error);
      appendAiMessage('⚠️ A network error occurred while reaching the AI engine. Please verify your connection or try again.');
      showToast('Network error');
    } finally {
      isSending = false;
      updateSendButton();
      promptInput.focus();
    }
  }

  // ========================================================
  // 8. EVENT LISTENERS
  // ========================================================
  promptInput.addEventListener('input', () => {
    updateSendButton();
    autoResizeTextarea();
  });

  promptInput.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      if (!isSending && promptInput.value.trim().length > 0) {
        sendMessage(promptInput.value);
      }
    }
  });

  sendButton.addEventListener('click', () => {
    if (!isSending && promptInput.value.trim().length > 0) {
      sendMessage(promptInput.value);
    }
  });

  // Clear Input Button
  clearInputBtn?.addEventListener('click', () => {
    promptInput.value = '';
    updateSendButton();
    autoResizeTextarea();
    promptInput.focus();
  });

  // Quick Chips
  quickChipsBar?.addEventListener('click', (e) => {
    const chip = e.target.closest('.quick-chip');
    if (!chip) return;
    const textToInsert = chip.getAttribute('data-insert') || chip.textContent.trim();
    promptInput.value = textToInsert;
    autoResizeTextarea();
    updateSendButton();
    promptInput.focus();
    promptInput.setSelectionRange(promptInput.value.length, promptInput.value.length);
  });

  // Suggestion Cards
  suggestionGrid?.addEventListener('click', (e) => {
    const card = e.target.closest('.suggestion-card');
    if (!card) return;
    const prompt = card.getAttribute('data-prompt');
    if (prompt) {
      sendMessage(prompt);
    }
  });

  // New Chat Button
  newChatButton?.addEventListener('click', () => {
    if (window.speechSynthesis && window.speechSynthesis.speaking) {
      window.speechSynthesis.cancel();
    }
    chatHistory = [];
    chatMessages.innerHTML = '';
    if (welcomePanel) {
      welcomePanel.style.display = 'block';
    }
    promptInput.value = '';
    autoResizeTextarea();
    updateSendButton();
    promptInput.focus();
    showToast('New workspace conversation started');
  });

  // Global Keyboard Shortcuts (Ctrl+K or Cmd+K for New Chat)
  window.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
      e.preventDefault();
      newChatButton?.click();
    }
  });

  // Init
  updateSendButton();
  promptInput.focus();

})();
