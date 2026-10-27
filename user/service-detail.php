<?php
require_once '../config/app.php';
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../includes/dich_vu.php';

$ten_danh_muc_theo_loai = [
    'thiet_bi'   => 'Máy ảnh - thiết bị',
    'phong_chup' => 'Chỗ chụp ảnh',
    'trang_phuc' => 'Bộ đồ',
    'phu_kien'   => 'Phụ kiện - đạo cụ',
];

$ma_dich_vu = (int) ($_GET['id'] ?? 0);

$stmt = $conn->prepare(
    "SELECT dv.DV_Ma AS id, dv.DV_Ten AS ten, dv.DV_Loai AS loai, dv.DV_GiaThue AS gia,
            dv.DV_DonViTinh AS don_vi, dv.DV_SoLuong AS so_luong, dv.DV_MoTa AS mo_ta,
            dv.DV_MoTaChiTiet AS mo_ta_chi_tiet, dv.DV_HinhAnh AS anh,
            pc.PC_SucChua AS suc_chua, tb.TB_Loai AS loai_thiet_bi,
            tp.TP_KichCo AS kich_co, pk.PK_Loai AS loai_phu_kien
     FROM dichvu dv
     LEFT JOIN phongchup pc ON pc.DV_Ma = dv.DV_Ma
     LEFT JOIN thietbi tb ON tb.DV_Ma = dv.DV_Ma
     LEFT JOIN trangphuc tp ON tp.DV_Ma = dv.DV_Ma
     LEFT JOIN phukien pk ON pk.DV_Ma = dv.DV_Ma
     WHERE dv.DV_Ma = ? AND dv.DV_DangBan = 1"
);
$stmt->execute([$ma_dich_vu]);
$dv = $stmt->fetch(PDO::FETCH_ASSOC);

$thong_bao_dat = $_SESSION['thong_bao_dat'] ?? '';
unset($_SESSION['thong_bao_dat']);

$ds_danh_gia = [];
$diem_trung_binh = 0;

if ($dv) {
    try {
        $stmt = $conn->prepare(
            "SELECT dg.DG_SoSao AS so_sao, dg.DG_NoiDung AS binh_luan, dg.DG_NgayTao AS ngay_tao, nd.ND_Ten AS name
             FROM danhgia dg
             JOIN nguoidung nd ON nd.ND_Ma = dg.ND_Ma
             WHERE dg.DV_Ma = ?
             ORDER BY dg.DG_Ma DESC"
        );
        $stmt->execute([$dv['id']]);
        $ds_danh_gia = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $ds_danh_gia = [];
    }

    if ($ds_danh_gia) {
        $diem_trung_binh = array_sum(array_column($ds_danh_gia, 'so_sao')) / count($ds_danh_gia);
    }

    $loai_chuan = $dv['loai'];
    $ten_danh_muc = $ten_danh_muc_theo_loai[$loai_chuan] ?? $loai_chuan;

    $tt_don_vi = function_exists('thong_tin_don_vi') ? thong_tin_don_vi($dv['don_vi']) : ['hinh_thuc' => 'Cho thuê'];
    $theo_gio = $dv['don_vi'] === 'gio';
    $kieu_nhap = $theo_gio ? 'datetime-local' : 'date';
    $nho_nhat = date($theo_gio ? 'Y-m-d\TH:i' : 'Y-m-d');

    $stmt_kho = $conn->prepare(
        "SELECT (dv.DV_SoLuong - COALESCE((
            SELECT SUM(c.CTD_SoLuong) FROM chitietdon c 
            JOIN dondatlich d ON d.DDL_Ma = c.DDL_Ma 
            WHERE c.DV_Ma = dv.DV_Ma AND d.DDL_TrangThai IN ('Chờ duyệt', 'Đã duyệt') AND c.CTD_KetThuc > NOW()
        ), 0)) AS con_lai FROM dichvu dv WHERE dv.DV_Ma = ?"
    );
    $stmt_kho->execute([$dv['id']]);
    $con_lai_ban_dau = (int) $stmt_kho->fetchColumn();

    $thuoc_tinh_rieng = [];
    if ($dv['suc_chua'] !== null) {
        $thuoc_tinh_rieng['Sức chứa'] = $dv['suc_chua'] . ' người';
    }
    if ($dv['loai_thiet_bi']) {
        $thuoc_tinh_rieng['Loại thiết bị'] = $dv['loai_thiet_bi'];
    }
    if ($dv['kich_co']) {
        $thuoc_tinh_rieng['Kích cỡ'] = $dv['kich_co'];
    }
    if ($dv['loai_phu_kien']) {
        $thuoc_tinh_rieng['Loại phụ kiện'] = $dv['loai_phu_kien'];
    }
}

