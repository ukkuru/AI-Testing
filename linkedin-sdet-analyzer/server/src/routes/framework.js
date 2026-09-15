const express = require("express");
const { FRAMEWORK, TOTAL_POINTS, SCORE_BANDS } = require("../lib/scoringFramework");

const router = express.Router();

router.get("/framework", (req, res) => {
  res.json({ sections: FRAMEWORK, totalPoints: TOTAL_POINTS, scoreBands: SCORE_BANDS });
});

module.exports = router;
