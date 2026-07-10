@extends('member.layouts.main')
@section('title', 'Dashboard')
@section('container')
    @php
        $referralUrl = config('detailsApp.url') . '/member/register/' . $data['memberid'];
    @endphp
    <style>
        .wave-container {
            position: relative;
            width: 100%;
            height: 80px;
            overflow: hidden;
        }

        .wave {
            fill: none;
            stroke: #00FF7F;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            stroke-dasharray: 100;
            animation: waveMove 2s infinite linear;
        }

        @keyframes waveMove {
            0% {
                stroke-dashoffset: 100;
            }

            100% {
                stroke-dashoffset: 0;
            }
        }
    </style>
    <style>
        /* Extra small devices (phones, 600px and down) */
        @media only screen and (max-width: 600px) {
            .noDataImg {
                height: 140px;
                width: 140px;
            }
        }

        /* Small devices (portrait tablets and large phones, 600px and up) */
        @media only screen and (min-width: 600px) {
            .noDataImg {
                height: 160px;
                width: 160px;
            }
        }

        /* Medium devices (landscape tablets, 768px and up) */
        @media only screen and (min-width: 768px) {
            .noDataImg {
                height: 220px;
                width: 220px;
            }
        }

        /* Large devices (laptops/desktops, 992px and up) */
        @media only screen and (min-width: 992px) {
            .noDataImg {
                height: 250px;
                width: 250px;
            }
        }

        /* Extra large devices (large laptops and desktops, 1200px and up) */
        @media only screen and (min-width: 1200px) {
            .noDataImg {
                height: 280px;
                width: 280px;
            }
        }
    </style>
    <style>
        /* Extra small devices (phones, 600px and down) */
        @media only screen and (max-width: 600px) {
            div:has(.custom-card):first-child {
                margin-top: 20px;
            }
        }

        /* Small devices (portrait tablets and large phones, 600px and up) */
        @media only screen and (min-width: 600px) {
            div:has(.custom-card):first-child {
                margin-top: 20px;
            }
        }

        /* Medium devices (landscape tablets, 768px and up) */
        @media only screen and (min-width: 768px) {
            div:has(.custom-card):first-child {
                margin-top: 20px;
            }
        }

        /* Large devices (laptops/desktops, 992px and up) */
        @media only screen and (min-width: 992px) {
            div:has(.custom-card):first-child {
                margin-top: 0px;
            }
        }

        /* Extra large devices (large laptops and desktops, 1200px and up) */
        @media only screen and (min-width: 1200px) {
            div:has(.custom-card):first-child {
                margin-top: 0px;
            }
        }
    </style>
    <style>
        /* Marquee container fix */
        .marquee-container {
            overflow: hidden;
            flex: 1 1 auto;
            min-width: 200px;
        }

        /* Web3 / Swap Minting Style Button */
        .swap-btn {
            background: linear-gradient(135deg, #0c4034, #00ffcc);
            color: #fff;
            padding: 12px 24px;
            border: none;
            border-radius: 999px;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: none;
            letter-spacing: 1px;
            box-shadow: 0 0 12px rgba(99, 102, 241, 0.5);
            transition: all 0.3s ease;
            position: relative;
            white-space: nowrap;
            z-index: 1;
            overflow: hidden;
        }

        /* Glow hover effect */
        .swap-btn::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, #3b82f6 0%, transparent 70%);
            opacity: 0;
            transition: opacity 0.4s ease;
            z-index: -1;
        }

        .swap-btn:hover::before {
            opacity: 0.3;
        }

        .swap-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.6);
        }
    </style>

    <svg class="member-electric-svg" aria-hidden="true" focusable="false">
        <defs>
            <filter id="member-wave-color" color-interpolation-filters="sRGB" x="-20%" y="-20%" width="140%"
                height="140%">
                <feTurbulence type="turbulence" baseFrequency="0.02" numOctaves="7" />
                <feColorMatrix type="hueRotate" result="pt1">
                    <animate attributeName="values" values="0;360;" dur=".6s" repeatCount="indefinite"
                        calcMode="paced" />
                </feColorMatrix>
                <feComposite />
                <feTurbulence type="turbulence" baseFrequency="0.03" numOctaves="7" seed="5" />
                <feColorMatrix type="hueRotate" result="pt2">
                    <animate attributeName="values" values="0; 333; 199; 286; 64; 168; 256; 157; 360;" dur="5s"
                        repeatCount="indefinite" calcMode="paced" />
                </feColorMatrix>
                <feBlend in="pt1" in2="pt2" mode="normal" result="combinedNoise" />
                <feDisplacementMap in="SourceGraphic" scale="30" xChannelSelector="R" yChannelSelector="B" />
            </filter>
        </defs>
    </svg>

    <!-- MAIN-CONTENT -->

    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="col-xm-12 mb-0">
                <!-- TradingView Widget BEGIN -->
                <div class="tradingview-widget-container mt-5">
                    <div class="tradingview-widget-container__widget"></div>
                    <div class="tradingview-widget-copyright"></div>
                    <script type="text/javascript" src="https://s3.tradingview.com/external-embedding/embed-widget-ticker-tape.js" async>
                        {
                            "symbols": [{
                                    "proName": "BITSTAMP:BTCUSD",
                                    "title": "Bitcoin"
                                },
                                {
                                    "proName": "BITSTAMP:ETHUSD",
                                    "title": "Ethereum"
                                },
                                {
                                    "description": "TRON",
                                    "proName": "BINANCE:TRXUSD.P"
                                },
                                {
                                    "description": "SOLUSDT",
                                    "proName": "BINANCE:SOLUSDT"
                                },
                                {
                                    "description": "",
                                    "proName": "BITSTAMP:XRPUSD"
                                },
                                {
                                    "description": "",
                                    "proName": "CRYPTOCAP:DOGE"
                                },
                                {
                                    "description": "",
                                    "proName": "CRYPTOCAP:ADA"
                                }
                            ],
                            "showSymbolLogo": true,
                            "isTransparent": false,
                            "displayMode": "adaptive",
                            "colorTheme": "dark",
                            "locale": "en"
                        }
                    </script>
                </div>
                <!-- TradingView Widget END -->
            </div>
            <div class="col-xl-12 col-md-12 mb-3">
                @if (session()->has('successMsg'))
                    <div class="alert alert-success alert-dismissible fade show custom-alert-icon shadow-sm"
                        role="alert">
                        <svg style="fill: rgb(var(--success-rgb));" xmlns="http://www.w3.org/2000/svg" height="1.5rem"
                            viewBox="0 0 24 24" width="1.5rem" fill="#000000">
                            <path d="M0 0h24v24H0z" fill="none" />
                            <path
                                d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                        </svg>
                        {{ session('successMsg') }}
                    </div>
                @endif
                @if (session()->has('failedMsg'))
                    <div class="alert alert-danger alert-dismissible fade show custom-alert-icon shadow-sm"
                        role="alert">
                        <svg style="fill: rgb(var(--danger-rgb));" style="color:red" xmlns="http://www.w3.org/2000/svg"
                            height="1.5rem" viewBox="0 0 24 24" width="1.5rem" fill="#000000">
                            <path d="M0 0h24v24H0z" fill="none" />
                            <path
                                d="M15.73 3H8.27L3 8.27v7.46L8.27 21h7.46L21 15.73V8.27L15.73 3zM12 17.3c-.72 0-1.3-.58-1.3-1.3 0-.72.58-1.3 1.3-1.3.72 0 1.3.58 1.3 1.3 0 .72-.58 1.3-1.3 1.3zm1-4.3h-2V7h2v6z" />
                        </svg>
                        {{ session('failedMsg') }}
                    </div>
                @endif
                <!--  <div class="d-flex justify-content-center">
                        <form action="{{ route('claimROI') }}" method="POST">
                            @csrf
                            <input type="hidden" name="memberid" value="{{ $data['memberid'] }}">
                            <button type="submit" class="btn swap-btn">
                                Claim Today's ROI
                            </button>
                        </form>
                    </div> -->
            </div>
            <section class="member-hero-panel mb-4">
                <div class="row g-4 align-items-center">
                    <div class="col-xl-7">
                        <span class="member-pill">
                            <i class="ti ti-sparkles"></i> Private Account Details
                        </span>
                        <h1 class="member-hero-title">Welcome back, {{ $data['memberid'] }}</h1>
                        <p class="member-soft-text fs-6 mb-0">
                            Track income, monitor team growth, manage deposits, and move quickly through your account
                            from one polished dashboard.
                        </p>
                        <div class="member-hero-metrics">
                            <div class="member-hero-metric">
                                <span>Direct Team</span>
                                <strong>{{ $data['downline'] }}</strong>
                            </div>
                            <div class="member-hero-metric">
                                <span>Total Withdrawal</span>
                                <strong>$ {{ totalWithdrawals($data['memberid']) }}</strong>
                            </div>
                            <div class="member-hero-metric">
                                <span>Deposit Wallet</span>
                                <strong>${{ $data['p2p_wallet'] }}</strong>
                            </div>
                        </div>
                        <div class="mt-3">
                            <span class="member-pill"><i class="ti ti-award"></i> Rank: {{ $data->rank }}</span>
                            <span class="member-pill"><i class="ti ti-shield-check"></i> Status:
                                {{ $data['status'] }}</span>
                            <span class="member-pill"><i class="ti ti-chart-candle"></i> Investment:
                                $ {{ $data['package'] }}</span>
                        </div>
                    </div>
                    <div class="col-xl-5">
                        <div class="member-hero-sidecard mb-3">
                            <div class="member-hero-sidecard__label">Capital Overview</div>
                            <div class="member-hero-sidecard__value">$ {{ number_format(totalIncome($data['memberid']) + $data['wallet'], 2) }}</div>
                            <div class="member-soft-text">Combined current wallet and earned income visibility</div>
                        </div>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="member-mini-stat">
                                    <div class="label">Wallet Balance</div>
                                    <div class="value">${{ $data['wallet'] }}</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="member-mini-stat">
                                    <div class="label">Total Income</div>
                                    <div class="value">$ {{ totalIncome($data['memberid']) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            @if (count($dashMsg) > 0)
                <div class="member-notice-ribbon mb-4">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="ti ti-speakerphone text-warning"></i>
                        <span class="fw-semibold">Latest notices</span>
                    </div>
                    <div class="member-notice-track">
                        <marquee behavior="scroll" direction="left" scrollamount="6" onmouseover="this.stop();" onmouseout="this.start();">
                            @foreach ($dashMsg as $item)
                                <span class="member-notice-chip">{{ $item['message'] }}</span>
                            @endforeach
                        </marquee>
                    </div>
                </div>
            @endif
            <section class="member-quick-actions mb-4">
                <div class="row g-3">
                    <div class="col-xl-4 col-md-6">
                        <a href="{{ url('/member/fund/import-fund') }}" class="member-quick-card">
                            <span class="member-quick-card__icon"><i class="ti ti-building-bank"></i></span>
                            <span class="member-quick-card__content">
                                <strong>Deposit Management</strong>
                                <small>Add new funds and review your deposit history.</small>
                            </span>
                            <span class="member-quick-card__arrow"><i class="ti ti-arrow-up-right"></i></span>
                        </a>
                    </div>
                    <div class="col-xl-4 col-md-6">
                        <a href="{{ url('/member/staking') }}" class="member-quick-card">
                            <span class="member-quick-card__icon"><i class="ti ti-chart-dots-3"></i></span>
                            <span class="member-quick-card__content">
                                <strong>Investment Center</strong>
                                <small>Create investment plans and monitor profit activity.</small>
                            </span>
                            <span class="member-quick-card__arrow"><i class="ti ti-arrow-up-right"></i></span>
                        </a>
                    </div>
                    <div class="col-xl-4 col-md-12">
                        <a href="{{ url('/member/profile/user-summary') }}" class="member-quick-card">
                            <span class="member-quick-card__icon"><i class="ti ti-user"></i></span>
                            <span class="member-quick-card__content">
                                <strong>Profile & Security</strong>
                                <small>Update account details and keep your member profile polished.</small>
                            </span>
                            <span class="member-quick-card__arrow"><i class="ti ti-arrow-up-right"></i></span>
                        </a>
                    </div>
                </div>
            </section>
            <div class="member-section-title">
                <h5>Account Snapshot</h5>
                <p class="member-soft-text mb-0">Your key balances, team stats, and earnings at a glance.</p>
            </div>
            <!-- DGTEK Swap Button -->
            {{-- <a href="https://dgtek-user.vercel.app/" target="_blank" class="swap-btn">
            🚀 DGTEK SWAP
        </a> --}}
            <br>
            <!-- Start:: row-1 -->
            <section class="member-dashboard-section member-dashboard-section--stats">
            <div class="row g-3">
                <div class="col-xl-3 col-md-6">
                    <div class="card custom-card text-center">
                        <div class="top-left"></div>
                        <div class="bottom-left"></div>
                        <div class="bottom-right"></div>
                        <div class="top-right"></div>
                        <div class="card-body py-3">

                            <i class="fas fa-trophy fa-3x text-warning mb-3"></i> <!-- Font Awesome trophy icon -->
                            <p class="card-title">Congratulations ! You have achieved the rank</p>
                            <h4 class="my-0"><strong>{{ $data->rank }}</strong></h4>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card custom-card">
                        <div class="top-left"></div>
                        <div class="bottom-left"></div>
                        <div class="bottom-right"></div>
                        <div class="top-right"></div>
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-top">
                                <div class="me-3 lh-1">
                                    <span class="avatar avatar-lg bg-info-transparent svg-info">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="icon icon-tabler icons-tabler-outline icon-tabler-users-group">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M10 13a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                                            <path d="M8 21v-1a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v1" />
                                            <path d="M15 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                                            <path d="M17 10h2a2 2 0 0 1 2 2v1" />
                                            <path d="M5 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                                            <path d="M3 13v-1a2 2 0 0 1 2 -2h2" />
                                        </svg>
                                    </span>
                                </div>
                                <div>
                                    <p class="mb-1 ">Memberid</p>
                                    <h5 class="fw-semibold mb-2"> {{ $data['memberid'] }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card custom-card">
                        <div class="top-left"></div>
                        <div class="bottom-left"></div>
                        <div class="bottom-right"></div>
                        <div class="top-right"></div>
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-top">
                                <div class="me-3 lh-1">
                                    <span class="avatar avatar-lg bg-info-transparent svg-info">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="icon icon-tabler icons-tabler-outline icon-tabler-users-group">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M10 13a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                                            <path d="M8 21v-1a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v1" />
                                            <path d="M15 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                                            <path d="M17 10h2a2 2 0 0 1 2 2v1" />
                                            <path d="M5 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                                            <path d="M3 13v-1a2 2 0 0 1 2 -2h2" />
                                        </svg>
                                    </span>
                                </div>
                                <div>
                                    <p class="mb-1 ">Status</p>
                                    <h5 class="fw-semibold mb-2"> {{ $data['status'] }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card custom-card">
                        <div class="top-left"></div>
                        <div class="bottom-left"></div>
                        <div class="bottom-right"></div>
                        <div class="top-right"></div>
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-top">
                                <div class="me-3 lh-1">
                                    <span class="avatar avatar-lg bg-info-transparent svg-info">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="icon icon-tabler icons-tabler-outline icon-tabler-users-group">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M10 13a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                                            <path d="M8 21v-1a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v1" />
                                            <path d="M15 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                                            <path d="M17 10h2a2 2 0 0 1 2 2v1" />
                                            <path d="M5 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                                            <path d="M3 13v-1a2 2 0 0 1 2 -2h2" />
                                        </svg>
                                    </span>
                                </div>
                                <div>
                                    <p class="mb-1 ">Investment</p>
                                    <h5 class="fw-semibold mb-2">$ {{ $data['package'] }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </section>
            <!-- End:: row-1 -->
            <section class="member-dashboard-section member-dashboard-section--performance">
            <div class="row g-3">
                <div class="col-12 col-md-6 col-xl-4 d-flex">
                    <article class="member-electric-card" data-variant="red">
                        <div class="member-electric-card__inner">
                            <div class="member-electric-card__border">
                                <div class="member-electric-card__core"></div>
                            </div>
                            <div class="member-electric-card__glow member-electric-card__glow--1"></div>
                            <div class="member-electric-card__glow member-electric-card__glow--2"></div>
                        </div>
                        <div class="member-electric-card__backdrop"></div>
                        <div class="member-electric-card__content">
                            <div class="member-electric-card__top">
                                <span class="member-electric-card__tag">Income</span>
                                <h3 class="member-electric-card__title">Total Income</h3>
                            </div>
                            <hr class="member-electric-card__divider">
                            <div class="member-electric-card__bottom">
                                <p class="member-electric-card__value">$ {{ totalIncome($data['memberid']) }}</p>
                            </div>
                        </div>
                    </article>
                </div>
                <div class="col-12 col-md-6 col-xl-4 d-flex">
                    <article class="member-electric-card" data-variant="blue">
                        <div class="member-electric-card__inner">
                            <div class="member-electric-card__border">
                                <div class="member-electric-card__core"></div>
                            </div>
                            <div class="member-electric-card__glow member-electric-card__glow--1"></div>
                            <div class="member-electric-card__glow member-electric-card__glow--2"></div>
                        </div>
                        <div class="member-electric-card__backdrop"></div>
                        <div class="member-electric-card__content">
                            <div class="member-electric-card__top">
                                <span class="member-electric-card__tag">Income</span>
                                <h3 class="member-electric-card__title">Staking Income</h3>
                            </div>
                            <hr class="member-electric-card__divider">
                            <div class="member-electric-card__bottom">
                                <p class="member-electric-card__value">$ {{ totalStakingIncome($data['memberid']) }}</p>
                            </div>
                        </div>
                    </article>
                </div>
                <div class="col-12 col-md-6 col-xl-4 d-flex">
                    <article class="member-electric-card" data-variant="green">
                        <div class="member-electric-card__inner">
                            <div class="member-electric-card__border">
                                <div class="member-electric-card__core"></div>
                            </div>
                            <div class="member-electric-card__glow member-electric-card__glow--1"></div>
                            <div class="member-electric-card__glow member-electric-card__glow--2"></div>
                        </div>
                        <div class="member-electric-card__backdrop"></div>
                        <div class="member-electric-card__content">
                            <div class="member-electric-card__top">
                                <span class="member-electric-card__tag">Income</span>
                                <h3 class="member-electric-card__title">Level Income</h3>
                            </div>
                            <hr class="member-electric-card__divider">
                            <div class="member-electric-card__bottom">
                                <p class="member-electric-card__value">$ {{ totalLevelIncome($data['memberid']) }}</p>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
            </section>
            <!-- Start:: row-4 -->
            <div class="row">
                <div class="col-lg-12 mb-3">
                    <div class="member-referral-banner" role="alert">
                        <span id="code">{{ $referralUrl }}</span>
                        <span id="copyButtonId" class="float-end">
                            <i class="mdi mdi-content-copy me-1"></i>
                            <span id="msgText">Copy Text</span>
                        </span>
                    </div>
                </div>
            </div>
            <!-- ENd:: row-4 -->
        </div>
    </div>
    <!-- End::app-content -->

    <!-- END MAIN-CONTENT -->
    @if (session()->has('dashImgModel1'))
        <div class="modal fade" id="myModal" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1"
            aria-labelledby="staticBackdropLabel" aria-hidden="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title" id="staticBackdropLabel"> Dashboard
                        </h6>
                        <button type="button" class="btn-close clearSession" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <img src="{{ asset('admin_assets/images/dash-img') }}/{{ $imgData['image'] }}"
                            style="width:100%">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary clearSession"
                            data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <script>
            $(document).ready(function() {
                $('#myModal').modal('show');

                $('.clearSession').on('click', function() {
                    {{ session()->forget('dashImgModel1') }};
                })
                
                //referal link
                $('#copyButtonId').on('click', function() {
                    var text = $('#code').text()
                    var $temp = $("<input>");
                    $("body").append($temp);
                    $temp.val(text).select();
                    document.execCommand("copy");
                    $temp.remove();
                    $('#msgText').html('Text Copied');
                    $('#rcode').show().delay(3000).fadeOut();
                });
            });
        </script>
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    @endif
@endsection
