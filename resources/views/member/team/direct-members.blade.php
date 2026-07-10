@extends('member.layouts.main')
@section('title', 'My Directs')
@section('container')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link href="https://cdn.datatables.net/2.1.5/css/dataTables.bootstrap5.css">
    <!-- MAIN-CONTENT -->

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
    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Start:: row-1 -->
            <div class="row">
                <div class="mb-3 mt-3">
                    <h5 class="fw-medium mb-0">My Directs</h5>
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
                                My Directs
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="basic-datatable" class="table dataTable table-bordered text-nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th>S.No</th>
                                             <!--<th>Image</th> -->
                                            <th>Joining Date</th>
                                            <th>Member ID</th>
                                    <!--         <th>Name</th>  -->
                                           <!--  <th>Email ID</th>  -->
                                            <th>Downline</th>
                                            <th>Investment</th>
                                            <th>Sponsor ID</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $i = 1;
                                        @endphp
                                        @foreach ($directData as $list)
                                            <tr>
                                                <td>{{ $i }}</td>
                                                 <!--<td>
                                                    @if ($list['profile_image'] == '')
                                                        <img src="{{ asset('uploads/avatar.jpg') }}"
                                                            class="img-fluid img-thumbnail"
                                                            style="border: 1px solid rgba(0, 255, 190, .3);background-color: rgba(0, 255, 190, .1);"
                                                            width="50" alt="">
                                                    @else
                                                        <img src="{{ asset('uploads') }}/{{ $list['profile_image'] }}"
                                                            width="50" class="img-fluid img-thumbnail"
                                                            style="border: 1px solid rgba(0, 255, 190, .3);background-color: rgba(0, 255, 190, .1);"
                                                            alt="">
                                                    @endif
                                                </td>  -->
                                                <td> {{ __(date('d-m-Y', strtotime($list['created_at']))) }}</td>
                                                <td>{{ __($list['memberid']) }}</td>
                                             <!--    <td>{{ __($list['name']) }}</td>  -->
                                             <!--    <td>{{ __($list['email']) }}</td> -->
                                                <td>{{ __($list['downline']) }}</td>
                                                <td>${{ __($list['package']) }}</td>
                                                <td>{{ __($list['sponsorid']) }}</td>
                                                <td>
                                                    @if ($list['status'] == 'Active')
                                                        <div class="btn btn-sm btn-success ">
                                                            {{ __($list['status']) }}
                                                        </div>
                                                    @elseif ($list['status'] == 'Temp')
                                                        <div class="btn btn-sm btn-primary ">
                                                            {{ __($list['status']) }}
                                                        </div>
                                                    @else
                                                        <div class="btn btn-sm btn-danger ">
                                                            {{ __($list['status']) }}
                                                        </div>
                                                    @endif
                                                </td>
                                            </tr>
                                            @php
                                                $i++;
                                            @endphp
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End:: row-1 -->

        </div>
    </div>
    <!-- End::app-content -->

    @push('user-scripts')
        <!-- Jquery Cdn -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

        <!-- Datatables Cdn -->
        <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdn.datatables.net/responsive/2.3.0/js/dataTables.responsive.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
        <script>
            new DataTable('#basic-datatable');
        </script>
        <!-- Internal Datatables JS -->
        <link rel="modulepreload" href="{{ asset('mem_assets/datatables-_3Z3Rdpk.js') }}" />
        <script type="module" src="{{ asset('mem_assets/datatables-_3Z3Rdpk.js') }}"></script>
    @endpush

@endsection
