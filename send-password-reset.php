<?php

session_start(); // Start the session to store errors

require __DIR__ . "/database.php"; // gives you $conn from mysqli_connect()

$email = $_POST["email"];

// Validate email (recommended)
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['errors'] = ['Invalid email format.'];
    header("Location: forgot-password.php");
    exit();
}

// Generate token and hash
$token = bin2hex(random_bytes(16));
$token_hash = hash("sha256", $token);
$expiry = date("Y-m-d H:i:s", time() + 60 * 30); // 30 mins from now

// Prepare and bind
$sql = "UPDATE users
        SET reset_token_hash = ?,
            reset_token_expires_at = ?
        WHERE email = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "sss", $token_hash, $expiry, $email);
mysqli_stmt_execute($stmt);
    
if (mysqli_affected_rows($conn)) {
    $mail = require __DIR__ . "/mailer.php";

    $mail->setFrom("noreply@technovation.com");
    $mail->addAddress($email);
    $mail->Subject = "Password Reset";
    $mail->isHTML(true); // Important for HTML content
    $mail->Body = <<<END
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 700px;
            margin: 20px auto;
            background-color: rgb(233, 233, 233);
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            font-size: 16px;
            padding-bottom: 10px;
        }
        .email-header {
            text-align: center;
            background-color: #166dd1;
            padding: 20px;
            color: #ffffff;
            border-radius: 8px 8px 0 0;
        }
        .email-header h1 {
            font-size: 26px;
            font-style: italic;
            margin: 0;
        }
        .email-content {
            padding: 30px;
            color: #333;
            line-height: 1.6;
        }
        .email-content p {
            text-align: center;
            font-style: italic;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .reset-button {
            display: inline-block;
            padding: 12px 25px;
            background-color: #007dd1;
            font-style: italic;
            text-decoration: none;
            text-decoration: none;
            font-weight: bold;
            border-radius: 10px;
            text-align: center;
            width: 100%;
            max-width: 300px;
            display: block;
            margin-left: auto;
            margin-right: auto; 
        }
        span {
            color: #ffffff !important;
            text-decoration: none;
        }
        .footer {
            text-align: center;
            font-size: 14px;
            color: #777;
            margin-top: 30px;
            font-style: italic;
        }
        .footer a {
            color: #006efd;
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>Password Reset Request</h1>
        </div>
        <div class="email-content">
            <p>We received a request to reset your password. If you did not request this change, please ignore this email.</p>
            <p>Click the button below to reset your password</p>
            <a href="http://technovation.wuaze.com/reset-password.php?token=$token" class="reset-button"><span>Reset Your Password</span></a>
            <p>This link will expire in 30 minutes</p>
        </div>
        <div class="footer">
            <p>If you have any questions, feel free to <a href="mailto:support@technovation.com">contact us</a>.</p>
        </div>
    </div>
</body>
</html>
END;

    try {
        $mail->send();
        $_SESSION['message'] = "Message has been sent please check your inbox !";
        header("Location: forgot-password.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['errors'] = ["Message could not be sent ! Mailer error : {$mail->ErrorInfo}"];
        header("Location: forgot-password.php");
        exit();
    }
} else {
    $_SESSION['errors'] = ["No matching account found !"];
    header("Location: forgot-password.php");
    exit();
}
