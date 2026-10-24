<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" >
<head>
	<title>@yield('title','') | {{ config('detailsApp.name', 'Demo') }}</title>
	<!-- initiate head with meta tags, css and script -->
	<meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- FAVICONS ICON -->
    <link rel="icon" type="image/png" href="{{ asset('logo/favicon/favicon-96x96.png') }}" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="{{ asset('logo/favicon/favicon.svg') }}" />
    <link rel="shortcut icon" href="{{ asset('logo/favicon/favicon.ico') }}" />
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('logo/favicon/apple-touch-icon.png') }}" />
    <link rel="manifest" href="{{ asset('logo/favicon/site.webmanifest') }}" />


    <!-- font awesome library -->
    <link href="https://fonts.googleapis.com/css?family=Nunito+Sans:300,400,600,700,800" rel="stylesheet">

    <script src="{{ asset('js/app.js') }}"></script>

    <!-- themekit admin template asstes -->
    <link rel="stylesheet" href="{{ asset('all.css') }}">
    <link rel="stylesheet" href="{{ asset('adm_assets/assets/dist/css/theme.css') }}">
    <link rel="stylesheet" href="{{ asset('adm_assets/assets/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adm_assets/assets/plugins/icon-kit/dist/css/iconkit.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adm_assets/assets/plugins/ionicons/dist/css/ionicons.min.css') }}">

    <link rel="stylesheet" href="{{ asset('adm_assets/assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('adm_assets/assets/plugins/weather-icons/css/weather-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adm_assets/assets/plugins/owl.carousel/dist/assets/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adm_assets/assets/plugins/owl.carousel/dist/assets/owl.theme.default.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adm_assets/assets/plugins/chartist/dist/chartist.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adm_assets/assets/plugins/DataTables/datatables.min.css') }}">

    <link rel="stylesheet" href="{{ asset('adm_assets/assets/plugins/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adm_assets/assets/plugins/summernote/dist/summernote-bs4.css') }}">
    <link rel="stylesheet" href="{{ asset('adm_assets/assets/plugins/bootstrap-tagsinput/dist/bootstrap-tagsinput.css') }}">
    <link rel="stylesheet" href="{{ asset('adm_assets/assets/plugins/mohithg-switchery/dist/switchery.min.css') }}">


