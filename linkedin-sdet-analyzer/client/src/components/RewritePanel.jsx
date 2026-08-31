import React, { useState } from "react";
import { AnimatePresence, motion } from "framer-motion";
import { Wand2, Award, TrendingUp, Target, Copy, Check, RefreshCw, AlertTriangle } from "lucide-react";
import { requestRewrite } from "../api";

const ANGLE_ICONS = { Authority: Award, Outcome: TrendingUp, Niche: Target };

function CopyButton({ text }) {
  const [copied, setCopied] = useState(false);

  async function handleCopy() {
    try {
      await navigator.clipboard.writeText(text || "");
      setCopied(true);
      setTimeout(() => setCopied(false), 1500);
    } catch {
      // clipboard access denied — silently ignore, button just won't confirm
    }
  }

  return (
    <button type="button" className={`icon-btn${copied ? " copied" : ""}`} onClick={handleCopy}>
      {copied ? <Check size={12} /> : <Copy size={12} />}
      {copied ? "Copied" : "Copy"}
    </button>
  );
}

export default function RewritePanel({ extractedText, gapAnalysis }) {
  const [status, setStatus] = useState("idle"); // idle | loading | done | error
  const [error, setError] = useState("");
  const [result, setResult] = useState(null);
  const [activeAngle, setActiveAngle] = useState(0);

  async function handleGenerate() {
    setStatus("loading");
    setError("");
    try {
      const data = await requestRewrite({ extractedText, gapAnalysis });
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
        <h2>
          <Wand2 size={19} />
          AI rewrite
        </h2>
        <p className="subtle-text">
          Generate 3 positioning angles (Authority, Outcome, Niche) for your headline, About section, and one
          experience bullet, grounded only in what&apos;s actually on your profile, nothing invented.
        </p>
        {error && (
          <div className="error-banner">
            <AlertTriangle size={16} />
            <span>{error}</span>
          </div>
        )}
        <button className="btn" onClick={handleGenerate}>
          Generate rewrite
        </button>
      </div>
    );
  }

  if (status === "loading") {
    return (
      <div className="card loading-screen">
        <div className="loading-orbit">
          <motion.div
            className="loading-orbit-ring"
            animate={{ rotate: 360 }}
            transition={{ duration: 1, repeat: Infinity, ease: "linear" }}
          />
        </div>
        <div className="loading-title">Generating rewrite angles…</div>
      </div>
    );
  }

  const angle = result.rewrites[activeAngle];

  return (
    <div className="card">
      <h2>
        <Wand2 size={19} />
        AI rewrite
      </h2>
      <div className="angle-tabs">
        {result.rewrites.map((r, idx) => {
          const Icon = ANGLE_ICONS[r.angle] || Wand2;
          return (
            <button
              key={r.angle}
              className={`angle-tab${idx === activeAngle ? " active" : ""}`}
              onClick={() => setActiveAngle(idx)}
            >
              <Icon size={14} />
              {r.angle}
            </button>
          );
        })}
      </div>

      <div className="compare-grid">
        <div className="compare-col original">
          <h3>Original</h3>
          <div className="field-block">
            <h4>Headline</h4>
            <p>{result.original.headline || "(not present in PDF export)"}</p>
          </div>
          <div className="field-block">
            <h4>About</h4>
            <p>{result.original.about || "(not present in PDF export)"}</p>
          </div>
          <div className="field-block">
            <h4>Experience bullet</h4>
            <p>{result.original.experienceBullet || "(none extracted)"}</p>
          </div>
        </div>

        <AnimatePresence mode="wait">
          <motion.div
            key={angle.angle}
            className="compare-col rewrite"
            initial={{ opacity: 0, x: 10 }}
            animate={{ opacity: 1, x: 0 }}
            exit={{ opacity: 0, x: -10 }}
            transition={{ duration: 0.18 }}
          >
            <h3>{angle.angle} rewrite</h3>
            <div className="field-block">
              <div className="field-block-head">
                <h4>Headline</h4>
                <CopyButton text={angle.headline} />
              </div>
              <p>{angle.headline}</p>
            </div>
            <div className="field-block">
              <div className="field-block-head">
                <h4>About</h4>
                <CopyButton text={angle.about} />
              </div>
              <p>{angle.about}</p>
            </div>
            <div className="field-block">
              <div className="field-block-head">
                <h4>Experience bullet</h4>
                <CopyButton text={angle.experience_bullet} />
              </div>
              <p>{angle.experience_bullet}</p>
            </div>
          </motion.div>
        </AnimatePresence>
      </div>

      <div className="btn-row">
        <span />
        <button className="btn btn-secondary" onClick={handleGenerate}>
          <RefreshCw size={14} />
          Regenerate
        </button>
      </div>
    </div>
  );
}
