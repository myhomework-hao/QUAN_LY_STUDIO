<?php
require_once '../config/app.php';
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../includes/dich_vu.php';
require_once '../includes/security.php';

$ten_danh_muc_theo_loai = [
    'thiet_bi'   => 'Máy ảnh - thiết bị',
    'phong_chup' => 'Chỗ chụp ảnh',
    'trang_phuc' => 'Trang phục',
    'phu_kien'   => 'Phụ kiện - đạo cụ',
];

$ma_dich_vu = (int) ($_GET['id'] ?? 0);

$stmt = $conn->prepare(
    "SELECT dv.DV_Ma AS id, dv.DV_Ten AS ten, dv.DV_Loai AS loai, dv.DV_GiaThue AS gia,
            dv.DV_DonViTinh AS don_vi, dv.DV_SoLuong AS so_luong, dv.DV_MoTa AS mo_ta,
            dv.DV_MoTaChiTiet AS mo_ta_chi_tiet, dv.DV_HinhAnh AS anh,
            dv.DV_DangBan AS dang_ban,
            pc.PC_SucChua AS suc_chua, tb.TB_Loai AS loai_thiet_bi,
            tp.TP_KichCo AS kich_co, pk.PK_Loai AS loai_phu_kien
     FROM dichvu dv
     LEFT JOIN phongchup pc ON pc.DV_Ma = dv.DV_Ma
     LEFT JOIN thietbi tb ON tb.DV_Ma = dv.DV_Ma
     LEFT JOIN trangphuc tp ON tp.DV_Ma = dv.DV_Ma
     LEFT JOIN phukien pk ON pk.DV_Ma = dv.DV_Ma
     WHERE dv.DV_Ma = ? AND (
         dv.DV_DangBan = 1 OR (
             dv.DV_Loai = 'phong_chup' AND EXISTS (
                 SELECT 1 FROM baocao_huhong b
                 WHERE b.DV_Ma = dv.DV_Ma AND b.BCHH_TrangThai <> 'Đã xử lý'
             )
         )
     )"
);
$stmt->execute([$ma_dich_vu]);
$dv = $stmt->fetch(PDO::FETCH_ASSOC);

$thong_bao_dat = $_SESSION['thong_bao_dat'] ?? '';
unset($_SESSION['thong_bao_dat']);

$ds_danh_gia = [];
$ds_bao_cao_hu_hong = [];
$ds_bo_phan_phong = [];
$ds_bo_phan_dang_hu = [];
$ds_bo_phan_kha_dung = [];
$diem_trung_binh = 0;

