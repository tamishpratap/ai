@extends('admin.layouts.main') 
@section('title', 'Profile Section')
@section('content')
   
    <!-- push external head elements to head -->
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="ik ik-edit bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('Change Profile Details')}}</h5>
                            <span>{{ __('Change Profile Details')}}</span>
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
                            <li class="breadcrumb-item active" aria-current="page">{{ __('Change Password')}}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="row">
        
            <div class="col-md-8">
                @if (session()->has('passMsg'))
                    <div class="alert alert-success" role="alert">
                        {{session('passMsg')}}
                    </div>
                @endif
                <div class="card">
                    <div class="card-header"><h3>{{ __('Change Email')}}</h3></div>
                    <div class="card-body">
                        <form class="forms-sample" action="{{route('changeEmail')}}" method="post">
                            @csrf
                            <div class="form-group">
                                <label for="exampleInputName1">{{ __('New Email')}}</label>
                                <input type="email" class="form-control" name="email" id="exampleInputName1" placeholder="Enter New Email" value="{{$admin['email']}}">
                                @error('email')
                                   <span class="text-danger">{{$message}}</span>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary mr-2 float-right">{{ __('Change Email')}}</button>
                            
                        </form>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header"><h3>{{ __('Change Password')}}</h3></div>
                    <div class="card-body">
                        <form class="forms-sample" action="{{route('changePassword')}}" method="post">
                            @csrf
                            <div class="form-group">
                                <label for="exampleInputName1">{{ __('New Password')}}</label>
                                <input type="password" class="form-control" name="password" id="exampleInputName1" placeholder="Enter Password">
                                @error('password')
                                   <span class="text-danger">{{$message}}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="exampleInputName1">{{ __('Retype Password')}}</label>
                                <input type="password" class="form-control" name="password_confirmation" id="exampleInputName1" placeholder="Retype Password">
                            </div>
                            <button type="submit" class="btn btn-primary mr-2 float-right">{{ __('Change Password')}}</button>
                            
                        </form>
                    </div>
                </div>
            </div>    
        </div>
       
        

    </div>
    <!-- push external js -->
    <!-- push external js -->
    @push('script')
        <script src="{{ asset('adm_assets/assets/plugins/owl.carousel/dist/owl.carousel.min.js') }}"></script>
        <script src="{{ asset('adm_assets/assets/plugins/chartist/dist/chartist.min.js') }}"></script>
        <script src="{{ asset('adm_assets/assets/plugins/flot-charts/jquery.flot.js') }}"></script>
        <!-- <script src="{{ asset('adm_assets/assets/plugins/flot-charts/jquery.flot.categories.js') }}"></script> -->
        <script src="{{ asset('plugins/flot-charts/curvedLines.js') }}"></script>
        <script src="{{ asset('adm_assets/assets/plugins/flot-charts/jquery.flot.tooltip.min.js') }}"></script>

        <script src="{{ asset('adm_assets/assets/plugins/amcharts/amcharts.js') }}"></script>
        <script src="{{ asset('adm_assets/assets/plugins/amcharts/serial.js') }}"></script>
        <script src="{{ asset('adm_assets/assets/plugins/amcharts/themes/light.js') }}"></script>
       
        
        <script src="{{ asset('adm_assets/assets/js/widget-statistic.js') }}"></script>
        <script src="{{ asset('adm_assets/assets/js/widget-data.js') }}"></script>
        <script src="{{ asset('adm_assets/assets/js/dashboard-charts.js') }}"></script>
        
    @endpush
@endsection