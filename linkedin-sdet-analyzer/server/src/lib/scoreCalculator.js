const { FRAMEWORK, TOTAL_POINTS, getScoreBand } = require("./scoringFramework");

/**
 * Recomputes totals from Claude's per-item 0/1 scores rather than trusting
 * any total the model might state, and validates the item ids/shape match
 * the fixed framework so the UI never renders a malformed breakdown.
 */
function computeScoredSections(sectionsInput) {
  const scoredSections = [];
  let totalEarned = 0;

  for (const sectionDef of FRAMEWORK) {
    const sectionResult = sectionsInput[sectionDef.id];
    if (!sectionResult || !Array.isArray(sectionResult.items)) {
      throw new Error(`MALFORMED_SECTION:${sectionDef.id}`);
    }

    const itemsById = new Map(sectionResult.items.map((item) => [item.id, item]));
    const items = sectionDef.items.map((itemDef) => {
      const returned = itemsById.get(itemDef.id);
      if (!returned) {
        throw new Error(`MISSING_ITEM:${sectionDef.id}.${itemDef.id}`);
      }
      const pointsEarned = returned.points_earned === 1 ? 1 : 0;
      return {
        id: itemDef.id,
        label: itemDef.label,
        pointsPossible: 1,
        pointsEarned,
        rationale: String(returned.rationale || "").trim(),
      };
    });

    const sectionEarned = items.reduce((sum, i) => sum + i.pointsEarned, 0);
    totalEarned += sectionEarned;

    scoredSections.push({
      id: sectionDef.id,
      label: sectionDef.label,
      pointsPossible: sectionDef.maxPoints,
      pointsEarned: sectionEarned,
      note: String(sectionResult.section_note || "").trim(),
      items,
    });
  }

  const percentage = Math.round((totalEarned / TOTAL_POINTS) * 100);
  const band = getScoreBand(percentage);

  return {
    sections: scoredSections,
    totalEarned,
    totalPossible: TOTAL_POINTS,
    percentage,
    level: band.level,
    rgb: band.rgb,
    hex: band.hex,
  };
}

module.exports = { computeScoredSections };
