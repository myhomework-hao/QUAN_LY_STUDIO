<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/auth.php';

$tieu_de_trang = 'Dịch vụ & Gói chụp - VIBE Studio';
$trang_hien_tai = 'dich-vu';

// CSS riêng cho phần slider dịch vụ
$css_rieng = [
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css',
    BASE_URL . 'assets/css/at.css',
    BASE_URL . 'assets/css/dich-vu.css?v=' . time()
];

$danh_sach_muc = [
    [
        'id' => 'vay-cuoi',
        'tieu_de' => '| VÁY CƯỚI &amp; CONCEPT',
        'mo_ta' => 'Thương hiệu cung cấp trang phục cưới và góc chụp uy tín',
        'items' => [
            ['id' => 37, 'img' => 'cuoi12.png', 'title' => 'Look 1 - Royal White'],
            ['id' => 38, 'img' => 'cuoi2.png', 'title' => 'Look 2 - Classic Couple'],
            ['id' => 39, 'img' => 'cuoi3.png', 'title' => 'Look 3 - European Vintage'],
            ['id' => 40, 'img' => 'cuoi4.png', 'title' => 'Look 4 - Modern Confetti'],
            ['id' => 41, 'img' => 'cuoi5.png', 'title' => 'Look 5 - Champagne Toast'],
            ['id' => 42, 'img' => 'cuoi6.png', 'title' => 'Look 6 - Luxury Golden'],
            ['id' => 43, 'img' => 'cuoi7.png', 'title' => 'Look 7 - Spring Blossom'],
            ['id' => 44, 'img' => 'cuoi8.png', 'title' => 'Look 8 - Minimalist White'],
        ]
    ],
    [
        'id' => 'ao-dai',
        'tieu_de' => '| ÁO DÀI',
        'mo_ta' => 'Bộ sưu tập áo dài cưới hỏi, lễ gia tiên và concept áo dài nghệ thuật',
        'items' => [
            ['id' => 57, 'img' => 'aodai1.png', 'title' => 'Áo Dài 1 - Tĩnh Mịch Hoa Loa Kèn'],
            ['id' => 58, 'img' => 'aodai2.png', 'title' => 'Áo Dài 2 - Thanh Khí Sen Trắng'],
            ['id' => 59, 'img' => 'aodai3.png', 'title' => 'Áo Dài 3 - Duyên Xanh Bên Quạt'],
            ['id' => 60, 'img' => 'aodai4.png', 'title' => 'Áo Dài 4 - Sắc Hồng Gia Tiên'],
            ['id' => 61, 'img' => 'aodai5.png', 'title' => 'Áo Dài 5 - Nàng Xuân Cổng Cổ'],
            ['id' => 62, 'img' => 'aodai6.png', 'title' => 'Áo Dài 6 - Khúc Cầm Nón Hoa'],
            ['id' => 63, 'img' => 'aodai7.png', 'title' => 'Áo Dài 7 - Mộng Sen Voan Trắng'],
            ['id' => 64, 'img' => 'aodai8.png', 'title' => 'Áo Dài 8 - Phu Thê Son Sắt'],
        ]
    ],
    [
        'id' => 'nuoc-ngoai',
        'tieu_de' => '| HANBOK',
        'mo_ta' => 'Gói chụp ngoại cảnh và concept phong cách hàn quốc',
        'items' => [
            ['id' => 93,  'img' => 'han1.png', 'title' => 'Hanbok 1 - Palace Corridor'],
            ['id' => 94,  'img' => 'han2.png', 'title' => 'Hanbok 2 - Traditional Hanok'],
            ['id' => 95,  'img' => 'han3.png', 'title' => 'Hanbok 3 - Cherry Blossom Palace'],
            ['id' => 96,  'img' => 'han4.png', 'title' => 'Hanbok 4 - Winter Snow'],
            ['id' => 97,  'img' => 'han5.png', 'title' => 'Hanbok 5 - Autumn Village'],
            ['id' => 98,  'img' => 'han6.png', 'title' => 'Hanbok 6 - Royal Pavilion'],
            ['id' => 99,  'img' => 'han7.png', 'title' => 'Hanbok 7 - Traditional Wedding Gate'],
            ['id' => 100, 'img' => 'han8.png', 'title' => 'Hanbok 8 - Outdoor Garden'],
        ]
    ]
];

