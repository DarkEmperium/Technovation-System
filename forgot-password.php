<?php
session_start(); // Start the session to access session variables
$errors = isset($_SESSION['errors']) ? $_SESSION['errors'] : [];
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';

// Clear session errors after displaying them
unset($_SESSION['errors']);
unset($_SESSION['message']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/forgot_password.css">
    <link rel="stylesheet" href="assets/css/preloader.css">
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

    <div class="container">
        <div class="row">
            <div class="col-md-4 offset-md-4 form">
                <form action="send-password-reset.php" method="POST" autocomplete="">
                    <h1 class="text-center">Forgot Password</h1>
                    <img src="assets/images/forget_password.jpg" alt="forget-password">

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

                    <div class="form-group">
                        <input class="form-control" type="email" name="email" placeholder="Enter email address" required value="<?php echo isset($email) ? $email : ''; ?>">
                    </div>
                    <div class="form-group">
                        <input class="button" type="submit" name="check-email" value="Continue">
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/js/preloader.js"></script>
</body>
</html>
