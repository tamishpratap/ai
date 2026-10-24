@extends('member.layouts.main')
@section('title', 'AI Assistant')
@section('container')
    @php
        $memberName = getName($data['memberid']) ?: ($data['name'] ?? $data['memberid']);
    @endphp

    <style>
        /* Scoped Chat AI styles adhering strictly to aidemo dark luxury theme */
        .member-ai-chat {
            font-family: "Manrope", sans-serif;
            color: var(--member-text, #f6f1e8);
            padding: 10px 0 24px;
        }

        .member-ai-chat__shell {
            position: relative;
            background: var(--member-surface, rgba(14, 18, 24, 0.86));
            border: 1px solid var(--member-border, rgba(205, 176, 106, 0.18));
            border-radius: 20px;
            padding: 22px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.45);
            backdrop-filter: blur(18px);
            display: flex;
            flex-direction: column;
            height: calc(100vh - 190px);
            min-height: 560px;
            overflow: hidden;
        }

        .member-ai-chat__header {
            border-bottom: 1px solid var(--member-border, rgba(205, 176, 106, 0.18));
            padding-bottom: 14px;
            margin-bottom: 14px;
            flex-shrink: 0;
        }

        .member-ai-chat__eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--member-primary, #d9b36b);
            margin-bottom: 4px;
        }

        .member-ai-chat__live-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #22c55e;
            box-shadow: 0 0 10px rgba(34, 197, 94, 0.6);
            display: inline-block;
        }

        .member-ai-chat__title {
            font-family: "Space Grotesk", sans-serif;
            font-size: 22px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 3px;
            letter-spacing: -0.02em;
        }

        .member-ai-chat__subtitle {
            color: var(--member-text-soft, #aaafb9);
            font-size: 13px;
        }

        .member-ai-chat__badge {
            display: inline-flex;
            align-items: center;
            padding: 5px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            background: rgba(217, 179, 107, 0.1);
            border: 1px solid rgba(217, 179, 107, 0.25);
            color: var(--member-secondary, #f3dfb0);
        }

        /* Scrollable Message History Area */
        .member-ai-chat__history {
            flex: 1;
            overflow-y: auto;
            padding: 12px 6px;
            margin-bottom: 12px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .member-ai-chat__history::-webkit-scrollbar {
            width: 6px;
        }
        .member-ai-chat__history::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.02);
            border-radius: 999px;
        }
        .member-ai-chat__history::-webkit-scrollbar-thumb {
            background: rgba(217, 179, 107, 0.35);
            border-radius: 999px;
        }
        .member-ai-chat__history::-webkit-scrollbar-thumb:hover {
            background: rgba(217, 179, 107, 0.6);
        }

        /* Message Bubbles */
        .member-ai-chat__bubble {
            max-width: 82%;
            padding: 14px 18px;
            border-radius: 16px;
            font-size: 13.5px;
            line-height: 1.6;
            animation: bubbleSlideIn 0.25s ease-out forwards;
            position: relative;
            word-wrap: break-word;
        }

        @keyframes bubbleSlideIn {
            from {
                opacity: 0;
                transform: translateY(6px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .member-ai-chat__bubble--ai {
            align-self: flex-start;
            background: rgba(20, 26, 36, 0.94);
            border: 1px solid var(--member-border, rgba(205, 176, 106, 0.18));
            color: var(--member-text, #f6f1e8);
            border-top-left-radius: 4px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
        }

        .member-ai-chat__bubble--user {
            align-self: flex-end;
            background: linear-gradient(135deg, #1b2a40 0%, #0d1e33 100%);
            border: 1px solid rgba(122, 162, 255, 0.35);
            color: #ffffff;
            border-top-right-radius: 4px;
            box-shadow: 0 4px 16px rgba(13, 30, 51, 0.4);
        }

        .member-ai-chat__bubble-sender {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--member-primary, #d9b36b);
            margin-bottom: 4px;
        }

        .member-ai-chat__bubble--user .member-ai-chat__bubble-sender {
            color: #9cbaff;
            text-align: right;
        }

        .member-ai-chat__bubble-body h6 {
            color: var(--member-secondary, #f3dfb0);
            font-size: 14px;
            font-weight: 700;
            margin: 10px 0 6px;
        }

        .member-ai-chat__bubble-body ul {
            margin: 6px 0 6px 18px;
            padding: 0;
        }

        .member-ai-chat__bubble-body li {
            margin-bottom: 4px;
        }

        .member-ai-chat__bubble-body strong {
            color: #ffffff;
        }

        /* Suggestion Chips Box */
        .member-ai-chat__suggestions {
            margin-bottom: 12px;
            flex-shrink: 0;
        }

        .member-ai-chat__suggestions-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--member-text-soft, #aaafb9);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }

        .member-ai-chat__suggestions-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .member-ai-chat__chip {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(217, 179, 107, 0.22);
            color: var(--member-secondary, #f3dfb0);
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            outline: none;
        }

        .member-ai-chat__chip:hover {
            background: rgba(217, 179, 107, 0.12);
            border-color: var(--member-primary, #d9b36b);
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(217, 179, 107, 0.18);
        }

        /* Input Form Area */
        .member-ai-chat__form {
            flex-shrink: 0;
        }

        .member-ai-chat__input-group {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(10, 14, 20, 0.95);
            border: 1px solid rgba(205, 176, 106, 0.28);
            border-radius: 14px;
            padding: 6px 10px;
            transition: all 0.2s ease;
        }

        .member-ai-chat__input-group:focus-within {
            border-color: var(--member-primary, #d9b36b);
            box-shadow: 0 0 16px rgba(217, 179, 107, 0.22);
        }

        .member-ai-chat__input {
            flex: 1;
            background: transparent;
            border: none;
            outline: none;
            color: #ffffff;
            font-size: 13.5px;
            padding: 8px 10px;
            resize: none;
            max-height: 120px;
            line-height: 1.5;
        }

        .member-ai-chat__input::placeholder {
            color: rgba(170, 175, 185, 0.6);
        }

        .member-ai-chat__submit-btn {
            background: linear-gradient(135deg, #d9b36b 0%, #b88d40 100%);
            color: #07090d;
            border: none;
            border-radius: 10px;
            padding: 9px 18px;
            font-family: "Space Grotesk", sans-serif;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.02em;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
        }

        .member-ai-chat__submit-btn:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(217, 179, 107, 0.35);
        }

        .member-ai-chat__submit-btn:disabled {
            opacity: 0.55;
            cursor: not-allowed;
        }

        /* Typing Dots Animation */
        .member-ai-chat__loading {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 4px;
        }

        .member-ai-chat__loading-dot {
            width: 7px;
            height: 7px;
            background-color: var(--member-primary, #d9b36b);
            border-radius: 50%;
            animation: dotPulse 1.2s infinite ease-in-out;
        }

        .member-ai-chat__loading-dot:nth-child(2) { animation-delay: 0.2s; }
        .member-ai-chat__loading-dot:nth-child(3) { animation-delay: 0.4s; }

        @keyframes dotPulse {
            0%, 100% { transform: translateY(0); opacity: 0.3; }
            50% { transform: translateY(-5px); opacity: 1; }
        }

        /* Mobile Adjustments */
        @media (max-width: 768px) {
            .member-ai-chat__shell {
                height: calc(100vh - 160px);
                padding: 14px;
                border-radius: 14px;
            }
            .member-ai-chat__bubble {
                max-width: 92%;
                font-size: 13px;
                padding: 10px 14px;
            }
            .member-ai-chat__title {
                font-size: 18px;
            }
        }
    </style>

    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="member-ai-chat">
                <div class="member-ai-chat__shell">
                    <!-- Header -->
                    <div class="member-ai-chat__header d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div>
                            <div class="member-ai-chat__eyebrow">
                                <span class="member-ai-chat__live-dot"></span>
                                <span>AI Assistant</span>
                            </div>
                            <h3 class="member-ai-chat__title">Personal Account AI</h3>
                            <p class="member-ai-chat__subtitle mb-0">Ask questions about your wallet balance, incomes, downline team, investments, and account status.</p>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="member-ai-chat__badge">
                                <i class="ri-shield-check-line me-1 text-success"></i>
                                <span>Active: {{ $data['memberid'] }}</span>
                            </span>
                        </div>
                    </div>

                    <!-- Conversation History Area -->
                    <div class="member-ai-chat__history" id="chatHistory" role="log" aria-live="polite">
                        <!-- Initial Greeting -->
                        <div class="member-ai-chat__bubble member-ai-chat__bubble--ai">
                            <div class="member-ai-chat__bubble-sender">AI Assistant</div>
                            <div class="member-ai-chat__bubble-body">
                                Namaste, <strong>{{ $memberName }}</strong>! Main aapka personal account assistant hoon.<br>
                                Main aapki <strong>Wallet Balance, Incomes, Downline count, aur Investments</strong> se related queries ka safe, direct database reports ke basis par reply kar sakta hoon.
                            </div>
                        </div>
                    </div>

                    <!-- Suggestion Chips -->
                    <div class="member-ai-chat__suggestions">
                        <div class="member-ai-chat__suggestions-title">
                            <i class="ri-flashlight-line me-1" style="color: var(--member-primary, #d9b36b);"></i>
                            <span>Quick Queries</span>
                        </div>
                        <div class="member-ai-chat__suggestions-list">
                            <button type="button" class="member-ai-chat__chip" onclick="sendSuggestion('Mera current wallet balance dikhao')">
                                Wallet Balance?
                            </button>
                            <button type="button" class="member-ai-chat__chip" onclick="sendSuggestion('Mujhe aaj total kitni income mili?')">
                                Aaj ki Income?
                            </button>
                            <button type="button" class="member-ai-chat__chip" onclick="sendSuggestion('Kal ka wallet credit kitna tha?')">
                                Kal ka Credit?
                            </button>
                            <button type="button" class="member-ai-chat__chip" onclick="sendSuggestion('Mera total downline team count kya hai?')">
                                Team Downline?
                            </button>
                            <button type="button" class="member-ai-chat__chip" onclick="sendSuggestion('Mera active investment package and business kya hai?')">
                                Investment Status?
                            </button>
                            <button type="button" class="member-ai-chat__chip" onclick="sendSuggestion('Mera account summary and profile dikhao')">
                                Profile Summary?
                            </button>
                        </div>
                    </div>

                    <!-- Input Form -->
                    <form id="chatForm" class="member-ai-chat__form">
                        @csrf
                        <div class="member-ai-chat__input-group">
                            <input 
                                type="text" 
                                class="member-ai-chat__input" 
                                id="chatInput" 
                                placeholder="Apne wallet, income, team ya account details se related sawal poochhein..." 
                                autocomplete="off">
                            <button type="submit" class="member-ai-chat__submit-btn" id="sendBtn">
                                <span>Send</span>
                                <i class="ri-send-plane-fill ms-1"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Ensure jQuery is available and hook up chat interaction -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof jQuery === 'undefined') {
                return;
            }

            var $ = jQuery;
            var $chatHistory = $('#chatHistory');
            var $chatInput = $('#chatInput');
            var $sendBtn = $('#sendBtn');
            var $chatForm = $('#chatForm');
            var postEndpoint = "{{ route('member.ai.chat.send') }}";
            var lastSubmittedQuestion = '';

            // Safe HTML escape helper
            function escapeHtml(str) {
                var div = document.createElement('div');
                div.textContent = str;
                return div.innerHTML;
            }

            // Safe Markdown Formatter (headers, bold, lists, newlines)
            function formatMessage(rawText) {
                var escaped = escapeHtml(rawText);

                // Format markdown headers (e.g. ### Header)
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

            // Append a message bubble to the chat history
            function appendBubble(text, isUser) {
                var senderLabel = isUser ? 'You' : 'AI Assistant';
                var bubbleClass = isUser ? 'member-ai-chat__bubble--user' : 'member-ai-chat__bubble--ai';
                var formattedBody = isUser ? escapeHtml(text).replace(/\n/g, '<br>') : formatMessage(text);

                var html = 
                    '<div class="member-ai-chat__bubble ' + bubbleClass + '">' +
                        '<div class="member-ai-chat__bubble-sender">' + senderLabel + '</div>' +
                        '<div class="member-ai-chat__bubble-body">' + formattedBody + '</div>' +
                    '</div>';

                $chatHistory.append(html);
                $chatHistory.scrollTop($chatHistory[0].scrollHeight);
            }

            // Show typing indicator
            function showLoading() {
                var loadingHtml = 
                    '<div class="member-ai-chat__bubble member-ai-chat__bubble--ai" id="loadingBubble">' +
                        '<div class="member-ai-chat__bubble-sender">AI Assistant</div>' +
                        '<div class="member-ai-chat__bubble-body">' +
                            '<div class="member-ai-chat__loading">' +
                                '<span class="member-ai-chat__loading-dot"></span>' +
                                '<span class="member-ai-chat__loading-dot"></span>' +
                                '<span class="member-ai-chat__loading-dot"></span>' +
                            '</div>' +
                        '</div>' +
                    '</div>';

                $chatHistory.append(loadingHtml);
                $chatHistory.scrollTop($chatHistory[0].scrollHeight);
            }

            // Remove typing indicator
            function hideLoading() {
                $('#loadingBubble').remove();
            }

            // Global suggestion chip handler
            window.sendSuggestion = function(text) {
                $chatInput.val(text);
                $chatForm.submit();
            };

            // Submit handler
            $chatForm.on('submit', function(e) {
                e.preventDefault();

                var message = $.trim($chatInput.val());
                if (!message) {
                    return;
                }

                lastSubmittedQuestion = message;

                // Reset and lock inputs
                $chatInput.val('');
                $chatInput.prop('disabled', true);
                $sendBtn.prop('disabled', true);

                // Add user message to UI
                appendBubble(message, true);
                showLoading();

                // Send AJAX request to existing Member POST endpoint
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
                        hideLoading();

                        var answer = (response && (response.answer || response.reply)) 
                            ? (response.answer || response.reply)
                            : "Kripya apne wallet, income, downline ya investment se related sawal poochhein.";

                        appendBubble(answer, false);
                    },
                    error: function(xhr) {
                        hideLoading();

                        var errorMsg = "Kripya koshish karein, server par kuch takniki samasya hai.";

                        if (xhr.status === 429) {
                            errorMsg = "Bahut saare requests bheje gaye hain. Kripya 1 minute baad koshish karein.";
                        } else if (xhr.status === 401 || xhr.status === 403) {
                            errorMsg = "Session unauthenticated ya expire ho gaya hai. Kripya login page par jaakar dobara login karein.";
                        } else if (xhr.status === 419) {
                            errorMsg = "Aapka security CSRF token expire ho gaya hai. Kripya page ko refresh karein.";
                        } else if (xhr.status === 422) {
                            var json = xhr.responseJSON;
                            if (json && json.errors && json.errors.message) {
                                errorMsg = "Validation Error: " + json.errors.message[0];
                            } else {
                                errorMsg = "Input validation failed. Kripya sahi sawal likhein.";
                            }
                        }

                        appendBubble(errorMsg, false);

                        // Retain question in input field so user can re-try without re-typing
                        if (lastSubmittedQuestion) {
                            $chatInput.val(lastSubmittedQuestion);
                        }
                    },
                    complete: function() {
                        $chatInput.prop('disabled', false);
                        $sendBtn.prop('disabled', false);
                        $chatInput.focus();
                    }
                });
            });
        });
    </script>
@endsection
