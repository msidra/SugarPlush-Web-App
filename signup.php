<?php
include 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $street = $_POST['street'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $zip = $_POST['zip'];
    $country = $_POST['country'];
    $card_number = $_POST['card_number'];
    $expiry_date = $_POST['expiry_date'];
    $cvv = $_POST['cvv'];

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $query = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $query->bind_param("s", $email);
    $query->execute();
    $query->store_result();

    if ($query->num_rows > 0) {
        header("Location: signin.html");
        exit();
    }
    $query->close();

    $query = $conn->prepare("INSERT INTO users (full_name, email, password, street, city, state, zip, country, card_number, expiry_date, cvv) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $query->bind_param("sssssssssss", $full_name, $email, $hashed_password, $street, $city, $state, $zip, $country, $card_number, $expiry_date, $cvv);

    if ($query->execute()) {
        header("Location: signin.html");
        exit();
    } else {
        echo json_encode(["status" => "error", "message" => $query->error]);
    }
    $query->close();
}
?>
