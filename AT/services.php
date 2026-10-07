<?php
require_once '../config/app.php';
require_once '../config/db.php';
require_once '../includes/dich_vu.php';

// Danh sách 3 danh mục chuẩn
$danh_muc_chinh = [
    'may_anh'   => 'Máy ảnh',
    'studio'    => 'Chỗ chụp ảnh',
    'trang_phuc' => 'Bộ đồ'
];

// Hàm chuyển đổi tất cả kiểu đặt tên trong CSDL về 3 mã chuẩn
function chuan_hoa_loai($loai_goc) {
    $loai = strtolower(trim((string)$loai_goc));

    // Nhóm Chỗ chụp / Studio
    if (in_array($loai, ['studio', 'cho_chup', 'cho_chup_anh', 'diadiem', 'dia_diem', 'khong_gian', 'phong_chup', 'location'], true)) {
        return 'studio';
    }
    // Nhóm Bộ đồ / Trang phục
    if (in_array($loai, ['trang_phuc', 'trangphuc', 'do_bo', 'dobo', 'quan_ao', 'quanao', 'co_truong', 'vay', 'dam', 'costume'], true)) {
        return 'trang_phuc';
    }
    // Nhóm Máy ảnh
    if (in_array($loai, ['may_anh', 'mayanh', 'camera', 'lens', 'ong_kinh'], true)) {
        return 'may_anh';
    }

    return 'may_anh'; // Mặc định nếu không khớp
}

// Truy vấn dữ liệu từ MySQL
$ds_dich_vu = $conn->query(
    "SELECT id, ten, loai, gia, don_vi, mo_ta, anh
     FROM services
     WHERE trang_thai = 'hien'
     ORDER BY id DESC"
)->fetchAll(PDO::FETCH_ASSOC);

// Phân loại dữ liệu vào mảng
$danh_sach_theo_loai = [
    'may_anh'   => [],
    'studio'    => [],
    'trang_phuc' => []
];

foreach ($ds_dich_vu as $dv) {
    $loai_chuan = chuan_hoa_loai($dv['loai']);
    $dv['loai_chuan'] = $loai_chuan;
    $danh_sach_theo_loai[$loai_chuan][] = $dv;
}

$loai_chon = $_GET['loai'] ?? 'all';

$tieu_de_trang = 'Sản phẩm cho thuê - VIBE STUDIO';
$trang_hien_tai = 'san-pham';

$css_rieng = [
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css',
    'at.css'
];

// Thêm time() vào tên file JS để trình duyệt không dùng cache cũ
$js_rieng = ['at.js?v=' . time()];

require_once '../includes/header.php';
?>

<main class="product-page">

    <section class="product-intro">
        <div class="product-intro-content">
            <p class="small-title">STUDIO RENTAL</p>
            <h1>
                Thiết bị & không gian
                <i>cho thuê</i>
            </h1>
            <p class="product-intro-text">
                Khám phá các thiết bị chụp ảnh, không gian Studio (chỗ chụp)
                và trang phục (bộ đồ) được chuẩn bị sẵn cho những buổi chụp ảnh của bạn.
            </p>
        </div>
    </section>

    <section class="product-list-section">

        <!-- NÚT LỌC VÀ TÌM KIẾM -->
        <div class="product-tools">
            <div class="product-category">
                <button type="button" class="category-button <?= $loai_chon === 'all' ? 'active' : '' ?>" data-category="all">
                    Tất cả
                </button>

                <?php foreach ($danh_muc_chinh as $ma_loai => $ten_loai): ?>
                    <button type="button" class="category-button <?= $loai_chon === $ma_loai ? 'active' : '' ?>" data-category="<?= h($ma_loai) ?>">
                        <?= h($ten_loai) ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="product-search">
                <input type="text" id="search-product" placeholder="Tìm máy ảnh, chỗ chụp, đồ bộ...">
                <button type="button" id="search-button" aria-label="Tìm kiếm">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </div>
        </div>

        <!-- DANH SÁCH KHUNG SẢN PHẨM -->
        <div class="product-sections-wrapper" id="product-list">

            <?php foreach ($danh_muc_chinh as $ma_loai => $ten_loai): ?>
                <?php 
                $items = $danh_sach_theo_loai[$ma_loai] ?? [];
                ?>

                <!-- KHUNG LỚN CỦA TỪNG DANH MỤC -->
                <section class="category-section-box" data-category="<?= h($ma_loai) ?>">

                    <div class="category-box-header">
                        <div class="category-box-title">
                            <h2><?= h($ten_loai) ?></h2>
                            <span class="category-count">(<?= count($items) ?> sản phẩm)</span>
                        </div>
                    </div>

                    <div class="product-grid">
                        <?php foreach ($items as $dv): ?>
                            <?php $link = BASE_URL . 'AT/service-detail.php?id=' . (int) $dv['id']; ?>

                            <!-- CARD SẢN PHẨM -->
                            <article class="product-card" data-category="<?= h($dv['loai_chuan']) ?>">

                                <div class="product-image">
                                    <?php if (!empty($dv['anh'])): ?>
                                        <img src="<?= BASE_URL ?>assets/uploads/<?= h($dv['anh']) ?>" alt="<?= h($dv['ten']) ?>" loading="lazy">
                                    <?php else: ?>
                                        <img src="<?= BASE_URL ?>assets/images/no-image.jpg" alt="<?= h($dv['ten']) ?>" loading="lazy">
                                    <?php endif; ?>

                                    <div class="product-overlay">
                                        <a href="<?= $link ?>" class="main-button">Xem chi tiết</a>
                                    </div>
                                </div>

                                <div class="product-info">
                                    <div class="product-tags">
                                        <span class="product-status-tag">Có sẵn</span>
                                    </div>

                                    <h3 class="product-name"><?= h($dv['ten']) ?></h3>
                                    <p class="product-description" style="display: none;"><?= h($dv['mo_ta']) ?></p>

                                    <div class="product-meta">
                                        <span>Chưa có đánh giá</span>
                                        <span>TP.HCM</span>
                                    </div>

                                    <div class="product-vendor">
                                        <i class="fa-solid fa-camera"></i>
                                        <span>VIBE STUDIO</span>
                                        <i class="fa-solid fa-circle-check check-icon"></i>
                                    </div>

                                    <div class="product-price-box">
                                        <span class="product-price"><?= h(dinh_dang_gia((int) $dv['gia'], $dv['don_vi'])) ?></span>
                                    </div>

                                    <div class="product-actions">
                                        <a href="<?= $link ?>" class="btn-action btn-rent">Thuê ngay</a>
                                        <a href="<?= $link ?>" class="btn-action btn-chat">
                                            <i class="fa-regular fa-comment"></i> Chi tiết
                                        </a>
                                    </div>
                                </div>

                            </article>
                        <?php endforeach; ?>
                    </div>

                </section>
            <?php endforeach; ?>

        </div>

        <div class="no-product" id="no-product" style="display: none; text-align: center; padding: 40px 0; color: #666;" hidden>
            Không tìm thấy sản phẩm phù hợp trong danh mục này.
        </div>

    </section>
</main>

<?php require_once '../includes/footer.php'; ?>
