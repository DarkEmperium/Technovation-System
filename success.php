<?php
session_start();
include 'database.php';

if (!isset($_SESSION['User_ID']) || !isset($_SESSION['payment_type'])) {
    die("Invalid Session Data !");
}

$user_id = $_SESSION['User_ID'];
$payment_type = $_SESSION['payment_type'];
$selected_bills = isset($_SESSION['selected_bills']) ? $_SESSION['selected_bills'] : null;

unset($_SESSION['payment_type'], $_SESSION['selected_bills']); // Clear session variables after use

if ($payment_type === "invoice") {
    if (!empty($selected_bills)) {
        $placeholders = implode(',', array_fill(0, count($selected_bills), '?'));
        $stmt = $conn->prepare("UPDATE invoices SET status = 'Paid',payment_date = NOW() WHERE InvoiceID IN ($placeholders)");
        $stmt->bind_param(str_repeat('i', count($selected_bills)), ...$selected_bills);
        $update_success = $stmt->execute();
        $stmt->close();

        if ($update_success) {
            foreach ($selected_bills as $invoice_id) {
                // Get the Room_ID from the invoice
                $stmt = $conn->prepare("SELECT Room_ID FROM invoices WHERE InvoiceID = ?");
                $stmt->bind_param("i", $invoice_id);
                $stmt->execute();
                $stmt->bind_result($room_id);
                $stmt->fetch();
                $stmt->close();

                // Get the House_ID associated with the room
                $stmt = $conn->prepare("SELECT House_ID FROM rooms WHERE Room_ID = ?");
                $stmt->bind_param("i", $room_id);
                $stmt->execute();
                $stmt->bind_result($house_id);
                $stmt->fetch();
                $stmt->close();

                // Update the utility status for the associated house if a utility exists
                $stmt = $conn->prepare("UPDATE utilities SET status = 'Paid' WHERE House_ID = ?");
                $stmt->bind_param("i", $house_id);
                $stmt->execute();
                $stmt->close();
            }
        }
    } else {
        $update_success = false;
    }
} elseif ($payment_type === "deposit") {
    $stmt = $conn->prepare("UPDATE deposit SET status = 'Paid' WHERE User_ID = ?");
    $stmt->bind_param("i", $user_id);
    $update_success = $stmt->execute();
    $stmt->close();
} else {
    die("Invalid Payment Type !");
}

$conn->close();

if ($update_success) {
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
        <title>Payment Success</title>
        <link rel="icon" type="image/x-icon" href="assets/images/logo.png">
        <link rel="stylesheet" href="assets/css/payment_status.css">
    </head>

    <body>
        <div class="container">
            <div class="message-box">
                <i class="fa fa-check-circle" aria-hidden="true"></i>
                <h2>Payment Successful</h2>
                <p>Thank you for your payment. Your payment is being processed and you will receive a confirmation email shortly.</p>
            </div>
            <a href="index.php"><button id="homeBtn">Dismiss</button></a>
        </div>
    </body>

    </html>
<?php
} else {
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error</title>
    </head>

    <body>
        <h1>Error</h1>
        <p>There was an error updating the payment status !</p>
    </body>

    </html>
<?php
}
?>