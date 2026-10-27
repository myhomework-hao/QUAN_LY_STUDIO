<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Thêm dấu gạch chéo / sau __DIR__ để lùi ra thư mục gốc chính xác
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/donhang.php';

date_default_timezone_set('Asia/Ho_Chi_Minh');
yeu_cau_vai_tro('nhanvien');

$ma_nhan_vien = ma_nhan_vien_hien_tai($conn);
if ($ma_nhan_vien === null) {
    http_response_code(403);
    die('Tài khoản nhân viên của bạn đang bị khóa.');
}

const SO_DONG_MOI_TRANG = 10;
// Sửa đường dẫn thư mục ảnh trỏ đúng ra thư mục assets/uploads/ chung ở gốc
define('THU_MUC_ANH', dirname(__DIR__) . '/assets/uploads/');

function h($chuoi): string
{
    return htmlspecialchars((string) $chuoi, ENT_QUOTES, 'UTF-8');
}

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

function csrf_token(): string
{
    if (empty($_SESSION['csrf_nv'])) {
        $_SESSION['csrf_nv'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf_nv'];
}

function csrf_input(): string
{
    return '<input type="hidden" name="csrf" value="' . h(csrf_token()) . '">';
}

function csrf_hop_le(): bool
{
    return isset($_POST['csrf']) && hash_equals(csrf_token(), (string) $_POST['csrf']);
}

function dat_thong_bao(string $noi_dung, string $loai = 'success'): void
{
    $_SESSION['nv_thong_bao'] = [$loai, $noi_dung];
}

function chuyen_huong(string $duong_dan): void
{
    header('Location: ' . $duong_dan);
    exit;
}

function ve_trang_truoc(string $trang_goc): string
{
    parse_str((string) ($_POST['ve'] ?? ''), $truy_van);
    $chuoi = http_build_query($truy_van);
    return $trang_goc . ($chuoi !== '' ? '?' . $chuoi : '');
}

function ten_don_vi_gia(string $don_vi): string
{
    return ['gio' => 'giờ', 'ngay' => 'ngày', 'bo' => 'bộ'][$don_vi] ?? $don_vi;
}

function gia_hien_thi($gia, string $don_vi): string
{
    return dinh_dang_tien($gia) . ' / ' . ten_don_vi_gia($don_vi);
}

function lop_badge_don(string $trang_thai): string
{
    return ['cho' => 'badge-cho', 'duyet' => 'badge-xac-nhan', 'xong' => 'badge-hoan-thanh', 'huy' => 'badge-huy'][lop_trang_thai($trang_thai)];
}

function lien_ket_trang(int $trang): string
{
    $truy_van = $_GET;
    $truy_van['trang'] = $trang;
    return '?' . http_build_query($truy_van);
}

function hien_phan_trang(int $tong_trang, int $trang): void
{
    if ($tong_trang <= 1) {
        return;
    }
    echo '<nav class="pager" aria-label="Phân trang">';
    if ($trang > 1) {
        echo '<a href="' . h(lien_ket_trang($trang - 1)) . '">← Trước</a>';
    }
    for ($i = 1; $i <= $tong_trang; $i++) {
        if ($i === 1 || $i === $tong_trang || abs($i - $trang) <= 2) {
            echo '<a href="' . h(lien_ket_trang($i)) . '"' . ($i === $trang ? ' class="active" aria-current="page"' : '') . '>' . $i . '</a>';
        } elseif (abs($i - $trang) === 3) {
            echo '<span>…</span>';
        }
    }
    if ($trang < $tong_trang) {
        echo '<a href="' . h(lien_ket_trang($trang + 1)) . '">Sau →</a>';
    }
    echo '</nav>';
}

function trang_hien_tai(int $tong, int &$tong_trang): int
{
    $tong_trang = max(1, (int) ceil($tong / SO_DONG_MOI_TRANG));
    return min(max(1, (int) ($_GET['trang'] ?? 1)), $tong_trang);
}

function ngay_viet_nam(): string
{
    $thu = ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'];
    return $thu[(int) date('w')] . ', ' . date('d/m/Y');
}

function nv_dau(string $tieu_de, string $muc_chon): void
{
    $menu = [
        'dich-vu' => ['Dịch vụ', 'my-services.php'],
        'them' => ['Thêm dịch vụ', 'dich-vu-form.php'],
        'don' => ['Đơn thuê', 'vendor-orders.php'],
    ];
    $thong_bao = $_SESSION['nv_thong_bao'] ?? null;
    unset($_SESSION['nv_thong_bao']);
    ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($tieu_de) ?> | VIBE Studio</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dashboard.css">
</head>
<body>

<div class="admin">

    <aside class="admin-sidebar">
        <a href="<?= BASE_URL ?>vendor/my-services.php" class="admin-logo">VIBE <i>Nhân viên</i></a>

        <nav class="admin-menu" aria-label="Menu nhân viên">
            <?php foreach ($menu as $ma => [$ten, $lien_ket]) : ?>
                <a href="<?= h($lien_ket) ?>" class="<?= $ma === $muc_chon ? 'active' : '' ?>"><?= h($ten) ?></a>
            <?php endforeach; ?>
        </nav>

        <a href="<?= BASE_URL ?>index.php" class="admin-back">← Về trang chủ</a>
    </aside>

    <div class="admin-main">

        <header class="admin-topbar">
            <div>
                <h1><?= h($tieu_de) ?></h1>
                <p><?= h(ngay_viet_nam()) ?></p>
            </div>
            <div class="admin-user">
                <span><?= h(ten_nguoi_dung_hien_tai()) ?></span>
                <!-- Nếu bạn đã đưa profile.php và logout.php ra root, hãy bỏ tiền tố KB/ đi -->
                <a href="<?= BASE_URL ?>user/profile.php" class="btn-line">Hồ sơ</a>
                <a href="<?= BASE_URL ?>user/logout.php" class="btn-line">Đăng xuất</a>
            </div>
        </header>

        <?php if ($thong_bao) : ?>
            <p class="flash flash-<?= h($thong_bao[0]) ?>" role="alert"><?= h($thong_bao[1]) ?></p>
        <?php endif; ?>
<?php
}

function nv_cuoi(): void
{
    ?>
    </div>
</div>

</body>
</html>
<?php
}