<?php
require_once '../config/app.php';
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../includes/cart_helper.php';
require_once '../includes/coupon_helper.php';
require_once '../includes/donhang.php';
require_once '../includes/security.php';

yeu_cau_vai_tro('khachhang');

const SO_LUONG_TOI_DA_MOI_DICH_VU = 20;

function ten_don_vi_gio_hang(string $don_vi): string
{
    return nhan_don_vi($don_vi);
}

function so_luong_toi_da(PDO $conn, int $ma_dich_vu): int
{
    $stmt = $conn->prepare("SELECT DV_SoLuong FROM dichvu WHERE DV_Ma = ? AND DV_DangBan = 1");
    $stmt->execute([$ma_dich_vu]);
    $so = $stmt->fetchColumn();
    return $so === false ? 0 : min(SO_LUONG_TOI_DA_MOI_DICH_VU, (int) $so);
}

function co_cau_hinh_size_db(PDO $conn, int $ma_dich_vu): bool
{
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM dichvu_kichco WHERE DV_Ma = ? AND DVK_DangHoatDong = 1");
        $stmt->execute([$ma_dich_vu]);
        return (int) $stmt->fetchColumn() > 0;
    } catch (Throwable $e) {
        return false;
    }
}

// Chuẩn hóa danh sách trang phục về tối đa 2 mẫu, phân cách bằng "|".
function chuan_hoa_ds_trang_phuc(string $raw, bool $chi_so = false): string
{
    $phan = preg_split('/[,|]/', $raw) ?: [];
    $phan = array_values(array_filter(array_map('trim', $phan), 'strlen'));
    if ($chi_so) {
        $phan = array_values(array_filter($phan, static function ($giatri) {
            return ctype_digit($giatri) && (int) $giatri > 0;
        }));
    }
    $phan = array_values(array_unique($phan));
    return implode('|', array_slice($phan, 0, 2));
}

