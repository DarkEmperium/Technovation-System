<?php
session_start();
include("database.php");

if (isset($_POST["Register"])) {
    $username = $_POST["name"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $contact = $_POST["tele"];
    $registration_date = date('Y-m-d');

    // Password strength validation
    if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
        $_SESSION["toast"] = "Password must be at least 8 characters long and include uppercase, lowercase, number, and special character !";
        $_SESSION["toast_type"] = "danger";
        header("Location: registration.php");
        exit();
    }

    // Phone format validation
    if (!preg_match('/^01[0-9]-\d{7,8}$/', $contact)) {
        $_SESSION["toast"] = "Invalid phone number format please use format like 012-3456789 !";
        $_SESSION["toast_type"] = "danger";
        header("Location: registration.php");
        exit();
    }

    // Check if email already exists
    $check_sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($result) > 0) {
        $_SESSION["toast"] = "Email already exists please try using another email !";
        $_SESSION["toast_type"] = "danger";
        header("Location: registration.php");
        exit();
    }

    // Insert new user
    $sql = "INSERT INTO users (name, email, password, phoneNum, registration_date) 
            VALUES ('$username','$email','$password','$contact', '$registration_date')";

    if (mysqli_query($conn, $sql)) {
        $user_id = mysqli_insert_id($conn);

        if ($user_id) {
            $refund_date = date('Y-m-d', strtotime('+1 year'));
            $deposit_sql = "INSERT INTO deposit (Deposit_Amount, status, Refund_Date, User_ID)  
                            VALUES (1000, 'Unpaid', '$refund_date', '$user_id')";

            if (!mysqli_query($conn, $deposit_sql)) {
                $_SESSION["toast"] = "Deposit Insert Error : " . mysqli_error($conn);
                $_SESSION["toast_type"] = "danger";
                header("Location: registration.php");
                exit();
            }

            $_SESSION["toast"] = "Registration successful !";
            $_SESSION["toast_type"] = "success";
            header("Location: registration.php");
            exit();
        } else {
            // User ID not found
            $_SESSION["toast"] = "Error : User ID not found !";
            $_SESSION["toast_type"] = "danger";
            header("Location: registration.php");
            exit();
        }
    } else {
        // User insert error
        $_SESSION["toast"] = "User Insert Error : " . mysqli_error($conn);
        $_SESSION["toast_type"] = "danger";
        header("Location: registration.php");
        exit();
    }
}

if (isset($_POST["Login"])) {
    $registeredEmail = $_POST["email"];
    $registeredPass = $_POST["password"];

    $sql = "SELECT * FROM users WHERE email ='$registeredEmail' AND password = '$registeredPass'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        $_SESSION["User_ID"] = $user["User_ID"];
        $_SESSION["username"] = $user["name"];
        $_SESSION["role"] = $user["Role"];

        header("Location: index.php?page=home");
        exit();
    } else {
        $_SESSION["toast"] = "Invalid Email ID or Password !";
        $_SESSION["toast_type"] = "danger";
        header("Location: registration.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/preloader.css">
    <link rel="stylesheet" href="assets/css/registration.css">
    <title>Login Page</title>
    <link rel="icon" type="image/x-icon" href="assets/images/logo.png">
</head>

<body>

    <!--Website Preloader-->
    <div id="preloader">
        <img src="assets/images/logo.png" alt="Technovation House Rental System">
        <h3>Loading Page</h3>
        <div class="loader"></div>
    </div>

    <a href="landing_page.php"><img src="assets/images/logo.png" alt="logo" id="logo"></a>

    <div class="container" id="container">
        <div class="form-container sign-up">
            <form method="POST">
                <h1>Create Account</h1>
                <input type="text" name="name" placeholder="Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="tel" name="tele" placeholder="Phone Number" required>
                <button type="submit" name="Register">Sign Up</button>
            </form>
        </div>
        <div class="form-container sign-in">
            <form method="POST">
                <h1>Sign In</h1>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" id="password-input" required>
                <div class="remember-forget">
                    <div class="remember">
                        <input type="checkbox" id="show-password" name="showpwd" onclick="showpassword()">
                        <label for="show-password">Show Password</label>
                    </div>
                    <a href="forgot-password.php" class="forgot-password-link">Forgot Password?</a>
                </div>
                <button type="submit" name="Login">Sign In</button>
            </form>
        </div>
        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-left">
                    <h1>Welcome Back !</h1>
                    <p>Log in to access your personalized dashboard, manage your rental properties, and stay on top of your rental processes. Your seamless and efficient rental management experience starts here.</p>
                    <button class="hidden" id="login">Sign In</button>
                </div>
                <div class="toggle-panel toggle-right">
                    <h1>Bonjour !</h1>
                    <p>Register now to list your properties, find the perfect rental, and take advantage of our innovative solutions. Become a part of our community and revolutionize your rental management journey.</p>
                    <button class="hidden" id="register">Sign Up</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Alert Box -->
    <?php if (isset($_SESSION["toast"])): ?>
        <?php $toast_type = $_SESSION["toast_type"] ?? "danger"; ?>
        <div id="customAlert" class="custom-alert custom-alert-<?= $toast_type ?>" role="alert">
            <div class="alert-content">
                <?= $_SESSION["toast"] ?>
                <button id="closeAlert" class="close-alert" aria-label="Close">×</button>
            </div>
        </div>
        <?php unset($_SESSION["toast"], $_SESSION["toast_type"]); ?>
    <?php endif; ?>

    <script src="assets/js/registration.js"></script>
    <script src="assets/js/preloader.js"></script>

</body>

</html>