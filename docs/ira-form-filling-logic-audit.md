# IRA Voice AI — Form-Filling Logic Audit & Architecture Report

**Document Version:** 1.0.0  
**Audit Date:** October 24, 2026  
**Target Application:** `C:\xampp\htdocs\aidemo\` (Laravel 10 MLM & Staking Platform)  
**Reference Application:** `C:\xampp\htdocs\jarvis\` (Laravel 10 Hospital Management System with Voice AI)  
**Mode:** Strict Read-Only Architectural Audit — No Application Code Altered  

---

## 1. Executive Summary & Audit Scope

This audit evaluates the feasibility, mechanics, and design requirements for introducing **voice-driven conversational form filling** into the **AI Demo (`aidemo`)** platform.

To build an application-aware form assistant that is reliable, secure, and natural to converse with, we audited the existing voice assistant implementation in the reference project (`jarvis`), specifically dissecting its voice extraction pipelines, entity resolution logic, two-stage transactional confirmation model, and frontend DOM manipulation techniques.

Concurrently, we conducted an exhaustive audit of `aidemo`'s Member and Admin portals, analyzing every form, route, controller action, validation rule, dynamic client-side script, and transaction risk profile.

### Core Audit Findings
1. **Reference Architecture Specialization:** The reference system (`jarvis`) implements sophisticated multi-field extraction, entity resolution against Eloquent models, and a secure session-backed two-stage confirmation pipeline with HMAC tokens. However, **it is hardcoded for exactly two specific hospital workflows** (`#appointmentForm` and `#inquiryForm`). It is **not** a generic form engine.
2. **Target Application Heterogeneity:** The target application (`aidemo`) contains **15 distinct Member forms** and **17 Admin forms**, ranging from low-risk search filters to high-risk financial fund transfers, staking investments, and withdrawal approvals. Hardcoding form workflows in the style of `jarvis` would lead to an unmaintainable codebase.
3. **Architectural Gap:** Direct translation of `jarvis` code is impossible without abstracting its extraction and DOM-mutation layers into a **declarative Form Registry & Schema Engine**.
4. **Safety & Financial Gating:** While `jarvis` gates appointment booking and destructive deletions behind affirmative voice confirmation (`confirm_action`), `aidemo` requires an even stricter transaction boundary: financial mutations require both **explicit spoken value confirmation** and handling of **security transaction passwords / PINs**.

---

## 2. Reference Architecture & Turn Flow Analysis (Jarvis)

In `C:\xampp\htdocs\jarvis\`, voice interactions are managed through a coordinated multi-tier architecture spanning the browser DOM, Web Speech API, navigation router, and specialized form-understanding micro-services.

```
+-----------------------------------------------------------------------------------+
|                                  BROWSER (DOM)                                    |
|  1. User speaks: "Mujhe Dr. Sharma se cardiology ke liye kal appointment chahiye"  |
|  2. Web Speech API transcribes -> transcripts captured by `ira-assistant.js`      |
|  3. Frontend inspects DOM: active page, form visible, existing input field values |
|  4. POST payload to `/member/appointments/voice-step` or `/ira/intent`            |
+----------------------------------------+------------------------------------------+
                                         | JSON Request
                                         v
+-----------------------------------------------------------------------------------+
|                              LARAVEL BACKEND PIPELINE                             |
|  A. IraNavigationService.php: Analyzes intent (navigation vs form action)         |
|  B. IraFormUnderstandingService.php:                                              |
|     - Multi-field extraction (Regex + Gemini LLM fallback)                        |
|     - Entity resolution against DB (Doctors, Hospitals, Departments, Slots)       |
|     - Slot availability check via HospitalAiService                               |
|     - Conversational turn state tracking & missing required fields detection      |
|  C. IraActionService.php:                                                         |
|     - Evaluates risk: if booking, prepares pending action with HMAC token        |
|     - Generates bilingual TTS prompt (Hindi/Hinglish/English)                     |
+----------------------------------------+------------------------------------------+
                                         | JSON Response
                                         v
