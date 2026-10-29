-- One-time upgrade for a pre-Practical-8 StudentHub database.
-- Preserves legacy tables rather than deleting them, then creates the schema
-- required by the current registration, login, CRUD, and audit features.
-- Run only when the three legacy_* tables do not already exist.

USE studenthub;

SET FOREIGN_KEY_CHECKS = 0;
RENAME TABLE
  registrations TO legacy_registrations,
  students TO legacy_students,
  events TO legacy_events;
SET FOREIGN_KEY_CHECKS = 1;
