<?php
declare(strict_types=1);

require_once '../config/app.php';
require_once '../config/db.php';
require_once '../config/auth.php';

header('Content-Type: application/json; charset=utf-8');

function chat_json(array $data, int $status = 200): never
{
    http_response_code($status);
    echo json_encode(
        $data,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
    exit;
}

function chat_require_customer(): int
{
    if (!da_dang_nhap()) {
        chat_json([
            'success' => false,
            'message' => 'Bạn cần đăng nhập để sử dụng chat.'
        ], 401);
    }

    if (vai_tro_hien_tai() !== 'khachhang') {
        chat_json([
            'success' => false,
            'message' => 'Chức năng này dành cho khách hàng.'
        ], 403);
    }

    $userId = ma_nguoi_dung_hien_tai();

    if ($userId <= 0) {
        chat_json([
            'success' => false,
            'message' => 'Tài khoản không hợp lệ.'
        ], 401);
    }

    return $userId;
}

function chat_read_input(): array
{
    $data = json_decode(
        file_get_contents('php://input') ?: '',
        true
    );

    if (!is_array($data)) {
        chat_json([
            'success' => false,
            'message' => 'Dữ liệu gửi lên không hợp lệ.'
        ], 400);
    }

    return $data;
}

function chat_get_owned_conversation(
    PDO $conn,
    int $conversationId,
    int $userId
): array {
    $stmt = $conn->prepare(
        'SELECT CHT_Ma, CHT_CheDo, CHT_TrangThai
         FROM chat_hoi_thoai
         WHERE CHT_Ma = ? AND ND_Ma = ?
         LIMIT 1'
    );

    $stmt->execute([$conversationId, $userId]);
    $conversation = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$conversation) {
        chat_json([
            'success' => false,
            'message' => 'Không tìm thấy cuộc hội thoại.'
        ], 404);
    }

    return $conversation;
}

function chat_create_conversation(
    PDO $conn,
    int $userId,
    ?int $productId
): int {
    $title = 'Hỗ trợ khách hàng VIBE STUDIO';

    if ($productId !== null) {
        $stmt = $conn->prepare(
            'SELECT DV_Ten
             FROM dichvu
             WHERE DV_Ma = ? AND DV_DangBan = 1
             LIMIT 1'
        );

        $stmt->execute([$productId]);
        $productName = $stmt->fetchColumn();

        if ($productName === false) {
            chat_json([
                'success' => false,
                'message' => 'Sản phẩm không tồn tại hoặc đã ngừng bán.'
            ], 404);
        }

        $title = 'Tư vấn: ' . $productName;
    }

    $stmt = $conn->prepare(
        "INSERT INTO chat_hoi_thoai
            (ND_Ma, CHT_TieuDe, CHT_CheDo, CHT_TrangThai)
         VALUES (?, ?, 'bot', 'dang_mo')"
    );

    $stmt->execute([$userId, $title]);

    return (int) $conn->lastInsertId();
}

function chat_save_message(
    PDO $conn,
    int $conversationId,
    string $sender,
    string $content,
    ?int $userId = null
): int {
    $stmt = $conn->prepare(
        'INSERT INTO chat_tin_nhan
            (CHT_Ma, CTN_NguoiGui, ND_Ma, NV_Ma,
             CTN_NoiDung, CTN_DaDoc)
         VALUES (?, ?, ?, NULL, ?, 0)'
    );

    $stmt->execute([
        $conversationId,
        $sender,
        $userId,
        $content
    ]);

    return (int) $conn->lastInsertId();
}

function chat_make_reply(string $message): array
{
    $text = mb_strtolower(trim($message), 'UTF-8');

    if (
        str_contains($text, 'nhân viên') ||
        str_contains($text, 'nhan vien') ||
        str_contains($text, 'người thật') ||
        str_contains($text, 'tư vấn trực tiếp')
    ) {
        return [
            'reply' => 'Mình đã ghi nhận yêu cầu hỗ trợ trực tiếp. '
                . 'Tin nhắn của bạn sẽ được lưu để nhân viên tiếp nhận. '
                . 'Hiện hệ thống cần có nhân viên trực và giao diện quản lý '
                . 'chat để nhân viên phản hồi.',
            'handoff' => true
        ];
    }

    if (
        str_contains($text, 'giá') ||
        str_contains($text, 'bao nhiêu') ||
        str_contains($text, 'chi phí')
    ) {
        return [
            'reply' => 'Bạn có thể xem giá thuê và đơn vị tính ngay '
                . 'trên thẻ sản phẩm hoặc trang chi tiết sản phẩm. '
                . 'Nếu bạn cần tư vấn giá cho thời gian thuê cụ thể, '
                . 'hãy nhắn “Gặp nhân viên”.',
            'handoff' => false
        ];
    }

    if (
        str_contains($text, 'đặt thuê') ||
        str_contains($text, 'đặt lịch') ||
        str_contains($text, 'thuê như thế nào')
    ) {
        return [
            'reply' => 'Bạn hãy chọn sản phẩm, nhấn “Thuê ngay”, '
                . 'nhập thông tin và thời gian thuê, sau đó kiểm tra '
                . 'lại đơn trước khi xác nhận. Nếu cần trợ giúp, '
                . 'hãy nhắn “Gặp nhân viên”.',
            'handoff' => false
        ];
    }

    if (
        str_contains($text, 'xin chào') ||
        str_contains($text, 'chào bạn') ||
        str_contains($text, 'hello') ||
        preg_match('/^hi[!. ]*$/u', $text)
    ) {
        return [
            'reply' => 'Xin chào bạn! VIBE STUDIO có thể hỗ trợ '
                . 'thông tin sản phẩm, giá thuê và cách đặt lịch. '
                . 'Bạn muốn tìm hiểu điều gì?',
            'handoff' => false
        ];
    }

    return [
        'reply' => 'Cảm ơn bạn đã liên hệ VIBE STUDIO. '
            . 'Mình chưa có đủ thông tin để trả lời chính xác. '
            . 'Bạn có thể hỏi về giá thuê, cách đặt lịch hoặc '
            . 'nhắn “Gặp nhân viên” để yêu cầu hỗ trợ trực tiếp.',
        'handoff' => false
    ];
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');

    chat_json([
        'success' => false,
        'message' => 'API này chỉ nhận yêu cầu POST.'
    ], 405);
}

