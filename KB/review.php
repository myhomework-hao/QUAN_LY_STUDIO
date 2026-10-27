<?php
require_once '../config/app.php';
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../includes/donhang.php';

yeu_cau_vai_tro('khachhang');

$ma_nguoi_dung = ma_nguoi_dung_hien_tai();
$ma_don = (int) ($_GET['id'] ?? 0);
$ma_dich_vu = (int) ($_GET['service_id'] ?? 0);

$thong_bao = '';
$loi = '';
$don = null;
$dich_vu = null;

if ($ma_don > 0 && $ma_dich_vu > 0) {
    $stmt = $conn->prepare(
        "SELECT d.DDL_Ma, d.DDL_TrangThai, dv.DV_Ten
         FROM dondatlich d
         JOIN chitietdon c ON c.DDL_Ma = d.DDL_Ma
         JOIN dichvu dv ON dv.DV_Ma = c.DV_Ma
         WHERE d.DDL_Ma = ? AND d.ND_Ma = ? AND c.DV_Ma = ?
         LIMIT 1"
    );
    $stmt->execute([$ma_don, $ma_nguoi_dung, $ma_dich_vu]);
    $don = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$don) {
    $loi = 'Không tìm thấy dịch vụ trong đơn thuê của bạn.';
} elseif ($don['DDL_TrangThai'] !== TRANG_THAI_HOAN_THANH) {
    $loi = 'Bạn chỉ có thể đánh giá dịch vụ sau khi đơn thuê đã hoàn thành.';
} else {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM danhgia WHERE DDL_Ma = ? AND DV_Ma = ?");
    $stmt->execute([$ma_don, $ma_dich_vu]);
    if ((int) $stmt->fetchColumn() > 0) {
        $loi = 'Bạn đã đánh giá dịch vụ này trong đơn này rồi.';
    }
}

$so_sao_chon = 5;
$noi_dung = '';

if ($loi === '' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $so_sao_chon = (int) ($_POST['so_sao'] ?? 0);
    $noi_dung = trim($_POST['noi_dung'] ?? '');

    if ($so_sao_chon < 1 || $so_sao_chon > 5) {
        $loi = 'Vui lòng chọn số sao từ 1 đến 5.';
    } elseif (mb_strlen($noi_dung) > 1000) {
        $loi = 'Nhận xét không được dài quá 1000 ký tự.';
    } else {
        try {
            $stmt = $conn->prepare(
                "INSERT INTO danhgia (ND_Ma, DDL_Ma, DV_Ma, DG_SoSao, DG_NoiDung, DG_NgayTao) VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$ma_nguoi_dung, $ma_don, $ma_dich_vu, $so_sao_chon, $noi_dung !== '' ? $noi_dung : null, date('Y-m-d H:i:s')]);
            $thong_bao = 'Cảm ơn bạn đã đánh giá dịch vụ!';
        } catch (PDOException $e) {
            $loi = 'Bạn đã đánh giá dịch vụ này trong đơn này rồi.';
        }
    }
}

$hien_form = $thong_bao === '' && $don && $don['DDL_TrangThai'] === TRANG_THAI_HOAN_THANH
    && ($loi === '' || $_SERVER['REQUEST_METHOD'] === 'POST' && !str_contains($loi, 'đã đánh giá'));

$tieu_de_trang = 'Đánh giá dịch vụ - VIBE Studio';
$css_rieng = ['kb.css'];
require_once '../includes/header.php';
?>

<section class="page-head">
    <p class="page-label">REVIEW</p>
    <h1>ĐÁNH GIÁ <i>DỊCH VỤ.</i></h1>
</section>

<section class="shop-area" style="max-width: 600px; margin: 0 auto; padding: 20px;">

    <?php if ($thong_bao !== '') : ?>
        <div style="background:#d4edda; color:#155724; padding:15px; text-align:center;" role="status">
            <p style="margin-bottom:10px;"><?= htmlspecialchars($thong_bao) ?></p>
            <a href="order-detail.php?id=<?= $ma_don ?>" class="btn-dark">QUAY LẠI CHI TIẾT ĐƠN</a>
        </div>

    <?php elseif ($loi !== '' && !$hien_form) : ?>
        <div style="background:#f8d7da; color:#721c24; padding:15px; text-align:center;" role="alert">
            <p style="margin-bottom:10px;"><?= htmlspecialchars($loi) ?></p>
            <a href="my-orders.php" class="btn-dark">ĐƠN CỦA TÔI</a>
        </div>

    <?php else : ?>
        <?php if ($loi !== '') : ?>
            <div style="background:#f8d7da; color:#721c24; padding:12px; margin-bottom:16px;" role="alert"><?= htmlspecialchars($loi) ?></div>
        <?php endif; ?>
        <div class="card" style="background:#fff; padding:30px;">
            <h2 style="margin-bottom:20px;"><?= htmlspecialchars($don['DV_Ten']) ?></h2>
            <form method="POST">
                <label style="display:block; margin-bottom:20px;">
                    <span style="display:block; margin-bottom:8px; font-weight:600;">Chất lượng dịch vụ thế nào?</span>
                    <select name="so_sao" style="padding:10px; width:100%; border:1px solid #ddd;">
                        <?php foreach ([5 => 'Tuyệt vời', 4 => 'Hài lòng', 3 => 'Bình thường', 2 => 'Tạm được', 1 => 'Không tốt'] as $sao => $nhan) : ?>
                            <option value="<?= $sao ?>" <?= $so_sao_chon === $sao ? 'selected' : '' ?>><?= str_repeat('★', $sao) ?> - <?= $nhan ?> (<?= $sao ?> sao)</option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label style="display:block; margin-bottom:20px;">
                    <span style="display:block; margin-bottom:8px; font-weight:600;">Nhận xét của bạn</span>
                    <textarea name="noi_dung" rows="5" maxlength="1000" placeholder="Chia sẻ trải nghiệm của bạn..." style="width:100%; padding:10px; border:1px solid #ddd; resize:vertical;"><?= htmlspecialchars($noi_dung) ?></textarea>
                </label>
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <a href="order-detail.php?id=<?= $ma_don ?>" style="color:#666;">Hủy</a>
                    <button type="submit" class="btn-dark" style="cursor:pointer;">GỬI ĐÁNH GIÁ</button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</section>

<?php require_once '../includes/footer.php'; ?>