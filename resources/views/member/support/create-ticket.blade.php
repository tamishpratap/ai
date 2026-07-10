@extends('member.layouts.main')
@section('title', 'Send Messages')
@section('container')

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
               <div class="mb-3 mt-4">
                    <h5 class="fw-medium mb-0">Send Messages</h5>
                    <div class="text-muted fs-13 op-8 mb-3">
                        send message
                    </div>
                </div>
                @if (session()->has('successMsg'))
                    <div class="alert alert-success alert-dismissible fade show custom-alert-icon shadow-sm" role="alert">
                        <svg style="fill: rgb(var(--success-rgb));" xmlns="http://www.w3.org/2000/svg" height="1.5rem"
                            viewBox="0 0 24 24" width="1.5rem" fill="#000000">
                            <path d="M0 0h24v24H0z" fill="none" />
                            <path
                                d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                        </svg>
                        {{ session('successMsg') }}
                    </div>
                @endif
                @if (session()->has('failedMsg'))
                    <div class="alert alert-danger alert-dismissible fade show custom-alert-icon shadow-sm" role="alert">
                        <svg style="fill: rgb(var(--danger-rgb));" style="color:red" xmlns="http://www.w3.org/2000/svg"
                            height="1.5rem" viewBox="0 0 24 24" width="1.5rem" fill="#000000">
                            <path d="M0 0h24v24H0z" fill="none" />
                            <path
                                d="M15.73 3H8.27L3 8.27v7.46L8.27 21h7.46L21 15.73V8.27L15.73 3zM12 17.3c-.72 0-1.3-.58-1.3-1.3 0-.72.58-1.3 1.3-1.3.72 0 1.3.58 1.3 1.3 0 .72-.58 1.3-1.3 1.3zm1-4.3h-2V7h2v6z" />
                        </svg>
                        {{ session('failedMsg') }}
                    </div>
                @endif
                <div class="col-xl-6">
                    <div class="card custom-card">
                        <div class="top-left"></div>
                        <div class="top-right"></div>
                        <div class="bottom-left"></div>
                        <div class="bottom-right"></div>
                        <div class="card-body">
                            <div class="d-sm-flex flex-wrap align-items-start gap-5 p-2 mb-4"
                                style="border-bottom: 1px dashed grey">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div class="lh-1">
                                            <span class="avatar avatar-xxl  me-3">
                                                @if ($data['profile_image'] != '')
                                                    <img src="{{ asset('uploads') }}/{{ $data['profile_image'] }}"
                                                        alt="img">
                                                @else
                                                    <img src="{{ asset('uploads/avatar.jpg') }}" alt="img">
                                                @endif
                                            </span>
                                        </div>
                                        <div class="flex-fill main-profile-info">
                                            <div class="d-flex align-items-center justify-content-between mb-1">
                                                <h5 class="fw-medium mb-1">{{ $data['memberid'] }}</h5>
                                            </div>
                                            <p class="mb-1 text-muted op-7">{{ getName($data['memberid']) }}</p>
                                            <p class="fs-12 mb-0 op-5">
                                                <span class=""><i
                                                        class="ri-mail-line me-1 d-inline-block"></i>{{ $data['email'] }}</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <form action="{{route('createTicket')}}" method="post" enctype="multipart/form-data">
                                <input type="hidden" id="csrf" value="{{csrf_token()}}">
                                @csrf
                                <div class="mb-3">
                                    <label for="memberid" class="form-label fs-14 text-dark">MemberId</label>
                                    <input type="text" class="form-control" name="memberid" id="memberid"
                                        value="{{ $data['memberid'] }}" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="subject" class="form-label fs-14 text-dark">Subject</label>
                                    <input type="text" class="form-control" name="subject" id="subject"
                                        placeholder="Enter Subject">
                                    @error('subject')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="server_name" class="form-label fs-14 text-dark">Message</label>
                                    <textarea class="form-control " name="issue" placeholder="Enter Your Issue" rows="5" style="min-height:100px"></textarea>
                                    @error('issue')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <button class="btn btn-primary float-end" type="submit">Submit Details</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!--End::row-1 -->

        </div>
    </div>
    <!-- End::app-content -->

    <!-- END MAIN-CONTENT -->

@endsection
