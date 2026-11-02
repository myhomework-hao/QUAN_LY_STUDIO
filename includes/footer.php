<?php
$js_rieng = $js_rieng ?? [];
?>

<!-- ===== CSS NỘI BỘ ĐỒNG BỘ CHO FOOTER & CHAT WIDGET ===== -->
<style>
    .site-footer {
        background: #111111;
        color: #ffffff;
        padding: 70px 8% 30px;
        border-top: 1px solid #222222;
        font-family: inherit;
    }

    .footer-container {
        display: grid;
        grid-template-columns: 2fr 1fr 1.5fr 1fr;
        gap: 40px;
        margin-bottom: 50px;
    }

    .footer-logo {
        font-size: 24px;
        font-weight: 600;
        letter-spacing: -1px;
        color: #ffffff;
    }

    .footer-logo span {
        font-weight: 300;
        color: #888888;
    }

    .footer-brand .footer-slogan {
        color: #888888;
        font-size: 13px;
        margin-top: 15px;
        line-height: 1.7;
        max-width: 300px;
    }

    .footer-column h4 {
        font-size: 11px;
        letter-spacing: 2px;
        margin-bottom: 20px;
        color: #ffffff;
        font-weight: 600;
    }

    .footer-column ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .footer-column ul li {
        margin-bottom: 12px;
    }

    .footer-column ul li a, 
    .footer-column p {
        color: #aaaaaa;
        font-size: 13px;
        text-decoration: none;
        transition: color 0.3s ease;
        line-height: 1.5;
    }

    .footer-column ul li a:hover {
        color: #ffffff;
    }

    .footer-bottom {
        border-top: 1px solid #222222;
        padding-top: 25px;
        text-align: center;
    }

    .footer-copyright {
        color: #666666;
        font-size: 12px;
        letter-spacing: 0.5px;
    }

    /* ===== CHAT WIDGET KÉO THẢ TÙY Ý ===== */
    .vibe-chat-widget,
    #vibe-chat-widget {
        position: fixed !important;
        bottom: 90px !important; /* Đẩy lên cao hơn nút Back to Top */
        right: 25px !important;
        z-index: 99999 !important;
        cursor: grab;
        touch-action: none;
    }

    .vibe-chat-widget:active {
        cursor: grabbing;
    }

    .vibe-chat-toggle,
    #vibe-chat-toggle {
        width: 52px !important;
        height: 52px !important;
        background-color: #111111 !important;
        color: #ffffff !important;
        border-radius: 50% !important;
        border: 2px solid #ffffff !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 20px !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.35) !important;
        transition: transform 0.2s ease !important;
    }

    .vibe-chat-toggle:hover {
        background-color: #ffffff !important;
        color: #111111 !important;
        transform: scale(1.08);
    }

    @media (max-width: 900px) {
        .footer-container {
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
    }

    @media (max-width: 600px) {
        .footer-container {
            grid-template-columns: 1fr;
            gap: 30px;
        }
        .site-footer {
            padding: 50px 5% 25px;
        }
    }
</style>

<!-- ===== FOOTER ===== -->
<footer class="site-footer">
    <div class="footer-container">
        <!-- Cột 1: Thông tin thương hiệu -->
        <div class="footer-brand">
            <div class="footer-logo">
                <strong>VIBE</strong><span>STUDIO</span>
            </div>
            <p class="footer-slogan">Không gian sáng tạo & giải pháp cho thuê thiết bị chụp ảnh chuyên nghiệp.</p>
        </div>

        <!-- Cột 2: Điều hướng nhanh dịch vụ -->
        <div class="footer-column">
            <h4>DỊCH VỤ</h4>
            <ul>
                <li><a href="<?= BASE_URL ?>user/services.php?loai=thiet_bi">Máy ảnh - Thiết bị</a></li>
                <li><a href="<?= BASE_URL ?>user/services.php?loai=phong_chup">Chỗ chụp ảnh</a></li>
                <li><a href="<?= BASE_URL ?>user/services.php?loai=trang_phuc">Trang phục</a></li>
                <li><a href="<?= BASE_URL ?>user/services.php?loai=phu_kien">Phụ kiện - Đạo cụ</a></li>
            </ul>
        </div>

        <!-- Cột 3: Chính sách đơn thuê & Hoàn tiền -->
        <div class="footer-column">
            <h4>CHÍNH SÁCH & ĐIỀU KHOẢN</h4>
            <ul>
                <li><a href="<?= BASE_URL ?>user/policy.php#dat-thue">Chính sách đặt thuê</a></li>
                <li><a href="<?= BASE_URL ?>user/policy.php#huy-don-hoan-tien">Quy định hủy đơn & Hoàn tiền</a></li>
                <li><a href="<?= BASE_URL ?>user/policy.php#boi-thuong-hu-hong">Bồi thường & Hư hỏng thiết bị</a></li>
                <li><a href="<?= BASE_URL ?>user/policy.php#bao-mat">Bảo mật thông tin</a></li>
            </ul>
        </div>

        <!-- Cột 4: Thông tin liên hệ -->
        <div class="footer-column">
            <h4>LIÊN HỆ</h4>
            <p>Email: hello@vibestudio.vn</p>
            <p>Hotline: 0909 123 456</p>
            <p>Địa chỉ: TP. Cần Thơ, Việt Nam</p>
        </div>
    </div>

    <div class="footer-bottom">
        <p class="footer-copyright">
            © <?= date('Y') ?> VIBE Studio. All Rights Reserved.
        </p>
    </div>
</footer>


<!-- ===== CHAT WIDGET GÓC MÀN HÌNH ===== -->
<?php require_once __DIR__ . '/chat_widget.php'; ?>


<!-- ===== JAVASCRIPT DÙNG CHUNG ===== -->
<script src="<?= BASE_URL ?>assets/js/common.js?v=<?= is_file(__DIR__ . '/../assets/js/common.js') ? filemtime(__DIR__ . '/../assets/js/common.js') : 1 ?>"></script>

<!-- ===== JAVASCRIPT RIÊNG TỪNG TRANG ===== -->
<?php foreach ($js_rieng as $file_js): ?>
    <script src="<?= htmlspecialchars($file_js, ENT_QUOTES, 'UTF-8') ?>"></script>
<?php endforeach; ?>

<!-- ===== BACK TO TOP ===== -->
<?php require_once __DIR__ . '/back_to_top.php'; ?>

</body>
</html>