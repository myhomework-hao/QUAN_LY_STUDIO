<?php
declare(strict_types=1);

require_once '../config/app.php';
require_once '../config/db.php';
require_once '../config/auth.php';
require_once '../includes/security.php';

date_default_timezone_set('Asia/Ho_Chi_Minh');
header('Content-Type: application/json; charset=utf-8');

function nv_json(array $data, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function nv_yeu_cau_nhan_vien(PDO $conn): int
{
    if (!da_dang_nhap()) {
        nv_json(['success' => false, 'message' => 'Bạn cần đăng nhập.'], 401);
    }

    $maNhanVien = ma_nhan_vien_hien_tai($conn);

    if ($maNhanVien === null) {
        nv_json(['success' => false, 'message' => 'Chức năng này dành cho nhân viên đang hoạt động.'], 403);
    }

    return $maNhanVien;
}

function nv_doc_du_lieu(): array
{
    if (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== 0) {
        nv_json(['success' => false, 'message' => 'Yêu cầu không hợp lệ.'], 415);
    }

    $data = json_decode(file_get_contents('php://input') ?: '', true);

    if (!is_array($data)) {
        nv_json(['success' => false, 'message' => 'Dữ liệu gửi lên không hợp lệ.'], 400);
    }

    return $data;
}

function nv_so_nguyen($value): ?int
{
    $so = filter_var($value, FILTER_VALIDATE_INT);
    return $so && $so > 0 ? $so : null;
}

function nv_lay_hoi_thoai(PDO $conn, int $maHoiThoai): array
{
    $stmt = $conn->prepare(
        'SELECT h.CHT_Ma AS id, h.CHT_TieuDe AS title, h.CHT_CheDo AS mode, h.CHT_TrangThai AS status,
                h.NV_Ma AS staff_id, h.CHT_NgayTao AS created_at,
                kh.ND_Ten AS customer_name, kh.ND_Email AS customer_email,
                ns.ND_Ten AS staff_name
         FROM chat_hoi_thoai h
         JOIN nguoidung kh ON kh.ND_Ma = h.ND_Ma
         LEFT JOIN nhanvien nv ON nv.NV_Ma = h.NV_Ma
         LEFT JOIN nguoidung ns ON ns.ND_Ma = nv.ND_Ma
         WHERE h.CHT_Ma = ?'
    );
    $stmt->execute([$maHoiThoai]);
    $hoiThoai = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$hoiThoai) {
        nv_json(['success' => false, 'message' => 'Không tìm thấy cuộc hội thoại.'], 404);
    }

    $hoiThoai['id'] = (int) $hoiThoai['id'];
    $hoiThoai['staff_id'] = $hoiThoai['staff_id'] !== null ? (int) $hoiThoai['staff_id'] : null;

    return $hoiThoai;
}

function nv_ten_nhan_vien(PDO $conn, int $maNhanVien): string
{
    $stmt = $conn->prepare('SELECT nd.ND_Ten FROM nhanvien nv JOIN nguoidung nd ON nd.ND_Ma = nv.ND_Ma WHERE nv.NV_Ma = ?');
    $stmt->execute([$maNhanVien]);
    return (string) $stmt->fetchColumn();
}

function nv_luu_tin(PDO $conn, int $maHoiThoai, string $nguoiGui, string $noiDung, ?int $maNhanVien = null): array
{
    $now = date('Y-m-d H:i:s');
    $stmt = $conn->prepare(
        'INSERT INTO chat_tin_nhan (CHT_Ma, CTN_NguoiGui, ND_Ma, NV_Ma, CTN_NoiDung, CTN_ThoiDiem, CTN_DaDoc)
         VALUES (?, ?, NULL, ?, ?, ?, 0)'
    );
    $stmt->execute([$maHoiThoai, $nguoiGui, $maNhanVien, $noiDung, $now]);
    $id = (int) $conn->lastInsertId();

    $conn->prepare('UPDATE chat_hoi_thoai SET CHT_NgayCapNhat = ? WHERE CHT_Ma = ?')->execute([$now, $maHoiThoai]);

    return ['id' => $id, 'sender' => $nguoiGui, 'content' => $noiDung, 'created_at' => $now];
}

function nv_lay_tin(PDO $conn, int $maHoiThoai, int $sauMa = 0): array
{
    $stmt = $conn->prepare(
        'SELECT CTN_Ma AS id, CTN_NguoiGui AS sender, CTN_NoiDung AS content, CTN_ThoiDiem AS created_at
         FROM chat_tin_nhan
         WHERE CHT_Ma = ? AND CTN_Ma > ?
         ORDER BY CTN_Ma ASC
         LIMIT 300'
    );
    $stmt->execute([$maHoiThoai, $sauMa]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as &$row) {
        $row['id'] = (int) $row['id'];
    }

    return $rows;
}

