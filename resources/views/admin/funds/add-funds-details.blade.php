@extends('admin.layouts.main')
@section('title', 'Funds Details')
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
                            <h5>{{ __('Funds Details') }}</h5>
                            <span>{{ __('All Funds Details') }}</span>
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
                            <li class="breadcrumb-item active" aria-current="page">{{ __(' Funds Details') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-md-12">
                @if (session()->has('failedMsg'))
                    <div class="alert alert-danger" role="alert">
                        {{ session('failedMsg') }}
                    </div>
                @endif
                @if (session()->has('successMsg'))
                    <div class="alert alert-success" role="alert">
                        {{ session('successMsg') }}
                    </div>
                @endif
                <div class="card">
                    <div class="card-header">
                        <h3>{{ __(' Funds Details') }}</h3>
                        <span class="ml-auto mr-2"><a href="{{ url('admin/funds/add-funds') }}"
                                class="btn btn-sm btn-success text-white"> + Add Funds</a></span>
                        <span class="ml-2 mr-0"><a href="{{ url('admin/funds/deduct-funds') }}"
                                class="btn btn-sm btn-danger text-white"> - Deduct Funds</a></span>
                    </div>
                    <div class="card-body px-5" style="overflow:auto">
                        <table id="data_table" class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('S.No') }}</th>
                                    <th>{{ __('Time') }}</th>
                                    <th>{{ __('Memberid') }}</th>
                                    <th>{{ __('OrderId') }}</th>
                                    {{-- <th>{{ __('Txnid') }}</th> --}}
                                    <th>{{ __('Wallet Type') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Mode') }}</th>
                                    <th>{{ __('Status') }}</th>

                                </tr>
                            </thead>
                            <tbody>
                                @php $i = 1;  @endphp
                                @foreach ($data as $list)
                                    <tr>
                                        <td>{{ $i }}</td>
                                        <td>{{ $list['created_at'] }}</td>
                                        <td>{{ $list['memberid'] }}</td>
                                        <td>{{ $list['orderid'] }}</td>
                                        {{-- <td>{{ $list['txnid'] }}</td> --}}
                                        <td>{{ $list['wallet_type'] }}</td>
                                        <td>$ {{ number_format($list['amount'], 2) }}</td>
                                        <td>
                                            @if ($list['type'] == 'Add')
                                                <span class="badge badge-success">{{ $list['type'] }}</span>
                                            @else
                                                <span class="badge badge-danger">{{ $list['type'] }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($list['mode'] == 'Online')
                                                <span class="badge badge-warning">{{ $list['mode'] }}</span>
                                            @else
                                                <span class="badge badge-primary">{{ $list['mode'] }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="badge badge-success">
                                                {{ $list['status'] }}
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
        <script src="{{ asset('adm_assets/assets/js/form-components.js') }}"></script>
    @endpush
@endsection
