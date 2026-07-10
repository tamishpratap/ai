@extends('member.layouts.main')
@section('title', 'Level Members Detail')
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
    <!-- MAIN-CONTENT -->

    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Start:: row-1 -->
            <div class="row">
                <div class="mb-3 mt-3">
                    <h5 class="fw-medium mb-0">Level Members Detail</h5>
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
                                Level Members Details
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="basic-datatable" class="table dataTable table-bordered text-nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th>S.No</th>
                                            <th>Image</th>
                                            <th>Joining Date</th>
                                            <th>Member ID</th>
                                            <th>Name</th>
                                            <th>Email ID</th>
                                            <th>Downline</th>  
                                            <th>Package</th> 
                                            <th>Sponsor ID</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $i = 1;
                                        @endphp
                                        @foreach ($users as $list)
                                            <tr>
                                                <td>{{ $i }}</td>
                                                <td>
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
                                                </td>
                                                <td> {{ __(date('d-m-Y', strtotime($list['created_at']))) }}</td>
                                                <td>{{ __($list['memberid']) }}</td>
                                                <td>{{ __($list['name']) }}</td>
                                                <td>{{ __($list['email']) }}</td>
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
                {{-- <div class="col-xl-12">
                    <div class="d-flex justify-content-between mb-3">
                        <div class="h5 fw-medium">Level Members Details</div>
                    </div>
                    <div class="row">
                      
                      @foreach ($users as $list)
                            <div class="col-xxl-3 col-lg-6">
                                <div class="card custom-card border rounded">
                                    <div class="top-left"></div>
                                    <div class="top-right"></div>
                                    <div class="bottom-left"></div>
                                    <div class="bottom-right"></div>
                                    <div class="card-body p-4 text-center">
                                        <span class="avatar avatar-xl team-avatar p-1 mb-3">
                                            @if ($list['profile_image'] == '')
                                                <img src="{{ asset('uploads/avatar.jpg') }}" class="img-fluid"
                                                    width="50" alt="">
                                            @else
                                                <img src="{{ asset('uploads') }}/{{ $list['profile_image'] }}"
                                                    width="50" class="img-fluid" alt="">
                                            @endif
                                        </span>
                                        <div class="team-member-details">
                                            <span class="mb-0 fw-medium fs-16 text-truncate d-block">
                                                {{ $list['memberid'] }}
                                            </span>
                                            <span class="mb-0 fw-medium fs-20 text-truncate d-block">
                                                {{ getName($list['memberid']) }}
                                            </span>
                                            <span class="mb-0 fs-13 text-muted d-block">{{ $list['email'] }}</span>
                                            <hr>
                                            <span class="mb-0 fw-medium fs-16 text-primary d-block">
                                               Downline : {{ $list['downline'] }}
                                            </span>
                                            <span class="mb-0 fw-medium fs-16 text-primary d-block">
                                               Investment : $ {{ __(totalInvestment($list['memberid'])) }}
                                            </span>
                                            <span class="mb-0 fw-medium fs-16 text-primary d-block">
                                               Last Month Staking Income : $ {{ getStakingIncomeLastMonth($list['memberid'])}}
                                            </span>
                                           
                                           
                                        </div>
                                    </div>
                                    <div class="card-footer border-block-start-dashed border-top text-center p-0">
                                        <div class="d-flex">
                                            <div class="text-center p-3 flex-fill">
                                                <button class="btn mt-2 btn-warning-light btn-wave waves-effect waves-light">
                                                    Status : {{ $list['status'] }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div> --}}
            </div>
            <!-- End:: row-1 -->

        </div>
    </div>

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
