<?php 
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: signin.php");
        exit();
    }
    include ('db.php');
    $user_id = $_SESSION['user_id'];
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ship_price'])) {
        if (isset($_POST['ship_price'])) {
            $ship_price = $_POST['ship_price'];
            $_SESSION['ship_price'] = $ship_price;
            $stmt = $conn->prepare("UPDATE users SET shipping = ? WHERE id = ?");
            $stmt->bind_param("ii", $ship_price, $user_id);
            $stmt->execute();
            $stmt->close();
        }
    }
?>

<!-- session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ship_price'])) {
    // Lấy giá trị ship_price từ POST request
    $ship_price = (int) $_POST['ship_price'];
    
    // Lưu giá trị ship_price vào session
    $_SESSION['ship_price'] = $ship_price;

    // Cập nhật giá trị final_price vào session
    if (isset($_SESSION['total_price'])) {
        $_SESSION['final_price'] = $_SESSION['total_price'] + $ship_price;
    }

    echo 'Cập nhật thành công';
} else {
    echo 'Cập nhật không thành công';
} -->

