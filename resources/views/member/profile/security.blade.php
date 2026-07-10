@extends('member.layouts.main')
@section('title', 'Security')
@section('container')

    <!-- MAIN-CONTENT -->

    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">
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
            @if (session()->has('successMsg_pass'))
                <div class="alert alert-success alert-dismissible fade show custom-alert-icon shadow-sm"
                    role="alert">
                    <svg style="fill: rgb(var(--success-rgb));" xmlns="http://www.w3.org/2000/svg" height="1.5rem"
                        viewBox="0 0 24 24" width="1.5rem" fill="#000000">
                        <path d="M0 0h24v24H0z" fill="none" />
                        <path
                            d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                    </svg>
                    {{ session('successMsg_pass') }}
                </div>
            @endif
            <!-- Start:: row-1 -->
            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="mb-3 mt-5">
                        <h5 class="fw-medium mb-0">Security</h5>
                        <div class="text-muted fs-13 op-8 mb-3">
                            Update your Password Change
                        </div>
                    </div>
                           
                </div>
                 <div class="col-lg-6">
                         <div class="card custom-card">
                            <div class="top-left"></div>
                            <div class="top-right"></div>
                            <div class="bottom-left"></div>
                            <div class="bottom-right"></div>
                            <div class="card-header"><h6 class="card-title">Change Transaction Password</h6></div>
                            <div class="card-body">
                                <div class="row mt-2 justify-content-center">
                                  <form action="{{ route('txnpassChange') }}" method="post">
                                        @csrf
                                        <div class="row">
                                            <div class="col-xl-12">
                                                <div class="mb-2">
                                                    <label for="new-password" class="form-label">New Transaction Password</label>
                                                    <input type="password" class="form-control" name="txn_password" placeholder="Enter Transaction Password" required>
                                                    @error('txn_password')
                                                        <span class="text-danger"> {{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-xl-12">
                                                <div class="mb-4">
                                                    <label for="confirm-password" class="form-label">Confirm Transaction Password</label>
                                                    <input type="password" class="form-control"
                                                    name="txn_password_confirm" placeholder="Enter Confirm Transaction Password" required>
                                                    @error('txn_password_confirm')
                                                        <span class="text-danger"> {{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-xl-12">
                                                <div class="btn-list mb-4">
                                                    <input type="hidden" class="form-control" value="{{$data['id']}}" name="id">
                                                    <button type="submit"
                                                        class="btn btn-primary float-end">Change
                                                        Transaction Password</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card custom-card">
                            <div class="top-left"></div>
                            <div class="top-right"></div>
                            <div class="bottom-left"></div>
                            <div class="bottom-right"></div>
                            <div class="card-header"><h6 class="card-title">Change Password</h6></div>
                            <div class="card-body">
                                <div class="row mt-2 justify-content-center">
                                     <div>
                                            <form action="{{ route('passChange') }}" method="post">
                                                @csrf
                                                <div class="row">
                                                    <div class="col-xl-12">
                                                        <div class="mb-2">
                                                            <label for="new-password" class="form-label">New
                                                                Password</label>
                                                            <input type="password" class="form-control" name="password" placeholder="Enter Password" required>
                                                            @error('password')
                                                                <span class="text-danger"> {{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-12">
                                                        <div class="mb-4">
                                                            <label for="confirm-password" class="form-label">Confirm Password</label>
                                                            <input type="password" class="form-control"
                                                            name="password_confirm" placeholder="Enter Confirm Password" required>
                                                            @error('password_confirm')
                                                                <span class="text-danger"> {{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-12">
                                                        <div class="btn-list mb-4">
                                                            <input type="hidden" class="form-control" value="{{$data['id']}}" name="id">
                                                            <button type="submit" class="btn btn-primary float-end">Change
                                                                Password</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
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
