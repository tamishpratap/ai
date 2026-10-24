(function () {
  'use strict';

  const welcomePanel = document.getElementById('welcomePanel');
  const chatMessages = document.getElementById('chatMessages');
  const promptForm = document.getElementById('promptForm');
  const promptInput = document.getElementById('promptInput');
  const sendButton = document.getElementById('sendButton');
  const newChatButton = document.getElementById('newChatButton');
  const suggestionGrid = document.getElementById('suggestionGrid');
  const toast = document.getElementById('toast');

  const endpoint = window.DEMO_PROJECT_AI_ENDPOINT || '/project-ai/message';
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  let chatHistory = [];
  let isSending = false;
  let toastTimer = null;

  function showToast(message) {
    if (!toast) return;
    toast.textContent = message;
    toast.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
      toast.classList.remove('show');
    }, 2800);
  }

  function updateSendButton() {
    const hasText = promptInput.value.trim().length > 0;
    sendButton.setAttribute('aria-disabled', String(!hasText || isSending));
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
    return text.replace(/[&<>"']/g, m => map[m]);
  }

  function formatMarkdown(text) {
    if (!text) return '';
    let html = escapeHtml(text);

    // Code blocks
    html = html.replace(/```([a-zA-Z0-9_-]*)\n([\s\S]*?)```/g, function (_, lang, code) {
      return '<pre><code>' + code.trim() + '</code></pre>';
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

    // Bullet points
    html = html.replace(/^\s*[-*]\s+(.*)$/gim, '<li>$1</li>');
    html = html.replace(/(<li>.*<\/li>)/gims, function (match) {
      return '<ul>' + match + '</ul>';
    });

    // Clean up multiple nested uls
    html = html.replace(/<\/ul>\s*<ul>/g, '');

    // Paragraphs / linebreaks
    const lines = html.split('\n');
    let parsed = '';
    for (let line of lines) {
      line = line.trim();
      if (!line) continue;
      if (line.startsWith('<h') || line.startsWith('<pre') || line.startsWith('<ul>') || line.startsWith('<li>') || line.startsWith('</ul')) {
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

  function appendUserMessage(text) {
    if (welcomePanel) {
      welcomePanel.style.display = 'none';
    }

    const row = document.createElement('div');
    row.className = 'message-row user';
    row.innerHTML = `
      <div class="message-bubble">${escapeHtml(text)}</div>
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

    // Actions (Copy)
    const actions = document.createElement('div');
    actions.className = 'bubble-actions';
    const copyBtn = document.createElement('button');
    copyBtn.className = 'copy-btn';
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
        showToast('Copied to clipboard');
        copyBtn.querySelector('span').textContent = 'Copied!';
        setTimeout(() => {
          copyBtn.querySelector('span').textContent = 'Copy';
        }, 2000);
      } catch (err) {
        showToast('Failed to copy');
      }
    });

    actions.appendChild(copyBtn);
    bubble.appendChild(actions);

    row.innerHTML = `<div class="message-avatar" title="Project AI">AI</div>`;
    row.appendChild(bubble);

    chatMessages.appendChild(row);
    scrollToBottom();
  }

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

  // Event Listeners
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

  // Suggestion Cards
  suggestionGrid?.addEventListener('click', (e) => {
    const card = e.target.closest('.suggestion-card');
    if (!card) return;
    const prompt = card.getAttribute('data-prompt');
    if (prompt) {
      sendMessage(prompt);
    }
  });

  // New Chat
  newChatButton?.addEventListener('click', () => {
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

  // Init
  updateSendButton();
  promptInput.focus();

})();
