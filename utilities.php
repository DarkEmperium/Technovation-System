<?php
if (!isset($_SESSION["role"])) {
    header("Location: landing_page.php");
    exit();
}

$house_result = mysqli_query($conn, "SELECT * FROM houses");
if (isset($_POST['save'])) {
    $month_input = $_POST["month"]; // Get the text input for month
    $house_id = $_POST["house_id"];
    $water_bill = $_POST["wotahbill"];
    $electric_bill = $_POST["electribill"];

    // Define month names and their corresponding numbers
    $months = [
        'january' => '01',
        'jan' => '01',
        '1' => '01',
        'february' => '02',
        'feb' => '02',
        '2' => '02',
        'march' => '03',
        'mar' => '03',
        '3' => '03',
        'april' => '04',
        'apr' => '04',
        '4' => '04',
        'may' => '05',
        '5' => '05',
        'june' => '06',
        'jun' => '06',
        '6' => '06',
        'july' => '07',
        'jul' => '07',
        '7' => '07',
        'august' => '08',
        'aug' => '08',
        '8' => '08',
        'september' => '09',
        'sep' => '09',
        '9' => '09',
        'october' => '10',
        'oct' => '10',
        '10' => '10',
        'november' => '11',
        'nov' => '11',
        '11' => '11',
        'december' => '12',
        'dec' => '12',
        '12' => '12'
    ];

    // Convert month input to lowercase
    $month_input = strtolower(trim($month_input));

    // Check if the entered month is valid
    if (array_key_exists($month_input, $months)) {
        $month_number = $months[$month_input];
        $year = date('Y');
        $formatted_month = $year . '-' . $month_number; // Combine year and month
    } else {
        $_SESSION['message'] = 'Invalid month name !';
        header("Location: index.php?page=utilities");
        exit();
    }

    // Check if the utility record already exists for this house and month
    $check_sql = "SELECT * FROM utilities WHERE House_ID = '$house_id' AND month = '$formatted_month'";
    $check_result = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($check_result) > 0) {
        // If a record exists, show an error message and don't insert
        $_SESSION['message'] = 'Utility bill for this house already exists for the selected month !';
        header("Location: index.php?page=utilities");
        exit();
    }

    // Insert data into the database
    $sql = "INSERT INTO utilities (House_ID, month, water_bill, electric_bill) 
            VALUES ('$house_id', '$formatted_month', '$water_bill', '$electric_bill')";
    if (mysqli_query($conn, $sql)) {
        // Get all rented rooms for this house
        $rooms_query = "SELECT Room_ID FROM rooms WHERE House_ID = '$house_id' AND rent_status = 'Rented'";
        $rooms_result = mysqli_query($conn, $rooms_query);

        while ($room = mysqli_fetch_assoc($rooms_result)) {
            $room_id = $room['Room_ID'];

            // Only update invoice if it exists for this room and month
            $invoice_exists_query = "
            SELECT 1 FROM invoices
            WHERE Room_ID = '$room_id' 
              AND DATE_FORMAT(Invoice_Date, '%Y-%m') = '$formatted_month'
            LIMIT 1
        ";
            $invoice_exists_result = mysqli_query($conn, $invoice_exists_query);

            if (mysqli_num_rows($invoice_exists_result) > 0) {
                $update_invoice_sql = "
                UPDATE invoices
                SET amount = amount + $water_bill + $electric_bill
                WHERE Room_ID = '$room_id'
                AND DATE_FORMAT(Invoice_Date, '%Y-%m') = '$formatted_month'
            ";
                mysqli_query($conn, $update_invoice_sql);
            }
        }

        $_SESSION['message'] = 'Utility bill added successfully and invoices updated !';
    } else {
        $_SESSION['message'] = 'Failed to add utility bill !';
    }
}

