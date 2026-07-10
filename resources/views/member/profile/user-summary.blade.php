@extends('member.layouts.main')
@section('title', 'Profile')
@section('container')
    @php
        $memberName = getName($data['memberid']);
    @endphp

    <!-- MAIN-CONTENT -->

    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Start:: row-1 -->
            <div class="row justify-content-center">
                <div class="col-12 pb-3">
                    <div class="mb-3 mt-5">
                        <h5 class="fw-medium mb-0">Profile Settings</h5>
                        <div class="text-muted fs-13 op-8 mb-3">
                            Update your profile settings
                        </div>
                    </div>
                    @if (session()->has('successMsg'))
                        <div class="alert alert-success alert-dismissible fade show custom-alert-icon shadow-sm"
                            role="alert">
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
                        <div class="alert alert-danger alert-dismissible fade show custom-alert-icon shadow-sm"
                            role="alert">
                            <svg style="fill: rgb(var(--danger-rgb));" style="color:red" xmlns="http://www.w3.org/2000/svg"
                                height="1.5rem" viewBox="0 0 24 24" width="1.5rem" fill="#000000">
                                <path d="M0 0h24v24H0z" fill="none" />
                                <path
                                    d="M15.73 3H8.27L3 8.27v7.46L8.27 21h7.46L21 15.73V8.27L15.73 3zM12 17.3c-.72 0-1.3-.58-1.3-1.3 0-.72.58-1.3 1.3-1.3.72 0 1.3.58 1.3 1.3 0 .72-.58 1.3-1.3 1.3zm1-4.3h-2V7h2v6z" />
                            </svg>
                            {{ session('failedMsg') }}
                        </div>
                    @endif
                    <div class="row mt-4 justify-content-center">
                        <div class="col-xl-10 col-lg-11 col-md-11 col-sm-12">
                            <section class="member-profile-layout">
                                <div class="row g-4 align-items-stretch">
                                    <div class="col-xl-6">
                                        <div class="member-profile-panel member-profile-panel--summary h-100">
                                            <div class="member-profile-avatar-wrap">
                                                <span class="member-profile-avatar">
                                                    {{ strtoupper(substr($memberName ?: $data['memberid'], 0, 1)) }}
                                                </span>
                                            </div>
                                            <div class="member-wallet-panel__eyebrow">Profile Identity</div>
                                            <h3 class="member-profile-name">{{ $memberName }}</h3>
                                            <p class="member-profile-email">{{ $data['email'] }}</p>
                                            <div class="member-profile-meta">
                                                <div class="member-profile-meta__item">
                                                    <span>Member ID</span>
                                                    <strong>{{ $data['memberid'] }}</strong>
                                                </div>
                                                <div class="member-profile-meta__item">
                                                    <span>Mobile</span>
                                                    <strong>{{ $data['mobile'] ?: 'Not updated' }}</strong>
                                                </div>
                                                <div class="member-profile-meta__item">
                                                    <span>Country</span>
                                                    <strong>{{ $data['country'] ?: 'Not updated' }}</strong>
                                                </div>
                                            </div>
                                            <div class="member-profile-note">
                                                Keep your details current to maintain smooth account support and verification.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-6">
                                        <div class="member-profile-panel member-profile-panel--form h-100">
                                            <div class="member-wallet-panel__eyebrow">Account Settings</div>
                                            <h3 class="member-profile-form__title">Update profile information</h3>
                                            <p class="member-wallet-panel__subtitle mb-4">Edit your personal details below and save your changes securely.</p>
                                            <form class="profile-form member-profile-form" action="{{ route('insertProfile') }}"
                                                method="post" enctype="multipart/form-data">
                                                @csrf
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label member-wallet-label">Full Name</label>
                                                        <input type="text" class="form-control member-wallet-input" name="name"
                                                            value="{{ $data['name'] }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label member-wallet-label">Email</label>
                                                        <input type="text" class="form-control member-wallet-input" name="email"
                                                            value="{{ $data['email'] }}" readonly>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label member-wallet-label">Mobile</label>
                                                        <input type="text" class="form-control member-wallet-input" name="mobile"
                                                            value="{{ $data['mobile'] }}" minlength="10" maxlength="10">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label member-wallet-label">Country</label>
                                                        <input type="text" class="form-control member-wallet-input" name="country"
                                                            value="{{ $data['country'] }}">
                                                    </div>
                                                </div>
                                                <div class="member-profile-actions">
                                                    <input class="form-control" type="hidden" name="id"
                                                        value="{{ $data['id'] }}">
                                                    <button type="submit" class="btn member-wallet-submit">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End:: row-1 -->

        </div>
    </div>
    <!-- End::app-content -->

    <!-- END MAIN-CONTENT -->


@endsection
