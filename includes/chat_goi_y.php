<?php
/**
 * includes/chat_goi_y.php
 * Gợi ý sản phẩm từ CSDL cho chatbot (không AI, tra cứu theo từ khóa).
 * Dùng chung cho api/chat_api.php (khách) và api/chat_nv_api.php (nhân viên).
 */

const CHAT_TOKEN_SAN_PHAM = '/\n?\[\[sp:(\d+(?:,\d+){0,4})\]\]\s*$/u';
const CHAT_TOKEN_AUTO = '/\n?\[\[auto:cho-nv\]\]\s*$/u';
const CHAT_CHO_NV_GIAY = 300;

const CHAT_TU_KHOA_LOAI = [
    'thiet_bi' => ['máy ảnh', 'may anh', 'camera', 'lens', 'đèn', 'den', 'flash', 'máy', 'may'],
    'trang_phuc' => ['váy', 'vay', 'đầm', 'dam', 'áo', 'ao', 'quần', 'quan', 'trang phục', 'trang phuc', 'bộ đồ', 'bo do', 'outfit'],
    'phong_chup' => ['phòng', 'phong', 'studio', 'phông', 'phong', 'ngoài trời', 'ngoai troi', 'khu chụp', 'khu chup'],
    'phu_kien' => ['phụ kiện', 'phu kien', 'đạo cụ', 'dao cu', 'chân máy', 'chan may', 'chân đèn', 'chan den', 'hắt sáng', 'hat sang', 'tripod', 'phu kien'],
];

const CHAT_TU_THUA = [
    'tôi', 'toi', 'mình', 'minh', 'bạn', 'ban', 'anh', 'chị', 'chi', 'em', 'shop', 'studio',
    'vibe', 'cho', 'thuê', 'thue', 'giá', 'gia', 'bao', 'nhiêu', 'nhieu', 'còn', 'con', 'không', 'khong',
    'muốn', 'muon', 'hỏi', 'hoi', 'cần', 'can', 'có', 'co', 'là', 'la', 'của', 'cua', 'và', 'va', 'với', 'voi',
    'về', 've', 'sản', 'san', 'phẩm', 'pham', 'dịch', 'dich', 'vụ', 'vu', 'ngày', 'ngay', 'giờ', 'gio', 'lượt', 'luot',
    'tiền', 'tien', 'thế', 'the', 'nào', 'nao', 'gì', 'gi', 'giúp', 'giup', 'xin', 'chào', 'chao', 'hello', 'hi',
    'thanks', 'thank', 'ạ', 'a', 'nhỉ', 'nhi', 'nhé', 'nhe', 'vui', 'lòng', 'long', 'được', 'duoc', 'này', 'nay',
    'đó', 'do', 'kia', 'nào', 'nao', 'loại', 'loai', 'một', 'mot', 'cái', 'cai', 'chiếc', 'chiec',
    'chụp', 'chup', 'ảnh', 'anh', 'rẻ', 're', 'tốt', 'tot', 'mới', 'moi', 'cũ', 'cu', 'đẹp', 'dep',
    'hong', 'ko', 'dc', 'đc', 'vs', 'thui', 'thoi',
];

function chat_chuan_hoa_tin(string $tin): string
{
    $tin = mb_strtolower(trim($tin), 'UTF-8');
    $tin = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $tin);
    $tin = preg_replace('/\s+/u', ' ', $tin);
    return trim($tin);
}

