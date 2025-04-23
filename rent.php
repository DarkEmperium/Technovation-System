<?php

if (!isset($_SESSION["role"])) {
    header("Location: landing_page.php");
    exit();
}

$user_id = $_SESSION['User_ID'];

if (isset($_POST['rent'])) {
    $room_id = $_POST['selectedRoom'];

    if (!empty($room_id)) {
        // Check if room is available
        $check_sql = "SELECT * FROM rooms WHERE Room_ID = $room_id AND rent_status = 'Available'";
        $check_result = mysqli_query($conn, $check_sql);

        if (mysqli_num_rows($check_result) > 0) {
            // Fetch room details
            $room_data = mysqli_fetch_assoc($check_result);
            $room_price = $room_data['price'];
            $house_id = $room_data['House_ID']; // Get the House_ID of the rented room

            // Fetch the current month's utility bills for the house
            $utility_sql = "SELECT water_bill, electric_bill 
                            FROM utilities 
                            WHERE House_ID = $house_id AND month = '" . date('Y-m') . "'"; // Current month
            $utility_result = mysqli_query($conn, $utility_sql);

            if (mysqli_num_rows($utility_result) > 0) {
                $utility_data = mysqli_fetch_assoc($utility_result);
                $water_bill = $utility_data['water_bill'];
                $electric_bill = $utility_data['electric_bill'];
            } else {
                // If no utility record exists for this house in the current month, set default values (0)
                $water_bill = 0;
                $electric_bill = 0;
            }

            // Calculate total invoice amount (room price + utility bills)
            $total_amount = $room_price + $water_bill + $electric_bill;

            // Assign the room to the tenant and update rent_status
            $update_sql = "UPDATE rooms SET User_ID = $user_id, rent_status = 'Rented' WHERE Room_ID = $room_id";
            mysqli_query($conn, $update_sql);

            // Insert the invoice record with the calculated total amount
            $invoice_date = date("Y-m-d"); // Current date
            $status = "Pending"; // Mark as pending until payment is confirmed

            // Insert invoice into the invoices table
            $insert_invoice_sql = "INSERT INTO invoices (UserID, Room_ID, Invoice_Date, status, amount)
                                   VALUES ($user_id, $room_id, '$invoice_date', '$status', $total_amount)";
            mysqli_query($conn, $insert_invoice_sql);

            // Success message and redirect
            $_SESSION['message'] = "Room rented successfully with utility charges !";
            header("Location: index.php?page=rent");
            exit();
        } else {
            $_SESSION['message'] = "Room is already rented !";
        }
    } else {
        $_SESSION['message'] = "Please select a room !";
    }
}


// Fetch rooms rented by this user
$rented_sql = "SELECT users.name, rooms.RoomNo, houses.HouseName, rooms.price 
               FROM rooms 
               JOIN houses ON rooms.House_ID = houses.House_ID 
               JOIN users ON rooms.User_ID = users.User_ID 
               WHERE rooms.User_ID = $user_id";
$rented_result = mysqli_query($conn, $rented_sql);

// Fetch available rooms (only those with rent_status = 'Available')
$available_sql = "SELECT rooms.Room_ID, rooms.RoomNo, houses.HouseName, rooms.price 
                  FROM rooms 
                  JOIN houses ON rooms.House_ID = houses.House_ID 
                  WHERE rooms.rent_status = 'Available'";
$available_result = mysqli_query($conn, $available_sql);

// Fetch all available rooms for display
$all_available_sql = "SELECT rooms.RoomNo, houses.HouseName, rooms.price
                      FROM rooms 
                      JOIN houses ON rooms.House_ID = houses.House_ID 
                      WHERE rooms.rent_status = 'Available'";
$all_available_result = mysqli_query($conn, $all_available_sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Rent Portal</title>
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
        .alert,
        select {
            font-style: italic;
        }

        table {
            text-align: center;
        }

        #category-label {
            margin-left: 5px;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
        }

        .table th {
            background-color: #343a40;
            color: white;
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
            <h3 class="mb-4">Room Rental Portal</h3>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['message'];
                    unset($_SESSION['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-5">
                    <div class="mb-4 p-3 bg-light rounded">
                        <h5 id="category-label">Rent a Unit</h5>
                        <form method="POST">
                            <div class="mb-3">
                                <select class="form-control" id="selectedRoom" name="selectedRoom" required>
                                    <option value="">-- Select Room --</option>
                                    <?php while ($room = mysqli_fetch_assoc($available_result)): ?>
                                        <option value="<?php echo $room['Room_ID']; ?>">
                                            <?php echo htmlspecialchars($room['RoomNo']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>

                            </div>
                            <button type="submit" name="rent" class="btn btn-primary">Proceed to Rent</button>
                        </form>
                    </div>
                </div>

                <div class="col-md-7">
                    <h5>Your Rented Rooms</h5>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Room</th>
                                <th>Category</th>
                                <th>Tenant Name</th>
                                <th>Price (RM)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                            if (mysqli_num_rows($rented_result) > 0):
                                while ($rented = mysqli_fetch_assoc($rented_result)): ?>
                                    <tr>
                                        <td><?php echo $i++; ?></td>
                                        <td><?php echo htmlspecialchars($rented['RoomNo']); ?></td>
                                        <td><?php echo htmlspecialchars($rented['HouseName']); ?></td>
                                        <td><?php echo htmlspecialchars($rented['name']); ?></td>
                                        <td><?php echo number_format($rented['price'], 2); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No rented rooms found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>

                    </table>
                </div>
            </div>

            <!-- Show All Available Rooms (Corrected Section) -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <h5>Overview of All Available Rooms</h5>
                    <?php if (mysqli_num_rows($all_available_result) > 0): ?>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Room</th>
                                    <th>Category</th>
                                    <th>Price (RM)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $k = 1;
                                while ($available = mysqli_fetch_assoc($all_available_result)): ?>
                                    <tr>
                                        <td><?php echo $k++; ?></td>
                                        <td><?php echo htmlspecialchars($available['RoomNo']); ?></td>
                                        <td><?php echo htmlspecialchars($available['HouseName']); ?></td>
                                        <td><?php echo number_format($available['price'], 2); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="alert alert-info">No available rooms at the moment.</div>
                    <?php endif; ?>
                </div>
            </div>


        </div>
    </div>

    <script src="assets/js/preloader.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>