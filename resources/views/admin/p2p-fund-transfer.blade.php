@extends('admin.layouts.main') 
@section('title', 'P2P Fund Transfer')
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
                            <h5>{{ __('P2P Fund Transfer')}}</h5>
                            <span>{{ __('P2P Fund Transfer details')}}</span>
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
                            <li class="breadcrumb-item active" aria-current="page">{{ __('P2P Fund Transfer')}}</li>
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
                    <div class="card-header"><h3>{{ __('P2P Fund Transfer')}}</h3></div>
                    <div class="card-body px-5">
                        <div class="responsive">
                            <table id="data_table" class="table">
                                <thead>
                                    <tr>
                                        <th>{{ __('S.No')}}</th>
                                        <th>{{ __('Date')}}</th>
                                        <th>{{ __('Sender Id')}}</th>
                                        <th>{{ __('Reciever Id')}}</th>
                                        <th>{{ __('Amount')}}</th>
                                        <th>{{ __('Deduction')}}</th>
                                        <th>{{ __('Net Amount')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $i = 1;  @endphp
                                    @foreach ($p2pdata as $list )
                                        <tr>
                                            <td>{{$i}}</td>
                                            <td>{{date('d-m-Y', strtotime($list['created_at']))}}</td>
                                            <td>{{ $list['senderid']}}</td>
                                            <td>{{ $list['receiverid']}}</td>
                                            <td>{{ $list['amount']}}</td>
                                            <td>{{ $list['deduction']}}</td>
                                            <td>{{ $list['net_amount']}}</td>
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
    </div>

    @push('script')
        <script src="{{ asset('adm_assets/assets/plugins/DataTables/datatables.min.js') }}"></script>
        <script src="{{ asset('adm_assets/assets/js/datatables.js') }}"></script>
    @endpush
@endsection