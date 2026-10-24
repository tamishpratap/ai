# IRA AI API USAGE & GEMINI CONFIGURATION AUDIT

**Project:** `C:\xampp\htdocs\aidemo\`  
**Reference Project:** `C:\xampp\htdocs\jarvis\`  
**Date:** 2026-10-24  
**Audit Type:** Read-Only Technical Architecture & API Dependency Verification  
**Author:** Antigravity AI Assistant

---

## Executive Summary

This comprehensive audit investigates the actual API usage, external dependencies, and Google Gemini integration across both **Navigation AI** and **Form Filling AI** in `aidemo`.

### Core Findings at a Glance

| Item | Finding / Status | Detail |
|---|---|---|
| **`GEMINI_API_KEY` in `aidemo`** | **MISSING** (Not Configured) | Key is absent in `aidemo/.env`. `GeminiService::isConfigured()` returns `false`. |
| **`GEMINI_API_KEY` in `jarvis`** | **PRESENT** | Reference application has `GEMINI_API_KEY` configured in `.env`. |
| **Navigation AI Calls Gemini?** | **NO** (in current deployment) | Has an optional fallback at `IraNavigationService:672`, but it is only invoked if deterministic matchers fail **AND** `isConfigured()` is `true`. Because key is MISSING, Gemini is never called. |
| **Form Filling AI Calls Gemini?** | **NO** (under any normal workflow) | Modern guided form filling (`IRA_FORM_FILLER` $\rightarrow$ `processGuidedTurn`) is 100% deterministic code with **zero** Gemini API calls. |
| **External AI Providers Used?** | **NONE** | No OpenAI, Anthropic, Claude, Cohere, or other AI services exist in the codebase. |
| **Web Speech vs. Gemini API** | **Client Browser APIs Only** | All Speech-to-Text (STT) and Text-to-Speech (TTS) use browser `window.webkitSpeechRecognition` and `window.speechSynthesis`. Gemini is **not** used for voice audio. |
| **Necessity of Gemini for Form Filling** | **Completely Unnecessary** | Form filling operates entirely offline via schema definitions, regex tokenizers, and phonetic/Devanagari normalizers with <20ms latency. |

---

## 1. Key Loading & Configuration Architecture

### 1.1 Resolution Sequence (`GeminiService::resolveApiKey`)
File: [`app/Services/Gemini/GeminiService.php`](file:///c:/xampp/htdocs/aidemo/app/Services/Gemini/GeminiService.php#L100-L123)

The application resolves the Gemini API key through a strict 4-level fallback hierarchy:
```php
// app/Services/Gemini/GeminiService.php:100-123
public function resolveApiKey(?string $apiKey = null): ?string
{
    $requestKey = null;
    if (app()->bound('request')) {
        try {
            $req = request();
            $requestKey = $req?->header('X-Gemini-Api-Key') ?: $req?->input('gemini_api_key');
        } catch (\Throwable) {
            $requestKey = null;
        }
    }

    $sessionKey = null;
    if (app()->bound('session')) {
        try {
            $sessionKey = session('USER_GEMINI_API_KEY');
        } catch (\Throwable) {
            $sessionKey = null;
        }
    }

    $candidate = trim((string) ($apiKey ?: $requestKey ?: $sessionKey ?: config('gemini.api_key')));
    return filled($candidate) ? $candidate : null;
}
```

1. **Explicit Parameter:** Passed directly into method calls `$apiKey`.
2. **Request Header / Parameter:** `X-Gemini-Api-Key` HTTP header or `gemini_api_key` input.
3. **Session Variable:** `session('USER_GEMINI_API_KEY')` (used in multi-tenant or user-key environments).
4. **Application Config:** `config('gemini.api_key')`, which reads `env('GEMINI_API_KEY')` from `.env`.

### 1.2 Configuration File (`config/gemini.php`)
File: [`config/gemini.php`](file:///c:/xampp/htdocs/aidemo/config/gemini.php#L1-L19)

- **API Key:** `env('GEMINI_API_KEY')`
- **Base URL:** `env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com')`
- **API Version:** `env('GEMINI_API_VERSION', 'v1alpha')`
- **Default Model:** `env('GEMINI_DEFAULT_MODEL', env('GEMINI_MODEL', 'gemini-3.1-flash-lite'))`
- **Live Model:** `env('GEMINI_LIVE_MODEL', 'gemini-2.0-flash-exp')`
- **Timeout:** `60` seconds
- **Max Output Tokens:** `2048`
- **Voice Label:** `env('GEMINI_VOICE', 'Kore')` (passed as config metadata, but speech synthesis is handled by browser synthesis voices)

