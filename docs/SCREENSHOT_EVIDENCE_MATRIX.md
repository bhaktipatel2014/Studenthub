# StudentHub Screenshot Evidence Matrix

Status meanings: **Verified** = exercised during the recorded project verification; **Partially verified** = source/static inspection plus syntax validation only; **Blocked** = requires local Apache/MySQL, data, or an account. No screenshot has been captured by this report.

| Practical | Requirement | Screenshot ID | Evidence provided | Status |
|---|---|---|---|---|
| 01 | Planning, sitemap and wireframes | P01-S01 | README sitemap and supplied wireframes | Partially verified |
| 02 | Semantic accessible linked pages | P02-S01 | Home page with navigation; keyboard focus/skip link | Partially verified |
| 03 | Responsive mobile-first UI | P03-S01 | Mobile home/navigation view | Partially verified |
| 04 | JavaScript interactions | P04-S01–S03 | Theme, home dialog/notice, FAQ expansion | Partially verified |
| 05 | Registration UX validation | P05-S01–S02 | Inline invalid state and valid form | Partially verified |
| 06 | JSON Fetch views | P06-S01–S03 | Events and students filters/paging; FAQ search | Partially verified |
| 07 | PHP forms and private CSV storage | P07-S01–S04 | Registration/contact success and safe CSV evidence | Blocked |
| 08 | MySQL schema, PDO and stored procedure | P08-S01–S05 | Schema/ER, connection, seeded data, registration/procedure | Blocked |
| 09 | Secure registration | P09-S01–S02 | Successful DB registration; duplicate-email feedback | Blocked |
| 10 | Login, logout and authorization | P10-S01–S04 | Failed login, student dashboard, admin dashboard, logout/protected route | Blocked |
| 11 | Administrator student CRUD | P11-S01–S04 | Create/search-edit/deactivate with feedback | Blocked |
| 12 | Event CRUD and safe posters | P12-S01–S04 | Create/edit/delete; accepted image and rejected invalid image | Blocked |
| 13 | JSON API and Fetch updates | P13-S01–S03 | Fetch-updated list/totals, API JSON, CSRF/role error | Blocked |
| 14 | Admin dashboard, roles, audit and account status | P14-S01–S05 | Dashboard, role menu/access denial, audit log, account safeguards | Blocked |

## Non-static evidence

Screenshots cannot conclusively prove that a password is hashed, an API uses prepared statements, a request is asynchronous, a session ID was regenerated, or a private CSV folder is Apache-protected. Pair the listed visual evidence with the stated local evidence where required: phpMyAdmin structure/data view, browser DevTools Network response, or a safely redacted local CSV/header check. Never capture passwords, credentials, session cookies, or personal records.
