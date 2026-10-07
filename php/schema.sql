-- StudentHub schema for Practical 8 and Practical 9 (MySQL 8+).
CREATE DATABASE IF NOT EXISTS studenthub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE studenthub;

CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL,
  mobile CHAR(10) NOT NULL,
  course VARCHAR(80) NOT NULL,
  year_level VARCHAR(20) NOT NULL,
  gender VARCHAR(30) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('student','admin') NOT NULL DEFAULT 'student',
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS students (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NULL,
  student_code VARCHAR(24) NOT NULL,
  full_name VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL,
  course VARCHAR(80) NOT NULL,
  year_level TINYINT UNSIGNED NOT NULL,
  department VARCHAR(100) NOT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_students_code (student_code),
  UNIQUE KEY uq_students_email (email),
  UNIQUE KEY uq_students_user (user_id),
  CONSTRAINT fk_students_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS events (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(160) NOT NULL,
  description TEXT NOT NULL,
  event_date DATE NOT NULL,
  category VARCHAR(60) NOT NULL,
  poster_path VARCHAR(255) NULL,
  status ENUM('open','closed') NOT NULL DEFAULT 'open',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_events_title_date (title, event_date)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS registrations (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  student_id BIGINT UNSIGNED NOT NULL,
  event_id BIGINT UNSIGNED NOT NULL,
  registered_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_event_student (student_id, event_id),
  CONSTRAINT fk_registrations_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_registrations_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Sample SQL seed data (20 students, 15 events and 5 event registrations).
INSERT INTO students (student_code, full_name, email, course, year_level, department) VALUES
('STU2026001','Aarav Shah','aarav.shah@example.edu','B.Tech IT',2,'Information Technology'),
('STU2026002','Diya Mehta','diya.mehta@example.edu','B.Tech CE',1,'Computer Engineering'),
('STU2026003','Rahul Joshi','rahul.joshi@example.edu','B.Tech CSE',4,'Computer Science'),
('STU2026004','Sneha Patel','sneha.patel@example.edu','B.Tech IT',3,'Information Technology'),
('STU2026005','Karan Desai','karan.desai@example.edu','B.Tech CE',2,'Computer Engineering'),
('STU2026006','Isha Trivedi','isha.trivedi@example.edu','B.Tech IT',1,'Information Technology'),
('STU2026007','Manav Raval','manav.raval@example.edu','B.Tech CSE',3,'Computer Science'),
('STU2026008','Pooja Nair','pooja.nair@example.edu','B.Tech CE',4,'Computer Engineering'),
('STU2026009','Yash Chauhan','yash.chauhan@example.edu','B.Tech IT',2,'Information Technology'),
('STU2026010','Riya Gupta','riya.gupta@example.edu','B.Tech CSE',1,'Computer Science'),
('STU2026011','Harsh Patel','harsh.patel@example.edu','B.Tech CE',3,'Computer Engineering'),
('STU2026012','Nidhi Shah','nidhi.shah@example.edu','B.Tech IT',4,'Information Technology'),
('STU2026013','Vivek Amin','vivek.amin@example.edu','B.Tech CSE',2,'Computer Science'),
('STU2026014','Ananya Rao','ananya.rao@example.edu','B.Tech CE',1,'Computer Engineering'),
('STU2026015','Meet Solanki','meet.solanki@example.edu','B.Tech IT',3,'Information Technology'),
('STU2026016','Kavya Desai','kavya.desai@example.edu','B.Tech CSE',2,'Computer Science'),
('STU2026017','Dev Patel','dev.patel@example.edu','B.Tech IT',1,'Information Technology'),
('STU2026018','Mira Shah','mira.shah@example.edu','B.Tech CE',4,'Computer Engineering'),
('STU2026019','Rohan Mehta','rohan.mehta@example.edu','B.Tech CSE',3,'Computer Science'),
('STU2026020','Tara Joshi','tara.joshi@example.edu','B.Tech IT',2,'Information Technology')
ON DUPLICATE KEY UPDATE full_name=VALUES(full_name);

INSERT INTO events (title, description, event_date, category, status) VALUES
('Hackathon 2026','24-hour student coding competition.','2026-09-12','Technical','open'),
('AI Workshop','Explore practical artificial intelligence.','2026-09-18','Academic','open'),
('Career Fair','Meet recruiters and learn about internships.','2026-09-25','Campus','open'),
('Sports Week','Inter-class sports and competitions.','2026-10-02','Campus','open'),
('Cultural Program','Student performances and celebration.','2026-10-10','Campus','open'),
('Cloud Computing Seminar','Introduction to cloud platforms.','2026-10-15','Academic','open'),
('Web Development Workshop','Hands-on HTML, CSS and JavaScript.','2026-10-20','Technical','open'),
('Coding Contest','Solve programming challenges with peers.','2026-10-28','Technical','open'),
('Project Exhibition','Showcase student projects.','2026-11-05','Academic','open'),
('Cyber Safety Awareness','Learn practical online safety.','2026-11-12','Academic','open'),
('Data Science Talk','Explore data careers and analysis.','2026-11-18','Academic','open'),
('Placement Preparation','Resume, aptitude and interview practice.','2026-11-25','Campus','open'),
('Git and GitHub Session','Learn version control fundamentals.','2026-12-02','Technical','open'),
('Technology Quiz','A friendly campus technology quiz.','2026-12-10','Campus','open'),
('Annual Student Meet','Connect with the student community.','2026-12-18','Campus','open')
ON DUPLICATE KEY UPDATE description=VALUES(description), category=VALUES(category), status=VALUES(status);

INSERT IGNORE INTO registrations (student_id, event_id)
SELECT s.id, e.id FROM students s JOIN events e ON e.title='Hackathon 2026'
WHERE s.student_code IN ('STU2026001','STU2026002','STU2026003','STU2026004','STU2026005');

-- Practical 9 demo users should be created through register.php to use password_hash().
-- To promote an account after signup: UPDATE users SET role='admin' WHERE email='admin@example.com';
