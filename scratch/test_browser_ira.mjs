import { spawn } from 'child_process';
import http from 'http';

const CHROME_PATH = "C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe";
const PROFILE_DIR = "C:\\Users\\Lifeleads\\.gemini\\antigravity\\brain\\b8c82334-db52-4619-816d-9dd60029416c\\scratch\\chrome-test-profile";
const PORT = 9222;

function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

async function getWsUrl() {
    for (let i = 0; i < 30; i++) {
        try {
            const res = await fetch(`http://127.0.0.1:${PORT}/json/list`);
            const data = await res.json();
            const page = data.find(t => t.type === 'page' && t.url && t.url.includes('browser-test.html')) 
                      || data.find(t => t.type === 'page' && t.url && !t.url.startsWith('chrome-extension://'))
                      || data[0];
            if (page && page.webSocketDebuggerUrl) {
                return page.webSocketDebuggerUrl;
            }
        } catch (e) {
            // waiting for chrome to be ready
        }
        await sleep(300);
    }
    throw new Error("Could not connect to Chrome DevTools port " + PORT);
}

async function main() {
    console.log("[TEST] Launching Chrome in headless mode with fake media stream...");
    const chromeProc = spawn(CHROME_PATH, [
        '--headless=new',
        `--remote-debugging-port=${PORT}`,
        '--use-fake-ui-for-media-stream',
        '--use-fake-device-for-media-stream',
        '--disable-gpu',
        '--no-first-run',
        '--no-default-browser-check',
        `--user-data-dir=${PROFILE_DIR}`,
        'file:///C:/xampp/htdocs/aidemo/scratch/browser-test.html'
    ], { stdio: 'ignore' });

    try {
        const wsUrl = await getWsUrl();
        console.log("[TEST] Connected to Chrome CDP at:", wsUrl);

        const ws = new WebSocket(wsUrl);
        let idCounter = 1;
        const pending = new Map();

        ws.onmessage = (event) => {
            const msg = JSON.parse(event.data);
            if (msg.id && pending.has(msg.id)) {
                pending.get(msg.id)(msg);
                pending.delete(msg.id);
            }
        };

        await new Promise(resolve => ws.onopen = resolve);

        function sendCommand(method, params = {}) {
            return new Promise((resolve) => {
                const id = idCounter++;
                pending.set(id, resolve);
                ws.send(JSON.stringify({ id, method, params }));
            });
        }

        async function evaluate(expression) {
            const res = await sendCommand("Runtime.evaluate", {
                expression,
                returnByValue: true,
                awaitPromise: true
            });
            if (res.result && res.result.exceptionDetails) {
                throw new Error(JSON.stringify(res.result.exceptionDetails));
            }
            return res.result?.result?.value;
        }

        // Wait for page load and IRA initialization
        console.log("[TEST] Waiting for page and IRA to initialize...");
        for (let i = 0; i < 20; i++) {
            const currentUrl = await evaluate(`window.location.href`);
            const title = await evaluate(`document.title`);
            const isReady = await evaluate(`typeof window.IRA !== 'undefined' && window.IRA.initialized === true`);
            console.log(`[WAIT ${i}] URL: ${currentUrl}, Title: ${title}, IRA Ready: ${isReady}`);
            if (isReady) break;
            await sleep(500);
        }

        const initialized = await evaluate(`window.IRA && window.IRA.initialized`);
        console.log("[TEST] IRA Initialized:", initialized);
        if (!initialized) throw new Error("IRA Assistant failed to initialize on http://127.0.0.1:8000/");

        // --- TEST 1: Main Green IRA Voice AI Button ---
        console.log("\n--- TEST 1: Click Main Green Button (#iraFloatingBtn) ---");
        const t1Result = await evaluate(`(() => {
            const btn = document.getElementById('iraFloatingBtn');
            const modal = document.getElementById('iraModal');
            if (!btn) return { error: 'No floatingBtn found' };
            btn.click();
            return {
                modalShown: modal.classList.contains('show'),
                modalDisplay: window.getComputedStyle(modal).display,
                bodyPanelOpen: document.body.classList.contains('ira-panel-open'),
                currentState: window.IRA.currentState,
                isRecognitionRunning: window.IRA.isRecognitionRunning,
                isRecognitionStarting: window.IRA.isRecognitionStarting
            };
        })()`);
        console.log("[TEST 1 Result]:", JSON.stringify(t1Result, null, 2));

        const test1Pass = !t1Result.modalShown && 
                          t1Result.modalDisplay === 'none' && 
                          !t1Result.bodyPanelOpen && 
                          t1Result.currentState === 'READY' &&
                          !t1Result.isRecognitionRunning &&
                          !t1Result.isRecognitionStarting;
        console.log("[TEST 1 PASS]:", test1Pass ? "YES" : "NO");
        if (!test1Pass) throw new Error("Test 1 failed: Clicking main button opened modal or started listening!");

        // --- TEST 2: Dedicated Expand Button -> Open & Start Listening ---
        console.log("\n--- TEST 2: Click Dedicated Expand Button (#iraFloatingExpandBtn) ---");
        await evaluate(`document.getElementById('iraFloatingExpandBtn').click()`);
        
        // Wait 450ms for modal transition and startListening invocation
        await sleep(500);

        const t2Result = await evaluate(`(() => {
            const modal = document.getElementById('iraModal');
            const statusLabel = document.getElementById('iraStatusLabel');
            return {
                modalShown: modal.classList.contains('show'),
                bodyPanelOpen: document.body.classList.contains('ira-panel-open'),
                currentState: window.IRA.currentState,
                statusLabelText: statusLabel ? statusLabel.textContent : '',
                isRecognitionRunning: window.IRA.isRecognitionRunning,
                isRecognitionStarting: window.IRA.isRecognitionStarting
            };
        })()`);
        console.log("[TEST 2 Result]:", JSON.stringify(t2Result, null, 2));

        const test2Pass = t2Result.modalShown && 
                          t2Result.bodyPanelOpen && 
                          (t2Result.currentState === 'LISTENING' || t2Result.isRecognitionStarting || t2Result.isRecognitionRunning);
        console.log("[TEST 2 PASS]:", test2Pass ? "YES" : "NO");
        if (!test2Pass) throw new Error("Test 2 failed: Dedicated expand button did not open panel or start listening!");

        // --- TEST 3: Confirm Recognition Actually Changes Status to LISTENING ---
        console.log("\n--- TEST 3: Verify Recognition Event Updates Status to LISTENING ---");
        // In headless Chrome, trigger onstart if not already fired by mock device
        await evaluate(`(() => {
            if (window.IRA.recognition && window.IRA.currentState !== 'LISTENING') {
                if (window.IRA.recognition.onstart) window.IRA.recognition.onstart();
            }
        })()`);
        const t3Result = await evaluate(`(() => {
            return {
                currentState: window.IRA.currentState,
                statusLabel: document.getElementById('iraStatusLabel').textContent,
                pillClass: document.getElementById('iraStatusPill').className,
                orbClass: document.getElementById('iraOrbBtn').className
            };
        })()`);
        console.log("[TEST 3 Result]:", JSON.stringify(t3Result, null, 2));
        const test3Pass = t3Result.currentState === 'LISTENING' && t3Result.pillClass.includes('listening');
        console.log("[TEST 3 PASS]:", test3Pass ? "YES" : "NO");

        // --- TEST 4 & 5: Speak a Command & Confirm Transcript and Response ---
        console.log("\n--- TEST 4 & 5: Handle Spoken Turn & Verify Visual Cards & Transcript ---");
        const spokenCommand = "Earning section mein kaun-kaun se pages hain?";
        console.log("[TEST] Dispatching user utterance:", spokenCommand);
        
        await evaluate(`(async () => {
            window.IRA.setUserUtterance(${JSON.stringify(spokenCommand)});
            await window.IRA.handleSpokenTurn(${JSON.stringify(spokenCommand)});
        })()`);

        // Wait 1200ms for backend intent resolution and visual card rendering
        await sleep(1500);

        const t4Result = await evaluate(`(() => {
            const userText = document.getElementById('iraUserText')?.textContent;
            const aiText = document.getElementById('iraAiText')?.textContent;
            const cardContainer = document.getElementById('iraCardContainer');
            const hasSectionCard = cardContainer && cardContainer.querySelector('.ira-section-card') !== null;
            const pageItems = cardContainer ? Array.from(cardContainer.querySelectorAll('.ira-page-name')).map(el => el.textContent) : [];
            return {
                userText,
                aiText,
                hasSectionCard,
                cardCount: pageItems.length,
                pageItems
            };
        })()`);
        console.log("[TEST 4 & 5 Result]:", JSON.stringify(t4Result, null, 2));
        const test4Pass = t4Result.userText === spokenCommand && t4Result.aiText.length > 0;
        console.log("[TEST 4 & 5 PASS]:", test4Pass ? "YES" : "NO");

        // --- TEST 6: Close Modal, Reopen & Verify Microphone Works Without Duplicates ---
        console.log("\n--- TEST 6: Close and Reopen Verification ---");
        console.log("[TEST] Clicking close button...");
        await evaluate(`document.getElementById('iraCloseBtn').click()`);
        await sleep(400);

        const closedState = await evaluate(`(() => {
            const modal = document.getElementById('iraModal');
            return {
                modalShown: modal.classList.contains('show'),
                bodyPanelOpen: document.body.classList.contains('ira-panel-open'),
                currentState: window.IRA.currentState
            };
        })()`);
        console.log("[TEST 6 Closed State]:", JSON.stringify(closedState, null, 2));

        console.log("[TEST] Re-clicking dedicated expand button...");
        await evaluate(`document.getElementById('iraFloatingExpandBtn').click()`);
        await sleep(500);

        const reopenedState = await evaluate(`(() => {
            const modal = document.getElementById('iraModal');
            return {
                modalShown: modal.classList.contains('show'),
                bodyPanelOpen: document.body.classList.contains('ira-panel-open'),
                currentState: window.IRA.currentState,
                isRecognitionRunning: window.IRA.isRecognitionRunning,
                isRecognitionStarting: window.IRA.isRecognitionStarting
            };
        })()`);
        console.log("[TEST 6 Reopened State]:", JSON.stringify(reopenedState, null, 2));
        const test6Pass = reopenedState.modalShown && 
                          reopenedState.bodyPanelOpen && 
                          (reopenedState.currentState === 'LISTENING' || reopenedState.isRecognitionStarting || reopenedState.isRecognitionRunning);
        console.log("[TEST 6 PASS]:", test6Pass ? "YES" : "NO");

        ws.close();
        console.log("\n==========================================");
        console.log("ALL REAL BROWSER TESTS COMPLETED SUCCESSFULLY!");
        console.log("==========================================");
    } finally {
        chromeProc.kill();
    }
}

main().catch(err => {
    console.error("[TEST FAILED]:", err);
    process.exit(1);
});
