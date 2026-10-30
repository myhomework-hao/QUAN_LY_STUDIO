<?php
/*
 * TÌM KIẾM SẢN PHẨM
 *  - search.php?format=json&q=...  -> trả JSON cho popup tìm kiếm (assets/js/search.js)
 *  - search.php?q=...              -> trang kết quả đầy đủ (khi bấm Enter / nút tìm / "Xem tất cả kết quả")
 * Tìm theo tên và mô tả của dịch vụ đang bán. Collation utf8mb4_unicode_ci nên gõ "may anh" vẫn ra "Máy ảnh".
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/dich_vu.php';
 
$ten_danh_muc_theo_loai = [
    'thiet_bi'   => 'Máy ảnh - thiết bị',
    'phong_chup' => 'Chỗ chụp ảnh',
    'trang_phuc' => 'Trang phục',
    'phu_kien'   => 'Phụ kiện - đạo cụ',
];
 
$q = trim((string) ($_GET['q'] ?? ''));
$q = preg_replace('/\s+/u', ' ', $q);
$q = mb_substr($q, 0, 60);
 
$la_json = (($_GET['format'] ?? '') === 'json');
$gioi_han = $la_json ? 8 : 40;
 
// Mỗi ký tự gõ là một request: nhả khóa session để các request không phải chờ nhau
if ($la_json && session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}
 
function tim_dich_vu(PDO $conn, string $q, int $gioi_han): array
{
    if ($q === '') {
        return [];
    }
 
    // Escape % _ \ để người dùng gõ ký tự đặc biệt vẫn tìm đúng nghĩa đen
    $an_toan = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q);
    $chua = '%' . $an_toan . '%';
    $bat_dau = $an_toan . '%';
 
    $sql = "SELECT dv.DV_Ma AS id, dv.DV_Ten AS ten, dv.DV_Loai AS loai, dv.DV_GiaThue AS gia,
                   dv.DV_DonViTinh AS don_vi, dv.DV_HinhAnh AS anh
            FROM dichvu dv
            WHERE dv.DV_DangBan = 1
              AND (dv.DV_Ten LIKE :ten OR dv.DV_MoTa LIKE :mo_ta)
            ORDER BY (dv.DV_Ten LIKE :bat_dau) DESC, (dv.DV_Ten LIKE :co_ten) DESC, dv.DV_Ma DESC
            LIMIT " . (int) $gioi_han;
 
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':ten'     => $chua,
            ':mo_ta'   => $chua,
            ':bat_dau' => $bat_dau,
            ':co_ten'  => $chua,
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}
 
$ket_qua = tim_dich_vu($conn, $q, $gioi_han);
 
/* ---------- Chế độ JSON ---------- */
if ($la_json) {
    $items = [];
    foreach ($ket_qua as $dv) {
        $items[] = [
            'id'   => (int) $dv['id'],
            'ten'  => $dv['ten'],
            'loai' => $ten_danh_muc_theo_loai[$dv['loai']] ?? $dv['loai'],
            'gia'  => dinh_dang_gia((int) $dv['gia'], $dv['don_vi']),
            'anh'  => BASE_URL . (!empty($dv['anh']) ? 'assets/uploads/' . $dv['anh'] : 'assets/images/no-image.jpg'),
            'link' => BASE_URL . 'user/service-detail.php?id=' . (int) $dv['id'],
        ];
    }
 
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode(['q' => $q, 'items' => $items], JSON_UNESCAPED_UNICODE);
    exit;
}
 
/* ---------- Chế độ trang kết quả ---------- */
$tieu_de_trang = ($q !== '' ? 'Tìm "' . $q . '"' : 'Tìm kiếm') . ' - VIBE STUDIO';
$trang_hien_tai = 'san-pham';
$css_rieng = [
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css',
    BASE_URL . 'assets/css/at.css',
];
$js_rieng = [];
 
require_once __DIR__ . '/header.php';
?>
 
<main class="product-page">
 
    <section class="product-list-section">
 
        <?php if ($q === ''): ?>
            <p class="no-product">Bấm vào biểu tượng kính lúp trên menu để tìm máy ảnh, chỗ chụp, bộ đồ hoặc phụ kiện.</p>
 
        <?php elseif (empty($ket_qua)): ?>
            <p class="no-product">Không tìm thấy sản phẩm nào cho “<?= h($q) ?>”.</p>
 
        <?php else: ?>
            <h1 class="search-result-title">
                <?= count($ket_qua) ?> kết quả cho “<?= h($q) ?>”
            </h1>
 
            <div class="product-grid">
                <?php foreach ($ket_qua as $dv): ?>
                    <a href="<?= BASE_URL ?>user/service-detail.php?id=<?= (int) $dv['id'] ?>" class="product-card search-result-card">
                        <div class="product-image">
                            <img src="<?= BASE_URL ?><?= !empty($dv['anh']) ? 'assets/uploads/' . h($dv['anh']) : 'assets/images/no-image.jpg' ?>"
                                 alt="<?= h($dv['ten']) ?>" loading="lazy">
                        </div>
                        <div class="product-info">
                            <div class="product-tags">
                                <span class="product-status-tag"><?= h($ten_danh_muc_theo_loai[$dv['loai']] ?? $dv['loai']) ?></span>
                            </div>
                            <h3 class="product-name"><?= h($dv['ten']) ?></h3>
                            <div class="product-price-box">
                                <span class="product-price"><?= h(dinh_dang_gia((int) $dv['gia'], $dv['don_vi'])) ?></span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
 
    </section>
</main>
 
<?php require_once __DIR__ . '/footer.php'; ?>
 