// Gợi ý món còn thiếu để đủ điều kiện combo, kèm liên kết lọc đúng loại ở trang sản phẩm.
function goi_y_combo(array $cart_items): array
{
    $co_loai = [];
    foreach ($cart_items as $item) {
        $loai = (string) ($item['loai'] ?? '');
        if ($loai !== '') {
            $co_loai[$loai] = true;
        }
    }

    $ds = [];
    if (isset($co_loai['phong_chup']) && !isset($co_loai['trang_phuc'])) {
        $ds[] = [
            'noi_dung' => 'Thêm 1 bộ trang phục bất kì khi thuê studio là được giảm '
                        . COMBO_TRANG_PHUC_PHONG_CHUP_PHAN_TRAM . '% trên combo — giá sẽ bất ngờ đấy!',
            'nhan'     => 'XEM TRANG PHỤC',
            'loai'     => 'trang_phuc',
        ];
    }
    if (isset($co_loai['trang_phuc']) && !isset($co_loai['phong_chup'])) {
        $ds[] = [
            'noi_dung' => 'Thêm 1 phòng chụp khi thuê trang phục là được giảm '
                        . COMBO_TRANG_PHUC_PHONG_CHUP_PHAN_TRAM . '% trên combo — giá sẽ bất ngờ đấy!',
            'nhan'     => 'XEM STUDIO',
            'loai'     => 'phong_chup',
        ];
    }
    if (isset($co_loai['thiet_bi']) && !isset($co_loai['phu_kien'])) {
        $ds[] = [
            'noi_dung' => 'Thêm 1 phụ kiện - đạo cụ khi thuê máy ảnh là được giảm '
                        . COMBO_THIET_BI_PHU_KIEN_PHAN_TRAM . '% trên combo — giá sẽ bất ngờ đấy!',
            'nhan'     => 'XEM PHỤ KIỆN',
            'loai'     => 'phu_kien',
        ];
    }
    if (isset($co_loai['phu_kien']) && !isset($co_loai['thiet_bi'])) {
        $ds[] = [
            'noi_dung' => 'Thêm 1 máy ảnh khi thuê phụ kiện là được giảm '
                        . COMBO_THIET_BI_PHU_KIEN_PHAN_TRAM . '% trên combo — giá sẽ bất ngờ đấy!',
            'nhan'     => 'XEM MÁY ẢNH',
            'loai'     => 'thiet_bi',
        ];
    }

    return $ds;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_hop_le()) {
        $_SESSION['thong_bao_dat'] = 'Phiên làm việc đã hết hạn, vui lòng tải lại trang và thử lại.';
        header('Location: cart.php');
        exit;
    }

    $hanh_dong = $_POST['action'] ?? 'add';
    $ma = (int) ($_POST['service_id'] ?? $_POST['id'] ?? 0);
    $so_luong = (int) ($_POST['so_luong'] ?? $_POST['qty'] ?? 1);
    
    $key_item = $_POST['cart_key'] ?? ''; 

    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if ($hanh_dong === 'clear') {
        unset($_SESSION['cart'], $_SESSION['coupon_code']);
    } elseif ($hanh_dong === 'add' && $ma > 0) {
        $toi_da = so_luong_toi_da($conn, $ma);
        $dv = null;
        if ($toi_da > 0) {
            $stmt = $conn->prepare("SELECT DV_DonViTinh FROM dichvu WHERE DV_Ma = ?");
            $stmt->execute([$ma]);
            $dv = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        // Bắt dữ liệu ngày, giờ, size và trang phục gửi lên từ form
        $ngay_nhan      = trim((string) ($_POST['ngay_nhan'] ?? ''));
        $gio_nhan       = trim((string) ($_POST['gio_nhan'] ?? '08:00'));
        $ngay_tra       = trim((string) ($_POST['ngay_tra'] ?? ''));
        $gio_tra        = trim((string) ($_POST['gio_tra'] ?? '17:00'));
        $kich_co_nu     = trim((string) ($_POST['kich_co_nu'] ?? ''));
        $kich_co_nam    = trim((string) ($_POST['kich_co_nam'] ?? ''));
        $kich_co        = trim((string) ($_POST['kich_co'] ?? ''));
        if ($kich_co === '') {
            $parts_sz = [];
            if ($kich_co_nu !== '') $parts_sz[] = 'Nữ: ' . $kich_co_nu;
            if ($kich_co_nam !== '') $parts_sz[] = 'Nam: ' . $kich_co_nam;
            $kich_co = implode(' | ', $parts_sz);
        }
        $trang_phuc_id  = chuan_hoa_ds_trang_phuc(trim((string) ($_POST['trang_phuc_id'] ?? '')), true);
        $trang_phuc_anh = chuan_hoa_ds_trang_phuc(trim((string) ($_POST['trang_phuc'] ?? '')));
        $trang_phuc_ten = chuan_hoa_ds_trang_phuc(trim((string) ($_POST['trang_phuc_ten'] ?? '')));

        // Nếu form không truyền ngày nhận (VD: trang gói cưới service-detail-view.php), tự động lấy ngày mai
        $tu_dong_ngay = ($ngay_nhan === '');
        if ($tu_dong_ngay) {
            $ngay_mac_dinh = date('Y-m-d', strtotime('+1 day'));
            $ngay_nhan = $ngay_mac_dinh;
            $gio_nhan  = '08:00';
            $ngay_tra  = $ngay_mac_dinh;
            $gio_tra   = '17:00';
        }

        // Kiểm tra nếu là dịch vụ tính theo lượt thì gộp thời gian theo 1 mốc, ngược lại theo khoảng thời gian nhận/trả
        $don_vi_tinh = chuan_hoa_don_vi((string) ($dv['DV_DonViTinh'] ?? 'ngay'));

        if (la_don_vi_luot($don_vi_tinh)) {
            $bat_dau  = ($ngay_nhan !== '' ? $ngay_nhan : date('Y-m-d')) . ' ' . ($gio_nhan !== '' ? $gio_nhan : '08:00') . ':00';
            $ket_thuc = $bat_dau; // Dịch vụ tính theo lượt dùng chung mốc thời điểm
        } else {
            $bat_dau  = ($ngay_nhan !== '' ? $ngay_nhan : date('Y-m-d')) . ' ' . ($gio_nhan !== '' ? $gio_nhan : '08:00') . ':00';
            $ket_thuc = ($ngay_tra !== '' ? $ngay_tra : date('Y-m-d')) . ' ' . ($gio_tra !== '' ? $gio_tra : '17:00') . ':00';
        }

        // Ràng buộc phía Server: Thời gian trả bắt buộc phải lớn hơn hoặc bằng thời gian nhận (đối với dịch vụ theo ngày/giờ)
        if (!la_don_vi_luot($don_vi_tinh) && strtotime($ket_thuc) <= strtotime($bat_dau)) {
            $_SESSION['thong_bao_dat'] = 'Thời gian trả phải lớn hơn thời gian nhận!';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'services.php'));
            exit;
        }

        $tg = $dv ? thoi_gian_thue((string) $dv['DV_DonViTinh'], $bat_dau, $ket_thuc) : null;

        if ($toi_da === 0) {
            $_SESSION['thong_bao_dat'] = 'Dịch vụ này hiện không còn nhận thuê.';
        } elseif ($tg === null) {
            $_SESSION['thong_bao_dat'] = 'Vui lòng chọn thời gian nhận và trả hợp lệ.';
        } elseif (!$tu_dong_ngay && strtotime($tg['bat_dau']) <= time()) {
            $_SESSION['thong_bao_dat'] = 'Thời gian nhận đã qua, vui lòng chọn khung giờ khác.';
        } elseif (!$tu_dong_ngay && strtotime($tg['ket_thuc']) <= time()) {
            $_SESSION['thong_bao_dat'] = 'Thời gian thuê đã qua, vui lòng chọn lại.';
        } else {
            // Tạo khóa riêng cho từng sản phẩm + size + trang phục + khung giờ.
            // Nhờ vậy đặt cùng dịch vụ ở hai khung giờ khác nhau sẽ không ghi đè nhau.
            $khoa_thoi_gian = substr(md5($tg['bat_dau'] . '|' . $tg['ket_thuc']), 0, 10);
            $khoa_tp = $trang_phuc_id !== '' ? '_tp' . md5($trang_phuc_id) : '';
            $cart_key = $ma . ($kich_co !== '' ? '_' . $kich_co : '') . $khoa_tp . '_' . $khoa_thoi_gian;

            $da_co = is_array($_SESSION['cart'][$cart_key] ?? null) ? (int) ($_SESSION['cart'][$cart_key]['qty'] ?? 0) : 0;
            
            $_SESSION['cart'][$cart_key] = [
                'id'             => $ma,
                'qty'            => min($toi_da, max(1, $da_co + $so_luong)),
                'bat_dau'        => $tg['bat_dau'],
                'ket_thuc'       => $tg['ket_thuc'],
                'kich_co'        => $kich_co,
                'kich_co_nu'     => $kich_co_nu,
                'kich_co_nam'    => $kich_co_nam,
                'trang_phuc_id'  => $trang_phuc_id !== '' ? $trang_phuc_id : null,
                'trang_phuc_anh' => $trang_phuc_anh,
                'trang_phuc_ten' => $trang_phuc_ten,
            ];
            $_SESSION['thong_bao_dat'] = 'Đã thêm dịch vụ vào giỏ hàng.';
        }
    } elseif ($hanh_dong === 'update' && $key_item !== '' && isset($_SESSION['cart'][$key_item])) {
        if ($so_luong <= 0) {
            unset($_SESSION['cart'][$key_item]);
        } else {
            $ma_dv = (int) $_SESSION['cart'][$key_item]['id'];
            $_SESSION['cart'][$key_item]['qty'] = min(max(1, so_luong_toi_da($conn, $ma_dv)), $so_luong);
        }
    } elseif ($hanh_dong === 'remove' && $key_item !== '') {
        unset($_SESSION['cart'][$key_item]);
    } elseif ($hanh_dong === 'apply_coupon') {
        $code = trim((string) ($_POST['coupon_code'] ?? ''));
        if ($code === '') {
            $_SESSION['thong_bao_dat'] = 'Vui lòng nhập mã giảm giá.';
        } else {
            $items_tmp = lay_gio_hang($conn);
            $st_tmp    = tinh_tong_gio_hang($items_tmp);
            // Mã giảm giá tính trên số tiền còn lại sau khi trừ combo.
            $combo_tmp = tinh_giam_combo($items_tmp);
            $sau_combo_tmp = max(0, (float) $st_tmp['subtotal'] - (float) $combo_tmp['tong_giam']);
            $kq        = kiem_tra_ma_giam_gia($conn, $code, $sau_combo_tmp);
            if ($kq['ok']) {
                $_SESSION['coupon_code']    = strtoupper($code);
                $_SESSION['thong_bao_dat']  = 'Đã áp dụng mã ' . strtoupper($code) . '.';
            } else {
                $_SESSION['thong_bao_dat']  = $kq['message'];
            }
        }
    } elseif ($hanh_dong === 'remove_coupon') {
        unset($_SESSION['coupon_code']);
        $_SESSION['thong_bao_dat'] = 'Đã hủy mã giảm giá.';
    }

    header('Location: cart.php');
    exit;
}

