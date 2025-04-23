<?php
session_start(); // Start the session to store errors

require __DIR__ . "/database.php"; // $conn is the connection

$token = $_POST["token"];
$token_hash = hash("sha256", $token);

// Get user with matching token
$sql = "SELECT * FROM users WHERE reset_token_hash = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $token_hash);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Token not found or expired
if ($user === null) {
    $_SESSION['errors'] = ["Token not found or expired !"];
    header("Location: reset-password.php?token=" . urlencode($token)); // Redirect to reset-password page
    exit();
}

if (strtotime($user["reset_token_expires_at"]) <= time()) {
    $_SESSION['errors'] = ["Token has expired !"];
    header("Location: reset-password.php?token=" . urlencode($token)); // Redirect to reset-password page
    exit();
}

// Validate new password
$errors = [];

if (strlen($_POST["password"]) < 8) {
    $errors[] = "Password must be at least 8 characters !";
}

if (!preg_match("/[0-9]/", $_POST["password"])) {
    $errors[] = "Password must contain at least one number !";
}

if (!preg_match('/[\W_]/', $_POST["password"])) {
    $errors[] = "Password must contain at least one special character !";
}

if (!preg_match('/[A-Z]/', $_POST["password"])) {
    $errors[] = "Password must contain at least one uppercase letter !";
}

if (!preg_match('/[a-z]/', $_POST["password"])) {
    $errors[] = "Password must contain at least one lowercase letter !";
}

if (preg_match('/\s/', $_POST["password"])) {
    $errors[] = "Password must not contain any spaces !";
}

if ($_POST["password"] !== $_POST["password_confirmation"]) {
    $errors[] = "Passwords must be match !";
}

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    header("Location: reset-password.php?token=" . urlencode($token)); // Redirect to reset-password page
    exit();
}

$password = $_POST["password"]; 

// Update user's password and remove token
$sql = "UPDATE users
        SET password = ?,
            reset_token_hash = NULL,
            reset_token_expires_at = NULL
        WHERE User_ID = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "si", $password, $user["User_ID"]);
mysqli_stmt_execute($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password Updated</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="assets/images/logo.png">
    <link rel="stylesheet" href="assets/css/preloader.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f3f5;
            display: flex;
            justify-content: center;
            align-items: center;
            font-style: italic;
            height: 100vh;
        }

        .card-box {
            background: #ffffff;
            border-radius: 16px;
            padding: 40px 30px;
            max-width: 420px;
            text-align: center;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.08);
        }

        .alert {
            font-size: 15px;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .spinner-border {
            margin-top: 20px;
        }

        .fade-in {
            animation: fadeIn 0.8s ease-in;
        }

        img {
            width: 200px;
            margin-top: 20px;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

</head>
<body>

    <!-- Website Preloader -->
    <div id="preloader">
        <img src="assets/images/logo.png" alt="Technovation House Rental System">
        <h3>Loading Page</h3>
        <div class="loader"></div>
    </div>

    <div class="card-box fade-in">
        <div class="alert alert-success" role="alert">
            <b>Password Has Been Updated Successfully !</b>
        </div>
        <div class="image-container">
            <img src="assets/images/password-update-success.gif" alt="password-updated">
        </div>
        <p class="text-muted mt-3 mb-2">Redirecting to Login Page</p>
        <div class="spinner-border text-success" role="status"></div>
    </div>

    <script>
        setTimeout(() => {
            window.location.href = 'registration.php';
        }, 5000);
    </script>
    <script src="assets/js/preloader.js"></script>

</body>
</html>
