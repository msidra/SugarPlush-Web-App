<?php
session_start();
session_destroy(); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging Out...</title>
    <script>
        localStorage.removeItem("loggedInUser");
        localStorage.removeItem("user_id");

        window.location.href = "index.html";
    </script>
</head>
<body>
    <p>Logging out...</p>
</body>
</html>