if ($dv) {
    if ($dv['loai'] === 'phong_chup') {
        $stmt = $conn->prepare(
            "SELECT BCHH_BoPhan AS bo_phan, BCHH_MoTa AS mo_ta, BCHH_TrangThai AS trang_thai, BCHH_NgayBao AS ngay_bao
             FROM baocao_huhong
             WHERE DV_Ma = ? AND BCHH_TrangThai <> ?
             ORDER BY BCHH_NgayBao DESC, BCHH_Ma DESC"
        );
        $stmt->execute([$dv['id'], 'Đã xử lý']);
        $ds_bao_cao_hu_hong = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $ds_bo_phan_phong = preg_split('/\R/u', (string) $dv['mo_ta_chi_tiet'], -1, PREG_SPLIT_NO_EMPTY);
        $ds_bo_phan_phong = array_values(array_unique(array_filter(array_map('trim', $ds_bo_phan_phong))));
        foreach ($ds_bao_cao_hu_hong as $bao_cao) {
            $bo_phan_hu = trim((string) ($bao_cao['bo_phan'] ?? ''));
            if ($bo_phan_hu !== '') {
                $ds_bo_phan_dang_hu[] = $bo_phan_hu;
            }
        }
        $ds_bo_phan_dang_hu = array_values(array_unique($ds_bo_phan_dang_hu));
        $ds_bo_phan_kha_dung = array_values(array_diff($ds_bo_phan_phong, $ds_bo_phan_dang_hu));
    }

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
    $theo_gio = chuan_hoa_don_vi((string) $dv['don_vi']) === 'gio';
    $kieu_nhap = $theo_gio ? 'datetime-local' : 'date';
    $nho_nhat = date($theo_gio ? 'Y-m-d\TH:i' : 'Y-m-d');

    // Hiển thị tồn kho ban đầu theo sức chứa thực tế.
    // Tồn kho theo khung giờ sẽ do API kiểm tra khi khách chọn thời gian.
    $con_lai_ban_dau = (int) $dv['so_luong'];
    $co_the_dat = (int) $dv['dang_ban'] === 1 && $con_lai_ban_dau > 0;
    if ($dv['loai'] === 'phong_chup' && !$ds_bo_phan_kha_dung) {
        $co_the_dat = false;
    }

    // Mốc mặc định luôn ở tương lai để trang vừa mở đã tính được tạm tính.
    $khoa_don_vi = chuan_hoa_don_vi((string) $dv['don_vi']);
    $moc_bat_dau_mac_dinh = strtotime(date('Y-m-d H:00:00')) + 3600;
    $moc_ket_thuc_mac_dinh = $moc_bat_dau_mac_dinh + ($khoa_don_vi === 'ngay' ? 86400 : 3600);
    $ngay_nhan_mac_dinh = date('Y-m-d', $moc_bat_dau_mac_dinh);
    $gio_nhan_mac_dinh = date('H:i', $moc_bat_dau_mac_dinh);
    $ngay_tra_mac_dinh = date('Y-m-d', $moc_ket_thuc_mac_dinh);
    $gio_tra_mac_dinh = date('H:i', $moc_ket_thuc_mac_dinh);

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
    BASE_URL . 'assets/css/at.css?v=' . (is_file(__DIR__ . '/../assets/css/at.css') ? filemtime(__DIR__ . '/../assets/css/at.css') : time())
];
$js_rieng = $dv ? [
    BASE_URL . 'assets/js/at-chitiet.js?v=' . (is_file(__DIR__ . '/../assets/js/at-chitiet.js') ? filemtime(__DIR__ . '/../assets/js/at-chitiet.js') : time())
] : [];

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
                <span class="product-status-tag" id="nhan-trang-thai" style="<?= !$co_the_dat ? 'background: #ff4d4f; color: #fff;' : '' ?>">
                    <?= (int) $dv['dang_ban'] !== 1 ? 'Đang bảo trì' : ($con_lai_ban_dau <= 0 ? 'Hết hàng' : ($dv['loai'] === 'phong_chup' && !$co_the_dat ? 'Không có món khả dụng' : 'Còn ' . $con_lai_ban_dau . ' sản phẩm')) ?>
                </span>
            </div>

            <h1 class="detail-name"><?= h($dv['ten']) ?></h1>
            
            <div class="product-vendor">
                <i class="fa-solid fa-camera"></i>
                <span>VIBE STUDIO</span>
                <i class="fa-solid fa-circle-check check-icon"></i>
            </div>

            <p class="detail-price"><?= h(dinh_dang_gia((int) $dv['gia'], $dv['don_vi'])) ?></p>
            

            <div class="detail-information">
                <div class="information-item">
                    <span>Loại danh mục</span>
                    <strong><?= h($ten_danh_muc) ?></strong>
                </div>
                <div class="information-item">
                    <span>Địa điểm</span>
                    <strong>TP.Cần Thơ</strong>
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
            <form id="formDatThue" method="post" action="cart.php">
                <?= csrf_input() ?>
                <input type="hidden" name="service_id" value="<?= (int) $dv['id'] ?>">
                <input type="hidden" name="so_luong" id="so-luong-gui" value="1"
                       data-toi-da="<?= (int) $con_lai_ban_dau ?>"
                       data-don-vi="<?= h((string) $dv['don_vi']) ?>"
                       data-gia="<?= (int) $dv['gia'] ?>"
                       data-api="<?= BASE_URL ?>api/kiem_tra_kho.php">

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

<div class="chon-thoi-gian" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
    <div>
        <label for="ngay-nhan" style="display: block; font-weight: 600; margin-bottom: 6px; font-size: 12px;">NGÀY NHẬN</label>
        <input type="date" id="ngay-nhan" name="ngay_nhan" min="<?= date('Y-m-d') ?>" value="<?= $ngay_nhan_mac_dinh ?>" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #dcdcdc;">
    </div>
    <div>
        <label for="gio-nhan" style="display: block; font-weight: 600; margin-bottom: 6px; font-size: 12px;">GIỜ NHẬN</label>
        <input type="time" id="gio-nhan" name="gio_nhan" value="<?= $gio_nhan_mac_dinh ?>" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #dcdcdc;">
    </div>
