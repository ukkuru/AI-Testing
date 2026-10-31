const express = require("express");
const config = require("../config");
const { isValidEmail, findByEmail, createUser } = require("../lib/userRepo");
const { hashPassword, verifyPassword, signToken, verifyToken, setAuthCookie, clearAuthCookie } = require("../lib/auth");

const router = express.Router();
const MIN_PASSWORD_LENGTH = 8;

router.post("/auth/register", express.json({ limit: "16kb" }), async (req, res) => {
  try {
    const email = String(req.body?.email || "").trim().toLowerCase();
    const password = String(req.body?.password || "");

    if (!isValidEmail(email)) {
      return res.status(400).json({ error: "INVALID_EMAIL", message: "Enter a valid email address." });
    }
    if (password.length < MIN_PASSWORD_LENGTH) {
      return res.status(400).json({ error: "WEAK_PASSWORD", message: `Password must be at least ${MIN_PASSWORD_LENGTH} characters.` });
    }
    if (findByEmail(email)) {
      return res.status(409).json({ error: "EMAIL_TAKEN", message: "An account with this email already exists." });
    }

    const passwordHash = await hashPassword(password);
    const user = createUser(email, passwordHash);
    const token = signToken(user);
    setAuthCookie(res, token);

    res.status(201).json({ user: { id: user.id, email: user.email } });
  } catch (err) {
    handleAuthError(err, res, "REGISTRATION_FAILED", "Could not create your account.");
  }
});

router.post("/auth/login", express.json({ limit: "16kb" }), async (req, res) => {
  try {
    const email = String(req.body?.email || "").trim().toLowerCase();
    const password = String(req.body?.password || "");

    const existing = findByEmail(email);
    if (!existing) {
      return res.status(401).json({ error: "INVALID_CREDENTIALS", message: "Incorrect email or password." });
    }
    const valid = await verifyPassword(password, existing.password_hash);
    if (!valid) {
      return res.status(401).json({ error: "INVALID_CREDENTIALS", message: "Incorrect email or password." });
    }

    const token = signToken(existing);
    setAuthCookie(res, token);
    res.json({ user: { id: existing.id, email: existing.email } });
  } catch (err) {
    handleAuthError(err, res, "LOGIN_FAILED", "Could not sign you in.");
  }
});

router.post("/auth/logout", (req, res) => {
  clearAuthCookie(res);
  res.json({ ok: true });
});

router.get("/auth/me", (req, res) => {
  const token = req.cookies?.[config.auth.cookieName];
  if (!token) {
    return res.json({ user: null });
  }
  try {
    const payload = verifyToken(token);
    res.json({ user: { id: payload.sub, email: payload.email } });
  } catch {
    res.json({ user: null });
  }
});

function handleAuthError(err, res, code, message) {
  if (err.message === "MISSING_JWT_SECRET") {
    console.error("JWT_SECRET is not configured on the server.");
    return res.status(500).json({ error: "SERVER_MISCONFIGURED", message: "Auth service is not configured." });
  }
  console.error(`${code}:`, err);
  return res.status(500).json({ error: code, message });
}

module.exports = router;
