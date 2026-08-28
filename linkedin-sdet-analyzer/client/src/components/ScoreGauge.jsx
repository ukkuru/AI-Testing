import React from "react";

const SIZE = 140;
const STROKE = 12;
const RADIUS = (SIZE - STROKE) / 2;
const CIRCUMFERENCE = 2 * Math.PI * RADIUS;

export default function ScoreGauge({ percentage, hex }) {
  const clamped = Math.max(0, Math.min(100, percentage));
  const offset = CIRCUMFERENCE * (1 - clamped / 100);

  return (
    <svg width={SIZE} height={SIZE} viewBox={`0 0 ${SIZE} ${SIZE}`}>
      <circle
        cx={SIZE / 2}
        cy={SIZE / 2}
        r={RADIUS}
        fill="none"
        stroke="#e9ecf0"
        strokeWidth={STROKE}
      />
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
        style={{ transition: "stroke-dashoffset 0.6s ease" }}
      />
    </svg>
  );
}
