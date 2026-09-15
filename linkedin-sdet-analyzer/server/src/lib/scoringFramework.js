/**
 * Single source of truth for the 15-point QA/SDET LinkedIn scoring framework.
 * Used to (a) build the Claude system prompt, (b) build the tool JSON schema
 * that forces structured output, and (c) label the API response so the
 * frontend never has to duplicate this definition.
 *
 * Every item here must be assessable from the plain text of a standard
 * LinkedIn "Save to PDF" profile export — that export has no banner image,
 * no Featured section, no company logos, no Recommendations, and no
 * verified-badge indicator, so none of those can be scored here.
 */

const FRAMEWORK = [
  {
    id: "headline_positioning",
    label: "Headline & Positioning Layer",
    maxPoints: 3,
    items: [
      { id: "headline_uvp", label: "Headline unique value proposition" },
      { id: "headline_keywords", label: "Headline keywords (SDET/QA/automation terms)" },
      { id: "proof_link_in_text", label: "Portfolio/CTA link mentioned in profile text (GitHub, portfolio site, blog, etc.)" },
    ],
  },
  {
    id: "about_section",
    label: "About Section Layer",
    maxPoints: 6,
    items: [
      { id: "hook_strength", label: "Hook strength in first 3 lines" },
      { id: "personal_intro", label: "Personal introduction present" },
      { id: "unique_value_prop", label: "Unique value proposition stated" },
      { id: "company_positioning", label: "Company/industry positioning" },
      { id: "clear_cta", label: "Clear call-to-action" },
      { id: "top5_skills_in_about", label: "Top 5 skills listed in About text" },
    ],
  },
  {
    id: "experience",
    label: "Experience Layer",
    maxPoints: 1,
    items: [
      { id: "current_role_quality", label: "Current role description quality (positioning + outcomes, not task list)" },
    ],
  },
  {
    id: "credentials",
    label: "Credentials Layer",
    maxPoints: 4,
    items: [
      { id: "education_certifications", label: "Education/certifications listed" },
      { id: "top3_skills_match_seniority", label: "Top 3 pinned skills match current seniority" },
      { id: "fifteen_plus_skills", label: "15+ skills listed" },
      { id: "seo_researched_skills", label: "Skills reflect SEO-researched terms (AI in Testing, SDET, Shift-Left Testing, etc.)" },
    ],
  },
  {
    id: "accomplishments",
    label: "Accomplishments Layer",
    maxPoints: 1,
    items: [
      { id: "two_plus_accomplishments", label: "2+ accomplishments listed (certifications, publications, talks)" },
    ],
  },
];

const TOTAL_POINTS = FRAMEWORK.reduce((sum, section) => sum + section.maxPoints, 0); // 15

const SCORE_BANDS = [
  { min: 0, max: 19, level: "Passive", rgb: "rgb(200, 40, 40)", hex: "#C82828" },
  { min: 20, max: 39, level: "Developing", rgb: "rgb(230, 110, 30)", hex: "#E66E1E" },
  { min: 40, max: 59, level: "Average", rgb: "rgb(230, 180, 30)", hex: "#E6B41E" },
  { min: 60, max: 79, level: "Professional", rgb: "rgb(90, 170, 60)", hex: "#5AAA3C" },
  { min: 80, max: 100, level: "Expert", rgb: "rgb(20, 140, 90)", hex: "#148C5A" },
];

function getScoreBand(percentage) {
  const pct = Math.max(0, Math.min(100, Math.round(percentage)));
  return SCORE_BANDS.find((band) => pct >= band.min && pct <= band.max) || SCORE_BANDS[0];
}

module.exports = { FRAMEWORK, TOTAL_POINTS, SCORE_BANDS, getScoreBand };
