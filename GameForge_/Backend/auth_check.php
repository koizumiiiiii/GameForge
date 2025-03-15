<?php
ob_start();
include 'session_start.php'; // Ensure session is started

// Get the current script name
$current_page = basename($_SERVER['PHP_SELF']);

// Allow access to the sign-in and registration pages
$allowed_pages = ['signIn.php', 'registration.php'];

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    if (!in_array($current_page, $allowed_pages)) {
        header("Location: ../../src/Main_Pages/signIn.php");
        exit();
    }
}
?>
