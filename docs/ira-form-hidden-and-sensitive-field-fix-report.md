# IRA Voice Form Filling AI — Hidden, Invisible, Read-Only & Sensitive Input Elimination Report

**Project:** `C:\xampp\htdocs\aidemo\`  
**Target Component:** Independent Form Filling AI (`window.IRA_FORM_FILLER`)  
**Scope:** Elimination of hidden inputs, commented-out fields, read-only displays, and sensitive credentials (passwords, PINs, OTPs, CAPTCHAs) from voice prompts, introductions, and field sequences.  
**Navigation AI Status:** STRICTLY UNTOUCHED & FROZEN (`public/ira/ira-assistant.js`, `IraNavigationService.php`, `IraNavigationController.php`).

---

## 1. Problem Statement & Root Cause Analysis

### Identified Deficiencies:
1. **Commented-Out & Non-Existent Fields Announced:**
   - In `/admin/funds/add-funds` (`resources/views/admin/funds/add-funds.blade.php`), the `txnid` input was commented out (`{{-- <input ... name="txnid"> --}}`). However, `IraFormRegistry.php` still listed `'txnid'` in `'admin_funds_add'`, causing IRA to introduce and ask for `Transaction ID / Reference`.
   - Internal tokens like `_token` and `#csrf` could be treated as targetable fields if not explicitly filtered.
2. **Read-Only / Display Inputs Targeted:**
   - In `/member/staking` (`resources/views/member/staking/staking.blade.php`), `memberid` is a read-only input showing the current logged-in user, and the wallet balance is a read-only preview. IRA previously treated all schema fields as fillable steps.
3. **Sensitive Credentials Prompted via Voice:**
   - Forms requiring authentication or authorization confirmations (e.g. `txn_password` on `/member/staking`, login passwords, transaction passwords) had those password inputs in their schemas. IRA should never ask users to speak passwords, PINs, or security codes over voice.
4. **Global Query Leakage in Frontend:**
   - `public/ira/ira-form-filler.js` previously used `document.querySelector` as a fallback when looking up target input elements during `setActiveField` and `applyFieldUpdates`. On pages with multiple cards, hidden tabs, or navigation modals, this could match elements outside the active form.

---

## 2. Architectural Solution & Changes Implemented

### A. Backend Schema & Service Hardening

#### 1. `app/Services/AI/IraFormRegistry.php`
- Cleaned up `'admin_funds_add'` schema to reflect the actual Blade template:
  - Removed `'txnid'` from `admin_funds_add` fields.
  - Form now strictly defines: `memberid` (Target Member ID), `wallet_type` (Wallet Type dropdown), and `amount` (Amount).

#### 2. `app/Services/AI/IraFormUnderstandingService.php`
- Added **`isSensitiveField(array $fieldDef): bool`**:
  - Checks if `sensitive === true`, `type === 'password'`, or if field name/label contains keywords: `password`, `pass`, `pin`, `tpin`, `otp`, `captcha`, `secret`, `token`, `पासवर्ड`, `पिन`.
- Added **`isReadOnlyOrHiddenField(array $fieldDef): bool`**:
  - Checks if `read_only === true`, `hidden === true`, `type === 'hidden'`, or field key is `_token` / `csrf`.
- Added **`findNextEligibleField(array $schema, array $fieldSequence, array $currentFields, array $excludeKeys, ?array $domEligibleKeys): ?string`**:
  - Iterates through the field sequence and returns the next unfilled, visible, editable, non-sensitive field.
  - Dynamically enforces DOM intersection when `dom_eligible_fields` is provided by the client.
- Updated **`initGuidedSession`**:
  - Filters all schema fields before computing introductory speech, target count, or step sequence.
  - Excludes sensitive fields, read-only fields, hidden fields, and file upload inputs.
  - Intersects with client-supplied `dom_eligible_fields` so that elements hidden in CSS or closed tabs are never announced.
  - If a form consists only of sensitive fields (e.g. `member_security_txn_password`), returns `status: 'all_sensitive'` and `action: 'complete'`, speaking a polite notice that passwords must be entered manually and never arming the microphone.
- Updated **`processGuidedTurn`**:
  - Re-validates active field on turn entry; skips immediately if sensitive or read-only.
  - Advances to the next genuinely eligible field via `findNextEligibleField`.
  - In `getFieldPromptQuestion`: returns safe manual entry guidance if a sensitive field is inspected.
  - In `extractTargetedFieldValue`: returns `null` immediately if field is sensitive, read-only, or hidden.
  - In `buildCompletionPayload`: appends polite reminder if the form contains sensitive fields (e.g. Transaction Password): *"Kripya apna Transaction Password manually enter karein aur Submit button par click karein."*

