<?php
if (!isset($_SESSION["role"])) {
    header("Location: landing_page.php");
    exit();
}

$user_id = $_SESSION['User_ID'];
$name = $_SESSION['username'];

// Fetch deposit details for the logged-in user
$sql = "SELECT Deposit_Amount, status, Refund_Date FROM deposit WHERE User_ID = $user_id";
$result = mysqli_query($conn, $sql);

if ($row = mysqli_fetch_assoc($result)) {
    $depositAmount = "RM " . number_format($row["Deposit_Amount"], 2);
    $refundTime = date("jS F Y", strtotime($row["Refund_Date"]));
    $paymentStatus = $row["status"];

    // Determine badge class and button visibility
    if ($paymentStatus === "Paid") {
        $statusClass = "badge bg-success";
        $statusText = "Settled";
        $showPayButton = false;
    } else {
        $statusClass = "badge bg-danger";
        $statusText = "Unpaid";
        $showPayButton = true;
    }
} else {
    // If no deposit record found
    $depositAmount = "N/A";
    $refundTime = "N/A";
    $statusClass = "badge bg-warning";
    $statusText = "Not Available";
    $showPayButton = false;
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Deposit Portal</title>
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
            max-width: 800px;
            margin: 70px auto;
            background: #343a40;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        .container h3 {
            color: white;
        }

        h3,
        h5,
        p,
        .btn {
            font-style: italic;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: bold;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .card {
            background: rgba(248, 249, 250, 0.8);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0px 3px 8px rgba(0, 0, 0, 0.15);
            text-align: left;
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
            <h3 class="mb-4">Deposit Payment Portal</h3>

            <div class="card">
                <h5 class="text-center mb-3">Deposit Details</h5>
                <p><strong>Tenant: </strong> <?php echo $name; ?></p>
                <p><strong>Deposit Amount : </strong> <?php echo $depositAmount; ?></p>
                <p><strong>Refund Time : </strong> <?php echo $refundTime; ?></p>
                <p><strong>Status : </strong> <span class="<?php echo $statusClass; ?>"><?php echo $statusText; ?></span></p>

                <?php if ($showPayButton) { ?>
                    <div class="text-end mt-4">
                        <form method="POST" action="checkout.php">
                            <input type="hidden" name="deposit_amount" value="<?php echo $row['Deposit_Amount']; ?>">
                            <button class="btn btn-primary">Pay Deposit</button>
                        </form>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <script src="assets/js/preloader.js"></script>

</body>

</html>