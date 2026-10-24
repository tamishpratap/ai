<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#020611">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>NEXUS AI &mdash; Project Intelligence</title>
  <link rel="stylesheet" href="{{ asset('project_voice_assistant/nexus-dashboard.css') }}?v=6">
</head>
<body>
  <svg class="icon-sprite" aria-hidden="true">
    <symbol id="i-wave" viewBox="0 0 24 24"><path d="M3 12h2l2-7 3 14 3-11 3 8 2-4h3"/></symbol>
    <symbol id="i-bell" viewBox="0 0 24 24"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></symbol>
    <symbol id="i-globe" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a15 15 0 0 1 0 18M12 3a15 15 0 0 0 0 18"/></symbol>
    <symbol id="i-calendar" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18"/></symbol>
    <symbol id="i-mic" viewBox="0 0 24 24"><rect x="9" y="3" width="6" height="11" rx="3"/><path d="M5 11a7 7 0 0 0 14 0M12 18v4M8 22h8"/></symbol>
    <symbol id="i-brain" viewBox="0 0 24 24"><path d="M9.5 4A3 3 0 0 0 5 6.6 3.5 3.5 0 0 0 4 13a3.5 3.5 0 0 0 4 5.5A3 3 0 0 0 12 20V5.5A2.5 2.5 0 0 0 9.5 3Z"/><path d="M14.5 4A3 3 0 0 1 19 6.6a3.5 3.5 0 0 1 1 6.4 3.5 3.5 0 0 1-4 5.5A3 3 0 0 1 12 20V5.5A2.5 2.5 0 0 1 14.5 3ZM8 9h4M12 14h4"/></symbol>
    <symbol id="i-income" viewBox="0 0 24 24"><path d="M4 19 9 13l4 3 7-9M15 7h5v5"/></symbol>
    <symbol id="i-business" viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V4h8v3M3 12h18"/></symbol>
    <symbol id="i-team" viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"/><path d="M3 20v-2a6 6 0 0 1 12 0v2M16 5a3 3 0 0 1 0 6M17 14a5 5 0 0 1 4 5v1"/></symbol>
    <symbol id="i-wallet" viewBox="0 0 24 24"><path d="M4 6h14a2 2 0 0 1 2 2v11H5a2 2 0 0 1-2-2V6a3 3 0 0 1 3-3h11"/><path d="M15 11h7v5h-7a2.5 2.5 0 0 1 0-5Z"/></symbol>
    <symbol id="i-summary" viewBox="0 0 24 24"><path d="M4 4h16v16H4zM8 15l3-3 2 2 4-5"/></symbol>
    <symbol id="i-shield" viewBox="0 0 24 24"><path d="M12 3 4 6v5c0 5 3.5 8.5 8 10 4.5-1.5 8-5 8-10V6l-8-3Z"/><path d="m9 12 2 2 4-4"/></symbol>
    <symbol id="i-spark" viewBox="0 0 24 24"><path d="m12 2 1.8 5.2L19 9l-5.2 1.8L12 16l-1.8-5.2L5 9l5.2-1.8L12 2Z"/></symbol>
  </svg>

  <main class="nexus-dashboard" data-endpoint="{{ route('member.ai.project-voice-message') }}" data-state="idle">
    <div class="ai-atmosphere" aria-hidden="true">
      <i class="ai-scan-beam"></i><i class="horizon-energy"></i>
      @foreach([[12,26,0],[27,63,2],[42,18,4],[58,31,1],[72,56,3],[87,24,5],[91,69,2.5],[35,76,4.5]] as [$x,$y,$delay])
        <b style="--node-x:{{ $x }}%;--node-y:{{ $y }}%;--node-delay:{{ $delay }}s"></b>
      @endforeach
    </div>
    <header class="nexus-header">
      <a class="brand" href="{{ url('member/dashboard') }}" aria-label="Back to member dashboard"><i class="brand-mark"></i><span><strong>NEXUS AI</strong><small>PROJECT INTELLIGENCE</small></span></a>
      <div class="clock-card glass"><strong id="currentTime">{{ now()->format('h:i A') }}</strong><small id="currentDate">{{ now()->format('d M Y') }}</small></div>
      <div class="header-tools">
        <button class="circle-tool settings-control" type="button" aria-label="Voice controls" aria-expanded="false"><svg><use href="#i-wave"/></svg></button>
        <button class="circle-tool notification-control" type="button" aria-label="Notifications"><svg><use href="#i-bell"/></svg></button>
        <button class="circle-tool language-control" type="button" aria-label="Change voice language"><svg><use href="#i-globe"/></svg></button>
        <button class="profile-control" type="button" aria-label="Open profile menu" aria-expanded="false">
          <span class="avatar">@if(filled($data->profile_image))<img src="{{ asset('uploads/'.$data->profile_image) }}" alt="{{ $data->name ?? 'User' }}">@else{{ mb_strtoupper(mb_substr($data->name ?? 'M', 0, 1)) }}@endif</span>
          <span><strong>{{ $data->name ?? session('MEMBER_NAME', 'Member') }}</strong><small>&#9679; Online</small></span>
        </button>
      </div>
    </header>

    <section class="hero">
      <p class="hero-greeting">Good {{ now()->hour < 12 ? 'Morning' : (now()->hour < 17 ? 'Afternoon' : 'Evening') }}, {{ $data->name ?? session('MEMBER_NAME', 'Member') }} &#128075;</p>
      <h1>Know your business.<span>Lead with clarity.</span></h1>
      <p class="hero-copy">Voice-powered insights from your verified income, team, wallet and business data.</p>
      <div class="hero-actions">
        <div class="privacy-pill"><i></i> Private &amp; member-scoped</div>
        <button type="button" class="continuous-pill" id="continuousPill" aria-pressed="true" title="Hands-free continuous conversation mode (auto-listen after assistant speaks)">
          <i class="dot"></i>
          <span>Hands-Free Conversation: <strong data-continuous-label>ON</strong></span>
        </button>
      </div>
    </section>

    <aside class="summary glass" aria-label="Today summary">
      <div class="summary-head"><h2><svg><use href="#i-spark"/></svg> Today Summary</h2><span><svg><use href="#i-calendar"/></svg></span></div>
      <div class="kpi-grid">
        <article class="kpi cyan"><small>Income today</small><strong>${{ number_format($projectStats['income_today'], 2) }}</strong><div class="sparkline"><svg viewBox="0 0 150 30"><path d="M1 25 C15 8 22 27 35 16 S55 26 68 12 88 24 102 10 122 23 149 5"/></svg></div></article>
        <article class="kpi purple"><small>Team business</small><strong>${{ number_format($projectStats['business'], 2) }}</strong><div class="sparkline"><svg viewBox="0 0 150 30"><path d="M1 24 C13 11 25 27 37 17 S58 25 72 14 94 27 108 9 128 24 149 6"/></svg></div></article>
        <article class="kpi teal"><small>Team members</small><strong>{{ number_format($projectStats['team_members']) }}</strong><div class="sparkline"><svg viewBox="0 0 150 30"><path d="M1 25 C17 11 27 23 42 16 S63 26 77 13 98 22 113 7 133 25 149 15"/></svg></div></article>
        <article class="kpi orange"><small>Wallet balance</small><strong>${{ number_format($projectStats['wallet_balance'], 2) }}</strong><div class="sparkline"><svg viewBox="0 0 150 30"><path d="M1 24 C16 8 26 26 41 17 S63 28 78 12 99 24 113 8 133 18 149 5"/></svg></div></article>
      </div>
    </aside>

    <div class="ai-hologram" aria-hidden="true"><i class="holo-ring"></i><i class="holo-ring two"></i><i class="holo-ring three"></i><span class="brain-core"><svg><use href="#i-brain"/></svg></span></div>

    <section class="voice-zone" aria-label="Voice assistant control">
      <span class="blast-fx" aria-hidden="true">
        <i class="blast-ring"></i><i class="blast-ring blast-ring-two"></i>
        <span class="blast-particles">
          @foreach([0,30,60,90,120,150,180,210,240,270,300,330] as $angle)<b style="--blast-angle:{{ $angle }}deg"></b>@endforeach
        </span>
      </span>
      <div class="waveform" aria-hidden="true">@for($i=0;$i<88;$i++)<i></i>@endfor</div>
      <button class="voice-button" id="voiceButton" data-voice-trigger type="button" aria-label="Start voice input" aria-pressed="false"><svg><use href="#i-mic"/></svg></button>
      <p class="voice-status" id="voiceStatus" data-assistant-status aria-live="polite">Tap to speak</p>
    </section>

    <nav class="quick-actions" aria-label="Project data shortcuts">
      <button type="button" data-question="Meri aaj ki total income kitni bani?"><span class="action-icon"><svg><use href="#i-income"/></svg></span><span><strong>Aaj ki income</strong><small>Today's earning report</small></span></button>
      <button type="button" data-question="Mera self business aur team business kitna hai?"><span class="action-icon"><svg><use href="#i-business"/></svg></span><span><strong>Mera business</strong><small>Business performance</small></span></button>
      <button type="button" data-question="Meri team mein kitne members hain?"><span class="action-icon"><svg><use href="#i-team"/></svg></span><span><strong>Meri team</strong><small>Team analytics</small></span></button>
      <button type="button" data-question="Mera wallet balance batao"><span class="action-icon"><svg><use href="#i-wallet"/></svg></span><span><strong>Wallet balance</strong><small>Available balance</small></span></button>
      <button type="button" data-question="Mera complete account summary batao"><span class="action-icon"><svg><use href="#i-summary"/></svg></span><span><strong>Complete summary</strong><small>Full member overview</small></span></button>
    </nav>

    <div class="verify-footer"><svg><use href="#i-spark"/></svg> AI makes mistakes. Always verify important information. <svg><use href="#i-shield"/></svg></div>
    <button class="floating-action keyboard-control" type="button" aria-label="Open text assistant" aria-expanded="false"><svg><use href="#i-spark"/></svg></button>

    <div class="popover profile-menu glass" role="menu" aria-hidden="true"><strong>{{ $data->name ?? session('MEMBER_NAME', 'Member') }}</strong><small>&#9679; Online</small><a href="{{ url('/member/profile/user-summary') }}">Profile settings</a><a href="{{ route('member.ai.voice-assistant') }}">General Voice Assistant</a><a href="{{ url('member/logout') }}">Sign out</a></div>
    <div class="popover settings-menu glass" aria-hidden="true"><strong>Project Voice Assistant</strong><p>Only your verified member ID data is used.</p><span>Voice: Kore Profile (Smooth Human-like Neural)</span><button type="button" id="toggleContinuousBtn">Continuous Mode: Active (ON)</button><span data-status-copy>Ready for your voice</span><button type="button" id="stopSpeaking">Stop assistant audio</button></div>

    <form class="keyboard-panel glass" id="questionForm" aria-hidden="true">
      <div class="panel-heading"><label for="questionInput" id="responseLabel">Ask NEXUS AI</label><button type="button" id="closeResponse" aria-label="Close">&times;</button></div>
      <section id="responsePopover" aria-live="polite"><div class="messages" id="messages"><article class="message assistant-message"><p>Namaste {{ $data->name ?? session('MEMBER_NAME', 'Member') }}! Main aapki verified project information ke liye ready hoon.</p></article></div></section>
      <div class="keyboard-row"><select id="languageSelect" aria-label="Voice language"><option value="hi-IN">Hindi</option><option value="en-IN">English</option></select><input id="questionInput" maxlength="1000" autocomplete="off" placeholder="Apne project account ke baare mein poochhein..."><button type="submit" id="sendButton">Send</button></div>
    </form>
    <div class="toast" id="toast" role="status" aria-live="polite"></div>
  </main>
  <script src="{{ asset('project_voice_assistant/app.js') }}?v=14" defer></script>
</body>
</html>
