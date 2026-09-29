document.addEventListener("DOMContentLoaded", function () {

    /* ================= IMAGE SLIDER ================= */

    let slideIndex = 1;

    function showSlide(number) {
        const slides = document.querySelectorAll(".slides img");
        const dots = document.querySelectorAll(".dot");

        if (slides.length === 0) {
            return;
        }

        if (number > slides.length) {
            slideIndex = 1;
        }

        if (number < 1) {
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

    window.changeSlide = function (number) {
        slideIndex += number;
        showSlide(slideIndex);
    };

    window.currentSlide = function (number) {
        slideIndex = number;
        showSlide(slideIndex);
    };

    showSlide(slideIndex);


    /* ================= NOTIFICATION ================= */

    const notification = document.getElementById("notification");
    const closeButton = document.getElementById("closeBtn");

    if (notification && closeButton) {
        closeButton.addEventListener("click", function () {
            notification.style.display = "none";
        });
    }


    /* ================= DARK / LIGHT MODE ================= */

    const themeToggle = document.getElementById("themeToggle");

    function updateThemeButton() {
        if (!themeToggle) {
            return;
        }

        const darkMode = document.body.classList.contains("dark");

        themeToggle.textContent = darkMode
            ? "☀️ Light Mode"
            : "🌙 Dark Mode";
    }

    const savedTheme = localStorage.getItem("theme");

    if (savedTheme === "dark") {
        document.body.classList.add("dark");
    }

    updateThemeButton();

    if (themeToggle) {
        themeToggle.addEventListener("click", function () {
            document.body.classList.toggle("dark");

            const darkMode = document.body.classList.contains("dark");

            localStorage.setItem(
                "theme",
                darkMode ? "dark" : "light"
            );

            updateThemeButton();
        });
    }


    /* ================= FAQ - PRACTICAL 4 ================= */

    const faqList = document.getElementById("faqList");

    if (faqList) {
        fetch("data/faqs.json")
            .then(function (response) {
                if (!response.ok) {
                    throw new Error("FAQ data could not be loaded.");
                }
                return response.json();
            })
            .then(function (faqs) {
                faqList.innerHTML = "";

                faqs.forEach(function (faq) {
                    const item = document.createElement("article");
                    const button = document.createElement("button");
                    const answer = document.createElement("p");

                    button.type = "button";
                    button.textContent = faq.question;
                    answer.textContent = faq.answer;
                    answer.hidden = true;

                    button.addEventListener("click", function () {
                        answer.hidden = !answer.hidden;
                    });

                    item.appendChild(button);
                    item.appendChild(answer);
                    faqList.appendChild(item);
                });
            })
            .catch(function () {
                faqList.innerHTML =
                    "<p>Unable to load FAQ data.</p>";
            });
    }

});