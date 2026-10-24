<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Demo — Smart Arbitrage. Max Returns.</title>
  <meta name="description"
    content="Demo leverages advanced arbitrage technology to identify price differences across global markets, ensuring consistent returns with minimal risk. Arbitrage. Advantage. Automated.">
  <meta name="theme-color" content="#05070A">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link
    href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;700;800;900&family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap"
    rel="stylesheet">
  <style>
    :root {
      --void: #05070A;
      --panel: #0A100C;
      --panel2: #0D160F;
      --panel3: #12211628;
      --line: rgba(43, 232, 107, .16);
      --line2: rgba(43, 232, 107, .32);
      --lime: #9BE23E;
      --green: #2BE86B;
      --emerald: #12B76A;
      --deep: #0E7A3A;
      --txt: #E9F7ED;
      --txt2: #A9C4B2;
      --mut: #6E8A78;
      --brand: linear-gradient(120deg, #B6F24A 0%, #2BE86B 48%, #0E7A3A 100%);
      --glow: rgba(43, 232, 107, .55);
      --glow-soft: rgba(43, 232, 107, .14);
    }

    * {
      box-sizing: border-box
    }

    html {
      scroll-behavior: smooth
    }

    body {
      margin: 0;
      background: var(--void);
      color: var(--txt);
      font-family: 'Inter', sans-serif;
      overflow-x: hidden;
      -webkit-font-smoothing: antialiased
    }

    .mono {
      font-family: 'JetBrains Mono', monospace;
      font-variant-numeric: tabular-nums
    }

    .disp {
      font-family: 'Orbitron', sans-serif;
      letter-spacing: .02em
    }

    .head {
      font-family: 'Space Grotesk', sans-serif
    }

    a {
      text-decoration: none;
      color: inherit
    }

    img,
    canvas {
      display: block;
      max-width: 100%
    }

    ::selection {
      background: var(--green);
      color: #04120A
    }

    .gtext {
      background: var(--brand);
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent
    }

    /* ambient background */
    .bgfx {
      position: fixed;
      inset: 0;
      z-index: -3;
      pointer-events: none;
      background:
        radial-gradient(60% 50% at 15% 0%, rgba(43, 232, 107, .10), transparent 60%),
        radial-gradient(50% 45% at 100% 10%, rgba(155, 226, 62, .08), transparent 60%),
        radial-gradient(60% 60% at 50% 120%, rgba(14, 122, 58, .12), transparent 60%),
        var(--void)
    }

    .bg-grid {
      position: fixed;
      inset: 0;
      z-index: -2;
      pointer-events: none;
      background-image: linear-gradient(rgba(43, 232, 107, .05) 1px, transparent 1px), linear-gradient(90deg, rgba(43, 232, 107, .05) 1px, transparent 1px);
      background-size: 54px 54px;
      -webkit-mask: radial-gradient(ellipse 80% 60% at 50% 0%, #000 20%, transparent 75%);
      mask: radial-gradient(ellipse 80% 60% at 50% 0%, #000 20%, transparent 75%)
    }

    .scan {
      position: fixed;
      inset: 0;
      z-index: -1;
      pointer-events: none;
      opacity: .5;
      background: repeating-linear-gradient(0deg, transparent 0 3px, rgba(0, 0, 0, .18) 3px 4px)
    }

    /* ticker */
    .tick {
      border-block: 1px solid var(--line);
      background: rgba(8, 14, 10, .85);
      overflow: hidden;
      height: 40px;
      display: flex;
      align-items: center
    }

    .tick-track {
      display: inline-flex;
      gap: 34px;
      white-space: nowrap;
      padding-left: 34px;
      animation: tk 40s linear infinite
    }

    .tick:hover .tick-track {
      animation-play-state: paused
    }

    @keyframes tk {
      to {
        transform: translateX(-50%)
      }
    }

    .tk {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      font-size: 12.5px;
      color: var(--mut);
      font-family: 'JetBrains Mono'
    }

    .tk b {
      color: var(--txt)
    }

    .tk .up {
      color: var(--green)
    }

    /* nav */
    header {
      position: sticky;
      top: 0;
      z-index: 100;
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
      background: rgba(5, 7, 10, .7);
      border-bottom: 1px solid var(--line)
    }

    .logo {
      height: 44px;
      filter: drop-shadow(0 0 14px rgba(43, 232, 107, .35))
    }

    .nlink {
      font-size: 13.5px;
      color: var(--txt2);
      font-weight: 500;
      position: relative;
      transition: color .15s
    }

    .nlink:hover {
      color: var(--green)
    }

    .nlink::after {
      content: "";
      position: absolute;
      left: 0;
      bottom: -7px;
      height: 2px;
      width: 0;
      background: var(--brand);
      transition: width .25s;
      box-shadow: 0 0 8px var(--glow)
    }

    .nlink:hover::after {
      width: 100%
    }

    /* buttons */
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 9px;
      font-weight: 600;
      border-radius: 12px;
      cursor: pointer;
      transition: .2s;
      border: 1px solid transparent;
      font-size: 14px;
      line-height: 1;
      white-space: nowrap;
      font-family: 'Space Grotesk'
    }

    .btn-primary {
      background: var(--brand);
      color: #04120A;
      box-shadow: 0 0 0 1px rgba(43, 232, 107, .3), 0 10px 30px -8px var(--glow)
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 0 0 1px rgba(43, 232, 107, .5), 0 16px 40px -8px var(--glow)
    }

    .btn-ghost {
      background: rgba(43, 232, 107, .06);
      border-color: var(--line2);
      color: var(--txt)
    }

    .btn-ghost:hover {
      border-color: var(--green);
      color: var(--green);
      box-shadow: 0 0 24px -6px var(--glow)
    }

    .btn-lg {
      padding: 15px 30px;
      font-size: 15px
    }

    .btn-sm {
      padding: 9px 18px;
      font-size: 13px
    }

    /* section shell */
    .sec {
      position: relative;
      max-width: 1280px;
      margin: 0 auto;
      padding: 88px 20px
    }

    @media(max-width:768px) {
      .sec {
        padding: 60px 18px
      }
    }

    .eyebrow {
      font-family: 'Orbitron';
      font-weight: 700;
      font-size: 12px;
      letter-spacing: .28em;
      text-transform: uppercase;
      color: var(--green);
      text-shadow: 0 0 18px var(--glow)
    }

    .h2 {
      font-family: 'Space Grotesk';
      font-weight: 700;
      font-size: clamp(1.7rem, 3.6vw, 2.7rem);
      line-height: 1.08;
      letter-spacing: -.01em;
      margin: 12px 0 0
    }

    .lead {
      color: var(--txt2);
      font-size: 15px;
      line-height: 1.7;
      max-width: 620px
    }

    /* glass card + neon */
    .glass {
      position: relative;
      background: linear-gradient(180deg, rgba(13, 22, 15, .75), rgba(8, 13, 10, .6));
      border: 1px solid var(--line);
      border-radius: 18px;
      backdrop-filter: blur(8px)
    }

    .neon:hover {
      border-color: var(--line2);
      box-shadow: 0 0 40px -14px var(--glow)
    }

    .gborder {
      position: relative
    }

    .gborder::before {
      content: "";
      position: absolute;
      inset: 0;
      border-radius: inherit;
      padding: 1px;
      background: var(--brand);
      -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
      -webkit-mask-composite: xor;
      mask-composite: exclude;
      opacity: 0;
      transition: opacity .3s;
      pointer-events: none
    }

    .gborder:hover::before {
      opacity: 1
    }

    .tilt {
      transform-style: preserve-3d;
      transition: transform .18s ease-out;
      will-change: transform
    }

    .ic {
      width: 54px;
      height: 54px;
      border-radius: 14px;
      display: grid;
      place-items: center;
      font-size: 22px;
      color: #04120A;
      background: var(--brand);
      box-shadow: 0 8px 26px -8px var(--glow)
    }

    .ic-ghost {
      width: 50px;
      height: 50px;
      border-radius: 14px;
      display: grid;
      place-items: center;
      font-size: 20px;
      color: var(--green);
      background: rgba(43, 232, 107, .08);
      border: 1px solid var(--line2)
    }

    /* hero */
    .hero {
      position: relative;
      min-height: 92vh;
      display: flex;
      align-items: center;
      overflow: hidden
    }

    #bg3d {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      z-index: 0
    }

    .hero-fallback {
      position: absolute;
      inset: 0;
      z-index: 0;
      background: radial-gradient(40% 50% at 72% 45%, rgba(43, 232, 107, .16), transparent 70%)
    }

    .hero-wrap {
      position: relative;
      z-index: 2;
      max-width: 1280px;
      margin: 0 auto;
      padding: 40px 20px;
      width: 100%
    }

    .badge {
      display: inline-flex;
      align-items: center;
      gap: 9px;
      font-family: 'JetBrains Mono';
      font-size: 12.5px;
      padding: 7px 14px;
      border-radius: 100px;
      background: rgba(43, 232, 107, .08);
      border: 1px solid var(--line2);
      color: var(--green)
    }

    .hero h1 {
      font-family: 'Orbitron';
      font-weight: 800;
      font-size: clamp(2.3rem, 6vw, 4.2rem);
      line-height: 1.02;
      margin: 18px 0 0;
      text-shadow: 0 0 40px rgba(43, 232, 107, .25)
    }

    .tagband {
      display: inline-block;
      font-family: 'Space Grotesk';
      font-weight: 700;
      font-size: clamp(1rem, 2vw, 1.25rem);
      letter-spacing: .02em;
      margin-top: 16px;
      padding: 8px 16px;
      border-radius: 10px;
      background: linear-gradient(90deg, rgba(155, 226, 62, .16), rgba(43, 232, 107, .05));
      border-left: 3px solid var(--green)
    }

    .dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: var(--green);
      box-shadow: 0 0 0 0 var(--green);
      animation: pulse 2s infinite
    }

    @keyframes pulse {
      0% {
        box-shadow: 0 0 0 0 rgba(43, 232, 107, .6)
      }

      70% {
        box-shadow: 0 0 0 9px rgba(43, 232, 107, 0)
      }

      100% {
        box-shadow: 0 0 0 0 rgba(43, 232, 107, 0)
      }
    }

    /* floating arbitrage card */
    .arbcard {
      position: relative;
      border-radius: 20px;
      padding: 20px;
      background: linear-gradient(180deg, rgba(12, 22, 15, .92), rgba(6, 11, 8, .92));
      border: 1px solid var(--line2);
      box-shadow: 0 40px 90px -30px rgba(0, 0, 0, .9), 0 0 60px -20px var(--glow);
      animation: float 6s ease-in-out infinite
    }

    @keyframes float {

      0%,
      100% {
        transform: translateY(0)
      }

      50% {
        transform: translateY(-14px)
      }
    }

    .arbrow {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 12px;
      border-radius: 12px;
      background: rgba(43, 232, 107, .05);
      border: 1px solid var(--line);
      margin-bottom: 10px
    }

    .exch {
      width: 34px;
      height: 34px;
      border-radius: 9px;
      display: grid;
      place-items: center;
      font-weight: 700;
      font-size: 13px;
      color: #04120A
    }

    .swap {
      width: 44px;
      height: 44px;
      border-radius: 50%;
      display: grid;
      place-items: center;
      margin: 6px auto;
      background: var(--brand);
      color: #04120A;
      font-size: 18px;
      box-shadow: 0 0 24px -4px var(--glow);
      animation: spin 4s linear infinite
    }

    @keyframes spin {
      to {
        transform: rotate(360deg)
      }
    }

    .profit-tag {
      text-align: center;
      padding: 12px;
      border-radius: 12px;
      background: linear-gradient(90deg, rgba(43, 232, 107, .14), rgba(155, 226, 62, .06));
      border: 1px solid var(--line2)
    }

    /* stats */
    .stat .n {
      font-family: 'Orbitron';
      font-weight: 800;
      font-size: clamp(1.5rem, 3vw, 2rem);
      line-height: 1
    }

    .trust {
      display: inline-flex;
      align-items: center;
      gap: 9px;
      color: var(--txt2);
      font-size: 13px;
      font-weight: 500
    }

    .trust i {
      color: var(--green)
    }

    /* arbitrage flow */
    .flow {
      display: grid;
      grid-template-columns: 1fr auto 1fr auto 1fr;
      align-items: center;
      gap: 10px
    }

    @media(max-width:820px) {
      .flow {
        grid-template-columns: 1fr;
        gap: 16px
      }

      .flow .arrow {
        transform: rotate(90deg)
      }
    }

    .flow-node {
      border-radius: 16px;
      padding: 20px;
      text-align: center
    }

    .flow .arrow {
      position: relative;
      color: var(--green);
      font-size: 20px;
      text-align: center
    }

    .flow .arrow::after {
      content: "";
      position: absolute;
      inset: 0;
      margin: auto;
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: var(--green);
      box-shadow: 0 0 12px var(--glow);
      animation: travel 2s linear infinite
    }

    @keyframes travel {
      0% {
        transform: translateX(-24px);
        opacity: 0
      }

      20% {
        opacity: 1
      }

      80% {
        opacity: 1
      }

      100% {
        transform: translateX(24px);
        opacity: 0
      }
    }

    /* tables */
    .qtable {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0 8px
    }

    .qtable th {
      font-family: 'Orbitron';
      font-size: 11px;
      letter-spacing: .12em;
      text-transform: uppercase;
      color: var(--green);
      text-align: left;
      padding: 0 16px 6px
    }

    .qtable td {
      padding: 0
    }

    .qrow>td>div {
      background: linear-gradient(180deg, rgba(13, 22, 15, .7), rgba(8, 13, 10, .55));
      border-top: 1px solid var(--line);
      border-bottom: 1px solid var(--line);
      padding: 15px 16px;
      height: 100%;
      display: flex;
      align-items: center
    }

    .qrow>td:first-child>div {
      border-left: 1px solid var(--line);
      border-radius: 12px 0 0 12px
    }

    .qrow>td:last-child>div {
      border-right: 1px solid var(--line);
      border-radius: 0 12px 12px 0
    }

    .qrow:hover>td>div {
      background: rgba(43, 232, 107, .06);
      border-color: var(--line2)
    }

    .pct {
      font-family: 'Orbitron';
      font-weight: 800;
      font-size: 1.25rem
    }

    /* rank rows */
    .rankwrap {
      overflow: hidden;
      border-radius: 18px;
      border: 1px solid var(--line)
    }

    .rankrow {
      display: grid;
      grid-template-columns: 72px 1.4fr 1.2fr 1fr;
      align-items: center;
      gap: 12px;
      padding: 14px 18px;
      border-bottom: 1px solid var(--line);
      transition: .18s
    }

    .rankrow:last-child {
      border-bottom: none
    }

    .rankrow:hover {
      background: rgba(43, 232, 107, .06)
    }

    .tier {
      font-family: 'Orbitron';
      font-weight: 800;
      font-size: 13px;
      color: var(--green);
      border: 1px solid var(--line2);
      border-radius: 9px;
      padding: 6px 0;
      text-align: center;
      background: rgba(43, 232, 107, .05)
    }

    .rankname {
      font-family: 'Space Grotesk';
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 10px
    }

    .gem {
      width: 26px;
      height: 26px;
      border-radius: 7px;
      display: grid;
      place-items: center;
      font-size: 13px;
      color: #04120A;
      background: var(--brand)
    }

    .reward {
      font-family: 'Orbitron';
      font-weight: 800;
      color: var(--lime);
      text-align: right
    }

    @media(max-width:680px) {
      .rankrow {
        grid-template-columns: 52px 1fr auto;
        gap: 8px
      }

      .rankrow .mbiz {
        display: none
      }
    }

    /* calculator */
    input[type=range] {
      -webkit-appearance: none;
      appearance: none;
      width: 100%;
      height: 6px;
      border-radius: 6px;
      background: rgba(43, 232, 107, .15);
      outline: none
    }

    input[type=range]::-webkit-slider-thumb {
      -webkit-appearance: none;
      width: 24px;
      height: 24px;
      border-radius: 50%;
      background: var(--brand);
      cursor: pointer;
      box-shadow: 0 0 16px var(--glow);
      border: 2px solid #04120A
    }

    input[type=range]::-moz-range-thumb {
      width: 24px;
      height: 24px;
      border-radius: 50%;
      background: #2BE86B;
      cursor: pointer;
      border: 2px solid #04120A
    }

    .calc-out {
      background: rgba(43, 232, 107, .05);
      border: 1px solid var(--line);
      border-radius: 14px;
      padding: 16px;
      text-align: center
    }

    .calc-out .v {
      font-family: 'Orbitron';
      font-weight: 800;
      font-size: 1.5rem
    }

    /* live spreads */
    .mkt {
      width: 100%;
      border-collapse: collapse
    }

    .mkt th {
      font-family: 'Orbitron';
      font-size: 10.5px;
      letter-spacing: .1em;
      text-transform: uppercase;
      color: var(--green);
      text-align: left;
      padding: 12px 16px
    }

    .mkt td {
      padding: 14px 16px;
      border-top: 1px solid var(--line);
      font-size: 14px
    }

    .mkt tbody tr:hover {
      background: rgba(43, 232, 107, .05)
    }

    /* footer */
    footer {
      border-top: 1px solid var(--line);
      background: linear-gradient(180deg, transparent, rgba(14, 122, 58, .06))
    }

    .flink {
      color: var(--mut);
      font-size: 13.5px;
      line-height: 2.1;
      transition: .15s
    }

    .flink:hover {
      color: var(--green)
    }

    .soc {
      width: 42px;
      height: 42px;
      border-radius: 12px;
      border: 1px solid var(--line2);
      display: grid;
      place-items: center;
      color: var(--txt2);
      transition: .18s;
      background: rgba(43, 232, 107, .05)
    }

    .soc:hover {
      color: #04120A;
      background: var(--brand);
      transform: translateY(-3px);
      box-shadow: 0 0 24px -6px var(--glow)
    }

    [data-aos] {
      will-change: transform, opacity
    }

    @media(prefers-reduced-motion:reduce) {
      * {
        animation: none !important;
        transition: none !important
      }

      .arbcard,
      .swap,
      .tick-track {
        animation: none !important
      }
    }

    .mobmenu {
      display: none
    }

    /* scroll reveal (replaces AOS) */
    html.jsr [data-aos] {
      opacity: 0;
      transform: translateY(22px);
      transition: opacity .7s cubic-bezier(.2, .6, .2, 1), transform .7s cubic-bezier(.2, .6, .2, 1)
    }

    html.jsr [data-aos].in {
      opacity: 1;
      transform: none
    }

    /* self-contained layout utilities */
    .-z-0 {
      z-index: 0
    }

    .absolute {
      position: absolute
    }

    .block {
      display: block
    }

    .flex {
      display: flex
    }

    .flex-col {
      flex-direction: column
    }

    .flex-wrap {
      flex-wrap: wrap
    }

    .font-bold {
      font-weight: 700
    }

    .font-extrabold {
      font-weight: 800
    }

    .font-semibold {
      font-weight: 600
    }

    .gap-1 {
      gap: .25rem
    }

    .gap-10 {
      gap: 2.5rem
    }

    .gap-12 {
      gap: 3rem
    }

    .gap-2 {
      gap: .5rem
    }

    .gap-2\.5 {
      gap: .625rem
    }

    .gap-3 {
      gap: .75rem
    }

    .gap-4 {
      gap: 1rem
    }

    .gap-5 {
      gap: 1.25rem
    }

    .gap-6 {
      gap: 1.5rem
    }

    .gap-x-8 {
      column-gap: 2rem
    }

    .gap-y-3 {
      row-gap: .75rem
    }

    .grid {
      display: grid
    }

    .grid-cols-2 {
      grid-template-columns: repeat(2, minmax(0, 1fr))
    }

    .grid-cols-3 {
      grid-template-columns: repeat(3, minmax(0, 1fr))
    }

    .h-8 {
      height: 2rem
    }

    .h-\[68px\] {
      height: 68px
    }

    .hidden {
      display: none
    }

    .inset-0 {
      top: 0;
      right: 0;
      bottom: 0;
      left: 0
    }

    .items-baseline {
      align-items: baseline
    }

    .items-center {
      align-items: center
    }

    .items-end {
      align-items: flex-end
    }

    .items-start {
      align-items: flex-start
    }

    .justify-between {
      justify-content: space-between
    }

    .justify-center {
      justify-content: center
    }

    .justify-self-center {
      justify-self: center
    }

    .max-w-\[1280px\] {
      max-width: 1280px
    }

    .max-w-\[360px\] {
      max-width: 360px
    }

    .max-w-\[400px\] {
      max-width: 400px
    }

    .max-w-\[440px\] {
      max-width: 440px
    }

    .max-w-\[640px\] {
      max-width: 640px
    }

    .max-w-\[680px\] {
      max-width: 680px
    }

    .max-w-\[860px\] {
      max-width: 860px
    }

    .mb-2 {
      margin-bottom: .5rem
    }

    .mb-3 {
      margin-bottom: .75rem
    }

    .mb-4 {
      margin-bottom: 1rem
    }

    .mb-5 {
      margin-bottom: 1.25rem
    }

    .mb-6 {
      margin-bottom: 1.5rem
    }

    .mb-8 {
      margin-bottom: 2rem
    }

    .ml-2 {
      margin-left: .5rem
    }

    .ml-auto {
      margin-left: auto
    }

    .mr-3 {
      margin-right: .75rem
    }

    .mt-1 {
      margin-top: .25rem
    }

    .mt-1\.5 {
      margin-top: .375rem
    }

    .mt-10 {
      margin-top: 2.5rem
    }

    .mt-12 {
      margin-top: 3rem
    }

    .mt-14 {
      margin-top: 3.5rem
    }

    .mt-2 {
      margin-top: .5rem
    }

    .mt-3 {
      margin-top: .75rem
    }

    .mt-4 {
      margin-top: 1rem
    }

    .mt-5 {
      margin-top: 1.25rem
    }

    .mt-6 {
      margin-top: 1.5rem
    }

    .mt-7 {
      margin-top: 1.75rem
    }

    .mt-8 {
      margin-top: 2rem
    }

    .mt-9 {
      margin-top: 2.25rem
    }

    .mx-auto {
      margin-left: auto;
      margin-right: auto
    }

    .overflow-hidden {
      overflow: hidden
    }

    .overflow-x-auto {
      overflow-x: auto
    }

    .p-5 {
      padding: 1.25rem
    }

    .p-6 {
      padding: 1.5rem
    }

    .p-7 {
      padding: 1.75rem
    }

    .p-8 {
      padding: 2rem
    }

    .pb-4 {
      padding-bottom: 1rem
    }

    .place-items-center {
      place-items: center
    }

    .pt-0 {
      padding-top: 0
    }

    .pt-6 {
      padding-top: 1.5rem
    }

    .px-5 {
      padding-left: 1.25rem;
      padding-right: 1.25rem
    }

    .px-6 {
      padding-left: 1.5rem;
      padding-right: 1.5rem
    }

    .py-14 {
      padding-top: 3.5rem;
      padding-bottom: 3.5rem
    }

    .py-16 {
      padding-top: 4rem;
      padding-bottom: 4rem
    }

    .py-2\.5 {
      padding-top: .625rem;
      padding-bottom: .625rem
    }

    .relative {
      position: relative
    }

    .rounded-2xl {
      border-radius: 1rem
    }

    .rounded-3xl {
      border-radius: 1.5rem
    }

    .rounded-full {
      border-radius: 9999px
    }

    .shrink-0 {
      flex-shrink: 0
    }

    .text-\[1\.2rem\] {
      font-size: 1.2rem
    }

    .text-\[1\.3rem\] {
      font-size: 1.3rem
    }

    .text-\[1\.4rem\] {
      font-size: 1.4rem
    }

    .text-\[1\.5rem\] {
      font-size: 1.5rem
    }

    .text-\[1\.7rem\] {
      font-size: 1.7rem
    }

    .text-\[11\.5px\] {
      font-size: 11.5px
    }

    .text-\[11px\] {
      font-size: 11px
    }

    .text-\[12\.5px\] {
      font-size: 12.5px
    }

    .text-\[12px\] {
      font-size: 12px
    }

    .text-\[13\.5px\] {
      font-size: 13.5px
    }

    .text-\[13px\] {
      font-size: 13px
    }

    .text-\[14px\] {
      font-size: 14px
    }

    .text-\[15px\] {
      font-size: 15px
    }

    .text-\[17px\] {
      font-size: 17px
    }

    .text-\[19px\] {
      font-size: 19px
    }

    .text-black {
      color: #04120A
    }

    .text-center {
      text-align: center
    }

    .text-left {
      text-align: left
    }

    .w-24 {
      width: 6rem
    }

    .w-40 {
      width: 10rem
    }

    .w-8 {
      width: 2rem
    }

    .w-full {
      width: 100%
    }

    @media(min-width:640px) {
      .sm\:grid-cols-2 {
        grid-template-columns: repeat(2, minmax(0, 1fr))
      }

      .sm\:inline-flex {
        display: inline-flex
      }
    }

    @media(min-width:768px) {
      .md\:col-span-2 {
        grid-column: span 2/span 2
      }

      .md\:grid-cols-3 {
        grid-template-columns: repeat(3, minmax(0, 1fr))
      }

      .md\:grid-cols-4 {
        grid-template-columns: repeat(4, minmax(0, 1fr))
      }
    }

    @media(min-width:1024px) {
      .lg\:flex {
        display: flex
      }

      .lg\:grid-cols-2 {
        grid-template-columns: repeat(2, minmax(0, 1fr))
      }

      .lg\:grid-cols-4 {
        grid-template-columns: repeat(4, minmax(0, 1fr))
      }

      .lg\:hidden {
        display: none
      }

      .lg\:p-10 {
        padding: 2.5rem
      }
    }
  </style>
  <script>document.documentElement.classList.add("jsr")</script>