#### 3. `app/Http/Controllers/IraFormController.php`
- Extracts `dom_eligible_fields` from POST requests in both `action === 'init'` and `action === 'turn'`.
- Passes `$context['dom_eligible_fields']` into `initGuidedSession` and `processGuidedTurn`.

---

### B. Frontend Controller Hardening (`public/ira/ira-form-filler.js`)

#### 1. Form Element Visibility & Sensitivity Detection
- Added **`isSensitiveElement(el)`**:
  - Inspects element type (`password`), attributes (`autocomplete`, `aria-label`), and identifiers (`name`, `id`).
  - Rejects elements containing `password`, `pin`, `tpin`, `otp`, `captcha`, `secret`, `token`, `csrf`.
- Added **`isElementVisibleAndEditable(el)`**:
  - Rejects elements that are not `INPUT`, `SELECT`, or `TEXTAREA`.
  - Rejects `type="hidden"`, `submit`, `button`, `reset`, `image`.
  - Rejects sensitive elements via `isSensitiveElement(el)`.
  - Rejects `readOnly`, `disabled`, and HTML `hidden` attributes.
  - Checks computed layout: `display === 'none'`, `visibility === 'hidden'`, `opacity === '0'`, `offsetParent === null` (excluding fixed containers), `width === 0 && height === 0`.
  - Checks ancestor chain for `.d-none`, `.hidden`, `.visually-hidden`, `.sr-only`, `aria-hidden="true"`, or hidden parent containers.
- Added **`getDomEligibleFields(formEl)`**:
  - Queries `formEl.querySelectorAll('input, select, textarea')` and returns an array of unique names/IDs of elements that pass `isElementVisibleAndEditable`.

#### 2. Strict Scoping to Active Form
- In **`startFormFill(formKey, triggerBtn)`**:
  - Resolves `formEl` strictly using `triggerBtn.closest('form')` or parent card container `triggerBtn.closest('.card, .member-wallet-panel, .panel, .box')?.querySelector('form')`.
  - Tags `formEl` with `data-ira-form-key`.
  - Calculates `domEligible = this.getDomEligibleFields(formEl)`.
  - If no eligible fields exist (`domEligible.length === 0`): displays dock with status `COMPLETED`, speaks notice that all fields are protected/manual, and does not listen.
  - Transmits `dom_eligible_fields` in `init` payload to `/ira/form-step`.
- In **`handleSpokenTurn`**:
  - Re-evaluates `domEligible = this.getDomEligibleFields(this.activeFormEl)` and transmits in `turn` payload.
- In **`setActiveField(fieldDef)`**:
  - Queries strictly inside `this.activeFormEl`. Removed all fallback `document.querySelector` lookups.
  - Verifies `this.isElementVisibleAndEditable(targetEl)` before applying active highlight and scroll focus.
- In **`applyFieldUpdates(updates)`**:
  - Searches strictly inside `this.activeFormEl`. Removed all fallback `document.querySelector` lookups.
  - Refuses to write to read-only elements (`target.readOnly && !update.override_readonly`).
  - Refuses to write to sensitive elements (`this.isSensitiveElement(target)`).
  - Refuses to write to hidden or invisible elements.
- In **`scrapeCurrentFormFields(formEl)`**:
  - Excludes sensitive elements via `this.isSensitiveElement(input)` and hidden inputs.

---

## 3. Real-World Form Behavior Verification

### A. Admin Add Funds (`/admin/funds/add-funds`)
- **Blade File:** `resources/views/admin/funds/add-funds.blade.php`
- **Visible Inputs Rendered:**
  1. `memberid` (Member ID text input)
  2. `wallet_type` (Wallet Type dropdown)
  3. `amount` (Amount number input)
- **Omitted / Skipped Elements:**
  - `txnid` (commented out in Blade) &rarr; **Completely omitted from sequence and introduction.**
  - `_token` / `#csrf` &rarr; **Filtered out.**
- **Workflow Observed:**
  - Step 1: Member ID &rarr; User speaks "QRV123456" &rarr; populates and validates.
  - Step 2: Wallet Type &rarr; User speaks "Activation Wallet" &rarr; selects option.
  - Step 3: Amount &rarr; User speaks "500" &rarr; populates and completes.
  - Session completes without ever prompting for `txnid`.

### B. Member Staking / Investment (`/member/staking`)
- **Blade File:** `resources/views/member/staking/staking.blade.php`
- **Visible Editable Inputs:**
  1. `stak_package` (Package dropdown)
  2. `amount` (Investment Amount number input)
