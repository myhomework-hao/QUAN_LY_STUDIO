<?php
require_once '../config/app.php';
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../includes/donhang.php';

yeu_cau_vai_tro('khachhang');

$ma_nguoi_dung = ma_nguoi_dung_hien_tai();

// Lấy danh sách đơn đặt lịch của khách hàng hiện tại kèm thông tin dịch vụ đầu tiên
$stmt = $conn->prepare("
    SELECT d.DDL_Ma, d.DDL_TongTien, d.DDL_TrangThai, d.DDL_NgayTao,
           (SELECT COUNT(*) FROM chitietdon c WHERE c.DDL_Ma = d.DDL_Ma) AS so_dich_vu,
           (SELECT dv.DV_Ten FROM chitietdon c JOIN dichvu dv ON dv.DV_Ma = c.DV_Ma
             WHERE c.DDL_Ma = d.DDL_Ma ORDER BY c.CTD_Ma LIMIT 1) AS ten_dau_tien,
           (SELECT MIN(c.CTD_BatDau) FROM chitietdon c WHERE c.DDL_Ma = d.DDL_Ma) AS bat_dau_som_nhat
    FROM dondatlich d
    WHERE d.ND_Ma = ?
    ORDER BY d.DDL_Ma DESC
");
$stmt->execute([$ma_nguoi_dung]);
$cac_don = $stmt->fetchAll(PDO::FETCH_ASSOC);

$tieu_de_trang = 'Đơn hàng của tôi - VIBE Studio';

$css_rieng = [
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css',
    BASE_URL . 'assets/css/kb.css' // Trỏ chuẩn tuyệt đối từ gốc xuống
];
require_once '../includes/header.php';
?>

<main class="order-page" style="padding: 40px 8% 80px 8%; background: #ffffff;">
    <section class="page-head" id="pageHead" style="margin-bottom: 30px; border-bottom: 1px solid #eaeaea; padding-bottom: 20px;">
        <p class="page-label" style="font-size: 11px; font-weight: 600; letter-spacing: 3px; color: #777; text-transform: uppercase; margin-bottom: 6px;">ACCOUNT</p>
        <h1 style="font-size: 32px; font-weight: 700; color: #111; margin: 0;">ĐƠN HÀNG <i>CỦA TÔI.</i></h1>
    </section>

    <div class="dh-khung" style="max-width: 100%; padding: 0; margin: 0;">
        <?php if (empty($cac_don)) : ?>
            <div class="dh-trong" style="text-align: center; padding: 60px 20px; background: #ffffff; border: 1px solid #eaeaea; border-radius: 20px;">
                <p style="font-size: 14px; color: #666; margin-bottom: 20px;">Bạn chưa có đơn thuê nào tại VIBE Studio.</p>
                <a href="services.php" class="dh-nut" style="display: inline-block; padding: 12px 24px; background: #111; color: #fff; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 12px; letter-spacing: 1px;">KHÁM PHÁ DỊCH VỤ</a>
            </div>
        <?php else : ?>
            <div class="dh-danh-sach" style="display: flex; flex-direction: column; gap: 20px;">
                <?php foreach ($cac_don as $don) : ?>
                    <article class="dh-the" style="background: #ffffff; border: 1px solid #eaeaea; border-radius: 20px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.02);">
                        
                        <!-- Header thẻ đơn hàng -->
                        <div class="dh-the-dau" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eaeaea; padding-bottom: 16px; margin-bottom: 16px;">
                            <div>
                                <p class="dh-ma" style="font-size: 16px; font-weight: 700; color: #111; margin: 0 0 4px 0;">
                                    <?= htmlspecialchars(ma_don_hien_thi((int) $don['DDL_Ma'])) ?>
                                </p>
                                <p class="dh-phu" style="font-size: 12px; color: #666; margin: 0;">
                                    Đặt lúc <?= htmlspecialchars(dinh_dang_ngay_gio($don['DDL_NgayTao'])) ?>
                                </p>
                            </div>
                            <span class="dh-nhan dh-<?= lop_trang_thai($don['DDL_TrangThai']) ?>" style="padding: 5px 12px; border-radius: 6px; font-size: 11px; font-weight: 600;">
                                <?= htmlspecialchars($don['DDL_TrangThai']) ?>
                            </span>
                        </div>

                        <!-- Thân thẻ đơn hàng -->
                        <div class="dh-the-than" style="display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap;">
                            <div>
                                <p class="dh-dong" style="font-size: 14px; color: #111; margin: 0 0 6px 0;">
                                    <b><?= htmlspecialchars($don['ten_dau_tien'] ?? 'Dịch vụ Studio') ?></b>
                                    <?php if ((int) $don['so_dich_vu'] > 1) : ?>
                                        <span class="dh-phu" style="color: #666;">và <?= (int) $don['so_dich_vu'] - 1 ?> dịch vụ khác</span>
                                    <?php endif; ?>
                                </p>
                                <?php if ($don['bat_dau_som_nhat']) : ?>
                                    <p class="dh-phu" style="font-size: 12px; color: #666; margin: 0;">
                                        Thuê từ <?= htmlspecialchars(dinh_dang_ngay_gio($don['bat_dau_som_nhat'])) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <div class="dh-tien" style="text-align: right;">
                                <span class="dh-phu" style="font-size: 12px; color: #666; display: block;">Tổng tiền</span>
                                <strong style="font-size: 18px; color: #111;"><?= dinh_dang_tien($don['DDL_TongTien']) ?></strong>
                            </div>
                        </div>

                        <!-- Chân thẻ đơn hàng -->
                        <div class="dh-the-chan" style="margin-top: 20px; padding-top: 16px; border-top: 1px solid #eaeaea; display: flex; justify-content: flex-end;">
                            <a href="order-detail.php?id=<?= (int) $don['DDL_Ma'] ?>" class="dh-nut" style="display: inline-flex; align-items: center; justify-content: center; padding: 10px 20px; font-size: 12px; font-weight: 600; text-decoration: none; color: #fff; background: #111; border-radius: 8px;">
                                CHI TIẾT ĐƠN THUÊ
                            </a>
                        </div>

                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>