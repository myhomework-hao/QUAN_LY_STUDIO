<?php
require_once '../config/app.php';
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../includes/o-mat-khau.php';

if (da_dang_nhap()) {
    header('Location: ' . trang_chu_theo_vai_tro(vai_tro_hien_tai()));
    exit;
}

$loi = [];
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($name === '') {
        $loi['name'] = 'Vui lòng nhập họ và tên.';
    } elseif (mb_strlen($name) > 100) {
        $loi['name'] = 'Họ và tên không được dài quá 100 ký tự.';
    }

    if ($email === '') {
        $loi['email'] = 'Vui lòng nhập email.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 100) {
        $loi['email'] = 'Email không hợp lệ.';
    }

    if ($password === '') {
        $loi['password'] = 'Vui lòng nhập mật khẩu.';
    } elseif (strlen($password) < 6) {
        $loi['password'] = 'Mật khẩu phải có ít nhất 6 ký tự.';
    }

    if ($confirm_password === '') {
        $loi['confirm_password'] = 'Vui lòng nhập lại mật khẩu.';
    } elseif ($password !== $confirm_password) {
        $loi['confirm_password'] = 'Mật khẩu xác nhận không khớp.';
    }

    if (!isset($loi['email'])) {
        $stmt = $conn->prepare("SELECT ND_Ma FROM nguoidung WHERE ND_Email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $loi['email'] = 'Email này đã được sử dụng.';
        }
    }

    if (!$loi) {
        try {
            $stmt = $conn->prepare(
                "INSERT INTO nguoidung (ND_Ten, ND_Email, ND_MatKhau, ND_TrangThai, LND_Ma) VALUES (?, ?, ?, 1, ?)"
            );
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), VAI_TRO_KHACH_HANG]);

            header('Location: login.php?dangky=ok');
            exit;
        } catch (PDOException $e) {
            $loi['email'] = $e->getCode() === '23000'
                ? 'Email này đã được sử dụng.'
                : 'Không thể tạo tài khoản, vui lòng thử lại.';
        }
    }
}

$tieu_de_trang = 'Đăng ký - VIBE STUDIO';
$css_rieng = [BASE_URL . 'assets/css/kb.css'];
require_once '../includes/header.php';
?>

<main class="auth-trang">
    <div class="auth-container">

        <p class="small-title">ACCOUNT</p>
        <h1>ĐĂNG <i>KÝ</i></h1>

        <form method="POST" action="" novalidate>

            <label for="name">Họ và tên</label>
            <input type="text" id="name" name="name" placeholder="Nguyễn Văn A" maxlength="100" autocomplete="name"
                   class="<?= isset($loi['name']) ? 'co-loi' : '' ?>" value="<?= htmlspecialchars($name) ?>" autofocus required>
            <?php if (isset($loi['name'])): ?><span class="loi-truong" role="alert"><?= htmlspecialchars($loi['name']) ?></span><?php endif; ?>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="ten@email.com" autocomplete="email"
                   class="<?= isset($loi['email']) ? 'co-loi' : '' ?>" value="<?= htmlspecialchars($email) ?>" required>
            <?php if (isset($loi['email'])): ?><span class="loi-truong" role="alert"><?= htmlspecialchars($loi['email']) ?></span><?php endif; ?>

            <?php o_mat_khau('password', 'password', 'Mật khẩu', [
                'goi_y' => 'Ít nhất 6 ký tự',
                'autocomplete' => 'new-password',
                'loi' => $loi['password'] ?? '',
            ]); ?>

            <?php o_mat_khau('confirm_password', 'confirm_password', 'Xác nhận mật khẩu', [
                'goi_y' => 'Nhập lại mật khẩu',
                'autocomplete' => 'new-password',
                'loi' => $loi['confirm_password'] ?? '',
            ]); ?>
            <span class="khop-mat-khau" id="khop-mat-khau" aria-live="polite"></span>

            <button type="submit">TẠO TÀI KHOẢN</button>

        </form>

        <p>
            Đã có tài khoản?
            <a href="login.php">ĐĂNG NHẬP</a>
        </p>

    </div>
</main>

<script>
(function () {
    var a = document.getElementById('password');
    var b = document.getElementById('confirm_password');
    var chu = document.getElementById('khop-mat-khau');
    function kiemTra() {
        if (b.value === '') { chu.textContent = ''; chu.className = 'khop-mat-khau'; return; }
        var khop = a.value === b.value;
        chu.textContent = khop ? 'Mật khẩu khớp.' : 'Mật khẩu chưa khớp.';
        chu.className = 'khop-mat-khau ' + (khop ? 'dung' : 'sai');
    }
    a.addEventListener('input', kiemTra);
    b.addEventListener('input', kiemTra);
})();
</script>

<?php require_once '../includes/footer.php'; ?>