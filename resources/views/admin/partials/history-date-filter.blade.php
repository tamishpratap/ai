{{-- Reusable Admin History Date Filter Partial --}}
@php
    $filterData = $filterData ?? [];
    $currentBaseUrl = url()->current();
    $hasDateOptions = !empty($filterData['date_options']) && count($filterData['date_options']) > 1;
@endphp

<style>
    .admin-history-filter-card {
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        border: 1px solid #e3e6f0;
        margin-bottom: 1.5rem;
        background: #ffffff;
    }
    .admin-history-filter-card .card-header {
        background-color: #f8f9fc;
        border-bottom: 1px solid #e3e6f0;
        padding: 0.75rem 1.25rem;
    }
    .admin-history-filter-badge {
        font-size: 0.82rem;
        padding: 0.35rem 0.65rem;
        border-radius: 4px;
        letter-spacing: 0.2px;
    }
    .admin-history-filter-card label {
        display: block;
        font-size: 0.8rem;
        font-weight: 700;
        color: #4e73df;
        margin-bottom: 0.4rem;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        line-height: 1.2;
    }
    /* Fixed identical height and box-model across all inputs, selects, and buttons */
    .admin-history-filter-card .form-control,
    .admin-history-filter-card .admin-filter-btn {
        height: 38px !important;
        min-height: 38px !important;
        max-height: 38px !important;
        font-size: 0.875rem !important;
        border-radius: 4px !important;
        box-sizing: border-box !important;
    }
    .admin-history-filter-card .form-control {
        border: 1px solid #d1d3e2 !important;
        padding: 0.375rem 0.75rem !important;
        line-height: 1.5 !important;
        background-color: #fff !important;
        color: #495057 !important;
        width: 100% !important;
    }
    .admin-history-filter-card .form-control:focus {
        border-color: #bac8f3 !important;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25) !important;
    }
    .admin-history-filter-card .admin-filter-btn {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 0.375rem 1rem !important;
        font-weight: 600 !important;
        white-space: nowrap !important;
        line-height: 1.5 !important;
    }
    .admin-history-filter-actions {
        display: flex;
        align-items: center;
        width: 100%;
    }
    /* Responsive rules for mobile landscape, portrait, and tablets */
    @media (max-width: 575.98px) {
        .admin-history-filter-actions {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .admin-history-filter-actions .admin-filter-btn {
            width: 100% !important;
            margin-left: 0 !important;
        }
    }
</style>

<div class="card admin-history-filter-card">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
        <div class="d-flex align-items-center flex-wrap mb-1 mb-sm-0">
            <h5 class="mb-0 font-weight-bold text-dark d-flex align-items-center">
                <i class="ik ik-calendar mr-2 text-primary"></i>{{ __('Date Filter') }}
            </h5>
            @if (!empty($filterData['active_filter']) && !empty($filterData['badge_text']))
                <span class="badge badge-info ml-sm-3 ml-2 admin-history-filter-badge" title="Active Filter">
                    <i class="ik ik-filter mr-1"></i>{{ $filterData['badge_text'] }}
                </span>
            @endif
        </div>
        <div class="d-flex align-items-center">
            @if (!empty($filterData['active_filter']))
                <a href="{{ $currentBaseUrl }}" class="btn btn-sm btn-outline-danger" title="{{ __('Reset Filter') }}">
                    <i class="ik ik-rotate-ccw mr-1"></i>{{ __('Reset') }}
                </a>
            @endif
        </div>
    </div>

    <div class="card-body py-3">
        {{-- Validation Error Alert --}}
        @if (!empty($filterData['validation_error']))
            <div class="alert alert-danger alert-dismissible fade show py-2 mb-3" role="alert">
                <i class="ik ik-alert-circle mr-1"></i> <strong>{{ __('Filter Error:') }}</strong> {{ $filterData['validation_error'] }}
                <button type="button" class="close py-2" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        {{-- Advanced Date Filter Section (Always Visible - 12 Columns Full Width) --}}
        <form method="GET" action="{{ $currentBaseUrl }}" class="admin-history-advanced-form" id="advancedDateFilterForm">
            <input type="hidden" name="filter_mode" value="advanced">

            <div class="form-row align-items-end">
                @if ($hasDateOptions)
                    {{-- Pages WITH Date Type Option (e.g. Payment History / Cancelled Requests / Member Details) --}}
                    <div class="col-12 col-sm-6 col-md-2 col-lg-2 mb-2">
                        <label for="adv_date_field">{{ __('Date Type') }}</label>
                        <select class="form-control" id="adv_date_field" name="date_field">
                            @foreach ($filterData['date_options'] as $colKey => $colLabel)
                                <option value="{{ $colKey }}" {{ ($filterData['date_field'] ?? '') == $colKey ? 'selected' : '' }}>
                                    {{ __($colLabel) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Quick Period Preset --}}
                    <div class="col-12 col-sm-6 col-md-3 col-lg-3 mb-2">
                        <label for="adv_preset">{{ __('Quick Period') }}</label>
                        <select class="form-control" id="adv_preset" name="preset">
                            <option value="" disabled {{ empty($filterData['preset']) ? 'selected' : '' }}>{{ __('-- Select Preset --') }}</option>
                            <option value="today" {{ ($filterData['preset'] ?? '') == 'today' ? 'selected' : '' }}>{{ __('Today') }}</option>
                            <option value="yesterday" {{ ($filterData['preset'] ?? '') == 'yesterday' ? 'selected' : '' }}>{{ __('Yesterday') }}</option>
                            <option value="last_7_days" {{ ($filterData['preset'] ?? '') == 'last_7_days' ? 'selected' : '' }}>{{ __('Last 7 Days') }}</option>
                            <option value="last_30_days" {{ ($filterData['preset'] ?? '') == 'last_30_days' ? 'selected' : '' }}>{{ __('Last 30 Days') }}</option>
                            <option value="this_month" {{ ($filterData['preset'] ?? '') == 'this_month' ? 'selected' : '' }}>{{ __('This Month') }}</option>
                            <option value="last_month" {{ ($filterData['preset'] ?? '') == 'last_month' ? 'selected' : '' }}>{{ __('Last Month') }}</option>
                            <option value="this_year" {{ ($filterData['preset'] ?? '') == 'this_year' ? 'selected' : '' }}>{{ __('This Year') }}</option>
                            <option value="custom" {{ ($filterData['preset'] ?? '') == 'custom' ? 'selected' : '' }}>{{ __('Custom Range') }}</option>
                        </select>
                    </div>

                    {{-- From Date --}}
                    <div class="col-12 col-sm-6 col-md-2 col-lg-2 mb-2">
                        <label for="adv_from_date">{{ __('From Date') }}</label>
                        <input type="date" class="form-control" id="adv_from_date" name="from_date" 
                            value="{{ !empty($filterData['filter_mode']) && $filterData['filter_mode'] == 'advanced' ? ($filterData['from_date'] ?? '') : '' }}" required>
                    </div>

                    {{-- To Date --}}
                    <div class="col-12 col-sm-6 col-md-2 col-lg-2 mb-2">
                        <label for="adv_to_date">{{ __('To Date') }}</label>
                        <input type="date" class="form-control" id="adv_to_date" name="to_date" 
                            value="{{ !empty($filterData['filter_mode']) && $filterData['filter_mode'] == 'advanced' ? ($filterData['to_date'] ?? '') : '' }}" required>
                    </div>

                    {{-- Buttons (Fills remaining 3 columns = 12 total) --}}
                    <div class="col-12 col-sm-6 col-md-3 col-lg-3 mb-2">
                        <div class="admin-history-filter-actions">
                            <button type="submit" class="btn btn-primary admin-filter-btn flex-grow-1 w-100">
                                <i class="ik ik-filter mr-1"></i>{{ __('Filter') }}
                            </button>
                            @if (!empty($filterData['active_filter']))
                                <a href="{{ $currentBaseUrl }}" class="btn btn-secondary border admin-filter-btn ml-2" style="flex-shrink: 0;">
                                    <i class="ik ik-x mr-1"></i>{{ __('Clear') }}
                                </a>
                            @endif
                        </div>
                    </div>
                @else
                    {{-- Standard Pages without Date Type (4 Columns Grid = 12 Cols Total) --}}
                    {{-- Quick Period Preset (4 Cols on lg, 3 Cols on md) --}}
                    <div class="col-12 col-sm-6 col-md-3 col-lg-4 mb-2">
                        <label for="adv_preset">{{ __('Quick Period') }}</label>
                        <select class="form-control" id="adv_preset" name="preset">
                            <option value="" disabled {{ empty($filterData['preset']) ? 'selected' : '' }}>{{ __('-- Select Preset --') }}</option>
                            <option value="today" {{ ($filterData['preset'] ?? '') == 'today' ? 'selected' : '' }}>{{ __('Today') }}</option>
                            <option value="yesterday" {{ ($filterData['preset'] ?? '') == 'yesterday' ? 'selected' : '' }}>{{ __('Yesterday') }}</option>
                            <option value="last_7_days" {{ ($filterData['preset'] ?? '') == 'last_7_days' ? 'selected' : '' }}>{{ __('Last 7 Days') }}</option>
                            <option value="last_30_days" {{ ($filterData['preset'] ?? '') == 'last_30_days' ? 'selected' : '' }}>{{ __('Last 30 Days') }}</option>
                            <option value="this_month" {{ ($filterData['preset'] ?? '') == 'this_month' ? 'selected' : '' }}>{{ __('This Month') }}</option>
                            <option value="last_month" {{ ($filterData['preset'] ?? '') == 'last_month' ? 'selected' : '' }}>{{ __('Last Month') }}</option>
                            <option value="this_year" {{ ($filterData['preset'] ?? '') == 'this_year' ? 'selected' : '' }}>{{ __('This Year') }}</option>
                            <option value="custom" {{ ($filterData['preset'] ?? '') == 'custom' ? 'selected' : '' }}>{{ __('Custom Range') }}</option>
                        </select>
                    </div>

                    {{-- From Date (3 Cols on lg, 3 Cols on md) --}}
                    <div class="col-12 col-sm-6 col-md-3 col-lg-3 mb-2">
                        <label for="adv_from_date">{{ __('From Date') }}</label>
                        <input type="date" class="form-control" id="adv_from_date" name="from_date" 
                            value="{{ !empty($filterData['filter_mode']) && $filterData['filter_mode'] == 'advanced' ? ($filterData['from_date'] ?? '') : '' }}" required>
                    </div>

                    {{-- To Date (3 Cols on lg, 3 Cols on md) --}}
                    <div class="col-12 col-sm-6 col-md-3 col-lg-3 mb-2">
                        <label for="adv_to_date">{{ __('To Date') }}</label>
                        <input type="date" class="form-control" id="adv_to_date" name="to_date" 
                            value="{{ !empty($filterData['filter_mode']) && $filterData['filter_mode'] == 'advanced' ? ($filterData['to_date'] ?? '') : '' }}" required>
                    </div>

                    {{-- Buttons (Fills remaining 2 columns on lg, 3 columns on md = 12 total) --}}
                    <div class="col-12 col-sm-6 col-md-3 col-lg-2 mb-2">
                        <div class="admin-history-filter-actions">
                            <button type="submit" class="btn btn-primary admin-filter-btn flex-grow-1 w-100">
                                <i class="ik ik-filter mr-1"></i>{{ __('Filter') }}
                            </button>
                            @if (!empty($filterData['active_filter']))
                                <a href="{{ $currentBaseUrl }}" class="btn btn-secondary border admin-filter-btn ml-2" style="flex-shrink: 0;">
                                    <i class="ik ik-x mr-1"></i>{{ __('Clear') }}
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </form>
    </div>
</div>

<script>
    (function() {
        function formatDate(d) {
            var year = d.getFullYear();
            var month = ('0' + (d.getMonth() + 1)).slice(-2);
            var day = ('0' + d.getDate()).slice(-2);
            return year + '-' + month + '-' + day;
        }

        function calculatePresetDates(preset) {
            var today = new Date();
            var from = new Date();
            var to = new Date();

            switch (preset) {
                case 'today':
                    from = today;
                    to = today;
                    break;
                case 'yesterday':
                    from.setDate(today.getDate() - 1);
                    to.setDate(today.getDate() - 1);
                    break;
                case 'last_7_days':
                    from.setDate(today.getDate() - 6);
                    to = today;
                    break;
                case 'last_30_days':
                    from.setDate(today.getDate() - 29);
                    to = today;
                    break;
                case 'this_month':
                    from = new Date(today.getFullYear(), today.getMonth(), 1);
                    to = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                    break;
                case 'last_month':
                    from = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                    to = new Date(today.getFullYear(), today.getMonth(), 0);
                    break;
                case 'this_year':
                    from = new Date(today.getFullYear(), 0, 1);
                    to = new Date(today.getFullYear(), 11, 31);
                    break;
                default:
                    return null;
            }

            return {
                from: formatDate(from),
                to: formatDate(to)
            };
        }

        document.addEventListener('DOMContentLoaded', function() {
            var presetSelect = document.getElementById('adv_preset');
            var fromInput = document.getElementById('adv_from_date');
            var toInput = document.getElementById('adv_to_date');

            if (presetSelect && fromInput && toInput) {
                presetSelect.addEventListener('change', function() {
                    var val = this.value;
                    if (val === 'custom') {
                        return;
                    }
                    var dates = calculatePresetDates(val);
                    if (dates) {
                        fromInput.value = dates.from;
                        toInput.value = dates.to;
                    }
                });

                function handleDateInputManualChange() {
                    if (presetSelect.value !== 'custom') {
                        presetSelect.value = 'custom';
                    }
                }

                fromInput.addEventListener('change', handleDateInputManualChange);
                toInput.addEventListener('change', handleDateInputManualChange);
            }
        });
    })();
</script>
