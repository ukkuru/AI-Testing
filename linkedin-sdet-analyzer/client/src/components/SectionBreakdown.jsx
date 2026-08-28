import React, { useState } from "react";

function SourceTag({ source }) {
  return <span className={`source-tag ${source}`}>{source}</span>;
}

function SectionItem({ item }) {
  const earned = item.pointsEarned === 1;
  return (
    <div className="score-item">
      <div className={`score-dot ${earned ? "earned" : "missing"}`}>{earned ? "✓" : "✕"}</div>
      <div className="score-item-body">
        <div className="score-item-label">
          {item.label}
          <SourceTag source={item.source} />
        </div>
        <div className="score-item-rationale">{item.rationale}</div>
      </div>
    </div>
  );
}

function Section({ section, defaultOpen }) {
  const [open, setOpen] = useState(defaultOpen);
  return (
    <div className="section-block">
      <div className="section-summary" onClick={() => setOpen((o) => !o)}>
        <div className="section-title">{section.label}</div>
        <div style={{ display: "flex", alignItems: "center", gap: 10 }}>
          <span className="section-points">
            {section.pointsEarned}/{section.pointsPossible}
          </span>
          <span className="section-toggle-icon">{open ? "▲" : "▼"}</span>
        </div>
      </div>
      {open && (
        <div className="section-detail">
          {section.note && <div className="section-note">{section.note}</div>}
          {section.items.map((item) => (
            <SectionItem key={item.id} item={item} />
          ))}
        </div>
      )}
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
