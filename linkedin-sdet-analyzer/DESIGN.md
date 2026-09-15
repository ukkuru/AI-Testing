# Design System — LinkedIn Profile Scorecard for Testers

**Note on provenance:** this is a reconstruction from what is actually
implemented in `client/src/styles.css` today, not the original attachment
that was used as the starting brief. That original file was only ever
shared in chat and was never committed to the repo, so it isn't recoverable
here — this document describes the resulting, current design system instead.

A Slacc-inspired ("Slack" + "Salesforce" hybrid) aubergine system: deep
purple primary, warm cream/lavender neutrals, blue inline links, pill-shaped
buttons and tags.

## Typography

- Both display and body text use **Inter** (Google Fonts, with a system-font
  fallback stack if that request is blocked), substituting for the original
  spec's proprietary Salesforce Avant Garde / Salesforce Sans.
- `--font-display`: `"Inter", -apple-system, BlinkMacSystemFont, sans-serif`
  — used for `h1`–`h4`, with tight letter-spacing (`-0.014em` base,
  `-0.018em`/`-0.01em` on hero/logo text).
- `--font-sans`: `"Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif`
  — body text.

## Color tokens

### Light theme (default)
| Token | Value | Use |
|---|---|---|
| `--bg` | `#f8f4ef` | Page background |
| `--surface` | `#ffffff` | Cards |
| `--surface-2` | `#f4ede4` | Secondary surfaces (footer, etc.) |
| `--surface-hover` | `#efe3d5` | Hover state |
| `--border` / `--border-strong` | `#e6e6e6` / `#d6d1ca` | Dividers, card borders |
| `--text` | `#1d1d1d` | Primary text |
| `--text-muted` | `#696969` | Secondary text |
| `--text-faint` | `#8f8f8f` | Tertiary/caption text |
| `--accent` | `#4a154b` | Primary aubergine — buttons, active states |
| `--accent-hover` | `#611f69` | |
| `--accent-soft` / `--accent-soft-strong` | `#f9f0ff` / `#efd9f8` | Tinted backgrounds (chips, active nav) |
| `--link-blue` / `--link-hover` | `#1264a3` / `#3860be` | Inline links only |
| `--semantic-error` | `#cc4117` | |
| `--semantic-success` | `#007a5a` | |

### Dark theme (`data-theme="dark"`)
An original aubergine-toned companion palette — **not** a literal brand
asset, since the source spec didn't define a dark mode. Deep plum
background, lightened orchid accent so it still reads as the same brand
family:
| Token | Value |
|---|---|
| `--bg` | `#17111b` |
| `--surface` | `#221a27` |
| `--surface-2` | `#2a1f30` |
| `--text` | `#f6eef9` |
| `--text-muted` | `#c9b6d1` |
| `--accent` | `#c98fd8` |
| `--link-blue` | `#6fb3e8` |
| `--semantic-error` | `#ff8a62` |
| `--semantic-success` | `#3fd6ac` |

Theme is applied via a `data-theme` attribute on `<html>`, set **before**
React mounts (inline script in `index.html`) to avoid a flash of the wrong
theme, persisted to `localStorage`, toggle in the header.

### Locked, theme-independent tokens
Two categories deliberately never change between light/dark, because they
carry semantic meaning rather than brand decoration:

- **Score bands** (`--band-passive` `#c82828`, `--band-developing` `#e66e1e`,
  `--band-average` `#e6b41e`, `--band-professional` `#5aaa3c`,
  `--band-expert` `#148c5a`) — the 0–100% RGB bands from the scoring
  framework spec, used as-is so a "red" score always means the same thing
  regardless of theme.
- `--brand-band-bg` (`#4a154b`) — fixed deep-aubergine fill for large brand
  surfaces like the closing CTA band, so white text on it always keeps AA
  contrast (unlike `--accent`, which lightens in dark mode for small UI
  elements and would fail contrast at that size).

## Shape & elevation

- Radii: `--radius-xs` 2px → `--radius-xl` 16px, plus `--radius-pill` 90px
  for buttons/tags/badges.
- Shadows: `--shadow-sm` (cards), `--shadow-md` (elevated surfaces),
  `--shadow-btn` (buttons) — all soften and deepen slightly in dark mode.

## Components (patterns actually in use)

- **Buttons**: pill-shaped (`--radius-pill`), solid aubergine primary, ghost
  variant for secondary actions. In tight containers (the ~420px-wide auth
  card), buttons stack full-width with a centered ghost link beneath rather
  than sitting side-by-side — a fix added after the side-by-side layout
  wrapped button labels onto two lines on mobile.
- **Cards**: white/dark surface, `--radius-lg`, `--shadow-sm`.
  `--surface`/`--border` swap per theme; card content padding is consistent
  across the app (results dashboard, auth forms, criteria sections).
- **Tags/chips**: pill-shaped, single neutral style for keyword-strategy
  chips specifically — deliberately **not** color-coded per category
  (tool/testing_type/methodology/etc.), per the "don't add a third accent
  color" rule from the original spec; differentiation there comes from
  group headings instead.
- **Header logo**: icon (fixed 38×38 / 18px sparkle) + wordmark. In the
  marketing header the wordmark truncates with an ellipsis on narrow
  viewports (`white-space: nowrap; overflow: hidden; text-overflow: ellipsis`)
  rather than wrapping or forcing the header to overflow — added after the
  product rename to "LinkedIn Profile Scorecard for Testers" overflowed the
  390px mobile header and pushed the hamburger menu off-screen.

## Layout

- Content max-width: 980px (tool shell `.app-shell`), 1120px (marketing
  header/footer/main).
- Side gutter: 20px minimum at all widths.
- Verified responsive down to ~390px viewport width (iPhone-class), with two
  specific mobile bugs found and fixed post-launch (documented above) —
  treat that width as the practical minimum to test against for any new
  header/button-row work.

## What this system deliberately does NOT do

- No third accent color beyond aubergine + the locked score-band palette.
- No OCR/vision-styled iconography — the product now reads PDF text, not
  screenshots, so upload/loading copy and icons were updated accordingly
  (see commit `df4d86d`) away from screenshot/camera imagery toward
  file/document imagery.
