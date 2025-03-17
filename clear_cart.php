<?php
include "database.php";
header("Content-Type: application/json");

session_start();
if (!isset($_SESSION["user_id"])) {
    echo json_encode(["status" => "error", "message" => "User not logged in."]);
    exit();
}

$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare("DELETE FROM cart WHERE user_email = (SELECT email FROM users WHERE id = ?)");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

echo json_encode(["status" => "success", "message" => "Cart cleared successfully."]);
exit();
?>
