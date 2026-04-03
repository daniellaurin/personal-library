/**
 * themes.js — seasonal theme switcher, particle engine, and background audio.
 *
 * audio files required in /audio/:
 *   autumn-wind.mp3     — wind / rustling leaves ambiance (royalty-free, e.g. freesound.org CC0)
 *   spring-birds.mp3    — birds chirping ambiance      (royalty-free, e.g. freesound.org CC0)
 *
 * the data-base attribute on the <script> tag tells this file where the
 * project root is so audio paths resolve correctly from any subdirectory.
 * example: <script src="../themes.js" data-base="../" defer></script>
 */

/* theme + audio config */

const THEMES = {
  autumn: {
    label:     "Autumn",
    icon:      "🍂",
    particles: ["🍂", "🍁", "🌿", "🍃", "🍂", "🍁"],
    count:     18,
    speedMin:  8,
    speedMax:  16,
    audio:     "audio/autumn-wind.mp3",
    audioVol:  0.45,
  },
  spring: {
    label:     "Spring",
    icon:      "🌸",
    particles: ["🌸", "🌺", "🌷", "🌸", "🌼", "🌸"],
    count:     20,
    speedMin:  10,
    speedMax:  18,
    audio:     "audio/spring-birds.mp3",
    audioVol:  0.4,
  },
  winter: {
    label:     "Winter",
    icon:      "❄️",
    particles: ["❄️", "❄️", "❄️", "🌨️", "❄️", "❄️"],
    count:     22,
    speedMin:  12,
    speedMax:  22,
    audio:     "audio/winter-christmas.mp3",
    audioVol:  0.5,
  },
};

const DEFAULT_THEME = "autumn";
const STORAGE_KEY   = "pl-season";
const MUTE_KEY      = "pl-muted";

/*  state  */

let audioMap     = {};   // { theme: HTMLAudioElement }
let currentAudio = null;
let isMuted      = false;
let audioReady   = false; // true after first user interaction unlocks autoplay

/* resolve base path from data-base attribute  */

function getBase() {
  /* defer attribute means document.currentScript is null at execution time,
     so we query by src pattern instead */
  const el = document.querySelector('script[src*="themes.js"]');
  return el ? (el.getAttribute("data-base") || "") : "";
}

/*  audio engine */

function initAudio() {
  try { isMuted = localStorage.getItem(MUTE_KEY) === "true"; } catch (_) {}

  const base = getBase();

  Object.entries(THEMES).forEach(([key, cfg]) => {
    if (!cfg.audio) return;
    const audio  = new Audio(base + cfg.audio);
    audio.loop   = true;
    audio.volume = 0;
    audioMap[key] = audio;
  });
}

/* gradually reduce volume then pause */
function fadeOut(audio, durationMs = 700, onDone) {
  if (audio.paused) { if (onDone) onDone(); return; }
  const steps    = durationMs / 40;
  const decrement = audio.volume / steps;
  const id = setInterval(() => {
    audio.volume = Math.max(0, audio.volume - decrement);
    if (audio.volume <= 0.001) {
      clearInterval(id);
      audio.pause();
      audio.volume = 0;
      if (onDone) onDone();
    }
  }, 40);
}

/* gradually raise volume from 0 to target */
function fadeIn(audio, targetVol, durationMs = 1200) {
  if (isMuted) return;
  audio.volume = 0;
  audio.play().catch(() => {
    /* browser blocked autoplay — user hasn't interacted yet; will retry on next click */
    audioReady = false;
  });
  const steps     = durationMs / 40;
  const increment = targetVol / steps;
  const id = setInterval(() => {
    audio.volume = Math.min(targetVol, audio.volume + increment);
    if (audio.volume >= targetVol - 0.001) {
      audio.volume = targetVol;
      clearInterval(id);
    }
  }, 40);
}

/* switch to the audio track for the given theme */
function playThemeAudio(theme, fromInteraction = false) {
  if (fromInteraction) audioReady = true;
  if (!audioReady) return; // respect browser autoplay policy until user has clicked

  const next    = audioMap[theme];
  const cfg     = THEMES[theme];
  if (!next || !cfg) return;

  if (currentAudio && currentAudio !== next) {
    /* cross-fade: fade out old, then fade in new */
    const prev = currentAudio;
    currentAudio = next;
    fadeOut(prev, 700, () => fadeIn(next, cfg.audioVol));
  } else if (!currentAudio || currentAudio.paused) {
    currentAudio = next;
    fadeIn(next, cfg.audioVol);
  }
}

/* pause all audio (called when muting) */
function muteAll() {
  Object.values(audioMap).forEach(a => { a.volume = 0; });
  if (currentAudio && !currentAudio.paused) {
    fadeOut(currentAudio, 400);
  }
}

/* resume current track (called when unmuting) */
function unmuteAudio() {
  if (!currentAudio) return;
  const theme = document.body.dataset.theme || DEFAULT_THEME;
  const cfg   = THEMES[theme];
  if (cfg) fadeIn(currentAudio, cfg.audioVol);
}

/*  mute button */

function buildMuteBtn() {
  const switcher = document.querySelector(".season-switcher");
  if (!switcher) return;

  const btn = document.createElement("button");
  btn.className = "mute-btn";
  btn.setAttribute("aria-label", "toggle background music");
  btn.title = "Toggle background music";
  btn.textContent = isMuted ? "🔇" : "🔊";

  btn.addEventListener("click", () => {
    audioReady = true; /* first click = user interaction = autoplay allowed */
    isMuted = !isMuted;
    btn.textContent = isMuted ? "🔇" : "🔊";
    try { localStorage.setItem(MUTE_KEY, isMuted); } catch (_) {}

    if (isMuted) {
      muteAll();
    } else {
      /* start playing the current theme's track if not already */
      const theme = document.body.dataset.theme || DEFAULT_THEME;
      playThemeAudio(theme, true);
    }
  });

  switcher.appendChild(btn);
}