function nv_tiep_nhan(PDO $conn, int $maHoiThoai, int $maNhanVien): void
{
    $stmt = $conn->prepare(
        "UPDATE chat_hoi_thoai SET NV_Ma = ?, CHT_TrangThai = 'dang_ho_tro', CHT_CheDo = 'nhanvien'
         WHERE CHT_Ma = ? AND CHT_TrangThai IN ('cho_nhan_vien', 'dang_ho_tro')
           AND (CHT_TrangThai = 'cho_nhan_vien' OR NV_Ma IS NULL OR NV_Ma <> ?)"
    );
    $stmt->execute([$maNhanVien, $maHoiThoai, $maNhanVien]);

    if ($stmt->rowCount() === 1) {
        nv_luu_tin($conn, $maHoiThoai, 'bot', 'Nhân viên ' . nv_ten_nhan_vien($conn, $maNhanVien) . ' đã tham gia hỗ trợ bạn.');
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    nv_json(['success' => false, 'message' => 'API này chỉ nhận yêu cầu POST.'], 405);
}

$maNhanVien = nv_yeu_cau_nhan_vien($conn);
$data = nv_doc_du_lieu();

if (!csrf_hop_le($data['csrf'] ?? null)) {
    nv_json(['success' => false, 'message' => 'Phiên làm việc đã hết hạn, vui lòng tải lại trang và thử lại.'], 403);
}

// Sau khi đọc xong session, nhả khóa để poll thường xuyên không chặn trang khác.
session_write_close();

$action = trim((string) ($data['action'] ?? ''));

try {
    if ($action === 'list') {
        $loc = (string) ($data['loc'] ?? 'cho');
        $trangThai = ['cho' => 'cho_nhan_vien', 'dang' => 'dang_ho_tro', 'bot' => 'dang_mo', 'dong' => 'da_dong'][$loc] ?? 'cho_nhan_vien';
        $thuTu = $loc === 'cho' ? 'ASC' : 'DESC';

        $stmt = $conn->prepare(
            "SELECT h.CHT_Ma AS id, h.CHT_TieuDe AS title, h.CHT_TrangThai AS status, h.NV_Ma AS staff_id,
                    h.CHT_NgayCapNhat AS updated_at, kh.ND_Ten AS customer_name, ns.ND_Ten AS staff_name,
                    (SELECT m.CTN_NoiDung FROM chat_tin_nhan m WHERE m.CHT_Ma = h.CHT_Ma ORDER BY m.CTN_Ma DESC LIMIT 1) AS last_message,
                    (SELECT COUNT(*) FROM chat_tin_nhan m WHERE m.CHT_Ma = h.CHT_Ma AND m.CTN_NguoiGui = 'khach' AND m.CTN_DaDoc = 0) AS unread
             FROM chat_hoi_thoai h
             JOIN nguoidung kh ON kh.ND_Ma = h.ND_Ma
             LEFT JOIN nhanvien nv ON nv.NV_Ma = h.NV_Ma
             LEFT JOIN nguoidung ns ON ns.ND_Ma = nv.ND_Ma
             WHERE h.CHT_TrangThai = ?
             ORDER BY h.CHT_NgayCapNhat $thuTu, h.CHT_Ma $thuTu
             LIMIT 50"
        );
        $stmt->execute([$trangThai]);
        $ds = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($ds as &$dong) {
            $dong['id'] = (int) $dong['id'];
            $dong['unread'] = (int) $dong['unread'];
            $dong['staff_id'] = $dong['staff_id'] !== null ? (int) $dong['staff_id'] : null;
            $dong['last_message'] = mb_substr((string) $dong['last_message'], 0, 80);
        }

        $dem = ['cho' => 0, 'dang' => 0];
        $stmt = $conn->query("SELECT CHT_TrangThai AS t, COUNT(*) AS n FROM chat_hoi_thoai WHERE CHT_TrangThai IN ('cho_nhan_vien', 'dang_ho_tro') GROUP BY CHT_TrangThai");
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $d) {
            $dem[$d['t'] === 'cho_nhan_vien' ? 'cho' : 'dang'] = (int) $d['n'];
        }

        nv_json(['success' => true, 'conversations' => $ds, 'counts' => $dem, 'me' => $maNhanVien]);
    }

    $maHoiThoai = nv_so_nguyen($data['conversation_id'] ?? null);

    if ($maHoiThoai === null) {
        nv_json(['success' => false, 'message' => 'Thiếu mã cuộc hội thoại.'], 422);
    }

    if ($action === 'history') {
        $sauMa = max(0, (int) ($data['after_id'] ?? 0));
        $hoiThoai = nv_lay_hoi_thoai($conn, $maHoiThoai);
        $tin = nv_lay_tin($conn, $maHoiThoai, $sauMa);

        $conn->prepare("UPDATE chat_tin_nhan SET CTN_DaDoc = 1 WHERE CHT_Ma = ? AND CTN_NguoiGui = 'khach' AND CTN_DaDoc = 0")
            ->execute([$maHoiThoai]);

        nv_json(['success' => true, 'conversation' => $hoiThoai, 'messages' => $tin, 'me' => $maNhanVien]);
    }

    if ($action === 'nhan') {
        $conn->beginTransaction();
        $hoiThoai = nv_lay_hoi_thoai($conn, $maHoiThoai);

        if (!in_array($hoiThoai['status'], ['cho_nhan_vien', 'dang_ho_tro'], true)) {
            $conn->rollBack();
            nv_json(['success' => false, 'message' => 'Cuộc hội thoại này không cần nhân viên tiếp nhận.'], 409);
        }

        nv_tiep_nhan($conn, $maHoiThoai, $maNhanVien);
        $conn->commit();

        nv_json(['success' => true, 'conversation' => nv_lay_hoi_thoai($conn, $maHoiThoai)]);
    }

    if ($action === 'gui') {
        $noiDung = trim((string) ($data['message'] ?? ''));

        if ($noiDung === '') {
            nv_json(['success' => false, 'message' => 'Bạn chưa nhập nội dung tin nhắn.'], 422);
        }

        if (mb_strlen($noiDung, 'UTF-8') > 2000) {
            nv_json(['success' => false, 'message' => 'Tin nhắn không được vượt quá 2000 ký tự.'], 422);
        }

        $conn->beginTransaction();
        $hoiThoai = nv_lay_hoi_thoai($conn, $maHoiThoai);

        if ($hoiThoai['status'] === 'da_dong') {
            $conn->rollBack();
            nv_json(['success' => false, 'message' => 'Cuộc hội thoại đã đóng.'], 409);
        }

        if ($hoiThoai['status'] === 'dang_mo') {
            $conn->rollBack();
            nv_json(['success' => false, 'message' => 'Khách đang chat với trợ lý tự động, chưa yêu cầu gặp nhân viên.'], 409);
        }

        if ($hoiThoai['status'] === 'cho_nhan_vien') {
            nv_tiep_nhan($conn, $maHoiThoai, $maNhanVien);
            $hoiThoai = nv_lay_hoi_thoai($conn, $maHoiThoai);
        }

        if ($hoiThoai['staff_id'] !== $maNhanVien) {
            $conn->rollBack();
            nv_json(['success' => false, 'message' => 'Nhân viên ' . $hoiThoai['staff_name'] . ' đang hỗ trợ cuộc trò chuyện này.'], 409);
        }

        $tin = nv_luu_tin($conn, $maHoiThoai, 'nhanvien', $noiDung, $maNhanVien);
        $conn->commit();

        nv_json(['success' => true, 'message_sent' => $tin, 'conversation' => nv_lay_hoi_thoai($conn, $maHoiThoai)]);
    }

    if ($action === 'dong') {
        $conn->beginTransaction();
        $hoiThoai = nv_lay_hoi_thoai($conn, $maHoiThoai);

        if ($hoiThoai['status'] === 'da_dong') {
            $conn->rollBack();
            nv_json(['success' => true, 'conversation' => $hoiThoai]);
        }

        $now = date('Y-m-d H:i:s');
        $conn->prepare("UPDATE chat_hoi_thoai SET CHT_TrangThai = 'da_dong', CHT_NgayDong = ?, CHT_NgayCapNhat = ? WHERE CHT_Ma = ?")
            ->execute([$now, $now, $maHoiThoai]);
        nv_luu_tin($conn, $maHoiThoai, 'bot', 'Nhân viên đã kết thúc cuộc trò chuyện. Cảm ơn bạn đã liên hệ VIBE STUDIO!');
        $conn->commit();

        nv_json(['success' => true, 'conversation' => nv_lay_hoi_thoai($conn, $maHoiThoai)]);
    }

    nv_json(['success' => false, 'message' => 'Hành động không hợp lệ.'], 400);
} catch (Throwable $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    error_log('Chat NV API error: ' . $e->getMessage());

    nv_json(['success' => false, 'message' => 'Không thể xử lý lúc này, vui lòng thử lại.'], 500);
}