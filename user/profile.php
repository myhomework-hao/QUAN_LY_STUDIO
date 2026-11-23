<?php
require_once '../config/app.php';
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../includes/security.php';
require_once '../includes/dich_vu.php';
require_once '../includes/donhang.php';

function gia_tri(PDO $conn, string $sql, array $tham_so = [])
{
    $stmt = $conn->prepare($sql);
    $stmt->execute($tham_so);
    return $stmt->fetchColumn();
}
function lay_nhieu(PDO $conn, string $sql, array $tham_so = []): array
{
    $stmt = $conn->prepare($sql);
    $stmt->execute($tham_so);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function lay_mot(PDO $conn, string $sql, array $tham_so = [])
{
    $stmt = $conn->prepare($sql);
    $stmt->execute($tham_so);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

yeu_cau_dang_nhap();

$user_id = ma_nguoi_dung_hien_tai();

$message = '';
$message_type = '';
$hanh_dong = '';

function lay_nguoi_dung(PDO $conn, int $user_id)
{
    $stmt = $conn->prepare("SELECT ND_Ma, ND_Ten, ND_Email, ND_MatKhau, ND_NgayTao, ND_Anh, ND_GioiTinh, ND_NgaySinh, ND_SDT FROM nguoidung WHERE ND_Ma = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

$user = lay_nguoi_dung($conn, $user_id);

if (!$user) {
    session_unset();
    session_destroy();
    header('Location: ' . BASE_URL . 'user/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $hanh_dong = $_POST['hanh_dong'] ?? '';

    if (!csrf_hop_le()) {
        $message = 'Phiên làm việc đã hết hạn, vui lòng tải lại trang và thử lại.';
        $message_type = 'error';
        $hanh_dong = '';
    }

    if ($hanh_dong === 'cap_nhat_avatar') {

        $tep = $_FILES['avatar'] ?? null;
        if (!$tep || ($tep['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            $message = 'Vui lòng chọn ảnh avatar.';
            $message_type = 'error';
        } elseif (($tep['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            $message = 'Tải ảnh lên không thành công.';
            $message_type = 'error';
        } elseif (($tep['size'] ?? 0) > 2 * 1024 * 1024) {
            $message = 'Ảnh không được lớn hơn 2MB.';
            $message_type = 'error';
        } else {
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tep['tmp_name']);
            $dang_cho_phep = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            if (!isset($dang_cho_phep[$mime]) || getimagesize($tep['tmp_name']) === false) {
                $message = 'Ảnh phải có định dạng JPG, PNG hoặc WEBP.';
                $message_type = 'error';
            } else {
                $thu_muc = __DIR__ . '/../assets/uploads/';
                if (!is_dir($thu_muc)) {
                    mkdir($thu_muc, 0755, true);
                }
                $ten_tep = 'avatar_' . bin2hex(random_bytes(8)) . '.' . $dang_cho_phep[$mime];
                if (!move_uploaded_file($tep['tmp_name'], $thu_muc . $ten_tep)) {
                    $message = 'Không thể lưu ảnh lên máy chủ.';
                    $message_type = 'error';
                } else {
                    if (!empty($user['ND_Anh']) && is_file($thu_muc . $user['ND_Anh'])) {
                        @unlink($thu_muc . $user['ND_Anh']);
                    }
                    $stmt = $conn->prepare("UPDATE nguoidung SET ND_Anh = ? WHERE ND_Ma = ?");
                    $stmt->execute([$ten_tep, $user_id]);
                    $_SESSION['user_avatar'] = $ten_tep;
                    $message = 'Cập nhật avatar thành công.';
                    $message_type = 'success';
                    $user = lay_nguoi_dung($conn, $user_id);
                }
            }
        }

    } elseif ($hanh_dong === 'cap_nhat_thong_tin') {

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $sdt = trim($_POST['sdt'] ?? '');
        $ngay_sinh = trim($_POST['ngay_sinh'] ?? '');
        $gioi_tinh = trim($_POST['gioi_tinh'] ?? '');

        if ($name === '') {
            $message = 'Họ tên không được để trống.';
            $message_type = 'error';
        } elseif (mb_strlen($name) > 100) {
            $message = 'Họ tên không được dài quá 100 ký tự.';
            $message_type = 'error';
        } elseif ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Email không hợp lệ.';
            $message_type = 'error';
        } else {
            $stmt = $conn->prepare("SELECT ND_Ma FROM nguoidung WHERE ND_Email = ? AND ND_Ma <> ?");
            $stmt->execute([$email, $user_id]);
            if ($stmt->fetch()) {
                $message = 'Email đã được sử dụng bởi tài khoản khác.';
                $message_type = 'error';
            } elseif ($sdt !== '' && !preg_match('/^0[0-9]{9,10}$/', $sdt)) {
                $message = 'Số điện thoại không hợp lệ.';
                $message_type = 'error';
            } elseif ($ngay_sinh !== '' && (!DateTime::createFromFormat('Y-m-d', $ngay_sinh) || strtotime($ngay_sinh) > time())) {
                $message = 'Ngày sinh không hợp lệ.';
                $message_type = 'error';
            } else {
                $stmt = $conn->prepare("UPDATE nguoidung SET ND_Ten = ?, ND_Email = ?, ND_SDT = ?, ND_NgaySinh = ?, ND_GioiTinh = ? WHERE ND_Ma = ?");
                $stmt->execute([$name, $email, $sdt !== '' ? $sdt : null, $ngay_sinh !== '' ? $ngay_sinh : null, $gioi_tinh !== '' ? $gioi_tinh : null, $user_id]);
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $message = 'Cập nhật thông tin thành công.';
                $message_type = 'success';
                $user = lay_nguoi_dung($conn, $user_id);
            }
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
        } elseif (strlen($mat_khau_moi) < 8) {
            $message = 'Mật khẩu mới phải có ít nhất 8 ký tự.';
            $message_type = 'error';
        } elseif ($mat_khau_moi !== $xac_nhan_mat_khau) {
            $message = 'Xác nhận mật khẩu không khớp.';
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

// Thong ke
$so_don_hoan_thanh = (int) gia_tri($conn, "SELECT COUNT(*) FROM dondatlich WHERE ND_Ma = ? AND DDL_TrangThai = ?", [$user_id, TRANG_THAI_HOAN_THANH]);
$so_don_dang_thue = (int) gia_tri($conn, "SELECT COUNT(*) FROM dondatlich WHERE ND_Ma = ? AND DDL_TrangThai = ?", [$user_id, TRANG_THAI_DA_DUYET]);
$so_danh_gia = (int) gia_tri($conn, "SELECT COUNT(*) FROM danhgia WHERE ND_Ma = ?", [$user_id]);

// Don gan day
$don_gan_day = lay_nhieu(
    $conn,
    "SELECT d.DDL_Ma, d.DDL_TrangThai, d.DDL_NgayTao, c.CTD_BatDau, c.CTD_KetThuc, c.DV_Ma, dv.DV_Ten
     FROM dondatlich d
     JOIN chitietdon c ON c.DDL_Ma = d.DDL_Ma
     JOIN dichvu dv ON dv.DV_Ma = c.DV_Ma
     WHERE d.ND_Ma = ?
     ORDER BY d.DDL_NgayTao DESC, d.DDL_Ma DESC
     LIMIT 8",
    [$user_id]
);
$don_unique = [];
foreach ($don_gan_day as $d) {
    if (!isset($don_unique[$d['DDL_Ma']])) {
        $don_unique[$d['DDL_Ma']] = $d;
    }
    if (count($don_unique) >= 4) {
        break;
    }
}

// Danh gia gan nhat
$danh_gia_gan_nhat = lay_mot(
    $conn,
    "SELECT dg.DG_NoiDung, dg.DG_NgayTao, dv.DV_Ten
     FROM danhgia dg
     JOIN dichvu dv ON dv.DV_Ma = dg.DV_Ma
     WHERE dg.ND_Ma = ?
     ORDER BY dg.DG_NgayTao DESC
     LIMIT 1",
    [$user_id]
);

$chu_cai_dau = mb_strtoupper(mb_substr($user['ND_Ten'], 0, 1));
$ngay_tham_gia = $user['ND_NgayTao'] ? date('m/Y', strtotime($user['ND_NgayTao'])) : '';

$ten_trong_o = ($hanh_dong === 'cap_nhat_thong_tin' && $message_type === 'error')
    ? ($name ?? $user['ND_Ten'])
    : $user['ND_Ten'];

$ten_vai_tro = [
    'khachhang' => 'Khách hàng',
    'nhanvien' => 'Nhân viên',
    'admin' => 'Quản trị viên',
][vai_tro_hien_tai()] ?? '';

$tieu_de_trang = 'Hồ sơ cá nhân - VIBE STUDIO';
$css_rieng = [BASE_URL . 'assets/css/profile.css'];
require_once '../includes/header.php';
?>
<main class="ho-so-trang">
    <section class="ho-so-dau">
        <p class="nhan-nho">HỒ SƠ CÁ NHÂN</p>
        <h1>Xin chào, <i><?= htmlspecialchars($user['ND_Ten']) ?></i></h1>
        <?php if ($ngay_tham_gia !== ''): ?>
            <div class="ho-so-dau-phai">THÀNH VIÊN TỪ <?= htmlspecialchars($ngay_tham_gia) ?></div>
        <?php endif; ?>
    </section>

    <div class="ho-so-than">
        <aside class="cot-trai">
            <div class="ho-so-avatar">
                <?php if (!empty($user['ND_Anh'])) : ?>
                    <img src="<?= BASE_URL ?>assets/uploads/<?= htmlspecialchars($user['ND_Anh']) ?>" alt="Avatar">
                <?php else : ?>
                    <?= htmlspecialchars($chu_cai_dau) ?>
                <?php endif; ?>
            </div>

            <form method="POST" enctype="multipart/form-data" style="margin-bottom:16px;">
                <?= csrf_input() ?>
                <input type="hidden" name="hanh_dong" value="cap_nhat_avatar">
                <label class="nut-doi-anh">
                    ĐỔI ẢNH ĐẠI DIỆN
                    <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" style="display:none;" onchange="this.form.submit()">
                </label>
            </form>

            <h2 class="ho-so-ten"><?= htmlspecialchars($user['ND_Ten']) ?></h2>
            <p class="ho-so-email"><?= htmlspecialchars($user['ND_Email']) ?></p>

            <ul class="menu-ho-so">
                <li><a href="#thong-tin" class="dang-chon">THÔNG TIN CÁ NHÂN</a></li>
                <li><a href="#don-thue">ĐƠN THUÊ</a></li>
                <li><a href="#danh-gia">ĐÁNH GIÁ CỦA TÔI</a></li>
                <li><a href="#bao-mat">BẢO MẬT</a></li>
                <li>
                    <form method="POST" action="<?= BASE_URL ?>user/logout.php" style="margin:0;">
                        <?= csrf_input() ?>
                        <button type="submit" class="menu-ho-so a dang-xuat" style="background:none;border:none;padding:14px 0;width:100%;text-align:left;cursor:pointer;">ĐĂNG XUẤT</button>
                    </form>
                </li>
            </ul>
        </aside>

        <main class="cot-phai">
            <?php if ($message !== ''): ?>
                <p class="thong-bao <?= htmlspecialchars($message_type) ?>" role="alert"><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>

            <section class="phan" id="thong-tin">
                <div class="phan-tieu-de">
                    <span class="phan-so">01</span>
                    <h2 class="phan-ten">Thông tin <i>cá nhân</i></h2>
                </div>
                <form method="POST" class="ho-so-form">
                    <?= csrf_input() ?>
                    <input type="hidden" name="hanh_dong" value="cap_nhat_thong_tin">
                    <div class="hang">
                        <div>
                            <label for="name">Họ và tên</label>
                            <input type="text" id="name" name="name" maxlength="100" value="<?= htmlspecialchars($ten_trong_o) ?>" required>
                            <?php if ($message_type === 'error' && $hanh_dong === 'cap_nhat_thong_tin' && ($name ?? '') === ''): ?>
                                <span class="loi-truong">Họ tên không được để trống.</span>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['ND_Email']) ?>" required>
                        </div>
                    </div>
                    <div class="hang">
                        <div>
                            <label for="sdt">Số điện thoại</label>
                            <input type="text" id="sdt" name="sdt" value="<?= htmlspecialchars($user['ND_SDT'] ?? '') ?>">
                        </div>
                        <div>
                            <label for="ngay_sinh">Ngày sinh</label>
                            <input type="date" id="ngay_sinh" name="ngay_sinh" value="<?= htmlspecialchars($user['ND_NgaySinh'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="hang">
                        <div>
                            <label for="gioi_tinh">Giới tính</label>
                            <select id="gioi_tinh" name="gioi_tinh" style="width:100%;border:none;border-bottom:1px solid #bbb;padding:8px 0;background:transparent;">
                                <option value="">Chưa cập nhật</option>
                                <option value="Nam" <?= ($user['ND_GioiTinh'] ?? '') === 'Nam' ? 'selected' : '' ?>>Nam</option>
                                <option value="Nữ" <?= ($user['ND_GioiTinh'] ?? '') === 'Nữ' ? 'selected' : '' ?>>Nữ</option>
                                <option value="Khác" <?= ($user['ND_GioiTinh'] ?? '') === 'Khác' ? 'selected' : '' ?>>Khác</option>
                            </select>
                        </div>
                        <div></div>
                    </div>
                    <button type="submit" class="nut-chinh">LƯU THAY ĐỔI</button>
                    <button type="reset" class="nut-phu">HỦY</button>
                </form>
            </section>

            <section class="phan" id="tong-quan">
                <div class="phan-tieu-de">
                    <span class="phan-so">02</span>
                    <h2 class="phan-ten">Tổng <i>quan</i></h2>
                </div>
                <div class="thong-ke">
                    <div>
                        <div class="so"><?= $so_don_hoan_thanh ?></div>
                        <div class="nhan">Đơn đã thuê</div>
                    </div>
                    <div>
                        <div class="so"><?= $so_don_dang_thue ?></div>
                        <div class="nhan">Đang thuê</div>
                    </div>
                    <div>
                        <div class="so"><?= $so_danh_gia ?></div>
                        <div class="nhan">Đánh giá đã viết</div>
                    </div>
                </div>
            </section>

            <section class="phan" id="don-thue">
                <div class="phan-tieu-de">
                    <span class="phan-so">03</span>
                    <h2 class="phan-ten">Đơn thuê <i>gần đây</i></h2>
                </div>
                <div class="don-gan-day">
                    <?php if (empty($don_unique)) : ?>
                        <p class="dong-nhe">Bạn chưa có đơn thuê nào.</p>
                    <?php else : ?>
                        <?php foreach ($don_unique as $d) : ?>
                            <div class="dong">
                                <div>
                                    <div class="ma-don">MÃ ĐƠN <?= h(ma_don_hien_thi((int) $d['DDL_Ma'])) ?></div>
                                    <div class="ten-dv"><?= h($d['DV_Ten']) ?></div>
                                    <div class="thoi-gian"><?= h(dinh_dang_ngay_gio($d['CTD_BatDau'])) ?> → <?= h(dinh_dang_ngay_gio($d['CTD_KetThuc'] ?? $d['CTD_BatDau'])) ?></div>
                                </div>
                                <div>
                                    <span class="the-trang-thai <?= h(lop_trang_thai_css($d['DDL_TrangThai'])) ?>"><?= h($d['DDL_TrangThai']) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <a class="lien-ket-tat-ca" href="<?= BASE_URL ?>user/my-orders.php">XEM TẤT CẢ ĐƠN THUÊ →</a>
            </section>

            <section class="phan" id="danh-gia">
                <div class="phan-tieu-de">
                    <span class="phan-so">04</span>
                    <h2 class="phan-ten">Đánh giá <i>của tôi</i></h2>
                </div>
                <?php if ($danh_gia_gan_nhat) : ?>
                    <div class="danh-gia-gan-nhat">
                        “<?= htmlspecialchars($danh_gia_gan_nhat['DG_NoiDung'] ?: 'Không có nội dung') ?>”
                        <div class="ten-dv"><?= htmlspecialchars($danh_gia_gan_nhat['DV_Ten']) ?></div>
                    </div>
                <?php else : ?>
                    <p class="dong-nhe">Bạn chưa có đánh giá nào.</p>
                <?php endif; ?>
            </section>

            <section class="phan" id="bao-mat">
                <div class="phan-tieu-de">
                    <span class="phan-so">05</span>
                    <h2 class="phan-ten">Bảo <i>mật</i></h2>
                </div>
                <div class="bao-mat-dong">
                    <div>
                        <strong style="font-size:12px;letter-spacing:1px;text-transform:uppercase;">Mật khẩu</strong>
                        <p>Thay đổi mật khẩu đăng nhập của bạn.</p>
                    </div>
                    <button type="button" class="nut-phu" onclick="document.getElementById('form-doi-mat-khau').style.display='block'">ĐỔI MẬT KHẨU</button>
                </div>
                <form method="POST" action="" id="form-doi-mat-khau" style="display:none;margin-top:24px;">
                    <?= csrf_input() ?>
                    <input type="hidden" name="hanh_dong" value="doi_mat_khau">
                    <label for="mat_khau_hien_tai">Mật khẩu hiện tại</label>
                    <input type="password" id="mat_khau_hien_tai" name="mat_khau_hien_tai" required>
                    <label for="mat_khau_moi">Mật khẩu mới</label>
                    <input type="password" id="mat_khau_moi" name="mat_khau_moi" placeholder="Ít nhất 8 ký tự" required>
                    <label for="xac_nhan_mat_khau">Xác nhận mật khẩu mới</label>
                    <input type="password" id="xac_nhan_mat_khau" name="xac_nhan_mat_khau" required>
                    <button type="submit" class="nut-chinh">ĐỔI MẬT KHẨU</button>
                </form>
            </section>
        </main>
    </div>
</main>

<?php
// helper lop_trang_thai_css
function lop_trang_thai_css(string $trang_thai): string
{
    return [
        'Chờ duyệt' => 'cho-duyet',
        'Đã duyệt' => 'da-duyet',
        'Đang thuê' => 'dang-thue',
        'Đã từ chối' => 'da-tu-choi',
        'Hoàn thành' => 'hoan-thanh',
    ][$trang_thai] ?? 'cho-duyet';
}
?>

<?php require_once '../includes/footer.php'; ?>