+-----------------------------------------------------------------------------------+
|                             FRONTEND DOM MANIPULATION                             |
|  1. `ira-assistant.js` unpacks `form_updates` payload                             |
|  2. Locates target inputs by ID: `#patient_name`, `#hospital_id`, `#doctor_id`    |
|  3. Assigns values: `el.value = update.value`                                     |
|  4. Dispatches synthetic DOM events: `el.dispatchEvent(new Event('change'))`      |
|  5. Triggers dynamic cascading UI: `populateServices()`, `fetchSlots()`          |
|  6. Visual feedback: `flashField()`, scrolls field into view                      |
|  7. Plays TTS speech audio / speaks response                                      |
+-----------------------------------------------------------------------------------+
```

### Detailed Class & File Analysis in Reference (`jarvis`)

#### A. Backend Form Understanding Service
- **File:** `C:\xampp\htdocs\jarvis\app\Services\AI\IraFormUnderstandingService.php` (58,416 bytes)
- **Primary Methods:**
  - `processAppointmentTurn(string $transcript, array $currentData, int $memberId): array` (Lines 38–184)
    Manages appointment conversational flow. Extracts fields, validates against database, detects missing slots or doctor selections, checks available timeslots via `HospitalAiService::getAvailableSlots`, and formulates follow-up queries.
  - `processInquiryTurn(string $transcript, array $currentData): array` (Lines 186–274)
    Manages contact/inquiry forms. Validates name, email, phone, and subject/message.
  - `extractAppointmentFields(string $transcript, array $existingData): array` (Lines 276–448)
    Dual-engine extraction:
    1. Deterministic Regex: Extracts phone numbers (`/\b[6-9]\d{9}\b/`), dates (`kal`, `parso`, `tomorrow`, `next monday`, DD/MM/YYYY), time (`subah 10 baje`, `evening 4pm`), department keywords (`cardio`, `ortho`, `dental`), and doctor honorifics (`Dr. \w+`).
    2. Fallback to Gemini: When confidence is low or complex sentences are used, passes transcript to `extractWithGemini()` (Lines 450–520) using structured JSON output schemas.
  - `resolveEntities(array $fields): array` (Lines 522–610)
    Fuzzy-matches extracted strings against Eloquent models: `Hospital::where('name', 'LIKE', "%$hospital%")->first()`, `Doctor::where('name', 'LIKE', "%$doctor%")->first()`.

#### B. Backend Action & Safety Confirmation Service
- **File:** `C:\xampp\htdocs\jarvis\app\Services\AI\IraActionService.php` (63,205 bytes)
- **Primary Methods:**
  - `evaluateIntent(string $transcript, array $context): array` (Lines 60–140)
    Classifies user utterance into: `NAVIGATE`, `FORM_FILL`, `CONFIRM_ACTION`, `CANCEL_ACTION`, `QUERY`, or `UNKNOWN`.
  - `preparePendingAction(string $actionType, array $payload, int $userId): array` (Lines 180–235)
    Constructs a session-persisted pending action record:
    ```php
    $token = hash_hmac('sha256', uniqid($actionType, true), config('app.key'));
    session()->put('ira_pending_action', [
        'token' => $token,
        'action' => $actionType,
        'payload' => $payload,
        'user_id' => $userId,
        'expires_at' => now()->addMinutes(10)->timestamp
    ]);
    ```
  - `confirmAction(string $transcript, string $token): array` (Lines 240–320)
    Verifies voice affirmative triggers (`haan`, `yes`, `confirm karo`, `theek hai`, `proceed`). Checks token match and expiry before executing state mutation.

#### C. Frontend Assistant Controller & DOM Hooks
- **File:** `C:\xampp\htdocs\jarvis\public\ira\ira-assistant.js` (85,410 bytes)
- **Primary Methods:**
  - `detectCurrentForm(): string|null` (Lines 1120–1160)
    Checks if `#appointmentForm` or `#inquiryForm` exists and is visible in DOM.
  - `getAppointmentFormData(): object` (Lines 1165–1210)
    Scrapes existing values from DOM inputs (`#patient_name`, `#patient_mobile`, `#hospital_id`, `#department_id`, `#doctor_id`, `#appointment_date`, `#time_slot`).
  - `applyAppointmentFormUpdates(updates): void` (Lines 1215–1310)
    Iterates over updates, updates values, dispatches `Event('change')` and `Event('input')`, and calls cascading triggers:
    ```javascript
    if (updates.hospital_id) {
        $('#hospital_id').val(updates.hospital_id).trigger('change');
        this.populateDepartments(updates.hospital_id);
    }
    ```
  - `flashField(fieldElement): void` (Lines 1315–1340)
    Applies pulsating CSS highlight (`box-shadow: 0 0 10px #22c55e`) and scrolls the target input into viewport.

---

## 3. 20-Point Capability Classification Table

Every capability required for voice-driven form filling was audited against the reference codebase (`jarvis`) and evaluated for its technical compatibility with `aidemo`:

