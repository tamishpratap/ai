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
    <title>Sign Up | {{ config('detailsApp.name') }} </title>

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
                        <h1 class="member-auth-title">Create your secure member account.</h1>
                        <p class="member-auth-copy">
                            Join the same polished member experience with secure onboarding, clean registration,
                            and a consistent premium dashboard design.
                        </p>
                    </div>
                    <div class="member-auth-info">
                        <div class="member-auth-info__card">
                            <span>Secure Dashboard</span>
                            <strong>Instant access to wallet controls, earnings, and profile tools after login.</strong>
                        </div>
                        <div class="member-auth-info__card">
                            <span>Smart Protection</span>
                            <strong>Password safety, captcha protection, and secure member sessions.</strong>
                        </div>
                        <div class="member-auth-info__card">
                            <span>Fast Onboarding</span>
                            <strong>Minimal registration fields with quick verification and account activation.</strong>
                        </div>
                        <div class="member-auth-info__card">
                            <span>Live Support</span>
                            <strong>Track help tickets, get updates, and connect with support inside your panel.</strong>
                        </div>
                    </div>
                    <div class="member-auth-side-card member-auth-side-card--highlight">
                        <div class="member-auth-side-card__label">Golden Member Suite</div>
                        <div class="member-auth-side-card__value">Fast and Protected Registration</div>
                        <p>Complete account creation in the same premium visual system as your member dashboard.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-6 col-xl-6 col-lg-12">
            <div class="row justify-content-center align-items-center h-100 member-auth-shell">
                <div class="col-xxl-10 col-xl-10 col-lg-10 col-md-10 col-sm-10 col-12">
                    <div class="card member-auth-card shadow-none my-4">
                        <form action="{{ route('userRegister') }}" method="post" id="signupForm">
                            @csrf
                            <div class="card-body">
                                <div class="member-auth-head justify-content-center">
                                    <a href="{{ url('/member') }}">
                                        <img src="{{ asset('logo/logo.png') }}" alt="Demo" style="height: auto; max-width: 100px; object-fit: contain;">
                                    </a>
                                    <!--<span class="member-auth-icon"><i class="ti ti-user-plus"></i></span>-->
                                </div>
                                <div class="member-auth-form-wrap">
                                    <div class="member-auth-form-head">
                                        <h5>Create Account</h5>
                                        <p>Fill your registration details carefully to continue.</p>
                                    </div>

                                    <div class="member-auth-alerts">
                                        @if (session()->has('successMsg'))
                                            <div class="alert alert-success alert-dismissible fade show custom-alert-icon shadow-sm" role="alert">
                                                <svg style="fill: rgb(var(--success-rgb));" xmlns="http://www.w3.org/2000/svg" height="1.5rem"
                                                    viewBox="0 0 24 24" width="1.5rem" fill="#000000">
                                                    <path d="M0 0h24v24H0z" fill="none" />
                                                    <path
                                                        d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                                                </svg>
                                                {{ session('successMsg') }}
                                            </div>
                                        @endif
                                        @if (session()->has('failedMsg'))
                                            <div class="alert alert-danger alert-dismissible fade show custom-alert-icon shadow-sm" role="alert">
                                                <svg style="fill: rgb(var(--danger-rgb));" xmlns="http://www.w3.org/2000/svg"
                                                    height="1.5rem" viewBox="0 0 24 24" width="1.5rem" fill="#000000">
                                                    <path d="M0 0h24v24H0z" fill="none" />
                                                    <path
                                                        d="M15.73 3H8.27L3 8.27v7.46L8.27 21h7.46L21 15.73V8.27L15.73 3zM12 17.3c-.72 0-1.3-.58-1.3-1.3 0-.72.58-1.3 1.3-1.3.72 0 1.3.58 1.3 1.3 0 .72-.58 1.3-1.3 1.3zm1-4.3h-2V7h2v6z" />
                                                </svg>
                                                {{ session('failedMsg') }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="member-auth-form-grid">
                                        <div class="member-auth-field--full">
                                            <label for="sponsor" class="form-label member-auth-label">Sponsor ID<sup>*</sup></label>
                                            <input type="text"
                                                class="form-control member-auth-input @error('sponsor') is-invalid @enderror"
                                                placeholder="Enter Sponsor ID" name="sponsor" id="sponsor" minlength="9"
                                                maxlength="9" value="{{ $sdata != '1' ? $sdata : '' }}">
                                            <span id="sponhtml"></span>
                                            @error('sponsor')
                                                <span class="small text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        {{-- <div class="member-auth-field--full">
                                            <label class="form-label member-auth-label">Placement Side<sup>*</sup></label>
                                            <div class="d-flex gap-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="position" id="position-left" value="Left" {{ old('position') == 'Left' ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="position-left">Left</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="position" id="position-right" value="Right" {{ old('position') == 'Right' ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="position-right">Right</label>
                                                </div>
                                            </div>
                                            @error('position')
                                                <span class="small text-danger">{{ $message }}</span>
                                            @enderror
                                        </div> --}}

                                        <div>
                                            <label for="signup-name" class="form-label member-auth-label">Name<sup>*</sup></label>
                                            <input type="text"
                                                class="form-control member-auth-input @error('name') is-invalid @enderror"
                                                id="signup-name" name="name" placeholder="Enter Your Name">
                                            @error('name')
                                                <span class="small text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="signin-username" class="form-label member-auth-label">Email Address</label>
                                            <input type="email"
                                                class="form-control member-auth-input @error('email') is-invalid @enderror"
                                                id="signin-username" name="email" placeholder="Enter Email Address">
                                            @error('email')
                                                <span class="small text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="signup-mobile" class="form-label member-auth-label">Mobile Number<sup>*</sup></label>
                                            <input type="text"
                                                class="form-control member-auth-input @error('mobile') is-invalid @enderror"
                                                name="mobile" id="signup-mobile" placeholder="Enter Your Mobile Number"
                                                minlength="10" maxlength="10"
                                                onkeypress='return event.charCode >= 48 && event.charCode <= 57'>
                                            @error('mobile')
                                                <span class="small text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="inputState" class="form-label member-auth-label">Country</label>
                                            <select id="inputState"
                                                class="form-select member-auth-select @error('country') is-invalid @enderror"
                                                name="country">
                                                <option class="text-dark" value="">Select Country</option>
                                                @foreach ($countries as $country)
                                                    <option class="text-dark" value="{{ $country['name'] }}">
                                                        ({{ $country['phonecode'] }}) - {{ $country['name'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('country')
                                                <span class="small text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="member-auth-field--full">
                                            <label for="signup-password" class="form-label member-auth-label d-block">Password</label>
                                            <div class="position-relative">
                                                <input type="password"
                                                    class="form-control member-auth-input create-password-input @error('password') is-invalid @enderror"
                                                    id="signup-password" placeholder="Enter Password" name="password">
                                                <a href="javascript:void(0);" class="show-password-button text-muted"
                                                    onclick="createpassword('signup-password',this)" id="button-addon2">
                                                    <i class="ri-eye-off-line align-middle"></i>
                                                </a>
                                                <span class="member-auth-warning d-block">Password length must be equal or greater than 6.</span>
                                            </div>
                                            @error('password')
                                                <span class="small text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="member-auth-field--full">
                                            <label for="confirm-password" class="form-label member-auth-label d-block">Confirm Password</label>
                                            <input type="password"
                                                class="form-control member-auth-input @error('confirm_password') is-invalid @enderror"
                                                id="confirm-password" placeholder="Confirm Password" name="confirm_password">
                                            @error('confirm_password')
                                                <span class="small text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="member-auth-field--full">
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" value="" id="defaultCheck1" required>
                                                <label class="form-check-label member-auth-helper fw-normal fs-14" for="defaultCheck1">
                                                    By creating an account you agree to our
                                                    <a href="javascript:;" class="member-auth-link"><u>Terms & Conditions</u></a>
                                                    and
                                                    <a class="member-auth-link"><u>Privacy Policy</u></a>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-grid mt-4">
                                        <button type="submit" class="btn member-auth-submit" id="btnSignupSubmit">Create Account</button>
                                    </div>

                                    <div class="text-center member-auth-footer-note">
                                        <p class="member-auth-shell-note mb-0">Already have an account?
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

    <style>
        .member-auth-submit:disabled,
        .member-auth-submit.disabled {
            opacity: 0.65 !important;
            cursor: not-allowed !important;
            pointer-events: none !important;
            filter: grayscale(0.5);
        }
    </style>

    <script src="{{ asset('mem_assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('mem_assets/libs/swiper/swiper-bundle.min.js') }}"></script>
    <link rel="modulepreload" href="{{ asset('mem_assets/authentication-WetCL-A9.js') }}" />
    <script type="module" src="{{ asset('mem_assets/authentication-WetCL-A9.js') }}"></script>
    <script src="{{ asset('mem_assets/show-password.js') }}"></script>
    <script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
    <script>
        $(document).ready(function() {
            var isSubmitting = false;
            var $form = $('#signupForm');
            var $btn = $('#btnSignupSubmit');

            $('#sponsor').on('input', function() {
                var sponsor = $(this).val();

                if (sponsor.length == 0) {
                    $('#sponhtml').html('');
                }
                $.ajax({
                    url: '/getSponname',
                    type: 'POST',
                    data: {
                        'sponsorid': sponsor,
                        _token: '{{ csrf_token() }}',
                    },
                    success: function(response) {
                        $('#sponhtml').html(response['data'])
                    }
                })
            });

            // Prevent multiple clicks: disable button on submit
            $form.on('submit', function(e) {
                if (isSubmitting) {
                    e.preventDefault();
                    return false;
                }
                isSubmitting = true;
                $btn.prop('disabled', true).addClass('disabled').html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Please wait...');
            });

            // Re-enable button when user edits / changes any field
            $form.find('input, select').on('input change keyup', function() {
                if (isSubmitting) {
                    isSubmitting = false;
                    $btn.prop('disabled', false).removeClass('disabled').html('Create Account');
                }
            });
        })
    </script>
</body>

</html>