</div>

<div class="chon-thoi-gian" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 16px;">
    <div>
        <label for="ngay-tra" style="display: block; font-weight: 600; margin-bottom: 6px; font-size: 12px;">NGÀY TRẢ</label>
        <input type="date" id="ngay-tra" name="ngay_tra" min="<?= date('Y-m-d') ?>" value="<?= $ngay_tra_mac_dinh ?>" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #dcdcdc;">
    </div>
    <div>
        <label for="gio-tra" style="display: block; font-weight: 600; margin-bottom: 6px; font-size: 12px;">GIỜ TRẢ</label>
        <input type="time" id="gio-tra" name="gio_tra" value="<?= $gio_tra_mac_dinh ?>" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #dcdcdc;">
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

                <div class="vibe-price-summary" aria-live="polite">
                    <div class="vibe-price-summary-row">
                        <span>Tạm tính</span>
                        <strong id="tong-tien-so">—</strong>
                    </div>
                    <small class="vibe-price-summary-note" id="tong-tien-chi-tiet">Chọn thời gian để xem giá</small>
                </div>

                <div class="detail-buttons">
                    <button type="submit" class="main-button rental-button" <?= !$co_the_dat ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : '' ?>>
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

    <?php if ($dv['loai'] === 'phong_chup' && $ds_bo_phan_dang_hu) : ?>
        <section class="detail-description-section">
            <p class="small-title">ROOM CONDITION</p>
            <h2>Tình trạng <i>phòng studio</i></h2>
            <p>Xin lỗi quý khách vì những món dưới đây hiện đang bị hư hỏng:</p>
            <ul>
                <?php foreach ($ds_bo_phan_dang_hu as $bo_phan) : ?>
                    <li><?= h($bo_phan) ?></li>
                <?php endforeach; ?>
            </ul>
            <p>Nếu có thắc mắc hoặc cần hỗ trợ, vui lòng liên hệ shop qua biểu tượng chat hỗ trợ.</p>
        </section>
    <?php endif; ?>

    <section class="detail-description-section">
        <p class="small-title">INFORMATION</p>
        <h2>Thông tin <i>sản phẩm</i></h2>
        <div class="detail-description-content">
            <p><?= nl2br(h($dv['loai'] === 'phong_chup' ? $dv['mo_ta'] : ($dv['mo_ta_chi_tiet'] ?: $dv['mo_ta']))) ?></p>
            <?php if ($dv['loai'] === 'phong_chup') : ?>
                <fieldset style="border: 0; padding: 0; margin: 16px 0 0;">
                    <legend style="font-weight: 600; margin-bottom: 8px; color: #333;">Chọn món trong phòng</legend>
                    <?php if (!$ds_bo_phan_phong) : ?>
                        <p role="status" style="color: #a66a00;">Phòng chưa có danh sách món. Vui lòng liên hệ studio để cập nhật thông tin.</p>
                    <?php else : ?>
                        <label style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px; font-weight: 600;">
                            <input type="checkbox" id="chon-tat-ca-bo-phan" <?= !$ds_bo_phan_kha_dung ? 'disabled' : '' ?>>
                            <span>Chọn tất cả món còn hoạt động</span>
                        </label>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 10px;">
                            <?php foreach ($ds_bo_phan_phong as $bo_phan) : $bi_hu = in_array($bo_phan, $ds_bo_phan_dang_hu, true); ?>
                                <label style="display: flex; align-items: flex-start; gap: 8px; padding: 12px; border: 1px solid <?= $bi_hu ? '#d0d0d0' : '#e5e5e5' ?>; border-radius: 8px; background: <?= $bi_hu ? '#eeeeee' : '#fff' ?>; color: <?= $bi_hu ? '#999' : '#333' ?>; cursor: <?= $bi_hu ? 'not-allowed' : 'pointer' ?>;">
                                    <input form="formDatThue" type="checkbox" name="bo_phan[]" value="<?= h($bo_phan) ?>" <?= $bi_hu ? 'disabled' : '' ?> style="margin-top: 2px;">
                                    <span><?= h($bo_phan) ?><?= $bi_hu ? ' (đang hư hỏng)' : '' ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </fieldset>
            <?php endif; ?>
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

<?php if ($dv) { $chat_product_id = (int) $dv['id']; } ?>
<?php require_once '../includes/footer.php'; ?>