$thong_bao = $_SESSION['thong_bao_dat'] ?? '';
unset($_SESSION['thong_bao_dat']);

$cart_items = lay_gio_hang($conn);
$totals = tinh_tong_gio_hang($cart_items);

// Combo giảm trên tiền các món thuộc combo; mã giảm giá tính trên số còn lại sau combo.
$combo = tinh_giam_combo($cart_items);
$giam_combo = (float) $combo['tong_giam'];
$sau_combo  = max(0, (float) $totals['subtotal'] - $giam_combo);
$giam_may_anh = giam_thue_dai_han($cart_items);

$coupon_code = $_SESSION['coupon_code'] ?? '';
$coupon_info = null;
$discount    = 0.0;

if ($coupon_code !== '') {
    $kq_coupon = kiem_tra_ma_giam_gia($conn, $coupon_code, $sau_combo);
    if ($kq_coupon['ok']) {
        $coupon_info = $kq_coupon['coupon'];
        $discount    = (float) $kq_coupon['discount'];
    } else {
        unset($_SESSION['coupon_code']);
        $coupon_code = '';
    }
}

$totals['combo']         = $giam_combo;
$totals['giam_may_anh']  = $giam_may_anh;
$totals['discount']      = $discount;
$totals['total']         = max(0, $sau_combo - $discount - $giam_may_anh);
$tong_so_luong = array_sum(array_column($cart_items, 'qty'));
$ds_goi_y_combo = goi_y_combo($cart_items);

