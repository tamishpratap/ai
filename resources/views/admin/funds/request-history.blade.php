@extends('admin.layouts.main')
@section('title', 'Fund Request History')
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
                            <h5>{{ __('Fund Request History') }}</h5>
                            <span>{{ __('All Fund Request History') }}</span>
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
                            <li class="breadcrumb-item active" aria-current="page">{{ __('Fund Request History') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-md-12">
                @if (session()->has('actMsg'))
                    <div class="alert alert-success" role="alert">{{ session('actMsg') }}</div>
                @endif
                @if (session()->has('failMsg'))
                    <div class="alert alert-danger" role="alert">{{ session('failMsg') }}</div>
                @endif
                <div class="card">
                    <div class="card-header">
                        <h3>{{ __('Fund Request History') }}</h3>
                    </div>
                    <div class="card-body px-5" style="overflow: auto">
                        <table id="data_table" class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('S.No') }}</th>
                                    <th>{{ __('Request Date') }}</th>
                                    <th>{{ __('Memberid') }}</th>
                                    <th>{{ __('TxnId') }}</th>
                                    <!--<th>{{ __('Type') }}</th>-->
                                    {{-- <th>{{ __('USDG Amount') }}</th> --}}
                                    <th>{{ __('USDT Amount') }}</th>
                                    <th>{{ __('Image') }}</th>
                                    <th>{{ __('Status') }}</th>

                                </tr>
                            </thead>
                            <tbody>
                                @php $i = 1;  @endphp
                                @foreach ($data as $list)
                                    <tr>
                                        <td>{{ $i }}</td>
                                        <td>{{ date('d-m-Y', strtotime($list['created_at'])) }}</td>
                                        <td>{{ $list['memberid'] }}</td>
                                        <td>{{ $list['tran_id'] }}</td>
                                        <!--<td>{{ $list['type'] }}</td>-->
                                        {{-- <td>{{ $list['usdg_amount'] }} USDG</td> --}}
                                        <td>$ {{ number_format($list['amount'],2) }}</td>
                                        <td class="img">
                                            <button class="btn btn-sm btn-warning packView" id="{{ $list['id'] }}">View
                                            </button>
                                        </td>
                                        <td>
                                            @if ($list['status'] == 'Accepted')
                                                <span class="badge badge-success"><b>{{ $list['status'] }}</b></span>
                                            @elseif ($list['status'] == 'Cancelled')
                                                <span class="badge badge-danger"><b>{{ $list['status'] }}</b></span>
                                            @else
                                                <span class="badge badge-primary"><b>{{ $list['status'] }}</b></span>
                                            @endif
                                        </td>
                                        
                                       
                                    </tr>
                                      <tr style="display:none" class="mySec{{ $list['id'] }}">
                                        <td colspan="10">
                                            <img src="{{ asset('uploads/qr-code') }}/{{ $list['image'] }}" width="400">
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
    <script>
        $(document).ready(function() {
            $('.packView').click(function() {
                var id = $(this).prop('id');
                $('.mySec' + id).toggle();
            });
        })
    </script>
@endsection
