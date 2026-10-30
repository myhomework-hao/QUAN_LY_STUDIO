<?php
require_once '../config/app.php';
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../includes/o-mat-khau.php';

if (da_dang_nhap()) {
    header('Location: ' . trang_chu_theo_vai_tro(vai_tro_hien_tai()));
    exit;
}

$message = '';
$message_type = '';
$email = '';

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

        $stmt = $conn->prepare(
            "SELECT nd.ND_Ma, nd.ND_Ten, nd.ND_Email, nd.ND_MatKhau, nd.ND_TrangThai, nd.LND_Ma, nv.NV_TrangThai
             FROM nguoidung nd
             LEFT JOIN nhanvien nv ON nv.ND_Ma = nd.ND_Ma
             WHERE nd.ND_Email = ?"
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['ND_MatKhau'])) {

            $message = 'Email hoặc mật khẩu không chính xác.';
            $message_type = 'error';

        } elseif ((int) $user['ND_TrangThai'] !== 1
                  || ((int) $user['LND_Ma'] === VAI_TRO_NHAN_VIEN && (int) $user['NV_TrangThai'] !== 1)) {

            $message = 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ studio.';
            $message_type = 'error';

        } else {

            session_regenerate_id(true);

            $_SESSION['user_id'] = (int) $user['ND_Ma'];
            $_SESSION['user_name'] = $user['ND_Ten'];
            $_SESSION['user_email'] = $user['ND_Email'];
            $_SESSION['role'] = (int) $user['LND_Ma'];

            $ve_trang = (string) ($_GET['tu'] ?? '');
            $duong_dan_an_toan = $ve_trang !== ''
                && strpos($ve_trang, BASE_URL) === 0
                && strpos($ve_trang, '//') !== 0
                && strpos($ve_trang, '\\') === false;

            header('Location: ' . ($duong_dan_an_toan ? $ve_trang : trang_chu_theo_vai_tro(vai_tro_hien_tai())));
            exit;
        }
    }
}

$tieu_de_trang = 'Đăng nhập - VIBE STUDIO';
$css_rieng = [BASE_URL . 'assets/css/kb.css'];
require_once '../includes/header.php';
?>

<main class="auth-trang">
    <div class="auth-container">

        <p class="small-title">ACCOUNT</p>
        <h1>ĐĂNG <i>NHẬP</i></h1>

        <?php if ($message !== ''): ?>
            <p class="message <?= htmlspecialchars($message_type) ?>" role="<?= $message_type === 'error' ? 'alert' : 'status' ?>">
                <?= htmlspecialchars($message) ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="" novalidate>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="ten@email.com" autocomplete="email"
                   value="<?= htmlspecialchars($email) ?>" <?= $email === '' ? 'autofocus' : '' ?> required>

            <?php o_mat_khau('password', 'password', 'Mật khẩu', [
                'goi_y' => 'Nhập mật khẩu',
                'autocomplete' => 'current-password',
                'autofocus' => $email !== '',
            ]); ?>

            <button type="submit">ĐĂNG NHẬP</button>

        </form>

        <p>
            Chưa có tài khoản?
            <a href="register.php">ĐĂNG KÝ</a>
        </p>

    </div>
</main>

<?php require_once '../includes/footer.php'; ?>