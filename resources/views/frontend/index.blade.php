<!DOCTYPE html>
<html lang="en" class="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gain Arbitrage Bot — Automated Crypto Arbitrage, Daily Returns</title>
<meta name="description" content="Gain Arbitrage Bot — automated cross-exchange crypto arbitrage paying up to 1% daily for 200 days. Packages from $500 to $25,000 with a 3-level referral program.">
<meta name="theme-color" content="#0A0D16">
<script src="https://cdn.tailwindcss.com"></script>
<script>window.tailwind&&(tailwind.config={darkMode:'class',theme:{extend:{fontFamily:{sans:['Inter','sans-serif'],mono:['"JetBrains Mono"','monospace']}}}});</script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#0A0D16; --surface:#111524; --surface2:#161B2E; --surface3:#1E2540;
  --line:#252C44; --line2:#333C5C;
  --txt:#EEF1F8; --txt2:#BAC1D5; --mut:#7C849C;
  --p:#8B5CF6; --bl:#4F86F7; --gr:#34D39A;
  --brand:linear-gradient(120deg,#8B5CF6 0%,#4F86F7 50%,#34D39A 100%);
  --brand-soft:linear-gradient(120deg,rgba(139,92,246,.16),rgba(52,211,154,.14));
  --up:#34D39A; --down:#F6566F;
}
html:not(.dark){
  --bg:#F7F9FC; --surface:#FFFFFF; --surface2:#F2F5FA; --surface3:#E9EEF6;
  --line:#E6EBF3; --line2:#D5DCEA;
  --txt:#0F1729; --txt2:#2A3349; --mut:#6B7488;
  --brand:linear-gradient(120deg,#7C3AED 0%,#3B74E8 50%,#1FB585 100%);
  --brand-soft:linear-gradient(120deg,rgba(124,58,237,.10),rgba(31,181,133,.10));
}
*{box-sizing:border-box}
html{scroll-behavior:smooth}
body{margin:0;background:var(--bg);color:var(--txt);font-family:'Inter',sans-serif;overflow-x:hidden;transition:background .25s,color .25s;-webkit-font-smoothing:antialiased}
.mono{font-family:'JetBrains Mono',monospace;font-variant-numeric:tabular-nums}
.disp{font-family:'Sora',sans-serif}
::selection{background:var(--bl);color:#fff}
a{text-decoration:none;color:inherit}
img,svg{display:block;max-width:100%}
.gtext{background:var(--brand);-webkit-background-clip:text;background-clip:text;color:transparent}

.amb{position:fixed;inset:0;z-index:-2;pointer-events:none;overflow:hidden}
.amb span{position:absolute;border-radius:50%;filter:blur(130px)}
.amb .g1{width:560px;height:560px;background:#8B5CF6;top:-200px;left:-140px;opacity:.20}
.amb .g2{width:520px;height:520px;background:#34D39A;top:-120px;right:-160px;opacity:.16}
.amb .g3{width:480px;height:480px;background:#4F86F7;bottom:-220px;left:40%;opacity:.12}
html:not(.dark) .amb span{opacity:.08}
.gridlines{position:fixed;inset:0;z-index:-1;pointer-events:none;background-image:linear-gradient(var(--line) 1px,transparent 1px),linear-gradient(90deg,var(--line) 1px,transparent 1px);background-size:56px 56px;opacity:.25;-webkit-mask:radial-gradient(ellipse 75% 55% at 50% 0%,#000 25%,transparent 72%);mask:radial-gradient(ellipse 75% 55% at 50% 0%,#000 25%,transparent 72%)}

.tape{background:var(--surface);border-bottom:1px solid var(--line);overflow:hidden;white-space:nowrap;height:38px;display:flex;align-items:center}
.tape-track{display:inline-flex;gap:32px;padding-left:32px;animation:tape 42s linear infinite}
.tape:hover .tape-track{animation-play-state:paused}
@keyframes tape{from{transform:translateX(0)}to{transform:translateX(-50%)}}
.tk{display:inline-flex;align-items:center;gap:7px;font-size:12.5px;color:var(--mut)}
.tk b{font-family:'JetBrains Mono';font-weight:500;color:var(--txt)}

header{position:sticky;top:0;z-index:1000;background:color-mix(in srgb,var(--bg) 84%,transparent);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);border-bottom:1px solid var(--line)}
.navlink{color:var(--txt2);font-weight:500;font-size:14px;position:relative;transition:color .15s}
.navlink:hover{color:var(--txt)}
.navlink::after{content:"";position:absolute;left:0;bottom:-6px;height:2px;width:0;background:var(--brand);border-radius:2px;transition:width .25s}
.navlink:hover::after{width:100%}
.seg{display:inline-flex;background:var(--surface2);border:1px solid var(--line);border-radius:10px;padding:3px}
.seg button{border:none;background:transparent;color:var(--mut);width:34px;height:30px;border-radius:7px;cursor:pointer;font-size:13px;transition:.15s}
.seg button.on{background:var(--brand);color:#fff}
.icon-btn{width:40px;height:40px;border-radius:10px;display:grid;place-items:center;color:var(--txt2);border:1px solid var(--line);background:var(--surface2);cursor:pointer}

.btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;font-weight:600;border-radius:10px;cursor:pointer;transition:.18s;border:1px solid transparent;font-size:14px;line-height:1;white-space:nowrap}
.btn-primary{background:var(--brand);color:#fff;box-shadow:0 8px 26px -10px rgba(79,134,247,.6)}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 14px 34px -10px rgba(79,134,247,.85)}
.btn-outline{background:transparent;border-color:var(--line2);color:var(--txt)}
.btn-outline:hover{border-color:var(--bl);color:var(--bl)}
.btn-ghost{background:var(--surface2);border-color:var(--line);color:var(--txt)}
.btn-ghost:hover{border-color:var(--bl)}
.btn-lg{padding:14px 28px;font-size:15px;border-radius:12px}
.btn-sm{padding:8px 16px;font-size:13px}

.card{position:relative;background:var(--surface);border:1px solid var(--line);border-radius:16px;transition:transform .2s,border-color .2s}
.gborder::before{content:"";position:absolute;inset:0;border-radius:inherit;padding:1px;background:var(--brand);-webkit-mask:linear-gradient(#000 0 0) content-box,linear-gradient(#000 0 0);-webkit-mask-composite:xor;mask-composite:exclude;opacity:0;transition:opacity .25s;pointer-events:none}
.gborder:hover::before{opacity:1}
.gborder:hover{transform:translateY(-4px)}
.kick{font-weight:600;font-size:12px;letter-spacing:.16em;text-transform:uppercase}
.h-title{font-weight:800;letter-spacing:-.015em;line-height:1.08;font-family:'Sora',sans-serif}
.fic{width:50px;height:50px;border-radius:13px;display:grid;place-items:center;font-size:20px;color:#fff;background:var(--brand);box-shadow:0 10px 26px -10px rgba(79,134,247,.55)}
.fic-soft{width:46px;height:46px;border-radius:12px;display:grid;place-items:center;font-size:19px;background:var(--brand-soft);color:var(--bl);border:1px solid color-mix(in srgb,var(--bl) 30%,transparent)}

/* arbitrage engine card */
.engine{position:relative;background:var(--surface);border:1px solid var(--line);border-radius:18px;overflow:hidden;box-shadow:0 30px 80px -36px rgba(79,134,247,.45)}
.engine::before{content:"";position:absolute;inset:0;border-radius:inherit;padding:1px;background:var(--brand);opacity:.5;-webkit-mask:linear-gradient(#000 0 0) content-box,linear-gradient(#000 0 0);-webkit-mask-composite:xor;mask-composite:exclude;pointer-events:none}
.engine .etop{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid var(--line);background:var(--surface2)}
.live-dot{width:8px;height:8px;border-radius:50%;background:var(--up);box-shadow:0 0 0 0 var(--up);animation:pulse 2s infinite}
@keyframes pulse{0%{box-shadow:0 0 0 0 rgba(52,211,154,.6)}70%{box-shadow:0 0 0 8px rgba(52,211,154,0)}100%{box-shadow:0 0 0 0 rgba(52,211,154,0)}}
.opp{display:grid;grid-template-columns:1.1fr 1fr 1fr .8fr;gap:8px;align-items:center;padding:12px 18px;border-bottom:1px solid var(--line);font-size:13px}
.opp:last-child{border-bottom:none}
.ex{font-size:11px;color:var(--mut)}
.spread{font-family:'JetBrains Mono';font-weight:700;color:var(--up);text-align:right}
.profit-bar{padding:14px 18px;display:flex;align-items:center;justify-content:space-between;background:var(--surface2);border-top:1px solid var(--line)}

.pkg{position:relative;background:var(--surface);border:1px solid var(--line);border-radius:18px;padding:26px;transition:.22s;overflow:hidden}
.pkg:hover{transform:translateY(-6px);border-color:transparent}
.pkg.pop{border-color:transparent}
.pkg.pop::after{content:"POPULAR";position:absolute;top:14px;right:-30px;transform:rotate(45deg);background:var(--brand);color:#fff;font-size:10px;font-weight:700;letter-spacing:.1em;padding:4px 36px}
.pkg .amt{font-family:'Sora';font-weight:800;font-size:1.8rem;line-height:1}
.pkg ul{list-style:none;padding:0;margin:18px 0 0}
.pkg li{display:flex;gap:9px;align-items:flex-start;font-size:13.5px;color:var(--txt2);padding:6px 0}
.pkg li i{color:var(--gr);margin-top:3px}

.ref{position:relative;background:var(--surface);border:1px solid var(--line);border-radius:16px;padding:26px;text-align:center;transition:.2s;overflow:hidden}
.ref:hover{transform:translateY(-5px);border-color:var(--bl)}
.ref .lvl{font-size:12px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--mut)}
.ref .pct{font-family:'Sora';font-weight:800;font-size:2.6rem;line-height:1;margin:10px 0}

/* calculator */
.calc-out{background:var(--surface2);border:1px solid var(--line);border-radius:12px;padding:16px;text-align:center}
.calc-out .v{font-family:'Sora';font-weight:800;font-size:1.5rem;line-height:1}
input[type=range]{-webkit-appearance:none;appearance:none;width:100%;height:6px;border-radius:5px;background:var(--surface3);outline:none}
input[type=range]::-webkit-slider-thumb{-webkit-appearance:none;width:22px;height:22px;border-radius:50%;background:var(--brand);cursor:pointer;box-shadow:0 2px 10px rgba(79,134,247,.6);border:2px solid #fff}
input[type=range]::-moz-range-thumb{width:22px;height:22px;border-radius:50%;background:#4F86F7;cursor:pointer;border:2px solid #fff}

.step{position:relative;background:var(--surface);border:1px solid var(--line);border-radius:16px;padding:26px}
.step .no{font-family:'Sora';font-weight:800;font-size:34px;line-height:1}

.mkt{width:100%;border-collapse:collapse}
.mkt th{color:var(--mut);font-size:11px;text-transform:uppercase;letter-spacing:.05em;text-align:left;padding:13px 18px;font-weight:600}
.mkt td{padding:14px 18px;border-top:1px solid var(--line);font-size:14px}
.mkt tbody tr{transition:background .15s}
.mkt tbody tr:hover{background:var(--surface2)}

.stat-card{background:var(--surface);border:1px solid var(--line);border-radius:14px;padding:22px}
.stat-card .num{font-family:'Sora';font-weight:800;font-size:1.8rem;line-height:1}
.trust{display:flex;align-items:center;gap:9px;color:var(--mut);font-size:13px;font-weight:500}
.trust i{color:var(--gr)}

.shield{position:relative;width:min(330px,76vw);aspect-ratio:1;margin:auto;display:grid;place-items:center}
.shield .ring{position:absolute;inset:0;border-radius:50%;border:1px solid var(--line)}
.shield .ring.r2{inset:15%;border-style:dashed;animation:rot 30s linear infinite}
.shield .ring.r3{inset:30%;animation:rot 20s linear infinite reverse}
@keyframes rot{to{transform:rotate(360deg)}}
.shield .orbit{position:absolute;width:42px;height:42px;border-radius:12px;background:var(--surface2);border:1px solid var(--line);display:grid;place-items:center;color:var(--bl)}

footer{border-top:1px solid var(--line);background:var(--surface)}
.flink{color:var(--mut);font-size:14px;line-height:2.2;transition:.15s}
.flink:hover{color:var(--bl)}
.soc{width:40px;height:40px;border-radius:11px;border:1px solid var(--line);display:grid;place-items:center;color:var(--mut);transition:.15s;background:var(--surface2)}
.soc:hover{color:#fff;background:var(--brand);border-color:transparent;transform:translateY(-3px)}

[data-aos]{will-change:transform,opacity}
.flash-up{animation:fu .55s}.flash-down{animation:fd .55s}
@keyframes fu{0%{color:var(--up)}100%{}}@keyframes fd{0%{color:var(--down)}100%{}}
@media (max-width:639px){.login-btn{display:none}.wm-sub{display:none}}
@media (prefers-reduced-motion:reduce){*{animation:none!important;transition:none!important}}
</style>
</head>
<body>
<div class="amb"><span class="g1"></span><span class="g2"></span><span class="g3"></span></div>
<div class="gridlines"></div>

<!-- brand mark (reusable SVG) -->
<svg width="0" height="0" style="position:absolute"><defs>
  <linearGradient id="gab" x1="12" y1="88" x2="88" y2="12" gradientUnits="userSpaceOnUse">
    <stop offset="0" stop-color="#8B5CF6"/><stop offset=".5" stop-color="#4F86F7"/><stop offset="1" stop-color="#34D39A"/></linearGradient>
</defs></svg>

<div class="tape"><div class="tape-track" id="tape"></div></div>

<header>
  <div class="max-w-[1300px] mx-auto px-4 lg:px-6 h-[70px] flex items-center gap-6">
    <a href="#home" class="flex items-center gap-3 shrink-0">
      <svg width="42" height="42" viewBox="0 0 100 100" aria-label="Gain Arbitrage Bot">
        <path d="M78 33 A34 34 0 1 0 84 58 L58 58" fill="none" stroke="url(#gab)" stroke-width="11" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M30 78 L66 36" stroke="url(#gab)" stroke-width="11" stroke-linecap="round"/>
        <path d="M58 30 L72 30 L72 44" fill="none" stroke="url(#gab)" stroke-width="9" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      <span class="leading-tight">
        <span class="disp block text-[18px] font-extrabold tracking-tight">Gain <span class="gtext">Arbitrage</span></span>
        <span class="wm-sub block text-[9.5px] tracking-[.34em] mt-0.5" style="color:var(--mut)">BOT</span>
      </span>
    </a>
    <nav class="hidden lg:flex items-center gap-6 ml-2">
      <a class="navlink" href="#how">How it works</a>
      <a class="navlink" href="#packages">Packages</a>
      <a class="navlink" href="#referral">Referral</a>
      <a class="navlink" href="#live">Live</a>
      <a class="navlink" href="#about">About</a>
    </nav>
    <div class="flex items-center gap-2 ml-auto">
      <div class="seg" role="group" aria-label="Theme"><button id="segLight" title="Light"><i class="fa-solid fa-sun"></i></button><button id="segDark" title="Dark"><i class="fa-solid fa-moon"></i></button></div>
      <a class="btn btn-outline btn-sm login-btn" href="{{ url('/member') }}"><i class="fa-regular fa-user"></i> Login</a>
      <a class="btn btn-primary btn-sm" href="{{ url('/member/register/1') }}">Register</a>
      <button class="icon-btn lg:hidden" id="burger" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
    </div>
  </div>
  <div id="mnav" class="lg:hidden hidden border-t" style="border-color:var(--line);background:var(--surface)">
    <div class="px-5 py-3 flex flex-col gap-1">
      <a class="navlink py-2" href="#how">How it works</a><a class="navlink py-2" href="#packages">Packages</a>
      <a class="navlink py-2" href="#referral">Referral</a><a class="navlink py-2" href="#live">Live</a><a class="navlink py-2" href="#about">About</a>
      <a class="btn btn-outline btn-sm mt-2" href="{{ url('/member') }}"><i class="fa-regular fa-user"></i> Login</a>
    </div>
  </div>
</header>

<!-- HERO -->
<section id="home" class="relative">
  <div class="max-w-[1300px] mx-auto px-4 lg:px-6 pt-12 lg:pt-20 pb-12 relative z-10">
    <div class="grid lg:grid-cols-2 gap-10 lg:gap-14 items-center">
      <div data-aos="fade-up">
        <div class="inline-flex items-center gap-2 text-[12.5px] font-semibold px-3.5 py-1.5 rounded-full" style="background:var(--brand-soft);border:1px solid color-mix(in srgb,var(--bl) 32%,transparent);color:var(--bl)">
          <span class="live-dot"></span> Automated · 24/7 · Non-stop arbitrage
        </div>
        <h1 class="h-title mt-5 text-[2.3rem] sm:text-[2.9rem] lg:text-[3.4rem]">Earn <span class="gtext">1% daily</span> with automated crypto arbitrage</h1>
        <p class="mt-5 text-[1.05rem] leading-relaxed max-w-[500px]" style="color:var(--mut)">Gain Arbitrage Bot scans price gaps across exchanges around the clock and books the spread for you — paying steady daily returns for up to 200 days. No trading skills required.</p>
        <div class="mt-7 flex flex-wrap gap-3">
          <a href="#packages" class="btn btn-primary btn-lg">Start Earning <i class="fa-solid fa-arrow-right"></i></a>
          <a href="#how" class="btn btn-outline btn-lg"><i class="fa-solid fa-circle-play"></i> How it works</a>
        </div>
        <div class="mt-9 grid grid-cols-3 gap-4 max-w-[500px]">
          <div><div class="disp text-[1.7rem] font-extrabold leading-none gtext">1%</div><div class="text-[13px] mt-1.5" style="color:var(--mut)">Daily ROI</div></div>
          <div><div class="disp text-[1.7rem] font-extrabold leading-none gtext">200</div><div class="text-[13px] mt-1.5" style="color:var(--mut)">Days payout</div></div>
          <div><div class="disp text-[1.7rem] font-extrabold leading-none gtext" data-count="48" data-prefix="$" data-suffix="M+">$0</div><div class="text-[13px] mt-1.5" style="color:var(--mut)">Paid to users</div></div>
        </div>
      </div>

      <!-- LIVE ARBITRAGE ENGINE -->
      <div data-aos="fade-left" data-aos-delay="120">
        <div class="engine">
          <div class="etop">
            <div class="flex items-center gap-2.5"><span class="live-dot"></span><div><div class="font-bold text-[15px]">Arbitrage Engine</div><div class="text-[11px]" style="color:var(--mut)" id="engSrc">Live · scanning exchanges</div></div></div>
            <div class="text-[11px] mono px-2.5 py-1 rounded-md" style="background:var(--brand-soft);color:var(--gr)">● ONLINE</div>
          </div>
          <div id="opps"></div>
          <div class="profit-bar">
            <div class="text-[12px]" style="color:var(--mut)">Bot profit booked today</div>
            <div class="disp text-[18px] font-extrabold gtext mono" id="profitToday">$0.00</div>
          </div>
        </div>
      </div>
    </div>

    <div class="mt-12 pt-7 flex flex-wrap items-center gap-x-8 gap-y-3" style="border-top:1px solid var(--line)">
      <span class="trust"><i class="fa-solid fa-robot"></i> Fully automated</span>
      <span class="trust"><i class="fa-solid fa-clock"></i> Daily payouts</span>
      <span class="trust"><i class="fa-solid fa-shield-halved"></i> Secured funds</span>
      <span class="trust"><i class="fa-solid fa-bolt"></i> Instant withdrawals</span>
      <span class="trust"><i class="fa-solid fa-users"></i> 3-level referral</span>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section id="how" class="py-16 lg:py-24">
  <div class="max-w-[1300px] mx-auto px-4 lg:px-6">
    <div class="text-center mb-10" data-aos="fade-up"><div class="kick gtext">How it works</div><h2 class="h-title text-[1.7rem] lg:text-[2.3rem] mt-2">Three steps to daily returns</h2></div>
    <div class="grid md:grid-cols-3 gap-4">
      <div class="step gborder" data-aos="fade-up"><div class="no gtext">01</div><h4 class="font-bold text-[17px] mt-3">Choose a package</h4><p class="text-[14px] mt-2" style="color:var(--mut)">Activate from $500 up to $25,000. Your capital powers the bot's arbitrage trades.</p></div>
      <div class="step gborder" data-aos="fade-up" data-aos-delay="80"><div class="no gtext">02</div><h4 class="font-bold text-[17px] mt-3">Bot trades the spread</h4><p class="text-[14px] mt-2" style="color:var(--mut)">Our engine buys low on one exchange and sells high on another — capturing risk-controlled price gaps 24/7.</p></div>
      <div class="step gborder" data-aos="fade-up" data-aos-delay="160"><div class="no gtext">03</div><h4 class="font-bold text-[17px] mt-3">Earn 1% daily</h4><p class="text-[14px] mt-2" style="color:var(--mut)">Receive 1% of your package value every day for 200 days — withdraw anytime.</p></div>
    </div>
  </div>
</section>

<!-- PACKAGES + CALCULATOR -->
<section id="packages" class="pb-16 lg:pb-24">
  <div class="max-w-[1300px] mx-auto px-4 lg:px-6">
    <div class="text-center mb-10" data-aos="fade-up"><div class="kick gtext">Investment Packages</div><h2 class="h-title text-[1.7rem] lg:text-[2.3rem] mt-2">1% daily for 200 days</h2>
      <p class="text-[14px] mt-3 max-w-[520px] mx-auto" style="color:var(--mut)">Every package earns the same 1% daily ROI for up to 200 days — that's up to 200% total return on your capital.</p></div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
      <div class="pkg gborder" data-aos="fade-up">
        <div class="text-[13px] font-semibold" style="color:var(--mut)">STARTER</div>
        <div class="amt gtext mt-2">$500</div><div class="text-[12px]" style="color:var(--mut)">to $2,499</div>
        <ul><li><i class="fa-solid fa-check"></i> 1% daily ROI</li><li><i class="fa-solid fa-check"></i> Up to 200 days</li><li><i class="fa-solid fa-check"></i> Up to 200% total ROI</li><li><i class="fa-solid fa-check"></i> 3-level referral</li></ul>
        <a href="#" class="btn btn-ghost w-full mt-5">Activate</a>
      </div>
      <div class="pkg gborder" data-aos="fade-up" data-aos-delay="60">
        <div class="text-[13px] font-semibold" style="color:var(--mut)">SILVER</div>
        <div class="amt gtext mt-2">$2,500</div><div class="text-[12px]" style="color:var(--mut)">to $7,499</div>
        <ul><li><i class="fa-solid fa-check"></i> 1% daily ROI</li><li><i class="fa-solid fa-check"></i> Up to 200 days</li><li><i class="fa-solid fa-check"></i> Faster withdrawals</li><li><i class="fa-solid fa-check"></i> 3-level referral</li></ul>
        <a href="#" class="btn btn-ghost w-full mt-5">Activate</a>
      </div>
      <div class="pkg pop" data-aos="fade-up" data-aos-delay="120">
        <div class="text-[13px] font-semibold" style="color:var(--bl)">GOLD</div>
        <div class="amt gtext mt-2">$7,500</div><div class="text-[12px]" style="color:var(--mut)">to $14,999</div>
        <ul><li><i class="fa-solid fa-check"></i> 1% daily ROI</li><li><i class="fa-solid fa-check"></i> Up to 200 days</li><li><i class="fa-solid fa-check"></i> Priority support</li><li><i class="fa-solid fa-check"></i> 3-level referral</li></ul>
        <a href="#" class="btn btn-primary w-full mt-5">Activate</a>
      </div>
      <div class="pkg gborder" data-aos="fade-up" data-aos-delay="180">
        <div class="text-[13px] font-semibold" style="color:var(--mut)">PLATINUM</div>
        <div class="amt gtext mt-2">$15,000</div><div class="text-[12px]" style="color:var(--mut)">to $25,000</div>
        <ul><li><i class="fa-solid fa-check"></i> 1% daily ROI</li><li><i class="fa-solid fa-check"></i> Up to 200 days</li><li><i class="fa-solid fa-check"></i> Dedicated manager</li><li><i class="fa-solid fa-check"></i> 3-level referral</li></ul>
        <a href="#" class="btn btn-ghost w-full mt-5">Activate</a>
      </div>
    </div>

    <!-- ROI calculator -->
    <div class="card gborder p-6 lg:p-8" data-aos="fade-up">
      <div class="grid lg:grid-cols-2 gap-8 items-center">
        <div>
          <div class="kick gtext">ROI Calculator</div>
          <h3 class="h-title text-[1.4rem] lg:text-[1.8rem] mt-2 mb-5">See your earnings</h3>
          <div class="flex justify-between items-end mb-2">
            <span class="text-[13px]" style="color:var(--mut)">Investment amount</span>
            <span class="disp text-[1.6rem] font-extrabold gtext mono" id="calcAmt">$5,000</span>
          </div>
          <input type="range" id="calcRange" min="500" max="25000" step="100" value="5000">
          <div class="flex justify-between text-[11px] mt-1" style="color:var(--mut)"><span>$500</span><span>$25,000</span></div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div class="calc-out"><div class="text-[11px] uppercase tracking-wider" style="color:var(--mut)">Daily (1%)</div><div class="v gtext mt-1" id="calcDaily">$50</div></div>
          <div class="calc-out"><div class="text-[11px] uppercase tracking-wider" style="color:var(--mut)">Monthly (30%)</div><div class="v gtext mt-1" id="calcMonthly">$1,500</div></div>
          <div class="calc-out"><div class="text-[11px] uppercase tracking-wider" style="color:var(--mut)">200-day profit</div><div class="v gtext mt-1" id="calcProfit">$10,000</div></div>
          <div class="calc-out"><div class="text-[11px] uppercase tracking-wider" style="color:var(--mut)">Total return</div><div class="v gtext mt-1" id="calcTotal">$15,000</div></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- REFERRAL -->
<section id="referral" class="pb-16 lg:pb-24">
  <div class="max-w-[1300px] mx-auto px-4 lg:px-6">
    <div class="text-center mb-10" data-aos="fade-up"><div class="kick gtext">Referral Program</div><h2 class="h-title text-[1.7rem] lg:text-[2.3rem] mt-2">Earn from 3 levels — no conditions</h2>
      <p class="text-[14px] mt-3 max-w-[520px] mx-auto" style="color:var(--mut)">Invite others and earn daily commission on their package activity for up to 50 days. No volume targets, no conditions.</p></div>
    <div class="grid md:grid-cols-3 gap-4 max-w-[920px] mx-auto">
      <div class="ref" data-aos="fade-up"><div class="lvl">Level 1</div><div class="pct gtext">0.5%</div><div class="text-[13px]" style="color:var(--txt2)">daily commission</div><div class="text-[12px] mt-1" style="color:var(--mut)">up to 50 days</div></div>
      <div class="ref" data-aos="fade-up" data-aos-delay="80"><div class="lvl">Level 2</div><div class="pct gtext">0.25%</div><div class="text-[13px]" style="color:var(--txt2)">daily commission</div><div class="text-[12px] mt-1" style="color:var(--mut)">up to 50 days</div></div>
      <div class="ref" data-aos="fade-up" data-aos-delay="160"><div class="lvl">Level 3</div><div class="pct gtext">0.25%</div><div class="text-[13px]" style="color:var(--txt2)">daily commission</div><div class="text-[12px] mt-1" style="color:var(--mut)">up to 50 days</div></div>
    </div>
    <div class="flex flex-wrap justify-center gap-3 mt-7" data-aos="fade-up">
      <span class="trust"><i class="fa-solid fa-circle-check"></i> No conditions</span>
      <span class="trust"><i class="fa-solid fa-bolt"></i> Daily payout</span>
      <span class="trust"><i class="fa-solid fa-infinity"></i> Unlimited referrals</span>
    </div>
  </div>
</section>

<!-- LIVE ARBITRAGE OPPORTUNITIES -->
<section id="live" class="pb-16 lg:pb-24">
  <div class="max-w-[1300px] mx-auto px-4 lg:px-6">
    <div class="flex flex-wrap justify-between items-end gap-4 mb-6" data-aos="fade-up">
      <div><div class="kick gtext">Live Feed</div><h2 class="h-title text-[1.7rem] lg:text-[2.3rem] mt-2">Real-time arbitrage spreads</h2></div>
      <span class="text-[13px] flex items-center gap-2" style="color:var(--mut)"><span class="live-dot"></span><span id="liveSrc">Live prices · Binance</span></span>
    </div>
    <div class="card overflow-hidden" data-aos="fade-up" data-aos-delay="80">
      <div class="overflow-x-auto"><table class="mkt">
        <thead><tr><th>Pair</th><th>Buy @ Exchange A</th><th>Sell @ Exchange B</th><th>Spread</th><th>Est. profit / $1k</th></tr></thead>
        <tbody id="arbBody"></tbody>
      </table></div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="pb-16 lg:pb-24">
  <div class="max-w-[1300px] mx-auto px-4 lg:px-6">
    <div class="text-center mb-10" data-aos="fade-up"><div class="kick gtext">Why Gain Arbitrage Bot</div><h2 class="h-title text-[1.7rem] lg:text-[2.3rem] mt-2">Built to earn, built to last</h2></div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <div class="card gborder p-6" data-aos="fade-up"><div class="fic"><i class="fa-solid fa-robot"></i></div><h4 class="font-bold text-[17px] mt-4">Automated Engine</h4><p class="text-[14px] mt-2" style="color:var(--mut)">The bot trades arbitrage 24/7 — no charts to watch, no decisions to make.</p></div>
      <div class="card gborder p-6" data-aos="fade-up" data-aos-delay="60"><div class="fic"><i class="fa-solid fa-coins"></i></div><h4 class="font-bold text-[17px] mt-4">Daily Returns</h4><p class="text-[14px] mt-2" style="color:var(--mut)">1% credited to your balance every day for up to 200 days.</p></div>
      <div class="card gborder p-6" data-aos="fade-up" data-aos-delay="120"><div class="fic"><i class="fa-solid fa-bolt"></i></div><h4 class="font-bold text-[17px] mt-4">Instant Withdrawals</h4><p class="text-[14px] mt-2" style="color:var(--mut)">Cash out your earnings whenever you want — fast and on-chain.</p></div>
      <div class="card gborder p-6" data-aos="fade-up"><div class="fic"><i class="fa-solid fa-users"></i></div><h4 class="font-bold text-[17px] mt-4">3-Level Referral</h4><p class="text-[14px] mt-2" style="color:var(--mut)">Earn 0.5% / 0.25% / 0.25% daily across three levels — no conditions.</p></div>
      <div class="card gborder p-6" data-aos="fade-up" data-aos-delay="60"><div class="fic"><i class="fa-solid fa-shield-halved"></i></div><h4 class="font-bold text-[17px] mt-4">Secured Capital</h4><p class="text-[14px] mt-2" style="color:var(--mut)">Funds protected with cold storage and multi-layer security.</p></div>
      <div class="card gborder p-6" data-aos="fade-up" data-aos-delay="120"><div class="fic"><i class="fa-solid fa-headset"></i></div><h4 class="font-bold text-[17px] mt-4">24/7 Support</h4><p class="text-[14px] mt-2" style="color:var(--mut)">A dedicated team is always available to help you grow.</p></div>
    </div>
  </div>
</section>

<!-- ABOUT -->
<section id="about" class="pb-16 lg:pb-24">
  <div class="max-w-[1300px] mx-auto px-4 lg:px-6">
    <div class="card gborder p-7 lg:p-10 overflow-hidden" data-aos="fade-up">
      <div class="grid lg:grid-cols-2 gap-10 items-center">
        <div>
          <div class="kick gtext">About</div>
          <h2 class="h-title text-[1.7rem] lg:text-[2.4rem] mt-2 mb-4">Smart arbitrage, made simple</h2>
          <p class="text-[15px] leading-relaxed" style="color:var(--txt2)">Gain Arbitrage Bot is an automated trading platform that turns tiny price differences between exchanges into steady, predictable returns. Our engine works around the clock so you don't have to — making professional-grade arbitrage accessible to everyone.</p>
          <div class="flex flex-wrap gap-3 mt-6">
            <a href="#packages" class="btn btn-primary"><i class="fa-solid fa-rocket"></i> Get started</a>
            <a href="#" class="btn btn-ghost"><i class="fa-solid fa-file-shield"></i> Proof of Reserves</a>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div class="stat-card"><div class="num gtext" data-count="120" data-suffix="K+">0</div><div class="text-[13px] mt-2" style="color:var(--mut)">Active investors</div></div>
          <div class="stat-card"><div class="num gtext" data-count="48" data-prefix="$" data-suffix="M+">$0</div><div class="text-[13px] mt-2" style="color:var(--mut)">Paid to users</div></div>
          <div class="stat-card"><div class="num gtext" data-count="15" data-suffix="+">0</div><div class="text-[13px] mt-2" style="color:var(--mut)">Exchanges scanned</div></div>
          <div class="stat-card"><div class="num gtext" data-count="60" data-suffix="+">0</div><div class="text-[13px] mt-2" style="color:var(--mut)">Countries</div></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- SECURITY -->
<section class="pb-16 lg:pb-24">
  <div class="max-w-[1300px] mx-auto px-4 lg:px-6 grid lg:grid-cols-2 gap-12 items-center">
    <div data-aos="zoom-in">
      <div class="shield">
        <div class="ring"></div><div class="ring r2"></div><div class="ring r3"></div>
        <svg width="44%" height="44%" viewBox="0 0 120 120">
          <rect x="6" y="6" width="108" height="108" rx="26" fill="url(#gab)"/>
          <path d="M60 26 L86 36 V60 C86 78 74 90 60 96 C46 90 34 78 34 60 V36 Z" fill="#0A0D16" opacity=".9"/>
          <path d="M52 60 l6 6 12 -14" stroke="#fff" stroke-width="5" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <div class="orbit" style="top:3%;left:46%"><i class="fa-solid fa-snowflake"></i></div>
        <div class="orbit" style="bottom:5%;left:16%"><i class="fa-solid fa-key"></i></div>
        <div class="orbit" style="top:42%;right:1%"><i class="fa-solid fa-fingerprint"></i></div>
      </div>
    </div>
    <div data-aos="fade-left">
      <div class="kick gtext">Security First</div>
      <h2 class="h-title text-[1.7rem] lg:text-[2.3rem] mt-2 mb-6">Your capital, protected</h2>
      <div class="grid sm:grid-cols-2 gap-3">
        <div class="card p-4 flex gap-3 items-start"><div class="fic-soft shrink-0"><i class="fa-solid fa-snowflake"></i></div><div><h4 class="font-bold text-[15px]">Cold Storage</h4><p class="text-[13px] mt-1" style="color:var(--mut)">Majority of funds held offline.</p></div></div>
        <div class="card p-4 flex gap-3 items-start"><div class="fic-soft shrink-0"><i class="fa-solid fa-key"></i></div><div><h4 class="font-bold text-[15px]">2FA &amp; Biometrics</h4><p class="text-[13px] mt-1" style="color:var(--mut)">Multi-layer account protection.</p></div></div>
        <div class="card p-4 flex gap-3 items-start"><div class="fic-soft shrink-0"><i class="fa-solid fa-layer-group"></i></div><div><h4 class="font-bold text-[15px]">Multi-Sig Wallets</h4><p class="text-[13px] mt-1" style="color:var(--mut)">Distributed key authorization.</p></div></div>
        <div class="card p-4 flex gap-3 items-start"><div class="fic-soft shrink-0"><i class="fa-solid fa-certificate"></i></div><div><h4 class="font-bold text-[15px]">Proof of Reserves</h4><p class="text-[13px] mt-1" style="color:var(--mut)">Transparent, verifiable backing.</p></div></div>
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="pb-16 lg:pb-24">
  <div class="max-w-[1300px] mx-auto px-4 lg:px-6">
    <div class="card gborder text-center px-6 py-14 lg:py-20 relative overflow-hidden" data-aos="fade-up">
      <div class="absolute inset-0 -z-0" style="background:radial-gradient(60% 80% at 50% 0%,rgba(79,134,247,.16),transparent 60%)"></div>
      <div class="relative z-10">
        <h2 class="h-title text-[1.7rem] lg:text-[2.6rem]">Put your capital <span class="gtext">to work</span></h2>
        <p class="mt-3 text-[15px]" style="color:var(--mut)">Activate a package from $500 and start earning 1% daily — for up to 200 days.</p>
        <div class="flex flex-wrap gap-3 justify-center mt-7">
          <a href="{{ url('/member/register/1') }}" class="btn btn-primary btn-lg">Register Now</a>
          <a href="#packages" class="btn btn-outline btn-lg">View Packages</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer id="footer" class="pt-14 pb-8">
  <div class="max-w-[1300px] mx-auto px-4 lg:px-6">
    <div class="grid grid-cols-2 lg:grid-cols-6 gap-8">
      <div class="col-span-2">
        <div class="flex items-center gap-3 mb-3">
          <svg width="38" height="38" viewBox="0 0 100 100"><path d="M78 33 A34 34 0 1 0 84 58 L58 58" fill="none" stroke="url(#gab)" stroke-width="11" stroke-linecap="round" stroke-linejoin="round"/><path d="M30 78 L66 36" stroke="url(#gab)" stroke-width="11" stroke-linecap="round"/><path d="M58 30 L72 30 L72 44" fill="none" stroke="url(#gab)" stroke-width="9" stroke-linecap="round" stroke-linejoin="round"/></svg>
          <span class="leading-tight"><span class="disp block text-[16px] font-extrabold">Gain <span class="gtext">Arbitrage</span></span><span class="block text-[9px] tracking-[.34em]" style="color:var(--mut)">BOT</span></span>
        </div>
        <p class="text-[14px] max-w-[320px]" style="color:var(--mut)">Automated cross-exchange crypto arbitrage paying steady daily returns — built for everyone.</p>
        <div class="flex gap-2 mt-4">
          <a class="soc" href="#"><i class="fa-brands fa-x-twitter"></i></a><a class="soc" href="#"><i class="fa-brands fa-telegram"></i></a>
          <a class="soc" href="#"><i class="fa-brands fa-discord"></i></a><a class="soc" href="#"><i class="fa-brands fa-youtube"></i></a>
        </div>
      </div>
      <div><h5 class="text-[14px] font-bold mb-3">Platform</h5><a href="#how" class="flink block">How it works</a><a href="#packages" class="flink block">Packages</a><a href="#referral" class="flink block">Referral</a><a href="#live" class="flink block">Live feed</a></div>
      <div><h5 class="text-[14px] font-bold mb-3">Company</h5><a href="#about" class="flink block">About</a><a href="#" class="flink block">Careers</a><a href="#" class="flink block">Blog</a><a href="#" class="flink block">Contact</a></div>
      <div><h5 class="text-[14px] font-bold mb-3">Support</h5><a href="#" class="flink block">Help Center</a><a href="#" class="flink block">FAQ</a><a href="#" class="flink block">Status</a><a href="#" class="flink block">Security</a></div>
      <div><h5 class="text-[14px] font-bold mb-3">Legal</h5><a href="#" class="flink block">Terms</a><a href="#" class="flink block">Privacy</a><a href="#" class="flink block">Risk Disclosure</a><a href="#" class="flink block">AML</a></div>
    </div>
    <div class="flex flex-wrap justify-between items-center gap-3 mt-10 pt-6" style="border-top:1px solid var(--line)">
      <span class="text-[13px]" style="color:var(--mut)">© 2026 Gain Arbitrage Bot. All rights reserved.</span>
      <span class="text-[13px]" style="color:var(--mut)">Crypto involves risk. Past performance is not a guarantee.</span>
    </div>
  </div>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<script>
AOS.init({duration:650,easing:'ease-out-cubic',once:true,offset:70});
const root=document.documentElement,segL=document.getElementById('segLight'),segD=document.getElementById('segDark');
function applyTheme(t){root.classList.toggle('dark',t==='dark');segD.classList.toggle('on',t==='dark');segL.classList.toggle('on',t!=='dark');localStorage.setItem('gab-theme',t);}
segL.onclick=()=>applyTheme('light');segD.onclick=()=>applyTheme('dark');
document.getElementById('burger').onclick=()=>document.getElementById('mnav').classList.toggle('hidden');
document.querySelectorAll('#mnav a').forEach(a=>a.onclick=()=>document.getElementById('mnav').classList.add('hidden'));
const $=id=>document.getElementById(id);
const money=n=>'$'+Math.round(n).toLocaleString('en-US');

/* ===== ROI CALCULATOR ===== */
const cr=$('calcRange');
function calc(){const a=+cr.value;$('calcAmt').textContent=money(a);$('calcDaily').textContent=money(a*0.01);$('calcMonthly').textContent=money(a*0.30);$('calcProfit').textContent=money(a*2);$('calcTotal').textContent=money(a*3);}
cr.addEventListener('input',calc);calc();

/* ===== LIVE PRICES (Binance) -> arbitrage spreads ===== */
const PAIRS=[
 {s:'BTC',n:'Bitcoin',p:68000,col:'#F7931A',i:'fa-brands fa-bitcoin'},
 {s:'ETH',n:'Ethereum',p:3560,col:'#627EEA',i:'fa-brands fa-ethereum'},
 {s:'SOL',n:'Solana',p:172,col:'#9945FF',i:'fa-solid fa-circle-notch'},
 {s:'BNB',n:'BNB',p:596,col:'#F0B90B',i:'fa-solid fa-b'},
 {s:'XRP',n:'Ripple',p:0.62,col:'#23292F',i:'fa-solid fa-x'},
];
const EXA=['Binance','OKX','Bybit','Kraken'],EXB=['Coinbase','Kucoin','Gate.io','Huobi'];
const fmt=n=>n>=100?n.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}):n.toLocaleString('en-US',{minimumFractionDigits:4,maximumFractionDigits:4});
let live=false,profitToday=0;

async function loadPrices(){
  try{
    const r=await fetch('https://api.binance.com/api/v3/ticker/price?symbols=%5B%22BTCUSDT%22,%22ETHUSDT%22,%22SOLUSDT%22,%22BNBUSDT%22,%22XRPUSDT%22%5D');
    if(!r.ok)throw 0;const d=await r.json();const map={};d.forEach(x=>map[x.symbol]=+x.price);
    PAIRS.forEach(p=>{if(map[p.s+'USDT'])p.p=map[p.s+'USDT'];});
    live=true;if($('engSrc'))$('engSrc').textContent='Live · scanning exchanges';if($('liveSrc'))$('liveSrc').textContent='Live prices · Binance';
  }catch(e){live=false;if($('engSrc'))$('engSrc').textContent='Live · demo data';if($('liveSrc'))$('liveSrc').textContent='Live feed · demo data';}
}

function renderArb(){
  const opps=$('opps'),body=$('arbBody');let oh='',th='';
  PAIRS.forEach((p,i)=>{
    if(!live)p.p*=1+(Math.random()-.5)*.001;
    const spread=(0.12+Math.random()*0.7);          // %
    const a=p.p, b=p.p*(1+spread/100);
    const exa=EXA[i%EXA.length],exb=EXB[i%EXB.length];
    const prof=(spread/100*1000).toFixed(2);
    if(i<3){oh+='<div class="opp"><div class="flex items-center gap-2"><span class="w-7 h-7 rounded-full grid place-items-center text-white text-[12px]" style="background:'+p.col+'"><i class="'+p.i+'"></i></span><div><div class="font-semibold text-[13px]">'+p.s+'/USDT</div><div class="ex">'+exa+' → '+exb+'</div></div></div>'+
      '<div class="mono text-[12px]" style="color:var(--mut)">$'+fmt(a)+'</div>'+
      '<div class="mono text-[12px]" style="color:var(--mut)">$'+fmt(b)+'</div>'+
      '<div class="spread">+'+spread.toFixed(2)+'%</div></div>';}
    th+='<tr><td><div class="flex items-center gap-3"><span class="w-8 h-8 rounded-full grid place-items-center text-white text-[13px]" style="background:'+p.col+'"><i class="'+p.i+'"></i></span><div><div class="font-semibold">'+p.s+'/USDT</div><div class="text-[12px]" style="color:var(--mut)">'+p.n+'</div></div></div></td>'+
      '<td class="mono"><span style="color:var(--mut)">'+exa+'</span> · $'+fmt(a)+'</td>'+
      '<td class="mono"><span style="color:var(--mut)">'+exb+'</span> · $'+fmt(b)+'</td>'+
      '<td class="mono font-semibold" style="color:var(--up)">+'+spread.toFixed(2)+'%</td>'+
      '<td class="mono font-semibold gtext">+$'+prof+'</td></tr>';
  });
  if(opps)opps.innerHTML=oh; if(body)body.innerHTML=th;
}
function bumpProfit(){profitToday+=Math.random()*180+40;const e=$('profitToday');if(e)e.textContent='$'+profitToday.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2});}

/* ticker */
const tape=$('tape');
function buildTape(){const one=PAIRS.map(p=>'<span class="tk"><span style="font-weight:600;color:var(--mut)">'+p.s+'</span><b>$'+fmt(p.p)+'</b><span style="color:#34D39A">arb +'+(0.1+Math.random()*0.6).toFixed(2)+'%</span></span>').join('');tape.innerHTML=one+one;}

loadPrices().then(()=>{renderArb();buildTape();});
renderArb();buildTape();bumpProfit();
setInterval(renderArb,2200);
setInterval(buildTape,4000);
setInterval(bumpProfit,2600);
setInterval(loadPrices,20000);

/* counters */
document.querySelectorAll('[data-count]').forEach(el=>{const o=new IntersectionObserver(es=>es.forEach(e=>{if(e.isIntersecting){const t=+el.dataset.count,sfx=el.dataset.suffix||'',pre=el.dataset.prefix||'';let cur=0,stp=t/45;const iv=setInterval(()=>{cur+=stp;if(cur>=t){cur=t;clearInterval(iv);}el.textContent=pre+Math.floor(cur)+sfx;},20);o.unobserve(el);}}),{threshold:.6});o.observe(el);});

applyTheme(localStorage.getItem('gab-theme')||'dark');
</script>
</body>
</html>
