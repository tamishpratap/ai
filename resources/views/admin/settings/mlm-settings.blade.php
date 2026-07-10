@extends('admin.layouts.main')
@section('title', 'MLM Settings')
@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="ik ik-settings bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('MLM Settings') }}</h5>
                            <span>{{ __('Binary MLM configuration') }}</span>
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
                            <li class="breadcrumb-item active" aria-current="page">{{ __('MLM Settings') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                @if (session()->has('successMsg'))
                    <div class="alert alert-primary">{{ session('successMsg') }}</div>
                @endif
                @if (session()->has('failedMsg'))
                    <div class="alert alert-danger">{{ session('failedMsg') }}</div>
                @endif
                <div class="card">
                    <div class="card-header">
                        <h3>{{ __('MLM Settings') }}</h3>
                    </div>
                    <div class="card-body px-5">
                        <form action="{{ route('mlmSettingsUpdate') }}" method="post">
                            @csrf

                            <h5 class="mt-2 mb-3">Income Percentages</h5>
                            <div class="form-group">
                                <label>Direct Referral Reward (%)</label>
                                <input type="number" step="0.01" min="0" class="form-control"
                                    name="direct_referral_percentage"
                                    value="{{ old('direct_referral_percentage', $settings['direct_referral_percentage']) }}">
                                @error('direct_referral_percentage')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Binary Matching Reward (%)</label>
                                <input type="number" step="0.01" min="0" class="form-control"
                                    name="binary_matching_percentage"
                                    value="{{ old('binary_matching_percentage', $settings['binary_matching_percentage']) }}">
                                @error('binary_matching_percentage')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Maximum Income Cap (% of active package)</label>
                                <input type="number" step="0.01" min="0" class="form-control"
                                    name="maximum_income_percentage"
                                    value="{{ old('maximum_income_percentage', $settings['maximum_income_percentage']) }}">
                                @error('maximum_income_percentage')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <small class="text-muted">Informational - the cap is currently applied as a flat 2x (200%) in code.</small>
                            </div>

                            <h5 class="mt-4 mb-3">Trading Reward</h5>
                            <div class="form-group">
                                <label>Trading Reward</label>
                                <select class="form-select" name="trading_reward_status">
                                    <option value="enabled" {{ old('trading_reward_status', $settings['trading_reward_status']) == 'enabled' ? 'selected' : '' }}>Enabled</option>
                                    <option value="disabled" {{ old('trading_reward_status', $settings['trading_reward_status']) == 'disabled' ? 'selected' : '' }}>Disabled</option>
                                </select>
                                <small class="text-muted">The daily percentage itself is set on the <a href="{{ url('admin/rate/set-rates') }}">Set Rates</a> page.</small>
                            </div>

                            <h5 class="mt-4 mb-3">Binary Closing</h5>
                            <div class="form-group">
                                <label>Binary Closing</label>
                                <select class="form-select" name="binary_closing_status">
                                    <option value="enabled" {{ old('binary_closing_status', $settings['binary_closing_status']) == 'enabled' ? 'selected' : '' }}>Enabled</option>
                                    <option value="disabled" {{ old('binary_closing_status', $settings['binary_closing_status']) == 'disabled' ? 'selected' : '' }}>Disabled</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Binary Closing Time (daily)</label>
                                <input type="time" class="form-control" name="binary_closing_time"
                                    value="{{ old('binary_closing_time', $settings['binary_closing_time']) }}">
                                @error('binary_closing_time')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <h5 class="mt-4 mb-3">Withdrawal</h5>
                            <div class="form-group">
                                <label>Minimum Withdrawal (USDT)</label>
                                <input type="number" step="0.01" min="0" class="form-control"
                                    name="minimum_withdrawal" value="{{ old('minimum_withdrawal', $settings['minimum_withdrawal']) }}">
                                @error('minimum_withdrawal')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Withdrawal Fee (%)</label>
                                <input type="number" step="0.01" min="0" class="form-control"
                                    name="withdrawal_fee_percentage" value="{{ old('withdrawal_fee_percentage', $settings['withdrawal_fee_percentage']) }}">
                                @error('withdrawal_fee_percentage')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Withdrawals</label>
                                <select class="form-select" name="withdrawal_status">
                                    <option value="enabled" {{ old('withdrawal_status', $settings['withdrawal_status']) == 'enabled' ? 'selected' : '' }}>Enabled</option>
                                    <option value="disabled" {{ old('withdrawal_status', $settings['withdrawal_status']) == 'disabled' ? 'selected' : '' }}>Disabled</option>
                                </select>
                            </div>

                            <h5 class="mt-4 mb-3">P2P Transfer</h5>
                            <div class="form-group">
                                <label>P2P Transfer Fee (%)</label>
                                <input type="number" step="0.01" min="0" class="form-control"
                                    name="p2p_transfer_fee_percentage" value="{{ old('p2p_transfer_fee_percentage', $settings['p2p_transfer_fee_percentage']) }}">
                                @error('p2p_transfer_fee_percentage')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>P2P Transfers</label>
                                <select class="form-select" name="p2p_transfer_status">
                                    <option value="enabled" {{ old('p2p_transfer_status', $settings['p2p_transfer_status']) == 'enabled' ? 'selected' : '' }}>Enabled</option>
                                    <option value="disabled" {{ old('p2p_transfer_status', $settings['p2p_transfer_status']) == 'disabled' ? 'selected' : '' }}>Disabled</option>
                                </select>
                            </div>

                            <h5 class="mt-4 mb-3">Deposits</h5>
                            <div class="form-group">
                                <label>Deposits</label>
                                <select class="form-select" name="deposit_status">
                                    <option value="enabled" {{ old('deposit_status', $settings['deposit_status']) == 'enabled' ? 'selected' : '' }}>Enabled</option>
                                    <option value="disabled" {{ old('deposit_status', $settings['deposit_status']) == 'disabled' ? 'selected' : '' }}>Disabled</option>
                                </select>
                            </div>

                            <div>
                                <button class="btn btn-primary float-right">Update Settings</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
