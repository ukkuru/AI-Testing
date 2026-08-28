import React, { useState } from "react";
import { Link, useLocation, useNavigate } from "react-router-dom";
import { LogIn, AlertTriangle, ArrowRight } from "lucide-react";
import { useAuth } from "../contexts/AuthContext";

export default function Login() {
  const { login } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();
  const from = location.state?.from || "/app";

  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const [submitting, setSubmitting] = useState(false);

  async function handleSubmit(e) {
    e.preventDefault();
    setError("");
    setSubmitting(true);
    try {
      await login(email.trim().toLowerCase(), password);
      navigate(from, { replace: true });
    } catch (err) {
      setError(err.message || "Could not sign you in.");
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <div className="auth-page">
      <div className="card auth-card">
        <h2>
          <LogIn size={19} />
          Sign in
        </h2>
        <p className="subtle-text">Sign in to run the analyzer and generate AI rewrites.</p>

        {error && (
          <div className="error-banner">
            <AlertTriangle size={16} />
            <span>{error}</span>
          </div>
        )}

        <form onSubmit={handleSubmit}>
          <div className="checklist-item">
            <label className="field-label" htmlFor="login-email">
              Email
            </label>
            <input
              id="login-email"
              type="email"
              required
              autoComplete="email"
              className="number-input auth-input"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
            />
          </div>
          <div className="checklist-item">
            <label className="field-label" htmlFor="login-password">
              Password
            </label>
            <input
              id="login-password"
              type="password"
              required
              autoComplete="current-password"
              className="number-input auth-input"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
            />
          </div>

          <div className="btn-row">
            <Link to="/register" className="btn-ghost">
              Need an account? Register
            </Link>
            <button className="btn" type="submit" disabled={submitting}>
              {submitting ? "Signing in…" : "Sign in"}
              <ArrowRight size={15} />
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
