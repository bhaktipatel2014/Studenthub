document.addEventListener("DOMContentLoaded", () => {
  const list = document.getElementById("eventList");
  if (!list) return;

  const search = document.getElementById("eventSearch");
  const filter = document.getElementById("eventFilter");
  const sort = document.getElementById("eventSort");
  const status = document.getElementById("eventStatus");
  const previous = document.getElementById("previousPage");
  const next = document.getElementById("nextPage");
  const pageLabel = document.getElementById("pageNumber");
  const pageSize = 6;
  // Browsers block fetch() from file:// pages, so keep sample cards available
  // when the project is opened directly instead of through Apache/Live Server.
  const localEvents = [
    { title: "Hackathon 2026", date: "12 Sep 2026", description: "24-hour coding competition for student teams.", image: "images/hackthon.jpg", category: "Technical" },
    { title: "AI Workshop", date: "18 Sep 2026", description: "Learn the basics of Artificial Intelligence and Machine Learning.", image: "images/2nd.jpg", category: "Academic" },
    { title: "Career Fair", date: "25 Sep 2026", description: "Meet recruiters and explore internship opportunities.", image: "images/3rd.jpeg", category: "Campus" },
    { title: "Sports Week", date: "02 Oct 2026", description: "Inter-class sports activities and competitions.", image: "images/4th.jpg", category: "Technical" },
    { title: "Cultural Program", date: "10 Oct 2026", description: "A student cultural celebration with performances.", image: "images/1st.png", category: "Academic" },
    { title: "Cloud Computing Seminar", date: "15 Oct 2026", description: "Introduction to cloud computing and modern services.", image: "images/2nd.jpg", category: "Campus" },
    { title: "Web Development Workshop", date: "20 Oct 2026", description: "Hands-on HTML, CSS and JavaScript workshop.", image: "images/3rd.jpeg", category: "Technical" },
    { title: "Coding Contest", date: "28 Oct 2026", description: "Solve programming problems and improve coding skills.", image: "images/4th.jpg", category: "Academic" },
    { title: "Project Exhibition", date: "05 Nov 2026", description: "Showcase innovative student projects.", image: "images/1st.png", category: "Campus" },
    { title: "Cyber Security Awareness", date: "12 Nov 2026", description: "Learn basic online safety and security practices.", image: "images/2nd.jpg", category: "Technical" },
    { title: "Data Science Talk", date: "18 Nov 2026", description: "Explore data analysis and career opportunities.", image: "images/3rd.jpeg", category: "Academic" },
    { title: "Placement Preparation", date: "25 Nov 2026", description: "Resume, aptitude and interview preparation session.", image: "images/4th.jpg", category: "Campus" },
    { title: "Git and GitHub Session", date: "02 Dec 2026", description: "Learn version control and repository management.", image: "images/1st.png", category: "Technical" },
    { title: "Tech Quiz", date: "10 Dec 2026", description: "Test your knowledge of technology and computing.", image: "images/2nd.jpg", category: "Academic" },
    { title: "Annual Student Meet", date: "18 Dec 2026", description: "Student interaction and academic year activities.", image: "images/3rd.jpeg", category: "Campus" }
  ];
  let events = [];
  let page = 1;

  function eventTime(event) {
    // Support ISO dates as well as the existing "12 Sep 2026" data format.
    const iso = /^(\d{4})-(\d{2})-(\d{2})$/.exec(event.date);
    if (iso) return Date.UTC(Number(iso[1]), Number(iso[2]) - 1, Number(iso[3]));

    const match = /^(\d{1,2})\s+([A-Za-z]{3})\s+(\d{4})$/.exec(event.date);
    if (!match) return Number.MAX_SAFE_INTEGER;
    const months = { jan: 0, feb: 1, mar: 2, apr: 3, may: 4, jun: 5, jul: 6, aug: 7, sep: 8, oct: 9, nov: 10, dec: 11 };
    const month = months[match[2].toLowerCase()];
    return month === undefined
      ? Number.MAX_SAFE_INTEGER
      : Date.UTC(Number(match[3]), month, Number(match[1]));
  }

  function populateCategories() {
    const categories = [...new Set(events.map((event) => event.category.trim()))]
      .filter(Boolean)
      .sort((a, b) => a.localeCompare(b));
    filter.replaceChildren(new Option("All Categories", "all"));
    categories.forEach((category) => filter.add(new Option(category, category)));
  }

  function render() {
    const query = search.value.trim().toLocaleLowerCase();
    const category = filter.value;
    const sortOrder = sort.value;
    const results = events.filter((event) => {
      const searchable = [event.title, event.description, event.category].join(" ").toLocaleLowerCase();
      return searchable.includes(query) && (category === "all" || event.category === category);
    }).sort((a, b) => {
      if (sortOrder === "title-asc") return a.title.localeCompare(b.title);
      if (sortOrder === "title-desc") return b.title.localeCompare(a.title);
      const dateOrder = eventTime(a) - eventTime(b);
      return (sortOrder === "date-desc" ? -dateOrder : dateOrder) || a.title.localeCompare(b.title);
    });

    const pageCount = Math.max(1, Math.ceil(results.length / pageSize));
    page = Math.min(page, pageCount);
    list.replaceChildren();

    results.slice((page - 1) * pageSize, page * pageSize).forEach((event) => {
      const card = document.createElement("article");
      card.className = "card event-card";

      if (event.image) {
        const image = document.createElement("img");
        image.src = event.image;
        image.alt = `${event.title} event`;
        image.loading = "lazy";
        image.addEventListener("error", () => image.remove(), { once: true });
        card.append(image);
      }

      const content = document.createElement("div");
      content.className = "event-card-content";
      const category = document.createElement("span");
      category.className = "event-category";
      category.textContent = event.category;
      const title = document.createElement("h3");
      title.textContent = event.title;
      const date = document.createElement("p");
      date.className = "event-date";
      date.textContent = `📅 ${event.date}`;
      const description = document.createElement("p");
      description.textContent = event.description || "More details will be announced soon.";
      content.append(category, title, date, description);
      card.append(content);
      list.append(card);
    });

    if (!results.length) {
      const empty = document.createElement("p");
      empty.className = "event-empty";
      empty.textContent = events.length
        ? "No events match your search. Try another keyword or category."
        : "There are no events to display right now.";
      list.append(empty);
    }

    status.textContent = `${results.length} event${results.length === 1 ? "" : "s"} found`;
    pageLabel.textContent = `Page ${page} of ${pageCount}`;
    previous.disabled = page <= 1;
    next.disabled = page >= pageCount;
  }

  async function loadEvents() {
    status.textContent = "Loading events…";
    try {
      const response = await fetch("Data/events.json", { headers: { Accept: "application/json" } });
      if (!response.ok) throw new Error(`Request failed (${response.status})`);
      const data = await response.json();
      if (!Array.isArray(data)) throw new Error("The event data must be a JSON array.");
      events = data.filter((event) => event && typeof event.title === "string"
        && event.title.trim() && typeof event.date === "string"
        && typeof event.category === "string")
        .map((event) => ({ ...event, title: event.title.trim(), category: event.category.trim() }));
      populateCategories();
      render();
    } catch (error) {
      console.error("Unable to load events from Data/events.json:", error);
      events = localEvents;
      populateCategories();
      render();
      status.textContent = "Showing the included event list. Run the project through Apache or Live Server to load the JSON file.";
    }
  }

  search.addEventListener("input", () => { page = 1; render(); });
  filter.addEventListener("change", () => { page = 1; render(); });
  sort.addEventListener("change", () => { page = 1; render(); });
  previous.addEventListener("click", () => { page = Math.max(1, page - 1); render(); });
  next.addEventListener("click", () => { page += 1; render(); });

  loadEvents();
});
