<?php

if (!isset($_SESSION["role"])) {
    header("Location: landing_page.php");
    exit();
}

$user_id = $_SESSION['User_ID'];

$searchDate = isset($_POST['search']) ? strtolower(trim($_POST['search'])) : '';
$monthNumber = '';

// Convert month name to number
$months = [
    'january' => '01',
    'jan' => '01',
    'february' => '02',
    'feb' => '02',
    'march' => '03',
    'mar' => '03',
    'april' => '04',
    'apr' => '04',
    'may' => '05',
    'june' => '06',
    'jun' => '06',
    'july' => '07',
    'jul' => '07',
    'august' => '08',
    'aug' => '08',
    'september' => '09',
    'sep' => '09',
    'october' => '10',
    'oct' => '10',
    'november' => '11',
    'nov' => '11',
    'december' => '12',
    'dec' => '12'
];

if (array_key_exists($searchDate, $months)) {
    $monthNumber = $months[$searchDate];
}

$query = "
    SELECT CONCAT('INV-', InvoiceID) AS InvoiceID, Invoice_Date, 'Invoice' AS Type, amount 
    FROM invoices 
    WHERE UserID = ? AND status = 'Paid'
";

// Filter by month if provided
if (!empty($monthNumber)) {
    $query .= " AND MONTH(Invoice_Date) = '$monthNumber'";
} elseif (!empty($searchDate)) {
    $query .= " AND Invoice_Date LIKE '%$searchDate%'";
}

// Add deposit part
$query .= "
    UNION 
    SELECT 'DEP-1000' AS InvoiceID, CURDATE() AS Invoice_Date, 'Deposit' AS Type, 1000 AS amount 
    FROM deposit 
    WHERE User_ID = ? AND status = 'Paid'
";

// Only filter deposit if monthNumber or fallback is present
if (!empty($monthNumber)) {
    $query .= " AND MONTH(CURDATE()) = '$monthNumber'";
} elseif (!empty($searchDate)) {
    $query .= " AND CURDATE() LIKE '%$searchDate%'";
}

$query .= " ORDER BY Invoice_Date DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment History Portal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="icon" type="image/x-icon" href="assets/images/logo.png">
    <link rel="stylesheet" href="assets/css/preloader.css">
    <style>
        body {
            background-color: #f8f9fa;
            margin: 0;
        }

        .container {
            max-width: 1000px;
            margin: 70px auto;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h3 {
            font-style: italic;
            margin-bottom: 30px;
        }

        .table,
        #searchPayment {
            font-style: italic;
        }

        .table th {
            background-color: #343a40;
            color: white;
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

    <div class="main-content">
        <div class="container">
            <h3 class="mb-4 d-flex justify-content-between align-items-center">
                Payment History Portal
                <div>
                    <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST["search"])): ?>
                        <a href="index.php?page=history" class="btn btn-secondary me-2">
                            <i class="bi bi-arrow-clockwise"></i> Reset Search
                        </a>
                    <?php endif; ?>
                </div>
            </h3>
            <div class="mb-4 p-3 bg-light rounded">
                <form method="POST" action="index.php?page=history">
                    <input type="text" class="form-control" placeholder="Search by Month (e.g. March or Mar)" name="search" value="<?php echo isset($_POST['search']) ? $_POST['search'] : ''; ?>" id="searchPayment">
                </form>
            </div>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Invoice</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $count = 1;
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $count++ . "</td>";
                            echo "<td>" . $row['InvoiceID'] . "</td>";
                            echo "<td>" . $row['Invoice_Date'] . "</td>";
                            echo "<td>" . $row['Type'] . "</td>";
                            echo "<td>RM " . number_format($row['amount'], 2) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo '<tr><td colspan="5">No invoices found.</td></tr>';
                    }
                    ?>
                </tbody>

            </table>
        </div>
    </div>

    <script src="assets/js/preloader.js"></script>
</body>

</html>


<?php
$stmt->close();
$conn->close();
?>