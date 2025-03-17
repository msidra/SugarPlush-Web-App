<?php include 'database.php'; ?>
<button onclick="proceedToCheckout()">Proceed to Checkout 🎀</button>

<script>
function proceedToCheckout() {
    <?php if (isset($_SESSION['user_email'])) { ?>
        window.location.href = "checkout.php"; 
    <?php } else { ?>
        alert("You need to sign in before proceeding to checkout!");
        window.location.href = "signin.php";
    <?php } ?>
}

</script>
