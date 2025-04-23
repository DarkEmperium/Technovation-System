<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <title>Payment Unsuccess</title>
    <link rel="icon" type="image/x-icon" href="assets/images/logo.png">
    <link rel="stylesheet" href="assets/css/payment_status.css">
</head>

<body>

    <a href="index.php"><img src="assets/images/logo.png" alt="logo" id="logo"></a>

    <div class="container">
        <div class="message-box">
            <i class="fa-solid fa-circle-xmark" aria-hidden="true" id="fail"></i>
            <h2>Payment Failed</h2>
            <p>We encountered an issue processing your payment. Please try again or contact our customer support for assistance.</p>
        </div>
        <a href="index.php"><button id="homeBtn">Dismiss</button></a>
    </div>

</body>

</html>