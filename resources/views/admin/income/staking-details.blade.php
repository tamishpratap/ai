@extends('admin.layouts.main') 
@section('title', 'Staking Details')
@section('content')
    @push('head')
        <link rel="stylesheet" href="{{ asset('adm_assets/assets/plugins/DataTables/datatables.min.css')}}">
    @endpush
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="ik ik-edit bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('Staking Details')}}</h5>
                            <span>{{ __('All Staking Details')}}</span>
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
                            <li class="breadcrumb-item active" aria-current="page">{{ __('Staking Details')}}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-md-12">
                @if (session()->has('wMessage'))
                <div class="alert alert-primary">{{session('wMessage')}}</div> 
                @endif
                <div class="card">
                    <div class="card-header"><h3>{{ __('Staking Details')}}</h3></div>
                    <div class="card-body px-5 " style="overflow: auto">
                        <div class="responsive">
                            <table id="data_table" class="table">
                                <thead>
                                    <tr>
                                        <th>{{ __('S.No')}}</th>
                                        <th>{{ __('Invest Date')}}</th>
                                        <th>{{ __('Memberid')}}</th>
                                        <!--<th>{{ __('Package')}}</th> -->
                                        <th>{{ __('Paid Installments')}}</th>
                                        <th>{{ __('Invest Amount')}}</th>
                                        <!--<th>{{ __('Last Date')}}</th>-->
                                        <!--<th>{{ __('Rate')}}</th>-->
                                        
                                        <th>{{ __('Status')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $i = 1;  @endphp
                                    @foreach ($sdata as $list )
                                    <tr>
                                        <td>{{ $i }}</td>
                                        <td> {{ date('d-m-Y', strtotime($list['invest_date'])) }}</td>
                                        <td>{{ $list['memberid'] }}</td>
                                        <!--<td>$ {{ $list['package'] }}</td>-->
                                         <td>{{ $list['installments'] }}</td>
                                        <td>$ {{ number_format($list['invest_amount'], 0) }}</td>
                                        <!--<td>{{ date('d-m-Y', strtotime($list['lock_date'])) }}</td>-->
                                        <!--<td>{{ $list['rate'] }} %</td>-->
                                       
                                        <td>
                                            @if ($list['status'] == 'Active')
                                                <button class="btn btn-sm btn-success">{{ $list['status'] }}</button>
                                            @else
                                                <button class="btn btn-sm btn-danger ">{{ $list['status'] }}</button>
                                            @endif
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
    </div>

    @push('script')
        <script src="{{ asset('adm_assets/assets/plugins/DataTables/datatables.min.js') }}"></script>
        <script src="{{ asset('adm_assets/assets/js/datatables.js') }}"></script>
    @endpush
@endsection