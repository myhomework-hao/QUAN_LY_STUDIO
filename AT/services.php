<?php
require_once '../config/app.php';
require_once '../config/db.php';
require_once '../includes/dich_vu.php';

$thu_tu_loai = ['may_anh', 'studio', 'trang_phuc', 'phu_kien', 'tho_chup'];

$ds_dich_vu = $conn->query(
    "SELECT id, ten, loai, gia, don_vi, mo_ta, anh
     FROM services
     WHERE trang_thai = 'hien'
     ORDER BY id"
)->fetchAll(PDO::FETCH_ASSOC);

$loai_dang_co = array_values(array_intersect($thu_tu_loai, array_column($ds_dich_vu, 'loai')));

$loai_chon = $_GET['loai'] ?? 'all';
if (!in_array($loai_chon, $loai_dang_co, true)) {
    $loai_chon = 'all';
}

$tieu_de_trang = 'Sản phẩm cho thuê - VIBE STUDIO';
$trang_hien_tai = 'san-pham';
$css_rieng = [
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css',
    'at.css'
];
$js_rieng = ['at.js'];

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
                Khám phá các thiết bị chụp ảnh, không gian Studio
                và trang phục được chuẩn bị sẵn cho những buổi
                chụp ảnh của bạn.
            </p>
        </div>
    </section>

    <section class="product-list-section">

        <div class="product-tools">

            <div class="product-category">
                <button type="button" class="category-button <?= $loai_chon === 'all' ? 'active' : '' ?>" data-category="all">
                    Tất cả
                </button>

                <?php foreach ($loai_dang_co as $ma_loai): ?>
                    <button type="button" class="category-button <?= $loai_chon === $ma_loai ? 'active' : '' ?>" data-category="<?= h($ma_loai) ?>">
                        <?= h(thong_tin_loai($ma_loai)['ten']) ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="product-search">
                <input type="text" id="search-product" placeholder="Tìm máy ảnh, chỗ chụp, bộ đồ...">
                <button type="button" id="search-button" aria-label="Tìm kiếm">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </div>

        </div>

        <div class="product-grid" id="product-list">

            <?php foreach ($ds_dich_vu as $dv): ?>
                <?php
                $tt_loai = thong_tin_loai($dv['loai']);
                $link = BASE_URL . 'AT/service-detail.php?id=' . (int) $dv['id'];
                ?>
                <article class="product-card" data-category="<?= h($dv['loai']) ?>">

                    <div class="product-image">
                        <?php if (!empty($dv['anh'])): ?>
                            <img src="<?= BASE_URL ?>assets/uploads/<?= h($dv['anh']) ?>" alt="<?= h($dv['ten']) ?>" loading="lazy">
                        <?php endif; ?>

                        <span class="product-label"><?= h($tt_loai['nhan']) ?></span>

                        <div class="product-overlay">
                            <a href="<?= $link ?>" class="main-button">Xem chi tiết</a>
                        </div>
                    </div>

                    <div class="product-info">
                        <p class="product-category-name"><?= h($tt_loai['ten']) ?></p>
                        <h2 class="product-name"><?= h($dv['ten']) ?></h2>
                        <p class="product-description"><?= h($dv['mo_ta']) ?></p>

                        <div class="product-bottom">
                            <span class="product-price"><?= h(dinh_dang_gia((int) $dv['gia'], $dv['don_vi'])) ?></span>
                            <a href="<?= $link ?>" class="product-detail-link">
                                Chi tiết
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>

                </article>
            <?php endforeach; ?>

        </div>

        <div class="no-product" id="no-product" hidden>
            Không tìm thấy sản phẩm phù hợp.
        </div>

    </section>

</main>

<?php require_once '../includes/footer.php'; ?>