| # | Capability | Reference Status (`jarvis`) | Code Evidence (File & Line) | Target Application Applicability (`aidemo`) |
|---|------------|-----------------------------|------------------------------|---------------------------------------------|
| 1 | **Detect currently open form on page** | Partially Implemented | `ira-assistant.js:1120` (`detectCurrentForm`) | **High**. Needs generalization. Target has 32+ forms across different URLs; must detect form by route and active DOM container. |
| 2 | **Match voice commands to specific form fields** | Implemented (Hardcoded) | `IraFormUnderstandingService.php:280-440` | **Crucial**. Hardcoding cannot scale across 32 forms. Requires a declarative Form Registry mapping field aliases to DOM IDs/names. |
| 3 | **Extract entity values (names, amounts, dates, text, phone)** | Implemented | `IraFormUnderstandingService.php:295` (Regex + Gemini) | **Crucial**. In `aidemo`, entities are Member IDs (e.g. `GAB123456`), Dollar amounts (`$100`), Package tiers, and Wallet addresses. |
| 4 | **Resolve select dropdown options (fuzzy match label vs value)** | Implemented | `IraFormUnderstandingService.php:540` (`resolveEntities`) | **Crucial**. In `aidemo`, resolves Package options (`Silver`, `Gold`, `Platinum`, `Diamond`) and Wallet types (`Wallet`, `Fund Wallet`). |
| 5 | **Handle multi-field voice input in single turn** | Implemented | `IraFormUnderstandingService.php:58-90` | **Crucial**. User says: *"Transfer 50 dollars to member GAB10025 with note payment"*. Must extract recipient, amount, and note in one turn. |
| 6 | **Conversational multi-turn slot filling** | Implemented | `IraFormUnderstandingService.php:110-184` | **Crucial**. If user says *"Mujhe fund transfer karna hai"*, assistant must reply: *"Kripya member ID aur amount batayein."* |
| 7 | **Form state persistence across turns** | Implemented | `IraFormUnderstandingService.php:42` (`$currentData`) | **Crucial**. State kept in backend session or frontend assistant memory until form is completely filled or cancelled. |
| 8 | **Visual feedback on filled fields (highlighting, scrolling)** | Implemented | `ira-assistant.js:1315` (`flashField`) | **Crucial**. Input fields must flash green and scroll smoothly into view so user visually verifies the voice-filled data. |
| 9 | **DOM event dispatching after fill (change, input, blur)** | Implemented | `ira-assistant.js:1240` (`dispatchEvent(new Event('change'))`) | **Essential**. In `aidemo`, dynamic listeners (e.g. `/getMember` lookup on `#memberid`) depend on `input`/`change` events. |
| 10 | **Dynamic dependent field handling (cascades, AJAX)** | Implemented (Custom) | `ira-assistant.js:1255` (`populateServices`) | **Crucial**. In `aidemo`, typing `#sponsor` or `#memberid` triggers AJAX to fetch and display the user's full name. |
| 11 | **Checkbox and radio button interaction** | Not Implemented | *Absent in jarvis JS* | **Required**. Needed for `#defaultCheck1` (Terms checkbox in signup) and `#statusActive` / `#statusInactive` (Popup image). |
| 12 | **File upload handling or detection** | Not Implemented | *Absent in jarvis JS* | **Informational Only**. Voice cannot transmit files. Assistant must detect file inputs and verbally instruct user: *"Kripya file browser se select karein."* |
| 13 | **Validation error detection and voice feedback** | Partially Implemented | `IraFormUnderstandingService.php:125` | **Crucial**. If backend validation fails or balance is insufficient, assistant must speak the exact error message clearly. |
| 14 | **Two-stage confirmation before final submission** | Implemented | `IraActionService.php:180-320` (`ira_pending_action`) | **Mandatory**. Final submission must never happen immediately. Assistant must summarize values and ask: *"Kya aap ise submit karna chahte hain?"* |
| 15 | **Destructive / financial action safety gating** | Implemented | `IraActionService.php:210` (HMAC token + expiry) | **Mandatory**. Critical in `aidemo` for fund transfers, staking investments, and admin fund credit/debit. |
| 16 | **Disambiguation when multiple fields match voice instruction** | Partially Implemented | `IraFormUnderstandingService.php:570` | **Required**. Example: Password vs Transaction Password, or Main Wallet vs P2P Wallet. Assistant must clarify which one to update. |
| 17 | **Cancellation / reset voice command handling** | Implemented | `IraActionService.php:115` (`CANCEL_ACTION`) | **Essential**. User says *"Cancel karo"*, *"Ruk jao"*, or *"Form clear karo"*; assistant flushes pending action and clears inputs. |
| 18 | **Fallback to LLM / Gemini when rule-based extraction fails** | Implemented | `IraFormUnderstandingService.php:450` (`extractWithGemini`) | **High Value**. Provides resilience against complex Hinglish sentences and phrasing variations. |
| 19 | **Speech recognition noise / transcript correction** | Partially Implemented | `IraFormUnderstandingService.php:310` | **Crucial**. Corrects numbers spoken in Hindi (*"pachaas"* -> `50`, *"ek sau"* -> `100`) and fixes phonetic Member IDs (*"G A B"* -> `GAB`). |
| 20 | **Voice-driven form submission execution** | Implemented | `ira-assistant.js:1290` (`$form.submit()`) | **Mandatory**. Executes `form.submit()` or triggers the submit button click once voice confirmation is affirmative. |

