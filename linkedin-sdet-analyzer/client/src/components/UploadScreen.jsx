import React, { useRef, useState } from "react";
import { motion } from "framer-motion";
import { UploadCloud, ImageUp, FileImage, Info, AlertTriangle, ArrowRight } from "lucide-react";

const ACCEPTED_TYPES = ["image/png", "image/jpeg"];
const MAX_BYTES = 8 * 1024 * 1024;

export default function UploadScreen({ onFileReady, initialFile }) {
  const [file, setFile] = useState(initialFile || null);
  const [previewUrl, setPreviewUrl] = useState(initialFile ? URL.createObjectURL(initialFile) : null);
  const [dragging, setDragging] = useState(false);
  const [error, setError] = useState("");
  const inputRef = useRef(null);

  function validateAndSet(candidate) {
    setError("");
    if (!candidate) return;
    if (!ACCEPTED_TYPES.includes(candidate.type)) {
      setError("Only .png and .jpg/.jpeg screenshots are supported (no PDFs).");
      return;
    }
    if (candidate.size > MAX_BYTES) {
      setError(`File is too large. Max size is ${Math.round(MAX_BYTES / (1024 * 1024))}MB.`);
      return;
    }
    setFile(candidate);
    setPreviewUrl(URL.createObjectURL(candidate));
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
        <ImageUp size={19} />
        Step 1 — Upload your LinkedIn profile screenshot
      </h2>

      <div className="callout">
        <Info size={16} />
        <div>
          <strong>Before capturing:</strong> click &ldquo;Show all skills&rdquo; and &ldquo;Show all
          recommendations&rdquo; if those buttons appear on your profile, so the screenshot captures the full
          list.
          <div className="tool-recs">
            <span className="tool-chip">GoFullPage</span>
            <span className="tool-chip">Fireshot</span>
            <span className="tool-chip">Browser&apos;s native full-page screenshot</span>
          </div>
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
          accept="image/png,image/jpeg"
          onChange={(e) => validateAndSet(e.target.files?.[0])}
        />
        {file ? (
          <>
            <div className="file-chip">
              <FileImage size={15} />
              {file.name}
            </div>
            <div className="upload-hint">{Math.round(file.size / 1024)} KB — click to replace</div>
            <div className="preview-thumb-wrap">
              <img src={previewUrl} alt="Screenshot preview" className="preview-thumb" />
            </div>
          </>
        ) : (
          <>
            <div className="upload-icon-circle">
              <UploadCloud size={26} />
            </div>
            <div className="upload-cta">Click to choose a file or drag it here</div>
            <div className="upload-hint">.png or .jpg only, single full-page screenshot, max 8MB</div>
          </>
        )}
      </motion.div>

      <div className="btn-row">
        <span />
        <button className="btn" disabled={!file} onClick={() => onFileReady(file)}>
          Continue to checklist
          <ArrowRight size={15} />
        </button>
      </div>
    </div>
  );
}