require_once __DIR__ . '/../includes/header.php';
?>

<main class="services-page" style="padding: 40px 5%; max-width: 1400px; margin: 0 auto;">

    <?php foreach ($danh_sach_muc as $muc): ?>
    <section class="service-slider-section" data-slider="<?= htmlspecialchars($muc['id']) ?>" style="margin-bottom: 60px;">
        <div style="margin-bottom: 25px;">
            <h2 style="font-size: 28px; font-family: 'Playfair Display', serif; font-weight: 500; letter-spacing: 1px; color: #111;">
                <?= $muc['tieu_de'] ?>
            </h2>
            <p style="color: #666; font-size: 14px; margin-top: 5px;"><?= htmlspecialchars($muc['mo_ta']) ?></p>
        </div>

        <div class="slider-wrapper" style="position: relative; width: 100%;">
            <button type="button" class="btn-prev" aria-label="Trước" style="position: absolute; left: 15px; top: 210px; transform: translateY(-50%); z-index: 10; background: rgba(255, 255, 255, 0.75); color: #111; border: none; width: 38px; height: 38px; border-radius: 50%; cursor: pointer; display: none; align-items: center; justify-content: center; font-size: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.15); transition: background 0.2s;" onmouseover="this.style.background='#fff'" onmouseout="this.style.background='rgba(255, 255, 255, 0.75)'">❮</button>

            <div class="slider-container" style="overflow: hidden; width: 100%;">
                <div class="image-track" style="display: flex; gap: 20px; transition: transform 0.4s ease-in-out; will-change: transform;">
                    <?php foreach ($muc['items'] as $item): ?>
                    <a href="service-detail-view.php?id=<?= (int)$item['id'] ?>" class="slider-item" style="min-width: calc(25% - 15px); flex: 0 0 calc(25% - 15px); background: #f9f9f9; border-radius: 8px; overflow: hidden; text-decoration: none; display: block;">
                        <div style="width: 100%; height: 420px; overflow: hidden; background: #eee;">
                            <img src="<?= BASE_URL ?>assets/uploads/<?= htmlspecialchars($item['img']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                        </div>
                        <p style="padding: 15px 10px; text-align: center; font-size: 14px; font-weight: 500; color: #333; letter-spacing: 0.5px;"><?= htmlspecialchars($item['title']) ?></p>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <button type="button" class="btn-next" aria-label="Tiếp" style="position: absolute; right: 15px; top: 210px; transform: translateY(-50%); z-index: 10; background: rgba(255, 255, 255, 0.75); color: #111; border: none; width: 38px; height: 38px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.15); transition: background 0.2s;" onmouseover="this.style.background='#fff'" onmouseout="this.style.background='rgba(255, 255, 255, 0.75)'">❯</button>
        </div>
    </section>
    <?php endforeach; ?>

</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const sections = document.querySelectorAll('.service-slider-section');

    sections.forEach((section) => {
        const track = section.querySelector('.image-track');
        const btnPrev = section.querySelector('.btn-prev');
        const btnNext = section.querySelector('.btn-next');
        const items = track.children;
        let currentIndex = 0;

        function updateSlider() {
            const totalItems = items.length;
            const visibleCount = window.innerWidth <= 768 ? 1 : 4;
            const maxIndex = Math.max(0, totalItems - visibleCount);

            if (currentIndex < 0) currentIndex = 0;
            if (currentIndex > maxIndex) currentIndex = maxIndex;

            if (totalItems === 0) {
                btnPrev.style.display = 'none';
                btnNext.style.display = 'none';
                return;
            }

            const itemWidth = items[0].getBoundingClientRect().width;
            const gap = parseFloat(getComputedStyle(track).gap) || 20;
            const movePx = currentIndex * (itemWidth + gap);

            track.style.transform = `translateX(-${movePx}px)`;
            btnPrev.style.display = currentIndex === 0 ? 'none' : 'flex';
            btnNext.style.display = currentIndex >= maxIndex ? 'none' : 'flex';
        }

        btnPrev.addEventListener('click', () => {
            currentIndex--;
            updateSlider();
        });

        btnNext.addEventListener('click', () => {
            currentIndex++;
            updateSlider();
        });

        window.addEventListener('resize', updateSlider);
        updateSlider();
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

