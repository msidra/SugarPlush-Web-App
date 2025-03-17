<?php
session_start();
header("Content-Type: application/json");
include "database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
    exit();
}

$email = isset($_POST["email"]) ? trim($_POST["email"]) : null;
$password = isset($_POST["password"]) ? $_POST["password"] : null;

if (!$email || !$password) {
    echo json_encode(["status" => "error", "message" => "Email and password are required."]);
    exit();
}

$stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($user_id, $hashed_password);
$stmt->fetch();

if ($stmt->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "No account found with this email."]);
    exit();
}

if (!password_verify($password, $hashed_password)) {
    echo json_encode(["status" => "error", "message" => "Incorrect password."]);
    exit();
}

$stmt->close();

$_SESSION["user_id"] = $user_id;
$_SESSION["email"] = $email;

echo json_encode([
    "status" => "success",
    "message" => "Login successful.",
    "user_id" => $user_id,
    "email" => $email
]);
exit();
?>
