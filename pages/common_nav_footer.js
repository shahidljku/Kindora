// --- continent link navigation ---
document.querySelectorAll(".scroll-container1 a").forEach((a) => {
  a.addEventListener("click", (e) => {
    e.preventDefault();
    const href = a.getAttribute("data-href");
    if (href) {
      window.location.href = href; // single click works now
    }
  });
});

// --- toggle sidebar ---
function toggleMenu() {
  document.getElementById("sidebar").classList.toggle("open");
}

// --- CTA button (check if exists first) ---
const ctaBtn = document.querySelector(".cta-btn");
if (ctaBtn) {
  ctaBtn.addEventListener("click", () => {
    const popular = document.getElementById("popular");
    if (popular) {
      popular.scrollIntoView({ behavior: "smooth" });
    }
  });
}

// --- Be Inspired dropdown ---
const inspireBtn = document.getElementById("inspireBtn");
const scrollContainer = document.getElementById("inspireScroll");

if (inspireBtn && scrollContainer) {
  inspireBtn.addEventListener("click", (e) => {
    e.preventDefault();
    scrollContainer.style.display =
      scrollContainer.style.display === "flex" ? "none" : "flex";
  });
}
