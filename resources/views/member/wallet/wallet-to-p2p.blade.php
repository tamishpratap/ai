@extends('member.layouts.main')
@section('title', 'Wallet To P2P')
@section('container')
    <style>
        /* Extra small devices (phones, 600px and down) */
        @media only screen and (max-width: 600px) {
            .alert {
                margin-top: 20px;
            }

            div:has(.custom-card) {
                margin-top: 10px;
            }
        }

        /* Small devices (portrait tablets and large phones, 600px and up) */
        @media only screen and (min-width: 600px) {
            .alert {
                margin-top: 20px;
            }

            div:has(.custom-card) {
                margin-top: 10px;
            }
        }

        /* Medium devices (landscape tablets, 768px and up) */
        @media only screen and (min-width: 768px) {
            .alert {
                margin-top: 20px;
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

    <!-- MAIN-CONTENT -->

    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Start::row-1 -->
            <div class="row">
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
                <div class="col-xl-6">
                    <div class="card custom-card currency-exchange-card">
                        <div class="top-left"></div>
                        <div class="top-right"></div>
                        <div class="bottom-left"></div>
                        <div class="bottom-right"></div>
                        <form action="{{ route('walletP2pTransfer') }}" method="post" class="myForm">
                            <input type="hidden" id="csrf" value="{{ csrf_token() }}">
                            @csrf
                            <div class="card-body p-3 d-flex align-items-center justify-content-center">
                                <div class="container">
                                    <div class="row justify-content-center">
                                        <img src="{{ asset('uploads/avatar.jpg') }}" id="memImg" alt="img"
                                            class="bg-info p-2 img-thummbnail"
                                            style="object-fit: cover;object-position: top center;border-radius:50%;width: 200px;height: 200px;">
                                        {{-- <img src="{{asset('mem_assets/images/p2p.png')}}" class="bg-info p-3 img-thummbnail" style=" object-fit: cover;object-position: top center;border-radius:50%;width: 200px;height: 200px;"> --}}
                                    </div>
                                    <h3 class="text-fixed-white text-center">Stake Wallet To P2P</h3>
                                    <div class="text-center mb-3">
                                        <span class="btn btn-success-gradient btn-wave rounded-pill">Balance:
                                            ${{ $data['lending_wallet'] }}</span>
                                    </div>
                                    <div class="p-3 mb-4 rounded currency-exchange-area">
                                        <div class="row gy-3">
                                            <div class="col-xxl-12 col-12">
                                                <label>Amount</label>
                                                <input type="number" id="amount" class="form-control" name="amount"
                                                    placeholder="Enter Amount" required>
                                                @error('amount')
                                                    <b><span class="text-danger">{{ $message }}</span></b>
                                                @enderror
                                                <b><small id="amountText" class="text-danger"></small></b>
                                            </div>
                                            <div class="col-xxl-12 col-12">
                                                <label>Security PIN</label>
                                                <input type="password" class="form-control" placeholder="Enter Security PIN"
                                                    name="pin" required>
                                                @error('pin')
                                                    <b><span class="text-danger">{{ $message }}</span></b>
                                                @enderror
                                            </div>
                                            <div class="col-xxl-12 col-12 d-grid gap-2">
                                                <input type="hidden" name="balance" id="balance"
                                                    value="{{ $data['lending_wallet'] }}">
                                                <button type="submit" class="btn btn-success btn-wave subBtn">Transfer
                                                    Amount</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <button class="btn btn btn-orange-gradient" style="width: 100%" id="viewBtn">VIEW HISTORY</button>
                </div>
                <div class="col-xl-12" id="fundHis" style="display: none">
                    <div class="card custom-card">
                        <div class="top-left"></div>
                        <div class="top-right"></div>
                        <div class="bottom-left"></div>
                        <div class="bottom-right"></div>
                        <div class="card-header justify-content-between pb-3">
                            <div class="card-title">
                                Wallet To P2P History
                            </div>
                        </div>
                        <div class="card-body ">
                            <div class="table-responsive">
                                <table id="example" class="table text-nowrap">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Date</th>
                                            <th width="50%">Particular</th>
                                            <th>Add</th>
                                            <th>Deduct</th>
                                            <th>Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $i = 1;
                                        @endphp
                                        @foreach ($passdata as $list)
                                            <tr>
                                                <td>{{ $i }}</td>
                                                <td>{{ $list['created_at'] }}</td>
                                                <td>{{ $list['particular'] }}</td>
                                                <td>{{ $list['debit'] }}</td>
                                                <td>{{ $list['credit'] }}</td>
                                                <td> <label class="badge bg-warning">{{ $list['balance'] }}</label></td>
                                            </tr>
                                            @php
                                                $i++;
                                            @endphp
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--End::row-1 -->

            <!-- Start:: row-2 -->
            <div class="row justify-content-center">

            </div>
            <!-- End:: row-2 -->

        </div>
    </div>
    <!-- End::app-content -->

    <!-- END MAIN-CONTENT -->

    @push('user-scripts')
        <script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
        <script>
            $(function() {
                $('#fundmemberid').on('input', function() {
                    var memberid = $(this).val();
                    var csrf = $('#csrf').val();
                    var path = window.location.pathname;
                    $('#memImg').attr("src",
                        "{{ asset('uploads/avatar.jpg') }}"
                    )
                    if (memberid.length == 0) {
                        $('#memMsg').html('');
                        $('#transBtn').attr('disabled', false);
                    }
                    $.ajax({
                        url: '/getMember',
                        type: 'POST',
                        data: {
                            'memberid': memberid,
                            _token: csrf,
                        },
                        success: function(response) {
                            $('#memMsg').html(response['data']);
                            if (response['code'] == 0) {
                                $('#transBtn').attr('disabled', true);
                                $('#memImg').attr("src",
                                    "{{ asset('uploads/avatar.jpg') }}"
                                )
                            } else {
                                if (response['code'] == 1) {
                                    if (response['image'] != null) {
                                        $('#memImg').attr("src",
                                            "{{ asset('uploads') }}/" +
                                            response['image']
                                        )
                                    }
                                    if (response['image'] == null) {
                                        $('#memImg').attr("src",
                                            "{{ asset('uploads/avatar.jpg') }}"
                                        )
                                    }
                                }
                                $('#transBtn').attr('disabled', false);
                            }
                        }
                    });
                });

                //BALANCE Check
                $('#amount').on('input', function() {
                    var amount = parseInt($(this).val());
                    var balance = parseInt($('#balance').val());

                    if (balance >= amount) {
                        $('#amountText').html('');
                        $('.subBtn').attr("disabled", false);
                    } else if (balance < amount) {
                        $('#amountText').html('You do not have enough balance to transfer this amount');
                        $('.subBtn').attr('disabled', true);
                    }
                });

                $('#viewBtn').on("click", function() {
                    $(this).text(function(i, text) {
                        return text === "VIEW HISTORY" ? "CLOSE HISTORY" : "VIEW HISTORY";
                    });
                    $('#fundHis').toggle();
                })
            })
        </script>
    @endpush

@endsection
