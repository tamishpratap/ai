@extends('admin.layouts.main')
@section('title', 'Member gallery')
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
                            <h5>{{ __('Member gallery') }}</h5>
                            <span>{{ __('All Member gallery') }}</span>
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
                            <li class="breadcrumb-item active" aria-current="page">{{ __('Member gallery') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                @if (session()->has('successMsg'))
                    <div class="alert alert-success">{{ session('successMsg') }}</div>
                @endif
                <div class="card">
                    <div class="card-header">
                        <h3>{{ __('Member Galleries') }}</h3>
                    </div>
                    <div class="card-body px-3" style="overflow: auto">
                        <div class="table-responsive px-4 text-center">
                            <table id="data_table" class="table">
                                <thead>
                                    <tr>
                                        <th>{{ __('S.No') }}</th>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('MemberID') }}</th>
                                        <th>{{ __('Title') }}</th>
                                        <th>{{ __('Image') }}</th>
                                        <th>{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $i = 1;  @endphp
                                    @foreach ($data as $list)
                                        <tr>
                                            <td>{{ $i }}</td>
                                            <td>{{ date('d-m-Y', strtotime($list['created_at'])) }}<br>{{ date('h:i:s', strtotime($list['created_at'])) }}
                                            </td>
                                            <td>{{ $list['memberid'] }}</td>
                                            <td>{{ $list['title'] }}</td>
                                            <td>
                                                @if ($list['image'] == '')
                                                    <img src="{{ asset('uploads/avatar.jpg') }}" class="table-user-thumb"
                                                        alt="">
                                                @else
                                                    <img src="{{ asset('uploads') }}/{{ $list['image'] }}"
                                                        class="table-user-thumb" alt="">
                                                @endif
                                            </td>
                                            <td>
                                                @if ($list['status'] == 'Live')
                                                    <div class="text-white text-center badge badge-primary">
                                                        <a class="text-white"
                                                            href="{{ url('admin/member-gallery/status-change') }}/{{ $list['id'] }}">Live</a>
                                                    </div>
                                                @else
                                                    <div class="text-white text-center badge badge-danger">
                                                        <a class="text-white"
                                                            href="{{ url('admin/member-gallery/status-change') }}/{{ $list['id'] }}">
                                                            Non Live</a>
                                                    </div>
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
        <script src="{{ asset('adm_assets/assets/js/form-components.js') }}"></script>
    @endpush
@endsection
