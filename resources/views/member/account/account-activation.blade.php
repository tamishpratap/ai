@extends('member.layouts.main')
@section('title', 'User Activation')
@section('container')

    <!-- MAIN-CONTENT -->

    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Start:: row-1 -->
            <div class="row justify-content-center">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                    <div class="mb-4">
                        <h5 class="fw-medium mb-0">User Activation</h5>
                        <div class="text-muted fs-13 op-8 mb-3">
                            Account Activation
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-xl-7 col-lg-7 col-md-7 col-sm-12">
                            <div class="card custom-card">
                                <div class="top-left"></div>
                                <div class="top-right"></div>
                                <div class="bottom-left"></div>
                                <div class="bottom-right"></div>
                                <div class="card-body">
                                    <div class="row mt-2">
                                        <div>
                                            @if (session()->has('successMsg'))
                                                <div class="alert alert-success alert-dismissible fade show custom-alert-icon shadow-sm"
                                                    role="alert">
                                                    <svg style="fill: rgb(var(--success-rgb));"
                                                        xmlns="http://www.w3.org/2000/svg" height="1.5rem"
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
                                                    <svg style="fill: rgb(var(--danger-rgb));" style="color:red"
                                                        xmlns="http://www.w3.org/2000/svg" height="1.5rem"
                                                        viewBox="0 0 24 24" width="1.5rem" fill="#000000">
                                                        <path d="M0 0h24v24H0z" fill="none" />
                                                        <path
                                                            d="M15.73 3H8.27L3 8.27v7.46L8.27 21h7.46L21 15.73V8.27L15.73 3zM12 17.3c-.72 0-1.3-.58-1.3-1.3 0-.72.58-1.3 1.3-1.3.72 0 1.3.58 1.3 1.3 0 .72-.58 1.3-1.3 1.3zm1-4.3h-2V7h2v6z" />
                                                    </svg>
                                                    {{ session('failedMsg') }}
                                                </div>
                                            @endif
                                            <div class="tab-pane" role="tabpanel" aria-labelledby="main-security-tab"
                                                tabindex="0">
                                                <div>
                                                    @if ($data['status'] == 'Temp')
                                                        <h3 class="title mb-3">Account Activation Form</h3>
                                                        <form action="{{ route('activateUser') }}" method="post">
                                                            @csrf
                                                            <div class="row">
                                                                <div class="col-sm-12 m-b30 mb-4">
                                                                    <label class="form-label my-2">Memberid</label>
                                                                    <input type="text" class="form-control"
                                                                        name="memberid" value="{{ $data['memberid'] }}"
                                                                        readonly id="memberid">
                                                                    @error('memberid')
                                                                        <span class="text-danger"> {{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                                <div class="mb-6">
                                                                    <label class="form-label"
                                                                        for="basic-default-country">Package Amount</label>
                                                                    <select name="package" class="form-control" id="package"
                                                                        style="color: #3465ff;font-weight: 800;  font-size: 17px;">
                                                                        <option value="" style="color:blue;">Select
                                                                            Package</option>
                                                                        <option value="30" style="color:rgb(0, 0, 0);">Basic - $ 30
                                                                        </option>
                                                                        <option value="50" style="color:rgb(0, 0, 0);">Standard - $ 50
                                                                        </option>
                                                                        <option value="110" style="color:rgb(0, 0, 0);">Premium - $ 110
                                                                        </option>
                                                                        <option value="200" style="color:rgb(0, 0, 0);">Pro - $ 200
                                                                        </option>
                                                                    </select>
                                                                    {{-- <span class="text-danger" id="packMsg"></span> --}}
                                                                    @error('package')
                                                                        <span class="text-danger">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                                <div class="mb-6 mt-4">
                                                                    <label class="form-label" for="basic-default-dob">Fund
                                                                        Balance</label>
                                                                    <input type="text"
                                                                        class="form-control flatpickr-validation"
                                                                        id="basic-default-dob"
                                                                        value="{{ $data['p2p_wallet'] }}" name="balance"
                                                                        readonly />
                                                                </div>
                                                                <div class="mb-6 mt-4">
                                                                    <label class="form-label"
                                                                        for="basic-default-upload-file">Transaction
                                                                        Password</label>
                                                                    <input type="password" class="form-control"
                                                                        placeholder="Enter Transaction Password"
                                                                        name="txn_password" required />
                                                                    @error('txn_password')
                                                                        <span class="text-danger">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                                <div class="col-sm-12 m-b30 mb-3 mt-4">
                                                                    <input type="hidden" class="form-control my-1"
                                                                        value="{{ csrf_token() }}" id="csrf">
                                                                    <button class="btn btn-primary float-end"
                                                                        id="subBtn">Activate
                                                                        Account</button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    @else
                                                        <h3 class="title mb-3">Account Activation</h3>
                                                        <div class="card bg-primary m-b30 shadow-lg ">
                                                            <div class="card-body mb-3 pb-4">
                                                                <h3 class="text-white mt-2 mb-3" style="font-weight:600">
                                                                    Account Activated Successfully
                                                                </h3>
                                                                <h6 class=" mb-0"
                                                                    style="color: rgb(6, 4, 129);font-weight:600;font-size:14px;word-spacing:1px">
                                                                    Congratulations And Welcome To
                                                                    {{ config('detailsApp.name') }} Family
                                                                    Your
                                                                    account is successfully activated please go for upgrade
                                                                    section to activate other
                                                                    packages Wish You a very Best Of Luck.</h6>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End:: row-1 -->

        </div>
    </div>
    <!-- End::app-content -->

    <!-- END MAIN-CONTENT -->


@endsection