---

## 4. Deep Dive: Select Option Resolution & Dynamic Cascades

In `jarvis`, dropdown matching and cascading updates were coded specifically for hospital entities:
- **Label vs Value Resolution:** When a user says *"Cardiology"*, the service searches the `Department` model for `name LIKE '%cardiology%'` and resolves its primary key `id = 4`. The frontend receives `{ field: 'department_id', value: 4, label: 'Cardiology' }` and selects `<option value="4">Cardiology</option>`.
- **Dynamic Cascades:** In `jarvis`, selecting a hospital loaded its departments, which then loaded its doctors, which then triggered an AJAX call to calculate available time slots.

### Adaptation to `aidemo`:
In `aidemo`, dropdowns and cascades follow different patterns:
1. **Package Tiers (`member/staking/staking.blade.php`, `admin/members/free-pin-account.blade.php`):**
   - `<select name="stak_package" id="stak_package">` has options:
     - `Silver` ($100 – $1,000)
     - `Gold` ($1,100 – $5,000)
     - `Platinum` ($5,100 – $10,000)
     - `Diamond` ($10,100+)
   - Voice assistant must understand spoken package names (*"Gold package"* -> value `Gold`), and cross-validate that the spoken amount falls within that package's allowable bounds.
2. **Wallet Types (`admin/funds/add-funds.blade.php`, `admin/funds/deduct-funds.blade.php`):**
   - Options: `Wallet` (Main) vs `Fund Wallet` (P2P).
   - Voice assistant must map *"Main balance"* or *"Normal wallet"* -> `Wallet`, and *"P2P balance"* or *"Fund wallet"* -> `Fund Wallet`.
3. **Date Filter Presets (`admin/partials/history-date-filter.blade.php`):**
   - Options: `today`, `yesterday`, `last_7_days`, `last_30_days`, `this_month`, `last_month`, `this_year`, `custom`.
   - Voice assistant must map natural temporal phrases (*"Is mahine ka history dikhao"* -> preset `this_month`).

---

## 5. Two-Stage Confirmation, Safety Boundaries & Transaction Gating

The two-stage confirmation pipeline in `jarvis` (`IraActionService.php`) is its strongest architectural pattern and is essential for `aidemo`:

### The Reference Mechanism:
1. **First Stage (Fill & Summarize):** The assistant populates the DOM fields, validates completeness, and synthesizes a spoken confirmation request:
   *"Maine GAB10045 ke liye 50 dollars transfer form me bhar diya hai. Kya main ise submit karun?"*
2. **Pending Action Session Token:** The backend generates an HMAC token stored in the user's session with a 10-minute TTL:
   ```php
   session()->put('ira_pending_action', [
       'token' => $token,
       'action' => 'SUBMIT_FORM',
       'form_id' => 'p2pTransferForm',
       'payload' => $validatedPayload,
       'expires_at' => now()->addMinutes(10)->timestamp,
   ]);
   ```
3. **Second Stage (Execution Gating):**
   - If the user says *"Haan"*, *"Yes"*, *"Proceed"*, or *"Submit karo"*, the frontend passes the token back to `/ira/execute-action`.
   - If user says *"Nahi"*, *"Cancel"*, or modifies a field, the pending token is immediately discarded.

