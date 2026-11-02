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


<!-- ===== TÍCH HỢP TRỰC TIẾP CHAT WIDGET GÓC MÀN HÌNH ===== -->
<?php
$la_trang_chat = isset($trang_hien_tai) && $trang_hien_tai === 'chat';
$vai_tro_chat = function_exists('vai_tro_hien_tai') ? vai_tro_hien_tai() : 'guest';
$duoc_hien_thi_chat = in_array($vai_tro_chat, ['guest', 'khachhang'], true);

if (!$la_trang_chat && $duoc_hien_thi_chat):
?>
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/chat_widget.css">

<div class="vibe-chat-widget" id="vibe-chat-widget">
    <!-- Nút mở chat -->
    <button type="button" class="vibe-chat-toggle" id="vibe-chat-toggle" aria-label="Mở chat hỗ trợ" aria-expanded="false" aria-controls="vibe-chat-panel">
        <i class="fa-regular fa-comment-dots"></i>
        <span class="vibe-chat-notification" aria-hidden="true"></span>
    </button>

    <!-- Khung chat -->
    <section class="vibe-chat-panel" id="vibe-chat-panel" aria-label="Chat hỗ trợ VIBE Studio" hidden>
        <div class="vibe-chat-header">
            <div class="vibe-chat-brand">
                <div class="vibe-chat-logo">
                    <i class="fa-solid fa-camera"></i>
                </div>
                <div>
                    <h3>VIBE STUDIO</h3>
                    <p>Trợ lý hỗ trợ trực tuyến</p>
                </div>
            </div>
            <div class="vibe-chat-header-actions">
                <button type="button" id="vibe-chat-minimize" aria-label="Thu nhỏ chat"><i class="fa-solid fa-minus"></i></button>
                <button type="button" id="vibe-chat-close" aria-label="Đóng chat"><i class="fa-solid fa-xmark"></i></button>
            </div>
        </div>

        <div class="vibe-chat-body">
            <div class="vibe-chat-intro">
                <span class="vibe-chat-intro-icon"><i class="fa-solid fa-wand-magic-sparkles"></i></span>
                <div>
                    <h4>Xin chào!</h4>
                    <p>VIBE Studio có thể hỗ trợ gì cho bạn hôm nay?</p>
                </div>
            </div>

            <div class="vibe-chat-messages" id="vibe-chat-messages" aria-live="polite">
                <div class="vibe-message bot">
                    <div class="vibe-message-avatar"><i class="fa-solid fa-camera"></i></div>
                    <div class="vibe-message-content">
                        <div class="vibe-message-bubble">
                            Xin chào! Mình là trợ lý VIBE Studio. Bạn muốn tìm hiểu dịch vụ hay cần hỗ trợ đặt lịch?
                        </div>
                        <span class="vibe-message-time">Vừa xong</span>
                    </div>
                </div>
            </div>

            <div class="vibe-chat-suggestions">
                <button type="button" data-message="Tôi muốn hỏi giá thuê dịch vụ.">Hỏi giá dịch vụ</button>
                <button type="button" data-message="Tôi muốn đặt lịch chụp ảnh.">Đặt lịch chụp</button>
                <button type="button" data-message="Tôi cần nhân viên hỗ trợ.">Gặp nhân viên</button>
            </div>
        </div>

        <form class="vibe-chat-form" id="vibe-chat-form">
            <textarea id="vibe-chat-input" placeholder="Nhập tin nhắn của bạn..." rows="1" maxlength="2000" aria-label="Nội dung tin nhắn" required></textarea>
            <button type="submit" id="vibe-chat-send" aria-label="Gửi tin nhắn"><i class="fa-solid fa-paper-plane"></i></button>
        </form>

        <div class="vibe-chat-footer">Được hỗ trợ bởi VIBE Studio</div>
    </section>
</div>

<script>
window.VIBE_WIDGET_CONFIG = {
    apiUrl: <?= json_encode(BASE_URL . 'api/chat_api.php') ?>,
    productId: <?= json_encode(isset($chat_product_id) ? (int) $chat_product_id : null) ?>,
    loginUrl: <?= json_encode(BASE_URL . 'user/login.php') ?>
};

// XỬ LÝ KÉO THẢ THÔNG MINH (KHÔNG LÀM ẢNH HƯỞNG ĐẾN SỰ KIỆN CLICK MỞ CHAT)
document.addEventListener('DOMContentLoaded', () => {
    const widget = document.getElementById('vibe-chat-widget');
    if (!widget) return;

    let isDragging = false;
    let hasMoved = false;
    let startX, startY, initialX, initialY;

    widget.addEventListener('pointerdown', (e) => {
        // Không can thiệp nếu người dùng đang bấm thao tác bên trong bảng khung chat
        if (e.target.closest('.vibe-chat-panel')) return;
        
        isDragging = true;
        hasMoved = false;
        startX = e.clientX;
        startY = e.clientY;

        const rect = widget.getBoundingClientRect();
        initialX = rect.left;
        initialY = rect.top;
    });

    widget.addEventListener('pointermove', (e) => {
        if (!isDragging) return;

        const dx = e.clientX - startX;
        const dy = e.clientY - startY;

        // Nếu dịch chuyển chuột vượt quá 6 pixel mới kích hoạt chế độ kéo thả
        if (Math.abs(dx) > 6 || Math.abs(dy) > 6) {
            if (!hasMoved) {
                hasMoved = true;
                widget.setPointerCapture(e.pointerId);
                widget.style.bottom = 'auto';
                widget.style.right = 'auto';
            }
            widget.style.left = (initialX + dx) + 'px';
            widget.style.top = (initialY + dy) + 'px';
        }
    });

    widget.addEventListener('pointerup', (e) => {
        if (!isDragging) return;
        isDragging = false;
        if (hasMoved) {
            try {
                widget.releasePointerCapture(e.pointerId);
            } catch (err) {}
        }
    });

    // Chặn sự kiện click truyền đi nếu thực sự đang kéo thả, ngược lại cho phép file chat_widget.js mở chat bình thường
    widget.addEventListener('click', (e) => {
        if (hasMoved) {
            e.stopImmediatePropagation();
            e.preventDefault();
        }
    }, true);
});
</script>
<script src="<?= BASE_URL ?>assets/js/chat_widget.js" defer></script>
<?php endif; ?>


<!-- ===== JAVASCRIPT DÙNG CHUNG ===== -->
<script src="<?= BASE_URL ?>assets/js/common.js"></script>

<!-- ===== JAVASCRIPT RIÊNG TỪNG TRANG ===== -->
<?php foreach ($js_rieng as $file_js): ?>
    <script src="<?= htmlspecialchars($file_js, ENT_QUOTES, 'UTF-8') ?>"></script>
<?php endforeach; ?>

<!-- ===== BACK TO TOP ===== -->
<?php require_once __DIR__ . '/back_to_top.php'; ?>

</body>
</html>