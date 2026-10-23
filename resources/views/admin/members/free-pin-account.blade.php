@extends('admin.layouts.main')
@section('title', 'Free Pin Activation')
@section('content')
    @push('head')
        <link rel="stylesheet" href="{{ asset('adm_assets/assets/plugins/DataTables/datatables.min.css') }}">
    @endpush
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="ik ik-file-text bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('Free Pin Activation') }}</h5>
                            <span>{{ __('Free Pin Activation') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <nav class="breadcrumb-container" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="#"><i class="ik ik-home"></i></a>
                            </li>
                            <li class="breadcrumb-item"><a href="#">{{ __('Admin') }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('Free Pin Activation') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-md-12">
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
                <div class="card">
                    <div class="card-header">
                        <h3>{{ __('Free Pin Activation') }}</h3>
                    </div>
                    <div class="card-body px-5" style="overflow: auto">
                        <table id="data_table" class="table text-center">
                            <thead>
                                <tr>
                                    <th>{{ __('S.No') }}</th>
                                    <th>{{ __('Joining') }}</th>
                                    <th>{{ __('Avatar') }}</th>
                                    <th>{{ __('Member Id') }}</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Sponsor Id') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Package') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $i = 1;  @endphp
                                @foreach ($data as $list)
                                    <tr>
                                        <td>{{ $i }}</td>
                                        <td>{{ date('d-m-Y', strtotime($list['created_at'])) }}<br>{{ date('h:i:s', strtotime($list['created_at'])) }}
                                        </td>
                                        <td>
                                            @if ($list['profile_image'] == '')
                                                <img src="{{ asset('uploads/avatar.jpg') }}" class="table-user-thumb"
                                                    alt="">
                                            @else
                                                <img src="{{ asset('uploads') }}/{{ $list['profile_image'] }}"
                                                    class="table-user-thumb" alt="">
                                            @endif
                                        </td>
                                        <td>{{ $list['memberid'] }}</td>
                                        <td>{{ $list['name'] }}</td>
                                        <td>{{ $list['email'] }}</td>
                                        <td>{{ $list['sponsorid'] }}</td>
                                        <td>
                                            @if ($list['status'] == 'Temp')
                                                <button class="btn btn-sm btn-danger">{{ $list['status'] }}</button>
                                            @elseif ($list['status'] == 'Active')
                                                <button class="btn btn-sm btn-success">{{ $list['status'] }}</button>
                                            @else
                                                <button class="btn btn-sm btn-primary">{{ $list['status'] }}</button>
                                            @endif
                                        </td>
                                        <form action="{{ route('PinUserAccount') }}" method="post" class="activation-form">
                                            @csrf
                                            <td>
                                                <div class="table-actions text-center">
                                                    <select class="form-control" name="package" id="package" required>
                                                     <option value="">Select Package</option>
                                                     <option value="Silver">Silver ($100 - $1,000)</option>
                                                     <option value="Gold">Gold ($1,100 - $5,000)</option>
                                                     <option value="Platinum">Platinum ($5,100 - $10,000)</option>
                                                     <option value="Diamond">Diamond ($10,100+)</option>
                                                    </select>
                                                    @error('package')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror

                                                </div>
                                            </td>
                                            <td>
                                                <div class="table-actions text-center">
                                                    <input type="text" class="form-control" name="amount" id="amount"
                                                        onkeypress='return event.charCode >= 48 && event.charCode <= 57'
                                                        placeholder="Enter amount" required>
                                                    <span class="ptext" style="color: rgb(26 12 185)"></span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="table-actions text-center">
                                                    <input type="hidden" name="memberid" value="{{ $list['memberid'] }}">
                                                    <button type="submit" class="btn btn-sm btn-primary activate-btn">Activate</button>
                                                </div>
                                            </td>
                                        </form>
                                    </tr>
                                    @php $i++;  @endphp
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('script')
        <script src="{{ asset('adm_assets/assets/plugins/DataTables/datatables.min.js') }}"></script>
        <script src="{{ asset('adm_assets/assets/js/datatables.js') }}"></script>
        <script src="{{ asset('adm_assets/assets/js/form-components.js') }}"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <script>
            $(document).ready(function() {
                $(document).on('submit', '.activation-form', function(e) {
                    var $form = $(this);
                    if ($form.data('submitted') === true) {
                        e.preventDefault();
                        return false;
                    }
                    $form.data('submitted', true);
                    var $btn = $form.find('.activate-btn');
                    $btn.prop('disabled', true);
                    $btn.html('<i class="ik ik-loader fa-spin"></i> Processing...');
                });
            });
        </script>
        {{-- <script>
            $('#package').on('change', function() {
                var package = $(this).val();
                $('#amount').val('');
                if (package == 'Carbon Spark') {
                    $('.ptext').html('Min $50 - Max $5000 to invest in this package');
                } else if (package == 'Carbon Pulse') {
                    $('.ptext').html('Min $5001 - Max $15000 to invest in this package');
                } else if (package == 'Carbon Surge') {
                    $('.ptext').html('Min $15001 and Max $30000 unlimited to invest in this package');
                } else if (package == 'Carbon Apex') {
                    $('.ptext').html('Min $30001 and Max $75000 unlimited to invest in this package');
                } else {
                    $('.ptext').html('');
                }

            });
        </script> --}}
    @endpush
@endsection
