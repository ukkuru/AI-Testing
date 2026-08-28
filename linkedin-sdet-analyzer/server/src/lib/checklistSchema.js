const POSTING_FREQUENCY_OPTIONS = ["never", "rarely", "2+ times a month"];
const CONTENT_FORMAT_OPTIONS = ["text only", "mixed formats"];
const ENGAGEMENT_OPTIONS = ["rarely comment on others' posts", "regularly comment"];

function validateChecklist(raw) {
  let parsed = raw;
  if (typeof raw === "string") {
    try {
      parsed = JSON.parse(raw);
    } catch {
      return { valid: false, error: "Checklist must be valid JSON." };
    }
  }

  if (!parsed || typeof parsed !== "object") {
    return { valid: false, error: "Checklist is required." };
  }

  const { postingFrequency, contentFormatVariety, engagementHabits, groupMemberships } = parsed;

  if (!POSTING_FREQUENCY_OPTIONS.includes(postingFrequency)) {
    return { valid: false, error: `postingFrequency must be one of: ${POSTING_FREQUENCY_OPTIONS.join(", ")}` };
  }
  if (!CONTENT_FORMAT_OPTIONS.includes(contentFormatVariety)) {
    return { valid: false, error: `contentFormatVariety must be one of: ${CONTENT_FORMAT_OPTIONS.join(", ")}` };
  }
  if (!ENGAGEMENT_OPTIONS.includes(engagementHabits)) {
    return { valid: false, error: `engagementHabits must be one of: ${ENGAGEMENT_OPTIONS.join(", ")}` };
  }
  const groupCount = Number(groupMemberships);
  if (!Number.isFinite(groupCount) || groupCount < 0 || !Number.isInteger(groupCount)) {
    return { valid: false, error: "groupMemberships must be a non-negative integer (0 = none)." };
  }

  return {
    valid: true,
    checklist: {
      postingFrequency,
      contentFormatVariety,
      engagementHabits,
      groupMemberships: groupCount,
    },
  };
}

module.exports = {
  validateChecklist,
  POSTING_FREQUENCY_OPTIONS,
  CONTENT_FORMAT_OPTIONS,
  ENGAGEMENT_OPTIONS,
};
