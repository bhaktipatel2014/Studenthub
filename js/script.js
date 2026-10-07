document.addEventListener("DOMContentLoaded", function () {
  const slides = Array.from(document.querySelectorAll(".slides img"));
  const dots = Array.from(document.querySelectorAll(".slider-dots .dot"));
  let activeSlide = 0;
  function showSlide(index) {
    if (!slides.length) return;
    activeSlide = (index + slides.length) % slides.length;
    slides.forEach((slide, i) => { slide.hidden = i !== activeSlide; });
    dots.forEach((dot, i) => {
      dot.classList.toggle("active", i === activeSlide);
      dot.setAttribute("aria-current", i === activeSlide ? "true" : "false");
    });
  }
  window.changeSlide = (step) => showSlide(activeSlide + step);
  window.currentSlide = (number) => showSlide(number - 1);
  showSlide(0);

  const notification = document.getElementById("notification");
  const close = document.getElementById("closeBtn");
  if (notification && close) close.addEventListener("click", () => notification.remove());

  // Small native dialog gives the home page a keyboard-friendly detail popup.
  const openDialog = document.getElementById("openWelcomeDialog");
  const dialog = document.getElementById("welcomeDialog");
  if (openDialog && dialog && typeof dialog.showModal === "function") {
    openDialog.addEventListener("click", () => dialog.showModal());
    dialog.querySelector("[data-close-dialog]")?.addEventListener("click", () => dialog.close());
  }
});
