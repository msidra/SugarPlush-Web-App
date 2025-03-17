const plushieImages = {
    "cow": [
        "https://i.pinimg.com/736x/da/71/fe/da71fee1960d46dda65011cb5fbac93d.jpg", 
        "https://i.pinimg.com/736x/9b/89/82/9b8982ad619de45c7a8d8f8460d921a8.jpg"
    ],
    "bunny": [
        "https://i.pinimg.com/736x/14/9f/ef/149fefff3be2e123a00aa5fabdd7d5ec.jpg", 
        "https://i.pinimg.com/736x/18/02/57/180257c648cdb3a9be6830a35991756d.jpg"
    ]
};

let currentPlushie = "";
let currentIndex = 0;

function openCarousel(plushieType) {
    currentPlushie = plushieType;
    currentIndex = 0;
    document.getElementById("carouselImage").src = plushieImages[plushieType][0];
    document.getElementById("carouselModal").style.display = "flex";
}

function closeCarousel() {
    document.getElementById("carouselModal").style.display = "none";
}

function displayTotalAmount() {
    let urlParams = new URLSearchParams(window.location.search);
    let total = urlParams.get("total");
    let totalElement = document.getElementById("total-amount");

    if (!totalElement) {
        console.error("Error: total-amount element not found in DOM.");
        return;
    }

    if (!total || isNaN(parseFloat(total))) {
        totalElement.innerText = "$0.00";  
    } else {
        totalElement.innerText = `$${parseFloat(total).toFixed(2)}`;
    }
}

document.addEventListener("DOMContentLoaded", function () {
    fetch("check_session.php")
        .then(response => response.json())
        .then(data => {
            if (data.loggedIn) {
                localStorage.setItem("user_id", data.user_id);
                localStorage.setItem("loggedInUser", data.email);
            } else {
                localStorage.removeItem("user_id");
                localStorage.removeItem("loggedInUser");
            }
        });

    
    setupLogoutButton();
    setupPaymentButton();
    displayTotalAmount();  
    loadSavedCard();
});


function setupLogoutButton() {
    let logoutBtn = document.getElementById("logout-btn");

    if (logoutBtn) {
        logoutBtn.addEventListener("click", function () {
            fetch("logout.php")
                .then(() => {
                    localStorage.clear();
                    window.location.href = "signin.html"; 
                });
        });
    }
}
function loadSavedCard() {
    let userId = localStorage.getItem("user_id");

    if (!userId) {
        console.warn("User not logged in, skipping saved card fetch.");
        return;
    }

    fetch(`get_card.php?user_id=${userId}`)
        .then(response => response.json())
        .then(data => {
            let cardSelect = document.getElementById("saved-card");
            if (!cardSelect) return;

            cardSelect.innerHTML = "";

            if (data.status === "success" && data.card) {
                let option = document.createElement("option");
                option.value = data.full_card;
                option.textContent = data.card;
                cardSelect.appendChild(option);
            } else {
                let noCardOption = document.createElement("option");
                noCardOption.textContent = "No saved cards found";
                noCardOption.value = "";
                cardSelect.appendChild(noCardOption);
            }
        })
        .catch(error => console.error("Error fetching saved cards:", error));
}

function setupPaymentButton() {
    let paymentButton = document.getElementById("process-payment");

    if (paymentButton) {
        paymentButton.addEventListener("click", processPayment);
    }
}

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
            let deliveryDate = document.getElementById("delivery-date").value;
            let deliveryTime = document.getElementById("delivery-time").value;

            // ✅ Retrieve cart from cart.js
            let cartItems = JSON.parse(localStorage.getItem("cart")) || [];

            if (!cartItems || cartItems.length === 0) {
                console.error("🚨 No cart items found in localStorage.");
                alert("Your cart is empty!");
                return;
            }

            // ✅ Ensure each cart item has an item_id (from cart.js mapping)
            cartItems.forEach(item => {
                if (!item.item_id) {
                    console.warn(`⚠️ Missing item_id for ${item.name}`);
                    item.item_id = null;
                }
            });

            if (!selectedCard) {
                alert("Please select a card to proceed.");
                return;
            }

            console.log("🛒 Preparing payment request...");
            console.log("🛒 Cart Items Before Checkout:", cartItems);

            let requestData = {
                user_id: userId,
                total_price: parseFloat(totalAmount),
                card_number: selectedCard,
                delivery_date: deliveryDate,
                delivery_time: deliveryTime,
                cart_items: cartItems
            };

            console.log("🔹 Sending payment data:", JSON.stringify(requestData));

            fetch("process_payment.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(requestData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    console.log("✅ Payment successful, order ID:", data.order_id);
                    
                    // ✅ Only clear cart AFTER a successful payment
                    localStorage.removeItem("cart");

                    window.location.href = `delivery.html?order_id=${data.order_id}&total=${totalAmount}`;
                } else {
                    console.error("❌ Payment failed:", data.message);
                    alert("Payment failed: " + data.message);
                }
            })
            .catch(error => {
                console.error("❌ Error processing payment:", error);
                alert("Unexpected error. Please try again.");
            });
        })
        .catch(error => console.error("Error checking session:", error));
}


function gobacktoshopping() {
    console.log(" Redirecting back to Shopping Page...");
    window.location.href = "shopping.html";
}

document.addEventListener("DOMContentLoaded", () => {
    fetchTables();
});

function fetchTables() {
    fetch("db_select.php?action=getTables")
        .then(response => response.json())
        .then(data => {
            if (data.status === "success" && Array.isArray(data.tables)) {
                let tableSelect = document.getElementById("tableSelect");

                if (!tableSelect) {
                    console.warn("⚠ Warning: Table select dropdown not found.");
                    return;
                }

                tableSelect.innerHTML = "";
                
                data.tables.forEach(table => {
                    let option = document.createElement("option");
                    option.value = table;
                    option.textContent = table;
                    tableSelect.appendChild(option);
                });
                
                if (data.tables.length > 0) {
                    fetchTableData(data.tables[0]);
                }
            } else {
                console.error("❌ Error fetching tables:", data.message || "Invalid response format.");
            }
        })
        .catch(error => console.error("❌ Fetch Error:", error));
}


function fetchTableData() {
    let tableName = document.getElementById("tableSelect").value;
    fetch(`db_select.php?table=${tableName}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                let headersRow = document.getElementById("tableHeaders");
                let tableBody = document.getElementById("tableBody");

                headersRow.innerHTML = "";
                tableBody.innerHTML = "";
  
                data.columns.forEach(column => {
                    let th = document.createElement("th");
                    th.textContent = column;
                    headersRow.appendChild(th);
                });

                data.records.forEach(record => {
                    let tr = document.createElement("tr");
                    data.columns.forEach(column => {
                        let td = document.createElement("td");
                        td.textContent = record[column] || "N/A";
                        tr.appendChild(td);
                    });
                    tableBody.appendChild(tr);
                });
            } else {
                console.error("Error fetching table data:", data.message);
            }
        })
        .catch(error => console.error("Error:", error));
}
