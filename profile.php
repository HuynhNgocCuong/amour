<?php
session_start();

// Kiểm tra nếu người dùng đã đăng nhập hay chưa
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

// Kết nối cơ sở dữ liệu
require_once 'db.php'; 

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
if ($stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}

$stmt->bind_param("i", $user_id);

if (!$stmt->execute()) {
    die('Execute failed: ' . htmlspecialchars($stmt->error));
}

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user === null) {
    die('No user found with that ID.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="path/to/your/css/styles.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script>
        function confirmLogout() {
            $('#logoutModal').modal('show');
        }

        function handleLogout(action) {
            if (action === 'yes') {
                window.location.href = 'logout.php';
            } else {
                $('#logoutModal').modal('hide');
            }
        }
    </script>
</head>
<body>
    <h1>Profile Page</h1>
    <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
    <button class="btn btn-primary" onclick="confirmLogout()">Logout</button>

    <!-- Modal HTML -->
    <div class="modal" id="logoutModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Đăng xuất?</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn đăng xuất?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="handleLogout('yes')">Có</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="handleLogout('no')">Không</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
