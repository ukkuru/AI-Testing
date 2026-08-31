const path = require("path");
const fs = require("fs");
const express = require("express");
const cors = require("cors");
const cookieParser = require("cookie-parser");
const config = require("./config");
const { analysisRateLimiter, authRateLimiter } = require("./middleware/rateLimit");
const analyzeRouter = require("./routes/analyze");
const rewriteRouter = require("./routes/rewrite");
const authRouter = require("./routes/auth");
const frameworkRouter = require("./routes/framework");

const app = express();

app.use(cors({ origin: config.clientOrigin, credentials: true }));
app.use(cookieParser());

app.get("/api/health", (req, res) => {
  res.json({ ok: true });
});

app.use("/api", frameworkRouter);
app.use("/api", authRateLimiter, authRouter);
app.use("/api", analysisRateLimiter, analyzeRouter);
app.use("/api", analysisRateLimiter, rewriteRouter);

// Single-container production mode: if a built client bundle is present
// (see Dockerfile), serve it and fall back to index.html for any non-API
// GET route so react-router client-side routes survive a hard refresh.
// In local dev the two apps run as separate Vite/Express processes and
// this directory never exists, so this block is a no-op there.
const clientDistPath = path.join(__dirname, "..", "public");
if (fs.existsSync(clientDistPath)) {
  app.use(express.static(clientDistPath));
  app.get(/^(?!\/api\/).*/, (req, res) => {
    res.sendFile(path.join(clientDistPath, "index.html"));
  });
}

// eslint-disable-next-line no-unused-vars
app.use((err, req, res, next) => {
  console.error("Unhandled error:", err);
  res.status(500).json({ error: "INTERNAL_ERROR", message: "Unexpected server error." });
});

app.listen(config.port, () => {
  if (!config.anthropicApiKey) {
    console.warn("WARNING: ANTHROPIC_API_KEY is not set. /api/analyze and /api/rewrite will fail until it is configured in server/.env");
  }
  if (!config.auth.jwtSecret) {
    console.warn("WARNING: JWT_SECRET is not set. /api/auth/* will fail until it is configured in server/.env");
  }
  console.log(`LinkedIn SDET Analyzer API listening on port ${config.port}`);
});
