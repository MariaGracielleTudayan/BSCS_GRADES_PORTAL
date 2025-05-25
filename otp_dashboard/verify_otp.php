<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $otp = $_POST['otp'];

    if (!$email || !$otp) {
        header("Location: verify.php?email=" . urlencode($email) . "&error=Invalid input");
        exit();
    }

    // Check if OTP exists and is valid
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND otp_code = ? AND otp_expiry > NOW()");
    $stmt->bind_param("ss", $email, $otp);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update user as verified
        $update = $conn->prepare("UPDATE users SET is_verified = 1 WHERE email = ?");
        $update->bind_param("s", $email);
        $update->execute();

        // Set session variables
        $_SESSION['email'] = $email;
        $_SESSION['is_verified'] = 1;

        // Redirect to dashboard
        header("Location: dashboard.php");
        exit();
    } else {
        header("Location: verify.php?email=" . urlencode($email) . "&error=Invalid or expired OTP");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?> 