<?php

if (isset($_POST['tenant_id']) && isset($_POST['room_number']) && !empty($_POST['tenant_id']) && !empty($_POST['room_number'])) {
    $tenantId = $_POST['tenant_id'];
    $roomNumber = $_POST['room_number'];

    // Only update if the new room is different from the current one
    if ($roomNumber != $_POST['current_room']) {
        
        $sqlRemoveCurrentRoom = "UPDATE rooms SET User_ID = NULL, rent_status = 'Available' WHERE RoomNo = '{$_POST['current_room']}'";
        mysqli_query($conn, $sqlRemoveCurrentRoom);

        $sqlAssignNewRoom = "UPDATE rooms SET User_ID = $tenantId, rent_status = 'Rented' WHERE RoomNo = '$roomNumber'";
        mysqli_query($conn, $sqlAssignNewRoom);

        $_SESSION['message'] = 'Room updated successfully!';
    } else {
        $_SESSION['message'] = 'No changes made to the room !';
    }

    header("Location: index.php?page=rented");
    exit();
}

// Fetch tenant data and available rooms
$sql = "SELECT u.User_ID AS id, u.name AS tenant_name, r.RoomNo AS room_number, h.HouseName AS category, r.rent_status AS room_status
        FROM users u
        JOIN rooms r ON u.User_ID = r.User_ID
        JOIN houses h ON r.House_ID = h.House_ID
        WHERE u.Role = 'user'";

$result = $conn->query($sql);

// Fetch available rooms
$availableRoomsSql = "SELECT RoomNo FROM rooms WHERE rent_status = 'Available'";
$availableRoomsResult = $conn->query($availableRoomsSql);
$availableRooms = [];

while ($room = $availableRoomsResult->fetch_assoc()) {
    $availableRooms[] = $room['RoomNo'];
}

// Fetch tenants excluding admins
$tenantSql = "SELECT User_ID, name FROM users WHERE Role != 'admin'";
$tenantResult = $conn->query($tenantSql);



