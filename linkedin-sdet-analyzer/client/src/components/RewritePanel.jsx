import React, { useState } from "react";
import { requestRewrite } from "../api";

export default function RewritePanel({ extractedText, gapAnalysis, checklist }) {
  const [status, setStatus] = useState("idle"); // idle | loading | done | error
  const [error, setError] = useState("");
  const [result, setResult] = useState(null);
  const [activeAngle, setActiveAngle] = useState(0);

  async function handleGenerate() {
    setStatus("loading");
    setError("");
    try {
      const data = await requestRewrite({ extractedText, gapAnalysis, checklist });
      setResult(data);
      setActiveAngle(0);
      setStatus("done");
    } catch (err) {
      setError(err.message || "Failed to generate rewrite.");
      setStatus("error");
    }
  }

  if (status === "idle" || status === "error") {
    return (
      <div className="card">
        <h2>AI rewrite</h2>
        <p style={{ fontSize: 13.5, color: "#5b6270" }}>
          Generate 3 positioning angles (Authority, Outcome, Niche) for your headline, About section, and one
          experience bullet — grounded only in what's actually on your profile and checklist, nothing invented.
        </p>
        {error && <div className="error-banner">{error}</div>}
        <button className="btn" onClick={handleGenerate}>
          Generate rewrite
        </button>
      </div>
    );
  }

  if (status === "loading") {
    return (
      <div className="card loading-screen">
        <div className="spinner" />
        <div>Generating rewrite angles...</div>
      </div>
    );
  }

  const angle = result.rewrites[activeAngle];

  return (
    <div className="card">
      <h2>AI rewrite</h2>
      <div className="angle-tabs">
        {result.rewrites.map((r, idx) => (
          <button
            key={r.angle}
            className={`angle-tab${idx === activeAngle ? " active" : ""}`}
            onClick={() => setActiveAngle(idx)}
          >
            {r.angle}
          </button>
        ))}
      </div>

      <div className="compare-grid">
        <div className="compare-col original">
          <h3>Original</h3>
          <div className="field-block">
            <h4>Headline</h4>
            <p>{result.original.headline || "(not visible on screenshot)"}</p>
          </div>
          <div className="field-block">
            <h4>About</h4>
            <p>{result.original.about || "(not visible on screenshot)"}</p>
          </div>
          <div className="field-block">
            <h4>Experience bullet</h4>
            <p>{result.original.experienceBullet || "(none extracted)"}</p>
          </div>
        </div>

        <div className="compare-col rewrite">
          <h3>{angle.angle} rewrite</h3>
          <div className="field-block">
            <h4>Headline</h4>
            <p>{angle.headline}</p>
          </div>
          <div className="field-block">
            <h4>About</h4>
            <p>{angle.about}</p>
          </div>
          <div className="field-block">
            <h4>Experience bullet</h4>
            <p>{angle.experience_bullet}</p>
          </div>
        </div>
      </div>

      <div className="btn-row">
        <span />
        <button className="btn btn-secondary" onClick={handleGenerate}>
          Regenerate
        </button>
      </div>
    </div>
  );
}
