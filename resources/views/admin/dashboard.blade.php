@extends('admin.layouts.main')
@section('title', 'Dashboard')
@section('content')
    <!-- push external head elements to head -->
    @push('head')
        <link rel="stylesheet" href="{{ asset('adm_assets/assets/plugins/weather-icons/css/weather-icons.min.css') }}">
        <link rel="stylesheet" href="{{ asset('adm_assets/assets/plugins/owl.carousel/dist/assets/owl.carousel.min.css') }}">
        <link rel="stylesheet" href="{{ asset('adm_assets/assets/plugins/owl.carousel/dist/assets/owl.theme.default.min.css') }}">
        <link rel="stylesheet" href="{{ asset('adm_assets/assets/plugins/chartist/dist/chartist.min.css') }}">
    @endpush
        <div class="container-fluid">
            <div class="row">
                <!-- page statustic chart start -->

                <div class="col-xl-3 col-md-6">
                    <div class="card card-red text-white">
                        <div class="card-block">
                            <div class="row align-items-center">
                                <div class="col-8">
                                    <h4 class="mb-0">{{ totalMembersadmin() }}</h4>
                                    <p class="mb-0">Total Members</p>
                                </div>
                                <div class="col-4 text-right">
                                    <i class="ik ik-shopping-cart f-30"></i>
                                </div>
                            </div>
                            <div id="Widget-line-chart1" class="chart-line chart-shadow"></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card card-blue text-white">
                        <div class="card-block">
                            <div class="row align-items-center">
                                <div class="col-8">
                                    <h4 class="mb-0">{{ totalActiveMembers() }}</h4>
                                    <p class="mb-0">Total Active </p>
                                </div>
                                <div class="col-4 text-right">
                                    <i class="ik ik-shopping-cart f-30"></i>
                                </div>
                            </div>
                            <div id="Widget-line-chart2" class="chart-line chart-shadow"></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card card-green text-white">
                        <div class="card-block">
                            <div class="row align-items-center">
                                <div class="col-8">
                                    <h4 class="mb-0">{{ totalTempMembers() }}</h4>
                                    <p class="mb-0">Total Inactive </p>
                                </div>
                                <div class="col-4 text-right">
                                    <i class="fas fa-cube f-30"></i>
                                </div>
                            </div>
                            <div id="Widget-line-chart3" class="chart-line chart-shadow"></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card card-yellow text-white">
                        <div class="card-block">
                            <div class="row align-items-center">
                                <div class="col-9">
                                    <h4 class="mb-0">{{ totalblockedMembers() }}</h4>
                                    <p class="mb-0">Total Blocked</p>
                                </div>
                                <div class="col-3 text-right">
                                    <i class="fas fa-cube f-30"></i>
                                </div>
                            </div>
                            <div id="Widget-line-chart4" class="chart-line chart-shadow"></div>
                        </div>
                    </div>
                </div>
                <!-------White Section-------->

                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="widget">
                        <div class="widget-body">
                            <h6>Today's Withdrawal</h6>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="state">
                                    <h4>${{ number_format(todaysWithdrawal(), 2) }}</h4>
                                </div>
                                <div class="icon">
                                    <i class="ik ik-mail"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="widget">
                        <div class="widget-body">
                            <h6>Total Withdrawal</h6>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="state">
                                    <h4>${{ number_format(totalWithdrawal(), 2) }}</h4>
                                </div>
                                <div class="icon">
                                    <i class="ik ik-zap"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="widget">
                        <div class="widget-body">
                            <h6>Todays Import Fund</h6>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="state">
                                    <h4>${{ number_format(todaysFundImported(), 2) }}</h4>
                                </div>
                                <div class="icon">
                                    <i class="ik ik-zap"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="widget">
                        <div class="widget-body">
                            <h6>Total Import Fund</h6>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="state">
                                    <h4>${{ number_format(totalFundImported(), 2) }}</h4>
                                </div>
                                <div class="icon">
                                    <i class="ik ik-zap"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-------White Section-------->
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <div class="widget">
                        <div class="widget-body">
                            <h6>Total In Wallet</h6>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="state">
                                    <h4>${{ number_format(totalinWallet(), 2) }}</h4>
                                </div>
                                <div class="icon">
                                    <i class="ik ik-award"></i>
                                </div>
                            </div>
                            <small class="text-small mt-10 d-block">Total In Wallet Balance</small>
                        </div>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-danger" role="progressbar" aria-valuenow="62" aria-valuemin="0"
                                aria-valuemax="100" style="width: 62%;"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <div class="widget">
                        <div class="widget-body">
                            <h6>Total In Deposit Wallet</h6>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="state">
                                    <h4>${{ number_format(totalinFundWallet(), 2) }}</h4>
                                </div>
                                <div class="icon">
                                    <i class="ik ik-thumbs-up"></i>
                                </div>
                            </div>
                            <small class="text-small mt-10 d-block">Total In Deposit Wallet Balance</small>
                        </div>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-success" role="progressbar" aria-valuenow="78" aria-valuemin="0"
                                aria-valuemax="100" style="width: 78%;"></div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-12">
                    <div class="card product-progress-card">
                        <div class="card-block">
                            <div class="row pp-main">
                                <div class="col-xl-12 col-md-6  mt-3">
                                    <div class="pp-cont">
                                        <div class="row align-items-center mb-20">
                                            <div class="row col-auto">
                                                <i class="fas fa-tag f-24 text-mute mx-2"></i>
                                                <h5 class="mb-0 mx-3">Total Income</h5>
                                            </div>
                                            <div class="col text-right">
                                                <h4 class="mb-0 text-blue">${{ number_format(totalInc(), 2) }}</h4>
                                            </div>
                                        </div>
                                        <div class="row align-items-center mb-15">
                                            <div class="col-auto">
                                                <p class="mb-0">Total Income</p>
                                            </div>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-blue" style="width:35%"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-md-6  mt-3">
                                    <div class="pp-cont">
                                        <div class="row align-items-center mb-20">
                                            <div class="row col-auto">
                                                <i class="fas fa-tag f-24 text-mute mx-2"></i>
                                                <h5 class="mb-0 mx-3">Staking Income</h5>
                                            </div>
                                            <div class="col text-right">
                                                <h4 class="mb-0 text-success">${{ number_format(totalStakingInc(), 2) }} </h4>
                                            </div>
                                        </div>
                                        <div class="row align-items-center mb-15">
                                            <div class="col-auto">
                                                <p class="mb-0">Total Staking Income</p>
                                            </div>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-success" style="width:27%"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-md-6  mt-3">
                                    <div class="pp-cont">
                                        <div class="row align-items-center mb-20">
                                            <div class="row col-auto">
                                                <i class="fas fa-tag f-24 text-mute mx-2"></i>
                                                <h5 class="mb-0 mx-3">Direct Income</h5>
                                            </div>
                                            <div class="col text-right">
                                                <h4 class="mb-0 text-yellow">${{ number_format(totalDirectInc(), 2) }} </h4>
                                            </div>
                                        </div>
                                        <div class="row align-items-center mb-15">
                                            <div class="col-auto">
                                                <p class="mb-0">Total Direct Income</p>
                                            </div>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-yellow" style="width:27%"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-md-6  mt-3">
                                    <div class="pp-cont">
                                        <div class="row align-items-center mb-20">
                                            <div class="row col-auto">
                                                <i class="fas fa-tag f-24 text-mute mx-2"></i>
                                                <h5 class="mb-0 mx-3">Reward Income</h5>
                                            </div>
                                            <div class="col text-right">
                                                <h4 class="mb-0 text-yellow">${{ number_format(totalRewardInc(), 2) }} </h4>
                                            </div>
                                        </div>
                                        <div class="row align-items-center mb-15">
                                            <div class="col-auto">
                                                <p class="mb-0">Total Reward Income</p>
                                            </div>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-yellow" style="width:27%"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-md-6 mt-3">
                                    <div class="pp-cont">
                                        <div class="row align-items-center mb-20">
                                            <div class="row col-auto">
                                                <i class="fas fa-tag f-24 text-mute mx-2"></i>
                                                <h5 class="mb-0 mx-3">Level Income</h5>
                                            </div>
                                            <div class="col text-right">
                                                <h4 class="mb-0 text-success">${{ number_format(totalLevelInc(), 2) }} </h4>
                                            </div>
                                        </div>
                                        <div class="row align-items-center mb-15">
                                            <div class="col-auto">
                                                <p class="mb-0">Total Level Income</p>
                                            </div>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-success" style="width:44%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6">
                    <div class="card card-red text-white">
                        <div class="card-block">
                            <div class="row align-items-center">
                                <div class="col-9">
                                    <h4 class="mb-0">$ {{ totalgrossAmount() }}</h4>
                                    <p class="mb-0">Withdrawal Gross Amount</p>
                                </div>
                                <div class="col-3 text-right">
                                    <i class="ik ik-user f-30"></i>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6">
                    <div class="card card-blue text-white">
                        <div class="card-block">
                            <div class="row align-items-center">
                                <div class="col-9">
                                    <h4 class="mb-0">$ {{ totalnetAmount() }}</h4>
                                    <p class="mb-0">Withdrawal Net Amount</p>
                                </div>
                                <div class="col-3 text-right">
                                    <i class="ik ik-user f-30"></i>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6">
                    <div class="card card-green text-white">
                        <div class="card-block">
                            <div class="row align-items-center">
                                <div class="col-9">
                                    <h4 class="mb-0">$ {{ totaldeductions() }}</h4>
                                    <p class="mb-0">Deduction on Withdrawal</p>
                                </div>
                                <div class="col-3 text-right">
                                    <i class="ik ik-user f-30"></i>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- page statustic chart end -->

            </div>
        </div>
        <!-- push external js -->
        @push('script')
            <script src="{{ asset('adm_assets/assets/plugins/owl.carousel/dist/owl.carousel.min.js') }}"></script>
            <script src="{{ asset('adm_assets/assets/plugins/chartist/dist/chartist.min.js') }}"></script>
            <script src="{{ asset('adm_assets/assets/plugins/flot-charts/jquery.flot.js') }}"></script>
            <!-- <script src="{{ asset('adm_assets/assets/plugins/flot-charts/jquery.flot.categories.js') }}"></script> -->
            <script src="{{ asset('plugins/flot-charts/curvedLines.js') }}"></script>
            <script src="{{ asset('adm_assets/assets/plugins/flot-charts/jquery.flot.tooltip.min.js') }}"></script>

            <script src="{{ asset('adm_assets/assets/plugins/amcharts/amcharts.js') }}"></script>
            <script src="{{ asset('adm_assets/assets/plugins/amcharts/serial.js') }}"></script>
            <script src="{{ asset('adm_assets/assets/plugins/amcharts/themes/light.js') }}"></script>


            <script src="{{ asset('adm_assets/assets/js/widget-statistic.js') }}"></script>
            <script src="{{ asset('adm_assets/assets/js/widget-data.js') }}"></script>
            <script src="{{ asset('adm_assets/assets/js/dashboard-charts.js') }}"></script>
            <script src="{{ asset('adm_assets/assets/js/widgets.js') }}"></script>
        @endpush
    @endsection
