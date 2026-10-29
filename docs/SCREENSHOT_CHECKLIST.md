# StudentHub Practical Screenshot Checklist

This plan maps the stated Practical 1–14 scope in `README.md` to the existing StudentHub implementation. “Verified” is deliberately conservative: PHP parsing passed, and the README records static/safe-unconfigured checks; live database, browser, account, and API workflows were not run during this inspection.

## Practical 01 — Planning

**Objective/implementation:** sitemap, page inventory, supplied wireframes and project layout. Relevant: `README.md`, `images/home-wireframe.png`, `images/events-wireframe.png`, `images/login-wireframe.png`, `images/wireframe.png`.

| ID / filename | Requirement and screen state | Capture steps and expected evidence | Status |
|---|---|---|---|
| P01-S01 `P01-S01-Sitemap-And-Wireframes.png` | Planning scope, linked routes and low-fidelity design references. | Open README at its sitemap and place/open the supplied wireframes beside it in a file viewer. Capture the diagram/list and wireframe thumbnails. | Partially verified |

## Practical 02 — Semantic HTML and accessibility

**Objective/implementation:** 15 linked semantic pages, landmarks, labelled forms, skip link, headings, image text and focus styling.

| ID / filename | Requirement and screen state | Capture steps and expected evidence | Status |
|---|---|---|---|
| P02-S01 `P02-S01-Home-Navigation-And-Skip-Link.png` | Shared navigation and keyboard skip link. | Open `/index.html`, press Tab once, and capture the visible “Skip to main content” link, header/nav and page heading. | Partially verified |
| P02-S02 `P02-S02-Registration-Labels-And-Focus.png` | Accessible labelled registration controls and focus state. | Open `/register.html`, Tab to a form field, and capture its focus indicator with label and required control visible. | Partially verified |

## Practical 03 — Responsive UI

**Objective/implementation:** mobile-first tokens, Grid/Flex layouts and mobile navigation.

| ID / filename | Requirement and screen state | Capture steps and expected evidence | Status |
|---|---|---|---|
| P03-S01 `P03-S01-Mobile-Home-Navigation.png` | Responsive navigation. | In DevTools device emulation or a narrow browser, open home, press **Menu**, and capture the expanded readable navigation. | Partially verified |
| P03-S02 `P03-S02-Mobile-Events-Card-Layout.png` | Responsive card/control layout. | At the same narrow width, open `/events.html` after cards load and capture search, filter and stacked cards. | Partially verified |

## Practical 04 — JavaScript interactions

**Objective/implementation:** persistent theme/mobile menu, home slider/announcement/dialog, FAQ native details.

| ID / filename | Requirement and screen state | Capture steps and expected evidence | Status |
|---|---|---|---|
| P04-S01 `P04-S01-Dark-Theme.png` | Theme toggle. | On any public page press the moon/sun control and capture the dark page plus changed icon. Reload once before capture if persistence is being demonstrated. | Partially verified |
| P04-S02 `P04-S02-Welcome-Dialog.png` | Home dialog interaction. | On home select the welcome-dialog trigger and capture the open dialog and its close control. | Partially verified |
| P04-S03 `P04-S03-FAQ-Expanded-Answer.png` | Expandable FAQ answer. | Open `/faq.html`, expand one question, and capture its visible answer and question. | Partially verified |

## Practical 05 — Registration UX

**Objective/implementation:** client-side validation for name, email, mobile, strong password, confirmation, course/year/gender and terms; repeated server validation.

| ID / filename | Requirement and screen state | Capture steps and expected evidence | Status |
|---|---|---|---|
| P05-S01 `P05-S01-Registration-Inline-Validation.png` | Client validation. | At `/register.html`, enter a weak password/mismatched confirmation, leave another required selection unset, submit, and capture the inline messages. | Partially verified |
| P05-S02 `P05-S02-Registration-Valid-Form.png` | Complete valid UX state. | Enter fictional valid values, including accepted terms, and capture the form before submit with password strength shown. Do not expose the password value. | Partially verified |

## Practical 06 — JSON views

**Objective/implementation:** Fetch-rendered events/students/FAQs with search, filtering, sorting, paging and empty/error states.

| ID / filename | Requirement and screen state | Capture steps and expected evidence | Status |
|---|---|---|---|
| P06-S01 `P06-S01-Events-Search-Filter-Pagination.png` | Events JSON view. | Open `/events.html` via Apache; search “workshop”, choose a category/sort, then capture result count, cards and pager. | Partially verified |
| P06-S02 `P06-S02-Students-Filter-Sort.png` | Students JSON view. | Open `/students.html`; choose a course and sort order, then capture matching cards, status and pager. | Partially verified |
| P06-S03 `P06-S03-FAQ-Search-And-Answer.png` | FAQ Fetch/search/answer. | Search a keyword on `/faq.html`, expand a remaining answer, and capture status plus answer. | Partially verified |

