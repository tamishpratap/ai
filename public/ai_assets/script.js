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
  const ambientToggleBtn = document.getElementById('ambientToggleBtn');
  const bgVideo = document.querySelector('.ambient-video-bg');

  // Background Video Animation Safe Autoplay
  if (bgVideo) {
    const playPromise = bgVideo.play();
    if (playPromise !== undefined) {
      playPromise.catch(() => {
        window.addEventListener('click', () => bgVideo.play(), { once: true });
      });
    }
  }

  if (ambientToggleBtn && bgVideo) {
    ambientToggleBtn.addEventListener('click', () => {
      if (bgVideo.paused) {
        bgVideo.play();
        ambientToggleBtn.classList.remove('paused');
        showToast('Background animation resumed');
      } else {
        bgVideo.pause();
        ambientToggleBtn.classList.add('paused');
        showToast('Background animation paused');
      }
    });
  }

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
  // 1. COSMIC ZERO-GRAVITY 3D SPACE BALLS & MOUSE SIMULATION
  // ========================================================
  if (canvas && canvas.getContext) {
    const ctx = canvas.getContext('2d');
    let width = (canvas.width = window.innerWidth);
    let height = (canvas.height = window.innerHeight);
    let mouse = { x: null, y: null, prevX: null, prevY: null, speed: 0 };
    const shockwaves = [];
    const stardust = [];

    window.addEventListener('resize', () => {
      width = canvas.width = window.innerWidth;
      height = canvas.height = window.innerHeight;
    });

    window.addEventListener('mousemove', (e) => {
      if (mouse.x !== null) {
        const dx = e.clientX - mouse.x;
        const dy = e.clientY - mouse.y;
        mouse.speed = Math.sqrt(dx * dx + dy * dy);
      }
      mouse.prevX = mouse.x;
      mouse.prevY = mouse.y;
      mouse.x = e.clientX;
      mouse.y = e.clientY;

      // Emit cosmic stardust trail behind mouse
      if (Math.random() < 0.6) {
        stardust.push(new Stardust(e.clientX, e.clientY));
        if (stardust.length > 35) stardust.shift();
      }
    });

    window.addEventListener('mouseleave', () => {
      mouse.x = null;
      mouse.y = null;
    });

    // Cosmic Shockwave on click
    window.addEventListener('click', (e) => {
      shockwaves.push({
        x: e.clientX,
        y: e.clientY,
        radius: 12,
        maxRadius: 280,
        alpha: 0.85
      });
    });

    // Color palettes matching the background image
    const spaceColors = [
      { name: 'cyan', light: '#a5f3fc', main: '#00e5ff', deep: '#0284c7', glow: 'rgba(0, 229, 255, 0.5)' },
      { name: 'blue', light: '#bfdbfe', main: '#168bff', deep: '#1e3a8a', glow: 'rgba(22, 139, 255, 0.5)' },
      { name: 'purple', light: '#e9d5ff', main: '#a855f7', deep: '#6b21a8', glow: 'rgba(168, 85, 247, 0.5)' },
      { name: 'magenta', light: '#fbcfe8', main: '#ec4899', deep: '#9d174d', glow: 'rgba(236, 72, 153, 0.5)' },
      { name: 'indigo', light: '#c7d2fe', main: '#5145ff', deep: '#312e81', glow: 'rgba(81, 69, 255, 0.5)' }
    ];

    class Stardust {
      constructor(x, y) {
        this.x = x + (Math.random() - 0.5) * 14;
        this.y = y + (Math.random() - 0.5) * 14;
        this.vx = (Math.random() - 0.5) * 1.5;
        this.vy = (Math.random() - 0.5) * 1.5;
        this.radius = Math.random() * 2 + 0.8;
        this.alpha = 0.85;
        const colorObj = spaceColors[Math.floor(Math.random() * spaceColors.length)];
        this.color = colorObj.main;
      }
      update() {
        this.x += this.vx;
        this.y += this.vy;
        this.alpha -= 0.025;
      }
      draw() {
        if (this.alpha <= 0) return;
        ctx.beginPath();
        ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
        ctx.fillStyle = this.color;
        ctx.globalAlpha = Math.max(0, this.alpha);
        ctx.shadowColor = this.color;
        ctx.shadowBlur = 6;
        ctx.fill();
        ctx.shadowBlur = 0;
        ctx.globalAlpha = 1.0;
      }
    }

    class SpaceBall {
      constructor() {
        this.reset();
      }
      reset() {
        this.x = Math.random() * width;
        this.y = Math.random() * height;
        // Zero-gravity drift velocity
        this.vx = (Math.random() - 0.5) * 0.45;
        this.vy = (Math.random() - 0.5) * 0.45;
        this.z = Math.random() * 1.1 + 0.6; // 3D depth layer
        this.baseRadius = Math.random() * 14 + 8; // 8px to 22px
        this.radius = this.baseRadius * this.z;
        this.color = spaceColors[Math.floor(Math.random() * spaceColors.length)];
        this.pulse = Math.random() * Math.PI * 2;
      }
      update() {
        this.pulse += 0.02;
        this.x += this.vx;
        this.y += this.vy;

        // Space wrap around with soft margins
        const pad = this.radius * 2;
        if (this.x < -pad) this.x = width + pad;
        if (this.x > width + pad) this.x = -pad;
        if (this.y < -pad) this.y = height + pad;
        if (this.y > height + pad) this.y = -pad;

        // Friction damping back to gentle zero-gravity drift
        this.vx *= 0.985;
        this.vy *= 0.985;
        if (Math.abs(this.vx) < 0.2) this.vx += (Math.random() - 0.5) * 0.08;
        if (Math.abs(this.vy) < 0.2) this.vy += (Math.random() - 0.5) * 0.08;

        // Interactive Mouse Gravitational Physics
        if (mouse.x !== null) {
          const dx = mouse.x - this.x;
          const dy = mouse.y - this.y;
          const dist = Math.sqrt(dx * dx + dy * dy);
          const maxDist = 200 * this.z;

          if (dist < maxDist && dist > 1) {
            // Smooth gravitational force: pushes and swirls
            const force = (maxDist - dist) / maxDist;
            const angle = Math.atan2(dy, dx);
            // Repulsion push
            this.vx -= Math.cos(angle) * force * 1.8;
            this.vy -= Math.sin(angle) * force * 1.8;
            // Space orbital swirl
            this.vx += -Math.sin(angle) * force * 0.8;
            this.vy += Math.cos(angle) * force * 0.8;
          }
        }

        // React to cosmic shockwaves
        for (let sw of shockwaves) {
          const swdx = this.x - sw.x;
          const swdy = this.y - sw.y;
          const swdist = Math.sqrt(swdx * swdx + swdy * swdy);
          if (Math.abs(swdist - sw.radius) < 32) {
            const push = (1 - sw.radius / sw.maxRadius) * 2.4;
            const swAngle = Math.atan2(swdy, swdx);
            this.vx += Math.cos(swAngle) * push;
            this.vy += Math.sin(swAngle) * push;
          }
        }
      }
      draw() {
        const currentRadius = this.radius + Math.sin(this.pulse) * 1.2;

        // 3D Spherical Radial Gradient Lighting
        const grad = ctx.createRadialGradient(
          this.x - currentRadius * 0.32,
          this.y - currentRadius * 0.32,
          currentRadius * 0.08,
          this.x,
          this.y,
          currentRadius
        );
        grad.addColorStop(0, '#ffffff'); // Glint specular highlight
        grad.addColorStop(0.22, this.color.light);
        grad.addColorStop(0.58, this.color.main);
        grad.addColorStop(0.85, this.color.deep);
        grad.addColorStop(1, 'rgba(4, 8, 26, 0.9)');

        // Outer glow
        ctx.shadowColor = this.color.main;
        ctx.shadowBlur = currentRadius * 0.85;

        ctx.beginPath();
        ctx.arc(this.x, this.y, currentRadius, 0, Math.PI * 2);
        ctx.fillStyle = grad;
        ctx.fill();

        ctx.shadowBlur = 0; // reset

        // Glass Rim Reflection Crescent
        ctx.beginPath();
        ctx.arc(this.x, this.y, currentRadius * 0.82, Math.PI * 0.65, Math.PI * 1.35);
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.45)';
        ctx.lineWidth = Math.max(1, currentRadius * 0.09);
        ctx.stroke();
      }
    }

    // Spawn space balls (matching density of background)
    const ballCount = Math.min(Math.floor((width * height) / 22000), 38);
    const spaceBalls = [];
    for (let i = 0; i < ballCount; i++) {
      spaceBalls.push(new SpaceBall());
    }

    function animateSpaceSimulation() {
      ctx.clearRect(0, 0, width, height);

      // 1. Draw Shockwaves
      for (let i = shockwaves.length - 1; i >= 0; i--) {
        const sw = shockwaves[i];
        sw.radius += 5;
        sw.alpha -= 0.016;
        if (sw.radius > sw.maxRadius || sw.alpha <= 0) {
          shockwaves.splice(i, 1);
          continue;
        }
        ctx.beginPath();
        ctx.arc(sw.x, sw.y, sw.radius, 0, Math.PI * 2);
        ctx.strokeStyle = `rgba(0, 229, 255, ${sw.alpha * 0.5})`;
        ctx.lineWidth = 2.5;
        ctx.stroke();
      }

      // 2. Draw Quantum Energy Lines between balls and to Mouse
      for (let i = 0; i < spaceBalls.length; i++) {
        // Line to mouse
        if (mouse.x !== null) {
          const mdx = spaceBalls[i].x - mouse.x;
          const mdy = spaceBalls[i].y - mouse.y;
          const mdist = Math.sqrt(mdx * mdx + mdy * mdy);
          if (mdist < 140) {
            const malpha = (1 - mdist / 140) * 0.4;
            ctx.beginPath();
            ctx.moveTo(spaceBalls[i].x, spaceBalls[i].y);
            ctx.lineTo(mouse.x, mouse.y);
            ctx.strokeStyle = `rgba(0, 229, 255, ${malpha})`;
            ctx.lineWidth = 1;
            ctx.stroke();
          }
        }

        // Line between close balls
        for (let j = i + 1; j < spaceBalls.length; j++) {
          const dx = spaceBalls[i].x - spaceBalls[j].x;
          const dy = spaceBalls[i].y - spaceBalls[j].y;
          const dist = Math.sqrt(dx * dx + dy * dy);
          if (dist < 120) {
            const alpha = (1 - dist / 120) * 0.15;
            ctx.beginPath();
            ctx.moveTo(spaceBalls[i].x, spaceBalls[i].y);
            ctx.lineTo(spaceBalls[j].x, spaceBalls[j].y);
            ctx.strokeStyle = `rgba(168, 85, 247, ${alpha})`;
            ctx.lineWidth = 0.8;
            ctx.stroke();
          }
        }
      }

      // 3. Update & Draw Stardust
      for (let i = stardust.length - 1; i >= 0; i--) {
        stardust[i].update();
        stardust[i].draw();
        if (stardust[i].alpha <= 0) {
          stardust.splice(i, 1);
        }
      }

      // 4. Update & Draw 3D Space Balls (sorted by z-depth for real parallax)
      spaceBalls.sort((a, b) => a.z - b.z);
      spaceBalls.forEach((ball) => {
        ball.update();
        ball.draw();
      });

      requestAnimationFrame(animateSpaceSimulation);
    }

    requestAnimationFrame(animateSpaceSimulation);
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
        <div class="user-message-time">${currentTime}</div>
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
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
  // 5. CLIENT-SIDE SPEECH SYNTHESIS (LISTEN TO AI - KORE VOICE)
  // ========================================================
  let availableVoices = [];
  function populateVoices() {
    if ('speechSynthesis' in window) {
      availableVoices = window.speechSynthesis.getVoices() || [];
    }
  }
  if ('speechSynthesis' in window) {
    populateVoices();
    window.speechSynthesis.onvoiceschanged = populateVoices;
  }

  function getKoreVoice(text) {
    const voices = (availableVoices && availableVoices.length) 
      ? availableVoices 
      : (window.speechSynthesis?.getVoices() || []);
    if (!voices.length) return null;

    const hasHindi = /[\u0900-\u097f]/.test(text);

    // 1. Direct match for 'kore' if available in system or extensions
    const directKore = voices.find((v) => /kore/i.test(v.name));
    if (directKore) return directKore;

    // 2. High-fidelity Smooth Neural / Natural Voices (Kore signature: warm, clear, natural female)
    const neuralPriority = hasHindi
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
          /samantha/i,
          /natural/i
        ];

    for (const pattern of neuralPriority) {
      const match = voices.find((v) => pattern.test(v.name) && !/desktop|david|mark|george|ravi/i.test(v.name));
      if (match) return match;
    }

    // 3. Any online/natural female voice matching language
    const langPrefix = hasHindi ? 'hi' : 'en';
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

  function cleanTextForKoreVoice(text) {
    if (!text) return '';
    return String(text)
      .replace(/```[\s\S]*?```/g, 'Code block omitted.')
      .replace(/`([^`]+)`/g, '$1')
      .replace(/\*\*/g, '')
      .replace(/__+/g, '')
      .replace(/^#+\s*/gm, '')
      .replace(/\$(\d+(?:,\d+)*(?:\.\d+)?)/g, '$1 dollars')
      .replace(/₹\s*(\d+(?:,\d+)*(?:\.\d+)?)/g, '$1 rupees')
      .replace(/\bRs\.?\s*(\d+)/gi, '$1 rupees')
      .replace(/[•|]/g, ', ')
      .replace(/^\s*[-*]\s+/gm, ', ')
      .replace(/:\s+/g, ', ')
      .replace(/https?:\/\/\S+/g, 'link')
      .replace(/[*_~]/g, '')
      .replace(/\s+/g, ' ')
      .trim();
  }

  let keepAliveTimer = null;

  function toggleSpeech(text, button) {
    if (!('speechSynthesis' in window)) {
      showToast('Speech synthesis not supported in this browser');
      return;
    }

    if (window.speechSynthesis.speaking) {
      window.speechSynthesis.cancel();
      window.clearInterval(keepAliveTimer);
      if (currentSpeakingButton) {
        currentSpeakingButton.classList.remove('active');
        currentSpeakingButton.innerHTML = `
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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

    const spokenText = cleanTextForKoreVoice(text);
    if (!spokenText) return;

    const utterance = new SpeechSynthesisUtterance(spokenText);
    const hasHindi = /[\u0900-\u097f]/.test(text);
    utterance.lang = hasHindi ? 'hi-IN' : 'en-US';

    // Smooth, human-like cadence matching Kore's natural signature delivery
    utterance.rate = 0.98;
    utterance.pitch = 1.02;
    utterance.volume = 1;

    const koreVoice = getKoreVoice(text);
    if (koreVoice) {
      utterance.voice = koreVoice;
    }

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

    // Browser audio keep-alive guard for smooth, continuous speech playback
    window.clearInterval(keepAliveTimer);
    keepAliveTimer = window.setInterval(() => {
      if (window.speechSynthesis.speaking && !window.speechSynthesis.paused) {
        window.speechSynthesis.pause();
        window.speechSynthesis.resume();
      } else {
        window.clearInterval(keepAliveTimer);
      }
    }, 10000);

    utterance.onend = () => {
      window.clearInterval(keepAliveTimer);
      button.classList.remove('active');
      button.innerHTML = `
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
          <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
        </svg>
        <span>Listen</span>
      `;
      currentSpeakingButton = null;
    };

    utterance.onerror = () => {
      window.clearInterval(keepAliveTimer);
      button.classList.remove('active');
      button.innerHTML = `
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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

  // ========================================================
  // INTERACTIVE COSMIC MOUSE SPOTLIGHT & CURSOR FOLLOWER
  // ========================================================
  const mouseGlow = document.getElementById('mouseGlowFollower');
  const mouseDot = document.getElementById('mousePointerDot');
  let mouseTargetX = window.innerWidth / 2;
  let mouseTargetY = window.innerHeight / 2;
  let mouseCurrentX = mouseTargetX;
  let mouseCurrentY = mouseTargetY;
  let isMouseActive = false;

  window.addEventListener('mousemove', (e) => {
    mouseTargetX = e.clientX;
    mouseTargetY = e.clientY;
    if (!isMouseActive) {
      isMouseActive = true;
      mouseGlow?.classList.add('active');
      mouseDot?.classList.add('active');
    }
    if (mouseDot) {
      mouseDot.style.left = e.clientX + 'px';
      mouseDot.style.top = e.clientY + 'px';
    }
  });

  window.addEventListener('mouseleave', () => {
    isMouseActive = false;
    mouseGlow?.classList.remove('active');
    mouseDot?.classList.remove('active');
  });

  function renderMouseFollower() {
    if (isMouseActive && mouseGlow) {
      mouseCurrentX += (mouseTargetX - mouseCurrentX) * 0.12;
      mouseCurrentY += (mouseTargetY - mouseCurrentY) * 0.12;
      mouseGlow.style.left = mouseCurrentX + 'px';
      mouseGlow.style.top = mouseCurrentY + 'px';
    }
    requestAnimationFrame(renderMouseFollower);
  }
  requestAnimationFrame(renderMouseFollower);

  // Hover expansion on interactive buttons & cards
  const interactiveSelectors = 'button, a, input, textarea, .feature-card, .floating-side-card, .nav-pill';
  document.addEventListener('mouseover', (e) => {
    if (e.target.closest(interactiveSelectors)) {
      mouseDot?.classList.add('hover-active');
    }
  });
  document.addEventListener('mouseout', (e) => {
    if (e.target.closest(interactiveSelectors)) {
      mouseDot?.classList.remove('hover-active');
    }
  });

  // 3D Perspective Tilt on Card Hover
  document.querySelectorAll('.feature-card, .floating-side-card').forEach((card) => {
    card.addEventListener('mousemove', (e) => {
      const rect = card.getBoundingClientRect();
      const x = e.clientX - rect.left - rect.width / 2;
      const y = e.clientY - rect.top - rect.height / 2;
      const rotateX = -(y / rect.height) * 12;
      const rotateY = (x / rect.width) * 12;
      card.style.transform = `perspective(800px) rotateX(${rotateX.toFixed(2)}deg) rotateY(${rotateY.toFixed(2)}deg) translateY(-4px) scale(1.02)`;
    });
    card.addEventListener('mouseleave', () => {
      card.style.transform = '';
    });
  });

  // Floating Side Cards Interactions
  const floatLeftCard = document.getElementById('floatLeftCard');
  floatLeftCard?.addEventListener('click', () => {
    promptInput.value = 'Think deeply and provide a rigorous, comprehensive architectural breakdown of: ';
    autoResizeTextarea();
    updateSendButton();
    promptInput.focus();
    promptInput.setSelectionRange(promptInput.value.length, promptInput.value.length);
  });

  // ========================================================
  // ANIMATED CHAT ASSISTANT TYPEWRITER & INTERACTION
  // ========================================================
  const chatAssistantStage = document.getElementById('chatAssistantStage');
  const assistantTypewriter = document.getElementById('assistantTypewriter');

  if (assistantTypewriter) {
    const dynamicPhrases = [
      'think deeply, analyze data, and architect solutions',
      'refactor, debug, and optimize complex software',
      'draft launch roadmaps, strategies, and KPIs',
      'synthesize insights powered by Gemini 2.5'
    ];
    let phraseIndex = 0;
    let charIndex = 0;
    let isDeleting = false;
    let typingSpeed = 45;

    function typeLoop() {
      const currentPhrase = dynamicPhrases[phraseIndex];
      if (isDeleting) {
        assistantTypewriter.textContent = currentPhrase.substring(0, charIndex - 1);
        charIndex--;
        typingSpeed = 25;
      } else {
        assistantTypewriter.textContent = currentPhrase.substring(0, charIndex + 1);
        charIndex++;
        typingSpeed = 50;
      }

      if (!isDeleting && charIndex === currentPhrase.length) {
        typingSpeed = 2200; // Pause at end of phrase
        isDeleting = true;
      } else if (isDeleting && charIndex === 0) {
        isDeleting = false;
        phraseIndex = (phraseIndex + 1) % dynamicPhrases.length;
        typingSpeed = 400; // Pause before starting next phrase
      }

      setTimeout(typeLoop, typingSpeed);
    }
    setTimeout(typeLoop, 800);
  }

  chatAssistantStage?.addEventListener('click', (e) => {
    const rect = chatAssistantStage.getBoundingClientRect();
    const cx = e.clientX || (rect.left + rect.width / 2);
    const cy = e.clientY || (rect.top + rect.height / 2);
    window.dispatchEvent(new MouseEvent('click', { clientX: cx, clientY: cy }));
    showToast('✦ Chat Assistant online and ready');
  });

  // Suggestion Cards (5 Horizontal Cards)
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

