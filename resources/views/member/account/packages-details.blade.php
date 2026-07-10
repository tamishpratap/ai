@extends('member.layouts.main')
@section('title', 'Package Details')
@section('container')
 <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link href="https://cdn.datatables.net/2.1.5/css/dataTables.bootstrap5.css">

    <!--**********************************
                Content body start
            ***********************************-->
     <div class="main-content app-content">
        <div class="container-fluid">

            <!-- row -->
           <div class="row justify-content-center">
               <div class="mb-4">
                    <h5 class="fw-medium mb-0">Package Details</h5>
                    <div class="text-muted fs-13 op-8 mb-3">
                        create your Package
                    </div>
                </div>
               
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                    @if (session()->has('successMsg'))
                        <div class="alert alert-success" role="alert">
                            {{ session('successMsg') }}
                        </div>
                    @endif
                    @if (session()->has('failedMsg'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('failedMsg') }}
                        </div>
                    @endif
                    <div class="card custom-card">
                        <div class="card-header">
                            <h4 class="card-title">Package Details </h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive" style="overflow: auto">
                                <table id="basic-datatable" class="display table">
                                    <thead>
                                        <tr>
                                            <th>S.No.</th>
                                            <th>Invest Date</th>
                                            <th>Memberid</th>
                                            <th>Package</th>
                                            <th>Invest Amount</th>
                                            <th>Rate</th>
                                            <th>Installments</th>
                                            <th>Status</th>
                                            
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $i = 1;
                                        @endphp
                                        @foreach ($sdata as $list)
                                            <tr>
                                                <td>{{ $i }}</td>
                                                <td> {{ date('d-m-Y', strtotime($list['invest_date'])) }}</td>
                                                <td>{{ $list['memberid'] }}</td>
                                                <td class="text-center">{{ $list['package'] }}</td>
                                                <td class="text-center">$ {{ $list['invest_amount'] }}</td>
                                                <td>{{ $list['rate'] }}%</td>
                                                {{-- <td>{{ date('d-m-Y', strtotime($list['lock_date'])) }}</td> --}}
                                                <td>{{ $list['installments'] }}</td>
                                                
                                              
                                                <td>
                                                    @if ($list['status'] == 'Active')
                                                        <span class="btn btn-sm  btn-success">{{ $list['status'] }}</span>
                                                    @else
                                                        <span class="btn btn-sm btn-danger ">{{ $list['status'] }}</span>
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


        </div>
    </div>

    <!--**********************************
                Content body end
            ***********************************-->
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
