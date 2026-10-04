<?php
require_once '../config/app.php';
require_once '../includes/auth.php';
require_once '../config/db.php';

$message = '';
$message_type = '';

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

        $sql = "SELECT id, name, email, password, role, trang_thai
                FROM users
                WHERE email = ?";

        $stmt = $conn->prepare($sql);
        $stmt->execute([$email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password'])) {

            $message = 'Email hoặc mật khẩu không chính xác.';
            $message_type = 'error';

        } elseif ($user['trang_thai'] === 'cho_duyet') {

            $message = 'Tài khoản Vendor của bạn đang chờ Admin duyệt.';
            $message_type = 'error';

        } elseif ($user['trang_thai'] === 'tu_choi') {

            $message = 'Yêu cầu đăng ký Vendor của bạn đã bị từ chối.';
            $message_type = 'error';

        } elseif ($user['trang_thai'] === 'bi_khoa') {

            $message = 'Tài khoản của bạn đã bị khóa.';
            $message_type = 'error';

        } else {

            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

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

?>
<?php
$tieu_de_trang = 'Đăng nhập - VIBE STUDIO';
$css_rieng = ['kb.css'];
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