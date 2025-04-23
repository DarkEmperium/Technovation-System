<?php
$searchQuery = "";

// Check if the search term is provided
if (isset($_POST['search'])) {
    $searchTerm = mysqli_real_escape_string($conn, $_POST['search']);
    $searchQuery = " AND users.name LIKE '%$searchTerm%'";
}

// Update the main query to include the search and sort by tenant name
$query = "SELECT users.User_ID, users.name, users.email, users.phoneNum AS contact, 
                users.registration_date, deposit.status AS deposit_status
            FROM users
            LEFT JOIN deposit ON users.User_ID = deposit.User_ID
            WHERE users.Role != 'admin'  -- Exclude admin
            $searchQuery
            ORDER BY users.name ASC";  // Ensure results are ordered by tenant name in ascending order

if (isset($_POST["save"])) {
    $username = $_POST['name'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];

    $registration_date = date('Y-m-d');

    // Insert new tenant into users table
    $sql = "INSERT INTO users (name, email, password, phoneNum, registration_date, Role) 
            VALUES ('$username', '$email', '123456789', '$contact', '$registration_date', 'user')";

    if (mysqli_query($conn, $sql)) {
        // Get the newly inserted User_ID
        $new_user_id = mysqli_insert_id($conn);

        // Insert deposit record (default to 'Unpaid' and refund date set one year later)
        $refund_date = date('Y-m-d', strtotime('+1 year'));
        $depositQuery = "INSERT INTO deposit (User_ID, status, Refund_Date, Deposit_Amount) VALUES ('$new_user_id', 'Unpaid', '$refund_date','1000')";
        mysqli_query($conn, $depositQuery);

        // Set success message
        $_SESSION['message'] = "Tenant added successfully !";
        header("Location: index.php?page=tenants");
        exit();
    } else {
        // Set error message
        $_SESSION['message'] = "Error adding tenant : " . mysqli_error($conn);
        header("Location: index.php?page=tenants");
        exit();
    }
}


if (isset($_GET['delete'])) {
    $user_id = $_GET['delete'];

    $deleteInvoiceQuery = "DELETE FROM invoices WHERE UserID = '$user_id'";
    mysqli_query($conn, $deleteInvoiceQuery);

    // Delete dependent records first (check your database for other related tables)
    $deleteDepositQuery = "DELETE FROM deposit WHERE User_ID = '$user_id'";
    mysqli_query($conn, $deleteDepositQuery);

    $deleteRoomsQuery = "UPDATE rooms SET User_ID = NULL, rent_status = 'Available' WHERE User_ID = '$user_id'";
    mysqli_query($conn, $deleteRoomsQuery);

    $deleteReportQuery = "DELETE FROM report WHERE User_ID = '$user_id'";
    mysqli_query($conn, $deleteReportQuery);

    // Now delete the user
    $deleteUserQuery = "DELETE FROM users WHERE User_ID = '$user_id'";
    if (mysqli_query($conn, $deleteUserQuery)) {
        // Set success message
        $_SESSION['message'] = "Tenant deleted successfully !";
        header("Location: index.php?page=tenants");
        exit();
    } else {
        // Set error message
        $_SESSION['message'] = "Error deleting tenant : " . mysqli_error($conn);
        header("Location: index.php?page=tenants");
        exit();
    }
}

if (isset($_GET['edit'])) {
    $user_id = $_GET['edit'];

    // Fetch tenant's details for editing
    $query = "SELECT users.name, users.phoneNum AS contact, users.email, deposit.status AS deposit_status 
              FROM users
              LEFT JOIN deposit ON users.User_ID = deposit.User_ID
              WHERE users.User_ID = '$user_id'";
    $result = mysqli_query($conn, $query);
    $tenant = mysqli_fetch_assoc($result);
}

