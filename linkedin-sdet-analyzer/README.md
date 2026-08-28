# LinkedIn Profile Analyzer & Rewriter — QA / Test Automation / SDET

Scores a full-page LinkedIn profile screenshot against a 25-point framework built
specifically for QA, test automation, and SDET careers, using Claude's vision
capability directly on the image (no OCR/parsing library). Optionally generates
3 positioning-angle rewrites (Authority / Outcome / Niche) for the headline,
About section, and one experience bullet.

## Architecture

```
linkedin-sdet-analyzer/
  server/   Node/Express API — talks to the Claude API server-side only
  client/   React (Vite) frontend — Upload → Checklist → Results
```

- **No image parsing library.** The screenshot is sent straight through to
  Claude as a vision input; Claude both scores the profile and transcribes
  the key text (headline, About excerpt, experience bullets, skills) in the
  same call, so the rewrite step never needs to re-read the image.
- **API key never reaches the browser.** All Claude calls happen in
  `server/src/lib/claudeClient.js`, called only from the Express routes.
- **Scoring math is computed server-side** (`server/src/lib/scoreCalculator.js`)
  from Claude's per-item 0/1 answers — the model's arithmetic is never trusted
  for the total/percentage/band.
- **No persistent storage.** Uploads use `multer` memory storage; nothing is
  written to disk, and nothing survives past the response.

## Scoring framework and the checklist

The 25-point framework (`server/src/lib/scoringFramework.js`) is scored
entirely from what's visible in the screenshot — every one of the 25 items is
tagged `source: "screenshot"` in the API response. The 4-item checklist
(posting frequency, content format variety, engagement habits, group
memberships) is self-reported and is **not** scored — it doesn't add or
subtract points. It's passed to Claude as context and used to sharpen the
hard-truth diagnosis, gap analysis, and keyword strategy (e.g. "you post
2+ times a month but rarely comment — that caps your network visibility").
The UI labels this clearly so self-reported input is never blended silently
into the verified score.

## Setup

### 1. Backend

```bash
cd server
npm install
cp .env.example .env
# edit .env and set ANTHROPIC_API_KEY
npm run dev      # http://localhost:8787
```

### 2. Frontend

```bash
cd client
npm install
npm run dev       # http://localhost:5173, proxies /api to :8787
```

Open http://localhost:5173.

## API

- `POST /api/analyze` — multipart form: `screenshot` (png/jpg file) +
  `checklist` (JSON string). Returns the score, section breakdown (each item
  tagged `screenshot`/`checklist`), extracted text, gap analysis, keyword
  strategy, and top 5 priority fixes.
- `POST /api/rewrite` — JSON body: `extractedText` + `gapAnalysis` +
  `checklist` from a prior `/api/analyze` response. Returns 3 rewrite angles
  (Authority / Outcome / Niche). Never invents certifications, metrics, or
  achievements not present in the extracted text or checklist.

Both routes are rate-limited (default: 10 requests / 15 min / IP, configurable
via `RATE_LIMIT_MAX` / `RATE_LIMIT_WINDOW_MS`).

## Upload validation

- Only `image/png` and `image/jpeg` are accepted (rejected by both declared
  mimetype and by verifying the actual file's magic bytes — the `image-size`
  dependency has an unpatched DoS in its ICNS/JXL/HEIF parsers, and it sniffs
  real file bytes rather than trusting the declared mimetype, so only
  verified PNG/JPEG bytes are ever handed to it).
- Max upload size: 8MB (`MAX_UPLOAD_BYTES`).
- A warning (non-blocking) is surfaced in the UI if the screenshot's width is
  below `MIN_RECOMMENDED_WIDTH` (default 1000px), since photo/banner quality
  can't be assessed reliably at low resolution.

## Known dev-only vulnerability note

`npm audit` on the client flags Vite's dev server (HMR/dep-optimizer) for a
few CVEs that only affect the local dev server (not the production build
output) — fixing them fully requires a major Vite version bump. If you expose
the Vite dev server beyond localhost, upgrade Vite first (`npm audit fix
--force`) or put it behind auth.

## Screenshot capture instructions (shown in the UI)

Before capturing, click "Show all skills" and "Show all recommendations" if
those buttons appear on the profile, then use a full-page capture tool
(GoFullPage, Fireshot, or your browser's native full-page screenshot).

## Status

Phase 1 (upload → checklist → analyze → score) and Phase 2 (AI rewrite) are
both implemented.
