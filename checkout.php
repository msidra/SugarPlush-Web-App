<?php
header("Content-Type: application/json");
include "database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
$user_id = $data["user_id"];
$cart_items = $data["cart_items"];
$total_price = $data["total_price"];

error_log("Received checkout data: " . print_r($data, true));

if (empty($user_id) || empty($cart_items) || empty($total_price)) {
    echo json_encode(["status" => "error", "message" => "Missing checkout data"]);
    exit();
}

$conn->begin_transaction(); 

try {
    $sql = "INSERT INTO orders (date_issued, date_received, total_price, payment_code, user_id, trip_id, receipt_id)
            VALUES (CURDATE(), DATE_ADD(CURDATE(), INTERVAL 3 DAY), ?, 123456, ?, 1, 1)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("di", $total_price, $user_id);
    $stmt->execute();
    $order_id = $stmt->insert_id; 
    $stmt->close(); }
  
foreach ($cartItems as $item) {
    $item_id = $item['item_id'];
    $quantity = $item['quantity'];

    $checkQuery = $conn->prepare("SELECT * FROM order_items WHERE order_id = ? AND item_id = ?");
    $checkQuery->bind_param("ii", $order_id, $item_id);
    $checkQuery->execute();
    $result = $checkQuery->get_result();

    if ($result->num_rows == 0) {
        $insertQuery = $conn->prepare("INSERT INTO order_items (order_id, item_id, quantity) VALUES (?, ?, ?)");
        $insertQuery->bind_param("iii", $order_id, $item_id, $quantity);
        $insertQuery->execute();
    }
}

$itemQuery = $conn->prepare("INSERT INTO order_items (order_id, item_id, quantity) VALUES (?, ?, ?)");
foreach ($cart as $item) {
    $item_id = $item['item_id'];
    $quantity = $item['quantity'];
    
    if (!isset($item_id) || !isset($quantity)) {
        continue; 
    }

    $itemQuery->bind_param("iii", $order_id, $item_id, $quantity);
    if (!$itemQuery->execute()) {
        error_log("Error inserting order item: " . $itemQuery->error);
    }
}
$itemQuery->close();

$userCheck = $conn->prepare("SELECT email FROM users WHERE id = ?");
$userCheck->bind_param("i", $user_id);
$userCheck->execute();
$userCheck->store_result();

if ($userCheck->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Invalid user ID."]);
    exit();
}
$userCheck->bind_result($user_email);
$userCheck->fetch();
$userCheck->close();


$conn->close();
?>