</head>
<body id="app" >
    <div class="wrapper">
    	<!-- initiate header-->
        <header class="header-top" header-theme="light">
            <div class="container-fluid">
                <div class="d-flex justify-content-between">
                    <div class="top-menu d-flex align-items-center">
                        <button type="button" class="btn-icon mobile-nav-toggle d-lg-none"><span></span></button>
                        
                        <div class="header-search">
                            <div class="input-group">

                                <span class="input-group-addon search-close">
                                    <i class="ik ik-x"></i>
                                </span>
                                <input type="text" class="form-control">
                                <span class="input-group-addon search-btn"><i class="ik ik-search"></i></span>
                            </div>
                        </div>
                        <button class="nav-link" title="clear cache">
                            <a  href="{{url('clear-cache')}}">
                            <i class="ik ik-battery-charging"></i> 
                        </a>
                        </button> &nbsp;&nbsp;
                        <button type="button" id="navbar-fullscreen" class="nav-link"><i class="ik ik-maximize"></i></button>
                    </div>
                    <div class="top-menu d-flex align-items-center">
                        <div class="dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="notiDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="ik ik-bell"></i><span class="badge bg-danger">3</span></a>
                            <div class="dropdown-menu dropdown-menu-right notification-dropdown" aria-labelledby="notiDropdown">
                                <h4 class="header">{{ __('Notifications')}}</h4>
                                <div class="notifications-wrap">
                                    <a href="#" class="media">
                                        <span class="d-flex">
                                            <i class="ik ik-check"></i> 
                                        </span>
                                        <span class="media-body">
                                            <span class="heading-font-family media-heading">{{ __('Invitation accepted')}}</span> 
                                            <span class="media-content">{{ __('Your have been Invited ...')}}</span>
                                        </span>
                                    </a>
                                    <a href="#" class="media">
                                        <span class="d-flex">
                                            <img src="{{ asset('adm_assets/assets/img/users/1.jpg')}}" class="rounded-circle" alt="">
                                        </span>
                                        <span class="media-body">
                                            <span class="heading-font-family media-heading">{{ __('Steve Smith')}}</span> 
                                            <span class="media-content">{{ __('I slowly updated projects')}}</span>
                                        </span>
                                    </a>
                                    <a href="#" class="media">
                                        <span class="d-flex">
                                            <i class="ik ik-calendar"></i> 
                                        </span>
                                        <span class="media-body">
                                            <span class="heading-font-family media-heading">{{ __('To Do')}}</span> 
                                            <span class="media-content">{{ __('Meeting with Nathan on Friday 8 AM ...')}}</span>
                                        </span>
                                    </a>
                                </div>
                                <div class="footer"><a href="javascript:void(0);">{{ __('See all activity')}}</a></div>
                            </div>
                        </div>
                        <button type="button" class="nav-link ml-10 right-sidebar-toggle"><i class="ik ik-message-square"></i><span class="badge bg-success">3</span></button>
                        <div class="dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="menuDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="ik ik-plus"></i></a>
                            <div class="dropdown-menu dropdown-menu-right menu-grid" aria-labelledby="menuDropdown">
                                <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="Dashboard"><i class="ik ik-bar-chart-2"></i></a>
                                <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="Message"><i class="ik ik-mail"></i></a>
                                <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="Accounts"><i class="ik ik-users"></i></a>
                                <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="Sales"><i class="ik ik-shopping-cart"></i></a>
                                <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="Purchase"><i class="ik ik-briefcase"></i></a>
                                <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="Pages"><i class="ik ik-clipboard"></i></a>
                                <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="Chats"><i class="ik ik-message-square"></i></a>
                                <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="Contacts"><i class="ik ik-map-pin"></i></a>
                                <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="Blocks"><i class="ik ik-inbox"></i></a>
                                <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="Events"><i class="ik ik-calendar"></i></a>
                                <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="Notifications"><i class="ik ik-bell"></i></a>
                                <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="More"><i class="ik ik-more-horizontal"></i></a>
                            </div>
                        </div>
                        <button type="button" class="nav-link ml-10" id="apps_modal_btn" data-toggle="modal" data-target="#appsModal"><i class="ik ik-grid"></i></button>
                        <div class="dropdown">
                            <a class="dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><img class="avatar" src="{{ asset('img/user.jpg')}}" alt=""></a>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="{{url('profile')}}"><i class="ik ik-user dropdown-icon"></i> {{ __('Profile')}}</a>
                                <a class="dropdown-item" href="#"><i class="ik ik-navigation dropdown-icon"></i> {{ __('Message')}}</a>
                                <a class="dropdown-item" href="{{ url('logout') }}">
                                    <i class="ik ik-power dropdown-icon"></i> 
                                    {{ __('Logout')}}
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </header>

        <div class="app-sidebar colored">
            <div class="sidebar-header">
                <a class="header-brand" href="{{route('dashboard')}}">
                    <div class="logo-img">
                       <img width="30" src="{{ asset('logo/logo.png') }}" class="header-brand-img" title="Demo" alt="Demo" style="height: auto; object-fit: contain;"> 
                    </div>
                </a>
                <div class="sidebar-action"><i class="ik ik-arrow-left-circle"></i></div>
                <button id="sidebarClose" class="nav-close"><i class="ik ik-x"></i></button>
            </div>
        
            @php
                $segment1 = request()->segment(1);
                $segment2 = request()->segment(2);
            @endphp
            
            <div class="sidebar-content">
                <div class="nav-container">
                    <nav id="main-menu-navigation" class="navigation-main">
                        <div class="nav-item {{ ($segment1 == 'dashboard') ? 'active' : '' }}">
                            <a href="{{route('dashboard')}}"><i class="ik ik-bar-chart-2"></i><span>{{ __('Dashboard')}}</span></a>
                        </div>
                        <div class="nav-lavel">{{ __('Layouts')}} </div>
                        <div class="nav-item {{ ($segment1 == 'pos') ? 'active' : '' }}">
                            <a href="{{url('inventory')}}"><i class="ik ik-shopping-cart"></i><span>{{ __('Inventory')}}</span> <span class=" badge badge-success badge-right">{{ __('New')}}</span></a>
                        </div>
                        <div class="nav-item {{ ($segment1 == 'pos') ? 'active' : '' }}">
                            <a href="{{url('pos')}}"><i class="ik ik-printer"></i><span>{{ __('POS')}}</span> <span class=" badge badge-success badge-right">{{ __('New')}}</span></a>
                        </div>
                        <div class="nav-item {{ ($segment1 == 'users' || $segment1 == 'roles'||$segment1 == 'permission' ||$segment1 == 'user') ? 'active open' : '' }} has-sub">
                            <a href="#"><i class="ik ik-user"></i><span>{{ __('Adminstrator')}}</span></a>
                            <div class="submenu-content">
                                <!-- only those have manage_user permission will get access -->
                                @can('manage_user')
                                <a href="{{url('users')}}" class="menu-item {{ ($segment1 == 'users') ? 'active' : '' }}">{{ __('Users')}}</a>
                                <a href="{{url('user/create')}}" class="menu-item {{ ($segment1 == 'user' && $segment2 == 'create') ? 'active' : '' }}">{{ __('Add User')}}</a>
                                 @endcan
                                 <!-- only those have manage_role permission will get access -->
                                @can('manage_roles')
                                <a href="{{url('roles')}}" class="menu-item {{ ($segment1 == 'roles') ? 'active' : '' }}">{{ __('Roles')}}</a>
                                @endcan
                                <!-- only those have manage_permission permission will get access -->
                                @can('manage_permission')
                                <a href="{{url('permission')}}" class="menu-item {{ ($segment1 == 'permission') ? 'active' : '' }}">{{ __('Permission')}}</a>
                                @endcan
                            </div>
                        </div>
        
                        <div class="nav-lavel">{{ __('Documentation')}} </div>
                        <div class="nav-item {{ ($segment1 == 'rest-api') ? 'active' : '' }}">
                            <a href="{{url('rest-api')}}"><i class="ik ik-cloud"></i><span>{{ __('REST API')}}</span> </a>
                        </div>
                        <div class="nav-item {{ ($segment1 == 'permission-example') ? 'active' : '' }}">
                            <a href="{{url('permission-example')}}"><i class="ik ik-unlock"></i><span>{{ __('Laravel Permission')}}</span> </a>
                        </div>
                        <div class="nav-item {{ ($segment1 == 'table-datatable-edit') ? 'active' : '' }}">
                            <a href="{{url('table-datatable-edit')}}"><i class="ik ik-layout"></i><span>{{ __('Editable Datatable')}}</span>  </a>
        
                        </div>
                        <!-- end inventory pages -->
        
                        <div class="nav-lavel">{{ __('Themekit Pages')}} </div>
                        <div class="nav-item {{ ($segment1 == 'form-components' || $segment1 == 'form-advance'||$segment1 == 'form-addon') ? 'active open' : '' }} has-sub">
                            <a href="#"><i class="ik ik-edit"></i><span>{{ __('Forms')}}</span></a>
                            <div class="submenu-content">
                                <a href="{{url('form-components')}}" class="menu-item {{ ($segment1 == 'form-components') ? 'active' : '' }}">{{ __('Components')}}</a>
                                <a href="{{url('form-addon')}}" class="menu-item {{ ($segment1 == 'form-addon') ? 'active' : '' }}">{{ __('Add-On')}}</a>
                                <a href="{{url('form-advance')}}" class="menu-item {{ ($segment1 == 'form-advance') ? 'active' : '' }}">{{ __('Advance')}}</a>
                            </div>
                        </div>
                        <div class="nav-item {{ ($segment1 == 'form-picker') ? 'active' : '' }}">
                            <a href="{{url('form-picker')}}"><i class="ik ik-terminal"></i><span>{{ __('Form Picker')}}</span> </a>
                        </div>
        
                        <div class="nav-item {{ ($segment1 == 'table-bootstrap') ? 'active' : '' }}">
                            <a href="{{url('table-bootstrap')}}"><i class="ik ik-credit-card"></i><span>{{ __('Bootstrap Table')}}</span></a>
                        </div>
                        <div class="nav-item {{ ($segment1 == 'table-datatable') ? 'active' : '' }}">
                            <a href="{{url('table-datatable')}}"><i class="ik ik-inbox"></i><span>{{ __('Data Table')}}</span></a>
                        </div>
                        <div class="nav-item {{ ($segment1 == 'navbar') ? 'active' : '' }}">
                            <a href="{{url('navbar')}}"><i class="ik ik-menu"></i><span>{{ __('Navigation')}}</span> </a>
                        </div>
                        <div class="nav-item {{ ($segment1 == 'widgets' || $segment1 == 'widget-statistic'||$segment1 == 'widget-data'||$segment1 == 'widget-chart') ? 'active open' : '' }} has-sub">
                            <a href="javascript:void(0)"><i class="ik ik-layers"></i><span>{{ __('Widgets')}}</span> <span class="badge badge-danger">{{ __('150+')}}</span></a>
                            <div class="submenu-content">
                                <a href="{{url('widgets')}}" class="menu-item {{ ($segment1 == 'widgets') ? 'active' : '' }}">{{ __('Basic')}}</a>
                                <a href="{{url('widget-statistic')}}" class="menu-item {{ ($segment1 == 'widget-statistic') ? 'active' : '' }}">{{ __('Statistic')}}</a>
                                <a href="{{url('widget-data')}}" class="menu-item {{ ($segment1 == 'widget-data') ? 'active' : '' }}">{{ __('Data')}}</a>
                                <a href="{{url('widget-chart')}}" class="menu-item {{ ($segment1 == 'widget-chart') ? 'active' : '' }}">{{ __('Chart Widget')}}</a>
                            </div>
                        </div>
                        <div class="nav-item {{ ($segment1 == 'alerts' || $segment1 == 'buttons'||$segment1 == 'badges'||$segment1 == 'navigation') ? 'active open' : '' }} has-sub">
                            <a href="#"><i class="ik ik-box"></i><span>{{ __('Basic')}}</span></a>
                            <div class="submenu-content">
                                <a href="{{url('alerts')}}" class="menu-item {{ ($segment1 == 'alerts') ? 'active' : '' }}">{{ __('Alerts')}}</a>
                                <a href="{{url('badges')}}" class="menu-item {{ ($segment1 == 'badges') ? 'active' : '' }}">{{ __('Badges')}}</a>
                                <a href="{{url('buttons')}}" class="menu-item {{ ($segment1 == 'buttons') ? 'active' : '' }}">{{ __('Buttons')}}</a>
                                <a href="{{url('navigation')}}" class="menu-item {{ ($segment1 == 'navigation') ? 'active' : '' }}">{{ __('Navigation')}}</a>
                            </div>
                        </div>
                        <div class="nav-item {{ ($segment1 == 'modals' || $segment1 == 'notifications'||$segment1 == 'carousel'||$segment1 == 'range-slider' ||$segment1 == 'rating') ? 'active open' : '' }} has-sub">
                            <a href="#"><i class="ik ik-gitlab"></i><span>{{ __('Advance')}}</span> </a>
                            <div class="submenu-content">
                                <a href="{{url('modals')}}" class="menu-item {{ ($segment1 == 'modals') ? 'active' : '' }}">{{ __('Modals')}}</a>
                                <a href="{{url('notifications')}}" class="menu-item {{ ($segment1 == 'notifications') ? 'active' : '' }}" >{{ __('Notifications')}}</a>
                                <a href="{{url('carousel')}}" class="menu-item {{ ($segment1 == 'carousel') ? 'active' : '' }}">{{ __('Slider')}}</a>
                                <a href="{{url('range-slider')}}" class="menu-item {{ ($segment1 == 'range-slider') ? 'active' : '' }}">{{ __('Range Slider')}}</a>
                                <a href="{{url('rating')}}" class="menu-item {{ ($segment1 == 'rating') ? 'active' : '' }}">{{ __('Rating')}}</a>
                            </div>
                        </div>
                        
                        
                        <div class="nav-item {{ ($segment1 == 'charts-chartist' || $segment1 == 'charts-flot'||$segment1 == 'charts-knob'||$segment1 == 'charts-amcharts') ? 'active open' : '' }} has-sub">
                            <a href="#"><i class="ik ik-pie-chart"></i><span>{{ __('Charts')}}</span> </a>
                            <div class="submenu-content">
                                <a href="{{url('charts-chartist')}}" class="menu-item {{ ($segment1 == 'charts-chartist') ? 'active' : '' }}">{{ __('Chartist')}}</a>
                                <a href="{{url('charts-flot')}}" class="menu-item {{ ($segment1 == 'charts-flot') ? 'active' : '' }}">{{ __('Flot')}}</a>
                                <a href="{{url('charts-knob')}}" class="menu-item {{ ($segment1 == 'charts-knob') ? 'active' : '' }}">{{ __('Knob')}}</a>
                                <a href="{{url('charts-amcharts')}}" class="menu-item {{ ($segment1 == 'charts-amcharts') ? 'active' : '' }}">{{ __('Amcharts')}}</a>
                            </div>
                        </div>
                        <div class="nav-item {{ ($segment1 == 'calendar') ? 'active' : '' }}">
                            <a href="{{url('calendar')}}"><i class="ik ik-calendar"></i><span>{{ __('Calendar')}}</span></a>
                        </div>
                        <div class="nav-item {{ ($segment1 == 'taskboard') ? 'active' : '' }}">
                            <a href="{{url('taskboard')}}"><i class="ik ik-server"></i><span>{{ __('Taskboard')}}</span></a>
                        </div>
        
                        <div class="nav-item {{ ($segment1 == 'login-1' || $segment1 == 'register'||$segment1 == 'forgot-password') ? 'active open' : '' }} has-sub">
                            <a href="#"><i class="ik ik-lock"></i><span>{{ __('Authentication')}}</span></a>
                            <div class="submenu-content">
                                <a href="{{url('login-1')}}" class="menu-item {{ ($segment1 == 'login-1') ? 'active' : '' }}">{{ __('Login')}}</a>
                                <a href="{{url('register')}}" class="menu-item {{ ($segment1 == 'register-1') ? 'active' : '' }}">{{ __('Register')}}</a>
                                <a href="{{url('forgot-password')}}" class="menu-item {{ ($segment1 == 'forgot-password') ? 'active' : '' }}">{{ __('Forgot Password')}}</a>
                            </div>
                        </div>
                        
                        <div class="nav-item {{ ($segment1 == 'profile' || $segment1 == 'invoice'||$segment1 == 'session-timeout') ? 'active open' : '' }} has-sub">
                            <a href="#"><i class="ik ik-file-text"></i><span>{{ __('Pages')}}</span></a>
                            <div class="submenu-content">
                                <a href="{{url('profile')}}" class="menu-item {{ ($segment1 == 'profile') ? 'active' : '' }}">{{ __('Profile')}}</a>
                                <a href="{{url('invoice')}}" class="menu-item {{ ($segment1 == 'invoice') ? 'active' : '' }}">{{ __('Invoice')}}</a>
                                <a href="{{url('project')}}" class="menu-item {{ ($segment1 == 'project') ? 'active' : '' }}">{{ __('Project')}}</a>
                                <a href="{{url('view')}}" class="menu-item {{ ($segment1 == 'view') ? 'active' : '' }}">{{ __('View')}}</a>
                                <a href="{{url('session-timeout')}}" class="menu-item {{ ($segment1 == 'session-timeout') ? 'active' : '' }}">{{ __('Session Timeout')}}</a>
                            </div>
                        </div>
                        <div class="nav-item {{ ($segment1 == 'layouts') ? 'active' : '' }}">
                            <a href="{{url('layouts')}}"><i class="ik ik-layout"></i><span>{{ __('Layouts')}}</span></a>
                        </div>
                        <div class="nav-item {{ ($segment1 == 'icons') ? 'active' : '' }}">
                            <a href="{{url('icons')}}"><i class="ik ik-command"></i><span>{{ __('Icons')}}</span></a>
                        </div>
                        <div class="nav-item {{ ($segment1 == 'pricing') ? 'active' : '' }}">
                            <a href="{{url('pricing')}}"><i class="ik ik-dollar-sign"></i><span>{{ __('Pricing')}}</span></a>
                        </div>
                        <div class="nav-item has-sub">
                            <a href="javascript:void(0)"><i class="ik ik-list"></i><span>{{ __('Menu Levels')}}</span></a>
                            <div class="submenu-content">
                                <a href="javascript:void(0)" class="menu-item">{{ __('Menu Level 2.1')}}</a>
                                <div class="nav-item {{ ($segment1 == '') ? 'active' : '' }} has-sub">
                                    <a href="javascript:void(0)" class="menu-item">{{ __('Menu Level 2.2')}}</a>
                                    <div class="submenu-content">
                                        <a href="javascript:void(0)" class="menu-item">{{ __('Menu Level 3.1')}}</a>
                                    </div>
                                </div>
                                <a href="javascript:void(0)" class="menu-item">{{ __('Menu Level 2.3')}}</a>
                            </div>
                        </div>
                        <div class="nav-item">
                            <a href="javascript:void(0)" class="disabled"><i class="ik ik-slash"></i><span>{{ __('Disabled Menu')}}</span></a>
                        </div>
                        
                </div>
            </div>
        </div>