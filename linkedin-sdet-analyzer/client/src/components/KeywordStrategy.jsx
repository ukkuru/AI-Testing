import React from "react";
import { motion } from "framer-motion";
import { Tags } from "lucide-react";

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
      <h2>
        <Tags size={19} />
        Keyword strategy
      </h2>
      {Object.entries(CATEGORY_LABELS).map(([cat, label]) =>
        grouped[cat]?.length ? (
          <div className="keyword-group" key={cat}>
            <div className="keyword-group-title">{label}</div>
            <div className="keyword-chips">
              {grouped[cat].map((term, idx) => (
                <motion.span
                  className={`keyword-chip cat-${cat}`}
                  key={term}
                  initial={{ opacity: 0, scale: 0.9 }}
                  animate={{ opacity: 1, scale: 1 }}
                  transition={{ duration: 0.2, delay: idx * 0.03 }}
                >
                  {term}
                </motion.span>
              ))}
            </div>
          </div>
        ) : null
      )}
    </div>
  );
}
