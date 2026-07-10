@extends('member.layouts.main')
@section('title', 'Support Tickets')
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
                <div class="mt-3">
                    <h5 class="fw-medium mb-0">Support Tickets</h5>
                    <div class="text-muted fs-13 op-8 mb-1">
                        send message
                    </div>
                </div>
                <div class="col-xl-8 col-lg-8">
                   <div class="card custom-card">
                        <div class="top-left"></div>
                        <div class="top-right"></div>
                        <div class="bottom-left"></div>
                        <div class="bottom-right"></div>
                        <div class="card-header mt-2">
                            <h6 class=" mb-1">Support Ticket</h6>
                            <p class="card-description mt-0 mb-0"> Support Ticket </p>
                        </div>
                        <div class="card-body">
                            @foreach ($support as $list)
                         <div class="card custom-card">
                            <div class="top-left"></div>
                            <div class="top-right"></div>
                            <div class="bottom-left"></div>
                            <div class="bottom-right"></div>
                            <div class="card-body my-2 pb-4 mt-0">
                                <h5 class="card-title" style="font-weight: 600">ID : {{ $list['ticket_id'] }} <span class="float-end "><a
                                            href="{{ url('member/support/view-support-ticket') }}/{{ $list['ticket_id'] }}"
                                            class="btn btn-sm btn-warning">View</a></span> </h5>
                                <p class="card-description p-0 m-0">Subject : {{ $list['subject'] }}</p>
                                <p class="card-description p-0 m-0">Created Date :
                                    {{ date('d-M-Y', strtotime($list['created_at'])) }}</p>
                                <p class="card-description p-0 m-0">Status : {{ $list['status'] }}</p>
                                <p class="card-description p-0 m-0">Unread :
                                    @if (AdmUnread($list['ticket_id']) == 0)
                                        <span class="btn btn-sm btn-dark mx-2 py-1 px-2">{{ AdmUnread($list['ticket_id']) }}</span>
                                </p>
                                @else
                                   <span class="btn btn-sm btn-danger mx-2 p-0 px-1">{{ AdmUnread($list['ticket_id']) }}</span></p>
                                @endif
                            </div>
                        </div>
                        <hr>
                        @endforeach
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
