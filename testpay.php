<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

include 'db.php';

$user_id = $_SESSION['user_id'];

// Xử lý cập nhật địa chỉ
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_address'])) {
        $city = $_POST['city'];
        $district = $_POST['district'];
        $ward = $_POST['ward'];
        $street = $_POST['street'];

        // Kiểm tra xem địa chỉ đã tồn tại chưa
        $stmt = $conn->prepare("SELECT * FROM addresses WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Cập nhật địa chỉ
            $stmt = $conn->prepare("UPDATE addresses SET city = ?, district = ?, ward = ?, street = ? WHERE user_id = ?");
            $stmt->bind_param("sssii", $city, $district, $ward, $street, $user_id);
        } else {
            // Thêm mới địa chỉ
            $stmt = $conn->prepare("INSERT INTO addresses (user_id, city, district, ward, street) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $user_id, $city, $district, $ward, $street);
        }
        $stmt->execute();
    }
}

// Lấy thông tin địa chỉ
$address = [];
$stmt = $conn->prepare("SELECT * FROM addresses WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $address = $result->fetch_assoc();
}

// Lấy thông tin giỏ hàng và tính tổng số tiền
$total_price = $_SESSION['total_price'] ?? 0;
$shipping_cost = 0; // Giá vận chuyển có thể cập nhật từ frontend
$final_price = $total_price + $shipping_cost;

// HTML và JavaScript
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
                <h3>Update Address</h3>
                <form method="post">
                    <div class="mb-3">
                        <label for="city" class="form-label">City/Province</label>
                        <input type="text" class="form-control" id="city" name="city" value="<?php echo $address['city'] ?? ''; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="district" class="form-label">District</label>
                        <input type="text" class="form-control" id="district" name="district" value="<?php echo $address['district'] ?? ''; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="ward" class="form-label">Ward</label>
                        <input type="text" class="form-control" id="ward" name="ward" value="<?php echo $address['ward'] ?? ''; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="street" class="form-label">Street</label>
                        <input type="text" class="form-control" id="street" name="street" value="<?php echo $address['street'] ?? ''; ?>" required>
                    </div>
                    <button type="submit" name="update_address" class="btn btn-primary">Update Address</button>
                </form>
            </div>
            <div class="col-md-6 text-center">
                <h3>Payment</h3>
                <img src="https://api.vietqr.io/image/970436-phucduuu-rAaIT80.jpg?accountName=DO%20NGUYEN%20PHUC" class="img-fluid" alt="QR Code">
                <p>Số tiền cần thanh toán là: <?php echo number_format($final_price); ?> VND</p>
                <form method="post" action="place_order.php">
                    <button type="submit" name="cancel" class="btn btn-danger">Hủy bỏ</button>
                    <button type="submit" name="complete" class="btn btn-success">Hoàn tất</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // JavaScript để cập nhật phí vận chuyển và tính toán tổng giá trị
        document.getElementById('shipping_method').addEventListener('change', function() {
            var shippingCost = parseInt(this.value);
            var totalPrice = <?php echo $total_price; ?>;
            var finalPrice = totalPrice + shippingCost;
            document.getElementById('final_price').innerText = finalPrice.toLocaleString() + ' VND';
        });
    </script>
</body>

</html>
