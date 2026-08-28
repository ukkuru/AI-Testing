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
  client/   React (Vite + react-router) frontend
            Marketing site (Home, Scoring Criteria, Contact, Login, Register)
            + a login-gated analyzer tool (Upload → Checklist → Results)
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
- **No persistent storage of screenshots.** Uploads use `multer` memory
  storage; nothing is written to disk, and nothing survives past the response.
  (User accounts are the one thing that *is* persisted — see below.)

## Accounts & auth

`POST /api/analyze` and `POST /api/rewrite` require a signed-in session — the
marketing site's "Try Out the Tool" links route through `/app`, which redirects
anonymous visitors to `/login` (returning them to `/app` after signing in).

- **Storage:** SQLite via `better-sqlite3`, one `users` table (`server/src/db.js`),
  file lives at `server/data/app.db` (gitignored, created on first boot).
- **Passwords:** hashed with `bcryptjs` (12 rounds), never logged or returned.
- **Sessions:** a JWT (`jsonwebtoken`) in an `httpOnly`, `SameSite=Lax` cookie —
  not readable by JS, sent automatically by the browser, verified per-request
  in `server/src/middleware/requireAuth.js`. Set `COOKIE_SECURE=true` once the
  app is served over HTTPS.
- **Routes:** `POST /api/auth/register`, `POST /api/auth/login`,
  `POST /api/auth/logout`, `GET /api/auth/me` (returns `{ user: null }` rather
  than erroring when logged out, so the frontend can silently hydrate session
  state on load). Rate-limited separately from the analysis endpoints
  (`AUTH_RATE_LIMIT_MAX`, default 20 / 15 min).
- **What email is used for:** account login only — shown explicitly on the
  registration form, next to the email field.
- Out of scope for this pass: password reset, email verification. The auth
  layer is straightforward to extend for those later.

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

## UI

React + [Framer Motion](https://www.framer.com/motion/) for transitions/stagger
animations and [lucide-react](https://lucide.dev/) for icons. Visual language
follows a Slacc-inspired design system: a deep aubergine primary (`#4a154b`),
cream/lavender surfaces, blue inline links, and pill-shaped buttons/tags, with
Inter substituted for the system's proprietary display and body fonts (Google
Fonts, with a system-font fallback if that request is blocked). Tokens live in
`client/src/styles.css`.

Light/dark theme is applied via a `data-theme` attribute set before React
mounts (no flash of the wrong theme), toggled from the header, and persisted
to `localStorage`. The design spec doesn't define a dark palette, so dark mode
is an original aubergine-toned companion (deep plum background, lightened
orchid accent) rather than a literal brand asset. The RGB score-band colors
from the scoring framework spec are used as-is in both themes — they're
semantic to the score, not brand decoration, so they're never re-themed.
Keyword-strategy chips intentionally share one neutral pill style rather than
being color-coded per category, per the design spec's "don't add a third
accent color" rule — differentiation there comes from the group headings.

## Pages

| Route | Description |
|---|---|
| `/` | Marketing home — hero, feature grid, "how it works", score-band preview, closing CTA. |
| `/criteria` | Full 25-point scoring framework, fetched live from `GET /api/framework`. |
| `/contact` | TestMetry contact details. |
| `/login` / `/register` | Auth forms. Register states explicitly that the email is used for login only. |
| `/app` | The analyzer tool (Upload → Checklist → Results). Requires sign-in — anonymous visitors are redirected to `/login` and returned here afterward. |

## Setup

### 1. Backend

```bash
cd server
npm install
cp .env.example .env
# edit .env and set ANTHROPIC_API_KEY, then generate a JWT secret:
node -e "console.log(require('crypto').randomBytes(48).toString('hex'))"
# paste the output into .env as JWT_SECRET
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

- `POST /api/analyze` *(requires sign-in)* — multipart form: `screenshot`
  (png/jpg file) + `checklist` (JSON string). Returns the score, section
  breakdown (each item tagged `screenshot`/`checklist`), extracted text, gap
  analysis, keyword strategy, and top 5 priority fixes.
- `POST /api/rewrite` *(requires sign-in)* — JSON body: `extractedText` +
  `gapAnalysis` + `checklist` from a prior `/api/analyze` response. Returns 3
  rewrite angles (Authority / Outcome / Niche). Never invents certifications,
  metrics, or achievements not present in the extracted text or checklist.
- `GET /api/framework` *(public)* — the same 25-point framework definition
  used to build the scoring prompt, served for the public Scoring Criteria
  page so it never drifts out of sync with what's actually scored.
- `POST /api/auth/register`, `POST /api/auth/login`, `POST /api/auth/logout`,
  `GET /api/auth/me` — see **Accounts & auth** above.

`/api/analyze` and `/api/rewrite` are rate-limited (default: 10 requests /
15 min / IP, via `RATE_LIMIT_MAX` / `RATE_LIMIT_WINDOW_MS`); the `/api/auth/*`
routes have their own separate, more permissive limit
(`AUTH_RATE_LIMIT_MAX` / `AUTH_RATE_LIMIT_WINDOW_MS`).

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

Phase 1 (upload → checklist → analyze → score), Phase 2 (AI rewrite), and
Phase 3 (marketing site + accounts) are all implemented.
