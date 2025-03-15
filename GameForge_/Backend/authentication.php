<?php

include 'session_start.php'; // Start the session
include '../dbconnection/dbconnect.php'; // Include database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signInbtn'])) {
    $username = trim($_POST['username']); // Get and trim the username from POST data
    $password = trim($_POST['password']); // Get and trim the password from POST data

    if (empty($username) || empty($password)) { // Check if username or password is empty
        $_SESSION['error_message'] = "Please fill in all fields."; // Set error message
        header("Location: ../src/Main_Pages/home.php"); // Redirect to home page
        exit();
    }

    // Prepare SQL query to fetch user details based on username
    $query = "SELECT id, username, firstname, password FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username); // Bind username as a string
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    // Verify the password and check if user exists
    if ($user && password_verify($password, $user['password'])) {
<<<<<<< HEAD
        session_regenerate_id(true); 
        $_SESSION['firstname'] = $user['firstname'];
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $username;
        $_SESSION['logged_in'] = true;
=======
        session_regenerate_id(true); // Prevent session fixation
        $_SESSION['firstname'] = $user['firstname']; // Store user's first name in session
        $_SESSION['user_id'] = $user['id']; // Store user's ID in session
        $_SESSION['username'] = $username; // Store username in session
        $_SESSION['logged_in'] = true; // Set logged in status
>>>>>>> main

        header("Location: ../src/Main_Pages/home.php"); // Redirect to home page
        exit();
    }

    // Set error message for invalid username or password
    $_SESSION['error_message'] = "❌ Invalid username or password.";
    header("Location: ../src/Main_Pages/signIn.php"); // Redirect to sign-in page
    exit();
}
?>