function chat_phan_tich_y_dinh(string $tin): ?array
{
    $tinChuan = chat_chuan_hoa_tin($tin);
    if ($tinChuan === '') {
        return null;
    }
    $tu = explode(' ', $tinChuan);

    $loai = null;
    $tenTu = [];
    $daThayTuKhoa = false;

    // 1) Nhận diện loại theo CỤM 2 từ trước
    foreach (CHAT_TU_KHOA_LOAI as $l => $ds) {
        foreach ($ds as $cum) {
            if (mb_strpos($cum, ' ') !== false && mb_strpos($tinChuan, $cum) !== false) {
                if ($loai === null) {
                    $loai = $l;
                }
                $daThayTuKhoa = true;
            }
        }
    }

    // Tập tất cả từ thành phần của mọi cụm loại → không coi là từ khóa tên
    $tatCaThanhPhan = [];
    foreach (CHAT_TU_KHOA_LOAI as $ds) {
        foreach ($ds as $cum) {
            foreach (explode(' ', $cum) as $tp) {
                $tatCaThanhPhan[] = $tp;
            }
        }
    }

    // 2) Xử lý từ đơn
    foreach ($tu as $w) {
        if (mb_strlen($w, 'UTF-8') < 2) {
            continue;
        }
        if (in_array($w, CHAT_TU_THUA, true)) {
            continue;
        }
        $laThanhPhan = in_array($w, $tatCaThanhPhan, true);
        // Từ khóa loại đơn
        $laLoai = false;
        foreach (CHAT_TU_KHOA_LOAI as $l => $ds) {
            if (in_array($w, $ds, true)) {
                if ($loai === null) {
                    $loai = $l;
                }
                $laLoai = true;
                $daThayTuKhoa = true;
                break;
            }
        }
        if ($laLoai) {
            continue;
        }
        // Từ thành phần của cụm loại → bỏ qua (không coi là tên)
        if ($laThanhPhan) {
            continue;
        }
        if (!in_array($w, $tenTu, true) && count($tenTu) < 8) {
            $tenTu[] = $w;
            $daThayTuKhoa = true;
        }
    }

    if (!$daThayTuKhoa) {
        return null;
    }
    return ['loai' => $loai, 'ten' => $tenTu];
}

function chat_tim_san_pham(PDO $conn, string $message): ?array
{
    $yDinh = chat_phan_tich_y_dinh($message);
    if ($yDinh === null) {
        return null;
    }

    $loai = $yDinh['loai'];
    $tenTu = $yDinh['ten'];

    $where = 'dv.DV_DangBan = 1';
    $params = [];
    if ($loai !== null) {
        $where .= ' AND dv.DV_Loai = ?';
        $params[] = $loai;
    }
    if (!empty($tenTu)) {
        $likes = [];
        foreach ($tenTu as $w) {
            $likes[] = 'dv.DV_Ten LIKE ?';
            $params[] = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $w) . '%';
        }
        $where .= ' AND (' . implode(' OR ', $likes) . ')';
    }

    $sql = "SELECT dv.DV_Ma, dv.DV_Ten, dv.DV_Loai, dv.DV_GiaThue, dv.DV_DonViTinh,
                   dv.DV_SoLuong, dv.DV_HinhAnh,
                   tb.TB_Loai AS tb_loai,
                   pc.PC_SucChua AS pc_suc_chua,
                   pk.PK_Loai AS pk_loai
            FROM dichvu dv
            LEFT JOIN thietbi tb ON tb.DV_Ma = dv.DV_Ma
            LEFT JOIN phongchup pc ON pc.DV_Ma = dv.DV_Ma
            LEFT JOIN phukien pk ON pk.DV_Ma = dv.DV_Ma
            WHERE $where
            ORDER BY dv.DV_Ma ASC
            LIMIT 20";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Không fallback: nếu có từ khóa tên mà không khớp sản phẩm nào → báo "không tìm thấy"
    // (tránh gợi ý sai như "máy Toshiba" → hiện máy Canon, hay typo "cannon" → hiện tất cả máy ảnh).

    if (empty($rows)) {
        return [
            'san_pham' => [],
            'token' => '',
            'intro' => 'Mình chưa thấy sản phẩm khớp với yêu cầu. '
                . 'Bạn có thể hỏi theo loại: máy ảnh, trang phục, phòng chụp, phụ kiện, '
                . 'hoặc nhắn "Gặp nhân viên" để được tư vấn trực tiếp.',
        ];
    }

    if (!empty($tenTu)) {
        usort($rows, function (array $a, array $b) use ($tenTu): int {
            $ta = mb_strtolower((string) $a['DV_Ten'], 'UTF-8');
            $tb = mb_strtolower((string) $b['DV_Ten'], 'UTF-8');
            $ca = 0;
            $cb = 0;
            foreach ($tenTu as $w) {
                if (mb_strpos($ta, $w, 0, 'UTF-8') !== false) {
                    $ca++;
                }
                if (mb_strpos($tb, $w, 0, 'UTF-8') !== false) {
                    $cb++;
                }
            }
            if ($ca !== $cb) {
                return $cb <=> $ca;
            }
            return (int) $a['DV_Ma'] <=> (int) $b['DV_Ma'];
        });
    }

    $rows = array_slice($rows, 0, 5);

    $sanPham = [];
    $ids = [];
    foreach ($rows as $r) {
        $id = (int) $r['DV_Ma'];
        $loai = (string) $r['DV_Loai'];
        $anh = trim((string) ($r['DV_HinhAnh'] ?? ''));
        if ($anh === '') {
            $anh = 'default.png';
        }
        $dongPhu = null;
        if ($loai === 'thiet_bi' && !empty($r['tb_loai'])) {
            $dongPhu = (string) $r['tb_loai'];
        } elseif ($loai === 'phong_chup' && $r['pc_suc_chua'] !== null) {
            $dongPhu = 'Tối đa ' . (int) $r['pc_suc_chua'] . ' người';
        } elseif ($loai === 'phu_kien' && !empty($r['pk_loai'])) {
            $dongPhu = (string) $r['pk_loai'];
        }

        $sizes = null;
        if ($loai === 'trang_phuc') {
            $stmt = $conn->prepare(
                "SELECT DVK_Ten, DVK_SoLuong FROM dichvu_kichco
                 WHERE DV_Ma = ? AND DVK_DangHoatDong = 1 ORDER BY DVK_Ma ASC"
            );
            $stmt->execute([$id]);
            $parts = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $s) {
                $parts[] = (string) $s['DVK_Ten'] . ' (' . (int) $s['DVK_SoLuong'] . ')';
            }
            if ($parts) {
                $sizes = implode(', ', $parts);
            }
        }

        $sanPham[] = [
            'id' => $id,
            'ma' => 'DV-' . str_pad((string) $id, 3, '0', STR_PAD_LEFT),
            'ten' => (string) $r['DV_Ten'],
            'loai' => $loai,
            'gia' => (int) $r['DV_GiaThue'],
            'don_vi' => (string) $r['DV_DonViTinh'],
            'so_luong' => $loai === 'trang_phuc' ? null : (int) $r['DV_SoLuong'],
            'sizes' => $sizes,
            'dong_phu' => $dongPhu,
            'anh' => $anh,
        ];
        $ids[] = $id;
    }

    $token = empty($ids) ? '' : "\n[[sp:" . implode(',', $ids) . ']]';
    $intro = 'Mình tìm thấy ' . count($sanPham) . ' sản phẩm phù hợp:';

    return ['san_pham' => $sanPham, 'token' => $token, 'intro' => $intro];
}