$tieu_de_trang = ($dv ? $dv['ten'] : 'Không tìm thấy') . ' - VIBE STUDIO';
$trang_hien_tai = 'san-pham';
$css_rieng = [
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css',
    BASE_URL . 'assets/css/at.css' // Trỏ chuẩn vào thư mục assets/css chung
];
$js_rieng = $dv ? [
       BASE_URL . 'assets/js/at-chitiet.js'] : []; // Trỏ chuẩn vào thư mục assets/js chung

require_once '../includes/header.php';
?>

<main class="product-detail-page">

<?php if (!$dv): ?>

    <section class="product-detail">
        <div class="detail-info-card">
            <h1 class="detail-name">Không tìm thấy sản phẩm</h1>
            <p class="detail-description">Sản phẩm bạn đang tìm kiếm không tồn tại hoặc đã bị ẩn.</p>
            <a href="services.php" class="back-button">
                <i class="fa-solid fa-arrow-left"></i>
                Quay lại sản phẩm
            </a>
        </div>
    </section>

<?php else: ?>

    <section class="detail-breadcrumb">
        <a href="services.php">Sản phẩm cho thuê</a>
        <i class="fa-solid fa-angle-right"></i>
        <a href="services.php?loai=<?= h($loai_chuan) ?>"><?= h($ten_danh_muc) ?></a>
        <i class="fa-solid fa-angle-right"></i>
        <span><?= h($dv['ten']) ?></span>
    </section>

    <section class="product-detail">
        <!-- Cột trái: Khung chứa ảnh có viền -->
        <div class="detail-image-box">
            <div class="detail-image">
                <?php if (!empty($dv['anh'])): ?>
                    <img src="<?= BASE_URL ?>assets/uploads/<?= h($dv['anh']) ?>" alt="<?= h($dv['ten']) ?>">
                <?php else: ?>
                    <img src="<?= BASE_URL ?>assets/images/no-image.jpg" alt="<?= h($dv['ten']) ?>">
                <?php endif; ?>
            </div>
        </div>

        <!-- Cột phải: Khung thông tin chính có viền bao bọc rõ ràng -->
        <div class="detail-info-card">
            <div class="product-tags">
                <span class="product-category-tag"><?= h($ten_danh_muc) ?></span>
                <span class="product-status-tag" id="nhan-trang-thai" style="<?= $con_lai_ban_dau == 0 ? 'background: #ff4d4f; color: #fff;' : '' ?>">
                    <?= $con_lai_ban_dau > 0 ? 'Còn ' . $con_lai_ban_dau . ' sản phẩm' : 'Hết hàng' ?>
                </span>
            </div>

            <h1 class="detail-name"><?= h($dv['ten']) ?></h1>
            
            <div class="product-vendor">
                <i class="fa-solid fa-camera"></i>
                <span>VIBE STUDIO</span>
                <i class="fa-solid fa-circle-check check-icon"></i>
            </div>

            <p class="detail-price"><?= h(dinh_dang_gia((int) $dv['gia'], $dv['don_vi'])) ?></p>
            <p class="detail-description"><?= h($dv['mo_ta']) ?></p>

            <div class="detail-information">
                <div class="information-item">
                    <span>Loại danh mục</span>
                    <strong><?= h($ten_danh_muc) ?></strong>
                </div>
                <div class="information-item">
                    <span>Địa điểm</span>
                    <strong>TP.HCM</strong>
                </div>
                <div class="information-item">
                    <span>Hình thức</span>
                    <strong><?= h($tt_don_vi['hinh_thuc'] ?? 'Cho thuê') ?></strong>
                </div>
                <?php foreach ($thuoc_tinh_rieng as $nhan => $gia_tri): ?>
                    <div class="information-item">
                        <span><?= h($nhan) ?></span>
                        <strong><?= h($gia_tri) ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Form gửi dữ liệu đặt thuê trỏ thẳng vào cart.php ở root -->
            <form method="post" action="cart.php">
                <input type="hidden" name="service_id" value="<?= (int) $dv['id'] ?>">
                <input type="hidden" name="so_luong" id="so-luong-gui" value="1" data-toi-da="<?= (int) $con_lai_ban_dau ?>">

                <!-- Phần chọn Size nếu sản phẩm là trang phục -->
                <?php if (!empty($dv['kich_co'])): ?>
                    <?php $danh_sach_size = array_map('trim', explode(',', $dv['kich_co'])); ?>
                    <div class="field" style="margin-bottom: 16px;">
                        <label for="kich_co_chon" style="display: block; font-weight: 600; margin-bottom: 6px; font-size: 12px; color: #333;">CHỌN KÍCH CỠ (SIZE)</label>
                        <select id="kich_co_chon" name="kich_co" class="form-control" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #dcdcdc; background: #fff;">
                            <option value="">-- Chọn kích cỡ --</option>
                            <?php foreach ($danh_sach_size as $size): ?>
                                <option value="<?= h($size) ?>"><?= h($size) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="chon-thoi-gian">
                    <div>
                        <label for="bat-dau"><?= $theo_gio ? 'GIỜ NHẬN' : 'NGÀY NHẬN' ?></label>
                        <input type="<?= $kieu_nhap ?>" id="bat-dau" name="bat-dau" min="<?= $nho_nhat ?>" required>
                    </div>
                    <div>
                        <label for="ket-thuc"><?= $theo_gio ? 'GIỜ TRẢ' : 'NGÀY TRẢ' ?></label>
                        <input type="<?= $kieu_nhap ?>" id="ket-thuc" name="ket-thuc" min="<?= $nho_nhat ?>" required>
                    </div>
                </div>

                <div class="quantity-box">
                    <p>Số lượng</p>
                    <div class="quantity-control">
                        <button type="button" id="quantity-minus">-</button>
                        <span id="quantity">1</span>
                        <button type="button" id="quantity-plus">+</button>
                    </div>
                </div>

                <div class="detail-buttons">
                    <button type="submit" class="main-button rental-button" <?= $con_lai_ban_dau == 0 ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : '' ?>>
                        <i class="fa-solid fa-bag-shopping"></i> Đặt thuê
                    </button>
                    <a href="services.php" class="back-button">
                        <i class="fa-solid fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </form>

            <?php if ($thong_bao_dat !== ''): ?>
                <p class="rental-message"><?= h($thong_bao_dat) ?></p>
            <?php endif; ?>
        </div>
    </section>

    <section class="detail-description-section">
        <p class="small-title">INFORMATION</p>
        <h2>Thông tin <i>sản phẩm</i></h2>
        <div class="detail-description-content">
            <p><?= nl2br(h($dv['mo_ta_chi_tiet'] ?: $dv['mo_ta'])) ?></p>
        </div>
    </section>

    <section class="danh-gia-khung">
        <p class="small-title">REVIEWS</p>
        <h2>Đánh giá <i>của khách</i></h2>

        <?php if (empty($ds_danh_gia)): ?>
            <p class="danh-gia-trong">Chưa có đánh giá nào cho sản phẩm này.</p>
        <?php else: ?>
            <p class="danh-gia-tong">
                <strong><?= number_format($diem_trung_binh, 1) ?></strong> / 5
                (<?= count($ds_danh_gia) ?> đánh giá)
            </p>

            <?php foreach ($ds_danh_gia as $dg): ?>
                <div class="danh-gia-muc">
                    <p class="danh-gia-sao">
                        <?= str_repeat('★', (int) $dg['so_sao']) . str_repeat('☆', 5 - (int) $dg['so_sao']) ?>
                    </p>
                    <?php if (!empty($dg['binh_luan'])): ?>
                        <p class="danh-gia-noi-dung"><?= h($dg['binh_luan']) ?></p>
                    <?php endif; ?>
                    <p class="danh-gia-ten">
                        <?= h($dg['name']) ?> · <?= h(date('d/m/Y', strtotime($dg['ngay_tao']))) ?>
                    </p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

<?php endif; ?>

</main>

<?php require_once '../includes/footer.php'; ?>