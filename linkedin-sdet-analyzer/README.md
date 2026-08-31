# LinkedIn Profile Analyzer & Rewriter — QA / Test Automation / SDET

Scores a LinkedIn profile's own "Save to PDF" export against a 15-point
framework built specifically for QA, test automation, and SDET careers, using
Claude to read the extracted PDF text directly (no image/vision analysis).
Optionally generates 3 positioning-angle rewrites (Authority / Outcome /
Niche) for the headline, About section, and one experience bullet.

## Architecture

```
linkedin-sdet-analyzer/
  server/   Node/Express API — talks to the Claude API server-side only
  client/   React (Vite + react-router) frontend
            Marketing site (Home, Scoring Criteria, Contact, Login, Register)
            + a login-gated analyzer tool (Upload → Results)
```

- **PDF text extraction, not vision.** The uploaded PDF's text is extracted
  server-side with `pdf-parse` and sent to Claude as plain text; Claude both
  scores the profile and transcribes the key text (headline, About excerpt,
  experience bullets, skills) in the same call, so the rewrite step never
  needs to re-read the PDF.
- **API key never reaches the browser.** All Claude calls happen in
  `server/src/lib/claudeClient.js`, called only from the Express routes.
- **Scoring math is computed server-side** (`server/src/lib/scoreCalculator.js`)
  from Claude's per-item 0/1 answers — the model's arithmetic is never trusted
  for the total/percentage/band.
- **No persistent storage of uploads.** Uploads use `multer` memory storage;
  nothing is written to disk, and nothing survives past the response.
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
- **What email is used for:** login, plus marketing emails and product
  updates — shown explicitly on the registration form, next to the email
  field.
- Out of scope for this pass: password reset, email verification. The auth
  layer is straightforward to extend for those later.

## Scoring framework

The 15-point framework (`server/src/lib/scoringFramework.js`) is scored
entirely from what's present in the text of LinkedIn's own "Save to PDF"
profile export. That export never includes the banner image, Featured
section, company logos, Recommendations, or the verified-badge indicator, so
the framework deliberately excludes anything that isn't reliably extractable
as text — see `/criteria` for the full breakdown of what's scored and why.

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
| `/criteria` | Full 15-point scoring framework, fetched live from `GET /api/framework`. |
| `/contact` | TestMetry contact details. |
| `/login` / `/register` | Auth forms. Register states explicitly that the email is used for login plus marketing/product updates. |
| `/app` | The analyzer tool (Upload → Results). Requires sign-in — anonymous visitors are redirected to `/login` and returned here afterward. |

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

- `POST /api/analyze` *(requires sign-in)* — multipart form: `profilePdf`
  (a LinkedIn "Save to PDF" export). Returns the score, section breakdown,
  extracted text, gap analysis, keyword strategy, and top 5 priority fixes.
- `POST /api/rewrite` *(requires sign-in)* — JSON body: `extractedText` +
  `gapAnalysis` from a prior `/api/analyze` response. Returns 3 rewrite
  angles (Authority / Outcome / Niche). Never invents certifications,
  metrics, or achievements not present in the extracted text.
- `GET /api/framework` *(public)* — the same 15-point framework definition
  used to build the scoring prompt, served for the public Scoring Criteria
  page so it never drifts out of sync with what's actually scored.
- `POST /api/auth/register`, `POST /api/auth/login`, `POST /api/auth/logout`,
  `GET /api/auth/me` — see **Accounts & auth** above.

`/api/analyze` and `/api/rewrite` are rate-limited (default: 10 requests /
15 min / IP, via `RATE_LIMIT_MAX` / `RATE_LIMIT_WINDOW_MS`); the `/api/auth/*`
routes have their own separate, more permissive limit
(`AUTH_RATE_LIMIT_MAX` / `AUTH_RATE_LIMIT_WINDOW_MS`).

## Upload validation

- Only `application/pdf` is accepted (rejected by both declared mimetype and
  by verifying the actual file's `%PDF` magic bytes, so a renamed non-PDF
  file is never handed to `pdf-parse`).
- Max upload size: 8MB (`MAX_UPLOAD_BYTES`).

## Known dev-only vulnerability note

`npm audit` on the client flags Vite's dev server (HMR/dep-optimizer) for a
few CVEs that only affect the local dev server (not the production build
output) — fixing them fully requires a major Vite version bump. If you expose
the Vite dev server beyond localhost, upgrade Vite first (`npm audit fix
--force`) or put it behind auth.

## PDF export instructions (shown in the UI)

On your LinkedIn profile, click the "More" button (below your profile
photo), then "Save to PDF" — that's the file to upload.

## Status

Phase 1 (upload → analyze → score), Phase 2 (AI rewrite), Phase 3 (marketing
site + accounts), and Phase 4 (PDF-export pivot, replacing screenshot/vision
analysis) are all implemented.
