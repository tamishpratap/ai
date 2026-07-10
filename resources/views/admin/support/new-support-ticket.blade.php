@extends('admin.layouts.main') 
@section('title', 'New Support Tickets')
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
                            <h5>{{ __('New Support Tickets')}}</h5>
                            <span>{{ __('New Support Tickets ')}}</span>
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
                            <li class="breadcrumb-item active" aria-current="page">{{ __('New Support Tickets')}}</li>
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
                    <div class="card-header"><h3>{{ __('Open Support Tickets')}}</h3></div>
                    <div class="card-body px-5">
                        <table id="data_table" class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('S.No')}}</th>
                                    <th>{{ __('Request Date')}}</th>
                                    <th>{{ __('Ticket ID')}}</th>
                                    <th>{{ __('Memberid')}}</th>
                                    <th>{{ __('Name')}}</th>
                                    <th>{{ __('Subject')}}</th>
                                    <th>{{ __('Action')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $i = 1;  @endphp
                                @foreach ($support as $list ) 
                                    <tr>
                                        <td>{{$i}}</td>
                                        <td>{{date('d-M-Y',strtotime($list['created_at']))}}</td>
                                        <td>{{ $list['ticket_id']}}</td>
                                        <td>{{ $list['memberid']}}</td>
                                        <td>{{getName($list['memberid']) }}</td>
                                        <td>{{ $list['subject']}}</td>
                                        <td>
                                            <div >
                                                <a href="{{url('admin/support/view-support-ticket')}}/{{$list['id']}}" class="btn btn-sm btn-primary ">View</a>
                                            </div>
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