<?php
header("Content-Type: application/json");

$host = "localhost";
$user = "root";
$password = "";
$dbname = "sugarplush_db";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['table']) || !isset($data['data']) || !isset($data['primaryKeyColumn'])) {
    echo json_encode(["status" => "error", "message" => "Invalid request. Missing required data."]);
    exit();
}

$table = $conn->real_escape_string($data['table']);
$primaryKeyColumn = $conn->real_escape_string($data['primaryKeyColumn']);
$recordData = $data['data'];

if (!isset($recordData[$primaryKeyColumn])) {
    echo json_encode(["status" => "error", "message" => "Primary key value is missing."]);
    exit();
}

$primaryKeyValue = $recordData[$primaryKeyColumn];
unset($recordData[$primaryKeyColumn]); 

$columns = array_keys($recordData);
$values = array_values($recordData);

$setQuery = implode(" = ?, ", array_map(fn($col) => "`$col`", $columns)) . " = ?";
$sql = "UPDATE `$table` SET $setQuery WHERE `$primaryKeyColumn` = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["status" => "error", "message" => "SQL Error: " . $conn->error]);
    exit();
}

$types = str_repeat("s", count($values)) . "s"; 
$params = array_merge($values, [$primaryKeyValue]);

$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Record updated successfully."]);
} else {
    echo json_encode(["status" => "error", "message" => "Update failed: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
