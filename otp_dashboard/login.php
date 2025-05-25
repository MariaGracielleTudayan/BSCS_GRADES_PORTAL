<?php
require 'config.php';

// Check if user is verified
if (isset($_GET['email'])) {
    $email = trim($_GET['email']);
    $check_sql = "SELECT is_verified FROM users WHERE email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0 && $result->fetch_assoc()['is_verified'] == 1) {
        // User is verified - show login form
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Verification Success</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    max-width: 500px;
                    margin: 50px auto;
                    padding: 20px;
                    text-align: center;
                }
                .success {
                    color: #4CAF50;
                    font-size: 24px;
                    margin-bottom: 20px;
                }
                .email {
                    font-weight: bold;
                    margin-bottom: 20px;
                }
                .home-link {
                    display: inline-block;
                    padding: 10px 20px;
                    background-color: #4CAF50;
                    color: white;
                    text-decoration: none;
                    border-radius: 5px;
                }
                .home-link:hover {
                    background-color: #45a049;
                }
            </style>
        </head>
        <body>
            <?php if (isset($_GET['verified']) && isset($_GET['email'])): ?>
                <div class="success">✓ Email Verified Successfully!</div>
                <div class="email"><?php echo htmlspecialchars($_GET['email']); ?></div>
            <?php endif; ?>
            <a href="index.php" class="home-link">Back to Home</a>
        </body>
        </html>
        <?php
    } else {
        header("Location: index.php");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>