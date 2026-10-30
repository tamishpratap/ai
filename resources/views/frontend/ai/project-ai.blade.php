<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="theme-color" content="#030712">
  <meta name="description" content="Quantum AI - Cognitive Workspace. Full-screen, cinematic, futuristic AI workspace powered by Google Gemini 2.5.">
  <title>QUANTUM AI &bull; Cognitive Workspace</title>
  
  <!-- Modern Geometric Typography from Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
  
  <!-- Premium Stylesheet -->
  <link rel="stylesheet" href="{{ asset('ai_assets/style.css') }}?v=18.0">
</head>
<body class="quantum-ai-body">
  <!-- Full-Screen Cosmic Background Image Layer (Given Reference Image) -->
  <div class="ambient-image-wrapper" aria-hidden="true">
    <img src="{{ asset('ai_assets/quantum-ai-bg.jpg') }}" alt="Quantum AI Ambient Cosmic Background" class="ambient-bg-image">
    <div class="image-overlay-vignette"></div>
    <div class="image-overlay-grid"></div>
  </div>

  <!-- Interactive Constellation Particles -->
  <canvas id="neuralCanvas" class="neural-canvas" aria-hidden="true"></canvas>

  <!-- Deep Atmospheric Glow Orbs -->
  <div class="ambient-glow orb-1" aria-hidden="true"></div>
  <div class="ambient-glow orb-2" aria-hidden="true"></div>
  <div class="ambient-glow orb-3" aria-hidden="true"></div>

  <!-- Interactive Cosmic Mouse Spotlight & Trail FX -->
  <div class="mouse-glow-follower" id="mouseGlowFollower" aria-hidden="true"></div>
  <div class="mouse-pointer-dot" id="mousePointerDot" aria-hidden="true"></div>

  <!-- Main Application Shell (No Sidebar) -->
  <div class="app-shell">
    <main class="main-panel">
      <!-- Premium Dark Glass Top Navigation -->
      <header class="topbar">
        <!-- Left: Brand Emblem & Title -->
        <div class="topbar-left">
          <a href="{{ url('/') }}" class="brand-link" title="Quantum AI Home">
            <div class="brand-gem-brain">
              <svg viewBox="0 0 40 40" width="30" height="30" fill="none" class="brain-svg">
                <defs>
                  <linearGradient id="navBrainLeft" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#00E5FF"/>
                    <stop offset="100%" stop-color="#168BFF"/>
                  </linearGradient>
                  <linearGradient id="navBrainRight" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#A855F7"/>
                    <stop offset="100%" stop-color="#EC4899"/>
                  </linearGradient>
                </defs>
                <!-- Left Hemisphere -->
                <path d="M18 10 C14.5 10 11.5 12.5 11.5 16 C9.5 16.5 7.5 18.5 7.5 21 C7.5 23.5 9 25.5 10.5 26.5 C8.5 28 8 30.5 9.5 32.5 C11 34.5 14 35 16 33.5 C17 34.5 18 33.5 18 31.5 Z" stroke="url(#navBrainLeft)" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" fill="rgba(0, 229, 255, 0.12)"/>
                <path d="M13.5 18 C15.5 20 15.5 22.5 13.5 24.5 M16.5 24 C14.5 27 14.5 29.5 16.5 31" stroke="url(#navBrainLeft)" stroke-width="2" stroke-linecap="round"/>
                <!-- Right Hemisphere -->
                <path d="M22 10 C25.5 10 28.5 12.5 28.5 16 C30.5 16.5 32.5 18.5 32.5 21 C32.5 23.5 31 25.5 29.5 26.5 C31.5 28 32 30.5 30.5 32.5 C29 34.5 26 35 24 33.5 C23 34.5 22 33.5 22 31.5 Z" stroke="url(#navBrainRight)" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" fill="rgba(168, 85, 247, 0.12)"/>
                <path d="M26.5 18 C24.5 20 24.5 22.5 26.5 24.5 M23.5 24 C25.5 27 25.5 29.5 23.5 31" stroke="url(#navBrainRight)" stroke-width="2" stroke-linecap="round"/>
              </svg>
            </div>
            <div class="brand-text-wrap">
              <span class="brand-title">QUANTUM AI</span>
              <span class="brand-sub">COGNITIVE WORKSPACE</span>
            </div>
          </a>
        </div>

        <!-- Center: Navigation Links Matching Reference -->
        <nav class="topbar-center" aria-label="Center Navigation">
          <!-- Home button (Blue-purple gradient active state) -->
          <a href="{{ url('/') }}" class="nav-pill pill-home active" title="Quantum AI Home">
            <svg viewBox="0 0 24 24" width="15" height="15" fill="currentColor">
              <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
            </svg>
            <span>Home</span>
          </a>

          <!-- Gemini 2.5 Model Indicator with Online Status -->
          <div class="nav-pill pill-model" title="Active Model Engine: Google Gemini 2.5">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" class="sparkle-icon">
              <path d="M12 2L14.5 9.5L22 12L14.5 14.5L12 22L9.5 14.5L2 12L9.5 9.5L12 2Z"/>
            </svg>
            <span class="model-name">Gemini 2.5</span>
            <span class="online-pill">Online</span>
          </div>

          <!-- Voice Assistant Button -->
          <a href="{{ url('/ai/voice-assistant') }}" class="nav-pill pill-voice" title="Switch to JARVIS AI Voice Assistant">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path>
              <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
              <line x1="12" y1="19" x2="12" y2="23"></line>
              <line x1="8" y1="23" x2="16" y2="23"></line>
            </svg>
            <span>Voice Assistant</span>
          </a>

          <!-- Plan Explainer Button -->
          <a href="{{ url('/ai/plan-explainer') }}" class="nav-pill pill-plan" title="Switch to Plan Explainer AI">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
              <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
            </svg>
            <span>Plan Explainer</span>
          </a>
        </nav>

        <!-- Right: Actions (Profile and Notifications removed as requested) -->
        <div class="topbar-right">
          <!-- Hidden New Chat Button for Global Shortcuts & Bindings -->
          <button class="new-chat-button visually-hidden-action" id="newChatButton" type="button" aria-label="Start new session"></button>
        </div>
      </header>

      <!-- Main Conversation & Viewport Area -->
      <section class="conversation-area conversation-scroll-shell" aria-label="Workspace Viewport">
        <!-- Central AI Hero Section (Exact Match to Reference Design) -->
        <div class="welcome-panel" id="welcomePanel">
          <!-- Floating Subtle Information Card (Left Side) -->
          <div class="floating-side-card float-left-card" id="floatLeftCard" title="Deep Reasoning">
            <div class="side-card-icon-wrap icon-sparkle">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 2L14.5 9.5L22 12L14.5 14.5L12 22L9.5 14.5L2 12L9.5 9.5L12 2Z"/>
              </svg>
            </div>
            <div class="side-card-text">
              <strong class="side-card-title">Think Deeper</strong>
              <span class="side-card-sub">Get smarter answers with Gemini 2.5</span>
            </div>
          </div>

          <!-- Floating Subtle Information Card (Right Side) -->
          <div class="floating-side-card float-right-card" id="floatRightCard" title="Quantum Engine">
            <div class="side-card-icon-wrap icon-waveform">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <path d="M12 2v20M17 5v14M7 9v6M22 10v4M2 11v2"/>
              </svg>
            </div>
            <div class="side-card-text">
              <strong class="side-card-title">Quantum Engine</strong>
              <span class="side-card-sub">Sub-millisecond Cognitive Synthesis</span>
            </div>
          </div>

          <!-- Animated "Chat Assistant" Hero Stage -->
          <div class="chat-assistant-stage" id="chatAssistantStage" role="banner" aria-label="Chat Assistant">
            <!-- Ambient Cosmic Radial Glow -->
            <div class="assistant-glow-aura" aria-hidden="true"></div>

            <!-- Sleek AI Indicator Pill -->
            <div class="assistant-status-pill">
              <span class="pill-spark-icon">
                <svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor">
                  <path d="M12 2L14.5 9.5L22 12L14.5 14.5L12 22L9.5 14.5L2 12L9.5 9.5L12 2Z"/>
                </svg>
              </span>
              <span class="pill-text-status">QUANTUM COGNITIVE AI &bull; GEMINI 2.5</span>
              <span class="pill-online-beacon"></span>
            </div>

            <!-- Main Breathtaking Animated "Chat Assistant" Heading -->
            <div class="assistant-title-wrapper">
              <h1 class="chat-assistant-headline">
                <span class="headline-gradient-text">Chat Assistant</span>
              </h1>
              <!-- Horizon Cyber Light Ray -->
              <div class="assistant-horizon-light" aria-hidden="true">
                <span class="horizon-center-core">✦</span>
              </div>
            </div>

            <!-- Dynamic Live Typewriter Subtitle -->
            <div class="assistant-typing-bar">
              <span class="typing-lead-icon">⚡</span>
              <span class="typing-lead-text">Ready to</span>
              <span class="typing-dynamic-text" id="assistantTypewriter">think deeper, analyze data, and architect solutions</span>
              <span class="typing-cursor">|</span>
            </div>
          </div>

          <!-- Main Hero Greeting & Headline (Clean, No Personal Names) -->
          <h1 class="welcome-title">
            <span class="title-headline">What shall we engineer, architect, or analyze today?</span>
          </h1>

          <!-- Three Sleek, Clean Prompt Cards -->
          <div class="feature-cards-row" id="suggestionGrid">
            <!-- Card 1: Launch Plan -->
            <button class="feature-card card-launch suggestion-card" type="button" data-prompt="Draft a comprehensive launch roadmap for a high-impact AI SaaS application. Include timeline, marketing channels, and KPI metrics.">
              <div class="card-icon-circle icon-rocket">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"></path>
                  <path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"></path>
                  <path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"></path>
                  <path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"></path>
                </svg>
              </div>
              <div class="card-text-block">
                <strong class="card-title">Launch Plan</strong>
                <span class="card-sub">Build your next big idea</span>
              </div>
              <div class="card-arrow-circle">
                <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5">
                  <path d="M5 12h14M12 5l7 7-7 7"></path>
                </svg>
              </div>
            </button>

            <!-- Card 2: Refactor Code -->
            <button class="feature-card card-refactor suggestion-card" type="button" data-prompt="Audit and refactor this code architecture for scalability, memory efficiency, and modern design patterns: ">
              <div class="card-icon-circle icon-code">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="16 18 22 12 16 6"></polyline>
                  <polyline points="8 6 2 12 8 18"></polyline>
                </svg>
              </div>
              <div class="card-text-block">
                <strong class="card-title">Refactor Code</strong>
                <span class="card-sub">Clean &bull; Optimize &bull; Improve</span>
              </div>
              <div class="card-arrow-circle">
                <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5">
                  <path d="M5 12h14M12 5l7 7-7 7"></path>
                </svg>
              </div>
            </button>

            <!-- Card 3: Architecture & Trade-offs -->
            <button class="feature-card card-tradeoff suggestion-card" type="button" data-prompt="Summarize key architectural trade-offs, scalability bottlenecks, and comparative decision matrix between: ">
              <div class="card-icon-circle icon-analytics">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="18" y1="20" x2="18" y2="10"></line>
                  <line x1="12" y1="20" x2="12" y2="4"></line>
                  <line x1="6" y1="20" x2="6" y2="14"></line>
                </svg>
              </div>
              <div class="card-text-block">
                <strong class="card-title">Deep Architecture</strong>
                <span class="card-sub">Compare &bull; Decide &bull; Scale</span>
              </div>
              <div class="card-arrow-circle">
                <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5">
                  <path d="M5 12h14M12 5l7 7-7 7"></path>
                </svg>
              </div>
            </button>
          </div>
        </div>

        <!-- Chat Stream Messages (Active when conversation begins) -->
        <div class="chat-messages conversation-scroll-pane" id="chatMessages" aria-live="polite"></div>
      </section>

      <!-- Premium Pill Chat Input Console (Exact Match to Reference Design) -->
      <form class="prompt-form" id="promptForm">
        <div class="prompt-pill-bar">
          <!-- Sparkling AI Emblem on Left -->
          <div class="input-sparkle-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 2L14.5 9.5L22 12L14.5 14.5L12 22L9.5 14.5L2 12L9.5 9.5L12 2Z"/>
            </svg>
          </div>

          <!-- Textarea Input Area -->
          <div class="textarea-wrapper">
            <textarea id="promptInput" rows="1" placeholder="Message Project AI... (Ask complex code, architecture, or strategies)" autocomplete="off" spellcheck="true"></textarea>
          </div>

          <!-- Tool Actions: Clear & Send Button -->
          <div class="prompt-actions">
            <!-- Clear Button -->
            <button type="button" class="prompt-tool-btn clear-btn" id="clearInputBtn" title="Clear input" style="display: none;">
              <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
            </button>

            <!-- Gradient Circular Send Button -->
            <button class="send-button" id="sendButton" type="button" aria-label="Send message" aria-disabled="true" title="Send message (Enter)">
              <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="22" y1="2" x2="11" y2="13"></line>
                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
              </svg>
            </button>
          </div>
        </div>

        <!-- Footer Meta: Keyboard Hints & Powered by Gemini 2.5 -->
        <div class="prompt-footer-meta">
          <div class="keyboard-hints">
            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" class="keyboard-icon">
              <rect x="2" y="4" width="20" height="16" rx="2" ry="2"></rect>
              <line x1="6" y1="8" x2="6" y2="8"></line>
              <line x1="10" y1="8" x2="10" y2="8"></line>
              <line x1="14" y1="8" x2="14" y2="8"></line>
              <line x1="18" y1="8" x2="18" y2="8"></line>
              <line x1="6" y1="12" x2="6" y2="12"></line>
              <line x1="18" y1="12" x2="18" y2="12"></line>
              <line x1="7" y1="16" x2="17" y2="16"></line>
            </svg>
            <span>Press <kbd>Enter</kbd> to send</span>
            <span class="meta-dot">&bull;</span>
            <span><kbd>Shift</kbd> + <kbd>Enter</kbd> for new line</span>
          </div>

          <div class="model-credit-line">
            <span class="credit-divider"></span>
            <span class="credit-badge">
              <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" class="credit-sparkle">
                <path d="M12 2L14.5 9.5L22 12L14.5 14.5L12 22L9.5 14.5L2 12L9.5 9.5L12 2Z"/>
              </svg>
              Powered by Gemini 2.5
            </span>
            <span class="credit-divider"></span>
          </div>
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

  <!-- Backend Endpoint Bridge (100% Intact) -->
  <script>window.DEMO_PROJECT_AI_ENDPOINT = @json(route('member.project-ai.message'));</script>
  <script defer src="{{ asset('ai_assets/script.js') }}?v=assistant-v18.0"></script>
</body>
</html>
