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
    <title>Account Created | {{ config('detailsApp.name') }} </title>

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

<body class="member-auth-shell member-auth-shell--created">
    <div class="row authentication mx-0">
        <div class="col-xxl-6 col-xl-6 col-lg-5 d-xl-block d-none px-0">
            <div class="member-auth-side">
                <video class="member-auth-side__video" autoplay muted playsinline loop preload="auto" poster="{{ asset('logo/background-poster.jpg') }}" onerror="this.style.display='none'">
                    <source src="{{ asset('logo/background.mp4') }}" type="video/mp4">
                </video>
                <div class="member-auth-side__inner">
                    <div>
                        <span class="member-auth-badge"><i class="ti ti-crown"></i> Member Access</span>
                        <h1 class="member-auth-title">Your member account has been created.</h1>
                        <p class="member-auth-copy">
                            Your registration is complete. Review the details below and continue to the same premium
                            member panel experience.
                        </p>
                    </div>
                    <div class="member-auth-side-card member-auth-side-card--highlight">
                        <div class="member-auth-side-card__label">Account Ready</div>
                        <div class="member-auth-side-card__value">Login With Your Credentials</div>
                        <p>Keep your details safe and use your member ID and password to access the member dashboard.</p>
                    </div>

                    <div class="member-auth-info">
                        <div class="member-auth-info__card">
                            <div class="member-auth-info__head">
                                <span class="member-auth-info__icon"><i class="ti ti-badge-check"></i></span>
                                <span>Account Status</span>
                            </div>
                            <strong>Registration completed successfully and ready for login.</strong>
                        </div>
                        <div class="member-auth-info__card">
                            <div class="member-auth-info__head">
                                <span class="member-auth-info__icon"><i class="ti ti-login"></i></span>
                                <span>Next Step</span>
                            </div>
                            <strong>Use your login credentials to enter the member panel.</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-6 col-xl-6 col-lg-12">
            <div class="row justify-content-center align-items-center h-100 member-auth-shell">
                <div class="col-xxl-10 col-xl-10 col-lg-10 col-md-10 col-sm-10 col-12">
                    <div class="card member-auth-card shadow-none my-4">
                        <div class="card-body">
                            <div class="member-auth-head justify-content-center">
                                <a href="{{ url('/member') }}">
                                    <img src="{{ asset('logo/logo.png') }}" alt="logo" width="80px">
                                </a>
                                <!--<span class="member-auth-icon"><i class="ti ti-user-circle"></i></span>-->
                            </div>

                            <div class="member-auth-form-wrap">
                                <div class="member-auth-created-panel-top">
                                    <div class="member-auth-created-panel-top__copy">
                                        <span class="member-auth-created-panel-top__eyebrow"><i class="ti ti-shield-check"></i> Secure Summary</span>
                                        <h5 class="mb-2">Member Account  Summary</h5>
                                        <p class="text-muted">{{ session('name') }}, your account has been created successfully. Please use the details below to login.</p>
                                    </div>
                                    <div class="member-auth-created-panel-top__icon">
                                        <i class="ti ti-file-certificate"></i>
                                    </div>
                                </div>

                                <div class="member-auth-table-wrap">
                                    <table class="table member-auth-table text-start">
                                        <tr>
                                            <th>Member Id</th>
                                            <td>{{ session('memberid') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Email</th>
                                            <td width="30%">{{ session('email') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Mobile</th>
                                            <td>{{ session('mobile') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Password</th>
                                            <td>{{ session('password') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Transaction<br> Password</th>
                                            <td>{{ session('txn_password') }}</td>
                                        </tr>
                                    </table>
                                </div>

                                <div class="member-auth-created-panel-footer">
                                    <p class="member-auth-created-panel-footer__note mb-0">
                                        <i class="ti ti-info-circle"></i>
                                        Your transaction password is required for protected wallet and transfer actions.
                                    </p>
                                </div>

                                <div class="member-auth-actions mt-4">
                                    <a href="{{ url('/member') }}" class="btn member-auth-submit"><i class="ti ti-login me-2"></i>Login to Account</a>
                                    <a href="{{ url('/member/register/1') }}" class="btn member-auth-secondary"><i class="ti ti-user-plus me-2"></i>New Registration</a>
                                </div>
                            </div>

                            
                        </div>
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
