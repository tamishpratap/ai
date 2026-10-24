<!doctype html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Login | {{ config('detailsApp.name') }}</title>
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
                <div class="col-xl-4 col-lg-4 col-md-4 m-auto ">

                    <div class="authentication-form mx-auto mt-5">
                        <div class="logo-centered">
                            <a href="{{ url('/admin') }}">
                                <img style="height: 120px; width: 150" src="{{ asset('logo/logo.png') }}"
                                    alt="{{ config('detailsApp.name') }}">
                            </a>
                        </div>
                        <p>Welcome back! </p>
                        @if (session()->has('OtpMsg'))
                            <div class="alert alert-info text-center mb-2">{{ session('OtpMsg') }}</div>
                        @endif
                        @if (session()->has('loginmsg'))
                            <div class="alert alert-danger mb-3">{{ session('loginmsg') }}</div>
                        @endif
                        <form method="POST" action="{{ route('adminLogin') }}">
                            @csrf

                            <div class="form-group">
                                <a href="{{ url('/send-admin-otp') }}" class="btn btn-custom text-white pt-2">Send OTP</a>
                                @if (session()->has('admin_otp'))
                                    <div class="mt-2 text-success" style="font-weight:600;">
                                        Your OTP: <span class="badge badge-success" style="font-size:14px;letter-spacing:1px;">{{ session('admin_otp') }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="form-group">
                                <input type="text" class="form-control " name="otp" placeholder="Enter OTP" onkeypress='return event.charCode >= 48 && event.charCode <= 57' required>
                                <i class="ik ik-mail"></i>
                                @error('otp')
                                    <span style="margin-top: .25rem;font-size: 80%;color: #dc3545;">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <input id="email" type="email" placeholder="Enter Email"
                                    class="form-control @error('email') is-invalid @enderror" name="email" required
                                    autocomplete="email" autofocus>
                                <i class="ik ik-user"></i>
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <input id="password" type="password" placeholder="Password"
                                    class="form-control @error('password') is-invalid @enderror" name="password"
                                    value="1234" required>
                                <i class="ik ik-lock"></i>
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>


                            <div class="row">
                                <div class="col text-left">
                                    <a class="btn text-danger" href="{{ url('/forget-password') }}">
                                        {{ __('Forgot Password ??') }}
                                    </a>
                                </div>
                            </div>
                            <div class="sign-btn text-center">
                                <button class="btn btn-custom">Sign In</button>
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
