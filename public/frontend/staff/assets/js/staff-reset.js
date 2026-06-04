window.addEventListener("load", () => {
  document.querySelector("[data-reveal]").classList.add("active");
});

document.addEventListener("mousemove", (e) => {
  const card = document.querySelector(".parallax");
  const bgBranding = document.querySelector(".bg-branding");
  const x = (window.innerWidth / 2 - e.clientX) / 35;
  const y = (window.innerHeight / 2 - e.clientY) / 35;
  if (card) card.style.transform = `translate(${x}px, ${y}px)`;
  if (bgBranding) bgBranding.style.transform = `translate(calc(-50% + ${-x * 1.5}px), calc(-50% + ${-y * 1.5}px))`;
});