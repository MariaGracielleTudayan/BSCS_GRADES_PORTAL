<?php
session_start();
require_once 'config.php';

// Check if user is logged in and verified
if (!isset($_SESSION['email']) || !isset($_SESSION['is_verified']) || $_SESSION['is_verified'] != 1) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id'])) {
    $student_id = $_POST['student_id'];
    
    // Get student information
    $stmt = $conn->prepare("SELECT first_name, last_name FROM students WHERE student_id = ?");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($student = $result->fetch_assoc()) {
        $student_name = $student['last_name'] . ', ' . $student['first_name'];
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'student_name' => $student_name
        ]);
    } else {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Student not found'
        ]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request'
    ]);
} 