document.addEventListener("DOMContentLoaded", () => {
  const params = new URLSearchParams(window.location.search);
  const notice = document.getElementById("loginNotice");
  if (params.has("status") && params.get("msg")) {
    notice.textContent = params.get("msg");
    notice.hidden = false;
  }
});
