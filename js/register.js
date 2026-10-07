document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("registrationForm");
  if (!form) return;
  const password = document.getElementById("password");
  const confirmation = document.getElementById("confirmPassword");
  const errors = ["nameError", "emailError", "mobileError", "courseError", "yearError", "genderError", "passwordError", "confirmError", "termsError"];
  const query = new URLSearchParams(window.location.search);
  const notice = document.getElementById("formNotice");
  if (query.has("status")) {
    notice.textContent = query.get("status") === "ok" ? "Your account details were saved. You can now sign in." : (query.get("msg") || "Please review the form and try again.");
    notice.className = query.get("status") === "ok" ? "success" : "error";
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
    errors.forEach((id) => setError(id, ""));
    let valid = true;
    const name = document.getElementById("fullName");
    const email = document.getElementById("email");
    const mobile = document.getElementById("mobile");
    const terms = document.getElementById("terms");
    if (!/^[A-Za-z][A-Za-z .'-]{1,49}$/.test(name.value.trim())) { setError("nameError", "Enter a name using 2–50 letters, spaces or apostrophes."); valid = false; }
    if (!email.validity.valid) { setError("emailError", "Enter a valid email address."); valid = false; }
    if (!/^\d{10}$/.test(mobile.value.trim())) { setError("mobileError", "Enter a 10-digit mobile number."); valid = false; }
    if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,128}$/.test(password.value)) { setError("passwordError", "Use 8–128 characters with upper- and lowercase letters, a number and a symbol."); valid = false; }
    if (!confirmation.value || confirmation.value !== password.value) { setError("confirmError", "Enter the same password again."); valid = false; }
    if (!form.elements.course.value) { setError("courseError", "Choose your course."); valid = false; }
    if (!form.elements.year.value) { setError("yearError", "Choose your current year."); valid = false; }
    if (!form.querySelector('input[name="gender"]:checked')) { setError("genderError", "Choose an option or select “Prefer not to say”."); valid = false; }
    if (!terms.checked) { setError("termsError", "Please confirm the terms to continue."); valid = false; }
    if (!valid) {
      event.preventDefault();
      const firstInvalid = form.querySelector(".error:not(:empty)") || form.querySelector(":invalid");
      if (firstInvalid) firstInvalid.scrollIntoView({ behavior: "smooth", block: "center" });
    }
  });
});
