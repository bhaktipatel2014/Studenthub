# StudentHub Screenshot Capture Guide

## Before capturing

1. Copy this project to XAMPP `htdocs` and start Apache. Open `http://localhost/StudentHub/`; do not use `file://` for Fetch or PHP evidence.
2. Practicals 1–6 can be captured without MySQL. For Practical 7, submit only fictional data and do not show the `php/storage` URL in a browser.
3. For Practicals 8–14, start MySQL; import `php/schema.sql`; configure the already-local `php/config.local.php`; then open `php/db-test.php`. If upgrading an old schema, run the Practical 10 and 14 migrations once.
4. Register a fictional student account, then promote a chosen account locally to `admin` as described in the README and sign in again. Do not invent credentials or expose the configuration file.
5. Create `screenshots/Practical-01` through `screenshots/Practical-14`. Use the exact suggested filename in the checklist.

## Capture rules

- Use a desktop browser window unless the checklist says mobile. Include the address bar only where the route/HTTP result matters; otherwise include the application title and relevant controls.
- Capture the result after the action: visible inline validation, success/error message, updated table row, count, or audit entry.
- Use distinct fictional names/emails for add/edit tests. Record created rows before removing/deactivating them.
- For destructive-looking actions, show the confirmation dialog before confirming and the post-action message afterward as separate evidence only when both are requested.
- A status marked **Blocked** in the checklist means it was not run by this inspection. Capture it only after the prerequisite works.

## Recommended sequence

Capture P01–P06 first from the public site. Then capture P07 after Apache is working. Complete P08 connection/schema evidence before registering accounts for P09–P10. Use the admin account in this order: P11 student actions, P12 event actions, P13 Fetch/API checks, then P14 dashboard/audit/account-status checks. This ordering leaves audit entries available for the final practical.

## Save convention

`screenshots/Practical-XX/PXX-SYY-Short-Description.png`

Examples: `screenshots/Practical-06/P06-S01-Events-Search-Filter.png` and `screenshots/Practical-14/P14-S04-Audit-Log-Change.png`.
