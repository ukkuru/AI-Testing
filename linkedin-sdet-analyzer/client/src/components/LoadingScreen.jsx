import React from "react";

export default function LoadingScreen({ label }) {
  return (
    <div className="card loading-screen">
      <div className="spinner" />
      <div>{label || "Analyzing your profile against the 25-point QA/SDET framework..."}</div>
    </div>
  );
}
