document.addEventListener("DOMContentLoaded", () => {
    displayCheckoutCart();
});

file_put_contents("checkout_debug.log", json_encode($_POST, JSON_PRETTY_PRINT));

function getCartItems() {
    return JSON.parse(localStorage.getItem("cart")) || [];
}

function saveCartItems(cartItems) {
    localStorage.setItem("cart", JSON.stringify(cartItems));
}

function displayCheckoutCart() {
    let cartItems = getCartItems();

console.log("🛒 Cart Data Before Checkout:", cartItems);

cartItems = cartItems.map(item => {
    if (!item.item_id) {
        console.error("❌ Missing `item_id` for:", item.name);
        alert(`Error: Missing item_id for ${item.name}`);
        return null; 
    }
    return item;
}).filter(item => item !== null);

console.log("🛒 Cart Data After Fixing item_id:", cartItems);

    let cartContainer = document.getElementById("cart-items");
    let cartTotal = document.getElementById("cart-total");
    cartContainer.innerHTML = "";

    let totalCost = 0;

    if (cartItems.length === 0) {
        cartContainer.innerHTML = "<p>Your cart is empty! 🛍️</p>";
        cartTotal.innerText = "$0.00";
        return;
    }

    cartItems.forEach((item, index) => {
        let itemTotal = item.price * item.quantity;
        totalCost += itemTotal;

        let cartItem = document.createElement("div");
        cartItem.classList.add("cart-item");
        cartItem.innerHTML = `
            <div class="cart-item-image">
                <img src="${item.image}" alt="${item.name}">
            </div>
            <div class="cart-item-info">
                <p><strong>${item.name}</strong></p>
                <p>Price: $${item.price.toFixed(2)}</p>
                <p><strong>Quantity:</strong> ${item.quantity}</p> 
                <p>Total: <span class="item-total">$${itemTotal.toFixed(2)}</span></p>
                <button class="remove-btn" data-index="${index}">Remove ❌</button>
            </div>
        `;
        cartContainer.appendChild(cartItem);
    });

    cartTotal.innerText = `$${totalCost.toFixed(2)}`;

    localStorage.setItem("cartTotal", totalCost.toFixed(2));

    document.querySelectorAll(".remove-btn").forEach(button => {
        button.addEventListener("click", removeItem);
    });
}

document.addEventListener("DOMContentLoaded", function () {
    let checkoutButton = document.getElementById("checkout-button");

    if (!checkoutButton) {
        console.error("❌ Checkout button not found!");
        return;
    }

    let totalPrice = cartItems.reduce((sum, item) => sum + item.price * item.quantity, 0);
    let userId = localStorage.getItem("user_id");

    console.log("🛒 Sending cart items to checkout.php:", cartItems);

    fetch("checkout.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            user_id: userId,
            cart_items: cartItems.map(item => ({
                item_id: item.item_id, 
                quantity: item.quantity
            })),
            total_price: totalPrice
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log("🔄 Checkout Response:", data);
        if (data.status === "success") {
            alert("✅ Order placed successfully!");
            localStorage.setItem("last_order_cart", JSON.stringify(cartItems)); 
            window.location.href = `payment.html?total=${totalPrice}`;
        } else {
            alert("❌ Error placing order: " + data.message);
        }
    })
    .catch(error => console.error("❌ Error during checkout:", error));
});


function processPayment() {
    fetch("check_session.php") 
        .then(response => response.json())
        .then(data => {
            if (!data.loggedIn) {
                alert("You need to sign in first.");
                window.location.href = "signin.html";
                return;
            }

            let userId = data.user_id;
            let selectedCard = document.getElementById("saved-card").value;
            let totalAmount = document.getElementById("total-amount").innerText.replace("$", "").trim();

            if (!selectedCard) {
                alert("Please select a card to proceed.");
                return;
            }

            console.log("Sending payment:", { user_id: userId, total_price: totalAmount, card_number: selectedCard });

            fetch("process_payment.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: new URLSearchParams({ user_id: userId, total_price: totalAmount, card_number: selectedCard }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    fetch("clear_cart.php");

                    window.location.href = `delivery.html?order_id=${data.order_id}&total=${totalAmount}`;
                } else {
                    alert("Payment failed: " + data.message);
                }
            })
            .catch(error => {
                console.error("Error processing payment:", error);
            });
        })
        .catch(error => console.error("Error checking session:", error));
}

function removeItem(event) {
    let index = event.target.dataset.index;
    let cartItems = getCartItems();
    cartItems.splice(index, 1);
    saveCartItems(cartItems);
    displayCheckoutCart();
}

function checkLoginBeforeCheckout() {
    const userId = localStorage.getItem("user_id");

    if (userId) {
        window.location.href = `payment.html?total=${encodeURIComponent(localStorage.getItem("cartTotal"))}`;
    } else {
        localStorage.setItem("redirect_to_checkout", "checkout.html");
        showSignInPopup();
    }
}

function showSignInPopup() {
    const popup = document.createElement("div");
    popup.classList.add("popup");
    popup.innerHTML = `
        <div class="popup-content">
            <p>Please sign in first!</p>
        </div>
    `;

    document.body.appendChild(popup);
    popup.style.display = "block";

    setTimeout(() => {
        popup.style.opacity = "0";
    }, 1500); 

    setTimeout(() => {
        window.location.href = "signin.html";  
    }, 2000); 
}


document.querySelector(".proceed-btn").addEventListener("click", function () {
    let total = localStorage.getItem("cartTotal");

    if (!total || isNaN(total)) {
        alert("❌ Error: Invalid total price.");
        return;
    }

    window.location.href = `payment.html?total=${encodeURIComponent(total)}`;
});

function proceedToDelivery(orderId, totalAmount) {
    let cartData = JSON.parse(localStorage.getItem("cart") || "[]");

    if (cartData.length === 0) {
        alert("❌ Your cart is empty!");
        return;
    }

    localStorage.setItem("last_order_cart", JSON.stringify(cartData));
    localStorage.setItem("last_order_total", totalAmount);

    window.location.href = `delivery.html?order_id=${orderId}&total=${totalAmount}`;
}

function goBackToShopping() {
    console.log("🔄 Redirecting back to Shopping Page...");
    window.location.href = "shopping.html"; 
}