require("dotenv").config();

const config = {
  port: parseInt(process.env.PORT || "8787", 10),
  anthropicApiKey: process.env.ANTHROPIC_API_KEY || "",
  anthropicModel: process.env.ANTHROPIC_MODEL || "claude-sonnet-5",
  clientOrigin: process.env.CLIENT_ORIGIN || "http://localhost:5173",
  maxUploadBytes: parseInt(process.env.MAX_UPLOAD_BYTES || String(8 * 1024 * 1024), 10), // 8MB
  minRecommendedWidth: parseInt(process.env.MIN_RECOMMENDED_WIDTH || "1000", 10),
  rateLimit: {
    windowMs: parseInt(process.env.RATE_LIMIT_WINDOW_MS || String(15 * 60 * 1000), 10), // 15 min
    max: parseInt(process.env.RATE_LIMIT_MAX || "10", 10),
  },
  auth: {
    jwtSecret: process.env.JWT_SECRET || "",
    jwtExpiresIn: process.env.JWT_EXPIRES_IN || "7d",
    cookieName: "lsdet_token",
    cookieSecure: process.env.COOKIE_SECURE === "true",
    cookieMaxAgeMs: 7 * 24 * 60 * 60 * 1000, // 7 days, mirrors jwtExpiresIn
    rateLimit: {
      windowMs: parseInt(process.env.AUTH_RATE_LIMIT_WINDOW_MS || String(15 * 60 * 1000), 10),
      max: parseInt(process.env.AUTH_RATE_LIMIT_MAX || "20", 10),
    },
  },
};

module.exports = config;
