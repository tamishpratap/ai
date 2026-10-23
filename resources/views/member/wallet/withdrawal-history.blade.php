@extends('member.layouts.main')
@section('title', 'Withdrawal History')
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

            <!-- Start::row-1 -->
            <div class="row">
                <div class="mb-3 mt-3">
                    <h5 class="fw-medium mb-0">Withdrawal History</h5>
                    <div class="text-muted fs-13 op-8 mb-3">
                        use Withdrawal
                    </div>
                </div>
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="top-left"></div>
                        <div class="top-right"></div>
                        <div class="bottom-left"></div>
                        <div class="bottom-right"></div>
                        <div class="card-header justify-content-between pb-3">
                            <div class="card-title">
                                Withdrawal History
                            </div>
                        </div>
                        <div class="card-body ">
                            <div class="table-responsive">
                                <table id="basic-datatable" class="table text-nowrap">
                                    <thead>
                                        <tr>
                                            <th>S.No.</th>
                                            <th scope="col">Request Date</th>
                                            <th scope="col">Request Id</th>
                                            <th scope="col">Wallet Address</th>
                                            <!--<th scope="col">Comment</th>-->
                                            <!--<th scope="col">Type</th>-->
                                            <th scope="col">Gross Amount</th>
                                            <th scope="col">Service</th>
                                            <th scope="col">Net Amount</th>
                                            <th scope="col">Payment Date</th>
                                            <th scope="col">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php 
                                            $i = 1;  
                                        @endphp
                                        @foreach ($wdata as $op_data)
                                            <tr>
                                                <td>
                                                    {{ $i }}
                                                </td>
                                                <td>
                                                    <span>{{ date('d,M Y', strtotime($op_data['created_at'])) }}</span>
                                                </td>
                                                <td>{{ $op_data['request_id'] }}</td>
                                                <td>{{ $op_data['wallet_address'] }}</td>
                                                <!--<td>{{ $op_data['admin_comment'] ?? '--' }}</td>-->
                                                 <!--<td> {{ $op_data['type'] }} </td>-->
                                                <td>$ {{ number_format($op_data['gross_amount'],2) }} </td>
                                               <td>$ {{ number_format($op_data['service_charge'],2) }} </td>
                                                <td>$ {{ number_format($op_data['net_amount'],2) }} </td>
                                                <td class="text-center">
                                                    @if ($op_data['payment_date'] == null)
                                                        <span class="text-center">--</span>
                                                    @else
                                                        {{ date('d-m-Y', strtotime($op_data['payment_date'])) }}
                                                        <br>{{ date('H:i:s', strtotime($op_data['payment_date'])) }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($op_data['status'] == 'Pending')
                                                        <span class="btn btn-primary p-2">{{ $op_data['status'] }}</span>
                                                    @elseif($op_data['status'] == 'Approved')
                                                        <span class="btn btn-success p-2">{{ $op_data['status'] }}</span>
                                                    @else
                                                        <span class="btn btn-danger p-2">{{ $op_data['status'] }}</span>
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
            new DataTable('#basic-datatable');
        </script>
        <!-- Internal Datatables JS -->
        <link rel="modulepreload" href="{{ asset('mem_assets/datatables-_3Z3Rdpk.js') }}" />
        <script type="module" src="{{ asset('mem_assets/datatables-_3Z3Rdpk.js') }}"></script>
    @endpush

@endsection
