<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/dich_vu.php';
require_once __DIR__ . '/../includes/security.php';

$id = (int) ($_GET['id'] ?? 37);
// Lấy thông tin gói dịch vụ từ database
$stmt = $conn->prepare("SELECT * FROM dichvu WHERE DV_Ma = ?");
$stmt->execute([$id]);
$goi = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$goi) {
    $goi = [
        'DV_Ma' => $id,
        'DV_Ten' => 'Look 1 - Royal White',
        'DV_GiaThue' => 2500000,
        'DV_DonViTinh' => 'gói',
        'DV_MoTa' => 'Gói dịch vụ chụp ảnh cưới và concept chuyên nghiệp tại VIBE Studio.',
        'DV_HinhAnh' => 'cuoi1.png'
    ];
}

// Kiểm tra loại dịch vụ / trang phục
$ten_lower = mb_strtolower($goi['DV_Ten'] ?? '', 'UTF-8');
$anh_lower = strtolower($goi['DV_HinhAnh'] ?? '');
$loai_dv   = $goi['DV_Loai'] ?? '';

$la_ao_dai_moi = ($id >= 57 && $id <= 64);
$is_ao_dai = $la_ao_dai_moi || ($id >= 69 && $id <= 80)
    || str_contains($ten_lower, 'áo dài')
    || str_contains($ten_lower, 'ao dai')
    || preg_match('/^ao\d+\./', $anh_lower)
    || str_starts_with($anh_lower, 'aodai');

// Nhóm trang phục Hanbok lẻ (ID 81..92 hoặc ảnh phuc1..phuc12)
$is_phuc = ($id >= 81 && $id <= 92)
    || str_starts_with($anh_lower, 'phuc');

// Gói chụp Hanbok (ID 93..100 hoặc DV_Loai = 'goi_chup' với ảnh han1..han8)
$is_goi_hanbok = ($id >= 93 && $id <= 100)
    || ($loai_dv === 'goi_chup' && str_starts_with($anh_lower, 'han'));

$is_hanbok_group = $is_phuc || $is_goi_hanbok;

// Hiển thị bộ chọn size khi xem Áo dài hoặc nhóm Hanbok
$has_size_selector = $is_ao_dai || $is_hanbok_group;

// Hiển thị danh sách chọn mẫu bên dưới cho trang phục lẻ hoặc gói chụp Hanbok
$has_outfit_picker = $is_ao_dai || $is_hanbok_group;

// Số lượng mẫu tối đa được chọn: chỉ được chọn 1 mẫu
$max_outfits = 1;

// Giả lập giá gốc cao hơn để hiển thị hiệu ứng giá cũ gạch ngang và giá giảm
$gia_giao_dien = (int) $goi['DV_GiaThue'];
$gia_cu = $gia_giao_dien + 500000;

// Ảnh chính cố định theo gói dịch vụ
$anh_chinh = !empty($goi['DV_HinhAnh']) ? trim($goi['DV_HinhAnh']) : 'cuoi1.png';

