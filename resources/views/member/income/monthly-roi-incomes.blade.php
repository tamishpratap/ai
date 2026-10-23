@extends('member.layouts.main')
@section('title', 'Monthly ROI Income')
@section('container')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link href="https://cdn.datatables.net/2.1.5/css/dataTables.bootstrap5.css">
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                    <div class="mb-4">
                        <h5 class="fw-medium mb-0">Monthly ROI Income</h5>
                        <div class="text-muted fs-13 op-8 mb-3">Earning Section</div>
                    </div>
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="top-left"></div><div class="top-right"></div>
                                <div class="bottom-left"></div><div class="bottom-right"></div>
                                <div class="card-header"><div class="card-title">Monthly ROI Income</div></div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="basic-datatable" class="table dataTable table-bordered text-nowrap w-100">
                                            <thead><tr>
                                                <th>S.No</th><th>Date</th><th>Memberid</th><th>Total Investment</th>
                                                <th>Rate</th><th>Installment</th><th>Amount</th><th>Status</th>
                                            </tr></thead>
                                            <tbody>
                                                @php $i = 1; @endphp
                                                @foreach ($roiData as $list)
                                                    <tr>
                                                        <td>{{ $i }}</td>
                                                        <td>{{ date('d-m-Y', strtotime($list['created_at'])) }}</td>
                                                        <td>{{ $list['memberid'] }}</td>
                                                        <td>$ {{ number_format($list['total_investment'], 2) }}</td>
                                                        <td>{{ $list['rate'] }} %</td>
                                                        <td>{{ $list['installment'] }}</td>
                                                        <td>$ {{ number_format($list['amount'], 2) }}</td>
                                                        <td>
                                                            @if ($list['status'] == 'Paid')
                                                                <label class="btn btn-sm btn-success">{{ $list['status'] }}</label>
                                                            @else
                                                                <label class="btn btn-sm btn-danger">{{ $list['status'] }}</label>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @php $i++; @endphp
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
        </div>
    </div>
    @push('user-scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdn.datatables.net/responsive/2.3.0/js/dataTables.responsive.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
        <script>new DataTable('#basic-datatable');</script>
        <link rel="modulepreload" href="{{ asset('mem_assets/datatables-_3Z3Rdpk.js') }}" />
        <script type="module" src="{{ asset('mem_assets/datatables-_3Z3Rdpk.js') }}"></script>
    @endpush
@endsection
