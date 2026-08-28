import React from "react";
import { Routes, Route } from "react-router-dom";
import MarketingLayout from "./layouts/MarketingLayout";
import Home from "./pages/Home";
import Criteria from "./pages/Criteria";
import Contact from "./pages/Contact";
import Login from "./pages/Login";
import Register from "./pages/Register";
import AnalyzerTool from "./pages/AnalyzerTool";
import ProtectedRoute from "./components/ProtectedRoute";

export default function App() {
  return (
    <Routes>
      <Route element={<MarketingLayout />}>
        <Route path="/" element={<Home />} />
        <Route path="/criteria" element={<Criteria />} />
        <Route path="/contact" element={<Contact />} />
        <Route path="/login" element={<Login />} />
        <Route path="/register" element={<Register />} />
      </Route>
      <Route
        path="/app"
        element={
          <ProtectedRoute>
            <AnalyzerTool />
          </ProtectedRoute>
        }
      />
    </Routes>
  );
}
