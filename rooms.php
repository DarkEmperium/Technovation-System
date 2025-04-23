<?php
if (!isset($_SESSION["role"])) {
    header("Location: landing_page.php");
    exit();
}

$edit_mode = false;
$edit_id = null;
$edit_room_number = "";
$edit_house_id = "";
$edit_price = "";

$house_result = mysqli_query($conn, "SELECT * FROM houses");

if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $edit_mode = true;

    $edit_sql = "SELECT * FROM rooms WHERE Room_ID = $edit_id";
    $edit_result = mysqli_query($conn, $edit_sql);
    if ($edit_row = mysqli_fetch_assoc($edit_result)) {
        $edit_room_number = $edit_row['RoomNo'];
        $edit_house_id = $edit_row['House_ID'];
        $edit_price = $edit_row['price'];
    }
}

if (isset($_POST["save"])) {
    $room_no = $_POST["roomNo"];
    $price = $_POST["price"];
    $house_id = $_POST["house_id"]; // foreign key

    if (!preg_match('/^[a-zA-Z0-9]+$/', $_POST['roomNo'])) {
        $_SESSION['message'] = "Invalid Room Number : No special characters allowed !";
        header("Location: index.php?page=rooms");
        exit();
    }

    if (isset($_POST['Room_ID']) && $_POST['Room_ID'] != '') {
        // --- Update existing room ---
        $room_id = $_POST['Room_ID'];
        $sql = "UPDATE rooms SET RoomNo = '$room_no', price = '$price', House_ID = '$house_id' WHERE Room_ID = $room_id";
        if (mysqli_query($conn, $sql)) {
            $_SESSION['message'] = "Room updated successfully !";
            header("Location: index.php?page=rooms");
            exit();
        } else {
            echo "SQL Error: " . mysqli_error($conn);
        }
    } else {
        // --- Add new room only if current room count < availableRoom ---
        $count_sql = "SELECT COUNT(*) AS room_count FROM rooms WHERE House_ID = $house_id";
        $count_result = mysqli_query($conn, $count_sql);
        $count_data = mysqli_fetch_assoc($count_result);
        $current_room_count = $count_data['room_count'];

        $check_sql = "SELECT availableRoom FROM houses WHERE House_ID = $house_id";
        $check_result = mysqli_query($conn, $check_sql);
        $house_data = mysqli_fetch_assoc($check_result);
        $available_rooms = $house_data['availableRoom'];

        if ($current_room_count < $available_rooms) {
            // Insert room
            $insert_sql = "INSERT INTO rooms (RoomNo, price, House_ID) VALUES ('$room_no', '$price', '$house_id')";
            if (mysqli_query($conn, $insert_sql)) {
                $_SESSION['message'] = "Room added successfully !";
                header("Location: index.php?page=rooms");
                exit();
            } else {
                echo "SQL Error: " . mysqli_error($conn);
            }
        } else {
            // Not enough availability
            $_SESSION['message'] = "No available rooms in this house !";
            header("Location: index.php?page=rooms");
            exit();
        }
    }
}
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];

    // Check if the room is occupied before deleting
    $check_sql = "SELECT rent_status FROM rooms WHERE Room_ID = $delete_id";
    $check_result = mysqli_query($conn, $check_sql);
    $room = mysqli_fetch_assoc($check_result);

    if ($room && $room['rent_status'] === 'Rented') {
        $_SESSION['message'] = "Cannot delete an occupied room !";
    } else {
        $del_sql = "DELETE FROM rooms WHERE Room_ID = $delete_id";
        mysqli_query($conn, $del_sql);
        $_SESSION['message'] = "Room deleted successfully !";
    }

    header("Location: index.php?page=rooms"); // Redirect to the rooms page
    exit();
}


