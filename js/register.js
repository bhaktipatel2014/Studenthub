document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("registrationForm");

    if (!form) {
        return;
    }

    const password = document.getElementById("password");
    const confirmPassword = document.getElementById("confirmPassword");
    const strength = document.getElementById("passwordStrength");

    function showError(id, message) {
        document.getElementById(id).textContent = message;
    }

    function checkPasswordStrength(value) {
        if (value.length < 8) {
            return "Weak";
        }

        const hasLetter = /[A-Za-z]/.test(value);
        const hasNumber = /[0-9]/.test(value);
        const hasSpecial = /[^A-Za-z0-9]/.test(value);

        if (hasLetter && hasNumber && hasSpecial) {
            return "Strong";
        }

        if (hasLetter && hasNumber) {
            return "Medium";
        }

        return "Weak";
    }

    password.addEventListener("input", function () {
        strength.textContent =
            "Password strength: " +
            checkPasswordStrength(password.value);
    });

    confirmPassword.addEventListener("input", function () {
        if (confirmPassword.value !== password.value) {
            showError("confirmError", "Passwords do not match.");
        } else {
            showError("confirmError", "");
        }
    });

    form.addEventListener("submit", function (event) {

        let valid = true;

        showError("nameError", "");
        showError("emailError", "");
        showError("mobileError", "");
        showError("passwordError", "");
        showError("confirmError", "");
        showError("termsError", "");

        const name = document.getElementById("fullName").value.trim();
        const email = document.getElementById("email").value.trim();
        const mobile = document.getElementById("mobile").value.trim();
        const terms = document.getElementById("terms").checked;

        if (!/^[A-Za-z ]{2,50}$/.test(name)) {
            showError("nameError", "Enter a valid name.");
            valid = false;
        }

        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showError("emailError", "Enter a valid email address.");
            valid = false;
        }

        if (!/^[0-9]{10}$/.test(mobile)) {
            showError("mobileError", "Enter a 10-digit mobile number.");
            valid = false;
        }

        if (password.value.length < 8) {
            showError("passwordError", "Password must contain at least 8 characters.");
            valid = false;
        }

        if (password.value !== confirmPassword.value) {
            showError("confirmError", "Passwords do not match.");
            valid = false;
        }

        if (!terms) {
            showError("termsError", "Please accept the terms.");
            valid = false;
        }

        if (!valid) {
            event.preventDefault();
        }
    });
});

