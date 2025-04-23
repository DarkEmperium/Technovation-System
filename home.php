<?php
include 'auto_generate_Invoice.php';

if (!isset($_SESSION["role"])) {
    header("Location: landing_page.php");
    exit();
}

// Query total number of houses (for admin)
$totalHousesQuery = "SELECT COUNT(*) AS total FROM houses";
$totalHousesResult = mysqli_query($conn, $totalHousesQuery);
$totalHouses = 0;

if ($row = mysqli_fetch_assoc($totalHousesResult)) {
    $totalHouses = $row['total'];
}

// Query the total number of tenants who are renting a room (for admin)
$totalTenantsQuery = "SELECT COUNT(DISTINCT r.User_ID) AS total FROM rooms r WHERE r.User_ID IS NOT NULL";
$totalTenantsResult = mysqli_query($conn, $totalTenantsQuery);
$totalTenants = 0;

if ($tenantRow = mysqli_fetch_assoc($totalTenantsResult)) {
    $totalTenants = $tenantRow['total'];
}


// Get the current month and year
$currentMonth = date('m');
$currentYear = date('Y');

// Query the total payments made this month (only paid invoices)
$paymentQuery = "
    SELECT SUM(i.amount) AS total_payments
    FROM invoices i
    WHERE i.status = 'Paid'
    AND MONTH(i.invoice_date) = $currentMonth
    AND YEAR(i.invoice_date) = $currentYear
";

$paymentResult = mysqli_query($conn, $paymentQuery);
$totalPayments = 0;

if ($paymentRow = mysqli_fetch_assoc($paymentResult)) {
    $totalPayments = $paymentRow['total_payments'] ?? 0;
}



// Initialize issue count (for tenant)
$issueCount = 0;

// Check if user is a tenant and fetch issue count
if ($_SESSION["role"] === "user") {
    $tenantId =  $_SESSION["User_ID"]; // Always safe to cast
    $issueQuery = "SELECT COUNT(*) AS issue_count FROM report WHERE User_ID = $tenantId";
    $issueResult = mysqli_query($conn, $issueQuery);

    if ($issueRow = mysqli_fetch_assoc($issueResult)) {
        $issueCount = $issueRow['issue_count'];
    }
}

// Outstanding Bill
$outstandingBill = 0;

if ($_SESSION["role"] === "user") {
    $tenantId = $_SESSION["User_ID"];

    // Query to sum all pending invoices for the tenant
    $outstandingQuery = "SELECT SUM(i.amount) AS total_due
                    FROM invoices i
                    INNER JOIN rooms r ON i.Room_ID = r.Room_ID
                    WHERE r.User_ID = $tenantId AND i.status = 'Pending'";

    $outstandingResult = mysqli_query($conn, $outstandingQuery);

    if ($outstandingRow = mysqli_fetch_assoc($outstandingResult)) {
        // Use the total sum of all pending invoices
        $outstandingBill = $outstandingRow['total_due'] ?? 0;
    }
}

//Renterd Rooms
$roomCount = 0;

// Check if user is a tenant and fetch room count
if ($_SESSION["role"] === "user") {
    $tenantId =  $_SESSION["User_ID"]; // Always safe to cast
    $rentRoom = "SELECT COUNT(*) AS rooms FROM rooms WHERE User_ID = $tenantId";
    $rentResult = mysqli_query($conn, $rentRoom);

    if ($rentRow = mysqli_fetch_assoc($rentResult)) {
        $roomCount = $rentRow['rooms'];
    }
}

$currentMonth = date('Y-m'); // e.g., "2025-04"
$showReminder = false;

// Query to check if there's a utility bill for the current month
$query = "SELECT COUNT(*) AS total FROM utilities WHERE month = '$currentMonth'";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);

// If no utility bill exists for the current month, show reminder
if ($row['total'] == 0) {
    $showReminder = true;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="assets/images/logo.png">
    <link href="assets/css/preloader.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .main-content {
            margin-top: 80px;
        }

        .title {
            text-align: center;
            margin-bottom: 20px;
        }

        .card {
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.1);
            border: none;
        }

        .card h5 {
            font-size: 2rem;
            font-weight: bold;
            font-style: italic;
        }

        .card p {
            font-size: 1.2rem;
            font-style: italic;
        }

        .btn-custom {
            width: 100%;
            margin-top: 10px;
            font-weight: bold;
            font-style: italic;
            border-radius: 8px;
            padding: 10px;
            transition: all 0.3s ease-in-out;
            border: none;
        }

        .btn-custom:hover {
            transform: scale(1.05);
            filter: brightness(1.2);
        }

        .btn-custom i {
            margin-right: 10px;
        }

        #alert-icon {
            margin-right: 10px;
        }

        .btn-width {
            width: 150px;
            margin: auto 20px;
        }
    </style>
