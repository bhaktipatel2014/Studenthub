/* Shared accessibility helpers and persistent theme/navigation controls. */
(function () {
  const body = document.body;
  const savedTheme = localStorage.getItem("studenthub-theme");
  const prefersDark = window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches;
  body.classList.toggle("dark", savedTheme ? savedTheme === "dark" : prefersDark);

  const main = document.querySelector("main");
  if (main) {
    main.id = main.id || "main-content";
    const skip = document.createElement("a");
    skip.href = "#main-content";
    skip.className = "skip-link";
    skip.textContent = "Skip to main content";
    body.prepend(skip);
  }

  const header = document.querySelector("header");
  const nav = (header && header.querySelector("nav")) || document.querySelector("nav");
  let themeButton = document.getElementById("themeToggle");
  if (!themeButton && header) {
    themeButton = document.createElement("button");
    themeButton.id = "themeToggle";
    themeButton.className = "theme-btn";
    themeButton.type = "button";
    themeButton.textContent = "☾";
    if (nav) nav.append(themeButton);
    else header.append(themeButton);
  }

  function updateThemeButton() {
    if (!themeButton) return;
    const dark = body.classList.contains("dark");
    themeButton.textContent = dark ? "☀" : "☾";
    themeButton.setAttribute("aria-label", dark ? "Switch to light theme" : "Switch to dark theme");
    themeButton.setAttribute("aria-pressed", String(dark));
    themeButton.title = dark ? "Switch to light theme" : "Switch to dark theme";
  }
  updateThemeButton();
  if (themeButton) {
    themeButton.addEventListener("click", function () {
      body.classList.toggle("dark");
      localStorage.setItem("studenthub-theme", body.classList.contains("dark") ? "dark" : "light");
      updateThemeButton();
    });
  }

  if (header && nav && !document.getElementById("navToggle")) {
    const menuButton = document.createElement("button");
    menuButton.id = "navToggle";
    menuButton.type = "button";
    menuButton.className = "nav-toggle";
    menuButton.textContent = "Menu";
    menuButton.setAttribute("aria-expanded", "false");
    menuButton.setAttribute("aria-controls", nav.id || "site-navigation");
    nav.id = nav.id || "site-navigation";
    header.insertBefore(menuButton, nav);
    menuButton.addEventListener("click", function () {
      const expanded = menuButton.getAttribute("aria-expanded") === "true";
      menuButton.setAttribute("aria-expanded", String(!expanded));
      nav.classList.toggle("is-open", !expanded);
    });
  }

  if (nav) {
    const groups = Array.from(nav.querySelectorAll(".nav-group"));
    groups.forEach(function (group) {
      group.addEventListener("toggle", function () {
        if (!group.open) return;
        groups.forEach(function (otherGroup) {
          if (otherGroup !== group) otherGroup.open = false;
        });
      });
      group.addEventListener("pointerleave", function (event) {
        if (event.pointerType === "mouse" && group.open && !group.contains(event.relatedTarget)) {
          group.open = false;
        }
      });
      group.addEventListener("keydown", function (event) {
        if (event.key === "Escape" && group.open) {
          group.open = false;
          group.querySelector("summary").focus();
        }
      });
    });

    document.addEventListener("click", function (event) {
      if (nav.contains(event.target)) return;
      groups.forEach(function (group) { group.open = false; });
    });
  }
})();
