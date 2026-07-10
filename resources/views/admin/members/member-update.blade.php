@extends('admin.layouts.main') 
@section('title', 'Update Member Deatils')
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
                            <h5>{{ __('Update Member Details')}}</h5>
                            <span>{{ __('User details updates')}}</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <nav class="breadcrumb-container" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="#"><i class="ik ik-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="#">{{ __('Dashboard')}}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('Member Details')}}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    
        <div class="row">
            <div class="col-lg-8 col-md-8">
                @if (session()->has('updateErr'))
                    <div class="alert alert-success" role="alert">
                        {{session('updateErr')}}
                    </div>
                @endif
                <div class="card">
                    <div class="card-header">
                        <h5>Member Details Updates</h5>
                    </div>
                  <div class="row">
                    <div class="col-lg-12">
                        <div class="card-body px-5"> 
                            <form action="{{route('updateDetails')}}" method="post" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-4">
                                        @if ($data['profile_image']==NULL)
                                          <img  src="{{ asset('uploads/avatar.jpg')}}" alt="" style="width: 90%"> 
                                        @else
                                          <img  src="{{ asset('uploads')}}/{{$data['profile_image']}}" alt="" style="width: 90%">  
                                        @endif
                                        <img src=""/>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label>Name </label>
                                            <input type="text" class="form-control" name="name" value="{{$data['name']}}">
                                        </div>
                                        <div class="form-group">
                                            <label>Mobile</label>
                                            <input type="text" class="form-control" name="mobile" value="{{$data['mobile']}}" maxlength="10" minlength="10">
                                        </div>
                                        <div class="form-group">
                                            <label>Email</label>
                                            <input type="text" class="form-control" name="email" value="{{$data['email']}}">
                                            @error('email')
                                                <span class="text-danger">{{$message}}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Father Name </label>
                                            <input type="text" class="form-control" name="father" value="{{$data['father_name']}}">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Date of Birth </label>
                                            <input type="date" class="form-control" name="dob" value="{{$data['dob']}}">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Address</label>
                                            <input type="text" class="form-control" name="address" value="{{$data['address']}}">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>City</label>
                                            <input type="text" class="form-control" name="city" value="{{$data['city']}}">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                          <label>State</label>
                                          <input type="text" class="form-control" name="state" value="{{$data['state']}}">
                                         </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                          <label>Country</label>
                                          <input type="text" class="form-control" name="country" value="{{$data['country']}}">
                                         </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                          <label>Gender</label>
                                          <input type="text" class="form-control" name="gender" value="{{$data['gender']}}">
                                         </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                          <label>Occupation</label>
                                          <input type="text" class="form-control" name="occupation" value="{{$data['occupation']}}">
                                         </div>
                                    </div>
                                    
                                    {{-- <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Password</label>
                                            <input type="password" class="form-control" name="password" value="{{$data['password']}}">
                                        </div>
                                    </div> --}}
                                    <p>Profile Image Change</p>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                          <label>Profile Image</label>
                                            <input type="file" name="image" class="file-upload-default" >
                                            <div class="input-group col-xs-12">
                                                <input type="text" class="form-control file-upload-info" disabled placeholder="Upload Image">
                                                <span class="input-group-append">
                                                <button class="file-upload-browse btn btn-primary" type="button">{{ __('Upload')}}</button>
                                                </span>
                                            </div>
                                         </div>
                                    </div>
                                    {{-- <p>Transaction Password Change</p>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Txn Password</label>
                                            <input type="password" class="form-control" name="txn_password" value="{{$data['txn_password']}}">
                                        </div>
                                    </div> --}}
                                </div>
                                <div class="row">
                                    
                                    <div class="col-md-12">
                                        <input type="hidden" class="form-control" name="id" value="{{$data['id']}}"> 
                                      <button class="btn btn-primary float-right mt-4">Update Details</button>
                                    </div>
                                </div>
                            </form>
                            
                        </div>
                    </div>
                  </div>
                </div>
            </div>
        </div>
    </div>
    @push('script')
        <script src="{{ asset('adm_assets/assets/plugins/DataTables/datatables.min.js') }}"></script>
        <script src="{{ asset('adm_assets/assets/js/datatables.js') }}"></script>
        <script src="{{ asset('adm_assets/assets/js/form-components.js') }}"></script>
    @endpush
@endsection