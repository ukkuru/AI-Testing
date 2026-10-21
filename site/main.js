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

  // Product tabs. Without JS every panel stays visible.
  const tabs = [...document.querySelectorAll('.tabs [role="tab"]')];
  const select = (tab, focus) => {
    tabs.forEach((t) => {
      const on = t === tab;
      t.setAttribute("aria-selected", String(on));
      t.tabIndex = on ? 0 : -1;
      document.getElementById(t.getAttribute("aria-controls")).hidden = !on;
    });
    if (focus) tab.focus();
    tab.scrollIntoView({ block: "nearest", inline: "nearest" });
  };
  tabs.forEach((t, i) => {
    t.addEventListener("click", () => select(t));
    t.addEventListener("keydown", (e) => {
      const keys = { ArrowRight: i + 1, ArrowLeft: i - 1, Home: 0, End: tabs.length - 1 };
      if (!(e.key in keys)) return;
      e.preventDefault();
      select(tabs[(keys[e.key] + tabs.length) % tabs.length], true);
    });
  });
  if (tabs.length) tabs.forEach((t) => { if (t.getAttribute("aria-selected") !== "true") document.getElementById(t.getAttribute("aria-controls")).hidden = true; });

  const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  // Hero loop: friction heatmap on a purchase request, about 17 seconds
  const loop = document.querySelector(".loop");
  if (loop) {
    const stage = loop.querySelector(".loop__stage");
    const cursor = loop.querySelector(".loop__cursor");
    const btn = loop.querySelector(".lf__btn");
    const status = loop.querySelector(".lf__status");
    const text = loop.querySelector(".loop__text");
    const time = loop.querySelector(".loop__time");
    const bar = loop.querySelector(".loop__progress i");
    const fields = [...loop.querySelectorAll(".lf__input span")];
    const [vendor, cc, amount] = fields;
    const LENGTH = 17000;
    const wait = (ms) => new Promise((r) => setTimeout(r, ms));
    let start = 0;

    const caption = (msg) => {
      const s = Math.floor((performance.now() - start) / 1000);
      time.textContent = `0:${String(s).padStart(2, "0")}`;
      text.style.opacity = 0;
      setTimeout(() => { text.textContent = msg; text.style.opacity = 1; }, 200);
    };
    const pointAt = (el, dx = 0.55, dy = 0.6) => {
      const s = stage.getBoundingClientRect();
      const r = el.getBoundingClientRect();
      const x = r.left - s.left + r.width * dx;
      const y = r.top - s.top + r.height * dy;
      cursor.style.transform = `translate(${x}px, ${y}px)`;
      return { x, y };
    };
    const click = async (el) => {
      const { x, y } = pointAt(el);
      const ripple = document.createElement("span");
      ripple.className = "ripple";
      ripple.style.left = `${x}px`;
      ripple.style.top = `${y}px`;
      stage.appendChild(ripple);
      setTimeout(() => ripple.remove(), 600);
      el.classList.add("is-press");
      await wait(120);
      el.classList.remove("is-press");
    };
    const type = async (span, ms = 45) => {
      const box = span.parentElement;
      pointAt(box, 0.15);
      await wait(500);
      box.classList.add("is-typing");
      for (const ch of span.dataset.type) { span.textContent += ch; await wait(ms); }
      box.classList.remove("is-typing");
    };
    const setStatus = (msg, cls) => { status.textContent = msg; status.className = `lf__status ${cls}`; };
    const reset = () => {
      fields.forEach((f) => { f.textContent = ""; });
      loop.classList.remove("is-heat", "is-tip");
      setStatus("", "");
    };

    if (reduceMotion) {
      vendor.textContent = vendor.dataset.type;
      amount.textContent = amount.dataset.type;
      loop.classList.add("is-heat", "is-tip");
      text.textContent = "The heatmap flags Check budget, and a tip points to the missing cost center.";
      cursor.style.display = "none";
    } else {
      (async function run() {
        for (;;) {
          reset();
          start = performance.now();
          bar.style.transition = "none";
          bar.style.width = "0";
          void bar.offsetWidth;
          bar.style.transition = `width ${LENGTH}ms linear`;
          bar.style.width = "100%";

          caption("A buyer fills in a purchase request and skips Cost center.");
          await type(vendor);
          await type(amount);
          await wait(300);

          caption("They click Check budget. Nothing happens, so they keep clicking.");
          pointAt(btn);
          await wait(900);
          for (let i = 0; i < 4; i++) { await click(btn); setStatus("Budget check failed", "is-err"); await wait(380); }
          await wait(400);

          caption("The friction heatmap flags the button: 42 rage clicks this week.");
          loop.classList.add("is-heat");
          await wait(3200);

          caption("Product Fluency pins a tip to the field that is actually missing.");
          loop.classList.add("is-tip");
          pointAt(cc.parentElement, 0.15);
          await wait(1800);
          loop.classList.remove("is-tip");
          await type(cc, 40);

          caption("Budget check passes. The request moves on and the hotspot cools.");
          pointAt(btn);
          await wait(900);
          await click(btn);
          setStatus("Within budget", "is-ok");
          loop.classList.remove("is-heat");
          await wait(Math.max(400, LENGTH - (performance.now() - start)));
        }
      })();
    }
  }

  // Dotted grid background, shared by the hero and the close section
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
