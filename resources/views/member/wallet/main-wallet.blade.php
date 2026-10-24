@extends('member.layouts.main')
@section('title', 'Withdrawal')
@section('container')
    @php
        $walletBalance = number_format((float) $data['wallet'], 2);
        $principalBalance = number_format((float) $data['principal_wallet'], 2);
        $totalIncomeValue = number_format((float) totalIncome($data['memberid']), 2);
        $totalWithdrawalValue = number_format((float) totalWithdrawals($data['memberid']), 2);
        $todayWithdrawalValue = number_format((float) todayWithdrawals($data['memberid']), 2);
    @endphp
    <style>
        /* Extra small devices (phones, 600px and down) */
        @media only screen and (max-width: 600px) {
            .alert {
                margin-top: 30px;
            }

            div:has(.custom-card) {
                margin-top: 10px;
            }
        }

        /* Small devices (portrait tablets and large phones, 600px and up) */
        @media only screen and (min-width: 600px) {
            .alert {
                margin-top: 30px;
            }

            div:has(.custom-card) {
                margin-top: 10px;
            }
        }

        /* Medium devices (landscape tablets, 768px and up) */
        @media only screen and (min-width: 768px) {
            .alert {
                margin-top: 30px;
            }

            div:has(.custom-card) {
                margin-top: 20px;
            }
        }

        /* Large devices (laptops/desktops, 992px and up) */
        @media only screen and (min-width: 992px) {
            .alert {
                margin-top: 0px;
            }

            div:has(.custom-card) {
                margin-top: 10px;
            }
        }

        /* Extra large devices (large laptops and desktops, 1200px and up) */
        @media only screen and (min-width: 1200px) {
            .alert {
                margin-top: 0px;
            }

            div:has(.custom-card) {
                margin-top: 10px;
            }
        }
    </style>
    {{-- <style>
        /* Extra small devices (phones, 600px and down) */
        @media only screen and (max-width: 600px) {
            .carousel-item>img {
                height: 250px;
            }
        }

        /* Small devices (portrait tablets and large phones, 600px and up) */
        @media only screen and (min-width: 600px) {
            .carousel-item>img {
                height: 250px;
            }
        }

        /* Medium devices (landscape tablets, 768px and up) */
        @media only screen and (min-width: 768px) {
            .carousel-item>img {
                height: 300px;
            }
        }

        /* Large devices (laptops/desktops, 992px and up) */
        @media only screen and (min-width: 992px) {
            .carousel-item>img {
                height: 400px;
            }
        }

        /* Extra large devices (large laptops and desktops, 1200px and up) */
        @media only screen and (min-width: 1200px) {
            .carousel-item>img {
                height: 400px;
            }
        }
    </style> --}}


    <!-- MAIN-CONTENT -->

    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">
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
            <section class="member-wallet-hero mb-4">
                <div class="row g-4 align-items-stretch">
                    <div class="col-xl-7">
                        <div class="member-wallet-panel member-wallet-panel--primary h-100">
                            <div class="member-wallet-panel__eyebrow">Treasury Overview</div>
                            <div class="member-wallet-panel__header">
                                <div>
                                    <h2 class="member-wallet-panel__title" id="wallet_text">Main Wallet</h2>
                                    <p class="member-wallet-panel__subtitle mb-0">Manage balances and process withdrawals from one secure screen.</p>
                                </div>
                                <div class="member-wallet-badge">Active Wallet</div>
                            </div>
                            <div class="member-wallet-balance">$ <span id="wallet_amount">{{ $walletBalance }}</span></div>
                            <div class="member-wallet-balance-note">Available balance for withdrawal requests</div>
                            <div class="member-wallet-strip">
                                <div class="member-wallet-strip__item">
                                    <span>Member ID</span>
                                    <strong>{{ $data['memberid'] }}</strong>
                                </div>
                            </div>
                            <div class="member-wallet-kpis">
                                <div class="member-wallet-kpi">
                                    <span>Total Income</span>
                                    <strong>${{ $totalIncomeValue }}</strong>
                                </div>
                                <div class="member-wallet-kpi">
                                    <span>Total Withdrawal</span>
                                    <strong>${{ $totalWithdrawalValue }}</strong>
                                </div>
                                <div class="member-wallet-kpi">
                                    <span>Today's Withdrawal</span>
                                    <strong>${{ $todayWithdrawalValue }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-5">
                        <div class="member-wallet-panel member-wallet-panel--form h-100">
                            <div class="member-wallet-form__top d-flex justify-content-between align-items-start gap-2 flex-wrap">
                                <div>
                                    <div class="member-wallet-panel__eyebrow">Withdrawal Request</div>
                                <h3 class="member-wallet-form__title">Secure withdrawal form</h3>
                                    <p class="member-wallet-panel__subtitle mb-0">Enter the amount and transaction password to continue.</p>
                                </div>
                                <button type="button" class="ira-form-fill-btn" data-ira-form="member_wallet_withdrawal" title="Fill with IRA Voice AI">
                                    <svg class="ira-form-fill-icon" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                        <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
                                        <path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>
                                    </svg>
                                    <span>AI Fill Form</span>
                                </button>
                            </div>
                            <form action="{{ route('initWithdrawal') }}" method="post" class="myForm member-wallet-form">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label member-wallet-label">Amount</label>
                                    <input type="text" class="form-control member-wallet-input" name="amount" id="amount"
                                        placeholder="Enter amount"
                                        onkeypress='return event.charCode >= 48 && event.charCode <= 57' required>
                                    @error('amount')
                                        <span style="color: red">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label member-wallet-label">Transaction Password</label>
                                    <input type="password" class="form-control member-wallet-input" name="txn_password"
                                        placeholder="Enter transaction password" required>
                                    @error('txn_password')
                                        <span style="color: red">{{ $message }}</span>
                                    @enderror
                                </div>
                                <input type="hidden" name="memberid" id="memberid" value="{{ $data['memberid'] }}">
                                <input type="hidden" name="requestId" id="requestId" value="{{ $data['memberid'] . time() }}">
                                <input type="hidden" id="wallet" value="{{ $data['wallet'] }}">
                                <input type="hidden" id="principal_wallet" value="{{ $data['principal_wallet'] }}">
                                <div class="d-grid gap-2 mt-4">
                                    <button type="submit" class="btn member-wallet-submit subBtn">Withdraw Fund</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </div>
    <!-- End::app-content -->

    <!-- END MAIN-CONTENT -->

    @push('user-scripts')
        <!-- Sales Dashboard -->
        {{-- <link rel="modulepreload" href="{{ asset('mem_assets/sales-dashboard-BqcsowLy.js') }}"> --}}
        <!-- POPPER JS -->
        <script src="https://laravelui.spruko.com/scifi/build/assets/libs/@popperjs/core/umd/popper.min.js"></script>

        <!-- BOOTSTRAP JS -->
        <script src="https://laravelui.spruko.com/scifi/build/assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>

        <!-- NODE WAVES JS -->
        <script src="https://laravelui.spruko.com/scifi/build/assets/libs/node-waves/waves.min.js"></script>

        <!-- SIMPLEBAR JS -->
        <script src="https://laravelui.spruko.com/scifi/build/assets/libs/simplebar/simplebar.min.js"></script>
        <link rel="modulepreload" href="https://laravelui.spruko.com/scifi/build/assets/simplebar-B35Aj-bA.js">
        <script type="module" src="https://laravelui.spruko.com/scifi/build/assets/simplebar-B35Aj-bA.js"></script>

        <!-- COLOR PICKER JS -->
        <script src="https://laravelui.spruko.com/scifi/build/assets/libs/@simonwep/pickr/pickr.es5.min.js"></script>

        <!-- Apex Charts JS -->
        <script src="https://laravelui.spruko.com/scifi/build/assets/libs/apexcharts/apexcharts.min.js"></script>

        <!-- Jobs Dashboard -->
        {{-- <link rel="modulepreload" href="https://laravelui.spruko.com/scifi/build/assets/jobs-dashboard-I5iAQGSC.js"> --}}
        {{-- <script type="module" src="https://laravelui.spruko.com/scifi/build/assets/jobs-dashboard-I5iAQGSC.js"></script> --}}


        <!-- STICKY JS -->
        <script src="https://laravelui.spruko.com/scifi/build/assets/sticky.js"></script>

        <!-- APP JS -->
        <link rel="modulepreload" href="https://laravelui.spruko.com/scifi/build/assets/app-tP9rsWKQ.js">
        <script type="module" src="https://laravelui.spruko.com/scifi/build/assets/app-tP9rsWKQ.js"></script>

        <!-- CUSTOM-SWITCHER JS -->
        <link rel="modulepreload" href="https://laravelui.spruko.com/scifi/build/assets/custom-switcher-Dok1OPLs.js">
        <script type="module" src="https://laravelui.spruko.com/scifi/build/assets/custom-switcher-Dok1OPLs.js"></script>
        <script type="module" src="{{ asset('mem_assets/sales-dashboard-BqcsowLy.js') }}"></script>
        <!-- Jquery Cdn -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <script>
            $(function() {
                $('.myForm').on('submit', function() {
                    $('.subBtn').attr('disabled', true);
                    $('.subBtn').html('Wait! Processing');
                })

                $('#type').on('change', function() {
                    var type = $(this).val();
                    var principal_wallet = parseFloat($('#principal_wallet').val());
                    var wallet = parseFloat($('#wallet').val());

                    if (type == 'Main Wallet' || type == '') {
                        $('#wallet_text').html('Main Wallet');
                        $('#wallet_amount').html(wallet.toFixed(2));
                    } else {
                        $('#wallet_text').html('Principal Wallet');
                        $('#wallet_amount').html(principal_wallet.toFixed(2));
                    }

                });

                $('#usdt_amount').on('input', function() {
                    var usdt = $(this).val();
                    var rate = $('#rate').val();
                    if (usdt.length == 0) {
                        $('#AmountFirst').val(0);
                    } else {
                        if (usdt > 0) {
                            $('#AmountFirst').val((usdt / rate).toFixed(2));
                        } else {
                            $('#AmountFirst').val(0);
                        }
                    }
                });

            });
        </script>
    @endpush


@endsection
