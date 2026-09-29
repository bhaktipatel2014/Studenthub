document.addEventListener("DOMContentLoaded", function () {

    const eventList = document.getElementById("eventList");
    const searchInput = document.getElementById("eventSearch");
    const filterInput = document.getElementById("eventFilter");
    const status = document.getElementById("eventStatus");
    const previousButton = document.getElementById("previousPage");
    const nextButton = document.getElementById("nextPage");
    const pageNumber = document.getElementById("pageNumber");

    let events = [];
    let currentPage = 1;
    const recordsPerPage = 6;

    function renderEvents() {

        const searchText = searchInput.value.toLowerCase();
        const category = filterInput.value;

        const filteredEvents = events.filter(function (event) {
            const matchesSearch =
                event.title.toLowerCase().includes(searchText);

            const matchesCategory =
                category === "all" || event.category === category;

            return matchesSearch && matchesCategory;
        });

        const totalPages =
            Math.max(1, Math.ceil(filteredEvents.length / recordsPerPage));

        if (currentPage > totalPages) {
            currentPage = totalPages;
        }

        const start = (currentPage - 1) * recordsPerPage;
        const pageEvents =
            filteredEvents.slice(start, start + recordsPerPage);

        eventList.innerHTML = "";

        if (pageEvents.length === 0) {
            eventList.innerHTML = "<p>No events found.</p>";
        }

        pageEvents.forEach(function (event) {

            const article = document.createElement("article");
            article.className = "card";

            article.innerHTML = `
                <img src="${event.image}" alt="${event.title}">
                <h3>${event.title}</h3>
                <p><strong>Date:</strong> ${event.date}</p>
                <p>${event.description}</p>
                <p><strong>Category:</strong> ${event.category}</p>
            `;

            eventList.appendChild(article);
        });

        status.textContent =
            filteredEvents.length + " event(s) found.";

        pageNumber.textContent =
            "Page " + currentPage + " of " + totalPages;

        previousButton.disabled = currentPage === 1;
        nextButton.disabled = currentPage === totalPages;
    }

    fetch("data/events.json")
        .then(function (response) {
            if (!response.ok) {
                throw new Error("Event data could not be loaded.");
            }
            return response.json();
        })
        .then(function (data) {
            events = data;
            renderEvents();
        })
        .catch(function () {
            status.textContent =
                "Unable to load event data.";
        });

    searchInput.addEventListener("input", function () {
        currentPage = 1;
        renderEvents();
    });

    filterInput.addEventListener("change", function () {
        currentPage = 1;
        renderEvents();
    });

    previousButton.addEventListener("click", function () {
        if (currentPage > 1) {
            currentPage--;
            renderEvents();
        }
    });

    nextButton.addEventListener("click", function () {
        currentPage++;
        renderEvents();
    });

});
