const API_BASE = import.meta.env.VITE_API_BASE_URL || "/api";

async function parseJsonOrThrow(response) {
  let body = null;
  try {
    body = await response.json();
  } catch {
    // ignore, handled below
  }
  if (!response.ok) {
    const message = body?.message || `Request failed with status ${response.status}`;
    const error = new Error(message);
    error.code = body?.error || "UNKNOWN_ERROR";
    error.status = response.status;
    throw error;
  }
  return body;
}

export async function analyzeProfile(file) {
  const formData = new FormData();
  formData.append("profilePdf", file);

  const response = await fetch(`${API_BASE}/analyze`, {
    method: "POST",
    credentials: "include",
    body: formData,
  });
  return parseJsonOrThrow(response);
}

export async function requestRewrite({ extractedText, gapAnalysis, experienceBulletToRewrite }) {
  const response = await fetch(`${API_BASE}/rewrite`, {
    method: "POST",
    credentials: "include",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ extractedText, gapAnalysis, experienceBulletToRewrite }),
  });
  return parseJsonOrThrow(response);
}

export async function fetchFramework() {
  const response = await fetch(`${API_BASE}/framework`);
  return parseJsonOrThrow(response);
}

export async function registerAccount(email, password) {
  const response = await fetch(`${API_BASE}/auth/register`, {
    method: "POST",
    credentials: "include",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ email, password }),
  });
  return parseJsonOrThrow(response);
}

export async function loginAccount(email, password) {
  const response = await fetch(`${API_BASE}/auth/login`, {
    method: "POST",
    credentials: "include",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ email, password }),
  });
  return parseJsonOrThrow(response);
}

export async function logoutAccount() {
  const response = await fetch(`${API_BASE}/auth/logout`, {
    method: "POST",
    credentials: "include",
  });
  return parseJsonOrThrow(response);
}

export async function fetchCurrentUser() {
  const response = await fetch(`${API_BASE}/auth/me`, {
    credentials: "include",
  });
  return parseJsonOrThrow(response);
}
