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

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['table']) || !isset($data['data'])) {
    echo json_encode(["status" => "error", "message" => "Invalid request. Table or data missing."]);
    exit();
}

$table = $data['table'];
$columns = array_keys($data['data']);
$values = array_values($data['data']);
$placeholders = implode(", ", array_fill(0, count($values), "?"));

$query = "INSERT INTO `$table` (" . implode(", ", $columns) . ") VALUES ($placeholders)";
$stmt = $conn->prepare($query);

$types = str_repeat("s", count($values));
$stmt->bind_param($types, ...$values);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Record inserted successfully."]);
} else {
    echo json_encode(["status" => "error", "message" => "Insert failed: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
