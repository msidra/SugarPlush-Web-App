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
if (isset($_GET['fetch_tables'])) {
    $tables = [];
    $query = "SHOW TABLES";
    $result = $conn->query($query);

    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }
    echo json_encode(["status" => "success", "tables" => $tables]);
    exit;
}
if (isset($_GET['table'])) {
    $table = $_GET['table'];
    
    $columns = [];
    $query = "DESCRIBE `$table`";
    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        $columns[] = ["name" => $row['Field'], "type" => $row['Type']];
    }
    
    $records = [];
    $query = "SELECT * FROM `$table`";
    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }

    echo json_encode(["status" => "success", "columns" => $columns, "records" => $records]);
    exit;
}

echo json_encode(["status" => "error", "message" => "Invalid request"]);
?>
