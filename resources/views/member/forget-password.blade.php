<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-theme-mode="dark" data-header-styles="dark"
    data-menu-styles="dark" data-toggled="close" data-vertical-style="default" data-card-style="style1"
    data-card-background="background1">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap"
        rel="stylesheet">
    <title>Forget Password | {{ config('detailsApp.name') }} </title>

    <link rel="icon" type="image/png" href="{{ asset('logo/favicon/favicon-96x96.png') }}" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="{{ asset('logo/favicon/favicon.svg') }}" />
    <link rel="shortcut icon" href="{{ asset('logo/favicon/favicon.ico') }}" />
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('logo/favicon/apple-touch-icon.png') }}" />
    <link rel="manifest" href="{{ asset('logo/favicon/site.webmanifest') }}" />

    <link id="style" href="{{ asset('mem_assets/libs/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('mem_assets/icon-fonts/icons.css') }}" rel="stylesheet">
    <link rel="preload" as="style" href="{{ asset('mem_assets/app-Qi1wo3Tk.css') }}" />
    <link rel="stylesheet" href="{{ asset('mem_assets/app-Qi1wo3Tk.css') }}" />
    <link rel="stylesheet" href="{{ asset('mem_assets/member-panel-redesign.css') }}">

    <script src="{{ asset('mem_assets/authentication-main.js') }}"></script>
</head>

<body class="member-auth-shell">
    <div class="row authentication mx-0">
        <div class="col-xxl-6 col-xl-6 col-lg-5 d-xl-block d-none px-0">
            <div class="member-auth-side">
                <video class="member-auth-side__video" autoplay muted playsinline loop preload="auto" poster="{{ asset('logo/background-poster.jpg') }}" onerror="this.style.display='none'">
                    <source src="{{ asset('logo/background.mp4') }}" type="video/mp4">
                </video>
                <div class="member-auth-side__inner">
                    <div>
                        <span class="member-auth-badge"><i class="ti ti-crown"></i> Member Access</span>
                        <h1 class="member-auth-title">Recover your account access quickly.</h1>
                        <p class="member-auth-copy">
                            Use your registered email address to receive password details through the same premium
                            member panel support experience.
                        </p>
                    </div>
                    <div class="member-auth-side-card member-auth-side-card--highlight">
                        <div class="member-auth-side-card__label">Recovery Access</div>
                        <div class="member-auth-side-card__value">Secure Email Based Help</div>
                        <p>Recover your account through the same protected and premium member experience.</p>
                    </div>

                    <div class="member-auth-info">
                        <div class="member-auth-info__card">
                            <div class="member-auth-info__head">
                                <span class="member-auth-info__icon"><i class="ti ti-mail-forward"></i></span>
                                <span>Recovery Mode</span>
                            </div>
                            <strong>Registered email based password retrieval.</strong>
                        </div>
                        <div class="member-auth-info__card">
                            <div class="member-auth-info__head">
                                <span class="member-auth-info__icon"><i class="ti ti-lifebuoy"></i></span>
                                <span>Support Flow</span>
                            </div>
                            <strong>Clean member support journey with clear next steps.</strong>
                        </div>
                        <div class="member-auth-info__card">
                            <div class="member-auth-info__head">
                                <span class="member-auth-info__icon"><i class="ti ti-shield-lock"></i></span>
                                <span>Protected Access</span>
                            </div>
                            <strong>Designed to keep your member account recovery simple and safe.</strong>
                        </div>
                        <div class="member-auth-info__card">
                            <div class="member-auth-info__head">
                                <span class="member-auth-info__icon"><i class="ti ti-layout-dashboard"></i></span>
                                <span>Panel Ready</span>
                            </div>
                            <strong>Return to the same member dashboard once your details are received.</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-6 col-xl-6 col-lg-12">
            <div class="row justify-content-center align-items-center h-100 member-auth-shell">
                <div class="col-xxl-8 col-xl-9 col-lg-8 col-md-8 col-sm-10 col-12">
                    <div class="card member-auth-card shadow-none my-4">
                        <form action="{{ route('memretrivePassword') }}" method="post">
                            @csrf
                            <div class="card-body">
                                <div class="member-auth-head">
                                    <a href="{{ url('/') }}">
                                        <img src="{{ asset('logo/logo.png') }}" alt="logo" width="80px">
                                    </a>
                                    <span class="member-auth-icon"><i class="ti ti-lock"></i></span>
                                </div>

                                <div class="member-auth-form-wrap">
                                    <div class="member-auth-form-head">
                                        <h5>Recovery Request</h5>
                                        <p>Use your registered email address to receive account details.</p>
                                    </div>

                                    <div class="member-auth-alerts">
                                        @if (session()->has('succMsg'))
                                            <div class="alert alert-success alert-dismissible fade show custom-alert-icon shadow-sm" role="alert">
                                                <svg style="fill: rgb(var(--success-rgb));" xmlns="http://www.w3.org/2000/svg" height="1.5rem"
                                                    viewBox="0 0 24 24" width="1.5rem" fill="#000000">
                                                    <path d="M0 0h24v24H0z" fill="none" />
                                                    <path
                                                        d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                                                </svg>
                                                {{ session('succMsg') }}
                                            </div>
                                        @endif
                                        @if (session()->has('forgetPassErr'))
                                            <div class="alert alert-danger alert-dismissible fade show custom-alert-icon shadow-sm" role="alert">
                                                <svg style="fill: rgb(var(--danger-rgb));" xmlns="http://www.w3.org/2000/svg"
                                                    height="1.5rem" viewBox="0 0 24 24" width="1.5rem" fill="#000000">
                                                    <path d="M0 0h24v24H0z" fill="none" />
                                                    <path
                                                        d="M15.73 3H8.27L3 8.27v7.46L8.27 21h7.46L21 15.73V8.27L15.73 3zM12 17.3c-.72 0-1.3-.58-1.3-1.3 0-.72.58-1.3 1.3-1.3.72 0 1.3.58 1.3 1.3 0 .72-.58 1.3-1.3 1.3zm1-4.3h-2V7h2v6z" />
                                                </svg>
                                                {{ session('forgetPassErr') }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="row gy-3">
                                        <div class="col-xl-12">
                                            <label for="signin-username" class="form-label member-auth-label">Email Address</label>
                                            <input type="email"
                                                class="form-control member-auth-input @error('email') is-invalid @enderror"
                                                id="signin-username" name="email" placeholder="Enter Email Address">
                                            @error('email')
                                                <span class="small text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <small class="member-auth-helper">Password will be sent to your registered email</small>
                                    </div>

                                    <div class="d-grid mt-4">
                                        <button type="submit" class="btn member-auth-submit">Send Password</button>
                                    </div>

                                    <div class="text-center member-auth-footer-note">
                                        <p class="member-auth-shell-note mb-0">Back to account access?
                                            <a href="{{ url('/member') }}" class="member-auth-link">Sign In</a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('mem_assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('mem_assets/libs/swiper/swiper-bundle.min.js') }}"></script>
    <link rel="modulepreload" href="{{ asset('mem_assets/authentication-WetCL-A9.js') }}" />
    <script type="module" src="{{ asset('mem_assets/authentication-WetCL-A9.js') }}"></script>
    <script src="{{ asset('mem_assets/show-password.js') }}"></script>
</body>

</html>
