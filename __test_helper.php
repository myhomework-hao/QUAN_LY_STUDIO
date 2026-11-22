<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/chat_goi_y.php';
$cases = ['có phòng Luxury không', 'phòng Luxury', 'có phòng luxury hong', 'trang phục Streetwear', 'có trang phục Streetwear không', 'phụ kiện chân máy', 'có phụ kiện chân máy không', 'máy Canon', 'streetwear', 'hanbok', 'retro'];
foreach ($cases as $m) {
    $r = chat_tim_san_pham($conn, $m);
    echo $m . ' => ' . $r['intro'] . ' ids=' . implode(',', array_column($r['san_pham'], 'id')) . "\n";
}