<?php
session_start();


// Kiểm tra nếu người dùng đã đăng nhập hay chưa
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}
include 'db.php'; // Tệp này chứa kết nối cơ sở dữ liệu

// Giả sử người dùng đã đăng nhập và có ID người dùng trong phiên
$user_id = $_SESSION['user_id'] ?? 0; // Giả định user_id là 1 nếu chưa đăng nhập

// Mô phỏng dữ liệu sản phẩm
$products = [];
$result = $conn->query("SELECT * FROM products");
while ($row = $result->fetch_assoc()) {
    $products[$row['id']] = ['name' => $row['name'], 'price' => $row['price']];
}
$total_price = 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['product_id'])) {
        $product_id = $_POST['product_id'];
        $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
        } else {
            $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
        }
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();

        // Tính toán lại tổng số tiền và số lượng sản phẩm
        $totalPrice = 0;
        $itemCount = 0;
        $result = $conn->query("SELECT product_id, quantity FROM cart WHERE user_id = $user_id");
        while ($row = $result->fetch_assoc()) {
            $totalPrice += $products[$row['product_id']]['price'] * $row['quantity'];
            $itemCount += $row['quantity'];
        }

        echo json_encode(['status' => 'success', 'totalPrice' => $totalPrice, 'itemCount' => $itemCount]);
        exit();
    }
 
    if (isset($_POST['remove_id'])) {
        $remove_id = $_POST['remove_id'];
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $remove_id);
        $stmt->execute();

        // Tính toán lại tổng số tiền và số lượng sản phẩm
        $totalPrice = 0;
        $itemCount = 0;
        $result = $conn->query("SELECT product_id, quantity FROM cart WHERE user_id = $user_id");
        while ($row = $result->fetch_assoc()) {
            $totalPrice += $products[$row['product_id']]['price'] * $row['quantity'];
            $itemCount += $row['quantity'];
        }

        echo json_encode(['status' => 'success', 'totalPrice' => $totalPrice, 'itemCount' => $itemCount]);
        exit();
    }

    if (isset($_POST['update_id']) && isset($_POST['quantity'])) {
        $update_id = $_POST['update_id'];
        $quantity = $_POST['quantity'];

        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("iii", $quantity, $user_id, $update_id);
        $stmt->execute();

        // Tính toán lại tổng số tiền và số lượng sản phẩm

        $itemCount = 0;
        $result = $conn->query("SELECT product_id, quantity FROM cart WHERE user_id = $user_id");
        while ($row = $result->fetch_assoc()) {
            $totalPrice += $products[$row['product_id']]['price'] * $row['quantity'];
            $itemCount += $row['quantity'];
        }

        echo json_encode(['status' => 'success', 'totalPrice' => $totalPrice, 'itemCount' => $itemCount]);
        exit();
    }
    if (isset($_POST['ship_price']) && isset($_GET['isship_price'])) {
        $ship_price = $_POST['ship_price'];
        $user_id1 = $_SESSION['user_id'];
        $stmt = $conn->prepare("UPDATE users SET shipping = ? WHERE id = ?");
        $stmt->bind_param("ii", $ship_price, $user_id1);
        $stmt->execute();
        $stmt->close();
        
    }
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Bag</title>
    <link rel="stylesheet" href="./css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    
</head>

