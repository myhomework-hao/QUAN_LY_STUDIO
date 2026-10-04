<?php

session_start();

require_once '../config/db.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Kiểm tra dữ liệu
    if ($email === '' || $password === '') {

        $message = 'Vui lòng nhập email và mật khẩu.';
        $message_type = 'error';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = 'Email không hợp lệ.';
        $message_type = 'error';

    } else {

        // Tìm tài khoản theo email
        $sql = "SELECT id, name, email, password, role
                FROM users
                WHERE email = ?";

        $stmt = $conn->prepare($sql);
        $stmt->execute([$email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Kiểm tra tài khoản và mật khẩu
        if ($user && password_verify($password, $user['password'])) {

            // Lưu thông tin người dùng vào session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            // Chuyển trang theo role
            // 0 = Customer
            // 1 = Admin
            // 2 = Vendor

            if ($user['role'] == 1) {

                header('Location: ../TP/admin/dashboard.php');
                exit;

            } elseif ($user['role'] == 2) {

                header('Location: ../AT/vendor/dashboard.php');
                exit;

            } else {

                header('Location: ../TP/index.php');
                exit;
            }

        } else {

            $message = 'Email hoặc mật khẩu không chính xác.';
            $message_type = 'error';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Đăng nhập - Studio Management</title>

    <link rel="stylesheet" href="person_d.css">

</head>

<body>

    <div class="auth-container">

        <h1>Đăng nhập</h1>


        <?php if ($message !== ''): ?>

            <p class="message <?= htmlspecialchars($message_type) ?>">
                <?= htmlspecialchars($message) ?>
            </p>

        <?php endif; ?>


        <form method="POST" action="">

            <label for="email">
                Email
            </label>

            <input
                type="email"
                id="email"
                name="email"
                placeholder="Nhập email"
                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                required
            >


            <label for="password">
                Mật khẩu
            </label>

            <input
                type="password"
                id="password"
                name="password"
                placeholder="Nhập mật khẩu"
                required
            >


            <button type="submit">
                Đăng nhập
            </button>

        </form>


        <p>
            Chưa có tài khoản?
            <a href="register.php">Đăng ký</a>
        </p>

    </div>

</body>

</html>