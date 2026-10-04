<?php

require_once '../config/db.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    $role = (int) ($_POST['role'] ?? 0);
    if (!in_array($role, [0, 2], true)) {
        $role = 0;
    }

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

            $trang_thai = ($role === 2) ? 'cho_duyet' : 'hoat_dong';

            $sql = "INSERT INTO users (name, email, password, role, trang_thai)
                    VALUES (?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);

            $stmt->execute([
                $name,
                $email,
                $hashed_password,
                $role,
                $trang_thai
            ]);

            if ($role === 2) {
                $message = 'Đăng ký thành công! Tài khoản Vendor cần Admin duyệt trước khi đăng nhập.';
            } else {
                $message = 'Đăng ký thành công! Bạn có thể đăng nhập.';
            }
            $message_type = 'success';
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

            <label for="role">Bạn đăng ký với vai trò</label>
            <select id="role" name="role">
                <option value="0" <?= ($_POST['role'] ?? '0') === '0' ? 'selected' : '' ?>>
                    Khách hàng (thuê đồ, thuê studio)
                </option>
                <option value="2" <?= ($_POST['role'] ?? '') === '2' ? 'selected' : '' ?>>
                    Vendor (cho thuê, cần Admin duyệt)
                </option>
            </select>

            <button type="submit">ĐĂNG KÝ</button>

        </form>

        <p>
            Đã có tài khoản?
            <a href="login.php">ĐĂNG NHẬP</a>
        </p>

    </div>
</main>

<?php require_once '../includes/footer.php'; ?>