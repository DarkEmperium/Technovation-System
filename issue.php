<?php

if (!isset($_SESSION["role"])) {
    header("Location: landing_page.php");
    exit();
}

$user_id = $_SESSION['User_ID'];  // Get current user

// Handle Report Submission
if (isset($_POST['issue_type'])) {
    $issue_type = $_POST['issue_type'];
    $date_reported = date('Y-m-d');

    // Normal SQL INSERT
    $sql_insert = "INSERT INTO report (User_ID, Issue_Type, Date_Reported) 
    VALUES ('$user_id', '$issue_type', '$date_reported')";

    if (mysqli_query($conn, $sql_insert)) {
        $_SESSION['message'] = "Issue reported successfully !";
    } else {
        $_SESSION['message'] = "Error reporting the issue. Please try again.";
    }
}

$searchIssue = isset($_POST['search']) ? strtolower(trim($_POST['search'])) : '';

// Base query
$sql_fetch = "SELECT * FROM report WHERE User_ID = '$user_id'";

// Add issue type search condition if input is provided
if (!empty($searchIssue)) {
    $sql_fetch .= " AND LOWER(Issue_Type) LIKE '%$searchIssue%'";
}

// Order by latest date
$sql_fetch .= " ORDER BY Date_Reported DESC";

// Execute the query
$issues_result = mysqli_query($conn, $sql_fetch);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Report Portal</title>
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
            margin-left: 5px;
        }

        .modal-content select {
            margin-top: 5px;
        }

        .modal-content h5 {
            margin: 20px auto 40px auto;
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
            <h3 class="mb-4 d-flex justify-content-between align-items-center">
                Issue Report Portal
                <div>
                    <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST["search"])): ?>
                        <a href="index.php?page=issue" class="btn btn-secondary me-2">
                            <i class="bi bi-arrow-clockwise"></i> Reset Search
                        </a>
                    <?php endif; ?>
                    <button class="btn btn-success" id="reportIssueBtn">
                        <i class="bi bi-exclamation-circle-fill"></i> Report Issue
                    </button>
                </div>
            </h3>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['message'];
                    unset($_SESSION['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="mb-4 p-3 bg-light rounded">
                <form method="POST" action="index.php?page=issue">
                    <input type="text" class="form-control" placeholder="Search By Issue Type" name="search" value="<?php echo isset($_POST['search']) ? $_POST['search'] : ''; ?>">
                </form>
            </div>


            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Issue Type</th>
                        <th>Date Reported</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1;
                    if (mysqli_num_rows($issues_result) > 0):
                        while ($row = mysqli_fetch_assoc($issues_result)):
                    ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td><?php echo htmlspecialchars($row['Issue_Type']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($row['Date_Reported'])); ?></td>
                                <td>
                                    <?php if (strtolower($row['status']) == 'pending'): ?>
                                        <span class="badge bg-warning text-dark"><?php echo ucfirst($row['status']); ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-success"><?php echo ucfirst($row['status']); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php
                        endwhile;
                    else:
                        ?>
                        <tr>
                            <td colspan="4">No issues reported yet.</td>
                        </tr>
                    <?php endif; ?>

                </tbody>


            </table>

            <!-- Report Issue Modal -->
            <div id="reportIssueModal" class="modal">
                <div class="modal-content">
                    <h5>Report a New Issue</h5>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="issueType" class="form-label">Issue Type</label>
                            <select class="form-control" id="issueType" name="issue_type" required>
                                <option value="">Select Issue</option>
                                <option value="Water Leakage">Water Leakage</option>
                                <option value="Electric Issue">Electric Issue</option>
                                <option value="Others">Others</option>
                            </select>
                        </div>
                        <div class="edit_btn">
                            <button type="submit" class="btn btn-primary" id="submitIssue">Submit</button>
                            <button type="button" class="btn btn-secondary close-modal">Cancel</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <script>
        var reportIssueModal = document.getElementById("reportIssueModal");
        var reportIssueBtn = document.getElementById("reportIssueBtn");
        var closeModalBtns = document.querySelectorAll(".close-modal");

        reportIssueBtn.onclick = function() {
            reportIssueModal.style.display = "block";
            setTimeout(function() {
                reportIssueModal.classList.add('fade-in');
                reportIssueModal.querySelector('.modal-content').classList.add('slide-down');
            }, 0);
        };

        closeModalBtns.forEach(button => {
            button.onclick = function() {
                reportIssueModal.querySelector('.modal-content').classList.remove('slide-down');
                reportIssueModal.querySelector('.modal-content').classList.add('slide-up');
                reportIssueModal.classList.remove('fade-in');
                reportIssueModal.classList.add('fade-out');
                setTimeout(function() {
                    reportIssueModal.style.display = "none";
                    reportIssueModal.classList.remove('fade-out');
                    reportIssueModal.querySelector('.modal-content').classList.remove('slide-up');
                }, 400);
            };
        });
    </script>

    <script src="assets/js/preloader.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>