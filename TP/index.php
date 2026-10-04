<?php
$tieu_de_trang = 'VIBE Studio';
$trang_hien_tai = 'trang-chu';
$css_rieng = ['homepage.css'];

require_once __DIR__ . '/../includes/header.php';
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


    <!-- ABOUT -->
    <section class="about" id="about">

        <div class="section-number">01</div>

        <div class="about-content">

            <p class="section-title">ABOUT US</p>

            <h2>
                YOUR MOMENTS.<br>
                OUR <i>VISION.</i>
            </h2>

            <p>
                VIBE Studio là không gian sáng tạo dành cho những
                người yêu thích hình ảnh và nghệ thuật.
                Chúng tôi cung cấp các dịch vụ chụp ảnh chuyên nghiệp
                với phong cách hiện đại, tối giản và cá tính.
            </p>

            <a href="#services" class="text-button">KHÁM PHÁ DỊCH VỤ →</a>

        </div>

    </section>


    <!-- SERVICES -->
    <section class="services" id="services">

        <div class="section-header">
            <div>
                <p class="section-title">WHAT WE DO</p>
                <h2>OUR <i>SERVICES</i></h2>
            </div>
            <p class="section-number">02</p>
        </div>

        <div class="service-list">

            <a href="<?= BASE_URL ?>AT/services.php?dich-vu=portrait" class="service-item">
                <span>01</span>
                <div>
                    <h3>PORTRAIT</h3>
                    <p>Chụp ảnh chân dung cá nhân, profile và concept.</p>
                </div>
                <strong>↗</strong>
            </a>

            <a href="<?= BASE_URL ?>AT/services.php?dich-vu=event" class="service-item">
                <span>02</span>
                <div>
                    <h3>EVENT</h3>
                    <p>Ghi lại những khoảnh khắc đáng nhớ trong sự kiện.</p>
                </div>
                <strong>↗</strong>
            </a>

            <a href="<?= BASE_URL ?>AT/services.php?dich-vu=product" class="service-item">
                <span>03</span>
                <div>
                    <h3>PRODUCT</h3>
                    <p>Hình ảnh sản phẩm chuyên nghiệp cho thương hiệu.</p>
                </div>
                <strong>↗</strong>
            </a>

            <a href="<?= BASE_URL ?>AT/services.php?dich-vu=couple" class="service-item">
                <span>04</span>
                <div>
                    <h3>COUPLE</h3>
                    <p>Lưu giữ những khoảnh khắc đặc biệt của các cặp đôi.</p>
                </div>
                <strong>↗</strong>
            </a>

        </div>

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

            <a href="<?= BASE_URL ?>AT/services.php" class="white-button">ĐẶT LỊCH NGAY</a>

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
                <a href="https://instagram.com/ten-cua-ban" target="_blank" rel="noopener">INSTAGRAM</a>
                <a href="https://facebook.com/ten-cua-ban" target="_blank" rel="noopener">FACEBOOK</a>
                <a href="https://tiktok.com/@ten-cua-ban" target="_blank" rel="noopener">TIKTOK</a>
            </div>

        </div>

    </section>

    </main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>