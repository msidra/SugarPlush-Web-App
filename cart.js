document.addEventListener("DOMContentLoaded", () => {
    displayCartItems();
    setupDragDrop();
});

function handleDragStart(event) {
    let plushieElement = event.target.closest(".plushie-item");
    if (!plushieElement) return;

    let plushieData = {
        name: plushieElement.dataset.name,
        price: parseFloat(plushieElement.dataset.price),
        image: plushieElement.dataset.image
    };

    event.dataTransfer.setData("application/json", JSON.stringify(plushieData));
}


function getCartItems() {
    let cartItems = JSON.parse(localStorage.getItem("cart")) || [];

    let itemIdMapping = {
        "Cow Plushy": 1,
        "Unicorn Plushy": 2,
        "Cat Plushy": 3,
        "Penguin Plushy": 4,
        "Zebra Plushy": 5,
        "Lion Plushy": 6,
        "Bunny Plushy": 7,
        "Dog Plushy": 8
    };

    cartItems.forEach(item => {
        if (!item.item_id) {
            item.item_id = itemIdMapping[item.name] || null;
        }
    });

    console.log("🛒 Updated Cart Before Checkout:", cartItems);
    saveCartItems(cartItems);
    return cartItems;
}

function saveCartItems(cartItems) {
    let itemIdMapping = {
        "Cow Plushy": 1,
        "Unicorn Plushy": 2,
        "Cat Plushy": 3,
        "Penguin Plushy": 4,
        "Zebra Plushy": 5,
        "Lion Plushy": 6,
        "Bunny Plushy": 7,
        "Dog Plushy": 8
    };

    cartItems.forEach(item => {
        if (!item.item_id) {
            item.item_id = itemIdMapping[item.name] || null;
        }
    });

    localStorage.setItem("cart", JSON.stringify(cartItems));
}

function displayCartItems() {
    let cartItems = getCartItems();
    let cartContainer = document.getElementById("cart-items");
    let cartTotalElement = document.getElementById("cart-total");

    if (!cartContainer || !cartTotalElement) {
        console.error("❌ Cart container or total not found.");
        return;
    }

    cartContainer.innerHTML = ""; 

    let totalCost = 0;

    if (cartItems.length === 0) {
        cartContainer.innerHTML = "<p>Your cart is empty! 🛍️</p>";
        cartTotalElement.innerText = "$0";
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
                <p>Price: $${item.price}</p>
                <label>Quantity: </label>
                <input type="number" min="1" value="${item.quantity}" class="quantity-input" data-index="${index}">
                <p>Total: <span class="item-total">$${itemTotal.toFixed(2)}</span></p>
                <button class="remove-btn" data-index="${index}">Remove ❌</button>
            </div>
        `;
        cartContainer.appendChild(cartItem);
    });
   
    cartTotalElement.innerText = `${totalCost.toFixed(2)}`;
    
    document.querySelectorAll(".quantity-input").forEach(input => {
        input.addEventListener("change", updateQuantity);
    });

    document.querySelectorAll(".remove-btn").forEach(button => {
        button.addEventListener("click", removeItem);
    });
}

function updateQuantity(event) {
    let cartItems = getCartItems();
    let index = event.target.dataset.index;
    let newQuantity = parseInt(event.target.value);

    if (newQuantity < 1) return;

    cartItems[index].quantity = newQuantity;
    saveCartItems(cartItems);
    displayCartItems();
}

function removeItem(event) {
    let cartItems = getCartItems();
    let index = event.target.dataset.index;

    cartItems.splice(index, 1);
    saveCartItems(cartItems);
    displayCartItems();
}

function setupDragDrop() {
    let cartDropArea = document.getElementById("cart-drop-area");

    if (!cartDropArea) {
        console.error("❌ Cart drop area not found.");
        return;
    }

    cartDropArea.addEventListener("dragover", (event) => {
        event.preventDefault();
        cartDropArea.classList.add("drag-over");
    });

    cartDropArea.addEventListener("dragleave", () => {
        cartDropArea.classList.remove("drag-over");
    });

    cartDropArea.addEventListener("drop", (event) => {
        event.preventDefault();
        cartDropArea.classList.remove("drag-over");

        let plushieData = event.dataTransfer.getData("application/json");
        if (!plushieData) return;

        let plushie = JSON.parse(plushieData);
        let cartItems = getCartItems();

        let existingItem = cartItems.find(item => item.name === plushie.name);
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            cartItems.push({
                name: plushie.name,
                price: parseFloat(plushie.price),
                image: plushie.image,
                quantity: 1
            });
        }

        saveCartItems(cartItems);
        displayCartItems();
    });
}


document.addEventListener("DOMContentLoaded", function () {
    let checkoutButton = document.getElementById("checkout-button");

    if (!checkoutButton) {
        console.error("❌ Checkout button not found!");
        return;
    }

    checkoutButton.addEventListener("click", redirectToCheckout);
});

function redirectToCheckout() {
    let cartItems = getCartItems();
    if (cartItems.length === 0) {
        alert("🛒 Your cart is empty. Grab some plushies!");
        return;
    }

    let totalPrice = document.getElementById("cart-total").innerText.replace("$", "").trim();
    window.location.href = `checkout.html?total=${totalPrice}`;
}