<body>
    <section class="h-100 h-custom" style="background-color: #d2c9ff;">
        <div class="container py-5 h-100">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <div class="col-12">
                    <div class="card card-registration card-registration-2" style="border-radius: 15px;">
                        <div class="card-body p-0">
                            <div class="row g-0">
                                <div class="col-lg-8">
                                    <div class="p-5">
                                        <div class="d-flex justify-content-between align-items-center mb-5">
                                            <h1 class="fw-bold mb-0 text-black">Shopping Cart</h1>
                                            <h6 class="mb-0 text-muted" id="item_count">
                                                <?php
                                                $itemCount = 0;
                                                $result = $conn->query("SELECT * FROM cart WHERE user_id = $user_id");
                                                while ($row = $result->fetch_assoc()) {
                                                    $itemCount += $row['quantity'];
                                                }
                                                echo $itemCount . " items";
                                                ?>
                                            </h6>
                                        </div>
                                        <hr class="my-4">

                                        <?php
                                        $result = $conn->query("SELECT * FROM cart WHERE user_id = $user_id");
                                        while ($row = $result->fetch_assoc()) {
                                            $product_id = $row['product_id'];
                                            $quantity = $row['quantity'];
                                            $product = $products[$product_id];
                                            $total_price += $product['price'] * $quantity;
                                        ?>
                                            <div class="row mb-4 d-flex justify-content-between align-items-center" data-item-id="<?php echo $product_id; ?>">
                                                <div class="col-md-2 col-lg-2 col-xl-2">
                                                    <img src="images/nenthom<?php echo $product_id; ?>.png" class="img-fluid rounded-3" alt="<?php echo $product['name']; ?>">
                                                </div>
                                                <div class="col-md-3 col-lg-3 col-xl-3">
                                                    <h6 class="text-muted"><?php echo $product['name']; ?></h6>
                                                </div>
                                                <div class="col-md-3 col-lg-3 col-xl-2 d-flex">
                                                    <input onchange="updateQuantity(this)" id="quantity_<?php echo $product_id; ?>" min="0" name="quantity" value="<?php echo $quantity; ?>" type="number" class="form-control form-control-sm"  />
                                                </div>
                                                <div class="col-md-3 col-lg-2 col-xl-2 offset-lg-1">
                                                    <h6 class="mb-0" id="price_<?php echo $product_id; ?>" data-price="<?php echo $product['price']; ?>"><?php echo number_format($product['price'] * $quantity); ?> VND</h6>
                                                </div>
                                                <div class="col-md-1 col-lg-1 col-xl-1 text-end">
                                                    <button href="#!" class="btn btn-danger" onclick="removeProduct(<?php echo $product_id; ?>)"><i class="fas fa-times"></i>Xóa</button>
                                                </div>
                                            </div>
                                            <hr class="my-4">
                                        <?php } ?>

                                        <div class="pt-5">
                                            <h6 class="mb-0"><a href="index.php" class="text-body"><i class="fas fa-long-arrow-alt-left me-2"></i>Back to shop</a></h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 bg-grey">
                                    <div class="p-5">
                                        <h3 class="fw-bold mb-5 mt-2 pt-1">Summary</h3>
                                        <hr class="my-4">

                                        <div class="d-flex justify-content-between mb-4">
                                            <h5 class="text-uppercase">items <?php echo $itemCount; ?></h5>
                                            <h5 id="item_count_summary"><?php echo $itemCount; ?></h5>
                                        </div>

                                        <h5 class="text-uppercase mb-3">Shipping</h5>

                                        <div class="mb-4 pb-2">
                                            <select class="select p-2 bg-grey" id="shipping_method" onchange="updateShipping(this)">
                                                <option value="0">Standard-Delivery- 0 VND</option>
                                                <option value="30000">Registered-Delivery- 30,000 VND</option>
                                                <option value="50000">Express-Delivery- 50,000 VND</option>
                                            </select>
                                        </div>

                                        <hr class="my-4">

                                        <div class="d-flex justify-content-between mb-5">
                                            <h5 class="text-uppercase">Total price</h5>
                                            <h5 id="total_price"><?php echo number_format($total_price); ?> VND</h5>
                                        </div>

                                        <div class="d-flex justify-content-between mb-5">
                                            <h5 class="text-uppercase">Final price</h5>
                                            <h5 id="final_price"><?php echo number_format($total_price); ?> VND</h5>
                                        </div>
                                        
                                        <button type="button" class="btn btn-dark btn-block btn-lg" data-mdb-ripple-color="dark" onclick="window.location.href = 'pay.php';">Order Now</button>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        function addToCart(productId) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'cart.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.status === 'success') {
                        alert('Sản phẩm đã được thêm vào giỏ hàng.');
                        document.getElementById('total_price').innerText = priceFormatter(response.totalPrice) + ' VND';
                        document.getElementById('final_price').innerText = priceFormatter(response.totalPrice + parseInt(document.getElementById('shipping_method').value)) + ' VND';
                        document.getElementById('item_count').innerText = response.itemCount + ' items';
                        document.getElementById('item_count_summary').innerText = response.itemCount;
                    } else {
                        alert('Có lỗi xảy ra. Vui lòng thử lại.');
                    }
                }
            };
            xhr.send('product_id=' + productId);
        }

        function updateQuantity(input) {
            const amount = parseInt(input.value);
            const productId = input.getAttribute('id').replace('quantity_', '');
            const productPrice = parseInt(document.getElementById(`price_${productId}`).getAttribute('data-price'));

            if (amount < 0) {
                alert('Số lượng không thể nhỏ hơn 0.');
                input.value = 0;
                return;
            }

            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'cart.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.status === 'success') {
                        document.getElementById(`price_${productId}`).textContent = priceFormatter(productPrice * amount) + ' VND';
                        updateCartSummary(response.totalPrice, response.itemCount);
                    } else {
                        alert('Có lỗi xảy ra. Vui lòng thử lại.');
                    }
                }
            };
            xhr.send('update_id=' + productId + '&quantity=' + amount);
        }

        function priceFormatter(number) {
            return number.toLocaleString();
        }

        function updateCartSummary(totalPrice, itemCount) {
            document.getElementById('total_price').innerText = priceFormatter(totalPrice) + ' VND';
            document.getElementById('final_price').innerText = priceFormatter(totalPrice + parseInt(document.getElementById('shipping_method').value)) + ' VND';
            document.getElementById('item_count').innerText = itemCount + ' items';
            document.getElementById('item_count_summary').innerText = itemCount;
        }

        function removeProduct(productId) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'cart.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.status === 'success') {
                        var productRow = document.querySelector("[data-item-id='" + productId + "']");
                        if (productRow) {
                            productRow.parentNode.removeChild(productRow);
                            updateCartSummary(response.totalPrice, response.itemCount);
                        }
                    } else {
                        alert('Có lỗi xảy ra. Vui lòng thử lại.');
                    }
                }
            };
            xhr.send('remove_id=' + productId);
        }

        function updateShipping(get) {
    var ship_price = parseInt(document.getElementById('shipping_method').value);
    var totalPrice = parseInt(document.getElementById('total_price').innerText.replace(/\D/g, ''));
    document.getElementById('final_price').innerText = priceFormatter(totalPrice + ship_price) + ' VND';
    
    let xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            console.log(this.responseText);
            alert('Đã cập nhật thành công phí ship');
        }
    }
    xhr.open("POST", 'solve.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send('ship_price=' + encodeURIComponent(ship_price));
}

    </script>
</body>

</html>
