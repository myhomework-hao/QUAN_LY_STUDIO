<?php
require_once '../config/app.php';
require_once '../config/auth.php';
require_once '../config/db.php';

if (da_dang_nhap()) {
    header('Location: ' . trang_chu_theo_vai_tro(vai_tro_hien_tai()));
    exit;
}

$message = '';
$message_type = '';

if (($_GET['dangky'] ?? '') === 'ok') {
    $message = 'Đăng ký thành công! Mời bạn đăng nhập.';
    $message_type = 'success';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {

        $message = 'Vui lòng nhập email và mật khẩu.';
        $message_type = 'error';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = 'Email không hợp lệ.';
        $message_type = 'error';

    } else {

        $sql = "SELECT nd.ND_Ma, nd.ND_Ten, nd.ND_Email, nd.ND_MatKhau, nd.ND_TrangThai, nd.LND_Ma,
                       nv.NV_TrangThai
                FROM nguoidung nd
                LEFT JOIN nhanvien nv ON nv.ND_Ma = nd.ND_Ma
                WHERE nd.ND_Email = ?";

        $stmt = $conn->prepare($sql);
        $stmt->execute([$email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['ND_MatKhau'])) {

            $message = 'Email hoặc mật khẩu không chính xác.';
            $message_type = 'error';

        } elseif ((int) $user['ND_TrangThai'] !== 1
                  || ((int) $user['LND_Ma'] === VAI_TRO_NHAN_VIEN && (int) $user['NV_TrangThai'] !== 1)) {

            $message = 'Tài khoản của bạn đã bị khóa.';
            $message_type = 'error';

        } else {

            session_regenerate_id(true);

            $_SESSION['user_id'] = (int) $user['ND_Ma'];
            $_SESSION['user_name'] = $user['ND_Ten'];
            $_SESSION['user_email'] = $user['ND_Email'];
            $_SESSION['role'] = (int) $user['LND_Ma'];

            $ve_trang = $_GET['tu'] ?? '';

            if ($ve_trang !== '' && strpos($ve_trang, BASE_URL) === 0) {
                header('Location: ' . $ve_trang);
            } else {
                header('Location: ' . trang_chu_theo_vai_tro(vai_tro_hien_tai()));
            }
            exit;
        }
    }
}

$tieu_de_trang = 'Đăng nhập - VIBE STUDIO';
$css_rieng = ['KB.css'];
require_once '../includes/header.php';
?>

<main class="auth-trang">
    <div class="auth-container">

        <p class="small-title">ACCOUNT</p>
        <h1>ĐĂNG <i>NHẬP</i></h1>

        <?php if ($message !== ''): ?>
            <p class="message <?= htmlspecialchars($message_type) ?>">
                <?= htmlspecialchars($message) ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="">

            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Nhập email"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>

            <label for="password">Mật khẩu</label>
            <input type="password" id="password" name="password" placeholder="Nhập mật khẩu" required>

            <button type="submit">ĐĂNG NHẬP</button>

        </form>

        <p>
            Chưa có tài khoản?
            <a href="register.php">ĐĂNG KÝ</a>
        </p>

    </div>
</main>

<?php require_once '../includes/footer.php'; ?>