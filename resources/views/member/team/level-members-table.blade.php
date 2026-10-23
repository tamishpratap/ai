@extends('member.layouts.main')
@section('title', 'Level Details')
@section('container')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link href="https://cdn.datatables.net/2.1.5/css/dataTables.bootstrap5.css">
    {{-- <style>
        thead .sorting:after,
        thead .sorting_asc:after,
        thead .sorting_desc:after {
            color: yellow;
            opacity: 0.6 !important;
        }
    </style> --}}

    <style>
        /* Extra small devices (phones, 600px and down) */
        @media only screen and (max-width: 600px) {
            div:has(.custom-card):first-child {
                margin-top: 20px;
            }
        }
    
        /* Small devices (portrait tablets and large phones, 600px and up) */
        @media only screen and (min-width: 600px) {
            div:has(.custom-card):first-child {
                margin-top: 20px;
            }
        }
    
        /* Medium devices (landscape tablets, 768px and up) */
        @media only screen and (min-width: 768px) {
            div:has(.custom-card):first-child {
                margin-top: 20px;
            }
        }
    
        /* Large devices (laptops/desktops, 992px and up) */
        @media only screen and (min-width: 992px) {
            div:has(.custom-card):first-child {
                margin-top: 0px;
            }
        }
    
        /* Extra large devices (large laptops and desktops, 1200px and up) */
        @media only screen and (min-width: 1200px) {
            div:has(.custom-card):first-child {
                margin-top: 0px;
            }
        }
    </style>
    <!-- MAIN-CONTENT -->

    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Start::row-1 -->
            <div class="row">
                <div class="mb-3 mt-3">
                    <h5 class="fw-medium mb-0">Level Table</h5>
                    <div class="text-muted fs-13 op-8 mb-3">
                        check your Geneology
                    </div>
                </div>
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="top-left"></div>
                        <div class="top-right"></div>
                        <div class="bottom-left"></div>
                        <div class="bottom-right"></div>
                        <div class="card-header">
                            <div class="card-title">
                                Level Details
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table  class="table  table-bordered  w-100 text-center">
                                    <thead >
                                        <tr>
                                            <th class="text-center">S.No</th>
                                            <th class="text-center">Level No</th>
                                            <th>Total Members</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @for ($level = 1; $level <= 20; $level++)
                                            @php
                                                $ends = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];
                                                $suffix = ((($level % 100) >= 11) && (($level % 100) <= 13)) ? 'th' : $ends[$level % 10];
                                                $levelTitle = $level . $suffix . ' Level';
                                            @endphp
                                            <tr>
                                                <td>{{ $level }}</td>
                                                <td>{{ __($levelTitle) }}</td>
                                                <td>
                                                    <div class="btn btn-pill bg-success ">
                                                        {{ totalsingleLevel($data['memberid'], (string)$level) }}</div>
                                                </td>
                                                <td><a class="btn btn-primary"
                                                        href="{{ url('member/levels/level-members-details') }}/{{ $level }}">View</a>
                                                </td>
                                            </tr>
                                        @endfor
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--End::row-1 -->

        </div>
    </div>
    <!-- End::app-content -->

    <!-- END MAIN-CONTENT -->

    @push('user-scripts')
        <!-- Jquery Cdn -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

        <!-- Datatables Cdn -->
        <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdn.datatables.net/responsive/2.3.0/js/dataTables.responsive.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
        <script>
            $('#basic-datatable').DataTable({
                searching: false,
            });
        </script>
        <!-- Internal Datatables JS -->
        <link rel="modulepreload" href="{{ asset('mem_assets/datatables-_3Z3Rdpk.js') }}" />
        <script type="module" src="{{ asset('mem_assets/datatables-_3Z3Rdpk.js') }}"></script>
    @endpush

@endsection