// Danh sách dự phòng 12 mẫu trang phục cưới
$ds_trang_phuc_mac_dinh = [
    ['DV_Ma' => 45, 'DV_Ten' => 'Mẫu 1 - Váy Cưới 1',   'DV_HinhAnh' => 'vay1.png'],
    ['DV_Ma' => 46, 'DV_Ten' => 'Mẫu 2 - Váy Cưới 2',   'DV_HinhAnh' => 'vay2.png'],
    ['DV_Ma' => 47, 'DV_Ten' => 'Mẫu 3 - Váy Cưới 3',   'DV_HinhAnh' => 'vay3.png'],
    ['DV_Ma' => 48, 'DV_Ten' => 'Mẫu 4 - Váy Cưới 4',   'DV_HinhAnh' => 'vay4.png'],
    ['DV_Ma' => 49, 'DV_Ten' => 'Mẫu 5 - Váy Cưới 5',   'DV_HinhAnh' => 'vay5.png'],
    ['DV_Ma' => 50, 'DV_Ten' => 'Mẫu 6 - Váy Cưới 6',   'DV_HinhAnh' => 'vay6.png'],
    ['DV_Ma' => 51, 'DV_Ten' => 'Mẫu 7 - Váy Cưới 7',   'DV_HinhAnh' => 'vay7.png'],
    ['DV_Ma' => 52, 'DV_Ten' => 'Mẫu 8 - Váy Cưới 8',   'DV_HinhAnh' => 'vay9.png'],
    ['DV_Ma' => 53, 'DV_Ten' => 'Mẫu 9 - Váy Cưới 9',   'DV_HinhAnh' => 'vay10.png'],
    ['DV_Ma' => 54, 'DV_Ten' => 'Mẫu 10 - Váy Cưới 10', 'DV_HinhAnh' => 'vay10_2.png'],
    ['DV_Ma' => 55, 'DV_Ten' => 'Mẫu 11 - Váy Cưới 11', 'DV_HinhAnh' => 'vay11.png'],
    ['DV_Ma' => 56, 'DV_Ten' => 'Mẫu 12 - Váy Cưới 12', 'DV_HinhAnh' => 'vay12.png'],
];

// Danh sách dự phòng 12 mẫu Áo dài (DV_Ma 69..80, ảnh ao1.png -> ao12.png)
$ds_ao_dai_mac_dinh = [
    ['DV_Ma' => 69, 'DV_Ten' => 'Áo Dài 1 - Gấm Đỏ Hoàng Gia',    'DV_HinhAnh' => 'ao1.png'],
    ['DV_Ma' => 70, 'DV_Ten' => 'Áo Dài 2 - Lụa Trắng Thanh Lịch', 'DV_HinhAnh' => 'ao2.png'],
    ['DV_Ma' => 71, 'DV_Ten' => 'Áo Dài 3 - Cổ Yếm Cách Tân',      'DV_HinhAnh' => 'ao3.png'],
    ['DV_Ma' => 72, 'DV_Ten' => 'Áo Dài 4 - Thêu Hoa Sen',         'DV_HinhAnh' => 'ao4.png'],
    ['DV_Ma' => 73, 'DV_Ten' => 'Áo Dài 5 - Cặp Đôi Gia Tiên',     'DV_HinhAnh' => 'ao5.png'],
    ['DV_Ma' => 74, 'DV_Ten' => 'Áo Dài 6 - Pastel Dịu Dàng',      'DV_HinhAnh' => 'ao6.png'],
    ['DV_Ma' => 75, 'DV_Ten' => 'Áo Dài 7 - Nhung Sang Trọng',     'DV_HinhAnh' => 'ao7.png'],
    ['DV_Ma' => 76, 'DV_Ten' => 'Áo Dài 8 - Nàng Thơ Phố Cổ',      'DV_HinhAnh' => 'ao8.png'],
    ['DV_Ma' => 77, 'DV_Ten' => 'Áo Dài 9 - Ren Hoa Tinh Xảo',     'DV_HinhAnh' => 'ao9.png'],
    ['DV_Ma' => 78, 'DV_Ten' => 'Áo Dài 10 - Tơ Tằm Ánh Kim',     'DV_HinhAnh' => 'ao10.png'],
    ['DV_Ma' => 79, 'DV_Ten' => 'Áo Dài 11 - Ngọc Trai Thanh Tú',   'DV_HinhAnh' => 'ao11.png'],
    ['DV_Ma' => 80, 'DV_Ten' => 'Áo Dài 12 - Hoàng Hôn Hiện Đại',  'DV_HinhAnh' => 'ao12.png'],
];

