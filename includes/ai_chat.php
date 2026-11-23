<?php
/**
 * includes/ai_chat.php
 * Gọi Gemini (config/ai.local.php) để trả lời chat tự do.
 * Context sản phẩm lấy từ CSDL tại lúc gọi → thêm sản phẩm mới không cần sửa code.
 */

function ai_lay_context_san_pham(PDO $conn): string
{
    $stmt = $conn->query(
        "SELECT dv.DV_Ma, dv.DV_Ten, dv.DV_Loai, dv.DV_GiaThue, dv.DV_DonViTinh,
                dv.DV_SoLuong, dv.DV_HinhAnh
         FROM dichvu dv
         WHERE dv.DV_DangBan = 1
         ORDER BY dv.DV_Ma ASC"
    );
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $lines = [];
    foreach ($rows as $r) {
        $id = (int) $r['DV_Ma'];
        $ten = (string) $r['DV_Ten'];
        $loai = (string) $r['DV_Loai'];
        $gia = (int) $r['DV_GiaThue'];
        $donVi = (string) $r['DV_DonViTinh'];
        $soLuong = (int) $r['DV_SoLuong'];
        $line = "- ID {$id}: {$ten} ({$loai}, {$gia} VND/{$donVi})";

        // Trang phục có size → bổ sung size
        if ($loai === 'trang_phuc') {
            $st = $conn->prepare(
                "SELECT DVK_Ten, DVK_SoLuong FROM dichvu_kichco
                 WHERE DV_Ma = ? AND DVK_DangHoatDong = 1 ORDER BY DVK_Ma ASC"
            );
            $st->execute([$id]);
            $parts = [];
            foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $s) {
                $parts[] = (string) $s['DVK_Ten'] . '(' . (int) $s['DVK_SoLuong'] . ')';
            }
            if ($parts) {
                $line .= ' - size: ' . implode(', ', $parts);
            }
        }
        $lines[] = $line;
    }

    if (empty($lines)) {
        return '(Hiện chưa có sản phẩm nào đang bán.)';
    }
    return implode("\n", $lines);
}

function ai_tra_loi(PDO $conn, string $message): ?string
{
    $configPath = __DIR__ . '/../config/ai.local.php';
    if (!is_file($configPath)) {
        return null;
    }
    $config = require $configPath;
    if (!is_array($config)) {
        return null;
    }
    $apiKey = (string) ($config['api_key'] ?? '');
    $model = (string) ($config['model'] ?? '');
    $url = (string) ($config['api_url'] ?? '');
    if ($apiKey === '' || $model === '' || $url === '') {
        return null;
    }

    $context = ai_lay_context_san_pham($conn);

    $system = "Bạn là trợ lý ảo của VIBE Studio (studio cho thuê thiết bị chụp ảnh, phòng chụp, trang phục, phụ kiện). "
        . "Trả lời bằng tiếng Việt, thân thiện, ngắn gọn (tối đa 4-5 câu). "
        . "Chỉ tư vấn dựa trên danh sách sản phẩm bên dưới; không bịa thêm sản phẩm hay giá. "
        . "Nếu khách muốn gặp người thật hoặc câu hỏi ngoài phạm vi, gợi ý nhắn \"Gặp nhân viên\". "
        . "Khi gợi ý sản phẩm cụ thể, ở cuối câu trả lời thêm một dòng riêng theo đúng định dạng: [[sp:ID1,ID2]] "
        . "(dùng ID SỐ từ danh sách, ví dụ [[sp:4,5]], tối đa 5, không có khoảng trắng và không có chữ DV trong ngoặc). Nếu không gợi ý sản phẩm thì không thêm dòng này. "
        . "Danh sách sản phẩm hiện có:\n" . $context;

    $data = [
        'model' => $model,
        'input' => $system . "\n\nKhách hỏi: " . $message,
        // Giới hạn đầu ra để phản hồi nhanh hơn
        'generation_config' => [
            'max_output_tokens' => 2000,
            'temperature' => 0.6,
        ],
    ];

    $ch = curl_init($url);
    if ($ch === false) {
        return null;
    }
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'x-goog-api-key: ' . $apiKey,
        ],
        CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_UNICODE),
        CURLOPT_TIMEOUT => 20,
    ]);
    $response = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($response === false || $error !== '' || $status < 200 || $status >= 300) {
        error_log('AI chat error: HTTP ' . $status . ' ' . $error);
        return null;
    }

    $result = json_decode($response, true);
    if (!is_array($result)) {
        return null;
    }

    $text = '';
    if (!empty($result['output_text']) && is_string($result['output_text'])) {
        $text = $result['output_text'];
    } else {
        foreach ($result['steps'] ?? [] as $step) {
            foreach ($step['content'] ?? [] as $content) {
                if (($content['type'] ?? '') === 'text' && isset($content['text'])) {
                    $text .= $content['text'];
                }
            }
        }
    }

    $text = trim($text);
    return $text !== '' ? $text : null;
}
