<?php
require_once '../config/app.php';
require_once '../includes/auth.php';
require_once '../config/db.php';

yeu_cau_dang_nhap();

$ma_mon = (int) ($_GET['item'] ?? $_POST['item'] ?? 0);

$sql = "SELECT oi.id AS ma_mon, oi.order_id AS ma_don, s.id AS ma_dich_vu, s.ten, s.anh
        FROM order_items oi
        JOIN orders o ON o.id = oi.order_id
        JOIN services s ON s.id = oi.service_id
        WHERE oi.id = ? AND o.user_id = ? AND o.trang_thai = 'da_tra'";
$stmt = $conn->prepare($sql);
$stmt->execute([$ma_mon, $_SESSION['user_id']]);
$mon = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mon) {
    http_response_code(404);
    die('Không tìm thấy món cần đánh giá, hoặc đơn chưa hoàn thành.');
}

$stmt = $conn->prepare("SELECT id FROM reviews WHERE order_item_id = ?");
$stmt->execute([$ma_mon]);
$da_danh_gia = (bool) $stmt->fetch();

$thong_bao = '';
$loai_thong_bao = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$da_danh_gia) {

    $so_sao = (int) ($_POST['so_sao'] ?? 0);
    $binh_luan = trim($_POST['binh_luan'] ?? '');

    if ($so_sao < 1 || $so_sao > 5) {

        $thong_bao = 'Vui lòng chọn số sao từ 1 đến 5.';
        $loai_thong_bao = 'error';

    } elseif (mb_strlen($binh_luan) > 1000) {

        $thong_bao = 'Bình luận tối đa 1000 ký tự.';
        $loai_thong_bao = 'error';

    } else {

        try {
            $stmt = $conn->prepare(
                "INSERT INTO reviews (user_id, order_item_id, service_id, so_sao, binh_luan)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $_SESSION['user_id'],
                $mon['ma_mon'],
                $mon['ma_dich_vu'],
                $so_sao,
                $binh_luan
            ]);

            header('Location: ' . BASE_URL . 'KB/order-detail.php?id=' . $mon['ma_don'] . '&danh_gia=ok');
            exit;

        } catch (PDOException $loi) {
            if ($loi->getCode() === '23000') {
                $da_danh_gia = true;
            } else {
                throw $loi;
            }
        }
    }
}

$tieu_de_trang = 'Đánh giá - VIBE STUDIO';
$css_rieng = ['kb.css'];
require_once '../includes/header.php';
?>

<main class="khung-danh-gia">

    <p class="small-title">REVIEW</p>
    <h1>ĐÁNH GIÁ <i><?= htmlspecialchars($mon['ten']) ?></i></h1>

    <?php if ($da_danh_gia): ?>

        <p class="message success">Bạn đã đánh giá món này rồi. Cảm ơn bạn!</p>
        <a href="<?= BASE_URL ?>KB/order-detail.php?id=<?= (int) $mon['ma_don'] ?>" class="main-button">VỀ CHI TIẾT ĐƠN</a>

    <?php else: ?>

        <?php if ($thong_bao !== ''): ?>
            <p class="message <?= htmlspecialchars($loai_thong_bao) ?>">
                <?= htmlspecialchars($thong_bao) ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="item" value="<?= (int) $mon['ma_mon'] ?>">

            <div class="chon-sao">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <input type="radio" id="sao<?= $i ?>" name="so_sao" value="<?= $i ?>" <?= ((int) ($_POST['so_sao'] ?? 0) === $i) ? 'checked' : '' ?>>
                    <label for="sao<?= $i ?>" title="<?= $i ?> sao">★</label>
                <?php endfor; ?>
            </div>

            <label for="binh_luan">Nhận xét của bạn</label>
            <textarea id="binh_luan" name="binh_luan" rows="5" maxlength="1000" placeholder="Chia sẻ trải nghiệm thuê của bạn..."><?= htmlspecialchars($_POST['binh_luan'] ?? '') ?></textarea>

            <button type="submit">GỬI ĐÁNH GIÁ</button>
        </form>

    <?php endif; ?>

</main>

<?php require_once '../includes/footer.php'; ?>