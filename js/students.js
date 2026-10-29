document.addEventListener("DOMContentLoaded", () => {
  const list = document.getElementById("studentList");
  if (!list) return;
  const search = document.getElementById("studentSearch");
  const filter = document.getElementById("courseFilter");
  const sort = document.getElementById("studentSort");
  const status = document.getElementById("studentStatus");
  const previous = document.getElementById("previousStudents");
  const next = document.getElementById("nextStudents");
  const pageLabel = document.getElementById("studentPage");
  const pageSize = 9;
  const sampleStudents = [
    { id: 1, name: "Patel Bhakti", email: "d26dcs149@charusat.edu.in", course: "CE", year: 3, department: "Computer Science" },
    { id: 2, name: "Aarav Shah", email: "aarav.shah@charusat.edu.in", course: "IT", year: 2, department: "Information Technology" },
    { id: 3, name: "Diya Mehta", email: "diya.mehta@charusat.edu.in", course: "CE", year: 1, department: "Computer Science" },
    { id: 4, name: "Rahul Joshi", email: "rahul.joshi@charusat.edu.in", course: "CSE", year: 4, department: "Computer Science" },
    { id: 5, name: "Sneha Patel", email: "sneha.patel@charusat.edu.in", course: "IT", year: 3, department: "Information Technology" },
    { id: 6, name: "Karan Desai", email: "karan.desai@charusat.edu.in", course: "CE", year: 2, department: "Computer Science" },
    { id: 7, name: "Isha Trivedi", email: "isha.trivedi@charusat.edu.in", course: "IT", year: 1, department: "Information Technology" },
    { id: 8, name: "Manav Raval", email: "manav.raval@charusat.edu.in", course: "CSE", year: 3, department: "Computer Science" },
    { id: 9, name: "Pooja Nair", email: "pooja.nair@charusat.edu.in", course: "CE", year: 4, department: "Computer Science" },
    { id: 10, name: "Yash Chauhan", email: "yash.chauhan@charusat.edu.in", course: "IT", year: 2, department: "Information Technology" },
    { id: 11, name: "Riya Gupta", email: "riya.gupta@charusat.edu.in", course: "CSE", year: 1, department: "Computer Science" },
    { id: 12, name: "Harsh Patel", email: "harsh.patel@charusat.edu.in", course: "CE", year: 3, department: "Computer Science" },
    { id: 13, name: "Nidhi Shah", email: "nidhi.shah@charusat.edu.in", course: "IT", year: 4, department: "Information Technology" },
    { id: 14, name: "Vivek Amin", email: "vivek.amin@charusat.edu.in", course: "CSE", year: 2, department: "Computer Science" },
    { id: 15, name: "Ananya Rao", email: "ananya.rao@charusat.edu.in", course: "CE", year: 1, department: "Computer Science" },
    { id: 16, name: "Meet Solanki", email: "meet.solanki@charusat.edu.in", course: "IT", year: 3, department: "Information Technology" }
  ];
  let students = [];
  let page = 1;
  let usingSampleData = false;

  function render() {
    const query = search.value.trim().toLocaleLowerCase();
    const course = filter.value;
    const order = sort.value;
    const matches = students.filter((student) =>
      [student.name, student.email, student.course, student.department, student.year].join(" ").toLocaleLowerCase().includes(query)
      && (course === "all" || student.course === course)
    ).sort((a, b) => {
      if (order === "name-desc") return b.name.localeCompare(a.name);
      if (order === "year-asc") return Number(a.year) - Number(b.year) || a.name.localeCompare(b.name);
      if (order === "year-desc") return Number(b.year) - Number(a.year) || a.name.localeCompare(b.name);
      return a.name.localeCompare(b.name);
    });
    const pageCount = Math.max(1, Math.ceil(matches.length / pageSize));
    page = Math.min(page, pageCount);
    list.replaceChildren();
    matches.slice((page - 1) * pageSize, page * pageSize).forEach((student) => {
      const card = document.createElement("article");
      card.className = "card student-card";
      const top = document.createElement("div");
      top.className = "student-card-top";
      const avatar = document.createElement("span");
      avatar.className = "student-avatar";
      avatar.setAttribute("aria-hidden", "true");
      avatar.textContent = student.name.split(/\s+/).map((part) => part[0]).slice(0, 2).join("").toUpperCase();
      const headingWrap = document.createElement("div");
      const heading = document.createElement("h2");
      heading.textContent = student.name;
      const course = document.createElement("p");
      course.className = "student-course student-course-code";
      course.textContent = student.course;
      const year = document.createElement("span");
      year.className = "student-year";
      year.textContent = `Year ${student.year}`;
      const badges = document.createElement("div");
      badges.className = "student-badges";
      badges.append(course, year);
      headingWrap.append(heading, badges);
      top.append(avatar, headingWrap);
      const meta = document.createElement("div");
      meta.className = "student-meta";
      [["Department", student.department], ["Email", student.email]].forEach(([label, value]) => {
        const line = document.createElement("p");
        const strong = document.createElement("strong");
        strong.textContent = `${label}: `;
        line.append(strong);
        if (label === "Email") {
          const email = document.createElement("a");
          email.className = "student-email";
          email.href = `mailto:${value}`;
          email.textContent = value;
          line.append(email);
        } else {
          line.append(document.createTextNode(value));
        }
        meta.append(line);
      });
      card.append(top, meta);
      list.append(card);
    });
    if (!matches.length) {
      const empty = document.createElement("p");
      empty.className = "event-empty";
      empty.textContent = students.length ? "No students match your search. Try another name or course." : "There are no student records to display right now.";
      list.append(empty);
    }
    status.textContent = usingSampleData
      ? `Showing included sample data · ${matches.length} student${matches.length === 1 ? "" : "s"} found`
      : `${matches.length} student${matches.length === 1 ? "" : "s"} found`;
    pageLabel.textContent = `Page ${page} of ${pageCount}`;
    previous.disabled = page <= 1;
    next.disabled = page >= pageCount;
  }

  async function loadStudents() {
    try {
      const response = await fetch("Data/students.json", { headers: { Accept: "application/json" } });
      if (!response.ok) throw new Error(`Request failed (${response.status})`);
      const data = await response.json();
      if (!Array.isArray(data)) throw new Error("Student data must be a JSON array.");
      students = data.filter((student) => student && typeof student.name === "string" && student.name.trim() && typeof student.email === "string" && typeof student.course === "string" && typeof student.department === "string" && Number.isFinite(Number(student.year)))
        .map((student) => ({ ...student, name: student.name.trim(), course: student.course.trim(), department: student.department.trim(), year: Number(student.year) }));
      if (!students.length) throw new Error("No valid student records were found.");
      populateCourses();
      render();
    } catch (error) {
      // Fetch is blocked for file:// URLs. Keep the practical's sample directory
      // usable when the page is opened directly from a file manager.
      console.info("Using the included student sample because Data/students.json could not be fetched.", error);
      usingSampleData = true;
      students = sampleStudents;
      populateCourses();
      render();
    }
  }
  function populateCourses() {
    [...new Set(students.map((student) => student.course))].sort().forEach((course) => {
      if (![...filter.options].some((option) => option.value === course)) filter.add(new Option(course, course));
    });
  }
  search.addEventListener("input", () => { page = 1; render(); });
  filter.addEventListener("change", () => { page = 1; render(); });
  sort.addEventListener("change", () => { page = 1; render(); });
  previous.addEventListener("click", () => { page = Math.max(1, page - 1); render(); });
  next.addEventListener("click", () => { page += 1; render(); });
  loadStudents();
});
