<?php

include 'session_start.php';
include '../../dbconnection/dbconnect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $email = trim($_POST['email']);
    $contactnum = trim($_POST['contactnum']);
    $address = trim($_POST['address']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($email) || empty($password)) {
        $_SESSION['error_message'] = "All fields are required.";
        header("Location: signIn.php");
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $stmt->bind_param("ss", $email, $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['error_message'] = "Email or username already in use.";
        header("Location: signIn.php"); 
        exit;
    }
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO users (username, firstname, lastname, email, contactnum, address, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $username, $firstname, $lastname, $email, $contactnum, $address, $hashed_password);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Registration successful! Please log in.";
        header("Location: signIn.php"); 
        exit;
    } else {
        $_SESSION['error_message'] = "Error: " . $stmt->error;
        header("Location: signIn.php");
        exit;
    }

    $stmt->close();
    $conn->close();
}
?>
