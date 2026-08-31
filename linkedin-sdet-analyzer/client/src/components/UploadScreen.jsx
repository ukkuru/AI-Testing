import React, { useRef, useState } from "react";
import { motion } from "framer-motion";
import { UploadCloud, FileText, FileCheck, Info, AlertTriangle, ArrowRight } from "lucide-react";

const ACCEPTED_TYPES = ["application/pdf"];
const MAX_BYTES = 8 * 1024 * 1024;

export default function UploadScreen({ onFileReady, initialFile }) {
  const [file, setFile] = useState(initialFile || null);
  const [dragging, setDragging] = useState(false);
  const [error, setError] = useState("");
  const inputRef = useRef(null);

  function validateAndSet(candidate) {
    setError("");
    if (!candidate) return;
    if (!ACCEPTED_TYPES.includes(candidate.type)) {
      setError("Only a .pdf LinkedIn profile export is supported.");
      return;
    }
    if (candidate.size > MAX_BYTES) {
      setError(`File is too large. Max size is ${Math.round(MAX_BYTES / (1024 * 1024))}MB.`);
      return;
    }
    setFile(candidate);
  }

  function handleDrop(e) {
    e.preventDefault();
    setDragging(false);
    const candidate = e.dataTransfer.files?.[0];
    validateAndSet(candidate);
  }

  return (
    <div className="card">
      <h2>
        <FileText size={19} />
        Step 1 — Upload your LinkedIn PDF export
      </h2>

      <div className="callout">
        <Info size={16} />
        <div>
          <strong>How to export:</strong> on your LinkedIn profile, click the <strong>More</strong> button (below
          your profile photo), then <strong>Save to PDF</strong>. That downloads a PDF with your full profile
          text — headline, About, Experience, Education, Licenses &amp; certifications, Skills, and
          Accomplishments.
        </div>
      </div>

      {error && (
        <div className="error-banner">
          <AlertTriangle size={16} />
          <span>{error}</span>
        </div>
      )}

      <motion.div
        className={`upload-dropzone${dragging ? " dragging" : ""}`}
        onClick={() => inputRef.current?.click()}
        onDragOver={(e) => {
          e.preventDefault();
          setDragging(true);
        }}
        onDragLeave={() => setDragging(false)}
        onDrop={handleDrop}
        whileHover={{ scale: 1.005 }}
        whileTap={{ scale: 0.995 }}
      >
        <input
          ref={inputRef}
          type="file"
          accept="application/pdf,.pdf"
          onChange={(e) => validateAndSet(e.target.files?.[0])}
        />
        {file ? (
          <>
            <div className="upload-icon-circle">
              <FileCheck size={26} />
            </div>
            <div className="file-chip">
              <FileText size={15} />
              {file.name}
            </div>
            <div className="upload-hint">{Math.round(file.size / 1024)} KB — click to replace</div>
          </>
        ) : (
          <>
            <div className="upload-icon-circle">
              <UploadCloud size={26} />
            </div>
            <div className="upload-cta">Click to choose a file or drag it here</div>
            <div className="upload-hint">.pdf only, LinkedIn's own "Save to PDF" export, max 8MB</div>
          </>
        )}
      </motion.div>

      <div className="btn-row">
        <span />
        <button className="btn" disabled={!file} onClick={() => onFileReady(file)}>
          Analyze my profile
          <ArrowRight size={15} />
        </button>
      </div>
    </div>
  );
}
