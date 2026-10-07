document.addEventListener("DOMContentLoaded", async function () {
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
  // Keep a small local-file fallback so the page still works when opened
  // directly from Explorer (browsers block fetch() for file:// pages).
  const localEvents = [
    { id: 1, title: "Hackathon 2026", date: "12 Sep 2026", description: "24-hour coding competition for student teams.", image: "images/hackthon.jpg", category: "Technical" },
    { id: 2, title: "AI Workshop", date: "18 Sep 2026", description: "Learn the basics of Artificial Intelligence and Machine Learning.", image: "images/2nd.jpg", category: "Academic" },
    { id: 3, title: "Career Fair", date: "25 Sep 2026", description: "Meet recruiters and explore internship opportunities.", image: "images/3rd.jpeg", category: "Campus" },
    { id: 4, title: "Sports Week", date: "02 Oct 2026", description: "Inter-class sports activities and competitions.", image: "images/4th.jpg", category: "Technical" },
    { id: 5, title: "Cultural Program", date: "10 Oct 2026", description: "A student cultural celebration with performances.", image: "images/1st.png", category: "Academic" },
    { id: 6, title: "Cloud Computing Seminar", date: "15 Oct 2026", description: "Introduction to cloud computing and modern services.", image: "images/2nd.jpg", category: "Campus" },
    { id: 7, title: "Web Development Workshop", date: "20 Oct 2026", description: "Hands-on HTML, CSS and JavaScript workshop.", image: "images/3rd.jpeg", category: "Technical" },
    { id: 8, title: "Coding Contest", date: "28 Oct 2026", description: "Solve programming problems and improve coding skills.", image: "images/4th.jpg", category: "Academic" },
    { id: 9, title: "Project Exhibition", date: "05 Nov 2026", description: "Showcase innovative student projects.", image: "images/1st.png", category: "Campus" },
    { id: 10, title: "Cyber Security Awareness", date: "12 Nov 2026", description: "Learn basic online safety and security practices.", image: "images/2nd.jpg", category: "Technical" },
    { id: 11, title: "Data Science Talk", date: "18 Nov 2026", description: "Explore data analysis and career opportunities.", image: "images/3rd.jpeg", category: "Academic" },
    { id: 12, title: "Placement Preparation", date: "25 Nov 2026", description: "Resume, aptitude and interview preparation session.", image: "images/4th.jpg", category: "Campus" },
    { id: 13, title: "Git and GitHub Session", date: "02 Dec 2026", description: "Learn version control and repository management.", image: "images/1st.png", category: "Technical" },
    { id: 14, title: "Tech Quiz", date: "10 Dec 2026", description: "Test your knowledge of technology and computing.", image: "images/2nd.jpg", category: "Academic" },
    { id: 15, title: "Annual Student Meet", date: "18 Dec 2026", description: "Student interaction and academic year activities.", image: "images/3rd.jpeg", category: "Campus" }
  ];
  let events = [];
  let page = 1;
  let loaded = false;

  function eventTime(event) {
    // The JSON uses dates such as "12 Sep 2026". Parse that format explicitly
    // so sorting is chronological instead of alphabetical by event title.
    const match = /^(\d{1,2})\s+([A-Za-z]{3})\s+(\d{4})$/.exec(event.date);
    if (!match) return Number.MAX_SAFE_INTEGER;
    const months = { jan: 0, feb: 1, mar: 2, apr: 3, may: 4, jun: 5, jul: 6, aug: 7, sep: 8, oct: 9, nov: 10, dec: 11 };
    const month = months[match[2].toLowerCase()];
    return month === undefined
      ? Number.MAX_SAFE_INTEGER
      : Date.UTC(Number(match[3]), month, Number(match[1]));
  }

  function render() {
    if (!loaded) return;
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
      card.className = "card";
      const image = document.createElement("img");
      image.src = event.image;
      image.alt = event.title + " event";
      image.loading = "lazy";
      image.addEventListener("error", () => { image.hidden = true; }, { once: true });
      const title = document.createElement("h3");
      title.textContent = event.title;
      const date = document.createElement("p");
      date.textContent = "Date: " + event.date;
      const description = document.createElement("p");
      description.textContent = event.description;
      const label = document.createElement("p");
      label.textContent = "Category: " + event.category;
      card.append(image, title, date, description, label);
      list.append(card);
    });
    if (!results.length) {
      const empty = document.createElement("p");
      empty.textContent = "No events match your search. Try another keyword or category.";
      list.append(empty);
    }
    status.textContent = results.length + " event" + (results.length === 1 ? "" : "s") + " found.";
    pageLabel.textContent = "Page " + page + " of " + pageCount;
    previous.disabled = page <= 1;
    next.disabled = page >= pageCount;
  }

  try {
    const response = await fetch("Data/events.json");
    if (!response.ok) throw new Error("Events are unavailable.");
    const data = await response.json();
    if (!Array.isArray(data)) throw new Error("Event data has an invalid format.");
    events = data.filter((event) => event && event.title && event.date && event.category);
    loaded = true;
    render();
  } catch (error) {
    events = localEvents;
    loaded = true;
    render();
    status.textContent = "Showing the included event list. Run the project through a web server to load the latest event data.";
  }
  search.addEventListener("input", () => { page = 1; render(); });
  filter.addEventListener("change", () => { page = 1; render(); });
  sort.addEventListener("change", () => { page = 1; render(); });
  previous.addEventListener("click", () => { page = Math.max(1, page - 1); render(); });
  next.addEventListener("click", () => { page += 1; render(); });
});
