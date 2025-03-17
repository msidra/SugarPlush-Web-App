<?php
header("Content-Type: application/json");

$host = "localhost";
$user = "root";
$password = "";
$dbname = "sugarplush_db";
$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Database connection failed: " . $conn->connect_error]));
}

if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    die(json_encode(["status" => "error", "message" => "Order ID is required"]));
}

$order_id = intval($_GET['order_id']); // Ensure integer input

$warehouse_address = "220 Yonge St, Toronto, ON, Canada";

$query = $conn->prepare("
    SELECT o.order_id, o.total_price, o.date_issued, o.delivered, o.delivery_date, o.delivery_time,  
           t.trip_id, t.source_code, t.destination_code, t.distance_km, t.price AS trip_price,
           COALESCE(u.full_name, 'Unknown') AS full_name,
           COALESCE(u.street, 'Unknown') AS street,
           COALESCE(u.city, 'Unknown') AS city,
           COALESCE(u.state, 'Unknown') AS state,
           COALESCE(u.zip, 'Unknown') AS zip,
           COALESCE(u.country, 'Unknown') AS country,
           COALESCE(tr.truck_id, 0) AS truck_id, tr.truck_code,
           s.receipt_id, COALESCE(s.store_code, 'Unknown') AS store_code, 
           COALESCE(s.total_price, 0) AS receipt_price
    FROM orders o
    LEFT JOIN trip t ON o.trip_id = t.trip_id
    LEFT JOIN users u ON o.user_id = u.id
    LEFT JOIN truck tr ON t.truck_id = tr.truck_id
    LEFT JOIN shopping s ON o.receipt_id = s.receipt_id
    WHERE o.order_id = ?
");

$query->bind_param("i", $order_id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 0) {
    die(json_encode(["status" => "error", "message" => "Order details not found."]));
}

$order = $result->fetch_assoc();
$query->close();

$trip_source = ($order['source_code'] == "SRC655") ? $warehouse_address : $order['source_code'];

$itemQuery = $conn->prepare("
    SELECT i.item_id, i.item_name, i.price, oi.quantity, i.image_url
    FROM order_items oi
    JOIN items i ON oi.item_id = i.item_id
    WHERE oi.order_id = ?
");

$itemQuery->bind_param("i", $order_id);
$itemQuery->execute();
$itemResult = $itemQuery->get_result();

$items = [];
while ($row = $itemResult->fetch_assoc()) {
    $items[] = $row;
}
$itemQuery->close();

echo json_encode([
    "status" => "success",
    "order_id" => $order['order_id'],
    "total_price" => $order['total_price'],
    "date_issued" => $order['date_issued'],
    "delivered" => $order['delivered'],
    "delivery_date" => $order['delivery_date'],
    "delivery_time" => $order['delivery_time'], 
    "customer_details" => [
        "full_name" => $order['full_name'],
        "street" => $order['street'],
        "city" => $order['city'],
        "state" => $order['state'],
        "zip" => $order['zip'],
        "country" => $order['country']
    ],
    "trip_details" => [
        "trip_id" => $order['trip_id'],
        "source" => $warehouse_address,
        "destination" => "{$order['street']}, {$order['city']}, {$order['state']}, {$order['country']}",
        "distance_km" => $order['distance_km'] ?? 0, // Set to 0 if null
        "trip_price" => $order['trip_price'] ?? 0
    ],
    "truck_details" => [
        "truck_id" => $order['truck_id'],
        "truck_code" => $order['truck_code']
    ],
    "receipt_details" => [
        "receipt_id" => $order['receipt_id'],
        "store_code" => $order['store_code'],
        "receipt_price" => $order['receipt_price']
    ],
    "items" => $items
]);

$conn->close();
?>
