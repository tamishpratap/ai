@extends('admin.layouts.main') 
@section('title', 'Cancelled Withdrawal Requests')
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
                            <h5>{{ __('Cancelled Withdrawal Requests')}}</h5>
                            <span>{{ __('Cancelled Withdrawal Requests ')}}</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <nav class="breadcrumb-container" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="#"><i class="ik ik-home"></i></a>
                            </li>
                            <li class="breadcrumb-item"><a href="#">{{ __('Admin')}}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('Cancelled Withdrawal Requests')}}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    
    
        <div class="row">
            <div class="col-md-12">
                
            @include('admin.partials.history-date-filter')
                @if (session()->has('wMessage'))
                   <div class="alert alert-primary">{{session('wMessage')}}</div> 
                @endif
                <div class="card">
                    <div class="card-header"><h3>{{ __('Cancelled Withdrawal Requests')}}</h3></div>
                    <div class="card-body px-5"  style="overflow: auto">
                        <table id="data_table" class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('S.No')}}</th>
                                    <th>{{ __('Request Date')}}</th>
                                    <th>{{ __('Request Id') }}</th>
                                    <th>{{ __('Member Id')}}</th>
                                    <th>{{ __('Wallet Address')}}</th>
                                    <!--<th>{{ __('USDT Amount') }}</th>-->
                                    <th>{{ __('Gross Amount') }}</th>
                                    <th>{{ __('Charges Amount') }}</th>
                                    <th>{{ __('Net Amount') }}</th>
                                    <th>{{ __('Cancelled On ')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $i = 1;  @endphp
                                @foreach ($data as $list )
                                    <tr>
                                        <td>{{$i}}</td>
                                        <td>{{ date('d-m-Y', strtotime($list['request_date']))}}<br>{{ date('H:i:s', strtotime($list['request_date']))}}</td>
                                        <td>{{ $list['request_id'] }}</td>
                                        <td>{{ $list['memberid']}}</td>
                                        <td>{{ $list['wallet_address']}}</td>
                                        <!--<td>$ {{ $list['usdt_amount'] }}</td>-->
                                        <td>
                                            @if ($list['type'] == 'Coin')
                                                $ {{ number_format($list['gross_amount'], 2) }}
                                            @else
                                                $ {{ number_format($list['gross_amount'],2) }}
                                            @endif
                                        </td>
                                        <td>
                                            @if ($list['type'] == 'Coin')
                                                $ {{ number_format($list['service_charge'], 2) }}
                                            @else
                                                $ {{ number_format($list['service_charge'],2) }}
                                            @endif
                                        </td>
                                        <td>
                                            @if ($list['type'] == 'Coin')
                                                $ {{ number_format($list['net_amount'], 2) }}
                                            @else
                                                $ {{ number_format($list['net_amount'],2) }}
                                            @endif
                                        </td>
                                        <td>{{date('d-m-Y', strtotime( $list['updated_at']))}}</td>
                                       
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