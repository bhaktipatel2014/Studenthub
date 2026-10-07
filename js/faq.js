document.addEventListener("DOMContentLoaded", async function () {
  const list = document.getElementById("faqList");
  const search = document.getElementById("faqSearch");
  const status = document.getElementById("faqStatus");
  // Browsers block fetch() for file:// pages, so keep the common answers
  // available when students open this static project directly.
  const localFaqs = [
    { question: "What is StudentHub?", answer: "StudentHub is a campus portal that brings student information, academic tools, notices, and events together in one place." },
    { question: "How do I create a StudentHub account?", answer: "Choose Create account in the navigation, complete the required fields, and submit the registration form. The page will show whether the submission succeeded or needs attention." },
    { question: "How do I sign in?", answer: "Open Sign in and enter the email address and password associated with your account." },
    { question: "I cannot sign in. What should I do?", answer: "Check that your email and password are entered correctly. If you still cannot access your account, use the Contact page to ask the StudentHub team for help. Do not send your password." },
    { question: "What can I do in StudentHub?", answer: "You can view your profile, attendance, timetable, and assignments, and browse college notices and events." },
    { question: "Where can I find my attendance?", answer: "Choose Attendance from the Academics menu in the navigation bar." },
    { question: "Where can I find my timetable?", answer: "Choose Timetable from the Academics menu to view the weekly schedule." },
    { question: "Where can I find assignments?", answer: "Choose Assignments from the Academics menu." },
    { question: "Where can I view my profile?", answer: "Choose Profile from the Academics menu to review the student information shown on your account." },
    { question: "Where can I find the latest college notices?", answer: "The home page shows a Latest Notices preview. Choose View all notices or open Notices from the Campus menu to see the full list." },
    { question: "How do I browse college events?", answer: "Open Events from the Campus menu. You can search by event name or description and filter events by category." },
    { question: "How do I search for a specific event?", answer: "On the Events page, enter a keyword in Search Events. You can combine the search with a category filter." },
    { question: "Can I use StudentHub on my phone?", answer: "Yes. The pages adapt to smaller screens, and the Menu button opens the navigation on mobile." },
    { question: "How do I switch between light and dark themes?", answer: "Use the theme button beside the navigation. Your preference is saved in this browser." },
    { question: "How can I contact the StudentHub team?", answer: "Open Contact from the Support menu and use the contact form to send your question." },
    { question: "How can I share feedback about the portal?", answer: "Open Feedback from the Support menu and submit the feedback form." },
    { question: "What if my registration form shows an error?", answer: "Review the fields marked on the form and correct any missing or invalid information before submitting again." },
    { question: "How do I update incorrect profile information?", answer: "Review your details on the Profile page. If information needs correction, contact your college or StudentHub administrator through the appropriate support channel." }
  ];
  let faqs = [];
  function render() {
    const query = search.value.trim().toLocaleLowerCase();
    const matches = faqs.filter((item) => (item.question + " " + item.answer).toLocaleLowerCase().includes(query));
    list.replaceChildren();
    matches.forEach((item) => {
      const details = document.createElement("details");
      const summary = document.createElement("summary");
      const answer = document.createElement("p");
      summary.textContent = item.question;
      answer.textContent = item.answer;
      details.append(summary, answer);
      list.append(details);
    });
    if (!matches.length) {
      const empty = document.createElement("p");
      empty.textContent = "No answers match that search. Try a different phrase or contact us.";
      list.append(empty);
    }
    status.textContent = matches.length + " answer" + (matches.length === 1 ? "" : "s");
  }
  try {
    const response = await fetch("Data/faqs.json");
    if (!response.ok) throw new Error("FAQ data is unavailable.");
    faqs = await response.json();
    render();
  } catch (error) {
    faqs = localFaqs;
    render();
    status.textContent = "Showing the included FAQ answers. Run the project through a web server to load the latest FAQ data.";
  }
  search.addEventListener("input", render);
});
