   <!-- SEARCH-MODAL -->

   <div class="modal fade" id="header-responsive-search" tabindex="-1" aria-labelledby="header-responsive-search"
       aria-hidden="true">
       <div class="modal-dialog">
           <div class="modal-content">
               <div class="modal-body">
                   <div class="input-group">
                       <input type="text" class="form-control border-end-0" placeholder="Search Anything ..."
                           aria-label="Search Anything ..." aria-describedby="button-addon2">
                       <button class="btn btn-primary" type="button" id="button-addon2"><i
                               class="bi bi-search"></i></button>
                   </div>
               </div>
           </div>
       </div>
   </div>
   <!-- END SEARCH-MODAL -->
<!-- Bottom Mobile Menu -->
<!--<div class="mobile-bottom-menu">-->
<!--  <a href="{{ url('/member/dashboard') }}" class="menu-item">-->
<!--    <i class="bx bx-shield-plus"></i>-->
<!--    <span>Home</span>-->
<!--  </a>-->

<!--  <a href="{{ url('member/income/daily-profit-income') }}" class="menu-item">-->
<!--    <i class="bx bx-wallet"></i>-->
<!--    <span>Earn</span>-->
<!--  </a>-->

<!-- Claim ROI Button (replaces Trade) -->
<!--<div class="menu-item claim-btn">-->
<!--  <form  action="{{ route('claimROI') }}" method="POST">-->
<!--    @csrf-->
<!--    <input type="hidden" name="memberid" value="{{ $data['memberid'] }}">-->
<!--    <button type="submit" class="claim-roi-btn">-->
<!--      <i class="bx bx-transfer"></i>-->
<!--      <span>Trade</span>-->
<!--    </button>-->
<!--  </form>-->
<!--</div>-->


<!--  <a href="{{ url('member/trading/arbitrage-trading') }}" class="menu-item">-->
<!--    <i class="bx bx-line-chart"></i>-->
<!--    <span>Markets</span>-->
<!--  </a>-->

