<?php
header("Content-Type: application/json");

$host = "localhost";
$user = "root";
$password = "";
$dbname = "sugarplush_db";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data["table"]) || !isset($data["idField"]) || !isset($data["idValue"])) {
        echo json_encode(["status" => "error", "message" => "Invalid request: Missing required data"]);
        exit;
    }

    $table = $data["table"];
    $idField = $data["idField"];
    $idValue = $data["idValue"];

    if (!preg_match('/^[a-zA-Z0-9_]+$/', $table) || !preg_match('/^[a-zA-Z0-9_]+$/', $idField)) {
        echo json_encode(["status" => "error", "message" => "Invalid table or column name"]);
        exit;
    }

    $query = "DELETE FROM `$table` WHERE `$idField` = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Query preparation failed: " . $conn->error]);
        exit;
    }

    if (is_numeric($idValue)) {
        $stmt->bind_param("i", $idValue);
    } else {
        $stmt->bind_param("s", $idValue);
    }

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Record deleted successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to delete record: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>
