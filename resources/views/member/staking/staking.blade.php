@extends('member.layouts.main')
@section('title', 'Staking Creation')
@section('container')
    <!--**********************************
                                                                                    Content body start
                                                                                ***********************************-->
    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- row -->
            <div class="row">
                <div class="mb-3 mt-5">
                    <h5 class="fw-medium mb-0">Investment Creation</h5>
                    <div class="text-muted fs-13 op-8 mb-3">
                        create your Investment
                    </div>
                </div>
                <div class="col-xl-6 col-lg-6">
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
                    <div class="card custom-card card-bx m-b30">
                        <div class="card-header">
                            <h6 class="title">Create Investment Form</h6>
                        </div>
                        <form action="{{ route('createStaking') }}" method="post" id="stakingForm">
                            @csrf
                            <div class="card-body mb-5 pb-4">
                                <div class="col-sm-12 mb-3">
                                    <label class="form-label">Memberid</label>
                                    <input type="text" class="form-control" name="memberid"
                                        value="{{ $data['memberid'] }}" readonly id="memberid">
                                    @error('memberid')
                                        <span class="text-danger"> {{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-sm-12 mb-3">
                                    <label class="form-label">Investment Package</label>

                                    <select class="form-select" id="stak_package" name="stak_package" required>
                                        <option value="">Select Package</option>
                                        <option value="Silver">Silver ($100 - $1,000)</option>
                                        <option value="Gold">Gold ($1,100 - $5,000)</option>
                                        <option value="Platinum">Platinum ($5,100 - $10,000)</option>
                                        <option value="Diamond">Diamond ($10,100+)</option>
                                    </select>

                                    @error('stak_package')
                                        <span style="color:red">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-sm-12 mb-3">
                                    <label class="form-label">Investment Amount</label>

                                    <input type="number" class="form-control" name="amount" id="amount"
                                        value="{{ old('amount') }}" step="1" placeholder="Enter Investment Amount" required
                                        >

                                    <small id="amount_limit" class="text-warning d-block mt-2"></small>

                                    <small id="amount_error" class="d-block mt-1" style="color:red"></small>

                                    @error('amount')
                                        <span style="color:red">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-sm-12 mb-3">
                                    <label class="form-label">Wallet Balance</label>
                                    <div class="row">
                                        <div class="col-2 me-0 pe-0">
                                            <input type="text" class="form-control" value="$" readonly
                                                style="border-top-right-radius: 0px; border-bottom-right-radius:0px">
                                        </div>
                                        <div class="col-10 ms-0 ps-0">
                                            <input type="text" class="form-control"
                                                value="{{ number_format($data['p2p_wallet'], 2) }}"
                                                style="border-top-left-radius: 0px; border-bottom-left-radius:0px" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12 m-b30">
                                    <label class="form-label">Transaction Password</label>
                                    <div class="col-12 ms-0 ps-0">
                                        <input type="password" class="form-control" placeholder="Enter Transacton Password"
                                            name="txn_password" required>
                                    </div>
                                    @error('txn_password')
                                        <span style="color:red"> {{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-sm-12 m-b30 mt-3">
                                    <input type="hidden" class="form-control" value="{{ csrf_token() }}" id="csrf">
                                    <button type="submit" class="btn btn-primary float-end" id="act_btn">Create Investment</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script>
        $(document).ready(function() {

            function setPackageLimit() {

                var packageName = $('#stak_package').val();

                var minAmount = '';
                var maxAmount = '';
                var limitText = '';

                if (packageName == 'Silver') {

                    minAmount = 100;
                    maxAmount = 1000;

                    limitText = 'Minimum Amount: $100 | Maximum Amount: $1,000';

                } else if (packageName == 'Gold') {

                    minAmount = 1100;
                    maxAmount = 5000;

                    limitText = 'Minimum Amount: $1,100 | Maximum Amount: $5,000';

                } else if (packageName == 'Platinum') {

                    minAmount = 5100;
                    maxAmount = 10000;

                    limitText = 'Minimum Amount: $5,100 | Maximum Amount: $10,000';

                } else if (packageName == 'Diamond') {

                    minAmount = 10100;
                    maxAmount = '';

                    limitText = 'Minimum Amount: $10,100 | No Maximum Limit';

                }

                $('#amount').attr('min', minAmount);

                if (maxAmount != '') {
                    $('#amount').attr('max', maxAmount);
                } else {
                    $('#amount').removeAttr('max');
                }

                $('#amount_limit').text(limitText);

                $('#amount').val('');
                $('#amount_error').text('');
            }


            $('#stak_package').change(function() {
                setPackageLimit();
            });


            $('#amount').on('input', function() {

                var packageName = $('#stak_package').val();
                var amount = parseFloat($(this).val());

                var minAmount = 0;
                var maxAmount = 0;

                $('#amount_error').text('');

                if (packageName == '') {
                    $('#amount_error').text('Please select an investment package first.');
                    return;
                }

                if (packageName == 'Silver') {

                    minAmount = 100;
                    maxAmount = 1000;

                } else if (packageName == 'Gold') {

                    minAmount = 1100;
                    maxAmount = 5000;

                } else if (packageName == 'Platinum') {

                    minAmount = 5100;
                    maxAmount = 10000;

                } else if (packageName == 'Diamond') {

                    minAmount = 10100;
                    maxAmount = null;
                }


                if (isNaN(amount)) {
                    return;
                }


                if (amount < minAmount) {

                    if (maxAmount != null) {
                        $('#amount_error').text(
                            packageName + ' package amount must be between $' +
                            minAmount.toLocaleString() + ' and $' +
                            maxAmount.toLocaleString()
                        );
                    } else {
                        $('#amount_error').text(
                            packageName + ' package minimum amount is $' +
                            minAmount.toLocaleString()
                        );
                    }

                } else if (maxAmount != null && amount > maxAmount) {

                    $('#amount_error').text(
                        packageName + ' package amount must be between $' +
                        minAmount.toLocaleString() + ' and $' +
                        maxAmount.toLocaleString()
                    );

                } else {

                    $('#amount_error').text('');
                }

            });

            var isSubmitting = false;
            $('#stakingForm').on('submit', function(e) {
                if (isSubmitting) {
                    e.preventDefault();
                    return false;
                }

                isSubmitting = true;
                var $btn = $('#act_btn');
                $btn.prop('disabled', true);
                $btn.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Please wait...');
            });

        });
    </script>
@endsection