function chat_trich_token_san_pham(string $noiDung): array
{
    if (!preg_match(CHAT_TOKEN_SAN_PHAM, $noiDung, $m)) {
        return [];
    }
    $ids = [];
    foreach (explode(',', $m[1]) as $raw) {
        $id = (int) trim($raw);
        if ($id > 0) {
            $ids[] = $id;
        }
    }
    $ids = array_values(array_unique($ids));
    $ids = array_slice($ids, 0, 5);
    return $ids;
}

function chat_xoa_token(string $noiDung): string
{
    return preg_replace(CHAT_TOKEN_SAN_PHAM, '', $noiDung);
}

function chat_xoa_token_auto(string $noiDung): string
{
    return preg_replace(CHAT_TOKEN_AUTO, '', $noiDung);
}

function chat_lay_san_pham_theo_id(PDO $conn, array $ids): array
{
    if (empty($ids)) {
        return [];
    }
    $ids = array_values(array_unique(array_map('intval', $ids)));
    $ids = array_filter($ids, static fn($v) => $v > 0);
    if (empty($ids)) {
        return [];
    }
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $conn->prepare(
        "SELECT dv.DV_Ma, dv.DV_Ten, dv.DV_Loai, dv.DV_GiaThue, dv.DV_DonViTinh,
                dv.DV_SoLuong, dv.DV_HinhAnh,
                tb.TB_Loai AS tb_loai, pc.PC_SucChua AS pc_suc_chua, pk.PK_Loai AS pk_loai
         FROM dichvu dv
         LEFT JOIN thietbi tb ON tb.DV_Ma = dv.DV_Ma
         LEFT JOIN phongchup pc ON pc.DV_Ma = dv.DV_Ma
         LEFT JOIN phukien pk ON pk.DV_Ma = dv.DV_Ma
         WHERE dv.DV_Ma IN ($placeholders) AND dv.DV_DangBan = 1"
    );
    $stmt->execute($ids);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $byId = [];
    foreach ($rows as $r) {
        $byId[(int) $r['DV_Ma']] = $r;
    }

    $result = [];
    foreach ($ids as $id) {
        if (!isset($byId[$id])) {
            continue;
        }
        $r = $byId[$id];
        $loai = (string) $r['DV_Loai'];
        $anh = trim((string) ($r['DV_HinhAnh'] ?? ''));
        if ($anh === '') {
            $anh = 'default.png';
        }
        $dongPhu = null;
        if ($loai === 'thiet_bi' && !empty($r['tb_loai'])) {
            $dongPhu = (string) $r['tb_loai'];
        } elseif ($loai === 'phong_chup' && $r['pc_suc_chua'] !== null) {
            $dongPhu = 'Tối đa ' . (int) $r['pc_suc_chua'] . ' người';
        } elseif ($loai === 'phu_kien' && !empty($r['pk_loai'])) {
            $dongPhu = (string) $r['pk_loai'];
        }

        $sizes = null;
        if ($loai === 'trang_phuc') {
            $st = $conn->prepare(
                "SELECT DVK_Ten, DVK_SoLuong FROM dichvu_kichco
                 WHERE DV_Ma = ? AND DVK_DangHoatDong = 1 ORDER BY DVK_Ma ASC"
            );
            $st->execute([$id]);
            $parts = [];
            foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $s) {
                $parts[] = (string) $s['DVK_Ten'] . ' (' . (int) $s['DVK_SoLuong'] . ')';
            }
            if ($parts) {
                $sizes = implode(', ', $parts);
            }
        }

        $result[] = [
            'id' => (int) $r['DV_Ma'],
            'ma' => 'DV-' . str_pad((string) $r['DV_Ma'], 3, '0', STR_PAD_LEFT),
            'ten' => (string) $r['DV_Ten'],
            'loai' => $loai,
            'gia' => (int) $r['DV_GiaThue'],
            'don_vi' => (string) $r['DV_DonViTinh'],
            'so_luong' => $loai === 'trang_phuc' ? null : (int) $r['DV_SoLuong'],
            'sizes' => $sizes,
            'dong_phu' => $dongPhu,
            'anh' => $anh,
        ];
    }
    return $result;
}

