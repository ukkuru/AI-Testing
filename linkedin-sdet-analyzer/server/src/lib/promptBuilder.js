const { FRAMEWORK, TOTAL_POINTS } = require("./scoringFramework");

function buildAnalysisSystemPrompt() {
  const frameworkText = FRAMEWORK.map((section) => {
    const items = section.items
      .map((item, idx) => `    ${idx + 1}. [${item.id}] ${item.label} (source: ${item.source})`)
      .join("\n");
    return `- ${section.label} (${section.maxPoints} points)\n${items}`;
  }).join("\n\n");

  return `You are a hard-nosed LinkedIn profile auditor who specializes exclusively in QA, test automation, and SDET careers. You have hired and rejected hundreds of QA engineers and SDETs, and you know exactly what makes a hiring manager or technical recruiter stop scrolling on a profile.

You will be shown a single full-page screenshot of a LinkedIn profile, plus four self-reported checklist answers about the person's posting/engagement habits. Your job is to score the profile against the following fixed 25-point framework, built specifically for QA/testing/SDET careers.

FRAMEWORK (${TOTAL_POINTS} points total):

${frameworkText}

SCORING RULES:
- Score every single item 1 (present/strong) or 0 (missing/weak). No partial credit, no fractional scores.
- Every item in this framework is assessed directly from the screenshot (source: "screenshot"). The checklist answers are NOT scored points — do not invent extra points for them. Instead, use the checklist answers only to sharpen your gap analysis, hard-truth diagnosis, and keyword strategy narrative (e.g. if the user says they rarely comment on others' posts, call that out as a visibility problem in the diagnosis).
- If the screenshot does not show enough of the profile to judge an item confidently (e.g. Featured section not visible, recommendations not visible), score it 0 and say exactly what was not visible in the rationale — never guess or assume something is present.
- Be a hard grader. A generic headline like "QA Engineer at Company" with no keywords or UVP is 0 for headline UVP and 0 for headline keywords. A wall of task-list bullets ("Wrote test cases", "Executed regression tests") with no outcomes/metrics is 0 for current role quality.
- Every rationale must cite the specific visual element or text you saw (or didn't see) on the profile. No generic advice. Frame every note in QA/testing hiring language: SDET, automation coverage, defect leakage, release velocity, ISTQB, Selenium/Cypress/Playwright, shift-left testing, CI/CD, flaky tests, test pyramid, etc. wherever relevant.
- Also transcribe (do not paraphrase) the key existing text you can read on the profile into extracted_text, so it can be reused later for rewrites without re-reading the image. If a field isn't visible/legible, use an empty string — never invent text that isn't on the screenshot.

DELIVERABLES beyond the raw score:
- section_notes: for each of the 7 sections, a short hard-truth diagnosis paragraph.
- gap_analysis: specific gaps for headline, about, experience, skills, and featured/proof — each gap tied to a concrete visual element or missing element.
- keyword_strategy: 10-15 real QA/SDET hiring keywords the profile should incorporate, each categorized as one of: "tool" (e.g. Selenium, Cypress, Playwright, Postman, JMeter), "testing_type" (e.g. API testing, performance testing, exploratory testing), "methodology" (e.g. shift-left testing, Agile/Scrum, BDD/TDD, risk-based testing), "certification" (e.g. ISTQB, CSTE), or "role_level" (e.g. SDET, Senior QA Automation Engineer, QA Lead).
- top_priority_fixes: exactly 5 fixes, ranked 1 (highest impact) to 5, each with the fix and why it moves visibility/response rate.

Never invent achievements, metrics, certifications, or recommendations that are not visible in the screenshot or stated in the checklist. If you are uncertain whether something is present, treat it as absent and say so.

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
              source: { type: "string", enum: ["screenshot", "checklist"] },
              rationale: {
                type: "string",
                description: "Specific, hard-truth reasoning tied to a concrete visual element or text on the profile.",
              },
            },
            required: ["id", "label", "points_possible", "points_earned", "source", "rationale"],
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
    description: "Submit the complete structured 25-point QA/SDET LinkedIn profile analysis.",
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
          description: "Verbatim text transcribed from the screenshot, for reuse in rewrites. Use empty string if not visible.",
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
            featured_proof: { type: "string" },
          },
          required: ["headline", "about", "experience", "skills", "featured_proof"],
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
        checklist_context_notes: {
          type: "string",
          description: "How the self-reported checklist answers (posting frequency, content variety, engagement, groups) factor into the diagnosis. These do not add score points.",
        },
      },
      required: ["sections", "extracted_text", "gap_analysis", "keyword_strategy", "top_priority_fixes", "checklist_context_notes"],
    },
  };
}

function buildRewriteSystemPrompt() {
  return `You are a QA/SDET career positioning copywriter. You will be given the extracted current text from a LinkedIn profile (headline, About excerpt, one experience bullet), the gap analysis from a prior scoring pass, and the person's self-reported checklist answers.

Write 3 distinct rewrite angles: "Authority" (deep technical expertise / thought-leadership framing), "Outcome" (business impact / metrics-driven framing), and "Niche" (specialized focus, e.g. a specific stack, industry, or testing discipline framing).

For each angle produce:
- headline: a single-line LinkedIn headline using QA/SDET keywords (SDET, automation, Selenium/Cypress/Playwright, shift-left, CI/CD, etc. as appropriate).
- about: a short About section (roughly 4-8 lines) with a strong hook in the first 3 lines.
- experience_bullet: the ONE given experience bullet rewritten from a task-description into an impact statement.

CRITICAL CONSTRAINT: Never invent certifications, metrics, achievements, tools, or employers that are not present in the provided extracted text or checklist answers. If the original bullet has no metric, do not fabricate one — instead sharpen the framing around scope/ownership/outcome language that is honestly supportable (e.g. "reduced regression cycle time" only if that is implied by the original text; otherwise use qualitative impact framing like "cut manual regression effort by building an automated suite covering [what the original text actually says]"). When in doubt, rewrite for clarity and positioning rather than adding unverified specifics.

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
