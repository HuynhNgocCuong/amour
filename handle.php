<?php
    session_start(); // Bắt đầu session để sử dụng $_SESSION
// include/handle.php
function emailExists($email) {
    global $conn;
    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

function register($firstname, $lastname, $country, $email, $password, $gender) {
    global $conn;

    // Kiểm tra xem email đã tồn tại chưa
    if (emailExists($email)) {
        $_SESSION['error'] = "Email đã tồn tại.";
    }

    // Mã hóa mật khẩu
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (firstname, lastname, country, email, password, gender) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $firstname, $lastname, $country, $email, $hashed_password, $gender);

    if ($stmt->execute()) {
        $_SESSION['error'] = "Đăng ký thành công.";
    } else {
        $_SESSION['error'] = "Đăng ký thành công.". $stmt->error;
    }
}
function login($email, $password) {
    global $conn;


    // Kiểm tra nếu email hợp lệ
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Email không hợp lệ.";
        header("Location: signin.php");
        exit();
    }

    // Chuẩn bị truy vấn SQL để tìm kiếm email
    $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) { // Giả sử mật khẩu đã được hash bằng password_hash
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['success'] = "Đăng nhập thành công.";
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['error'] = "Email hoặc mật khẩu không đúng.";
            header("Location: signin.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Email hoặc mật khẩu không đúng.";
        header("Location: signin.php");
        exit();
    }
}



function logoutUser() {
    session_start();
    session_unset();
    session_destroy();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}
?>