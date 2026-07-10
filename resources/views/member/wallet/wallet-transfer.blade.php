@extends('member.layouts.main')
@section('title', 'Transactions')
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
                    <h5 class="fw-medium mb-0">Transactions History</h5>
                    <div class="text-muted fs-13 op-8 mb-3">
                        use Transactions History
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
                                Transaction History
                            </div>
                        </div>
                        <div class="card-body ">
                            <div class="table-responsive">
                                <table id="basic-datatable" class="table text-nowrap">
                                    <thead>
                                        <tr>
                                            <th>S.No</th>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Amount Add</th>
                                            <th>Amount Deduct</th>
                                            <th>Balance</th>
                                            <th>Particular</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $i = 1;
                                        @endphp
                                        @foreach ($transdata as $list)
                                            <tr>
                                                <td>{{ $i }}</td>
                                                <td>{{ date('d-m-Y', strtotime($list['created_at'])) }}</td>
                                                <td>{{ $list['walletType'] }}</td>
                                                <td>$ {{ $list['debit'] }}</td>
                                                <td>$ {{ $list['credit'] }}</td>
                                                <td>$ {{ $list['balance'] }}</td>
                                                <td>{{ $list['particular'] }}</td>
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
