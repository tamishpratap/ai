import { createServer } from "node:http";
import { readFile } from "node:fs/promises";
import { extname, join, normalize } from "node:path";
import { fileURLToPath } from "node:url";

const root = fileURLToPath(new URL(".", import.meta.url));
const projectRoot = join(root, "..", "..");

async function loadEnv() {
  try {
    const env = await readFile(join(projectRoot, ".env"), "utf8");
    for (const line of env.split(/\r?\n/)) {
      const trimmed = line.trim();
      if (!trimmed || trimmed.startsWith("#")) continue;

      const separator = trimmed.indexOf("=");
      if (separator === -1) continue;

      const key = trimmed.slice(0, separator).trim();
      const value = trimmed.slice(separator + 1).trim().replace(/^["']|["']$/g, "");
      if (key && !process.env[key]) process.env[key] = value;
    }
  } catch {
    // .env is optional; production hosts usually inject environment variables directly.
  }
}

await loadEnv();

const port = Number(process.env.CHATBOT_PORT || 3000);
const apiKey = process.env.GEMINI_API_KEY;
const pdfPath = join(projectRoot, "public", "logo", "project.pdf");

const candidateModels = [
  process.env.GEMINI_MODEL,
  "gemini-1.5-flash",
  "gemini-1.5-flash-8b",
  "gemini-2.0-flash",
  "gemini-flash-latest"
].filter(Boolean);

const model = candidateModels[0] || "gemini-1.5-flash";

let cachedPdf;

const mimeTypes = {
  ".html": "text/html; charset=utf-8",
  ".css": "text/css; charset=utf-8",
  ".js": "text/javascript; charset=utf-8",
  ".pdf": "application/pdf",
  ".png": "image/png",
  ".jpg": "image/jpeg",
  ".jpeg": "image/jpeg",
  ".svg": "image/svg+xml"
};

function sendJson(res, status, body) {
  res.writeHead(status, {
    "Content-Type": "application/json; charset=utf-8",
    "Access-Control-Allow-Origin": "*"
  });
  res.end(JSON.stringify(body));
}

async function readJson(req) {
  const chunks = [];
  for await (const chunk of req) chunks.push(chunk);
  const raw = Buffer.concat(chunks).toString("utf8");
  return raw ? JSON.parse(raw) : {};
}

const docPath = join(projectRoot, "public", "logo", "project");
let cachedDocText = "";

async function getDocText() {
  if (!cachedDocText) {
    try {
      cachedDocText = await readFile(docPath, "utf8");
    } catch {
      cachedDocText = "";
    }
  }
  return cachedDocText;
}

function getInstantDocAnswer(question) {
  const q = String(question || "").toLowerCase();

  if (q.includes("roi") || q.includes("package") || q.includes("tier") || q.includes("yield") || q.includes("return") || q.includes("invest")) {
    return "<strong>Staking Packages & Monthly ROI Tiers</strong> (from Document Sec. 4.A):<br><br>"
      + "• <strong>Tier 1:</strong> $100 – $1,000 → <strong>10% Monthly</strong> (0.333% / day)<br>"
      + "• <strong>Tier 2:</strong> $1,100 – $5,000 → <strong>12% Monthly</strong> (0.400% / day)<br>"
      + "• <strong>Tier 3:</strong> $5,100 – $10,000 → <strong>14% Monthly</strong> (0.466% / day)<br>"
      + "• <strong>Tier 4:</strong> $10,100+ → <strong>15% Monthly</strong> (0.500% / day)<br><br>"
      + "<strong>Formula:</strong> <code>Daily Payout = (Invested Amount × Monthly Rate) / (30 × 100)</code>";
  }

  if (q.includes("capping") || q.includes("3x") || q.includes("5x") || q.includes("limit") || q.includes("non-working") || q.includes("working")) {
    return "<strong>Income Capping Rules</strong> (from Document Sec. 4.B):<br><br>"
      + "• <strong>3X Non-Working Capping:</strong> Passive investors can earn up to a maximum of <strong>300% (3X)</strong> of active staking amount. Once 3X is reached, the plan auto-deactivates.<br><br>"
      + "• <strong>5X Working Capping:</strong> Active network builders can earn up to <strong>500% (5X)</strong> before requiring a package re-topup.";
  }

  if (q.includes("ai") || q.includes("engine") || q.includes("matrix") || q.includes("neural") || q.includes("sentinel")) {
    return "<strong>3 AI Trading Engines</strong> (from Document Sec. 6.1):<br><br>"
      + "1. <strong>Quantum Matrix AI</strong> (<code>/ai/quantum-arbitrage</code>): 30ms high-frequency spread detection core.<br>"
      + "2. <strong>Neural Pulse AI</strong> (<code>/ai/neural-pulse</code>): ML engine trained on 10B+ order-book ticks.<br>"
      + "3. <strong>Sentinel Guard AI</strong> (<code>/ai/sentinel-guard</code>): Capital defense, anti-MEV front-running shield & circuit breaker.";
  }

  if (q.includes("rank") || q.includes("reward") || q.includes("titan") || q.includes("bronze") || q.includes("silver") || q.includes("gold") || q.includes("platinum")) {
    return "<strong>Progressive Rank & Reward System</strong> (from Document Sec. 4.E):<br>"
      + "<em>(50% Strong Leg / 50% Weaker Legs rule)</em><br><br>"
      + "• <strong>Starter:</strong> $1,000 Vol → Platform Badge<br>"
      + "• <strong>Bronze:</strong> $5,000 Vol → Cash Bonus<br>"
      + "• <strong>Silver:</strong> $15,000 Vol → Smart Device<br>"
      + "• <strong>Gold:</strong> $50,000 Vol → Luxury Trip<br>"
      + "• <strong>Platinum:</strong> $150,000 Vol → Supercar Bonus<br>"
      + "• <strong>Quantum Titan:</strong> $500,000+ Vol → Global Pool Equity";
  }

  if (q.includes("level") || q.includes("direct") || q.includes("referral") || q.includes("affiliate") || q.includes("matching")) {
    return "<strong>Affiliate Structure</strong> (from Document Sec. 4.C & 4.D):<br><br>"
      + "• <strong>Direct Referral Benefit:</strong> Instant commission credited to sponsor wallet upon package activation.<br>"
      + "• <strong>Multi-Level ROI Matching:</strong> Daily percentage commission on team ROI from Level 1 to Level 15+.";
  }

  return "According to <strong>public/logo/project</strong>, Quantum ARB delivers autonomous cross-market crypto arbitrage across 40+ exchanges with 10%–15% monthly staking ROI, protected by 3X/5X capital preservation and 3 AI trading engines.";
}

async function answerQuestion(question, history) {
  const docText = await getDocText();

  // If Gemini API Key is not set, provide exact instant answer immediately
  if (!apiKey) {
    return { answer: getInstantDocAnswer(question) };
  }

  const userParts = [
    {
      text:
        `Reference Document: public/logo/project\n\n${docText.slice(0, 30000)}\n\nQuestion: ${question}\n\nImportant: Ground your answer strictly and accurately in the provided document. Output clean markdown or HTML.`
    }
  ];

  const contents = [
    ...toGeminiHistory(history),
    {
      role: "user",
      parts: userParts
    }
  ];

  let lastError = "Gemini request failed.";

  for (const m of [...new Set(candidateModels)]) {
    try {
      const response = await fetch(
        `https://generativelanguage.googleapis.com/v1beta/models/${m}:generateContent?key=${apiKey}`,
        {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            systemInstruction: {
              parts: [
                {
                  text:
                    "You are Quantum ARB's Official AI Document Assistant. You answer questions strictly based on the provided document (public/logo/project). Be concise, clear, and exact."
                }
              ]
            },
            contents,
            generationConfig: {
              temperature: 0.1,
              maxOutputTokens: 900
            }
          })
        }
      );

      const data = await response.json();
      if (!response.ok) {
        lastError = data?.error?.message || "Gemini request failed.";
        if (response.status === 503 || response.status === 429 || response.status === 404 || response.status === 500) {
          continue;
        }
        throw new Error(lastError);
      }

      const answer =
        data?.candidates?.[0]?.content?.parts
          ?.map((part) => part.text || "")
          .join("")
          .trim();

      if (answer) return { answer };
    } catch (err) {
      lastError = err.message || lastError;
    }
  }

  // Fallback to instant local document answer
  return { answer: getInstantDocAnswer(question) };
}

