const rateLimit = require("express-rate-limit");
const config = require("../config");

const analysisRateLimiter = rateLimit({
  windowMs: config.rateLimit.windowMs,
  max: config.rateLimit.max,
  standardHeaders: true,
  legacyHeaders: false,
  message: {
    error: "RATE_LIMITED",
    message: "Too many requests. Please wait before trying again.",
  },
});

module.exports = { analysisRateLimiter };