## Practical 07 — PHP forms and CSV storage

**Objective/implementation:** POST/redirect/GET registration and contact validation; private, locked CSV storage with hashed registration passwords.

| ID / filename | Requirement and screen state | Capture steps and expected evidence | Status |
|---|---|---|---|
| P07-S01 `P07-S01-Registration-Success.png` | PHP registration success. | With Apache running and no database configured, submit valid fictional registration data; capture the redirected success notice. | Blocked — needs Apache test |
| P07-S02 `P07-S02-Registration-Server-Error.png` | Server-side validation. | Bypass/alter a required value only through normal form submission where possible; capture the redirected error notice. | Blocked |
| P07-S03 `P07-S03-Contact-Success.png` | PHP contact success. | Submit valid fictional contact data at `/contact.html`; capture its success notice. | Blocked |
| P07-S04 `P07-S04-CSV-Storage-Redacted.png` | File-storage result and hashing. | In a local file viewer, capture only headers and a redacted/fictional row showing `password_hash` (never its full value); separately verify browser access to storage is denied if permitted by your setup. | Blocked |

## Practical 08 — MySQL schema and PDO

**Objective/implementation:** normalized MySQL schema, PDO connection/prepared statements, joined registrations and `GetStudentRegistrations`.

| ID / filename | Requirement and screen state | Capture steps and expected evidence | Status |
|---|---|---|---|
| P08-S01 `P08-S01-Database-Schema-Relations.png` | Tables, keys and relationships. | In phpMyAdmin after import, capture users/students/events/registrations and registrations indexes/foreign keys; use the README ER diagram as supplemental evidence. | Blocked — MySQL was unreachable in recorded verification |
| P08-S02 `P08-S02-PDO-Connection-Success.png` | Live PDO health check. | Open `/php/db-test.php`; capture the successful connection/prepared-statement message. | Blocked |
| P08-S03 `P08-S03-Seeded-Students-And-Events.png` | Seed data. | In phpMyAdmin capture fictional seeded rows from students and events. | Blocked |
| P08-S04 `P08-S04-PDO-Registration-List.png` | Prepared PDO inserts and joined list. | At `/php/practical-8.php`, add a unique fictional student/event, register the pair, and capture success plus the registration table row. | Blocked |
| P08-S05 `P08-S05-Stored-Procedure-Result.png` | Stored-procedure output/duplicate guard. | Run the procedure for a registered student; capture status/result rows. Optionally submit the same pair again and capture the duplicate message separately. | Blocked |

## Practical 09 — Secure registration

**Objective/implementation:** matching validation, MySQLi prepared inserts, duplicate-email check, hash storage and linked student/user transaction.

| ID / filename | Requirement and screen state | Capture steps and expected evidence | Status |
|---|---|---|---|
| P09-S01 `P09-S01-Database-Registration-Success.png` | Successful secure DB registration. | With MySQL configured, register a new fictional account and capture the success notice; show linked user/student rows in phpMyAdmin only if course requires database evidence. | Blocked |
| P09-S02 `P09-S02-Duplicate-Email-Error.png` | Unique email feedback. | Submit the same fictional email again and capture the explicit duplicate-account notice. | Blocked |

## Practical 10 — Secure login/logout and roles

**Objective/implementation:** password verification, session protection/time-out, student/admin redirect and logout.

| ID / filename | Requirement and screen state | Capture steps and expected evidence | Status |
|---|---|---|---|
| P10-S01 `P10-S01-Login-Failed.png` | Failed credential feedback. | On `/login.html`, submit a real account email with an incorrect password; capture the generic failed-login notice. | Blocked — needs test account |
| P10-S02 `P10-S02-Student-Dashboard.png` | Student role redirect/protected dashboard. | Sign in as registered student; capture welcome text, student links and sign-out link at `/php/dashboard.php`. | Blocked |
| P10-S03 `P10-S03-Admin-Dashboard-Redirect.png` | Administrator role redirect. | Sign in again as a configured admin; capture `/php/admin.php` heading and signed-in context. | Blocked |
| P10-S04 `P10-S04-Logout-Or-Protected-Route.png` | Logout/session access control. | Sign out, then open a protected PHP route; capture the login redirect/notice. The 30-minute expiry needs a timed test or screen recording, not one static image. | Blocked |

## Practical 11 — Student CRUD

**Objective/implementation:** admin-only prepared API create/list/update/deactivate, search/filter, unique constraints and feedback.

