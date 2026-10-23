@extends('admin.layouts.main') 
@section('title', 'View Closed Tickets')
@section('content')
    @push('head')
        <link rel="stylesheet" href="{{ asset('adm_assets/assets/plugins/DataTables/datatables.min.css') }}">
        <link rel="stylesheet" href="{{ asset('adm_assets/assets/plugins/weather-icons/css/weather-icons.min.css') }}">
        <link rel="stylesheet" href="{{ asset('adm_assets/assets/plugins/tempusdominus-bootstrap-4/build/css/tempusdominus-bootstrap-4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('adm_assets/assets/plugins/jvectormap/jquery-jvectormap.css') }}">
    @endpush
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="ik ik-edit bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('View Closed Tickets')}}</h5>
                            <span>{{ __('View Closed Tickets ')}}</span>
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
                            <li class="breadcrumb-item active" aria-current="page">{{ __('View Closed Tickets')}}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-8 col-md-12">
            @include('admin.partials.history-date-filter')
                <div class="card">
                    <div class="card-header">
                        <h3>Closed Chat</h3>
                        <div class="card-header-right">
                            <ul class="list-unstyled card-option">
                                <li><i class="ik ik-chevron-left action-toggle"></i></li>
                                <li><i class="ik ik-minus minimize-card"></i></li>
                                <li><i class="ik ik-x close-card"></i></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body chat-box scrollable card-300" >
                        <ul class="chat-list">
                            @foreach ($tictext as $item)
                                @if ($item['written_by'] == 'Member')
                                    <li class="chat-item">
                                        <div class="chat-img"><img src="{{asset('uploads/avatar.jpg')}}" alt="user"></div>
                                        <div class="chat-content">
                                            <h6 class="font-medium">{{getName($support['memberid'])}}</h6>
                                            <div class="box bg-light-info">{{$item['text']}}</div>
                                        </div>
                                        <div class="chat-time">{{$item['created_at']}}</div>
                                    </li>
                                @else
                                    <li class="odd chat-item">
                                        <div class="chat-content">
                                            <div class="box bg-light-inverse">{{$item['text']}}</div>
                                            <br>
                                        </div>
                                    </li>
                                @endif
                            @endforeach
                            
                            
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    
        
    </div>

    @push('script')
        <script src="{{ asset('adm_assets/assets/plugins/DataTables/datatables.min.js') }}"></script>
        <script src="{{ asset('adm_assets/assets/js/datatables.js') }}"></script>
        <script src="{{ asset('adm_assets/assets/js/widgets.js') }}"></script>

        <script src="{{ asset('adm_assets/assets/plugins/moment/moment.js') }}"></script>
        <script src="{{ asset('adm_assets/assets/plugins/tempusdominus-bootstrap-4/build/js/tempusdominus-bootstrap-4.min.js') }}"></script>
        <script src="{{ asset('adm_assets/assets/plugins/jvectormap/jquery-jvectormap.min.js') }}"></script>
        <script src="{{ asset('adm_assets/assets/plugins/jvectormap/jquery-jvectormap-world-mill-en.js') }}"></script>
    @endpush
@endsection