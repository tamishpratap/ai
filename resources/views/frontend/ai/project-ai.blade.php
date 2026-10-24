<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="description" content="A premium project AI workspace powered by the Google Gemini API.">
  <title>Project AI Workspace</title>
  <link rel="stylesheet" href="{{ asset('ai_assets/style.css') }}?v=3">
</head>
<body>
  <div class="app-shell">
    <main class="main-panel">
      <header class="topbar">
        <div class="topbar-left">
          <a href="{{ url('/') }}" class="topbar-back" title="Back to Quantum ARB Landing Page">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            <span>Landing Page</span>
          </a>
          <button class="model-selector" type="button" aria-label="Current model">
            <span class="model-status-dot"></span>
            <span>Gemini 2.5 Flash</span>
          </button>
        </div>

        <div class="topbar-actions">
          <a href="{{ url('/ai/voice-assistant') }}" class="topbar-pill" title="Switch to Voice AI Assistant">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path>
              <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
              <line x1="12" y1="19" x2="12" y2="23"></line>
              <line x1="8" y1="23" x2="16" y2="23"></line>
            </svg>
            <span>Voice Assistant</span>
          </a>
          <button class="new-chat-button topbar-new-chat" id="newChatButton" type="button" aria-label="New chat">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M12 5v14M5 12h14"></path>
            </svg>
            <span>New chat</span>
          </button>
        </div>
      </header>

      <section class="conversation-area conversation-scroll-shell" aria-label="Conversation">
        <div class="welcome-panel" id="welcomePanel">
          <div class="welcome-kicker">Quantum AI Workspace</div>
          <h1>
            <span>Hello, {{ $data->name ?? 'Quantum Member' }}.</span>
            <span>What would you like to build, analyze, or plan today?</span>
          </h1>
          <p class="welcome-copy">
            Use this workspace for project thinking, content drafting, technical help, and polished strategy support with a premium AI experience.
          </p>

          <div class="welcome-tags">
            <span class="welcome-tag">Fast Ideas</span>
            <span class="welcome-tag">Strategic Prompts</span>
            <span class="welcome-tag">Project Guidance</span>
          </div>

          <div class="suggestion-grid" id="suggestionGrid">
            <button class="suggestion-card" type="button" data-prompt="Draft a crisp launch plan for a new AI productivity app.">
              <span>Draft a launch plan</span>
              <small>Roadmap, positioning, execution</small>
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M4 19.5V4.5A2.5 2.5 0 0 1 6.5 2H18a2 2 0 0 1 2 2v16a1 1 0 0 1-1 1H6.5A2.5 2.5 0 0 1 4 18.5"></path>
                <path d="M8 6h8M8 10h8M8 14h5"></path>
              </svg>
            </button>
            <button class="suggestion-card" type="button" data-prompt="Explain JavaScript closures with a short example and a memorable analogy.">
              <span>Explain closures</span>
              <small>Concept + example + analogy</small>
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M8 9h8M8 13h5"></path>
                <path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4Z"></path>
              </svg>
            </button>
            <button class="suggestion-card" type="button" data-prompt="Create a weekly learning schedule for mastering frontend system design.">
              <span>Build a learning plan</span>
              <small>Weekly system design practice</small>
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M8 2v4M16 2v4M3 10h18"></path>
                <path d="M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z"></path>
              </svg>
            </button>
            <button class="suggestion-card" type="button" data-prompt="Review this code for accessibility, performance, and maintainability: ">
              <span>Review my code</span>
              <small>Accessibility, speed, maintainability</small>
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="m8 9-4 3 4 3M16 9l4 3-4 3M14 4l-4 16"></path>
              </svg>
            </button>
          </div>
        </div>

        <div class="chat-messages conversation-scroll-pane" id="chatMessages" aria-live="polite"></div>
      </section>

      <form class="prompt-form" id="promptForm">
        <div class="prompt-bar">
          <textarea id="promptInput" rows="1" placeholder="Ask Project AI anything..." autocomplete="off" spellcheck="true"></textarea>

          <div class="prompt-actions">
            <button class="send-button" id="sendButton" type="button" aria-label="Send message" aria-disabled="true">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M5 12h14M13 6l6 6-6 6"></path>
              </svg>
            </button>
          </div>
        </div>
        <p class="prompt-disclaimer">Project AI can still make mistakes, so always verify critical output before using it.</p>
      </form>
    </main>
  </div>

  <div class="toast" id="toast" role="status" aria-live="polite"></div>

  <script>window.DEMO_PROJECT_AI_ENDPOINT = @json(route('member.project-ai.message'));</script>
  <script defer src="{{ asset('ai_assets/script.js') }}?v=general-chat-v3"></script>
</body>
</html>
