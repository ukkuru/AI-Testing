const db = require("../db");

const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

function isValidEmail(email) {
  return typeof email === "string" && email.length <= 254 && EMAIL_REGEX.test(email);
}

function findByEmail(email) {
  return db.prepare("SELECT id, email, password_hash, created_at FROM users WHERE email = ?").get(email);
}

function createUser(email, passwordHash) {
  const info = db
    .prepare("INSERT INTO users (email, password_hash) VALUES (?, ?)")
    .run(email, passwordHash);
  return { id: info.lastInsertRowid, email };
}

module.exports = { isValidEmail, findByEmail, createUser };
