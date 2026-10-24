@extends('admin.layouts.main')
@section('title', 'AI Assistant')
@section('content')
    <style>
        /* Scoped Admin AI Chat Styles adhering strictly to ThemeKit Admin theme */
        .admin-ai-chat {
            font-family: 'Nunito Sans', sans-serif;
            color: #334155;
        }

        .admin-ai-chat__card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            background: #ffffff;
            height: calc(100vh - 220px);
            min-height: 560px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .admin-ai-chat__header {
            padding: 16px 20px;
            border-bottom: 1px solid #edf2f8;
            background: #ffffff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }

        .admin-ai-chat__title {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
        }

        .admin-ai-chat__subtitle {
            margin: 2px 0 0;
            font-size: 13px;
            color: #64748b;
        }

        .admin-ai-chat__body {
            flex: 1;
            display: flex;
            overflow: hidden;
        }

        /* Preset Reports Sidebar */
        .admin-ai-chat__presets {
            width: 270px;
            border-right: 1px solid #edf2f8;
            background: #f8fafc;
            padding: 16px 14px;
            display: flex;
            flex-direction: column;
            gap: 9px;
            overflow-y: auto;
            flex-shrink: 0;
        }

        .admin-ai-chat__presets-title {
            font-size: 11px;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 6px;
            padding-left: 4px;
        }

        .admin-ai-chat__preset-item {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            text-align: left;
            width: 100%;
            outline: none;
        }

        .admin-ai-chat__preset-item i {
            font-size: 16px;
            color: #007bff;
            flex-shrink: 0;
        }

        .admin-ai-chat__preset-item:hover {
            border-color: #007bff;
            background: #f0f7ff;
            color: #007bff;
            transform: translateX(2px);
        }

        /* Main Chat Area */
        .admin-ai-chat__main {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 16px 20px;
            background: #ffffff;
            overflow: hidden;
        }

        .admin-ai-chat__history {
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 14px;
            padding-right: 8px;
            margin-bottom: 14px;
        }

        /* Webkit Scrollbars */
        .admin-ai-chat__history::-webkit-scrollbar,
        .admin-ai-chat__presets::-webkit-scrollbar {
            width: 6px;
        }
        .admin-ai-chat__history::-webkit-scrollbar-track,
        .admin-ai-chat__presets::-webkit-scrollbar-track {
            background: transparent;
        }
        .admin-ai-chat__history::-webkit-scrollbar-thumb,
        .admin-ai-chat__presets::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 99px;
        }
        .admin-ai-chat__history::-webkit-scrollbar-thumb:hover,
        .admin-ai-chat__presets::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Speech Bubbles */
        .admin-ai-chat__bubble {
            max-width: 82%;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13.5px;
            line-height: 1.6;
            animation: bubbleFadeIn 0.2s ease forwards;
            word-wrap: break-word;
        }

        @keyframes bubbleFadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .admin-ai-chat__bubble--ai {
            align-self: flex-start;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #1e293b;
            border-top-left-radius: 2px;
        }

        .admin-ai-chat__bubble--user {
            align-self: flex-end;
            background: #007bff;
            color: #ffffff;
            border-top-right-radius: 2px;
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.25);
        }

        .admin-ai-chat__bubble-sender {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #007bff;
            margin-bottom: 3px;
        }

        .admin-ai-chat__bubble--user .admin-ai-chat__bubble-sender {
            color: #e0f2fe;
            text-align: right;
        }

        .admin-ai-chat__bubble-body h6 {
            color: #0f172a;
            font-size: 14px;
            font-weight: 700;
            margin: 8px 0 4px;
        }

        .admin-ai-chat__bubble-body ul {
            margin: 4px 0 4px 18px;
            padding: 0;
        }

        .admin-ai-chat__bubble-body li {
            margin-bottom: 3px;
        }

        .admin-ai-chat__bubble--ai strong {
            color: #0f172a;
        }

        .admin-ai-chat__bubble--user strong {
            color: #ffffff;
        }

        /* Input Form Container */
        .admin-ai-chat__form {
            flex-shrink: 0;
        }

        .admin-ai-chat__input-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 6px 8px;
            background: #ffffff;
            transition: all 0.2s ease;
        }

        .admin-ai-chat__input-wrapper:focus-within {
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.15);
        }

        .admin-ai-chat__input {
            flex: 1;
            border: 0;
            outline: none;
            padding: 6px 10px;
            font-size: 13.5px;
            color: #1e293b;
            background: transparent;
        }

        .admin-ai-chat__input::placeholder {
            color: #94a3b8;
        }

        .admin-ai-chat__submit-btn {
            background: #007bff;
            color: #ffffff;
            border: 0;
            border-radius: 8px;
            padding: 8px 18px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
        }

        .admin-ai-chat__submit-btn:hover:not(:disabled) {
            background: #006fe6;
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.25);
        }

        .admin-ai-chat__submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Typing Dots Animation */
        .admin-ai-chat__loading {
            display: inline-flex;
            gap: 4px;
            align-items: center;
            padding: 6px 4px;
        }

        .admin-ai-chat__loading-dot {
            width: 6px;
            height: 6px;
            background-color: #64748b;
            border-radius: 50%;
            animation: adminDotWave 1.2s infinite ease-in-out;
        }

        .admin-ai-chat__loading-dot:nth-child(2) { animation-delay: 0.2s; }
        .admin-ai-chat__loading-dot:nth-child(3) { animation-delay: 0.4s; }

        @keyframes adminDotWave {
            0%, 100% { transform: translateY(0); opacity: 0.4; }
            50% { transform: translateY(-5px); opacity: 1; }
        }

        /* Responsive Breakpoints */
        @media (max-width: 991px) {
            .admin-ai-chat__presets {
                width: 220px;
            }
        }

        @media (max-width: 768px) {
            .admin-ai-chat__card {
                height: calc(100vh - 180px);
            }
            .admin-ai-chat__body {
                flex-direction: column;
            }
            .admin-ai-chat__presets {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid #edf2f8;
                padding: 10px 14px;
                flex-direction: row;
                overflow-x: auto;
                gap: 8px;
            }
            .admin-ai-chat__preset-item {
                white-space: nowrap;
                padding: 6px 10px;
                font-size: 12px;
                width: auto;
            }
            .admin-ai-chat__presets-title {
                display: none;
            }
            .admin-ai-chat__bubble {
                max-width: 92%;
            }
        }
    </style>

    <div class="container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="ik ik-cpu bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('AI Assistant') }}</h5>
                            <span>{{ __('Administrative Business Reports & Analytics') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <nav class="breadcrumb-container" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ url('admin/dashboard') }}"><i class="ik ik-home"></i></a>
                            </li>
                            <li class="breadcrumb-item"><a href="#">{{ __('Admin') }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('AI Assistant') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Chat Container -->
        <div class="admin-ai-chat">
            <div class="card admin-ai-chat__card mb-0">
                <!-- Card Header -->
                <div class="admin-ai-chat__header">
                    <div>
                        <h4 class="admin-ai-chat__title">
                            <i class="ik ik-cpu mr-2 text-primary"></i>{{ __('Admin AI Assistant') }}
                        </h4>
                        <p class="admin-ai-chat__subtitle">
                            {{ __('Ask safe read-only business reports, member statistics, and system workflows.') }}
                        </p>
                    </div>
                    <div>
                        <span class="badge badge-success px-3 py-2 font-weight-bold" style="border-radius: 99px;">
                            <i class="ik ik-shield mr-1"></i>{{ __('Admin Access Only') }}
                        </span>
                    </div>
                </div>

                <!-- Card Body -->
                <div class="admin-ai-chat__body">
                    <!-- Report Presets Sidebar -->
                    <div class="admin-ai-chat__presets">
                        <div class="admin-ai-chat__presets-title">{{ __('Report Presets') }}</div>

                        <button type="button" class="admin-ai-chat__preset-item" onclick="sendPreset('Explain system details and summaries')">
                            <i class="ik ik-bar-chart-2"></i>
                            <span>{{ __('System Summary') }}</span>
                        </button>
                        <button type="button" class="admin-ai-chat__preset-item" onclick="sendPreset('Show total members statistics and counts report')">
                            <i class="ik ik-users"></i>
                            <span>{{ __('Members Report') }}</span>
                        </button>
                        <button type="button" class="admin-ai-chat__preset-item" onclick="sendPreset('Show total withdrawals reports and gross/net summaries')">
                            <i class="ik ik-credit-card"></i>
                            <span>{{ __('Withdrawals Report') }}</span>
                        </button>
                        <button type="button" class="admin-ai-chat__preset-item" onclick="sendPreset('Show income distribution breakdown report')">
                            <i class="ik ik-database"></i>
                            <span>{{ __('Incomes Report') }}</span>
                        </button>
                        <button type="button" class="admin-ai-chat__preset-item" onclick="sendPreset('Show total wallet balances and transfer statistics')">
                            <i class="ik ik-briefcase"></i>
                            <span>{{ __('Wallets Report') }}</span>
                        </button>
                        <button type="button" class="admin-ai-chat__preset-item" onclick="sendPreset('Show support tickets and resolution summary')">
                            <i class="ik ik-help-circle"></i>
                            <span>{{ __('Support Tickets') }}</span>
                        </button>
                        <button type="button" class="admin-ai-chat__preset-item" onclick="sendPreset('Show staking and investment packages report')">
                            <i class="ik ik-box"></i>
                            <span>{{ __('Staking & Packages') }}</span>
                        </button>
                    </div>

                    <!-- Main Chat Panel -->
                    <div class="admin-ai-chat__main">
                        <!-- Message History -->
                        <div class="admin-ai-chat__history" id="adminHistory" role="log" aria-live="polite">
                            <div class="admin-ai-chat__bubble admin-ai-chat__bubble--ai">
                                <div class="admin-ai-chat__bubble-sender">{{ __('AI Assistant') }}</div>
                                <div class="admin-ai-chat__bubble-body">
                                    {{ __('Welcome,') }} <strong>{{ __('Admin') }}</strong>! {{ __('I am your read-only system and analytics assistant.') }}<br>
                                    {{ __('I can generate high-level summaries of') }} <strong>{{ __('members, wallet balances, withdrawals, incomes, support tickets') }}</strong>, {{ __('and individual member reports.') }}
                                </div>
                            </div>
                        </div>

                        <!-- Input Form -->
                        <form id="adminForm" class="admin-ai-chat__form">
                            @csrf
                            <div class="admin-ai-chat__input-wrapper">
                                <input 
                                    type="text" 
                                    class="admin-ai-chat__input" 
                                    id="adminInput" 
                                    placeholder="{{ __('Ask for safe reports, member counts, or specific member IDs (e.g. QRB123456)...') }}" 
                                    autocomplete="off">
                                <button type="submit" class="admin-ai-chat__submit-btn" id="adminSend">
                                    <span>{{ __('Send') }}</span>
                                    <i class="ik ik-send"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Interactive Chat Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof jQuery === 'undefined') {
                return;
            }

            var $ = jQuery;
            var $adminHistory = $('#adminHistory');
            var $adminInput = $('#adminInput');
            var $adminSend = $('#adminSend');
            var $adminForm = $('#adminForm');
            var postEndpoint = "{{ route('admin.ai.chat.send') }}";
            var lastSubmittedQuestion = '';

            // Safe HTML escape helper
            function escapeHtml(str) {
                var div = document.createElement('div');
                div.textContent = str;
                return div.innerHTML;
            }

            // Safe Markdown Formatter
            function formatMessage(rawText) {
                var escaped = escapeHtml(rawText);

                // Format markdown headers
                escaped = escaped.replace(/^###\s+(.*?)$/gm, '<h6>$1</h6>');
                escaped = escaped.replace(/^##\s+(.*?)$/gm, '<h6>$1</h6>');
                escaped = escaped.replace(/^#\s+(.*?)$/gm, '<h6>$1</h6>');

                // Format markdown bold (**text**)
                escaped = escaped.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

                // Format bullet points (- item)
                escaped = escaped.replace(/(?:^|\n)-\s+(.*?)(?=(?:\n|$))/g, '<br>&bull; $1');

                // Format standard line breaks
                escaped = escaped.replace(/\n/g, '<br>');

                // Clean consecutive <br>
                escaped = escaped.replace(/(<br>){3,}/g, '<br><br>');

                return escaped;
            }

            // Append message bubble
            function appendAdminMessage(text, isUser) {
                var senderLabel = isUser ? 'Admin' : 'AI Assistant';
                var bubbleClass = isUser ? 'admin-ai-chat__bubble--user' : 'admin-ai-chat__bubble--ai';
                var formattedBody = isUser ? escapeHtml(text).replace(/\n/g, '<br>') : formatMessage(text);

                var html = 
                    '<div class="admin-ai-chat__bubble ' + bubbleClass + '">' +
                        '<div class="admin-ai-chat__bubble-sender">' + senderLabel + '</div>' +
                        '<div class="admin-ai-chat__bubble-body">' + formattedBody + '</div>' +
                    '</div>';

                $adminHistory.append(html);
                $adminHistory.scrollTop($adminHistory[0].scrollHeight);
            }

            // Show typing indicator
            function showAdminLoading() {
                var loadingHtml = 
                    '<div class="admin-ai-chat__bubble admin-ai-chat__bubble--ai" id="adminLoadingBubble">' +
                        '<div class="admin-ai-chat__bubble-sender">AI Assistant</div>' +
                        '<div class="admin-ai-chat__bubble-body">' +
                            '<div class="admin-ai-chat__loading">' +
                                '<span class="admin-ai-chat__loading-dot"></span>' +
                                '<span class="admin-ai-chat__loading-dot"></span>' +
                                '<span class="admin-ai-chat__loading-dot"></span>' +
                            '</div>' +
                        '</div>' +
                    '</div>';

                $adminHistory.append(loadingHtml);
                $adminHistory.scrollTop($adminHistory[0].scrollHeight);
            }

            // Remove typing indicator
            function hideAdminLoading() {
                $('#adminLoadingBubble').remove();
            }

            // Preset queries click callback
            window.sendPreset = function(text) {
                $adminInput.val(text);
                $adminForm.submit();
            };

            // Form submission handler
            $adminForm.on('submit', function(e) {
                e.preventDefault();

                var message = $.trim($adminInput.val());
                if (!message) {
                    return;
                }

                lastSubmittedQuestion = message;

                // Lock inputs
                $adminInput.val('');
                $adminInput.prop('disabled', true);
                $adminSend.prop('disabled', true);

                // Append user bubble and spinner
                appendAdminMessage(message, true);
                showAdminLoading();

                // Dispatch AJAX request to Phase 2 Admin POST endpoint
                $.ajax({
                    url: postEndpoint,
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                    },
                    data: {
                        message: message
                    },
                    success: function(response) {
                        hideAdminLoading();

                        var answer = (response && (response.answer || response.reply))
                            ? (response.answer || response.reply)
                            : "Unable to retrieve the safe report requested. Please verify your query matches allowed read-only system actions.";

                        appendAdminMessage(answer, false);
                    },
                    error: function(xhr) {
                        hideAdminLoading();

                        var errorMsg = "An error occurred while communicating with the assistant server. Please try again.";

                        if (xhr.status === 429) {
                            errorMsg = "Rate limit reached. Please wait 1 minute before sending more queries.";
                        } else if (xhr.status === 401 || xhr.status === 403) {
                            errorMsg = "Admin session unauthenticated or expired. Please refresh or log in again.";
                        } else if (xhr.status === 419) {
                            errorMsg = "CSRF security token expired. Please refresh the page.";
                        } else if (xhr.status === 422) {
                            var json = xhr.responseJSON;
                            if (json && json.errors && json.errors.message) {
                                errorMsg = "Validation error: " + json.errors.message[0];
                            } else {
                                errorMsg = "Invalid input parameters.";
                            }
                        }

                        appendAdminMessage(errorMsg, false);

                        // Retain question in input box for convenient retry
                        if (lastSubmittedQuestion) {
                            $adminInput.val(lastSubmittedQuestion);
                        }
                    },
                    complete: function() {
                        $adminInput.prop('disabled', false);
                        $adminSend.prop('disabled', false);
                        $adminInput.focus();
                    }
                });
            });
        });
    </script>
@endsection