</head>

<body>
    <!--Website Preloader-->
    <div id="preloader">
        <img src="assets/images/logo.png" alt="Technovation House Rental System">
        <h3>Loading Page</h3>
        <div class="loader"></div>
    </div>

    <div class="main-content">
        <div class="row mt-4">

            <?php if (isset($_SESSION["role"]) && $_SESSION["role"] === "admin"): ?>
                <!-- Admin Dashboard -->
                <div class="col-md-4">
                    <div class="card text-white" style="background-color: #6ECEDA;">
                        <div class="card-body text-center">
                            <h5><i class="fas fa-home"></i> <?php echo $totalHouses; ?></h5>
                            <p>Total Houses</p>
                            <a href="index.php?page=categories" class="btn btn-light btn-custom"><i class="fas fa-home"></i> View Houses</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white" style="background-color: #F9A26C;">
                        <div class="card-body text-center">
                            <!-- Tenant Count -->
                            <h5><i class="fas fa-users"></i> <?php echo $totalTenants; ?></h5>
                            <p>Total Tenants</p>
                            <a href="index.php?page=rented" class="btn btn-light btn-custom"><i class="fas fa-users"></i> View Tenants</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white" style="background-color: rgb(132, 223, 138);">
                        <div class="card-body text-center">
                            <h5></i> RM <?php echo number_format($totalPayments, 2); ?></h5>
                            <p>Payments This Month</p>
                            <a href="index.php?page=invoices" class="btn btn-light btn-custom"><i class="fas fa-dollar-sign"></i> View Payments</a>
                        </div>
                    </div>
                </div>

                <!-- Utility Bill Reminder Modal -->
                <div class="modal fade" id="utilityReminderModal" tabindex="-1" aria-labelledby="utilityReminderLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content shadow-lg rounded-3">
                            <div class="modal-header bg-warning text-dark">
                                <h5 class="modal-title d-flex align-items-center" id="utilityReminderLabel">
                                    <i class="fa-solid fa-triangle-exclamation" id="alert-icon"></i><em>Utility Bill Reminder</em>
                                </h5>
                            </div>
                            <div class="modal-body text-center">
                                <p class="fs-5 mb-3"><em>Please add this month's utility bill to keep your records up to date !</em></p>
                            </div>
                            <div class="modal-footer justify-content-center">
                                <a href="index.php?page=utilities" class="btn btn-primary btn-width"><em>Go to Utilities</em></a>
                                <button type="button" class="btn btn-secondary btn-width" onclick="dismissReminder()" data-dismiss="modal"><em>Dismiss</em></button>
                            </div>
                        </div>
                    </div>
                </div>

            <?php else: ?>

                <!-- Tenant Dashboard -->
                <div class="col-md-4">
                    <div class="card text-white" style="background-color: #90EE90;">
                        <div class="card-body text-center">
                            <h5></i> <span id="outstandingBill">RM <?php echo number_format($outstandingBill, 2); ?></span></h5>
                            <p>Outstanding Bill</p>
                            <a href="index.php?page=payment" class="btn btn-light btn-custom"><i class="fas fa-file-invoice-dollar"></i> View Outstanding Bills</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white" style="background-color: #87CEEB;">
                        <div class="card-body text-center">
                            <h5><i class="fas fa-exclamation-circle"></i> <?php echo $issueCount; ?></h5>
                            <p>Issue Reported</p>
                            <a href="index.php?page=issue" class="btn btn-light btn-custom"><i class="fas fa-exclamation-circle"></i> View Issues</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white" style="background-color: #FF69B4;">
                        <div class="card-body text-center">
                            <h5><i class="fas fa-home"></i> <?php echo $roomCount; ?></h5>
                            <p>Rented Rooms</p>
                            <a href="index.php?page=rent" class="btn btn-light btn-custom"><i class="fas fa-home"></i> View Rented Rooms</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="assets/js/preloader.js"></script>
    <script src="assets/js/home.js"></script>
    <script>
        window.onload = function() {
            // Run preloader script if needed
            if (typeof initPreloader === "function") {
                initPreloader();
            }

            <?php if ($showReminder): ?>
                const monthKey = new Date().toISOString().slice(0, 7); // 'YYYY-MM'
                const cookieName = "utility_reminder_dismissed_" + monthKey;

                if (!getCookie(cookieName)) {
                    const reminderModal = new bootstrap.Modal(document.getElementById('utilityReminderModal'));
                    reminderModal.show();
                }
            <?php endif; ?>
        };
    </script>
</body>
</html>