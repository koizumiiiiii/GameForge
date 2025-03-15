<?php

// Include session start file; if not found, terminate the script
include realpath(__DIR__ . '/../session_start.php') ?: die("session_start.php not found");

// Include database connection file
include '../../dbconnection/dbconnect.php';

/**
 * Function to fetch user details and store them in the session.
 * 
 * @param mysqli $conn Database connection object.
 * @param int $user_id ID of the user.
 */
function fetchUserDetails($conn, $user_id) {
    // SQL query to fetch user details based on user ID
    $query = "SELECT username, firstname, lastname, email, profile_image FROM users WHERE id = ?";
    $stmt = $conn->prepare($query); // Prepare the SQL statement
    
    if ($stmt) { // Ensure statement preparation was successful
        $stmt->bind_param("i", $user_id); // Bind user_id as an integer
        $stmt->execute(); // Execute the query
        $result = $stmt->get_result(); // Get the result set

        if ($row = $result->fetch_assoc()) { // Fetch the user details
            // Store user details in session variables
            $_SESSION['username'] = $row['username'];
            $_SESSION['firstname'] = $row['firstname'];
            $_SESSION['lastname'] = $row['lastname'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['profile_image'] = $row['profile_image']; 
        }

        $stmt->close(); // Close the statement
    }
}

/**
 * Function to fetch the user's wallet balance and store it in the session.
 * 
 * @param mysqli $conn Database connection object.
 * @param int $user_id ID of the user.
 */
function fetchWalletBalance($conn, $user_id) {
    $balance = 0.00; // Default balance value in case no record is found
    
    // SQL query to get the user's wallet balance
    $query = "SELECT balance FROM wallet WHERE user_id = ?";
    $stmt = $conn->prepare($query); // Prepare the SQL statement
    
    if ($stmt) { // Ensure statement preparation was successful
        $stmt->bind_param("i", $user_id); // Bind user_id as an integer
        $stmt->execute(); // Execute the query
        $stmt->bind_result($balance); // Bind the result to the balance variable
        $stmt->fetch(); // Fetch the result
        $_SESSION['balance'] = $balance; // Store the balance in session
        $stmt->close(); // Close the statement
    }
}

?>