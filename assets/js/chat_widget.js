
document.addEventListener('DOMContentLoaded', () => {
    const widget = document.getElementById('vibe-chat-widget');

    if (!widget) return;

    const toggle = document.getElementById('vibe-chat-toggle');
    const panel = document.getElementById('vibe-chat-panel');
    const close = document.getElementById('vibe-chat-close');
    const minimize = document.getElementById('vibe-chat-minimize');
    const form = document.getElementById('vibe-chat-form');
    const input = document.getElementById('vibe-chat-input');
    const sendButton = document.getElementById('vibe-chat-send');
    const messages = document.getElementById('vibe-chat-messages');

    const config = window.VIBE_WIDGET_CONFIG || {};
    const apiUrl = config.apiUrl;

    const productId = Number(config.productId) || null;
    const storageKey = 'vibe_chat_conversation_id';

    let conversationId = null;
    let isSending = false;

    try {
        conversationId = Number(sessionStorage.getItem(storageKey)) || null;
    } catch (error) {
        conversationId = null;
    }

    function openChat() {
        panel.hidden = false;
        toggle.setAttribute('aria-expanded', 'true');
        toggle.style.display = 'none';
        input.focus();
        scrollBottom();
    }

    function closeChat() {
        panel.hidden = true;
        toggle.setAttribute('aria-expanded', 'false');
        toggle.style.display = '';
    }

    function scrollBottom() {
        messages.scrollTop = messages.scrollHeight;
    }

    function getTime() {
        return new Date().toLocaleTimeString('vi-VN', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function addMessage(content, sender = 'bot') {
        const message = document.createElement('div');
        message.className = 'vibe-message ' + (
            sender === 'khach' ? 'user' : 'bot'
        );

        const avatar = document.createElement('div');
        avatar.className = 'vibe-message-avatar';

        const icon = document.createElement('i');
        icon.className = sender === 'khach'
            ? 'fa-solid fa-user'
            : 'fa-solid fa-camera';

        avatar.appendChild(icon);

        const messageContent = document.createElement('div');
        messageContent.className = 'vibe-message-content';

        const bubble = document.createElement('div');
        bubble.className = 'vibe-message-bubble';
        bubble.textContent = content;

        const time = document.createElement('span');
        time.className = 'vibe-message-time';
        time.textContent = getTime();

        messageContent.appendChild(bubble);
        messageContent.appendChild(time);

        message.appendChild(avatar);
        message.appendChild(messageContent);

        messages.appendChild(message);
        scrollBottom();
    }

    function addHandoffNotice() {
        if (messages.querySelector('.vibe-chat-handoff')) return;

        const notice = document.createElement('div');
        notice.className = 'vibe-chat-handoff';
        notice.textContent =
            'Yêu cầu của bạn đã được chuyển đến nhân viên. Vui lòng chờ phản hồi nhé!';

        messages.appendChild(notice);
        scrollBottom();
    }

    function setSending(value) {
        isSending = value;
        sendButton.disabled = value;
        sendButton.innerHTML = value
            ? '<i class="fa-solid fa-spinner fa-spin"></i>'
            : '<i class="fa-solid fa-paper-plane"></i>';
    }

    async function sendMessage(messageText) {
        const message = messageText.trim();

        if (!message || isSending) return;

        if (!apiUrl) {
            addMessage('Không tìm thấy địa chỉ API chat.', 'bot');
            return;
        }

        addMessage(message, 'khach');
        input.value = '';
        input.style.height = 'auto';

        setSending(true);

        try {
            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    action: 'send',
                    message: message,
                    conversation_id: conversationId,
                    product_id: productId
                })
            });

            const raw = await response.text();

            let data;

            try {
                data = JSON.parse(raw);
            } catch (error) {
                console.error('API chat trả về:', raw);
                throw new Error('Máy chủ trả về dữ liệu không hợp lệ.');
            }

            if (response.status === 401) {
                addMessage(
                    'Bạn cần đăng nhập để sử dụng chức năng chat. Vui lòng đăng nhập nhé!'
                );
                return;
            }

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Không thể gửi tin nhắn.');
            }

            conversationId = Number(data.conversation_id);

            try {
                sessionStorage.setItem(storageKey, conversationId);
            } catch (error) {
                console.warn('Không thể lưu phiên chat.');
            }

            if (data.reply) {
                addMessage(data.reply, 'bot');
            }

            if (data.handoff) {
                addHandoffNotice();
            }

        } catch (error) {
            console.error('Lỗi chat:', error);
            addMessage(
                error.message || 'Không thể kết nối. Vui lòng thử lại sau.'
            );
        } finally {
            setSending(false);
        }
    }

    toggle.addEventListener('click', openChat);
    close.addEventListener('click', closeChat);
    minimize.addEventListener('click', closeChat);

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

    input.addEventListener('input', () => {
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 90) + 'px';
    });

    widget.querySelectorAll('[data-message]').forEach(button => {
        button.addEventListener('click', () => {
            sendMessage(button.dataset.message);
        });
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !panel.hidden) {
            closeChat();
        }
    });
});
