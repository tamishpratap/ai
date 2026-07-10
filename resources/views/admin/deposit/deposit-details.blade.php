@extends('admin.layouts.main')
@section('title', 'Deposit Details')
@section('content')
    @push('head')
        <link rel="stylesheet" href="{{ asset('adm_assets/assets/plugins/DataTables/datatables.min.css') }}">
    @endpush
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="ik ik-edit bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('Deposit Details') }}</h5>
                            <span>{{ __('All Deposit Details') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <nav class="breadcrumb-container" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="#"><i class="ik ik-home"></i></a>
                            </li>
                            <li class="breadcrumb-item"><a href="#">{{ __('Admin') }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('Deposit Details') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>


        <div class="row">
            {{-- <div class="col-lg-12">
                @if (session()->has('failedMsg'))
                    <div class="alert alert-danger" role="alert">
                        {{ session('failedMsg') }}
                    </div>
                @endif
                @if (session()->has('successMsg'))
                    <div class="alert alert-success" role="alert">
                        {{ session('successMsg') }}
                    </div>
                @endif
                <div class="card">
                    <div class="card-header">
                        <h3>{{ __('Bank Deposit Details Form') }}</h3>
                    </div>
                    <div class="card-body ">

                        <form action="{{ route('depositDataInr') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="form-group col-4 ">
                                    <label>Bank </label>
                                    <input type="text" class="form-control" name="bank" placeholder="Enter Bank Name">
                                    @error('bank')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group col-4">
                                    <label>IFSC </label>
                                    <input type="text" class="form-control" name="ifsc" placeholder="Enter IFSC Code">
                                    @error('ifsc')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group col-4">
                                    <label>Account No </label>
                                    <input type="text" class="form-control" name="account_no"
                                        placeholder="Enter Account No">
                                    @error('account_no')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-6">
                                    <label>UPI </label>
                                    <input type="text" class="form-control" name="upi" placeholder="Enter UPI">
                                    @error('upi')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group col-6">
                                    <label>QR Code </label>
                                    <input type="file" class="form-control" name="qrcode" placeholder="Upload QR Code">
                                    @error('qrcode')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-12 text-center">
                                    <button class="btn btn-primary my-3">Submit Depost Details</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div> --}}
            <div class="col-lg-12">
                @if (session()->has('failedMsg2'))
                    <div class="alert alert-danger" role="alert">
                        {{ session('failedMsg2') }}
                    </div>
                @endif
                @if (session()->has('successMsg2'))
                    <div class="alert alert-success" role="alert">
                        {{ session('successMsg2') }}
                    </div>
                @endif
                <div class="card">
                    <div class="card-header">
                        <h3>{{ __('Deposit Details Form') }}</h3>
                    </div>
                    <div class="card-body ">

                        <form action="{{ route('depositDataUSDT') }}" method="post" enctype="multipart/form-data">
                            @csrf

                            <div class="row">
                                <div class="form-group col-6">
                                    <label>Wallet Address </label>
                                    <input type="text" class="form-control" name="wallet"
                                        placeholder="Enter Wallet Address">
                                    @error('wallet')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group col-6">
                                    <label>QR Code </label>
                                    <input type="file" class="form-control" name="uqrcode" placeholder="Upload QR Code">
                                    @error('uqrcode')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-12 text-center">
                                    <button class="btn btn-primary my-3">Submit Depost Details</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                @if (session()->has('delMsg'))
                    <div class="alert alert-primary" role="alert">
                        {{ session('delMsg') }}
                    </div>
                @endif
                <div class="card">
                    <div class="card-header">
                        <h3>{{ __('Deposit Details') }}</h3>
                    </div>
                    <div class="card-body px-5" style="overflow: auto">
                        <table id="data_table" class="table text-center">
                            <thead>
                                <tr>
                                    <th>{{ __('S.No') }}</th>
                                    <th>{{ __('QR Image') }}</th>
                                    <th>{{ __('Wallet Address') }}</th>
                                    <th>{{ __('Status') }}</th>

                                </tr>
                            </thead>
                            <tbody>
                                @php $i = 1;  @endphp
                                @foreach ($data as $list)
                                    @if ($list['address'] != NULL)
                                    <tr>
                                        <td>{{ $i }}</td>
                                        <td><img src="{{ asset('uploads/qr-code') }}/{{ $list['qrcode'] }}"
                                                class="img-responsive" alt="" height="45"></td>
                                        
                                        <td>
                                            @if ($list['address'] == null)
                                                --
                                            @else
                                                {{ $list['address'] }}
                                            @endif
                                        </td>
                                        <td>
                                            @if ($list['status'] == 'Not Live')
                                                <a class="btn btn-danger "
                                                    href="{{ url('admin/deposit-status') }}/{{ $list['id'] }}">{{ $list['status'] }}</a>
                                            @else
                                                <a class="btn btn-success " href="#">{{ $list['status'] }}</a>
                                            @endif

                                        </td>
                                    </tr>
                                    @endif
                                    
                                    @php $i++;  @endphp
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{-- <div class="card-body px-5" style="overflow: auto">
                        <table id="data_table" class="table text-center">
                            <thead>
                                <tr>
                                    <th>{{ __('S.No') }}</th>
                                    <th>{{ __('QR Image') }}</th>
                                    <th>{{ __('Bank') }}</th>
                                    <th>{{ __('IFSC') }}</th>
                                    <th>{{ __('Account No') }}</th>
                                    <th>{{ __('UPI') }}</th>
                                    <th>{{ __('Status') }}</th>

                                </tr>
                            </thead>
                            <tbody>
                                @php $i = 1;  @endphp
                                @foreach ($data as $list)
                                    @if ($list['address'] == NULL)
                                    <tr>
                                        <td>{{ $i }}</td>
                                        <td><img src="{{ asset('uploads') }}/{{ $list['qrcode'] }}"
                                                class="img-responsive" alt="" height="45"></td>
                                        <td>
                                            @if ($list['bank'] == null)
                                                --
                                            @else
                                                {{ $list['bank'] }}
                                            @endif
                                        </td>
                                        <td>
                                            @if ($list['ifsc'] == null)
                                                --
                                            @else
                                                {{ $list['ifsc'] }}
                                            @endif
                                        </td>
                                        <td>
                                            @if ($list['account'] == null)
                                                --
                                            @else
                                                {{ $list['account'] }}
                                            @endif
                                        </td>
                                        <td>
                                            @if ($list['upi'] == null)
                                                --
                                            @else
                                                {{ $list['upi'] }}
                                            @endif
                                        </td>
                                        <td>
                                            @if ($list['status'] == 'Not Live')
                                                <a class="btn btn-danger "
                                                    href="{{ url('admin/deposit-status') }}/{{ $list['id'] }}">{{ $list['status'] }}</a>
                                            @else
                                                <a class="btn btn-success " href="#">{{ $list['status'] }}</a>
                                            @endif

                                        </td>
                                    </tr>
                                    @endif
                                    @php $i++;  @endphp
                                @endforeach
                            </tbody>
                        </table>
                    </div> --}}
                </div>
            </div>
        </div>
    </div>

    @push('script')
        <script src="{{ asset('adm_assets/assets/plugins/DataTables/datatables.min.js') }}"></script>
        <script src="{{ asset('adm_assets/assets/js/datatables.js') }}"></script>
        <script src="{{ asset('adm_assets/assets/js/form-components.js') }}"></script>
    @endpush
@endsection
