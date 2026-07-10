@extends('member.layouts.main')
@section('title', 'View Geneology')
@section('container')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link href="https://cdn.datatables.net/2.1.5/css/dataTables.bootstrap5.css">
    <!-- MAIN-CONTENT -->

    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- Start:: row-1 -->
            <div class="row">
               <div class="mb-3 mt-3">
                    <h5 class="fw-medium mb-0">View Geneology</h5>
                    <div class="text-muted fs-13 op-8 mb-3">
                        check Geneology
                    </div>
                </div>
                @if (session()->has('searchMsg'))
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        {{ session('searchMsg') }}
                    </div>
                @endif
                <div class="col-xl-12">
                    <div class="card custom-card" style="overflow: auto">
                        <div class="card-body bg-primary">
                            <div class="row justify-content-between">
                                <div class="col-md-4 text-center">
                                    <h6 class="card-title mt-2 ml-0" style="font-weight:600">Search Members </h6>
                                </div>
                                <div class="col-md-4">
                                    <form action="{{ route('searchGenelogy') }}" method="post">
                                        @csrf
                                        <div class="input-group">
                                            <input type="text" class="form-control form-control-sm"
                                                placeholder="Enter Memberid" size="15" name="searchid" maxlength="9"
                                                minlength="9" required style="background-color:#0D1C19">
                                            <button class="btn btn-sm " type="submit"
                                                style="border-top-left-radius:0px; border-bottom-left-radius:0px ;background-color:#1D2726;color:white">Search</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                       <div class="card-body" style="">
                            <div class="body genealogy-body genealogy-scroll" style="overflow-y: scroll !important;">
                                <div class="genealogy-tree">
                                    <ul>
                                        <li>
                                            <a href="javascript:void(0);">
                                                <div class="member-view-box">
                                                    <div class="performance-card"
                                                        style=" background-image: url(/uploads/black.jpg);background-size: cover;">
                                                        <div class="member-image">
                                                            @if ($d_data['profile_image'] == '')
                                                                <img src="{{ asset('uploads/avatar.jpg') }}"
                                                                    alt="Profile Image">
                                                            @else
                                                                <img src="{{ asset('uploads') }}/{{ $d_data['profile_image'] }}"
                                                                    alt="Profile Image">
                                                            @endif
                                                        </div>
                                                        <div class="perfomance-cont">
                                                            <h4>{{ $d_data['memberid'] }}</h4>
                                                            @if ($d_data['status'] == 'Temp')
                                                                <h4>Inactive</h4>
                                                            @else
                                                                <h4>{{ $d_data['status'] }}</h4>
                                                            @endif
                                                        </div>
                                                        <div class="dropdown">
                                                            <div class="dropdown-menu performance-h-list">
                                                                <div class="performance-h-title color-1">
                                                                    <h4>Hi !
                                                                        {{ $d_data['name'] }}
                                                                    </h4>
                                                                </div>
                                                                <div class="performance-h-cont">
                                                                    <div class="performance-h-data">
                                                                        <h6>Sponsor Id</h6>
                                                                        <h5>:&nbsp; {{ $d_data['sponsorid'] }}</h5>
                                                                    </div>
                                                                    <div class="performance-h-data">
                                                                        <h6>Package</h6>
                                                                        <h5>:&nbsp; $ {{ $d_data['package'] }}
                                                                        </h5>
                                                                    </div>
                                                                    <div class="performance-h-data">
                                                                        <h6>Direct</h6>
                                                                        <h5>:&nbsp; {{ $d_data['downline'] }}
                                                                        </h5>
                                                                    </div>
                                                                    <div class="performance-h-data">
                                                                        <h6>Self Biz</h6>
                                                                        <h5>:&nbsp; $ {{ $d_data['self_biz'] }}
                                                                        </h5>
                                                                    </div>
                                                                    <div class="performance-h-data">
                                                                        <h6>Principal Wallet</h6>
                                                                        <h5>: &nbsp; $ {{ number_format($d_data['principal_wallet'], 2) }}
                                                                        </h5>
                                                                    </div>
                                                                    <div class="performance-h-data">
                                                                        <h6>Wallet</h6>
                                                                        <h5>: &nbsp; $ {{ $d_data['wallet'] }}
                                                                        </h5>
                                                                    </div>
                                                                    <div class="performance-h-data">
                                                                        <h6>Fund Wallet</h6>
                                                                        <h5>: &nbsp; $ {{ $d_data['p2p_wallet'] }}
                                                                        </h5>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                            @if (getDirectCount($d_data['memberid']) > 0)
                                                <ul class="active">
                                                    @foreach ($directs as $item)
                                                        <li style="padding-top:0px !important;">
                                                            <a href="javascript:void(0);">
                                                                <div class="member-view-box">
                                                                    <div class="performance-card"
                                                                        style=" background-image: url(/uploads/black.jpg);background-size: cover;">
                                                                        <div class="member-image">
                                                                            <a
                                                                                href="{{ url('/member/team/view-geneology') }}/{{ $item['id'] }}">
                                                                                @if ($item['profile_image'] == '')
                                                                                    <img src="{{ asset('uploads/avatar.jpg') }}"
                                                                                        alt="Profile Image">
                                                                                @else
                                                                                    <img src="{{ asset('uploads') }}/{{ $item['profile_image'] }}"
                                                                                        alt="Profile Image">
                                                                                @endif
                                                                            </a>
                                                                        </div>
                                                                        <div class="perfomance-cont">
                                                                            <h4>{{ $item['memberid'] }}</h4>
                                                                            @if ($item['status'] == 'Temp')
                                                                                <h4>Inactive</h4>
                                                                            @else
                                                                                <h4>{{ $item['status'] }}</h4>
                                                                            @endif
                                                                        </div>
                                                                        <div class="dropdown">
                                                                            <div class="dropdown-menu performance-h-list">
                                                                                <div class="performance-h-title color-1">
                                                                                    <h4>Hi !
                                                                                        {{ $item['name'] }}
                                                                                    </h4>
                                                                                </div>
                                                                                <div class="performance-h-cont">
                                                                                    <div class="performance-h-data">
                                                                                        <h6>Sponsor Id</h6>
                                                                                        <h5>:&nbsp;
                                                                                            {{ $item['sponsorid'] }}</h5>
                                                                                    </div>
                                                                                    <div class="performance-h-data">
                                                                                        <h6>Package</h6>
                                                                                        <h5>:&nbsp; $
                                                                                            {{ $item['package'] }}
                                                                                        </h5>
                                                                                    </div>
                                                                                    <div class="performance-h-data">
                                                                                        <h6>Direct</h6>
                                                                                        <h5>:&nbsp; {{ $item['downline'] }}
                                                                                        </h5>
                                                                                    </div>
                                                                                    <div class="performance-h-data">
                                                                                        <h6>Self Biz</h6>
                                                                                        <h5>:&nbsp; $
                                                                                            {{ $item['self_biz'] }}
                                                                                        </h5>
                                                                                    </div>
                                                                                    <div class="performance-h-data">
                                                                                        <h6>Principal Wallet</h6>
                                                                                        <h5>: &nbsp; $
                                                                                            {{ number_format($item['principal_wallet'], 2) }}
                                                                                        </h5>
                                                                                    </div>
                                                                                    <div class="performance-h-data">
                                                                                        <h6>Wallet</h6>
                                                                                        <h5>: &nbsp; $
                                                                                            {{ $item['wallet'] }}
                                                                                        </h5>
                                                                                    </div>
                                                                                    <div class="performance-h-data">
                                                                                        <h6>Fund Wallet</h6>
                                                                                        <h5>: &nbsp; $
                                                                                            {{ $item['p2p_wallet'] }}
                                                                                        </h5>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </a>
                                                            @if (getDirectCount($item->memberid) > 0)
                                                                <ul style="padding-top:0px !important;">
                                                                    @php
                                                                        $directs_2 = getLevelData($item['memberid']);
                                                                    @endphp
                                                                    @foreach ($directs_2 as $item_2)
                                                                        <li>
                                                                            <a href="javascript:void(0);">
                                                                                <div class="member-view-box">
                                                                                    <div class="performance-card"
                                                                                        style="    background-image: url(/uploads/black.jpg);background-size: cover;">
                                                                                        <div class="member-image">
                                                                                            <a
                                                                                                href="{{ url('/member/team/view-geneology') }}/{{ $item_2['id'] }}">
                                                                                                @if ($item_2['profile_image'] == '')
                                                                                                    <img src="{{ asset('uploads/avatar.jpg') }}"
                                                                                                        alt="Profile Image">
                                                                                                @else
                                                                                                    <img src="{{ asset('uploads') }}/{{ $item_2['profile_image'] }}"
                                                                                                        alt="Profile Image">
                                                                                                @endif
                                                                                            </a>
                                                                                        </div>
                                                                                        <div class="perfomance-cont">
                                                                                            <h4>{{ $item_2['memberid'] }}
                                                                                            </h4>
                                                                                            @if ($item_2['status'] == 'Temp')
                                                                                                <h4>Inactive</h4>
                                                                                            @else
                                                                                                <h4>{{ $item_2['status'] }}
                                                                                                </h4>
                                                                                            @endif
                                                                                        </div>
                                                                                        <div class="dropdown">
                                                                                            <div
                                                                                                class="dropdown-menu performance-h-list">
                                                                                                <div
                                                                                                    class="performance-h-title color-1">
                                                                                                    <h4>Hi !
                                                                                                        {{ $item_2['name'] }}
                                                                                                    </h4>
                                                                                                </div>
                                                                                                <div
                                                                                                    class="performance-h-cont">
                                                                                                    <div
                                                                                                        class="performance-h-data">
                                                                                                        <h6>Sponsor Id</h6>
                                                                                                        <h5>:&nbsp;
                                                                                                            {{ $item_2['sponsorid'] }}
                                                                                                        </h5>
                                                                                                    </div>
                                                                                                    <div
                                                                                                        class="performance-h-data">
                                                                                                        <h6>Package</h6>
                                                                                                        <h5>:&nbsp; $
                                                                                                            {{ $item_2['package'] }}
                                                                                                        </h5>
                                                                                                    </div>
                                                                                                    <div
                                                                                                        class="performance-h-data">
                                                                                                        <h6>Direct</h6>
                                                                                                        <h5>:&nbsp;
                                                                                                            {{ $item_2['downline'] }}
                                                                                                        </h5>
                                                                                                    </div>
                                                                                                    <div
                                                                                                        class="performance-h-data">
                                                                                                        <h6>Self Biz</h6>
                                                                                                        <h5>:&nbsp; $
                                                                                                            {{ $item_2['self_biz'] }}
                                                                                                        </h5>
                                                                                                    </div>
                                                                                                    <div
                                                                                                        class="performance-h-data">
                                                                                                        <h6>Principal Wallet</h6>
                                                                                                        <h5>: &nbsp; $
                                                                                                            {{ number_format($item_2['principal_wallet'], 2) }}
                                                                                                        </h5>
                                                                                                    </div>
                                                                                                    <div
                                                                                                        class="performance-h-data">
                                                                                                        <h6>Wallet</h6>
                                                                                                        <h5>: &nbsp; $
                                                                                                            {{ $item_2['wallet'] }}
                                                                                                        </h5>
                                                                                                    </div>
                                                                                                    <div
                                                                                                        class="performance-h-data">
                                                                                                        <h6>Fund Wallet</h6>
                                                                                                        <h5>: &nbsp; $
                                                                                                            {{ $item_2['p2p_wallet'] }}
                                                                                                        </h5>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </a>
                                                                            @if (getDirectCount($item_2['memberid']) > 0)
                                                                                <ul style="padding-top:0px !important;">
                                                                                    @php
                                                                                        $directs_3 = getLevelData(
                                                                                            $item_2['memberid'],
                                                                                        );
                                                                                    @endphp
                                                                                    @foreach ($directs_3 as $item_3)
                                                                                        <li>
                                                                                            <a href="javascript:void(0);">
                                                                                                <div
                                                                                                    class="member-view-box">
                                                                                                    <div class="performance-card"
                                                                                                        style="    background-image: url(/uploads/black.jpg);background-size: cover;">
                                                                                                        <div
                                                                                                            class="member-image">
                                                                                                            <a
                                                                                                                href="{{ url('/member/team/view-geneology') }}/{{ $item_3['id'] }}">
                                                                                                                @if ($item_3['profile_image'] == '')
                                                                                                                    <img src="{{ asset('uploads/avatar.jpg') }}"
                                                                                                                        alt="Profile Image">
                                                                                                                @else
                                                                                                                    <img src="{{ asset('uploads') }}/{{ $item_3['profile_image'] }}"
                                                                                                                        alt="Profile Image">
                                                                                                                @endif
                                                                                                            </a>
                                                                                                        </div>
                                                                                                        <div
                                                                                                            class="perfomance-cont">
                                                                                                            <h4> {{ $item_3['memberid'] }}
                                                                                                            </h4>
                                                                                                            @if ($item_3['status'] == 'Temp')
                                                                                                                <h4>Inactive
                                                                                                                </h4>
                                                                                                            @else
                                                                                                                <h4>{{ $item_3['status'] }}
                                                                                                                </h4>
                                                                                                            @endif
                                                                                                        </div>
                                                                                                        <div
                                                                                                            class="dropdown">
                                                                                                            <div
                                                                                                                class="dropdown-menu performance-h-list">
                                                                                                                <div
                                                                                                                    class="performance-h-title color-1">
                                                                                                                    <h4>Hi !
                                                                                                                        {{ $item_3['name'] }}
                                                                                                                    </h4>
                                                                                                                </div>
                                                                                                                <div
                                                                                                                    class="performance-h-cont">
                                                                                                                    <div
                                                                                                                        class="performance-h-data">
                                                                                                                        <h6>Sponsor
                                                                                                                            Id
                                                                                                                        </h6>
                                                                                                                        <h5>:&nbsp;
                                                                                                                            {{ $item_3['sponsorid'] }}
                                                                                                                        </h5>
                                                                                                                    </div>
                                                                                                                    <div
                                                                                                                        class="performance-h-data">
                                                                                                                        <h6>Package
                                                                                                                        </h6>
                                                                                                                        <h5>:&nbsp;
                                                                                                                            $
                                                                                                                            {{ $item_3['package'] }}
                                                                                                                        </h5>
                                                                                                                    </div>
                                                                                                                    <div
                                                                                                                        class="performance-h-data">
                                                                                                                        <h6>Direct
                                                                                                                        </h6>
                                                                                                                        <h5>:&nbsp;
                                                                                                                            {{ $item_3['downline'] }}
                                                                                                                        </h5>
                                                                                                                    </div>
                                                                                                                    <div
                                                                                                                        class="performance-h-data">
                                                                                                                        <h6>Self Biz</h6>
                                                                                                                        <h5>:&nbsp;
                                                                                                                            $
                                                                                                                            {{ $item_3['self_biz'] }}
                                                                                                                        </h5>
                                                                                                                    </div>
                                                                                                                    <div
                                                                                                                        class="performance-h-data">
                                                                                                                        <h6>Principal Wallet
                                                                                                                        </h6>
                                                                                                                        <h5>:
                                                                                                                            &nbsp;
                                                                                                                            $
                                                                                                                            {{ number_format($item_3['principal_wallet'], 2) }}
                                                                                                                        </h5>
                                                                                                                    </div>
                                                                                                                    <div
                                                                                                                        class="performance-h-data">
                                                                                                                        <h6>Wallet
                                                                                                                        </h6>
                                                                                                                        <h5>:
                                                                                                                            &nbsp;
                                                                                                                            $
                                                                                                                            {{ $item_3['wallet'] }}
                                                                                                                        </h5>
                                                                                                                    </div>
                                                                                                                    <div
                                                                                                                        class="performance-h-data">
                                                                                                                        <h6>Fund
                                                                                                                            Wallet
                                                                                                                        </h6>
                                                                                                                        <h5>:
                                                                                                                            &nbsp;
                                                                                                                            $
                                                                                                                            {{ $item_3['p2p_wallet'] }}
                                                                                                                        </h5>
                                                                                                                    </div>
                                                                                                                </div>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </a>
                                                                                            @if (getDirectCount($item_3['memberid']) > 0)
                                                                                                <ul style="padding-top:0px !important;">
                                                                                                    @php
                                                                                                        $directs_4 = getLevelData(
                                                                                                            $item_3[
                                                                                                                'memberid'
                                                                                                            ],
                                                                                                        );
                                                                                                    @endphp
                                                                                                    @foreach ($directs_4 as $item_4)
                                                                                                        <li>
                                                                                                            <a
                                                                                                                href="javascript:void(0);">
                                                                                                                <div
                                                                                                                    class="member-view-box">
                                                                                                                    <div class="performance-card"
                                                                                                                        style="    background-image: url(/uploads/black.jpg);background-size: cover;">
                                                                                                                        <div
                                                                                                                            class="member-image">
                                                                                                                            <a
                                                                                                                                href="{{ url('/member/team/view-geneology') }}/{{ $item_4['id'] }}">
                                                                                                                                @if ($item_4['profile_image'] == '')
                                                                                                                                    <img src="{{ asset('uploads/avatar.jpg') }}"
                                                                                                                                        alt="Profile Image">
                                                                                                                                @else
                                                                                                                                    <img src="{{ asset('uploads') }}/{{ $item_4['profile_image'] }}"
                                                                                                                                        alt="Profile Image">
                                                                                                                                @endif
                                                                                                                            </a>
                                                                                                                        </div>
                                                                                                                        <div
                                                                                                                            class="perfomance-cont">
                                                                                                                            <h4>
                                                                                                                                {{ $item_4['memberid'] }}
                                                                                                                            </h4>
                                                                                                                            @if ($item_4['status'] == 'Temp')
                                                                                                                                <h4>Inactive
                                                                                                                                </h4>
                                                                                                                            @else
                                                                                                                                <h4>{{ $item_4['status'] }}
                                                                                                                                </h4>
                                                                                                                            @endif
                                                                                                                        </div>
                                                                                                                        <div
                                                                                                                            class="dropdown">
                                                                                                                            <div
                                                                                                                                class="dropdown-menu performance-h-list">
                                                                                                                                <div
                                                                                                                                    class="performance-h-title color-1">
                                                                                                                                    <h4>Hi
                                                                                                                                        !
                                                                                                                                        {{ $item_4['name'] }}
                                                                                                                                    </h4>
                                                                                                                                </div>
                                                                                                                                <div
                                                                                                                                    class="performance-h-cont">
                                                                                                                                    <div
                                                                                                                                        class="performance-h-data">
                                                                                                                                        <h6>Sponsor
                                                                                                                                            Id
                                                                                                                                        </h6>
                                                                                                                                        <h5>:&nbsp;
                                                                                                                                            {{ $item_4['sponsorid'] }}
                                                                                                                                        </h5>
                                                                                                                                    </div>
                                                                                                                                    <div
                                                                                                                                        class="performance-h-data">
                                                                                                                                        <h6>Package
                                                                                                                                        </h6>
                                                                                                                                        <h5>:&nbsp;
                                                                                                                                            $
                                                                                                                                            {{ $item_4['package'] }}
                                                                                                                                        </h5>
                                                                                                                                    </div>
                                                                                                                                    <div
                                                                                                                                        class="performance-h-data">
                                                                                                                                        <h6>Direct
                                                                                                                                        </h6>
                                                                                                                                        <h5>:&nbsp;
                                                                                                                                            {{ $item_4['downline'] }}
                                                                                                                                        </h5>
                                                                                                                                    </div>
                                                                                                                                    <div
                                                                                                                                        class="performance-h-data">
                                                                                                                                        <h6>Self Biz</h6>
                                                                                                                                        <h5>:&nbsp;
                                                                                                                                            $
                                                                                                                                            {{ $item_4['self_biz'] }}
                                                                                                                                        </h5>
                                                                                                                                    </div>
                                                                                                                                    <div
                                                                                                                                        class="performance-h-data">
                                                                                                                                        <h6>Principal Wallet
                                                                                                                                        </h6>
                                                                                                                                        <h5>:
                                                                                                                                            &nbsp;
                                                                                                                                            $
                                                                                                                                            {{ number_format($item_4['principal_wallet'], 2) }}
                                                                                                                                        </h5>
                                                                                                                                    </div>
                                                                                                                                    <div
                                                                                                                                        class="performance-h-data">
                                                                                                                                        <h6>Wallet
                                                                                                                                        </h6>
                                                                                                                                        <h5>:
                                                                                                                                            &nbsp;
                                                                                                                                            $
                                                                                                                                            {{ $item_4['wallet'] }}
                                                                                                                                        </h5>
                                                                                                                                    </div>
                                                                                                                                    <div
                                                                                                                                        class="performance-h-data">
                                                                                                                                        <h6>Fund
                                                                                                                                            Wallet
                                                                                                                                        </h6>
                                                                                                                                        <h5>:
                                                                                                                                            &nbsp;
                                                                                                                                            $
                                                                                                                                            {{ $item_4['p2p_wallet'] }}
                                                                                                                                        </h5>
                                                                                                                                    </div>
                                                                                                                                </div>
                                                                                                                            </div>
                                                                                                                        </div>
                                                                                                                    </div>
                                                                                                                </div>
                                                                                                            </a>
                                                                                                        </li>
                                                                                                    @endforeach
                                                                                                </ul>
                                                                                            @endif
                                                                                        </li>
                                                                                    @endforeach
                                                                                </ul>
                                                                            @endif
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End:: row-1 -->

        </div>
    </div>
    <!-- End::app-content -->

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
