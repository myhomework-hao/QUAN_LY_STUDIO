
<?php
require_once '../config/app.php';
require_once '../config/db.php';
require_once '../includes/header.php';

$product_id = filter_input(INPUT_GET, 'product_id', FILTER_VALIDATE_INT);
$product_id = $product_id && $product_id > 0 ? $product_id : null;

$tieu_de_trang = 'Chat hỗ trợ - VIBE STUDIO';
$trang_hien_tai = 'chat';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/chat.css">

<main class="chat-page">
    <section class="chat-container">
        <aside class="chat-sidebar">
            <div class="chat-brand">
                <div class="chat-brand-icon">
                    <i class="fa-regular fa-comments"></i>
                </div>
                <div>
                    <h2>VIBE STUDIO</h2>
                    <p>Hỗ trợ khách hàng</p>
                </div>
            </div>

            <div class="chat-welcome">
                <span class="chat-online-dot"></span>
                <div>
                    <strong>Trợ lý VIBE</strong>
                    <p>Luôn sẵn sàng hỗ trợ bạn</p>
                </div>
            </div>

            <div class="chat-help">
                <h3>Bạn cần hỗ trợ?</h3>

                <button type="button" class="chat-suggestion"
                    data-message="Tôi muốn hỏi giá thuê sản phẩm.">
                    <i class="fa-solid fa-tag"></i>
                    Giá thuê sản phẩm
                </button>

                <button type="button" class="chat-suggestion"
                    data-message="Tôi muốn biết cách đặt thuê.">
                    <i class="fa-regular fa-calendar-check"></i>
                    Cách đặt thuê
                </button>

                <button type="button" class="chat-suggestion"
                    data-message="Tôi muốn gặp nhân viên hỗ trợ.">
                    <i class="fa-solid fa-headset"></i>
                    Gặp nhân viên
                </button>
            </div>

            <div class="chat-sidebar-note">
                <i class="fa-solid fa-shield-halved"></i>
                <span>Thông tin trao đổi được lưu trong cuộc hội thoại.</span>
            </div>
        </aside>

        <section class="chat-main">
            <header class="chat-header">
                <div class="chat-header-info">
                    <div class="chat-avatar">
                        <i class="fa-solid fa-headset"></i>
                    </div>
                    <div>
                        <h1>Trò chuyện với VIBE</h1>
                        <p>
                            <span class="chat-online-dot"></span>
                            Trợ lý tự động
                        </p>
                    </div>
                </div>

                <span class="chat-mode-label">
                    <i class="fa-solid fa-robot"></i>
                    Hỗ trợ trực tuyến
                </span>
            </header>

            <div class="chat-product-context" id="chat-product-context"
                data-product-id="<?= $product_id ?? '' ?>"
                <?= $product_id ? '' : 'hidden' ?>>
                <i class="fa-solid fa-camera"></i>
                <span>Đang trao đổi về sản phẩm bạn đã chọn</span>
            </div>

            <div class="chat-messages" id="chat-messages"
                aria-live="polite">
                <div class="chat-message bot-message">
                    <div class="message-avatar">
                        <i class="fa-solid fa-robot"></i>
                    </div>
                    <div class="message-content">
                        <span class="message-sender">Trợ lý VIBE</span>
                        <div class="message-bubble">
                            Xin chào! Chào mừng bạn đến với VIBE STUDIO.
                            Bạn muốn tìm hiểu về sản phẩm, giá thuê hay
                            cần nhân viên hỗ trợ?
                        </div>
                        <time>Trợ lý tự động</time>
                    </div>
                </div>
            </div>

            <div class="chat-quick-actions">
                <button type="button" data-message="Tôi muốn hỏi giá thuê.">
                    Hỏi giá thuê
                </button>
                <button type="button" data-message="Tôi cần nhân viên hỗ trợ.">
                    Gặp nhân viên
                </button>
            </div>

            <form class="chat-input-area" id="chat-form">
                <label class="sr-only" for="chat-input">
                    Nội dung tin nhắn
                </label>

                <textarea id="chat-input" name="message"
                    placeholder="Nhập tin nhắn của bạn..."
                    rows="1" maxlength="2000" required></textarea>

                <button type="submit" id="chat-send"
                    aria-label="Gửi tin nhắn">
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
            </form>

            <p class="chat-footer-note">
                VIBE STUDIO · Đồng hành cùng buổi chụp của bạn
            </p>
        </section>
    </section>
</main>

<script>
    window.CHAT_CONFIG = {
        productId: <?= $product_id ? (int) $product_id : 'null' ?>,
        apiUrl: <?= json_encode(BASE_URL . 'api/chat_api.php') ?>
    };
</script>
<script src="<?= BASE_URL ?>assets/js/chat.js" defer></script>

<?php require_once '../includes/footer.php'; ?>
