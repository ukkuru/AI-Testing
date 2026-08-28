import React, { useState } from "react";
import UploadScreen from "./components/UploadScreen";
import ChecklistScreen from "./components/ChecklistScreen";
import LoadingScreen from "./components/LoadingScreen";
import ResultsDashboard from "./components/ResultsDashboard";
import { analyzeProfile } from "./api";

const STEPS = ["Upload", "Checklist", "Results"];

export default function App() {
  const [screen, setScreen] = useState("upload"); // upload | checklist | analyzing | results
  const [file, setFile] = useState(null);
  const [checklist, setChecklist] = useState(null);
  const [analysis, setAnalysis] = useState(null);
  const [error, setError] = useState("");

  function currentStepIndex() {
    if (screen === "upload") return 0;
    if (screen === "checklist") return 1;
    return 2;
  }

  async function handleChecklistSubmit(submittedChecklist) {
    setChecklist(submittedChecklist);
    setScreen("analyzing");
    setError("");
    try {
      const result = await analyzeProfile(file, submittedChecklist);
      setAnalysis(result);
      setScreen("results");
    } catch (err) {
      setError(err.message || "Analysis failed. Please try again.");
      setScreen("checklist");
    }
  }

  function handleStartOver() {
    setFile(null);
    setChecklist(null);
    setAnalysis(null);
    setError("");
    setScreen("upload");
  }

  return (
    <div className="app-shell">
      <div className="app-header">
        <div>
          <h1 className="app-title">LinkedIn SDET Profile Analyzer</h1>
          <p className="app-subtitle">25-point scoring framework for QA, test automation &amp; SDET careers</p>
        </div>
        <div className="step-indicator">
          {STEPS.map((step, idx) => (
            <span key={step} className={`step${idx === currentStepIndex() ? " active" : ""}`}>
              {idx + 1}. {step}
            </span>
          ))}
        </div>
      </div>

      {error && screen === "checklist" && <div className="error-banner">{error}</div>}

      {screen === "upload" && (
        <UploadScreen
          initialFile={file}
          onFileReady={(f) => {
            setFile(f);
            setScreen("checklist");
          }}
        />
      )}

      {screen === "checklist" && (
        <ChecklistScreen
          initialChecklist={checklist}
          onBack={() => setScreen("upload")}
          onSubmit={handleChecklistSubmit}
        />
      )}

      {screen === "analyzing" && <LoadingScreen />}

      {screen === "results" && analysis && (
        <ResultsDashboard analysis={analysis} onStartOver={handleStartOver} />
      )}

      <div className="footer-note">
        Screenshots are processed in-memory only and are never stored beyond your session.
      </div>
    </div>
  );
}
