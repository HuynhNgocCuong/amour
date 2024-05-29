<?php
session_start();
// auth/register.php
include('db.php');
include('handle.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $firstname = $_POST ['first_name'];
    $lastname = $_POST ['last_name'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $country = $_POST ['country'];
    $gender = $_POST ['gender'];
    
    // Kiểm tra và đăng ký người dùng
    $result = register($firstname, $lastname, $country, $email, $password, $gender);

    if ($result === "Đăng ký thành công.") {
        // Chuyển hướng đến trang signin.php
        header("Location: signin.php");
        exit;
    } else {
        // Chuyển hướng lại trang signup.php và hiển thị thông báo lỗi
        header("Location: signup.php" . urlencode($result));
        exit;
    }
}
?>