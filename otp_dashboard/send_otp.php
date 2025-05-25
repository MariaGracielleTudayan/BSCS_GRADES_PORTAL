<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'config.php';

// Check if database connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    if (!$email) {
        die("Invalid email format. Please enter a valid email address.");
    }

    // Generate OTP
    $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $otp_expiry = date("Y-m-d H:i:s", strtotime("+15 minutes"));

    try {
        // Check if email exists in users table
        $check_sql = "SELECT id FROM users WHERE email = ?";
        $check_stmt = $conn->prepare($check_sql);
        if (!$check_stmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            // Update existing OTP
            $sql = "UPDATE users SET otp_code = ?, otp_expiry = ? WHERE email = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Database error: " . $conn->error);
            }
            $stmt->bind_param("sss", $otp, $otp_expiry, $email);
        } else {
            // Insert new OTP
            $sql = "INSERT INTO users (email, otp_code, otp_expiry) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Database error: " . $conn->error);
            }
            $stmt->bind_param("sss", $email, $otp, $otp_expiry);
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to store OTP: " . $stmt->error);
        }

        // Send email
        $mail = new PHPMailer(true);

        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'gracielletudayan0526@gmail.com';
        $mail->Password = 'syut kwzd nuas mnzk';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('gracielletudayan0526@gmail.com', 'BSCS Grades Portal');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Code - BSCS Grades Portal';
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>
                <h2 style='color: #2c3e50; text-align: center;'>OTP Verification</h2>
                <p style='color: #34495e;'>Hello,</p>
                <p style='color: #34495e;'>Your OTP code for the BSCS Grades Portal is:</p>
                <div style='background-color: #f8f9fa; padding: 15px; text-align: center; margin: 20px 0; border-radius: 5px;'>
                    <span style='font-size: 24px; font-weight: bold; color: #2c3e50;'>{$otp}</span>
                </div>
                <p style='color: #34495e;'><strong>Important:</strong></p>
                <ul style='color: #34495e;'>
                    <li>This code will expire in 15 minutes</li>
                    <li>Do not share this code with anyone</li>
                    <li>If you didn't request this code, please ignore this email</li>
                </ul>
                <p style='color: #34495e;'>Best regards,<br>BSCS Grades Portal Team</p>
            </div>
        ";

        $mail->send();
        
        // Set session variables
        $_SESSION['email'] = $email;
        $_SESSION['otp_sent'] = true;
        
        // Redirect to verify page
        header("Location: verify.php?email=" . urlencode($email));
        exit();

    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}
?>