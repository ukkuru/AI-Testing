/**
 * Single source of truth for the 25-point QA/SDET LinkedIn scoring framework.
 * Used to (a) build the Claude system prompt, (b) build the tool JSON schema
 * that forces structured output, and (c) label the API response so the
 * frontend never has to duplicate this definition.
 */

const FRAMEWORK = [
  {
    id: "first_impression",
    label: "First Impression Layer",
    maxPoints: 5,
    items: [
      { id: "photo_quality", label: "Profile photo quality/framing", source: "screenshot" },
      { id: "banner_messaging", label: "Banner presence and QA-specialization messaging", source: "screenshot" },
      { id: "headline_uvp", label: "Headline unique value proposition", source: "screenshot" },
      { id: "headline_keywords", label: "Headline keywords (SDET/QA/automation terms)", source: "screenshot" },
      { id: "custom_cta_link", label: "Custom CTA link in featured/contact info", source: "screenshot" },
    ],
  },
  {
    id: "content_activity",
    label: "Content & Activity Layer",
    maxPoints: 2,
    items: [
      { id: "featured_min_two", label: "Featured section has 2+ items pinned", source: "screenshot" },
      { id: "featured_low_commitment_offer", label: "Featured section includes a low-commitment offer (template, checklist, guide)", source: "screenshot" },
    ],
  },
  {
    id: "about_section",
    label: "About Section Layer",
    maxPoints: 6,
    items: [
      { id: "hook_strength", label: "Hook strength in first 3 lines", source: "screenshot" },
      { id: "personal_intro", label: "Personal introduction present", source: "screenshot" },
      { id: "unique_value_prop", label: "Unique value proposition stated", source: "screenshot" },
      { id: "company_positioning", label: "Company/industry positioning", source: "screenshot" },
      { id: "clear_cta", label: "Clear call-to-action", source: "screenshot" },
      { id: "top5_skills_in_about", label: "Top 5 skills listed in About text", source: "screenshot" },
    ],
  },
  {
    id: "experience",
    label: "Experience Layer",
    maxPoints: 3,
    items: [
      { id: "company_logo_visible", label: "Company logo visible", source: "screenshot" },
      { id: "current_role_quality", label: "Current role description quality (positioning + outcomes, not task list)", source: "screenshot" },
      { id: "media_attached", label: "Media attached to current role", source: "screenshot" },
    ],
  },
  {
    id: "credentials",
    label: "Credentials Layer",
    maxPoints: 4,
    items: [
      { id: "education_certifications", label: "Education/certifications listed", source: "screenshot" },
      { id: "top3_skills_match_seniority", label: "Top 3 pinned skills match current seniority", source: "screenshot" },
      { id: "fifteen_plus_skills", label: "15+ skills listed", source: "screenshot" },
      { id: "seo_researched_skills", label: "Skills reflect SEO-researched terms (AI in Testing, SDET, Shift-Left Testing, etc.)", source: "screenshot" },
    ],
  },
  {
    id: "recommendations",
    label: "Recommendations Layer",
    maxPoints: 3,
    items: [
      { id: "three_plus_recommendations", label: "3+ recommendations received", source: "screenshot" },
      { id: "client_stakeholder_recs", label: "Client/stakeholder recommendations present", source: "screenshot" },
      { id: "recent_recommendation", label: "Most recent recommendation under 1 year old", source: "screenshot" },
    ],
  },
  {
    id: "accomplishments_network",
    label: "Accomplishments & Network Layer",
    maxPoints: 2,
    items: [
      { id: "two_plus_accomplishments", label: "2+ accomplishments listed (certifications, publications, talks)", source: "screenshot" },
      { id: "verified_badge", label: "Verified profile badge visible", source: "screenshot" },
    ],
  },
];

const TOTAL_POINTS = FRAMEWORK.reduce((sum, section) => sum + section.maxPoints, 0); // 25

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
