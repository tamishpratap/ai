const chatToggle = document.querySelector("#chatToggle");
const chatCard = document.querySelector("#chatCard");
const chatClose = document.querySelector("#chatClose");
const chatForm = document.querySelector("#chatForm");
const questionInput = document.querySelector("#questionInput");
const sendButton = document.querySelector("#sendButton");
const messagesEl = document.querySelector("#messages");
const chatStatus = document.querySelector("#chatStatus");
const suggestionButtons = document.querySelectorAll(".chat-chip");

const history = [];
const preferredApiBase = (window.CHATBOT_API_BASE || window.location.origin || "").replace(/\/+$/, "");
let activeApiBase = preferredApiBase;

async function resolveApiBase() {
  const candidates = [...new Set([preferredApiBase, window.location.origin].filter(Boolean))].map((url) =>
    url.replace(/\/+$/, "")
  );

  for (const base of candidates) {
    try {
      const response = await fetch(`${base}/api/health`, {
        method: "GET",
        headers: { Accept: "application/json" }
      });
      const contentType = response.headers.get("content-type") || "";
      const data = contentType.includes("application/json")
        ? await response.json().catch(() => null)
        : null;

      if (response.ok && data && data.ok === true) {
        activeApiBase = base;
        chatStatus.textContent = "Ready to read your document";
        return base;
      }
    } catch {
      // Keep trying the next candidate.
    }
  }

  activeApiBase = preferredApiBase;
  chatStatus.textContent = "Ready to read your document";
  return activeApiBase;
}

function setOpen(isOpen) {
  chatCard.classList.toggle("open", isOpen);
  chatCard.setAttribute("aria-hidden", String(!isOpen));
  chatToggle.setAttribute("aria-label", isOpen ? "Close chat" : "Open chat");
  if (isOpen) questionInput.focus();
}

function formatMessage(text) {
  return String(text)
    .replace(/\*\*(.*?)\*\*/g, "<strong>$1</strong>")
    .replace(/\n/g, "<br>");
}

function addMessage(role, text, isError = false) {
  const article = document.createElement("article");
  article.className = `message ${isError ? "error" : role}`;

  const meta = document.createElement("div");
  meta.className = "message-meta";

  const avatar = document.createElement("span");
  avatar.className = "message-avatar";
  avatar.textContent = role === "user" ? "You" : "AI";

  const label = document.createElement("span");
  label.className = "message-label";
  label.textContent = isError ? "Connection Notice" : role === "user" ? "Your Question" : "Document Assistant";

  meta.append(avatar, label);

  const body = document.createElement("div");
  body.className = "message-body";
  body.innerHTML = `<p>${formatMessage(text)}</p>`;

  article.append(meta, body);

  messagesEl.append(article);
  messagesEl.scrollTop = messagesEl.scrollHeight;
}

function setBusy(isBusy) {
  sendButton.disabled = isBusy;
  questionInput.disabled = isBusy;
  suggestionButtons.forEach((button) => {
    button.disabled = isBusy;
  });
  chatStatus.textContent = isBusy ? "Analyzing document..." : "Ready to read your document";
}

async function askGemini(question) {
  const apiBase = (activeApiBase || (await resolveApiBase()) || preferredApiBase || "").replace(/\/+$/, "");
  let response;

  try {
    response = await fetch(`${apiBase}/api/chat`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json"
      },
      body: JSON.stringify({ question, history })
    });
  } catch {
    throw new Error("Cannot connect to server. Please check your network connection.");
  }

  const data = await response.json().catch(() => ({}));
  if (!response.ok) {
    throw new Error(
      data.error ||
        data.message ||
        `Request failed (${response.status}). Please try again.`
    );
  }

  return data.answer;
}

chatToggle.addEventListener("click", () => {
  setOpen(!chatCard.classList.contains("open"));
});

chatClose.addEventListener("click", () => setOpen(false));

suggestionButtons.forEach((button) => {
  button.addEventListener("click", () => {
    questionInput.value = button.dataset.question || "";
    chatForm.requestSubmit();
  });
});

questionInput.addEventListener("keydown", (event) => {
  if (event.key === "Enter" && !event.shiftKey) {
    event.preventDefault();
    chatForm.requestSubmit();
  }
});

chatForm.addEventListener("submit", async (event) => {
  event.preventDefault();

  const question = questionInput.value.trim();
  if (!question) return;

  questionInput.value = "";
  addMessage("user", question);
  history.push({ role: "user", text: question });
  setBusy(true);

  try {
    const answer = await askGemini(question);
    addMessage("assistant", answer);
    history.push({ role: "assistant", text: answer });
  } catch (error) {
    addMessage("assistant", error.message || "Something went wrong.", true);
  } finally {
    setBusy(false);
    questionInput.focus();
  }
});

resolveApiBase();
