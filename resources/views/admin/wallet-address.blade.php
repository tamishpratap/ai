@extends('admin.layouts.main') 
@section('title', 'User Wallet Address')
@section('content')
    @push('head')
        <link rel="stylesheet" href="{{ asset('adm_assets/assets/plugins/DataTables/datatables.min.css') }}">
    @endpush
<div class="container-fluid">
    <div class="page-header">
        <div class="row align-items-end">
            <div class="col-lg-8">
                <div class="page-header-title">
                    <i class="ik ik-edit bg-blue"></i>
                    <div class="d-inline">
                        <h5>{{ __('User Wallet Address')}}</h5>
                        <span>{{ __('User Wallet Address Details & Updation')}}</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <nav class="breadcrumb-container" aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="#"><i class="ik ik-home"></i></a>
                        </li>
                        <li class="breadcrumb-item"><a href="#">{{ __('Admin')}}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Wallet Address')}}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">
            @include('admin.partials.history-date-filter')
            @if (session()->has('update'))
               <div class="alert alert-primary">{{session('update')}}</div> 
            @endif
            <div class="card">
                <div class="card-header"><h3>{{ __('Wallet Address')}}</h3></div>
                <div class="card-body px-5">
                    <table id="data_table" class="table">
                        <thead>
                            <tr>
                                <th>{{ __('S.No')}}</th>
                                <th>{{ __('Avatar')}}</th>
                                <th>{{ __('Member Id')}}</th>
                                <th>{{ __('Name')}}</th>
                                <th class="text-center">{{ __('Wallet Address')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $i = 1;  @endphp
                            @foreach ($data as $list )
                                <tr>
                                    <td>{{$i}}</td>
                                    <td>
                                    @if ($list['profile_image']=='')
                                       <img src="{{asset('uploads/avatar.jpg')}}" class="table-user-thumb" alt=""> 
                                    @else
                                       <img src="{{asset('uploads')}}/{{$list['profile_image']}}" class="table-user-thumb" alt="">
                                    @endif
                                    </td>
                                    <td>{{ $list['memberid']}}</td>
                                    <td>{{ $list['name']}}</td>
                                    <td>
                                        <form action="{{route('updateWallet')}}" method="post">
                                            @csrf
                                            <div class="form-group row">
                                                <div class="col-md-9">
                                                   <input type="text" class="form-control" name="address" value="{{$list['wallet_address']}}">  
                                                </div>
                                                <div class="col-md-3">
                                                    <input type="hidden"  name="id" value="{{$list['id']}}">
                                                    <button class="btn btn-primary">Update </button>
                                                </div>
                                            </div>
                                        </form>
                                    </td>
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
    @endpush
@endsection