const app = document.querySelector('.assistant-app');
const toast = document.querySelector('.toast');
const profileButton = document.querySelector('.profile-control');
const profileMenu = document.querySelector('.profile-menu');
const settingsMenu = document.querySelector('.settings-menu');
const keyboardPanel = document.querySelector('.keyboard-panel');
const micButtons = [...document.querySelectorAll('.microphone-control, .mobile-mic')];
let toastTimer;

function fitReferenceCanvas() {
  const scale = Math.min(window.innerWidth / 1536, window.innerHeight / 1024);
  const scaledWidth = 1536 * scale;
  const scaledHeight = 1024 * scale;
  const left = (window.innerWidth - scaledWidth) / 2;
  const top = (window.innerHeight - scaledHeight) / 2;
  app.style.setProperty('--canvas-scale', String(scale));
  app.style.setProperty('--canvas-left', `${left}px`);
  app.style.setProperty('--canvas-top', `${top}px`);
}

fitReferenceCanvas();
window.addEventListener('resize', fitReferenceCanvas, { passive: true });

function showToast(message) {
  toast.textContent = message;
  toast.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => toast.classList.remove('show'), 2300);
}

function togglePanel(panel, trigger) {
  const willOpen = !panel.classList.contains('open');
  document.querySelectorAll('.popover.open, .keyboard-panel.open').forEach(item => {
    if (item !== panel) item.classList.remove('open');
  });
  panel.classList.toggle('open', willOpen);
  panel.setAttribute('aria-hidden', String(!willOpen));
  if (trigger) trigger.setAttribute('aria-expanded', String(willOpen));
}

profileButton?.addEventListener('click', event => {
  event.stopPropagation();
  togglePanel(profileMenu, profileButton);
});
document.querySelector('.settings-control')?.addEventListener('click', event => {
  event.stopPropagation();
  togglePanel(settingsMenu);
});
document.querySelector('.keyboard-control')?.addEventListener('click', event => {
  event.stopPropagation();
  togglePanel(keyboardPanel);
  setTimeout(() => document.querySelector('#prompt')?.focus(), 220);
});

micButtons.forEach(button => button.addEventListener('click', () => {
  const listening = button.getAttribute('aria-pressed') !== 'true';
  button.setAttribute('aria-pressed', String(listening));
  button.setAttribute('aria-label', listening ? 'Stop listening' : 'Start listening');
  button.classList.toggle('listening', listening);
  showToast(listening ? 'Listening… speak naturally' : 'Voice input paused');
}));

document.querySelectorAll('[data-action]').forEach(button => button.addEventListener('click', () => {
  showToast(`${button.dataset.action} selected`);
}));
document.querySelector('.notification-control')?.addEventListener('click', () => showToast('You have 3 new notifications'));
document.querySelector('.language-control')?.addEventListener('click', () => showToast('Language: English'));

keyboardPanel.addEventListener('submit', event => {
  event.preventDefault();
  const input = document.querySelector('#prompt');
  if (!input.value.trim()) return;
  showToast('Message sent to AI Assistant');
  input.value = '';
  togglePanel(keyboardPanel);
});

app.addEventListener('click', event => {
  if (!event.target.closest('.popover,.keyboard-panel,.profile-control,.settings-control,.keyboard-control')) {
    document.querySelectorAll('.popover.open,.keyboard-panel.open').forEach(panel => panel.classList.remove('open'));
    profileButton?.setAttribute('aria-expanded', 'false');
  }
});
document.addEventListener('keydown', event => {
  if (event.key === 'Escape') {
    document.querySelectorAll('.popover.open,.keyboard-panel.open').forEach(panel => panel.classList.remove('open'));
    profileButton?.setAttribute('aria-expanded', 'false');
  }
});
