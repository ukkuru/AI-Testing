import React, { useState } from "react";
import { motion } from "framer-motion";
import { AlertTriangle, Wand2 } from "lucide-react";
import ScoreGauge from "./ScoreGauge";
import SectionBreakdown from "./SectionBreakdown";
import GapAnalysis from "./GapAnalysis";
import KeywordStrategy from "./KeywordStrategy";
import PriorityFixes from "./PriorityFixes";
import RewritePanel from "./RewritePanel";

const LEVEL_SUMMARY = {
  Passive: "Your profile is nearly invisible to recruiters searching for QA/SDET talent.",
  Developing: "The basics are there, but you're missing the signals recruiters filter on.",
  Average: "Functional, but it blends in with hundreds of similar QA profiles.",
  Professional: "Solid foundation — a handful of targeted fixes will make you stand out.",
  Expert: "Highly optimized for QA/SDET recruiter search and outbound sourcing.",
};

const container = {
  hidden: {},
  show: { transition: { staggerChildren: 0.06 } },
};
const item = {
  hidden: { opacity: 0, y: 14 },
  show: { opacity: 1, y: 0, transition: { duration: 0.35 } },
};

export default function ResultsDashboard({ analysis, onStartOver }) {
  const [showRewrite, setShowRewrite] = useState(false);
  const { score, sections, gapAnalysis, keywordStrategy, topPriorityFixes, checklistContextNotes, lowResolutionWarning, extractedText, checklist } =
    analysis;

  return (
    <motion.div variants={container} initial="hidden" animate="show">
      {lowResolutionWarning && (
        <motion.div variants={item} className="warning-banner">
          <AlertTriangle size={16} />
          <span>{lowResolutionWarning}</span>
        </motion.div>
      )}

      <motion.div variants={item} className="card score-hero">
        <div className="score-header">
          <ScoreGauge percentage={score.percentage} hex={score.hex} />
          <div>
            <div className="score-summary-headline">{LEVEL_SUMMARY[score.level]}</div>
            <span className="level-badge" style={{ background: score.hex }}>
              {score.level}
            </span>
            <div className="score-summary-points">
              {score.totalEarned} / {score.totalPossible} points scored from your screenshot
            </div>
          </div>
        </div>
      </motion.div>

      <motion.div variants={item}>
        <SectionBreakdown sections={sections} />
      </motion.div>
      <motion.div variants={item}>
        <GapAnalysis gapAnalysis={gapAnalysis} checklistContextNotes={checklistContextNotes} />
      </motion.div>
      <motion.div variants={item}>
        <KeywordStrategy keywords={keywordStrategy} />
      </motion.div>
      <motion.div variants={item}>
        <PriorityFixes fixes={topPriorityFixes} />
      </motion.div>

      <motion.div variants={item}>
        {!showRewrite ? (
          <div className="card">
            <h2>
              <Wand2 size={19} />
              Want AI-rewritten sections?
            </h2>
            <p className="subtle-text">
              Get 3 positioning angles for your headline, About section, and one experience bullet.
            </p>
            <button className="btn" onClick={() => setShowRewrite(true)}>
              Request rewrite
            </button>
          </div>
        ) : (
          <RewritePanel extractedText={extractedText} gapAnalysis={gapAnalysis} checklist={checklist} />
        )}
      </motion.div>

      <motion.div variants={item} className="btn-row">
        <button className="btn btn-secondary" onClick={onStartOver}>
          Analyze a different screenshot
        </button>
        <span />
      </motion.div>
    </motion.div>
  );
}
