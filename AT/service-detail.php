<?php
require_once '../config/app.php';
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/dich_vu.php';

// Hàm chuẩn hóa loại sản phẩm đồng bộ với trang danh sách
function chuan_hoa_loai($loai_goc) {
    $loai_goc = strtolower(trim((string)$loai_goc));
    
    if (in_array($loai_goc, ['studio', 'cho_chup', 'diadiem', 'dia_diem', 'khong_gian'], true)) {
        return 'studio';
    }
    if (in_array($loai_goc, ['trang_phuc', 'do_bo', 'quan_ao', 'trangphuc', 'dobo'], true)) {
        return 'trang_phuc';
    }
    if (in_array($loai_goc, ['may_anh', 'mayanh', 'camera'], true)) {
        return 'may_anh';
    }

    return $loai_goc;
}

$ma_dich_vu = (int) ($_GET['id'] ?? 0);

$stmt = $conn->prepare(
    "SELECT id, ten, loai, gia, don_vi, so_luong, mo_ta, mo_ta_chi_tiet, anh
     FROM services
     WHERE id = ? AND trang_thai = 'hien'"
);
$stmt->execute([$ma_dich_vu]);
$dv = $stmt->fetch(PDO::FETCH_ASSOC);

$thong_bao_dat = $_SESSION['thong_bao_dat'] ?? '';
unset($_SESSION['thong_bao_dat']);

$ds_danh_gia = [];
$diem_trung_binh = 0;

if ($dv) {
    $stmt = $conn->prepare(
        "SELECT r.so_sao, r.binh_luan, r.ngay_tao, u.name
         FROM reviews r
         JOIN users u ON u.id = r.user_id
         WHERE r.service_id = ?
         ORDER BY r.id DESC"
    );
    $stmt->execute([$dv['id']]);
    $ds_danh_gia = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($ds_danh_gia) {
        $diem_trung_binh = array_sum(array_column($ds_danh_gia, 'so_sao')) / count($ds_danh_gia);
    }

    // Chuẩn hóa loại dịch vụ để lấy thông tin tên danh mục chuẩn
    $loai_chuan = chuan_hoa_loai($dv['loai']);
    $tt_loai = function_exists('thong_tin_loai') ? thong_tin_loai($loai_chuan) : [];
    $ten_danh_muc = $tt_loai['ten'] ?? ucfirst(str_replace('_', ' ', $loai_chuan));

    $tt_don_vi = function_exists('thong_tin_don_vi') ? thong_tin_don_vi($dv['don_vi']) : ['hinh_thuc' => 'Cho thuê'];
    $theo_gio = $dv['don_vi'] === 'gio';
    $kieu_nhap = $theo_gio ? 'datetime-local' : 'date';
    $nho_nhat = date($theo_gio ? 'Y-m-d\TH:i' : 'Y-m-d');
}

$tieu_de_trang = ($dv ? $dv['ten'] : 'Không tìm thấy') . ' - VIBE STUDIO';
$trang_hien_tai = 'san-pham';
$css_rieng = [
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css',
    'at.css'
];
$js_rieng = $dv ? ['at-chitiet.js'] : [];

require_once '../includes/header.php';
?>

<main class="product-detail-page">

<?php if (!$dv): ?>

    <section class="product-detail">
        <div class="detail-info">
            <h1 class="detail-name">Không tìm thấy sản phẩm</h1>
            <p class="detail-description">Sản phẩm bạn đang tìm kiếm không tồn tại hoặc đã bị ẩn.</p>
            <a href="services.php" class="back-button">
                <i class="fa-solid fa-arrow-left"></i>
                Quay lại sản phẩm
            </a>
        </div>
    </section>

<?php else: ?>

    <!-- Breadcrumb điều hướng -->
    <section class="detail-breadcrumb">
        <a href="services.php">Sản phẩm cho thuê</a>
        <i class="fa-solid fa-angle-right"></i>
        <a href="services.php?loai=<?= h($loai_chuan) ?>"><?= h($ten_danh_muc) ?></a>
        <i class="fa-solid fa-angle-right"></i>
        <span><?= h($dv['ten']) ?></span>
    </section>

    <!-- Khung thông tin chính sản phẩm -->
    <section class="product-detail">

        <!-- Ảnh chính sản phẩm bo góc -->
        <div class="detail-image">
            <?php if (!empty($dv['anh'])): ?>
                <img src="<?= BASE_URL ?>assets/uploads/<?= h($dv['anh']) ?>" alt="<?= h($dv['ten']) ?>">
            <?php else: ?>
                <img src="<?= BASE_URL ?>assets/images/no-image.jpg" alt="<?= h($dv['ten']) ?>">
            <?php endif; ?>
        </div>

        <!-- Chi tiết thông tin và Đặt thuê -->
        <div class="detail-info">

            <!-- Thanh nhãn phân loại đồng bộ với trang danh sách -->
            <div class="product-tags">
                <span class="product-category-tag"><?= h($ten_danh_muc) ?></span>
                <span class="product-status-tag">Có sẵn</span>
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
            </div>

            <form method="post" action="<?= BASE_URL ?>HH/cart.php">
                <input type="hidden" name="service_id" value="<?= (int) $dv['id'] ?>">
                <input type="hidden" name="so_luong" id="so-luong-gui" value="1" data-toi-da="<?= (int) $dv['so_luong'] ?>">

                <div class="chon-thoi-gian">
                    <div>
                        <label for="bat-dau"><?= $theo_gio ? 'GIỜ NHẬN' : 'NGÀY NHẬN' ?></label>
                        <input type="<?= $kieu_nhap ?>" id="bat-dau" name="bat_dau" min="<?= $nho_nhat ?>" required>
                    </div>
                    <div>
                        <label for="ket-thuc"><?= $theo_gio ? 'GIỜ TRẢ' : 'NGÀY TRẢ' ?></label>
                        <input type="<?= $kieu_nhap ?>" id="ket-thuc" name="ket_thuc" min="<?= $nho_nhat ?>" required>
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
                    <button type="submit" class="main-button rental-button">
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

    <!-- Khung mô tả chi tiết -->
    <section class="detail-description-section">
        <p class="small-title">INFORMATION</p>
        <h2>Thông tin <i>sản phẩm</i></h2>
        <div class="detail-description-content">
            <p><?= nl2br(h($dv['mo_ta_chi_tiet'])) ?></p>
        </div>
    </section>

    <!-- Khung đánh giá sản phẩm -->
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
