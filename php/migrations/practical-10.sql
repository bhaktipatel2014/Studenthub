-- Run once in phpMyAdmin only if the database was imported before Practical 10.
ALTER TABLE users ADD COLUMN last_login TIMESTAMP NULL DEFAULT NULL;
