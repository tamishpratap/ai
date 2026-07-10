<!doctype html>
<html class="no-js" lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <title>Forget Password | {{config('detailsApp.name')}}</title>
        <meta name="description" content="">
        <meta name="keywords" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">


      <!-- FAVICONS ICON -->
<link rel="icon" type="image/png" href="{{asset('logo/favicon/favicon-96x96.png')}}" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="{{asset('logo/favicon/favicon.svg')}}" />
    <link rel="shortcut icon" href="{{asset('logo/favicon/favicon.ico')}}" />
    <link rel="apple-touch-icon" sizes="180x180" href="{{asset('logo/favicon/apple-touch-icon.png')}}" />
    <link rel="manifest" href="{{asset('logo/favicon/site.webmanifest')}}" />

        <link href="https://fonts.googleapis.com/css?family=Nunito+Sans:300,400,600,700,800" rel="stylesheet">

        <link rel="stylesheet" href="{{ asset('adm_assets/assets/plugins/bootstrap/dist/css/bootstrap.min.css') }}">
        <link rel="stylesheet" href="{{ asset('adm_assets/assets/plugins/fontawesome-free/css/all.min.css') }}">
        <link rel="stylesheet" href="{{ asset('adm_assets/assets/plugins/ionicons/dist/css/ionicons.min.css') }}">
        <link rel="stylesheet" href="{{ asset('adm_assets/assets/plugins/icon-kit/dist/css/iconkit.min.css') }}">
        <link rel="stylesheet" href="{{ asset('adm_assets/assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css') }}">
        <link rel="stylesheet" href="{{ asset('adm_assets/assets/dist/css/theme.min.css') }}">
        <link rel="stylesheet" href="{{ asset('adm_assets/assets/css/style.css') }}">
        <script src="{{ asset('adm_assets/assets/src/js/vendor/modernizr-2.8.3.min.js') }}"></script>
    </head>

    <body>


        <div class="auth-wrapper">
            <div class="container-fluid h-100">
                <div class="row flex-row h-100">
                    <div class="col-xl-4 col-lg-4 col-md-4 m-auto">
                        <div class="authentication-form mx-auto pt-0">
                            <div class="logo-centered mt-0 pt-4 mb-3">
                                <img class="text-center" height="85px" src="{{ asset('logo/logo.png') }}" alt="Logo" >
                                <!--<a href="#"><h3 class="text-primary"><b>{{config('detailsApp.name')}}</b></h3></a>-->
                            </div>
                            <h5 class="text-danger pb-2 pt-0 text-center">Forget Password ? </h5>
                             @if (session()->has('succMsg'))
                                <div class="alert alert-success alert-dismissible fade show my-3" role="alert">
                                    {{ session('succMsg') }}
                                </div>
                            @endif
                            <form method="POST" action="{{ route('retrivePassword') }}">
                                @csrf
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary mt-2 mb-2">Retrive Password</button>
                                </div>
                                <div class="row mt-3">
                                    <small class="text-warning">** Password will be sent to your registered email
                                        Id</small>
                                    <div class="col text-right">
                                        <a class="btn text-primary" href="{{url('/admin')}}">
                                           <h6> {{ __('LogIn?') }} </h6>
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <script src="{{ asset('adm_assets/assets/src/js/vendor/jquery-3.3.1.min.js') }}"></script>
        <script src="{{ asset('adm_assets/assets/plugins/popper.js/dist/umd/popper.min.js') }}"></script>
        <script src="{{ asset('adm_assets/assets/plugins/bootstrap/dist/js/bootstrap.min.js') }}"></script>
        <script src="{{ asset('adm_assets/assets/plugins/perfect-scrollbar/dist/perfect-scrollbar.min.js') }}"></script>
        <script src="{{ asset('adm_assets/assets/plugins/screenfull/dist/screenfull.js') }}"></script>

    </body>
</html>
