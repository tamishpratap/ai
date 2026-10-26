/**
 * ============================================================================
 * IRA FORM FILLING AI (ira-form-filler.js)
 * ============================================================================
 * Independent, conversational voice form-filling controller.
 * Completely decoupled from Navigation AI (ira-assistant.js).
 *
 * Namespace: window.IRA_FORM_FILLER
 *
 * Key Capabilities:
 * - Dedicated UI state machine & compact inline dock (never opens #iraModal)
 * - Language selector: Hindi (hi-IN), Hinglish (hi-IN), English (en-IN/en-US)
 * - Prominent "Exit Form Fill" control that stops audio and neatly removes dock
 * - Preserves all entered form values without submitting or resetting
 * - Intercepts clicks on .ira-form-fill-btn in CAPTURE phase before ira-assistant.js
 * - Private SpeechRecognition & SpeechSynthesis lifecycle
 * - Step-by-step active field highlighting & smooth scroll focus
 * - Robust dropdown option matching ignoring empty placeholder options
 * - Safe native & jQuery event dispatching for dependent-field limits
 * - Strict zero auto-submission policy (leaves submit button to user)
 * ============================================================================
 */

(function (window, document) {
    'use strict';

    const IRA_FORM_FILLER = {
        // Configuration
        config: {
            formEndpoint: '/ira/form-step',
            formsForPageEndpoint: '/ira/forms-for-page',
            csrfToken: '',
            role: 'guest',
            defaultLocale: 'hi-IN',
            voiceRate: 1.0,
            voicePitch: 1.05,
            silenceThresholdMs: 1800,
            maxListeningDurationMs: 25000
        },

        // Runtime State
        activeFormKey: null,
        activeFormEl: null,
        activeFormTitle: '',
        activeField: null,
        activeFieldEl: null,
        guideState: null,
        currentFields: {},
        status: 'IDLE', // IDLE, INITIALIZING, ASKING, LISTENING, THINKING, SPEAKING, REVIEW, COMPLETED, PAUSED
        selectedLanguage: 'hinglish', // 'hi', 'hinglish', 'en'

        // Speech & Audio
        recognition: null,
        isRecognitionRunning: false,
        isSpeaking: false,
        sessionStartTime: 0,
        sessionInterimTranscript: '',
        sessionFinalTranscript: '',
        hasDispatchedTurn: false,
        silenceTimer: null,
        maxListeningTimer: null,
        lastSpokenPrompt: '',

        // DOM Elements
        dockEl: null,

        // Registered Form Catalog (All 39 Admin & Member Forms)
        formRules: [
            // Member Portal Forms
            { key: 'member_staking', selector: '#stakingForm, form[action*="createStaking"]', title: 'Investment / Staking' },
            { key: 'member_p2p_transfer', selector: 'form[action*="fundTrans"]', title: 'P2P Fund Transfer' },
            { key: 'member_wallet_withdrawal', selector: 'form[action*="initWithdrawal"]', title: 'Main Wallet Withdrawal' },
            { key: 'member_wallet_to_p2p', selector: 'form[action*="walletP2pTransfer"]', title: 'Wallet to P2P Transfer' },
            { key: 'member_fund_import', selector: '#myForm, form[action*="instantFunds"]', title: 'Import / Add Funds' },
            { key: 'member_support_create', selector: 'form[action*="createTicket"]', title: 'Create Support Ticket' },
            { key: 'member_support_reply', selector: 'form[action*="MbReplyTicket"]', title: 'Reply Support Ticket' },
            { key: 'member_profile_user_summary', selector: 'form[action*="insertProfile"]', title: 'Profile Details' },
            { key: 'member_profile_wallet_address', selector: 'form[action*="insertWalletAddress"]', title: 'Wallet Address' },
            { key: 'member_security_txn_password', selector: 'form[action*="txnpassChange"]', title: 'Transaction Password' },
            { key: 'member_security_login_password', selector: 'form[action*="passChange"]', title: 'Login Password' },
            { key: 'member_security_email', selector: 'form[action*="emailChange"]', title: 'Change Account Email' },
            { key: 'member_team_geneology_search', selector: 'form[action*="searchGenelogy"]', title: 'Genealogy Search' },
            { key: 'member_account_activation', selector: 'form[action*="userCreate"], form[action*="activateUser"]', title: 'Account Activation' },
            { key: 'member_account_upgrade', selector: 'form[action*="userCreate"]', title: 'Account Upgrade' },

            // Admin Portal Forms
            { key: 'admin_funds_add', selector: 'form[action*="addMemFunds"]', title: 'Add Member Funds' },
            { key: 'admin_funds_deduct', selector: 'form[action*="deductMemFunds"]', title: 'Deduct Member Funds' },
            { key: 'admin_set_rates', selector: 'form[action*="rateUpdate"]', title: 'USD Exchange Rates' },
            { key: 'admin_free_pin_account', selector: 'form[action*="PinUserAccount"]', title: 'Zero Pin Account' },
            { key: 'admin_dash_messages', selector: 'form[action*="dashMsg"]', title: 'Dashboard Broadcast' },
            { key: 'admin_wallet_address', selector: 'form[action*="updateWallet"]', title: 'Receiving Wallet Address' },
            { key: 'admin_popup_image', selector: 'form[action*="popupImageUpdate"]', title: 'Popup Announcement' },
            { key: 'admin_achievers_image', selector: 'form[action*="acheivImage"]', title: 'Achievers Image' },
            { key: 'admin_withdrawal_accept_comment', selector: 'form[action*="withdrawal.accept"]', title: 'Accept Withdrawal' },
            { key: 'admin_withdrawal_cancel_comment', selector: 'form[action*="withdrawal.cancel"]', title: 'Reject Withdrawal' },
            { key: 'admin_date_filter', selector: '#advancedDateFilterForm', title: 'Date Range Filter' },
            { key: 'admin_support_reply', selector: 'form[action*="Ticket"]', title: 'Support Reply' },
            { key: 'admin_member_update', selector: 'form[action*="updateDetails"]', title: 'Edit Member Details' },
            { key: 'admin_member_security', selector: 'form[action*="securityUpdate"]', title: 'Member Security Reset' },
            { key: 'admin_profile_email', selector: 'form[action*="changeEmail"]', title: 'Admin Email Update' },
            { key: 'admin_profile_password', selector: 'form[action*="changePassword"]', title: 'Admin Password' },
            { key: 'admin_deposit_details_bank', selector: 'form[action*="depositData"]', title: 'Deposit Bank Details' },
            { key: 'admin_deposit_details_usdt', selector: 'form[action*="depositDataUSDT"]', title: 'Deposit USDT Details' },
            { key: 'admin_withdrawal_online_pay', selector: 'form[action*="sendWdlRequest"]', title: 'Online Pay Request' },

            // Public Forms
            { key: 'public_member_signup', selector: '#signupForm, form[action*="userRegister"]', title: 'Member Registration' },
            { key: 'public_member_signin', selector: 'form[action*="userLogin"]', title: 'Member Login' },
            { key: 'public_admin_login', selector: 'form[action*="adminLogin"]', title: 'Admin Login' },
            { key: 'public_member_forget_password', selector: 'form[action*="memretrivePassword"]', title: 'Member Password Reset' },
            { key: 'public_admin_forget_password', selector: 'form[action*="retrivePassword"]', title: 'Admin Password Reset' }
        ],

        /**
         * Initialize the Form Filling AI Controller
         */
        init: function () {
            // Read backend config if exported
            if (window.IRA_CONFIG) {
                this.config.formEndpoint = window.IRA_CONFIG.formEndpoint || this.config.formEndpoint;
                this.config.formsForPageEndpoint = window.IRA_CONFIG.formsForPageEndpoint || this.config.formsForPageEndpoint;
                this.config.csrfToken = window.IRA_CONFIG.csrfToken || '';
                this.config.role = window.IRA_CONFIG.role || 'guest';
                this.config.defaultLocale = window.IRA_CONFIG.defaultLocale || 'hi-IN';
                this.config.voiceRate = window.IRA_CONFIG.voiceRate || 1.0;
                this.config.voicePitch = window.IRA_CONFIG.voicePitch || 1.05;
            }

            // Set default language based on locale
            if (this.config.defaultLocale.startsWith('en')) {
                this.selectedLanguage = 'en';
            } else {
                this.selectedLanguage = 'hinglish';
            }

            // Bind capture phase click interceptor to prevent Navigation AI modal opening
            this.bindCaptureClickInterceptor();

            // Mount form buttons for current page
            this.mountFormButtons();

            // Also check again after DOM updates (for ajax-loaded forms)
            setTimeout(() => this.mountFormButtons(), 800);
            setTimeout(() => this.mountFormButtons(), 2000);

            // Initialize isolated speech synthesis
            this.initSpeechSynthesis();

            console.log('[IRA_FORM_FILLER] Independent Form Filling AI initialized. Default language:', this.selectedLanguage);
        },

        /**
         * Intercept clicks on .ira-form-fill-btn in CAPTURE PHASE.
         * Calling e.stopImmediatePropagation() prevents the event from reaching
         * the bubbling listener inside ira-assistant.js, guaranteeing #iraModal NEVER opens!
         */
        bindCaptureClickInterceptor: function () {
            document.addEventListener('click', (e) => {
                const btn = e.target.closest('.ira-form-fill-btn');
                if (!btn) {
                    return; // Allow ordinary clicks to proceed untouched
                }

                // Strictly intercept AI Fill Form clicks!
                e.preventDefault();
                e.stopImmediatePropagation();

                const formKey = btn.getAttribute('data-ira-form') || '';
                console.log('[IRA_FORM_FILLER] Intercepted AI Fill Form click for:', formKey);
                this.startFormFill(formKey, btn);
            }, true); // TRUE = Capture Phase!
        },

        /**
         * Mount "AI Fill Form" button on all eligible forms rendered on this page
         */
        mountFormButtons: function () {
            this.formRules.forEach(rule => {
                const formEl = document.querySelector(rule.selector);
                if (!formEl) return;

                // Tag form element with data-ira-form-key
                formEl.setAttribute('data-ira-form-key', rule.key);

                // Check if button already exists in DOM
                const existingBtn = document.querySelector(`.ira-form-fill-btn[data-ira-form="${rule.key}"]`);
                if (existingBtn) {
                    existingBtn.setAttribute('data-ira-form-key', rule.key);
                    return;
                }

                // Create the button
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'ira-form-fill-btn';
                btn.setAttribute('data-ira-form', rule.key);
                btn.setAttribute('data-ira-form-key', rule.key);
                btn.setAttribute('title', `Fill ${rule.title} with IRA Voice Form AI`);
                btn.innerHTML = `
                    <svg class="ira-form-fill-icon" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
                        <path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>
                    </svg>
                    <span>AI Fill Form</span>
                `;

                // Smart insertion into card header or top of form
                const parentCard = formEl.closest('.card, .member-wallet-panel, .panel, .box');
                const header = parentCard ? parentCard.querySelector('.card-header, .card-title, .member-wallet-form__top, .box-header') : null;

                if (header) {
                    if (header.classList.contains('card-header') || header.classList.contains('box-header')) {
                        if (!header.classList.contains('d-flex')) {
                            header.classList.add('d-flex', 'align-items-center', 'justify-content-between', 'gap-2');
                        }
                    }
                    header.appendChild(btn);
                } else {
                    const topHeading = formEl.querySelector('h2, h3, h4, h5, legend, .card-title');
                    if (topHeading) {
                        const wrapper = document.createElement('div');
                        wrapper.className = 'd-flex justify-content-between align-items-center mb-3 gap-2 flex-wrap';
                        topHeading.parentNode.insertBefore(wrapper, topHeading);
                        wrapper.appendChild(topHeading);
                        wrapper.appendChild(btn);
                    } else {
                        formEl.parentNode.insertBefore(btn, formEl);
                    }
                }
            });
        },

        /**
         * Check if an element is sensitive (passwords, PINs, OTPs, tokens, CAPTCHA)
         */
        isSensitiveElement: function (el) {
            if (!el) return true;
            const type = (el.type || '').toLowerCase();
            const name = (el.name || '').toLowerCase();
            const id = (el.id || '').toLowerCase();
            const autocomplete = (el.getAttribute('autocomplete') || '').toLowerCase();
            const ariaLabel = (el.getAttribute('aria-label') || '').toLowerCase();

            if (type === 'password') return true;

            const sensitiveTokens = ['password', 'pass', 'pin', 'tpin', 'otp', 'captcha', 'secret', 'token', 'csrf'];
            for (const token of sensitiveTokens) {
                if (name.includes(token) || id.includes(token) || autocomplete.includes(token) || ariaLabel.includes(token)) {
                    return true;
                }
            }
            return false;
        },

        /**
         * Check if a DOM form element is visible, editable and eligible for voice input
         */
        isElementVisibleAndEditable: function (el) {
            if (!el || !(el instanceof Element)) return false;

            // 1. Tag name must be input, select, or textarea
            const tagName = el.tagName.toUpperCase();
            if (tagName !== 'INPUT' && tagName !== 'SELECT' && tagName !== 'TEXTAREA') {
                return false;
            }

            // 2. Reject hidden input types, buttons, submit, reset
            const type = (el.type || '').toLowerCase();
            if (type === 'hidden' || type === 'submit' || type === 'button' || type === 'reset' || type === 'image') {
                return false;
            }

            // 3. Reject sensitive credentials (passwords, PINs, tokens)
            if (this.isSensitiveElement(el)) {
                return false;
            }

            // 4. Reject read-only or disabled elements
            if (el.readOnly || el.hasAttribute('readonly') || el.disabled || el.hasAttribute('disabled')) {
                return false;
            }

            // 5. Reject HTML hidden attribute
            if (el.hidden || el.hasAttribute('hidden')) {
                return false;
            }

            // 6. Check computed styles and layout visibility
            try {
                const style = window.getComputedStyle(el);
                if (style.display === 'none' || style.visibility === 'hidden' || style.opacity === '0') {
                    return false;
                }
                if (el.offsetParent === null && style.position !== 'fixed') {
                    return false;
                }
                const rect = el.getBoundingClientRect();
                if (rect.width === 0 && rect.height === 0) {
                    return false;
                }
            } catch (e) {
                // In non-DOM or test environment
            }

            // 7. Check parent hierarchy for hidden classes or attributes
            let parent = el.parentElement;
            while (parent && parent !== document.body) {
                if (parent.hidden || parent.hasAttribute('hidden') || parent.getAttribute('aria-hidden') === 'true') {
                    return false;
                }
                const pClass = (parent.className || '').toString();
                if (/\b(d-none|hidden|visually-hidden|sr-only)\b/.test(pClass)) {
                    return false;
                }
                try {
                    const pStyle = window.getComputedStyle(parent);
                    if (pStyle.display === 'none' || pStyle.visibility === 'hidden') {
                        return false;
                    }
                } catch (e) {}
                parent = parent.parentElement;
            }

            return true;
        },

        /**
         * Get all DOM visible, editable, non-sensitive fields inside a form element
         */
        getDomEligibleFields: function (formEl) {
            if (!formEl) return [];
            const fields = [];
            const elements = formEl.querySelectorAll('input, select, textarea');
            elements.forEach(el => {
                if (this.isElementVisibleAndEditable(el)) {
                    const key = el.name || el.id;
                    if (key && !fields.includes(key)) {
                        fields.push(key);
                    }
                }
            });
            return fields;
        },

        /**
         * Launch independent Form Filling session
         */
        startFormFill: async function (formKey, triggerBtn) {
            // Find rule and form element strictly scoped
            const rule = this.formRules.find(r => r.key === formKey) || { key: formKey, selector: '', title: 'Form' };
            let formEl = triggerBtn ? triggerBtn.closest('form') : null;
            if (!formEl && triggerBtn) {
                const parentCard = triggerBtn.closest('.card, .member-wallet-panel, .panel, .box');
                formEl = parentCard ? parentCard.querySelector('form') : null;
            }
            if (!formEl && rule.selector) {
                formEl = document.querySelector(rule.selector);
            }
            if (!formEl) {
                formEl = document.querySelector('form');
            }

            if (!formEl) {
                console.warn('[IRA_FORM_FILLER] Form element not found for key:', formKey);
                return;
            }

            // Tag form element with data-ira-form-key
            formEl.setAttribute('data-ira-form-key', formKey);

            // Close Navigation AI modal if open to guarantee zero overlap
            if (window.IRA_ASSISTANT) {
                try {
                    if (typeof window.IRA_ASSISTANT.closeModal === 'function') {
                        window.IRA_ASSISTANT.closeModal();
                    }
                    if (typeof window.IRA_ASSISTANT.stopListening === 'function') {
                        window.IRA_ASSISTANT.stopListening();
                    }
                } catch (e) {}
            }

            // Clean up any prior active dock
            this.clearHighlights();
            this.removeDock();

            // Set state
            this.activeFormKey = formKey;
            this.activeFormEl = formEl;
            this.activeFormTitle = rule.title;
            this.guideState = null;
            this.activeField = null;
            this.currentFields = this.scrapeCurrentFormFields(formEl);

            // Calculate visible, editable, non-sensitive fields present in the DOM
            const domEligible = this.getDomEligibleFields(formEl);
            console.log('[IRA_FORM_FILLER] Eligible DOM fields for', formKey, ':', domEligible);

            // If no fields are eligible for voice entry (e.g. all hidden, read-only, or sensitive)
            if (!domEligible || domEligible.length === 0) {
                this.setStatus('COMPLETED');
                this.renderDock(formEl, rule.title);
                const noFieldsMsg = this.selectedLanguage === 'en'
                    ? "This form has no voice-fillable fields available. Protected fields like passwords must be entered manually."
                    : (this.selectedLanguage === 'hi'
                        ? "इस फ़ॉर्म में आवाज़ से भरने योग्य कोई फ़ील्ड उपलब्ध नहीं है। पासवर्ड या सुरक्षा फ़ील्ड स्वयं भरें।"
                        : "Is form mein voice se bharne yogya koi field available nahi hai. Passwords ya security fields swayam enter karein.");
                this.updatePromptDisplay(noFieldsMsg);
                this.speak(noFieldsMsg);
                return;
            }

            this.setStatus('INITIALIZING');

            // Render inline compact dock immediately above form
            this.renderDock(formEl, rule.title);

            // Fetch session initialization from POST /ira/form-step
            try {
                const endpoint = this.config.formEndpoint || '/ira/form-step';
                const payload = {
                    action: 'init',
                    speech: '',
                    form_key: formKey,
                    current_page: window.location.pathname,
                    current_fields: this.currentFields,
                    language: this.selectedLanguage,
                    dom_eligible_fields: domEligible
                };

                const res = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.config.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                if (!res.ok) {
                    throw new Error('HTTP ' + res.status);
                }

                const data = await res.json();
                console.log('[IRA_FORM_FILLER] Form init response:', data);

                // Handle server reporting no eligible or all-sensitive fields
                if (data.status === 'all_sensitive' || data.status === 'no_eligible_fields') {
                    this.setStatus('COMPLETED');
                    const notice = data.speech || (this.selectedLanguage === 'en'
                        ? "All fields on this form are sensitive and must be entered manually."
                        : "Is form ke sabhi fields sensitive hain aur swayam enter karne honge.");
                    this.updatePromptDisplay(notice);
                    this.speak(notice);
                    return;
                }

                if (data.guide_state) {
                    this.guideState = data.guide_state;
                }
                if (data.active_field) {
                    this.setActiveField(data.active_field);
                }

                // Prefer server-returned context-aware speech or generate greeting in selected language
                let speechPrompt = data.speech || '';
                if (!speechPrompt) {
                    if (data.active_field) {
                        speechPrompt = this.generateIntroPrompt(rule.title, data.active_field, this.selectedLanguage);
                    } else {
                        speechPrompt = `Main ${rule.title} bharnay ke liye taiyyar hoon. Kripya pehla field bataiye.`;
                    }
                }

                this.updatePromptDisplay(speechPrompt);
                this.updateStepProgress();

                // Speak introductory question and start isolated recognition
                this.speak(speechPrompt, () => {
                    this.startListening();
                });

            } catch (err) {
                console.error('[IRA_FORM_FILLER] Failed to init form session:', err);
                const fallbackPrompt = this.selectedLanguage === 'en'
                    ? `I am ready to help you fill ${rule.title}. What would you like to enter?`
                    : (this.selectedLanguage === 'hi'
                        ? `मैं ${rule.title} भरने के लिए तैयार हूँ। कृपया बोलिए आप क्या भरना चाहते हैं।`
                        : `Main ${rule.title} bharnay ke liye taiyyar hoon. Kripya boliye aap kya bharna chahte hain.`);

                this.updatePromptDisplay(fallbackPrompt);
                this.speak(fallbackPrompt, () => {
                    this.startListening();
                });
            }
        },

        /**
         * Render compact, non-obstructive inline dock adjacent to the form
         */
        renderDock: function (formEl, formTitle) {
            // Remove prior dock if present
            this.removeDock();

            const dock = document.createElement('div');
            dock.id = 'iraFormFillerDock';
            dock.className = 'ira-ff-dock';
            dock.innerHTML = `
                <div class="ira-ff-header">
                    <div class="ira-ff-brand">
                        <div class="ira-ff-badge-avatar" aria-hidden="true">IRA</div>
                        <div class="ira-ff-title-group">
                            <h6 class="ira-ff-title">${this.escapeHtml(formTitle)}</h6>
                            <span class="ira-ff-subtitle">Voice Form Assistant &bull; Fill without typing</span>
                        </div>
                    </div>
                    <div class="ira-ff-controls">
                        <select class="ira-ff-lang-select" id="iraFfLangSelect" title="Select Voice Form Language" aria-label="Select Voice Form Language">
                            <option value="hinglish" ${this.selectedLanguage === 'hinglish' ? 'selected' : ''}>Hinglish</option>
                            <option value="hi" ${this.selectedLanguage === 'hi' ? 'selected' : ''}>Hindi</option>
                            <option value="en" ${this.selectedLanguage === 'en' ? 'selected' : ''}>English</option>
                        </select>
                        <span class="ira-ff-status-pill ready" id="iraFfStatusPill">
                            <span class="ira-ff-status-dot"></span>
                            <span id="iraFfStatusLabel">Ready</span>
                        </span>
                        <button type="button" class="ira-ff-btn ira-ff-btn-mic" id="iraFfMicBtn" title="Toggle Microphone" aria-label="Toggle Microphone">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
                                <path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>
                            </svg>
                        </button>
                        <button type="button" class="ira-ff-btn" id="iraFfRepeatBtn" title="Repeat Question" aria-label="Repeat Question">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="23 4 23 10 17 10"></polyline>
                                <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                            </svg>
                        </button>
                        <button type="button" class="ira-ff-btn-exit" id="iraFfExitBtn" title="Exit Form Filling AI" aria-label="Exit Form Filling AI">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                            <span>Exit</span>
                        </button>
                    </div>
                </div>
                <div class="ira-ff-body">
                    <div class="ira-ff-prompt-box">
                        <svg class="ira-ff-prompt-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                        <div class="ira-ff-prompt-content">
                            <p class="ira-ff-prompt-text" id="iraFfPromptText">Initializing form assistant...</p>
                        </div>
                    </div>

                    <div class="ira-ff-transcript-box">
                        <span class="ira-ff-transcript-label">You:</span>
                        <span class="ira-ff-transcript-text" id="iraFfTranscriptText">Waiting for your voice...</span>
                    </div>

                    <div class="ira-ff-footer">
                        <span class="ira-ff-field-target" id="iraFfTargetField">Target: None</span>
                        <span class="ira-ff-steps" id="iraFfStepProgress">Step 1 of 1</span>
                    </div>
                </div>
            `;

            // Insert immediately before the active form
            formEl.parentNode.insertBefore(dock, formEl);
            this.dockEl = dock;

            // Bind Language Selector
            const langSelect = dock.querySelector('#iraFfLangSelect');
            if (langSelect) {
                langSelect.onchange = (e) => {
                    this.setLanguage(e.target.value);
                };
            }

            // Bind Mic Toggle Button
            const micBtn = dock.querySelector('#iraFfMicBtn');
            if (micBtn) {
                micBtn.onclick = (e) => {
                    e.preventDefault();
                    if (this.isRecognitionRunning) {
                        this.stopListening();
                        this.setStatus('PAUSED');
                    } else {
                        this.startListening();
                    }
                };
            }

            // Bind Repeat Question Button
            const repeatBtn = dock.querySelector('#iraFfRepeatBtn');
            if (repeatBtn) {
                repeatBtn.onclick = (e) => {
                    e.preventDefault();
                    if (this.lastSpokenPrompt) {
                        this.speak(this.lastSpokenPrompt, () => this.startListening());
                    }
                };
            }

            // Bind Exit Form Fill Button
            const exitBtn = dock.querySelector('#iraFfExitBtn');
            if (exitBtn) {
                exitBtn.onclick = (e) => {
                    e.preventDefault();
                    this.exitFormFill();
                };
            }

            // Scroll dock smoothly into view
            dock.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        },

        /**
         * Dynamic Language Switcher
         * Applies to Speech Recognition and Speech Synthesis immediately.
         * Preserves active form, current field, and all populated values!
         */
        setLanguage: function (newLang) {
            if (!newLang || (newLang !== 'hi' && newLang !== 'hinglish' && newLang !== 'en')) {
                return;
            }
            this.selectedLanguage = newLang;
            console.log('[IRA_FORM_FILLER] Language switched to:', newLang);

            // 1. Resolve speech recognition locale
            let recLocale = 'hi-IN';
            if (newLang === 'en') {
                recLocale = 'en-IN';
            } else if (newLang === 'hi') {
                recLocale = 'hi-IN';
            } else {
                recLocale = 'hi-IN'; // Hinglish uses hi-IN for natural bilingual recognition
            }

            // 2. Safely reconfigure recognition without losing active form state
            const wasListening = this.isRecognitionRunning;
            this.stopListening();
            if (this.recognition) {
                this.recognition.lang = recLocale;
            }

            // 3. Re-prompt current active field in the newly selected language
            if (this.activeField) {
                const promptText = this.generateFieldPrompt(this.activeField, newLang);
                this.updatePromptDisplay(promptText);
                this.speak(promptText, () => {
                    this.startListening();
                });
            } else if (this.status === 'COMPLETED') {
                const compPrompt = this.generateCompletionPrompt(this.activeFormTitle, newLang);
                this.updatePromptDisplay(compPrompt);
                this.speak(compPrompt);
            } else if (wasListening) {
                this.startListening();
            }
        },

        /**
         * Remove dock from DOM
         */
        removeDock: function () {
            if (this.dockEl && this.dockEl.parentNode) {
                this.dockEl.parentNode.removeChild(this.dockEl);
            }
            this.dockEl = null;
        },

        /**
         * Update dock voice status pill
         */
        setStatus: function (statusName) {
            this.status = statusName;
            const pill = document.getElementById('iraFfStatusPill');
            const label = document.getElementById('iraFfStatusLabel');
            const micBtn = document.getElementById('iraFfMicBtn');

            if (!pill || !label) return;

            pill.className = 'ira-ff-status-pill ' + statusName.toLowerCase();
            label.textContent = statusName;

            if (micBtn) {
                if (statusName === 'LISTENING') {
                    micBtn.classList.add('active');
                } else {
                    micBtn.classList.remove('active');
                }
            }
        },

        /**
         * Update current prompt display
         */
        updatePromptDisplay: function (text) {
            this.lastSpokenPrompt = text;
            const promptEl = document.getElementById('iraFfPromptText');
            if (promptEl) {
                promptEl.textContent = text;
            }
        },

        /**
         * Update user transcript display
         */
        updateTranscriptDisplay: function (text) {
            const transcriptEl = document.getElementById('iraFfTranscriptText');
            if (transcriptEl) {
                transcriptEl.textContent = text || 'Waiting for voice...';
            }
        },

        /**
         * Update step progress indicator
         */
        updateStepProgress: function () {
            const targetEl = document.getElementById('iraFfTargetField');
            const stepEl = document.getElementById('iraFfStepProgress');

            if (this.activeField && targetEl) {
                targetEl.textContent = 'Active: ' + (this.activeField.label || this.activeField.name || 'Field');
            }

            if (this.guideState && stepEl) {
                const seq = this.guideState.field_sequence || [];
                const total = seq.length || 1;
                const idx = (this.guideState.step_index || 0) + 1;
                stepEl.textContent = `Field ${Math.min(idx, total)} of ${total}`;
            }
        },

        /**
         * Set and highlight the active field
         */
        setActiveField: function (fieldDef) {
            this.clearHighlights();
            this.activeField = fieldDef;

            if (!fieldDef) return;

            // Resolve DOM element strictly inside activeFormEl
            let targetEl = null;
            if (this.activeFormEl) {
                if (fieldDef.selector) {
                    targetEl = this.activeFormEl.querySelector(fieldDef.selector);
                }
                if (!targetEl && fieldDef.name) {
                    targetEl = this.activeFormEl.querySelector(`[name="${fieldDef.name}"]`) || this.activeFormEl.querySelector(`#${fieldDef.name}`);
                }
            }

            // Only highlight and scroll to visible, editable, non-sensitive elements
            if (targetEl && this.isElementVisibleAndEditable(targetEl)) {
                this.activeFieldEl = targetEl;
                targetEl.classList.add('ira-ff-active-field');

                // Smoothly focus / scroll into view without disorienting the user
                try {
                    targetEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } catch (e) {}
            }
        },

        /**
         * Clear active field highlight borders
         */
        clearHighlights: function () {
            document.querySelectorAll('.ira-ff-active-field').forEach(el => {
                el.classList.remove('ira-ff-active-field');
            });
            this.activeFieldEl = null;
        },

        /**
         * Isolated Speech Recognition Lifecycle
         */
        initSpeechRecognition: function () {
            if (this.recognition) return;

            const SpeechRec = window.SpeechRecognition || window.webkitSpeechRecognition;
            if (!SpeechRec) {
                console.warn('[IRA_FORM_FILLER] Web Speech API not supported in this browser.');
                return;
            }

            const rec = new SpeechRec();
            rec.continuous = false;
            rec.interimResults = true;

            // Determine locale based on selected language
            let locale = 'hi-IN';
            if (this.selectedLanguage === 'en') {
                locale = 'en-IN';
            }
            rec.lang = locale;

            rec.onstart = () => {
                this.isRecognitionRunning = true;
                this.hasDispatchedTurn = false;
                this.sessionStartTime = Date.now();
                this.sessionInterimTranscript = '';
                this.sessionFinalTranscript = '';
                this.setStatus('LISTENING');
                this.armMaxListeningTimer();
            };

            rec.onresult = (event) => {
                let interim = '';
                let finals = '';
                for (let i = 0; i < event.results.length; i++) {
                    const item = event.results[i];
                    if (item.isFinal) {
                        finals += item[0].transcript;
                    } else {
                        interim += item[0].transcript;
                    }
                }
                if (finals.trim()) {
                    this.sessionFinalTranscript = finals.trim();
                }
                this.sessionInterimTranscript = interim.trim();

                const display = (this.sessionFinalTranscript + ' ' + interim).trim();
                if (display) {
                    this.updateTranscriptDisplay(display);
                    this.resetSilenceTimer();
                }
            };

            rec.onerror = (event) => {
                console.warn('[IRA_FORM_FILLER] Recognition note:', event.error);
                if (event.error === 'not-allowed') {
                    this.setStatus('ERROR');
                    this.updateTranscriptDisplay('Microphone permission blocked. Please enable mic access.');
                }
            };

            rec.onend = () => {
                this.isRecognitionRunning = false;
                this.clearSilenceTimer();
                this.clearMaxListeningTimer();

                const textToProcess = (this.sessionFinalTranscript || this.sessionInterimTranscript || '').trim();
                if (textToProcess && !this.hasDispatchedTurn && this.status === 'LISTENING') {
                    this.hasDispatchedTurn = true;
                    this.sessionFinalTranscript = '';
                    this.sessionInterimTranscript = '';
                    this.handleSpokenTurn(textToProcess);
                } else if (this.status === 'LISTENING') {
                    this.setStatus('READY');
                }
            };

            this.recognition = rec;
        },

        startListening: function () {
            if (this.isSpeaking) return;
            this.initSpeechRecognition();
            if (!this.recognition) return;

            // Set current locale dynamically
            if (this.selectedLanguage === 'en') {
                this.recognition.lang = 'en-IN';
            } else {
                this.recognition.lang = 'hi-IN';
            }

            try {
                this.recognition.abort();
            } catch (e) {}

            try {
                this.recognition.start();
            } catch (err) {
                console.warn('[IRA_FORM_FILLER] Start listening note:', err);
            }
        },

        stopListening: function () {
            this.clearSilenceTimer();
            this.clearMaxListeningTimer();
            if (this.recognition && this.isRecognitionRunning) {
                try {
                    this.recognition.stop();
                } catch (e) {}
            }
            this.isRecognitionRunning = false;
        },

        resetSilenceTimer: function () {
            this.clearSilenceTimer();
            const threshold = this.config.silenceThresholdMs || 1800;
            this.silenceTimer = setTimeout(() => {
                if (this.isRecognitionRunning && !this.hasDispatchedTurn) {
                    this.stopListening();
                }
            }, threshold);
        },

        clearSilenceTimer: function () {
            if (this.silenceTimer) {
                clearTimeout(this.silenceTimer);
                this.silenceTimer = null;
            }
        },

        armMaxListeningTimer: function () {
            this.clearMaxListeningTimer();
            const maxDuration = this.config.maxListeningDurationMs || 25000;
            this.maxListeningTimer = setTimeout(() => {
                if (this.isRecognitionRunning) {
                    console.log('[IRA_FORM_FILLER] Max listening duration reached.');
                    this.stopListening();
                }
            }, maxDuration);
        },

        clearMaxListeningTimer: function () {
            if (this.maxListeningTimer) {
                clearTimeout(this.maxListeningTimer);
                this.maxListeningTimer = null;
            }
        },

        /**
         * Isolated Speech Synthesis Lifecycle with Language & Voice Selection
         */
        initSpeechSynthesis: function () {
            if (!('speechSynthesis' in window)) return;
            // Warm up voices
            window.speechSynthesis.getVoices();
        },

        speak: function (text, onEnd) {
            if (!('speechSynthesis' in window) || !text) {
                if (typeof onEnd === 'function') onEnd();
                return;
            }

            // Temporarily stop microphone while IRA speaks
            this.stopListening();
            this.isSpeaking = true;
            this.setStatus('SPEAKING');

            try {
                window.speechSynthesis.cancel();
            } catch (e) {}

            const utt = new SpeechSynthesisUtterance(text);
            utt.rate = this.config.voiceRate || 1.0;
            utt.pitch = this.config.voicePitch || 1.05;

            const voices = window.speechSynthesis.getVoices() || [];

            // Select best natural voice based on user's language selection
            if (this.selectedLanguage === 'en') {
                utt.lang = 'en-IN';
                const engVoice = voices.find(v => v.lang === 'en-IN') ||
                                 voices.find(v => v.lang.startsWith('en') && (v.name.includes('India') || v.name.includes('Google'))) ||
                                 voices.find(v => v.lang.startsWith('en'));
                if (engVoice) utt.voice = engVoice;
            } else if (this.selectedLanguage === 'hi') {
                utt.lang = 'hi-IN';
                const hindiVoice = voices.find(v => v.lang === 'hi-IN' || v.name.includes('Hindi') || v.name.includes('हिन्दी'));
                if (hindiVoice) utt.voice = hindiVoice;
            } else {
                // Hinglish: prefer natural Indian voice
                utt.lang = 'hi-IN';
                const indVoice = voices.find(v => v.lang === 'hi-IN' || v.lang === 'en-IN' || v.name.includes('India') || v.name.includes('Hindi'));
                if (indVoice) utt.voice = indVoice;
            }

            utt.onend = () => {
                this.isSpeaking = false;
                if (typeof onEnd === 'function') {
                    onEnd();
                }
            };

            utt.onerror = (e) => {
                console.warn('[IRA_FORM_FILLER] Speech synthesis note:', e);
                this.isSpeaking = false;
                if (typeof onEnd === 'function') {
                    onEnd();
                }
            };

            window.speechSynthesis.speak(utt);
        },

        /**
         * Handle a finalized user utterance
         */
        handleSpokenTurn: async function (spokenText) {
            let clean = (spokenText || '').trim();
            if (!clean || !this.activeFormKey) {
                this.setStatus('READY');
                return;
            }

            // If active field is an identifier, apply dedicated identifier normalization
            if (this.isIdentifierField(this.activeField)) {
                const normId = this.normalizeIdentifier(clean);
                if (normId) {
                    clean = normId;
                }
            } else if (this.activeField && ((this.activeField.type === 'number') || String(this.activeField.name || '').includes('amount'))) {
                // Normalize spoken numbers if Hindi or Roman Hindi amounts
                clean = this.normalizeSpokenHindi(clean);
            }

            this.setStatus('THINKING');
            this.updateTranscriptDisplay(`"${clean}"`);

            const domEligible = this.getDomEligibleFields(this.activeFormEl);
            const endpoint = this.config.formEndpoint || '/ira/form-step';
            const payload = {
                action: 'turn',
                speech: clean,
                form_key: this.activeFormKey,
                current_page: window.location.pathname,
                current_fields: this.scrapeCurrentFormFields(this.activeFormEl),
                guide_state: this.guideState,
                language: this.selectedLanguage,
                dom_eligible_fields: domEligible
            };

            try {
                const res = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.config.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                if (!res.ok) {
                    throw new Error('HTTP ' + res.status);
                }

                const data = await res.json();
                console.log('[IRA_FORM_FILLER] Turn response:', data);

                // Handle cancellation / exit command from user speech
                if (data.action === 'exit' || data.status === 'cancelled') {
                    const exitMsg = this.generateExitPrompt(this.selectedLanguage);
                    this.updatePromptDisplay(exitMsg);
                    this.speak(exitMsg, () => {
                        this.exitFormFill();
                    });
                    return;
                }

                // Apply DOM Updates if returned
                let domUpdated = true;
                if (Array.isArray(data.form_updates) && data.form_updates.length > 0) {
                    domUpdated = this.applyFieldUpdates(data.form_updates);
                }

                if (!domUpdated) {
                    console.error('[IRA_FORM_FILLER] DOM update failed for updates:', data.form_updates);
                    const domFailMsg = this.selectedLanguage === 'en'
                        ? "Could not update the field on the form. Please check the screen."
                        : (this.selectedLanguage === 'hi'
                            ? "फ़ॉर्म में फ़ील्ड अपडेट नहीं हो सका। कृपया स्क्रीन पर जाँच करें।"
                            : "Form mein field update nahi ho saka. Kripya screen par check karein.");
                    this.updatePromptDisplay(domFailMsg);
                    this.speak(domFailMsg, () => {
                        this.startListening();
                    });
                    return;
                }

                // Update guide state ONLY after DOM update succeeds
                if (data.guide_state) {
                    this.guideState = data.guide_state;
                }

                // Update active field
                if (data.active_field) {
                    this.setActiveField(data.active_field);
                }

                this.updateStepProgress();

                // Check for completion
                if (data.action === 'complete' || data.status === 'review') {
                    this.setStatus('COMPLETED');
                    this.clearHighlights();

                    const compPrompt = this.generateCompletionPrompt(this.activeFormTitle, this.selectedLanguage);
                    this.updatePromptDisplay(compPrompt);

                    // Show completion badge in dock
                    const promptBox = this.dockEl ? this.dockEl.querySelector('.ira-ff-prompt-content') : null;
                    if (promptBox && !promptBox.querySelector('.ira-ff-success-badge')) {
                        const successNotice = document.createElement('div');
                        successNotice.className = 'ira-ff-success-badge mt-2';
                        successNotice.innerHTML = `
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                            </svg>
                            <span>Ready to submit! Review inputs and click Submit below.</span>
                        `;
                        promptBox.appendChild(successNotice);
                    }

                    // Speak review text, and do NOT re-arm microphone; NEVER auto-submit!
                    this.speak(compPrompt);
                    return;
                }

                // Check for clarification requirement
                if (data.status === 'clarify_field') {
                    const clarifyPrompt = this.generateClarificationPrompt(this.activeField, this.selectedLanguage);
                    this.updatePromptDisplay(clarifyPrompt);
                    this.speak(clarifyPrompt, () => {
                        this.startListening();
                    });
                    return;
                }

                // Prefer server-returned natural bilingual speech with digit-by-digit spoken confirmations
                let nextSpeech = '';
                if (data.speech) {
                    nextSpeech = data.speech;
                } else if (data.active_field) {
                    const confirm = (data.form_updates && data.form_updates.length > 0)
                        ? (this.generateFieldConfirmation(data.form_updates[0], this.selectedLanguage) + ' ')
                        : '';
                    const nextQ = this.generateFieldPrompt(data.active_field, this.selectedLanguage);
                    nextSpeech = confirm + nextQ;
                } else {
                    nextSpeech = 'Agle field ki value bataiye.';
                }

                this.updatePromptDisplay(nextSpeech);
                this.speak(nextSpeech, () => {
                    this.startListening();
                });

            } catch (err) {
                console.error('[IRA_FORM_FILLER] Turn processing error:', err);
                const retrySpeech = this.selectedLanguage === 'en'
                    ? "Sorry, I couldn't process that. Please repeat."
                    : (this.selectedLanguage === 'hi'
                        ? "माफ़ कीजिए, मैं समझ नहीं पाई। कृपया दोबारा बोलिए।"
                        : "Kripya dubara boliye, main samajh nahi paayi.");

                this.updatePromptDisplay(retrySpeech);
                this.speak(retrySpeech, () => {
                    this.startListening();
                });
            }
        },

        /**
         * Robust DOM Field Updates with Dropdown Fix & Event Propagation
         * Strictly scoped to activeFormEl; rejects sensitive, hidden and read-only inputs
         */
        applyFieldUpdates: function (updates) {
            let allVerified = true;

            for (const update of updates) {
                let target = null;
                // Search inside active form element ONLY (strictly scoped)
                if (this.activeFormEl) {
                    if (update.selector) {
                        target = this.activeFormEl.querySelector(update.selector);
                    }
                    if (!target && update.name) {
                        target = this.activeFormEl.querySelector(`[name="${update.name}"]`) || this.activeFormEl.querySelector(`#${update.name}`);
                    }
                }

                if (!target) {
                    console.warn('[IRA_FORM_FILLER] Target element not found inside active form for update:', update);
                    allVerified = false;
                    continue;
                }

                // If element is not editable (readonly, disabled) or is sensitive, reject update!
                if (target.readOnly && !update.override_readonly) {
                    console.warn('[IRA_FORM_FILLER] Target element is read-only, skipping:', update.name);
                    continue;
                }
                if (this.isSensitiveElement(target)) {
                    console.warn('[IRA_FORM_FILLER] Target element is sensitive, refusing to populate:', update.name);
                    continue;
                }
                if (!this.isElementVisibleAndEditable(target) && !update.override_readonly) {
                    console.warn('[IRA_FORM_FILLER] Target element is not visible or editable, skipping:', update.name);
                    continue;
                }

                // 1. SELECT DROPDOWN ELEMENT (Robust matching ignoring empty options!)
                if (target.tagName === 'SELECT') {
                    const verified = this.applySelectUpdate(target, update);
                    if (!verified) allVerified = false;
                }
                // 2. CHECKBOX ELEMENT
                else if (target.type === 'checkbox') {
                    target.checked = !!update.value;
                    target.dispatchEvent(new Event('change', { bubbles: true }));
                    if (typeof jQuery !== 'undefined') {
                        try { jQuery(target).trigger('change'); } catch (e) {}
                    }
                }
                // 3. RADIO BUTTONS
                else if (target.type === 'radio') {
                    const radioGroup = this.activeFormEl ? this.activeFormEl.querySelectorAll(`[name="${target.name}"]`) : [];
                    let matchedRadio = false;
                    const cleanVal = String(update.value || '').trim().toLowerCase();
                    radioGroup.forEach(r => {
                        if (r.value.toLowerCase() === cleanVal) {
                            r.checked = true;
                            r.dispatchEvent(new Event('change', { bubbles: true }));
                            matchedRadio = true;
                        }
                    });
                    if (!matchedRadio) allVerified = false;
                }
                // 4. TEXT / NUMBER / DATE / TEXTAREA INPUTS
                else {
                    // Do not overwrite readonly fields unless explicitly allowed
                    if (target.readOnly && !update.override_readonly) {
                        continue;
                    }

                    const finalVal = String(update.value !== undefined && update.value !== null ? update.value : '');
                    target.value = finalVal;

                    // Dispatch events for form validation & dependent lookups (e.g. #fundmemberid AJAX lookup)
                    target.dispatchEvent(new Event('input', { bubbles: true }));
                    target.dispatchEvent(new Event('change', { bubbles: true }));
                    if (typeof jQuery !== 'undefined') {
                        try {
                            jQuery(target).trigger('input').trigger('change');
                        } catch (e) {}
                    }

                    // Verify DOM value populated
                    if (String(target.value).trim() !== finalVal.trim()) {
                        console.warn('[IRA_FORM_FILLER] DOM verification failed for', update.name, 'expected:', finalVal, 'actual:', target.value);
                        allVerified = false;
                    }
                }
            }

            return allVerified;
        },

        /**
         * Robust Dropdown Matching Algorithm
         * Fixes the critical bug where "diamond".includes("") matched empty Option 0!
         */
        applySelectUpdate: function (target, update) {
            const rawTarget = String(update.value || '').trim();
            const cleanTarget = rawTarget.toLowerCase();
            const rawDisplay = String(update.display_value || '').trim();
            const cleanDisplay = rawDisplay.toLowerCase();

            let matchedIndex = -1;

            // PASS 1: Exact Option Value Match (Ignoring empty placeholder options!)
            for (let i = 0; i < target.options.length; i++) {
                const opt = target.options[i];
                const optVal = (opt.value || '').trim();
                if (optVal !== '' && (optVal === rawTarget || optVal.toLowerCase() === cleanTarget)) {
                    matchedIndex = i;
                    break;
                }
            }

            // PASS 2: Exact Option Label Match
            if (matchedIndex === -1 && cleanDisplay !== '') {
                for (let i = 0; i < target.options.length; i++) {
                    const opt = target.options[i];
                    const optVal = (opt.value || '').trim();
                    const optText = (opt.text || '').trim();
                    if (optVal !== '' && (optText === rawDisplay || optText.toLowerCase() === cleanDisplay)) {
                        matchedIndex = i;
                        break;
                    }
                }
            }

            // PASS 3: Distinctive Keyword / StartsWith Match
            if (matchedIndex === -1) {
                for (let i = 0; i < target.options.length; i++) {
                    const opt = target.options[i];
                    const optVal = (opt.value || '').trim().toLowerCase();
                    const optText = (opt.text || '').trim().toLowerCase();

                    // CRITICAL FIX: NEVER match placeholder options with empty value!
                    if (optVal === '' || optText.includes('select') || optText.includes('choose')) {
                        continue;
                    }

                    // Check if option label starts with spoken value e.g. "Diamond ($10,100+)" startsWith "Diamond"
                    if (optText.startsWith(cleanTarget) || (optVal !== '' && optVal === cleanTarget)) {
                        matchedIndex = i;
                        break;
                    }

                    // Check whole-word / contains match safely
                    if ((cleanTarget !== '' && optText.includes(cleanTarget)) || (optVal !== '' && cleanTarget.includes(optVal))) {
                        matchedIndex = i;
                        break;
                    }
                }
            }

            if (matchedIndex >= 0) {
                target.selectedIndex = matchedIndex;
                target.value = target.options[matchedIndex].value;
            } else if (rawTarget !== '') {
                target.value = rawTarget;
            }

            // Dispatch native and jQuery change/input events for listeners (e.g. setPackageLimit() in staking.blade.php)
            target.dispatchEvent(new Event('input', { bubbles: true }));
            target.dispatchEvent(new Event('change', { bubbles: true }));
            if (typeof jQuery !== 'undefined') {
                try {
                    jQuery(target).trigger('change').trigger('input');
                } catch (e) {}
            }

            // Verify actual selection (Must have a selected option with non-empty value!)
            const verified = (target.selectedIndex >= 0 && target.value !== '');
            console.log(`[IRA_FORM_FILLER] Dropdown match for "${rawTarget}": selectedIndex=${target.selectedIndex}, value="${target.value}", verified=${verified}`);
            return verified;
        },

        /**
         * Scrape current non-sensitive form field values
         */
        scrapeCurrentFormFields: function (formEl) {
            if (!formEl) return {};
            const data = {};
            const inputs = formEl.querySelectorAll('input, select, textarea');

            inputs.forEach(input => {
                const name = input.name || input.id;
                if (!name) return;

                // Never scrape sensitive inputs (passwords, PINs, tokens)
                if (this.isSensitiveElement(input)) {
                    return;
                }
                const type = (input.type || '').toLowerCase();
                if (type === 'hidden' || input.hidden || input.hasAttribute('hidden')) {
                    return;
                }

                if (type === 'checkbox') {
                    data[name] = input.checked;
                } else if (type === 'radio') {
                    if (input.checked) {
                        data[name] = input.value;
                    }
                } else {
                    data[name] = input.value;
                }
            });

            return data;
        },

        /**
         * Exit Form Filling mode cleanly
         * Stops microphone and synthesis, clears highlights and dock,
         * but strictly PRESERVES all filled values in the actual form!
         */
        exitFormFill: function () {
            // 1. Cancel active speech synthesis
            if ('speechSynthesis' in window) {
                try {
                    window.speechSynthesis.cancel();
                } catch (e) {}
            }
            this.isSpeaking = false;

            // 2. Stop microphone recognition
            this.stopListening();

            // 3. Clear active field highlighting
            this.clearHighlights();

            // 4. Remove dock from DOM
            this.removeDock();

            // 5. Reset internal state (form inputs remain 100% untouched!)
            this.activeFormKey = null;
            this.activeFormEl = null;
            this.activeFormTitle = '';
            this.activeField = null;
            this.activeFieldEl = null;
            this.guideState = null;
            this.currentFields = {};
            this.setStatus('IDLE');

            console.log('[IRA_FORM_FILLER] Form filling session closed by user. Form inputs preserved intact.');
        },

        /**
         * Normalize Spoken Hindi Numbers & Devanagari Digits
         */
        normalizeSpokenHindi: function (text) {
            if (!text) return '';
            let s = String(text);

            // Convert Devanagari digits to standard Western digits
            const devanagariDigits = { '०':'0','१':'1','२':'2','३':'3','४':'4','५':'5','६':'6','७':'7','८':'8','९':'9' };
            s = s.replace(/[०-९]/g, d => devanagariDigits[d] || d);

            // Convert common spoken Hindi number words to standard digits
            const hindiWords = [
                { re: /(^|[^a-zA-Z0-9_\u0900-\u097F])(ek\s*hazaar|एक\s*हजार)($|[^a-zA-Z0-9_\u0900-\u097F])/gui, val: '1000' },
                { re: /(^|[^a-zA-Z0-9_\u0900-\u097F])(do\s*hazaar|दो\s*हजार)($|[^a-zA-Z0-9_\u0900-\u097F])/gui, val: '2000' },
                { re: /(^|[^a-zA-Z0-9_\u0900-\u097F])(teen\s*hazaar|तीन\s*हजार)($|[^a-zA-Z0-9_\u0900-\u097F])/gui, val: '3000' },
                { re: /(^|[^a-zA-Z0-9_\u0900-\u097F])(chaar\s*hazaar|चार\s*हजार)($|[^a-zA-Z0-9_\u0900-\u097F])/gui, val: '4000' },
                { re: /(^|[^a-zA-Z0-9_\u0900-\u097F])(paanch\s*hazaar|पांच\s*हजार)($|[^a-zA-Z0-9_\u0900-\u097F])/gui, val: '5000' },
                { re: /(^|[^a-zA-Z0-9_\u0900-\u097F])(das\s*hazaar|दस\s*हजार)($|[^a-zA-Z0-9_\u0900-\u097F])/gui, val: '10000' },
                { re: /(^|[^a-zA-Z0-9_\u0900-\u097F])(pandrah\s*hazaar|पंद्रह\s*हजार)($|[^a-zA-Z0-9_\u0900-\u097F])/gui, val: '15000' },
                { re: /(^|[^a-zA-Z0-9_\u0900-\u097F])(bees\s*hazaar|बीस\s*हजार)($|[^a-zA-Z0-9_\u0900-\u097F])/gui, val: '20000' },
                { re: /(^|[^a-zA-Z0-9_\u0900-\u097F])(pachas\s*hazaar|पचास\s*हजार)($|[^a-zA-Z0-9_\u0900-\u097F])/gui, val: '50000' },
                { re: /(^|[^a-zA-Z0-9_\u0900-\u097F])(ek\s*laakh|एक\s*लाख)($|[^a-zA-Z0-9_\u0900-\u097F])/gui, val: '100000' },
                { re: /(^|[^a-zA-Z0-9_\u0900-\u097F])(sau|एक\s*सौ|ek\s*sau)($|[^a-zA-Z0-9_\u0900-\u097F])/gui, val: '100' },
                { re: /(^|[^a-zA-Z0-9_\u0900-\u097F])(do\s*sau|दो\s*सौ)($|[^a-zA-Z0-9_\u0900-\u097F])/gui, val: '200' },
                { re: /(^|[^a-zA-Z0-9_\u0900-\u097F])(paanch\s*sau|पांच\s*सौ)($|[^a-zA-Z0-9_\u0900-\u097F])/gui, val: '500' },
                { re: /(^|[^a-zA-Z0-9_\u0900-\u097F])(pachaas|पचास)($|[^a-zA-Z0-9_\u0900-\u097F])/gui, val: '50' }
            ];

            hindiWords.forEach(hw => {
                s = s.replace(hw.re, (m, p1, p2, p3) => p1 + hw.val + p3);
            });

            return s;
        },

        /**
         * Check if a field definition represents an alphanumeric identifier
         */
        isIdentifierField: function (fieldDef) {
            if (!fieldDef) return false;
            const name = String(fieldDef.name || '').toLowerCase();
            const label = String(fieldDef.label || '').toLowerCase();
            const type = String(fieldDef.type || '').toLowerCase();

            return type === 'identifier' ||
                name.includes('memberid') ||
                name.includes('member_id') ||
                name.includes('user_id') ||
                name.includes('username') ||
                name.includes('sponsor') ||
                name.includes('searchid') ||
                name.includes('receiverid') ||
                name.includes('recipientid') ||
                label.includes('member id') ||
                label.includes('sponsor id') ||
                label.includes('user id') ||
                label.includes('recipient id') ||
                label.includes('receiver id') ||
                label.includes('receiver member id');
        },

        /**
         * Dedicated Alphanumeric Identifier Normalizer
         * Normalizes spoken letters (English / Devanagari) and individual digit words into uppercase alphanumeric string.
         * Preserves leading zeros (e.g. "zero five three" -> "053").
         */
        normalizeIdentifier: function (text) {
            if (!text) return null;
            let clean = String(text).trim();
            if (!clean) return null;

            // Remove zero-width characters and normalize whitespace
            clean = clean.replace(/[\u200B-\u200D\uFEFF]/g, '');
            clean = clean.replace(/[\u00A0\s]+/g, ' ');

            // Convert Devanagari numerals ०-९ to 0-9
            const devanagariDigits = { '०':'0','१':'1','२':'2','३':'3','४':'4','५':'5','६':'6','७':'7','८':'8','९':'9' };
            clean = clean.replace(/[०-९]/g, d => devanagariDigits[d] || d);

            // Strip conversational command fillers and field titles
            const fillers = [
                /\b(receiver\s*member\s*id|recipient\s*member\s*id|target\s*member\s*id|search\s*member\s*id)\b/gi,
                /(?:^|\s)(रिसीवर\s*मेंबर\s*आईडी|प्राप्तकर्ता\s*मेंबर\s*आईडी)(?:$|\s)/gui,
                /\b(member\s*id|sponsor\s*id|user\s*id|receiver\s*id|recipient\s*id|target\s*id|search\s*id|username|id)\b/gi,
                /(?:^|\s)(मेंबर\s*आईडी|सदस्य\s*आईडी|स्पॉन्सर\s*आईडी|यूज़र\s*आईडी|रिसीवर\s*आईडी|आईडी)(?:$|\s)/gui,
                /\b(likho|daalo|bharo|rakho|karo|hoga|kardo|kijiye|hai|yeh\s*hai|mera|meri|kripya|please|enter|likh\s*do|bhar\s*do|set\s*karo)\b/gi,
                /(?:^|\s)(लिखो|डालो|भरो|रखो|करो|होगा|कर\s*दो|कीजिए|है|यह\s*है|मेरा|मेरी|कृपया|भर\s*दो|लिख\s*दो)(?:$|\s)/gui
            ];
            for (const f of fillers) {
                clean = clean.replace(f, ' ');
            }

            clean = clean.replace(/\bdouble\s*(?:u|you)\b/gi, ' W ');
            clean = clean.replace(/(?:^|\s)डबल\s*(?:यू|यु)(?:$|\s)/gui, ' W ');

            // Separate attached digits from letters e.g. "क्यूआरवी123456" -> "क्यूआरवी 123456"
            clean = clean.replace(/([a-zA-Z\u0900-\u097F])([0-9])/gu, '$1 $2');
            clean = clean.replace(/([0-9])([a-zA-Z\u0900-\u097F])/gu, '$1 $2');

            clean = clean.replace(/\s+/g, ' ').trim();
            if (!clean) return null;

            // Already a clean single-token identifier without spaces
            if (/^[A-Za-z0-9_-]{2,30}$/.test(clean)) {
                return clean.toUpperCase();
            }

            const hasDigitOrIdClue = /\d/.test(clean) ||
                /\b(zero|one|two|three|four|five|six|seven|eight|nine|shunya|ek|do|teen|chaar|char|paanch|panch|che|chheh|chhah|saat|sat|aath|ath|nau)\b/i.test(clean) ||
                /(?:^|\s)(शून्य|ज़ीरो|जीरो|एक|वन|दो|टू|तीन|थ्री|चार|फोर|पांच|पाँच|फाइव|छह|छः|सिक्स|सात|सेवन|आठ|एट|नौ|नाइन|[०-९])(?:$|\s)/u.test(clean) ||
                /\b(cue|vee|gee|zee|zed|doubleu|aitch)\b/i.test(clean);

            if (!hasDigitOrIdClue && clean.includes(' ')) {
                return null;
            }

            const letterMap = {
                'डबल्यू':'W','डब्लू':'W','डब्ल्यू':'W',
                'क्यू':'Q','क्यु':'Q','कियू':'Q',
                'आर':'R','अर':'R',
                'वी':'V','व्ही':'V','वे':'V','वि':'V',
                'जी':'G','ज़ी':'G',
                'एच':'H','आई':'I','जे':'J','के':'K','एल':'L','एम':'M','एन':'N','ओ':'O','पी':'P','एस':'S','टी':'T','यू':'U','यु':'U',
                'एक्स':'X','वाई':'Y','वाय':'Y','ज़ेड':'Z','जेड':'Z',
                'ए':'A','ऐ':'A','बी':'B','सी':'C','डी':'D','ई':'E',
                'एफ':'F','फ़':'F','एफ़':'F'
            };
            const sortedDevKeys = Object.keys(letterMap).sort((a, b) => b.length - a.length);

            const englishLetterNames = {
                'ay':'A','aye':'A','bee':'B','cee':'C','see':'C','sea':'C','dee':'D','ee':'E','eff':'F','ef':'F','gee':'G','jee':'G',
                'aitch':'H','eitch':'H','eye':'I','jay':'J','kay':'K','el':'L','ell':'L','em':'M','en':'N','oh':'O','pee':'P',
                'cue':'Q','queue':'Q','ar':'R','are':'R','arr':'R','ess':'S','es':'S','tee':'T','tea':'T','you':'U','vee':'V',
                'doubleu':'W','ex':'X','why':'Y','zed':'Z','zee':'Z'
            };

            const digitWords = {
                'zero':'0','one':'1','two':'2','three':'3','four':'4','five':'5','six':'6','seven':'7','eight':'8','nine':'9',
                'शून्य':'0','ज़ीरो':'0','जीरो':'0','एक':'1','वन':'1','दो':'2','टू':'2','तीन':'3','थ्री':'3','चार':'4','फोर':'4',
                'पांच':'5','पाँच':'5','फाइव':'5','छह':'6','छः':'6','सिक्स':'6','सात':'7','सेवन':'7','आठ':'8','एट':'8','नौ':'9','नाइन':'9',
                'shunya':'0','ek':'1','do':'2','teen':'3','chaar':'4','char':'4','paanch':'5','panch':'5','che':'6','chheh':'6','chhah':'6',
                'saat':'7','sat':'7','aath':'8','ath':'8','nau':'9'
            };

            const tokens = clean.split(/[\s,\.\-]+/).filter(Boolean);
            let result = '';

            for (const token of tokens) {
                const tTrim = token.trim();
                if (!tTrim) continue;
                const tLower = tTrim.toLowerCase();

                if (letterMap[tTrim]) {
                    result += letterMap[tTrim];
                    continue;
                }

                if (/^[\u0900-\u097F]+$/.test(tTrim)) {
                    let remaining = tTrim;
                    let subRes = '';
                    while (remaining.length > 0) {
                        let matched = false;
                        for (const key of sortedDevKeys) {
                            if (remaining.startsWith(key)) {
                                subRes += letterMap[key];
                                remaining = remaining.slice(key.length);
                                matched = true;
                                break;
                            }
                        }
                        if (!matched) break;
                    }
                    if (remaining.length === 0 && subRes.length > 0) {
                        result += subRes;
                        continue;
                    }
                }

                if (englishLetterNames[tLower]) {
                    result += englishLetterNames[tLower];
                    continue;
                }

                if (digitWords[tTrim] !== undefined) {
                    result += digitWords[tTrim];
                    continue;
                }
                if (digitWords[tLower] !== undefined) {
                    result += digitWords[tLower];
                    continue;
                }

                if (/^[A-Za-z]$/.test(tTrim)) {
                    result += tTrim.toUpperCase();
                    continue;
                }
                if (/^\d+$/.test(tTrim)) {
                    result += tTrim;
                    continue;
                }
                if (/^[A-Za-z]{2,5}$/.test(tTrim)) {
                    result += tTrim.toUpperCase();
                    continue;
                }
                if (/^[A-Za-z0-9_-]+$/.test(tTrim)) {
                    result += tTrim.toUpperCase();
                    continue;
                }

                return null;
            }

            if (result && /^[A-Z0-9_-]{2,30}$/.test(result)) {
                return result;
            }
            return null;
        },

        /**
         * Format numeric string or identifier for digit-by-digit SpeechSynthesis read-back
         */
        formatDigitByDigitSpeech: function (value, lang) {
            const val = String(value || '').trim();
            if (!val) return '';

            const devanagariDigits = { '०':'0','१':'1','२':'2','३':'3','४':'4','५':'5','६':'6','७':'7','८':'8','९':'9' };
            const cleanVal = val.replace(/[०-९]/g, d => devanagariDigits[d] || d);

            const hindiDigits = {
                '0':'शून्य','1':'एक','2':'दो','3':'तीन','4':'चार',
                '5':'पाँच','6':'छह','7':'सात','8':'आठ','9':'नौ'
            };
            const englishDigits = {
                '0':'zero','1':'one','2':'two','3':'three','4':'four',
                '5':'five','6':'six','7':'seven','8':'eight','9':'nine'
            };

            const chars = Array.from(cleanVal);
            const formatted = chars.map(ch => {
                if (englishDigits[ch] !== undefined) {
                    return lang === 'hi' ? hindiDigits[ch] : englishDigits[ch];
                }
                return ch.toUpperCase();
            });

            return formatted.join(' ');
        },

        /**
         * Generate Field Prompt in Selected Language
         */
        generateFieldPrompt: function (fieldDef, lang) {
            if (!fieldDef) return '';
            const label = fieldDef.label || fieldDef.name || 'field';
            const type = (fieldDef.type || 'text').toLowerCase();
            const name = (fieldDef.name || '').toLowerCase();
            const labelLower = label.toLowerCase();

            // 1. SELECT / RADIO OPTIONS
            if (type === 'select' || type === 'radio') {
                const options = (fieldDef.options || []).filter(o => o.value !== '');
                const cleanLabels = options.map(o => {
                    const l = o.label || o.value;
                    return l.replace(/\s*\(.*?\)/, '').trim(); // Remove ($100 - $1,000)
                }).filter(l => l && !l.toLowerCase().includes('select'));
                const optStr = cleanLabels.slice(0, 4).join(', ');

                if (lang === 'en') {
                    return optStr
                        ? `Which option would you like for ${label}? Available options are: ${optStr}.`
                        : `Which option would you like for ${label}?`;
                }
                if (lang === 'hi') {
                    return optStr
                        ? `${label} में कौन सा विकल्प चुनना है? उपलब्ध विकल्प हैं: ${optStr}।`
                        : `${label} में कौन सा विकल्प चुनना है?`;
                }
                // Hinglish
                return optStr
                    ? `${label} mein kaun sa option select karna hai? Available options hain: ${optStr}.`
                    : `${label} mein kaun sa option select karna hai?`;
            }

            // 2. IDENTIFIER FIELDS (Member ID, Sponsor ID, User ID)
            if (this.isIdentifierField(fieldDef)) {
                if (lang === 'en') return `Please specify the ${label}—you can speak letters and digits individually.`;
                if (lang === 'hi') return `${label} बताइए—अक्षर और अंक अलग-अलग बोल सकते हैं।`;
                return `${label} batayein—letters aur digits alag-alag bol sakte hain.`;
            }

            // 3. NAME
            if (name.includes('name') || labelLower.includes('name')) {
                if (lang === 'en') return `What is your full name?`;
                if (lang === 'hi') return `आपका पूरा नाम क्या है?`;
                return `Aapka poora ${label} kya hai?`;
            }

            // 4. NUMBER / AMOUNT
            if (type === 'number' || name.includes('amount') || labelLower.includes('amount')) {
                if (lang === 'en') return `Please specify the amount for ${label}.`;
                if (lang === 'hi') return `${label} में कितनी राशि या संख्या भरनी है?`;
                return `${label} mein kitni value bharni hai?`;
            }

            // 5. CHECKBOX
            if (type === 'checkbox') {
                if (lang === 'en') return `Would you like to check or accept ${label}?`;
                if (lang === 'hi') return `क्या आप ${label} को स्वीकार या चेक करना चाहते हैं?`;
                return `Kya aap ${label} ko check ya accept karna chahte hain?`;
            }

            // 6. DATE
            if (type === 'date') {
                if (lang === 'en') return `Please provide the date for ${label}.`;
                if (lang === 'hi') return `${label} की तारीख बताइए।`;
                return `${label} ki date batayein.`;
            }

            // 7. DEFAULT / TEXT
            if (lang === 'en') return `What should be the ${label}?`;
            if (lang === 'hi') return `${label} क्या होना चाहिए?`;
            return `${label} kya hona chahiye?`;
        },

        /**
         * Generate Confirmation Text for Updated Field with digit-by-digit speech
         */
        generateFieldConfirmation: function (update, lang) {
            const label = update.label || update.field || 'Field';
            const displayVal = update.display_value || update.value || '';
            const isIdentifier = this.isIdentifierField(update);
            const isPhone = (update.type === 'tel') || String(update.name || '').includes('mobile') || String(update.name || '').includes('phone');
            const isNumeric = (update.type === 'number') || String(update.name || '').includes('amount') || /^\d+$/.test(String(displayVal));

            let spokenVal = update.spoken_value || '';
            if (!spokenVal) {
                if (isIdentifier || isPhone || isNumeric) {
                    spokenVal = this.formatDigitByDigitSpeech(displayVal, lang);
                } else {
                    spokenVal = String(displayVal).replace(/\s*\(.*?\)/, '').trim();
                }
            }

            if (lang === 'en') {
                return `Thank you, ${label} has been filled with ${spokenVal}.`;
            }
            if (lang === 'hi') {
                return `धन्यवाद, ${label} में ${spokenVal} भर दिया है।`;
            }
            return `Dhanyavaad, ${label} mein ${spokenVal} bhar diya hai.`;
        },

        /**
         * Generate Introductory Prompt
         */
        generateIntroPrompt: function (formTitle, firstFieldDef, lang) {
            const fieldQ = this.generateFieldPrompt(firstFieldDef, lang);
            const firstLabel = firstFieldDef ? (firstFieldDef.label || firstFieldDef.name) : 'first field';
            if (lang === 'en') {
                return `Certainly, I can help you fill the ${formTitle} form. Let's start with ${firstLabel}. ${fieldQ}`;
            }
            if (lang === 'hi') {
                return `बिल्कुल, मैं आपका ${formTitle} form भरने में मदद करती हूँ। चलिए, पहले ${firstLabel} से शुरू करते हैं। ${fieldQ}`;
            }
            return `Bilkul, main aapka ${formTitle} form bharne mein help karti hoon. Chaliye, pehle ${firstLabel} se shuru karte hain. ${fieldQ}`;
        },

        /**
         * Generate Completion Prompt (Zero Auto-Submit)
         */
        generateCompletionPrompt: function (formTitle, lang) {
            if (lang === 'en') {
                return `The information you provided has been filled into the relevant fields. Please review the values on screen and click Submit when ready.`;
            }
            if (lang === 'hi') {
                return `आपने जो जानकारी दी थी, वह संबंधित fields में भर दी है। कृपया values एक बार देख लीजिए और form सही होने पर स्वयं submit कीजिए।`;
            }
            return `Aapne jo jaankari di thi, woh sambandhit fields mein bhar di hai. Kripya values ek baar dekh lijiye aur form sahi hone par swayam submit kijiye.`;
        },

        /**
         * Generate Clarification Prompt
         */
        generateClarificationPrompt: function (fieldDef, lang) {
            if (!fieldDef) return (lang === 'en' ? "Could you please repeat that?" : "Kripya dobara boliye.");
            const label = fieldDef.label || fieldDef.name || 'field';
            const isIdentifier = this.isIdentifierField(fieldDef);

            if (isIdentifier) {
                if (lang === 'en') {
                    return `Sorry, I could not understand the ${label}. Please speak it once more—you can say each letter and digit individually.`;
                }
                if (lang === 'hi') {
                    return `माफ़ कीजिए, मैं ${label} ठीक से समझ नहीं पाई। कृपया उसे एक बार फिर बोलिए—अक्षर और अंक अलग-अलग बोल सकते हैं।`;
                }
                return `Maaf kijiye, main ${label} theek se samajh nahi paayi. Kripya use ek baar phir boliye—letters aur digits alag-alag बोल sakte hain.`;
            }

            const fieldQ = this.generateFieldPrompt(fieldDef, lang);
            if (lang === 'en') {
                return `Sorry, I didn't catch that. ${fieldQ}`;
            }
            if (lang === 'hi') {
                return `माफ़ कीजिए, मैं समझ नहीं पाई। ${fieldQ}`;
            }
            return `Main samajh nahi paayi. ${fieldQ}`;
        },

        /**
         * Generate Exit Prompt
         */
        generateExitPrompt: function (lang) {
            if (lang === 'en') {
                return 'Form filling has been cancelled. You can reopen anytime.';
            }
            if (lang === 'hi') {
                return 'फॉर्म भरना रद्द कर दिया गया है। आप जब चाहें दोबारा शुरू कर सकते हैं।';
            }
            return 'Form filling cancel kar di gayi hai. Aap jab chahein dobara shuru kar sakte hain.';
        },

        escapeHtml: function (str) {
            return String(str || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }
    };

    // Export to window
    window.IRA_FORM_FILLER = IRA_FORM_FILLER;

    // Auto-initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => IRA_FORM_FILLER.init());
    } else {
        IRA_FORM_FILLER.init();
    }

})(window, document);
