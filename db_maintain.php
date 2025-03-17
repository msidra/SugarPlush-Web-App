<?php
include "database.php";
header("Content-Type: application/json");

session_start();

if (!isset($_SESSION["user_email"]) || $_SESSION["user_email"] !== "4amaryam@gmail.com") {
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit();
}

if (!isset($_POST["action"]) || !isset($_POST["input"])) {
    echo json_encode(["status" => "error", "message" => "Missing action or input."]);
    exit();
}

$action = $_POST["action"];
$input = $_POST["input"];

switch ($action) {
    case "insert":
        list($name, $email, $address) = explode(",", $input);
        $stmt = $conn->prepare("INSERT INTO users (name, email, address) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $address);
        $stmt->execute();
        echo json_encode(["status" => "success", "message" => "User inserted successfully."]);
        break;

    case "delete":
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $input);
        $stmt->execute();
        echo json_encode(["status" => "success", "message" => "User deleted successfully."]);
        break;

    case "select":
        $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $input);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            echo json_encode(["status" => "success", "user" => $row]);
        } else {
            echo json_encode(["status" => "error", "message" => "No user found."]);
        }
        break;

    case "update":
        list($userId, $newEmail) = explode(",", $input);
        $stmt = $conn->prepare("UPDATE users SET email = ? WHERE user_id = ?");
        $stmt->bind_param("si", $newEmail, $userId);
        $stmt->execute();
        echo json_encode(["status" => "success", "message" => "User updated successfully."]);
        break;
}

$conn->close();
?>