$userId = chat_require_customer();
$data = chat_read_input();

$action = trim((string) ($data['action'] ?? 'send'));

try {
    /*
     * Lấy lịch sử hội thoại.
     */
    if ($action === 'history') {
        $conversationId = filter_var(
            $data['conversation_id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (!$conversationId || $conversationId <= 0) {
            chat_json([
                'success' => true,
                'messages' => []
            ]);
        }

        chat_get_owned_conversation(
            $conn,
            $conversationId,
            $userId
        );

        $stmt = $conn->prepare(
            'SELECT CTN_Ma AS id,
                    CTN_NguoiGui AS sender,
                    CTN_NoiDung AS content,
                    CTN_ThoiDiem AS created_at
             FROM chat_tin_nhan
             WHERE CHT_Ma = ?
             ORDER BY CTN_Ma ASC'
        );

        $stmt->execute([$conversationId]);

        chat_json([
            'success' => true,
            'messages' => $stmt->fetchAll(PDO::FETCH_ASSOC)
        ]);
    }

    if ($action !== 'send') {
        chat_json([
            'success' => false,
            'message' => 'Hành động không hợp lệ.'
        ], 400);
    }

    $message = trim((string) ($data['message'] ?? ''));

    if ($message === '') {
        chat_json([
            'success' => false,
            'message' => 'Bạn chưa nhập nội dung tin nhắn.'
        ], 422);
    }

    if (mb_strlen($message, 'UTF-8') > 2000) {
        chat_json([
            'success' => false,
            'message' => 'Tin nhắn không được vượt quá 2000 ký tự.'
        ], 422);
    }

    $conversationId = filter_var(
        $data['conversation_id'] ?? null,
        FILTER_VALIDATE_INT
    );

    $conversationId = $conversationId && $conversationId > 0
        ? $conversationId
        : null;

    $productId = filter_var(
        $data['product_id'] ?? null,
        FILTER_VALIDATE_INT
    );

    $productId = $productId && $productId > 0
        ? $productId
        : null;

    $conn->beginTransaction();

    if ($conversationId === null) {
        $conversationId = chat_create_conversation(
            $conn,
            $userId,
            $productId
        );
    }

    $conversation = chat_get_owned_conversation(
        $conn,
        $conversationId,
        $userId
    );

    if ($conversation['CHT_TrangThai'] === 'da_dong') {
        $conn->rollBack();

        chat_json([
            'success' => false,
            'message' => 'Cuộc hội thoại đã đóng. '
                . 'Vui lòng bắt đầu cuộc trò chuyện mới.'
        ], 409);
    }

    chat_save_message(
        $conn,
        $conversationId,
        'khach',
        $message,
        $userId
    );

    $handoff = (
        $conversation['CHT_CheDo'] === 'nhanvien' ||
        in_array(
            $conversation['CHT_TrangThai'],
            ['cho_nhan_vien', 'dang_ho_tro'],
            true
        )
    );

    if ($handoff) {
        $reply = 'Tin nhắn của bạn đã được lưu vào cuộc hội thoại. '
            . 'Cuộc trò chuyện đang chờ nhân viên phản hồi.';
    } else {
        $answer = chat_make_reply($message);
        $reply = $answer['reply'];
        $handoff = $answer['handoff'];
    }

    if ($handoff) {
        $stmt = $conn->prepare(
            "UPDATE chat_hoi_thoai
             SET CHT_CheDo = 'nhanvien',
                 CHT_TrangThai = 'cho_nhan_vien'
             WHERE CHT_Ma = ? AND ND_Ma = ?"
        );

        $stmt->execute([$conversationId, $userId]);
    }

    /*
     * Chỉ lưu phản hồi tự động khi chatbot đang xử lý.
     * Khi chờ nhân viên, không giả lập tin nhắn của nhân viên.
     */
    if (
        !$conversation['CHT_CheDo'] ||
        $conversation['CHT_CheDo'] !== 'nhanvien'
    ) {
        if (
            !in_array(
                $conversation['CHT_TrangThai'],
                ['cho_nhan_vien', 'dang_ho_tro'],
                true
            )
        ) {
            chat_save_message(
                $conn,
                $conversationId,
                'bot',
                $reply
            );
        }
    }

    $conn->commit();

    chat_json([
        'success' => true,
        'conversation_id' => $conversationId,
        'reply' => $reply,
        'handoff' => $handoff
    ]);
} catch (Throwable $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    error_log('Chat API error: ' . $e->getMessage());

    chat_json([
        'success' => false,
        'message' => 'Không thể xử lý tin nhắn lúc này. '
            . 'Vui lòng thử lại sau.'
    ], 500);
}
