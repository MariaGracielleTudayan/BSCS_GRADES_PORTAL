<?php
require 'config.php';
$email = isset($_GET['email']) ? filter_var(trim($_GET['email']), FILTER_VALIDATE_EMAIL) : '';
if (!$email) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        input, button {
            padding: 10px;
            font-size: 16px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <h2>Verify Your OTP</h2>
    <?php if (isset($_GET['error'])): ?>
        <div class="error"><?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>
    <form action="verify_otp.php" method="POST">
        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required readonly>
        <input type="text" name="otp" placeholder="Enter 6-digit OTP" pattern="[0-9]{6}" maxlength="6" required>
        <button type="submit">Verify OTP</button>
    </form>
</body>
</html>