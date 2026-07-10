@extends('admin.layouts.main')
@section('title', 'Cancelled Requests')
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
                        <h5>{{ __('Cancelled Requests')}}</h5>
                        <span>{{ __('All Cancelled Requests')}}</span>
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
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Cancelled Requests')}}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">
            @if (session()->has('actMsg'))
               <div class="alert alert-success" role="alert">{{session('actMsg')}}</div>
            @endif
            @if (session()->has('failMsg'))
               <div class="alert alert-danger" role="alert">{{session('failMsg')}}</div>
            @endif
            <div class="card">
                <div class="card-header"><h3>{{ __('Cancelled Requests')}}</h3></div>
                <div class="card-body px-5" style="overflow: auto">
                    <table id="data_table" class="table">
                        <thead>
                            <tr>
                                <th>{{ __('S.No')}}</th>
                                <th>{{ __('Request Date')}}</th>
                                <th>{{ __('Memberid')}}</th>
                                <th>{{ __('Name')}}</th>
                                <th>{{ __('TxnId')}}</th>
                                <th>{{ __('Amount')}}</th>
                                <th>{{ __('Mode')}}</th>
                                <th>{{ __('Status')}}</th>
                                <th>{{ __('Image')}}</th>
                                <th class="text-center">{{ __('Accept')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $i = 1;  @endphp
                            @foreach ($data as $list )
                                <tr>
                                    <td>{{$i}}</td>
                                    <td>{{ date('d-m-Y', strtotime($list['created_at']))}}</td>
                                    <td>{{ $list['memberid']}}</td>
                                    <td>{{ getName($list['memberid'])}}</td>
                                    <td style="word-break: break-all">{{ $list['txnid']}}</td>
                                    <td>$ {{ $list['amount']}}</td>
                                    <td>{{ $list['mode']}}</td>
                                    <td>{{ $list['status']}}</td>
                                    <td class="img">
                                        <button class="btn btn-sm btn-warning packView" id="{{$list['id']}}" >View </button>
                                    </td>
                                    <td><a href="{{url('admin/accept-request')}}/{{$list['id']}}" class="btn btn-sm btn-success">Accept</a></td>

                                </tr>
                                <tr style="display:none"  class="mySec{{$list['id']}}">
                                    <td colspan="10">
                                        <img src="{{asset('uploads')}}/{{$list['image']}}" width="400">
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
        $(document).ready(function(){
           $('.packView').click(function(){
              var id = $(this).prop('id');
             $('.mySec'+id).toggle();
           });
        })
    </script>
@endsection
