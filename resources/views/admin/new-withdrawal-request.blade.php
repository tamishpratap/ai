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
                @if (session()->has('wMessage'))
                    <div class="alert alert-primary">{{ session('wMessage') }}</div>
                @endif
                <div class="card" class="overflow:auto">
                    <div class="card-header">
                        <h3>{{ __('Withdrawal Requests') }}</h3>
                    </div>
                    <div class="card-body mx-3" style="overflow: auto">
                            <table id="data_table" class="table" style="width: 100%">
                                <thead>
                                    <tr>
                                        <th>{{ __('S.No') }}</th>
                                        <th>{{ __('Request Date') }}</th>
                                        <th>{{ __('Request Id') }}</th>
                                        <th>{{ __('Member Id') }}</th>
                                        <th>{{ __('Wallet Address') }}</th>
                                        <!--<th>{{ __('USDT Amount') }}</th>-->
                                        <th>{{ __('Gross Amount') }}</th>
                                        <th>{{ __('Charges Amount') }}</th>
                                        <th>{{ __('Net Amount') }}</th>
                                        {{-- <th>{{ __('Action') }}</th> --}}
                                        <th>{{ __('Action') }}</th>
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
                                            <td>{{ $list['request_id'] }}</td>
                                            <td>{{ $list['memberid'] }}</td>
                                            <td>{{ $list['wallet_address'] }}</td>
                                            <!--<td>$ {{ $list['usdt_amount'] }}</td>-->
                                            <td>$ {{ number_format($list['gross_amount'],2) }}</td>
                                            <td>$ {{ number_format($list['service_charge'],2) }}</td>
                                            <td>$ {{ number_format($list['net_amount'],2) }}</td>
                                            <td class="px-0">
                                                <a href="{{ url('admin/withdrawal/withdrawal-comment') }}/{{ $list['id'] }}"
                                                    class="btn btn-sm btn-info mx-">Withdrawal Comment</a>
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
