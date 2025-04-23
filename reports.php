<?php
if (!isset($_SESSION["role"])) {
    header("Location: landing_page.php");
    exit();
}
$user_result = mysqli_query($conn, "SELECT * FROM users WHERE Role = 'user'");

// Initialize error message variable
$error_message = '';
$success_message = '';

if (isset($_POST['save'])) {
    $issue_type = $_POST['issueType'];
    $tenant_id = $_POST['tenant_id'];
    $today = date("Y-m-d");

    // === Error Check ===
    if (empty($issue_type) || empty($tenant_id)) {
        $error_message = "Please select both Issue Type and Tenant !";
    } else {
        $sql_insert = "INSERT INTO report (User_ID, Issue_Type, Date_Reported)  VALUES ('$tenant_id', '$issue_type', '$today')";
        if (mysqli_query($conn, $sql_insert)) {
            $success_message = "Issue reported successfully !";
        } else {
            $error_message = "Database error : " . mysqli_error($conn);
        }
    }
}

if (isset($_POST['update_status_id']) && isset($_POST['new_status'])) {
    $report_id = $_POST['update_status_id'];
    $new_status = $_POST['new_status'];

    $sql_update = "UPDATE report SET Status='$new_status' WHERE Report_ID='$report_id'";
    if (mysqli_query($conn, $sql_update)) {
        $success_message = "Report status updated to $new_status !";
    } else {
        $error_message = "Failed to update status : " . mysqli_error($conn);
    }
}

if (isset($_POST['delete_report_id'])) {
    $delete_id = $_POST['delete_report_id'];
    $sql_delete = "DELETE FROM report WHERE Report_ID='$delete_id'";
    if (mysqli_query($conn, $sql_delete)) {
        $success_message = "Report deleted successfully !";
    } else {
        $error_message = "Failed to delete report : " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Management</title>
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

        #addIssueIcon {
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

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif (!empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <h3 class="mb-4 d-flex justify-content-between align-items-center">
                Report Management
                <div>
                    <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST["search"])): ?>
                        <a href="index.php?page=reports" class="btn btn-secondary me-2">
                            <i class="bi bi-arrow-clockwise"></i> Reset Search
                        </a>
                    <?php endif; ?>
                    <button class="btn btn-success" id="addIssueBtn">
                        <i class="bi bi-exclamation-circle-fill" id="addIssueIcon"></i> Add Issue
                    </button>
                </div>
            </h3>
            <div class="mb-4 p-3 bg-light rounded">
                <form method="POST" action="index.php?page=reports">
                    <input type="text" class="form-control" placeholder="Search Tenants" name="search" value="<?php echo isset($_POST['search']) ? $_POST['search'] : ''; ?>">
                </form>
            </div>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Issue Type</th>
                        <th>Date Reported</th>
                        <th>Reported Tenant</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php

                    $searchQuery = "";

                    // Check if the search term is provided and sanitize it
                    if (isset($_POST['search']) && !empty($_POST['search'])) {
                        $searchTerm = mysqli_real_escape_string($conn, $_POST['search']);
                        $searchQuery = " AND users.name LIKE '%$searchTerm%'";
                    }

                    // Modify the report query to include the search condition if provided
                    $report_query = "SELECT report.Report_ID, report.Issue_Type, report.Date_Reported, report.Status, users.name 
                    FROM report 
                    JOIN users ON report.User_ID = users.User_ID
                    WHERE 1=1 $searchQuery 
                    ORDER BY users.name ASC";

                    $report_result = mysqli_query($conn, $report_query);

                    $counter = 1;

                    // Check if there are any records
                    if (mysqli_num_rows($report_result) > 0):
                        // Loop through and display each row if results exist
                        while ($report = mysqli_fetch_assoc($report_result)):
                            $formatted_date = date("M Y", strtotime($report['Date_Reported']));
                            $status = $report['Status'];
                    ?>
                            <tr>
                                <td><?php echo $counter++; ?></td>
                                <td><?php echo htmlspecialchars($report['Issue_Type']); ?></td>
                                <td><?php echo htmlspecialchars($formatted_date); ?></td>
                                <td><?php echo htmlspecialchars($report['name']); ?></td>
                                <td>
                                    <?php if (strtolower($status) === 'solved'): ?>
                                        <span class="badge bg-success">Solved</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (strtolower($status) == 'pending'): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="update_status_id" value="<?php echo $report['Report_ID']; ?>">
                                            <input type="hidden" name="new_status" value="Solved">
                                            <button type="submit" class="btn btn-primary btn-sm">Mark Solved</button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="update_status_id" value="<?php echo $report['Report_ID']; ?>">
                                            <input type="hidden" name="new_status" value="Pending">
                                            <button type="submit" class="btn btn-primary btn-sm">Mark Pending</button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="delete_report_id" value="<?php echo $report['Report_ID']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No reports found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>


            </table>

            <!-- Add Issue Modal -->
            <div id="addIssueModal" class="modal">
                <div class="modal-content">
                    <form method="POST">
                        <h5>Add New Issue</h5>
                        <div class="mb-3">
                            <label for="issueType" class="form-label">Issue Type</label>
                            <select class="form-control" id="selectIssue" name="issueType" required>
                                <option value="">Select Issue</option>
                                <option value="Water Leakage">Water Leakage</option>
                                <option value="Electric Issue">Electric Issue</option>
                                <option value="Others">Others</option>
                            </select>

                        </div>
                        <div class="mb-3">
                            <label for="reportedTenant" class="form-label">Reported Tenant</label>
                            <select class="form-control" id="selectTenant" name="tenant_id" required>
                                <option value="">Select Tenant</option>
                                <?php while ($user = mysqli_fetch_assoc($user_result)): ?>
                                    <option value="<?php echo htmlspecialchars($user['User_ID']); ?>">
                                        <?php echo htmlspecialchars($user['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="edit_btn">
                            <button class="btn btn-primary" id="saveNewIssue" name="save" type="submit">Save</button>
                            <button class="btn btn-secondary close-modal" type="button">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
            </di>
        </div>

        <script>
            var addIssueModal = document.getElementById("addIssueModal");
            var addIssueBtn = document.getElementById("addIssueBtn");
            var closeAddIssueModal = document.querySelectorAll(".close-modal");

            addIssueBtn.onclick = function() {
                addIssueModal.style.display = "block";
                setTimeout(function() {
                    addIssueModal.classList.add('fade-in');
                    addIssueModal.querySelector('.modal-content').classList.add('slide-down');
                }, 0);
            };

            closeAddIssueModal.forEach(button => {
                button.onclick = function() {
                    addIssueModal.querySelector('.modal-content').classList.remove('slide-down');
                    addIssueModal.querySelector('.modal-content').classList.add('slide-up');
                    addIssueModal.classList.remove('fade-in');
                    addIssueModal.classList.add('fade-out');
                    setTimeout(function() {
                        addIssueModal.style.display = "none";
                        addIssueModal.classList.remove('fade-out');
                        addIssueModal.querySelector('.modal-content').classList.remove('slide-up');
                    }, 400);
                };
            });
        </script>

        <script src="assets/js/preloader.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>