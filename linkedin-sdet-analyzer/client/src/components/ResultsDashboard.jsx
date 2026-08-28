import React, { useState } from "react";
import ScoreGauge from "./ScoreGauge";
import SectionBreakdown from "./SectionBreakdown";
import GapAnalysis from "./GapAnalysis";
import KeywordStrategy from "./KeywordStrategy";
import PriorityFixes from "./PriorityFixes";
import RewritePanel from "./RewritePanel";

export default function ResultsDashboard({ analysis, onStartOver }) {
  const [showRewrite, setShowRewrite] = useState(false);
  const { score, sections, gapAnalysis, keywordStrategy, topPriorityFixes, checklistContextNotes, lowResolutionWarning, extractedText, checklist } =
    analysis;

  return (
    <div>
      {lowResolutionWarning && <div className="warning-banner">{lowResolutionWarning}</div>}

      <div className="card">
        <div className="score-header">
          <div className="gauge-wrap">
            <ScoreGauge percentage={score.percentage} hex={score.hex} />
            <div className="gauge-label">
              <div className="gauge-percentage">{score.percentage}%</div>
            </div>
          </div>
          <div>
            <span className="level-badge" style={{ background: score.hex }}>
              {score.level}
            </span>
            <div className="score-summary-points">
              {score.totalEarned} / {score.totalPossible} points scored from your screenshot
            </div>
          </div>
        </div>
      </div>

      <SectionBreakdown sections={sections} />
      <GapAnalysis gapAnalysis={gapAnalysis} checklistContextNotes={checklistContextNotes} />
      <KeywordStrategy keywords={keywordStrategy} />
      <PriorityFixes fixes={topPriorityFixes} />

      {!showRewrite ? (
        <div className="card">
          <h2>Want AI-rewritten sections?</h2>
          <p style={{ fontSize: 13.5, color: "#5b6270" }}>
            Get 3 positioning angles for your headline, About section, and one experience bullet.
          </p>
          <button className="btn" onClick={() => setShowRewrite(true)}>
            Request rewrite
          </button>
        </div>
      ) : (
        <RewritePanel extractedText={extractedText} gapAnalysis={gapAnalysis} checklist={checklist} />
      )}

      <div className="btn-row">
        <button className="btn btn-secondary" onClick={onStartOver}>
          Analyze a different screenshot
        </button>
        <span />
      </div>
    </div>
  );
}
