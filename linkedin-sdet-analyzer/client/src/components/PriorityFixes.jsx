import React from "react";
import { motion } from "framer-motion";
import { Flag } from "lucide-react";

export default function PriorityFixes({ fixes }) {
  const sorted = [...fixes].sort((a, b) => a.rank - b.rank);
  return (
    <div className="card">
      <h2>
        <Flag size={19} />
        Top 5 priority fixes
      </h2>
      {sorted.map((fix, idx) => (
        <motion.div
          className="priority-fix"
          key={fix.rank}
          initial={{ opacity: 0, x: -10 }}
          animate={{ opacity: 1, x: 0 }}
          transition={{ duration: 0.25, delay: idx * 0.05 }}
        >
          <div className="priority-rank">{fix.rank}</div>
          <div>
            <div className="priority-fix-title">{fix.fix}</div>
            <div className="priority-fix-impact">{fix.impact}</div>
          </div>
        </motion.div>
      ))}
    </div>
  );
}
