document.addEventListener("DOMContentLoaded", () => {
  const token = document.querySelector('meta[name="csrf-token"]')?.content || "";
  const studentForm = document.getElementById("studentForm");
  const eventForm = document.getElementById("eventForm");
  const studentRows = document.getElementById("studentRows");
  const eventRows = document.getElementById("eventRows");
  const userRows = document.getElementById("userRows");
  const message = (id, value) => { document.getElementById(id).textContent = value; };

  async function request(url, options = {}) {
    const headers = new Headers(options.headers || {});
    headers.set("X-CSRF-Token", token);
    headers.set("Accept", "application/json");
    const response = await fetch(url, { ...options, headers, credentials: "same-origin" });
    const data = await response.json().catch(() => ({}));
    if (!response.ok) throw new Error(data.error || `Request failed (${response.status})`);
    return data;
  }
  const cell = (value) => { const td = document.createElement("td"); td.textContent = value ?? ""; return td; };
  function actionButton(label, callback, danger = false) {
    const button = document.createElement("button");
    button.type = "button"; button.textContent = label;
    if (danger) button.className = "delete";
    button.addEventListener("click", callback);
    return button;
  }

  async function loadStudents() {
    try {
      const query = new URLSearchParams({ q: document.getElementById("studentQuery").value, course: document.getElementById("studentCourseFilter").value });
      const data = await request(`api/students.php?${query}`);
      studentRows.replaceChildren();
      data.students.forEach((student) => {
        const row = document.createElement("tr");
        row.append(cell(`${student.name} · ${student.student_code}\n${student.email}`), cell(`${student.course} · Year ${student.year}`));
        const actions = document.createElement("td"); actions.className = "admin-actions";
        actions.append(actionButton("Edit", () => {
          for (const [field, value] of Object.entries({ id: student.id, student_code: student.student_code, name: student.name, email: student.email, course: student.course, year: student.year, department: student.department, status: student.status })) studentForm.elements[field].value = value;
          studentForm.scrollIntoView({ behavior: "smooth", block: "center" });
        }), actionButton("Delete", async () => {
          if (!window.confirm(`Delete ${student.name}'s record?`)) return;
          try { const result = await request("api/students.php", { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ action: "delete", id: student.id }) }); message("studentMessage", result.message); await loadStudents(); await loadDashboard(); }
          catch (error) { message("studentMessage", error.message); }
        }, true));
        row.append(actions); studentRows.append(row);
      });
    } catch (error) { message("studentMessage", error.message); }
  }
  studentForm.addEventListener("submit", async (event) => {
    event.preventDefault();
    const values = Object.fromEntries(new FormData(studentForm));
    values.action = values.id ? "update" : "create";
    values.year = Number(values.year);
    try { const result = await request("api/students.php", { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify(values) }); message("studentMessage", result.message); studentForm.reset(); await loadStudents(); await loadDashboard(); }
    catch (error) { message("studentMessage", error.message); }
  });
  studentForm.addEventListener("reset", () => { setTimeout(() => { studentForm.elements.id.value = ""; }, 0); });

  async function loadEvents() {
    try {
      const data = await request("api/events.php");
      eventRows.replaceChildren();
      data.events.forEach((event) => {
        const row = document.createElement("tr");
        row.append(cell(event.title), cell(event.date));
        const posterCell = document.createElement("td");
        if (typeof event.poster_path === "string" && event.poster_path.startsWith("php/uploads/events/")) {
          const image = document.createElement("img"); image.className = "poster-thumb"; image.alt = `${event.title} poster`; image.src = `../${event.poster_path}`; posterCell.append(image);
        } else posterCell.textContent = "—";
        row.append(posterCell);
        const actions = document.createElement("td"); actions.className = "admin-actions";
        actions.append(actionButton("Edit", () => {
          for (const [field, value] of Object.entries({ id: event.id, title: event.title, description: event.description, date: event.date, category: event.category, status: event.status })) eventForm.elements[field].value = value;
          eventForm.scrollIntoView({ behavior: "smooth", block: "center" });
        }), actionButton("Delete", async () => {
          if (!window.confirm(`Delete “${event.title}”?`)) return;
          const body = new FormData(); body.set("action", "delete"); body.set("id", event.id);
          try { const result = await request("api/events.php", { method: "POST", body }); message("eventMessage", result.message); await loadEvents(); await loadDashboard(); }
          catch (error) { message("eventMessage", error.message); }
        }, true));
        row.append(actions); eventRows.append(row);
      });
    } catch (error) { message("eventMessage", error.message); }
  }
  async function loadUsers() {
    try {
      const data = await request("api/users.php");
      userRows.replaceChildren();
      data.data.users.forEach((user) => {
        const row = document.createElement("tr");
        row.append(cell(`${user.full_name}\n${user.email}`), cell(user.role), cell(user.status));
        const actions = document.createElement("td"); actions.className = "admin-actions";
        const nextStatus = user.status === "active" ? "inactive" : "active";
        actions.append(actionButton(nextStatus === "inactive" ? "Deactivate" : "Activate", async () => {
          if (nextStatus === "inactive" && !window.confirm(`Deactivate ${user.full_name}'s account?`)) return;
          try { const result = await request("api/users.php", { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ id: user.id, status: nextStatus }) }); message("userMessage", result.message); await loadUsers(); await loadDashboard(); }
          catch (error) { message("userMessage", error.message); }
        }, nextStatus === "inactive"));
        row.append(actions); userRows.append(row);
      });
    } catch (error) { message("userMessage", error.message); }
  }
  eventForm.addEventListener("submit", async (event) => {
    event.preventDefault();
    const body = new FormData(eventForm); body.set("action", body.get("id") ? "update" : "create");
    try { const result = await request("api/events.php", { method: "POST", body }); message("eventMessage", result.message); eventForm.reset(); await loadEvents(); await loadDashboard(); }
    catch (error) { message("eventMessage", error.message); }
  });
  eventForm.addEventListener("reset", () => { setTimeout(() => { eventForm.elements.id.value = ""; }, 0); });

  async function loadDashboard() {
    try {
      const data = await request("api/dashboard.php");
      for (const key of ["students", "events", "registrations", "users"]) document.getElementById(`count${key[0].toUpperCase()}${key.slice(1)}`).textContent = data.counts[key];
      const recent = document.getElementById("recentEvents"); recent.replaceChildren();
      data.recent_events.forEach((event) => { const li = document.createElement("li"); li.textContent = `${event.title} · ${event.event_date} · ${event.status}`; recent.append(li); });
      if (!data.recent_events.length) recent.textContent = "No events have been added yet.";
      message("dashboardMessage", "Live totals loaded from the database.");
    } catch (error) { message("dashboardMessage", error.message); }
  }
  let searchTimer;
  document.getElementById("studentQuery").addEventListener("input", () => { clearTimeout(searchTimer); searchTimer = setTimeout(loadStudents, 220); });
  document.getElementById("studentCourseFilter").addEventListener("change", loadStudents);
  loadStudents(); loadEvents(); loadUsers(); loadDashboard();
});
