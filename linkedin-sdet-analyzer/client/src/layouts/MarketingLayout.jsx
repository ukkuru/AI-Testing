import React, { useState } from "react";
import { Link, NavLink, Outlet, useNavigate } from "react-router-dom";
import { Sparkles, Menu, X, LogOut } from "lucide-react";
import ThemeToggle from "../components/ThemeToggle";
import { useAuth } from "../contexts/AuthContext";
import { getInitialTheme, applyTheme } from "../theme";

const NAV_LINKS = [
  { to: "/", label: "Home", end: true },
  { to: "/criteria", label: "Scoring Criteria" },
  { to: "/contact", label: "Contact" },
];

export default function MarketingLayout() {
  const { user, logout } = useAuth();
  const navigate = useNavigate();
  const [theme, setTheme] = useState(getInitialTheme);
  const [menuOpen, setMenuOpen] = useState(false);

  function toggleTheme() {
    setTheme((t) => {
      const next = t === "dark" ? "light" : "dark";
      applyTheme(next);
      return next;
    });
  }

  async function handleSignOut() {
    await logout();
    navigate("/");
  }

  return (
    <div className="marketing-shell">
      <header className="marketing-header">
        <div className="marketing-header-inner">
          <Link to="/" className="marketing-logo" onClick={() => setMenuOpen(false)}>
            <span className="app-logo">
              <Sparkles size={18} />
            </span>
            <span className="marketing-logo-text">LinkedIn SDET Analyzer</span>
          </Link>

          <nav className={`marketing-nav${menuOpen ? " open" : ""}`}>
            {NAV_LINKS.map((link) => (
              <NavLink
                key={link.to}
                to={link.to}
                end={link.end}
                className={({ isActive }) => `marketing-nav-link${isActive ? " active" : ""}`}
                onClick={() => setMenuOpen(false)}
              >
                {link.label}
              </NavLink>
            ))}
          </nav>

          <div className="marketing-header-actions">
            <ThemeToggle theme={theme} onToggle={toggleTheme} />
            {user ? (
              <>
                <Link to="/app" className="btn">
                  Open the Tool
                </Link>
                <button className="icon-btn" onClick={handleSignOut} title="Sign out">
                  <LogOut size={12} />
                  Sign out
                </button>
              </>
            ) : (
              <>
                <Link to="/login" className="btn-ghost">
                  Sign In
                </Link>
                <Link to="/app" className="btn">
                  Try Out the Tool
                </Link>
              </>
            )}
            <button className="marketing-menu-toggle" onClick={() => setMenuOpen((o) => !o)} aria-label="Toggle menu">
              {menuOpen ? <X size={20} /> : <Menu size={20} />}
            </button>
          </div>
        </div>
      </header>

      <main className="marketing-main">
        <Outlet />
      </main>

      <footer className="marketing-footer">
        <div className="marketing-footer-inner">
          <div className="marketing-footer-brand">
            <span className="app-logo">
              <Sparkles size={16} />
            </span>
            <div>
              <div className="marketing-footer-title">LinkedIn SDET Analyzer</div>
              <div className="marketing-footer-tagline">by TestMetry</div>
            </div>
          </div>
          <nav className="marketing-footer-nav">
            {NAV_LINKS.map((link) => (
              <Link key={link.to} to={link.to}>
                {link.label}
              </Link>
            ))}
            <Link to="/login">Sign In</Link>
            <Link to="/register">Register</Link>
          </nav>
          <div className="marketing-footer-copy">
            &copy; {new Date().getFullYear()} TestMetry. Built for QA, test automation &amp; SDET professionals.
          </div>
        </div>
      </footer>
    </div>
  );
}
