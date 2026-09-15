# Requirements — LinkedIn Profile Scorecard for Testers

Compiled from the current codebase on `claude/linkedin-sdet-analyzer-si58nm` (final,
deployed version as of this document). Reflects what is actually implemented,
not an aspirational spec.

## 1. Purpose

Score a QA/test-automation/SDET professional's LinkedIn profile against a fixed,
transparent rubric, and offer AI-generated rewrite suggestions for the weakest
sections — positioned as a lead-gen tool for TestMetry.

## 2. Functional Requirements

### 2.1 Input
- User uploads their LinkedIn profile as a **PDF** — specifically the file
  produced by LinkedIn's own **Profile → More → Save to PDF** export.
- Accepted type: `application/pdf` only, verified both by declared mimetype
  and by checking the file's `%PDF` magic bytes (not just the client-supplied
  content-type).
- Max upload size: 8MB (configurable via `MAX_UPLOAD_BYTES`).
- Upload is in-memory only (`multer` memory storage) — never written to disk,
  never persisted beyond the single request/response cycle.

### 2.2 Analysis
- Server extracts plain text from the PDF (`pdf-parse`).
- Extracted text is sent to Claude (Anthropic API) as a single forced
  tool-use call — the model must respond by calling `submit_analysis` with a
  fully structured result; free-text responses are rejected.
- Scoring is against a **fixed 15-point framework** (`server/src/lib/scoringFramework.js`),
  the single source of truth for both the prompt and the frontend's Criteria
  page (served live via `GET /api/framework` so they can never drift apart):

  | Section | Points | Items |
  |---|---|---|
  | Headline & Positioning | 3 | Headline UVP, headline keywords, portfolio/CTA link in text |
  | About Section | 6 | Hook strength, personal intro, UVP, company positioning, clear CTA, top-5 skills in About |
  | Experience | 1 | Current role description quality (outcomes, not a task list) |
  | Credentials | 4 | Education/certifications, top-3 skills match seniority, 15+ skills listed, SEO-researched skill terms |
  | Accomplishments | 1 | 2+ accomplishments (certifications, publications, talks) |

  Every item is binary (1 = present/strong, 0 = missing/weak) — no partial
  credit. Every item must be assessable purely from PDF **text**; nothing that
  only exists visually (banner, Featured section media, company logos,
  Recommendations, verified badge) is in scope, because LinkedIn's PDF export
  doesn't include those.
- Final score/percentage/band is **recomputed server-side** from the model's
  per-item 0/1 answers (`scoreCalculator.js`) — the model's own stated totals
  are never trusted.
- Score bands (percentage-based, theme-independent RGB colors):
  Passive (0–19%), Developing (20–39%), Average (40–59%), Professional
  (60–79%), Expert (80–100%).

### 2.3 Output
For each analysis, the API returns:
- Score (points earned/possible, percentage, level, RGB/hex).
- Section-by-section breakdown with per-item rationale (why each point was
  earned or missed, tied to specific text).
- Gap analysis (headline, about, experience, skills).
- Keyword strategy: 10–15 real QA/SDET hiring keywords, each categorized as
  tool / testing_type / methodology / certification / role_level.
- Top 5 priority fixes, ranked by impact.
- Extracted text (headline, About excerpt, current role/company, experience
  bullets, skills) — reused by the rewrite feature so it never has to
  re-parse the PDF.

### 2.4 AI Rewrite
- On request, generates 3 positioning-angle rewrites — **Authority**
  (technical depth), **Outcome** (metrics/impact), **Niche** (specialized
  focus) — for the headline, About section, and one experience bullet.
- Hard constraint: never invents certifications, metrics, achievements, or
  employers not present in the extracted text. Enforced in the system prompt
  the same way the analysis step is.

### 2.5 Accounts
- `/api/analyze` and `/api/rewrite` require a signed-in session; anonymous
  visitors hitting `/app` are redirected to `/login` and returned to `/app`
  after authenticating.
