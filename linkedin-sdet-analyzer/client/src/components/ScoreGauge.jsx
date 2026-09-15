import React, { useEffect, useState } from "react";
import { animate } from "framer-motion";

const SIZE = 156;
const STROKE = 13;
const RADIUS = (SIZE - STROKE) / 2;
const CIRCUMFERENCE = 2 * Math.PI * RADIUS;

export default function ScoreGauge({ percentage, hex }) {
  const target = Math.max(0, Math.min(100, percentage));
  const [displayPct, setDisplayPct] = useState(0);

  useEffect(() => {
    const controls = animate(0, target, {
      duration: 1.1,
      ease: [0.16, 1, 0.3, 1],
      onUpdate: (v) => setDisplayPct(v),
    });
    return () => controls.stop();
  }, [target]);

  const offset = CIRCUMFERENCE * (1 - displayPct / 100);

  return (
    <div className="gauge-wrap">
      <svg width={SIZE} height={SIZE} viewBox={`0 0 ${SIZE} ${SIZE}`} style={{ filter: `drop-shadow(0 0 14px ${hex}33)` }}>
        <circle cx={SIZE / 2} cy={SIZE / 2} r={RADIUS} fill="none" stroke="var(--border)" strokeWidth={STROKE} />
        <circle
          cx={SIZE / 2}
          cy={SIZE / 2}
          r={RADIUS}
          fill="none"
          stroke={hex}
          strokeWidth={STROKE}
          strokeLinecap="round"
          strokeDasharray={CIRCUMFERENCE}
          strokeDashoffset={offset}
          transform={`rotate(-90 ${SIZE / 2} ${SIZE / 2})`}
        />
      </svg>
      <div className="gauge-label">
        <div className="gauge-percentage">{Math.round(displayPct)}%</div>
      </div>
    </div>
  );
}
