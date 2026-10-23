@extends('member.layouts.main')
@section('title', 'Dashboard')
@section('container')
    @php
        $referralUrl = config('detailsApp.url') . 'member/register/' . data_get($data, 'memberid');
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
            <filter id="member-wave-color" color-interpolation-filters="sRGB" x="-20%" y="-20%" width="140%" height="140%">
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
                    <div class="alert alert-success alert-dismissible fade show custom-alert-icon shadow-sm" role="alert">
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
                    <div class="alert alert-danger alert-dismissible fade show custom-alert-icon shadow-sm" role="alert">
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
                                <strong>$ {{ number_format(totalWithdrawals($data['memberid']), 2) }}</strong>
                            </div>
                            <div class="member-hero-metric">
                                <span>Deposit Wallet</span>
                                <strong>${{ number_format($data['p2p_wallet'], 2) }}</strong>
                            </div>
                        </div>
                        <div class="mt-3">
                            <span class="member-pill"><i class="ti ti-award"></i> Rank: {{ $data->rank }}</span>
                            <span class="member-pill"><i class="ti ti-shield-check"></i> Status:
                                {{ $data['status'] }}</span>
                            <span class="member-pill"><i class="ti ti-chart-candle"></i> Investment:
                                $ {{ number_format($data['self_biz'], 2) }}</span>
                        </div>
                    </div>
                    <div class="col-xl-5">
                        <div class="member-hero-sidecard mb-3">
                            <div class="member-hero-sidecard__label">Capital Overview</div>
                            <div class="member-hero-sidecard__value">$
                                {{ number_format(totalIncome($data['memberid']) + $data['wallet'], 2) }}</div>
                            <div class="member-soft-text">Combined current wallet and earned income visibility</div>
                        </div>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="member-mini-stat">
                                    <div class="label">Wallet Balance</div>
                                    <div class="value">${{ number_format($data['wallet'], 2) }}</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="member-mini-stat">
                                    <div class="label">Total Income</div>
                                    <div class="value">$ {{ number_format(totalIncome($data['memberid']), 2) }}</div>
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
                        <marquee behavior="scroll" direction="left" scrollamount="6" onmouseover="this.stop();"
                            onmouseout="this.start();">
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
            <section class="member-dashboard-section member-dashboard-section--stats mb-4">
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6 col-xl-4 d-flex">
                        <div class="card custom-card text-center h-100 w-100 mb-0">
                            <div class="top-left"></div>
                            <div class="bottom-left"></div>
                            <div class="bottom-right"></div>
                            <div class="top-right"></div>
                            <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                                <i class="fas fa-trophy fa-3x text-warning mb-3"></i> <!-- Font Awesome trophy icon -->
                                <p class="card-title mb-1">Congratulations ! You have achieved the rank</p>
                                <h4 class="my-0"><strong>{{ $data->rank }}</strong></h4>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-xl-4 d-flex">
                        <div class="card custom-card h-100 w-100 mb-0">
                            <div class="top-left"></div>
                            <div class="bottom-left"></div>
                            <div class="bottom-right"></div>
                            <div class="top-right"></div>
                            <div class="card-body d-flex align-items-center">
                                <div class="d-flex align-items-center w-100">
                                    <div class="me-3 lh-1 flex-shrink-0">
                                        <span class="avatar avatar-lg bg-info-transparent svg-info">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
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
                                    <div class="flex-grow-1 min-w-0">
                                        <p class="mb-1">Memberid</p>
                                        <h5 class="fw-semibold mb-0 text-truncate">{{ $data['memberid'] }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-xl-4 d-flex">
                        <div class="card custom-card h-100 w-100 mb-0">
                            <div class="top-left"></div>
                            <div class="bottom-left"></div>
                            <div class="bottom-right"></div>
                            <div class="top-right"></div>
                            <div class="card-body d-flex align-items-center">
                                <div class="d-flex align-items-center w-100">
                                    <div class="me-3 lh-1 flex-shrink-0">
                                        <span class="avatar avatar-lg bg-info-transparent svg-info">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
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
                                    <div class="flex-grow-1 min-w-0">
                                        <p class="mb-1">Status</p>
                                        <h5 class="fw-semibold mb-0 text-truncate">{{ $data['status'] }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- End:: row-1 -->
            <section class="member-dashboard-section member-dashboard-section--stats mb-4">
                 <div class="row g-3">
                    <div class="col-12 col-md-6 col-xl-6 d-flex mt-3">
                        <div class="card custom-card h-100 w-100 mb-0">
                            <div class="top-left"></div>
                            <div class="bottom-left"></div>
                            <div class="bottom-right"></div>
                            <div class="top-right"></div>
                            <div class="card-body d-flex align-items-center">
                                <div class="d-flex align-items-center w-100">
                                    <div class="me-3 lh-1 flex-shrink-0">
                                        <span class="avatar avatar-lg bg-info-transparent svg-info">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
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
                                    <div class="flex-grow-1 min-w-0">
                                        <p class="mb-1">Investment</p>
                                        <h5 class="fw-semibold mb-0 text-truncate">$ {{ number_format($data['self_biz'], 2) }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-xl-6 d-flex ">
                        <div class="card custom-card h-100 w-100 mb-0 mt-3">
                            <div class="top-left"></div>
                            <div class="bottom-left"></div>
                            <div class="bottom-right"></div>
                            <div class="top-right"></div>
                            <div class="card-body d-flex align-items-center">
                                <div class="d-flex align-items-center w-100">
                                    <div class="me-3 lh-1 flex-shrink-0">
                                        <span class="avatar avatar-lg bg-info-transparent svg-info">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
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
                                    <div class="flex-grow-1 min-w-0">
                                        <p class="mb-1">Team Biz</p>
                                        <h5 class="fw-semibold mb-0 text-truncate">$ {{ number_format(($data['team_biz'] - $data['self_biz']), 2) }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- START:: Capping Meter Section -->
            @php
                $cappingStateClass = 'capping-normal';
                $cappingBadgeClass = 'member-capping-badge--normal';
                $cappingStatusText = 'Active / Safe';

                if ($cappingPercentage >= 100) {
                    $cappingStateClass = 'capping-reached';
                    $cappingBadgeClass = 'member-capping-badge--reached';
                    $cappingStatusText = 'Capping Reached';
                } elseif ($cappingPercentage >= 90) {
                    $cappingStateClass = 'capping-danger';
                    $cappingBadgeClass = 'member-capping-badge--danger';
                    $cappingStatusText = 'Critical (90%+)';
                } elseif ($cappingPercentage >= 70) {
                    $cappingStateClass = 'capping-warning';
                    $cappingBadgeClass = 'member-capping-badge--warning';
                    $cappingStatusText = 'Approaching Cap';
                }
            @endphp
            <section class="member-capping-section mb-4">
                <div class="member-capping-card">
                    <div class="member-capping-card__header">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="member-pill">
                                <i class="ti ti-gauge"></i> Capping Meter
                            </span>
                            <span class="member-capping-status-badge {{ $cappingBadgeClass }}">
                                <i
                                    class="ti {{ $cappingPercentage >= 100 ? 'ti-alert-octagon' : ($cappingPercentage >= 90 ? 'ti-alert-triangle' : ($cappingPercentage >= 70 ? 'ti-alert-circle' : 'ti-shield-check')) }}"></i>
                                {{ $cappingStatusText }}
                            </span>
                        </div>
                        <div class="member-capping-percentage-text">
                            <span class="fs-6 member-soft-text">Used:</span>
                            <strong>{{ number_format($cappingPercentage, 2) }}%</strong>
                        </div>
                    </div>

                    <div class="member-capping-progress-wrap my-3">
                        <div class="progress member-capping-progress">
                            <div class="progress-bar member-capping-bar {{ $cappingStateClass }}" role="progressbar"
                                style="width: {{ $cappingPercentage }}%;"
                                aria-valuenow="{{ number_format($cappingPercentage, 2, '.', '') }}" aria-valuemin="0"
                                aria-valuemax="100" aria-label="Income Capping Meter">
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 member-capping-metrics">
                        <div class="col-6 col-md-4">
                            <div class="member-capping-metric-item">
                                <span class="label"><i class="ti ti-chart-pie me-1"></i> Capping Used</span>
                                <strong class="value text-warning">$ {{ number_format($achievedCapping, 2) }}</strong>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="member-capping-metric-item">
                                <span class="label"><i class="ti ti-shield-dollar me-1"></i> Total Capping</span>
                                <strong class="value text-info">$ {{ number_format($totalCapping, 2) }}</strong>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="member-capping-metric-item">
                                <span class="label"><i class="ti ti-hourglass-empty me-1"></i> Remaining</span>
                                <strong class="value {{ $cappingPercentage >= 100 ? 'text-danger' : 'text-success' }}">$
                                    {{ number_format($remainingCapping, 2) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- END:: Capping Meter Section -->
            <section class="member-dashboard-section member-dashboard-section--performance">
                <div class="row g-3">
                    <div class="col-12 col-md-12 col-xl-12 d-flex">
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
                                    <p class="member-electric-card__value">$ {{ number_format(totalIncome($data['memberid']), 2) }}</p>
                                </div>
                            </div>
                        </article>
                    </div>
                    <div class="col-12 col-md-6 col-xl-6 d-flex">
                        <article class="member-electric-card" data-variant="cyan">
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
                                    <h3 class="member-electric-card__title">Direct Income</h3>
                                </div>
                                <hr class="member-electric-card__divider">
                                <div class="member-electric-card__bottom">
                                    <p class="member-electric-card__value">$ {{ number_format(totalDirectIncome($data['memberid']), 2) }}
                                    </p>
                                </div>
                            </div>
                        </article>
                    </div>
                    <div class="col-12 col-md-6 col-xl-6 d-flex">
                        <article class="member-electric-card" data-variant="violet">
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
                                    <h3 class="member-electric-card__title">Reward Income</h3>
                                </div>
                                <hr class="member-electric-card__divider">
                                <div class="member-electric-card__bottom">
                                    <p class="member-electric-card__value">$ {{ number_format(totalRewardIncome($data['memberid']), 2) }}
                                    </p>
                                </div>
                            </div>
                        </article>
                    </div>
                    <div class="col-12 col-md-6 col-xl-6 d-flex">
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
                                    <p class="member-electric-card__value">$ {{ number_format(totalStakingIncome($data['memberid']), 2) }}
                                    </p>
                                </div>
                            </div>
                        </article>
                    </div>
                    <div class="col-12 col-md-6 col-xl-6 d-flex">
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
                                    <p class="member-electric-card__value">$ {{ number_format(totalLevelIncome($data['memberid']), 2) }}</p>
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
                            <span id="msgText" style="cursor:pointer">Copy Text</span>
                        </span>
                    </div>
                </div>
            </div>
            <!-- ENd:: row-4 -->
        </div>
    </div>
    <!-- End::app-content -->

    <!-- END MAIN-CONTENT -->
    <!-- DASHBOARD ANNOUNCEMENT POPUP OVERLAY -->
    @if (!empty($popupImage))
        <style>
            #arbCustomPopupOverlay {
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                right: 0 !important;
                bottom: 0 !important;
                width: 100vw !important;
                height: 100vh !important;
                background: rgba(4, 8, 16, 0.85) !important;
                backdrop-filter: blur(8px) !important;
                -webkit-backdrop-filter: blur(8px) !important;
                z-index: 99999999 !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                padding: 16px !important;
                box-sizing: border-box !important;
            }

            #arbCustomPopupOverlay.arb-popup-hidden {
                display: none !important;
            }

            .arb-popup-card {
                background: linear-gradient(145deg, #0b1320 0%, #0f1c2e 100%) !important;
                border: 1px solid rgba(0, 255, 204, 0.45) !important;
                box-shadow: 0 0 40px rgba(0, 255, 204, 0.25), 0 25px 50px rgba(0, 0, 0, 0.9) !important;
                border-radius: 16px !important;
                width: 100% !important;
                max-width: 620px !important;
                max-height: 90vh !important;
                display: flex !important;
                flex-direction: column !important;
                overflow: hidden !important;
                position: relative !important;
                z-index: 100000000 !important;
                animation: arbPopupScale 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards !important;
            }

            @keyframes arbPopupScale {
                0% {
                    transform: scale(0.92);
                    opacity: 0;
                }
                100% {
                    transform: scale(1);
                    opacity: 1;
                }
            }

            .arb-popup-header {
                border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
                padding: 14px 20px !important;
                background: rgba(15, 28, 46, 0.9) !important;
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
                flex-shrink: 0 !important;
            }

            .arb-popup-title {
                color: #00ffcc !important;
                font-weight: 700 !important;
                font-size: 1.15rem !important;
                letter-spacing: 0.5px !important;
                display: flex !important;
                align-items: center !important;
                gap: 8px !important;
                margin: 0 !important;
            }

            .arb-popup-close-btn {
                background: rgba(255, 255, 255, 0.12) !important;
                border: 1px solid rgba(255, 255, 255, 0.25) !important;
                color: #e2e8f0 !important;
                width: 34px !important;
                height: 34px !important;
                border-radius: 50% !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                cursor: pointer !important;
                transition: all 0.2s ease !important;
                font-size: 20px !important;
                line-height: 1 !important;
                padding: 0 !important;
                outline: none !important;
                user-select: none !important;
            }

            .arb-popup-close-btn:hover,
            .arb-popup-close-btn:focus {
                background: rgba(239, 68, 68, 0.9) !important;
                border-color: #ef4444 !important;
                color: #ffffff !important;
                transform: rotate(90deg) scale(1.08) !important;
            }

            .arb-popup-body {
                padding: 0 !important;
                text-align: center !important;
                background: #070d17 !important;
                overflow-y: auto !important;
                flex-grow: 1 !important;
                max-height: 65vh !important;
            }

            .arb-popup-body img {
                max-width: 100% !important;
                max-height: 60vh !important;
                width: auto !important;
                height: auto !important;
                object-fit: contain !important;
                display: block !important;
                margin: 0 auto !important;
            }

            .arb-popup-footer {
                border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
                padding: 14px 20px !important;
                background: rgba(15, 28, 46, 0.95) !important;
                display: flex !important;
                justify-content: flex-end !important;
                align-items: center !important;
                gap: 12px !important;
                flex-shrink: 0 !important;
            }

            .arb-popup-action-btn {
                background: linear-gradient(135deg, #0c4034, #00ffcc) !important;
                color: #0b1320 !important;
                font-weight: 700 !important;
                border: none !important;
                padding: 9px 22px !important;
                border-radius: 999px !important;
                text-decoration: none !important;
                transition: all 0.3s ease !important;
                box-shadow: 0 0 14px rgba(0, 255, 204, 0.4) !important;
                display: inline-flex !important;
                align-items: center !important;
                gap: 6px !important;
                cursor: pointer !important;
                font-size: 14px !important;
            }

            .arb-popup-action-btn:hover {
                transform: scale(1.04) !important;
                box-shadow: 0 0 20px rgba(0, 255, 204, 0.7) !important;
                color: #000000 !important;
            }

            .arb-popup-dismiss-btn {
                background: rgba(255, 255, 255, 0.12) !important;
                color: #e2e8f0 !important;
                border: 1px solid rgba(255, 255, 255, 0.25) !important;
                font-weight: 600 !important;
                padding: 9px 24px !important;
                border-radius: 999px !important;
                transition: all 0.2s ease !important;
                cursor: pointer !important;
                font-size: 14px !important;
                outline: none !important;
                user-select: none !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
            }

            .arb-popup-dismiss-btn:hover,
            .arb-popup-dismiss-btn:focus {
                background: rgba(0, 255, 204, 0.2) !important;
                border-color: #00ffcc !important;
                color: #ffffff !important;
                transform: scale(1.02) !important;
            }
        </style>

        <!-- Custom Standalone Popup Overlay (Outside any Stacking Context) -->
        <div id="arbCustomPopupOverlay" role="dialog" aria-modal="true">
            <div class="arb-popup-card" onclick="event.stopPropagation()">
                <div class="arb-popup-header">
                    <h6 class="arb-popup-title">
                        <i class="bx bx-bell" style="font-size: 20px;"></i>
                        <span>{{ !empty($popupTitle) ? $popupTitle : 'Welcome to QuantumARB' }}</span>
                    </h6>
                    <button type="button" class="arb-popup-close-btn" id="arbBtnCross" title="Close">
                        <i class="bx bx-x" style="pointer-events: none;"></i>
                    </button>
                </div>
                <div class="arb-popup-body">
                    @if (!empty($popupLink))
                        <a href="{{ $popupLink }}" target="_blank" rel="noopener noreferrer" title="Click to view">
                            <img src="{{ asset('uploads/' . $popupImage) }}" alt="{{ $popupTitle ?? 'Announcement' }}">
                        </a>
                    @else
                        <img src="{{ asset('uploads/' . $popupImage) }}" alt="{{ $popupTitle ?? 'Announcement' }}">
                    @endif
                </div>
                <div class="arb-popup-footer">
                    @if (!empty($popupLink))
                        <a href="{{ $popupLink }}" target="_blank" rel="noopener noreferrer" class="arb-popup-action-btn">
                            <i class="bx bx-link-external"></i> View Details
                        </a>
                    @endif
                    <button type="button" class="arb-popup-dismiss-btn" id="arbBtnDismiss">
                        Close & Continue
                    </button>
                </div>
            </div>
        </div>

        <script>
            (function() {
                function closeArbPopup() {
                    var overlay = document.getElementById('arbCustomPopupOverlay');
                    if (overlay) {
                        overlay.classList.add('arb-popup-hidden');
                        overlay.style.display = 'none';
                    }
                    document.body.style.removeProperty('overflow');
                }

                // Bind click to Cross button
                var btnCross = document.getElementById('arbBtnCross');
                if (btnCross) {
                    btnCross.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        closeArbPopup();
                    });
                }

                // Bind click to Dismiss button
                var btnDismiss = document.getElementById('arbBtnDismiss');
                if (btnDismiss) {
                    btnDismiss.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        closeArbPopup();
                    });
                }

                // Bind click to outside overlay
                var overlay = document.getElementById('arbCustomPopupOverlay');
                if (overlay) {
                    overlay.addEventListener('click', function(e) {
                        if (e.target === overlay) {
                            closeArbPopup();
                        }
                    });
                }

                // Bind Escape key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' || e.keyCode === 27) {
                        closeArbPopup();
                    }
                });

                // Prevent background scrolling while popup is open
                document.body.style.overflow = 'hidden';

                // Move overlay to document.body so no parent container can trap or dim it
                if (overlay && overlay.parentElement !== document.body) {
                    document.body.appendChild(overlay);
                }
            })();
        </script>
    @endif

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            // Referral Link Copy
            $('#copyButtonId').on('click', function() {
                var text = $('#code').text();
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
@endsection