- **Omitted / Skipped Elements:**
  - `memberid` (`readonly="readonly"`) &rarr; **Skipped.**
  - Wallet balance (`readonly="readonly"`) &rarr; **Skipped.**
  - `txn_password` (`type="password"`) &rarr; **Skipped.**
- **Workflow Observed:**
  - Introduction: *"Create Investment / Staking form shuru karte hain. Ismein 2 fields hain: Investment Package aur Investment Amount..."*
  - Step 1: `stak_package` &rarr; User speaks "Silver" &rarr; option selected.
  - Step 2: `amount` &rarr; User speaks "2000" &rarr; amount populated.
  - Completion Speech: *"Form ke sabhi aavashyak fields bhar diye gaye hain. Kripya apna Transaction Password manually enter karein aur Submit button par click karein."*

### C. Password / Sensitive Only Forms (`/member/security/change-password`)
- **Form:** `member_security_txn_password`
- **Result:**
  - Backend recognizes all fields are sensitive passwords.
  - IRA announces: *"Suraksha ke liye kripya password fields screen par manually bharein. Main in fields mein aawaaz se password nahi bharoongi."*
  - Microphone is not armed; form is preserved for manual entry.

---

## 4. Test Suite Execution & Results

Automated feature test suite executed via `php artisan test --filter Ira`:

```
   PASS  Tests\Feature\IraGuidedVoiceFormFillingTest
  ✓ complete guided flow step by step                                             0.18s
  ✓ dropdown options resolution                                                   0.01s
  ✓ dynamic lookup event configuration                                            0.02s
  ✓ multiform page isolation                                                      0.01s
  ✓ sensitive only form immediate explanation                                     0.01s
  ✓ file input manual instruction                                                 0.01s
  ✓ spoken correction handling                                                    0.01s
  ✓ unclear answer keeps active field                                             0.01s
  ✓ zero auto submission guarantee                                                0.01s
  ✓ http endpoint guided session                                                  0.06s
  ✓ devanagari name transliteration to roman title case                           0.01s
  ✓ devanagari country transliteration                                            0.01s
  ✓ devanagari free text transliteration                                          0.01s
  ✓ spoken hindi amounts normalization                                            0.01s
  ✓ devanagari digits phone normalization                                         0.01s
  ✓ natural correction pattern for name                                           0.01s
  ✓ spoken rewind command                                                         0.01s
  ✓ spoken field switch command                                                   0.01s
  ✓ alphanumeric member id recognition variations                                 0.01s
  ✓ numeric identifier preserves leading zeros                                    0.01s
  ✓ digit by digit speech formatting voice response                               0.01s
  ✓ unclear identifier asks specific polite clarification                         0.01s
  ✓ context aware purpose announcement and polite conversation                    0.01s
  ✓ cardinal amount vs digit identifier separation                                0.01s
  ✓ devanagari and mixed identifier variations resolve correctly                  0.01s
  ✓ p2p wallet receiver member id advances to amount                              0.01s
  ✓ navigation ai source files remain strictly untouched                          0.01s
  ✓ admin funds add omits commented out txnid and asks visible fields only        0.01s
  ✓ member staking skips readonly and sensitive fields                            0.01s
  ✓ dom eligible fields filtering                                                 0.01s
  ✓ all sensitive form returns gracefully                                         0.01s

   PASS  Tests\Feature\IraNavigationRegressionTest
  ✓ 19 Navigation AI tests passed                                                 0.32s

   PASS  Tests\Feature\IraVoiceFormFillingTest
  ✓ 19 Voice Form Filling tests passed                                            0.35s

  Tests:    69 passed (1,076 assertions)
  Duration: 1.46s
```

JavaScript syntax validation:
```
node -c public/ira/ira-form-filler.js
-> Exit Code 0 (clean)
```

---

## 5. Strict Navigation AI Preservation Audit

| File | Status | Notes |
|---|---|---|
| `public/ira/ira-assistant.js` | **UNTOUCHED** | Zero modifications during this task. |
| `app/Services/AI/IraNavigationService.php` | **UNTOUCHED** | Zero modifications during this task. |
| `app/Http/Controllers/IraNavigationController.php` | **UNTOUCHED** | Zero modifications during this task. |

All changes were strictly confined to:
1. `app/Services/AI/IraFormRegistry.php`
2. `app/Services/AI/IraFormUnderstandingService.php`
3. `app/Http/Controllers/IraFormController.php`
4. `public/ira/ira-form-filler.js`
5. `tests/Feature/IraGuidedVoiceFormFillingTest.php`
