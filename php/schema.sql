-- StudentHub_Bhakti database schema (XAMPP MySQL/MariaDB compatible).
-- Safe to import into a NEW database. It never drops tables or data.

CREATE DATABASE IF NOT EXISTS studenthub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE studenthub;

CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  full_name VARCHAR(100) NOT NULL, email VARCHAR(190) NOT NULL,
  mobile VARCHAR(20) NOT NULL, course VARCHAR(80) NOT NULL,
  year_level VARCHAR(20) NOT NULL, gender VARCHAR(30) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('student','admin') NOT NULL DEFAULT 'student',
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  last_login TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id), UNIQUE KEY uq_users_email (email), KEY idx_users_role_status (role,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS students (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NULL, student_code VARCHAR(24) NOT NULL,
  full_name VARCHAR(100) NOT NULL, email VARCHAR(190) NOT NULL, phone VARCHAR(30) NULL,
  course VARCHAR(80) NOT NULL, year_level TINYINT UNSIGNED NOT NULL,
  department VARCHAR(100) NOT NULL, status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id), UNIQUE KEY uq_students_code (student_code),
  UNIQUE KEY uq_students_email (email), UNIQUE KEY uq_students_user (user_id),
  KEY idx_students_course_year (course,year_level), KEY idx_students_status (status),
  CONSTRAINT fk_students_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS events (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(160) NOT NULL, description TEXT NOT NULL, event_date DATE NOT NULL,
  location VARCHAR(160) NULL, category VARCHAR(60) NOT NULL, poster_path VARCHAR(255) NULL,
  status ENUM('open','closed') NOT NULL DEFAULT 'open',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id), UNIQUE KEY uq_events_title_date (title,event_date),
  KEY idx_events_date (event_date), KEY idx_events_category_date (category,event_date), KEY idx_events_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS registrations (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  student_id BIGINT UNSIGNED NOT NULL, event_id BIGINT UNSIGNED NOT NULL,
  status ENUM('registered','cancelled') NOT NULL DEFAULT 'registered',
  registered_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id), UNIQUE KEY uq_registrations_student_event (student_id,event_id),
  KEY idx_registrations_event_date (event_id,registered_at), KEY idx_registrations_student_date (student_id,registered_at),
  CONSTRAINT fk_registrations_student FOREIGN KEY (student_id) REFERENCES students(id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_registrations_event FOREIGN KEY (event_id) REFERENCES events(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_logs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, user_id BIGINT UNSIGNED NOT NULL,
  action ENUM('create','update','delete') NOT NULL,
  entity_type ENUM('student','event','user','registration') NOT NULL,
  entity_id BIGINT UNSIGNED NOT NULL, details VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id), KEY idx_audit_created (created_at), KEY idx_audit_entity (entity_type,entity_id),
  CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Fictional seed students. INSERT IGNORE allows safe re-imports.
INSERT IGNORE INTO students (student_code,full_name,email,phone,course,year_level,department) VALUES
('STU2026001','Aarav Shah','aarav.shah@example.edu',NULL,'B.Tech IT',2,'Information Technology'),
('STU2026002','Diya Mehta','diya.mehta@example.edu',NULL,'B.Tech CE',1,'Computer Engineering'),
('STU2026003','Rahul Joshi','rahul.joshi@example.edu',NULL,'B.Tech CSE',4,'Computer Science'),
('STU2026004','Sneha Patel','sneha.patel@example.edu',NULL,'B.Tech IT',3,'Information Technology'),
('STU2026005','Karan Desai','karan.desai@example.edu',NULL,'B.Tech CE',2,'Computer Engineering'),
('STU2026006','Isha Trivedi','isha.trivedi@example.edu',NULL,'B.Tech IT',1,'Information Technology'),
('STU2026007','Manav Raval','manav.raval@example.edu',NULL,'B.Tech CSE',3,'Computer Science'),
('STU2026008','Pooja Nair','pooja.nair@example.edu',NULL,'B.Tech CE',4,'Computer Engineering'),
('STU2026009','Yash Chauhan','yash.chauhan@example.edu',NULL,'B.Tech IT',2,'Information Technology'),
('STU2026010','Riya Gupta','riya.gupta@example.edu',NULL,'B.Tech CSE',1,'Computer Science'),
('STU2026011','Harsh Patel','harsh.patel@example.edu',NULL,'B.Tech CE',3,'Computer Engineering'),
('STU2026012','Nidhi Shah','nidhi.shah@example.edu',NULL,'B.Tech IT',4,'Information Technology'),
('STU2026013','Vivek Amin','vivek.amin@example.edu',NULL,'B.Tech CSE',2,'Computer Science'),
('STU2026014','Ananya Rao','ananya.rao@example.edu',NULL,'B.Tech CE',1,'Computer Engineering'),
('STU2026015','Meet Solanki','meet.solanki@example.edu',NULL,'B.Tech IT',3,'Information Technology'),
('STU2026016','Kavya Desai','kavya.desai@example.edu',NULL,'B.Tech CSE',2,'Computer Science'),
('STU2026017','Dev Patel','dev.patel@example.edu',NULL,'B.Tech IT',1,'Information Technology'),
('STU2026018','Mira Shah','mira.shah@example.edu',NULL,'B.Tech CE',4,'Computer Engineering'),
('STU2026019','Rohan Mehta','rohan.mehta@example.edu',NULL,'B.Tech CSE',3,'Computer Science'),
('STU2026020','Tara Joshi','tara.joshi@example.edu',NULL,'B.Tech IT',2,'Information Technology');

INSERT IGNORE INTO events (title,description,event_date,location,category,status) VALUES
('Hackathon 2026','24-hour student coding competition.','2026-09-12','Innovation Lab','Technical','open'),
('AI Workshop','Explore practical artificial intelligence.','2026-09-18','Seminar Hall','Academic','open'),
('Career Fair','Meet recruiters and learn about internships.','2026-09-25','Campus Ground','Campus','open'),
('Sports Week','Inter-class sports and competitions.','2026-10-02','Sports Complex','Campus','open'),
('Cultural Program','Student performances and celebration.','2026-10-10','Auditorium','Campus','open'),
('Cloud Computing Seminar','Introduction to cloud platforms.','2026-10-15','Seminar Hall','Academic','open'),
('Web Development Workshop','Hands-on HTML, CSS and JavaScript.','2026-10-20','Computer Lab','Technical','open'),
('Coding Contest','Solve programming challenges with peers.','2026-10-28','Computer Lab','Technical','open'),
('Project Exhibition','Showcase student projects.','2026-11-05','Exhibition Hall','Academic','open'),
('Cyber Safety Awareness','Learn practical online safety.','2026-11-12','Auditorium','Academic','open'),
('Data Science Talk','Explore data careers and analysis.','2026-11-18','Seminar Hall','Academic','open'),
('Placement Preparation','Resume, aptitude and interview practice.','2026-11-25','Career Centre','Campus','open'),
('Git and GitHub Session','Learn version control fundamentals.','2026-12-02','Computer Lab','Technical','open'),
('Technology Quiz','A friendly campus technology quiz.','2026-12-10','Auditorium','Campus','open'),
('Annual Student Meet','Connect with the student community.','2026-12-18','Campus Ground','Campus','open');

INSERT IGNORE INTO registrations (student_id,event_id)
SELECT s.id,e.id FROM students AS s JOIN events AS e ON e.title='Hackathon 2026'
WHERE s.student_code IN ('STU2026001','STU2026002','STU2026003','STU2026004','STU2026005');

DROP PROCEDURE IF EXISTS GetStudentRegistrations;
CREATE PROCEDURE GetStudentRegistrations(IN p_student_id BIGINT UNSIGNED)
SELECT r.id AS registration_id,r.status,r.registered_at,s.full_name,s.email,
       e.title,e.description,e.event_date,e.location,e.category
FROM registrations AS r JOIN students AS s ON s.id=r.student_id JOIN events AS e ON e.id=r.event_id
WHERE r.student_id=p_student_id ORDER BY e.event_date,r.registered_at;