function chat_bo_sung_san_pham(PDO $conn, array $tin): array
{
    $noiDung = (string) ($tin['content'] ?? '');
    $ids = chat_trich_token_san_pham($noiDung);
    if (!empty($ids)) {
        $tin['content'] = chat_xoa_token($noiDung);
        $tin['san_pham'] = chat_lay_san_pham_theo_id($conn, $ids);
    } else {
        $tin['content'] = chat_xoa_token_auto($noiDung);
    }
    return $tin;
}

function chat_an_token_hien_thi(PDO $conn, string $noiDung): string
{
    $ids = chat_trich_token_san_pham($noiDung);
    $noiDung = chat_xoa_token($noiDung);
    $noiDung = chat_xoa_token_auto($noiDung);
    if (!empty($ids)) {
        $sp = chat_lay_san_pham_theo_id($conn, $ids);
        if (!empty($sp)) {
            $ten = [];
            foreach ($sp as $p) {
                $ten[] = $p['ma'] . ' ' . $p['ten'];
            }
            $noiDung = rtrim($noiDung) . "\nBot đã gợi ý: " . implode(', ', $ten) . '.';
        }
    }
    return $noiDung;
}

/**
 * Kiểm tra và (nếu đủ 5 phút) chèn lời xin lỗi tự động cho hội thoại đang chờ NV.
 * Trả về tin nhắn đã chèn (để client hiển thị) hoặc null.
 * Tính thời gian hoàn toàn trong SQL (VN = UTC+7 do lệch múi giờ).
 */
