<?php
$user_id = $_SESSION['User_ID'];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newName = mysqli_real_escape_string($conn, $_POST['username']);
    $newEmail = mysqli_real_escape_string($conn, $_POST['email']);
    $newPhone = mysqli_real_escape_string($conn, $_POST['phone']);
    $newPassword = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    $errors = [];

    // Password validation
    if (!empty($newPassword) || !empty($confirmPassword)) {
        
        if (strlen($newPassword) < 8) {
            $errors[] = "Password must be at least 8 characters !";
        }
        if (!preg_match('/[A-Z]/', $newPassword)) {
            $errors[] = "Password must contain at least one uppercase letter !";
        }
        if (!preg_match('/[a-z]/', $newPassword)) {
            $errors[] = "Password must contain at least one lowercase letter !";
        }
        if (!preg_match('/\d/', $newPassword)) {
            $errors[] = "Password must contain at least one number !";
        }
        if (!preg_match('/[\W_]/', $newPassword)) {
            $errors[] = "Password must contain at least one special character !";
        }
        if (preg_match('/\s/', $newPassword)) {
            $errors[] = "Password must not contain any spaces !";
        }
        if ($newPassword !== $confirmPassword) {
            $errors[] = "Passwords must match !";
        }
    }

    if (empty($errors)) {
        $updateQuery = "UPDATE users SET name = '$newName', email = '$newEmail', phoneNum = '$newPhone'";

        if (!empty($newPassword)) {
            $updateQuery .= ", password = '$newPassword'";
        }
        

        $updateQuery .= " WHERE User_ID = $user_id";

        if (mysqli_query($conn, $updateQuery)) {
            $successMessage = "Updated Successfully !";
        } else {
            $errors[] = "Error updating data : " . mysqli_error($conn);
        }
    }
}

// Always fetch updated user data
$query = "SELECT * FROM users WHERE User_ID = $user_id";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);

$name = $row['name'];
$contact = $row['phoneNum'];
$email = $row['email'];
$password = $row['password'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Settings</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="assets/images/logo.png">
    <link rel="stylesheet" href="assets/css/preloader.css">
    <style>
        body {
            background-color: #f8f9fa;
            margin: 0;
        }
        .container {
            max-width: 1200px;
            margin: 70px auto;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        h3, h5, input, .btn, label, .alert {
            font-style: italic;
        }
        .btn-container {
            text-align: right;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div id="preloader">
        <img src="assets/images/logo.png" alt="Technovation House Rental System">
        <h3>Loading Page</h3>
        <div class="loader"></div>
    </div>

    <div class="main-content">
        <div class="container">
            <h3 class="mb-4">User Setting Portal</h3>

            <!-- Display Success or Error Messages -->
            <?php if (!empty($errors)): ?>
                <?php foreach ($errors as $error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if (!empty($successMessage)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $successMessage; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" value="<?php echo $name; ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo $email; ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control" value="<?php echo $contact; ?>" required pattern="^(0\d{2}-\d{7}|011-\d{8})$" title="Phone number must in format like 012-3456789">
                </div>

                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current password">
                </div>

                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Repeat new password">
                </div>

                <div class="btn-container">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/preloader.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