/* particle engine  */

function createParticleContainer() {
  let container = document.getElementById("seasonal-particles");
  if (!container) {
    container = document.createElement("div");
    container.id = "seasonal-particles";
    document.body.insertBefore(container, document.body.firstChild);
  }
  return container;
}

function spawnParticle(container, theme, slideIn = false) {
  const cfg = THEMES[theme];
  if (!cfg) return;

  const el = document.createElement("span");
  el.classList.add("particle");
  el.setAttribute("aria-hidden", "true");
  el.textContent = cfg.particles[Math.floor(Math.random() * cfg.particles.length)];
  el.style.left  = Math.random() * 100 + "vw";

  const duration = cfg.speedMin + Math.random() * (cfg.speedMax - cfg.speedMin);
  el.style.animationDuration = duration + "s";
  el.style.animationDelay   = Math.random() * -duration + "s";
  el.style.fontSize = (1 + Math.random() * 0.8) + "rem";

  if (slideIn) {
    el.classList.add("slide-in");
    el.style.top          = Math.random() * 80 + "vh";
    el.style.animationDelay = "0s";

    el.addEventListener("animationend", function onSlide() {
      el.classList.remove("slide-in");
      el.style.top          = "-60px";
      el.style.left         = Math.random() * 100 + "vw";
      el.style.animationDelay = Math.random() * -duration + "s";
      el.removeEventListener("animationend", onSlide);
    }, { once: true });
  }

  container.appendChild(el);
}

function clearParticles(container) {
  Array.from(container.children).forEach(el => {
    el.style.transition = "opacity 0.4s ease";
    el.style.opacity    = "0";
  });
  setTimeout(() => { container.innerHTML = ""; }, 450);
}

function spawnParticles(theme, slideIn = false) {
  const container = createParticleContainer();
  const cfg       = THEMES[theme];
  if (!cfg) return;
  for (let i = 0; i < cfg.count; i++) {
    setTimeout(() => spawnParticle(container, theme, slideIn), i * 60);
  }
}

/* theme application  */

function applyTheme(theme, slideIn = false) {
  if (!THEMES[theme]) theme = DEFAULT_THEME;

  document.body.dataset.theme = theme;

  try { localStorage.setItem(STORAGE_KEY, theme); } catch (_) {}

  document.querySelectorAll(".season-btn").forEach(btn => {
    btn.classList.toggle("active", btn.dataset.themeSet === theme);
  });

  const container = document.getElementById("seasonal-particles");
  if (container) clearParticles(container);
  setTimeout(() => spawnParticles(theme, slideIn), slideIn ? 100 : 0);

  /* audio: only play if user has already interacted (slideIn means they clicked) */
  playThemeAudio(theme, slideIn);
}

/* season switcher nav buttons  */

function buildSwitcher() {
  let switcher = document.querySelector(".season-switcher");
  if (!switcher) {
    const navInner = document.querySelector(".nav-inner");
    if (!navInner) return;
    switcher = document.createElement("div");
    switcher.className = "season-switcher";
    navInner.appendChild(switcher);
  }

  switcher.innerHTML = "";

  Object.entries(THEMES).forEach(([key, cfg]) => {
    const btn = document.createElement("button");
    btn.className              = "season-btn";
    btn.dataset.themeSet       = key;
    btn.title                  = cfg.label;
    btn.textContent            = cfg.icon;
    btn.setAttribute("aria-label", `Switch to ${cfg.label} theme`);

    btn.addEventListener("click", () => {
      audioReady = true; /* clicking = user interaction */
      const current = document.body.dataset.theme || DEFAULT_THEME;
      if (current !== key) applyTheme(key, true);
    });

    switcher.appendChild(btn);
  });

  /* mute button sits right after the season emoji buttons */
  buildMuteBtn();
}

/*  hamburger menu  */

function initHamburger() {
  const toggle = document.querySelector(".nav-toggle");
  const links  = document.querySelector(".nav-links");
  if (!toggle || !links) return;

  toggle.addEventListener("click", () => {
    const open = links.classList.toggle("open");
    toggle.classList.toggle("open", open);
    toggle.setAttribute("aria-expanded", open);
  });

  links.querySelectorAll("a").forEach(a => {
    a.addEventListener("click", () => {
      links.classList.remove("open");
      toggle.classList.remove("open");
      toggle.setAttribute("aria-expanded", false);
    });
  });
}

/*  auto-detect season from calendar month  */

function detectSeason() {
  const month = new Date().getMonth() + 1;
  if (month >= 3 && month <= 5)  return "spring";
  if (month >= 9 && month <= 11) return "autumn";
  return "winter";
}

/*  bootstrap  */

function init() {
  let saved;
  try { saved = localStorage.getItem(STORAGE_KEY); } catch (_) {}
  const theme = THEMES[saved] ? saved : detectSeason();

  /* set theme on body immediately (before audio/particles) to avoid flash */
  document.body.dataset.theme = theme;

  initAudio();
  buildSwitcher();
  initHamburger();
  applyTheme(theme, false);
  /* note: audio will NOT autoplay on page load — browser policy requires a user gesture.
     it will start on the first season button click or mute-button click. */
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", init);
} else {
  init();
}
