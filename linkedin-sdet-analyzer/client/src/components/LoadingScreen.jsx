import React, { useEffect, useState } from "react";
import { motion } from "framer-motion";
import { ScanEye, ListTree, Tags, FileCheck2, Check } from "lucide-react";

const STEPS = [
  { label: "Reading your profile screenshot", icon: ScanEye, duration: 2600 },
  { label: "Scoring against the 25-point framework", icon: ListTree, duration: 3200 },
  { label: "Building your keyword strategy", icon: Tags, duration: 2600 },
  { label: "Finalizing your report", icon: FileCheck2, duration: 999999 },
];

export default function LoadingScreen() {
  const [activeStep, setActiveStep] = useState(0);

  useEffect(() => {
    if (activeStep >= STEPS.length - 1) return;
    const timer = setTimeout(() => setActiveStep((s) => s + 1), STEPS[activeStep].duration);
    return () => clearTimeout(timer);
  }, [activeStep]);

  const progressPct = Math.min(96, ((activeStep + 0.5) / STEPS.length) * 100);

  return (
    <div className="card loading-screen">
      <div className="loading-orbit">
        <motion.div
          className="loading-orbit-ring"
          animate={{ rotate: 360 }}
          transition={{ duration: 1, repeat: Infinity, ease: "linear" }}
        />
      </div>
      <div className="loading-title">Analyzing your profile against the QA/SDET framework…</div>
      <div className="subtle-text">This usually takes 10–20 seconds.</div>

      <div className="loading-steps">
        {STEPS.map((step, idx) => {
          const Icon = step.icon;
          const isComplete = idx < activeStep;
          const isCurrent = idx === activeStep;
          return (
            <motion.div
              key={step.label}
              className={`loading-step${isCurrent ? " current" : ""}${isComplete ? " complete" : ""}`}
              initial={{ opacity: 0, x: -6 }}
              animate={{ opacity: isCurrent || isComplete ? 1 : 0.5, x: 0 }}
              transition={{ duration: 0.25 }}
            >
              <span className="loading-step-icon">
                {isComplete ? <Check size={15} /> : <Icon size={15} />}
              </span>
              {step.label}
            </motion.div>
          );
        })}
      </div>

      <div className="loading-progress-track">
        <motion.div
          className="loading-progress-fill"
          initial={{ width: "4%" }}
          animate={{ width: `${progressPct}%` }}
          transition={{ duration: 0.6, ease: "easeOut" }}
        />
      </div>
    </div>
  );
}
