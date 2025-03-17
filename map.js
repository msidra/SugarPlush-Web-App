let map;
let directionsService;
let directionsRenderer;
let markers = []; 

function initMap() {
    map = new google.maps.Map(document.getElementById("map"), {
        zoom: 10,
        center: { lat: 43.6532, lng: -79.3832 }, //Toronto Default zoom in
    });

    directionsService = new google.maps.DirectionsService();
    directionsRenderer = new google.maps.DirectionsRenderer({ map });

    fetchDeliveryDetails();
}

function fetchDeliveryDetails() {
    const urlParams = new URLSearchParams(window.location.search);
    const orderId = urlParams.get("order_id");

    if (!orderId) {
        console.error("No Order ID found in URL.");
        return;
    }

    fetch(`get_order_details.php?order_id=${orderId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status !== "success") {
                console.error("Error fetching order details:", data.message);
                return;
            }

            let source = data.trip_details.source;
            let destination = `${data.customer_details.street}, ${data.customer_details.city}, ${data.customer_details.state}, ${data.customer_details.country}`;

            if (!source || !destination) {
                console.error("Missing location data for the map.");
                return;
            }

            loadMapRoute(source, destination);
        })
        .catch(error => console.error("Error fetching delivery details:", error));
}

function loadMapRoute(source, destination) {
    let geocoder = new google.maps.Geocoder();

    geocoder.geocode({ address: source }, function (sourceResults, sourceStatus) {
        if (sourceStatus === "OK") {
            let sourceLocation = sourceResults[0].geometry.location;

            geocoder.geocode({ address: destination }, function (destResults, destStatus) {
                if (destStatus === "OK") {
                    let destinationLocation = destResults[0].geometry.location;

                    let sourceMarker = new google.maps.Marker({
                        position: sourceLocation,
                        map: map,
                        label: "W",
                        title: "Warehouse",
                    });

                    let destinationMarker = new google.maps.Marker({
                        position: destinationLocation,
                        map: map,
                        label: "D",
                        title: "Delivery Address",
                    });

                    let bounds = new google.maps.LatLngBounds();
                    bounds.extend(sourceLocation);
                    bounds.extend(destinationLocation);
                    map.fitBounds(bounds);

                    directionsService.route(
                        {
                            origin: sourceLocation,
                            destination: destinationLocation,
                            travelMode: google.maps.TravelMode.DRIVING,
                        },
                        function (response, status) {
                            if (status === "OK") {
                                directionsRenderer.setDirections(response);
                            } else {
                                console.error("Directions request failed due to " + status);
                            }
                        }
                    );
                } else {
                    console.error("Geocoding failed for destination:", destStatus);
                }
            });
        } else {
            console.error("Geocoding failed for source:", sourceStatus);
        }
    });
}
