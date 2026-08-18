console.log("StudentHub javascript loaded successfully");
console.log("Welcome to StudentHub");
console.log("Practical - 4 JavaScript");

let studentname = "Bhakti";
let course = "information technology";
let semester = 5;

console.log(studentname);
console.log(course);
console.log(semester);

let college = "CHARUSAT";
let year = "2026";
let isstudent = true;

console.log(college);
console.log(year);
console.log(isstudent);

function welcomeMessage() {
    console.log("Welcome to StudentHub.");
}


/* ================= IMAGE SLIDER ================= */

let slideIndex = 1;

showSlide(slideIndex);

function changeSlide(n) {
    showSlide(slideIndex += n);
}

function currentSlide(n) {
    showSlide(slideIndex = n);
}

function showSlide(n) {

    let slides = document.querySelectorAll(".slides img");
    let dots = document.querySelectorAll(".dot");

    if (slides.length === 0) {
        return;
    }

    if (n > slides.length) {
        slideIndex = 1;
    }

    if (n < 1) {
        slideIndex = slides.length;
    }

    slides.forEach(function (slide) {
        slide.style.display = "none";
    });

    dots.forEach(function (dot) {
        dot.classList.remove("active");
    });

    slides[slideIndex - 1].style.display = "block";

    if (dots[slideIndex - 1]) {
        dots[slideIndex - 1].classList.add("active");
    }
}


/* ================= NOTIFICATION ================= */

const notification = document.getElementById("notification");
const closeBtn = document.getElementById("closeBtn");

if (closeBtn && notification) {

    closeBtn.addEventListener("click", function () {
        notification.style.display = "none";
    });

}


/* ================= DARK / LIGHT MODE ================= */

const themeToggle = document.getElementById("themeToggle");

function updateThemeButton() {

    if (!themeToggle) {
        return;
    }

    const isDark = document.body.classList.contains("dark");

    themeToggle.textContent = isDark
        ? "☀️ Light Mode"
        : "🌙 Dark Mode";
}


/* Load saved theme */

const savedTheme = localStorage.getItem("theme");

if (savedTheme === "dark") {
    document.body.classList.add("dark");
} else {
    document.body.classList.remove("dark");
}

updateThemeButton();


/* Theme button */

if (themeToggle) {

    themeToggle.addEventListener("click", function () {

        document.body.classList.toggle("dark");

        const isDark = document.body.classList.contains("dark");

        localStorage.setItem(
            "theme",
            isDark ? "dark" : "light"
        );

        updateThemeButton();

    });

}