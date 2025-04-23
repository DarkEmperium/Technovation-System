<?php
if (!isset($_SESSION["role"])) {
    header("Location: landing_page.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_payment'])) {
    $userID = $_POST['user_id'];
    $roomID = $_POST['room_id'];
    $amount = $_POST['amount_paid'];
    $invoiceMonth = date('Y-m', strtotime($invoiceDate));
    $invoiceNumber = $_POST['invoice_number'] ? $_POST['invoice_number'] : null; // If not manually provided, auto-generate

    // Fetch room price and associated House_ID
    $roomQuery = "SELECT price, House_ID FROM rooms WHERE Room_ID = '$roomID'";
    $roomResult = mysqli_query($conn, $roomQuery);

    if (mysqli_num_rows($roomResult) > 0) {
        $room = mysqli_fetch_assoc($roomResult);
        $roomRentPrice = $room['price'];
        $houseID = $room['House_ID'];

        // Fetch utility bills for the associated house
        $utilityQuery = "SELECT water_bill, electric_bill FROM utilities WHERE House_ID = '$houseID' AND month = '$invoiceMonth'";
        $utilityResult = mysqli_query($conn, $utilityQuery);

        if (mysqli_num_rows($utilityResult) > 0) {
            $utility = mysqli_fetch_assoc($utilityResult);
            $waterBill = $utility['water_bill'];
            $electricBill = $utility['electric_bill'];
        } else {
            $waterBill = 0;
            $electricBill = 0;
        }

        // Calculate total amount (room rent + utilities)
        $totalAmount = $roomRentPrice + $waterBill + $electricBill;

        if ($amount != $totalAmount) {
            $_SESSION['message'] = "The amount paid does not match the total amount (RM " . $totalAmount . "). Please correct the amount.";
            header("Location: index.php?page=invoices");
            exit;
        }

        // Handle Invoice Number - Auto Generate if not provided
        if (!$invoiceNumber) {
            // Auto-generate invoice number by getting the highest InvoiceID and adding 1
            $invoiceQuery = "SELECT MAX(InvoiceID) AS max_invoice FROM invoices";
            $invoiceResult = mysqli_query($conn, $invoiceQuery);
            $invoiceRow = mysqli_fetch_assoc($invoiceResult);
            $invoiceNumber = $invoiceRow['max_invoice'] + 1;
        } else {
            // Check if the invoice number already exists to prevent duplicates
            $checkInvoiceQuery = "SELECT InvoiceID FROM invoices WHERE InvoiceID = '$invoiceNumber'";
            $checkInvoiceResult = mysqli_query($conn, $checkInvoiceQuery);
            if (mysqli_num_rows($checkInvoiceResult) > 0) {
                $_SESSION['message'] = "Invoice number $invoiceNumber already exists. Please choose a different number.";
                header("Location: index.php?page=invoices");
                exit;
            }
        }

        // Insert invoice with utilities included in the total amount
        $insertQuery = "INSERT INTO invoices (InvoiceID, UserID, Room_ID, Invoice_Date, status, amount) 
                        VALUES ('$invoiceNumber', '$userID', '$roomID', '$invoiceDate', 'Paid', '$totalAmount')";

        if (mysqli_query($conn, $insertQuery)) {
            // Update room status
            $updateRoomStatusQuery = "UPDATE rooms SET rent_status = 'Rented', User_ID = '$userID' WHERE Room_ID = '$roomID'";
            if (mysqli_query($conn, $updateRoomStatusQuery)) {
                $_SESSION['message'] = "Invoice added and room status updated to 'Rented' successfully!";
                header("Location: index.php?page=invoices");
                exit;
            } else {
                $_SESSION['message'] = "Error updating room status: " . mysqli_error($conn);
                header("Location: index.php?page=invoices");
                exit;
            }
        } else {
            $_SESSION['message'] = "Error adding invoice: " . mysqli_error($conn);
            header("Location: index.php?page=invoices");
            exit;
        }
    } else {
        $_SESSION['message'] = "Error: Room not found.";
        header("Location: index.php?page=invoices");
        exit;
    }
}



if (isset($_POST['delete_payment'])) {
    $invoiceId = $_POST['invoice_id'];

    // Get Room_ID before deleting the invoice
    $roomQuery = "SELECT Room_ID FROM invoices WHERE InvoiceID = '$invoiceId'";
    $roomResult = mysqli_query($conn, $roomQuery);

    if ($roomRow = mysqli_fetch_assoc($roomResult)) {
        $roomID = $roomRow['Room_ID'];

        // Delete the invoice
        $deleteInvoiceQuery = "DELETE FROM invoices WHERE InvoiceID = '$invoiceId'";
        if (mysqli_query($conn, $deleteInvoiceQuery)) {

            // Update the room status back to 'Available' and remove assigned tenant
            $updateRoomQuery = "UPDATE rooms SET rent_status = 'Available', User_ID = NULL WHERE Room_ID = '$roomID'";
            if (mysqli_query($conn, $updateRoomQuery)) {
                $_SESSION['message'] = "Invoice deleted and room set to 'Available'.";
            } else {
                $_SESSION['message'] = "Error updating room status: " . mysqli_error($conn);
            }
        } else {
            $_SESSION['message'] = "Error deleting invoice: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['message'] = "Invoice not found.";
    }

    header("Location: index.php?page=invoices");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Management</title>
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

        h3,
        h5,
        input,
        .btn,
        table,
        label,
        #selectTenant,
        select,
        .alert {
            font-style: italic;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
        }

        .btn-danger {
            background-color: #dc3545;
            border: none;
        }

        .table th {
            background-color: #343a40;
            color: white;
        }

        table {
            text-align: center;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
            animation: fadeIn 0.4s ease;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 100px auto;
            padding: 30px;
            border: 1px solid #888;
            max-width: 40%;
            animation: slideDown 0.4s ease;
        }

        .modal-content label {
            margin: 0;
        }

        .modal-content h5 {
            margin: 20px auto 40px auto;
        }

        #addPaymentIcon {
            margin-right: 5px;
        }

        @keyframes fadeIn {
            from {
                background-color: rgba(0, 0, 0, 0);
            }

            to {
                background-color: rgba(0, 0, 0, 0.4);
            }
        }

        @keyframes fadeOut {
            from {
                background-color: rgba(0, 0, 0, 0.4);
            }

            to {
                background-color: rgba(0, 0, 0, 0);
            }
        }

        .modal.fade-in {
            animation: fadeIn 0.4s ease forwards;
        }

        .modal.fade-out {
            animation: fadeOut 0.4s ease forwards;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20%);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideUp {
            from {
                opacity: 1;
                transform: translateY(0);
            }

            to {
                opacity: 0;
                transform: translateY(-20%);
            }
        }

        .modal-content.slide-down {
            animation: slideDown 0.4s ease forwards;
        }

        .modal-content.slide-up {
            animation: slideUp 0.4s ease forwards;
        }

        .edit_btn {
            text-align: right;
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
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <?php
                echo $_SESSION['message'];
                unset($_SESSION['message']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <div class="container">
            <h3 class="mb-4 d-flex justify-content-between align-items-center">

                Tenant Payment Management
                <div>
                    <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST["search"])): ?>
                        <a href="index.php?page=invoices" class="btn btn-secondary me-2">
                            <i class="bi bi-arrow-clockwise"></i> Reset Search
                        </a>
                    <?php endif; ?>
                    <button class="btn btn-success" id="addPaymentBtn">
                        <i class="bi bi-currency-dollar" id="addPaymentIcon"></i> New Payment
                    </button>
                </div>
            </h3>

            <div class="mb-4 p-3 bg-light rounded">
                <form method="POST" action="index.php?page=invoices">
                    <input type="text" class="form-control" placeholder="Search by Month (e.g. March or Mar)" name="search" value="<?php echo isset($_POST['search']) ? $_POST['search'] : ''; ?>">
                </form>
            </div>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Invoice</th>
                        <th>Date</th>
                        <th>Tenant</th>
                        <th>Room No</th>
                        <th>Amount</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch invoices from the database with tenant names
                    $searchDate = isset($_POST['search']) ? strtolower(trim($_POST['search'])) : '';
                    $monthNumber = '';

                    // Convert full or short month name to number
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

                    // Start building query
                    $query = "SELECT invoices.InvoiceID, invoices.Invoice_Date, invoices.amount, users.name, rooms.RoomNo
                    FROM invoices 
                    JOIN users ON invoices.UserID = users.User_ID
                    JOIN rooms ON invoices.Room_ID = rooms.Room_ID
                    WHERE invoices.status = 'Paid'";

                    // If a valid month is found, search for that month
                    if (!empty($monthNumber)) {
                        $query .= " AND MONTH(invoices.Invoice_Date) = '$monthNumber'";
                    } elseif (!empty($searchDate)) {
                        // fallback to full LIKE search (for partial YYYY-MM-DD or year-only)
                        $query .= " AND invoices.Invoice_Date LIKE '%$searchDate%'";
                    }

                    $query .= " ORDER BY invoices.Invoice_Date ASC";

                    $result = mysqli_query($conn, $query);
                    $count = 1;

                    // Check if there are any records returned
                    if (mysqli_num_rows($result) > 0) {
                        // If there are results, loop through them and display in table rows
                        while ($payment = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= $count++ ?></td>
                                <td><?= $payment['InvoiceID'] ?></td>
                                <td><?= date('M d, Y', strtotime($payment['Invoice_Date'])) ?></td>
                                <td><strong><?= $payment['name'] ?></strong></td> <!-- Tenant Name -->
                                <td><strong><?= $payment['RoomNo'] ?></strong></td> <!-- Room Paid For -->
                                <td>RM <?= number_format($payment['amount'], 2) ?></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="invoice_id" value="<?= $payment['InvoiceID'] ?>">
                                        <button type="submit" name="delete_payment" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile;
                    } else { ?>
                        <tr>
                            <td colspan="7" class="text-center">No payments found.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

            <!-- Add Payment Modal -->
            <div id="addPaymentModal" class="modal">
                <div class="modal-content">
                    <h5>Add New Payment</h5>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="selectTenant" class="form-label">Tenant</label>
                            <select class="form-control" id="selectTenant" name="user_id" required>
                                <option value="">Select Tenant</option>
                                <?php
                                // Fetch all tenants from the users table
                                $tenantQuery = "SELECT User_ID, name FROM users WHERE Role = 'user'";
                                $tenantResult = mysqli_query($conn, $tenantQuery);

                                while ($tenant = mysqli_fetch_assoc($tenantResult)) {
                                    echo "<option value='{$tenant['User_ID']}'>{$tenant['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="selectRoom" class="form-label">Room</label>
                            <select class="form-control" id="selectRoom" name="room_id" required>
                                <option value="">-- Select Room --</option>
                                <?php
                                // Fetch only available rooms from the rooms table
                                // Assuming rooms table has a 'status' field to indicate if the room is available or not
                                $roomQuery = "SELECT Room_ID, RoomNo FROM rooms WHERE rent_status = 'Available'";
                                $roomResult = mysqli_query($conn, $roomQuery);

                                while ($room = mysqli_fetch_assoc($roomResult)) {
                                    echo "<option value='{$room['Room_ID']}'>{$room['RoomNo']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="invoiceNumber" class="form-label">Invoice Number</label>
                            <input type="number" class="form-control" id="invoiceNumber" name="invoice_number" required>
                        </div>
                        <div class="mb-3">
                            <label for="amountPaid" class="form-label">Amount Paid</label>
                            <input type="number" class="form-control" id="amountPaid" name="amount_paid" required>
                        </div>
                        <div class="edit_btn">
                            <button class="btn btn-primary" id="saveNewPayment" name="add_payment">Save</button>
                            <button class="btn btn-secondary" id="closeAddPaymentModal">Cancel</button>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>

    <script>
        var addPaymentModal = document.getElementById("addPaymentModal");
        var addPaymentBtn = document.getElementById("addPaymentBtn");
        var closeAddPaymentBtn = document.getElementById("closeAddPaymentModal");

        addPaymentBtn.onclick = function() {
            addPaymentModal.style.display = "block";
            setTimeout(function() {
                addPaymentModal.classList.add('fade-in');
                addPaymentModal.querySelector('.modal-content').classList.add('slide-down');
            }, 0);
        };

        closeAddPaymentBtn.onclick = function() {
            addPaymentModal.querySelector('.modal-content').classList.remove('slide-down');
            addPaymentModal.querySelector('.modal-content').classList.add('slide-up');
            addPaymentModal.classList.remove('fade-in');
            addPaymentModal.classList.add('fade-out');
            setTimeout(function() {
                addPaymentModal.style.display = "none";
                addPaymentModal.classList.remove('fade-out');
                addPaymentModal.querySelector('.modal-content').classList.remove('slide-up');
            }, 400);
        };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/preloader.js"></script>
</body>