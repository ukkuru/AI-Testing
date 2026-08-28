import React from "react";
import { Link } from "react-router-dom";
import { motion } from "framer-motion";
import {
  ArrowRight,
  ScanEye,
  ListTree,
  Tags,
  Wand2,
  UploadCloud,
  ListChecks,
  Sparkles,
  Search,
  TrendingUp,
} from "lucide-react";

const FEATURES = [
  {
    icon: ScanEye,
    title: "Vision-powered analysis",
    body: "Upload one full-page screenshot. Claude reads it directly — your photo, banner, headline, About section, experience, skills, and recommendations — no manual data entry.",
  },
  {
    icon: ListTree,
    title: "A 25-point QA/SDET framework",
    body: "Purpose-built scoring across First Impression, About, Experience, Credentials, Recommendations, and Accomplishments — graded in the language hiring managers actually use.",
  },
  {
    icon: Tags,
    title: "Keyword strategy for recruiter search",
    body: "Get 10–15 real hiring keywords — SDET, Selenium, Cypress, Playwright, ISTQB, shift-left testing, CI/CD, API testing — categorized by tool, methodology, certification, and role level.",
  },
  {
    icon: Wand2,
    title: "3 AI rewrite angles",
    body: "Authority, Outcome, and Niche rewrites for your headline, About hook, and one experience bullet — grounded only in what's actually on your profile.",
  },
];

const STEPS = [
  { icon: UploadCloud, title: "Upload", body: "One full-page LinkedIn screenshot — PNG or JPG." },
  { icon: ListChecks, title: "Quick checklist", body: "4 questions about your posting and engagement habits." },
  { icon: ScanEye, title: "AI analysis", body: "Claude scores your profile against the 25-point framework." },
  { icon: TrendingUp, title: "Score & rewrite", body: "See your gaps, keyword strategy, and request an AI rewrite." },
];

const BANDS = [
  { label: "Passive", range: "0–19%", hex: "#c82828" },
  { label: "Developing", range: "20–39%", hex: "#e66e1e" },
  { label: "Average", range: "40–59%", hex: "#e6b41e" },
  { label: "Professional", range: "60–79%", hex: "#5aaa3c" },
  { label: "Expert", range: "80–100%", hex: "#148c5a" },
];

const fadeUp = {
  hidden: { opacity: 0, y: 18 },
  show: { opacity: 1, y: 0, transition: { duration: 0.45 } },
};

