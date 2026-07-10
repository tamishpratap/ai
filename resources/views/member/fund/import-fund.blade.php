@extends('member.layouts.main')
@section('title', 'Deposit Fund')
@section('container')
    <style>
        .deposit-qr-box {
            background: rgba(var(--light-rgb), 0.55);
            border: 1px dashed rgba(var(--default-border), 0.8);
            border-radius: 1rem;
            padding: 1.25rem;
        }

        .deposit-copy-box {
            word-break: break-all;
            cursor: pointer;
        }

        .deposit-summary-card {
            background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.14), rgba(var(--success-rgb), 0.12));
            border: 1px solid rgba(var(--primary-rgb), 0.18);
            border-radius: 1rem;
        }

        @media only screen and (max-width: 991px) {
            .deposit-form-card {
                margin-top: 1rem;
            }
        }
    </style>

    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="mb-3 mt-3">
                        <h5 class="fw-medium mb-0">Deposit Fund</h5>
                        <div class="text-muted fs-13 op-8 mb-3">
                            Add fund to your wallet using USDT BEP20
                        </div>
                    </div>
                </div>

                @if (session()->has('successMsg'))
                    <div class="col-12">
                        <div class="alert alert-success alert-dismissible fade show custom-alert-icon shadow-sm"
                            role="alert">
                            <svg style="fill: rgb(var(--success-rgb));" xmlns="http://www.w3.org/2000/svg"
                                height="1.5rem" viewBox="0 0 24 24" width="1.5rem" fill="#000000">
                                <path d="M0 0h24v24H0z" fill="none" />
                                <path
                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                            </svg>
                            {{ session('successMsg') }}
                        </div>
                    </div>
                @endif

                @if (session()->has('failedMsg'))
                    <div class="col-12">
                        <div class="alert alert-danger alert-dismissible fade show custom-alert-icon shadow-sm"
                            role="alert">
                            <svg style="fill: rgb(var(--danger-rgb));" xmlns="http://www.w3.org/2000/svg"
                                height="1.5rem" viewBox="0 0 24 24" width="1.5rem" fill="#000000">
                                <path d="M0 0h24v24H0z" fill="none" />
                                <path
                                    d="M15.73 3H8.27L3 8.27v7.46L8.27 21h7.46L21 15.73V8.27L15.73 3zM12 17.3c-.72 0-1.3-.58-1.3-1.3 0-.72.58-1.3 1.3-1.3.72 0 1.3.58 1.3 1.3 0 .72-.58 1.3-1.3 1.3zm1-4.3h-2V7h2v6z" />
                            </svg>
                            {{ session('failedMsg') }}
                        </div>
                    </div>
                @endif

                <div class="col-xl-6">
                    <div class="card custom-card h-100">
                        <div class="top-left"></div>
                        <div class="top-right"></div>
                        <div class="bottom-left"></div>
                        <div class="bottom-right"></div>
                        <div class="card-header">
                            <div class="card-title">Deposit Details</div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3 flex-wrap mb-4">
                                <span class="avatar avatar-lg bg-primary-transparent svg-primary">
                                    <i class="ti ti-wallet fs-4"></i>
                                </span>
                                <div>
                                    <h5 class="mb-1">USDT BEP20</h5>
                                    <p class="text-muted mb-0">
                                        Deposit only on BEP20 network, otherwise payment may be lost.
                                    </p>
                                </div>
                            </div>

                            <div class="deposit-qr-box text-center mb-4">
                                <img src="{{ asset('uploads/qr-code') }}/{{ $usdt_data->qrcode }}"
                                    alt="USDT QR Code" class="img-fluid rounded" style="max-height: 320px;">
                            </div>

                            <div class="alert alert-success text-center" role="alert" style="display: none"
                                id="rcode">
                                <strong>Copied!</strong> Wallet address copied successfully.
                            </div>

                            <div class="alert alert-primary deposit-copy-box mb-0" role="alert">
                                <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
                                    <div>
                                        <strong>Wallet Address :</strong>
                                        <span id="code">{{ $usdt_data->address }}</span>
                                    </div>
                                    <span id="copyButtonId">
                                        <i class="ti ti-files"></i>
                                        <span id="msgText">CopyText</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6 deposit-form-card">
                    <div class="card custom-card h-100">
                        <div class="top-left"></div>
                        <div class="top-right"></div>
                        <div class="bottom-left"></div>
                        <div class="bottom-right"></div>
                        <div class="card-header">
                            <div class="card-title">Submit Deposit Request</div>
                        </div>
                        <div class="card-body">
                            <div class="deposit-summary-card p-4 mb-4">
                                <p class="text-muted mb-2">Requested Amount</p>
                                <div class="d-flex align-items-end justify-content-between gap-3 flex-wrap">
                                    <h2 class="mb-0" id="fund">$ 0.00</h2>
                                    <span class="badge bg-success-transparent text-success">Ready to submit</span>
                                </div>
                            </div>

                            <form action="{{ route('USDTFundReq') }}" id="myForm" method="post"
                                enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" id="csrf" value="{{ csrf_token() }}">
                                <input type="hidden" name="memberid" value="{{ $data['memberid'] }}">
                                <input type="hidden" id="rate" value="{{ $rate['rate'] }}">

                                <div class="row gy-3">
                                    <div class="col-12">
                                        <label class="form-label" for="usdt_amount">Request Amount (USDT)</label>
                                        <input type="text" id="usdt_amount" name="amount" class="form-control"
                                            placeholder="Please Enter Amount $"
                                            onkeypress='return event.charCode >= 48 && event.charCode <= 57' />
                                        @error('amount')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label" for="billings-card-name1">Txn Id</label>
                                        <input type="text" id="billings-card-name1" class="form-control"
                                            placeholder="Enter TxnId" name="txnid" />
                                        @error('txnid')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label" for="billings-card-date1">Txn Screenshot</label>
                                        <input type="file" id="billings-card-date1" class="form-control"
                                            name="image" />
                                        @error('image')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-12 pt-2">
                                        <button class="btn btn-success btn-wave w-100" id="subBtn">
                                            <span class="me-2">Proceed with Payment</span>
                                            <i class="ti ti-arrow-right"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <p class="text-muted mt-4 mb-0">
                                By continuing, you accept our Terms of Services and Privacy Policy. Payments are
                                non-refundable.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
