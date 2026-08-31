const express = require("express");
const requireAuth = require("../middleware/requireAuth");
const { buildRewriteSystemPrompt, buildRewriteToolSchema } = require("../lib/promptBuilder");
const { callWithForcedTool } = require("../lib/claudeClient");

const router = express.Router();

function isNonEmptyString(v) {
  return typeof v === "string" && v.trim().length > 0;
}

router.post("/rewrite", requireAuth, express.json({ limit: "256kb" }), async (req, res) => {
  try {
    const { extractedText, gapAnalysis, experienceBulletToRewrite } = req.body || {};

    if (!extractedText || typeof extractedText !== "object") {
      return res.status(400).json({ error: "MISSING_EXTRACTED_TEXT", message: "extractedText from a prior /api/analyze call is required." });
    }
    if (!isNonEmptyString(extractedText.headline) && !isNonEmptyString(extractedText.about_excerpt)) {
      return res.status(400).json({
        error: "INSUFFICIENT_SOURCE_TEXT",
        message: "Not enough original profile text was extracted to safely generate a rewrite (headline and About were both unreadable).",
      });
    }

    const bulletToRewrite =
      isNonEmptyString(experienceBulletToRewrite) && (extractedText.experience_bullets || []).includes(experienceBulletToRewrite)
        ? experienceBulletToRewrite
        : (extractedText.experience_bullets || [])[0] || "";

    const userText = `Original extracted profile text:
- Headline: ${extractedText.headline || "(not present in PDF export)"}
- About excerpt: ${extractedText.about_excerpt || "(not present in PDF export)"}
- Current role: ${extractedText.current_role_title || "(unknown)"} at ${extractedText.current_company || "(unknown)"}
- Experience bullet to rewrite: ${bulletToRewrite || "(no experience bullet was extracted)"}
- Skills listed: ${(extractedText.skills_listed || []).join(", ") || "(none extracted)"}

Prior gap analysis:
${gapAnalysis ? JSON.stringify(gapAnalysis, null, 2) : "(none provided)"}

Write the 3 rewrite angles now.`;

    const messages = [{ role: "user", content: [{ type: "text", text: userText }] }];

    const { input } = await callWithForcedTool({
      system: buildRewriteSystemPrompt(),
      messages,
      tool: buildRewriteToolSchema(),
      maxTokens: 3000,
    });

    res.json({
      original: {
        headline: extractedText.headline || "",
        about: extractedText.about_excerpt || "",
        experienceBullet: bulletToRewrite,
      },
      rewrites: input.rewrites,
    });
  } catch (err) {
    handleRewriteError(err, res);
  }
});

function handleRewriteError(err, res) {
  if (err.message === "MISSING_API_KEY") {
    console.error("ANTHROPIC_API_KEY is not configured on the server.");
    return res.status(500).json({ error: "SERVER_MISCONFIGURED", message: "Rewrite service is not configured." });
  }
  if (err.message === "NO_TOOL_USE_RESPONSE") {
    return res.status(502).json({ error: "MODEL_RESPONSE_INVALID", message: "The rewrite model did not return a structured result. Please try again." });
  }
  console.error("Rewrite route error:", err);
  return res.status(500).json({ error: "REWRITE_FAILED", message: "Something went wrong while generating rewrites." });
}

module.exports = router;
