(() => {
  'use strict';

  // State Variables
  let currentLanguage = 'hi'; // 'hi' or 'en'
  let speaking = false;
  let isPaused = false;
  let speechLines = [];
  let currentLineIndex = 0;
  let availableVoices = [];
  let toastTimer = null;

  // DOM Elements
  const els = {
    appShell: document.querySelector('.explainer-shell'),
    mainMicBtn: document.getElementById('mainMicBtn'),
    micIcon: document.getElementById('micIcon'),
    voiceStatus: document.getElementById('voiceStatus'),
    nowPlayingPill: document.getElementById('nowPlayingPill'),
    lineCounterPill: document.getElementById('lineCounterPill'),
    audioVisualizer: document.getElementById('audioVisualizer'),

    answerBox: document.getElementById('answerBox'),
    answerTitle: document.getElementById('answerTitle'),
    answerBody: document.getElementById('answerBody'),
    replayAudioBtn: document.getElementById('replayAudioBtn'),
    stopSpeechBtn: document.getElementById('stopSpeechBtn'),
    langBtns: document.querySelectorAll('.lang-btn'),

    // 3D Cards
    cardsGrid3d: document.getElementById('cardsGrid3d'),
    cards3d: document.querySelectorAll('.card-3d'),

    // Upload Modal (Admin PDF / TXT)
    openUploadModalBtn: document.getElementById('openUploadModalBtn'),
    closeUploadModalBtn: document.getElementById('closeUploadModalBtn'),
    cancelUploadBtn: document.getElementById('cancelUploadBtn'),
    uploadModalOverlay: document.getElementById('uploadModalOverlay'),
    adminUploadForm: document.getElementById('adminUploadForm'),
    fileDropzone: document.getElementById('fileDropzone'),
    planFileInput: document.getElementById('planFileInput'),
    fileChosenInfo: document.getElementById('fileChosenInfo'),
    chosenFileName: document.getElementById('chosenFileName'),
    chosenFileSize: document.getElementById('chosenFileSize'),
    removeFileBtn: document.getElementById('removeFileBtn'),
    uploadProgressWrap: document.getElementById('uploadProgressWrap'),
    uploadProgressBar: document.getElementById('uploadProgressBar'),
    uploadProgressMsg: document.getElementById('uploadProgressMsg'),
    startUploadBtn: document.getElementById('startUploadBtn'),

    // Document Text Modal
    openDocModalBtn: document.getElementById('openDocModalBtn'),
    closeDocModalBtn: document.getElementById('closeDocModalBtn'),
    docModalOverlay: document.getElementById('docModalOverlay'),
    docTextarea: document.getElementById('docTextarea'),
    saveDocBtn: document.getElementById('saveDocBtn'),
    stopAudioBtn: document.getElementById('stopAudioBtn'),

    // Toast
    toast: document.getElementById('planToast')
  };

  const speechEndpoint = els.appShell?.getAttribute('data-speech-endpoint') || '/ai/plan-explainer/speech';
  const uploadEndpoint = els.appShell?.getAttribute('data-upload-endpoint') || '/ai/plan-explainer/upload-document';
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  function escapeHtml(str) {
    if (!str) return '';
    return str
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  // 1. Speech Synthesis - Natural Human Voice (Kore Persona)
  function populateVoices() {
    if ('speechSynthesis' in window) {
      availableVoices = window.speechSynthesis.getVoices();
    }
  }
  if ('speechSynthesis' in window) {
    populateVoices();
    window.speechSynthesis.onvoiceschanged = populateVoices;
  }

  function getKoreVoice() {
    if (currentLanguage === 'hi') {
      return availableVoices.find(v => 
        (v.lang.includes('hi') || v.lang.includes('IN')) && 
        (v.name.includes('Swara') || v.name.includes('Google') || v.name.includes('Female') || v.name.includes('Natural'))
      ) || availableVoices.find(v => v.lang.includes('hi'));
    } else {
      return availableVoices.find(v => 
        v.lang.includes('en') && 
        (v.name.includes('Jenny') || v.name.includes('Aria') || v.name.includes('Natural') || v.name.includes('Samantha') || v.name.includes('Google US'))
      ) || availableVoices.find(v => v.lang.includes('en'));
    }
  }

  let activeUtterance = null;
  let keepAliveTimer = null;

  function speakHumanText(text, onCompleteCallback) {
    if (!('speechSynthesis' in window) || !text) {
      if (onCompleteCallback) onCompleteCallback();
      return;
    }

    window.speechSynthesis.cancel();
    speaking = true;
    isPaused = false;
    setSpeakingUI(true);

    const clean = text.replace(/[*_#`\[\]]/g, '').replace(/\s+/g, ' ').trim();
    activeUtterance = new SpeechSynthesisUtterance(clean);

    activeUtterance.rate = 1.0;
    activeUtterance.pitch = 1.04;
    activeUtterance.lang = currentLanguage === 'hi' ? 'hi-IN' : 'en-US';

    const voice = getKoreVoice();
    if (voice) activeUtterance.voice = voice;

    // Browser keep-alive ping for smooth, continuous speech playback
    window.clearInterval(keepAliveTimer);
    keepAliveTimer = window.setInterval(() => {
      if (window.speechSynthesis.speaking && !window.speechSynthesis.paused) {
        window.speechSynthesis.pause();
        window.speechSynthesis.resume();
      } else {
        window.clearInterval(keepAliveTimer);
      }
    }, 12000);

    activeUtterance.onend = () => {
      window.clearInterval(keepAliveTimer);
      if (!isPaused && typeof onCompleteCallback === 'function') {
        onCompleteCallback();
      }
    };

    activeUtterance.onerror = (e) => {
      window.clearInterval(keepAliveTimer);
      console.warn('Speech error:', e);
      speaking = false;
      setSpeakingUI(false);
    };

    window.speechSynthesis.speak(activeUtterance);
  }

  function stopAudio() {
    window.clearInterval(keepAliveTimer);
    if ('speechSynthesis' in window) {
      window.speechSynthesis.cancel();
    }
    speaking = false;
    isPaused = false;
    setSpeakingUI(false);
    if (els.stopSpeechBtn) els.stopSpeechBtn.style.display = 'none';

    // Remove active line speaking highlight
    const activeEl = document.querySelector('.teleprompter-line.is-speaking');
    if (activeEl) activeEl.classList.remove('is-speaking');

    setStatus(currentLanguage === 'hi' ? 'स्पीच रोक दी गई है। दोबारा सुनने के लिए माइक पर क्लिक करें।' : 'Speech stopped. Click microphone to play again.', 'idle');
    if (els.nowPlayingPill) {
      els.nowPlayingPill.innerHTML = `<i class="fa-solid fa-stop"></i> ${currentLanguage === 'hi' ? 'रोका गया' : 'Stopped'}`;
    }
  }

  function setSpeakingUI(val) {
    els.appShell?.setAttribute('data-state', val ? 'speaking' : 'idle');
    els.mainMicBtn?.setAttribute('aria-pressed', val ? 'true' : 'false');

    if (els.micIcon) {
      els.micIcon.className = val ? 'fa-solid fa-volume-high' : 'fa-solid fa-microphone';
    }

    if (els.stopSpeechBtn) {
      els.stopSpeechBtn.style.display = val ? 'inline-flex' : 'none';
    }
  }

  function setStatus(text, state) {
    if (els.voiceStatus) els.voiceStatus.textContent = text;
    if (state && els.appShell) els.appShell.setAttribute('data-state', state);
  }

  // 2. Format Line Text for Clean Display
  function formatLineHtml(line) {
    let raw = line.text || '';
    if (line.type === 'heading') {
      const title = raw.replace(/^#\s*/, '');
      return `<h3 class="tele-heading"><i class="fa-solid fa-shield-halved"></i> ${escapeHtml(title)}</h3>`;
    }
    if (line.type === 'subheading') {
      const subtitle = raw.replace(/^##+\s*/, '');
      return `<h4 class="tele-subheading"><span class="sub-dot"></span> ${escapeHtml(subtitle)}</h4>`;
    }
    if (line.type === 'bullet') {
      let btext = raw.replace(/^[-•*]\s*/, '');
      let formatted = escapeHtml(btext)
        .replace(/(\$\d[\d,]*(?:\.\d+)?)/g, '<span class="num-highlight">$1</span>')
        .replace(/(\d+(?:\.\d+)?%)/g, '<span class="num-highlight">$1</span>')
        .replace(/(Starter|Standard|Advance|Elite|Master|VIP Whale|Silver|Gold|Diamond|3X|5X)/gi, '<strong class="keyword-highlight">$1</strong>');
      return `<div class="tele-bullet"><span class="bullet-gem"><i class="fa-solid fa-caret-right"></i></span><span class="bullet-content">${formatted}</span></div>`;
    }
    let ptext = escapeHtml(raw)
      .replace(/(\$\d[\d,]*(?:\.\d+)?)/g, '<span class="num-highlight">$1</span>')
      .replace(/(\d+(?:\.\d+)?%)/g, '<span class="num-highlight">$1</span>')
      .replace(/(Quantum ARB|USDT|Binance|OKX|Bybit|Coinbase)/gi, '<strong class="brand-highlight">$1</strong>');
    return `<p class="tele-paragraph">${ptext}</p>`;
  }

  // 3. Render Entire Document in One Go (Line-to-Line Teleprompter)
  function renderFullDocumentLines() {
    if (!els.answerBody) return;
    els.answerBody.innerHTML = '';

    const wrap = document.createElement('div');
    wrap.className = 'teleprompter-lines-wrap';

    speechLines.forEach((line, idx) => {
      const row = document.createElement('div');
      row.className = `teleprompter-line line-type-${line.type}`;
      row.id = `docLine_${idx}`;
      row.setAttribute('data-idx', idx);
      if (line.topic) row.setAttribute('data-topic', line.topic);
      row.setAttribute('title', currentLanguage === 'hi' ? 'यहाँ से सुनने के लिए क्लिक करें' : 'Click to hear speech from this line');

      row.innerHTML = `
        <div class="line-gutter">
          <span class="line-index">${idx + 1}</span>
          <span class="line-speaking-wave">
            <span></span><span></span><span></span>
          </span>
          <span class="line-play-hint"><i class="fa-solid fa-play"></i></span>
        </div>
        <div class="line-body-wrap">
          ${formatLineHtml(line)}
        </div>
      `;

      row.addEventListener('click', () => {
        playFromLine(idx);
      });

      wrap.appendChild(row);
    });

    els.answerBody.appendChild(wrap);
  }

  // 4. Load Speech Data from API
  async function loadSpeechData(autoStart = false) {
    try {
      setStatus(currentLanguage === 'hi' ? 'वेरिफाइड डॉक्यूमेंट लोड हो रहा है...' : 'Loading verified plan document...', 'thinking');
      const res = await fetch(`${speechEndpoint}?language=${currentLanguage}`);
      const data = await res.json();

      if (data && data.success && Array.isArray(data.lines) && data.lines.length > 0) {
        speechLines = data.lines;
        renderFullDocumentLines();

        if (els.lineCounterPill) {
          els.lineCounterPill.innerHTML = `<i class="fa-solid fa-file-lines"></i> ${speechLines.length} ${currentLanguage === 'hi' ? 'लाइनें' : 'Lines'}`;
        }

        setStatus(currentLanguage === 'hi' 
          ? 'पूरा प्लान डॉक्यूमेंट तैयार है। माइक पर क्लिक करें, AI बिना रुके पूरी स्पीच बोलेगी और टेक्स्ट साथ-साथ हाईलाइट होगा।' 
          : 'Full document ready. Click microphone to hear complete plan non-stop with live synced text.', 
          'idle'
        );

        if (els.nowPlayingPill) {
          els.nowPlayingPill.innerHTML = `<i class="fa-solid fa-circle-check"></i> ${currentLanguage === 'hi' ? 'तैयार' : 'Ready'}`;
        }

        if (autoStart) {
          playFromLine(0);
        }
      } else {
        throw new Error('No speech lines received');
      }
    } catch (e) {
      console.error('Could not load speech document:', e);
      setStatus('Could not load speech document.', 'idle');
    }
  }

  // 5. Play From Any Line Continuously in One Go without Stopping
  function playFromLine(startIndex) {
    if (startIndex < 0 || startIndex >= speechLines.length) return;

    currentLineIndex = startIndex;
    const line = speechLines[startIndex];

    // Highlight and style lines
    speechLines.forEach((_, i) => {
      const el = document.getElementById(`docLine_${i}`);
      if (!el) return;
      if (i < startIndex) {
        el.classList.add('is-read');
        el.classList.remove('is-speaking');
      } else if (i === startIndex) {
        el.classList.add('is-speaking');
        el.classList.remove('is-read');
      } else {
        el.classList.remove('is-speaking');
        el.classList.remove('is-read');
      }
    });

    // Auto-scroll the line smoothly to center of teleprompter
    const activeEl = document.getElementById(`docLine_${startIndex}`);
    if (activeEl && els.answerBody) {
      activeEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    if (els.nowPlayingPill) {
      els.nowPlayingPill.innerHTML = `<i class="fa-solid fa-volume-high"></i> ${currentLanguage === 'hi' ? 'बोल रही है' : 'Speaking'} &bull; ${startIndex + 1}/${speechLines.length}`;
    }

    setStatus(
      currentLanguage === 'hi' 
        ? `AI (Kore) बिना रुके पूरी स्पीच बोल रही है (लाइन ${startIndex + 1}/${speechLines.length})...` 
        : `AI (Kore) speaking complete plan non-stop (Line ${startIndex + 1}/${speechLines.length})...`,
      'speaking'
    );

    speakHumanText(line.spoken, () => {
      // Line finished speaking!
      if (!speaking) return; // If stopped by user

      const finishedEl = document.getElementById(`docLine_${startIndex}`);
      if (finishedEl) {
        finishedEl.classList.remove('is-speaking');
        finishedEl.classList.add('is-read');
      }

      // Automatically speak the next line without stopping!
      if (currentLineIndex + 1 < speechLines.length) {
        playFromLine(currentLineIndex + 1);
      } else {
        // Complete speech reached the end
        finishAllSpeech();
      }
    });
  }

  function finishAllSpeech() {
    speaking = false;
    isPaused = false;
    setSpeakingUI(false);
    if (els.stopSpeechBtn) els.stopSpeechBtn.style.display = 'none';

    speechLines.forEach((_, i) => {
      const el = document.getElementById(`docLine_${i}`);
      if (el) {
        el.classList.remove('is-speaking');
        el.classList.add('is-read');
      }
    });

    setStatus(
      currentLanguage === 'hi' 
        ? 'बिजनेस प्लान की पूरी स्पीच समाप्त हो गई है। दोबारा सुनने के लिए माइक पर क्लिक करें।' 
        : 'Complete business plan speech finished. Click microphone to play again.',
      'idle'
    );

    if (els.nowPlayingPill) {
      els.nowPlayingPill.innerHTML = `<i class="fa-solid fa-circle-check"></i> ${currentLanguage === 'hi' ? 'पूरी स्पीच समाप्त' : 'Speech Completed'}`;
    }
  }

  // 6. Central Microphone Click - Starts full speech from line 0
  if (els.mainMicBtn) {
    els.mainMicBtn.addEventListener('click', () => {
      if (speaking) {
        stopAudio();
      } else {
        playFromLine(0);
      }
    });
  }

  if (els.stopAudioBtn) {
    els.stopAudioBtn.addEventListener('click', stopAudio);
  }

  if (els.stopSpeechBtn) {
    els.stopSpeechBtn.addEventListener('click', stopAudio);
  }

  if (els.replayAudioBtn) {
    els.replayAudioBtn.addEventListener('click', () => {
      playFromLine(0);
    });
  }

  // 7. Language Switching
  function setLanguage(lang) {
    stopAudio();
    currentLanguage = lang;
    els.langBtns.forEach(btn => {
      btn.classList.toggle('active', btn.dataset.lang === lang);
    });

    showToast(`Language set to ${lang === 'hi' ? 'Hindi (हिंदी)' : 'English'}`);
    loadSpeechData(false);
  }

  els.langBtns.forEach(btn => {
    btn.addEventListener('click', () => setLanguage(btn.dataset.lang));
  });

  // 8. 3D CARDS CLICK -> SCROLL TO & PLAY MATCHING LINES NON-STOP
  function init3DCards() {
    if (!els.cards3d) return;

    els.cards3d.forEach(card => {
      // 3D Perspective Tilt on Mouse Move
      card.addEventListener('mousemove', (e) => {
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const cx = rect.width / 2;
        const cy = rect.height / 2;

        const rotX = -((y - cy) / cy) * 14;
        const rotY = ((x - cx) / cx) * 14;

        card.style.transform = `perspective(1000px) rotateX(${rotX.toFixed(2)}deg) rotateY(${rotY.toFixed(2)}deg) scale3d(1.03, 1.03, 1.03)`;
        card.style.setProperty('--mouse-x', `${((x / rect.width) * 100).toFixed(1)}%`);
        card.style.setProperty('--mouse-y', `${((y / rect.height) * 100).toFixed(1)}%`);
      });

      card.addEventListener('mouseleave', () => {
        card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)';
      });

      // Click Card -> Highlight & start playing from this topic's line non-stop
      card.addEventListener('click', () => {
        const topic = card.getAttribute('data-topic');
        let targetIndex = -1;

        if (topic) {
          targetIndex = speechLines.findIndex(l => l.topic === topic);
        }

        if (targetIndex === -1 && topic) {
          const kw = topic.replace('_', ' ').toLowerCase();
          targetIndex = speechLines.findIndex(l => (l.text || '').toLowerCase().includes(kw));
        }

        if (targetIndex !== -1 && targetIndex < speechLines.length) {
          card.style.transform = 'perspective(1000px) scale3d(0.96, 0.96, 0.96)';
          setTimeout(() => {
            card.style.transform = 'perspective(1000px) scale3d(1.03, 1.03, 1.03)';
          }, 140);

          els.answerBox?.scrollIntoView({ behavior: 'smooth', block: 'center' });
          playFromLine(targetIndex);
        }
      });
    });
  }

  // 7. ADMIN FILE UPLOAD (PDF / TXT)
  function initAdminUpload() {
    if (els.openUploadModalBtn) {
      els.openUploadModalBtn.addEventListener('click', () => {
        els.uploadModalOverlay?.classList.add('open');
      });
    }

    const closeUploadModal = () => {
      els.uploadModalOverlay?.classList.remove('open');
      resetUploadForm();
    };

    if (els.closeUploadModalBtn) els.closeUploadModalBtn.addEventListener('click', closeUploadModal);
    if (els.cancelUploadBtn) els.cancelUploadBtn.addEventListener('click', closeUploadModal);
    if (els.uploadModalOverlay) {
      els.uploadModalOverlay.addEventListener('click', (e) => {
        if (e.target === els.uploadModalOverlay) closeUploadModal();
      });
    }

    // Drag and Drop
    if (els.fileDropzone && els.planFileInput) {
      ['dragenter', 'dragover'].forEach(name => {
        els.fileDropzone.addEventListener(name, (e) => {
          e.preventDefault();
          els.fileDropzone.classList.add('dragover');
        });
      });

      ['dragleave', 'drop'].forEach(name => {
        els.fileDropzone.addEventListener(name, (e) => {
          e.preventDefault();
          els.fileDropzone.classList.remove('dragover');
        });
      });

      els.fileDropzone.addEventListener('drop', (e) => {
        if (e.dataTransfer?.files?.length) {
          els.planFileInput.files = e.dataTransfer.files;
          handleFileChosen(e.dataTransfer.files[0]);
        }
      });

      els.planFileInput.addEventListener('change', () => {
        if (els.planFileInput.files?.length) {
          handleFileChosen(els.planFileInput.files[0]);
        }
      });
    }

    if (els.removeFileBtn) {
      els.removeFileBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        resetUploadForm();
      });
    }

    function handleFileChosen(file) {
      if (!file) return;
      const ext = file.name.split('.').pop().toLowerCase();
      if (!['pdf', 'txt'].includes(ext)) {
        showToast('Please upload a valid .pdf or .txt document.');
        resetUploadForm();
        return;
      }
      if (els.chosenFileName) els.chosenFileName.textContent = file.name;
      if (els.chosenFileSize) {
        const sizeMb = (file.size / (1024 * 1024)).toFixed(2);
        els.chosenFileSize.textContent = `${sizeMb} MB (${ext.toUpperCase()})`;
      }
      if (els.fileChosenInfo) els.fileChosenInfo.style.display = 'flex';
      const visual = els.fileDropzone?.querySelector('.dropzone-visual');
      if (visual) visual.style.display = 'none';
    }

    function resetUploadForm() {
      if (els.planFileInput) els.planFileInput.value = '';
      if (els.fileChosenInfo) els.fileChosenInfo.style.display = 'none';
      const visual = els.fileDropzone?.querySelector('.dropzone-visual');
      if (visual) visual.style.display = 'block';
      if (els.uploadProgressWrap) els.uploadProgressWrap.style.display = 'none';
      if (els.uploadProgressBar) els.uploadProgressBar.style.width = '0%';
      if (els.startUploadBtn) els.startUploadBtn.disabled = false;
    }

    // Submit File Upload
    if (els.adminUploadForm) {
      els.adminUploadForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const file = els.planFileInput?.files?.[0];
        if (!file) {
          showToast('Please select a PDF or TXT file to upload.');
          return;
        }

        if (els.uploadProgressWrap) els.uploadProgressWrap.style.display = 'flex';
        if (els.startUploadBtn) els.startUploadBtn.disabled = true;
        if (els.uploadProgressBar) els.uploadProgressBar.style.width = '30%';
        if (els.uploadProgressMsg) {
          els.uploadProgressMsg.textContent = file.name.endsWith('.pdf') 
            ? 'Uploading PDF & extracting plan with Gemini AI...' 
            : 'Uploading & parsing TXT plan document...';
        }

        const formData = new FormData();
        formData.append('file', file);

        try {
          setTimeout(() => {
            if (els.uploadProgressBar) els.uploadProgressBar.style.width = '75%';
          }, 800);

          const res = await fetch(uploadEndpoint, {
            method: 'POST',
            headers: {
              'Accept': 'application/json',
              'X-CSRF-TOKEN': csrfToken
            },
            body: formData
          });

          const data = await res.json();
          if (data && data.success) {
            if (els.uploadProgressBar) els.uploadProgressBar.style.width = '100%';
            if (els.uploadProgressMsg) els.uploadProgressMsg.textContent = 'Knowledge base updated successfully!';
            
            showToast(`Document uploaded! Extracted ${data.characters || ''} characters.`);

            if (els.docTextarea && data.document_preview) {
              els.docTextarea.value = data.preview || els.docTextarea.value;
            }

            setTimeout(() => {
              closeUploadModal();
              loadSpeechData(true);
            }, 1200);
          } else {
            throw new Error(data?.message || 'Upload failed');
          }
        } catch (err) {
          console.error('File upload error:', err);
          showToast(err.message || 'Error uploading file.');
          if (els.uploadProgressWrap) els.uploadProgressWrap.style.display = 'none';
          if (els.startUploadBtn) els.startUploadBtn.disabled = false;
        }
      });
    }
  }

  // 8. Raw Document View / Edit Modal
  async function openDocModal() {
    if (!els.docModalOverlay) return;
    els.docModalOverlay.classList.add('open');
    try {
      const res = await fetch('/ai/plan-explainer/document');
      const data = await res.json();
      if (data && data.success && els.docTextarea) {
        els.docTextarea.value = data.document || '';
      }
    } catch (e) {
      console.warn('Could not load plan document:', e);
    }
  }

  function closeDocModal() {
    els.docModalOverlay?.classList.remove('open');
  }

  async function saveDoc() {
    const content = els.docTextarea?.value.trim();
    if (!content) {
      showToast("Document cannot be empty.");
      return;
    }
    try {
      const res = await fetch('/ai/plan-explainer/document', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ document: content })
      });
      const data = await res.json();
      if (data && data.success) {
        showToast("Business Plan Document saved successfully!");
        closeDocModal();
        loadSpeechData(false);
      } else {
        showToast("Failed to save document.");
      }
    } catch (e) {
      console.error(e);
      showToast("Error updating document.");
    }
  }

  if (els.openDocModalBtn) els.openDocModalBtn.addEventListener('click', openDocModal);
  if (els.closeDocModalBtn) els.closeDocModalBtn.addEventListener('click', closeDocModal);
  if (els.saveDocBtn) els.saveDocBtn.addEventListener('click', saveDoc);
  if (els.docModalOverlay) {
    els.docModalOverlay.addEventListener('click', (e) => {
      if (e.target === els.docModalOverlay) closeDocModal();
    });
  }

  function showToast(msg) {
    if (!els.toast) return;
    els.toast.textContent = msg;
    els.toast.classList.add('show');
    window.clearTimeout(toastTimer);
    toastTimer = window.setTimeout(() => {
      els.toast.classList.remove('show');
    }, 3500);
  }

  function renderMarkdown(text) {
    if (!text) return '';
    let html = text
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');

    html = html.replace(/^### (.*$)/gim, '<h3>$1</h3>');
    html = html.replace(/^## (.*$)/gim, '<h2>$1</h2>');
    html = html.replace(/^# (.*$)/gim, '<h1>$1</h1>');
    html = html.replace(/\*\*(.*?)\*\*/gim, '<strong>$1</strong>');
    html = html.replace(/\*(.*?)\*/gim, '<em>$1</em>');
    html = html.replace(/^\- (.*$)/gim, '<li>$1</li>');
    html = html.replace(/(<li>.*<\/li>)/gms, '<ul>$1</ul>');
    html = html.replace(/\n\n+/g, '<br><br>');
    return html;
  }

  // 9. THREE.JS 3D EARTH GLOBE - Dito Frontend Landing Page Canvas
  function initEarthGlobe() {
    if (!window.THREE) return;
    if (window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    const canvas = document.getElementById('bg3d');
    if (!canvas) return;

    let renderer;
    try {
      renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true });
    } catch (e) {
      return;
    }

    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(55, window.innerWidth / window.innerHeight, 0.1, 100);
    camera.position.z = 6.2;

    const grp = new THREE.Group();
    scene.add(grp);
    const R = 2.5;

    // Fibonacci Point Globe
    const N = 1500;
    const pos = new Float32Array(N * 3);
    for (let i = 0; i < N; i++) {
      const y = 1 - (i / (N - 1)) * 2;
      const r = Math.sqrt(1 - y * y);
      const th = Math.PI * (3 - Math.sqrt(5)) * i;
      pos[i * 3] = Math.cos(th) * r * R;
      pos[i * 3 + 1] = y * R;
      pos[i * 3 + 2] = Math.sin(th) * r * R;
    }
    const g = new THREE.BufferGeometry();
    g.setAttribute('position', new THREE.BufferAttribute(pos, 3));
    grp.add(new THREE.Points(g, new THREE.PointsMaterial({ color: 0x2BE86B, size: 0.028, transparent: true, opacity: 0.85 })));

    // Wire Sphere
    grp.add(new THREE.Mesh(new THREE.SphereGeometry(R * 0.985, 26, 26), new THREE.MeshBasicMaterial({ color: 0x0E7A3A, wireframe: true, transparent: true, opacity: 0.12 })));

    // Orbiting Exchange Nodes
    const nodes = [];
    for (let i = 0; i < 7; i++) {
      const m = new THREE.Mesh(new THREE.SphereGeometry(0.07, 16, 16), new THREE.MeshBasicMaterial({ color: 0xB6F24A }));
      grp.add(m);
      nodes.push({ m, ph: Math.random() * 6.28, sp: 0.15 + Math.random() * 0.3, rad: R + 0.35 + Math.random() * 0.7, tilt: (Math.random() - 0.5) * 1.4 });
    }

    // Ambient Dust Particles
    const M = 260;
    const dp = new Float32Array(M * 3);
    for (let i = 0; i < M; i++) {
      dp[i * 3] = (Math.random() - 0.5) * 16;
      dp[i * 3 + 1] = (Math.random() - 0.5) * 10;
      dp[i * 3 + 2] = (Math.random() - 0.5) * 8;
    }
    const dg = new THREE.BufferGeometry();
    dg.setAttribute('position', new THREE.BufferAttribute(dp, 3));
    scene.add(new THREE.Points(dg, new THREE.PointsMaterial({ color: 0x2BE86B, size: 0.02, transparent: true, opacity: 0.35 })));

    let mx = 0, my = 0;
    window.addEventListener('mousemove', (e) => {
      mx = (e.clientX / window.innerWidth - 0.5);
      my = (e.clientY / window.innerHeight - 0.5);
    }, { passive: true });

    function resize() {
      const w = window.innerWidth;
      const h = window.innerHeight;
      renderer.setSize(w, h, false);
      camera.aspect = w / h;
      camera.updateProjectionMatrix();
    }
    resize();
    window.addEventListener('resize', resize);

    let running = true;
    document.addEventListener('visibilitychange', () => { running = !document.hidden; });
    const clock = new THREE.Clock();

    function loop() {
      requestAnimationFrame(loop);
      if (!running) return;
      const t = clock.getElapsedTime();
      grp.rotation.y = t * 0.07;
      grp.rotation.x = -0.28;
      nodes.forEach(n => {
        const a = n.ph + t * n.sp;
        n.m.position.set(Math.cos(a) * n.rad, Math.sin(a * 0.8 + n.tilt) * 1.1, Math.sin(a) * n.rad);
      });
      camera.position.x += ((mx * 1.3) - camera.position.x) * 0.04;
      camera.position.y += ((-my * 0.9) - camera.position.y) * 0.04;
      camera.lookAt(0, 0, 0);
      renderer.render(scene, camera);
    }
    loop();
  }

  // Initialize
  initEarthGlobe();
  init3DCards();
  initAdminUpload();
  loadSpeechData(false);
})();