<!--  <a href="{{ url('member/wallet') }}" class="menu-item">-->
<!--    <i class="bx bx-lock-alt"></i>-->
<!--    <span>Assets</span>-->
<!--  </a>-->
<!--</div>-->

   <!-- FOOTER -->

   <footer class="footer mt-auto py-3 text-center">
       <div class="container">
           <span class="text-muted"> Copyright ©
               <script>
                   document.write(new Date().getFullYear())
               </script>
               {{ config('detailsApp.name') }}
               All
               rights
               reserved <br><a href="https://www.mlmsoftwaredeveloper.live/" target="_blank"
                   class="developer-link">Developed By The Crypto Developers</a>
           </span>
       </div>
   </footer>
   <!-- END FOOTER -->

   </div>
   <!-- END PAGE-->

   <!-- SCRIPTS -->

   <!-- SCROLL-TO-TOP -->
   <div class="scrollToTop">
       <span class="arrow"><i class="ti ti-arrow-narrow-up fs-20"></i></span>
   </div>
   <div id="responsive-overlay"></div>

   <!-- END SCRIPTS -->


   <!-- POPPER JS -->
   <script src="{{ asset('mem_assets/libs/%40popperjs/core/umd/popper.min.js') }}"></script>

   <!-- BOOTSTRAP JS -->
   <script src="{{ asset('mem_assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

   <!-- NODE WAVES JS -->
   <script src="{{ asset('mem_assets/libs/node-waves/waves.min.js') }}"></script>

   <!-- SIMPLEBAR JS -->
   <script src="{{ asset('mem_assets/libs/simplebar/simplebar.min.js') }}"></script>
   <link rel="modulepreload" href="{{ asset('mem_assets/simplebar-B35Aj-bA.js') }}" />
   <script type="module" src="{{ asset('mem_assets/simplebar-B35Aj-bA.js') }}"></script>

   <!-- COLOR PICKER JS -->
   <script src="{{ asset('mem_assets/libs/%40simonwep/pickr/pickr.es5.min.js') }}"></script>

   <!-- Apex Charts JS -->
   {{-- <script src="{{ asset('mem_assets/libs/apexcharts/apexcharts.min.js') }}"></script> --}}

   <!-- JSVector Maps JS -->
   <script src="{{ asset('mem_assets/libs/jsvectormap/js/jsvectormap.min.js') }}"></script>

   <!-- JSVector Maps MapsJS -->
   <script src="{{ asset('mem_assets/libs/jsvectormap/maps/world-merc.js') }}"></script>


   <!-- Crypto Dashboard -->
   <link rel="modulepreload" href="{{ asset('mem_assets/crypto-dashboard-4LcfNEiY.js') }}" />
   <script type="module" src="{{ asset('mem_assets/crypto-dashboard-4LcfNEiY.js') }}"></script>

   <!-- CRM Dashboard -->
   <link rel="modulepreload" href="{{ asset('mem_assets/crm-dashboard-DM07rKog.js') }}" />
   <script type="module" src="{{ asset('mem_assets/crm-dashboard-DM07rKog.js') }}"></script>

   <!-- Courses Dashboard -->
   <link rel="modulepreload" href="{{ asset('mem_assets/courses-dashboard-D_t_faxY.js') }}" />
   <script type="module" src="{{ asset('mem_assets/courses-dashboard-D_t_faxY.js') }}"></script>

   @stack('user-scripts')


   <!-- STICKY JS -->
   <script src="{{ asset('mem_assets/sticky.js') }}"></script>

   <!-- APP JS -->
   <link rel="modulepreload" href="{{ asset('mem_assets/app-tP9rsWKQ.js') }}" />
   <script type="module" src="{{ asset('mem_assets/app-tP9rsWKQ.js') }}"></script>

   <!-- CUSTOM-SWITCHER JS -->
   <link rel="modulepreload" href="{{ asset('mem_assets/custom-switcher-Dok1OPLs.js') }}" />
   <script type="module" src="{{ asset('mem_assets/custom-switcher-Dok1OPLs.js') }}"></script>

   <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
   <script>
       $(document).ready(function() {
           var status = $('#status').val();
           if (status == 'Temp') {
               $('#activation').modal('show');
           } else {
               $('#activation').modal('hide');
           }

           $('#act_btn').on('click', function() {
               $(this).html('Processing .......')
           });

           $('#usdt_amount').on('input', function() {
               var amount = parseInt($('#usdt_amount').val());
               if (amount) {
                   $('#fund').html('$ ' + amount.toFixed(2));
               } else {
                   $('#fund').html('$ 0');
               }

           });
           //referal link
           $('#copyButtonId').on('click', function() {
               var text = $('#code').text()
               var $temp = $("<input>");
               $("body").append($temp);
               $temp.val(text).select();
               document.execCommand("copy");
               $temp.remove();
               $('#msgText').html('Text Copied')
           });

           $('#copyButtonIdLeft').on('click', function() {
               var text = $('#codeLeft').text()
               var $temp = $("<input>");
               $("body").append($temp);
               $temp.val(text).select();
               document.execCommand("copy");
               $temp.remove();
               $('#msgTextLeft').html('Text Copied')
           });

           //referal link
           $('#copyButtonIdRight').on('click', function() {
               var text = $('#codeRight').text()
               var $temp = $("<input>");
               $("body").append($temp);
               $temp.val(text).select();
               document.execCommand("copy");
               $temp.remove();
               $('#msgTextRight').html('Text Copied')
           });
       })
   </script>
   <script>
       $(document).ready(function() {

           //user id setting
           $('#userid').on('input', function() {
               var userid = $(this).val();
               if (userid.length == 0) {
                   $('#messageU').html('');
               }
               $.ajax({
                   url: '/getUserid',
                   type: 'POST',
                   data: {
                       'userid': userid,
                       _token: '{{ csrf_token() }}',
                   },
                   success: function(response) {

                       $('#messageU').html(response['data']);
                       $('#packAmount').val(response['package']);
                       $('#package').val(response['plan']);

                       if (response['code'] == 0) {
                           $('#actBtn').attr("disabled", true);
                       } else {
                           $('#actBtn').attr('disabled', false);
                       }
                   }
               })
           });

           $('#myForm').on('submit', function() {
               $('#subBtn').html('Processing....');
               $('#subBtn').prop('disabled', true);
           });

           $('#amount').on('input', function() {
               var amount = $(this).val();
               //alert(package);
               $("#amountMsg").html('');

               if (amount.length > 0) {
                   if (amount < 50) {
                       $("#amountMsg").html(
                           '<span style="color:#ffa647">*** Min investment amount is $50</span>');
                   } else {
                       if (amount % 10 != 0) {
                           $("#amountMsg").html(
                               '<span style="color:#ffa647">*Amount must be multiple of $10</span>');
                       } else {
                           $("#amountMsg").empty('');
                       }
                   }
               } else {
                   $("#amountMsg").html('');
               }
           });

       });
   </script>
   </body>
   </html>
