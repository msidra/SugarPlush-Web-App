<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = "localhost";
$user = "root";  
$password = "";  
$dbname = "sugarplush_db";

$conn = new mysqli($host, $user, $password);

if ($conn->connect_error) {
    die(json_encode(["error" => "❌ Database Connection Failed: " . $conn->connect_error]));
}

$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
$conn->query($sql);

$conn->select_db($dbname);

$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL, 
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    street VARCHAR(255),
    city VARCHAR(255),
    state VARCHAR(255),
    zip VARCHAR(10),
    country VARCHAR(100),
    card_number VARCHAR(16),
    expiry_date VARCHAR(5),
    cvv INT(3)
)";
$conn->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(255) NOT NULL UNIQUE,
    price DECIMAL(10,2) NOT NULL,
    image_url VARCHAR(500) NOT NULL
)";
$conn->query($sql);

$sql = "INSERT IGNORE INTO items (item_name, price, image_url) VALUES
    ('Cow Plushy', 25.00, 'https://i.pinimg.com/736x/da/71/fe/da71fee1960d46dda65011cb5fbac93d.jpg'),
    ('Unicorn Plushy', 20.00, 'https://i.pinimg.com/736x/50/3d/18/503d18a3561fadaeb60c5a93ce1e4ebc.jpg'),
    ('Cat Plushy', 18.00, 'https://i.pinimg.com/736x/ba/29/79/ba29790107f4599b5382d23e745b2cee.jpg'),
    ('Penguin Plushy', 22.00, 'https://i.pinimg.com/736x/21/35/8d/21358d9c94f2a1046e42d3a363250971.jpg'),
    ('Zebra Plushy', 32.00, 'https://i.pinimg.com/736x/c0/f9/56/c0f9566ddd02ee6eb877f4db0be16125.jpg'),
    ('Lion Plushy', 45.00, 'https://i.pinimg.com/736x/50/d8/39/50d83913d62ee3f5debf2c5a025519eb.jpg'),
    ('Bunny Plushy', 25.00, 'https://i.pinimg.com/736x/14/9f/ef/149fefff3be2e123a00aa5fabdd7d5ec.jpg'),
    ('Dog Plushy', 15.00, 'https://i.pinimg.com/736x/0f/b3/7c/0fb37c7a5681d00b54b2533c48c2b932.jpg')";
$conn->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS shopping (
    receipt_id INT AUTO_INCREMENT PRIMARY KEY,
    store_code INT(6) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL
)";
$conn->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS truck (
    truck_id INT AUTO_INCREMENT PRIMARY KEY,
    truck_code VARCHAR(6) UNIQUE NOT NULL,
    availability_code VARCHAR(3) NOT NULL
)";
$conn->query($sql);

$sql = "INSERT IGNORE INTO truck (truck_code, availability_code) VALUES
    ('TRK111','AVL'),
    ('TRK121','NOT AVL'),
    ('TRK133','AVL'),
    ('TRK144','NOT AVL'),
    ('TRK555','NOT AVL'),
    ('TRK562','AVL'),
    ('TRK456','AVL'),
    ('TRK245','NOT AVL')";

$conn->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS trip (
    trip_id INT AUTO_INCREMENT PRIMARY KEY,
    source_code VARCHAR(6) NOT NULL,
    destination_code VARCHAR(6) NOT NULL,
    distance_km INT NOT NULL,
    truck_id INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (truck_id) REFERENCES truck(truck_id) ON DELETE CASCADE
)";
$conn->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    date_issued DATE NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    payment_code INT(6) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    trip_id INT NOT NULL,
    receipt_id INT NOT NULL,
    delivered BOOLEAN DEFAULT FALSE,
    delivery_date DATE NOT NULL,
    delivery_time TIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (trip_id) REFERENCES trip(trip_id) ON DELETE CASCADE,
    FOREIGN KEY (receipt_id) REFERENCES shopping(receipt_id) ON DELETE CASCADE
)";
$conn->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS order_items (
    order_id INT,
    item_id INT,
    quantity INT DEFAULT 1,
    PRIMARY KEY (order_id, item_id),
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(item_id) ON DELETE CASCADE
)";
$conn->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS cart (
    cart_id INT AUTO_INCREMENT PRIMARY KEY,
    user_email VARCHAR(255) NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    product_price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    FOREIGN KEY (user_email) REFERENCES users(email) ON DELETE CASCADE
)";
$conn->query($sql);

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['fetch_tables'])) {
    $tables = [];
    $query = "SHOW TABLES";
    $result = $conn->query($query);

    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }
    echo json_encode(["tables" => $tables]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['table'])) {
    $table = $_GET['table'];
    $columns = [];
    
    $query = "DESCRIBE `$table`";
    $result = $conn->query($query);

    while ($row = $result->fetch_assoc()) {
        $columns[] = ["name" => $row['Field'], "type" => $row['Type']];
    }
    echo json_encode(["columns" => $columns]);
    exit();
}

if (isset($_GET['fetch_records'])) {
    $table = $_GET['fetch_records'];
    $query = "SELECT * FROM $table";
    $result = $conn->query($query);
    $records = [];

    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }

    echo json_encode(["records" => $records]);
    exit;
}

if (isset($_GET['fetch_primary_key'])) {
    $table = $_GET['fetch_primary_key'];
    $query = "SHOW KEYS FROM $table WHERE Key_name = 'PRIMARY'";
    $result = $conn->query($query);
    $primaryKey = $result->fetch_assoc()['Column_name'] ?? '';

    echo json_encode(["primary_key" => $primaryKey]);
    exit;
}

if (isset($_GET['fetch_record'])) {
    $table = $_GET['fetch_record'];
    $id = $_GET['id'];
    $pk = $_GET['pk'];

    $query = "SELECT * FROM $table WHERE $pk = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $record = $result->fetch_assoc();

    echo json_encode(["record" => $record]);
    exit;
}
?>
