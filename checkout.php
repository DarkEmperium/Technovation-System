<?php
session_start();
include 'database.php';

$user_id = $_SESSION['User_ID'];
$selected_bills = isset($_POST['selected_bills']) ? $_POST['selected_bills'] : [];

// Retrieve deposit amount from form
$deposit_amount = isset($_POST['deposit_amount']) ? floatval($_POST['deposit_amount']) : 0;
$grand_total = 0;

// If bills are selected, calculate total invoice amount
if (!empty($selected_bills)) {
    $_SESSION['selected_bills'] = $selected_bills;

    $placeholders = implode(',', array_fill(0, count($selected_bills), '?'));
    $stmt = $conn->prepare("
        SELECT i.amount AS total
        FROM invoices i
        LEFT JOIN rooms r ON i.Room_ID = r.Room_ID
        LEFT JOIN utilities u ON r.House_ID = u.House_ID
        WHERE i.InvoiceID IN ($placeholders)
    ");
    $stmt->bind_param(str_repeat('i', count($selected_bills)), ...$selected_bills);
    $stmt->execute();
    $result = $stmt->get_result();

    $grand_total = 0;
    while ($row = $result->fetch_assoc()) {
        $grand_total += $row['total'];
    }
}

// Determine payment type
if ($deposit_amount > 0 && $grand_total == 0) {
    $_SESSION['payment_type'] = "deposit"; // Paying deposit only
    $payment_amount = $deposit_amount * 100;
} elseif ($grand_total > 0) {
    $_SESSION['payment_type'] = "invoice"; // Paying invoice
    $payment_amount = $grand_total * 100;
} else {
    echo "<script>alert('Invalid Payment Amount !'); window.location.href='index.php?page=payment';</script>";
    return;
}

require __DIR__ . "/vendor/autoload.php";
\Stripe\Stripe::setApiKey(""); // Put your Stripe secret key here

$checkout_session = \Stripe\Checkout\Session::create([
    "mode" => "payment",
    "success_url" => "http://technovation.wuaze.com/success.php",
    "cancel_url" => "http://technovation.wuaze.com/unsuccess.php",
    "locale" => "auto",
    "line_items" => [
        [
            "quantity" => 1,
            "price_data" => [
                "currency" => "myr",
                "unit_amount" => $payment_amount,
                "product_data" => [
                    "name" => ($_SESSION['payment_type'] === "deposit") ? "Rental Deposit" : "Rental and Utility Payment"
                ]
            ]
        ]
    ]
]);

http_response_code(303);
header("Location: " . $checkout_session->url);
