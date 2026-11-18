<?php

const TRANG_THAI_CHO_DUYET = 'Chờ duyệt';
const TRANG_THAI_DA_DUYET = 'Đã duyệt';
const TRANG_THAI_TU_CHOI = 'Đã từ chối';
const TRANG_THAI_DA_HUY = 'Đã hủy';
const TRANG_THAI_HOAN_THANH = 'Hoàn thành';

// Trạng thái hoàn tiền lưu trên dondatlich (cột DDL_TrangThaiHoanTien)
const HOAN_TIEN_KHONG = 'Không';
const HOAN_TIEN_CHO = 'Chờ hoàn tiền';
const HOAN_TIEN_DA = 'Đã hoàn tiền';

// Ưu đãi khi khách thanh toán 100% online (chuyển khoản QR): giảm thêm 5% trên số tiền sau mã giảm giá.
const GIAM_THANH_TOAN_ONLINE_PHAN_TRAM = 5;

// Số tiền được giảm khi thanh toán 100% online, làm tròn đến đồng.
function giam_thanh_toan_online(float $tong_sau_ma): float
{
    return (float) round($tong_sau_ma * GIAM_THANH_TOAN_ONLINE_PHAN_TRAM / 100);
}

// Ưu đãi combo theo nhóm dịch vụ: chỉ giảm trên tiền các món thuộc combo.
const COMBO_TRANG_PHUC_PHONG_CHUP_PHAN_TRAM = 5;
const COMBO_THIET_BI_PHU_KIEN_PHAN_TRAM = 10;

// Tính tiền giảm combo cho giỏ hàng. Mỗi combo chỉ áp khi giỏ có đủ cả hai nhóm loại.
// Trả về ['tong_giam' => float, 'danh_sach' => [['ma','ten','phan_tram','goc','giam'], ...]]
function tinh_giam_combo(array $cart_items): array
{
    $tien_theo_loai = [];
    foreach ($cart_items as $item) {
        $loai = (string) ($item['loai'] ?? '');
        if ($loai === '') {
            continue;
        }
        $tien_theo_loai[$loai] = ($tien_theo_loai[$loai] ?? 0) + (float) ($item['subtotal'] ?? 0);
    }

    $dinh_nghia = [
        [
            'ma'       => 'trang_phuc_phong_chup',
            'ten'      => 'Combo trang phục + studio',
            'phan_tram'=> COMBO_TRANG_PHUC_PHONG_CHUP_PHAN_TRAM,
            'loai'     => ['trang_phuc', 'phong_chup'],
        ],
        [
            'ma'       => 'thiet_bi_phu_kien',
            'ten'      => 'Combo máy ảnh + phụ kiện',
            'phan_tram'=> COMBO_THIET_BI_PHU_KIEN_PHAN_TRAM,
            'loai'     => ['thiet_bi', 'phu_kien'],
        ],
    ];

    $danh_sach = [];
    $tong_giam = 0.0;

    foreach ($dinh_nghia as $combo) {
        $du_dieu_kien = true;
        $goc = 0.0;
        foreach ($combo['loai'] as $loai) {
            if (empty($tien_theo_loai[$loai])) {
                $du_dieu_kien = false;
                break;
            }
            $goc += (float) $tien_theo_loai[$loai];
        }
        if (!$du_dieu_kien) {
            continue;
        }

        $giam = (float) round($goc * $combo['phan_tram'] / 100);
        if ($giam <= 0) {
            continue;
        }

        $danh_sach[] = [
            'ma'       => $combo['ma'],
            'ten'      => $combo['ten'],
            'phan_tram'=> $combo['phan_tram'],
            'goc'      => $goc,
            'giam'     => $giam,
        ];
        $tong_giam += $giam;
    }

    return ['tong_giam' => $tong_giam, 'danh_sach' => $danh_sach];
}

function ma_don_hien_thi(int $ma_don): string
{
    return 'DDL-' . str_pad((string) $ma_don, 5, '0', STR_PAD_LEFT);
}

function lop_trang_thai(string $trang_thai): string
{
    switch ($trang_thai) {
        case TRANG_THAI_CHO_DUYET:
            return 'cho';
        case TRANG_THAI_DA_DUYET:
            return 'duyet';
        case TRANG_THAI_HOAN_THANH:
            return 'xong';
        default:
            return 'huy';
    }
}