if (isset($_POST['saving'])) {
    $name = $_POST['name'];
    $contact = $_POST['contact']; // Added contact (phone number)
    $email = $_POST['email']; // Added email
    $deposit_status = $_POST['deposit_status'];
    $user_id = $_POST['user_id'];

    // Update tenant details and deposit status
    $updateQuery = "UPDATE users SET name = '$name', phoneNum = '$contact', email = '$email' WHERE User_ID = '$user_id'";
    $updateDepositQuery = "UPDATE deposit SET status = '$deposit_status' WHERE User_ID = '$user_id'";

    if (mysqli_query($conn, $updateQuery) && mysqli_query($conn, $updateDepositQuery)) {
        // Set success message
        $_SESSION['message'] = "Tenant details updated successfully !";
        header("Location: index.php?page=tenants");
        exit();
    } else {
        // Set error message
        $_SESSION['message'] = "Error updating tenant : " . mysqli_error($conn);
        header("Location: index.php?page=tenants");
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Management</title>
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

        #addTenantIcon {
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
                User Account Management
                <div>
                    <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST["search"])): ?>
                        <a href="index.php?page=tenants" class="btn btn-secondary me-2">
                            <i class="bi bi-arrow-clockwise"></i> Reset Search
                        </a>
                    <?php endif; ?>
                    <button class="btn btn-success" id="addTenantBtn">
                        <i class="bi bi-person-plus-fill" id="addTenantIcon"></i> New Tenant
                    </button>
                </div>
            </h3>


            <div class="mb-4 p-3 bg-light rounded">
                <form method="POST" action="index.php?page=tenants">
                    <input type="text" class="form-control" placeholder="Search Tenants" name="search" value="<?php echo isset($_POST['search']) ? $_POST['search'] : ''; ?>">
                </form>
            </div>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Registered Date</th>
                        <th>Deposit Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $count = 1;
                    $result = mysqli_query($conn, $query);
                    if (mysqli_num_rows($result) > 0):
                        while ($tenant = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= $count++ ?></td>
                                <td><?= $tenant['name'] ?></td> 
                                <td><?= $tenant['contact'] ?></td>
                                <td><?= $tenant['email'] ?></td>
                                <td><?= date('M d, Y', strtotime($tenant['registration_date'])) ?></td> 
                                <td><?= $tenant['deposit_status'] ?></td> 
                                <td>
                                    <button class="btn btn-primary btn-sm edit-tenant" data-user-id="<?= $tenant['User_ID'] ?>">Edit</button>
                                    <a href="index.php?page=tenants&delete=<?= $tenant['User_ID'] ?>" class="btn btn-danger btn-sm">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No tenants found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>



            </table>
        </div>

        <!-- Edit Tenant Modal -->
        <div id="editTenantModal" class="modal">
            <div class="modal-content">
                <h5>Edit Tenant Details</h5>
                <form method="POST">
                    <div class="mb-3">
                        <label for="editTenantName" class="form-label">Name</label>
                        <input type="text" class="form-control" id="editTenantName" name="name" value="<?= $tenant['name'] ?? '' ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="editTenantContact" class="form-label">Contact</label>
                        <input type="text" class="form-control" id="editTenantContact" name="contact" value="<?= $tenant['contact'] ?? '' ?>" required pattern="^(0\d{2}-\d{7}|011-\d{8})$" title="Phone number must in format like 012-3456789">
                    </div>

                    <div class="mb-3">
                        <label for="editTenantEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="editTenantEmail" name="email" value="<?= $tenant['email'] ?? '' ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="editTenantDepositState" class="form-label">Deposit Status</label>
                        <select class="form-control" id="editTenantDepositState" name="deposit_status" required>
                            <option value="Paid" <?= isset($tenant['deposit_status']) && $tenant['deposit_status'] == 'Paid' ? 'selected' : '' ?>>Paid</option>
                            <option value="Unpaid" <?= isset($tenant['deposit_status']) && $tenant['deposit_status'] == 'Unpaid' ? 'selected' : '' ?>>Unpaid</option>
                        </select>
                    </div>

                    <input type="hidden" name="user_id" value="<?= $tenant['User_ID'] ?? '' ?>">

                    <div class="edit_btn">
                        <button class="btn btn-primary" type="submit" name="saving">Save</button>
                        <button class="btn btn-secondary" type="button" id="closeEditTenantModal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>



        <!-- Add Tenant Modal -->
        <div id="addTenantModal" class="modal">
            <div class="modal-content">
                <h5>Add New Tenant</h5>
                <form method="POST">
                    <div class="mb-3">
                        <label for="addTenantName" class="form-label">Name</label>
                        <input type="text" class="form-control" id="addTenantName" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="addTenantEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="addTenantEmail" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="addTenantContact" class="form-label">Contact</label>
                        <input type="tel" class="form-control" id="addTenantContact" name="contact" required pattern="^(0\d{2}-\d{7}|011-\d{8})$" title="Phone number must in format like 012-3456789">
                    </div>

                    <div class="edit_btn">
                        <button class="btn btn-primary" id="saveNewTenant" name="save" type="submit">Save</button>
                        <button class="btn btn-secondary" id="closeAddTenantModal" type="button">Cancel</button>
                    </div>
                </form>
            </div>

        </div>

    </div>

    <script>
        var editTenantModal = document.getElementById("editTenantModal");
        var closeEditTenantBtn = document.getElementById("closeEditTenantModal");
        var addTenantModal = document.getElementById("addTenantModal");
        var addTenantBtn = document.getElementById("addTenantBtn");
        var closeAddTenantBtn = document.getElementById("closeAddTenantModal");

        addTenantBtn.onclick = function() {
            addTenantModal.style.display = "block";
            setTimeout(function() {
                addTenantModal.classList.add('fade-in');
                addTenantModal.querySelector('.modal-content').classList.add('slide-down');
            }, 0);
        };

        closeAddTenantBtn.onclick = function() {
            addTenantModal.querySelector('.modal-content').classList.remove('slide-down');
            addTenantModal.querySelector('.modal-content').classList.add('slide-up');
            addTenantModal.classList.remove('fade-in');
            addTenantModal.classList.add('fade-out');
            setTimeout(function() {
                addTenantModal.style.display = "none";
                addTenantModal.classList.remove('fade-out');
                addTenantModal.querySelector('.modal-content').classList.remove('slide-up');
            }, 400);
        };

        document.querySelectorAll('.edit-tenant').forEach(function(button) {
            button.addEventListener('click', function() {
                var userId = this.getAttribute('data-user-id'); // Get the user ID from the button
                var tenantName = this.closest('tr').querySelector('td:nth-child(2)').innerText; // Get the tenant's name
                var phoneNum = this.closest('tr').querySelector('td:nth-child(3)').innerText; // Get the tenant's phone number
                var email = this.closest('tr').querySelector('td:nth-child(4)').innerText; // Get the tenant's email
                var depositStatus = this.closest('tr').querySelector('td:nth-child(6)').innerText.trim(); // Get the deposit status

                // Populate the modal with the tenant's current details
                document.getElementById('editTenantName').value = tenantName;
                document.getElementById('editTenantContact').value = phoneNum;
                document.getElementById('editTenantEmail').value = email;
                document.getElementById('editTenantDepositState').value = depositStatus === 'Paid' ? 'Paid' : 'Unpaid';
                document.querySelector('input[name="user_id"]').value = userId;

                // Show the modal with animations
                var editTenantModal = document.getElementById('editTenantModal');
                editTenantModal.style.display = "block";
                setTimeout(function() {
                    editTenantModal.classList.add('fade-in');
                    editTenantModal.querySelector('.modal-content').classList.add('slide-down');
                }, 0);
            });
        });


        closeEditTenantBtn.onclick = function() {
            editTenantModal.querySelector('.modal-content').classList.remove('slide-down');
            editTenantModal.querySelector('.modal-content').classList.add('slide-up');
            editTenantModal.classList.remove('fade-in');
            editTenantModal.classList.add('fade-out');
            setTimeout(function() {
                editTenantModal.style.display = "none";
                editTenantModal.classList.remove('fade-out');
                editTenantModal.querySelector('.modal-content').classList.remove('slide-up');
            }, 400);
        };
    </script>
    <script src="assets/js/preloader.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>