<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#020817">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>JARVIS &bull; AI Voice Assistant</title>
  <link rel="stylesheet" href="{{ asset('styles.css') }}?v=11">
</head>
<body>
  <main class="assistant-app" aria-label="JARVIS AI Assistant dashboard" data-assistant-state="idle">
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

    <div class="dynamic-overlay dynamic-time">{{ now()->format('h:i A') }}<small>{{ now()->format('l, d F Y') }}</small></div>
    <div class="dynamic-overlay dynamic-greeting" id="dashboardGreeting">Good {{ now()->hour < 12 ? 'Morning' : (now()->hour < 17 ? 'Afternoon' : 'Evening') }}, {{ $data->name }} 👋</div>
    <div class="dynamic-overlay dynamic-profile-pill" id="dashboardProfilePill">
      <div class="dynamic-avatar" id="dashboardAvatar">
        @if (filled($data->profile_image))
          <img src="{{ asset($data->profile_image) }}" alt="{{ $data->name }}">
        @else
          <span>{{ mb_strtoupper(mb_substr($data->name, 0, 1)) }}</span>
        @endif
      </div>
      <div class="dynamic-profile-info">
        <strong id="dashboardProfileName">{{ $data->name }}</strong>
        <small><span class="online-dot"></span> Online</small>
      </div>
    </div>
    <div class="dynamic-overlay dynamic-stats" aria-label="Live assistant statistics">
      <strong data-stat="chats">{{ $assistantStats['chats'] }}</strong>
      <strong data-stat="tasks">{{ $assistantStats['tasks'] }}</strong>
      <strong data-stat="usage_minutes">{{ $assistantStats['usage_minutes'] }}m</strong>
      <strong data-stat="success_rate">{{ $assistantStats['success_rate'] === null ? '—' : $assistantStats['success_rate'].'%' }}</strong>
      <span>Success rate</span>
    </div>
    <div class="dynamic-overlay dynamic-creator-badge">✨ JARVIS AI &bull; Created by Deepanshu Saini</div>

    <canvas id="liveWaveform" class="live-waveform" aria-hidden="true"></canvas>
    <p class="live-state-label" data-assistant-status>Tap to speak</p>

    <section class="hotspots" aria-label="Assistant controls">
      <button class="hotspot profile-control" type="button" aria-label="Open profile menu" aria-expanded="false"></button>
      <button class="hotspot notification-control" type="button" aria-label="Notifications, no unread items"></button>
      <button class="hotspot language-control" type="button" aria-label="Assistant automatically detects English, Hindi, and Hinglish"></button>
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

    <div class="popover profile-menu" role="menu" aria-hidden="true">
      <strong id="menuProfileName">{{ $data->name }}</strong><small>● Online</small>
      <button type="button" class="profile-menu-action" id="openProfileModalBtn">👤 Edit Profile &amp; Photo</button>
      <div class="creator-badge-dropdown">✨ AI created by <strong>Deepanshu Saini</strong></div>
      <a href="{{ url('member/logout') }}" role="menuitem">Sign out</a>
    </div>

    <div class="popover settings-menu" aria-hidden="true">
      <strong>Voice Assistant Settings</strong>
      <p>Gemini automatically detects English, Hindi, and Hinglish.</p>
      <button type="button" class="open-gemini-key-modal" id="openGeminiModalBtn">
        <span>✨ Configure Gemini Key</span>
        <span class="settings-key-badge" id="settingsKeyBadge"></span>
      </button>
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

  <!-- Edit Profile & Avatar Modal -->
  <div class="gemini-modal-overlay" id="profileModalOverlay" aria-hidden="true">
    <div class="gemini-card-modal profile-card-modal" role="dialog" aria-modal="true" aria-labelledby="profileModalTitle">
      <div class="modal-glow-bg"></div>
      <div class="modal-header">
        <div class="modal-title-group">
          <div class="gemini-icon-badge">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
            </svg>
          </div>
          <div>
            <h2 id="profileModalTitle">Edit Profile &amp; Photo</h2>
            <p class="modal-subtitle">Update your personal details and set your profile avatar.</p>
          </div>
        </div>
        <button type="button" class="modal-close-btn" id="closeProfileModalBtn" aria-label="Close modal">&times;</button>
      </div>

      <form id="editProfileForm" enctype="multipart/form-data">
        <div class="modal-body">
          <!-- Avatar Upload Container -->
          <div class="profile-avatar-picker-section">
            <div class="avatar-picker-wrapper">
              <div class="avatar-preview-circle" id="modalAvatarPreview">
                @if (filled($data->profile_image))
                  <img src="{{ asset($data->profile_image) }}" alt="{{ $data->name }}" id="previewAvatarImg">
                @else
                  <span id="previewAvatarInitial">{{ mb_strtoupper(mb_substr($data->name, 0, 1)) }}</span>
                  <img src="" alt="Avatar" id="previewAvatarImg" style="display:none;">
                @endif
                <div class="avatar-camera-badge">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                    <circle cx="12" cy="13" r="4"/>
                  </svg>
                </div>
              </div>
              <input type="file" id="profileImageInput" name="profile_image" accept="image/*" style="display:none;">
              <button type="button" class="btn-choose-photo" id="btnTriggerPhotoUpload">
                <span>Change Profile Photo</span>
              </button>
            </div>
          </div>

          <div class="profile-fields-grid">
            <div class="modal-field">
              <label for="editProfileName">Full Name</label>
              <div class="key-input-container">
                <input type="text" id="editProfileName" name="name" value="{{ $data->name }}" required />
              </div>
            </div>

            <div class="modal-field">
              <label for="editProfileEmail">Email Address</label>
              <div class="key-input-container">
                <input type="email" id="editProfileEmail" name="email" value="{{ $data->email }}" required />
              </div>
            </div>

            <div class="modal-field">
              <label for="editProfileMobile">Mobile Number</label>
              <div class="key-input-container">
                <input type="tel" id="editProfileMobile" name="mobile" value="{{ $data->mobile }}" required />
              </div>
            </div>

            <div class="modal-field">
              <label for="editProfilePassword">New Password <small style="color:#7892bd;font-weight:400;">(Optional)</small></label>
              <div class="key-input-container">
                <input type="password" id="editProfilePassword" name="password" placeholder="Leave blank to keep unchanged" autocomplete="new-password" />
              </div>
            </div>
          </div>

          <div class="key-validation-feedback" id="profileFeedback" style="display:none;"></div>

          <div class="modal-footer-actions">
            <div class="creator-credit-note">
              ✨ <span>AI created by <strong>Deepanshu Saini</strong></span>
            </div>
            <button type="submit" class="modal-btn btn-save" id="saveProfileBtn">
              <span class="btn-spinner" id="profileSaveSpinner" style="display:none;"></span>
              <span id="profileSaveBtnText">Save Profile</span>
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Gemini API Key Card Modal -->
  <div class="gemini-modal-overlay" id="geminiModalOverlay" aria-hidden="true">
    <div class="gemini-card-modal" role="dialog" aria-modal="true" aria-labelledby="geminiModalTitle">
      <div class="modal-glow-bg"></div>
      <div class="modal-header">
        <div class="modal-title-group">
          <div class="gemini-icon-badge">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
              <path d="M12 2L14.6 9.4L22 12L14.6 14.6L12 22L9.4 14.6L2 12L9.4 9.4L12 2Z" fill="url(#geminiModalGrad)"/>
              <defs>
                <linearGradient id="geminiModalGrad" x1="2" y1="2" x2="22" y2="22" gradientUnits="userSpaceOnUse">
                  <stop stop-color="#00E5FF"/>
                  <stop offset="0.5" stop-color="#9B4DFF"/>
                  <stop offset="1" stop-color="#FF4DF0"/>
                </linearGradient>
              </defs>
            </svg>
          </div>
          <div>
            <h2 id="geminiModalTitle">Google Gemini API Key</h2>
            <p class="modal-subtitle">Configure your personal Gemini API key for all voice assistant features.</p>
          </div>
        </div>
        <button type="button" class="modal-close-btn" id="closeGeminiModalBtn" aria-label="Close modal">&times;</button>
      </div>

      <div class="modal-body">
        <div class="key-status-card" id="keyStatusCard">
          <div class="status-icon-wrap" id="statusIconWrap">
            <span class="status-ping"></span>
            <span class="status-circle"></span>
          </div>
          <div class="status-info">
            <strong id="statusTitle">API Key Not Set</strong>
            <p id="statusDesc">Please add your Google Gemini API key to activate voice and assistant capabilities.</p>
          </div>
        </div>

        <div class="modal-field">
          <div class="field-label-row">
            <label for="geminiApiKeyInput">Your Gemini API Key</label>
            <a href="https://aistudio.google.com/app/apikey" target="_blank" rel="noopener noreferrer" class="get-key-link">
              Get free key from Google AI Studio &nearr;
            </a>
          </div>
          <div class="key-input-container">
            <input type="password" id="geminiApiKeyInput" placeholder="Enter your Gemini API key (e.g. AIzaSy...)" autocomplete="off" spellcheck="false" />
            <button type="button" class="btn-toggle-eye" id="toggleKeyVisibilityBtn" aria-label="Toggle API Key visibility">
              <svg class="icon-eye-open" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg class="icon-eye-closed" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
            </button>
          </div>
          <div class="key-validation-feedback" id="keyFeedback" style="display:none;"></div>
        </div>

        <div class="modal-footer-actions">
          <button type="button" class="modal-btn btn-test" id="testKeyBtn">
            <span class="btn-spinner" id="testSpinner" style="display:none;"></span>
            <span id="testBtnText">Test Connection</span>
          </button>
          <div class="modal-footer-right">
            <button type="button" class="modal-btn btn-danger" id="removeKeyBtn" style="display:none;">
              Remove Key
            </button>
            <button type="button" class="modal-btn btn-save" id="saveKeyBtn">
              Save API Key
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  @php
    $voiceAssistantConfig = [
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
  <script type="module" src="{{ asset('voice_assistant/voice-assistant.js') }}?v=11"></script>
</body>
</html>
