
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

    function addMessage(content, sender, time = formatTime()) {
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
        wrapper.append(avatar, contentBox);
        messages.appendChild(wrapper);

        scrollToBottom();
    }

    // Đổi 'YYYY-MM-DD HH:MM:SS' từ API thành giờ dạng ngắn theo máy người dùng.
    function formatMessageTime(gia_tri) {
        const thoiGian = new Date(String(gia_tri || '').replace(' ', 'T'));
        return Number.isNaN(thoiGian.getTime()) ? formatTime() : formatTime(thoiGian);
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
        addMessage(m.content, m.sender || 'bot', formatMessageTime(m.created_at));
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
