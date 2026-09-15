const { FRAMEWORK, TOTAL_POINTS } = require("./scoringFramework");

function buildAnalysisSystemPrompt() {
  const frameworkText = FRAMEWORK.map((section) => {
    const items = section.items
      .map((item, idx) => `    ${idx + 1}. [${item.id}] ${item.label}`)
      .join("\n");
    return `- ${section.label} (${section.maxPoints} points)\n${items}`;
  }).join("\n\n");

  return `You are a hard-nosed LinkedIn profile auditor who specializes exclusively in QA, test automation, and SDET careers. You have hired and rejected hundreds of QA engineers and SDETs, and you know exactly what makes a hiring manager or technical recruiter stop scrolling on a profile.

You will be given the plain text extracted from a LinkedIn profile's "Save to PDF" export (headline, About, Experience, Education, Licenses & certifications, Skills, and Accomplishments — this export never includes the banner image, Featured section, company logos, Recommendations, or the verified-badge indicator, so never expect those). Your job is to score the profile against the following fixed 15-point framework, built specifically for QA/testing/SDET careers around what a PDF export actually contains.

FRAMEWORK (${TOTAL_POINTS} points total):

${frameworkText}

SCORING RULES:
- Score every single item 1 (present/strong) or 0 (missing/weak). No partial credit, no fractional scores.
- Every item is assessed strictly from the extracted PDF text provided. If the text does not contain enough to judge an item confidently, score it 0 and say exactly what was missing in the rationale — never guess or assume something is present.
- Be a hard grader. A generic headline like "QA Engineer at Company" with no keywords or UVP is 0 for headline UVP and 0 for headline keywords. A wall of task-list bullets ("Wrote test cases", "Executed regression tests") with no outcomes/metrics is 0 for current role quality.
- Every rationale must quote or closely paraphrase the specific text you read (or note precisely what was absent). No generic advice. Frame every note in QA/testing hiring language: SDET, automation coverage, defect leakage, release velocity, ISTQB, Selenium/Cypress/Playwright, shift-left testing, CI/CD, flaky tests, test pyramid, etc. wherever relevant.
- Also transcribe (do not paraphrase) the key existing text into extracted_text, so it can be reused later for rewrites. If a field isn't present in the extracted text, use an empty string — never invent text that isn't there.

DELIVERABLES beyond the raw score:
- section_notes: for each of the 5 sections, a short hard-truth diagnosis paragraph.
- gap_analysis: specific gaps for headline, about, experience, and skills — each gap tied to concrete text that is present or conspicuously missing.
- keyword_strategy: 10-15 real QA/SDET hiring keywords the profile should incorporate, each categorized as one of: "tool" (e.g. Selenium, Cypress, Playwright, Postman, JMeter), "testing_type" (e.g. API testing, performance testing, exploratory testing), "methodology" (e.g. shift-left testing, Agile/Scrum, BDD/TDD, risk-based testing), "certification" (e.g. ISTQB, CSTE), or "role_level" (e.g. SDET, Senior QA Automation Engineer, QA Lead).
- top_priority_fixes: exactly 5 fixes, ranked 1 (highest impact) to 5, each with the fix and why it moves visibility/response rate.

Never invent achievements, metrics, certifications, or recommendations that are not present in the extracted text. If you are uncertain whether something is present, treat it as absent and say so.

You must respond ONLY by calling the submit_analysis tool with the complete structured result. Do not respond with plain text.`;
}

