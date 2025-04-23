<?php
$currentDate = date('Y-m-d');       // Today's date
$currentMonth = date('Y-m');        // e.g., 2025-04
$lastMonth = date('Y-m', strtotime('-1 month')); // e.g., 2025-03

// Step 1: Get the latest invoice month from the table
$latestMonthQuery = "
    SELECT MAX(DATE_FORMAT(Invoice_Date, '%Y-%m')) AS latest_month
    FROM invoices
";
$latestMonthResult = mysqli_query($conn, $latestMonthQuery);
$latestMonthRow = mysqli_fetch_assoc($latestMonthResult);
$latestMonth = $latestMonthRow['latest_month'] ?? null;

// Step 2: Proceed only if the latest invoice is from last month
if ($latestMonth === $lastMonth) {
    // Step 3: Insert last month's paid invoices as unpaid for current month
    $insertQuery = "
        INSERT INTO invoices (Room_ID, UserID, Invoice_Date, amount, status)
        SELECT Room_ID, UserID, '$currentDate', amount, 'Pending'
        FROM invoices
        WHERE DATE_FORMAT(Invoice_Date, '%Y-%m') = '$lastMonth'
          AND status = 'Paid'
    ";
    mysqli_query($conn, $insertQuery);

    // Step 4: Get all rented rooms
    $rentedRoomsQuery = "
        SELECT Room_ID, User_ID, price, House_ID
        FROM rooms
        WHERE rent_status = 'Rented'
    ";
    $rentedRoomsResult = mysqli_query($conn, $rentedRoomsQuery);

    if (mysqli_num_rows($rentedRoomsResult) > 0) {
        while ($room = mysqli_fetch_assoc($rentedRoomsResult)) {
            $roomId = $room['Room_ID'];
            $tenantId = $room['User_ID'];
            $amount = $room['price'];
            $houseId = $room['House_ID'];

            // Step 5: Check for utility bills for current month
            $utilityQuery = "
                SELECT water_bill, electric_bill
                FROM utilities
                WHERE House_ID = '$houseId' AND DATE_FORMAT(month, '%Y-%m') = '$currentMonth'
            ";
            $utilityResult = mysqli_query($conn, $utilityQuery);
            $utility = mysqli_fetch_assoc($utilityResult);

            // Skip if utility bills don't exist or both are 0
            if (!$utility || ($utility['water_bill'] == 0 && $utility['electric_bill'] == 0)) {
                continue;
            }

            $waterBill = $utility['water_bill'] ?? 0;
            $electricBill = $utility['electric_bill'] ?? 0;
            $totalAmount = $amount + $waterBill + $electricBill;

            // Step 6: Ensure no duplicate for current month
            $existingInvoiceQuery = "
                SELECT COUNT(*) as count
                FROM invoices
                WHERE Room_ID = '$roomId' AND UserID = '$tenantId'
                AND DATE_FORMAT(Invoice_Date, '%Y-%m') = '$currentMonth'
            ";
            $existingInvoiceResult = mysqli_query($conn, $existingInvoiceQuery);
            $existingInvoice = mysqli_fetch_assoc($existingInvoiceResult);

            if ($existingInvoice['count'] == 0) {
                $insertRoomInvoice = "
                    INSERT INTO invoices (Room_ID, UserID, Invoice_Date, amount, status)
                    VALUES ('$roomId', '$tenantId', '$currentDate', '$totalAmount', 'Unpaid')
                ";
                mysqli_query($conn, $insertRoomInvoice);
            }
        }
    }
}
?>
