function searchOrders() {
    let searchType = document.getElementById("searchType").value;
    let searchQuery = document.getElementById("searchQuery").value;

    if (!searchQuery) {
        alert("Please enter a search value.");
        return;
    }

    console.log("Searching for:", searchType, searchQuery);

    fetch(`search.php?type=${searchType}&query=${encodeURIComponent(searchQuery)}`)
        .then(response => response.json())
        .then(data => {
            console.log("Search Response:", data);
            let resultsDiv = document.getElementById("searchResults");
            resultsDiv.innerHTML = ""; 
            resultsDiv.style.display = "block";

            if (data.status === "success" && data.orders.length > 0) {
                let table = `<table border="1">
                    <tr><th>Order ID</th><th>User ID</th><th>Date Issued</th><th>Total Price</th><th>Delivered</th></tr>`;
                
                data.orders.forEach(order => {
                    table += `<tr>
                        <td>${order.order_id}</td>
                        <td>${order.user_id}</td>
                        <td>${order.date_issued}</td>
                        <td>$${order.total_price}</td>
                        <td>${order.delivered ? "✅ Yes" : "❌ No"}</td>
                    </tr>`;
                });

                table += `</table>`;
                resultsDiv.innerHTML = table;
            } else {
                resultsDiv.innerHTML = "<p>No matching orders found.</p>";
            }
        })
        .catch(error => {
            console.error("Error fetching search results:", error);
            document.getElementById("searchResults").innerHTML = "<p>Error retrieving search results.</p>";
        });
}
