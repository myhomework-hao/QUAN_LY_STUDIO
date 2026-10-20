<?php
require_once '../config/app.php';
require_once '../config/auth.php';
require_once '../config/db.php';

yeu_cau_dang_nhap();

$user_id = ma_nguoi_dung_hien_tai();

$message = '';
$message_type = '';
$hanh_dong = '';

function lay_nguoi_dung(PDO $conn, int $user_id)
{
    $stmt = $conn->prepare("SELECT ND_Ma, ND_Ten, ND_Email, ND_MatKhau, ND_NgayTao FROM nguoidung WHERE ND_Ma = ?");
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

            $stmt = $conn->prepare("UPDATE nguoidung SET ND_Ten = ? WHERE ND_Ma = ?");
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

        } elseif (!password_verify($mat_khau_hien_tai, $user['ND_MatKhau'])) {

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

            $stmt = $conn->prepare("UPDATE nguoidung SET ND_MatKhau = ? WHERE ND_Ma = ?");
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
    ? ($name ?? $user['ND_Ten'])
    : $user['ND_Ten'];

$chu_cai_dau = mb_strtoupper(mb_substr($user['ND_Ten'], 0, 1));
$ngay_tham_gia = $user['ND_NgayTao'] ? date('d/m/Y', strtotime($user['ND_NgayTao'])) : '';

$tieu_de_trang = 'Hồ sơ cá nhân - VIBE STUDIO';
$css_rieng = ['KB.css'];
require_once '../includes/header.php';
?>

<main class="hs-trang">
    <div class="hs-khung">

        <section class="hs-dau">
            <div class="hs-avatar" aria-hidden="true"><?= htmlspecialchars($chu_cai_dau) ?></div>
            <div class="hs-dau-chu">
                <p class="hs-nhan-nho">TÀI KHOẢN</p>
                <h1 class="hs-ten"><?= htmlspecialchars($user['ND_Ten']) ?></h1>
                <p class="hs-email"><?= htmlspecialchars($user['ND_Email']) ?></p>
                <p class="hs-the">
                    <span class="hs-vai-tro"><?= htmlspecialchars($ten_vai_tro) ?></span>
                    <?php if ($ngay_tham_gia !== ''): ?>
                        <span class="hs-ngay">Thành viên từ <?= htmlspecialchars($ngay_tham_gia) ?></span>
                    <?php endif; ?>
                </p>
            </div>
        </section>

        <?php if ($message !== ''): ?>
            <p class="hs-thongbao <?= htmlspecialchars($message_type) ?>" role="alert">
                <?= htmlspecialchars($message) ?>
            </p>
        <?php endif; ?>

        <div class="hs-luoi">

            <section class="hs-the-khoi">
                <h2 class="hs-tieu-de">Thông tin <i>cá nhân</i></h2>

                <form method="POST" action="">
                    <input type="hidden" name="hanh_dong" value="cap_nhat_thong_tin">

                    <label for="name">Họ và tên</label>
                    <input type="text" id="name" name="name" maxlength="100"
                           value="<?= htmlspecialchars($ten_trong_o) ?>" required>

                    <label for="email">Email</label>
                    <input type="email" id="email" value="<?= htmlspecialchars($user['ND_Email']) ?>" disabled>

                    <label for="vai_tro">Vai trò</label>
                    <input type="text" id="vai_tro" value="<?= htmlspecialchars($ten_vai_tro) ?>" disabled>

                    <button type="submit">LƯU THAY ĐỔI</button>
                </form>
            </section>

            <section class="hs-the-khoi">
                <h2 class="hs-tieu-de">Đổi <i>mật khẩu</i></h2>

                <form method="POST" action="">
                    <input type="hidden" name="hanh_dong" value="doi_mat_khau">

                    <label for="mat_khau_hien_tai">Mật khẩu hiện tại</label>
                    <div class="hs-o-mat-khau">
                        <input type="password" id="mat_khau_hien_tai" name="mat_khau_hien_tai"
                               autocomplete="current-password" required>
                        <button type="button" class="hs-hien" data-dich="mat_khau_hien_tai">Hiện</button>
                    </div>

                    <label for="mat_khau_moi">Mật khẩu mới</label>
                    <div class="hs-o-mat-khau">
                        <input type="password" id="mat_khau_moi" name="mat_khau_moi"
                               placeholder="Ít nhất 6 ký tự" autocomplete="new-password" required>
                        <button type="button" class="hs-hien" data-dich="mat_khau_moi">Hiện</button>
                    </div>

                    <label for="xac_nhan_mat_khau">Xác nhận mật khẩu mới</label>
                    <div class="hs-o-mat-khau">
                        <input type="password" id="xac_nhan_mat_khau" name="xac_nhan_mat_khau"
                               placeholder="Nhập lại mật khẩu mới" autocomplete="new-password" required>
                        <button type="button" class="hs-hien" data-dich="xac_nhan_mat_khau">Hiện</button>
                    </div>

                    <button type="submit">ĐỔI MẬT KHẨU</button>
                </form>
            </section>

        </div>
    </div>
</main>

<script>
document.querySelectorAll('.hs-hien').forEach(function (nut) {
    nut.addEventListener('click', function () {
        var o = document.getElementById(nut.dataset.dich);
        var dangAn = o.type === 'password';
        o.type = dangAn ? 'text' : 'password';
        nut.textContent = dangAn ? 'Ẩn' : 'Hiện';
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>