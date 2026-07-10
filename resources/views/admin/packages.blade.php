@extends('admin.layouts.main')
@section('title', 'Packages')
@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="ik ik-box bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('Packages') }}</h5>
                            <span>{{ __('Joining package tiers') }}</span>
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
                            <li class="breadcrumb-item active" aria-current="page">{{ __('Packages') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-10">
                @if (session()->has('successMsg'))
                    <div class="alert alert-primary">{{ session('successMsg') }}</div>
                @endif
                <div class="card">
                    <div class="card-header">
                        <h3>{{ __('Packages') }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Sort</th>
                                        <th>Name</th>
                                        <th>Amount (USDT)</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data as $package)
                                        <tr>
                                            <td>{{ $package->sort_order }}</td>
                                            <td>{{ $package->name }}</td>
                                            <td>$ {{ number_format($package->amount, 2) }}</td>
                                            <td>
                                                @if ($package->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <form action="{{ url('admin/packages/' . $package->id . '/status') }}" method="post">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm {{ $package->is_active ? 'btn-danger' : 'btn-success' }}">
                                                        {{ $package->is_active ? 'Deactivate' : 'Activate' }}
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
