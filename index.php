<?php
ob_start();
session_start();
include 'database.php';


if (!isset($_SESSION['username'])) {
    header('Location: landing_page.php');
    exit();
}


// Default page
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
// Include the header and navigation bar
include('header.php');
include('navbar.php');
// Start the main content container
echo '<div class="main-content container-fluid mt-4">';
// Load content based on the page parameter
switch ($page) {
    case 'categories':
        include('categories.php');
        break;
    case 'rooms':
        include('rooms.php');
        break;
    case 'tenants':
        include('tenants.php');
        break;
    case 'invoices':
        include('invoices.php');
        break;
    case 'reports':
        include('reports.php');
        break;
    case 'utilities':
        include('utilities.php');
        break;
    case 'rent':
        include('rent.php');
        break;
    case 'payment':
        include('payment.php');
        break;
    case 'history':
        include('history.php');
        break;
    case 'issue':
        include('issue.php');
        break;
    case 'deposit':
        include('deposit.php');
        break;
    case 'configuration':
        include('configuration.php');
        break;
    case 'rented':
        include('rented.php');
        break;
    default:
        include('home.php');
        break;
}
// End the main content container
echo '</div>';
