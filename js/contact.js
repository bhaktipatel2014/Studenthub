document.addEventListener("DOMContentLoaded", () => {
  const notice = document.getElementById("contactNotice");
  if (!notice) return;

  const params = new URLSearchParams(window.location.search);
  const status = params.get("status");
  if (status !== "ok" && status !== "error") return;

  notice.textContent = params.get("msg") || (status === "ok"
    ? "Your message has been sent successfully."
    : "Please check your details and try again.");
  notice.classList.add(status === "ok" ? "is-success" : "is-error");
  notice.hidden = false;
});
