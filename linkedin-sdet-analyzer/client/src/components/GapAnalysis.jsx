import React from "react";

const LABELS = {
  headline: "Headline",
  about: "About",
  experience: "Experience",
  skills: "Skills",
  featured_proof: "Featured / Proof",
};

export default function GapAnalysis({ gapAnalysis, checklistContextNotes }) {
  return (
    <div className="card">
      <h2>Gap analysis</h2>
      {Object.entries(LABELS).map(([key, label]) => (
        <div className="gap-row" key={key}>
          <div className="gap-label">{label}</div>
          <div>{gapAnalysis?.[key]}</div>
        </div>
      ))}
      {checklistContextNotes && (
        <div className="gap-row">
          <div className="gap-label">Checklist context</div>
          <div>{checklistContextNotes}</div>
        </div>
      )}
    </div>
  );
}