async function serveStatic(req, res) {
  const url = new URL(req.url, `http://${req.headers.host}`);
  const requested = url.pathname === "/" ? "/index.html" : decodeURIComponent(url.pathname);
  const cleanPath = normalize(requested).replace(/^(\.\.[/\\])+/, "");
  const filePath = join(root, cleanPath);

  if (!filePath.startsWith(root)) {
    res.writeHead(403);
    res.end("Forbidden");
    return;
  }

  try {
    const content = await readFile(filePath);
    res.writeHead(200, {
      "Content-Type": mimeTypes[extname(filePath).toLowerCase()] || "application/octet-stream"
    });
    res.end(content);
  } catch {
    res.writeHead(404);
    res.end("Not found");
  }
}

const server = createServer(async (req, res) => {
  if (req.method === "OPTIONS") {
    res.writeHead(204, {
      "Access-Control-Allow-Origin": "*",
      "Access-Control-Allow-Methods": "GET,POST,OPTIONS",
      "Access-Control-Allow-Headers": "Content-Type"
    });
    res.end();
    return;
  }

  if (req.method === "GET" && req.url === "/api/health") {
    sendJson(res, 200, { ok: true, hasGeminiKey: Boolean(apiKey), model });
    return;
  }

  if (req.method === "POST" && req.url === "/api/chat") {
    try {
      const { question, history } = await readJson(req);
      const cleanQuestion = String(question || "").trim();

      if (!cleanQuestion) {
        sendJson(res, 400, { error: "Question is required." });
        return;
      }

      const result = await answerQuestion(cleanQuestion.slice(0, 4000), history);
      sendJson(res, 200, result);
    } catch (error) {
      sendJson(res, 500, { error: error.message || "Something went wrong." });
    }
    return;
  }

  if (req.method === "GET") {
    await serveStatic(req, res);
    return;
  }

  res.writeHead(405);
  res.end("Method not allowed");
});

server.listen(port, () => {
  console.log(`PDF chatbot running at http://127.0.0.1:${port}`);
});