function chat_kiem_tra_cho_nv(PDO $conn, int $conversationId, int $userId): ?array
{
    if ($conversationId <= 0 || $userId <= 0) {
        return null;
    }
    $conn->beginTransaction();
    try {
        $stmt = $conn->prepare(
            'SELECT CHT_Ma, CHT_CheDo, CHT_TrangThai
             FROM chat_hoi_thoai
             WHERE CHT_Ma = ? AND ND_Ma = ?
             FOR UPDATE'
        );
        $stmt->execute([$conversationId, $userId]);
        $hoiThoai = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$hoiThoai || $hoiThoai['CHT_CheDo'] !== 'nhanvien'
            || $hoiThoai['CHT_TrangThai'] === 'da_dong') {
            $conn->rollBack();
            return null;
        }

        $stmt = $conn->prepare(
            "SELECT MIN(t.CTN_Ma) AS first_id, MIN(t.CTN_ThoiDiem) AS first_time
             FROM chat_tin_nhan t
             WHERE t.CHT_Ma = ? AND t.CTN_NguoiGui = 'khach'
               AND t.CTN_Ma > COALESCE(
                   (SELECT MAX(n.CTN_Ma) FROM chat_tin_nhan n
                    WHERE n.CHT_Ma = ? AND n.CTN_NguoiGui = 'nhanvien'), 0)"
        );
        $stmt->execute([$conversationId, $conversationId]);
        $chain = $stmt->fetch(PDO::FETCH_ASSOC);
        if (empty($chain['first_id']) || empty($chain['first_time'])) {
            $conn->rollBack();
            return null;
        }
        $firstId = (int) $chain['first_id'];
        $firstTime = (string) $chain['first_time'];

        $stmt = $conn->prepare(
            "SELECT COUNT(*) FROM chat_tin_nhan
             WHERE CHT_Ma = ? AND CTN_NguoiGui = 'bot'
               AND CTN_Ma > ? AND CTN_NoiDung LIKE '%[[auto:cho-nv]]%'"
        );
        $stmt->execute([$conversationId, $firstId]);
        if ((int) $stmt->fetchColumn() > 0) {
            $conn->rollBack();
            return null;
        }

        $stmt = $conn->prepare(
            "SELECT TIMESTAMPDIFF(SECOND, ?, DATE_ADD(UTC_TIMESTAMP(), INTERVAL 7 HOUR)) AS elapsed"
        );
        $stmt->execute([$firstTime]);
        $elapsed = (int) $stmt->fetchColumn();
        if ($elapsed < CHAT_CHO_NV_GIAY) {
            $conn->rollBack();
            return null;
        }

        // Đọc giờ làm việc từ cau_hinh
        $gioMo = null;
        $gioDong = null;
        $stmt = $conn->query("SELECT CH_Khoa, CH_GiaTri FROM cau_hinh WHERE CH_Khoa IN ('gio_mo_cua','gio_dong_cua')");
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            if ($row['CH_Khoa'] === 'gio_mo_cua') {
                $gioMo = (string) $row['CH_GiaTri'];
            } elseif ($row['CH_Khoa'] === 'gio_dong_cua') {
                $gioDong = (string) $row['CH_GiaTri'];
            }
        }

        $noiDung = 'Xin lỗi bạn, hiện nhân viên đang bận nên chưa kịp phản hồi. '
            . 'Mình đã ghi lại tin nhắn của bạn và nhân viên sẽ liên hệ lại sớm nhất có thể.';
        if ($gioMo !== null && $gioDong !== null && $gioMo !== '' && $gioDong !== '') {
            $noiDung .= ' Giờ làm việc của studio: ' . $gioMo . ' – ' . $gioDong . '.';
        }
        $noiDung .= "\n[[auto:cho-nv]]";

        $now = date('Y-m-d H:i:s');
        $stmt = $conn->prepare(
            "INSERT INTO chat_tin_nhan (CHT_Ma, CTN_NguoiGui, ND_Ma, NV_Ma, CTN_NoiDung, CTN_ThoiDiem, CTN_DaDoc)
             VALUES (?, 'bot', NULL, NULL, ?, ?, 0)"
        );
        $stmt->execute([$conversationId, $noiDung, $now]);
        $id = (int) $conn->lastInsertId();
        $conn->prepare('UPDATE chat_hoi_thoai SET CHT_NgayCapNhat = ? WHERE CHT_Ma = ?')
            ->execute([$now, $conversationId]);
        $conn->commit();

        $row = ['id' => $id, 'sender' => 'bot', 'content' => $noiDung, 'created_at' => $now];
        return chat_bo_sung_san_pham($conn, $row);
    } catch (Throwable $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log('chat_kiem_tra_cho_nv: ' . $e->getMessage());
        return null;
    }
}
