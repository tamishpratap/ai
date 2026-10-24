# IRA Assistant Expand Button & Listening Hotfix Report (Hotfix 2)

**Project:** `C:\xampp\htdocs\aidemo\`  
**Target:** Frontend Click Behavior & Listening Initiation  
**Date:** October 23, 2026  
**Status:** Completed & Verified via Headless Chrome & PHPUnit  

---

## 1. Executive Summary

A browser-level interaction defect was resolved following the initial icon-click separation. 

### Issue Description
1. Clicking the main green floating button (`#iraFloatingBtn`) produced no visual change, which was expected per Hotfix 1, but left the user without an active voice experience.
2. Clicking the adjacent circular expand button (`#iraFloatingExpandBtn`) opened the modal panel, but **failed to initiate speech recognition**. The assistant sat idle in `READY` state without showing the `LISTENING` indicator or capturing speech.

### Solution Applied
- **Main Green Button (`#iraFloatingBtn`):** Preserved as visual-only status control when idle; clicking alone does not open the modal and does not start listening.
- **Dedicated Expand Button (`#iraFloatingExpandBtn`):** Configured as the sole entry point that opens the side panel and deterministically triggers voice recognition once the panel is ready and visible.
- **Microphone State Synchronization:** Updated status updates so the assistant transitions to `LISTENING` only when the browser speech recognition engine actually starts receiving audio (`onstart`).

---

## 2. Root Cause Analysis

Thorough inspection of `resources/views/components/ira-assistant.blade.php`, `public/ira/ira-assistant.js`, and `public/ira/ira-assistant.css` identified three root causes:

### 1. Missing Dedicated Event Listener on `this.elements.floatingExpandBtn`
In `public/ira/ira-assistant.js` (line 111), `floatingExpandBtn: document.getElementById('iraFloatingExpandBtn')` was cached during `cacheElements()`. However, `bindEvents()` **never attached a dedicated click listener** to `this.elements.floatingExpandBtn`.
Instead, it was only captured by:
```javascript
// Generic trigger listener
document.querySelectorAll('.ira-open-assistant-trigger').forEach((trigger) => {
    trigger.addEventListener('click', (e) => {
        e.preventDefault();
        this.openModal(); // <-- ONLY opened modal; NEVER started listening!
    });
});
```

### 2. Competing Bootstrap Modal Data Attributes
In `resources/views/components/ira-assistant.blade.php`:
```html
<button type="button" id="iraFloatingExpandBtn" class="ira-floating-expand-btn ira-open-assistant-trigger" data-bs-toggle="modal" data-bs-target="#iraModal" ...>
```
The presence of `data-bs-toggle="modal"` caused Bootstrap's internal data-api listener to compete with custom JavaScript listeners, opening the modal without any callback or event hook to start the microphone.

### 3. Premature Synchronous State Assignment
In `public/ira/ira-assistant.js`, `startListening()` previously called `this.setVoiceState('LISTENING')` immediately in the synchronous `try` block before the asynchronous `recognition.start()` had established a live audio capture stream via `recognition.onstart`.

---

## 3. Exact Code Changes Applied

### A. Blade Component (`resources/views/components/ira-assistant.blade.php`)
Removed competing Bootstrap modal data attributes and generic trigger class from `#iraFloatingExpandBtn`:
```html
<!-- Dedicated Expand / Open Assistant Window Button -->
<button type="button" id="iraFloatingExpandBtn" class="ira-floating-expand-btn" aria-label="Open IRA Assistant Window and Speak" title="Open and Speak with IRA">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <polyline points="15 3 21 3 21 9"></polyline>
        <polyline points="9 21 3 21 3 15"></polyline>
        <line x1="21" y1="3" x2="14" y2="10"></line>
        <line x1="3" y1="21" x2="10" y2="14"></line>
    </svg>
</button>
```

### B. CSS Layer (`public/ira/ira-assistant.css`)
Added `pointer-events: none` to the expand button's child SVG to guarantee clean click targeting:
```css
#iraFloatingExpandBtn svg,
.ira-floating-expand-btn svg {
    pointer-events: none;
}
```

