<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="theme-color" content="#060813">
  <meta name="description" content="Quantum Project AI Workspace - Premium Next-Generation Cognitive Workspace powered by Google Gemini 2.5.">
  <title>Quantum AI Workspace &bull; Intelligence Redefined</title>
  
  <!-- Modern Typography from Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
  
  <link rel="stylesheet" href="{{ asset('ai_assets/style.css') }}?v=4.2">
</head>
<body>
  <!-- Ambient Atmospheric FX Layers -->
  <canvas id="neuralCanvas" class="neural-canvas" aria-hidden="true"></canvas>
  <div class="ambient-glow orb-1" aria-hidden="true"></div>
  <div class="ambient-glow orb-2" aria-hidden="true"></div>
  <div class="ambient-glow orb-3" aria-hidden="true"></div>
  <div class="ambient-grid-overlay" aria-hidden="true"></div>

  <div class="app-shell">
    <main class="main-panel">
      <!-- Premium Glass Topbar -->
      <header class="topbar">
        <div class="topbar-left">
          <a href="{{ url('/') }}" class="topbar-nav-btn" title="Back to Quantum ARB Landing Page">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            <span>Landing Page</span>
          </a>

          <div class="brand-divider"></div>

          <!-- Brand Emblem -->
          <div class="brand-badge">
            <div class="brand-gem">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                <polyline points="2 17 12 22 22 17"></polyline>
                <polyline points="2 12 12 17 22 12"></polyline>
              </svg>
            </div>
            <div class="brand-meta">
              <span class="brand-title">QUANTUM AI</span>
              <span class="brand-sub">WORKSPACE</span>
            </div>
          </div>

          <!-- Model Live Indicator -->
          <div class="model-badge" title="Active Engine: Google Gemini 2.5 Flash">
            <span class="model-status-dot"></span>
            <span class="model-name">Gemini 2.5 Flash</span>
            <span class="model-tag">ACTIVE</span>
          </div>
        </div>

        <div class="topbar-actions">
          <a href="{{ url('/ai/voice-assistant') }}" class="topbar-pill pill-voice" title="Switch to JARVIS AI Voice Assistant">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path>
              <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
              <line x1="12" y1="19" x2="12" y2="23"></line>
              <line x1="8" y1="23" x2="16" y2="23"></line>
            </svg>
            <span>Voice Assistant</span>
          </a>

          <a href="{{ url('/ai/plan-explainer') }}" class="topbar-pill pill-plan" title="Switch to Plan Explainer AI">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
              <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
            </svg>
            <span>Plan Explainer</span>
          </a>

          <!-- User Profile Chip -->
          <div class="user-chip" title="Connected as {{ $data->name ?? 'Quantum Member' }}">
            <div class="user-avatar">
              @if(!empty($data->profile_image))
                <img src="{{ asset($data->profile_image) }}" alt="{{ $data->name ?? 'User' }}">
              @else
                <span>{{ mb_strtoupper(mb_substr($data->name ?? 'Q', 0, 1)) }}</span>
              @endif
            </div>
            <span class="user-name">{{ Str::limit($data->name ?? 'Member', 12) }}</span>
          </div>

          <button class="new-chat-button topbar-new-chat" id="newChatButton" type="button" aria-label="Start new session" title="Start new session (Ctrl + K)">
            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 5v14M5 12h14"></path>
            </svg>
            <span>New Chat</span>
            <kbd class="shortcut-badge">Ctrl+K</kbd>
          </button>
        </div>
      </header>

      <!-- Main Conversation & Scroll Area -->
      <section class="conversation-area conversation-scroll-shell" aria-label="Conversation Viewport">
        <!-- Welcome Hero View -->
        <div class="welcome-panel" id="welcomePanel">
          <!-- Holographic Rotating Core -->
          <div class="holo-core-stage" aria-hidden="true">
            <div class="holo-ring holo-ring-outer"></div>
            <div class="holo-ring holo-ring-mid"></div>
            <div class="holo-ring holo-ring-inner"></div>
            <div class="holo-sphere">
              <svg viewBox="0 0 24 24" width="34" height="34" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2a10 10 0 1 0 10 10H12V2z"></path>
                <path d="M12 12 2.1 12.5"></path>
                <path d="m12 12 7-7"></path>
                <circle cx="12" cy="12" r="3"></circle>
              </svg>
            </div>
            <div class="holo-glow-blur"></div>
          </div>

          <div class="welcome-kicker">
            <span class="kicker-gem">✦</span>
            <span>Quantum Cognitive Intelligence &bull; Gemini 2.5</span>
          </div>

          <h1 class="welcome-title">
            <span class="title-greeting">Hello, <span class="user-gradient-name">{{ $data->name ?? 'Quantum Member' }}</span></span>
            <span class="title-headline">What shall we engineer, architect, or analyze today?</span>
          </h1>

          <p class="welcome-copy">
            Your high-velocity cognitive workspace. Ask for deep technical architecture, launch roadmaps, algorithm design, smart contract review, or strategic problem-solving.
          </p>

          <!-- Capability Badges -->
          <div class="welcome-caps">
            <span class="cap-tag"><span class="cap-dot dot-cyan"></span> Sub-second Latency</span>
            <span class="cap-tag"><span class="cap-dot dot-purple"></span> Multi-turn Memory</span>
            <span class="cap-tag"><span class="cap-dot dot-emerald"></span> Code &amp; Logic Synthesis</span>
            <span class="cap-tag"><span class="cap-dot dot-amber"></span> Interactive Audio Readout</span>
          </div>

          <!-- Suggestion Cards Grid -->
          <div class="suggestion-grid" id="suggestionGrid">
            <button class="suggestion-card card-violet" type="button" data-prompt="Draft a high-impact launch roadmap for a cutting-edge AI SaaS application. Include timeline, marketing channels, and KPI metrics.">
              <div class="card-icon-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"></path>
                  <path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"></path>
                  <path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"></path>
                  <path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"></path>
                </svg>
              </div>
              <div class="card-content">
                <span class="card-title">Draft a Launch Roadmap</span>
                <small class="card-desc">SaaS timeline, positioning, viral loops &amp; KPIs</small>
              </div>
              <span class="card-badge">Strategy</span>
              <div class="card-arrow">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M5 12h14M12 5l7 7-7 7"></path>
                </svg>
              </div>
            </button>

            <button class="suggestion-card card-cyan" type="button" data-prompt="Explain JavaScript closures and asynchronous event loops with an easy analogy and concise real-world code snippets.">
              <div class="card-icon-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
                </svg>
              </div>
              <div class="card-content">
                <span class="card-title">Explain JS Closures &amp; Loops</span>
                <small class="card-desc">Visual mental model, memory leaks &amp; examples</small>
              </div>
              <span class="card-badge">Concept</span>
              <div class="card-arrow">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M5 12h14M12 5l7 7-7 7"></path>
                </svg>
              </div>
            </button>

            <button class="suggestion-card card-emerald" type="button" data-prompt="Audit this code architecture for scalability, memory efficiency, and modern design patterns: ">
              <div class="card-icon-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="16 18 22 12 16 6"></polyline>
                  <polyline points="8 6 2 12 8 18"></polyline>
                  <line x1="14" y1="4" x2="10" y2="20"></line>
                </svg>
              </div>
              <div class="card-content">
                <span class="card-title">Code Audit &amp; Refactor</span>
                <small class="card-desc">Security checks, performance optimization, best practices</small>
              </div>
              <span class="card-badge">Engineering</span>
              <div class="card-arrow">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M5 12h14M12 5l7 7-7 7"></path>
                </svg>
              </div>
            </button>

            <button class="suggestion-card card-amber" type="button" data-prompt="Design an advanced 4-week mastery curriculum for mastering distributed systems and low-latency API design.">
              <div class="card-icon-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="12" cy="12" r="10"></circle>
                  <polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon>
                </svg>
              </div>
              <div class="card-content">
                <span class="card-title">System Design Curriculum</span>
                <small class="card-desc">Distributed cache, Raft consensus &amp; microservices</small>
              </div>
              <span class="card-badge">Mastery</span>
              <div class="card-arrow">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M5 12h14M12 5l7 7-7 7"></path>
                </svg>
              </div>
            </button>
          </div>
        </div>

        <!-- Chat Stream -->
        <div class="chat-messages conversation-scroll-pane" id="chatMessages" aria-live="polite"></div>
      </section>

      <!-- Futuristic Floating Prompt Command Deck -->
      <form class="prompt-form" id="promptForm">
        <!-- Quick Starter Chips -->
        <div class="quick-chips-bar" id="quickChipsBar">
          <button type="button" class="quick-chip" data-insert="Draft a comprehensive launch roadmap for: ">🚀 Launch Plan</button>
          <button type="button" class="quick-chip" data-insert="Optimize and refactor this code snippet: ">💻 Refactor Code</button>
          <button type="button" class="quick-chip" data-insert="Summarize key architectural trade-offs between: ">⚖️ Trade-off Analysis</button>
          <button type="button" class="quick-chip" data-insert="Explain step-by-step how to implement: ">💡 Implementation Guide</button>
          <button type="button" class="quick-chip" data-insert="Write high-converting, professional marketing copy for: ">✨ Marketing Copy</button>
        </div>

        <div class="prompt-bar">
          <!-- Voice Dictation Button (Client-side SpeechRecognition) -->
          <button type="button" class="prompt-tool-btn mic-btn" id="voiceDictationBtn" title="Speak to dictate your message" aria-label="Voice dictation">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path>
              <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
              <line x1="12" y1="19" x2="12" y2="23"></line>
              <line x1="8" y1="23" x2="16" y2="23"></line>
            </svg>
            <span class="mic-wave"></span>
          </button>

          <!-- Input Area -->
          <div class="textarea-wrapper">
            <textarea id="promptInput" rows="1" placeholder="Message Project AI... (Ask complex code, architecture, or strategies)" autocomplete="off" spellcheck="true"></textarea>
          </div>

          <!-- Prompt Actions -->
          <div class="prompt-actions">
            <!-- Clear Button -->
            <button type="button" class="prompt-tool-btn clear-btn" id="clearInputBtn" title="Clear input" style="display: none;">
              <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
            </button>

            <!-- Send Button -->
            <button class="send-button" id="sendButton" type="button" aria-label="Send message" aria-disabled="true" title="Send message (Enter)">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="22" y1="2" x2="11" y2="13"></line>
                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
              </svg>
            </button>
          </div>
        </div>

        <div class="prompt-footer-meta">
          <span class="meta-item"><kbd>Enter</kbd> to send</span>
          <span class="meta-dot">&bull;</span>
          <span class="meta-item"><kbd>Shift + Enter</kbd> for new line</span>
          <span class="meta-dot">&bull;</span>
          <span class="meta-item text-gradient">Gemini 2.5 Neural Engine</span>
        </div>
      </form>
    </main>
  </div>

  <!-- Polished Notification Toast -->
  <div class="toast" id="toast" role="status" aria-live="polite">
    <div class="toast-icon">
      <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5">
        <polyline points="20 6 9 17 4 12"></polyline>
      </svg>
    </div>
    <span class="toast-message"></span>
  </div>

  <!-- Backend Endpoint Bridge (100% Preserved) -->
  <script>window.DEMO_PROJECT_AI_ENDPOINT = @json(route('member.project-ai.message'));</script>
  <script defer src="{{ asset('ai_assets/script.js') }}?v=general-chat-v4.2"></script>
</body>
</html>
