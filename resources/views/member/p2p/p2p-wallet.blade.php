@extends('member.layouts.main')
@section('title', 'P2P Wallet')
@section('container')
    @php
        $p2pBalance = number_format((float) $data['p2p_wallet'], 2);
        $mainWalletBalance = number_format((float) $data['wallet'], 2);
        $totalIncomeValue = number_format((float) totalIncome($data['memberid']), 2);
        $totalWithdrawalValue = number_format((float) totalWithdrawals($data['memberid']), 2);
        $todayWithdrawalValue = number_format((float) todayWithdrawals($data['memberid']), 2);
    @endphp
    <style>
        @media only screen and (max-width: 600px) {
            .alert {
                margin-top: 20px;
            }

            div:has(.custom-card) {
                margin-top: 10px;
            }
        }

        @media only screen and (min-width: 600px) {
            .alert {
                margin-top: 20px;
            }

            div:has(.custom-card) {
                margin-top: 10px;
            }
        }

        @media only screen and (min-width: 768px) {
            .alert {
                margin-top: 20px;
            }

            div:has(.custom-card) {
                margin-top: 20px;
            }
        }

        @media only screen and (min-width: 992px) {
            .alert {
                margin-top: 0;
            }

            div:has(.custom-card) {
                margin-top: 10px;
            }
        }

        @media only screen and (min-width: 1200px) {
            .alert {
                margin-top: 0;
            }

            div:has(.custom-card) {
                margin-top: 10px;
            }
        }
    </style>

    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="mb-4">
                <h5 class="fw-medium mb-0">P2P Wallet</h5>
                <div class="text-muted fs-13 op-8 mb-3">
                    Manage peer-to-peer balance and transfer funds securely.
                </div>
            </div>

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
                            <div class="member-wallet-panel__eyebrow">P2P Treasury</div>
                            <div class="member-wallet-panel__header">
                                <div>
                                    <h2 class="member-wallet-panel__title">P2P Wallet Balance</h2>
                                    <p class="member-wallet-panel__subtitle mb-0">Use this wallet for secure internal transfers between members.</p>
                                </div>
                                <div class="member-wallet-badge">Transfer Ready</div>
                            </div>
                            <div class="member-wallet-balance">$ {{ $p2pBalance }}</div>
                            <div class="member-wallet-balance-note">Current available P2P balance for peer transfers</div>
                            <div class="member-wallet-strip">
                                <div class="member-wallet-strip__item">
                                    <span>Main Wallet</span>
                                    <strong>${{ $mainWalletBalance }}</strong>
                                </div>
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
                            <div class="member-wallet-form__top">
                                <div class="member-wallet-panel__eyebrow">P2P Transfer</div>
                                <h3 class="member-wallet-form__title">Send funds to another member</h3>
                                <p class="member-wallet-panel__subtitle mb-0">Enter receiver ID, transfer amount, and your transaction password.</p>
                            </div>
                            <form action="{{ route('fundTrans') }}" method="post" class="myForm member-wallet-form">
                                <input type="hidden" id="csrf" value="{{ csrf_token() }}">
                                @csrf
                                <div class="row gy-3">
                                    <div class="col-xl-12">
                                        <label class="form-label member-wallet-label">Receiver Member ID</label>
                                        <input type="text" class="form-control member-wallet-input" placeholder="Enter member ID"
                                            name="memberid" id="fundmemberid" maxlength="9" minlength="9">
                                        <small id="memMsg" class="member-transfer-status"></small>
                                        @error('memberid')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-xl-12">
                                        <label class="form-label member-wallet-label">Amount</label>
                                        <input type="text" class="form-control member-wallet-input" name="amount" id="transfer_amount"
                                            placeholder="Enter amount to transfer"
                                            onkeypress='return event.charCode >= 48 && event.charCode <= 57' required>
                                        <small id="amountText" class="member-transfer-status member-transfer-status--danger"></small>
                                        @error('amount')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-xl-12">
                                        <label class="form-label member-wallet-label">Transaction Password</label>
                                        <input type="password" class="form-control member-wallet-input txnpasses" id="inptxnpass"
                                            name="txn_password" placeholder="Enter transaction password">
                                        @error('txn_password')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-12 d-grid gap-2">
                                        <input type="hidden" name="balance" id="balance" value="{{ $data['p2p_wallet'] }}">
                                        <input type="hidden" name="sender" value="{{ $data['memberid'] }}">
                                        <button type="submit" id="transBtn" class="btn member-wallet-submit subBtn">Transfer Amount</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    @push('user-scripts')
        <script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
        <script>
            $(function() {
                $('.myForm').on('submit', function() {
                    $('.subBtn').attr('disabled', true);
                    $('.subBtn').html('Wait! Processing');
                });

                $('#fundmemberid').on('input', function() {
                    var memberid = $(this).val();
                    var csrf = $('#csrf').val();

                    if (memberid.length === 0) {
                        $('#memMsg').html('');
                        $('#transBtn').attr('disabled', false);
                        return;
                    }

                    $.ajax({
                        url: '/getMember',
                        type: 'POST',
                        data: {
                            memberid: memberid,
                            _token: csrf,
                        },
                        success: function(response) {
                            $('#memMsg').html(response['data']);
                            if (response['code'] == 0) {
                                $('#transBtn').attr('disabled', true);
                            } else {
                                $('#transBtn').attr('disabled', false);
                            }
                        }
                    });
                });

                $('#transfer_amount').on('input', function() {
                    var amount = parseInt($(this).val());
                    var balance = parseInt($('#balance').val());

                    if (!amount || balance >= amount) {
                        $('#amountText').html('');
                        $('.subBtn').attr('disabled', false);
                    } else {
                        $('#amountText').html('You do not have enough balance to transfer this amount');
                        $('.subBtn').attr('disabled', true);
                    }
                });
            });
        </script>
    @endpush
@endsection
