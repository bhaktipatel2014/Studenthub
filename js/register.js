document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("registrationForm");
  if (!form) return;
  const password = document.getElementById("password");
  const confirmation = document.getElementById("confirmPassword");
  const query = new URLSearchParams(window.location.search);
  const notice = document.getElementById("formNotice");
  if (query.has("status")) {
    notice.textContent = query.get("status") === "ok" ? "Registration successful! Your details have been saved successfully." : (query.get("msg") || "Please review the form and try again.");
    notice.className = query.get("status") === "ok" ? "success" : "error";
  }
  if (window.location.protocol === "file:") {
    notice.textContent = "Start Apache in XAMPP and open this page at http://localhost/StudentHub/register.html to submit registration safely.";
    notice.className = "info";
    form.addEventListener("submit", function (event) {
      event.preventDefault();
      notice.scrollIntoView({ behavior: "smooth", block: "center" });
    });
  }
  function setError(id, message) { document.getElementById(id).textContent = message; }
  function passwordGrade(value) {
    let count = 0;
    if (value.length >= 8) count++;
    if (/[a-z]/.test(value)) count++;
    if (/[A-Z]/.test(value)) count++;
    if (/\d/.test(value)) count++;
    if (/[^A-Za-z0-9]/.test(value)) count++;
    return count >= 5 ? "Strong" : count >= 3 ? "Getting stronger" : "Weak";
  }
  password.addEventListener("input", function () {
    document.getElementById("passwordStrength").textContent = "Password strength: " + passwordGrade(password.value) + ". Use upper- and lowercase letters, a number and a symbol.";
  });
  confirmation.addEventListener("input", function () {
    setError("confirmError", confirmation.value && confirmation.value !== password.value ? "Passwords do not match." : "");
  });

  form.addEventListener("submit", function (event) {
    const checks = [
      ["fullName", "nameError", value => /^[\p{L}\p{M}][\p{L}\p{M} .'-]{1,49}$/u.test(value), "Enter a valid name using 2–50 letters."],
      ["email", "emailError", value => value.length <= 190 && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value), "Enter a valid email address."],
      ["mobile", "mobileError", value => /^\d{10}$/.test(value), "Mobile number must contain 10 digits."],
      ["course", "courseError", value => ["B.Tech IT", "B.Tech CSE", "B.Tech CE", "BCA", "MCA"].includes(value), "Select a valid course."],
      ["year", "yearError", value => ["1st Year", "2nd Year", "3rd Year", "4th Year"].includes(value), "Select your current year."],
      ["password", "passwordError", value => value.length <= 128 && /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,128}$/.test(value), "Use 8–128 characters with uppercase, lowercase, a number and a symbol."],
      ["confirmPassword", "confirmError", value => value !== "" && value === password.value, "Passwords do not match."],
    ];
    let firstInvalid = null;
    for (const [fieldId, errorId, valid, message] of checks) {
      const field = document.getElementById(fieldId);
      const isValid = valid(field.value.trim());
      setError(errorId, isValid ? "" : message);
      field.setAttribute("aria-invalid", String(!isValid));
      if (!isValid && !firstInvalid) firstInvalid = field;
    }

    const selectedGender = form.querySelector('input[name="gender"]:checked');
    const genderValid = Boolean(selectedGender);
    setError("genderError", genderValid ? "" : "Choose a gender option.");
    const terms = document.getElementById("terms");
    setError("termsError", terms.checked ? "" : "Accept the terms to continue.");
    if (!genderValid && !firstInvalid) firstInvalid = form.querySelector('input[name="gender"]');
    if (!terms.checked && !firstInvalid) firstInvalid = terms;

    if (firstInvalid) {
      event.preventDefault();
      firstInvalid.focus();
      firstInvalid.scrollIntoView({ behavior: "smooth", block: "center" });
    }
  });
});
