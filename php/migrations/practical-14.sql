-- Run once only for databases imported before Practical 14.
-- Back up the local database first. Fresh installs should import php/schema.sql.
ALTER TABLE audit_logs
  MODIFY entity_type ENUM('student','event','user','registration') NOT NULL;
