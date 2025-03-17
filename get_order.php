<?php
header("Content-Type: application/json");
include "database.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!isset($_GET['order_id'])) {
    echo json_encode(["status" => "error", "message" => "Missing order ID"]);
    exit();
}

$order_id = intval($_GET['order_id']);

$orderCheck = $conn->prepare("SELECT COUNT(*) FROM orders WHERE order_id = ?");
$orderCheck->bind_param("i", $order_id);
$orderCheck->execute();
$orderCheck->bind_result($orderExists);
$orderCheck->fetch();
$orderCheck->close();

if (!$orderExists) {
    echo json_encode(["status" => "error", "message" => "Order not found"]);
    exit();
}

$sql = "SELECT o.order_id, o.total_price, p.item_name, p.price AS product_price, oi.quantity
        FROM orders o
        JOIN order_items oi ON o.order_id = oi.order_id
        JOIN items p ON oi.item_id = p.item_id
        WHERE o.order_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

$orderDetails = [];
while ($row = $result->fetch_assoc()) {
    $orderDetails[] = $row;
}

if (count($orderDetails) > 0) {
    echo json_encode(["status" => "success", "order" => $orderDetails]);
} else {
    echo json_encode(["status" => "error", "message" => "Order details not found"]);
}

$stmt->close();
$conn->close();
?>
