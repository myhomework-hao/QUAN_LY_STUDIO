
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

        const wrapper = document.createElement('div');
        wrapper.className = isUser
            ? 'chat-message user-message'
            : 'chat-message bot-message';

        const avatar = document.createElement('div');
        avatar.className = 'message-avatar';

        const avatarIcon = document.createElement('i');
        avatarIcon.className = isUser
            ? 'fa-solid fa-user'
            : 'fa-solid fa-robot';

        avatar.appendChild(avatarIcon);

        const contentBox = document.createElement('div');
        contentBox.className = 'message-content';

        const senderName = document.createElement('span');
        senderName.className = 'message-sender';
        senderName.textContent = isUser
            ? 'Bạn'
            : 'Trợ lý VIBE';

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

    function setSending(sending) {
        isSending = sending;
        sendButton.disabled = sending;
        input.disabled = sending;
        sendButton.innerHTML = sending
            ? '<i class="fa-solid fa-spinner fa-spin"></i>'
            : '<i class="fa-solid fa-paper-plane"></i>';
    }

    async function sendMessage(text) {
        const message = String(text || '').trim();

        if (!message || isSending) {
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
                    product_id: productId
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

            if (!response.ok || !result.success) {
                throw new Error(
                    result.message || 'Không thể gửi tin nhắn.'
                );
            }

            if (result.conversation_id) {
                conversationId = Number(result.conversation_id);
            }

            if (result.reply) {
                addMessage(result.reply, 'bot');
            }

            if (result.handoff) {
                const status = document.createElement('p');
                status.className = 'chat-handoff-notice';
                status.textContent =
                    'Cuộc trò chuyện đang chờ nhân viên tiếp nhận.';

                messages.appendChild(status);
                scrollToBottom();
            }
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
