<?php
session_start();
session_unset(); // Xóa tất cả các biến session
session_destroy(); // Hủy session

session_start(); // Bắt đầu session mới để thiết lập thông báo
$_SESSION['logout_success'] = "Bạn đã đăng xuất thành công!";
header("Location: index.php"); // Chuyển hướng về trang chủ
exit();
?>
