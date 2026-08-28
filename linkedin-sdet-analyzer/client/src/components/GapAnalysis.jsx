import React from "react";
import { Signpost, FileText, Briefcase, Sparkles, Star, MessageCircle } from "lucide-react";

const CONFIG = {
  headline: { label: "Headline", icon: Signpost },
  about: { label: "About", icon: FileText },
  experience: { label: "Experience", icon: Briefcase },
  skills: { label: "Skills", icon: Sparkles },
  featured_proof: { label: "Featured / Proof", icon: Star },
};

export default function GapAnalysis({ gapAnalysis, checklistContextNotes }) {
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
      {checklistContextNotes && (
        <div className="gap-row">
          <div className="gap-label">
            <MessageCircle size={13} />
            Checklist context
          </div>
          <div>{checklistContextNotes}</div>
        </div>
      )}
    </div>
  );
}
