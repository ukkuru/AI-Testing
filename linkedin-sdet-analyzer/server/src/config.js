require("dotenv").config();

// .env values sometimes pick up stray whitespace or a trailing \r (e.g. a
// Windows-originated copy/paste, or CRLF line endings in the file itself).
// A secret with a hidden control character fails silently in confusing ways
// downstream (e.g. "not a legal HTTP header value" from the Anthropic SDK
// instead of a clear auth error), so trim every secret/string env value here.
function env(name, fallback = "") {
  const value = process.env[name];
  return value === undefined ? fallback : value.trim();
}

const config = {
  port: parseInt(process.env.PORT || "8787", 10),
  anthropicApiKey: env("ANTHROPIC_API_KEY"),
  anthropicModel: env("ANTHROPIC_MODEL", "claude-sonnet-5"),
  clientOrigin: env("CLIENT_ORIGIN", "http://localhost:5173"),
  maxUploadBytes: parseInt(process.env.MAX_UPLOAD_BYTES || String(8 * 1024 * 1024), 10), // 8MB
  minRecommendedWidth: parseInt(process.env.MIN_RECOMMENDED_WIDTH || "1000", 10),
  rateLimit: {
    windowMs: parseInt(process.env.RATE_LIMIT_WINDOW_MS || String(15 * 60 * 1000), 10), // 15 min
    max: parseInt(process.env.RATE_LIMIT_MAX || "10", 10),
  },
  auth: {
    jwtSecret: env("JWT_SECRET"),
    jwtExpiresIn: env("JWT_EXPIRES_IN", "7d"),
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