</head>

<body>
  <div class="bgfx"></div>
  <div class="bg-grid"></div>
  <div class="scan"></div>

  <!-- ticker -->
  <div class="tick">
    <div class="tick-track" id="ticker"></div>
  </div>

  <!-- nav -->
  <header>
    <div class="max-w-[1280px] mx-auto px-5 h-[68px] flex items-center gap-6">
      <a href="#home" class="flex items-center shrink-0"><img class="logo" src="{{ asset('logo/logo.png') }}" alt="Demo" style="height: auto; max-height: 48px; object-fit: contain;"></a>
      <nav class="hidden lg:flex items-center gap-6 ml-2">
        <a class="nlink" href="#ai-arsenal">AI Engines</a>
        <a class="nlink flex items-center gap-1.5" href="{{ url('/ai/voice-assistant') }}" style="color:var(--green);font-weight:600;"><i class="fa-solid fa-microphone-lines"></i> Voice AI</a>
        <a class="nlink" href="#about">About</a>
        <a class="nlink" href="#business">Core Business</a>
        <a class="nlink" href="#income">Income</a>
        <a class="nlink" href="#packages">Packages</a>
        <a class="nlink" href="#ranks">Ranks</a>
        <a class="nlink" href="#terms">Terms</a>
      </nav>
      <div class="flex items-center gap-2.5 ml-auto">
        <a class="btn btn-ghost btn-sm hidden sm:inline-flex" href="{{url('/member')}}"><i class="fa-regular fa-user"></i> Login</a>
        <a class="btn btn-primary btn-sm" href="{{url('/member/register/1')}}">Register</a>
        <button class="btn btn-ghost btn-sm lg:hidden" id="burger" aria-label="Menu"><i
            class="fa-solid fa-bars"></i></button>
      </div>
    </div>
    <div class="mobmenu lg:hidden px-5 pb-4 flex-col gap-1" id="mob"
      style="background:rgba(5,7,10,.96);border-bottom:1px solid var(--line)">
      <a class="nlink py-2.5 block" href="#ai-arsenal">AI Engines</a>
      <a class="nlink py-2.5 block flex items-center gap-1.5" href="{{ url('/ai/voice-assistant') }}" style="color:var(--green);font-weight:600;"><i class="fa-solid fa-microphone-lines"></i> Voice AI Assistant</a>
      <a class="nlink py-2.5 block" href="#about">About</a><a class="nlink py-2.5 block" href="#business">Core
        Business</a>
      <a class="nlink py-2.5 block" href="#income">Income</a><a class="nlink py-2.5 block" href="#packages">Packages</a>
      <a class="nlink py-2.5 block" href="#ranks">Ranks</a><a class="nlink py-2.5 block" href="#terms">Terms</a>
    </div>
  </header>

  <!-- HERO -->
  <section id="home" class="hero">
    <div class="hero-fallback"></div>
    <canvas id="bg3d"></canvas>
    <div class="hero-wrap">
      <div class="grid lg:grid-cols-2 gap-12 items-center">
        <div data-aos="fade-up">
          <span class="badge"><span class="dot"></span> Arbitrage · Advantage · Automated</span>
          <h1>WELCOME TO<br><span class="gtext">DEMO</span></h1>
          <div class="tagband">Smart Arbitrage. <span class="gtext">Max Returns.</span></div>
          <p class="lead mt-5">Demo leverages advanced arbitrage technology to identify price differences across
            global markets — ensuring consistent returns with minimal risk. Automated profits, limitless possibilities.
          </p>
          <div class="flex flex-wrap gap-3 mt-7">
            <a href="#packages" class="btn btn-primary btn-lg">Start Earning <i class="fa-solid fa-arrow-right"></i></a>
            <a href="#business" class="btn btn-ghost btn-lg"><i class="fa-solid fa-cube"></i> How it works</a>
          </div>
          <div class="grid grid-cols-3 gap-5 mt-9 max-w-[440px]">
            <div class="stat">
              <div class="n gtext">15%</div>
              <div class="text-[12.5px] mt-1.5" style="color:var(--mut)">Max monthly ROI</div>
            </div>
            <div class="stat">
              <div class="n gtext">24/7</div>
              <div class="text-[12.5px] mt-1.5" style="color:var(--mut)">Automated engine</div>
            </div>
            <div class="stat">
              <div class="n gtext" data-count="60" data-suffix="+">0</div>
              <div class="text-[12.5px] mt-1.5" style="color:var(--mut)">Global markets</div>
            </div>
          </div>
        </div>

        <!-- floating arbitrage opportunity card -->
        <div data-aos="fade-left" data-aos-delay="150" class="justify-self-center w-full max-w-[400px]">
          <div class="arbcard" data-tilt>
            <div class="flex items-center justify-between mb-3">
              <span class="mono text-[12px] flex items-center gap-2" style="color:var(--green)"><span
                  class="dot"></span> ARBITRAGE OPPORTUNITY</span>
              <span class="mono text-[11px]" style="color:var(--mut)">LIVE</span>
            </div>
            <div class="arbrow">
              <div class="flex items-center gap-3"><span class="exch" style="background:#F0B90B">B</span>
                <div>
                  <div class="font-semibold text-[13px]">Buy · Binance</div>
                  <div class="text-[11px]" style="color:var(--mut)">BTC/USDT</div>
                </div>
              </div>
              <span class="mono font-bold" id="buyP">$62,450.10</span>
            </div>
            <div class="swap"><i class="fa-solid fa-arrows-rotate"></i></div>
            <div class="arbrow">
              <div class="flex items-center gap-3"><span class="exch" style="background:#2BE86B">C</span>
                <div>
                  <div class="font-semibold text-[13px]">Sell · Coinbase</div>
                  <div class="text-[11px]" style="color:var(--mut)">BTC/USDT</div>
                </div>
              </div>
              <span class="mono font-bold" id="sellP">$62,680.75</span>
            </div>
            <div class="profit-tag mt-3">
              <div class="text-[11px] mono" style="color:var(--mut)">PROFIT PER TRADE</div>
              <div class="disp font-extrabold text-[1.7rem] gtext" id="profP">$230.65</div>
            </div>
            <button class="btn btn-primary w-full mt-3">Execute Trade</button>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-14" data-aos="fade-up">
        <div class="glass neon rounded-2xl p-5">
          <div class="ic-ghost mb-3"><i class="fa-solid fa-dollar-sign"></i></div>
          <h4 class="head font-bold text-[15px]">Smart Arbitrage</h4>
          <p class="text-[13px] mt-1" style="color:var(--mut)">Identify. Execute. Earn.</p>
        </div>
        <div class="glass neon rounded-2xl p-5">
          <div class="ic-ghost mb-3"><i class="fa-solid fa-chart-line"></i></div>
          <h4 class="head font-bold text-[15px]">Max Returns</h4>
          <p class="text-[13px] mt-1" style="color:var(--mut)">Optimized strategies for higher profits.</p>
        </div>
        <div class="glass neon rounded-2xl p-5">
          <div class="ic-ghost mb-3"><i class="fa-solid fa-shield-halved"></i></div>
          <h4 class="head font-bold text-[15px]">Risk Management</h4>
          <p class="text-[13px] mt-1" style="color:var(--mut)">Secure trades with advanced protection.</p>
        </div>
        <div class="glass neon rounded-2xl p-5">
          <div class="ic-ghost mb-3"><i class="fa-solid fa-robot"></i></div>
          <h4 class="head font-bold text-[15px]">Automated System</h4>
          <p class="text-[13px] mt-1" style="color:var(--mut)">AI-powered execution 24/7.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ========================================================
       NEON FLIP 3D CARDS (CODE LSD 3D CARD ANIMATION)
       ======================================================== -->
  <section id="ai-arsenal" class="sec" style="position:relative; z-index:10; background: transparent; padding: 50px 20px 80px;">
    <style>
      /* ===( CODE LSD )=== */
      @import url("https://fonts.googleapis.com/css2?family=Nunito:wght@200;500;700;800;900;1000&display=swap");

      #ai-arsenal * {
        font-family: "Nunito", sans-serif;
      }

      #ai-arsenal .neon-header {
        text-align: center;
        max-width: 820px;
        margin: 0 auto 10px;
      }

      #ai-arsenal .container-neon {
        padding: 40px 0px 30px;
        display: flex;
        gap: 36px;
        justify-content: center;
        align-items: center;
        flex-wrap: wrap;
        max-width: 1380px;
        margin: 0 auto;
      }

      #ai-arsenal .parent-link {
        display: block;
        text-decoration: none;
        color: inherit;
      }

      #ai-arsenal .parent {
        height: 480px;
        width: 380px;
        max-width: calc(100vw - 40px);
        padding: 20px;
        perspective: 1100px;
        cursor: pointer;
      }

      #ai-arsenal .card {
        position: relative;
        padding-top: 130px;
        transform-style: preserve-3d;
        background-image: url("https://images.pexels.com/photos/1629236/pexels-photo-1629236.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1");
        background-position: center center;
        width: 100%;
        transition: all 0.5s ease-in-out;
        border-radius: 12px;
      }

      #ai-arsenal .card:hover,
      #ai-arsenal .parent:hover .card {
        background-position: 80% 20%;
        transform: rotate3d(0.5, 1, 0, 30deg);
      }

      #ai-arsenal .content-box {
        height: 100%;
        width: 100%;
        background-color: rgba(0, 0, 0, 0.811);
        border-radius: 0px 0px 12px 12px;
        box-shadow: rgba(0, 115, 255, 0.925) 0px 20px 50px -25px;
        transition: all 0.5s ease-in-out;
        padding: 55px 28px 28px 28px;
        transform-style: preserve-3d;
      }

      #ai-arsenal .card-title {
        color: white;
        font-size: 28px;
        font-weight: 900;
        transition: all 0.5s ease-in-out;
        transform: translate3d(0px, 0px, 20px);
      }

      #ai-arsenal .card-title:hover,
      #ai-arsenal .parent:hover .card-title {
        transform: translate3d(0px, 0px, 50px);
      }

      #ai-arsenal .card-content {
        padding-top: 16px;
        font-size: 13.5px;
        color: rgb(185, 185, 185);
        transition: all 0.5s ease-in-out;
        transform: translate3d(0px, 0px, 20px);
        line-height: 1.6;
      }

      #ai-arsenal .card-content:hover,
      #ai-arsenal .parent:hover .card-content {
        transform: translate3d(0px, 0px, 50px);
      }

      #ai-arsenal .see-more {
        display: block;
        font-weight: 900;
        font-size: 13px;
        text-transform: uppercase;
        color: rgb(0, 136, 255);
        padding-top: 20px;
        transition: all 0.5s ease-in-out;
        transform: translate3d(0px, 0px, 20px);
      }

      #ai-arsenal .see-more:hover,
      #ai-arsenal .parent:hover .see-more {
        transform: translate3d(0px, 0px, 50px);
      }

      #ai-arsenal .date-box {
        position: absolute;
        top: 96px;
        left: 28px;
        height: 68px;
        width: 68px;
        background-color: black;
        border-radius: 12px;
        padding: 12px 6px;
        box-shadow: rgb(0, 102, 255) 0px 20px 60px 0px, rgb(0, 200, 255) 0px 18px 36px -18px;
        transform: translate3d(0px, 0px, 50px);
      }

      #ai-arsenal .date-box span {
        display: block;
        text-align: center;
      }

      #ai-arsenal .date-box .month {
        color: white;
        font-size: 13px;
        font-weight: 700;
      }

      #ai-arsenal .date-box .date {
        font-size: 22px;
        font-weight: 900;
        color: rgb(0, 136, 255);
      }

      #ai-arsenal .card2 {
        filter: hue-rotate(150deg);
      }

      #ai-arsenal .card3 {
        filter: hue-rotate(300deg);
      }
    </style>

    <div class="neon-header" data-aos="fade-up">
      <div class="eyebrow"><i class="fa-solid fa-microchip"></i> QUANTUM AI TRADING ARSENAL</div>
      <h2 class="h2">Autonomous <span class="gtext">3 AI Trading Engines</span></h2>
      <p class="lead mx-auto mt-4">
        Our proprietary three-tier artificial intelligence architecture is engineered to scan global liquidity, forecast order-book momentum, and execute risk-free arbitrage trades. Click any card to explore the dedicated engine.
      </p>
    </div>

    <div class="container-neon" data-aos="fade-up" data-aos-delay="100">
      
      <!-- Card 1 (Blue) - Project AI Workspace -->
      <a href="{{ url('/project-ai') }}" class="parent-link">
        <div class="parent">
          <div class="card">
            <div class="content-box">
              <h1 class="card-title">Chat Assistant</h1>
              <p class="card-content">
                Advanced generative AI workspace powered by Google Gemini. Formulate launch roadmaps, architect full-stack systems, code algorithms.
              </p>
              <span class="see-more">Launch Project AI</span>
            </div>
            <div class="date-box">
              <span class="month">Project</span>
              <span class="date">AI</span>
            </div>
          </div>
        </div>
      </a>

      <!-- Card 2 (Red) - AI Voice Assistant -->
      <a href="{{ url('/ai/voice-assistant') }}" class="parent-link">
        <div class="parent card2">
          <div class="card">
            <div class="content-box">
              <h1 class="card-title">AI Voice Assistant</h1>
              <p class="card-content">
                Real-time multilingual voice intelligence. Speak naturally to query your account data, live arbitrage trades, ROI tiers, capping rules, and platform analytics.
              </p>
              <span class="see-more">Launch Voice AI</span>
            </div>
            <div class="date-box">
              <span class="month">Voice</span>
              <span class="date">AI</span>
            </div>
          </div>
        </div>
      </a>

      <!-- Card 3 (Green) - Plan Explainer AI -->
      <a href="{{ url('/ai/plan-explainer') }}" class="parent-link" id="planExplainerAiCard" title="Launch Plan Explainer AI Voice Assistant">
        <div class="parent card3">
          <div class="card">
            <div class="content-box">
              <h1 class="card-title">Plan Explainer AI</h1>
              <p class="card-content">
                Real-time voice-powered compensation plan explainer. Speak naturally or ask anything about daily ROI tiers (1.5%–4%), 5% direct bonus, 20-level generation rewards, and 3X/5X capping booster.
              </p>
              <span class="see-more">Launch Plan Explainer</span>
            </div>
            <div class="date-box">
              <span class="month">Plan</span>
              <span class="date">AI</span>
            </div>
          </div>
        </div>
      </a>

    </div>
  </section>



  <!-- ABOUT -->
  <section id="about" class="sec">
    <div class="grid lg:grid-cols-2 gap-12 items-center">
      <div data-aos="fade-right">
        <div class="eyebrow">About Us</div>
        <h2 class="h2">A structured, transparent<br><span class="gtext">arbitrage platform</span></h2>
        <p class="lead mt-5">Demo is a modern and structured business platform designed around a transparent and
          systematic model. Our plan combines <b style="color:var(--green)">monthly ROI</b>, <b
            style="color:var(--green)">direct referral benefits</b>, <b style="color:var(--green)">ROI-to-ROI
            matching</b>, <b style="color:var(--green)">development rewards</b>, and a <b
            style="color:var(--green)">progressive rank system</b>.</p>
        <p class="lead mt-4">We aim to create a professional ecosystem where participants can build networks, develop
          leadership skills, and benefit from clearly defined business rules and performance-based opportunities — with
          a focus on transparency, structured growth, and long-term relationships.</p>
        <div class="grid sm:grid-cols-2 gap-4 mt-7">
          <div class="glass neon rounded-2xl p-5" data-tilt>
            <div class="ic-ghost mb-3"><i class="fa-solid fa-bullseye"></i></div>
            <h4 class="head font-bold">Mission</h4>
            <p class="text-[13.5px] mt-1.5" style="color:var(--txt2)">Build a transparent, structured, growth-oriented
              platform that empowers individuals to develop their networks and leadership, with clearly defined rewards
              and fairness.</p>
          </div>
          <div class="glass neon rounded-2xl p-5" data-tilt>
            <div class="ic-ghost mb-3"><i class="fa-solid fa-eye"></i></div>
            <h4 class="head font-bold">Vision</h4>
            <p class="text-[13.5px] mt-1.5" style="color:var(--txt2)">Become a trusted, recognized global platform known
              for transparency, structured opportunities, and innovative, future-focused network development.</p>
          </div>
        </div>
      </div>
      <div data-aos="fade-left" class="justify-self-center">
        <div class="glass gborder rounded-3xl p-8 text-center" style="box-shadow:0 0 80px -30px var(--glow)">
          <img src="{{ asset('logo/logo.png') }}" alt="Demo" class="w-40 mx-auto mb-6" style="height: auto; object-fit: contain; filter:drop-shadow(0 0 30px rgba(43,232,107,.5))">
          <div class="grid grid-cols-2 gap-4 text-left">
            <div>
              <div class="stat n gtext" data-count="10" data-suffix="+">0</div>
              <div class="text-[12.5px] mt-1" style="color:var(--mut)">Income streams</div>
            </div>
            <div>
              <div class="stat n gtext" data-count="10" data-suffix="">0</div>
              <div class="text-[12.5px] mt-1" style="color:var(--mut)">Rank tiers</div>
            </div>
            <div>
              <div class="stat n gtext" data-count="20" data-suffix="">0</div>
              <div class="text-[12.5px] mt-1" style="color:var(--mut)">Income levels</div>
            </div>
            <div>
              <div class="stat n gtext">15%</div>
              <div class="text-[12.5px] mt-1" style="color:var(--mut)">Top ROI</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- CORE BUSINESS -->
  <section id="business" class="sec">
    <div class="text-center max-w-[640px] mx-auto" data-aos="fade-up">
      <div class="eyebrow">Our Core Business</div>
      <h2 class="h2">Built on <span class="gtext">arbitrage</span></h2>
      <p class="lead mx-auto mt-4">Three engines working together to generate consistent, low-risk returns across global
        markets.</p>
    </div>
    <div class="grid md:grid-cols-3 gap-5 mt-10">
      <div class="glass gborder neon rounded-2xl p-7 tilt" data-tilt data-aos="fade-up">
        <div class="ic mb-4"><i class="fa-solid fa-arrows-rotate"></i></div>
        <h3 class="head font-bold text-[19px]">Arbitrage</h3>
        <p class="text-[14px] mt-2" style="color:var(--txt2)">We identify price differences across multiple markets and
          execute smart arbitrage strategies to generate consistent and <b style="color:var(--green)">low-risk
            profits</b>.</p>
      </div>
      <div class="glass gborder neon rounded-2xl p-7 tilt" data-tilt data-aos="fade-up" data-aos-delay="90">
        <div class="ic mb-4"><i class="fa-solid fa-microchip"></i></div>
        <h3 class="head font-bold text-[19px]">Algorithmic Trading</h3>
        <p class="text-[14px] mt-2" style="color:var(--txt2)">Our advanced trading algorithms analyze real-time data and
          market trends to make intelligent trades and maximize returns with <b style="color:var(--green)">speed and
            precision</b>.</p>
      </div>
      <div class="glass gborder neon rounded-2xl p-7 tilt" data-tilt data-aos="fade-up" data-aos-delay="180">
        <div class="ic mb-4"><i class="fa-solid fa-shield-halved"></i></div>
        <h3 class="head font-bold text-[19px]">Risk Management</h3>
        <p class="text-[14px] mt-2" style="color:var(--txt2)">We use robust risk management systems and automation to
          protect capital, minimize exposure, and ensure <b style="color:var(--green)">long-term sustainability</b>.</p>
      </div>
    </div>
    <div class="flex flex-wrap justify-center gap-x-8 gap-y-3 mt-8" data-aos="fade-up">
      <span class="trust"><i class="fa-solid fa-lock"></i> Capital Protection</span>
      <span class="trust"><i class="fa-solid fa-chart-pie"></i> Minimize Exposure</span>
      <span class="trust"><i class="fa-solid fa-gears"></i> Automation &amp; Security</span>
    </div>
  </section>

  <!-- ARBITRAGE FLOW -->
  <section class="sec pt-0">
    <div class="glass rounded-3xl p-8 lg:p-10" data-aos="zoom-in" style="box-shadow:0 0 90px -40px var(--glow)">
      <div class="text-center mb-8">
        <div class="eyebrow">How Arbitrage Works</div>
        <h2 class="h2 text-[1.5rem]">Buy low, sell high — <span class="gtext">automatically</span></h2>
      </div>
      <div class="flow">
        <div class="flow-node glass" style="border-color:var(--line2)">
          <div class="text-[11px] mono" style="color:var(--mut)">BUY ON</div>
          <div class="head font-bold text-[17px] mt-1">Binance</div>
          <div class="mono text-[13px] mt-2" style="color:var(--txt2)">BTC/USDT</div>
          <div class="disp font-extrabold text-[1.3rem] mt-1" style="color:var(--txt)">$62,450</div>
          <div class="mt-2 text-[12px]" style="color:var(--mut)">Lower price</div>
        </div>
        <div class="arrow"><i class="fa-solid fa-arrow-right-long"></i></div>
        <div class="flow-node gborder glass" style="border-color:var(--line2)">
          <div class="swap" style="margin-bottom:8px"><i class="fa-solid fa-atom"></i></div>
          <div class="head font-bold text-[17px]">Demo Engine</div>
          <div class="text-[12px] mt-1" style="color:var(--mut)">AI detects spread &amp; executes in milliseconds</div>
        </div>
        <div class="arrow"><i class="fa-solid fa-arrow-right-long"></i></div>
        <div class="flow-node glass" style="border-color:var(--line2)">
          <div class="text-[11px] mono" style="color:var(--mut)">SELL ON</div>
          <div class="head font-bold text-[17px] mt-1">Coinbase</div>
          <div class="mono text-[13px] mt-2" style="color:var(--txt2)">BTC/USDT</div>
          <div class="disp font-extrabold text-[1.3rem] mt-1 gtext">$62,680</div>
          <div class="mt-2 text-[12px]" style="color:var(--green)">+$230 profit</div>
        </div>
      </div>
    </div>
  </section>

  <!-- TYPES OF INCOME -->
  <section id="income" class="sec">
    <div class="text-center max-w-[640px] mx-auto" data-aos="fade-up">
      <div class="eyebrow">Types of Income</div>
      <h2 class="h2">Four ways to <span class="gtext">earn</span></h2>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5 mt-10">
      <div class="glass gborder neon rounded-2xl p-6 tilt" data-tilt data-aos="fade-up">
        <div class="flex items-center justify-between">
          <div class="ic"><i class="fa-solid fa-hand-holding-dollar"></i></div><span
            class="disp font-extrabold text-[15px]" style="color:var(--line2)">01</span>
        </div>
        <h3 class="head font-bold text-[17px] mt-4">ROI Income</h3>
        <p class="text-[13.5px] mt-2" style="color:var(--txt2)">Earn consistent returns on your investment through our
          automated ROI system.</p>
      </div>
      <div class="glass gborder neon rounded-2xl p-6 tilt" data-tilt data-aos="fade-up" data-aos-delay="70">
        <div class="flex items-center justify-between">
          <div class="ic"><i class="fa-solid fa-user-plus"></i></div><span class="disp font-extrabold text-[15px]"
            style="color:var(--line2)">02</span>
        </div>
        <h3 class="head font-bold text-[17px] mt-4">Direct Referral</h3>
        <p class="text-[13.5px] mt-2" style="color:var(--txt2)">Earn attractive income by referring direct members to
          the platform.</p>
      </div>
      <div class="glass gborder neon rounded-2xl p-6 tilt" data-tilt data-aos="fade-up" data-aos-delay="140">
        <div class="flex items-center justify-between">
          <div class="ic"><i class="fa-solid fa-sitemap"></i></div><span class="disp font-extrabold text-[15px]"
            style="color:var(--line2)">03</span>
        </div>
        <h3 class="head font-bold text-[17px] mt-4">Level Income</h3>
        <p class="text-[13.5px] mt-2" style="color:var(--txt2)">Earn income from your team across multiple levels as
          they grow and achieve.</p>
      </div>
      <div class="glass gborder neon rounded-2xl p-6 tilt" data-tilt data-aos="fade-up" data-aos-delay="210">
        <div class="flex items-center justify-between">
          <div class="ic"><i class="fa-solid fa-trophy"></i></div><span class="disp font-extrabold text-[15px]"
            style="color:var(--line2)">04</span>
        </div>
        <h3 class="head font-bold text-[17px] mt-4">Rank Reward</h3>
        <p class="text-[13.5px] mt-2" style="color:var(--txt2)">Achieve higher ranks and unlock exclusive rewards and
          bonuses.</p>
      </div>
    </div>
  </section>

  <!-- PACKAGE & ROI + CALCULATOR -->
  <section id="packages" class="sec">
    <div class="text-center max-w-[640px] mx-auto" data-aos="fade-up">
      <div class="eyebrow">Package &amp; ROI</div>
      <h2 class="h2">Grow your business, <span class="gtext">maximize returns</span></h2>
      <p class="lead mx-auto mt-4">Monthly ROI scales with your business volume.</p>
    </div>
    <div class="grid lg:grid-cols-2 gap-6 mt-10 items-start">
      <div data-aos="fade-right">
        <table class="qtable">
          <thead>
            <tr>
              <th>Monthly Amount / Business</th>
              <th style="text-align:right">Monthly ROI</th>
            </tr>
          </thead>
          <tbody>
            <tr class="qrow">
              <td>
                <div><span class="ic-ghost" style="width:38px;height:38px;font-size:15px;margin-right:12px"><i
                      class="fa-solid fa-dollar-sign"></i></span><span class="head font-semibold">$100 – $1,000</span>
                </div>
              </td>
              <td>
                <div style="justify-content:flex-end"><span class="pct gtext">10%</span></div>
              </td>
            </tr>
            <tr class="qrow">
              <td>
                <div><span class="ic-ghost" style="width:38px;height:38px;font-size:15px;margin-right:12px"><i
                      class="fa-solid fa-dollar-sign"></i></span><span class="head font-semibold">$1,100 – $5,000</span>
                </div>
              </td>
              <td>
                <div style="justify-content:flex-end"><span class="pct gtext">12%</span></div>
              </td>
            </tr>
            <tr class="qrow">
              <td>
                <div><span class="ic-ghost" style="width:38px;height:38px;font-size:15px;margin-right:12px"><i
                      class="fa-solid fa-dollar-sign"></i></span><span class="head font-semibold">$5,100 –
                    $10,000</span></div>
              </td>
              <td>
                <div style="justify-content:flex-end"><span class="pct gtext">14%</span></div>
              </td>
            </tr>
            <tr class="qrow">
              <td>
                <div><span class="ic-ghost" style="width:38px;height:38px;font-size:15px;margin-right:12px"><i
                      class="fa-solid fa-dollar-sign"></i></span><span class="head font-semibold">$10,100+</span></div>
              </td>
              <td>
                <div style="justify-content:flex-end"><span class="pct gtext">15%</span></div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <!-- calculator -->
      <div class="glass gborder rounded-3xl p-7" data-aos="fade-left">
        <div class="eyebrow">ROI Calculator</div>
        <h3 class="head font-bold text-[1.4rem] mt-2 mb-5">Estimate your monthly return</h3>
        <div class="flex justify-between items-end mb-2">
          <span class="text-[13px]" style="color:var(--mut)">Investment amount</span>
          <span class="disp font-extrabold text-[1.5rem] gtext mono" id="calcAmt">$5,000</span>
        </div>
        <input type="range" id="calcRange" min="100" max="50000" step="100" value="5000">
        <div class="flex justify-between text-[11px] mt-1" style="color:var(--mut)">
          <span>$100</span><span>$50,000+</span></div>
        <div class="grid grid-cols-3 gap-3 mt-6">
          <div class="calc-out">
            <div class="text-[11px] mono" style="color:var(--mut)">ROI TIER</div>
            <div class="v gtext mt-1" id="calcTier">12%</div>
          </div>
          <div class="calc-out">
            <div class="text-[11px] mono" style="color:var(--mut)">MONTHLY</div>
            <div class="v gtext mt-1" id="calcMonth">$600</div>
          </div>
          <div class="calc-out">
            <div class="text-[11px] mono" style="color:var(--mut)">YEARLY*</div>
            <div class="v gtext mt-1" id="calcYear">$7,200</div>
          </div>
        </div>
        <p class="text-[11.5px] mt-4" style="color:var(--mut)">*Illustration only, before applicable deductions. Working
          income capped at 5X and non-working at 3X per plan terms.</p>
      </div>
    </div>
  </section>

  <!-- DIRECT REFERRAL -->
  <section class="sec pt-0">
    <div class="glass gborder rounded-3xl p-8 lg:p-10 overflow-hidden" data-aos="zoom-in">
      <div class="grid lg:grid-cols-2 gap-10 items-center">
        <div>
          <div class="eyebrow">Refer · Connect · Earn</div>
          <h2 class="h2 mt-2">Direct Referral Income</h2>
          <div class="flex items-baseline gap-3 mt-5">
            <span class="disp font-extrabold gtext" style="font-size:clamp(3rem,8vw,4.5rem);line-height:1">5%</span>
            <span class="head font-semibold text-[1.2rem]" style="color:var(--txt2)">one time</span>
          </div>
          <p class="lead mt-4">Invite people, build connections, and earn <b style="color:var(--green)">5% one time</b>
            on every direct referral. Simple, powerful, and unlimited.</p>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div class="glass neon rounded-2xl p-5 text-center" data-tilt>
            <div class="ic-ghost mx-auto mb-3"><i class="fa-solid fa-people-group"></i></div>
            <h4 class="head font-bold text-[14px]">Simple &amp; Powerful</h4>
          </div>
          <div class="glass neon rounded-2xl p-5 text-center" data-tilt>
            <div class="ic-ghost mx-auto mb-3"><i class="fa-solid fa-medal"></i></div>
            <h4 class="head font-bold text-[14px]">One Time 5% Income</h4>
          </div>
          <div class="glass neon rounded-2xl p-5 text-center" data-tilt>
            <div class="ic-ghost mx-auto mb-3"><i class="fa-solid fa-handshake"></i></div>
            <h4 class="head font-bold text-[14px]">Grow Your Network</h4>
          </div>
          <div class="glass neon rounded-2xl p-5 text-center" data-tilt>
            <div class="ic-ghost mx-auto mb-3"><i class="fa-solid fa-chart-line"></i></div>
            <h4 class="head font-bold text-[14px]">Earn More Together</h4>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- LEVEL INCOME -->
  <section class="sec pt-0">
    <div class="text-center max-w-[640px] mx-auto" data-aos="fade-up">
      <div class="eyebrow">Level Income</div>
      <h2 class="h2">Build more connections, <span class="gtext">earn more</span></h2>
    </div>
    <div class="max-w-[860px] mx-auto mt-9" data-aos="fade-up">
      <table class="qtable">
        <thead>
          <tr>
            <th>Level</th>
            <th>Rate</th>
            <th>Qualification</th>
          </tr>
        </thead>
        <tbody>
          <tr class="qrow">
            <td>
              <div class="head font-semibold"><span class="gem mr-3">1</span>Level 1</div>
            </td>
            <td>
              <div><span class="pct gtext">15%</span></div>
            </td>
            <td>
              <div class="text-[13px]" style="color:var(--txt2)">1 direct required to open level</div>
            </td>
          </tr>
          <tr class="qrow">
            <td>
              <div class="head font-semibold"><span class="gem mr-3">2</span>Level 2</div>
            </td>
            <td>
              <div><span class="pct gtext">10%</span></div>
            </td>
            <td>
              <div class="text-[13px]" style="color:var(--txt2)">2 direct required to open level</div>
            </td>
          </tr>
          <tr class="qrow">
            <td>
              <div class="head font-semibold"><span class="gem mr-3"><i
                    class="fa-solid fa-users text-[11px]"></i></span>Levels 3–5</div>
            </td>
            <td>
              <div><span class="pct gtext">5%</span></div>
            </td>
            <td>
              <div class="text-[13px]" style="color:var(--txt2)">Each level open · 1 direct required</div>
            </td>
          </tr>
          <tr class="qrow">
            <td>
              <div class="head font-semibold"><span class="gem mr-3"><i
                    class="fa-solid fa-users text-[11px]"></i></span>Levels 6–10</div>
            </td>
            <td>
              <div><span class="pct gtext">3%</span></div>
            </td>
            <td>
              <div class="text-[13px]" style="color:var(--txt2)">Each level open · 1 direct required</div>
            </td>
          </tr>
          <tr class="qrow">
            <td>
              <div class="head font-semibold"><span class="gem mr-3"><i
                    class="fa-solid fa-users text-[11px]"></i></span>Levels 11–20</div>
            </td>
            <td>
              <div><span class="pct gtext">2%</span></div>
            </td>
            <td>
              <div class="text-[13px]" style="color:var(--txt2)">20 direct required to open 20 levels</div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>

  <!-- RANK & REWARD -->
  <section id="ranks" class="sec">
    <div class="text-center max-w-[680px] mx-auto" data-aos="fade-up">
      <div class="eyebrow">Rank &amp; Reward Plan</div>
      <h2 class="h2">Climb the ranks, <span class="gtext">claim rewards</span></h2>
      <p class="lead mx-auto mt-4">Achieve a rank and earn its reward for 3 consecutive months. Business accumulates 50%
        strong leg / 50% weak leg.</p>
    </div>
    <div class="rankwrap glass mt-9" data-aos="fade-up">
      <div class="rankrow" style="background:rgba(43,232,107,.06)">
        <div class="eyebrow" style="font-size:10px">Tier</div>
        <div class="eyebrow" style="font-size:10px">Rank</div>
        <div class="eyebrow mbiz" style="font-size:10px">Matching Business</div>
        <div class="eyebrow" style="font-size:10px;text-align:right">Reward</div>
      </div>
      <div class="rankrow">
        <div class="tier">T1</div>
        <div class="rankname"><span class="gem"><i class="fa-solid fa-star"></i></span>Silver</div>
        <div class="mbiz mono" style="color:var(--txt2)">$5,000</div>
        <div class="reward">$125</div>
      </div>
      <div class="rankrow">
        <div class="tier">T2</div>
        <div class="rankname"><span class="gem"><i class="fa-solid fa-star"></i></span>Gold</div>
        <div class="mbiz mono" style="color:var(--txt2)">$15,000</div>
        <div class="reward">$375</div>
      </div>
      <div class="rankrow">
        <div class="tier">T3</div>
        <div class="rankname"><span class="gem"><i class="fa-solid fa-gem"></i></span>Platinum</div>
        <div class="mbiz mono" style="color:var(--txt2)">$45,000</div>
        <div class="reward">$1,125</div>
      </div>
      <div class="rankrow">
        <div class="tier">T4</div>
        <div class="rankname"><span class="gem"><i class="fa-solid fa-gem"></i></span>Ruby</div>
        <div class="mbiz mono" style="color:var(--txt2)">$150,000</div>
        <div class="reward">$3,750</div>
      </div>
      <div class="rankrow">
        <div class="tier">T5</div>
        <div class="rankname"><span class="gem"><i class="fa-solid fa-gem"></i></span>Emerald</div>
        <div class="mbiz mono" style="color:var(--txt2)">$300,000</div>
        <div class="reward">$7,500</div>
      </div>
      <div class="rankrow">
        <div class="tier">T6</div>
        <div class="rankname"><span class="gem"><i class="fa-solid fa-gem"></i></span>Sapphire</div>
        <div class="mbiz mono" style="color:var(--txt2)">$900,000</div>
        <div class="reward">$22,500</div>
      </div>
      <div class="rankrow">
        <div class="tier">T7</div>
        <div class="rankname"><span class="gem"><i class="fa-solid fa-gem"></i></span>Blue Diamond</div>
        <div class="mbiz mono" style="color:var(--txt2)">$2.7 Million</div>
        <div class="reward">$67,500</div>
      </div>
      <div class="rankrow">
        <div class="tier">T8</div>
        <div class="rankname"><span class="gem"><i class="fa-solid fa-crown"></i></span>Crown Diamond</div>
        <div class="mbiz mono" style="color:var(--txt2)">$9 Million</div>
        <div class="reward">$225,000</div>
      </div>
      <div class="rankrow">
        <div class="tier">T9</div>
        <div class="rankname"><span class="gem"><i class="fa-solid fa-crown"></i></span>Regal Diamond</div>
        <div class="mbiz mono" style="color:var(--txt2)">$18 Million</div>
        <div class="reward">$450,000</div>
      </div>
      <div class="rankrow">
        <div class="tier">T10</div>
        <div class="rankname"><span class="gem"><i class="fa-solid fa-crown"></i></span>Imperial Crown Diamond</div>
        <div class="mbiz mono" style="color:var(--txt2)">$36 Million</div>
        <div class="reward">$900,000</div>
      </div>
    </div>
  </section>

  <!-- LIVE ARBITRAGE SPREADS -->
  <section class="sec pt-0">
    <div class="flex flex-wrap justify-between items-end gap-3 mb-6" data-aos="fade-up">
      <div>
        <div class="eyebrow">Live Arbitrage</div>
        <h2 class="h2 text-[1.5rem]">Real-time cross-exchange spreads</h2>
      </div>
      <span class="trust"><span class="dot"></span> <span id="liveSrc">Live prices</span></span>
    </div>
    <div class="glass rounded-2xl overflow-hidden" data-aos="fade-up">
      <div class="overflow-x-auto">
        <table class="mkt">
          <thead>
            <tr>
              <th>Pair</th>
              <th>Buy @ Exchange A</th>
              <th>Sell @ Exchange B</th>
              <th>Spread</th>
              <th>Profit / $10k</th>
            </tr>
          </thead>
          <tbody id="arbBody"></tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- TERMS -->
  <section id="terms" class="sec">
    <div class="text-center max-w-[640px] mx-auto" data-aos="fade-up">
      <div class="eyebrow">Terms &amp; Conditions</div>
      <h2 class="h2">Clear rules, <span class="gtext">fair play</span></h2>
      <p class="lead mx-auto mt-4">Please read these terms carefully before using the platform.</p>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-9">
      <div class="glass neon rounded-2xl p-6" data-aos="fade-up">
        <div class="ic-ghost mb-3"><i class="fa-solid fa-wallet"></i></div>
        <h4 class="head font-bold text-[15px]">Minimum Deposit</h4>
        <p class="mono text-[13px] mt-1" style="color:var(--green)">$100 minimum</p>
      </div>
      <div class="glass neon rounded-2xl p-6" data-aos="fade-up" data-aos-delay="50">
        <div class="ic-ghost mb-3"><i class="fa-solid fa-money-bill-transfer"></i></div>
        <h4 class="head font-bold text-[15px]">Minimum Withdrawal</h4>
        <p class="mono text-[13px] mt-1" style="color:var(--green)">$10 minimum</p>
      </div>
      <div class="glass neon rounded-2xl p-6" data-aos="fade-up" data-aos-delay="100">
        <div class="ic-ghost mb-3"><i class="fa-solid fa-gear"></i></div>
        <h4 class="head font-bold text-[15px]">Withdrawal Admin Charge</h4>
        <p class="mono text-[13px] mt-1" style="color:var(--green)">5% on withdrawal</p>
      </div>
      <div class="glass neon rounded-2xl p-6" data-aos="fade-up" data-aos-delay="150">
        <div class="ic-ghost mb-3"><i class="fa-solid fa-right-left"></i></div>
        <h4 class="head font-bold text-[15px]">P2P Transfer</h4>
        <p class="mono text-[13px] mt-1" style="color:var(--green)">Available</p>
      </div>
      <div class="glass neon rounded-2xl p-6" data-aos="fade-up">
        <div class="ic-ghost mb-3"><i class="fa-solid fa-arrow-down-to-bracket"></i></div>
        <h4 class="head font-bold text-[15px]">Income to Wallet</h4>
        <p class="mono text-[13px] mt-1" style="color:var(--green)">5% deduction</p>
      </div>
      <div class="glass neon rounded-2xl p-6" data-aos="fade-up" data-aos-delay="50">
        <div class="ic-ghost mb-3"><i class="fa-solid fa-gauge-high"></i></div>
        <h4 class="head font-bold text-[15px]">Income Cap</h4>
        <p class="mono text-[13px] mt-1" style="color:var(--green)">5X working / 3X non-working</p>
      </div>
      <div class="glass neon rounded-2xl p-6" data-aos="fade-up" data-aos-delay="100">
        <div class="ic-ghost mb-3"><i class="fa-solid fa-scale-balanced"></i></div>
        <h4 class="head font-bold text-[15px]">Strong / Weak Leg</h4>
        <p class="mono text-[13px] mt-1" style="color:var(--green)">50% / 50% accumulation</p>
      </div>
      <div class="glass neon rounded-2xl p-6" data-aos="fade-up" data-aos-delay="150">
        <div class="ic-ghost mb-3"><i class="fa-solid fa-file-contract"></i></div>
        <h4 class="head font-bold text-[15px]">Other Terms</h4>
        <p class="text-[13px] mt-1" style="color:var(--txt2)">All terms subject to change without prior notice. Please
          stay updated.</p>
      </div>
    </div>
  </section>

  <!-- CTA / THANK YOU -->
  <section id="contact" class="sec">
    <div class="glass gborder rounded-3xl text-center px-6 py-16 relative overflow-hidden" data-aos="zoom-in"
      style="box-shadow:0 0 100px -40px var(--glow)">
      <div class="absolute inset-0 -z-0"
        style="background:radial-gradient(60% 80% at 50% 0%,rgba(43,232,107,.14),transparent 60%)"></div>
      <div class="relative">
        <img src="{{ asset('logo/logo.png') }}" alt="Demo" class="w-24 mx-auto mb-6" style="height: auto; object-fit: contain; filter:drop-shadow(0 0 26px rgba(43,232,107,.5))">
        <div class="eyebrow">The best is yet to come</div>
        <h2 class="h2 mt-2">Let's create a <span class="gtext">successful future</span> together</h2>
        <p class="lead mx-auto mt-4">Together, we're building a stronger network and creating unlimited opportunities.
          Let's grow, achieve, and succeed together — see you at the top!</p>
        <div class="flex flex-wrap gap-3 justify-center mt-7">
          <a href="{{url('/member/register/1')}}" class="btn btn-primary btn-lg">Register Now <i class="fa-solid fa-arrow-right"></i></a>
          <a href="#packages" class="btn btn-ghost btn-lg">View Packages</a>
        </div>
        <div class="flex flex-wrap justify-center gap-x-8 gap-y-3 mt-9">
          <span class="trust"><i class="fa-solid fa-users"></i> Strong Community</span>
          <span class="trust"><i class="fa-solid fa-infinity"></i> Unlimited Opportunities</span>
          <span class="trust"><i class="fa-solid fa-shield-halved"></i> Secure &amp; Transparent</span>
          <span class="trust"><i class="fa-solid fa-handshake-angle"></i> Together We Achieve More</span>
        </div>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer>
    <div class="max-w-[1280px] mx-auto px-5 py-14">
      <div class="grid md:grid-cols-4 gap-10">
        <div class="md:col-span-2">
          <img class="logo" style="height: 54px; width: auto; object-fit: contain;" src="{{ asset('logo/logo.png') }}" alt="Demo">
          <p class="text-[14px] max-w-[360px] mt-4" style="color:var(--mut)">Advanced arbitrage technology identifying
            price differences across global markets — consistent returns with minimal risk. Arbitrage. Advantage.
            Automated.</p>
          <div class="flex items-center gap-2 mt-4 text-[13.5px]" style="color:var(--txt2)"><i class="fa-solid fa-globe"
              style="color:var(--green)"></i> www.demo.com</div>
          <div class="flex gap-2 mt-5">
            <a class="soc" href="#"><i class="fa-brands fa-telegram"></i></a>
            <a class="soc" href="#"><i class="fa-brands fa-x-twitter"></i></a>
            <a class="soc" href="#"><i class="fa-brands fa-instagram"></i></a>
            <a class="soc" href="#"><i class="fa-brands fa-youtube"></i></a>
          </div>
        </div>
        <div>
          <h5 class="head font-bold text-[14px] mb-3">Platform</h5><a href="#about" class="flink block">About Us</a><a
            href="#business" class="flink block">Core Business</a><a href="#income" class="flink block">Income
            Plan</a><a href="#packages" class="flink block">Package &amp; ROI</a>
        </div>
        <div>
          <h5 class="head font-bold text-[14px] mb-3">Earn</h5><a href="#packages" class="flink block">Monthly ROI</a><a
            href="#ranks" class="flink block">Rank &amp; Reward</a><a href="#terms" class="flink block">Terms</a><a
            href="#contact" class="flink block">Get Started</a>
        </div>
      </div>
      <div class="flex flex-wrap justify-between items-center gap-3 mt-12 pt-6"
        style="border-top:1px solid var(--line)">
        <span class="text-[13px]" style="color:var(--mut)">© {{ date('Y') }} Demo. All rights reserved.</span>
        <span class="text-[13px]" style="color:var(--mut)">Arbitrage and trading involve risk. Returns are not
          guaranteed.</span>
      </div>
    </div>
  </footer>

  <!-- ========================================================
       QUANTUM ARB AI CHATBOT (THEME ACCORDING STYLES & INTERACTION)
       ======================================================== -->
  <style>
    /* FLOATING TRIGGER BUTTON */
    .chat-fab {
      position: fixed;
      bottom: 28px;
      right: 28px;
      z-index: 9999;
      width: 60px;
      height: 60px;
      border-radius: 50%;
      background: var(--brand);
      border: 1px solid rgba(255, 255, 255, 0.3);
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 8px 32px rgba(43, 232, 107, 0.45), 0 0 20px rgba(43, 232, 107, 0.35);
      transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .chat-fab::after {
      content: '';
      position: absolute;
      inset: -5px;
      border-radius: 50%;
      border: 2px solid rgba(43, 232, 107, 0.5);
      animation: chatFabPulse 2.2s infinite cubic-bezier(0.2, 0.8, 0.2, 1);
      pointer-events: none;
    }

    @keyframes chatFabPulse {
      0% { transform: scale(0.92); opacity: 1; }
      100% { transform: scale(1.4); opacity: 0; }
    }

    .chat-fab:hover {
      transform: scale(1.1) translateY(-3px);
      box-shadow: 0 12px 38px rgba(43, 232, 107, 0.65), 0 0 28px rgba(43, 232, 107, 0.5);
    }

    .chat-fab svg {
      width: 26px;
      height: 26px;
      fill: #05070A;
      transition: transform 0.3s ease;
    }

    .chat-fab:hover svg {
      transform: scale(1.08);
    }

    .chat-fab.is-active svg {
      transform: rotate(90deg);
    }

    /* CHAT CARD ASIDE */
    .chat-card {
      position: fixed;
      bottom: 100px;
      right: 28px;
      z-index: 10000;
      width: 390px;
      max-width: calc(100vw - 36px);
      height: 560px;
      max-height: calc(100vh - 125px);
      background: rgba(10, 16, 12, 0.94);
      backdrop-filter: blur(24px);
      -webkit-backdrop-filter: blur(24px);
      border: 1px solid rgba(43, 232, 107, 0.25);
      border-radius: 20px;
      box-shadow: 0 25px 65px rgba(0, 0, 0, 0.88), 0 0 45px rgba(43, 232, 107, 0.16);
      display: flex;
      flex-direction: column;
      overflow: hidden;
      opacity: 0;
      visibility: hidden;
      pointer-events: none;
      transform: translateY(20px) scale(0.94);
      transform-origin: bottom right;
      transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .chat-card.open,
    .chat-card.is-open {
      opacity: 1;
      visibility: visible;
      pointer-events: auto;
      transform: translateY(0) scale(1);
    }

    .message.error .message-body {
      background: rgba(239, 68, 68, 0.12);
      border: 1px solid rgba(239, 68, 68, 0.35);
      border-radius: 4px 16px 16px 16px;
      color: #fca5a5;
    }
    .message.error .message-avatar {
      background: #ef4444;
      color: #fff;
    }

    /* HEADER */
    .chat-header {
      padding: 16px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 1px solid rgba(43, 232, 107, 0.16);
      background: rgba(5, 7, 10, 0.65);
    }

    .chat-header p {
      margin: 0;
      font-family: 'Space Grotesk', sans-serif;
      font-size: 16px;
      font-weight: 700;
      color: #ffffff;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .chat-header p::before {
      content: '';
      display: inline-block;
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: var(--green);
      box-shadow: 0 0 10px var(--green);
    }

    .chat-header span {
      display: block;
      font-size: 12px;
      color: var(--txt2);
      margin-top: 3px;
      font-family: 'Inter', sans-serif;
    }

    #chatClose {
      background: rgba(255, 255, 255, 0.06);
      border: 1px solid rgba(255, 255, 255, 0.12);
      width: 32px;
      height: 32px;
      border-radius: 50%;
      color: var(--txt2);
      font-size: 22px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      line-height: 1;
      transition: all 0.2s;
    }

    #chatClose:hover {
      background: rgba(255, 255, 255, 0.15);
      color: #fff;
      transform: scale(1.05);
    }

    /* MESSAGES */
    .messages {
      flex: 1;
      overflow-y: auto;
      padding: 18px;
      display: flex;
      flex-direction: column;
      gap: 14px;
      scroll-behavior: smooth;
    }

    .messages::-webkit-scrollbar {
      width: 5px;
    }

    .messages::-webkit-scrollbar-thumb {
      background: rgba(43, 232, 107, 0.25);
      border-radius: 10px;
    }

    .message {
      display: flex;
      flex-direction: column;
      max-width: 88%;
      animation: msgPop 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes msgPop {
      0% { opacity: 0; transform: translateY(8px); }
      100% { opacity: 1; transform: translateY(0); }
    }

    .message.assistant {
      align-self: flex-start;
    }

    .message.user {
      align-self: flex-end;
    }

    .message-meta {
      display: flex;
      align-items: center;
      gap: 6px;
      margin-bottom: 5px;
    }

    .message.user .message-meta {
      justify-content: flex-end;
    }

    .message-avatar {
      font-size: 10px;
      font-weight: 800;
      padding: 2px 7px;
      border-radius: 10px;
      letter-spacing: 0.05em;
    }

    .message.assistant .message-avatar {
      background: var(--brand);
      color: #05070A;
    }

    .message.user .message-avatar {
      background: rgba(255, 255, 255, 0.15);
      color: #fff;
    }

    .message-label {
      font-size: 11px;
      color: var(--mut);
    }

    .message-body {
      padding: 12px 15px;
      font-size: 13.5px;
      line-height: 1.55;
    }

    .message.assistant .message-body {
      background: rgba(18, 183, 106, 0.1);
      border: 1px solid rgba(43, 232, 107, 0.2);
      border-radius: 4px 16px 16px 16px;
      color: var(--txt);
    }

    .message.user .message-body {
      background: linear-gradient(135deg, rgba(43, 232, 107, 0.22), rgba(18, 183, 106, 0.35));
      border: 1px solid rgba(43, 232, 107, 0.45);
      border-radius: 16px 4px 16px 16px;
      color: #ffffff;
    }

    .message-body p {
      margin: 0;
    }

    /* SUGGESTIONS */
    .chat-suggestions {
      padding: 10px 16px;
      display: flex;
      gap: 8px;
      overflow-x: auto;
      scrollbar-width: none;
      background: rgba(5, 7, 10, 0.55);
      border-top: 1px solid rgba(255, 255, 255, 0.05);
    }

    .chat-suggestions::-webkit-scrollbar {
      display: none;
    }

    .chat-chip {
      background: rgba(43, 232, 107, 0.08);
      border: 1px solid rgba(43, 232, 107, 0.25);
      color: var(--txt);
      font-size: 11.5px;
      font-weight: 600;
      padding: 6px 13px;
      border-radius: 20px;
      cursor: pointer;
      white-space: nowrap;
      transition: all 0.2s ease;
      font-family: 'Space Grotesk', sans-serif;
    }

    .chat-chip:hover {
      background: rgba(43, 232, 107, 0.22);
      border-color: var(--green);
      color: #fff;
      transform: translateY(-1px);
    }

    /* FORM */
    .chat-form {
      padding: 12px 16px 16px;
      display: flex;
      gap: 10px;
      align-items: center;
      background: rgba(5, 7, 10, 0.75);
      border-top: 1px solid rgba(43, 232, 107, 0.15);
    }

    #questionInput {
      flex: 1;
      height: 44px;
      resize: none;
      background: rgba(255, 255, 255, 0.04);
      border: 1px solid rgba(43, 232, 107, 0.22);
      border-radius: 12px;
      color: var(--txt);
      padding: 11px 14px;
      font-family: 'Inter', sans-serif;
      font-size: 13.5px;
      outline: none;
      transition: border-color 0.2s, box-shadow 0.2s;
    }

    #questionInput:focus {
      border-color: var(--green);
      box-shadow: 0 0 14px rgba(43, 232, 107, 0.25);
    }

    #sendButton {
      width: 44px;
      height: 44px;
      border-radius: 12px;
      background: var(--brand);
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s;
      flex-shrink: 0;
    }

    #sendButton:hover {
      transform: scale(1.05);
      box-shadow: 0 0 16px rgba(43, 232, 107, 0.6);
    }

    #sendButton svg {
      width: 18px;
      height: 18px;
      fill: #05070A;
    }
  </style>

  <!-- FLOATING SIDE BUTTON -->
  <button class="chat-fab" id="chatToggle" type="button" aria-label="Open PDF chat" title="Open AI Assistant">
    <svg viewBox="0 0 24 24" aria-hidden="true">
      <path d="M4 5.5A3.5 3.5 0 0 1 7.5 2h9A3.5 3.5 0 0 1 20 5.5v7A3.5 3.5 0 0 1 16.5 16h-5.1l-4.6 4.1A1.1 1.1 0 0 1 5 19.3V16.2A3.5 3.5 0 0 1 4 13.8z" />
    </svg>
  </button>

  <!-- CHAT DRAWER ASIDE -->
  <aside class="chat-card" id="chatCard" aria-label="Sentinel Guard AI Assistant" aria-hidden="true">
    <header class="chat-header">
      <div>
        <p>Sentinel Guard AI</p>
        <span id="chatStatus">● Autonomous Capital & Platform Intelligence</span>
      </div>
      <button id="chatClose" type="button" aria-label="Close chat">&times;</button>
    </header>

    <div class="messages" id="messages">
      <article class="message assistant">
        <div class="message-meta">
          <span class="message-avatar">AI</span>
          <span class="message-label">Sentinel AI Assistant</span>
        </div>
        <div class="message-body">
          <p>Hello! I am your <strong>Sentinel Guard AI</strong> assistant. Ask me anything about our automated arbitrage scanner, anti-MEV front-running defense shield, ROI tiers, or project roadmap.</p>
        </div>
      </article>
    </div>

    <div class="chat-suggestions">
      <button class="chat-chip" type="button" data-question="How does Sentinel Guard AI protect trades and prevent slippage?">Capital Protection</button>
      <button class="chat-chip" type="button" data-question="What are the monthly ROI tiers and benefits?">ROI & Plans</button>
      <button class="chat-chip" type="button" data-question="Explain Quantum Arbitrage in simple words.">Arbitrage Guide</button>
    </div>

    <form class="chat-form" id="chatForm">
      <textarea id="questionInput" rows="2" placeholder="Ask Sentinel Guard AI anything..." aria-label="Question"></textarea>
      <button id="sendButton" type="submit" aria-label="Send question">
        <svg viewBox="0 0 24 24" aria-hidden="true">
          <path d="M3.7 20.3 21 12 3.7 3.7 3 10.3l10 1.7-10 1.7z" />
        </svg>
      </button>
    </form>
  </aside>

  <!-- PDF CHATBOT SCRIPT (front_assets/script.js) -->
  <script src="{{ asset('front_assets/script.js') }}"></script>

  <script>
    function openChatDrawer(e) {
      if (e) {
        e.preventDefault();
        e.stopPropagation();
      }
      const chatToggle = document.getElementById('chatToggle');
      const chatCard = document.getElementById('chatCard');
      const questionInput = document.getElementById('questionInput');

      if (chatCard) {
        if (!chatCard.classList.contains('open')) {
          if (chatToggle) {
            chatToggle.click();
          } else {
            chatCard.classList.add('open');
            chatCard.setAttribute('aria-hidden', 'false');
          }
        }
        setTimeout(() => {
          if (questionInput) questionInput.focus();
        }, 260);
      }
    }

    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('.open-ai-chat-trigger').forEach(el => {
        el.addEventListener('click', openChatDrawer);
      });
    });
  </script>

  <script>
      (function () {
        var els = document.querySelectorAll('[data-aos]');
        if (!('IntersectionObserver' in window)) { document.documentElement.classList.remove('jsr'); return; }
        var io = new IntersectionObserver(function (es) { es.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } }); }, { threshold: .12, rootMargin: '0px 0px -8% 0px' });
        els.forEach(function (el) { io.observe(el); });
        setTimeout(function () { els.forEach(function (el) { el.classList.add('in'); }); }, 2500);
      })();
    const $ = id => document.getElementById(id);
    const money = n => '$' + Math.round(n).toLocaleString('en-US');

    /* mobile menu */
    $('burger').onclick = () => { const m = $('mob'); m.style.display = m.style.display === 'flex' ? 'none' : 'flex'; };
    document.querySelectorAll('#mob a').forEach(a => a.onclick = () => $('mob').style.display = 'none');

    /* 3D tilt */
    document.querySelectorAll('[data-tilt]').forEach(el => {
      el.classList.add('tilt');
      el.addEventListener('mousemove', e => {
        const r = el.getBoundingClientRect();
        const px = (e.clientX - r.left) / r.width - 0.5, py = (e.clientY - r.top) / r.height - 0.5;
        el.style.transform = `perspective(800px) rotateY(${px * 10}deg) rotateX(${-py * 10}deg) translateY(-4px)`;
      });
      el.addEventListener('mouseleave', () => { el.style.transform = ''; });
    });

    /* ROI calculator */
    const tiers = [[100, 1000, 10], [1100, 5000, 12], [5100, 10000, 14], [10100, Infinity, 15]];
    const cr = $('calcRange');
    function calc() {
      const a = +cr.value;
      let pct = 10; for (const [lo, hi, p] of tiers) { if (a >= lo && a <= hi) { pct = p; break; } }
      $('calcAmt').textContent = money(a);
      $('calcTier').textContent = pct + '%';
      $('calcMonth').textContent = money(a * pct / 100);
      $('calcYear').textContent = money(a * pct / 100 * 12);
    }
    cr.addEventListener('input', calc); calc();

    /* counters */
    document.querySelectorAll('[data-count]').forEach(el => {
      const o = new IntersectionObserver(es => es.forEach(e => {
        if (e.isIntersecting) {
          const t = +el.dataset.count, sfx = el.dataset.suffix || ''; let c = 0, s = t / 40;
          const iv = setInterval(() => { c += s; if (c >= t) { c = t; clearInterval(iv); } el.textContent = Math.floor(c) + sfx; }, 22);
          o.unobserve(el);
        }
      }), { threshold: .6 }); o.observe(el);
    });

    /* live arbitrage prices */
    const PAIRS = [
      { s: 'BTC', n: 'Bitcoin', p: 62450, col: '#F7931A', i: 'fa-brands fa-bitcoin' },
      { s: 'ETH', n: 'Ethereum', p: 3150, col: '#627EEA', i: 'fa-brands fa-ethereum' },
      { s: 'SOL', n: 'Solana', p: 172, col: '#9945FF', i: 'fa-solid fa-bolt' },
      { s: 'BNB', n: 'BNB', p: 596, col: '#F0B90B', i: 'fa-solid fa-b' },
      { s: 'XRP', n: 'XRP', p: 0.62, col: '#22C55E', i: 'fa-solid fa-x' },
    ];
    const EXA = ['Binance', 'OKX', 'Bybit', 'Kraken'], EXB = ['Coinbase', 'Kucoin', 'Gate.io', 'Bybit'];
    const fmt = n => n >= 100 ? n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : n.toLocaleString('en-US', { minimumFractionDigits: 4, maximumFractionDigits: 4 });
    let live = false;
    async function loadPrices() {
      try {
        const r = await fetch('https://api.binance.com/api/v3/ticker/price?symbols=%5B%22BTCUSDT%22,%22ETHUSDT%22,%22SOLUSDT%22,%22BNBUSDT%22,%22XRPUSDT%22%5D');
        if (!r.ok) throw 0; const d = await r.json(); const m = {}; d.forEach(x => m[x.symbol] = +x.price);
        PAIRS.forEach(p => { if (m[p.s + 'USDT']) p.p = m[p.s + 'USDT']; });
        live = true; if ($('liveSrc')) $('liveSrc').textContent = 'Live prices · Binance';
      } catch (e) { live = false; if ($('liveSrc')) $('liveSrc').textContent = 'Live feed · demo data'; }
    }
    function renderArb() {
      const body = $('arbBody'); if (!body) return; let h = '';
      PAIRS.forEach((p, i) => {
        if (!live) p.p *= 1 + (Math.random() - .5) * .001;
        const spread = 0.10 + Math.random() * 0.65;
        const a = p.p, b = p.p * (1 + spread / 100);
        const prof = (spread / 100 * 10000).toFixed(2);
        h += `<tr><td><div class="flex items-center gap-3"><span class="w-8 h-8 rounded-full grid place-items-center text-black text-[13px]" style="background:${p.col}"><i class="${p.i}"></i></span><div><div class="head font-semibold">${p.s}/USDT</div><div class="text-[12px]" style="color:var(--mut)">${p.n}</div></div></div></td>` +
          `<td class="mono"><span style="color:var(--mut)">${EXA[i % EXA.length]}</span> · $${fmt(a)}</td>` +
          `<td class="mono"><span style="color:var(--mut)">${EXB[i % EXB.length]}</span> · $${fmt(b)}</td>` +
          `<td class="mono font-bold" style="color:var(--green)">+${spread.toFixed(2)}%</td>` +
          `<td class="mono font-bold gtext">+$${prof}</td></tr>`;
      });
      body.innerHTML = h;
      // hero card + ticker sync
      const btc = PAIRS[0].p, s = btc * 1.0037;
      if ($('buyP')) $('buyP').textContent = '$' + fmt(btc);
      if ($('sellP')) $('sellP').textContent = '$' + fmt(s);
      if ($('profP')) $('profP').textContent = '$' + (s - btc).toFixed(2);
    }
    function buildTicker() {
      const one = PAIRS.map(p => `<span class="tk"><b>${p.s}</b> $${fmt(p.p)} <span class="up">▲ spread +${(0.1 + Math.random() * 0.6).toFixed(2)}%</span></span>`).join('');
      $('ticker').innerHTML = one + one;
    }
    loadPrices().then(() => { renderArb(); buildTicker(); });
    renderArb(); buildTicker();
    setInterval(renderArb, 2400);
    setInterval(buildTicker, 4200);
    setInterval(loadPrices, 20000);

    /* ===== 3D hero: arbitrage network globe ===== */
    (function () {
      if (!window.THREE) return;
      if (window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches) return;
      const canvas = $('bg3d'); if (!canvas) return;
      let renderer;
      try { renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true }); } catch (e) { return; }
      renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
      const scene = new THREE.Scene();
      const camera = new THREE.PerspectiveCamera(55, 1, 0.1, 100);
      camera.position.z = 6.2;

      const grp = new THREE.Group(); scene.add(grp);
      const R = 2.5;
      // point globe (fibonacci sphere)
      const N = 1500, pos = new Float32Array(N * 3);
      for (let i = 0; i < N; i++) {
        const y = 1 - (i / (N - 1)) * 2, r = Math.sqrt(1 - y * y), th = Math.PI * (3 - Math.sqrt(5)) * i;
        pos[i * 3] = Math.cos(th) * r * R; pos[i * 3 + 1] = y * R; pos[i * 3 + 2] = Math.sin(th) * r * R;
      }
      const g = new THREE.BufferGeometry(); g.setAttribute('position', new THREE.BufferAttribute(pos, 3));
      grp.add(new THREE.Points(g, new THREE.PointsMaterial({ color: 0x2BE86B, size: 0.028, transparent: true, opacity: .85 })));
      // wire sphere
      grp.add(new THREE.Mesh(new THREE.SphereGeometry(R * 0.985, 26, 26), new THREE.MeshBasicMaterial({ color: 0x0E7A3A, wireframe: true, transparent: true, opacity: .12 })));

      // orbiting exchange nodes + arbitrage arcs
      const nodes = [];
      for (let i = 0; i < 7; i++) {
        const m = new THREE.Mesh(new THREE.SphereGeometry(0.07, 16, 16), new THREE.MeshBasicMaterial({ color: 0xB6F24A }));
        grp.add(m);
        nodes.push({ m, ph: Math.random() * 6.28, sp: 0.15 + Math.random() * 0.3, rad: R + 0.35 + Math.random() * 0.7, tilt: (Math.random() - .5) * 1.4 });
      }
      // ambient dust
      const M = 260, dp = new Float32Array(M * 3);
      for (let i = 0; i < M; i++) { dp[i * 3] = (Math.random() - .5) * 16; dp[i * 3 + 1] = (Math.random() - .5) * 10; dp[i * 3 + 2] = (Math.random() - .5) * 8; }
      const dg = new THREE.BufferGeometry(); dg.setAttribute('position', new THREE.BufferAttribute(dp, 3));
      scene.add(new THREE.Points(dg, new THREE.PointsMaterial({ color: 0x2BE86B, size: 0.02, transparent: true, opacity: .35 })));

      let mx = 0, my = 0;
      window.addEventListener('mousemove', e => { mx = (e.clientX / innerWidth - .5); my = (e.clientY / innerHeight - .5); }, { passive: true });
      function resize() { const w = canvas.clientWidth || innerWidth, h = canvas.clientHeight || innerHeight; renderer.setSize(w, h, false); camera.aspect = w / h; camera.updateProjectionMatrix(); }
      resize(); window.addEventListener('resize', resize);
      let running = true; document.addEventListener('visibilitychange', () => { running = !document.hidden; });
      const clock = new THREE.Clock();
      (function loop() {
        requestAnimationFrame(loop); if (!running) return;
        const t = clock.getElapsedTime();
        grp.rotation.y = t * 0.07; grp.rotation.x = -0.28;
        nodes.forEach(n => { const a = n.ph + t * n.sp; n.m.position.set(Math.cos(a) * n.rad, Math.sin(a * 0.8 + n.tilt) * 1.1, Math.sin(a) * n.rad); });
        camera.position.x += ((mx * 1.3) - camera.position.x) * 0.04;
        camera.position.y += ((-my * 0.9) - camera.position.y) * 0.04;
        camera.lookAt(0, 0, 0);
        renderer.render(scene, camera);
      })();
    })();
  </script>
</body>

</html>