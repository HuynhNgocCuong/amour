<?php
  if (!isset($_SESSION)) {
      session_start();
  }
  
    include('db.php');
    include('handle.php');
  
  if (isset($_SERVER["REQUEST_METHOD"])&& $_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];
        if (login($email, $password)){
          echo "Đăng nhập thành công!";
       //   header("Location:./index.php");
          exit();
      } else {
          echo "Đăng nhập thất bại!";
      }
  }
}
?>