@extends('admin.layouts.main')
@section('title', 'Withdrawal Requests')
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
                            <h5>{{ __('New Withdrawal Requests') }}</h5>
                            <span>{{ __('New Withdrawal Requests to verify') }}</span>
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
                            <li class="breadcrumb-item active" aria-current="page">{{ __('Withdrawal Requests') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-md-12">
            @include('admin.partials.history-date-filter')
                @if (session()->has('successMsg'))
                    <div class="alert alert-primary">{{ session('successMsg') }}</div>
                @endif
                 @if (session()->has('failedMsg'))
                    <div class="alert alert-danger">{{ session('failedMsg') }}</div>
                @endif
                <div class="card" class="overflow:auto">
                    <div class="card-header">
                        <h3>{{ __('Withdrawal Requests') }}</h3>
                    </div>
                    <div class="card-body mx-3">
                            <table id="data_table" class="table" style="width: 100%">
                                <thead>
                                    <tr>
                                        <th>{{ __('S.No') }}</th>
                                        <th>{{ __('Request Date') }}</th>
                                        <th>{{ __('Member Id') }}</th>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Wallet Address') }}</th>
                                        <th>{{ __('Gross') }}</th>
                                        <th>{{ __('Charges') }}</th>
                                        <th>{{ __('Net ') }}</th>
                                        <th colspan="3" class="text-center">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $i = 1;  @endphp
                                    @foreach ($withdrawlData as $list)
                                        @php
                                            $mdata = getAllData($list['memberid']);
                                        @endphp
                                        <tr>
                                            <td>{{ $i }}</td>
                                            <td>{{ date('d-m-Y', strtotime($list['request_date'])) }}<br>{{ date('H:i:s', strtotime($list['request_date'])) }}
                                            </td>
                                            <td>{{ $list['memberid'] }}</td>
                                            <td>{{ getName($list['memberid'])}}</td>
                                            <td><span class="badge badge-primary"><b>{{ $list['type'] }}</b></span></td>
                                            <td style="display:block; width:150px ">{{ $list['wallet_address'] }}</td>
                                            <td>${{ $list['gross_amount'] }}</td>
                                            <td>${{ $list['service_charge'] }}</td>
                                            <td>${{ $list['net_amount'] }}</td>
                                            <td class="px-0">
                                                <a href="{{ url('admin/withdrawal-accept/') }}/{{ $list['id'] }}"
                                                    class="btn btn-sm btn-success mx-0">Online Pay</a>
                                            </td>
                                            <td class="px-0">
                                                <form action="{{ route('withdrawal.accept', $list['id']) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to accept this withdrawal request?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-primary mx-2">Accept</button>
                                                </form>
                                            </td>
                                            <td class="px-0">
                                                <form action="{{ route('withdrawal.cancel', $list['id']) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this withdrawal request?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-danger mx-0">Cancel</button>
                                                </form>
                                            </td>
                                        </tr>
                                        @php $i++;  @endphp
                                    @endforeach
                                </tbody>
                            </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('script')
        <script src="{{ asset('adm_assets/assets/plugins/DataTables/datatables.min.js') }}"></script>
        <script src="{{ asset('adm_assets/assets/js/datatables.js') }}"></script>
    @endpush
@endsection