function buildAnalysisToolSchema() {
  const sectionProperties = {};
  const sectionRequired = [];

  FRAMEWORK.forEach((section) => {
    sectionRequired.push(section.id);
    sectionProperties[section.id] = {
      type: "object",
      description: `${section.label} (${section.maxPoints} points max)`,
      properties: {
        items: {
          type: "array",
          items: {
            type: "object",
            properties: {
              id: { type: "string", enum: section.items.map((i) => i.id) },
              label: { type: "string" },
              points_possible: { type: "integer", enum: [1] },
              points_earned: { type: "integer", enum: [0, 1] },
              rationale: {
                type: "string",
                description: "Specific, hard-truth reasoning tied to concrete text that was present or missing in the PDF export.",
              },
            },
            required: ["id", "label", "points_possible", "points_earned", "rationale"],
          },
          minItems: section.items.length,
          maxItems: section.items.length,
        },
        section_note: {
          type: "string",
          description: "Short hard-truth diagnosis paragraph for this section, in QA/SDET hiring language.",
        },
      },
      required: ["items", "section_note"],
    };
  });

  return {
    name: "submit_analysis",
    description: "Submit the complete structured 15-point QA/SDET LinkedIn profile analysis.",
    input_schema: {
      type: "object",
      properties: {
        sections: {
          type: "object",
          properties: sectionProperties,
          required: sectionRequired,
        },
        extracted_text: {
          type: "object",
          description: "Verbatim text transcribed from the PDF export, for reuse in rewrites. Use empty string if not present.",
          properties: {
            headline: { type: "string" },
            about_excerpt: { type: "string" },
            current_role_title: { type: "string" },
            current_company: { type: "string" },
            experience_bullets: {
              type: "array",
              items: { type: "string" },
              description: "Verbatim bullet points from the current/most recent role.",
            },
            skills_listed: {
              type: "array",
              items: { type: "string" },
            },
          },
          required: ["headline", "about_excerpt", "current_role_title", "current_company", "experience_bullets", "skills_listed"],
        },
        gap_analysis: {
          type: "object",
          properties: {
            headline: { type: "string" },
            about: { type: "string" },
            experience: { type: "string" },
            skills: { type: "string" },
          },
          required: ["headline", "about", "experience", "skills"],
        },
        keyword_strategy: {
          type: "array",
          minItems: 10,
          maxItems: 15,
          items: {
            type: "object",
            properties: {
              term: { type: "string" },
              category: {
                type: "string",
                enum: ["tool", "testing_type", "methodology", "certification", "role_level"],
              },
            },
            required: ["term", "category"],
          },
        },
        top_priority_fixes: {
          type: "array",
          minItems: 5,
          maxItems: 5,
          items: {
            type: "object",
            properties: {
              rank: { type: "integer", minimum: 1, maximum: 5 },
              fix: { type: "string" },
              impact: { type: "string", description: "Why this moves visibility/response rate, in hiring language." },
            },
            required: ["rank", "fix", "impact"],
          },
        },
      },
      required: ["sections", "extracted_text", "gap_analysis", "keyword_strategy", "top_priority_fixes"],
    },
  };
}

function buildRewriteSystemPrompt() {
  return `You are a QA/SDET career positioning copywriter. You will be given the extracted current text from a LinkedIn profile (headline, About excerpt, one experience bullet) and the gap analysis from a prior scoring pass.

Write 3 distinct rewrite angles: "Authority" (deep technical expertise / thought-leadership framing), "Outcome" (business impact / metrics-driven framing), and "Niche" (specialized focus, e.g. a specific stack, industry, or testing discipline framing).

For each angle produce:
- headline: a single-line LinkedIn headline using QA/SDET keywords (SDET, automation, Selenium/Cypress/Playwright, shift-left, CI/CD, etc. as appropriate).
- about: a short About section (roughly 4-8 lines) with a strong hook in the first 3 lines.
- experience_bullet: the ONE given experience bullet rewritten from a task-description into an impact statement.

CRITICAL CONSTRAINT: Never invent certifications, metrics, achievements, tools, or employers that are not present in the provided extracted text. If the original bullet has no metric, do not fabricate one — instead sharpen the framing around scope/ownership/outcome language that is honestly supportable (e.g. "reduced regression cycle time" only if that is implied by the original text; otherwise use qualitative impact framing like "cut manual regression effort by building an automated suite covering [what the original text actually says]"). When in doubt, rewrite for clarity and positioning rather than adding unverified specifics.

Respond ONLY by calling the submit_rewrite tool.`;
}

function buildRewriteToolSchema() {
  const angleSchema = {
    type: "object",
    properties: {
      angle: { type: "string", enum: ["Authority", "Outcome", "Niche"] },
      headline: { type: "string" },
      about: { type: "string" },
      experience_bullet: { type: "string" },
    },
    required: ["angle", "headline", "about", "experience_bullet"],
  };

  return {
    name: "submit_rewrite",
    description: "Submit the 3 positioning-angle rewrites.",
    input_schema: {
      type: "object",
      properties: {
        rewrites: {
          type: "array",
          minItems: 3,
          maxItems: 3,
          items: angleSchema,
        },
      },
      required: ["rewrites"],
    },
  };
}

module.exports = {
  buildAnalysisSystemPrompt,
  buildAnalysisToolSchema,
  buildRewriteSystemPrompt,
  buildRewriteToolSchema,
};
