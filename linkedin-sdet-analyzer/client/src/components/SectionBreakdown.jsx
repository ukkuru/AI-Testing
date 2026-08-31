import React, { useState } from "react";
import { AnimatePresence, motion } from "framer-motion";
import { Signpost, FileText, Briefcase, GraduationCap, Trophy, ChevronDown } from "lucide-react";

const SECTION_ICONS = {
  headline_positioning: Signpost,
  about_section: FileText,
  experience: Briefcase,
  credentials: GraduationCap,
  accomplishments: Trophy,
};

function bandColorForRatio(ratio) {
  const pct = ratio * 100;
  if (pct <= 19) return "var(--band-passive)";
  if (pct <= 39) return "var(--band-developing)";
  if (pct <= 59) return "var(--band-average)";
  if (pct <= 79) return "var(--band-professional)";
  return "var(--band-expert)";
}

function SectionItem({ item }) {
  const earned = item.pointsEarned === 1;
  return (
    <div className="score-item">
      <div className={`score-dot ${earned ? "earned" : "missing"}`}>
        {earned ? "✓" : "✕"}
      </div>
      <div className="score-item-body">
        <div className="score-item-label">{item.label}</div>
        <div className="score-item-rationale">{item.rationale}</div>
      </div>
    </div>
  );
}

function Section({ section, defaultOpen }) {
  const [open, setOpen] = useState(defaultOpen);
  const Icon = SECTION_ICONS[section.id] || FileText;
  const ratio = section.pointsPossible ? section.pointsEarned / section.pointsPossible : 0;
  const color = bandColorForRatio(ratio);

  return (
    <div className="section-block">
      <div className="section-summary" onClick={() => setOpen((o) => !o)}>
        <div className="section-title-group">
          <div className="section-icon" style={{ background: `${color}1f`, color }}>
            <Icon size={17} />
          </div>
          <div className="section-title-col">
            <div className="section-title">{section.label}</div>
            <div className="section-mini-bar">
              <motion.div
                className="section-mini-bar-fill"
                style={{ background: color }}
                initial={{ width: 0 }}
                animate={{ width: `${ratio * 100}%` }}
                transition={{ duration: 0.7, ease: "easeOut" }}
              />
            </div>
          </div>
        </div>
        <div style={{ display: "flex", alignItems: "center", gap: 12 }}>
          <span className="section-points">
            {section.pointsEarned}/{section.pointsPossible}
          </span>
          <motion.span
            className="section-toggle-icon"
            animate={{ rotate: open ? 180 : 0 }}
            transition={{ duration: 0.2 }}
          >
            <ChevronDown size={16} />
          </motion.span>
        </div>
      </div>
      <AnimatePresence initial={false}>
        {open && (
          <motion.div
            initial={{ height: 0, opacity: 0 }}
            animate={{ height: "auto", opacity: 1 }}
            exit={{ height: 0, opacity: 0 }}
            transition={{ duration: 0.25, ease: "easeInOut" }}
            style={{ overflow: "hidden" }}
          >
            <div className="section-detail">
              {section.note && <div className="section-note">{section.note}</div>}
              {section.items.map((item) => (
                <SectionItem key={item.id} item={item} />
              ))}
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
}

export default function SectionBreakdown({ sections }) {
  return (
    <div className="card">
      <h2>Section-by-section breakdown</h2>
      {sections.map((section, idx) => (
        <Section key={section.id} section={section} defaultOpen={idx === 0} />
      ))}
    </div>
  );
}
