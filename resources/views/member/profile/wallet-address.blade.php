@extends('member.layouts.main')
@section('title', 'Wallet Address')
@section('container')

    <!-- MAIN-CONTENT -->

    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Start:: row-1 -->
            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="mb-3 mt-5">
                        <h5 class="fw-medium mb-0">Wallet Address</h5>
                        <div class="text-muted fs-13 op-8 mb-3">
                            Update your Wallet Address
                        </div>
                    </div>
                    <div class="row mt-4 justify-content-center">
                        <div class="col-xl-7 col-lg-7 col-md-7 col-sm-12">
                            <div class="card custom-card">
                                <div class="top-left"></div>
                                <div class="top-right"></div>
                                <div class="bottom-left"></div>
                                <div class="bottom-right"></div>
                                <div class="card-body">
                                    <div class="row mt-4 justify-content-center">
                                        <div class="col-12">
                                            @if (session()->has('msgsucc'))
                                                <div class="alert alert-success alert-dismissible fade show custom-alert-icon shadow-sm"
                                                    role="alert">
                                                    <svg style="fill: rgb(var(--success-rgb));"
                                                        xmlns="http://www.w3.org/2000/svg" height="1.5rem"
                                                        viewBox="0 0 24 24" width="1.5rem" fill="#000000">
                                                        <path d="M0 0h24v24H0z" fill="none" />
                                                        <path
                                                            d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                                                    </svg>
                                                    {{ session('msgsucc') }}
                                                </div>
                                            @endif
                                            <div class="tab-pane" role="tabpanel" aria-labelledby="main-security-tab"
                                                tabindex="0">
                                                <div>
                                                    <h3 class="text-center title mb-3"><b>Wallet Address</b></h3>
                                                    @if ($data['wallet_address'] == '')
                                                        <form action="{{ route('insertWalletAddress') }}" method="post">
                                                            @csrf
                                                            <div class="row">
                                                                <div class="col-xl-12">
                                                                    <div class="mb-2">
                                                                        <label for="wallet_address" class="form-label">Wallet
                                                                            Address</label>
                                                                        <input type="text" class="form-control"
                                                                            name="wallet_address"
                                                                            value="{{ $data['wallet_address'] }}"
                                                                            placeholder="Enter Wallet Address" required>
                                                                        @error('wallet_address')
                                                                            <span class="text-danger">
                                                                                {{ $message }}</span>
                                                                        @enderror
                                                                    </div>
                                                                </div>
                                                                <div class="col-xl-12">
                                                                    <div class="btn-list mt-3">
                                                                        <input type="hidden" class="form-control"
                                                                            value="{{ $data['id'] }}" name="id">
                                                                        <button type="submit"
                                                                            class="btn btn-primary float-end">Submit</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    @else
                                                        <form>
                                                            @csrf
                                                            <div class="row">
                                                                <div class="col-xl-12">
                                                                    <div class="mb-2">
                                                                        <label for="new-password" class="form-label">Wallet
                                                                            Address</label>
                                                                        <input type="text" class="form-control"
                                                                            name="wallet_address"
                                                                            value="{{ $data['wallet_address'] }}"
                                                                            placeholder="Enter Wallet Address" required readonly>
                                                                        @error('password')
                                                                            <span class="text-danger">
                                                                                {{ $message }}</span>
                                                                        @enderror
                                                                    </div>
                                                                </div>
                                                                <p class="text-danger card-description mt-3">*** Please contact admin to change your wallet address.
                                                                </p>
                                                            </div>
                                                        </form>
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
