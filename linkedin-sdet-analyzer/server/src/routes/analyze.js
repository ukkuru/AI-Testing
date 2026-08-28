const express = require("express");
const { upload, checkImageResolution, handleUploadErrors } = require("../middleware/upload");
const { validateChecklist } = require("../lib/checklistSchema");
const { buildAnalysisSystemPrompt, buildAnalysisToolSchema } = require("../lib/promptBuilder");
const { callWithForcedTool, imageBlockFromBuffer } = require("../lib/claudeClient");
const { computeScoredSections } = require("../lib/scoreCalculator");

const router = express.Router();

router.post(
  "/analyze",
  upload.single("screenshot"),
  checkImageResolution,
  handleUploadErrors,
  async (req, res) => {
    try {
      if (!req.file) {
        return res.status(400).json({ error: "MISSING_FILE", message: "A screenshot file is required (field name: screenshot)." });
      }

      const checklistResult = validateChecklist(req.body.checklist);
      if (!checklistResult.valid) {
        return res.status(400).json({ error: "INVALID_CHECKLIST", message: checklistResult.error });
      }
      const { checklist } = checklistResult;

      const userText = `Checklist answers (self-reported, not scored, use as context only):
- Posting frequency: ${checklist.postingFrequency}
- Content format variety: ${checklist.contentFormatVariety}
- Engagement habits: ${checklist.engagementHabits}
- Group memberships: ${checklist.groupMemberships}

Analyze the attached full-page LinkedIn profile screenshot against the 25-point QA/SDET framework.`;

      const messages = [
        {
          role: "user",
          content: [
            imageBlockFromBuffer(req.file.buffer, req.file.mimetype),
            { type: "text", text: userText },
          ],
        },
      ];

      const { input } = await callWithForcedTool({
        system: buildAnalysisSystemPrompt(),
        messages,
        tool: buildAnalysisToolSchema(),
        maxTokens: 8000,
      });

      const scored = computeScoredSections(input.sections);

      res.json({
        score: {
          totalEarned: scored.totalEarned,
          totalPossible: scored.totalPossible,
          percentage: scored.percentage,
          level: scored.level,
          rgb: scored.rgb,
          hex: scored.hex,
        },
        sections: scored.sections,
        extractedText: input.extracted_text,
        gapAnalysis: input.gap_analysis,
        keywordStrategy: input.keyword_strategy,
        topPriorityFixes: input.top_priority_fixes,
        checklistContextNotes: input.checklist_context_notes,
        checklist,
        lowResolutionWarning: req.lowResolutionWarning || null,
      });
    } catch (err) {
      handleAnalysisError(err, res);
    }
  }
);

function handleAnalysisError(err, res) {
  if (err.message === "MISSING_API_KEY") {
    console.error("ANTHROPIC_API_KEY is not configured on the server.");
    return res.status(500).json({ error: "SERVER_MISCONFIGURED", message: "Analysis service is not configured." });
  }
  if (err.message === "NO_TOOL_USE_RESPONSE") {
    return res.status(502).json({ error: "MODEL_RESPONSE_INVALID", message: "The analysis model did not return a structured result. Please try again." });
  }
  if (typeof err.message === "string" && (err.message.startsWith("MALFORMED_SECTION") || err.message.startsWith("MISSING_ITEM"))) {
    console.error("Model returned malformed sections:", err.message);
    return res.status(502).json({ error: "MODEL_RESPONSE_INVALID", message: "The analysis result was incomplete. Please try again." });
  }
  console.error("Analyze route error:", err);
  return res.status(500).json({ error: "ANALYSIS_FAILED", message: "Something went wrong while analyzing the screenshot." });
}

module.exports = router;
