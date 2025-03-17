<?php
include "database.php";
header("Content-Type: application/json");

$inputData = json_decode(file_get_contents("php://input"), true);
if (!$inputData) {
    echo json_encode(["status" => "error", "message" => "Invalid JSON data received."]);
    exit(); }

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
    exit();
}


if (!isset($inputData["user_id"], $inputData["total_price"], $inputData["card_number"], 
          $inputData["delivery_date"], $inputData["delivery_time"], $inputData["cart_items"])) {
    echo json_encode(["status" => "error", "message" => "Missing required payment details."]);
    exit();
}

$user_id = intval($inputData["user_id"]);
$total_price = floatval($inputData["total_price"]);
$card_number = $inputData["card_number"];
$delivery_date = $inputData["delivery_date"];
$delivery_time = $inputData["delivery_time"];
$cart_items = $inputData["cart_items"]; 

if (empty($cart_items)) {
    echo json_encode(["status" => "error", "message" => "Cart is empty."]);
    exit();
}

$cart_items = $inputData["cart_items"];


if (!is_array($cart_items) || empty($cart_items)) {
    echo json_encode(["status" => "error", "message" => "Cart is empty (Decoded)."]);
    exit();
}

error_log("Received cart items: " . print_r($cart_items, true));




$truckQuery = $conn->query("SELECT truck_id FROM truck WHERE availability_code = 'AVL' ORDER BY RAND() LIMIT 1");
if ($truckQuery->num_rows > 0) {
    $truck = $truckQuery->fetch_assoc();
    $truck_id = $truck['truck_id'];
} else {
    echo json_encode(["status" => "error", "message" => "No available trucks."]);
    exit();
}

$stmt = $conn->prepare("INSERT INTO trip (source_code, destination_code, distance_km, truck_id, price) VALUES (?, ?, ?, ?, ?)");
$source_code = "SRC" . rand(100, 999);
$destination_code = "DEST" . rand(100, 999);
$distance_km = rand(10, 100);
$trip_price = rand(10, 50) + (0.5 * $distance_km);
$stmt->bind_param("ssiid", $source_code, $destination_code, $distance_km, $truck_id, $trip_price);

if (!$stmt->execute()) {
    echo json_encode(["status" => "error", "message" => "Could not create trip."]);
    exit();
}

$trip_id = $stmt->insert_id;
$stmt->close();

if (empty($trip_id)) {
    echo json_encode(["status" => "error", "message" => "Failed to generate a valid trip ID."]);
    exit();
}


$stmt = $conn->prepare("INSERT INTO shopping (store_code, total_price) VALUES (?, ?)");
$store_code = rand(1001, 1999);
$stmt->bind_param("id", $store_code, $total_price);

if (!$stmt->execute()) {
    echo json_encode(["status" => "error", "message" => "Could not create shopping receipt."]);
    exit();
}

$receipt_id = $stmt->insert_id; 
$stmt->close();

$payment_code = rand(100000, 999999);

if (empty($payment_code)) {
    echo json_encode(["status" => "error", "message" => "Failed to generate a valid payment code."]);
    exit();
}

$stmt = $conn->prepare("INSERT INTO orders (date_issued, total_price, payment_code, user_id, trip_id, receipt_id, delivery_date, delivery_time) VALUES (CURDATE(), ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("diiiiss", $total_price, $payment_code, $user_id, $trip_id, $receipt_id, $delivery_date, $delivery_time);

if (!$stmt->execute()) {
    echo json_encode(["status" => "error", "message" => "Could not process your order."]);
    exit();
}

$order_id = $stmt->insert_id;
$stmt->close();

$userCheckQuery = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
$userCheckQuery->bind_param("i", $user_id);
$userCheckQuery->execute();
$userCheckResult = $userCheckQuery->get_result();
if ($userCheckResult->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "User not found in database."]);
    exit();
}
$userCheckQuery->close();

$stmt = $conn->prepare("INSERT INTO order_items (order_id, item_id, quantity) VALUES (?, ?, ?)");

foreach ($cart_items as $item) {
    $stmt->bind_param("iii", $order_id, $item["item_id"], $item["quantity"]);
    $stmt->execute();
}

$stmt->close();

echo json_encode([
    "status" => "success",
    "order_id" => $order_id,
    "total_price" => $total_price
]);
exit();
?>
