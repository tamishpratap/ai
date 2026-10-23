@extends('member.layouts.main')
@section('title', 'Reward Details')
@section('container')

    <!-- Responsive & Theme Color Styles for Member Panel -->
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

        .reward-stat-card {
            transition: transform 0.28s ease, border-color 0.28s ease, box-shadow 0.28s ease;
        }

        .reward-stat-card:hover {
            transform: translateY(-4px);
        }

        .reward-progress-track {
            height: 7px;
            background-color: rgba(255, 255, 255, 0.12);
            border-radius: 999px;
            overflow: hidden;
        }

        /* All Headings & Titles in Pure Bright White */
        .reward-page-title,
        .reward-card-title,
        .card-header .card-title,
        .reward-matrix-table th,
        .reward-rank-title,
        .reward-card-label {
            color: #ffffff !important;
        }

        .reward-page-title {
            color: #ffffff !important;
            font-weight: 700 !important;
            font-size: 1.35rem !important;
            letter-spacing: -0.01em;
        }

        .reward-page-subtitle {
            color: rgba(255, 255, 255, 0.75) !important;
            font-size: 0.85rem !important;
        }

        .reward-card-title {
            color: #ffffff !important;
            font-weight: 700 !important;
            font-size: 1.05rem !important;
        }

        .reward-card-label {
            color: rgba(255, 255, 255, 0.85) !important;
            font-weight: 600 !important;
            letter-spacing: 0.05em !important;
            text-transform: uppercase !important;
            font-size: 0.75rem !important;
        }

        .reward-card-sublabel {
            color: rgba(255, 255, 255, 0.65) !important;
            font-size: 0.75rem !important;
        }

        .reward-matrix-table,
        .reward-matrix-table th,
        .reward-matrix-table td {
            color: #ffffff !important;
        }

        .reward-matrix-table thead th {
            font-family: 'Space Grotesk', sans-serif !important;
            letter-spacing: 0.05em !important;
            text-transform: uppercase !important;
            font-size: 0.8rem !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            background: rgba(255, 255, 255, 0.06) !important;
            border-bottom: 1px solid rgba(var(--primary-rgb, 0, 255, 190), 0.3) !important;
            padding: 1.1rem 0.9rem !important;
        }

        .reward-matrix-table tbody td {
            padding: 1rem 0.9rem !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
            vertical-align: middle !important;
            color: #ffffff !important;
        }

        .reward-matrix-table tr:hover td {
            background-color: rgba(var(--primary-rgb, 0, 255, 190), 0.05) !important;
        }

        .reward-rank-title {
            color: #ffffff !important;
            font-weight: 700 !important;
            font-size: 0.95rem !important;
        }

        .reward-leg-value {
            color: #ffffff !important;
            font-weight: 700 !important;
            font-size: 0.88rem !important;
        }

        .reward-leg-target {
            color: rgba(255, 255, 255, 0.75) !important;
            font-weight: 400 !important;
            font-size: 0.82rem !important;
        }

        .member-theme-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.52rem 0.92rem;
            border-radius: 999px;
            background: rgba(var(--primary-rgb, 0, 255, 190), 0.08);
            border: 1px solid rgba(var(--primary-rgb, 0, 255, 190), 0.25);
            color: #fff;
            font-size: 0.85rem;
        }
    </style>

    <!-- MAIN-CONTENT -->
    <div class="main-content app-content">
        <div class="container-fluid">

            @php
                $memberid = $data['memberid'];
                $directs = DB::table('member_details')
                    ->where('sponsorid', $memberid)
                    ->orderBy('team_biz', 'desc')
                    ->get();
                
                $powerLegBiz = (int) ($directs->first()->team_biz ?? 0);
                $remainingDirects = $directs->slice(1);
                $weakerLegBiz = (int) ($remainingDirects->sum('team_biz') ?? 0);
                $totalTeamBiz = (int) ($data['team_biz'] ?? ($powerLegBiz + $weakerLegBiz));
                $currentRank = $data['rank'] ?? 'Member';

                // Tiers mapping from TempController::rewardGen()
                $tiers = [
                    [
                        'num' => 1,
                        'name' => 'Silver',
                        'power_req' => 5000,
                        'weaker_req' => 5000,
                        'total_req' => 10000,
                        'reward' => 125,
                        'status_level' => 1,
                    ],
                    [
                        'num' => 2,
                        'name' => 'Gold',
                        'power_req' => 20000,
                        'weaker_req' => 20000,
                        'total_req' => 40000,
                        'reward' => 375,
                        'status_level' => 2,
                    ],
                    [
                        'num' => 3,
                        'name' => 'Platinum',
                        'power_req' => 65000,
                        'weaker_req' => 65000,
                        'total_req' => 130000,
                        'reward' => 1125,
                        'status_level' => 3,
                    ],
                    [
                        'num' => 4,
                        'name' => 'Ruby',
                        'power_req' => 215000,
                        'weaker_req' => 215000,
                        'total_req' => 430000,
                        'reward' => 3750,
                        'status_level' => 4,
                    ],
                    [
                        'num' => 5,
                        'name' => 'Emerald',
                        'power_req' => 515000,
                        'weaker_req' => 515000,
                        'total_req' => 1030000,
                        'reward' => 7500,
                        'status_level' => 5,
                    ],
                    [
                        'num' => 6,
                        'name' => 'Sapphire',
                        'power_req' => 1415000,
                        'weaker_req' => 1415000,
                        'total_req' => 2830000,
                        'reward' => 22500,
                        'status_level' => 6,
                    ],
                    [
                        'num' => 7,
                        'name' => 'Blue Diamond',
                        'power_req' => 4115000,
                        'weaker_req' => 4115000,
                        'total_req' => 8230000,
                        'reward' => 67500,
                        'status_level' => 7,
                    ],
                    [
                        'num' => 8,
                        'name' => 'Crown Diamond',
                        'power_req' => 13115000,
                        'weaker_req' => 13115000,
                        'total_req' => 26230000,
                        'reward' => 225000,
                        'status_level' => 8,
                    ],
                    [
                        'num' => 9,
                        'name' => 'Regal Diamond',
                        'power_req' => 31115000,
                        'weaker_req' => 31115000,
                        'total_req' => 62230000,
                        'reward' => 450000,
                        'status_level' => 9,
                    ],
                    [
                        'num' => 10,
                        'name' => 'Imperial Crown Diamond',
                        'power_req' => 67115000,
                        'weaker_req' => 67115000,
                        'total_req' => 134230000,
                        'reward' => 900000,
                        'status_level' => 10,
                    ],
                ];
            @endphp

            <!-- Page Header / Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div>
                    <h4 class="reward-page-title mb-0 d-flex align-items-center gap-2" style="color: #ffffff !important;">
                        <i class="ri-trophy-line text-primary fs-22"></i>
                        {{ __('Reward Progression & Rank Details') }}
                    </h4>
                    <p class="reward-page-subtitle mb-0 mt-1" style="color: rgba(255, 255, 255, 0.75) !important;">
                        {{ config('detailsApp.name') }} &bull; Track your power & weaker leg business targets to unlock rank rewards
                    </p>
                </div>
                <div class="mt-2 mt-md-0 d-flex align-items-center gap-2 flex-wrap">
                    <div class="member-theme-pill m-0 px-3 py-2">
                        <i class="ri-vip-crown-line text-primary fs-15"></i>
                        <span class="fs-12 text-uppercase fw-semibold" style="color: rgba(255, 255, 255, 0.75) !important;">Current Rank:</span>
                        <strong class="text-primary fs-13">{{ $currentRank }}</strong>
                    </div>
                </div>
            </div>
            <!-- End Page Header -->

            <!-- Summary KPI Stats Cards -->
            <!--<div class="row g-3 mb-4">-->
            <!--    <div class="col-12 col-sm-6 col-xl-3">-->
            <!--        <div class="card custom-card reward-stat-card h-100 mb-0">-->
            <!--            <div class="top-left"></div>-->
            <!--            <div class="top-right"></div>-->
            <!--            <div class="bottom-left"></div>-->
            <!--            <div class="bottom-right"></div>-->
            <!--            <div class="card-body p-3 d-flex align-items-center justify-content-between">-->
            <!--                <div>-->
            <!--                    <span class="reward-card-label d-block" style="color: rgba(255, 255, 255, 0.85) !important;">Power Leg Business</span>-->
            <!--                    <h4 class="fw-bold text-primary mb-0 mt-1">$ {{ number_format($powerLegBiz) }}</h4>-->
            <!--                    <small class="reward-card-sublabel mt-1 d-block" style="color: rgba(255, 255, 255, 0.65) !important;">Main Leg Volume</small>-->
            <!--                </div>-->
            <!--                <div class="avatar avatar-md avatar-rounded bg-primary-transparent text-primary border border-primary-subtle">-->
            <!--                    <i class="ri-flashlight-line fs-20"></i>-->
            <!--                </div>-->
            <!--            </div>-->
            <!--        </div>-->
            <!--    </div>-->
            <!--    <div class="col-12 col-sm-6 col-xl-3">-->
            <!--        <div class="card custom-card reward-stat-card h-100 mb-0">-->
            <!--            <div class="top-left"></div>-->
            <!--            <div class="top-right"></div>-->
            <!--            <div class="bottom-left"></div>-->
            <!--            <div class="bottom-right"></div>-->
            <!--            <div class="card-body p-3 d-flex align-items-center justify-content-between">-->
            <!--                <div>-->
            <!--                    <span class="reward-card-label d-block" style="color: rgba(255, 255, 255, 0.85) !important;">Weaker Leg Business</span>-->
            <!--                    <h4 class="fw-bold text-info mb-0 mt-1">$ {{ number_format($weakerLegBiz) }}</h4>-->
            <!--                    <small class="reward-card-sublabel mt-1 d-block" style="color: rgba(255, 255, 255, 0.65) !important;">Other Legs Combined</small>-->
            <!--                </div>-->
            <!--                <div class="avatar avatar-md avatar-rounded bg-info-transparent text-info border border-info-subtle">-->
            <!--                    <i class="ri-pie-chart-2-line fs-20"></i>-->
            <!--                </div>-->
            <!--            </div>-->
            <!--        </div>-->
            <!--    </div>-->
            <!--    <div class="col-12 col-sm-6 col-xl-3">-->
            <!--        <div class="card custom-card reward-stat-card h-100 mb-0">-->
            <!--            <div class="top-left"></div>-->
            <!--            <div class="top-right"></div>-->
            <!--            <div class="bottom-left"></div>-->
            <!--            <div class="bottom-right"></div>-->
            <!--            <div class="card-body p-3 d-flex align-items-center justify-content-between">-->
            <!--                <div>-->
            <!--                    <span class="reward-card-label d-block" style="color: rgba(255, 255, 255, 0.85) !important;">Total Team Business</span>-->
            <!--                    <h4 class="fw-bold text-success mb-0 mt-1">$ {{ number_format($totalTeamBiz) }}</h4>-->
            <!--                    <small class="reward-card-sublabel mt-1 d-block" style="color: rgba(255, 255, 255, 0.65) !important;">Cumulative Team Biz</small>-->
            <!--                </div>-->
            <!--                <div class="avatar avatar-md avatar-rounded bg-success-transparent text-success border border-success-subtle">-->
            <!--                    <i class="ri-group-line fs-20"></i>-->
            <!--                </div>-->
            <!--            </div>-->
            <!--        </div>-->
            <!--    </div>-->
            <!--    <div class="col-12 col-sm-6 col-xl-3">-->
            <!--        <div class="card custom-card reward-stat-card h-100 mb-0">-->
            <!--            <div class="top-left"></div>-->
            <!--            <div class="top-right"></div>-->
            <!--            <div class="bottom-left"></div>-->
            <!--            <div class="bottom-right"></div>-->
            <!--            <div class="card-body p-3 d-flex align-items-center justify-content-between">-->
            <!--                <div>-->
            <!--                    <span class="reward-card-label d-block" style="color: rgba(255, 255, 255, 0.85) !important;">Max Reward </span>-->
            <!--                    <h4 class="fw-bold text-primary mb-0 mt-1"></h4>-->
            <!--                    <small class="reward-card-sublabel mt-1 d-block" style="color: rgba(255, 255, 255, 0.65) !important;">10 Ranks Total Rewards</small>-->
            <!--                </div>-->
            <!--                <div class="avatar avatar-md avatar-rounded bg-primary-transparent text-primary border border-primary-subtle">-->
            <!--                    <i class="ri-trophy-line fs-20"></i>-->
            <!--                </div>-->
            <!--            </div>-->
            <!--        </div>-->
            <!--    </div>-->
            <!--</div>-->

            <!-- Reward Table Matrix Card -->
            <div class="row">
                <div class="col-12">
                    <div class="card custom-card">
                        <div class="top-left"></div>
                        <div class="top-right"></div>
                        <div class="bottom-left"></div>
                        <div class="bottom-right"></div>
                        <div class="card-header justify-content-between align-items-center flex-wrap gap-2">
                            <div class="card-title reward-card-title d-flex align-items-center gap-2 mb-0" style="color: #ffffff !important;">
                                <i class="ri-list-check-2 text-primary fs-20"></i>
                                {{ __('Reward Ranks & Team Business Matrix') }}
                            </div>
                            <span class="badge bg-primary-transparent text-primary border border-primary-subtle px-3 py-2 fs-12 fw-semibold">
                                <i class="ri-shield-star-line me-1"></i> 10 Milestone Ranks
                            </span>
                        </div>
                        <div class="card-body p-0 p-sm-3">
                            <div class="table-responsive">
                                <table class="table text-nowrap align-middle table-hover mb-0 reward-matrix-table">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width: 50px; color: #ffffff !important;">#</th>
                                            <th style="min-width: 170px; color: #ffffff !important;">Rank Name</th>
                                            <th style="min-width: 220px; color: #ffffff !important;">Power Leg Req.</th>
                                            <th style="min-width: 220px; color: #ffffff !important;">Weaker Leg Req.</th>
                                            <th class="text-center" style="min-width: 170px; color: #ffffff !important;">Total Team Biz Req.</th>
                                            <th class="text-end" style="min-width: 150px; color: #ffffff !important;">Reward Amount</th>
                                            <th class="text-center" style="min-width: 130px; color: #ffffff !important;">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($tiers as $tier)
                                            @php
                                                $powerAchieved = min($powerLegBiz, $tier['power_req']);
                                                $powerPct = min(100, round(($powerAchieved / $tier['power_req']) * 100));

                                                $weakerAchieved = min($weakerLegBiz, $tier['weaker_req']);
                                                $weakerPct = min(100, round(($weakerAchieved / $tier['weaker_req']) * 100));

                                                $isAchieved = ($powerLegBiz >= $tier['power_req']) && 
                                                              ($weakerLegBiz >= $tier['weaker_req']) && 
                                                              ($totalTeamBiz >= $tier['total_req']);

                                                if (($data['rank_status'] ?? 0) >= $tier['status_level']) {
                                                    $isAchieved = true;
                                                }
                                            @endphp
                                            <tr>
                                                <td class="text-center">
                                                    <span class="avatar avatar-sm avatar-rounded bg-primary-transparent text-primary border border-primary-subtle fw-bold fs-12">
                                                        {{ $tier['num'] }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="avatar avatar-xs avatar-rounded bg-primary-transparent text-primary border border-primary-subtle">
                                                            <i class="ri-vip-crown-fill fs-12"></i>
                                                        </div>
                                                        <span class="reward-rank-title" style="color: #ffffff !important;">{{ $tier['name'] }}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                                        <span class="reward-leg-value" style="color: #ffffff !important;">$ {{ number_format($powerAchieved) }} <span class="reward-leg-target" style="color: rgba(255, 255, 255, 0.75) !important;">/ $ {{ number_format($tier['power_req']) }}</span></span>
                                                        <span class="badge bg-primary-transparent text-primary fs-11 fw-semibold">{{ $powerPct }}%</span>
                                                    </div>
                                                    <div class="progress reward-progress-track">
                                                        <div class="progress-bar bg-primary" role="progressbar" 
                                                             style="width: {{ $powerPct }}%; border-radius: 999px; background: linear-gradient(90deg, rgb(var(--primary-rgb, 0, 255, 190)), #0077ff) !important;" 
                                                             aria-valuenow="{{ $powerPct }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                                        <span class="reward-leg-value" style="color: #ffffff !important;">$ {{ number_format($weakerAchieved) }} <span class="reward-leg-target" style="color: rgba(255, 255, 255, 0.75) !important;">/ $ {{ number_format($tier['weaker_req']) }}</span></span>
                                                        <span class="badge bg-info-transparent text-info fs-11 fw-semibold">{{ $weakerPct }}%</span>
                                                    </div>
                                                    <div class="progress reward-progress-track">
                                                        <div class="progress-bar bg-info" role="progressbar" 
                                                             style="width: {{ $weakerPct }}%; border-radius: 999px; background: linear-gradient(90deg, rgb(72, 247, 238), #0077ff) !important;" 
                                                             aria-valuenow="{{ $weakerPct }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-primary-transparent text-primary border border-primary-subtle px-3 py-2 fs-12 fw-semibold">
                                                        $ {{ number_format($tier['total_req']) }}
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <span class="fw-bold text-primary fs-15 font-space-grotesk">$ {{ number_format($tier['reward']) }}</span>
                                                </td>
                                                <td class="text-center">
                                                    @if ($isAchieved)
                                                        <span class="badge bg-success-transparent text-success border border-success-subtle px-3 py-2 fs-12 fw-semibold d-inline-flex align-items-center gap-1 shadow-sm">
                                                            <i class="ri-checkbox-circle-fill fs-13"></i> Achieved
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary-transparent text-muted border border-secondary-subtle px-3 py-2 fs-12 fw-semibold d-inline-flex align-items-center gap-1">
                                                            <i class="ri-time-line fs-13"></i> Pending
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div> <!-- end col -->
            </div> <!-- end row -->

        </div>
    </div>
    <!-- END MAIN-CONTENT -->

@endsection
