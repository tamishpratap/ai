<div class="app-sidebar colored">
    <div class="sidebar-header">
        <a class="header-brand" href="{{ url('admin/dashboard') }}">
            <div class="logo-img text-center pl-4">
                <img style="height: 48px; width: auto; max-width: 140px; object-fit: contain;" class="header-brand-img ml-5" src="{{ asset('logo/logo.png') }}" alt="Demo">
                {{-- <span class="pt-5" style="font-size: 30px;margin: 10px;">RAISEON</span> --}}
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
                <div class="nav-item {{ $segment1 == 'dashboard' ? 'active' : '' }} mt-4">
                    <a href="{{ url('admin/dashboard') }}"><i class="ik ik-bar-chart-2"></i><span>Dashboard</span></a>
                </div>

                <div class="nav-item">
                    <a href="javascript:void(0);" class="ira-open-assistant-trigger" data-toggle="modal" data-target="#iraModal"><i class="ik ik-mic"></i><span>Navigation AI</span></a>
                </div>

                <div class="nav-item {{ request()->is('admin/ai/chat*') ? 'active' : '' }}">
                    <a href="{{ url('admin/ai/chat') }}"><i class="ik ik-cpu"></i><span>AI Assistant</span></a>
                </div>

                <div class="nav-item {{ ($segment2 == 'popup-image' || $segment2 == 'achievers-images') ? 'active' : '' }}">
                    <a href="{{url('admin/popup-image')}}"><i class="ik ik-image"></i><span>Popup / Dashboard Image</span> </a>
                </div>
                <div class="nav-item {{ $segment2 == 'dash-messages' ? 'active' : '' }}">
                    <a href="{{ url('admin/dash-messages') }}"><i class="ik ik-message-square"></i><span>Dashboard Messages</span> </a>
                </div>
                <!--<div class="nav-item {{ $segment1 == 'rest-api' ? 'active' : '' }}">-->
                <!--    <a href="{{ url('admin/rate/set-rates') }}"><i class="ik ik-bar-chart"></i><span>Set Rates</span>-->
                <!--    </a>-->
                <!--</div>-->
                {{-- <div class="nav-item {{ $segment2 == 'mlm-settings' ? 'active' : '' }}">
                    <a href="{{ url('admin/mlm-settings') }}"><i class="ik ik-settings"></i><span>MLM Settings</span>
                    </a>
                </div> --}}
                <!--<div class="nav-item {{ $segment2 == 'packages' ? 'active' : '' }}">-->
                <!--    <a href="{{ url('admin/packages') }}"><i class="ik ik-box"></i><span>Packages</span>-->
                <!--    </a>-->
                <!--</div>-->
                <!--<div class="nav-item {{ $segment1 == 'rest-api' ? 'active' : '' }}">-->
                <!--    <a href="{{ url('admin/deposit-details') }}"><i class="ik ik-box"></i><span>Deposit Details</span>-->
                <!--    </a>-->
                <!--</div>-->
                <!--<div class="nav-item {{ $segment1 == 'rest-api' ? 'active' : '' }}">-->
                <!--    <a href="{{ url('admin/member-gallery') }}"><i class="ik ik-bar-chart"></i><span>Member-->
                <!--            Gallery</span>-->
                <!--    </a>-->
                <!--</div>-->
                
                <hr>
                <div
                    class="nav-item {{ $segment1 == 'alerts' || $segment1 == 'buttons' || $segment1 == 'badges' || $segment1 == 'navigation' ? 'active open' : '' }} has-sub">
                    <a href="#"><i class="ik ik-users"></i><span>{{ __('Member Management') }}</span></a>
                    <div class="submenu-content">
                        <a href="{{ url('admin/member-details') }}"
                            class="menu-item {{ $segment1 == 'alerts' ? 'active' : '' }}">{{ __('Members Details') }}</a>
                        <a href="{{ url('admin/account-control') }}"
                            class="menu-item {{ $segment1 == 'alerts' ? 'active' : '' }}">{{ __('Account Control') }}</a>
                        <a href="{{ url('admin/member-security') }}"
                            class="menu-item {{ $segment1 == 'badges' ? 'active' : '' }}">{{ __('Members Security') }}</a> 
                        <a href="{{ url('admin/free-pin-account') }}"
                            class="menu-item {{ $segment1 == 'badges' ? 'active' : '' }}">{{ __('Zero Pin Members') }}</a>
                        <a href="{{ url('admin/wallet-address') }}"
                            class="menu-item {{ $segment1 == 'badges' ? 'active' : '' }}">{{ __('Member Wallet Address') }}</a>
                        <!--<a href="{{url('admin/package-details')}}" class="menu-item {{ ($segment1 == 'badges') ? 'active' : '' }}">{{ __('Package Details')}}</a>-->
                    </div>
                </div>
                <div
                    class="nav-item {{ $segment1 == 'alerts' || $segment1 == 'buttons' || $segment1 == 'badges' || $segment1 == 'navigation' ? 'active open' : '' }} has-sub">
                    <a href="#"><i class="ik ik-database"></i><span>{{ __('Funds Management') }}</span></a>
                    <div class="submenu-content">
                        <a href="{{ url('admin/funds/add-funds') }}"
                            class="menu-item {{ $segment1 == 'alerts' ? 'active' : '' }}">{{ __('Add Funds') }}</a>
                        <a href="{{ url('admin/funds/deduct-funds') }}"
                            class="menu-item {{ $segment1 == 'alerts' ? 'active' : '' }}">{{ __('Deduct Funds') }}</a>
                        <a href="{{ url('admin/funds/add-funds-details') }}"
                            class="menu-item {{ $segment1 == 'badges' ? 'active' : '' }}">{{ __('Funds Details') }}</a>
                        <!--<a href="{{ url('admin/fund-requests') }}"-->
                        <!--    class="menu-item {{ $segment1 == 'badges' ? 'active' : '' }}">{{ __('Funds Requests') }}</a>-->
                        <a href="{{ url('admin/request-history') }}"
                            class="menu-item {{ $segment1 == 'badges' ? 'active' : '' }}">{{ __('Import Fund History') }}</a>
                    </div>
                </div>

                 <div
                    class="nav-item {{ $segment1 == 'alerts' || $segment1 == 'buttons' || $segment1 == 'badges' || $segment1 == 'navigation' ? 'active open' : '' }} has-sub">
                    <a href="#"><i class="ik ik-tag"></i><span>{{ __('Staking Section') }}</span></a>
                    <div class="submenu-content">
                        <a href="{{ url('admin/incomes/staking-incomes') }}"
                            class="menu-item {{ $segment1 == 'alerts' ? 'active' : '' }}">{{ __('Staking Income') }}</a>  
                        <a href="{{ url('admin/incomes/staking-details') }}"
                            class="menu-item {{ $segment1 == 'alerts' ? 'active' : '' }}">{{ __('Staking Details') }}</a>
                    </div>
                </div> 

                <div
                    class="nav-item {{ $segment1 == 'alerts' || $segment1 == 'buttons' || $segment1 == 'badges' || $segment1 == 'navigation' ? 'active open' : '' }} has-sub">
                    <a href="#"><i class="ik ik-database"></i><span>{{ __('Income Section') }}</span></a>
                    <div class="submenu-content">
                        <!--<a href="{{ url('admin/incomes/level-details') }}"-->
                        <!--    class="menu-item {{ $segment1 == 'badges' ? 'active' : '' }}">{{ __('Level Details') }}</a>  -->
                        <a href="{{ url('admin/incomes/level-incomes') }}"
                            class="menu-item {{ $segment1 == 'alerts' ? 'active' : '' }}">{{ __('Level Incomes') }}</a>
                        <a href="{{ url('admin/incomes/reward-incomes') }}"
                            class="menu-item {{ $segment1 == 'alerts' ? 'active' : '' }}">{{ __('Reward Income') }}</a>
                        <a href="{{ url('admin/incomes/direct-incomes') }}"
                            class="menu-item {{ $segment1 == 'alerts' ? 'active' : '' }}">{{ __('Direct Income') }}</a>
                        <!--<a href="{{ url('admin/incomes/monthly-roi-incomes') }}"-->
                        <!--    class="menu-item {{ $segment1 == 'alerts' ? 'active' : '' }}">{{ __('Monthly ROI Income') }}</a>-->
                    </div>
                </div>
                <div
                    class="nav-item {{ $segment1 == 'alerts' || $segment1 == 'buttons' || $segment1 == 'badges' || $segment1 == 'navigation' ? 'active open' : '' }} has-sub">
                    <a href="#"><i class="ik ik-credit-card"></i><span>{{ __('Payment Management') }}</span></a>
                    <div class="submenu-content">
                        <a href="{{ url('admin/new-withdrawal-request') }}"
                            class="menu-item {{ $segment1 == 'alerts' ? 'active' : '' }}">{{ __('New Withdrawal Requests') }}</a>
                        {{-- <a href="{{url('admin/gateway-request')}}" class="menu-item {{ ($segment1 == 'badges') ? 'active' : '' }}">{{ __('Gateway Requests')}}</a> --}}
                        <a href="{{ url('admin/cancelled-request') }}"
                            class="menu-item {{ $segment1 == 'badges' ? 'active' : '' }}">{{ __('Cancelled Requests') }}</a>
                        <a href="{{ url('admin/payment-history') }}"
                            class="menu-item {{ $segment1 == 'alerts' ? 'active' : '' }}">{{ __('Payment History') }}</a>
                        {{-- <a href="{{url('admin/p2p-fund-transfer')}}" class="menu-item {{ ($segment1 == 'badges') ? 'active' : '' }}">{{ __('P2P Fund Transfer')}}</a> --}}

                    </div>
                </div>
                <hr>
                <div
                    class="nav-item {{ $segment1 == 'alerts' || $segment1 == 'buttons' || $segment1 == 'badges' || $segment1 == 'navigation' ? 'active open' : '' }} has-sub">
                    <a href="#"><i class="ik ik-mail"></i><span>{{ __('Support') }}</span></a>
                    <div class="submenu-content">
                        <a href="{{ url('admin/support/new-support-ticket') }}"
                            class="menu-item {{ $segment1 == 'alerts' ? 'active' : '' }}">{{ __('New Support Tickets') }}</a>
                        <a href="{{ url('admin/support/open-support-ticket') }}"
                            class="menu-item {{ $segment1 == 'alerts' ? 'active' : '' }}">{{ __('Open Support Tickets') }}</a>
                        <a href="{{ url('admin/support/close-support-ticket') }}"
                            class="menu-item {{ $segment1 == 'badges' ? 'active' : '' }}">{{ __('Closed Support Tickets') }}</a>
                    </div>
                </div>
                <div class="nav-item {{ $segment1 == 'rest-api' ? 'active' : '' }}">
                    <a href="{{ url('admin/logout') }}"><i class="ik ik-stop-circle"></i><span>Logout</span> </a>
                </div>
                <!-- end inventory pages -->
            </nav>
        </div>
    </div>
</div>