- Registration: email + password (min 8 characters), passwords hashed with
  `bcryptjs` (12 rounds), never logged or returned by any endpoint.
- Sessions: JWT in an `httpOnly`, `SameSite=Lax` cookie, 7-day expiry.
  `COOKIE_SECURE=true` required once served over HTTPS.
- The registration form explicitly discloses that the email is used for
  login **and** for marketing emails / product updates.
- No password reset or email verification (explicitly out of scope for this
  version).
- No server-side session table — "logged in" is defined purely by holding a
  valid, unexpired JWT cookie; there is no live "who's online now" view.

### 2.6 Marketing site
Public, unauthenticated pages:
- **Home** (`/`) — hero, feature grid, "how it works" (3 steps: Upload → AI
  analysis → Score & rewrite), score-band preview, closing CTA.
- **Scoring Criteria** (`/criteria`) — the full 15-point framework, fetched
  live from `GET /api/framework` so it's always in sync with what's actually
  scored.
- **Contact** (`/contact`) — TestMetry's Google Business Profile details
  (Kochi, India), email `reachme@qpulse.sbs`.
- **Login** / **Register** (`/login`, `/register`).

The tool itself lives at `/app`, gated behind sign-in.

## 3. Non-Functional Requirements

- **Rate limiting**: `/api/analyze` + `/api/rewrite` limited to 10
  requests/15 min/IP by default; `/api/auth/*` limited separately (20/15 min
  by default). Both configurable via env vars.
- **No third-party data retention**: uploaded PDFs and screenshots (legacy)
  are processed in memory only, never written to disk or a database.
- **Secrets**: `ANTHROPIC_API_KEY` and `JWT_SECRET` never reach the browser;
  all Claude calls happen server-side only (`server/src/lib/claudeClient.js`).
- **Theming**: light/dark mode, applied before React mounts (no flash of
  wrong theme), toggle persisted to `localStorage`.
- **Responsive**: works down to ~390px mobile viewport width — verified for
  the auth forms and marketing header specifically after two mobile-layout
  bugs were found and fixed post-launch (submit-button wrapping; the
  marketing logo overflowing and hiding the hamburger menu).
- **Accessibility of the score bands**: RGB score-band colors are fixed
  across both themes (not re-themed) so they stay semantically meaningful
  and maintain contrast.

## 4. Technical / Infrastructure Requirements

- **Backend**: Node.js ≥22 (required by `better-sqlite3`'s prebuilt binary
  for the resolved version), Express, SQLite (`better-sqlite3`) for the
  `users` table only.
- **Frontend**: React + Vite + react-router, Framer Motion, lucide-react.
- **AI**: Anthropic API (`@anthropic-ai/sdk`), model configurable via
  `ANTHROPIC_MODEL` (defaults to `claude-sonnet-5`), forced tool-use for all
  structured responses.
- **Deployment**: single Docker image (multi-stage build: client build →
  server deps → runtime), `docker-compose.yml` binds to `127.0.0.1:3101`
  only, reverse-proxied by Nginx + Let's Encrypt/Certbot on the subdomain
  `sdet.qpulse.sbs`. Designed to run alongside a pre-existing, unrelated app
  on the same VPS without touching its config or certificate.
- **CORS**: locked to `CLIENT_ORIGIN`, credentials enabled (needed for the
  auth cookie).

## 5. Explicitly Out of Scope (this version)

- Screenshot/vision-based analysis (replaced entirely by the PDF-export
  pipeline — see commit `df4d86d`).
- The 4-item self-reported engagement checklist (posting frequency, content
  variety, etc.) — removed along with the pivot to PDF, since it had no
  PDF-derivable equivalent and never contributed to score.
- Password reset / email verification.
- Any scoring signal not present in LinkedIn's own PDF export (banner,
  Featured section, company logos, Recommendations, verified badge, activity
  history, connection/follower counts).
- Live "active sessions" / who's-logged-in-now visibility (JWT is stateless;
  only registered-account listing is available via direct DB query).
