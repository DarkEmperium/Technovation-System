<?php
if (!isset($_SESSION["role"])) {
    header("Location: landing_page.php");
    exit();
}

if (isset($_POST["logout"])) {
    session_start();
    session_destroy();
    header("Location: landing_page.php");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Technovation House Rental Management System</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="assets/images/logo.png">
    <style>
        .header {
            background-color: rgb(121, 196, 233);
            color: white;
            padding: 10px 20px;
            font-style: italic;
            position: fixed;
            width: 100%;
            top: 0;
            left: 0;
            z-index: 1000;
        }

        .header .logo {
            margin-right: 10px;
            width: 80px;
        }

        .header .logout-btn {
            background-color: rgba(53, 53, 53, 0.507);
            color: white;
            font-style: italic;
            margin-right: 10px;
            transition: 0.5s;
        }

        .header .logout-btn:hover {
            background-color: rgba(97, 97, 97, 0.51);
            color: white;
        }
    </style>
</head>

<body>

    <div class="header d-flex justify-content-between align-items-center px-3">
        <div class="d-flex align-items-center">
            <img src="assets/images/logo.png" alt="Logo" class="logo">
            <h1 class="h5 mb-0">Technovation House Rental Management System</h1>
        </div>
        <form method="POST">
            <button class="btn logout-btn" name="logout">Log Out</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>