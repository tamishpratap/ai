@extends('member.layouts.main')
@section('title', 'Import Funds')
@section('container')

    <!--**********************************
                                                                Content body start
                                                            ***********************************-->

    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- row -->
            <div class="row">

                <div class="col-12">

                    <div class="mb-3 mt-3">
                        <h5 class="fw-medium mb-0">Import Funds</h5>

                        <div class="text-muted fs-13 op-8 mb-3">
                            Add funds to your wallet
                        </div>
                    </div>

                </div>


                @if (session()->has('successMsg'))
                    <div class="col-12">

                        <div class="alert alert-success alert-dismissible fade show custom-alert-icon shadow-sm"
                            role="alert">

                            <svg style="fill: rgb(var(--success-rgb));" xmlns="http://www.w3.org/2000/svg" height="1.5rem"
                                viewBox="0 0 24 24" width="1.5rem" fill="#000000">

                                <path d="M0 0h24v24H0z" fill="none" />

                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10
                                        10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5
                                        1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />

                            </svg>

                            {{ session('successMsg') }}

                        </div>

                    </div>
                @endif


                @if (session()->has('failedMsg'))
                    <div class="col-12">

                        <div class="alert alert-danger alert-dismissible fade show custom-alert-icon shadow-sm"
                            role="alert">

                            <svg style="fill: rgb(var(--danger-rgb));" xmlns="http://www.w3.org/2000/svg" height="1.5rem"
                                viewBox="0 0 24 24" width="1.5rem" fill="#000000">

                                <path d="M0 0h24v24H0z" fill="none" />

                                <path d="M15.73 3H8.27L3 8.27v7.46L8.27 21h7.46
                                        L21 15.73V8.27L15.73 3zM12 17.3c-.72
                                        0-1.3-.58-1.3-1.3 0-.72.58-1.3
                                        1.3-1.3.72 0 1.3.58 1.3
                                        1.3 0 .72-.58 1.3-1.3
                                        1.3zm1-4.3h-2V7h2v6z" />

                            </svg>

                            {{ session('failedMsg') }}

                        </div>

                    </div>
                @endif


                <div class="col-xl-6 mt-4">

                    <div class="card custom-card h-100">

                        <div class="top-left"></div>
                        <div class="top-right"></div>
                        <div class="bottom-left"></div>
                        <div class="bottom-right"></div>

                        <div class="card-header">
                            <div class="card-title">Import Fund</div>
                        </div>

                        <div class="card-body">

                            <div class="d-flex align-items-center gap-3 flex-wrap mb-4">

                                <span class="avatar avatar-lg bg-primary-transparent svg-primary">
                                    <i class="ti ti-wallet fs-4"></i>
                                </span>

                                <div>
                                    <h5 class="mb-1">Import Funds</h5>

                                    <p class="text-muted mb-0">
                                        Add funds directly to your wallet.
                                    </p>
                                </div>

                            </div>


                            <div class="alert alert-primary mb-4" role="alert">

                                <strong>Wallet Balance :</strong>
                                $ {{ number_format($data['p2p_wallet'], 2) }}

                            </div>


                            <form action="{{ route('instantFunds') }}" id="myForm" method="post">

                                @csrf

                                <!-- Existing hidden member ID -->
                                <input type="hidden" name="memberid" value="{{ $data['memberid'] }}">

                                <!-- Backend compatibility field -->
                                <input type="hidden" name="inr_amount" id="inr_amount" value="{{ old('inr_amount') }}">


                                <div class="row gy-3">

                                    <!-- Member ID -->
                                    <div class="col-12">

                                        <label class="form-label" for="memberid">
                                            MemberID
                                        </label>

                                        <input type="text" id="memberid" class="form-control"
                                            value="{{ $data['memberid'] }}" readonly>

                                    </div>


                                    <!-- Amount -->
                                    <div class="col-12">

                                        <label class="form-label" for="amount">
                                            Amount
                                        </label>

                                        <input type="number" id="amount" name="amount" class="form-control"
                                            value="{{ old('amount') }}" placeholder="Enter Amount" min="100"
                                            step="1" required>

                                        @error('amount')
                                            <span class="text-danger">
                                                {{ $message }}
                                            </span>
                                        @enderror

                                    </div>


                                    <!-- Wallet Balance -->
                                    <div class="col-12">

                                        <label class="form-label" for="wallet_balance">
                                            Wallet Balance
                                        </label>

                                        <input type="text" id="wallet_balance" class="form-control"
                                            value="$ {{ number_format($data['p2p_wallet'], 2) }}" readonly>

                                    </div>


                                    <!-- Security PIN -->
                                    <div class="col-12">

                                        <label class="form-label" for="security_pin">
                                            Security PIN
                                        </label>

                                        <input type="password" id="security_pin" name="security_pin" class="form-control"
                                            placeholder="Enter Security PIN" required>

                                        @error('security_pin')
                                            <span class="text-danger">
                                                {{ $message }}
                                            </span>
                                        @enderror

                                    </div>


                                    <!-- Button -->
                                    <div class="col-12 pt-2">

                                        <button class="btn btn-success btn-wave w-100" id="subBtn">

                                            <span class="me-2">
                                                Import Funds
                                            </span>

                                            <i class="ti ti-arrow-right"></i>

                                        </button>

                                    </div>

                                </div>

                            </form>


                            <p class="text-muted mt-4 mb-0">
                                Enter the amount and your Security PIN to continue.
                            </p>

                        </div>

                    </div>

                </div>


                <!-- Right Side Information Card -->
                <div class="col-xl-6 mt-4">

                    <div class="card custom-card h-100">

                        <div class="top-left"></div>
                        <div class="top-right"></div>
                        <div class="bottom-left"></div>
                        <div class="bottom-right"></div>

                        <div class="card-header">
                            <div class="card-title">Import Fund Information</div>
                        </div>

                        <div class="card-body">

                            <div class="alert alert-success" role="alert">

                                <h6 class="mb-2">
                                    Import Funds
                                </h6>

                                <p class="mb-0">
                                    Enter the amount you want to add to your wallet
                                    and verify it using your Security PIN.
                                </p>

                            </div>


                            <div class="mt-4">

                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted">
                                        MemberID
                                    </span>

                                    <strong>
                                        {{ $data['memberid'] }}
                                    </strong>
                                </div>


                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted">
                                        Current Wallet
                                    </span>

                                    <strong>
                                        $ {{ number_format($data['p2p_wallet'], 2) }}
                                    </strong>
                                </div>


                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">
                                        Minimum Amount
                                    </span>

                                    <strong>
                                        $100
                                    </strong>
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script>
        $(document).ready(function() {

            $('#amount').on('input', function() {

                var amount = $(this).val();

                $('#inr_amount').val(amount);

            });

            $('#myForm').on('submit', function() {

                var amount = $('#amount').val();

                $('#inr_amount').val(amount);

            });

        });
    </script>

@endsection
