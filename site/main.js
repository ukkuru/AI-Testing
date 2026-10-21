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
  document.querySelectorAll("[data-video-open]").forEach((btn) => {
    btn.addEventListener("click", () => {
      if (!video.src) video.src = video.dataset.src;
      const t = Number(btn.dataset.t || 0);
      const seek = () => { video.currentTime = t; video.play().catch(() => {}); };
      if (video.readyState >= 1) seek();
      else video.addEventListener("loadedmetadata", seek, { once: true });
      modal.showModal();
    });
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

  // Hero loop: five chapters on the Intelligent Sights heatmap, about 18.5 seconds
  const loop = document.querySelector(".loop");
  if (loop) {
    const stage = loop.querySelector(".loop__stage");
    const cursor = loop.querySelector(".loop__cursor");
    const check = loop.querySelector(".app__check");
    const status = loop.querySelector(".app__status");
    const badge = loop.querySelector(".loop__badge");
    const count = loop.querySelector(".loop__count");
    const mode = loop.querySelector(".loop__mode");
    const text = loop.querySelector(".loop__text");
    const time = loop.querySelector(".loop__time");
    const segs = [...loop.querySelectorAll(".loop__chapters i")];
    const fills = [...loop.querySelectorAll(".fld i")];
    const hot = Object.fromEntries([...loop.querySelectorAll("[data-hot]")].map((el) => [el.dataset.hot, el]));
    const weights = { check: 0.5, search: 0.22, category: 0.16, help: 0.12 };
    const blobs = {};
    const wait = (ms) => new Promise((r) => setTimeout(r, ms));
    let start = 0;

    const center = (el, dx = 0.5, dy = 0.5) => {
      const s = stage.getBoundingClientRect();
      const r = el.getBoundingClientRect();
      return { x: r.left - s.left + r.width * dx, y: r.top - s.top + r.height * dy, w: r.width, h: r.height };
    };
    for (const k of Object.keys(weights)) {
      const b = document.createElement("span");
      b.className = "blob";
      stage.appendChild(b);
      blobs[k] = b;
    }
    const place = () => {
      for (const [k, b] of Object.entries(blobs)) {
        const c = center(hot[k]);
        b.style.left = `${c.x}px`;
        b.style.top = `${c.y}px`;
      }
    };
    const heat = (levels) => { for (const [k, v] of Object.entries(levels)) blobs[k].style.setProperty("--k", v); };
    const caption = (msg) => {
      time.textContent = `0:${String(Math.floor((performance.now() - start) / 1000)).padStart(2, "0")}`;
      text.style.opacity = 0;
      setTimeout(() => { text.textContent = msg; text.style.opacity = 1; }, 200);
    };
    const chapter = (i, secs) => {
      segs.forEach((seg, j) => {
        seg.style.setProperty("--t", "0s");
        seg.style.setProperty("--p", j < i ? "100%" : "0%");
      });
      void segs[i].offsetWidth;
      segs[i].style.setProperty("--t", `${secs}s`);
      segs[i].style.setProperty("--p", "100%");
    };
    const tally = async (from, to, ms) => {
      const t0 = performance.now();
      for (;;) {
        const p = Math.min(1, (performance.now() - t0) / ms);
        count.textContent = Math.round(from + (to - from) * p);
        if (p === 1) return;
        await wait(40);
      }
    };
    const rain = async (ms) => {
      const keys = Object.keys(weights);
      const t0 = performance.now();
      while (performance.now() - t0 < ms) {
        let r = Math.random();
        const k = keys.find((key) => (r -= weights[key]) < 0) || "check";
        const c = center(hot[k]);
        const d = document.createElement("span");
        d.className = "dotclick";
        d.style.left = `${c.x + (Math.random() - 0.5) * c.w * 0.8}px`;
        d.style.top = `${c.y + (Math.random() - 0.5) * c.h * 0.9}px`;
        stage.appendChild(d);
        setTimeout(() => d.remove(), 700);
        await wait(55);
      }
    };
    const pointAt = (el, dx = 0.5, dy = 0.6) => {
      const c = center(el, dx, dy);
      cursor.style.transform = `translate(${c.x}px, ${c.y}px)`;
      return c;
    };
    const click = async (el) => {
      const c = pointAt(el);
      const r = document.createElement("span");
      r.className = "ripple";
      r.style.left = `${c.x}px`;
      r.style.top = `${c.y}px`;
      stage.appendChild(r);
      setTimeout(() => r.remove(), 600);
      el.classList.add("is-press");
      await wait(140);
      el.classList.remove("is-press");
    };
    const type = async (el, ms = 38) => { for (const ch of el.dataset.fill) { el.textContent += ch; await wait(ms); } };
    const reset = () => {
      loop.classList.remove("is-flag", "is-cause", "is-tip", "is-after");
      check.classList.remove("is-ready");
      status.classList.remove("is-on");
      status.textContent = "";
      mode.textContent = "Before flow";
      count.textContent = "0";
      fills.forEach((f) => { f.textContent = ""; });
      fills[0].textContent = fills[0].dataset.fill;
      fills[3].textContent = fills[3].dataset.fill;
      heat({ check: 0, search: 0, category: 0, help: 0 });
      cursor.style.transform = `translate(${stage.clientWidth * 0.3}px, ${stage.clientHeight * 0.9}px)`;
    };

    if (reduceMotion) {
      reset();
      place();
      heat({ check: 1, search: 0.7, category: 0.5, help: 0.45 });
      count.textContent = "212";
      loop.classList.add("is-flag", "is-cause");
      text.textContent = "Clicks pile onto Check budget before cost centre and amount are set.";
    } else {
      window.addEventListener("resize", place);
      (async function run() {
        for (;;) {
          reset();
          await wait(50);
          place();
          start = performance.now();

          chapter(0, 4.5);
          caption("Before: 100 real sessions on a purchase request.");
          heat({ check: 1, search: 0.7, category: 0.5, help: 0.45 });
          await rain(4500);

          chapter(1, 3.5);
          caption("Clicks pile onto Check budget. It cannot be pressed yet.");
          loop.classList.add("is-flag");
          await tally(0, 212, 1600);
          await wait(1900);

          chapter(2, 3);
          caption("Intelligent Sights names the cause behind the hotspot.");
          loop.classList.add("is-cause");
          await wait(3000);

          chapter(3, 4);
          caption("Product Fluency pins a tip, and the button unlocks.");
          loop.classList.remove("is-cause");
          loop.classList.add("is-tip");
          pointAt(fills[1], 0.3);
          await wait(800);
          await type(fills[1]);
          pointAt(fills[2], 0.3);
          await wait(700);
          await type(fills[2], 60);
          check.classList.add("is-ready");
          pointAt(check);
          await wait(800);
          await click(check);
          status.textContent = "Within budget";
          status.classList.add("is-on");
          await wait(400);

          chapter(4, 3.5);
          caption("After flow: rage clicks on Check budget fall from 212 to 18.");
          loop.classList.remove("is-tip");
          loop.classList.add("is-after");
          mode.textContent = "After flow";
          heat({ check: 0.15, search: 0.12, category: 0.2, help: 0.05 });
          await tally(212, 18, 1400);
          await wait(2100);
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
