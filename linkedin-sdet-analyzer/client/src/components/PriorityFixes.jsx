import React from "react";

export default function PriorityFixes({ fixes }) {
  const sorted = [...fixes].sort((a, b) => a.rank - b.rank);
  return (
    <div className="card">
      <h2>Top 5 priority fixes</h2>
      {sorted.map((fix) => (
        <div className="priority-fix" key={fix.rank}>
          <div className="priority-rank">{fix.rank}</div>
          <div>
            <div className="priority-fix-title">{fix.fix}</div>
            <div className="priority-fix-impact">{fix.impact}</div>
          </div>
        </div>
      ))}
    </div>
  );
}
