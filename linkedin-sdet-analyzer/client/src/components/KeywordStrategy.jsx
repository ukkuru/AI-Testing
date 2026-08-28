import React from "react";

const CATEGORY_LABELS = {
  tool: "Tools",
  testing_type: "Testing types",
  methodology: "Methodologies",
  certification: "Certifications",
  role_level: "Role-level terms",
};

export default function KeywordStrategy({ keywords }) {
  const grouped = keywords.reduce((acc, kw) => {
    acc[kw.category] = acc[kw.category] || [];
    acc[kw.category].push(kw.term);
    return acc;
  }, {});

  return (
    <div className="card">
      <h2>Keyword strategy</h2>
      {Object.entries(CATEGORY_LABELS).map(([cat, label]) =>
        grouped[cat]?.length ? (
          <div className="keyword-group" key={cat}>
            <div className="keyword-group-title">{label}</div>
            <div className="keyword-chips">
              {grouped[cat].map((term) => (
                <span className="keyword-chip" key={term}>
                  {term}
                </span>
              ))}
            </div>
          </div>
        ) : null
      )}
    </div>
  );
}
