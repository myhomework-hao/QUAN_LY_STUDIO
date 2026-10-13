<?php
require_once '../config/app.php';
require_once '../config/auth.php';
require_once '../config/db.php';

yeu_cau_dang_nhap();

$user_id = (int) $_SESSION['user_id'];

$message = '';
$message_type = '';
$hanh_dong = '';

function lay_nguoi_dung(PDO $conn, int $user_id)
{
    $stmt = $conn->prepare("SELECT id, name, email, role, password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

$user = lay_nguoi_dung($conn, $user_id);

if (!$user) {
    session_unset();
    session_destroy();
    header('Location: ' . BASE_URL . 'KB/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $hanh_dong = $_POST['hanh_dong'] ?? '';

    if ($hanh_dong === 'cap_nhat_thong_tin') {

        $name = trim($_POST['name'] ?? '');

        if ($name === '') {

            $message = 'Họ tên không được để trống.';
            $message_type = 'error';

        } elseif (mb_strlen($name) > 100) {

            $message = 'Họ tên không được dài quá 100 ký tự.';
            $message_type = 'error';

        } else {

            $stmt = $conn->prepare("UPDATE users SET name = ? WHERE id = ?");
            $stmt->execute([$name, $user_id]);

            $_SESSION['user_name'] = $name;

            $message = 'Cập nhật thông tin thành công.';
            $message_type = 'success';

            $user = lay_nguoi_dung($conn, $user_id);
        }

    } elseif ($hanh_dong === 'doi_mat_khau') {

        $mat_khau_hien_tai = $_POST['mat_khau_hien_tai'] ?? '';
        $mat_khau_moi = $_POST['mat_khau_moi'] ?? '';
        $xac_nhan_mat_khau = $_POST['xac_nhan_mat_khau'] ?? '';

        if ($mat_khau_hien_tai === '' || $mat_khau_moi === '' || $xac_nhan_mat_khau === '') {

            $message = 'Vui lòng nhập đầy đủ các ô mật khẩu.';
            $message_type = 'error';

        } elseif (!password_verify($mat_khau_hien_tai, $user['password'])) {

            $message = 'Mật khẩu hiện tại không đúng.';
            $message_type = 'error';

        } elseif (strlen($mat_khau_moi) < 6) {

            $message = 'Mật khẩu mới phải có ít nhất 6 ký tự.';
            $message_type = 'error';

        } elseif ($mat_khau_moi !== $xac_nhan_mat_khau) {

            $message = 'Xác nhận mật khẩu không khớp.';
            $message_type = 'error';

        } elseif ($mat_khau_moi === $mat_khau_hien_tai) {

            $message = 'Mật khẩu mới phải khác mật khẩu hiện tại.';
            $message_type = 'error';

        } else {

            $mat_khau_ma = password_hash($mat_khau_moi, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$mat_khau_ma, $user_id]);

            session_regenerate_id(true);

            $message = 'Đổi mật khẩu thành công.';
            $message_type = 'success';

            $user = lay_nguoi_dung($conn, $user_id);
        }
    }
}

$ten_vai_tro = [
    'khachhang' => 'Khách hàng',
    'nhanvien' => 'Nhân viên',
    'admin' => 'Quản trị viên',
][vai_tro_hien_tai()] ?? '';

$ten_trong_o = ($hanh_dong === 'cap_nhat_thong_tin' && $message_type === 'error')
    ? ($name ?? $user['name'])
    : $user['name'];

$tieu_de_trang = 'Hồ sơ cá nhân - VIBE STUDIO';
$css_rieng = ['kb.css'];
require_once '../includes/header.php';
?>

<main class="auth-trang">
    <div class="auth-container">

        <p class="small-title">ACCOUNT</p>
        <h1>HỒ SƠ <i>CÁ NHÂN</i></h1>

        <?php if ($message !== ''): ?>
            <p class="message <?= htmlspecialchars($message_type) ?>">
                <?= htmlspecialchars($message) ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="">

            <input type="hidden" name="hanh_dong" value="cap_nhat_thong_tin">

            <label for="name">Họ và tên</label>
            <input type="text" id="name" name="name" maxlength="100"
                   value="<?= htmlspecialchars($ten_trong_o) ?>" required>

            <label for="email">Email</label>
            <input type="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>

            <label for="vai_tro">Vai trò</label>
            <input type="text" id="vai_tro" value="<?= htmlspecialchars($ten_vai_tro) ?>" disabled>

            <button type="submit">LƯU THAY ĐỔI</button>

        </form>

        <h2>ĐỔI <i>MẬT KHẨU</i></h2>

        <form method="POST" action="">

            <input type="hidden" name="hanh_dong" value="doi_mat_khau">

            <label for="mat_khau_hien_tai">Mật khẩu hiện tại</label>
            <input type="password" id="mat_khau_hien_tai" name="mat_khau_hien_tai"
                   autocomplete="current-password" required>

            <label for="mat_khau_moi">Mật khẩu mới</label>
            <input type="password" id="mat_khau_moi" name="mat_khau_moi"
                   placeholder="Ít nhất 6 ký tự" autocomplete="new-password" required>

            <label for="xac_nhan_mat_khau">Xác nhận mật khẩu mới</label>
            <input type="password" id="xac_nhan_mat_khau" name="xac_nhan_mat_khau"
                   placeholder="Nhập lại mật khẩu mới" autocomplete="new-password" required>

            <button type="submit">ĐỔI MẬT KHẨU</button>

        </form>

    </div>
</main>

<?php require_once '../includes/footer.php'; ?>