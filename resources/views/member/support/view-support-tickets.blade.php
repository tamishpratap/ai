@extends('member.layouts.main')
@section('title', 'View Support Tickets')
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
               <div class="mb-3 mt-3">
                    <h5 class="fw-medium mb-0">View Support Tickets</h5>
                    <div class="text-muted fs-13 op-8 mb-1">
                        send message
                    </div>
                </div>
                <div class="col-xl-6 col-lg-6">
                   <div class="card custom-card">
                        <div class="top-left"></div>
                        <div class="top-right"></div>
                        <div class="bottom-left"></div>
                        <div class="bottom-right"></div>
                         <div class="card-header mt-2" style="display:inline;border-bottom:1px dashed grey">
                            <div class="d-flex flex-row justify-content-between ">
                                <h6 class="card-title text-dark">Ticket ID : {{ $support['ticket_id'] }} </h6>
                                <p style="text-align:end">Status : <span class="btn btn-sm btn-primary p-2">{{ $support['status'] }}</span> </p>
                            </div> 
                        </div>
                        <div class="card-body">
                           <ul class="chat-p shaprate">
                            @foreach ($tdata as $list)
                                <li>
                                    <div class="d-flex">
                                        <div class="media me-2">
                                            @if ($data['profile_image'] == '' || $list['written_by'] == 'Admin')
                                                <img class="avatar rounded-circle" width="50px" height="45px"
                                                    src="{{ asset('uploads/avatar.jpg') }}" alt="">
                                            @else
                                                <img class="avatar rounded-circle" width="50px" height="45px"
                                                    src="{{ asset('uploads') }}/{{ $data['profile_image'] }}"
                                                    alt="">
                                            @endif
                                        </div>
                                        <div class="media-body mb-4 ms-2">
                                            <h5 class="mb-1">
                                                @if ($list['written_by'] == 'Member')
                                                    <h6 class="preview-subject text-primary">{{ $data['name'] }}
                                                    </h6>
                                                @else
                                                    <h6 class="preview-subject text-danger">Support Team</h6>
                                                @endif <small
                                                    class="text-muted">{{ date('d-M-Y H:i:s', strtotime($list['created_at'])) }}</small>
                                            </h5>
                                            <p class="mb-1" style="width:280px;">{{ $list['text'] }}</p>
                                        </div>
                                    </div>
                                </li>
                            @endforeach

                        </ul>
                        </div>
                    </div>
                </div>
                @if ($support['status'] != 'Closed')
                    <div class="col-md-6 col-xl-6 ">
                        @if (session()->has('createMsg'))
                            <div class="alert alert-success alert-dismissible fade show custom-alert-icon shadow-sm" role="alert">
                                <svg style="fill: rgb(var(--success-rgb));" xmlns="http://www.w3.org/2000/svg" height="1.5rem"
                                    viewBox="0 0 24 24" width="1.5rem" fill="#000000">
                                    <path d="M0 0h24v24H0z" fill="none" />
                                    <path
                                        d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                                </svg>
                                {{ session('createMsg') }}
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
                        <div class="card custom-card">
                            <div class="top-left"></div>
                            <div class="top-right"></div>
                            <div class="bottom-left"></div>
                            <div class="bottom-right"></div>
                            <div class="card-header">
                                <h5 class="card-title">Reply</h5>
                            </div>
                            <div class="card-body">
                                <form class="forms-sample" method="post" action="{{ route('MbReplyTicket') }}">
                                    @csrf
                                    <div class="mb-3">
                                        {{-- <label for="exampleInputUsername1">
                                            <h5 style="font-weight: 500">Reply</h5>
                                        </label> --}}
                                        <textarea class="form-control form-control-lg mt-1" name="reply" placeholder="Enter Your Reply" rows="4"
                                            style="min-height:100px"></textarea>
                                        @error('reply')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="mb-3">
                                        <input type="hidden" name="ticketid" value="{{ $list['ticket_id'] }}">
                                        <button type="submit" class="btn btn-sm btn-primary me-2 float-end">Your Reply</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <!--End::row-1 -->

        </div>
    </div>
    <!-- End::app-content -->

    <!-- END MAIN-CONTENT -->

@endsection
