<?php
session_start();
include 'db.php';

// Kiểm tra nếu người dùng đã đăng nhập hay chưa
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

// Lấy ID người dùng từ phiên
$user_id = $_SESSION['user_id'];

// Kiểm tra và lấy thông tin địa chỉ từ cơ sở dữ liệu
$address = [
    'city' => '',
    'district' => '',
    'ward' => '',
    'house_number' => ''
];

$stmt = $conn->prepare("SELECT * FROM addresses WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $address = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cập nhật thông tin địa chỉ
    if (isset($_POST['city'], $_POST['district'], $_POST['ward'], $_POST['house_number'])) {
        $city = $_POST['city'];
        $district = $_POST['district'];
        $ward = $_POST['ward'];
        $house_number = $_POST['house_number'];

        if ($result->num_rows > 0) {
            // Update address
            $stmt = $conn->prepare("UPDATE addresses SET city = ?, district = ?, ward = ?, house_number = ? WHERE user_id = ?");
            $stmt->bind_param("ssssi", $city, $district, $ward, $house_number, $user_id);
        } else {
            // Insert new address
            $stmt = $conn->prepare("INSERT INTO addresses (user_id, city, district, ward, house_number) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $user_id, $city, $district, $ward, $house_number);
        }
        $stmt->execute();
        header("Location: pay.php");
        exit();
    }
}

// Tính tổng số tiền từ giỏ hàng
$totalPrice = 0;
$result = $conn->query("SELECT product_id, quantity FROM cart WHERE user_id = $user_id");
while ($row = $result->fetch_assoc()) {
    $product_id = $row['product_id'];
    $quantity = $row['quantity'];
    $stmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product_result = $stmt->get_result();
    $product = $product_result->fetch_assoc();
    $totalPrice += $product['price'] * $quantity;
}

// Đặt giá trị mặc định cho tiền ship
$shipping_cost = 0;
$result = $conn->query("SELECT shipping FROM users WHERE id = $user_id");
if ($row = $result->fetch_assoc()) {
    $shipping_cost = $row['shipping'];
} else {
    $shipping_cost = 0; // Mặc định phí vận chuyển nếu không có thông tin
}
    $totalPrice = $totalPrice + $shipping_cost;

$final_price = $totalPrice;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>
    <link rel="stylesheet" href="./css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h2>Update Address Information</h2>
                <form method="POST">
                    <div class="mb-3">
                        <label for="city" class="form-label">City/Province</label>
                        <input type="text" class="form-control" id="city" name="city" value="<?php echo $address['city']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="district" class="form-label">District</label>
                        <input type="text" class="form-control" id="district" name="district" value="<?php echo $address['district']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="ward" class="form-label">Ward</label>
                        <input type="text" class="form-control" id="ward" name="ward" value="<?php echo $address['ward']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="house_number" class="form-label">House Number</label>
                        <input type="text" class="form-control" id="house_number" name="house_number" value="<?php echo $address['house_number']; ?>" required>
                    </div>
                    <button type="submit"  class="btn btn-primary">Update Address</button>
                </form>
            </div>
                 <div class="col-md-6 d-flex flex-column align-items-start">
                        <h2>Payment Information</h2>
                        <img src="https://api.vietqr.io/image/970436-phucduuu-rAaIT80.jpg?accountName=DO%20NGUYEN%20PHUC" class="img-fluid mb-3" alt="QR Code">
                        <p>Số tiền cần thanh toán là: <?php echo number_format($final_price); ?> VND</p>
                        <div class="d-flex">
                            <button class="btn btn-secondary me-2" onclick="window.location.href = 'cart.php';">Huỷ bỏ</button>
                            <button class="btn btn-success" onclick="completeOrder();">Hoàn tất</button>
                        </div>
                 </div>



        </div>
    </div>

    <script>
        function completeOrder() {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'complete_order.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    alert('Đã đặt hàng thành công!');
                    window.location.href = 'index.php';
                }
            };
            xhr.send('user_id=<?php echo $user_id; ?>&total_cost=<?php echo $final_price; ?>');
        }
    </script>
</body>
</html>
