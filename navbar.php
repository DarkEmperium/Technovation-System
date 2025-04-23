<?php
if (!isset($_SESSION["role"])) {
    header("Location: landing_page.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <title>Sidebar Navigation</title>
    <style>
        body {
            display: flex;
        }

        .sidebar {
            background-color: #343a40;
            height: calc(100vh - 85px);
            /* Adjust height to exclude header height */
            position: fixed;
            width: 250px;
            top: 100px;
            /* Push it down so it doesn't overlap the header */
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            /* Distribute space between elements */
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
            margin-top: 60px;
            /* Adjust for fixed header height */
        }

        .sidebar .nav-link {
            color: #fff;
            display: flex;
            align-items: center;
            padding: 10px 15px;
            transition: 0.5s;
        }

        .sidebar .nav-link:hover {
            background-color: #495057;
            color: #fff;
            border-radius: 10px;
        }

        .sidebar .icon-field {
            width: 50px;
            /* Set a fixed width for icons */
            display: inline-flex;
            justify-content: center;
        }

        /* User Profile Section */
        .user-profile {
            color: white;
            padding: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .user-profile i {
            font-size: 20px;
            margin-right: 30px;
        }

        .username {
            font-size: 16px;
            font-weight: bold;
            font-style: italic;
        }
    </style>
</head>

<body>

    <nav class="sidebar d-flex flex-column p-3">

        <!-- Administrator Sidebar Navigation Links-->
         
        <div class="sidebar-list">
           
            <?php if (isset($_SESSION["role"]) && $_SESSION["role"] === "admin"): ?>        
            <a href="index.php?page=home" class="nav-item nav-link"><span class='icon-field'><i class="fas fa-tachometer-alt"></i></span> Dashboard</a>
            <a href="index.php?page=categories" class="nav-item nav-link"><span class='icon-field'><i class="fas fa-home"></i></span> Category</a>
            <a href="index.php?page=rooms" class="nav-item nav-link"><span class='icon-field'><i class="fas fa-door-open"></i></span> Rooms</a>
            <a href="index.php?page=tenants" class="nav-item nav-link"><span class='icon-field'><i class="fas fa-user-friends"></i></span> Tenants</a>
            <a href="index.php?page=rented" class="nav-item nav-link"><span class='icon-field'><i class="fas fa-users-cog"></i></span> Rentals</a>
            <a href="index.php?page=invoices" class="nav-item nav-link"><span class='icon-field'><i class="fas fa-file-invoice"></i></span> Invoices</a>
            <a href="index.php?page=utilities" class="nav-item nav-link"><span class='icon-field'><i class="fas fa-tint"></i></span> Utilities</a>
            <a href="index.php?page=reports" class="nav-item nav-link"><span class='icon-field'><i class="fas fa-exclamation-triangle"></i></span> Reports</a>

            <?php else: ?>
            <!--Add if statement in php to detect user either admin or tenant, if admin access to full functionality
            Tenant Sidebar Navigation Links-->
            <a href="index.php?page=home" class="nav-item nav-link"><span class='icon-field'><i class="fas fa-tachometer-alt"></i></span> Dashboard</a>
            <a href="index.php?page=rent" class="nav-item nav-link"><span class='icon-field'><i class="fas fa-key"></i></span> Rent</a>
            <a href="index.php?page=payment" class="nav-item nav-link"><span class='icon-field'><i class="fas fa-dollar-sign"></i></span> Payment</a>
            <a href="index.php?page=deposit" class="nav-item nav-link"><span class='icon-field'><i class="fas fa-money-check"></i></span> Deposit</a>
            <a href="index.php?page=history" class="nav-item nav-link"><span class='icon-field'><i class="fas fa-file-invoice-dollar"></i></span> History</a>
            <a href="index.php?page=issue" class="nav-item nav-link"><span class='icon-field'><i class="fas fa-exclamation-circle"></i></span> Report</a>
            <a href="index.php?page=configuration" class="nav-item nav-link"><span class='icon-field'><i class="fas fa-user-cog"></i></span> Setting</a>
            <?php endif; ?>
        </div>

        <!-- User Profile Section -->
        <div class="user-profile">
            <i class="fas fa-user-circle"></i>
            <span class="username">
            <?php echo $_SESSION['username'] ?>
            </span>
        </div>
    </nav>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>