// Danh sách dự phòng 8 mẫu Áo dài mới (DV_Ma 57..64, ảnh aodai1.png -> aodai8.png)
$ds_ao_dai_moi_mac_dinh = [
    ['DV_Ma' => 57, 'DV_Ten' => 'Tĩnh Mịch Hoa Loa Kèn', 'DV_HinhAnh' => 'aodai1.png'],
    ['DV_Ma' => 58, 'DV_Ten' => 'Lụa Mây Bồng Bềnh',     'DV_HinhAnh' => 'aodai2.png'],
    ['DV_Ma' => 59, 'DV_Ten' => 'Nắng Sớm Sài Gòn',       'DV_HinhAnh' => 'aodai3.png'],
    ['DV_Ma' => 60, 'DV_Ten' => 'Áo Dài Cổ Điển',         'DV_HinhAnh' => 'aodai4.png'],
    ['DV_Ma' => 61, 'DV_Ten' => 'Hoa Lệ Phượng Đỏ',      'DV_HinhAnh' => 'aodai5.png'],
    ['DV_Ma' => 62, 'DV_Ten' => 'Tơ Lụa Thời Gian',       'DV_HinhAnh' => 'aodai6.png'],
    ['DV_Ma' => 63, 'DV_Ten' => 'Trăng Khuyết Lụa Mỏng',  'DV_HinhAnh' => 'aodai7.png'],
    ['DV_Ma' => 64, 'DV_Ten' => 'Rạng Đông Tươi Sáng',    'DV_HinhAnh' => 'aodai8.png'],
];

// Danh sách dự phòng 12 mẫu Hanbok / Trang phục (DV_Ma 81..92, ảnh phuc1.png -> phuc12.png)
$ds_phuc_mac_dinh = [
    ['DV_Ma' => 81, 'DV_Ten' => 'Hanbok 1 - Hỷ Phục Gấm Trắng',   'DV_HinhAnh' => 'phuc1.png'],
    ['DV_Ma' => 82, 'DV_Ten' => 'Hanbok 2 - Ánh Trăng Thuần Khiết', 'DV_HinhAnh' => 'phuc2.png'],
    ['DV_Ma' => 83, 'DV_Ten' => 'Hanbok 3 - Tím Mộng Nàng Thơ',    'DV_HinhAnh' => 'phuc3.png'],
    ['DV_Ma' => 84, 'DV_Ten' => 'Hanbok 4 - Sương Mai Thanh Tân',  'DV_HinhAnh' => 'phuc4.png'],
    ['DV_Ma' => 85, 'DV_Ten' => 'Hanbok 5 - Đào Phai Giao Duyên',   'DV_HinhAnh' => 'phuc5.png'],
    ['DV_Ma' => 86, 'DV_Ten' => 'Hanbok 6 - Xanh Lam Lưu Luyến',   'DV_HinhAnh' => 'phuc6.png'],
    ['DV_Ma' => 87, 'DV_Ten' => 'Hanbok 7 - Họa Tiết Thủy Mặc',    'DV_HinhAnh' => 'phuc7.png'],
    ['DV_Ma' => 88, 'DV_Ten' => 'Hanbok 8 - Khổng Tước Hạc Tiên',  'DV_HinhAnh' => 'phuc8.png'],
    ['DV_Ma' => 89, 'DV_Ten' => 'Hanbok 9 - Sơn Thủy Hữu Tình',    'DV_HinhAnh' => 'phuc9.png'],
    ['DV_Ma' => 90, 'DV_Ten' => 'Hanbok 10 - Dạ Khúc Huyền Bí',    'DV_HinhAnh' => 'phuc10.png'],
    ['DV_Ma' => 91, 'DV_Ten' => 'Hanbok 11 - Nắng Mai Kiến Trúc',  'DV_HinhAnh' => 'phuc11.png'],
    ['DV_Ma' => 92, 'DV_Ten' => 'Hanbok 12 - Giao Thoa Lịch Lãm',  'DV_HinhAnh' => 'phuc12.png'],
];

