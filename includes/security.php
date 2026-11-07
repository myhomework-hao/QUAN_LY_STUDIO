<?php
// Các hàm bảo mật dùng chung cho khu khách hàng.
// Yêu cầu: config/auth.php phải được nạp trước để session đã bắt đầu.

// Tạo hoặc lấy CSRF token của phiên hiện tại.
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

// In thẻ input ẩn chứa CSRF token vào form.
function csrf_input(): string
{
    return '<input type="hidden" name="csrf" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

// Kiểm tra token gửi lên có khớp token trong phiên không.
// Có thể truyền token trực tiếp (dùng cho API nhận JSON) hoặc để trống để đọc từ $_POST.
function csrf_hop_le($gia_tri = null): bool
{
    if ($gia_tri === null) {
        $gia_tri = $_POST['csrf'] ?? null;
    }

    return is_string($gia_tri)
        && $gia_tri !== ''
        && hash_equals(csrf_token(), $gia_tri);
}

// Lấy địa chỉ IP của người dùng để giới hạn đăng nhập sai.
function dia_chi_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '';
}

// Kiểm tra email hoặc IP đã nhập sai quá số lần cho phép trong 15 phút chưa.
function bi_khoa_dang_nhap(PDO $conn, string $email): bool
{
    $theo_email = $conn->prepare(
        "SELECT COUNT(*) FROM dangnhap_thatbai
         WHERE DNTB_Email = ? AND DNTB_ThoiGian > NOW() - INTERVAL 15 MINUTE"
    );
    $theo_email->execute([mb_strtolower($email)]);

    $theo_ip = $conn->prepare(
        "SELECT COUNT(*) FROM dangnhap_thatbai
         WHERE DNTB_IP = ? AND DNTB_ThoiGian > NOW() - INTERVAL 15 MINUTE"
    );
    $theo_ip->execute([dia_chi_ip()]);

    return (int) $theo_email->fetchColumn() >= 5 || (int) $theo_ip->fetchColumn() >= 20;
}

// Ghi lại một lần đăng nhập sai.
function ghi_dang_nhap_sai(PDO $conn, string $email): void
{
    $conn->prepare("INSERT INTO dangnhap_thatbai (DNTB_Email, DNTB_IP) VALUES (?, ?)")
         ->execute([mb_strtolower($email), dia_chi_ip()]);
}

// Xóa lịch sử sai của email sau khi đăng nhập thành công và dọn bản ghi cũ.
function xoa_dang_nhap_sai(PDO $conn, string $email): void
{
    $conn->prepare(
        "DELETE FROM dangnhap_thatbai
         WHERE DNTB_Email = ? OR DNTB_ThoiGian < NOW() - INTERVAL 1 DAY"
    )->execute([mb_strtolower($email)]);
}
