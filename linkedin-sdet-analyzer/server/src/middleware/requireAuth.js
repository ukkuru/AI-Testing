const config = require("../config");
const { verifyToken } = require("../lib/auth");

function requireAuth(req, res, next) {
  const token = req.cookies?.[config.auth.cookieName];
  if (!token) {
    return res.status(401).json({ error: "NOT_AUTHENTICATED", message: "Sign in to use this feature." });
  }
  try {
    const payload = verifyToken(token);
    req.user = { id: payload.sub, email: payload.email };
    next();
  } catch {
    return res.status(401).json({ error: "SESSION_EXPIRED", message: "Your session has expired. Please sign in again." });
  }
}

module.exports = requireAuth;