// Danh sách ngân hàng nhận hoàn tiền: mã BIN => tên hiển thị (BIN dùng để tạo ảnh VietQR).
// Khóa số nên khi so sánh với chuỗi từ form phải ép (string).
function danh_sach_ngan_hang(): array
{
    return [
        '970436' => 'Vietcombank (VCB)',
        '970415' => 'VietinBank (CTG)',
        '970418' => 'BIDV',
        '970405' => 'Agribank',
        '970407' => 'Techcombank (TCB)',
        '970422' => 'MBBank (MB)',
        '970416' => 'ACB',
        '970432' => 'VPBank',
        '970423' => 'TPBank',
        '970403' => 'Sacombank (STB)',
        '970437' => 'HDBank',
        '970448' => 'OCB',
        '970429' => 'SCB',
        '970441' => 'VIB',
        '970443' => 'SHB',
        '970426' => 'MSB',
        '970431' => 'Eximbank',
        '970438' => 'BaoViet Bank',
        '970449' => 'LienVietPostBank',
        '970454' => 'VietCapitalBank',
    ];
}

function ten_loai_dich_vu(string $loai): string
{
    return [
        'phong_chup' => 'Phòng chụp',
        'thiet_bi' => 'Máy ảnh - thiết bị',
        'trang_phuc' => 'Trang phục',
        'phu_kien' => 'Phụ kiện - đạo cụ',
    ][$loai] ?? $loai;
}

function dinh_dang_tien($so): string
{
    return number_format((float) $so, 0, ',', '.') . '₫';
}

function dinh_dang_ngay_gio(?string $thoi_diem): string
{
    return $thoi_diem ? date('d/m/Y H:i', strtotime($thoi_diem)) : '';
}

function duong_dan_anh(?string $duong_dan): string
{
    if (!$duong_dan) {
        return '';
    }
    if (preg_match('#^(https?:)?//#', $duong_dan)) {
        return $duong_dan;
    }
    if (strpos($duong_dan, '/') === false) {
        return BASE_URL . 'assets/uploads/' . $duong_dan;
    }
    return BASE_URL . ltrim($duong_dan, '/');
}

// Tính tổng số tiền đã thu thành công của đơn (không tính bản ghi hoàn tiền).
function tong_da_thu_don(PDO $conn, int $ma_don): float
{
    $stmt = $conn->prepare(
        "SELECT COALESCE(SUM(TT_SoTien), 0)
         FROM thanhtoan
         WHERE DDL_Ma = ? AND TT_TrangThai = 'Thành công' AND TT_Loai <> 'Hoàn tiền'"
    );
    $stmt->execute([$ma_don]);
    return (float) $stmt->fetchColumn();
}

// Tính mức hoàn tiền theo chính sách hủy đơn (user/policy.php):
// - Hủy trước từ 48 giờ: hoàn 100% số đã thu.
// - Hủy từ 24 đến dưới 48 giờ: hoàn 50% số đã thu.
// - Hủy dưới 24 giờ: không hoàn.
// $do_studio_huy = true khi nhân viên từ chối/hủy đơn (lỗi phía studio) => luôn hoàn 100%.
// Mốc so sánh: thời điểm hủy với giờ bắt đầu thuê sớm nhất của đơn.
function tinh_muc_hoan_tien(PDO $conn, int $ma_don, bool $do_studio_huy = false): array
{
    $stmt = $conn->prepare(
        "SELECT d.DDL_ThoiDiemHuy, d.DDL_ThoiDiemXuLy,
                (SELECT MIN(c.CTD_BatDau) FROM chitietdon c WHERE c.DDL_Ma = d.DDL_Ma) AS bat_dau
         FROM dondatlich d WHERE d.DDL_Ma = ?"
    );
    $stmt->execute([$ma_don]);
    $dong = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $bat_dau  = $dong['bat_dau'] ?? null;
    // Khách hủy ghi DDL_ThoiDiemHuy; nhân viên từ chối chỉ ghi DDL_ThoiDiemXuLy.
    $moc_huy  = $dong['DDL_ThoiDiemHuy'] ?: ($dong['DDL_ThoiDiemXuLy'] ?: null);
    $tong_thu = tong_da_thu_don($conn, $ma_don);

    $ket_qua = ['ty_le' => 100, 'so_gio' => null, 'bat_dau' => $bat_dau, 'moc_huy' => $moc_huy, 'so_tien' => $tong_thu];

    // Thiếu mốc thời gian (dữ liệu cũ): không đủ căn cứ trừ, giữ mức hoàn đủ.
    if ($bat_dau === null || $moc_huy === null) {
        return $ket_qua;
    }

    $so_gio = (strtotime((string) $bat_dau) - strtotime((string) $moc_huy)) / 3600;
    $ket_qua['so_gio'] = $so_gio;

    if ($do_studio_huy) {
        $ty_le = 100;
    } elseif ($so_gio >= 48) {
        $ty_le = 100;
    } elseif ($so_gio >= 24) {
        $ty_le = 50;
    } else {
        $ty_le = 0;
    }

    $ket_qua['ty_le']  = $ty_le;
    $ket_qua['so_tien'] = (float) round($tong_thu * $ty_le / 100);

    return $ket_qua;
}

