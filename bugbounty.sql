-- Create and use the database
CREATE DATABASE IF NOT EXISTS bugbounty_db;
USE bugbounty_db;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user','admin') DEFAULT 'user',
    streak INT DEFAULT 0,
    last_login DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Content table (Lessons, Tools, Labs descriptions)
CREATE TABLE content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('lesson','tool','lab') NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    body_html LONGTEXT,
    difficulty ENUM('Beginner','Intermediate','Advanced') DEFAULT 'Beginner',
    points INT DEFAULT 10,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Flags for labs
CREATE TABLE lab_flags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content_id INT UNIQUE,
    flag_text VARCHAR(255) NOT NULL,
    FOREIGN KEY (content_id) REFERENCES content(id) ON DELETE CASCADE
);

-- Quizzes
CREATE TABLE quizzes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content_id INT,
    title VARCHAR(255),
    FOREIGN KEY (content_id) REFERENCES content(id) ON DELETE CASCADE
);

-- Questions (MCQ)
CREATE TABLE questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT,
    question TEXT NOT NULL,
    option_a VARCHAR(255),
    option_b VARCHAR(255),
    option_c VARCHAR(255),
    option_d VARCHAR(255),
    correct_answer CHAR(1) CHECK (correct_answer IN ('a','b','c','d')),
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
);

-- User Progress
CREATE TABLE user_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    content_id INT,
    status ENUM('started','completed') DEFAULT 'started',
    score INT DEFAULT 0,
    completed_at DATETIME NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (content_id) REFERENCES content(id) ON DELETE CASCADE,
    UNIQUE KEY (user_id, content_id)
);

-- Badges
CREATE TABLE badges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    icon VARCHAR(50) DEFAULT 'fa-trophy',
    criteria TEXT
);
INSERT INTO badges (name, icon, criteria) VALUES 
('First Blood', 'fa-skull', 'Complete your first lab'),
('Streak Master', 'fa-fire', 'Login for 7 days straight'),
('Quiz Whiz', 'fa-brain', 'Score 100% on a quiz');

-- User Badges
CREATE TABLE user_badges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    badge_id INT,
    earned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE,
    UNIQUE KEY (user_id, badge_id)
);

-- Community Writeups
CREATE TABLE writeups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    title VARCHAR(255) NOT NULL,
    content LONGTEXT NOT NULL,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert default content
INSERT INTO content (type, title, description, body_html, difficulty, points) VALUES
('lesson', 'Understanding HTTP Requests', 'Learn how HTTP works for bug bounty.', '<h2>What is HTTP?</h2><p>HTTP is the foundation...</p>', 'Beginner', 10),
('lab', 'SQL Injection Playground', 'Find the admin password by breaking the search.', '<p>Enter anything into the search box. Try to break it!</p><p><b>Goal:</b> Find the flag (admin password).</p>', 'Beginner', 20),
('tool', 'Nmap Basics', 'Learn to scan ports like a pro.', '<p>Nmap is a network scanner. Use <code>nmap -sV target</code></p>', 'Beginner', 5);

-- Set flag for the lab (ID 2)
INSERT INTO lab_flags (content_id, flag_text) VALUES (2, 'admin_password_123');

-- Insert a quiz for the HTTP lesson (ID 1)
INSERT INTO quizzes (content_id, title) VALUES (1, 'HTTP Basics Quiz');
INSERT INTO questions (quiz_id, question, option_a, option_b, option_c, option_d, correct_answer) VALUES
(1, 'What does HTTP stand for?', 'Hyper Text Transfer Protocol', 'High Tech Transfer Protocol', 'Hyper Transfer Text Protocol', 'None', 'a'),
(1, 'Which HTTP method is used to retrieve data?', 'POST', 'GET', 'PUT', 'DELETE', 'b');