$co_loi = false;
foreach ($cart_items as $k => $item) {
    $tg = thoi_gian_thue((string) $item['don_vi'], (string) $item['bat_dau'], (string) $item['ket_thuc']);
    $cart_items[$k]['tg'] = $tg;
    $cart_items[$k]['canh_bao'] = '';

    if ($tg === null || strtotime($tg['bat_dau']) <= time() || strtotime($tg['ket_thuc']) <= time()) {
        $cart_items[$k]['canh_bao'] = 'Thời gian thuê không hợp lệ hoặc đã qua, vui lòng xóa và chọn lại.';
    } else {
        try {
            $ma_item = (int) $item['id'];
            $sz_check = (string) ($item['kich_co'] ?? '');
            // Gói Hanbok (ID 93..100) hoặc dịch vụ không có dòng size trong dichvu_kichco thì không kiểm tra size theo DV_Ma của gói
            if (($ma_item >= 93 && $ma_item <= 100) || !co_cau_hinh_size_db($conn, $ma_item)) {
                $sz_check = '';
            }
            kiem_tra_con_cho($conn, $ma_item, $item['ten'], $tg['bat_dau'], $tg['ket_thuc'], (int) $item['qty'], $sz_check);
        } catch (DomainException $e) {
            $cart_items[$k]['canh_bao'] = $e->getMessage();
        }
    }
    if ($cart_items[$k]['canh_bao'] !== '') {
        $co_loi = true;
    }
}

$tieu_de_trang = 'Giỏ hàng - VIBE Studio';
$css_rieng = [
    BASE_URL . 'assets/css/cart.css?v=' . (is_file(__DIR__ . '/../assets/css/cart.css') ? filemtime(__DIR__ . '/../assets/css/cart.css') : time())
];
require_once '../includes/header.php';
?>

<section class="page-head">
    <p class="page-label">YOUR CART</p>
    <h1>GIỎ <i>HÀNG.</i></h1>
    <ol class="steps">
        <li class="current">1. GIỎ HÀNG</li>
        <li>2. THANH TOÁN</li>
        <li>3. HOÀN TẤT</li>
    </ol>
</section>

