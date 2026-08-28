const express = require("express");
const cors = require("cors");
const config = require("./config");
const { analysisRateLimiter } = require("./middleware/rateLimit");
const analyzeRouter = require("./routes/analyze");
const rewriteRouter = require("./routes/rewrite");

const app = express();

app.use(cors({ origin: config.clientOrigin }));

app.get("/api/health", (req, res) => {
  res.json({ ok: true });
});

app.use("/api", analysisRateLimiter, analyzeRouter);
app.use("/api", analysisRateLimiter, rewriteRouter);

// eslint-disable-next-line no-unused-vars
app.use((err, req, res, next) => {
  console.error("Unhandled error:", err);
  res.status(500).json({ error: "INTERNAL_ERROR", message: "Unexpected server error." });
});

app.listen(config.port, () => {
  if (!config.anthropicApiKey) {
    console.warn("WARNING: ANTHROPIC_API_KEY is not set. /api/analyze and /api/rewrite will fail until it is configured in server/.env");
  }
  console.log(`LinkedIn SDET Analyzer API listening on port ${config.port}`);
});