if (isset($_GET['delete_room'])) {
    $roomNo = $conn->real_escape_string($_GET['delete_room']);

    // Get Room_ID using RoomNo
    $getRoomIdSql = "SELECT Room_ID FROM rooms WHERE RoomNo = '$roomNo'";
    $roomIdResult = $conn->query($getRoomIdSql);

    if ($roomIdResult && $roomIdResult->num_rows > 0) {
        $roomData = $roomIdResult->fetch_assoc();
        $roomId = $roomData['Room_ID'];

        // Set room as available and remove tenant
        $updateRoomSql = "UPDATE rooms SET User_ID = NULL, rent_status = 'Available' WHERE RoomNo = '$roomNo'";
        $conn->query($updateRoomSql);

        // Delete related invoices
        $deleteInvoicesSql = "DELETE FROM invoices WHERE Room_ID = $roomId";
        $conn->query($deleteInvoicesSql);

        $_SESSION['message'] = "Room and associated invoices deleted successfully.";
    } else {
        $_SESSION['message'] = "Room not found.";
    }

    header("Location: index.php?page=rented");
    exit();
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Rental Management</title>
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
        #editTenantRoom,
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

        .bi {
            margin-right: 5px;
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
                Room Rental Management
                <div>
                    <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST["search"])): ?>
                        <a href="index.php?page=rented" class="btn btn-secondary me-2">
                            <i class="bi bi-arrow-clockwise"></i> Reset Search
                        </a>
                    <?php endif; ?>
                    <button class="btn btn-success" id="addRoomBtn">
                        <i class="bi bi-door-open"></i> New Room
                    </button>
                </div>
            </h3>


            <div class="mb-4 p-3 bg-light rounded">
                <form method="POST" action="index.php?page=rented">
                    <input type="text" class="form-control" placeholder="Search Tenants" name="search" value="<?php echo isset($_POST['search']) ? $_POST['search'] : ''; ?>">
                </form>
            </div>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tenant</th>
                        <th>Room Number</th>
                        <th>Category</th>
                        <th>Monthly Rate</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                <tbody>
                    <?php
                    
                    $searchQuery = "";
                    if (isset($_POST['search']) && !empty($_POST['search'])) {
                        $search = $conn->real_escape_string($_POST['search']);
                        $searchQuery = " AND u.name LIKE '%$search%'";
                    }
                    $sql = "SELECT 
                                    u.User_ID AS id, 
                                    u.name AS tenant_name, 
                                    r.RoomNo AS room_number, 
                                    h.HouseName AS category, 
                                    r.price AS monthly_rate
                                FROM users u
                                JOIN rooms r ON u.User_ID = r.User_ID
                                JOIN houses h ON r.House_ID = h.House_ID
                                WHERE u.Role = 'user' $searchQuery
                                ORDER BY u.name ASC";

                    $result = $conn->query($sql);

                    $no = 1;
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $no++ . "</td>";
                            echo "<td>" . htmlspecialchars($row['tenant_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['room_number']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['category']) . "</td>";
                            echo "<td>RM " . htmlspecialchars($row['monthly_rate']) . "</td>";
                            
                            echo '<td>
                                <button class="btn btn-primary btn-sm editRoomBtn" 
                                    data-tenant-id="' . $row['id'] . '" 
                                    data-tenant-name="' . htmlspecialchars($row['tenant_name']) . '" 
                                    data-current-room="' . htmlspecialchars($row['room_number']) . '">Edit</button>
                              <a href="index.php?page=rented&delete_room=' . $row['room_number'] . '" class="btn btn-danger btn-sm">Delete</a>

                            </td>';
                        }
                    } else {
                        echo "<tr><td colspan='6'>No tenants found</td></tr>";
                    }

                    $conn->close();
                    ?>
                </tbody>


                </tr>
                </tbody>
            </table>
        </div>


        <div id="addRoomModal" class="modal" style="display: none;">
            <div class="modal-content">
                <h5>Add New Room</h5>
                <form method="POST">

                    <!-- Tenant Dropdown -->
                    <div class="mb-3">
                        <label for="tenantSelect" class="form-label">Select Tenant</label>
                        <select class="form-control" id="tenantSelect" name="tenant_id" required>
                            <option value="">-- Select Tenant --</option>
                            <?php
                            while ($tenant = $tenantResult->fetch_assoc()) {
                                echo "<option value='{$tenant['User_ID']}'>{$tenant['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Select New Room -->
                    <div class="mb-3">
                        <label for="addRoomNumber" class="form-label">Room</label>
                        <select class="form-control" id="addRoomNumber" name="room_number" required>
                            <option value="">-- Select Room --</option>
                            <?php
                            foreach ($availableRooms as $room) {
                                echo "<option value='$room'>$room</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <input type="hidden" name="add_new_room" value="1"> <!-- Flag for adding new room -->

                    <div class="edit_btn">
                        <button class="btn btn-primary" name="save" type="submit">Save</button>
                        <button class="btn btn-secondary" id="closeAddRoomModal" type="button">Cancel</button>
                    </div>
                </form>
            </div>
        </div>


        <div id="editRoomModal" class="modal" style="display: none;">
            <div class="modal-content">
                <h5>Edit Room</h5>
                <form method="POST">
                    <input type="hidden" id="tenantId" name="tenant_id" />

                    <!-- Tenant Name (Read-Only) -->
                    <div class="mb-3">
                        <label for="tenantName" class="form-label">Tenant Name</label>
                        <input type="text" class="form-control" id="tenantName" name="tenant_name" readonly />
                    </div>

                    <!-- Current Room (Read-Only) -->
                    <div class="mb-3">
                        <label for="currentRoom" class="form-label">Current Room</label>
                        <input type="text" class="form-control" id="currentRoom" name="current_room" readonly />
                    </div>

                    <!-- Select New Room -->
                    <div class="mb-3">
                        <label for="editRoomNumber" class="form-label">New Room Number</label>
                        <select class="form-control" id="editRoomNumber" name="room_number" required>
                            <option value="">-- Select Room --</option>
                            <?php
                            foreach ($availableRooms as $room) {
                                echo "<option value='$room'>$room</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="edit_btn">
                        <button class="btn btn-primary" name="save" type="submit">Save</button>
                        <button class="btn btn-secondary" id="closeEditRoomModal" type="button">Cancel</button>
                    </div>
                </form>
            </div>
        </div>



        <script>
            var editRoomModal = document.getElementById("editRoomModal");
            var closeEditRoomBtn = document.getElementById("closeEditRoomModal");
            var editRoomBtns = document.querySelectorAll(".editRoomBtn");
            var addRoomModal = document.getElementById("addRoomModal");
            var closeAddRoomBtn = document.getElementById("closeAddRoomModal");
            var addRoomBtn = document.getElementById("addRoomBtn");

            // Open the Add Room Modal when the 'New Room' button is clicked
            addRoomBtn.onclick = function() {
                addRoomModal.style.display = "block";
                setTimeout(function() {
                    addRoomModal.classList.add('fade-in');
                    addRoomModal.querySelector('.modal-content').classList.add('slide-down');
                }, 0);
            };

            // Close the Add Room Modal when the 'Cancel' button is clicked
            closeAddRoomBtn.onclick = function() {
                addRoomModal.querySelector('.modal-content').classList.remove('slide-down');
                addRoomModal.querySelector('.modal-content').classList.add('slide-up');
                addRoomModal.classList.remove('fade-in');
                addRoomModal.classList.add('fade-out');
                setTimeout(function() {
                    addRoomModal.style.display = "none";
                    addRoomModal.classList.remove('fade-out');
                    addRoomModal.querySelector('.modal-content').classList.remove('slide-up');
                }, 400);
            };
            editRoomBtns.forEach(function(button) {
                button.onclick = function() {

                    const tenantId = this.getAttribute('data-tenant-id');
                    const tenantName = this.getAttribute('data-tenant-name');
                    const currentRoom = this.getAttribute('data-current-room');

                    document.getElementById('tenantId').value = tenantId;
                    document.getElementById('tenantName').value = tenantName;
                    document.getElementById('currentRoom').value = currentRoom; // Set the current room number





                    editRoomModal.style.display = "block";
                    setTimeout(function() {
                        editRoomModal.classList.add('fade-in');
                        editRoomModal.querySelector('.modal-content').classList.add('slide-down');
                    }, 0);
                };
            });

            closeEditRoomBtn.onclick = function() {
                editRoomModal.querySelector('.modal-content').classList.remove('slide-down');
                editRoomModal.querySelector('.modal-content').classList.add('slide-up');
                editRoomModal.classList.remove('fade-in');
                editRoomModal.classList.add('fade-out');
                setTimeout(function() {
                    editRoomModal.style.display = "none";
                    editRoomModal.classList.remove('fade-out');
                    editRoomModal.querySelector('.modal-content').classList.remove('slide-up');
                }, 400);
            };
        </script>
        <script src="assets/js/preloader.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>