<section class="shop-area">
    <?php if ($thong_bao !== '') : ?>
        <div class="notice" role="status"><span><?= htmlspecialchars($thong_bao) ?></span></div>
    <?php endif; ?>

    <div class="checkout-layout">

        <div class="card">
            <div class="card-head">
                <h2>Dịch vụ trong giỏ</h2>
                <span id="cartCount"><?= (int) $tong_so_luong ?> mục</span>
            </div>

            <div id="cartItems">
                <?php if (empty($cart_items)) : ?>
                    <div class="cart-empty">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 7h12l1 13H5L6 7z"/><path d="M9 7V6a3 3 0 0 1 6 0v1"/></svg>
                        <h3>Giỏ hàng đang trống</h3>
                        <p>Chọn phòng chụp, thiết bị hoặc trang phục, dịch vụ sẽ xuất hiện tại đây.</p>
                        <a href="services.php" class="btn-dark">XEM DỊCH VỤ</a>
                    </div>
                <?php else : ?>
                    <?php foreach ($cart_items as $item) :
                        $toi_da = max(1, min(SO_LUONG_TOI_DA_MOI_DICH_VU, (int) $item['so_luong']));
                        $tp_id  = (string) ($item['trang_phuc_id'] ?? '');
                        $tp_anh = (string) ($item['trang_phuc_anh'] ?? '');
                        $tp_ten = (string) ($item['trang_phuc_ten'] ?? '');

                        // Giỏ cũ chỉ có id: tra cứu tên trong CSDL để hiển thị lại cho thân thiện.
                        if ($tp_ten === '' && $tp_id !== '') {
                            $ten_tim = [];
                            foreach (explode('|', $tp_id) as $ma_tp) {
                                $ma_tp = (int) $ma_tp;
                                if ($ma_tp <= 0) {
                                    continue;
                                }
                                $stmt_tp = $conn->prepare("SELECT DV_Ten FROM dichvu WHERE DV_Ma = ?");
                                $stmt_tp->execute([$ma_tp]);
                                $ten = (string) ($stmt_tp->fetchColumn() ?: '');
                                if ($ten !== '') {
                                    $ten_tim[] = $ten;
                                }
                            }
                            $tp_ten = implode('|', $ten_tim);
                        }
                        $tp_hien_thi = str_replace('|', ' + ', $tp_ten !== '' ? $tp_ten : $tp_anh);
                    ?>
                        <div class="cart-row">
                            <img src="<?= htmlspecialchars(duong_dan_anh($item['anh'] ?: 'default.png')) ?>" alt="<?= htmlspecialchars($item['ten']) ?>">
                            <div>
                                <h3><?= htmlspecialchars($item['ten']) ?></h3>
                                
                                <?php if (!empty($item['kich_co'])) : ?>
                                    <p class="unit" style="font-weight: 600; color: #d9534f; margin-bottom: 2px;">Kích cỡ (Size): <?= htmlspecialchars($item['kich_co']) ?></p>
                                <?php endif; ?>

                                <p class="unit"><?= dinh_dang_tien($item['gia']) ?> / <?= htmlspecialchars(ten_don_vi_gio_hang((string) $item['don_vi'])) ?></p>

                                <?php if ($item['tg']) : ?>
                                    <p class="unit" style="font-size:12px; color:#666;">
                                        <?= htmlspecialchars(dinh_dang_ngay_gio($item['tg']['bat_dau'])) ?>
                                        <?php if (!la_don_vi_luot((string) $item['don_vi'])) : ?>
                                            → <?= htmlspecialchars(dinh_dang_ngay_gio($item['tg']['ket_thuc'])) ?>
                                        <?php endif; ?>
                                        (<?= (int) $item['so_don_vi'] ?> <?= htmlspecialchars(ten_don_vi_gio_hang((string) $item['don_vi'])) ?>)
                                    </p>
                                <?php endif; ?>

                                <?php if ($tp_hien_thi !== '') : ?>
                                    <p class="unit" style="font-size:12px; color:#333; margin-top:4px;">
                                        Đã chọn mẫu: <strong><?= htmlspecialchars($tp_hien_thi) ?></strong>
                                    </p>
                                <?php endif; ?>

                                <?php if ($item['canh_bao'] !== '') : ?>
                                    <p class="unit" style="font-size:12px; color:#c00;" role="alert"><?= htmlspecialchars($item['canh_bao']) ?></p>
                                <?php endif; ?>

                                <form method="POST" style="display:inline-block;">
                                    <?= csrf_input() ?>
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="cart_key" value="<?= htmlspecialchars($item['cart_key'] ?? '') ?>">
                                    <div class="qty">
                                        <button type="submit" name="qty" value="<?= (int) $item['qty'] - 1 ?>" aria-label="Giảm số lượng">−</button>
                                        <span><?= (int) $item['qty'] ?></span>
                                        <button type="submit" name="qty" value="<?= min($toi_da, (int) $item['qty'] + 1) ?>" aria-label="Tăng số lượng" <?= (int) $item['qty'] >= $toi_da ? 'disabled' : '' ?>>+</button>
                                    </div>
                                </form>
                            </div>
                            <div class="cart-side">
                                <span class="line-total"><?= dinh_dang_tien($item['subtotal']) ?></span>
                                
                                <form method="POST">
                                    <?= csrf_input() ?>
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="cart_key" value="<?= htmlspecialchars($item['cart_key'] ?? '') ?>">
                                    <button type="submit" class="link-btn">XÓA</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <?php if (!empty($ds_goi_y_combo)) : ?>
                <div style="padding:14px 18px;border-top:1px solid #eee;background:#fffbea;">
                    <?php foreach ($ds_goi_y_combo as $gy) : ?>
                        <div style="display:flex;gap:12px;align-items:center;justify-content:space-between;flex-wrap:wrap;margin:6px 0;">
                            <p style="margin:0;font-size:13px;color:#8a6d00;">💡 <?= htmlspecialchars($gy['noi_dung']) ?></p>
                            <a href="services.php?loai=<?= htmlspecialchars($gy['loai']) ?>" class="btn-dark" style="text-decoration:none;padding:8px 14px;font-size:12px;"><?= htmlspecialchars($gy['nhan']) ?></a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="card-foot">
                <a href="services.php" class="continue">← TIẾP TỤC CHỌN DỊCH VỤ</a>
                <?php if (!empty($cart_items)) : ?>
                    <form method="POST" style="margin:0;">
                        <?= csrf_input() ?>
                        <input type="hidden" name="action" value="clear">
                        <button type="submit" class="link-btn">XÓA TẤT CẢ</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <aside class="summary">
            <p class="panel-title">Tóm tắt đơn thuê</p>

            <div class="summary-row"><span>Tạm tính</span><span><?= dinh_dang_tien($totals['subtotal']) ?></span></div>

            <?php foreach ($combo['danh_sach'] as $c) : ?>
                <div class="summary-row">
                    <span><?= htmlspecialchars($c['ten']) ?> (−<?= (int) $c['phan_tram'] ?>%)</span>
                    <span style="color: var(--success); font-weight: 700;">−<?= dinh_dang_tien($c['giam']) ?></span>
                </div>
            <?php endforeach; ?>
            <?php if ($giam_combo > 0) : ?>
                <p class="ship-note" style="color: var(--success);">Bạn thuê cả hai cùng lúc nên combo đã được áp dụng — giá bất ngờ đấy!</p>
            <?php endif; ?>

            <!-- ====================== COUPON ====================== -->
            <div class="coupon-box">
                <?php if ($coupon_info !== null) : ?>
                    <form method="POST" class="coupon-applied">
                        <?= csrf_input() ?>
                        <div class="coupon-applied-info">
                            <span class="coupon-tag">🎉 <?= htmlspecialchars($coupon_code) ?></span>
                            <span class="coupon-desc"><?= htmlspecialchars((string) ($coupon_info['MG_MoTa'] ?? '')) ?></span>
                        </div>
                        <input type="hidden" name="action" value="remove_coupon">
                        <button type="submit" class="link-btn">HỦY</button>
                    </form>
                <?php else : ?>
                    <form method="POST" class="coupon-form">
                        <?= csrf_input() ?>
                        <input type="hidden" name="action" value="apply_coupon">
                        <input type="text" name="coupon_code" placeholder="Nhập mã giảm giá"
                            maxlength="50" autocomplete="off" spellcheck="false">
                        <button type="submit">ÁP DỤNG</button>
                    </form>
                <?php endif; ?>
            </div>
            <!-- ===================================================== -->

            <?php if ($discount > 0) : ?>
                <div class="summary-row">
                    <span>Giảm giá (<?= htmlspecialchars($coupon_code) ?>)</span>
                    <span style="color: var(--success); font-weight: 700;">−<?= dinh_dang_tien($discount) ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($giam_may_anh) && $giam_may_anh > 0) : ?>
            <div class="summary-row">
                <span>Giảm thuê dài hạn</span>
                <span style="color: var(--success); font-weight: 700;">−<?= dinh_dang_tien($giam_may_anh) ?></span>
            </div>
            <?php endif; ?>
            <div class="summary-row total"><span>Tổng cộng</span><span><?= dinh_dang_tien($totals['total']) ?></span></div>

            <?php if ($co_loi) : ?>
                <p class="ship-note" style="color:#c00;">Có dịch vụ cần chỉnh lại trước khi thanh toán.</p>
            <?php endif; ?>

            <a href="checkout.php" class="btn-dark <?= (empty($cart_items) || $co_loi) ? 'is-disabled' : '' ?>"
            <?= (empty($cart_items) || $co_loi) ? 'aria-disabled="true" tabindex="-1"' : '' ?>>THANH TOÁN</a>

            <p class="ship-note">Dịch vụ được nhận trực tiếp tại studio nên không có phí vận chuyển. Đơn sẽ chờ studio duyệt sau khi đặt.</p>
        </aside>

    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
