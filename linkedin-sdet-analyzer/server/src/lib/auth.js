const bcrypt = require("bcryptjs");
const jwt = require("jsonwebtoken");
const config = require("../config");

const SALT_ROUNDS = 12;

async function hashPassword(plainPassword) {
  return bcrypt.hash(plainPassword, SALT_ROUNDS);
}

async function verifyPassword(plainPassword, hash) {
  return bcrypt.compare(plainPassword, hash);
}

function signToken(user) {
  if (!config.auth.jwtSecret) {
    throw new Error("MISSING_JWT_SECRET");
  }
  return jwt.sign({ sub: user.id, email: user.email }, config.auth.jwtSecret, {
    expiresIn: config.auth.jwtExpiresIn,
  });
}

function verifyToken(token) {
  if (!config.auth.jwtSecret) {
    throw new Error("MISSING_JWT_SECRET");
  }
  return jwt.verify(token, config.auth.jwtSecret);
}

function setAuthCookie(res, token) {
  res.cookie(config.auth.cookieName, token, {
    httpOnly: true,
    secure: config.auth.cookieSecure,
    sameSite: "lax",
    maxAge: config.auth.cookieMaxAgeMs,
    path: "/",
  });
}

function clearAuthCookie(res) {
  res.clearCookie(config.auth.cookieName, { path: "/" });
}

module.exports = { hashPassword, verifyPassword, signToken, verifyToken, setAuthCookie, clearAuthCookie };