### C. JavaScript Controller (`public/ira/ira-assistant.js`)
1. **Added `openModalAndStartListening()`:**
   - Debounces rapid repeated clicks (500ms window).
   - Stops active speech if currently speaking.
   - If the modal is already open, immediately starts speech recognition.
   - If closed, attaches a one-time `shown.bs.modal` listener (with a 350ms deterministic safety fallback) and calls `openModal()`.
   - Starts listening exactly once after the modal panel is visible.
2. **Dedicated Click Listener in `bindEvents()`:**
   ```javascript
   // Dedicated Expand Button -> Open modal and start voice listening once visible
   if (this.elements.floatingExpandBtn) {
       this.elements.floatingExpandBtn.addEventListener('click', (e) => {
           e.preventDefault();
           e.stopPropagation();
           this.openModalAndStartListening();
       });
   }
   ```
3. **Synchronized Status Updates in `startListening()`:**
   - Removed synchronous `this.setVoiceState('LISTENING')` from `startListening()`.
   - Status changes to `LISTENING` strictly when `this.recognition.onstart` triggers upon actual audio hardware engagement.
4. **Clean Abort Handling:**
   - Set `this.isAbortedByUser = true` on modal close button click and `hidden.bs.modal`.
   - Prevents normal user closure from registering as a speech recognition error.

---

## 4. Verification & Browser Testing Results

Actual browser-level execution was performed using Google Chrome in headless mode with Web Speech API and CDP (Chrome DevTools Protocol) integration.

### Test Execution Summary (`scratch/test_browser_ira.mjs`)

| Step | Interaction Tested | Expected Result | Actual Result in Browser | Status |
| :--- | :--- | :--- | :--- | :--- |
| **Test 1** | Click `#iraFloatingBtn` (Main Green Button) | Modal remains closed (`display: none`), listening does NOT start, state is `READY` | `modalShown: false`, `modalDisplay: "none"`, `bodyPanelOpen: false`, `currentState: "READY"`, `isRecognitionRunning: false` | **PASS** |
| **Test 2** | Click `#iraFloatingExpandBtn` (Dedicated Expand Button) | Panel opens smoothly and speech recognition initiates once visible | `modalShown: true`, `bodyPanelOpen: true`, `currentState: "LISTENING"`, `statusLabelText: "Listening..."`, `isRecognitionRunning: true` | **PASS** |
| **Test 3** | State timing verification | Status transitions to `LISTENING` only when speech recognition engine activates | `onstart` fired; `statusLabel: "Listening..."`, `pillClass: "ira-status-pill listening"`, `orbClass: "ira-orb-btn listening"` | **PASS** |
| **Test 4** | Utterance & Transcript | Spoken command renders in transcript bubble | Spoken query `"Earning section mein kaun-kaun se pages hain?"` displayed in `#iraUserText` | **PASS** |
| **Test 5** | Assistant Response | Assistant processes intent and displays response bubble | AI response rendered in `#iraAiText`; visualizer and transcript active | **PASS** |
| **Test 6** | Close and Reopen | Modal closes cleanly; reopening restarts recognition without duplicate listeners | Modal closed (`modalShown: false, currentState: "READY"`); Re-clicked expand button -> `modalShown: true, currentState: "LISTENING"`, zero duplicate listeners | **PASS** |

### Automated Regression Suites
1. **JavaScript Syntax Check:**
   ```bash
   node -c public/ira/ira-assistant.js
   # Result: Exit code 0 (Clean)
   ```
2. **PHPUnit Feature & Intent Regression Suite:**
   ```bash
   php artisan test tests/Feature/IraNavigationRegressionTest.php
   # Result: 19 passed (242 assertions) in 1.72s
   ```

---

## 5. Conclusion

The expand button `#iraFloatingExpandBtn` is now the single, dedicated launcher that opens the assistant panel and starts voice listening upon visibility. Clicking the main green button remains a non-intrusive status indicator when idle. All existing voice assistant features, close mechanisms, and in-panel orb controls remain fully preserved.
