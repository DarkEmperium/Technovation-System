<?php

if (!isset($_SESSION["role"])) {
    header("Location: landing_page.php");
    exit();
}
$edit_mode = false;
$edit_id = null;
$edit_name = "";
$edit_available = ""; // Add this near $edit_name


// Handle Edit Request
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $edit_mode = true;

    $edit_sql = "SELECT * FROM houses WHERE House_ID = $edit_id";
    $edit_result = mysqli_query($conn, $edit_sql);
    if ($edit_row = mysqli_fetch_assoc($edit_result)) {
        $edit_name = $edit_row['HouseName'];
        $edit_available = $edit_row['availableRoom'];
    }
}

if (isset($_POST["save"])) {
    $house_name = $_POST["house"];
    $available_rooms = $_POST["available"];

    if (!preg_match('/^[a-zA-Z0-9 ]+$/', $_POST['house'])) {
        $_SESSION['message'] = "Invalid House Name : No special characters allowed !";
        header("Location: index.php?page=categories");
        exit();
    }

    // Server-side validation for empty fields
    if (empty($house_name) || $available_rooms === "") {
        $_SESSION['message'] = "Please fill in both the house name and available rooms !";
        header("Location: index.php?page=categories");
        exit();
    }

    if ($available_rooms > 20) {
        $_SESSION['message'] = "Available rooms cannot exceed 20 !";
        header("Location: index.php?page=categories");
        exit();
    }

    if (!empty($_POST["House_ID"])) {
        // EDIT EXISTING HOUSE
        $house_id = $_POST["House_ID"];

        // Check how many rooms are currently rented in this house
        $occupied_rooms_query = "SELECT COUNT(*) as occupied FROM rooms WHERE House_ID = $house_id";
        $occupied_result = mysqli_query($conn, $occupied_rooms_query);
        $occupied_data = mysqli_fetch_assoc($occupied_result);
        $occupied_rooms = $occupied_data["occupied"];

        // Prevent reducing available rooms below the number of occupied rooms
        if ($available_rooms < $occupied_rooms) {
            $_SESSION['message'] = "Cannot reduce available rooms below the number of currently rented rooms !";
        } else {
            // Update house details
            $sql = "UPDATE houses SET HouseName = '$house_name', availableRoom = $available_rooms WHERE House_ID = $house_id";
            mysqli_query($conn, $sql);
            $_SESSION['message'] = "House updated successfully !";
        }
    } else {
        // ADD NEW HOUSE
        $sql = "INSERT INTO houses (HouseName, availableRoom) VALUES ('$house_name', $available_rooms)";
        mysqli_query($conn, $sql);
        $_SESSION['message'] = "New house added successfully !";
    }

    header("Location: index.php?page=categories");
    exit();
}

if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];

    // Check if any rooms exist for this house
    $check_rooms = mysqli_query($conn, "SELECT * FROM rooms WHERE House_ID = $delete_id");

    if (mysqli_num_rows($check_rooms) > 0) {
        $_SESSION['message'] = "This house cannot be deleted because a tenant is currently living in it !";
    } else {
        // Safe to delete
        mysqli_query($conn, "DELETE FROM houses WHERE House_ID = $delete_id");
        $_SESSION['message'] = "House deleted successfully !";
    }

    header("Location: index.php?page=categories");
    exit();
}

$search = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST["search"])) {
    $search = mysqli_real_escape_string($conn, $_POST["search"]);
    $sql = "SELECT * FROM houses WHERE HouseName LIKE '%$search%' ORDER BY HouseName ASC";
} else {
    $sql = "SELECT * FROM houses ORDER BY HouseName ASC";
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="icon" type="image/x-icon" href="assets/images/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@700&display=swap" rel="stylesheet">
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

        table {
            text-align: center;
        }

        .btn-width {
            width: 100px;
        }

        .categories-title-wrapper {
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
            <h3 class="mb-4">House Category Management</h3>

            <div class="mb-4 p-3 bg-light rounded">
                <h5 id="category-label">Add & Edit House Category</h5>
                <form method="POST">
                    <div class="mb-3">
                        <input name="house" type="text" class="form-control" id="categoryName" placeholder="Enter House Number or House Name" value="<?php echo htmlspecialchars($edit_name); ?>" required>
                    </div>

                    <div class="mb-3">
                        <input name="available" type="number" class="form-control" id="availableRooms" placeholder="Enter Available Rooms" value="<?php echo htmlspecialchars($edit_available); ?>" required>
                    </div>

                    <?php if ($edit_mode): ?>
                        <input type="hidden" name="House_ID" value="<?php echo $edit_id; ?>">
                    <?php endif; ?>

                    <button class="btn btn-primary btn-width" name="save"><?php echo $edit_mode ? "Update" : "Save"; ?></button>
                    <a href="index.php?page=categories" class="btn btn-secondary btn-width">Cancel</a>
                </form>


            </div>

            <div class="categories-title-wrapper">
                <h5>Category List</h5>

                <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST["search"])): ?>
                    <a href="index.php?page=categories" class="btn btn-secondary me-2">
                        <i class="bi bi-arrow-clockwise"></i> Reset Search
                    </a>
                <?php endif; ?>
            </div>

            <div class="mb-3 p-3 bg-light rounded">
                <form method="POST" action="index.php?page=categories">
                    <input type="text" class="form-control" placeholder="Search by Categories" name="search" value="<?php echo isset($_POST['search']) ? $_POST['search'] : ''; ?>">
                </form>
            </div>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Category</th>
                        <th>Available Rooms</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    
                    $result = mysqli_query($conn, $sql);
                    $i = 1;
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>" . $i++ . "</td>";
                            echo "<td>" . htmlspecialchars($row['HouseName']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['availableRoom']) . "</td>"; // NEW
                            echo '<td>
                            <a href="?page=categories&edit=' . $row['House_ID'] . '" class="btn btn-primary btn-sm" name="edit">Edit</a>
                            <a href="?page=categories&delete=' . $row['House_ID'] . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure ?\')" name="delete">Delete</a>
                        </td>';
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' class='text-center'>No categories found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>


    </div>

    <script src="assets/js/preloader.js">
        function clearInput() {
            document.getElementById("categoryName").value = "";
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>