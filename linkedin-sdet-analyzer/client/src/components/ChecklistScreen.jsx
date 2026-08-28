import React, { useState } from "react";

const POSTING_FREQUENCY_OPTIONS = ["never", "rarely", "2+ times a month"];
const CONTENT_FORMAT_OPTIONS = ["text only", "mixed formats"];
const ENGAGEMENT_OPTIONS = ["rarely comment on others' posts", "regularly comment"];

function RadioGroup({ options, value, onChange }) {
  return (
    <div className="radio-options">
      {options.map((opt) => (
        <label key={opt} className={`radio-option${value === opt ? " selected" : ""}`}>
          <input
            type="radio"
            checked={value === opt}
            onChange={() => onChange(opt)}
            style={{ accentColor: "#0a66c2" }}
          />
          {opt}
        </label>
      ))}
    </div>
  );
}

export default function ChecklistScreen({ initialChecklist, onBack, onSubmit }) {
  const [checklist, setChecklist] = useState(
    initialChecklist || {
      postingFrequency: "",
      contentFormatVariety: "",
      engagementHabits: "",
      groupMemberships: "",
    }
  );

  const isComplete =
    POSTING_FREQUENCY_OPTIONS.includes(checklist.postingFrequency) &&
    CONTENT_FORMAT_OPTIONS.includes(checklist.contentFormatVariety) &&
    ENGAGEMENT_OPTIONS.includes(checklist.engagementHabits) &&
    checklist.groupMemberships !== "" &&
    Number.isInteger(Number(checklist.groupMemberships)) &&
    Number(checklist.groupMemberships) >= 0;

  function update(key, value) {
    setChecklist((prev) => ({ ...prev, [key]: value }));
  }

  return (
    <div className="card">
      <h2>Step 2 — Quick engagement checklist</h2>
      <div className="callout">
        These 4 answers are <strong>self-reported</strong> — they're not visible on the screenshot, so they
        won't add to your 25-point score. We use them to sharpen the diagnosis and keyword strategy.
      </div>

      <div className="checklist-item">
        <label className="field-label">How often do you post on LinkedIn?</label>
        <RadioGroup
          options={POSTING_FREQUENCY_OPTIONS}
          value={checklist.postingFrequency}
          onChange={(v) => update("postingFrequency", v)}
        />
      </div>

      <div className="checklist-item">
        <label className="field-label">What content formats do you use?</label>
        <RadioGroup
          options={CONTENT_FORMAT_OPTIONS}
          value={checklist.contentFormatVariety}
          onChange={(v) => update("contentFormatVariety", v)}
        />
      </div>

      <div className="checklist-item">
        <label className="field-label">How do you engage with others' posts?</label>
        <RadioGroup
          options={ENGAGEMENT_OPTIONS}
          value={checklist.engagementHabits}
          onChange={(v) => update("engagementHabits", v)}
        />
      </div>

      <div className="checklist-item">
        <label className="field-label">How many QA/testing-related LinkedIn groups are you a member of?</label>
        <input
          type="number"
          min="0"
          step="1"
          className="number-input"
          placeholder="0 = none"
          value={checklist.groupMemberships}
          onChange={(e) => update("groupMemberships", e.target.value)}
        />
      </div>

      <div className="btn-row">
        <button className="btn btn-secondary" onClick={onBack}>
          Back
        </button>
        <button className="btn" disabled={!isComplete} onClick={() => onSubmit(checklist)}>
          Analyze my profile
        </button>
      </div>
    </div>
  );
}