// Phát sinh trạng thái 'Chờ hoàn tiền' cho đơn đã có khoản thu (gọi trong transaction).
// Trả về mảng: ['co_hoan_tien' => bool, 'so_tien' => float, 'da_phat_sinh' => bool, 'ty_le' => int|null]
function phat_sinh_cho_hoan_tien(PDO $conn, int $ma_don, bool $do_studio_huy = false): array
{
    $khoa = $conn->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql' ? ' FOR UPDATE' : '';
    $stmt = $conn->prepare(
        "SELECT DDL_MaGiamGia, DDL_TrangThaiHoanTien FROM dondatlich WHERE DDL_Ma = ?" . $khoa
    );
    $stmt->execute([$ma_don]);
    $don = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$don) {
        return ['co_hoan_tien' => false, 'so_tien' => 0.0, 'da_phat_sinh' => false, 'ty_le' => null];
    }

    $tt_hoan = (string) ($don['DDL_TrangThaiHoanTien'] ?? HOAN_TIEN_KHONG);
    $tong_da_thu = tong_da_thu_don($conn, $ma_don);

    // Đã ở trạng thái chờ/đã hoàn: không phát sinh lại, không trả mã lần nữa.
    if ($tt_hoan === HOAN_TIEN_CHO || $tt_hoan === HOAN_TIEN_DA) {
        return ['co_hoan_tien' => true, 'so_tien' => $tong_da_thu, 'da_phat_sinh' => false, 'ty_le' => null];
    }

    if ($tong_da_thu <= 0) {
        return ['co_hoan_tien' => false, 'so_tien' => 0.0, 'da_phat_sinh' => false, 'ty_le' => 0];
    }

    // Áp chính sách 48h/24h; hủy dưới 24 giờ thì không phát sinh hoàn tiền.
    $muc_hoan = tinh_muc_hoan_tien($conn, $ma_don, $do_studio_huy);
    if ($muc_hoan['ty_le'] <= 0 || $muc_hoan['so_tien'] <= 0) {
        return ['co_hoan_tien' => false, 'so_tien' => 0.0, 'da_phat_sinh' => false, 'ty_le' => 0];
    }

    $stmt = $conn->prepare(
        "UPDATE dondatlich SET DDL_TrangThaiHoanTien = ?, DDL_SoTienHoan = ? WHERE DDL_Ma = ?"
    );
    $stmt->execute([HOAN_TIEN_CHO, $muc_hoan['so_tien'], $ma_don]);

    // Trả 1 lượt mã giảm giá (nếu đơn có dùng mã) ngay khi chuyển sang chờ hoàn.
    $ma_giam_gia = trim((string) ($don['DDL_MaGiamGia'] ?? ''));
    if ($ma_giam_gia !== '') {
        $stmt = $conn->prepare(
            "UPDATE ma_giam_gia SET MG_DaDung = CASE WHEN MG_DaDung > 0 THEN MG_DaDung - 1 ELSE 0 END WHERE MG_Code = ?"
        );
        $stmt->execute([$ma_giam_gia]);
    }

    return ['co_hoan_tien' => true, 'so_tien' => $muc_hoan['so_tien'], 'da_phat_sinh' => true, 'ty_le' => $muc_hoan['ty_le']];
}