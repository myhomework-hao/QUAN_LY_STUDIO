<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/dich_vu.php';

$tieu_de_trang = 'VIBE Studio';
$trang_hien_tai = 'trang-chu';

// Gọi asset chung cho trang chủ
$css_rieng = ['assets/css/homepage.css?v=' . time()]; 
$js_rieng = ['assets/js/homepage.js?v=' . time()]; 

$ten_danh_muc_theo_loai = [
    'thiet_bi'   => 'Máy ảnh - thiết bị',
    'phong_chup' => 'Chỗ chụp ảnh',
    'trang_phuc' => 'Bộ đồ',
    'phu_kien'   => 'Phụ kiện - đạo cụ',
];

// SẢN PHẨM NỔI BẬT: bốc ngẫu nhiên 4 sản phẩm đang bán
$san_pham_noi_bat = [];
try {
    $san_pham_noi_bat = $conn->query(
        "SELECT dv.DV_Ma AS id, dv.DV_Ten AS ten, dv.DV_Loai AS loai, dv.DV_GiaThue AS gia,
                dv.DV_DonViTinh AS don_vi, dv.DV_HinhAnh AS anh
         FROM dichvu dv
         WHERE dv.DV_DangBan = 1
         ORDER BY RAND()
         LIMIT 4"
    )->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $san_pham_noi_bat = [];
}

require_once __DIR__ . '/includes/header.php';
?>

<main>

    <!-- HERO -->
    <section class="hero" id="home">
        <div class="hero-content">
            <p class="small-title">CREATIVE PHOTO STUDIO</p>
            <h1>
                CAPTURE<br>
                YOUR <i>VIBE.</i>
            </h1>
            <p class="hero-description">
                Chúng tôi biến những khoảnh khắc của bạn
                thành những hình ảnh đáng nhớ.
            </p>
            <a href="#products" class="main-button">XEM SẢN PHẨM</a>
        </div>

        <div class="hero-image">
            <img src="https://images.unsplash.com/photo-1554048612-b6a482bc67e5?auto=format&fit=crop&w=1200&q=80" alt="VIBE Studio">
        </div>
    </section>

    <!-- SẢN PHẨM NỔI BẬT -->
    <section class="featured" id="products">
        <div class="section-header">
            <div>
                <p class="section-title">BEST SELLERS</p>
                <h2>FEATURED <i>PRODUCTS</i></h2>
            </div>
            <p class="section-number">01</p>
        </div>

        <?php if (empty($san_pham_noi_bat)): ?>
            <p class="featured-empty">Sản phẩm đang được cập nhật.</p>
        <?php else: ?>
        <div class="featured-grid">
            <?php foreach ($san_pham_noi_bat as $sp): ?>
                <?php 
                    $link = BASE_URL . 'user/service-detail.php?id=' . (int) $sp['id']; 
                ?>
                <a href="<?= $link ?>" class="featured-card">
                    <div class="featured-image">
                        <?php if (!empty($sp['anh'])): ?>
                            <img src="<?= BASE_URL ?>assets/uploads/<?= h($sp['anh']) ?>" alt="<?= h($sp['ten']) ?>" loading="lazy">
                        <?php else: ?>
                            <img src="<?= BASE_URL ?>assets/images/no-image.jpg" alt="<?= h($sp['ten']) ?>" loading="lazy">
                        <?php endif; ?>
                    </div>
                    <p class="featured-tag"><?= h($ten_danh_muc_theo_loai[$sp['loai']] ?? $sp['loai']) ?></p>
                    <h3><?= h($sp['ten']) ?></h3>
                    <p class="featured-price"><?= h(dinh_dang_gia((int) $sp['gia'], $sp['don_vi'])) ?></p>
                </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <a href="<?= BASE_URL ?>user/services.php" class="text-button featured-more">XEM TẤT CẢ SẢN PHẨM →</a>
    </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>