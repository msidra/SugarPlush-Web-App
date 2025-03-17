<?php
header("Content-Type: application/json");
include "database.php";

if (!isset($_GET["user_id"])) {
    echo json_encode(["status" => "error", "message" => "User ID not provided"]);
    exit();
}

$user_id = $_GET["user_id"];

$stmt = $conn->prepare("SELECT card_number FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($card_number);
$stmt->fetch();
$stmt->close();

if ($card_number) {
    $masked_card = "**** **** **** " . substr($card_number, -4);
    echo json_encode(["status" => "success", "card" => $masked_card, "full_card" => $card_number]);
} else {
    echo json_encode(["status" => "error", "message" => "No saved card found"]);
}
?>
