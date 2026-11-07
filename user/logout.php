<?php
require_once '../config/app.php';
require_once '../config/auth.php';
require_once '../includes/security.php';

$loi_dang_xuat = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!csrf_hop_le()) {

        // Token sai: không đăng xuất, chỉ báo lỗi và cho làm lại.
        http_response_code(403);
        $loi_dang_xuat = 'Phiên làm việc đã hết hạn, vui lòng tải lại trang và thử lại.';

    } else {

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $tham_so = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $tham_so['path'],
                $tham_so['domain'],
                $tham_so['secure'],
                $tham_so['httponly']
            );
        }

        session_destroy();

        header('Location: login.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Đăng xuất - VIBE STUDIO</title>
<style>
    body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #f5f5f5; font-family: Arial, Helvetica, sans-serif; color: #111; }
    .hop { background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08); text-align: center; max-width: 380px; width: 90%; }
    .hop h1 { font-size: 24px; margin: 0 0 12px; }
    .hop p { color: #555; margin: 0 0 24px; }
    .hop .loi { color: #cf1322; }
    .hop .nut { display: inline-block; padding: 12px 24px; border-radius: 8px; font-weight: 600; text-decoration: none; border: 0; cursor: pointer; font-size: 14px; }
    .hop .chinh { background: #111; color: #fff; }
    .hop .phu { background: #eee; color: #111; margin-left: 8px; }
</style>
</head>
<body>
    <div class="hop">
        <h1>Đăng xuất</h1>

        <?php if ($loi_dang_xuat !== ''): ?>
            <p class="loi"><?= htmlspecialchars($loi_dang_xuat) ?></p>
        <?php endif; ?>

        <p>Bạn có chắc muốn đăng xuất khỏi VIBE STUDIO?</p>

        <form method="POST" action="">
            <?= csrf_input() ?>
            <button type="submit" class="nut chinh">Đăng xuất</button>
            <a href="<?= htmlspecialchars(BASE_URL) ?>index.php" class="nut phu">Quay lại</a>
        </form>
    </div>
</body>
</html>
