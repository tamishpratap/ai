@extends('admin.layouts.main')
@section('title', 'Withdrawal Request')
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
                        <h5>{{ __('Withdrawal Request')}}</h5>
                        <span>{{ __('All Withdrawal Request')}}</span>
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
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Withdrawal Request')}}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-md-5">
            @if (session()->has('uploadMsg'))
                <div class="alert alert-primary" role="alert">
                    {{session('uploadMsg')}}
                </div>
            @endif
            <div class="card">
                <div class="card-header">
                    <h3>{{ __('Withdrawal Request Form')}}</h3>
                </div>
                <div class="card-body">
                    <form action="{{route('sendWdlRequest')}}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label>Memberid </label>
                            <input type="text" class="form-control" name="memberid" value="{{$udata['memberid']}}" readonly>
                            @error('memberid')
                                <span class="text-danger">{{$message}}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>Wallet Address </label>
                            <input type="text" class="form-control" name="wallet" value="{{$udata['wallet_address']}}" readonly>
                            @error('wallet')
                                <span class="text-danger">{{$message}}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>Net Amount ($)</label>
                            <input type="text" class="form-control" name="net_amount" value="{{$udata['net_amount']}}" readonly>
                            @error('net_amount')
                                <span class="text-danger">{{$message}}</span>
                            @enderror
                        </div>
                        <div>
                            <input type="hidden" class="form-control" name="wid" value="{{$udata['id']}}">
                            <button class="btn btn-primary float-right">Accept Request</button>
                        </div>
                    </form>
                </div>
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
