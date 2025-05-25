<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "user_verification";

// Create connection without selecting database first
$conn = new mysqli($host, $user, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $database";
if (!$conn->query($sql)) {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($database);

// Create users table if not exists
$users_table_sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    otp_code VARCHAR(6) NOT NULL,
    otp_expiry DATETIME NOT NULL,
    is_verified TINYINT(1) DEFAULT 0,
    UNIQUE KEY (email)
)";

if (!$conn->query($users_table_sql)) {
    die("Error creating users table: " . $conn->error);
}

// Create students table if not exists
$students_table_sql = "CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    course VARCHAR(100) NOT NULL,
    year_level VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (!$conn->query($students_table_sql)) {
    die("Error creating students table: " . $conn->error);
}

// Create subjects table if not exists
$subjects_table_sql = "CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_code VARCHAR(20) NOT NULL UNIQUE,
    subject_name VARCHAR(255) NOT NULL,
    units INT NOT NULL,
    description TEXT,
    year_level VARCHAR(20) NOT NULL,
    semester VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (!$conn->query($subjects_table_sql)) {
    die("Error creating subjects table: " . $conn->error);
}

// Create grades table if not exists
$grades_table_sql = "CREATE TABLE IF NOT EXISTS grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) NOT NULL,
    subject_code VARCHAR(20) NOT NULL,
    midterm_grade DECIMAL(5,2),
    final_grade DECIMAL(5,2),
    semester VARCHAR(20) NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY (student_id, subject_code, semester, academic_year),
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_code) REFERENCES subjects(subject_code) ON DELETE CASCADE
)";

if (!$conn->query($grades_table_sql)) {
    die("Error creating grades table: " . $conn->error);
}

// Set timezone
date_default_timezone_set('Asia/Manila');
?>