import React, { useState } from "react";
import { motion } from "framer-motion";
import {
  ListChecks,
  Info,
  Check,
  CalendarX,
  CalendarClock,
  CalendarCheck2,
  Type,
  Layers,
  MessageSquareOff,
  MessageSquareText,
  Users,
  ArrowLeft,
  ArrowRight,
} from "lucide-react";

const POSTING_FREQUENCY_OPTIONS = [
  { value: "never", icon: CalendarX },
  { value: "rarely", icon: CalendarClock },
  { value: "2+ times a month", icon: CalendarCheck2 },
];
const CONTENT_FORMAT_OPTIONS = [
  { value: "text only", icon: Type },
  { value: "mixed formats", icon: Layers },
];
const ENGAGEMENT_OPTIONS = [
  { value: "rarely comment on others' posts", icon: MessageSquareOff },
  { value: "regularly comment", icon: MessageSquareText },
];

function RadioGroup({ name, options, value, onChange }) {
  return (
    <div className="radio-options">
      {options.map((opt, idx) => {
        const Icon = opt.icon;
        const selected = value === opt.value;
        return (
          <motion.label
            key={opt.value}
            className={`radio-pill${selected ? " selected" : ""}`}
            initial={{ opacity: 0, x: -8 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.2, delay: idx * 0.04 }}
          >
            <input type="radio" name={name} checked={selected} onChange={() => onChange(opt.value)} />
            <span className="radio-pill-icon">
              <Icon size={15} />
            </span>
            {opt.value}
            <span className="radio-pill-check">
              <Check size={16} />
            </span>
          </motion.label>
        );
      })}
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
    POSTING_FREQUENCY_OPTIONS.some((o) => o.value === checklist.postingFrequency) &&
    CONTENT_FORMAT_OPTIONS.some((o) => o.value === checklist.contentFormatVariety) &&
    ENGAGEMENT_OPTIONS.some((o) => o.value === checklist.engagementHabits) &&
    checklist.groupMemberships !== "" &&
    Number.isInteger(Number(checklist.groupMemberships)) &&
    Number(checklist.groupMemberships) >= 0;

  function update(key, value) {
    setChecklist((prev) => ({ ...prev, [key]: value }));
  }

  return (
    <div className="card">
      <h2>
        <ListChecks size={19} />
        Step 2 — Quick engagement checklist
      </h2>
      <div className="callout">
        <Info size={16} />
        <div>
          These 4 answers are <strong>self-reported</strong> — they&apos;re not visible on the screenshot, so
          they won&apos;t add to your 25-point score. We use them to sharpen the diagnosis and keyword
          strategy.
        </div>
      </div>

      <div className="checklist-item">
        <label className="field-label">How often do you post on LinkedIn?</label>
        <RadioGroup
          name="postingFrequency"
          options={POSTING_FREQUENCY_OPTIONS}
          value={checklist.postingFrequency}
          onChange={(v) => update("postingFrequency", v)}
        />
      </div>

      <div className="checklist-item">
        <label className="field-label">What content formats do you use?</label>
        <RadioGroup
          name="contentFormatVariety"
          options={CONTENT_FORMAT_OPTIONS}
          value={checklist.contentFormatVariety}
          onChange={(v) => update("contentFormatVariety", v)}
        />
      </div>

      <div className="checklist-item">
        <label className="field-label">How do you engage with others&apos; posts?</label>
        <RadioGroup
          name="engagementHabits"
          options={ENGAGEMENT_OPTIONS}
          value={checklist.engagementHabits}
          onChange={(v) => update("engagementHabits", v)}
        />
      </div>

      <div className="checklist-item">
        <label className="field-label">
          <Users size={13} style={{ verticalAlign: -2, marginRight: 5 }} />
          How many QA/testing-related LinkedIn groups are you a member of?
        </label>
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
          <ArrowLeft size={15} />
          Back
        </button>
        <button className="btn" disabled={!isComplete} onClick={() => onSubmit(checklist)}>
          Analyze my profile
          <ArrowRight size={15} />
        </button>
      </div>
    </div>
  );
}
