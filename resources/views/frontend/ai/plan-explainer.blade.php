<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#040906">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Quantum ARB &bull; Plan Explainer AI Voice Assistant</title>
  <meta name="description" content="Human-like AI Voice Assistant explaining the Quantum ARB business plan strictly from our official document in Hindi and English.">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="{{ asset('plan_voice_assistant/plan-voice.css') }}?v=4">
</head>
<body>

  <!-- Ambient Dito Frontend Background Layers -->
  <div class="bgfx" aria-hidden="true"></div>
  <div class="bg-grid" aria-hidden="true"></div>
  
  <!-- Interactive 3D Earth Globe Canvas (Same as Frontend) -->
  <canvas id="bg3d" aria-hidden="true"></canvas>

  <!-- Shell Container -->
  <main class="explainer-shell" data-endpoint="{{ route('ai.plan-explainer.message') }}" data-speech-endpoint="{{ route('ai.plan-explainer.speech') }}" data-upload-endpoint="{{ route('ai.plan-explainer.document.upload') }}" data-state="idle">

    <!-- Top Navigation -->
    <header class="top-nav glass">
      <div class="nav-left">
        <a href="{{ url('/') }}" class="nav-back-btn" title="Back to Quantum ARB Landing Page">
          <i class="fa-solid fa-arrow-left"></i>
          <span>Home</span>
        </a>
        <div class="brand-wrap">
          <span class="brand-dot"></span>
          <div class="brand-text">
            <strong>QUANTUM ARB</strong>
            <small>PLAN EXPLAINER AI</small>
          </div>
        </div>
      </div>

      <div class="nav-right">
        <!-- 2 Language Option: Hindi & English -->
        <div class="lang-switch" role="group" aria-label="Select voice language">
          <button type="button" class="lang-btn active" data-lang="hi" title="Hindi / Hinglish Mode">🇮🇳 Hindi</button>
          <button type="button" class="lang-btn" data-lang="en" title="English Mode">🇬🇧 English</button>
        </div>

        <!-- Admin Upload Plan File (PDF / TXT) -->
        <button type="button" class="admin-upload-btn" id="openUploadModalBtn" title="Admin: Upload Plan File (PDF or TXT)">
          <i class="fa-solid fa-cloud-arrow-up"></i>
          <span>Upload Plan File</span>
        </button>

        <!-- Plan Document Text View/Edit Button -->
        <button type="button" class="doc-btn" id="openDocModalBtn" title="View or edit the source business plan document">
          <i class="fa-solid fa-file-lines"></i>
          <span>Document</span>
        </button>

        <!-- Audio Stop Button -->
        <button type="button" class="stop-audio-btn" id="stopAudioBtn" title="Stop speaking">
          <i class="fa-solid fa-volume-xmark"></i>
          <span>Stop</span>
        </button>
      </div>
    </header>

    <!-- Hero Section -->
    <section class="hero-stage">
      <div class="badge">
        <span class="dot"></span>
        <span>Natural Voice &bull; Kore Persona &bull; Non-Stop Document Speech</span>
      </div>
      <h1>
        Plan Explainer <span class="gtext">Voice AI</span>
      </h1>
      <p class="lead">
        Click the central microphone to hear the full business plan speech from the admin's verified document delivered continuously in natural human voice (Kore), part by part without stopping.
      </p>

      <!-- Central Voice Assistant Continuous Narration Panel -->
      <div class="voice-panel glass">
        <!-- Pulsing Sphere & Mic Play Button -->
        <div class="mic-sphere-wrap">
          <div class="mic-ripple-ring" aria-hidden="true"></div>
          <button type="button" class="main-mic-btn" id="mainMicBtn" aria-label="Play full business plan speech" aria-pressed="false" title="Click to play full non-stop plan speech">
            <i class="fa-solid fa-microphone" id="micIcon"></i>
          </button>
        </div>

        <!-- Audio Live Visualizer Bars -->
        <div class="audio-wave-visualizer" id="audioVisualizer" aria-hidden="true">
          <span class="wave-bar"></span>
          <span class="wave-bar"></span>
          <span class="wave-bar"></span>
          <span class="wave-bar"></span>
          <span class="wave-bar"></span>
          <span class="wave-bar"></span>
          <span class="wave-bar"></span>
        </div>

        <!-- Voice Persona Tag -->
        <div class="voice-persona-badge">
          <i class="fa-solid fa-wand-magic-sparkles"></i>
          <span>AI Persona: <strong>Kore (Natural Human Voice)</strong> &bull; Non-Stop Document Narrator</span>
        </div>

        <!-- Status Line -->
        <p class="voice-status-line" id="voiceStatus" aria-live="polite">
          Click microphone to start full verified plan speech in natural human voice...
        </p>

        <!-- Live Teleprompter / Transcript Speech Box -->
        <div class="answer-box active speech-teleprompter" id="answerBox" aria-live="polite">
          <div class="answer-head">
            <div class="answer-head-left">
              <strong id="answerTitle"><i class="fa-solid fa-robot"></i> Plan Explainer AI (Kore) &bull; Live Speech Sync</strong>
              <span class="line-counter-pill" id="lineCounterPill"><i class="fa-solid fa-file-lines"></i> Loading...</span>
            </div>
            <div class="answer-actions">
              <span class="now-playing-pill" id="nowPlayingPill"><i class="fa-solid fa-volume-high"></i> Ready</span>
              <button type="button" class="action-icon-btn stop-btn" id="stopSpeechBtn" title="Stop speech" style="display:none;"><i class="fa-solid fa-stop"></i></button>
              <button type="button" class="action-icon-btn" id="replayAudioBtn" title="Replay full speech from start"><i class="fa-solid fa-rotate-right"></i></button>
            </div>
          </div>
          <div class="answer-body live-teleprompter-body" id="answerBody">
            <div class="speech-loading-placeholder">
              <p style="color:var(--txt2); font-size:15px; line-height:1.7;">
                <i class="fa-solid fa-spinner fa-spin" style="margin-right:8px; color:var(--green)"></i> Loading full verified business plan document...
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Unique 3D Interactive Plan Cards Deck -->
    <section class="deck-section-3d" aria-label="Interactive 3D Plan Cards">
      <div class="deck-header">
        <span class="deck-badge"><i class="fa-solid fa-cube"></i> 3D Holographic Plan Pillars</span>
        <h2>Explore Plan Pillars in <span class="gtext">3D Perspective</span></h2>
        <p>Dynamic 3D depth &amp; mouse-tilt. Click any card to hear the AI explain it in natural human voice.</p>
      </div>

      <div class="cards-grid-3d" id="cardsGrid3d">
        <!-- 3D Card 1: Daily ROI Packages -->
        <article class="card-3d" data-topic="daily_roi" data-explain-hi="Daily ROI Tiers aur packages ke baare mein vistar se samjhao" data-explain-en="Explain the daily ROI packages and return tiers in detail">
          <div class="card-3d-inner glass">
            <div class="card-3d-glow"></div>
            <div class="card-3d-sheen"></div>
            <div class="card-3d-content">
              <div class="card-3d-top">
                <div class="card-icon-3d">
                  <i class="fa-solid fa-bolt"></i>
                </div>
                <span class="badge-3d">DAILY YIELD</span>
              </div>
              <h3 class="card-title-3d">Daily ROI Packages</h3>
              <div class="card-metric-3d">
                <span class="metric-val gtext">1.5% - 4.0%</span>
                <span class="metric-unit">/ Daily</span>
              </div>
              <p class="card-desc-3d">
                Algorithmic arbitrage yield credited every 24 hours. Packages from $100 Starter to $100,000 VIP.
              </p>
              <div class="card-footer-3d">
                <span class="explain-trigger-btn">
                  <i class="fa-solid fa-volume-high"></i> Click to Explain
                </span>
                <span class="card-indicator"><i class="fa-solid fa-arrow-right"></i></span>
              </div>
            </div>
          </div>
        </article>

        <!-- 3D Card 2: 5% Direct Bonus -->
        <article class="card-3d" data-topic="direct_bonus" data-explain-hi="5% direct referral commission ke baare mein batao" data-explain-en="Explain the 5% direct referral commission and instant payout rules">
          <div class="card-3d-inner glass">
            <div class="card-3d-glow"></div>
            <div class="card-3d-sheen"></div>
            <div class="card-3d-content">
              <div class="card-3d-top">
                <div class="card-icon-3d">
                  <i class="fa-solid fa-bullseye"></i>
                </div>
                <span class="badge-3d">INSTANT CASH</span>
              </div>
              <h3 class="card-title-3d">5% Direct Sponsor</h3>
              <div class="card-metric-3d">
                <span class="metric-val gtext">5% Flat</span>
                <span class="metric-unit">Instant Credit</span>
              </div>
              <p class="card-desc-3d">
                Instant 5% referral bonus on every direct sponsored member's package deposit, withdrawable 24/7.
              </p>
              <div class="card-footer-3d">
                <span class="explain-trigger-btn">
                  <i class="fa-solid fa-volume-high"></i> Click to Explain
                </span>
                <span class="card-indicator"><i class="fa-solid fa-arrow-right"></i></span>
              </div>
            </div>
          </div>
        </article>

        <!-- 3D Card 3: 20-Level Matching -->
        <article class="card-3d" data-topic="level_matching" data-explain-hi="20-Level ROI Matching income kaise kaam karta hai" data-explain-en="Explain how the 20-level ROI matching income works across generations">
          <div class="card-3d-inner glass">
            <div class="card-3d-glow"></div>
            <div class="card-3d-sheen"></div>
            <div class="card-3d-content">
              <div class="card-3d-top">
                <div class="card-icon-3d">
                  <i class="fa-solid fa-network-wired"></i>
                </div>
                <span class="badge-3d">20 LEVELS</span>
              </div>
              <h3 class="card-title-3d">20-Level Matching</h3>
              <div class="card-metric-3d">
                <span class="metric-val gtext">Up to 15%</span>
                <span class="metric-unit">Level 1 Match</span>
              </div>
              <p class="card-desc-3d">
                Massive passive leverage on team daily ROI: Level 1 (15%), Level 2 (10%), L3-5 (5%), L6-10 (3%), L11-20 (2%).
              </p>
              <div class="card-footer-3d">
                <span class="explain-trigger-btn">
                  <i class="fa-solid fa-volume-high"></i> Click to Explain
                </span>
                <span class="card-indicator"><i class="fa-solid fa-arrow-right"></i></span>
              </div>
            </div>
          </div>
        </article>

        <!-- 3D Card 4: 3X vs 5X Capping -->
        <article class="card-3d" data-topic="capping_booster" data-explain-hi="3X aur 5X capping booster safety rule explain karo" data-explain-en="Explain the 3X and 5X safety capping multiplier booster">
          <div class="card-3d-inner glass">
            <div class="card-3d-glow"></div>
            <div class="card-3d-sheen"></div>
            <div class="card-3d-content">
              <div class="card-3d-top">
                <div class="card-icon-3d">
                  <i class="fa-solid fa-shield-halved"></i>
                </div>
                <span class="badge-3d">SAFETY BOOST</span>
              </div>
              <h3 class="card-title-3d">3X / 5X Capping</h3>
              <div class="card-metric-3d">
                <span class="metric-val gtext">300% &bull; 500%</span>
                <span class="metric-unit">Max Cap</span>
              </div>
              <p class="card-desc-3d">
                3X maximum earnings ceiling by default, easily unlocked to 5X with just 1 direct sponsor. Re-top up anytime.
              </p>
              <div class="card-footer-3d">
                <span class="explain-trigger-btn">
                  <i class="fa-solid fa-volume-high"></i> Click to Explain
                </span>
                <span class="card-indicator"><i class="fa-solid fa-arrow-right"></i></span>
              </div>
            </div>
          </div>
        </article>

        <!-- 3D Card 5: Leadership Ranks -->
        <article class="card-3d" data-topic="leadership_ranks" data-explain-hi="Leadership ranks, car bonus aur reward criteria explain karo" data-explain-en="Explain the leadership ranks, pool shares, and luxury rewards">
          <div class="card-3d-inner glass">
            <div class="card-3d-glow"></div>
            <div class="card-3d-sheen"></div>
            <div class="card-3d-content">
              <div class="card-3d-top">
                <div class="card-icon-3d">
                  <i class="fa-solid fa-trophy"></i>
                </div>
                <span class="badge-3d">ELITE POOLS</span>
              </div>
              <h3 class="card-title-3d">Leadership Ranks</h3>
              <div class="card-metric-3d">
                <span class="metric-val gtext">Silver &rarr; Crown</span>
                <span class="metric-unit">Global Pools</span>
              </div>
              <p class="card-desc-3d">
                Unlock executive royalty pools, monthly company turnover sharing, and international retreat incentives.
              </p>
              <div class="card-footer-3d">
                <span class="explain-trigger-btn">
                  <i class="fa-solid fa-volume-high"></i> Click to Explain
                </span>
                <span class="card-indicator"><i class="fa-solid fa-arrow-right"></i></span>
              </div>
            </div>
          </div>
        </article>
      </div>
    </section>

  </main>

  <!-- Admin Upload Modal (PDF / TXT) -->
  <div class="modal-overlay" id="uploadModalOverlay" role="dialog" aria-modal="true" aria-labelledby="uploadModalTitle">
    <div class="modal-card glass upload-modal-card">
      <div class="modal-head">
        <h3 id="uploadModalTitle"><i class="fa-solid fa-cloud-arrow-up"></i> Admin: Upload Plan Document (PDF / TXT)</h3>
        <button type="button" class="action-icon-btn" id="closeUploadModalBtn" aria-label="Close modal">&times;</button>
      </div>
      <p class="modal-sub">
        Upload the official business plan file in <strong>.pdf</strong> or <strong>.txt</strong> format. Gemini AI will automatically extract and parse all tiers, ROI, direct commissions, levels, and rules into the active knowledge base.
      </p>

      <form id="adminUploadForm" class="upload-form">
        <div class="dropzone-box" id="fileDropzone">
          <input type="file" id="planFileInput" name="file" accept=".pdf,.txt" class="dropzone-input" required>
          <div class="dropzone-visual">
            <i class="fa-solid fa-file-pdf dropzone-icon"></i>
            <h4>Drag &amp; Drop Plan File Here</h4>
            <p>or <span class="browse-link">browse from your computer</span></p>
            <div class="format-badges">
              <span class="fmt-badge"><i class="fa-solid fa-file-lines"></i> .TXT</span>
              <span class="fmt-badge"><i class="fa-solid fa-file-pdf"></i> .PDF</span>
              <span class="fmt-badge">Max 25MB</span>
            </div>
          </div>
          <div class="file-chosen-info" id="fileChosenInfo" style="display:none;">
            <i class="fa-solid fa-file-check chosen-icon"></i>
            <div class="chosen-meta">
              <strong id="chosenFileName">plan.pdf</strong>
              <small id="chosenFileSize">2.4 MB</small>
            </div>
            <button type="button" class="remove-file-btn" id="removeFileBtn" title="Remove selected file">&times;</button>
          </div>
        </div>

        <div class="upload-progress-wrap" id="uploadProgressWrap" style="display:none;">
          <div class="progress-bar-track">
            <div class="progress-bar-fill" id="uploadProgressBar"></div>
          </div>
          <p class="progress-status-msg" id="uploadProgressMsg">Uploading &amp; extracting compensation plan with Gemini AI...</p>
        </div>

        <div class="modal-foot">
          <button type="button" class="btn-ghost" id="cancelUploadBtn">Cancel</button>
          <button type="submit" class="btn-primary" id="startUploadBtn">
            <i class="fa-solid fa-wand-magic-sparkles"></i> Upload &amp; Update AI Document
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Document Knowledge Base Modal (View / Edit Raw Text) -->
  <div class="modal-overlay" id="docModalOverlay" role="dialog" aria-modal="true" aria-labelledby="docModalTitle">
    <div class="modal-card glass">
      <div class="modal-head">
        <h3 id="docModalTitle"><i class="fa-solid fa-file-contract"></i> Active Business Plan Document</h3>
        <button type="button" class="action-icon-btn" id="closeDocModalBtn" aria-label="Close modal">&times;</button>
      </div>
      <p class="modal-sub">
        The Plan Explainer AI consults this exact document to explain the plan in natural human voice. You can inspect or edit the extracted text directly.
      </p>
      <textarea class="doc-textarea" id="docTextarea" spellcheck="false" placeholder="Active verified business plan text...">{{ $planDocument }}</textarea>
      <div class="modal-foot">
        <button type="button" class="btn-ghost" onclick="document.getElementById('docModalOverlay').classList.remove('open')">Close</button>
        <button type="button" class="btn-primary" id="saveDocBtn">Save Document Changes</button>
      </div>
    </div>
  </div>

  <!-- Toast Notification -->
  <div class="toast-bar" id="planToast" role="status" aria-live="polite"></div>

  <!-- Three.js (for the 3D Earth Globe) -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>

  <!-- Client Voice Assistant Logic -->
  <script src="{{ asset('plan_voice_assistant/plan-voice.js') }}?v=4" defer></script>
</body>
</html>
