<?php
session_start(); // Start the session to access session variables
$errors = isset($_SESSION['errors']) ? $_SESSION['errors'] : [];
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';

// Clear session errors and message after displaying them
unset($_SESSION['errors']);
unset($_SESSION['message']);

$token = $_GET["token"];
$token_hash = hash("sha256", $token);

require __DIR__ . "/database.php"; // gives $conn, not $mysqli

$sql = "SELECT * FROM users WHERE reset_token_hash = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $token_hash);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);

if ($user === null) {
    die("Token not found !");
}

if (strtotime($user["reset_token_expires_at"]) <= time()) {
    die("Token has expired !");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/forgot_password.css">
    <link rel="stylesheet" href="assets/css/preloader.css">
    <link rel="icon" type="image/x-icon" href="assets/images/logo.png">
    <style>
        .img-fluid {
            width: 150px;
        }
        .image-container {
            margin: 60px auto;
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

    <a href="landing_page.php"><img src="assets/images/logo.png" alt="logo" id="logo"></a>

    <div class="container">
        <div class="row">
            <div class="col-md-4 offset-md-4 form">
                <form method="post" action="process-reset-password.php" autocomplete="">

                    <h1 class="text-center">Reset Password</h1>

                    <div class="image-container">
                        <img src="assets/images/reset_password.png" alt="Reset Password" class="img-fluid">
                    </div>

                    <!-- Display success message -->
                    <?php if (!empty($message)) { ?>
                        <div class="alert alert-success text-center">
                            <?php echo $message; ?>
                        </div>
                    <?php } ?>

                    <!-- Display errors -->
                    <?php if (count($errors) > 0) { ?>
                        <div class="alert alert-danger text-center">
                            <?php 
                            foreach ($errors as $error) {
                                echo $error . "<br>";
                            }
                            ?>
                        </div>
                    <?php } ?>

                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                    <div class="form-group">
                        <input type="password" id="password" name="password" class="form-control" placeholder="Enter New Password" required>
                    </div>

                    <div class="form-group">
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Repeat Password" required>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="button">Reset Now</button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script src="assets/js/preloader.js"></script>
</body>

</html>
