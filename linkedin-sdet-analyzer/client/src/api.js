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

export async function analyzeProfile(file, checklist) {
  const formData = new FormData();
  formData.append("screenshot", file);
  formData.append("checklist", JSON.stringify(checklist));

  const response = await fetch(`${API_BASE}/analyze`, {
    method: "POST",
    body: formData,
  });
  return parseJsonOrThrow(response);
}

export async function requestRewrite({ extractedText, gapAnalysis, checklist, experienceBulletToRewrite }) {
  const response = await fetch(`${API_BASE}/rewrite`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ extractedText, gapAnalysis, checklist, experienceBulletToRewrite }),
  });
  return parseJsonOrThrow(response);
}
