<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#020817">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>JARVIS &bull; AI Voice Assistant</title>
  <link rel="stylesheet" href="{{ asset('styles.css') }}?v=18">
</head>
<body>
  <!-- TRUE FULL-SCREEN FUTURISTIC AI BACKGROUND LAYER -->
  <div class="full-screen-ai-background" aria-hidden="true">
    <picture class="bg-picture-layer">
      <source srcset="https://images.unsplash.com/photo-1511447333015-45b65e60f6d5?auto=format&fit=crop&w=2560&q=85" media="(min-width: 1440px)">
      <img src="https://images.unsplash.com/photo-1511447333015-45b65e60f6d5?auto=format&fit=crop&w=1920&q=80"
           alt="Futuristic AI Command Center Background"
           class="bg-img-asset"
           loading="eager"
           decoding="async"
           onerror="this.onerror=null;this.src='{{ asset('assets/ai-command-center-bg.jpg') }}';">
    </picture>
    <div class="bg-dark-cinematic-overlay"></div>
    <div class="bg-subtle-radial-vignette"></div>
    <div class="bg-cyber-hologram-glow"></div>
  </div>

  <main class="assistant-app" aria-label="JARVIS AI Assistant dashboard" data-assistant-state="idle">
    <!-- DESKTOP COCKPIT STAGE (Active on desktop widescreen) -->
    <div class="desktop-stage-canvas" aria-label="Desktop Holographic Cockpit">
      <div class="reference-stage" aria-hidden="true"></div>

      <header class="semantic-header">
        <h1>JARVIS AI Voice Assistant</h1>
        <time datetime="{{ now()->toIso8601String() }}">{{ now()->format('h:i A, l d F Y') }}</time>
      </header>

      <section class="semantic-content" aria-label="Welcome">
        <p>Good {{ now()->hour < 12 ? 'Morning' : (now()->hour < 17 ? 'Afternoon' : 'Evening') }}, {{ $data->name }}</p>
        <h2>How can JARVIS help you today?</h2>
        <p>I am JARVIS, created by Deepanshu Saini. I can understand, speak, think, and remember your conversations.</p>
      </section>

      <div class="dynamic-overlay dynamic-time dashboard-live-clock" id="dashboardLiveTime">{{ now()->format('h:i A') }}<small>{{ now()->format('l, d F Y') }}</small></div>
      <div class="dynamic-overlay dynamic-greeting" id="dashboardGreeting">Good {{ now()->hour < 12 ? 'Morning' : (now()->hour < 17 ? 'Afternoon' : 'Evening') }}, {{ $data->name }} 👋</div>

      <!-- Futuristic AI System Status Widget in Top Right -->
      <div class="dynamic-overlay dynamic-system-status" aria-label="System status telemetry">
        <div class="status-badge-item">
          <span class="status-indicator-dot online"></span>
          <span class="status-label-sub">CORE</span>
          <strong class="status-value-main">ACTIVE</strong>
        </div>
        <div class="status-separator"></div>
        <div class="status-badge-item">
          <span class="status-indicator-dot pulse"></span>
          <span class="status-label-sub">AI ENGINE</span>
          <strong class="status-value-main glow-cyan">READY</strong>
        </div>
        <div class="status-separator"></div>
        <div class="status-badge-item">
          <span class="telemetry-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
            </svg>
          </span>
          <span class="status-label-sub">LATENCY</span>
          <strong class="status-value-main glow-green">24ms</strong>
        </div>
      </div>

      <canvas id="liveWaveform" class="live-waveform" aria-hidden="true"></canvas>
      <p class="live-state-label" data-assistant-status>Tap to speak</p>

      <section class="hotspots" aria-label="Assistant controls">
        <button class="hotspot keyboard-control" type="button" aria-label="Open keyboard input"></button>
        <button class="hotspot microphone-control" type="button" aria-label="Start listening" aria-pressed="false"><span class="active-ring"></span></button>
        <button class="hotspot settings-control" type="button" aria-label="Voice settings"></button>

        <nav class="quick-actions" aria-label="Quick actions">
          <button type="button" data-action="quantum" aria-label="Explain Quantum Computing"></button>
          <button type="button" data-action="switzerland" aria-label="Plan a Trip to Switzerland"></button>
          <button type="button" data-action="python" aria-label="Write Code in Python"></button>
          <button type="button" data-action="pdf" aria-label="Summarize this PDF"></button>
          <button type="button" data-action="image" aria-label="Generate an AI Image"></button>
        </nav>
      </section>
    </div>

    <!-- MOBILE & TABLET RESPONSIVE STAGE (Active on <= 991px or portrait) -->
    <div class="mobile-stage-canvas" aria-label="Mobile AI Voice Assistant Interface">
      <!-- Mobile Top Bar -->
      <header class="mobile-top-bar">
        <div class="mobile-brand-title">
          <span class="mobile-brand-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
              <path d="M12 2L14.6 9.4L22 12L14.6 14.6L12 22L9.4 14.6L2 12L9.4 9.4L12 2Z" fill="url(#mobileSparkGrad)"/>
              <defs>
                <linearGradient id="mobileSparkGrad" x1="2" y1="2" x2="22" y2="22" gradientUnits="userSpaceOnUse">
                  <stop stop-color="#00E5FF"/>
                  <stop offset="0.5" stop-color="#9B4DFF"/>
                  <stop offset="1" stop-color="#FF4DF0"/>
                </linearGradient>
              </defs>
            </svg>
          </span>
          <span class="mobile-brand-text">AI Assistant</span>
        </div>

        <div class="mobile-system-telemetry">
          <span class="status-indicator-dot online"></span>
          <span>CORE ACTIVE</span>
          <span class="telemetry-pill-mini">READY</span>
        </div>
      </header>

      <!-- Mobile Greeting & Live Clock -->
      <section class="mobile-hero-section">
        <div class="mobile-live-clock dashboard-live-clock">
          {{ now()->format('h:i A') }}<small>{{ now()->format('l, d F Y') }}</small>
        </div>
        <h2 class="mobile-greeting-heading">Good {{ now()->hour < 12 ? 'Morning' : (now()->hour < 17 ? 'Afternoon' : 'Evening') }}, {{ $data->name }} 👋</h2>
        <p class="mobile-greeting-sub">How can I help you today?</p>
      </section>

      <!-- Futuristic Holographic AI Core Orb -->
      <div class="mobile-hologram-container" aria-hidden="true">
        <div class="mobile-hologram-rings"></div>
        <div class="mobile-hologram-orb">
          <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="#00e5ff" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 2a4 4 0 0 0-4 4v1H7a4 4 0 0 0-4 4 4 4 0 0 0 2 3.46V15a4 4 0 0 0 4 4h1v1a2 2 0 0 0 4 0v-1h1a4 4 0 0 0 4-4v-.54A4 4 0 0 0 21 11a4 4 0 0 0-4-4h-1V6a4 4 0 0 0-4-4z"/>
            <path d="M9 10h.01M15 10h.01M9 14h.01M15 14h.01"/>
          </svg>
        </div>
        <div class="mobile-ai-status-pill">
          <span class="status-indicator-dot online"></span>
          <span>AI is ready to assist you</span>
        </div>
      </div>

      <!-- Mobile Voice Station -->
      <div class="mobile-voice-station">
        <div class="mobile-waveform-wrapper">
          <canvas class="live-waveform" aria-hidden="true"></canvas>
        </div>

        <p class="mobile-voice-status" data-assistant-status>Tap to speak to JARVIS</p>

        <div class="mobile-controls-row">
          <button type="button" class="mobile-control-btn keyboard-control" aria-label="Open keyboard input">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="2" y="4" width="20" height="16" rx="2.5"/>
              <path d="M6 8h.01M10 8h.01M14 8h.01M18 8h.01M6 12h.01M10 12h.01M14 12h.01M18 12h.01M8 16h8"/>
            </svg>
          </button>

          <button type="button" class="mobile-microphone-button microphone-control" aria-label="Start listening" aria-pressed="false">
            <span class="active-ring"></span>
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3z"/>
              <path d="M19 10v2a7 7 0 0 1-14 0v-2M12 19v4M8 23h8"/>
            </svg>
          </button>

          <button type="button" class="mobile-control-btn settings-control" aria-label="Voice settings">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="3"/>
              <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- Quick Action Cards Grid -->
      <section class="mobile-quick-actions" aria-label="Quick suggestions">
        <h3 class="mobile-section-label">Quick Suggestions</h3>
        <div class="mobile-cards-grid">
          <button type="button" class="mobile-action-card" data-action="quantum">
            <span class="action-card-icon cyan">⚛</span>
            <span class="action-card-text">Explain Quantum Computing</span>
          </button>
          <button type="button" class="mobile-action-card" data-action="switzerland">
            <span class="action-card-icon teal">✈</span>
            <span class="action-card-text">Plan a Trip to Switzerland</span>
          </button>
          <button type="button" class="mobile-action-card" data-action="python">
            <span class="action-card-icon orange">&lt;/&gt;</span>
            <span class="action-card-text">Write Code in Python</span>
          </button>
          <button type="button" class="mobile-action-card" data-action="pdf">
            <span class="action-card-icon red">📄</span>
            <span class="action-card-text">Summarize This PDF</span>
          </button>
          <button type="button" class="mobile-action-card" data-action="image">
            <span class="action-card-icon purple">🖼</span>
            <span class="action-card-text">Generate AI Image</span>
          </button>
        </div>
      </section>

      <footer class="mobile-footer-note">
        <span>✦ AI makes mistakes. Always verify important information.</span>
      </footer>
    </div>

    <!-- Shared Modals & Panels -->
    <div class="popover settings-menu" aria-hidden="true">
      <strong>Voice Assistant Settings</strong>
      <p>Gemini automatically detects English, Hindi, and Hinglish.</p>
      <button type="button" class="stop-audio">Stop assistant audio</button>
    </div>

    <form class="keyboard-panel" aria-hidden="true">
      <label for="prompt">Ask AI Assistant</label>
      <div class="transcript-messages" aria-live="polite"></div>
      <div class="keyboard-row"><input id="prompt" type="text" maxlength="8000" placeholder="Type your message…" autocomplete="off"><button type="submit">Send</button></div>
    </form>
    <input id="pdfUpload" type="file" accept="application/pdf,.pdf" hidden>
    <div class="toast" role="status" aria-live="polite"></div>
  </main>



  @php
    $voiceAssistantConfig = [
      'userName' => $data->name ?? 'User',
      'csrfToken' => csrf_token(),
      'workletUrl' => asset('voice_assistant/pcm-recorder-worklet.js'),
      'quickActions' => config('gemini.quick_actions'),
      'endpoints' => [
        'session' => route('assistant.session'),
        'message' => route('assistant.message'),
        'liveTurn' => route('assistant.live-turn'),
        'pdf' => route('assistant.pdf'),
        'image' => route('assistant.image'),
        'verifyKey' => route('assistant.verify-key'),
        'profile' => route('assistant.profile.update'),
        'stats' => route('assistant.stats'),
      ],
    ];
  @endphp
  <script>
    window.VOICE_ASSISTANT_CONFIG = {{ Illuminate\Support\Js::from($voiceAssistantConfig) }};
  </script>
  <script type="module" src="{{ asset('voice_assistant/voice-assistant.js') }}?v=18"></script>
</body>
</html>
