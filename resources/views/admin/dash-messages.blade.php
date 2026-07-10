@extends('admin.layouts.main')
@section('title', 'Dashboard Messages')
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
                            <h5>{{ __('Dashboard Messages') }}</h5>
                            <span>{{ __('All Dashboard Messages') }}</span>
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
                            <li class="breadcrumb-item active" aria-current="page">{{ __('Dashboard Messages') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-md-5">
                @if (session()->has('uploadMsg'))
                    <div class="alert alert-primary" role="alert">
                        {{ session('uploadMsg') }}
                    </div>
                @endif
                <div class="card">
                    <div class="card-header">
                        <h3>{{ __('Dashboard Messages Table') }}</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('dashMsg') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label>Rank </label>
                                <input type="number" class="form-control" name="rank" placeholder="Enter Rank">
                                @error('rank')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Message </label>
                                <input type="text" class="form-control" name="message" placeholder="Enter Message">
                                @error('message')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <button class="btn btn-primary float-right">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-7">
                @if (session()->has('delMsg'))
                    <div class="alert alert-primary" role="alert">
                        {{ session('delMsg') }}
                    </div>
                @endif
                <div class="card">
                    <div class="card-header">
                        <h3>{{ __('Image Table') }}</h3>
                    </div>
                    <div class="card-body px-5">
                        <table id="data_table" class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('S.No') }}</th>
                                    <th>{{ __('Rank') }}</th>
                                    <th>{{ __('Message') }}</th>
                                    <th class="text-center">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $i = 1;  @endphp
                                @foreach ($data as $list)
                                    <tr>
                                        <td>{{ $i }}</td>
                                        <td>{{ $list['rank'] }}</td>
                                        <td>{{ $list['message'] }}</td>
                                        <td>
                                            <div class="text-center">
                                                <a class="btn btn-danger text-white"
                                                    href="{{ url('admin/dash-messages/delete') }}/{{ $list['id'] }}">Delete</a>
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
