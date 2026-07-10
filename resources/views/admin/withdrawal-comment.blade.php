@extends('admin.layouts.main')
@section('title', 'Withdrawal Comment')
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
                            <h5>{{ __('Withdrawal Comment') }}</h5>
                            <span>{{ __('All Withdrawal Comment') }}</span>
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
                            <li class="breadcrumb-item active" aria-current="page">{{ __('Withdrawal Comment') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-md-6">
                @if (session()->has('uploadMsg'))
                    <div class="alert alert-primary" role="alert">
                        {{ session('uploadMsg') }}
                    </div>
                @endif
                <div class="card">
                    <div class="card-header">
                        <h3>{{ __('Withdrawal Accept') }}</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('withdrawal.accept', $data['id']) }}" method="post">
                            @csrf
                            <div class="form-group">
                                <label>MemberId</label>
                                <input type="text" class="form-control" value="{{ $data['memberid'] }}" readonly>
                            </div>
                            <div class="form-group">
                                <label>Transaction Hash</label>
                                <input type="text" class="form-control" name="transaction_hash" placeholder="Enter Transaction Hash">
                                @error('transaction_hash')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Comment</label>
                                <textarea class="form-control" name="comment" placeholder="Enter Comment"></textarea>
                                @error('comment')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <button class="btn btn-success float-right">Accept</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                @if (session()->has('uploadMsg'))
                    <div class="alert alert-primary" role="alert">
                        {{ session('uploadMsg') }}
                    </div>
                @endif
                <div class="card">
                    <div class="card-header">
                        <h3>{{ __('Withdrawal Reject') }}</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('withdrawal.cancel', $data['id']) }}" method="post">
                            @csrf
                            <div class="form-group">
                                <label>MemberId</label>
                                <input type="text" class="form-control" value="{{ $data['memberid'] }}" readonly>
                            </div>
                            <div class="form-group">
                                <label>Comment</label>
                                <textarea class="form-control" name="comment" placeholder="Enter Comment"></textarea>
                                @error('comment')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <button class="btn btn-danger float-right">Reject</button>
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