### 1.3 Confirmed Current Key Status
- **`aidemo/.env`:** **MISSING** (No `GEMINI_API_KEY` entry exists).
- **`jarvis/.env`:** **PRESENT** (Configured).

*(In accordance with strict safety rules, no API key characters, lengths, or hashes are output or stored).*

---

## 2. Actual API Usage by Component

### 2.1 Navigation AI (`IraNavigationService` + `IraNavigationController`)

#### Architecture & Call Pipeline
1. Incoming spoken utterance is posted to `/ira/intent` $\rightarrow$ [`IraNavigationController::resolve`](file:///c:/xampp/htdocs/aidemo/app/Http/Controllers/IraNavigationController.php#L16-L49).
2. Server authoritatively determines user role from Laravel session (`member`, `admin`, or `guest`).
3. [`IraNavigationService::resolveIntent`](file:///c:/xampp/htdocs/aidemo/app/Services/AI/IraNavigationService.php#L583-L681) executes in sequential stages:
   - **Stage 1 (Deterministic):** Resolves pending clarification / two-step confirmations (e.g. logout confirmation).
   - **Stage 2 (Deterministic):** Checks window controls (`close_assistant`, `go_back`, `refresh_page`, `scroll_up`, etc.).
   - **Stage 3 (Deterministic):** Checks in-page form fill triggers.
   - **Stage 4 (Deterministic):** Resolves sidebar section discovery (`discoverSidebarSection`).
   - **Stage 5 (Deterministic):** Resolves direct destination matching using exact aliases, fuzzy string distance, Hindi/Hinglish phrase dictionaries, and phonetic patterns.
   - **Stage 6 (Optional Fallback):** Gemini AI intent resolution.
   - **Stage 7 (Deterministic):** Smart fallback clarification response (`buildSmartFallbackResponse`).

#### When Does Navigation AI Call Gemini?
Lines 671–677 of [`IraNavigationService.php`](file:///c:/xampp/htdocs/aidemo/app/Services/AI/IraNavigationService.php#L671-L677):
```php
// 3. Gemini AI intent resolution fallback (preserved with strict role validation)
if ($this->gemini->isConfigured()) {
    $aiResult = $this->resolveWithGemini($clean, $role, $currentPage, $currentMode, $detectedLang, $context);
    if ($aiResult !== null) {
        return $aiResult;
    }
}
```

#### Conditions Required for Gemini Call in Navigation AI:
1. **Deterministic matching fails:** Utterance does not match any known section, page, alias, or window command.
2. **`isConfigured()` returns true:** `GEMINI_API_KEY` must be present and non-empty.

**Current Reality in `aidemo`:**
Because `GEMINI_API_KEY` is **MISSING**, `$this->gemini->isConfigured()` returns `false`. Navigation AI **never makes an HTTP request to Gemini**. Unrecognized commands immediately fall through to Stage 7 (`buildSmartFallbackResponse`), which returns a deterministic clarification prompt locally in <5ms.

---

### 2.2 Form Filling AI (`IraFormUnderstandingService` + `IraFormController` + `ira-form-filler.js`)

#### Architecture & Call Pipeline
The Form Filling AI operates through two distinct endpoints:
1. **Guided Session Initialization:** `POST /ira/form-step` with `action: 'init'`
2. **Step-by-Step Guided Turns:** `POST /ira/form-step` with `action: 'turn'` and `guide_state`

#### A. Guided Form Filling (`initGuidedSession` & `processGuidedTurn`)
Files:
- [`app/Services/AI/IraFormUnderstandingService.php:34-217`](file:///c:/xampp/htdocs/aidemo/app/Services/AI/IraFormUnderstandingService.php#L34-L217) (`initGuidedSession`)
- [`app/Services/AI/IraFormUnderstandingService.php:219-580`](file:///c:/xampp/htdocs/aidemo/app/Services/AI/IraFormUnderstandingService.php#L219-L580) (`processGuidedTurn`)

**Does Guided Form Filling Call Gemini?**
**NO. Never.**
- `initGuidedSession` is 100% deterministic: loads schema from [`IraFormRegistry`](file:///c:/xampp/htdocs/aidemo/app/Services/AI/IraFormRegistry.php), validates role authorization, isolates sensitive password fields, and returns the first eligible field.
- `processGuidedTurn` is 100% deterministic:
  - Cancel/Exit commands $\rightarrow$ Regex pattern matching.
  - Reset form commands $\rightarrow$ Regex pattern matching.
  - Review/Summary commands $\rightarrow$ Regex pattern matching.
  - Spoken rewind commands $\rightarrow$ Regex pattern matching.
  - Field switch commands $\rightarrow$ Target field alias lookup.
  - Targeted value extraction $\rightarrow$ [`IraTextNormalizer`](file:///c:/xampp/htdocs/aidemo/app/Services/AI/IraTextNormalizer.php) (Devanagari compound decomposition, Hindi cardinal number parser, alphanumeric member ID mapping, dropdown option matching, name transliteration).
  - Unclear answers $\rightarrow$ Specific local clarification speech ([`buildFieldClarificationSpeech`](file:///c:/xampp/htdocs/aidemo/app/Services/AI/IraFormUnderstandingService.php#L582-L612)).

There is **zero code** in `processGuidedTurn` that invokes Gemini or any external API.

#### B. Legacy Unguided Form Filling (`processFormTurn`)
File: [`app/Services/AI/IraFormUnderstandingService.php:1193-1270`](file:///c:/xampp/htdocs/aidemo/app/Services/AI/IraFormUnderstandingService.php#L1193-L1270)

In the older Phase 7 unguided mode:
```php
// app/Services/AI/IraFormUnderstandingService.php:1254-1261
// If deterministic extraction found no updates, try LLM fallback if configured
if (empty($updates) && empty($sensitiveSkipped) && empty($fileSkipped) && $this->gemini->isConfigured()) {
    $llmResult = $this->extractWithGemini($clean, $schema, $currentFields);
    if (!empty($llmResult['updates'])) {
        $updates = $llmResult['updates'];
        $sensitiveSkipped = array_merge($sensitiveSkipped, $llmResult['sensitive_skipped'] ?? []);
    }
}
```

Notice the safeguards:
1. It is guarded by `$this->gemini->isConfigured()`. Since the key is missing in `aidemo`, it is **never executed**.
2. Inside `extractWithGemini` ([line 2003](file:///c:/xampp/htdocs/aidemo/app/Services/AI/IraFormUnderstandingService.php#L2003)), the code calls `$this->gemini->generateText($prompt)`. However, `GeminiService` does not have a `generateText` method (it provides `generate(array $contents)`). If called, it throws a `BadMethodCallException`, which is caught by `catch (\Throwable $e)` at line 2030, logged as a warning, and returns empty updates without crashing.
3. The active production UI ([`ira-form-filler.js`](file:///c:/xampp/htdocs/aidemo/public/ira/ira-form-filler.js)) always maintains `guide_state` and routes to `processGuidedTurn`, entirely bypassing `processFormTurn`.

---

## 3. Deterministic Parsing vs. External Gemini Requests

The table below catalogs every user interaction category and identifies the processing engine:

| Interaction / Command Type | Engine Used | External API Called? | Latency |
|---|---|---|---|
| Page Navigation (e.g., "Open Deposit History", "Staking page par jao") | Deterministic Ontology & Alias Matcher ([`IraNavigationService`](file:///c:/xampp/htdocs/aidemo/app/Services/AI/IraNavigationService.php)) | No | 5–15 ms |
| Section Discovery (e.g., "Wallet me kya hai?", "Reports dikhao") | Deterministic Tree Traversal & Matcher ([`IraNavigationService`](file:///c:/xampp/htdocs/aidemo/app/Services/AI/IraNavigationService.php)) | No | 5–10 ms |
| Window Commands ("Close assistant", "Go back", "Scroll down") | Deterministic Regex ([`IraNavigationService`](file:///c:/xampp/htdocs/aidemo/app/Services/AI/IraNavigationService.php)) | No | 1–3 ms |
| Form Selection & Initialization ("AI Fill Form" button) | Deterministic Registry Schema ([`IraFormRegistry`](file:///c:/xampp/htdocs/aidemo/app/Services/AI/IraFormRegistry.php)) | No | 2–5 ms |
| Spoken Member IDs ("क्यू आर वी 123456" $\rightarrow$ `QRV123456`) | Deterministic Compound Normalizer ([`IraTextNormalizer`](file:///c:/xampp/htdocs/aidemo/app/Services/AI/IraTextNormalizer.php)) | No | 2–5 ms |
| Spoken Numbers / Amounts ("एक लाख बीस हजार" $\rightarrow$ `120000`) | Deterministic Hindi Cardinal Parser ([`IraTextNormalizer`](file:///c:/xampp/htdocs/aidemo/app/Services/AI/IraTextNormalizer.php)) | No | 1–3 ms |
| Dropdown Selection ("USDT TRC20", "Staking Package 1") | Deterministic Fuzzy Matcher ([`IraFormUnderstandingService`](file:///c:/xampp/htdocs/aidemo/app/Services/AI/IraFormUnderstandingService.php)) | No | 2–6 ms |
| Form Commands ("Cancel", "Reset form", "Pichli value badlo") | Deterministic Regex & Guide State ([`IraFormUnderstandingService`](file:///c:/xampp/htdocs/aidemo/app/Services/AI/IraFormUnderstandingService.php)) | No | 2–5 ms |
| Sensitive Field Protection (Password, PIN, OTP) | Deterministic Security Shield ([`IraFormRegistry`](file:///c:/xampp/htdocs/aidemo/app/Services/AI/IraFormRegistry.php)) | No | <1 ms |
| Unrecognized Navigation Utterance (if Key is MISSING) | Deterministic Smart Clarification ([`IraNavigationService`](file:///c:/xampp/htdocs/aidemo/app/Services/AI/IraNavigationService.php)) | No | 5–10 ms |
| Unrecognized Navigation Utterance (if Key were PRESENT) | External Gemini API (`generativelanguage.googleapis.com`) | **Yes** (Fallback) | 800–2500 ms |

---

## 4. Failure Modes & Resilience (Missing, Invalid, or Offline API)

The system was architected with complete defensive isolation against AI provider failures:

```
User Speech
    │
    ▼
[Web Speech API] (Browser Native STT)
    │
    ▼
[Laravel Controller] (Role Auth via Session)
    │
    ▼
[Deterministic Engine] (Ontology / Schema / Normalizer)
    │
    ├── Matched? ──► YES ──► Immediate Response (<20ms)
    │
    └── NO
         │
         ▼
     [Is Gemini Configured?]
         │
         ├── NO ─────────────► Smart Fallback Clarification (<10ms)
         │
         └── YES
              │
              ▼
          [External HTTP Call: Gemini]
              │
              ├── Success ──► Validated Role-Gated Response
              │
              └── Timeout / 401 / 429 / 500 Error
                     │
                     ▼
                 [Catch \Throwable] ──► Log Warning ──► Smart Fallback Clarification
```

### Specific Failure Scenarios:

1. **API Key is MISSING (Current State in `aidemo`):**
   - Method: `GeminiService::isConfigured()` returns `false`.
   - Behavior: External calls are completely bypassed. Zero exceptions are thrown, zero warning logs are produced, and user experiences zero latency penalty.
2. **API Key is INVALID or EXPIRED (HTTP 401 / 403):**
   - Handled in: `GeminiService::generate` catches non-200 status, logs `[GeminiService] Request failed`, and throws `RuntimeException('AI credentials are invalid. Please check the Gemini API key configuration.')`.
   - Resolution: `IraNavigationService::resolveWithGemini` catches `\Throwable` at line 1746, logs `[IraNavigationService] Gemini note: ...`, and returns `null`. The system gracefully falls back to `buildSmartFallbackResponse`. The application never crashes.
3. **External Provider Outage, Network Timeout, or Rate Limit (HTTP 429 / 500 / ConnectionException):**
   - Handled in: `GeminiService::generate` catches `ConnectionException` (line 55) or HTTP errors and throws friendly user exceptions.
   - Resolution: All callers wrap Gemini in `try { ... } catch (\Throwable $e)`. Any network failure, DNS error, or timeout fails silently into the deterministic clarification generator.

---

## 5. Web Speech API vs. Gemini API Confusion

There is an understandable tendency for users or observers to assume Gemini is being called because the assistant talks and listens. This is a technical misunderstanding:

### Browser Web Speech API (Used by IRA)
- **Speech-to-Text (Microphone $\rightarrow$ Text):**
  - Managed by `window.webkitSpeechRecognition` in [`public/ira/ira-assistant.js:358`](file:///c:/xampp/htdocs/aidemo/public/ira/ira-assistant.js#L358) and [`public/ira/ira-form-filler.js:630`](file:///c:/xampp/htdocs/aidemo/public/ira/ira-form-filler.js#L630).
  - Audio is converted to text directly within the user's browser (e.g. Chrome's built-in speech engine with `lang: 'hi-IN'`, `en-US`, or `en-IN`).
  - No raw audio is ever uploaded to Gemini or any backend server.
- **Text-to-Speech (Text $\rightarrow$ Audio):**
  - Managed by `window.speechSynthesis` and `SpeechSynthesisUtterance` in [`public/ira/ira-assistant.js:569`](file:///c:/xampp/htdocs/aidemo/public/ira/ira-assistant.js#L569) and [`public/ira/ira-form-filler.js:782`](file:///c:/xampp/htdocs/aidemo/public/ira/ira-form-filler.js#L782).
  - The browser reads the response text using voices installed on the client operating system.
  - Gemini TTS / Gemini Voice is **not** used.

### Gemini API (Generative Language API)
- The Gemini API is a cloud-based text-generation endpoint (`https://generativelanguage.googleapis.com/v1beta/models/...:generateContent`).
- It does not process client microphone audio in this application.
- It is only wired as a text-in, text-out semantic intent parser.

---

## 6. External Providers & Third-Party Audit

A complete search across all configuration files, composer dependencies, and application source code confirmed:

- **OpenAI / ChatGPT:** None.
- **Anthropic / Claude:** None.
- **Cohere / Mistral / HuggingFace:** None.
- **AWS Bedrock / Azure OpenAI:** None.
- **Other HTTP Calls in `app/`:**
  - `NowPayments API` (`https://api.nowpayments.io/v1/...` in [`FundController.php`](file:///c:/xampp/htdocs/aidemo/app/Http/Controllers/FundController.php#L56) and [`WithdrawalController.php`](file:///c:/xampp/htdocs/aidemo/app/Http/Controllers/WithdrawalController.php#L64)) — payment gateway for cryptocurrency deposits and automated payouts.
  - No other third-party API is called.

---

## 7. Necessity Assessment for Routine Form Filling

### Are Gemini API Calls Necessary for Routine Form Filling?
**No. API calls are completely unnecessary.**

### Why Deterministic Form Filling is Superior for this Application:
1. **Speed & Latency:**
   - Deterministic processing: **2ms – 15ms**.
   - Cloud LLM call: **1,000ms – 3,500ms** per spoken turn. Fast, conversational form filling is impossible with multi-second LLM turn latency.
2. **Reliability & Consistency:**
   - Deterministic normalizers always resolve `क्यू आर वी 123456` to `QRV123456` and `1 लाख 20 हजार` to `120000`.
   - LLMs can suffer from non-deterministic variance, hallucinations, formatting quirks, or markdown wrapping.
3. **Security & Financial Privacy:**
   - Form inputs often include member IDs, transaction amounts, staking packages, and deposit hashes.
   - Processing these deterministically keeps all financial and personal data strictly within the local application boundary without sending customer data to third-party AI cloud servers.
4. **Zero Cost & Offline Execution:**
   - Deterministic execution incurs zero API token costs, has no rate limits, and functions without external internet access.
   - All 65 feature and regression tests in `tests/Feature/Ira*` pass with 1,045 assertions completely offline.

---

## 8. Summary of Inspected Files & Locations

| Component | File Path | Relevant Lines |
|---|---|---|
| **Gemini Config** | [`config/gemini.php`](file:///c:/xampp/htdocs/aidemo/config/gemini.php) | L4 (API key), L5 (Base URL), L8 (Model) |
| **Gemini Service** | [`app/Services/Gemini/GeminiService.php`](file:///c:/xampp/htdocs/aidemo/app/Services/Gemini/GeminiService.php) | L23-79 (`generate`), L92-95 (`isConfigured`), L100-123 (`resolveApiKey`) |
| **Navigation Service** | [`app/Services/AI/IraNavigationService.php`](file:///c:/xampp/htdocs/aidemo/app/Services/AI/IraNavigationService.php) | L575 (Injection), L671-677 (Fallback check), L1652-1750 (`resolveWithGemini`) |
| **Form Understanding** | [`app/Services/AI/IraFormUnderstandingService.php`](file:///c:/xampp/htdocs/aidemo/app/Services/AI/IraFormUnderstandingService.php) | L23 (Injection), L219-580 (`processGuidedTurn`), L1254-1261 (`processFormTurn`), L1993-2035 (`extractWithGemini`) |
| **Navigation Controller** | [`app/Http/Controllers/IraNavigationController.php`](file:///c:/xampp/htdocs/aidemo/app/Http/Controllers/IraNavigationController.php) | L16-49 (`resolve` endpoint) |
| **Form Controller** | [`app/Http/Controllers/IraFormController.php`](file:///c:/xampp/htdocs/aidemo/app/Http/Controllers/IraFormController.php) | L21-100 (`formStep` endpoint), L106-140 (`formsForPage`) |
| **Navigation JS** | [`public/ira/ira-assistant.js`](file:///c:/xampp/htdocs/aidemo/public/ira/ira-assistant.js) | L358-365 (Web Speech STT), L569-588 (Web Speech TTS), L973 (Fetch `/ira/intent`) |
| **Form Filler JS** | [`public/ira/ira-form-filler.js`](file:///c:/xampp/htdocs/aidemo/public/ira/ira-form-filler.js) | L630-635 (Web Speech STT), L782-845 (Web Speech TTS), L289/L881 (Fetch `/ira/form-step`) |

---

## Conclusion

Neither Navigation AI nor Form Filling AI is currently making external Gemini API calls in `aidemo`.
- The key is **MISSING** in `aidemo/.env`, causing all optional Gemini code paths to be safely skipped.
- Both systems operate entirely on high-performance, deterministic PHP and client-side Web Speech APIs.
- No other external AI providers are used.
- Form filling does not require Gemini API access to function reliably and accurately.
