<?php
header("Content-Type: application/json");
include "database.php";

if (!isset($_GET['email'])) {
    echo json_encode(["status" => "error", "message" => "Missing email parameter"]);
    exit();
}

$email = $_GET['email'];

$sql = "SELECT u.lat, u.lng, w.lat AS warehouse_lat, w.lng AS warehouse_lng
        FROM users u
        JOIN warehouses w ON u.warehouse_id = w.id
        WHERE u.email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if ($data) {
    echo json_encode([
        "warehouse" => ["lat" => $data["warehouse_lat"], "lng" => $data["warehouse_lng"]],
        "user" => ["lat" => $data["lat"], "lng" => $data["lng"]]
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "User or warehouse location not found"]);
}
?>
