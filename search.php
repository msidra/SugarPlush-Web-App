<?php
header("Content-Type: application/json");

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_GET['type']) || !isset($_GET['query'])) {
    echo json_encode(["status" => "error", "message" => "Missing parameters"]);
    exit;
}

$type = $_GET['type'];
$query = $_GET['query'];

file_put_contents('debug.log', "Search Type: $type, Query: $query\n", FILE_APPEND);


$host = "localhost";
$user = "root";
$password = "";
$dbname = "sugarplush_db";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Database connection failed"]));
}

$type = $_GET['type'] ?? "";
$query = $_GET['query'] ?? "";

if (!in_array($type, ['user_id', 'order_id']) || empty($query)) {
    echo json_encode(["status" => "error", "message" => "Invalid search parameters"]);
    exit();
}

$sql = "SELECT order_id, user_id, date_issued, total_price, delivered FROM orders WHERE $type = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $query);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

echo json_encode(["status" => "success", "orders" => $orders]);
$stmt->close();
$conn->close();
?>
