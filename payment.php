<?php

if (!isset($_SESSION["role"])) {
    header("Location: landing_page.php");
    exit();
}

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


$tenant_id = $_SESSION['User_ID']; // Get logged-in tenant ID
$query = "SELECT i.InvoiceID, r.RoomNo, i.Invoice_Date, i.amount, 
                 COALESCE(u.water_bill, 0) AS Water_Bill, 
                 COALESCE(u.electric_bill, 0) AS Electricity_Bill 
          FROM invoices i
          INNER JOIN rooms r ON i.Room_ID = r.Room_ID
          LEFT JOIN utilities u 
              ON r.House_ID = u.House_ID 
              AND DATE_FORMAT(i.Invoice_Date, '%Y-%m') = u.month
          WHERE r.User_ID = $tenant_id AND i.status = 'Pending'";



// Add month filter if applicable
if (!empty($monthNumber)) {
    $query .= " AND MONTH(i.Invoice_Date) = '$monthNumber'";
} elseif (!empty($searchDate)) {
    $query .= " AND i.Invoice_Date LIKE '%$searchDate%'";
}


$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Payment Portal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
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

        h3,
        h5,
        input,
        .btn,
        table,
        label,
        #searchBill {
            font-style: italic;
        }

        table {
            text-align: center;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
        }

        .table th {
            background-color: #343a40;
            color: white;
        }

        .grand-total {
            text-align: right;
            font-weight: bold;
            font-style: italic;
        }

        .grand-total span {
            background-color: #e9ecef;
            padding: 5px 10px;
            border-radius: 5px;
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
            <h3 class="mb-4">Rental Payment Portal</h3>
            <div>
                <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST["search"])): ?>
                    <a href="index.php?page=payment" class="btn btn-secondary mb-3">
                        <i class="bi bi-arrow-clockwise"></i> Reset Search
                    </a>
                <?php endif; ?>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <h5>Outstanding Bills</h5>
                    <div class="mb-4 p-3 bg-light rounded">
                        <form method="POST" action="index.php?page=payment">
                            <input type="text" class="form-control" placeholder="Search by Month (e.g. March or Mar)" name="search" value="<?= isset($_POST['search']) ? htmlspecialchars($_POST['search']) : '' ?>">
                        </form>
                    </div>


                    <form method="post" action="checkout.php">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Room</th>
                                    <th>Billed Month</th>
                                    <th>Rental Fee (RM)</th>
                                    <th>Water Bill (RM)</th>
                                    <th>Electricity Bill (RM)</th>
                                    <th>Total (RM)</th>
                                    <th>Select</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $count = 1;
                                $hasValidPayments = false;

                                if (mysqli_num_rows($result) > 0) {
                                    
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        
                                        if ($row['Water_Bill'] == 0.00 && $row['Electricity_Bill'] == 0.00) {
                                            continue;
                                        }

                                        $hasValidPayments = true;

                                        // Calculate values
                                        $remaining_amount = $row['amount'] - $row['Water_Bill'] - $row['Electricity_Bill'];
                                        $room_id = $row['RoomNo'];
                                        $invoice_date = date("F Y", strtotime($row['Invoice_Date']));
                                        $rental_fee = number_format($remaining_amount, 2);
                                        $water_bill = number_format($row['Water_Bill'], 2);
                                        $electricity_bill = number_format($row['Electricity_Bill'], 2);
                                        $totalAmount = $row['amount'];
                                        $total = number_format($totalAmount, 2);
                                ?>
                                        <tr>
                                            <td><?= $count++ ?></td>
                                            <td><?= $room_id ?></td>
                                            <td><?= $invoice_date ?></td>
                                            <td><?= $rental_fee ?></td>
                                            <td><?= $water_bill ?></td>
                                            <td><?= $electricity_bill ?></td>
                                            <td><?= $total ?></td>
                                            <td>
                                                <input type="checkbox" class="bill-checkbox" name="selected_bills[]"
                                                    value="<?= $row['InvoiceID'] ?>" data-amount="<?= $totalAmount ?>">
                                            </td>
                                        </tr>
                                <?php
                                    }

                                    if (!$hasValidPayments) {
                                        echo '<tr><td colspan="8" class="text-center">Payments found but please wait for utility charges to be updated.</td></tr>';
                                    }
                                } else {
                                    echo '<tr><td colspan="8" class="text-center">No payment found.</td></tr>';
                                }
                                ?>

                            </tbody>
                        </table>

                        <!-- Hidden input to store Grand Total -->
                        <input type="hidden" name="grand_total" id="grandTotalInput">

                        <div class="text-end grand-total">
                            Grand Total : <span>RM <span id="totalAmount">0.00</span></span>
                        </div>

                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-primary">Proceed to Pay</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            function updateTotal() {
                let total = 0;
                document.querySelectorAll(".bill-checkbox:checked").forEach(cb => {
                    let amount = parseFloat(cb.dataset.amount) || 0;
                    total += amount;
                });

                document.getElementById("totalAmount").textContent = total.toFixed(2);
                document.getElementById("grandTotalInput").value = total.toFixed(2);
            }

            document.querySelectorAll(".bill-checkbox").forEach(cb => {
                cb.addEventListener("change", updateTotal);
            });

            updateTotal(); // Initialize total when page loads
        });
    </script>
    <script src="assets/js/preloader.js"></script>
</body>

</html>