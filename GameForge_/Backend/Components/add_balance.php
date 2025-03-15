<?php

// Start the session to track user authentication
include '../session_start.php';

// Include database connection file
include '../../dbconnection/dbconnect.php';

// Check if the database connection is established
if (!$conn || $conn->connect_errno) {
    die("Database connection failed: " . $conn->connect_error);
}

// Retrieve the user ID from session, default to 0 if not set
$user_id = $_SESSION['user_id'] ?? 0;

// Fetch the user's wallet balance
$query = "SELECT balance FROM wallet WHERE user_id = ?";
$stmt = $conn->prepare($query);
if ($stmt) {
    $stmt->bind_param("i", $user_id); // Bind user_id as an integer
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $balance = $row['balance'] ?? 0; // Default balance to 0 if no record found
    $stmt->close();
} else {
    $balance = 0; // Default balance to 0 if query preparation fails
}

// Check if the request is a POST request (for adding money)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = floatval($_POST['amount']); // Convert input to a float for security

    if ($amount > 0) { // Ensure the entered amount is positive
        // Check if the user already has a wallet entry in the database
        $check_query = "SELECT * FROM wallet WHERE user_id = ?";
        $stmt = $conn->prepare($check_query);
        
        if ($stmt) {
            $stmt->bind_param("i", $user_id); // Bind user_id as an integer
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();

            if ($result->num_rows == 0) { // If user has no wallet entry, create one
                $insert_query = "INSERT INTO wallet (user_id, balance) VALUES (?, 0)";
                $stmt_insert = $conn->prepare($insert_query);
                
                if ($stmt_insert) {
                    $stmt_insert->bind_param("i", $user_id); // Bind user_id as an integer
                    $stmt_insert->execute();
                    $stmt_insert->close();
                } else {
                    die("Error preparing insert query: " . $conn->error);
                }
            }

            // Update the user's wallet balance by adding the new amount
            $update_query = "UPDATE wallet SET balance = balance + ? WHERE user_id = ?";
            $stmt_update = $conn->prepare($update_query);

            if ($stmt_update) {
                $stmt_update->bind_param("di", $amount, $user_id); // Bind amount as double and user_id as integer

                if ($stmt_update->execute()) {
                    $_SESSION['message'] = "Money added successfully!"; // Success message
                } else {
                    $_SESSION['error'] = "Error updating balance: " . $stmt_update->error; // Error message
                }
                $stmt_update->close();
            } else {
                die("Error preparing update query: " . $conn->error);
            }
        } else {
            die("Error preparing check query: " . $conn->error);
        }
    } else {
        $_SESSION['error'] = "Invalid amount!"; // Set error message for invalid input
    }

    // Redirect the user back to the payments page after processing
    header("Location: ../../src/Main_pages/payments.php");
    exit();
}

// Close the database connection at the end of the script
$conn->close();
?>