<?php
include 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $item_id = $_POST['item_id'];
    $quantity = $_POST['quantity'];

    $sql = "INSERT INTO shopping (StoreCode, TotalPrice) VALUES ('$user_id', (SELECT Price FROM item WHERE ItemID = $item_id) * $quantity)";

    if ($conn->query($sql) === TRUE) {
        echo "Item added to cart!";
    } else {
        echo "Error: " . $conn->error;
    }
}
$conn->close();
?>