// DELETE utility record
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $delete_sql = "DELETE FROM utilities WHERE UtilitiesID = $delete_id";
    if (mysqli_query($conn, $delete_sql)) {
        $_SESSION['message'] = 'Utility bill deleted successfully !';
    } else {
        $_SESSION['message'] = 'Failed to delete utility bill !';
    }
    header("Location: index.php?page=utilities"); // refresh page
    exit();
}
// FETCH existing utility for editing
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $fetch_sql = "SELECT utilities.*, houses.HouseName FROM utilities 
                  JOIN houses ON utilities.House_ID = houses.House_ID 
                  WHERE UtilitiesID = $edit_id";
    $fetch_result = mysqli_query($conn, $fetch_sql);
    $edit_data = mysqli_fetch_assoc($fetch_result);
}
// UPDATE utility record
if (isset($_POST['update'])) {
    $utility_id = $_POST['utility_id'];
    $water_bill = $_POST['edit_waterbill'];
    $electric_bill = $_POST['edit_electricbill'];

    // Get current utility data to compare and update invoices accordingly
    $fetch_sql = "SELECT House_ID, month, water_bill AS old_water_bill, electric_bill AS old_electric_bill 
                  FROM utilities WHERE UtilitiesID = $utility_id";
    $fetch_result = mysqli_query($conn, $fetch_sql);
    $utility_data = mysqli_fetch_assoc($fetch_result);
    $house_id = $utility_data['House_ID'];
    $formatted_month = $utility_data['month'];
    $old_water_bill = $utility_data['old_water_bill'];
    $old_electric_bill = $utility_data['old_electric_bill'];

    // Update utility bill
    $update_sql = "UPDATE utilities SET water_bill = '$water_bill', electric_bill = '$electric_bill' 
                   WHERE UtilitiesID = $utility_id";

    if (mysqli_query($conn, $update_sql)) {
        // Get all rented rooms for this house
        $rooms_query = "SELECT Room_ID FROM rooms WHERE House_ID = '$house_id' AND rent_status = 'Rented'";
        $rooms_result = mysqli_query($conn, $rooms_query);

        while ($room = mysqli_fetch_assoc($rooms_result)) {
            $room_id = $room['Room_ID'];

            // Check if an invoice already exists for this room and month
            $invoice_exists_query = "
                SELECT 1 FROM invoices
                WHERE Room_ID = '$room_id' 
                  AND DATE_FORMAT(Invoice_Date, '%Y-%m') = '$formatted_month'
                LIMIT 1
            ";
            $invoice_exists_result = mysqli_query($conn, $invoice_exists_query);

            if (mysqli_num_rows($invoice_exists_result) > 0) {
                // Subtract the old utility amounts first, then add the new ones
                $update_invoice_sql = "
                    UPDATE invoices
                    SET amount = amount - $old_water_bill - $old_electric_bill + $water_bill + $electric_bill
                    WHERE Room_ID = '$room_id'
                    AND DATE_FORMAT(Invoice_Date, '%Y-%m') = '$formatted_month'
                ";
                mysqli_query($conn, $update_invoice_sql);
            }
        }

        $_SESSION['message'] = 'Utility bill updated successfully and invoices updated !';
    } else {
        $_SESSION['message'] = 'Failed to update utility bill !';
    }

    header("Location: index.php?page=utilities");
    exit();
}

$searchDate = isset($_POST['search']) ? strtolower(trim($_POST['search'])) : '';
$monthNumber = '';

