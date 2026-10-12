<?php

require_once '../config/db.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($name === '' || $email === '' || $password === '' || $confirm_password === '') {

        $message = 'Vui lòng nhập đầy đủ thông tin.';
        $message_type = 'error';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = 'Email không hợp lệ.';
        $message_type = 'error';

    } elseif (strlen($password) < 6) {

        $message = 'Mật khẩu phải có ít nhất 6 ký tự.';
        $message_type = 'error';

    } elseif ($password !== $confirm_password) {

        $message = 'Mật khẩu xác nhận không khớp.';
        $message_type = 'error';

    } else {

        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$email]);

        if ($stmt->fetch()) {

            $message = 'Email này đã được sử dụng.';
            $message_type = 'error';

        } else {

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (name, email, password, role, trang_thai)
                    VALUES (?, ?, ?, 0, 'hoat_dong')";

            $stmt = $conn->prepare($sql);
            $stmt->execute([$name, $email, $hashed_password]);

            header('Location: login.php?dangky=ok');
            exit;
        }
    }
}

$tieu_de_trang = 'Đăng ký - VIBE STUDIO';
$css_rieng = ['kb.css'];
require_once '../includes/header.php';
?>

<main class="auth-trang">
    <div class="auth-container">

        <p class="small-title">ACCOUNT</p>
        <h1>ĐĂNG <i>KÝ</i></h1>

        <?php if ($message !== ''): ?>
            <p class="message <?= htmlspecialchars($message_type) ?>">
                <?= htmlspecialchars($message) ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="">

            <label for="name">Họ và tên</label>
            <input type="text" id="name" name="name" placeholder="Nhập họ và tên"
                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Nhập email"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>

            <label for="password">Mật khẩu</label>
            <input type="password" id="password" name="password" placeholder="Ít nhất 6 ký tự" required>

            <label for="confirm_password">Xác nhận mật khẩu</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Nhập lại mật khẩu" required>

            <button type="submit">ĐĂNG KÝ</button>

        </form>

        <p>
            Đã có tài khoản?
            <a href="login.php">ĐĂNG NHẬP</a>
        </p>

    </div>
</main>

<?php require_once '../includes/footer.php'; ?>