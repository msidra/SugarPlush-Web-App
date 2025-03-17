<?php
session_start();
header("Content-Type: application/json");

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["loggedIn" => false]);
    exit();
}

echo json_encode(["loggedIn" => true, "user_id" => $_SESSION["user_id"], "email" => $_SESSION["email"]]);
exit();
?>