// Check if there's a valid search term
if (!empty($searchDate)) {
    // Convert numbers directly to zero-padded months
    if (is_numeric($searchDate)) {
        $monthNumber = str_pad($searchDate, 2, "0", STR_PAD_LEFT); // Converts '6' to '06'
    } else {
        // Convert month names to numbers
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
    }

    // If the search term maps to a valid month
    if (!empty($monthNumber)) {
        $year = date('Y'); // Get current year or allow users to input a year
        $searchPattern = $year . '-' . $monthNumber; // Format search term as 'YYYY-MM'
        $sql = "SELECT utilities.*, houses.HouseName 
                FROM utilities 
                JOIN houses ON utilities.House_ID = houses.House_ID
                WHERE utilities.month LIKE '%$searchPattern%'";
    } else {
        // Invalid input or search term; show no data
        $sql = "SELECT utilities.*, houses.HouseName FROM utilities 
                JOIN houses ON utilities.House_ID = houses.House_ID WHERE 1 = 0";
    }
} else {
    // No search input; default to show all records
    $sql = "SELECT utilities.*, houses.HouseName 
            FROM utilities 
            JOIN houses ON utilities.House_ID = houses.House_ID";
}

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Utilities Management</title>
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

        .edit_btn {
            display: flex;
            justify-content: right;
            margin-top: 50px;
        }

        .edit_btn button {
            margin-left: 20px;
            width: 90px;
        }

        .modal-content label {
            margin: 0;
        }

        .modal-content h5 {
            margin: 20px auto 40px auto;
        }

        #addUtilityIcon {
            margin-right: 5px;
        }

        .form-control:placeholder-shown {
            color: #aaa;
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
        <div class="container">
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['message'];
                    unset($_SESSION['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <h3 class="mb-4 d-flex justify-content-between align-items-center">
                Utilities Management
                <div>
                    <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST["search"])): ?>
                        <a href="index.php?page=utilities" class="btn btn-secondary me-2">
                            <i class="bi bi-arrow-clockwise"></i> Reset Search
                        </a>
                    <?php endif; ?>
                    <button class="btn btn-success" id="addUtilityBtn">
                        <i class="bi bi-lightning-fill" id="addUtilityIcon"></i> Add Utility Bill
                    </button>
                </div>
            </h3>
            <div class="mb-4 p-3 bg-light rounded">
                <form method="POST" action="index.php?page=utilities">
                    <input type="text" class="form-control" id="monthInput" placeholder="Search by Month (e.g. March or Mar)" name="search" value="<?= isset($_POST['search']) ? htmlspecialchars($_POST['search']) : '' ?>">
                </form>
            </div>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Month</th>
                        <th>House Category</th>
                        <th>Water Bill (RM)</th>
                        <th>Electric Bill (RM)</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php

                    $i = 1;

                    // Check if there are any records
                    if (mysqli_num_rows($result) > 0) {
                        // If there are results, loop through and display each row
                        while ($row = mysqli_fetch_assoc($result)) {
                            $formattedMonth = date("F Y", strtotime($row['month']));

                            echo "<tr>";
                            echo "<td>" . $i++ . "</td>";
                            echo "<td>" . htmlspecialchars($formattedMonth) . "</td>";
                            echo "<td>" . htmlspecialchars($row['HouseName']) . "</td>";
                            echo "<td>" . number_format($row['water_bill'], 2) . "</td>";
                            echo "<td>" . number_format($row['electric_bill'], 2) . "</td>";
                            echo '
                    <td>
                        <a href="?page=utilities&edit=' . $row['UtilitiesID'] . '" class="btn btn-primary btn-sm">Edit</a>
                        <a href="?page=utilities&delete=' . $row['UtilitiesID'] . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure you want to delete this record ?\')">Delete</a>
                    </td>';
                            echo "</tr>";
                        }
                    } else {
                        // If no records are found, display a message
                        echo '<tr>';
                        echo '<td colspan="6" class="text-center">No utility records found.</td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>

            </table>

            <!-- Add Utility Modal -->
            <div id="addUtilityModal" class="modal">
                <div class="modal-content">
                    <h5>Add New Utility Bill</h5>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="utilityMonth" class="form-label">Month</label>
                            <input name="month" type="text" class="form-control" id="utilityMonth" placeholder="Enter month (e.g., June)" required>
                        </div>

                        <div class="mb-3">
                            <label for="houseCategory" class="form-label">House Category</label>
                            <select name="house_id" class="form-control" required>
                                <option value="">-- Select House --</option>
                                <?php while ($house = mysqli_fetch_assoc($house_result)): ?>
                                    <option value="<?php echo $house['House_ID']; ?>">
                                        <?php echo htmlspecialchars($house['HouseName']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="waterBill" class="form-label">Water Bill (RM)</label>
                            <input type="number" class="form-control" id="waterBill" name="wotahbill" required>
                        </div>
                        <div class="mb-3">
                            <label for="electricBill" class="form-label">Electric Bill (RM)</label>
                            <input type="number" class="form-control" id="electricBill" name="electribill" required>
                        </div>
                        <div class="edit_btn">
                            <button class="btn btn-primary" id="saveNewUtility" name="save" type="submit">Save</button>
                            <button class="btn btn-secondary" id="closeAddUtilityModal" type="button">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Edit Utility Modal -->
            <?php if ($edit_data): ?>
                <script>
                    window.onload = function() {
                        document.getElementById("editUtilityModal").style.display = "block";
                        setTimeout(function() {
                            document.getElementById("editUtilityModal").classList.add('fade-in');
                            document.querySelector("#editUtilityModal .modal-content").classList.add('slide-down');
                        }, 0);
                    };
                </script>
                <div id="editUtilityModal" class="modal">
                    <div class="modal-content">
                        <h5>Edit Utility Bill</h5>
                        <form method="POST">
                            <input type="hidden" name="utility_id" value="<?php echo $edit_data['UtilitiesID']; ?>">
                            <div class="mb-3">
                                <label class="form-label">Month</label>
                                <input type="month" class="form-control" value="<?php echo $edit_data['month']; ?>" disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">House Category</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($edit_data['HouseName']); ?>" disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Water Bill (RM)</label>
                                <input type="number" class="form-control" name="edit_waterbill" value="<?php echo $edit_data['water_bill']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Electric Bill (RM)</label>
                                <input type="number" class="form-control" name="edit_electricbill" value="<?php echo $edit_data['electric_bill']; ?>" required>
                            </div>
                            <div class="edit_btn">
                                <button type="submit" class="btn btn-primary" name="update" type="submit">Save</button>
                                <button class="btn btn-secondary" id="closeEditUtilityModal" type="button">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        var addUtilityModal = document.getElementById("addUtilityModal");
        var addUtilityBtn = document.getElementById("addUtilityBtn");
        var closeAddUtilityBtn = document.getElementById("closeAddUtilityModal");
        var editUtilityModal = document.getElementById("editUtilityModal");
        var closeEditUtilityBtn = document.getElementById("closeEditUtilityModal");
        var editButtons = document.querySelectorAll(".edit-utility");
        var monthInput = document.getElementById("monthInput");

        if (!monthInput.value) {
            monthInput.type = "text";
        }

        addUtilityBtn.onclick = function() {
            addUtilityModal.style.display = "block";
            setTimeout(function() {
                addUtilityModal.classList.add('fade-in');
                addUtilityModal.querySelector('.modal-content').classList.add('slide-down');
            }, 0);
        };

        closeAddUtilityBtn.onclick = function() {
            addUtilityModal.querySelector('.modal-content').classList.remove('slide-down');
            addUtilityModal.querySelector('.modal-content').classList.add('slide-up');
            addUtilityModal.classList.remove('fade-in');
            addUtilityModal.classList.add('fade-out');
            setTimeout(function() {
                addUtilityModal.style.display = "none";
                addUtilityModal.classList.remove('fade-out');
                addUtilityModal.querySelector('.modal-content').classList.remove('slide-up');
            }, 400);
        };

        editButtons.forEach(button => {
            button.onclick = function() {
                editUtilityModal.style.display = "block";
                setTimeout(function() {
                    editUtilityModal.classList.add('fade-in');
                    editUtilityModal.querySelector('.modal-content').classList.add('slide-down');
                }, 0);
            };
        });

        closeEditUtilityBtn.onclick = function() {
            editUtilityModal.querySelector('.modal-content').classList.remove('slide-down');
            editUtilityModal.querySelector('.modal-content').classList.add('slide-up');
            editUtilityModal.classList.remove('fade-in');
            editUtilityModal.classList.add('fade-out');
            setTimeout(function() {
                editUtilityModal.style.display = "none";
                editUtilityModal.classList.remove('fade-out');
                editUtilityModal.querySelector('.modal-content').classList.remove('slide-up');
            }, 400);
        };
    </script>

    <script>
        var addUtilityModal = document.getElementById("addUtilityModal");
        var addUtilityBtn = document.getElementById("addUtilityBtn");
        var closeAddUtilityBtn = document.getElementById("closeAddUtilityModal");

        addUtilityBtn.onclick = function() {
            addUtilityModal.style.display = "block";
            setTimeout(function() {
                addUtilityModal.classList.add('fade-in');
                addUtilityModal.querySelector('.modal-content').classList.add('slide-down');
            }, 0);
        };

        closeAddUtilityBtn.onclick = function() {
            addUtilityModal.querySelector('.modal-content').classList.remove('slide-down');
            addUtilityModal.querySelector('.modal-content').classList.add('slide-up');
            addUtilityModal.classList.remove('fade-in');
            addUtilityModal.classList.add('fade-out');
            setTimeout(function() {
                addUtilityModal.style.display = "none";
                addUtilityModal.classList.remove('fade-out');
                addUtilityModal.querySelector('.modal-content').classList.remove('slide-up');
            }, 400);
        };
    </script>

    <script src="assets/js/preloader.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>