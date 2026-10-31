import React, { createContext, useCallback, useContext, useEffect, useState } from "react";
import { fetchCurrentUser, loginAccount, logoutAccount, registerAccount } from "../api";

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  const refresh = useCallback(async () => {
    try {
      const data = await fetchCurrentUser();
      setUser(data.user);
    } catch {
      setUser(null);
    }
  }, []);

  useEffect(() => {
    refresh().finally(() => setLoading(false));
  }, [refresh]);

  async function register(email, password) {
    const data = await registerAccount(email, password);
    setUser(data.user);
    return data.user;
  }

  async function login(email, password) {
    const data = await loginAccount(email, password);
    setUser(data.user);
    return data.user;
  }

  async function logout() {
    await logoutAccount();
    setUser(null);
  }

  return (
    <AuthContext.Provider value={{ user, loading, register, login, logout, refresh }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const ctx = useContext(AuthContext);
  if (!ctx) {
    throw new Error("useAuth must be used within an AuthProvider");
  }
  return ctx;
}
