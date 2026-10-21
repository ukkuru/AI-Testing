// Cognior homepage behavior: nav, dropdowns, video modal, dotted grid background.
(() => {
  const nav = document.querySelector(".nav");
  const menu = document.getElementById("nav-menu");
  const toggle = document.querySelector(".nav__toggle");
  const triggers = [...document.querySelectorAll(".nav__trigger")];

  // Solid nav once the page scrolls
  const onScroll = () => nav.classList.toggle("is-scrolled", window.scrollY > 24);
  onScroll();
  window.addEventListener("scroll", onScroll, { passive: true });

  // Mobile menu
  toggle.addEventListener("click", () => {
    const open = toggle.getAttribute("aria-expanded") !== "true";
    toggle.setAttribute("aria-expanded", String(open));
    toggle.setAttribute("aria-label", open ? "Close menu" : "Open menu");
    menu.classList.toggle("is-open", open);
  });

  // Dropdowns: one open at a time, close on outside click or Escape
  const closeAll = (except) => triggers.forEach((t) => t !== except && t.setAttribute("aria-expanded", "false"));
  triggers.forEach((t) => {
    t.addEventListener("click", (e) => {
      e.stopPropagation();
      const open = t.getAttribute("aria-expanded") !== "true";
      closeAll(t);
      t.setAttribute("aria-expanded", String(open));
    });
  });
  document.addEventListener("click", (e) => { if (!menu.contains(e.target)) closeAll(); });
  document.addEventListener("keydown", (e) => { if (e.key === "Escape") closeAll(); });
  menu.addEventListener("click", (e) => {
    if (e.target.closest("a")) {
      closeAll();
      toggle.setAttribute("aria-expanded", "false");
      menu.classList.remove("is-open");
    }
  });

  // Video modal: the file loads only on first click, so the page never reflows
  const modal = document.getElementById("video-modal");
  const video = modal.querySelector("video");
  document.querySelector("[data-video-open]").addEventListener("click", () => {
    if (!video.src) video.src = video.dataset.src;
    modal.showModal();
    video.play().catch(() => {});
  });
  const closeModal = () => { video.pause(); modal.close(); };
  modal.querySelector("[data-video-close]").addEventListener("click", closeModal);
  modal.addEventListener("click", (e) => { if (e.target === modal) closeModal(); });
  modal.addEventListener("cancel", () => video.pause());

  // Dotted grid background, shared by the hero and the close section
  const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  const colors = ["56,189,248", "37,99,235", "244,41,139", "255,115,102"];

  document.querySelectorAll(".grid-bg").forEach((canvas) => {
    const ctx = canvas.getContext("2d");
    let dots = [];
    let w = 0;
    let h = 0;
    let running = false;
    let frame = 0;

    const build = () => {
      const dpr = Math.min(window.devicePixelRatio || 1, 2);
      w = canvas.clientWidth;
      h = canvas.clientHeight;
      canvas.width = w * dpr;
      canvas.height = h * dpr;
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
      const step = w < 640 ? 18 : 14;
      dots = [];
      for (let y = step / 2; y < h; y += step) {
        for (let x = step / 2; x < w; x += step) {
          if (Math.random() > 0.62) continue;
          const accent = Math.random() < 0.06;
          dots.push({
            x, y,
            c: accent ? colors[2 + (Math.random() < 0.5 ? 0 : 1)] : colors[Math.random() < 0.8 ? 0 : 1],
            a: 0.3 + Math.random() * 0.65,
            p: Math.random() * Math.PI * 2,
            s: 1.4 + Math.random() * 1.6,
          });
        }
      }
    };

    const draw = (t) => {
      ctx.clearRect(0, 0, w, h);
      for (const d of dots) {
        const a = reduceMotion ? d.a : d.a * (0.55 + 0.45 * Math.sin(t / 1400 + d.p));
        ctx.fillStyle = `rgba(${d.c},${a.toFixed(3)})`;
        ctx.fillRect(d.x, d.y, d.s, d.s);
      }
    };

    const loop = (t) => {
      draw(t);
      if (running) frame = requestAnimationFrame(loop);
    };

    build();
    draw(0);
    window.addEventListener("resize", () => { build(); draw(performance.now()); });

    if (reduceMotion) return;
    // Animate only while visible
    new IntersectionObserver(([entry]) => {
      running = entry.isIntersecting;
      cancelAnimationFrame(frame);
      if (running) frame = requestAnimationFrame(loop);
    }).observe(canvas);
  });
})();
