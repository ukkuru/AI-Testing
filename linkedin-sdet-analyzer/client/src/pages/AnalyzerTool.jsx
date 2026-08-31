import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { AnimatePresence, motion } from "framer-motion";
import { AlertTriangle, Sparkles, Check, Home, LogOut } from "lucide-react";
import UploadScreen from "../components/UploadScreen";
import LoadingScreen from "../components/LoadingScreen";
import ResultsDashboard from "../components/ResultsDashboard";
import ThemeToggle from "../components/ThemeToggle";
import { analyzeProfile } from "../api";
import { getInitialTheme, applyTheme } from "../theme";
import { useAuth } from "../contexts/AuthContext";

const STEPS = ["Upload", "Results"];

const screenVariants = {
  initial: { opacity: 0, y: 12 },
  animate: { opacity: 1, y: 0 },
  exit: { opacity: 0, y: -8 },
};

export default function AnalyzerTool() {
  const [screen, setScreen] = useState("upload"); // upload | analyzing | results
  const [file, setFile] = useState(null);
  const [analysis, setAnalysis] = useState(null);
  const [error, setError] = useState("");
  const [theme, setTheme] = useState(getInitialTheme);
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  useEffect(() => {
    applyTheme(theme);
  }, [theme]);

  useEffect(() => {
    window.scrollTo({ top: 0, behavior: "smooth" });
  }, [screen]);

  function currentStepIndex() {
    if (screen === "upload") return 0;
    return 1;
  }

  async function handleFileReady(selectedFile) {
    setFile(selectedFile);
    setScreen("analyzing");
    setError("");
    try {
      const result = await analyzeProfile(selectedFile);
      setAnalysis(result);
      setScreen("results");
    } catch (err) {
      if (err.status === 401) {
        navigate("/login", { state: { from: "/app" } });
        return;
      }
      setError(err.message || "Analysis failed. Please try again.");
      setScreen("upload");
    }
  }

  function handleStartOver() {
    setFile(null);
    setAnalysis(null);
    setError("");
    setScreen("upload");
  }

  async function handleSignOut() {
    await logout();
    navigate("/");
  }

  const stepIdx = currentStepIndex();

  return (
    <div className="app-shell">
      <div className="app-header">
        <div className="app-title-row">
          <div className="app-logo">
            <Sparkles size={18} />
          </div>
          <div>
            <h1 className="app-title">LinkedIn SDET Profile Analyzer</h1>
            <p className="app-subtitle">15-point scoring framework for QA, test automation &amp; SDET careers</p>
          </div>
        </div>

        <div className="app-header-actions">
          <div className="step-progress">
            {STEPS.map((step, idx) => (
              <React.Fragment key={step}>
                <div className="step-progress-item">
                  <div className={`step-dot${idx === stepIdx ? " active" : idx < stepIdx ? " done" : ""}`}>
                    {idx < stepIdx ? <Check size={13} /> : idx + 1}
                  </div>
                  <span className={`step-label${idx === stepIdx ? " active" : ""}`}>{step}</span>
                </div>
                {idx < STEPS.length - 1 && (
                  <div className="step-connector">
                    <motion.div
                      className="step-connector-fill"
                      initial={false}
                      animate={{ width: idx < stepIdx ? "100%" : "0%" }}
                      transition={{ duration: 0.3 }}
                    />
                  </div>
                )}
              </React.Fragment>
            ))}
          </div>
          <ThemeToggle theme={theme} onToggle={() => setTheme((t) => (t === "dark" ? "light" : "dark"))} />
        </div>
      </div>

      <div className="tool-account-bar">
        <button className="tool-account-link" onClick={() => navigate("/")}>
          <Home size={13} />
          Home
        </button>
        {user && (
          <>
            <span className="tool-account-email">{user.email}</span>
            <button className="tool-account-link" onClick={handleSignOut}>
              <LogOut size={13} />
              Sign out
            </button>
          </>
        )}
      </div>

      <AnimatePresence mode="wait">
        {screen === "upload" && (
          <motion.div key="upload" variants={screenVariants} initial="initial" animate="animate" exit="exit" transition={{ duration: 0.22 }}>
            {error && (
              <div className="error-banner">
                <AlertTriangle size={16} />
                <span>{error}</span>
              </div>
            )}
            <UploadScreen initialFile={file} onFileReady={handleFileReady} />
          </motion.div>
        )}

        {screen === "analyzing" && (
          <motion.div key="analyzing" variants={screenVariants} initial="initial" animate="animate" exit="exit" transition={{ duration: 0.22 }}>
            <LoadingScreen />
          </motion.div>
        )}

        {screen === "results" && analysis && (
          <motion.div key="results" variants={screenVariants} initial="initial" animate="animate" exit="exit" transition={{ duration: 0.22 }}>
            <ResultsDashboard analysis={analysis} onStartOver={handleStartOver} />
          </motion.div>
        )}
      </AnimatePresence>

      <div className="footer-note">
        PDF exports are processed in-memory only and are never stored beyond your session.
      </div>
    </div>
  );
}
