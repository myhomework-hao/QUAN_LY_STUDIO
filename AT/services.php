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

    if (in_array($loai, ['studio', 'cho_chup', 'cho_chup_anh', 'diadiem', 'dia_diem', 'khong_gian', 'phong_chup', 'location'], true)) {
        return 'studio';
    }
    if (in_array($loai, ['trang_phuc', 'trangphuc', 'do_bo', 'dobo', 'quan_ao', 'quanao', 'co_truong', 'vay', 'dam', 'costume'], true)) {
        return 'trang_phuc';
    }
    if (in_array($loai, ['may_anh', 'mayanh', 'camera', 'lens', 'ong_kinh'], true)) {
        return 'may_anh';
    }

    return 'may_anh';
}

// Truy vấn trực tiếp chính xác theo cấu trúc bảng dich_vu của bạn
$sql = "SELECT DV_Ma as id, DV_Ten as ten, DV_Loai as loai, DV_GiaThue as gia, DV_DonViTinh as don_vi, DV_MoTa as mo_ta, DV_HinhAnh as anh 
        FROM dich_vu 
        WHERE DV_DangBan = 'hien' OR DV_DangBan = '1' 
        ORDER BY DV_Ma DESC";

$ds_dich_vu = [];
try {
    $ds_dich_vu = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $ds_dich_vu = [];
}

$danh_sach_theo_loai = [
    'may_anh'   => [],
    'studio'    => [],
    'trang_phuc' => []
];

foreach ($ds_dich_vu as $dv) {
    $loai_chuan = chuan_hoa_loai($dv['loai'] ?? '');
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

        <div class="product-sections-wrapper" id="product-list">
            <?php foreach ($danh_muc_chinh as $ma_loai => $ten_loai): ?>
                <?php $items = $danh_sach_theo_loai[$ma_loai] ?? []; ?>

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
                                        <span class="product-price"><?= h(dinh_dang_gia((int) ($dv['gia'] ?? 0), $dv['don_vi'] ?? '')) ?></span>
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