### Strict Safety Rules for `aidemo`:
- **Read-Only / Search Forms (Low Risk):** Forms like Genealogy search (`#searchGenelogy`) or History Date Filters may auto-submit immediately upon voice command.
- **Support Tickets / Profile Edits (Medium Risk):** Require simple affirmative confirmation before submission.
- **Financial Transactions (Critical Risk):**
  - Fund Transfer (`/fundTrans`)
  - Wallet to P2P Transfer (`/walletP2pTransfer`)
  - Main Wallet Withdrawal (`/initWithdrawal`)
  - Staking Creation (`/createStaking`)
  - Admin Fund Add/Deduct (`/addMemFunds`, `/deductMemFunds`)
  - **MANDATORY POLICY:** In addition to voice confirmation, any transaction requiring a `txn_password` or `security_pin` MUST have the user enter or verify their security credential. Voice AI must never bypass password/PIN verification.

---

## 6. Target Compatibility Matrix (Jarvis vs Aidemo)

| Architectural Dimension | Reference (`jarvis`) | Target (`aidemo`) | Compatibility / Migration Strategy |
|---|---|---|---|
| **Framework & Version** | Laravel 10.x, PHP 8.2 | Laravel 10.x, PHP 8.2 | **100% Compatible**. Service providers, sessions, routing, and controller architectures match exactly. |
| **Authentication System** | Multi-guard (Admin, Member, Patient) | Session-based (`session('MEMBER_LOGIN')`, `session('ADMIN_LOGIN')`) | **Fully Compatible**. Can read user ID directly from active session. |
| **Number of Active Forms** | 2 dedicated forms | 32 forms (15 Member, 17 Admin) | **Incompatible with hardcoding**. Must implement a centralized Schema Registry. |
| **CSRF Handling** | `@csrf` in Blade + `X-CSRF-TOKEN` in AJAX | Mix of `@csrf` token and hidden `<input id="csrf">` elements | **Compatible**. JS must read CSRF from meta tag or hidden input. |
| **Client-Side Framework** | jQuery + Bootstrap 5 + Custom JS | jQuery + Bootstrap 5 (Member) / Bootstrap 4 (Admin) | **Fully Compatible**. Standard DOM events (`dispatchEvent`, jQuery `.trigger('change')`) work identically. |
| **Entity Lookup Mechanism** | Eloquent queries on Hospital/Doctor | AJAX endpoints (`/getMember`, `/getSponname`) + Eloquent models | **Compatible**. Assist service can trigger existing AJAX routes or query models directly. |
| **Form IDs & Selectors** | Strict IDs (`#appointmentForm`, `#inquiryForm`) | Mixed: Some have IDs (`#stakingForm`, `#myForm`), many have no ID | **Requires Selector Mapping**. Assistant must resolve form by container/card class or action URL. |

---

## 7. Adaptation Blueprint for Laravel 10 Aidemo

To achieve robust voice-driven form filling across all 32 forms in `aidemo`, we must avoid the hardcoded trap of `jarvis` and implement a **Declarative Form Schema Registry**.

### Architectural Blueprint:

```
[User Utterance] 
      │
      ▼
[ira-assistant.js] ── (Detects Current URL / Active Form Selector)
      │
      ▼ POST /ira/form-step { transcript, form_key, current_fields }
[IraFormController.php]
      │
      ▼
[IraFormRegistry.php] ── (Loads Form Schema: fields, types, aliases, rules, risk)
      │
      ▼
[IraFormUnderstandingService.php] ── (Extracts values, resolves aliases, checks completeness)
      │
      ├──> If missing fields: Returns TTS follow-up prompt
      └──> If complete & high-risk: Generates confirmation token & asks user to confirm
      │
      ▼ JSON Response: { form_updates: [...], speech: "...", requires_confirm: true }
[ira-assistant.js]
      │
      ├──> Highlights & populates DOM inputs
      ├──> Dispatches 'input' and 'change' events
      └──> Speaks response to user
```

### Proposed Core Components for Phase 7:
1. **`app/Services/AI/IraFormRegistry.php`:**
   A centralized repository defining schemas for all supported forms (Member and Admin). Each schema declares:
   - Target route / form selector
   - Field definitions (HTML ID/name, entity type, spoken aliases in English/Hindi)
   - Mandatory vs Optional status
   - Risk classification (`LOW`, `MEDIUM`, `HIGH_FINANCIAL`)
2. **`app/Services/AI/IraFormUnderstandingService.php` (Generic Engine):**
   Extracts values by matching spoken aliases against registered field definitions, converts spoken Hindi numerals into numeric strings, normalizes Member IDs, and checks required slots.
3. **`public/ira/ira-assistant.js` (Generic Form Handler):**
   A dynamic DOM updater that accepts field updates by selector, dispatches bubbling change events, highlights elements with CSS animations, and hooks into form submissions.

---
*End of Deliverable 1 (Logic Audit & Architecture Report)*
