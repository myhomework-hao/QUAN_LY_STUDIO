<?php
require_once '../config/app.php';
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../includes/donhang.php';

yeu_cau_vai_tro('khachhang');

$ma_nguoi_dung = ma_nguoi_dung_hien_tai();

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
$css_rieng = ['cart.css', 'KB.css'];
require_once '../includes/header.php';
?>

<section class="page-head" id="pageHead">
    <p class="page-label">ACCOUNT</p>
    <h1>ĐƠN HÀNG <i>CỦA TÔI.</i></h1>
</section>

<section class="dh-khung">
    <?php if (empty($cac_don)) : ?>
        <div class="dh-trong">
            <p>Bạn chưa có đơn thuê nào tại VIBE Studio.</p>
            <a href="<?= BASE_URL ?>AT/services.php" class="dh-nut">KHÁM PHÁ DỊCH VỤ</a>
        </div>
    <?php else : ?>
        <div class="dh-danh-sach">
            <?php foreach ($cac_don as $don) : ?>
                <article class="dh-the">
                    <div class="dh-the-dau">
                        <div>
                            <p class="dh-ma"><?= htmlspecialchars(ma_don_hien_thi((int) $don['DDL_Ma'])) ?></p>
                            <p class="dh-phu">Đặt lúc <?= htmlspecialchars(dinh_dang_ngay_gio($don['DDL_NgayTao'])) ?></p>
                        </div>
                        <span class="dh-nhan dh-<?= lop_trang_thai($don['DDL_TrangThai']) ?>">
                            <?= htmlspecialchars($don['DDL_TrangThai']) ?>
                        </span>
                    </div>

                    <div class="dh-the-than">
                        <div>
                            <p class="dh-dong">
                                <b><?= htmlspecialchars($don['ten_dau_tien'] ?? '') ?></b>
                                <?php if ((int) $don['so_dich_vu'] > 1) : ?>
                                    <span class="dh-phu">và <?= (int) $don['so_dich_vu'] - 1 ?> dịch vụ khác</span>
                                <?php endif; ?>
                            </p>
                            <?php if ($don['bat_dau_som_nhat']) : ?>
                                <p class="dh-phu">Thuê từ <?= htmlspecialchars(dinh_dang_ngay_gio($don['bat_dau_som_nhat'])) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="dh-tien">
                            <span class="dh-phu">Tổng tiền</span>
                            <strong><?= dinh_dang_tien($don['DDL_TongTien']) ?></strong>
                        </div>
                    </div>

                    <div class="dh-the-chan">
                        <a href="../KB/order-detail.php?id=<?= (int) $don['DDL_Ma'] ?>" class="dh-nut">CHI TIẾT ĐƠN THUÊ</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php require_once '../includes/footer.php'; ?>