$search = "";
if (isset($_POST['search']) && !empty(trim($_POST['search']))) {
    $search = mysqli_real_escape_string($conn, $_POST['search']);
    $sql = "SELECT rooms.*, houses.HouseName 
            FROM rooms 
            JOIN houses ON rooms.House_ID = houses.House_ID 
            WHERE rooms.RoomNo LIKE '%$search%' 
               OR houses.HouseName LIKE '%$search%' 
               OR rooms.price LIKE '%$search%'";
} else {
    $sql = "SELECT rooms.*, houses.HouseName FROM rooms JOIN houses ON rooms.House_ID = houses.House_ID";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>House Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/preloader.css">
    <link rel="icon" type="image/x-icon" href="assets/images/logo.png">
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

        #category-label {
            margin-left: 5px;
            margin-bottom: 20px;
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

        .btn-width {
            width: 100px;
        }

        .room-title-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
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
        <!--Error Message-->
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
            <h3 class="mb-4">Room Unit Management</h3>

            <div class="row">

                <div class="col-md-5">
                    <div class="mb-4 p-3 bg-light rounded">
                        <h5 id="category-label">Add & Edit Room</h5>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="roomNo" class="form-label">Room Number</label>
                                <input type="text" name="roomNo" class="form-control" id="roomNo" placeholder="Enter Room Number" required
                                    value="<?php echo htmlspecialchars($edit_room_number); ?>">
                                <?php if ($edit_mode): ?>
                                    <input type="hidden" name="Room_ID" value="<?php echo $edit_id; ?>">
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Select House</label>
                                <select name="house_id" class="form-control" required>
                                    <option value="">-- Select House --</option>
                                    <?php while ($house = mysqli_fetch_assoc($house_result)): ?>
                                        <option value="<?php echo $house['House_ID']; ?>" <?php if ($edit_house_id == $house['House_ID'])
                                                                                                echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($house['HouseName']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="housePrice" class="form-label">Price</label>
                                <input type="number" name="price" class="form-control" id="housePrice" placeholder="Enter Price" required value="<?php echo htmlspecialchars($edit_price); ?>" min="0" step="0.01">
                            </div>

                            <button class="btn btn-primary btn-width" name="save"><?php echo $edit_mode ? "Update" : "Save"; ?></button>
                            <a href="index.php?page=rooms" class="btn btn-secondary btn-width">Cancel</a>
                        </form>
                    </div>
                </div>

                <div class="col-md-7">
                
                    <div class="room-title-wrapper">
                        <h5>Room List</h5>

                        <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST["search"])): ?>
                            <a href="index.php?page=rooms" class="btn btn-secondary me-2">
                                <i class="bi bi-arrow-clockwise"></i> Reset Search
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4 p-3 bg-light rounded">
                        <form method="POST" action="index.php?page=rooms">
                            <input type="text" class="form-control" placeholder="Search by Categories" name="search" value="<?php echo isset($_POST['search']) ? $_POST['search'] : ''; ?>">
                        </form>
                    </div>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Room</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result = mysqli_query($conn, $sql);
                            $i = 1;

                            if (mysqli_num_rows($result) > 0) {
                                // Display each room
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>";
                                    echo "<td><em>" . $i++ . "</em></td>";
                                    echo "<td>
                                        <strong>Room :</strong> " . htmlspecialchars($row['RoomNo']) . "<br>
                                        <strong>Category :</strong> " . htmlspecialchars($row['HouseName']) . "<br>
                                        <strong>Price :</strong> RM " . number_format($row['price'], 2) . "
                                        </td>";
                                    echo '<td>
                                        <a href="?page=rooms&edit=' . $row['Room_ID'] . '" class="btn btn-primary btn-sm" name="edit">Edit</a>
                                        <a href="?page=rooms&delete=' . $row['Room_ID'] . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure?\')" name="delete">Delete</a>
                                        </td>';
                                    echo "</tr>";
                                }
                            } else {
                                // If no rooms exist, show this message
                                echo "<tr><td colspan='3' class='text-center'>No rooms available in the system.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/preloader.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>