import React, { useState } from "react";
import { Link, useLocation, useNavigate } from "react-router-dom";
import { UserPlus, AlertTriangle, ArrowRight, ShieldCheck } from "lucide-react";
import { useAuth } from "../contexts/AuthContext";

const MIN_PASSWORD_LENGTH = 8;

export default function Register() {
  const { register } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();
  const from = location.state?.from || "/app";

  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");
  const [error, setError] = useState("");
  const [submitting, setSubmitting] = useState(false);

  async function handleSubmit(e) {
    e.preventDefault();
    setError("");

    if (password.length < MIN_PASSWORD_LENGTH) {
      setError(`Password must be at least ${MIN_PASSWORD_LENGTH} characters.`);
      return;
    }
    if (password !== confirmPassword) {
      setError("Passwords don't match.");
      return;
    }

    setSubmitting(true);
    try {
      await register(email.trim().toLowerCase(), password);
      navigate(from, { replace: true });
    } catch (err) {
      setError(err.message || "Could not create your account.");
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <div className="auth-page">
      <div className="card auth-card">
        <h2>
          <UserPlus size={19} />
          Create your account
        </h2>
        <p className="subtle-text">Register to run the QA/SDET profile analyzer and save your session.</p>

        {error && (
          <div className="error-banner">
            <AlertTriangle size={16} />
            <span>{error}</span>
          </div>
        )}

        <form onSubmit={handleSubmit}>
          <div className="checklist-item">
            <label className="field-label" htmlFor="register-email">
              Email
            </label>
            <input
              id="register-email"
              type="email"
              required
              autoComplete="email"
              className="number-input auth-input"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
            />
            <div className="auth-field-note">
              <ShieldCheck size={13} />
              Used only for registration and signing you back in — never for marketing, and never shared with
              anyone else.
            </div>
          </div>

          <div className="checklist-item">
            <label className="field-label" htmlFor="register-password">
              Password
            </label>
            <input
              id="register-password"
              type="password"
              required
              autoComplete="new-password"
              minLength={MIN_PASSWORD_LENGTH}
              className="number-input auth-input"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
            />
            <div className="auth-field-note">At least {MIN_PASSWORD_LENGTH} characters.</div>
          </div>

          <div className="checklist-item">
            <label className="field-label" htmlFor="register-confirm-password">
              Confirm password
            </label>
            <input
              id="register-confirm-password"
              type="password"
              required
              autoComplete="new-password"
              className="number-input auth-input"
              value={confirmPassword}
              onChange={(e) => setConfirmPassword(e.target.value)}
            />
          </div>

          <div className="btn-row">
            <Link to="/login" className="btn-ghost">
              Already have an account? Sign in
            </Link>
            <button className="btn" type="submit" disabled={submitting}>
              {submitting ? "Creating account…" : "Create account"}
              <ArrowRight size={15} />
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
