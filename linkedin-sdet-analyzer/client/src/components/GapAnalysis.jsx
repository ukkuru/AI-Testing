import React from "react";
import { Signpost, FileText, Briefcase, Sparkles } from "lucide-react";

const CONFIG = {
  headline: { label: "Headline", icon: Signpost },
  about: { label: "About", icon: FileText },
  experience: { label: "Experience", icon: Briefcase },
  skills: { label: "Skills", icon: Sparkles },
};

export default function GapAnalysis({ gapAnalysis }) {
  return (
    <div className="card">
      <h2>Gap analysis</h2>
      {Object.entries(CONFIG).map(([key, { label, icon: Icon }]) => (
        <div className="gap-row" key={key}>
          <div className="gap-label">
            <Icon size={13} />
            {label}
          </div>
          <div>{gapAnalysis?.[key]}</div>
        </div>
      ))}
    </div>
  );
}
