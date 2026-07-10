<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-vertical-style="detached" data-toggled="detached-close"
    data-theme-mode="dark" data-header-styles="dark" data-menu-styles="dark" data-card-style="style1"
    data-card-background="background1">
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>

    <!-- Meta Data -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap"
        rel="stylesheet">

    <!-- TITLE -->
    <title> @yield('title') | {{ config('detailsApp.name') }} </title>

    <!-- FAVICONS ICON -->
    <!-- FAVICONS ICON -->
    <link rel="icon" type="image/png" href="{{ asset('logo/favicon/favicon-96x96.png') }}" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="{{ asset('logo/favicon/favicon.svg') }}" />
    <link rel="shortcut icon" href="{{ asset('logo/favicon/favicon.ico') }}" />
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('logo/favicon/apple-touch-icon.png') }}" />
    <link rel="manifest" href="{{ asset('logo/favicon/site.webmanifest') }}" />


    <!-- BOOTSTRAP CSS -->
    <link id="style" href="{{ asset('mem_assets/libs/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('mem_assets/geneology.css') }}" rel="stylesheet">

    <!-- ICONS CSS -->
    <link href="{{ asset('mem_assets/icon-fonts/icons.css') }}" rel="stylesheet">

    <!-- APP SCSS -->
    <link rel="preload" as="style" href="{{ asset('mem_assets/app-Qi1wo3Tk.css') }}" />
    <link rel="stylesheet" href="{{ asset('mem_assets/app-Qi1wo3Tk.css') }}" />
    <link rel="stylesheet" href="{{ asset('mem_assets/member-panel-redesign.css') }}">

    <!-- NODE WAVES CSS -->
    <link href="{{ asset('mem_assets/libs/node-waves/waves.min.css') }}" rel="stylesheet">

    <!-- SIMPLEBAR CSS -->
    <link rel="stylesheet" href="{{ asset('mem_assets/libs/simplebar/simplebar.min.css') }}">

    <!-- COLOR PICKER CSS -->
    <link rel="stylesheet" href="{{ asset('mem_assets/libs/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('mem_assets/libs/%40simonwep/pickr/themes/nano.min.css') }}">

    <!-- CHOICES CSS -->
    <link rel="stylesheet" href="{{ asset('mem_assets/libs/choices.js/public/assets/styles/choices.min.css') }}">

    <!-- CHOICES JS -->
    <script src="{{ asset('mem_assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>

    <!-- MAIN JS -->
    <script src="{{ asset('mem_assets/main.js') }}"></script>


    <!-- Jsvector Maps -->
    <link rel="stylesheet" href="{{ asset('mem_assets/libs/jsvectormap/css/jsvectormap.min.css') }}">

    <link rel="stylesheet" href="{{ asset('mem_assets/libs/prismjs/themes/prism-coy.min.css') }}">
    <!-- Datatables CDN -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
    <!-- CSS -->
    <style>
        .toast {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(90deg, #00ffcc, #0077ff);
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            font-family: monospace;
            font-weight: bold;
            animation: fadeInOut 3s ease forwards;
            z-index: 9999;
        }

        @keyframes fadeInOut {
            0% {
                opacity: 0;
                transform: translateY(20px);
            }

            10% {
                opacity: 1;
                transform: translateY(0);
            }

            90% {
                opacity: 1;
            }

            100% {
                opacity: 0;
                transform: translateY(20px);
            }
        }
    </style>

    <style>
        .mobile-bottom-menu {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: #0a201c;
            /* Dark background */
            display: flex;
            justify-content: space-around;
            padding: 8px 0;
            border-top: 1px solid #333;
            z-index: 999;
        }

        .mobile-bottom-menu .menu-item {
            flex: 1;
            text-align: center;
            color: #fff;
            text-decoration: none;
            font-size: 12px;
        }

        .mobile-bottom-menu .menu-item i {
            font-size: 20px;
            display: block;
            margin-bottom: 3px;
        }

        /* Show only on mobile */
        @media (min-width: 768px) {
            .mobile-bottom-menu {
                display: none;
            }
        }

        /* ====== CLAIM ROI / TRADE BUTTON SPECIAL DESIGN ====== */
        .claim-btn form {
            margin: 0;
            padding: 0;
        }

        .claim-roi-btn {
            background: linear-gradient(135deg, #00ffcc, #0077ff);
            border: none;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            color: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            box-shadow: 0 0 15px rgba(0, 255, 204, 0.7), 0 0 30px rgba(0, 119, 255, 0.5);
            position: relative;
            overflow: hidden;
            animation: glowPulse 2s infinite ease-in-out;
            cursor: pointer;
        }

        /* Icon inside the button */
        .claim-roi-btn i {
            font-size: 24px;
            margin-bottom: 3px;
        }

        /* Text below icon */
        .claim-roi-btn span {
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        /* Hover effect */
        .claim-roi-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 0 25px #00ffcc, 0 0 45px #0077ff;
            transition: all 0.3s ease;
        }

        /* Glowing animation (crypto mining vibe) */
        @keyframes glowPulse {
            0% {
                box-shadow: 0 0 10px rgba(0, 255, 204, 0.6), 0 0 25px rgba(0, 119, 255, 0.4);
            }

            50% {
                box-shadow: 0 0 25px rgba(0, 255, 204, 1), 0 0 50px rgba(0, 119, 255, 0.9);
            }

            100% {
                box-shadow: 0 0 10px rgba(0, 255, 204, 0.6), 0 0 25px rgba(0, 119, 255, 0.4);
            }
        }

        /* Responsive adjustments for mobile */
        @media (max-width: 600px) {
            .claim-roi-btn {
                width: 55px;
                height: 55px;
            }

            .claim-roi-btn i {
                font-size: 20px;
            }

            .claim-roi-btn span {
                font-size: 10px;
            }
        }
    </style>


</head>

<body class="member-panel-shell">

    <!-- LOADER -->
    <div id="loader" style="display: flex">
        <img src="{{ asset('mem_assets/images/media/loader.svg') }}" alt="">
    </div>
    <!-- END LOADER -->

    <!-- SWITCHER -->

    <div class="offcanvas offcanvas-end switcher-offacanvas" tabindex="-1" id="switcher-canvas"
        aria-labelledby="offcanvasRightLabel">
        <div class="offcanvas-header border-bottom d-block p-0">
            <div class="d-flex align-items-center justify-content-between p-3">
                <h5 class="offcanvas-title text-default" id="offcanvasRightLabel">Switcher</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <nav class="border-top border-block-start-dashed">
                <div class="nav nav-tabs nav-justified" id="switcher-main-tab" role="tablist">
                    <button class="nav-link active" id="switcher-home-tab" data-bs-toggle="tab"
                        data-bs-target="#switcher-home" type="button" role="tab" aria-controls="switcher-home"
                        aria-selected="true">Theme Styles</button>
                    <button class="nav-link" id="switcher-profile-tab" data-bs-toggle="tab"
                        data-bs-target="#switcher-profile" type="button" role="tab"
                        aria-controls="switcher-profile" aria-selected="false">Theme Colors</button>
                </div>
            </nav>
        </div>
        <div class="offcanvas-body">
            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade show active border-0" id="switcher-home" role="tabpanel"
                    aria-labelledby="switcher-home-tab" tabindex="0">
                    <div class="">
                        <p class="switcher-style-head">Directions:</p>
                        <div class="row switcher-style gx-0">
                            <div class="col-4">
                                <div class="form-check switch-select">
                                    <label class="form-check-label" for="switcher-ltr">
                                        LTR
                                    </label>
                                    <input class="form-check-input" type="radio" name="direction"
                                        id="switcher-ltr" checked>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-check switch-select">
                                    <label class="form-check-label" for="switcher-rtl">
                                        RTL
                                    </label>
                                    <input class="form-check-input" type="radio" name="direction"
                                        id="switcher-rtl">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="">
                        <p class="switcher-style-head">Navigation Styles:</p>
                        <div class="row switcher-style gx-0">
                            <div class="col-4">
                                <div class="form-check switch-select">
                                    <label class="form-check-label" for="switcher-vertical">
                                        Vertical
                                    </label>
                                    <input class="form-check-input" type="radio" name="navigation-style"
                                        id="switcher-vertical" checked>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-check switch-select">
                                    <label class="form-check-label" for="switcher-horizontal">
                                        Horizontal
                                    </label>
                                    <input class="form-check-input" type="radio" name="navigation-style"
                                        id="switcher-horizontal">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="navigation-menu-styles">
                        <p class="switcher-style-head">Vertical & Horizontal Menu Styles:</p>
                        <div class="row switcher-style gx-0 pb-2 gy-2">
                            <div class="col-4">
                                <div class="form-check switch-select">
                                    <label class="form-check-label" for="switcher-menu-click">
                                        Menu Click
                                    </label>
                                    <input class="form-check-input" type="radio" name="navigation-menu-styles"
                                        id="switcher-menu-click">
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-check switch-select">
                                    <label class="form-check-label" for="switcher-menu-hover">
                                        Menu Hover
                                    </label>
                                    <input class="form-check-input" type="radio" name="navigation-menu-styles"
                                        id="switcher-menu-hover">
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-check switch-select">
                                    <label class="form-check-label" for="switcher-icon-click">
                                        Icon Click
                                    </label>
                                    <input class="form-check-input" type="radio" name="navigation-menu-styles"
                                        id="switcher-icon-click">
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-check switch-select">
                                    <label class="form-check-label" for="switcher-icon-hover">
                                        Icon Hover
                                    </label>
                                    <input class="form-check-input" type="radio" name="navigation-menu-styles"
                                        id="switcher-icon-hover">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sidemenu-layout-styles">
                        <p class="switcher-style-head">Sidemenu Layout Styles:</p>
                        <div class="row switcher-style gx-0 pb-2 gy-2">
                            <div class="col-sm-6">
                                <div class="form-check switch-select">
                                    <label class="form-check-label" for="switcher-default-menu">
                                        Default Menu
                                    </label>
                                    <input class="form-check-input" type="radio" name="sidemenu-layout-styles"
                                        id="switcher-default-menu">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-check switch-select">
                                    <label class="form-check-label" for="switcher-closed-menu">
                                        Closed Menu
                                    </label>
                                    <input class="form-check-input" type="radio" name="sidemenu-layout-styles"
                                        id="switcher-closed-menu">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-check switch-select">
                                    <label class="form-check-label" for="switcher-icontext-menu">
                                        Icon Text
                                    </label>
                                    <input class="form-check-input" type="radio" name="sidemenu-layout-styles"
                                        id="switcher-icontext-menu">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-check switch-select">
                                    <label class="form-check-label" for="switcher-icon-overlay">
                                        Icon Overlay
                                    </label>
                                    <input class="form-check-input" type="radio" name="sidemenu-layout-styles"
                                        id="switcher-icon-overlay">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-check switch-select">
                                    <label class="form-check-label" for="switcher-detached">
                                        Detached
                                    </label>
                                    <input class="form-check-input" type="radio" name="sidemenu-layout-styles"
                                        id="switcher-detached" checked>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-check switch-select">
                                    <label class="form-check-label" for="switcher-double-menu">
                                        Double Menu
                                    </label>
                                    <input class="form-check-input" type="radio" name="sidemenu-layout-styles"
                                        id="switcher-double-menu">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="">
                        <p class="switcher-style-head">Layout Width Styles:</p>
                        <div class="row switcher-style gx-0">
                            <div class="col-4">
                                <div class="form-check switch-select">
                                    <label class="form-check-label" for="switcher-full-width">
                                        Full Width
                                    </label>
                                    <input class="form-check-input" type="radio" name="layout-width"
                                        id="switcher-full-width" checked>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-check switch-select">
                                    <label class="form-check-label" for="switcher-boxed">
                                        Boxed
                                    </label>
                                    <input class="form-check-input" type="radio" name="layout-width"
                                        id="switcher-boxed">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="">
                        <p class="switcher-style-head">Menu Positions:</p>
                        <div class="row switcher-style gx-0">
                            <div class="col-4">
                                <div class="form-check switch-select">
                                    <label class="form-check-label" for="switcher-menu-fixed">
                                        Fixed
                                    </label>
                                    <input class="form-check-input" type="radio" name="menu-positions"
                                        id="switcher-menu-fixed" checked>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-check switch-select">
                                    <label class="form-check-label" for="switcher-menu-scroll">
                                        Scrollable
                                    </label>
                                    <input class="form-check-input" type="radio" name="menu-positions"
                                        id="switcher-menu-scroll">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="">
                        <p class="switcher-style-head">Header Positions:</p>
                        <div class="row switcher-style gx-0">
                            <div class="col-4">
                                <div class="form-check switch-select">
                                    <label class="form-check-label" for="switcher-header-fixed">
                                        Fixed
                                    </label>
                                    <input class="form-check-input" type="radio" name="header-positions"
                                        id="switcher-header-fixed" checked>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-check switch-select">
                                    <label class="form-check-label" for="switcher-header-scroll">
                                        Scrollable
                                    </label>
                                    <input class="form-check-input" type="radio" name="header-positions"
                                        id="switcher-header-scroll">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="">
                        <p class="switcher-style-head">Loader:</p>
                        <div class="row switcher-style gx-0">
                            <div class="col-4">
                                <div class="form-check switch-select">
                                    <label class="form-check-label" for="switcher-loader-enable">
                                        Enable
                                    </label>
                                    <input class="form-check-input" type="radio" name="page-loader"
                                        id="switcher-loader-enable">
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-check switch-select">
                                    <label class="form-check-label" for="switcher-loader-disable">
                                        Disable
                                    </label>
                                    <input class="form-check-input" type="radio" name="page-loader"
                                        id="switcher-loader-disable" checked>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade border-0" id="switcher-profile" role="tabpanel"
                    aria-labelledby="switcher-profile-tab" tabindex="0">
                    <div>
                        <div class="theme-colors">
                            <p class="switcher-style-head">Theme Primary:</p>
                            <div class="d-flex flex-wrap align-items-center switcher-style">
                                <div class="form-check switch-select me-3">
                                    <input class="form-check-input color-input color-primary-1" type="radio"
                                        name="theme-primary" id="switcher-primary">
                                </div>
                                <div class="form-check switch-select me-3">
                                    <input class="form-check-input color-input color-primary-2" type="radio"
                                        name="theme-primary" id="switcher-primary1">
                                </div>
                                <div class="form-check switch-select me-3">
                                    <input class="form-check-input color-input color-primary-3" type="radio"
                                        name="theme-primary" id="switcher-primary2">
                                </div>
                                <div class="form-check switch-select me-3">
                                    <input class="form-check-input color-input color-primary-4" type="radio"
                                        name="theme-primary" id="switcher-primary3">
                                </div>
                                <div class="form-check switch-select me-3">
                                    <input class="form-check-input color-input color-primary-5" type="radio"
                                        name="theme-primary" id="switcher-primary4">
                                </div>
                                <div class="form-check switch-select ps-0 mt-1 color-primary-light">
                                    <div class="theme-container-primary"></div>
                                    <div class="pickr-container-primary" onchange="updateChartColor(this.value)">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="pattern-image mb-3">
                            <p class="switcher-style-head">Background Patterns:</p>
                            <div class="d-flex flex-wrap align-items-center switcher-style">
                                <div class="form-check switch-select m-2">
                                    <input class="form-check-input patternimage-input bg-pattern1" type="radio"
                                        name="background-pattern" id="switcher-pattern-img">
                                </div>
                                <div class="form-check switch-select m-2">
                                    <input class="form-check-input patternimage-input bg-pattern2" type="radio"
                                        name="background-pattern" id="switcher-pattern-img1">
                                </div>
                                <div class="form-check switch-select m-2">
                                    <input class="form-check-input patternimage-input bg-pattern3" type="radio"
                                        name="background-pattern" id="switcher-pattern-img2">
                                </div>
                                <div class="form-check switch-select m-2">
                                    <input class="form-check-input patternimage-input bg-pattern4" type="radio"
                                        name="background-pattern" id="switcher-pattern-img3" checked>
                                </div>
                                <div class="form-check switch-select m-2">
                                    <input class="form-check-input patternimage-input bg-pattern5" type="radio"
                                        name="background-pattern" id="switcher-pattern-img4">
                                </div>
                                <div class="form-check switch-select m-2">
                                    <input class="form-check-input patternimage-input bg-pattern6" type="radio"
                                        name="background-pattern" id="switcher-pattern-img5">
                                </div>
                                <div class="form-check switch-select m-2">
                                    <input class="form-check-input patternimage-input bg-pattern7" type="radio"
                                        name="background-pattern" id="switcher-pattern-img6">
                                </div>
                                <div class="form-check switch-select m-2">
                                    <input class="form-check-input patternimage-input bg-pattern8" type="radio"
                                        name="background-pattern" id="switcher-pattern-img7">
                                </div>
                                <div class="form-check switch-select m-2">
                                    <input class="form-check-input patternimage-input bg-pattern9" type="radio"
                                        name="background-pattern" id="switcher-pattern-img8">
                                </div>
                                <div class="form-check switch-select m-2">
                                    <input class="form-check-input patternimage-input bg-pattern10" type="radio"
                                        name="background-pattern" id="switcher-pattern-img9">
                                </div>
                            </div>
                        </div>
                        <div class="card-style mb-3">
                            <p class="switcher-style-head">Card Styling:</p>
                            <div class="d-flex flex-wrap align-items-center switcher-style">
                                <div class="form-check switch-select m-2">
                                    <input class="form-check-input card-input card-style1" type="radio"
                                        name="card-style" id="switcher-card-style" checked>
                                </div>
                                <div class="form-check switch-select m-2">
                                    <input class="form-check-input card-input card-style2" type="radio"
                                        name="card-style" id="switcher-card-style1">
                                </div>
                                <div class="form-check switch-select m-2">
                                    <input class="form-check-input card-input card-style3" type="radio"
                                        name="card-style" id="switcher-card-style2">
                                </div>
                                <div class="form-check switch-select m-2">
                                    <input class="form-check-input card-input card-style4" type="radio"
                                        name="card-style" id="switcher-card-style3">
                                </div>
                                <div class="form-check switch-select m-2">
                                    <input class="form-check-input card-input card-style5" type="radio"
                                        name="card-style" id="switcher-card-style4">
                                </div>
                                <div class="form-check switch-select m-2">
                                    <input class="form-check-input card-input card-style6" type="radio"
                                        name="card-style" id="switcher-card-style5">
                                </div>
                                <div class="form-check switch-select m-2">
                                    <input class="form-check-input card-input card-style7" type="radio"
                                        name="card-style" id="switcher-card-style6">
                                </div>
                                <div class="form-check switch-select m-2">
                                    <input class="form-check-input card-input card-style8" type="radio"
                                        name="card-style" id="switcher-card-style7">
                                </div>
                                <div class="form-check switch-select m-2">
                                    <input class="form-check-input card-input card-style9" type="radio"
                                        name="card-style" id="switcher-card-style8">
                                </div>
                                <div class="form-check switch-select m-2">
                                    <input class="form-check-input card-input card-style10" type="radio"
                                        name="card-style" id="switcher-card-style9">
                                </div>
                            </div>
                        </div>
                        <div class="card-background mb-3">
                            <p class="switcher-style-head">Card Background:</p>
                            <div class="d-flex flex-wrap align-items-center switcher-style">
                                <div class="form-check switch-select m-2">
                                    <input class="form-check-input card-input card-background1" type="radio"
                                        name="card-background" id="switcher-card-background" checked>
                                </div>
                                <div class="form-check switch-select m-2">
                                    <input class="form-check-input card-input card-background2" type="radio"
                                        name="card-background" id="switcher-card-background1">
                                </div>
                                <div class="form-check switch-select m-2">
                                    <input class="form-check-input card-input card-background3" type="radio"
                                        name="card-background" id="switcher-card-background2">
                                </div>
                                <div class="form-check switch-select m-2">
                                    <input class="form-check-input card-input card-background4" type="radio"
                                        name="card-background" id="switcher-card-background3">
                                </div>
                                <div class="form-check switch-select m-2">
                                    <input class="form-check-input card-input card-background5" type="radio"
                                        name="card-background" id="switcher-card-background4">
                                </div>
                                <div class="form-check switch-select m-2">
                                    <input class="form-check-input card-input card-background6" type="radio"
                                        name="card-background" id="switcher-card-background5">
                                </div>
                                <div class="form-check switch-select m-2">
                                    <input class="form-check-input card-input card-background7" type="radio"
                                        name="card-background" id="switcher-card-background6">
                                </div>
                                <div class="form-check switch-select m-2">
                                    <input class="form-check-input card-input card-background8" type="radio"
                                        name="card-background" id="switcher-card-background7">
                                </div>
                                <div class="form-check switch-select m-2">
                                    <input class="form-check-input card-input card-background9" type="radio"
                                        name="card-background" id="switcher-card-background8">
                                </div>
                            </div>
                        </div>
                        <div class="menu-image mb-3">
                            <p class="switcher-style-head">Menu With Background Image:</p>
                            <div class="d-flex flex-wrap align-items-center switcher-style">
                                <div class="form-check switch-select m-2">
                                    <input class="form-check-input bgimage-input bg-img1" type="radio"
                                        name="theme-background" id="switcher-bg-img">
                                </div>
                                <div class="form-check switch-select m-2">
                                    <input class="form-check-input bgimage-input bg-img2" type="radio"
                                        name="theme-background" id="switcher-bg-img1">
                                </div>
                                <div class="form-check switch-select m-2">
                                    <input class="form-check-input bgimage-input bg-img3" type="radio"
                                        name="theme-background" id="switcher-bg-img2">
                                </div>
                                <div class="form-check switch-select m-2">
                                    <input class="form-check-input bgimage-input bg-img4" type="radio"
                                        name="theme-background" id="switcher-bg-img3">
                                </div>
                                <div class="form-check switch-select m-2">
                                    <input class="form-check-input bgimage-input bg-img5" type="radio"
                                        name="theme-background" id="switcher-bg-img4">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END SWITCHER -->
    <!-- PAGE -->
    <div class="page">

        <!-- HEADER -->

        <header class="app-header sticky">

            <!-- Start::main-header-container -->
            <div class="main-header-container container-fluid">

                <!-- Start::header-content-left -->
                <div class="header-content-left">

                    <!-- Start::header-element -->
                    <div class="header-element">
                        <div class="horizontal-logo">
                            <a href="{{ url('/') }}" class="header-logo">
                                <img style="height: 50px;" src="{{ asset('logo/logo.png') }}" alt="logo"
                                    class="desktop-dark">
                            </a>
                        </div>
                    </div>
                    <!-- End::header-element -->

                    <!-- Start::header-element -->
                    <div class="header-element mx-lg-0 mx-2">
                        <a aria-label="Hide Sidebar"
                            class="sidemenu-toggle header-link animated-arrow hor-toggle horizontal-navtoggle"
                            data-bs-toggle="sidebar" href="javascript:void(0);"><span></span></a>
                    </div>
                    <!-- End::header-element -->

                </div>
                <!-- End::header-content-left -->

                <!-- Start::header-content-right -->
                <ul class="header-content-right">
                    <!--<li class="header-element" style="padding-top:25px">-->
                    <!--    <marquee>-->
                    <!--        <h6 class="text-warning">-->
                    <!--          -->
                    <!--        </h6>-->
                    <!--    </marquee>-->
                    <!--</li>-->
                    <!-- Start::header-element -->
                    <!--<li class="header-element header-search d-md-block d-none">-->
                    <!-- Start::header-link -->
                    <!--    <input type="text" class="header-search-bar form-control border-0"-->
                    <!--        placeholder="Search for Results...">-->
                    <!--    <a href="javascript:void(0);" class="header-search-icon border-0">-->
                    <!--        <i class="bi bi-search"></i>-->
                    <!--    </a>-->
                    <!-- End::header-link -->
                    <!--</li>-->
                    <!-- End::header-element -->

                    <!-- Start::header-element -->
                    <!--<li class="header-element d-md-none d-block">-->
                    <!--    <a href="javascript:void(0);" class="header-link" data-bs-toggle="modal"-->
                    <!--        data-bs-target="#header-responsive-search">-->
                    <!-- Start::header-link-icon -->
                    <!--        <i class="bi bi-search header-link-icon"></i>-->
                    <!-- End::header-link-icon -->
                    <!--    </a>-->
                    <!--</li>-->
                    <!-- End::header-element -->

                    <!-- Start::header-element -->
                    <!--<li class="header-element notifications-dropdown  dropdown">-->
                    <!-- Start::header-link|dropdown-toggle -->
                    <!--    <a href="javascript:void(0);" class="header-link dropdown-toggle"-->
                    <!--        data-bs-auto-close="outside" data-bs-toggle="dropdown">-->
                    <!--        <i class="bi bi-bell header-link-icon"></i>-->
                    <!--        <span class="header-icon-pulse bg-warning rounded pulse"></span>-->
                    <!--    </a>-->
                    <!--    <div class="main-header-dropdown dropdown-menu dropdown-menu-end"-->
                    <!--        data-popper-placement="none">-->
                    <!--        <div class="p-3">-->
                    <!--            <div class="d-flex align-items-center justify-content-between">-->
                    <!--                <p class="mb-0 fs-16">Notifications</p>-->
                    <!--                {{-- <span class="badge bg-secondary-transparent" id="notifiation-data">4 Unread</span> --}}-->
                    <!--            </div>-->
                    <!--        </div>-->
                    <!--        <div class="dropdown-divider"></div>-->
                    <!--        <ul class="list-unstyled mb-0" id="header-notification-scroll">-->
                    <!--            {{-- @if (count(getNotification($data['memberid'])) > 0)-->
                    <!--                @php-->
                    <!--                    $msg = getNotification($data['memberid']);-->
                    <!--                @endphp-->
                    <!--                @foreach ($msg as $item)-->
                    <!--                    <li class="dropdown-item">-->
                    <!--                        <div class="d-flex align-items-center">-->
                    <!--                            <div class="pe-2 lh-1">-->
                    <!--                                <span class="avatar avatar-rounded">-->
                    <!--                                    <img src="{{ asset('uploads/avatar.jpg') }}" alt="">-->
                    <!--                                </span>-->
                    <!--                            </div>-->
                    <!--                            <div-->
                    <!--                                class="flex-grow-1 d-flex align-items-center justify-content-between">-->
                    <!--                                <div>-->
                    <!--                                    <p class="mb-0 fw-medium">{{ $item['title'] }}</p>-->
                    <!--                                    <span-->
                    <!--                                        class="text-muted fw-normal fs-12 header-notification-text">{{ $item['message'] }}</span>-->
                    <!--                                </div>-->
                    <!--                                <div>-->
                    <!--                                    <a href="javascript:void(0);"-->
                    <!--                                        class="min-w-fit-content text-muted me-1 dropdown-item-close1"><i-->
                    <!--                                            class="bi bi-x"></i></a>-->
                    <!--                                </div>-->
                    <!--                            </div>-->
                    <!--                        </div>-->
                    <!--                    </li>-->
                    <!--                @endforeach-->
                    <!--            @else-->
                    <!--            @endif --}}-->

                    <!--        </ul>-->
                    <!--        <div class="p-3 empty-header-item1 border-top">-->
                    <!--            <div class="text-center">-->
                    <!--                <a href="{{ url('/member/notifications') }}"-->
                    <!--                    class="link-primary text-decoration-underline">View All</a>-->
                    <!--            </div>-->
                    <!--        </div>-->
                    <!--        <div class="p-5 empty-item1 d-none">-->
                    <!--            <div class="text-center">-->
                    <!--                <span class="avatar avatar-xl avatar-rounded bg-secondary-transparent">-->
                    <!--                    <i class="bi bi-bell-slash fs-2"></i>-->
                    <!--                </span>-->
                    <!--                <h6 class="fw-medium mt-3">No New Notifications</h6>-->
                    <!--            </div>-->
                    <!--        </div>-->
                    <!--    </div>-->
                    <!-- End::main-header-dropdown -->
                    <!--</li>-->
                    <!-- End::header-element -->

                    <!-- Start::header-element -->
                    <li class="header-element dropdown">
                        <!-- Start::header-link|dropdown-toggle -->
                        <a href="javascript:void(0);" class="header-link dropdown-toggle member-profile-toggle" id="mainHeaderProfile"
                            data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                            <div class="d-flex align-items-center">
                                <!-- <div>
                                    @if ($data['profile_image'] != '')
<img src="{{ asset('uploads') }}/{{ $data['profile_image'] }}"
                                            alt="img" class="avatar avatar-sm rounded-0">
@else
<img src="{{ asset('uploads/avatar.jpg') }}" alt="img"
                                            class="avatar avatar-sm rounded-0">
@endif
                                    {{-- <img src="{{ asset('mem_assets/images/faces/22.jpg') }}" alt="img"
                                        class="avatar avatar-sm rounded-0"> --}}
                                </div>  -->
                                <span class="mx-2">{{ $data['memberid'] }}</span>
                            </div>
                        </a>
                        <!-- End::header-link|dropdown-toggle -->
                        <ul class="main-header-dropdown dropdown-menu pt-0 overflow-hidden header-profile-dropdown dropdown-menu-end"
                            aria-labelledby="mainHeaderProfile">
                            <li><a class="dropdown-item d-flex align-items-center"
                                    href="{{ url('/member/profile/user-summary') }}"><i
                                        class="bi bi-person fs-18 me-2 op-7"></i>Profile</a></li>
                            <li><a class="dropdown-item d-flex align-items-center"
                                    href="{{ url('/member/logout') }}"><i
                                        class="bi bi-box-arrow-right fs-18 me-2 op-7"></i>Log Out</a></li>
                        </ul>
                    </li>
                    <!-- End::header-element -->

                    <!-- Start::header-element -->
                    <!--<li class="header-element">-->
                    <!-- Start::header-link|switcher-icon -->
                    <!--    <a href="javascript:void(0);" class="header-link switcher-icon" data-bs-toggle="offcanvas"-->
                    <!--        data-bs-target="#switcher-canvas">-->
                    <!--        <i class="bi bi-gear header-link-icon border-0"></i>-->
                    <!--    </a>-->
                    <!-- End::header-link|switcher-icon -->
                    <!--</li>-->
                    <!-- End::header-element -->

                </ul>
                <!-- End::header-content-right -->

            </div>
            <!-- End::main-header-container -->

        </header>
        <!-- END HEADER -->

        <!-- SIDEBAR -->

        <aside class="app-sidebar sticky" id="sidebar">

            <div class="top-left"></div>
            <div class="top-right"></div>
            <div class="bottom-left"></div>
            <div class="bottom-right"></div>
            <!-- Start::main-sidebar-header -->
            <div class="main-sidebar-header">
                <a href="{{ url('/') }}" class="header-logo">
                    <img src="{{ asset('mem_assets/images/logo.png') }}" alt="logo" class="desktop-logo">

                    <img src="{{ asset('mem_assets/images/logo.png') }}" alt="logo" class="toggle-dark">
                    <img src="{{ asset('mem_assets/images/logo.png') }}" alt="logo" class="desktop-dark">
                    <img src="{{ asset('mem_assets/images/logo.pngg') }}" alt="logo" class="toggle-logo">
                </a>
                <div class="member-sidebar-brand">
                    <span class="member-sidebar-brand__eyebrow">Private Member Suite</span>
                    <strong>{{ config('detailsApp.name') }}</strong>
                    <span class="member-sidebar-brand__meta">Premium dashboard access</span>
                </div>
            </div>
            <!-- End::main-sidebar-header -->

            <!-- Start::main-sidebar -->
            <div class="main-sidebar" id="sidebar-scroll">

                <!-- Start::nav -->
                <nav class="main-menu-container nav nav-pills flex-column sub-open">
                    <div class="slide-left" id="slide-left">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24"
                            viewBox="0 0 24 24">
                            <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"></path>
                        </svg>
                    </div>
                    <ul class="main-menu">
                        @php
                            $token = 'EURUSD';
                        @endphp
                        <!-- Start::slide -->
                        {{-- <li class="slide has-sub">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon"
                                    viewBox="0 0 256 256">
                                    <rect width="256" height="256" fill="none" />
                                    <path
                                        d="M152,208V160a8,8,0,0,0-8-8H112a8,8,0,0,0-8,8v48a8,8,0,0,1-8,8H48a8,8,0,0,1-8-8V115.54a8,8,0,0,1,2.62-5.92l80-75.54a8,8,0,0,1,10.77,0l80,75.54a8,8,0,0,1,2.62,5.92V208a8,8,0,0,1-8,8H160A8,8,0,0,1,152,208Z"
                                        opacity="0.2" />
                                    <path
                                        d="M152,208V160a8,8,0,0,0-8-8H112a8,8,0,0,0-8,8v48a8,8,0,0,1-8,8H48a8,8,0,0,1-8-8V115.54a8,8,0,0,1,2.62-5.92l80-75.54a8,8,0,0,1,10.77,0l80,75.54a8,8,0,0,1,2.62,5.92V208a8,8,0,0,1-8,8H160A8,8,0,0,1,152,208Z"
                                        fill="none" stroke="currentColor" stroke-linecap="round"
                                        stroke-linejoin="round" stroke-width="16" />
                                </svg>
                                <span class="side-menu__label">Dashboards</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0)">Dashboards</a>
                                </li>
                                <li class="slide">
                                    <a href="{{ url('/member/dashboard') }}" class="side-menu__item">
                                        Dashboard</a>
                                </li>
                                <li class="slide">
                                    <a href="{{ url('/member/trade-dashboard') }}" class="side-menu__item">Trade
                                        Dashboard</a>
                                </li>
                                <li class="slide">
                                    <a href="{{ url('/member/staking-dashboard') }}" class="side-menu__item">Staking
                                        Dashboard</a>
                                </li>

                            </ul>
                        </li> --}}
                        <!-- End::slide -->

                        <!-- Start::slide -->
                        <li class="slide mt-3">
                            <a href="{{ url('/member/dashboard') }}" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon"
                                    viewBox="0 0 256 256">
                                    <rect width="256" height="256" fill="none" />
                                    <path
                                        d="M152,208V160a8,8,0,0,0-8-8H112a8,8,0,0,0-8,8v48a8,8,0,0,1-8,8H48a8,8,0,0,1-8-8V115.54a8,8,0,0,1,2.62-5.92l80-75.54a8,8,0,0,1,10.77,0l80,75.54a8,8,0,0,1,2.62,5.92V208a8,8,0,0,1-8,8H160A8,8,0,0,1,152,208Z"
                                        opacity="0.2" />
                                    <path
                                        d="M152,208V160a8,8,0,0,0-8-8H112a8,8,0,0,0-8,8v48a8,8,0,0,1-8,8H48a8,8,0,0,1-8-8V115.54a8,8,0,0,1,2.62-5.92l80-75.54a8,8,0,0,1,10.77,0l80,75.54a8,8,0,0,1,2.62,5.92V208a8,8,0,0,1-8,8H160A8,8,0,0,1,152,208Z"
                                        fill="none" stroke="currentColor" stroke-linecap="round"
                                        stroke-linejoin="round" stroke-width="16" />
                                </svg>
                                <span class="side-menu__label">Dashboard</span>
                            </a>
                        </li>
                        <!-- End::slide -->

                        <!-- Start::slide -->
                        <li class="slide has-sub">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon"
                                    viewBox="0 0 256 256">
                                    <rect width="256" height="256" fill="none" />
                                    <path
                                        d="M128,32A96,96,0,0,0,63.8,199.38h0A72,72,0,0,1,128,160a40,40,0,1,1,40-40,40,40,0,0,1-40,40,72,72,0,0,1,64.2,39.37A96,96,0,0,0,128,32Z"
                                        opacity="0.2" />
                                    <circle cx="128" cy="120" r="40" fill="none"
                                        stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="16" />
                                    <path d="M63.8,199.37a72,72,0,0,1,128.4,0" fill="none" stroke="currentColor"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="16" />
                                    <polyline points="200 128 224 152 248 128" fill="none" stroke="currentColor"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="16" />
                                    <polyline points="8 128 32 104 56 128" fill="none" stroke="currentColor"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="16" />
                                    <path d="M32,104v24a96,96,0,0,0,174,56" fill="none" stroke="currentColor"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="16" />
                                    <path d="M224,152V128A96,96,0,0,0,50,72" fill="none" stroke="currentColor"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="16" />
                                </svg>
                                <span class="side-menu__label">Profile</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0)">Profile</a>
                                </li>
                                <li class="slide">
                                    <a href="{{ url('/member/profile/user-summary') }}" class="side-menu__item">Edit
                                        Profile</a>
                                </li>
                                <li class="slide">
                                    <a href="{{ url('/member/profile/security') }}" class="side-menu__item">Change
                                        Password</a>
                                </li>

                            </ul>
                        </li>
                        <!-- End::slide -->

                        <!-- Start::slide -->
                        {{-- <li class="slide has-sub">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon"
                                    viewBox="0 0 256 256">
                                    <rect width="256" height="256" fill="none" />
                                    <rect x="152" y="40" width="56" height="168" opacity="0.2" />
                                    <polyline points="48 208 48 136 96 136" fill="none" stroke="currentColor"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="16" />
                                    <line x1="224" y1="208" x2="32" y2="208"
                                        fill="none" stroke="currentColor" stroke-linecap="round"
                                        stroke-linejoin="round" stroke-width="16" />
                                    <polyline points="96 208 96 88 152 88" fill="none" stroke="currentColor"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="16" />
                                    <polyline points="152 208 152 40 208 40 208 208" fill="none"
                                        stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="16" />
                                </svg>
                                <i class="bx bxs-bank " style="font-size: 16px"></i>
                                <span class="side-menu__label">Activation</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0)">Activation</a>
                                </li>
                                <li class="slide">
                                    <a href="{{ url('/member/account/account-activation') }}"
                                        class="side-menu__item">Account Activation</a>
                                </li>
                                <li class="slide">
                                    <a href="{{ url('/member/account/upgrade-activation') }}"
                                        class="side-menu__item">Upgrade Activation</a>
                                </li>
                                <li class="slide">
                                    <a href="{{ url('/member/account/packages-details') }}"
                                        class="side-menu__item">Packages Details</a>
                                </li>
                            </ul>
                        </li> --}}
                        <!-- End::slide -->

                        <!-- Start::slide -->
                        <li class="slide has-sub">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <i class="bx bxs-bank " style="font-size: 16px"></i>
                                <span class="side-menu__label mx-2">Deposit Section</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0)">Deposit Fund</a>
                                </li>

                                <li class="slide">
                                    <a href="{{ url('/member/fund/import-fund') }}" class="side-menu__item">Deposit
                                        Fund</a>
                                </li>
                                <li class="slide">
                                    <a href="{{ url('/member/fund/import-fund-history') }}"
                                        class="side-menu__item">Deposit Fund History</a>
                                </li>

                            </ul>
                        </li>
                        <!-- End::slide -->

                        <!-- Start::slide -->
                        <li class="slide has-sub">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <div class="side-menu__icon mt-1">
                                    <i class="bx bx-money-withdraw"></i>
                                </div>
                                <span class="side-menu__label">Investment</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0)">Investment </a>
                                </li>
                                <li class="slide">
                                    <a href="{{ url('/member/staking') }}" class="side-menu__item">Create
                                        Investment</a>
                                </li>
                                <li class="slide">
                                    <a href="{{ url('/member/staking/staking-details') }}"
                                        class="side-menu__item">Investment Details</a>
                                </li>

                            </ul>
                        </li>
                        <!-- End::slide -->

                        <!-- Start::slide -->
                        <li class="slide has-sub">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <div class="side-menu__icon mt-1">
                                    <i class="bx bx-money"></i>
                                </div>
                                <span class="side-menu__label">Earnings </span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">

                                <!--<li class="slide">-->
                                <!--    <a href="{{ url('member/income/staking-details') }}" class="side-menu__item">Staking-->
                                <!--        Details-->
                                <!--    </a>-->
                                <!--</li>-->

                                <li class="slide">
                                    <a href="{{ url('member/income/staking-incomes') }}" class="side-menu__item">Staking
                                        Income
                                    </a>
                                </li>

                                <!--<li class="slide">-->
                                <!--    <a href="{{ url('member/income/level-details') }}" class="side-menu__item">Level-->
                                <!--        Details-->
                                <!--    </a>-->
                                <!--</li>-->

                                <li class="slide">
                                    <a href="{{ url('member/income/level-incomes') }}" class="side-menu__item">Level
                                        Income
                                    </a>
                                </li>

                            </ul>
                        </li>
                        <li class="slide has-sub">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <div class="side-menu__icon mt-1">
                                    <i class="bx bx-group"></i>
                                </div>
                                <span class="side-menu__label">Team Management</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0)">Team</a>
                                </li>
                                <li class="slide">
                                    <a href="{{ url('/member/team/geneology') }}"
                                        class="side-menu__item">Geneology</a>
                                </li>
                                <li class="slide">
                                    <a href="{{ url('/member/levels/direct-members') }}"
                                        class="side-menu__item">Direct Members</a>
                                </li>
                                {{-- <li class="slide">
                                    <a href="{{ url('/member/levels/total-team-members') }}"
                                        class="side-menu__item">Total Team Members</a>
                                </li> --}}
                                <li class="slide">
                                    <a href="{{ url('/member/levels/level-members-table') }}"
                                        class="side-menu__item">Level Member Table</a>
                                </li>
                            </ul>
                        </li>

                        <!--<li class="slide has-sub">-->
                        <!--    <a href="javascript:void(0);" class="side-menu__item">-->
                        <!--        <div class="side-menu__icon mt-1">-->
                        <!--            <i class="bx bx-money"></i>-->
                        <!--        </div>-->
                        <!--        <span class="side-menu__label">Rank Section</span>-->
                        <!--        <i class="fe fe-chevron-right side-menu__angle"></i>-->
                        <!--    </a>-->
                        <!--    <ul class="slide-menu child1">-->
                        <!--        <li class="slide side-menu__label1">-->
                        <!--            <a href="javascript:void(0)">Income</a>-->
                        <!--        </li>-->
                        <!--        <li class="slide">-->
                        <!--            <a href="{{ url('member/rank/rank-income') }}" class="side-menu__item">Rank-->
                        <!--                Income</a>-->
                        <!--        </li>-->
                        <!--        <li class="slide">-->
                        <!--            <a href="{{ url('member/rank/rank-detail') }}" class="side-menu__item">Rank-->
                        <!--                Detail</a>-->
                        <!--        </li>-->
                        <!--    </ul>-->
                        <!--</li>-->
                        <!-- End::slide -->

                        <!-- Start::slide -->
                        <li class="slide has-sub">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <i class="bx bxs-bank " style="font-size: 16px"></i>
                                <span class="side-menu__label mx-2">Withdrawal Section</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide">
                                    <a href="{{ url('member/wallet') }}" class="side-menu__item">Withdrawal Fund</a>
                                </li>
                                <li class="slide">
                                    <a href="{{ url('member/wallet/wallet-history') }}"
                                        class="side-menu__item">Withdrawal History</a>
                                </li>
                                <li class="slide">
                                    <a href="{{ url('/member/profile/wallet-address') }}"
                                        class="side-menu__item">Settings (Wallet Address)</a>
                                </li>
                                <li class="slide">
                                    <a href="{{ url('/member/wallet/all-transaction') }}" class="side-menu__item">
                                        Transaction History</a>
                                </li>

                            </ul>
                        </li>
                        <!-- End::slide -->

                        <!-- Start::slide -->
                        <li class="slide has-sub">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <div class="side-menu__icon mt-1">
                                    <i class="ri ri-p2p-fill mt-2"></i>
                                </div>
                                <span class="side-menu__label">P2P </span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0)">P2P </a>
                                </li>
                                <li class="slide">
                                    <a href="{{ url('/member/p2p-wallet') }}" class="side-menu__item">P2P
                                        Wallet</a>
                                </li>
                                <li class="slide">
                                    <a href="{{ url('/member/p2p-wallet-history') }}" class="side-menu__item">P2P
                                        History</a>
                                </li>
                            </ul>
                        </li>
                        <!-- End::slide -->

                        <!-- Start::slide -->

                        <!-- End::slide -->

                        <!-- Start::slide -->

                        <!-- End::slide -->

                        <!-- Start::slide -->
                        <li class="slide has-sub">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <div class="side-menu__icon mt-1">
                                    <i class="bx bx-chat"></i>
                                </div>
                                <span class="side-menu__label">Support </span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0)">Support</a>
                                </li>
                                <li class="slide">
                                    <a href="{{ url('/member/support/create-ticket') }}"
                                        class="side-menu__item">Create New Ticket</a>
                                </li>
                                <li class="slide">
                                    <a href="{{ url('member/support/support-tickets') }}"
                                        class="side-menu__item">Support Tickets</a>
                                </li>
                            </ul>
                        </li>

                        {{-- <li class="slide has-sub">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <div class="side-menu__icon mt-1">
                                    <i class="bx bx-line-chart"></i> <!-- Trading chart icon -->
                                </div>
                                <span class="side-menu__label">Trading </span>
                                <i class="fe fe-trending-up side-menu__angle"></i> <!-- Upward trading arrow -->
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide">
                                    <a href="{{ url('member/trading/arbitrage-trading') }}" class="side-menu__item">
                                        <i class="bx bx-bar-chart-alt-2"></i> <!-- Bar chart icon -->
                                        Arbitrage Trading
                                    </a>
                                </li>
                            </ul>
                        </li> --}}

                        <!-- End::slide -->


                        <!-- Start::slide -->
                        <li class="slide">
                            <a href="{{ url('/member/logout') }}" class="side-menu__item">
                                <div class="side-menu__icon mt-1">
                                    <i class="bx bx-exit"></i>
                                </div>
                                <span class="side-menu__label">Logout</span>
                            </a>
                        </li>
                        <!-- End::slide -->
                        {{-- <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none" />
                            <polygon points="152 32 152 88 208 88 152 32" opacity="0.2" />
                            <path d="M200,224H56a8,8,0,0,1-8-8V40a8,8,0,0,1,8-8h96l56,56V216A8,8,0,0,1,200,224Z"
                                fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="16" />
                            <polyline points="152 32 152 88 208 88" fill="none" stroke="currentColor"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="16" />
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none" />
                            <path d="M216,96v96a8,8,0,0,1-8,8H48a8,8,0,0,1-8-8V96Z" opacity="0.2" />
                            <rect x="24" y="56" width="208" height="40" rx="8" fill="none"
                                stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="16" />
                            <path d="M216,96v96a8,8,0,0,1-8,8H48a8,8,0,0,1-8-8V96" fill="none"
                                stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="16" />
                            <line x1="104" y1="136" x2="152" y2="136" fill="none"
                                stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="16" />
                        </svg>

                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none" />
                            <path
                                d="M128,32A96,96,0,0,0,63.8,199.38h0A72,72,0,0,1,128,160a40,40,0,1,1,40-40,40,40,0,0,1-40,40,72,72,0,0,1,64.2,39.37A96,96,0,0,0,128,32Z"
                                opacity="0.2" />
                            <circle cx="128" cy="120" r="40" fill="none" stroke="currentColor"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="16" />
                            <path d="M63.8,199.37a72,72,0,0,1,128.4,0" fill="none" stroke="currentColor"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="16" />
                            <polyline points="200 128 224 152 248 128" fill="none" stroke="currentColor"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="16" />
                            <polyline points="8 128 32 104 56 128" fill="none" stroke="currentColor"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="16" />
                            <path d="M32,104v24a96,96,0,0,0,174,56" fill="none" stroke="currentColor"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="16" />
                            <path d="M224,152V128A96,96,0,0,0,50,72" fill="none" stroke="currentColor"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="16" />
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none" />
                            <path
                                d="M142.41,40.22l87.46,151.87C236,202.79,228.08,216,215.46,216H40.54C27.92,216,20,202.79,26.13,192.09L113.59,40.22C119.89,29.26,136.11,29.26,142.41,40.22Z"
                                opacity="0.2" />
                            <path
                                d="M142.41,40.22l87.46,151.87C236,202.79,228.08,216,215.46,216H40.54C27.92,216,20,202.79,26.13,192.09L113.59,40.22C119.89,29.26,136.11,29.26,142.41,40.22Z"
                                fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="16" />
                            <line x1="128" y1="144" x2="128" y2="104" fill="none"
                                stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="16" />
                            <circle cx="128" cy="180" r="12" />
                        </svg>


                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none" />
                            <rect x="152" y="40" width="56" height="168" opacity="0.2" />
                            <polyline points="48 208 48 136 96 136" fill="none" stroke="currentColor"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="16" />
                            <line x1="224" y1="208" x2="32" y2="208" fill="none"
                                stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="16" />
                            <polyline points="96 208 96 88 152 88" fill="none" stroke="currentColor"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="16" />
                            <polyline points="152 208 152 40 208 40 208 208" fill="none" stroke="currentColor"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="16" />
                        </svg>

                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none" />
                            <rect x="48" y="48" width="64" height="64" rx="8" opacity="0.2" />
                            <rect x="144" y="48" width="64" height="64" rx="8" opacity="0.2" />
                            <rect x="48" y="144" width="64" height="64" rx="8" opacity="0.2" />
                            <rect x="144" y="144" width="64" height="64" rx="8" opacity="0.2" />
                            <rect x="144" y="144" width="64" height="64" rx="8" fill="none"
                                stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="16" />
                            <rect x="48" y="48" width="64" height="64" rx="8" fill="none"
                                stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="16" />
                            <rect x="144" y="48" width="64" height="64" rx="8" fill="none"
                                stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="16" />
                            <rect x="48" y="144" width="64" height="64" rx="8" fill="none"
                                stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="16" />
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none" />
                            <polygon points="32 80 128 136 224 80 128 24 32 80" opacity="0.2" />
                            <polyline points="32 176 128 232 224 176" fill="none" stroke="currentColor"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="16" />
                            <polyline points="32 128 128 184 224 128" fill="none" stroke="currentColor"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="16" />
                            <polygon points="32 80 128 136 224 80 128 24 32 80" fill="none"
                                stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="16" />
                        </svg>

                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none" />
                            <path d="M128,36a92,92,0,1,0,92,92A92.1,92.1,0,0,0,128,36Z" opacity="0.2" />
                            <line x1="128" y1="64" x2="128" y2="192" fill="none"
                                stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="16" />
                            <polyline points="104 40 128 64 152 40" fill="none" stroke="currentColor"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="16" />
                            <polyline points="104 216 128 192 152 216" fill="none" stroke="currentColor"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="16" />
                            <line x1="72.57" y1="96" x2="183.43" y2="160" fill="none"
                                stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="16" />
                            <polyline points="40 104 72.57 96 64 64" fill="none" stroke="currentColor"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="16" />
                            <polyline points="192 192 183.43 160 216 152" fill="none" stroke="currentColor"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="16" />
                            <line x1="72.57" y1="160" x2="183.43" y2="96" fill="none"
                                stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="16" />
                            <polyline points="64 192 72.57 160 40 152" fill="none" stroke="currentColor"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="16" />
                            <polyline points="216 104 183.43 96 192 64" fill="none" stroke="currentColor"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="16" />
                        </svg>



                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none" />
                            <circle cx="128" cy="96" r="48" opacity="0.2" />
                            <circle cx="128" cy="96" r="80" fill="none" stroke="currentColor"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="16" />
                            <circle cx="128" cy="96" r="48" fill="none" stroke="currentColor"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="16" />
                            <polyline points="176 160 176 240 127.99 216 80 240 80 160.01" fill="none"
                                stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="16" />
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none" />
                            <polygon points="152 32 152 88 208 88 152 32" opacity="0.2" />
                            <path d="M200,224H56a8,8,0,0,1-8-8V40a8,8,0,0,1,8-8h96l56,56V216A8,8,0,0,1,200,224Z"
                                fill="none" stroke="currentColor" stroke-linecap="round"
                                stroke-linejoin="round" stroke-width="16" />
                            <polyline points="152 32 152 88 208 88" fill="none" stroke="currentColor"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="16" />
                        </svg>

                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none" />
                            <path d="M208,128v72a8,8,0,0,1-8,8H56a8,8,0,0,1-8-8V128Z" opacity="0.2" />
                            <rect x="32" y="80" width="192" height="48" rx="8" fill="none"
                                stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="16" />
                            <path d="M208,128v72a8,8,0,0,1-8,8H56a8,8,0,0,1-8-8V128" fill="none"
                                stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="16" />
                            <line x1="128" y1="80" x2="128" y2="208" fill="none"
                                stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="16" />
                            <path
                                d="M176.79,31.21c9.34,9.34,9.89,25.06,0,33.82C159.88,80,128,80,128,80s0-31.88,15-48.79C151.73,21.32,167.45,21.87,176.79,31.21Z"
                                fill="none" stroke="currentColor" stroke-linecap="round"
                                stroke-linejoin="round" stroke-width="16" />
                            <path
                                d="M79.21,31.21c-9.34,9.34-9.89,25.06,0,33.82C96.12,80,128,80,128,80s0-31.88-15-48.79C104.27,21.32,88.55,21.87,79.21,31.21Z"
                                fill="none" stroke="currentColor" stroke-linecap="round"
                                stroke-linejoin="round" stroke-width="16" />
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none" />
                            <rect x="48" y="48" width="160" height="160" rx="8" opacity="0.2" />
                            <rect x="48" y="48" width="160" height="160" rx="8" fill="none"
                                stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="16" />
                            <line x1="128" y1="48" x2="128" y2="208" fill="none"
                                stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="16" />
                            <line x1="48" y1="128" x2="208" y2="128" fill="none"
                                stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="16" />
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none" />
                            <path
                                d="M128,24a80,80,0,0,0-80,80c0,72,80,128,80,128s80-56,80-128A80,80,0,0,0,128,24Zm0,112a32,32,0,1,1,32-32A32,32,0,0,1,128,136Z"
                                opacity="0.2" />
                            <circle cx="128" cy="104" r="32" fill="none" stroke="currentColor"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="16" />
                            <path d="M208,104c0,72-80,128-80,128S48,176,48,104a80,80,0,0,1,160,0Z" fill="none"
                                stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="16" />
                        </svg>


                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none" />
                            <path d="M54,40H202a8,8,0,0,1,7.69,5.8L224,96H32L46.34,45.8A8,8,0,0,1,54,40Z"
                                opacity="0.2" />
                            <path d="M96,96v16a32,32,0,0,1-64,0V96Z" opacity="0.2" />
                            <path d="M224,96v16a32,32,0,0,1-64,0V96Z" opacity="0.2" />
                            <path d="M48,139.59V208a8,8,0,0,0,8,8H200a8,8,0,0,0,8-8V139.59" fill="none"
                                stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="16" />
                            <path d="M54,40H202a8,8,0,0,1,7.69,5.8L224,96H32L46.34,45.8A8,8,0,0,1,54,40Z"
                                fill="none" stroke="currentColor" stroke-linecap="round"
                                stroke-linejoin="round" stroke-width="16" />
                            <path d="M96,96v16a32,32,0,0,1-64,0V96" fill="none" stroke="currentColor"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="16" />
                            <path d="M160,96v16a32,32,0,0,1-64,0V96" fill="none" stroke="currentColor"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="16" />
                            <path d="M224,96v16a32,32,0,0,1-64,0V96" fill="none" stroke="currentColor"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="16" />
                        </svg> --}}


                    </ul>
                    <div class="slide-right" id="slide-right"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191"
                            width="24" height="24" viewBox="0 0 24 24">
                            <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z">
                            </path>
                        </svg></div>
                </nav>
                <!-- End::nav -->

            </div>
            <!-- End::main-sidebar -->

        </aside>
        <!-- END SIDEBAR -->
