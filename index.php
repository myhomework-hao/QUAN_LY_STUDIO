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
            <a href="#gallery" class="main-button">XEM PORTFOLIO</a>
        </div>

        <div class="hero-image">
            <img src="https://images.unsplash.com/photo-1554048612-b6a482bc67e5?auto=format&fit=crop&w=1200&q=80" alt="VIBE Studio">
        </div>
    </section>

    <!-- GIỚI THIỆU + DỊCH VỤ -->
    <section class="about-services" id="about">
        <div class="section-number">01</div>

        <div class="about-services-content">
            <p class="section-title">ABOUT US &amp; SERVICES</p>
            <h2>
                YOUR MOMENTS.<br>
                OUR <i>VISION.</i>
            </h2>
            <p class="about-text">
                VIBE Studio là không gian sáng tạo dành cho những
                người yêu thích hình ảnh và nghệ thuật.
                Chúng tôi cung cấp các dịch vụ chụp ảnh chuyên nghiệp
                với phong cách hiện đại, tối giản và cá tính.
            </p>

            <div class="service-list" id="services">
                <a href="<?= BASE_URL ?>user/services.php?loai=thiet_bi" class="service-item">
                    <span>01</span>
                    <div>
                        <h3>MÁY ẢNH - THIẾT BỊ</h3>
                        <p>Máy ảnh, ống kính, đèn và thiết bị cho buổi chụp của bạn.</p>
                    </div>
                    <strong>↗</strong>
                </a>

                <a href="<?= BASE_URL ?>user/services.php?loai=phong_chup" class="service-item">
                    <span>02</span>
                    <div>
                        <h3>CHỖ CHỤP ẢNH</h3>
                        <p>Không gian studio và phim trường chuẩn bị sẵn để thuê theo giờ.</p>
                    </div>
                    <strong>↗</strong>
                </a>

                <a href="<?= BASE_URL ?>user/services.php?loai=trang_phuc" class="service-item">
                    <span>03</span>
                    <div>
                        <h3>BỘ ĐỒ</h3>
                        <p>Trang phục cho chân dung, couple, sự kiện và lookbook.</p>
                    </div>
                    <strong>↗</strong>
                </a>

                <a href="<?= BASE_URL ?>user/services.php?loai=phu_kien" class="service-item">
                    <span>04</span>
                    <div>
                        <h3>PHỤ KIỆN - ĐẠO CỤ</h3>
                        <p>Đạo cụ và phụ kiện giúp bức ảnh có thêm cá tính.</p>
                    </div>
                    <strong>↗</strong>
                </a>
            </div>
        </div>
    </section>

    <!-- SẢN PHẨM NỔI BẬT -->
    <section class="featured" id="products">
        <div class="section-header">
            <div>
                <p class="section-title">BEST SELLERS</p>
                <h2>FEATURED <i>PRODUCTS</i></h2>
            </div>
            <p class="section-number">02</p>
        </div>

        <?php if (empty($san_pham_noi_bat)): ?>
            <p class="featured-empty">Sản phẩm đang được cập nhật.</p>
        <?php else: ?>
        <div class="featured-grid">
            <?php foreach ($san_pham_noi_bat as $sp): ?>
                <?php 
                    // Trỏ chuẩn vào trang chi tiết nằm trong thư mục user/
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

        <!-- Đã sửa lại đúng đường dẫn trỏ vào user/services.php -->
        <a href="<?= BASE_URL ?>user/services.php" class="text-button featured-more">XEM TẤT CẢ SẢN PHẨM →</a>
    </section>

    <!-- GALLERY -->
    <section class="gallery" id="gallery">
        <div class="section-header">
            <div>
                <p class="section-title">SELECTED WORK</p>
                <h2>OUR <i>GALLERY</i></h2>
            </div>
            <p class="section-number">03</p>
        </div>

        <div class="gallery-grid">
            <div class="gallery-item large">
                <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=900&q=80" alt="Portrait" loading="lazy">
            </div>
            <div class="gallery-item">
                <img src="https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=900&q=80" alt="Fashion" loading="lazy">
            </div>
            <div class="gallery-item">
                <img src="https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=900&q=80" alt="Couple" loading="lazy">
            </div>
            <div class="gallery-item large">
                <img src="https://images.unsplash.com/photo-1529139574466-a303027c1d8b?auto=format&fit=crop&w=900&q=80" alt="Studio" loading="lazy">
            </div>
            <div class="gallery-item">
                <img src="https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=900&q=80" alt="Portrait" loading="lazy">
            </div>
            <div class="gallery-item">
                <img src="https://images.unsplash.com/photo-1496747611176-843222e1e57c?auto=format&fit=crop&w=900&q=80" alt="Fashion" loading="lazy">
            </div>
        </div>
    </section>

    <!-- BOOKING -->
    <section class="booking" id="booking">
        <div class="booking-content">
            <p class="section-title">LET'S WORK TOGETHER</p>
            <h2>
                READY TO CREATE<br>
                YOUR <i>VIBE?</i>
            </h2>
            <p>
                Hãy để VIBE Studio đồng hành cùng bạn
                trong những khoảnh khắc đặc biệt.
            </p>
            <a href="<?= BASE_URL ?>user/services.php" class="white-button">ĐẶT LỊCH NGAY</a>
        </div>
    </section>

    <!-- CONTACT -->
    <section class="contact" id="contact">
        <div class="contact-left">
            <p class="section-title">CONTACT</p>
            <h2>
                LET'S<br>
                <i>TALK.</i>
            </h2>
        </div>

        <div class="contact-right">
            <div class="contact-info">
                <span>EMAIL</span>
                <p><a href="mailto:hello@vibestudio.vn">hello@vibestudio.vn</a></p>
            </div>
            <div class="contact-info">
                <span>PHONE</span>
                <p><a href="tel:0909123456">0909 123 456</a></p>
            </div>
            <div class="contact-info">
                <span>ADDRESS</span>
                <p>Cần Thơ, Việt Nam</p>
            </div>
            <div class="social">
                <a href="https://instagram.com" target="_blank" rel="noopener">INSTAGRAM</a>
                <a href="https://facebook.com" target="_blank" rel="noopener">FACEBOOK</a>
                <a href="https://tiktok.com" target="_blank" rel="noopener">TIKTOK</a>
            </div>
        </div>
    </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>