| ID / filename | Requirement and screen state | Capture steps and expected evidence | Status |
|---|---|---|---|
| P11-S01 `P11-S01-Student-Create.png` | Create student. | As admin, add unique fictional student in **Student management**; capture the success message and new table row. | Blocked |
| P11-S02 `P11-S02-Student-Search-Filter.png` | Search/filter/list. | Search the created name and select its course; capture query/filter and resulting row. | Blocked |
| P11-S03 `P11-S03-Student-Edit.png` | Update student. | Use Edit, change a non-key value, save, and capture the update message and changed row. | Blocked |
| P11-S04 `P11-S04-Student-Deactivated.png` | Safe delete/deactivation. | Select Delete, capture confirmation if shown; confirm and capture “deactivated; registrations preserved” feedback. | Blocked |

## Practical 12 — Event CRUD and posters

**Objective/implementation:** admin event create/update/delete and MIME/size-checked JPG/PNG/WebP poster upload.

| ID / filename | Requirement and screen state | Capture steps and expected evidence | Status |
|---|---|---|---|
| P12-S01 `P12-S01-Event-Create-With-Poster.png` | Create valid event/poster. | As admin, add a unique fictional event with a small harmless JPG/PNG/WebP; capture success and poster thumbnail/table row. | Blocked |
| P12-S02 `P12-S02-Event-Edit.png` | Edit event. | Edit its title/status/date and capture the update result. | Blocked |
| P12-S03 `P12-S03-Event-Delete.png` | Delete event. | Capture confirmation (if displayed), then completion feedback/list no longer showing the event. | Blocked |
| P12-S04 `P12-S04-Poster-Validation-Error.png` | Invalid upload rejection. | Attempt a non-image or oversized safe test file; capture the validation error. Do not upload executable content. | Blocked |

## Practical 13 — JSON API and Fetch

**Objective/implementation:** admin dashboard consumes student/event/dashboard APIs with Fetch, JSON messages, CSRF and role checks.

| ID / filename | Requirement and screen state | Capture steps and expected evidence | Status |
|---|---|---|---|
| P13-S01 `P13-S01-Fetch-Updated-Student-List.png` | In-page Fetch update. | On admin page create/edit a student, keep the page in place, and capture success plus updated row and totals. Pair with DevTools Network showing the JSON response if required. | Blocked |
| P13-S02 `P13-S02-API-JSON-Response.png` | JSON endpoint result. | While signed in as admin, open DevTools Network after a list request; select the students/events/dashboard response and capture its status/JSON without tokens/cookies. | Blocked |
| P13-S03 `P13-S03-Unauthorized-Or-CSRF-Response.png` | API protection. | Use a student session to request an admin API or let the page token expire and attempt a normal write; capture the 403/419 JSON/visible error. | Blocked |

## Practical 14 — Dynamic administrator dashboard, audit and account status

**Objective/implementation:** admin-only live counts/recent events, controls, audit view and account active/inactive safeguards.

| ID / filename | Requirement and screen state | Capture steps and expected evidence | Status |
|---|---|---|---|
| P14-S01 `P14-S01-Admin-Dashboard-Counts.png` | Dynamic dashboard. | As admin open `/php/admin.php`; wait for counts and recent events, then capture all overview cards and page context. | Blocked |
| P14-S02 `P14-S02-Admin-Only-Controls.png` | Role-based admin controls. | Capture Student management, Event management and User accounts panels under admin session; separately capture a student denied from `/php/admin.php` for access control. | Blocked |
| P14-S03 `P14-S03-Role-Restricted-Access.png` | Unauthorized access. | Signed in as student, navigate directly to `/php/admin.php`; capture the safe denial/redirect. | Blocked |
| P14-S04 `P14-S04-Audit-Log-Change.png` | Audit log. | First make a safe admin create/update/deactivate action, then open **Audit log** and capture newest entry with actor/action/entity/details. | Blocked |
| P14-S05 `P14-S05-Account-Status-Safeguard.png` | Active/inactive account management. | As admin change a noncritical fictional user’s status and capture result; then try self-deactivation or final-admin deactivation and capture the safeguard message. | Blocked |

## Evidence gaps and prerequisites

- Practical statements were located only through the project README; it describes Practical 1–15, and this checklist intentionally stops at Practical 14.
- PHP syntax validation passed for all PHP files using XAMPP PHP. That does not prove database or browser behavior.
- The README’s recorded verification says PDO/MySQLi extensions are available but MySQL at `127.0.0.1:3306` was unreachable; Practical 8–14 require resolving this plus schema import/configuration.
- Practical 7 needs Apache. Practical 9–14 need a fictional student and a locally promoted admin. Practical 12 needs a harmless image and invalid-file test fixture. Practical 14 needs earlier admin actions so audit data exists.
- No UI, schema, records, configuration, or application code was changed for this report; only these documentation files were added.
