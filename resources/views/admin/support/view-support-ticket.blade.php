@extends('admin.layouts.main') 
@section('title', 'View Support Tickets')
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
                            <h5>{{ __('View Support Tickets')}}</h5>
                            <span>{{ __('View Support Tickets ')}}</span>
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
                            <li class="breadcrumb-item active" aria-current="page">{{ __('View Support Tickets')}}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-8 col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3>Recent Chat</h3>
                        <div class="card-header-right">
                            <a href="{{url('admin/closed-ticket')}}/{{$support['ticket_id']}}" class="btn btn-primary">Close Ticket</a>
                        </div>
                    </div>
                    <div class="card-body chat-box scrollable card-300 mx-3">
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
                    <div class="card-footer chat-footer">
                        <form action="{{route('Ticket')}}" method="post">
                            @csrf
                            <div class="input-wrap">
                                <input type="text" placeholder="Type and enter" class="form-control" name="reply">
                                @error('reply')
                                    <span class="text-danger">{{$message}}</span>
                                @enderror
                            </div>
                            <input type="hidden" class="form-control" value="{{$support['ticket_id']}}" name="ticket_id">
                            <button type="submit" class="btn btn-icon btn-theme"><i class="fa fa-paper-plane"></i></button>
                        </form>
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