// Lấy danh sách trang phục tương ứng
$ds_trang_phuc = [];
try {
    if ($is_hanbok_group) {
        $stmt_tp = $conn->prepare("
            SELECT dv.DV_Ma, dv.DV_Ten, dv.DV_HinhAnh
            FROM trangphuc tp
            INNER JOIN dichvu dv ON dv.DV_Ma = tp.DV_Ma
            WHERE dv.DV_Loai = 'trang_phuc'
              AND dv.DV_DangBan = 1
              AND tp.TP_TinhTrang = 'Tốt'
              AND dv.DV_Ma BETWEEN 81 AND 92
            ORDER BY dv.DV_Ma ASC
            LIMIT 12
        ");
    } elseif ($is_ao_dai) {
        $stmt_tp = $conn->prepare("
            SELECT dv.DV_Ma, dv.DV_Ten, dv.DV_HinhAnh
            FROM trangphuc tp
            INNER JOIN dichvu dv ON dv.DV_Ma = tp.DV_Ma
            WHERE dv.DV_Loai = 'trang_phuc'
              AND dv.DV_DangBan = 1
              AND tp.TP_TinhTrang = 'Tốt'
              AND dv.DV_Ma BETWEEN 69 AND 80
            ORDER BY dv.DV_Ma ASC
            LIMIT 12
        ");
    } else {
        $stmt_tp = $conn->prepare("
            SELECT dv.DV_Ma, dv.DV_Ten, dv.DV_HinhAnh
            FROM trangphuc tp
            INNER JOIN dichvu dv ON dv.DV_Ma = tp.DV_Ma
            WHERE dv.DV_Loai = 'trang_phuc'
              AND dv.DV_DangBan = 1
              AND tp.TP_TinhTrang = 'Tốt'
              AND (dv.DV_HinhAnh LIKE 'vay%' OR (dv.DV_Ma >= 45 AND dv.DV_Ma <= 56))
            ORDER BY dv.DV_Ma ASC
            LIMIT 12
        ");
    }
    $stmt_tp->execute();
    $ds_trang_phuc = $stmt_tp->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $ds_trang_phuc = [];
}

if (empty($ds_trang_phuc)) {
    if ($is_hanbok_group) {
        $ds_trang_phuc = $ds_phuc_mac_dinh;
    } elseif ($la_ao_dai_moi) {
        $ds_trang_phuc = $ds_ao_dai_moi_mac_dinh;
    } elseif ($is_ao_dai) {
        $ds_trang_phuc = $ds_ao_dai_mac_dinh;
    } else {
        $ds_trang_phuc = $ds_trang_phuc_mac_dinh;
    }
}

// Danh sách Size Nữ (S - L) và Size Nam (S - XXL)
$ds_size_nu = [];
$ds_size_nam = [];
if ($has_size_selector) {
    if ($is_hanbok_group) {
        $ds_size_nu = [
            ['DVK_Ten' => 'S', 'DVK_SoLuong' => 10],
            ['DVK_Ten' => 'M', 'DVK_SoLuong' => 10],
            ['DVK_Ten' => 'L', 'DVK_SoLuong' => 10],
        ];
    } else {
        try {
            $stmt_sz = $conn->prepare("
                SELECT DVK_Ten, DVK_SoLuong
                FROM dichvu_kichco
                WHERE DV_Ma = ? AND DVK_DangHoatDong = 1
                ORDER BY FIELD(DVK_Ten, 'S', 'M', 'L', 'XL', 'XXL'), DVK_Ma ASC
            ");
            $stmt_sz->execute([$goi['DV_Ma']]);
            $ds_size_nu = $stmt_sz->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $ds_size_nu = [];
        }

        if (empty($ds_size_nu)) {
            $ds_size_nu = [
                ['DVK_Ten' => 'S', 'DVK_SoLuong' => 10],
                ['DVK_Ten' => 'M', 'DVK_SoLuong' => 10],
                ['DVK_Ten' => 'L', 'DVK_SoLuong' => 10],
            ];
        }
    }

    $ds_size_nam = [
        ['DVK_Ten' => 'S',   'DVK_SoLuong' => 10],
        ['DVK_Ten' => 'M',   'DVK_SoLuong' => 10],
        ['DVK_Ten' => 'L',   'DVK_SoLuong' => 10],
        ['DVK_Ten' => 'XL',  'DVK_SoLuong' => 10],
        ['DVK_Ten' => 'XXL', 'DVK_SoLuong' => 10],
    ];
}

$tieu_de_trang = $goi['DV_Ten'] . ' - VIBE Studio';
$trang_hien_tai = 'dich-vu';
$css_rieng = [
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css',
    BASE_URL . 'assets/css/at.css?v=' . time(),
    BASE_URL . 'assets/css/dich-vu.css?v=' . time()
];
$js_rieng = $has_outfit_picker ? [] : [
    BASE_URL . 'assets/js/at-chitiet.js?v=' . (is_file(__DIR__ . '/../assets/js/at-chitiet.js') ? filemtime(__DIR__ . '/../assets/js/at-chitiet.js') : time())
];

require_once __DIR__ . '/../includes/header.php';
?>

<main class="services-detail-page">

    <section class="detail-breadcrumb" style="margin-bottom: 25px;">
        <a href="dich-vu.php">Dịch vụ &amp; Gói chụp</a>
        <i class="fa-solid fa-angle-right"></i>
        <span><?= h($goi['DV_Ten']) ?></span>
    </section>

    <section class="product-detail">

        <!-- CỘT TRÁI: Ảnh chính cố định -->
        <div class="detail-gallery-col">
            <div class="service-detail-image">
                <img id="mainImage" src="<?= BASE_URL ?>assets/uploads/<?= h($anh_chinh) ?>" alt="<?= h($goi['DV_Ten']) ?>">
            </div>
        </div>

        <!-- CỘT PHẢI: Khung thông tin chi tiết -->
        <div class="detail-info-card" style="background: #fff; padding: 35px; border-radius: 12px; border: 1px solid #eaeaea; box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
            <div class="product-tags" style="margin-bottom: 10px;">
                <span class="product-category-tag" style="background: #f0f0f0; padding: 4px 10px; border-radius: 4px; font-size: 11px; font-weight: 600;">
                    <?= $has_size_selector ? 'Trang phục &amp; Gói chụp' : 'Dịch vụ chụp ảnh' ?>
                </span>
                <span class="product-status-tag" style="background: #e6f4ea; color: #137333; padding: 4px 10px; border-radius: 4px; font-size: 11px; font-weight: 600;">Sẵn sàng</span>
            </div>

            <h1 class="detail-name" style="font-size: 28px; font-family: 'Playfair Display', serif; color: #111; margin-bottom: 12px;"><?= h($goi['DV_Ten']) ?></h1>

            <div class="product-vendor" style="display: flex; align-items: center; gap: 8px; margin-bottom: 15px; color: #555; font-size: 13px;">
                <i class="fa-solid fa-camera"></i>
                <span>VIBE STUDIO</span>
                <i class="fa-solid fa-circle-check check-icon" style="color: #1a73e8;"></i>
            </div>

            <div class="price-box" style="display: flex; align-items: baseline; gap: 12px; margin-bottom: 15px;">
                <span class="detail-price" style="font-size: 24px; font-weight: 700; color: #111;"><?= number_format($gia_giao_dien, 0, ',', '.') ?>đ</span>
                <span class="old-price" style="font-size: 16px; color: #999; text-decoration: line-through;"><?= number_format($gia_cu, 0, ',', '.') ?>đ</span>
            </div>

            <p class="detail-description" style="color: #666; font-size: 14px; line-height: 1.6; margin-bottom: 20px;"><?= h($goi['DV_MoTa']) ?></p>

            <?php if ($has_size_selector): ?>
            <!-- BỘ CHỌN SIZE NỮ (S - L) & SIZE NAM (S - XXL) -->
            <div class="ao-dai-size-box" style="margin-bottom: 20px; display: flex; flex-direction: column; gap: 14px;">
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="font-size: 13px; font-weight: 600; color: #111;">Size Nữ :</span>
                        <span id="selectedSizeNuLabel" style="font-size: 12px; color: #666;">Chưa chọn</span>
                    </div>
                    <div id="aoDaiSizeNuGroup" style="display: flex; flex-wrap: wrap; gap: 10px;">
                        <?php foreach ($ds_size_nu as $sz): ?>
                            <?php
                            $ten_size = trim((string) $sz['DVK_Ten']);
                            $sl_size = (int) ($sz['DVK_SoLuong'] ?? 0);
                            $het_hang = $sl_size <= 0;
                            ?>
                            <button
                                type="button"
                                class="size-nu-btn"
                                data-size="<?= h($ten_size) ?>"
                                <?= $het_hang ? 'disabled' : '' ?>
                                style="min-width: 54px; padding: 8px 14px; border-radius: 6px; border: 1.5px solid #d4d4d4; background: <?= $het_hang ? '#f3f3f3' : '#fff' ?>; color: <?= $het_hang ? '#aaa' : '#111' ?>; font-size: 13px; font-weight: 600; cursor: <?= $het_hang ? 'not-allowed' : 'pointer' ?>; transition: all 0.2s ease;"
                            >
                                <?= h($ten_size) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="font-size: 13px; font-weight: 600; color: #111;">Size Nam :</span>
                        <span id="selectedSizeNamLabel" style="font-size: 12px; color: #666;">Chưa chọn</span>
                    </div>
                    <div id="aoDaiSizeNamGroup" style="display: flex; flex-wrap: wrap; gap: 10px;">
                        <?php foreach ($ds_size_nam as $sz): ?>
                            <?php
                            $ten_size = trim((string) $sz['DVK_Ten']);
                            $sl_size = (int) ($sz['DVK_SoLuong'] ?? 0);
                            $het_hang = $sl_size <= 0;
                            ?>
                            <button
                                type="button"
                                class="size-nam-btn"
                                data-size="<?= h($ten_size) ?>"
                                <?= $het_hang ? 'disabled' : '' ?>
                                style="min-width: 54px; padding: 8px 14px; border-radius: 6px; border: 1.5px solid #d4d4d4; background: <?= $het_hang ? '#f3f3f3' : '#fff' ?>; color: <?= $het_hang ? '#aaa' : '#111' ?>; font-size: 13px; font-weight: 600; cursor: <?= $het_hang ? 'not-allowed' : 'pointer' ?>; transition: all 0.2s ease;"
                            >
                                <?= h($ten_size) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="detail-information" style="border-top: 1px solid #eee; border-bottom: 1px solid #eee; padding: 15px 0; margin-bottom: 25px;">
                <div class="information-item" style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 8px;">
                    <span style="color: #666;">Hình thức</span>
                    <strong style="color: #111;"><?= $has_size_selector ? 'Thuê trang phục / Gói chụp' : 'Thuê trọn gói' ?></strong>
                </div>
                <div class="information-item" style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 8px;">
                    <span style="color: #666;">Địa điểm</span>
                    <strong style="color: #111;">TP.Cần Thơ</strong>
                </div>
                <div class="information-item" style="display: flex; justify-content: space-between; font-size: 13px;">
                    <span style="color: #666;">Cam kết</span>
                    <strong style="color: #111;">Ekip chuyên nghiệp, hỗ trợ tận tâm</strong>
                </div>
            </div>

            <form action="cart.php" method="POST" id="bookingForm">
                <?= csrf_input() ?>
                <input type="hidden" name="service_id" value="<?= (int)$goi['DV_Ma'] ?>">
                <input type="hidden" name="so_luong" value="1">
                <input type="hidden" id="selectedOutfitId" name="trang_phuc_id" value="">
                <input type="hidden" id="selectedOutfitInput" name="trang_phuc" value="">
                <input type="hidden" id="selectedOutfitTen" name="trang_phuc_ten" value="">
                <input type="hidden" id="selectedSizeNuInput" name="kich_co_nu" value="">
                <input type="hidden" id="selectedSizeNamInput" name="kich_co_nam" value="">
                <input type="hidden" id="selectedSizeInput" name="kich_co" value="">

                <div class="detail-buttons" style="display: flex; gap: 12px;">
                    <button type="submit" class="main-button rental-button" style="flex: 2; padding: 14px; background: #111; color: #fff; border: none; border-radius: 6px; font-weight: 600; letter-spacing: 1px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <i class="fa-solid fa-bag-shopping"></i> Đặt thuê ngay
                    </button>
                    <a href="dich-vu.php" class="back-button" style="flex: 1; padding: 14px; background: #f5f5f5; color: #333; text-decoration: none; border-radius: 6px; font-weight: 500; text-align: center; display: flex; align-items: center; justify-content: center; gap: 6px;">
                        <i class="fa-solid fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </form>
        </div>
    </section>

    <!-- PHẦN RIÊNG BÊN DƯỚI: chọn mẫu trang phục -->
    <section class="outfit-selection-section" style="margin-top: 40px; background: #fff; padding: 28px; border-radius: 12px; border: 1px solid #eaeaea; box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 10px; margin-bottom: 20px;">
            <div>
                <h2 style="font-size: 20px; font-family: 'Playfair Display', serif; color: #111; margin: 0;">
                    <?= $has_outfit_picker ? 'Lựa chọn mẫu trang phục' : 'Lựa chọn trang phục cưới' ?>
                </h2>
                <p style="color: #666; font-size: 13px; margin: 4px 0 0;">
                    <?php if ($is_hanbok_group): ?>
                        Chỉ được chọn 1 mẫu Hanbok; chọn size nữ , size nam  hoặc cả 2 tùy ý
                    <?php elseif ($has_outfit_picker): ?>
                        Chỉ được chọn 1 mẫu trang phục; chọn size nam, nữ hoặc cả 2 tùy ý
                    <?php else: ?>
                        Chỉ được chọn 1 mẫu váy cưới / trang phục cưới phù hợp cho buổi chụp của bạn
                    <?php endif; ?>
                </p>
            </div>
            <div style="font-size: 13px; color: #444;">
                Đang chọn: <strong id="selectedOutfitLabel" style="color: #111;">Chưa chọn (mặc định theo gói)</strong>
            </div>
        </div>

        <div class="outfit-grid" style="display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 14px;">
            <?php foreach ($ds_trang_phuc as $tp): ?>
                <button
                    type="button"
                    class="outfit-card"
                    data-outfit-id="<?= (int)$tp['DV_Ma'] ?>"
                    data-outfit="<?= h($tp['DV_HinhAnh']) ?>"
                    data-label="<?= h($tp['DV_Ten']) ?>"
                    style="background: #fafafa; border: 2px solid #e5e5e5; border-radius: 10px; padding: 8px; cursor: pointer; text-align: center; transition: all 0.2s ease; display: flex; flex-direction: column; gap: 8px;"
                >
                    <div style="width: 100%; aspect-ratio: 3 / 4; overflow: hidden; border-radius: 6px; background: #eee;">
                        <img
                            src="<?= BASE_URL ?>assets/uploads/<?= h($tp['DV_HinhAnh']) ?>"
                            alt="<?= h($tp['DV_Ten']) ?>"
                            style="width: 100%; height: 100%; object-fit: cover; display: block;"
                        >
                    </div>
                    <span style="font-size: 12px; font-weight: 600; color: #222; line-height: 1.35;">
                        <?= h($tp['DV_Ten']) ?>
                    </span>
                </button>
            <?php endforeach; ?>
        </div>
    </section>

</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const sizeNuInput = document.getElementById('selectedSizeNuInput');
    const sizeNamInput = document.getElementById('selectedSizeNamInput');
    const sizeCombinedInput = document.getElementById('selectedSizeInput');
    const sizeNuLabel = document.getElementById('selectedSizeNuLabel');
    const sizeNamLabel = document.getElementById('selectedSizeNamLabel');

    const outfitIdInput = document.getElementById('selectedOutfitId');
    const outfitInput = document.getElementById('selectedOutfitInput');
    const outfitTenInput = document.getElementById('selectedOutfitTen');
    const outfitLabel = document.getElementById('selectedOutfitLabel');
    const outfitCards = document.querySelectorAll('.outfit-card');

    const MAX_OUTFITS = <?= (int)$max_outfits ?>;
    let selectedOutfits = [];

    function syncCombinedSize() {
        const parts = [];
        if (sizeNuInput && sizeNuInput.value) parts.push('Nữ: ' + sizeNuInput.value);
        if (sizeNamInput && sizeNamInput.value) parts.push('Nam: ' + sizeNamInput.value);
        if (sizeCombinedInput) sizeCombinedInput.value = parts.join(' | ');
    }

    function bindSizeGroup(selector, hiddenInput, labelEl, prefixText) {
        const buttons = document.querySelectorAll(selector);
        if (!buttons.length || !hiddenInput || !labelEl) return;

        buttons.forEach((btn) => {
            btn.addEventListener('click', () => {
                const isSelected = btn.dataset.selected === '1';

                buttons.forEach((b) => {
                    b.dataset.selected = '0';
                    b.style.background = '#fff';
                    b.style.color = '#111';
                    b.style.borderColor = '#d4d4d4';
                });

                if (!isSelected) {
                    btn.dataset.selected = '1';
                    btn.style.background = '#111';
                    btn.style.color = '#fff';
                    btn.style.borderColor = '#111';
                    hiddenInput.value = btn.dataset.size;
                    labelEl.textContent = prefixText + ' ' + btn.dataset.size;
                } else {
                    hiddenInput.value = '';
                    labelEl.textContent = 'Chưa chọn';
                }

                syncCombinedSize();
            });
        });
    }

    function renderOutfitSelection() {
        outfitCards.forEach((card) => {
            const idVal = card.dataset.outfitId;
            const active = selectedOutfits.some((item) => item.id === idVal);
            card.dataset.selected = active ? '1' : '0';
            card.style.borderColor = active ? '#111' : '#e5e5e5';
            card.style.background = active ? '#f0f0f0' : '#fafafa';
            card.style.boxShadow = active ? '0 4px 12px rgba(0,0,0,0.08)' : 'none';
        });

        if (selectedOutfits.length === 0) {
            outfitIdInput.value = '';
            outfitInput.value = '';
            outfitTenInput.value = '';
            outfitLabel.textContent = 'Chưa chọn (mặc định theo gói)';
        } else {
            outfitIdInput.value = selectedOutfits.map((item) => item.id).join('|');
            outfitInput.value = selectedOutfits.map((item) => item.file).join('|');
            outfitTenInput.value = selectedOutfits.map((item) => item.label).join('|');
            outfitLabel.textContent = selectedOutfits.map((item) => item.label).join(' + ');
        }
    }

    outfitCards.forEach((card) => {
        card.addEventListener('click', () => {
            const item = {
                id: card.dataset.outfitId,
                file: card.dataset.outfit,
                label: card.dataset.label
            };
            const existsIndex = selectedOutfits.findIndex((x) => x.id === item.id);

            if (existsIndex !== -1) {
                selectedOutfits.splice(existsIndex, 1);
                renderOutfitSelection();
                return;
            }

            if (MAX_OUTFITS === 1) {
                selectedOutfits = [item];
                renderOutfitSelection();
                return;
            }

            if (selectedOutfits.length >= MAX_OUTFITS) {
                return;
            }

            selectedOutfits.push(item);
            renderOutfitSelection();
        });
    });

    bindSizeGroup('#aoDaiSizeNuGroup .size-nu-btn:not([disabled])', sizeNuInput, sizeNuLabel, 'Size');
    bindSizeGroup('#aoDaiSizeNamGroup .size-nam-btn:not([disabled])', sizeNamInput, sizeNamLabel, 'Size');
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
