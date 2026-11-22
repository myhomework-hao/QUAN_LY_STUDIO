
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('chat-form');
    const input = document.getElementById('chat-input');
    const messages = document.getElementById('chat-messages');
    const sendButton = document.getElementById('chat-send');

    if (!form || !input || !messages || !sendButton) {
        return;
    }

    const config = window.CHAT_CONFIG || {};
    const apiUrl = config.apiUrl;
    const productId = config.productId || null;

    let conversationId = null;
    let isSending = false;
    let lastId = 0;
    let boLayTin = null;
    let daKetThuc = false;
    const tinDaXem = new Set();

    function scrollToBottom() {
        messages.scrollTop = messages.scrollHeight;
    }

    function formatTime(date = new Date()) {
        return date.toLocaleTimeString('vi-VN', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function addMessage(content, sender, time = formatTime(), sanPham = null) {
        const isUser = sender === 'khach';
        const isStaff = sender === 'nhanvien';

        const wrapper = document.createElement('div');
        wrapper.className = isUser
            ? 'chat-message user-message'
            : 'chat-message bot-message';

        const avatar = document.createElement('div');
        avatar.className = 'message-avatar';

        const avatarIcon = document.createElement('i');
        avatarIcon.className = isUser
            ? 'fa-solid fa-user'
            : (isStaff ? 'fa-solid fa-headset' : 'fa-solid fa-robot');

        avatar.appendChild(avatarIcon);

        const contentBox = document.createElement('div');
        contentBox.className = 'message-content';

        const senderName = document.createElement('span');
        senderName.className = 'message-sender';
        senderName.textContent = isUser
            ? 'Bạn'
            : (isStaff ? 'Nhân viên VIBE' : 'Trợ lý VIBE');

        const bubble = document.createElement('div');
        bubble.className = 'message-bubble';

        // Dùng textContent để tin nhắn không thể chèn HTML hoặc script.
        bubble.textContent = content;

        const timeElement = document.createElement('time');
        timeElement.textContent = time;

        contentBox.append(senderName, bubble, timeElement);

        // Thẻ gợi ý sản phẩm (nếu có) — dựng bằng createElement/textContent, không innerHTML
        if (!isUser && Array.isArray(sanPham) && sanPham.length > 0) {
            const list = renderSanPham(sanPham);
            if (list) {
                contentBox.insertBefore(list, timeElement);
            }
        }

        wrapper.append(avatar, contentBox);
        messages.appendChild(wrapper);

        scrollToBottom();
    }

    // Đổi 'YYYY-MM-DD HH:MM:SS' từ API thành giờ dạng ngắn theo máy người dùng.
    function formatMessageTime(gia_tri) {
        const thoiGian = new Date(String(gia_tri || '').replace(' ', 'T'));
        return Number.isNaN(thoiGian.getTime()) ? formatTime() : formatTime(thoiGian);
    }

    function formatTien(so) {
        const n = Number(so);
        if (Number.isNaN(n)) return '';
        try {
            return new Intl.NumberFormat('vi-VN').format(n) + '₫';
        } catch (e) {
            return String(Math.round(n)) + '₫';
        }
    }

    function duongDanGoc() {
        return String(config.apiUrl || '').replace(/api\/chat_api\.php.*$/, '');
    }

    function renderSanPham(sanPham) {
        if (!Array.isArray(sanPham) || sanPham.length === 0) {
            return null;
        }
        const base = duongDanGoc();
        const container = document.createElement('div');
        container.className = 'chat-product-list';

        sanPham.forEach(sp => {
            const card = document.createElement('a');
            card.className = 'chat-product-card';
            card.href = base + 'user/service-detail.php?id=' + encodeURIComponent(sp && sp.id ? sp.id : '');

            const img = document.createElement('img');
            img.className = 'chat-product-thumb';
            img.src = base + 'assets/uploads/' + (sp && sp.anh ? sp.anh : 'default.png');
            img.alt = sp && sp.ten ? sp.ten : '';
            img.loading = 'lazy';
            card.appendChild(img);

            const info = document.createElement('div');
            info.className = 'chat-product-info';

            const name = document.createElement('strong');
            name.className = 'chat-product-name';
            name.textContent = sp && sp.ten ? sp.ten : '';
            info.appendChild(name);

            const meta = document.createElement('div');
            meta.className = 'chat-product-meta';

            const code = document.createElement('span');
            code.className = 'chat-product-code';
            code.textContent = sp && sp.ma ? sp.ma : '';
            meta.appendChild(code);

            const price = document.createElement('span');
            price.className = 'chat-product-price';
            price.textContent = formatTien(sp && sp.gia) + ' / ' + (sp && sp.don_vi ? sp.don_vi : '');
            meta.appendChild(price);

            info.appendChild(meta);

            if (sp && sp.sizes) {
                const size = document.createElement('div');
                size.className = 'chat-product-size';
                size.textContent = 'Size: ' + sp.sizes;
                info.appendChild(size);
            } else if (sp && sp.so_luong !== null && sp.so_luong !== undefined) {
                const qty = document.createElement('div');
                qty.className = 'chat-product-qty';
                qty.textContent = (Number(sp.so_luong) > 0)
                    ? ('Số lượng: ' + sp.so_luong)
                    : 'Tạm hết';
                info.appendChild(qty);
            }

            if (sp && sp.dong_phu) {
                const sub = document.createElement('div');
                sub.className = 'chat-product-sub';
                sub.textContent = sp.dong_phu;
                info.appendChild(sub);
            }

            card.appendChild(info);
            container.appendChild(card);
        });

        return container;
    }

    // Ghi nhận mã tin nhắn đã xử lý để lần hỏi sau không vẽ trùng.
    function ghiNhan(m) {
        const id = Number(m && m.id);
        if (id > 0) {
            tinDaXem.add(id);
            if (id > lastId) lastId = id;
        }
    }

    // Vẽ một tin nhắn lấy từ API (bot/nhân viên) nếu chưa từng thấy.
    function themTin(m) {
        const id = Number(m && m.id);
        if (id > 0 && tinDaXem.has(id)) {
            return;
        }
        ghiNhan(m);
        addMessage(m.content, m.sender || 'bot', formatMessageTime(m.created_at), m.san_pham);
    }

    // Dòng trạng thái (chờ nhân viên / đã kết thúc), chỉ giữ một dòng duy nhất.
    function thongBaoTrangThai(noiDung) {
        let notice = messages.querySelector('.chat-handoff-notice');
        if (!notice) {
            notice = document.createElement('p');
            notice.className = 'chat-handoff-notice';
            messages.appendChild(notice);
        }
        if (notice.textContent === noiDung) {
            return;
        }
        notice.textContent = noiDung;
        scrollToBottom();
    }

    function dinhLayTin() {
        if (boLayTin) {
            clearInterval(boLayTin);
            boLayTin = null;
        }
    }

    function setSending(sending) {
        isSending = sending;
        sendButton.disabled = sending || daKetThuc;
        input.disabled = sending || daKetThuc;
        sendButton.innerHTML = sending
            ? '<i class="fa-solid fa-spinner fa-spin"></i>'
            : '<i class="fa-solid fa-paper-plane"></i>';
    }

    async function sendMessage(text) {
        const message = String(text || '').trim();

        if (!message || isSending || daKetThuc) {
            return;
        }

        if (!apiUrl) {
            addMessage(
                'Chưa cấu hình địa chỉ API chat. Vui lòng kiểm tra CHAT_CONFIG.',
                'bot'
            );
            return;
        }

        addMessage(message, 'khach');
        input.value = '';
        setSending(true);

        try {
            const response = await fetch(apiUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    action: 'send',
                    message: message,
                    conversation_id: conversationId,
                    product_id: productId,
                    csrf: config.csrf || ''
                })
            });

            const result = await response.json();

            if (response.status === 401) {
                addMessage(
                    'Bạn cần đăng nhập trước khi gửi tin nhắn. '
                    + 'Hãy đăng nhập rồi quay lại cuộc trò chuyện nhé.',
                    'bot'
                );
                return;
            }

            if (response.status === 409) {
                daKetThuc = true;
                dinhLayTin();
            }

            if (!response.ok || !result.success) {
                throw new Error(
                    result.message || 'Không thể gửi tin nhắn.'
                );
            }

            if (result.conversation_id) {
                conversationId = Number(result.conversation_id);
            }

            // API trả về cả tin của chính mình vừa lưu: chỉ ghi nhận mã để
            // poll không vẽ trùng, phản hồi của bot/nhân viên sẽ được vẽ bên dưới.
            (result.messages || []).forEach(m => {
                if (m.sender === 'khach') {
                    ghiNhan(m);
                    return;
                }
                themTin(m);
            });

            if (result.handoff) {
                thongBaoTrangThai(
                    'Cuộc trò chuyện đang chờ nhân viên tiếp nhận.'
                );
            }

            batDauLayTin();
        } catch (error) {
            console.error('Lỗi chat:', error);

            addMessage(
                error.message === 'Failed to fetch'
                    ? 'Không kết nối được máy chủ. Bạn hãy kiểm tra XAMPP và thử lại.'
                    : error.message || 'Đã xảy ra lỗi. Vui lòng thử lại.',
                'bot'
            );
        } finally {
            setSending(false);
            input.focus();
        }
    }

    // Hỏi định kỳ để nhận phản hồi của bot/nhân viên gửi sau tin nhắn cuối.
    async function layTinMoi() {
        if (!conversationId || isSending || document.hidden || daKetThuc) {
            return;
        }

        try {
            const response = await fetch(apiUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    action: 'poll',
                    conversation_id: conversationId,
                    after_id: lastId,
                    csrf: config.csrf || ''
                })
            });

            if (!response.ok) {
                return;
            }

            const result = await response.json();

            if (!result.success) {
                return;
            }

            if (result.status === 'da_dong') {
                daKetThuc = true;
                dinhLayTin();
                thongBaoTrangThai('Cuộc trò chuyện đã kết thúc.');
                return;
            }

            let coMoi = false;
            (result.messages || []).forEach(m => {
                if (m.sender === 'khach') {
                    ghiNhan(m);
                    return;
                }
                themTin(m);
                coMoi = true;
            });

            if (coMoi) {
                scrollToBottom();
            }
        } catch (error) {
            // Lỗi tạm thời: bỏ qua, lần hỏi sau sẽ thử lại.
        }
    }

    function batDauLayTin() {
        if (boLayTin || !conversationId || !apiUrl) {
            return;
        }
        boLayTin = setInterval(layTinMoi, 4000);
    }

    form.addEventListener('submit', event => {
        event.preventDefault();
        sendMessage(input.value);
    });

    input.addEventListener('keydown', event => {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            form.requestSubmit();
        }
    });

    document.querySelectorAll('[data-message]').forEach(button => {
        button.addEventListener('click', () => {
            sendMessage(button.dataset.message);
        });
    });
});
