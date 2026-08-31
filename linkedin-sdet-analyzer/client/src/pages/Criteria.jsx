import React, { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { motion } from "framer-motion";
import {
  ArrowRight,
  AlertTriangle,
  Signpost,
  FileText,
  Briefcase,
  GraduationCap,
  Trophy,
  Info,
} from "lucide-react";
import { fetchFramework } from "../api";

const SECTION_ICONS = {
  headline_positioning: Signpost,
  about_section: FileText,
  experience: Briefcase,
  credentials: GraduationCap,
  accomplishments: Trophy,
};

export default function Criteria() {
  const [data, setData] = useState(null);
  const [error, setError] = useState("");

  useEffect(() => {
    fetchFramework()
      .then(setData)
      .catch((err) => setError(err.message || "Could not load the scoring framework."));
  }, []);

  return (
    <div className="criteria-page">
      <section className="criteria-hero">
        <h1 className="section-heading">The 15-point QA/SDET scoring framework</h1>
        <p className="section-lead">
          Every point is assessed directly from the text of your LinkedIn "Save to PDF" export — nothing is scored
          from unverifiable claims. Each item is worth exactly 1 point, scored present (1) or missing (0). Your
          total, out of 15, maps to one of 5 RGB score bands.
        </p>
        <Link to="/app" className="btn btn-lg">
          Try Out the Tool
          <ArrowRight size={16} />
        </Link>
      </section>

      <section className="card">
        <h2>Score bands</h2>
        <div className="criteria-band-table">
          {(data?.scoreBands || DEFAULT_BANDS).map((band) => (
            <div className="criteria-band-row" key={band.level}>
              <span className="band-strip-swatch" style={{ background: band.hex }} />
              <span className="criteria-band-level">{band.level}</span>
              <span className="criteria-band-range">
                {band.min}–{band.max}%
              </span>
            </div>
          ))}
        </div>
      </section>

      <div className="callout">
        <Info size={16} />
        <div>
          <strong>Why 15, not 25:</strong> all points are scored from text that's actually present in LinkedIn's own
          "Save to PDF" export. That export doesn't include your banner image, Featured section, company logos,
          Recommendations, or the verified-badge indicator — so nothing is scored from those, and nothing is ever
          guessed.
        </div>
      </div>

      {error && (
        <div className="error-banner">
          <AlertTriangle size={16} />
          <span>{error}</span>
        </div>
      )}

      {!data && !error && <div className="card subtle-text">Loading scoring framework…</div>}

      {data?.sections.map((section, idx) => {
        const Icon = SECTION_ICONS[section.id] || FileText;
        return (
          <motion.div
            className="card"
            key={section.id}
            initial={{ opacity: 0, y: 12 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true, margin: "-60px" }}
            transition={{ duration: 0.3, delay: Math.min(idx * 0.04, 0.3) }}
          >
            <h2>
              <span className="section-icon" style={{ background: "var(--accent-soft)", color: "var(--accent)" }}>
                <Icon size={17} />
              </span>
              {section.label}
              <span className="criteria-points-pill">{section.maxPoints} pts</span>
            </h2>
            <ul className="criteria-item-list">
              {section.items.map((item) => (
                <li key={item.id}>{item.label}</li>
              ))}
            </ul>
          </motion.div>
        );
      })}

      <section className="cta-band">
        <h2>Know exactly where you stand</h2>
        <p>Run your profile through the full 15-point framework in under a minute.</p>
        <Link to="/app" className="btn btn-lg btn-on-band">
          Try Out the Tool
          <ArrowRight size={16} />
        </Link>
      </section>
    </div>
  );
}

const DEFAULT_BANDS = [
  { level: "Passive", min: 0, max: 19, hex: "#c82828" },
  { level: "Developing", min: 20, max: 39, hex: "#e66e1e" },
  { level: "Average", min: 40, max: 59, hex: "#e6b41e" },
  { level: "Professional", min: 60, max: 79, hex: "#5aaa3c" },
  { level: "Expert", min: 80, max: 100, hex: "#148c5a" },
];
