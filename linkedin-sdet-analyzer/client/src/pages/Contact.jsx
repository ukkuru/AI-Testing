import React from "react";
import { MapPin, Phone, Mail, ExternalLink } from "lucide-react";

const MAPS_QUERY = encodeURIComponent("Kakkanad, Kochi, Kerala 682042, India");

export default function Contact() {
  return (
    <div className="contact-page">
      <section className="criteria-hero">
        <h1 className="section-heading">Contact TestMetry</h1>
        <p className="section-lead">
          LinkedIn SDET Analyzer is built by TestMetry, a software testing &amp; test automation community based in
          Kochi, India.
        </p>
      </section>

      <div className="card contact-card">
        <div className="contact-row">
          <span className="contact-icon">
            <MapPin size={18} />
          </span>
          <div>
            <div className="contact-row-label">Address</div>
            <div>Kakkanad, Kochi, Kerala 682042, India</div>
            <a
              className="contact-map-link"
              href={`https://www.google.com/maps/search/?api=1&query=${MAPS_QUERY}`}
              target="_blank"
              rel="noreferrer"
            >
              View area on Google Maps
              <ExternalLink size={12} />
            </a>
          </div>
        </div>

        <div className="contact-row">
          <span className="contact-icon">
            <Phone size={18} />
          </span>
          <div>
            <div className="contact-row-label">Phone</div>
            <a href="tel:+919496216498">+91 94962 16498</a>
            <br />
            <a href="tel:+919895780269">+91 98957 80269</a>
          </div>
        </div>

        <div className="contact-row">
          <span className="contact-icon">
            <Mail size={18} />
          </span>
          <div>
            <div className="contact-row-label">Email</div>
            <a href="mailto:george@testmetry.com">george@testmetry.com</a>
          </div>
        </div>
      </div>
    </div>
  );
}
