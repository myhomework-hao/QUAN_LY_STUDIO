<?php
// Không hiển thị popup trong trang chat toàn màn hình
$la_trang_chat = isset($trang_hien_tai) && $trang_hien_tai === 'chat';

// Chỉ hiển thị cho khách hàng đã đăng nhập hoặc khách vãng lai
$vai_tro_chat = function_exists('vai_tro_hien_tai')
    ? vai_tro_hien_tai()
    : 'guest';

$duoc_hien_thi_chat = in_array($vai_tro_chat, ['guest', 'khachhang'], true);

if (!$la_trang_chat && $duoc_hien_thi_chat):

    $js_chat = __DIR__ . '/../assets/js/chat_widget.js';
    $phien_ban_chat = is_file($js_chat) ? (string) filemtime($js_chat) : '1';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/chat_widget.css?v=<?= $phien_ban_chat ?>">

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

// Kéo thả widget, vẫn cho phép bấm mở chat bình thường
document.addEventListener('DOMContentLoaded', function () {
    var widget = document.getElementById('vibe-chat-widget');
    if (!widget) { return; }

    var dangKeo = false, daDiChuyen = false, xDau, yDau, xGoc, yGoc;

    widget.addEventListener('pointerdown', function (e) {
        if (e.target.closest('.vibe-chat-panel')) { return; }
        dangKeo = true;
        daDiChuyen = false;
        xDau = e.clientX;
        yDau = e.clientY;
        var rect = widget.getBoundingClientRect();
        xGoc = rect.left;
        yGoc = rect.top;
    });

    widget.addEventListener('pointermove', function (e) {
        if (!dangKeo) { return; }
        var dx = e.clientX - xDau;
        var dy = e.clientY - yDau;

        if (Math.abs(dx) > 6 || Math.abs(dy) > 6) {
            if (!daDiChuyen) {
                daDiChuyen = true;
                widget.setPointerCapture(e.pointerId);
                widget.style.bottom = 'auto';
                widget.style.right = 'auto';
            }
            widget.style.left = (xGoc + dx) + 'px';
            widget.style.top = (yGoc + dy) + 'px';
        }
    });

    widget.addEventListener('pointerup', function (e) {
        if (!dangKeo) { return; }
        dangKeo = false;
        if (daDiChuyen) {
            try { widget.releasePointerCapture(e.pointerId); } catch (err) {}
        }
    });

    widget.addEventListener('click', function (e) {
        if (daDiChuyen) {
            e.stopImmediatePropagation();
            e.preventDefault();
        }
    }, true);
});
</script>

<script src="<?= BASE_URL ?>assets/js/chat_widget.js?v=<?= $phien_ban_chat ?>" defer></script>

<?php endif; ?>
