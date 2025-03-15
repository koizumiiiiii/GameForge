<?php
header('Content-Type: application/json'); // Ensure JSON response
include '../session_start.php'; // Start session to track user authentication
include '../auth_check.php'; // Include authentication check
include '../../dbconnection/dbconnect.php'; // Include database connection file

$user_id = $_SESSION['user_id']; // Retrieve user ID from session
$cart = json_decode(file_get_contents("php://input"), true)['cart']; // Decode JSON input to get cart items

if (!$cart) { // Check if cart is empty
    echo json_encode(["success" => false, "message" => "Cart is empty."]);
    exit;
}

// Calculate total price of items in the cart
$total = 0;
foreach ($cart as $item) {
    $total += $item['quantity'] * $item['price'];
}

// Check user balance
$wallet_result = $conn->query("SELECT balance FROM wallet WHERE user_id = $user_id");
$wallet = $wallet_result->fetch_assoc();

if (!$wallet || $wallet['balance'] < $total) { // Ensure user has sufficient balance
    echo json_encode(["success" => false, "message" => "Insufficient balance."]);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Deduct balance first
    $update_wallet = $conn->query("UPDATE wallet SET balance = balance - $total WHERE user_id = $user_id");
    
    // Ensure balance was successfully deducted
    if (!$update_wallet) {
        throw new Exception("Failed to deduct balance.");
    }

    // Insert order only if wallet deduction was successful
    $conn->query("INSERT INTO orders (user_id, total) VALUES ($user_id, $total)");

    // Clear the cart in the database
    $conn->query("DELETE FROM cart WHERE user_id = $user_id");

    // Commit transaction
    $conn->commit();

    echo json_encode(["success" => true, "message" => "Order successful!"]);
    exit;

} catch (Exception $e) {
    // Rollback transaction if anything fails
    $conn->rollback();
    echo json_encode(["success" => false, "message" => "Checkout failed: " . $e->getMessage()]);
    exit;
}
?>