export default function Home() {
  return (
    <div className="home-page">
      <section className="hero">
        <motion.div className="hero-copy" initial="hidden" animate="show" variants={fadeUp}>
          <span className="hero-eyebrow">
            <Sparkles size={13} />
            Built for QA, Test Automation &amp; SDET Careers
          </span>
          <h1 className="hero-title">
            Your LinkedIn profile should get you found — <span className="hero-title-accent">not scrolled past</span>
          </h1>
          <p className="hero-subtitle">
            Recruiters and ATS search LinkedIn for specific terms — <strong>SDET</strong>, <strong>Selenium</strong>,{" "}
            <strong>Cypress</strong>, <strong>Playwright</strong>, <strong>ISTQB</strong>, <strong>shift-left testing</strong>,{" "}
            <strong>CI/CD</strong>. Our AI-powered LinkedIn profile analyzer scores your profile against a 25-point
            framework built specifically for QA engineers, test automation specialists, and SDETs — then shows you
            exactly what to fix.
          </p>
          <div className="hero-cta-row">
            <Link to="/app" className="btn btn-lg">
              Try Out the Tool
              <ArrowRight size={16} />
            </Link>
            <Link to="/criteria" className="btn btn-secondary btn-lg">
              See the Scoring Criteria
            </Link>
          </div>
          <div className="hero-trust-note">
            <Search size={13} />
            No OCR, no manual entry — Claude's vision model reads your screenshot directly.
          </div>
        </motion.div>

        <motion.div
          className="hero-preview"
          initial={{ opacity: 0, scale: 0.95, y: 20 }}
          animate={{ opacity: 1, scale: 1, y: 0 }}
          transition={{ duration: 0.5, delay: 0.15 }}
        >
          <div className="hero-preview-card">
            <div className="hero-preview-label">Sample score report</div>
            <div className="hero-preview-gauge-row">
              <svg width="88" height="88" viewBox="0 0 88 88">
                <circle cx="44" cy="44" r="38" fill="none" stroke="var(--border)" strokeWidth="9" />
                <circle
                  cx="44"
                  cy="44"
                  r="38"
                  fill="none"
                  stroke="#5aaa3c"
                  strokeWidth="9"
                  strokeLinecap="round"
                  strokeDasharray={2 * Math.PI * 38}
                  strokeDashoffset={2 * Math.PI * 38 * (1 - 0.72)}
                  transform="rotate(-90 44 44)"
                />
              </svg>
              <div>
                <div className="hero-preview-pct">72%</div>
                <span className="level-badge" style={{ background: "#5aaa3c" }}>
                  Professional
                </span>
              </div>
            </div>
            <div className="hero-preview-rows">
              {["Headline keywords", "About section hook", "Experience outcomes", "Skills coverage"].map(
                (label, idx) => (
                  <div className="hero-preview-row" key={label}>
                    <span className={`hero-preview-dot ${idx === 2 ? "miss" : "hit"}`}>{idx === 2 ? "✕" : "✓"}</span>
                    {label}
                  </div>
                )
              )}
            </div>
          </div>
        </motion.div>
      </section>

      <motion.section
        className="section-block-wide"
        initial="hidden"
        whileInView="show"
        viewport={{ once: true, margin: "-80px" }}
        variants={fadeUp}
      >
        <h2 className="section-heading">Why most QA and SDET profiles are invisible</h2>
        <p className="section-lead">
          LinkedIn profile optimization for QA engineers isn't the same as for other roles. Recruiters sourcing test
          automation talent search for exact terms — automation coverage, defect leakage, release velocity, test
          pyramid, AI in testing. A profile that reads "QA Engineer with 5 years of experience" instead of naming
          your stack, your methodology, and your impact simply doesn't surface in that search.
        </p>
      </motion.section>

      <section className="feature-grid-section">
        <motion.div className="feature-grid" initial="hidden" whileInView="show" viewport={{ once: true, margin: "-80px" }}>
          {FEATURES.map((f, idx) => (
            <motion.div
              className="feature-card"
              key={f.title}
              variants={fadeUp}
              initial="hidden"
              whileInView="show"
              viewport={{ once: true }}
              transition={{ delay: idx * 0.06 }}
            >
              <div className="feature-icon">
                <f.icon size={20} />
              </div>
              <h3>{f.title}</h3>
              <p>{f.body}</p>
            </motion.div>
          ))}
        </motion.div>
      </section>

      <section className="how-it-works-section">
        <h2 className="section-heading centered">How the LinkedIn profile analysis works</h2>
        <div className="how-it-works-row">
          {STEPS.map((s, idx) => (
            <motion.div
              className="how-it-works-step"
              key={s.title}
              initial="hidden"
              whileInView="show"
              viewport={{ once: true }}
              variants={fadeUp}
              transition={{ delay: idx * 0.08 }}
            >
              <div className="how-it-works-num">{idx + 1}</div>
              <div className="how-it-works-icon">
                <s.icon size={18} />
              </div>
              <h4>{s.title}</h4>
              <p>{s.body}</p>
            </motion.div>
          ))}
        </div>
      </section>

      <section className="band-strip-section">
        <h2 className="section-heading centered">Where does your profile land?</h2>
        <div className="band-strip">
          {BANDS.map((b) => (
            <div className="band-strip-item" key={b.label}>
              <span className="band-strip-swatch" style={{ background: b.hex }} />
              <div>
                <div className="band-strip-label">{b.label}</div>
                <div className="band-strip-range">{b.range}</div>
              </div>
            </div>
          ))}
        </div>
        <Link to="/criteria" className="band-strip-link">
          See the full 25-point scoring criteria
          <ArrowRight size={14} />
        </Link>
      </section>

      <section className="cta-band">
        <h2>Ready to see where your profile stands?</h2>
        <p>Upload a screenshot, answer 4 quick questions, and get your score in under a minute.</p>
        <Link to="/app" className="btn btn-lg btn-on-band">
          Try Out the Tool
          <ArrowRight size={16} />
        </Link>
      </section>
    </div>
  );
}
