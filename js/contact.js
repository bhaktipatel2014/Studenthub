document.addEventListener("DOMContentLoaded", () => {
  const notice = document.getElementById("contactNotice");
  if (!notice) return;
  const form = document.getElementById("contactForm");

  const params = new URLSearchParams(window.location.search);
  const status = params.get("status");
  if (status === "ok" || status === "error") {
    notice.textContent = params.get("msg") || (status === "ok"
      ? "Your message has been submitted successfully."
      : "Please check your details and try again.");
    notice.classList.add(status === "ok" ? "is-success" : "is-error");
    notice.hidden = false;
  }

  if (window.location.protocol === "file:" && form) {
    notice.textContent = "Start Apache in XAMPP and open http://localhost/StudentHub/contact.html to submit your message.";
    notice.className = "contact-notice is-info";
    notice.hidden = false;
    form.addEventListener("submit", (event) => {
      event.preventDefault();
      notice.scrollIntoView({ behavior: "smooth", block: "center" });
    });
  }
});
