<?php
require_once '../config/app.php';
require_once '../config/db.php';
require_once '../includes/dich_vu.php';

$danh_muc_chinh = [
    'thiet_bi'   => 'Máy ảnh - thiết bị',
    'phong_chup' => 'Chỗ chụp ảnh',
    'trang_phuc' => 'Bộ đồ',
    'phu_kien'   => 'Phụ kiện - đạo cụ',
];

$sql = "SELECT dv.DV_Ma AS id, dv.DV_Ten AS ten, dv.DV_Loai AS loai, dv.DV_GiaThue AS gia,
               dv.DV_DonViTinh AS don_vi, dv.DV_MoTa AS mo_ta, dv.DV_HinhAnh AS anh,
               (SELECT AVG(dg.DG_SoSao) FROM danhgia dg WHERE dg.DV_Ma = dv.DV_Ma) AS diem_tb,
               (SELECT COUNT(*) FROM danhgia dg WHERE dg.DV_Ma = dv.DV_Ma) AS so_danh_gia
        FROM dichvu dv
        WHERE dv.DV_DangBan = 1
        ORDER BY dv.DV_Ma DESC";

$ds_dich_vu = [];
try {
    $ds_dich_vu = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $ds_dich_vu = [];
}

$danh_sach_theo_loai = array_fill_keys(array_keys($danh_muc_chinh), []);

foreach ($ds_dich_vu as $dv) {
    if (isset($danh_sach_theo_loai[$dv['loai']])) {
        $danh_sach_theo_loai[$dv['loai']][] = $dv;
    }
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

                            <article class="product-card" data-category="<?= h($ma_loai) ?>">
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
                                        <?php if ((int) $dv['so_danh_gia'] > 0): ?>
                                            <span>★ <?= number_format((float) $dv['diem_tb'], 1) ?> (<?= (int) $dv['so_danh_gia'] ?>)</span>
                                        <?php else: ?>
                                            <span>Chưa có đánh giá</span>
                                        <?php endif; ?>
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