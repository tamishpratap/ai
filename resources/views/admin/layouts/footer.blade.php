<!-- initiate footer section-->
<footer class="footer">
    <div class="w-100 clearfix">
        <span class="text-center text-sm-left d-md-inline-block">
            v3.1.0 {{ __('Copyright © '.date("Y"))}} <a href="https://arthemic.com">Arthemic</a> 
            <i class="fa fa-heart text-danger"></i> 
        </span>
        <span class="float-none float-sm-right mt-1 mt-sm-0 text-center">
            {{ __('Developed by')}} 
            <a href="https://rakibul.dev" class="text-dark" target="_blank">
                {{ __('Md. Rakibul Islam')}}
            </a>
        </span>
    </div>
</footer>

</div>
</div>

<!-- initiate modal menu section-->
<div class="modal fade apps-modal" id="appsModal" tabindex="-1" role="dialog" aria-labelledby="appsModalLabel" aria-hidden="true" data-backdrop="false">
<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="ik ik-x-circle"></i></button>
<div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
        <div class="quick-search">
            <div class="container">
                <div class="row">
                    <div class="col-md-4 ml-auto mr-auto">
                        <div class="input-wrap">
                            <input type="text" id="quick-search" class="form-control" placeholder="Search..." />
                            <i class="ik ik-search"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-body d-flex align-items-center">
            <div class="container">
                <div class="apps-wrap">
                    <div class="app-item">
                        <a href="#"><i class="ik ik-bar-chart-2"></i><span>{{ __('Dashboard')}}</span></a>
                    </div>
                    <div class="app-item dropdown">
                        <a href="#" class="dropdown-toggle" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="ik ik-command"></i><span>{{ __('Ui')}}</span></a>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                            <a class="dropdown-item" href="#">{{ __('Action')}}</a>
                            <a class="dropdown-item" href="#">{{ __('Another action')}}</a>
                            <a class="dropdown-item" href="#">{{ __('Something else here')}}</a>
                        </div>
                    </div>
                    <div class="app-item">
                        <a href="#"><i class="ik ik-mail"></i><span>{{ __('Message')}}</span></a>
                    </div>
                    <div class="app-item">
                        <a href="#"><i class="ik ik-users"></i><span>{{ __('Accounts')}}</span></a>
                    </div>
                    <div class="app-item">
                        <a href="#"><i class="ik ik-shopping-cart"></i><span>{{ __('Sales')}}</span></a>
                    </div>
                    <div class="app-item">
                        <a href="#"><i class="ik ik-briefcase"></i><span>{{ __('Purchase')}}</span></a>
                    </div>
                    <div class="app-item">
                        <a href="#"><i class="ik ik-server"></i><span>{{ __('Menus')}}</span></a>
                    </div>
                    <div class="app-item">
                        <a href="#"><i class="ik ik-clipboard"></i><span>{{ __('Pages')}}</span></a>
                    </div>
                    <div class="app-item">
                        <a href="#"><i class="ik ik-message-square"></i><span>{{ __('Chats')}}</span></a>
                    </div>
                    <div class="app-item">
                        <a href="#"><i class="ik ik-map-pin"></i><span>{{ __('Contacts')}}</span></a>
                    </div>
                    <div class="app-item">
                        <a href="#"><i class="ik ik-box"></i><span>{{ __('Blocks')}}</span></a>
                    </div>
                    <div class="app-item">
                        <a href="#"><i class="ik ik-calendar"></i><span>{{ __('Events')}}</span></a>
                    </div>
                    <div class="app-item">
                        <a href="#"><i class="ik ik-bell"></i><span>{{ __('Notifications')}}</span></a>
                    </div>
                    <div class="app-item">
                        <a href="#"><i class="ik ik-pie-chart"></i><span>{{ __('Reports')}}</span></a>
                    </div>
                    <div class="app-item">
                        <a href="#"><i class="ik ik-layers"></i><span>{{ __('Tasks')}}</span></a>
                    </div>
                    <div class="app-item">
                        <a href="#"><i class="ik ik-edit"></i><span>{{ __('Blogs')}}</span></a>
                    </div>
                    <div class="app-item">
                        <a href="#"><i class="ik ik-settings"></i><span>{{ __('Settings')}}</span></a>
                    </div>
                    <div class="app-item">
                        <a href="#"><i class="ik ik-more-horizontal"></i><span>{{ __('More')}}</span></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<!-- initiate scripts-->
<script src="{{ asset('all.js') }}"></script>
<script src="{{ asset('adm_assets/assets/dist/js/theme.js') }}"></script>
<script src="{{ asset('adm_assets/assets/js/chat.js') }}"></script>	
<script src="{{ asset('adm_assets/assets/plugins/owl.carousel/dist/owl.carousel.min.js') }}"></script>
<script src="{{ asset('adm_assets/assets/plugins/chartist/dist/chartist.min.js') }}"></script>
<script src="{{ asset('adm_assets/assets/plugins/flot-charts/jquery.flot.js') }}"></script>
<!-- <script src="{{ asset('adm_assets/assets/plugins/flot-charts/jquery.flot.categories.js') }}"></script> -->
<script src="{{ asset('adm_assets/assets/plugins/flot-charts/curvedLines.js') }}"></script>
<script src="{{ asset('adm_assets/assets/plugins/flot-charts/jquery.flot.tooltip.min.js') }}"></script>

<script src="{{ asset('adm_assets/assets/plugins/amcharts/amcharts.js') }}"></script>
<script src="{{ asset('adm_assets/assets/plugins/amcharts/serial.js') }}"></script>
<script src="{{ asset('adm_assets/assets/plugins/amcharts/themes/light.js') }}"></script>


<script src="{{ asset('adm_assets/assets/js/widget-statistic.js') }}"></script>
<script src="{{ asset('adm_assets/assets/js/widget-data.js') }}"></script>
<script src="{{ asset('adm_assets/assets/js/dashboard-charts.js') }}"></script>

<script src="{{ asset('adm_assets/assets/plugins/DataTables/datatables.min.js') }}"></script>
<script src="{{ asset('adm_assets/assets/js/datatables.js') }}"></script>

<script src="{{ asset('adm_assets/assets/plugins/select2/dist/js/select2.min.js') }}"></script>
<script src="{{ asset('adm_assets/assets/plugins/summernote/dist/summernote-bs4.min.js') }}"></script>
<script src="{{ asset('adm_assets/assets/plugins/bootstrap-tagsinput/dist/bootstrap-tagsinput.min.js') }}"></script>
<script src="{{ asset('adm_assets/assets/plugins/jquery.repeater/jquery.repeater.min.js') }}"></script>
<script src="{{ asset('adm_assets/assets/plugins/mohithg-switchery/dist/switchery.min.js') }}"></script>
<script src="{{ asset('adm_assets/assets/js/form-advanced.js') }}"></script>
</body>
</html>