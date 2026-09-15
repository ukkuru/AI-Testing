const express = require("express");
const pdfParse = require("pdf-parse");
const requireAuth = require("../middleware/requireAuth");
const { upload, validatePdfSignature, handleUploadErrors } = require("../middleware/upload");
const { buildAnalysisSystemPrompt, buildAnalysisToolSchema } = require("../lib/promptBuilder");
const { callWithForcedTool } = require("../lib/claudeClient");
const { computeScoredSections } = require("../lib/scoreCalculator");

const router = express.Router();

router.post(
  "/analyze",
  requireAuth,
  upload.single("profilePdf"),
  validatePdfSignature,
  handleUploadErrors,
  async (req, res) => {
    try {
      if (!req.file) {
        return res.status(400).json({ error: "MISSING_FILE", message: "A PDF file is required (field name: profilePdf)." });
      }

      let extractedPdfText;
      try {
        const parsed = await pdfParse(req.file.buffer);
        extractedPdfText = parsed.text.trim();
      } catch {
        return res.status(400).json({ error: "INVALID_PDF_DATA", message: "The uploaded file could not be read as a valid PDF." });
      }

      if (!extractedPdfText) {
        return res.status(400).json({
          error: "EMPTY_PDF_TEXT",
          message: "No readable text was found in this PDF. Make sure it's LinkedIn's own 'Save to PDF' profile export, not a scanned image.",
        });
      }

      const userText = `Below is the text extracted from a LinkedIn profile's "Save to PDF" export. Analyze it against the 15-point QA/SDET framework.

--- EXTRACTED PDF TEXT START ---
${extractedPdfText}
--- EXTRACTED PDF TEXT END ---`;

      const messages = [
        {
          role: "user",
          content: [{ type: "text", text: userText }],
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
  return res.status(500).json({ error: "ANALYSIS_FAILED", message: "Something went wrong while analyzing the profile." });
}

module.exports = router;
