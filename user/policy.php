<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';

$tieu_de_trang = 'Chính sách & Điều khoản - VIBE STUDIO';
$trang_hien_tai = 'chinh-sach';

require_once __DIR__ . '/../includes/header.php';
?>

<style>
    /* Styling đồng bộ chuẩn phong cách VIBE STUDIO */
    .policy-page {
        padding: 60px 8% 100px;
        background: #ffffff;
        color: #111111;
        min-height: calc(100vh - 80px);
    }

    .policy-header {
        text-align: center;
        margin-bottom: 50px;
    }

    .policy-header .small-title {
        font-size: 11px;
        letter-spacing: 3px;
        color: #888888;
        margin-bottom: 10px;
        text-transform: uppercase;
    }

    .policy-header h1 {
        font-size: clamp(32px, 5vw, 55px);
        font-weight: 600;
        line-height: 1.1;
    }

    .policy-header h1 i {
        font-style: italic;
        font-weight: 400;
    }

    /* Thanh Menu Tab Điều Hướng */
    .policy-nav {
        display: flex;
        justify-content: center;
        gap: 15px;
        border-bottom: 1px solid #eeeeee;
        margin-bottom: 40px;
        flex-wrap: wrap;
    }

    .policy-tab {
        padding: 12px 24px;
        background: transparent;
        border: none;
        border-bottom: 2px solid transparent;
        font-size: 13px;
        font-weight: 600;
        letter-spacing: 1px;
        color: #777777;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .policy-tab:hover, .policy-tab.active {
        color: #111111;
        border-bottom-color: #111111;
    }

    /* Nội dung các Section */
    .policy-content-section {
        display: none;
        max-width: 900px;
        margin: 0 auto;
        line-height: 1.8;
        font-size: 15px;
        color: #333333;
        scroll-margin-top: 40px; /* Tạo khoảng cách khi cuộn tới */
    }

    .policy-content-section.active {
        display: block;
        animation: fadeIn 0.4s ease;
    }

    .policy-content-section h2 {
        font-size: 24px;
        margin-bottom: 20px;
        color: #111111;
        border-bottom: 1px solid #111111;
        padding-bottom: 10px;
        letter-spacing: -0.5px;
    }

    .policy-content-section h3 {
        font-size: 17px;
        margin: 25px 0 10px;
        color: #111111;
    }

    .policy-content-section ul {
        padding-left: 20px;
        margin-bottom: 20px;
    }

    .policy-content-section ul li {
        margin-bottom: 8px;
    }

    .policy-box {
        background: #f9f9f9;
        padding: 25px;
        border-left: 3px solid #111111;
        margin: 20px 0;
        border-radius: 4px;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 768px) {
        .policy-page { padding: 40px 5% 60px; }
        .policy-tab { padding: 10px 15px; font-size: 12px; }
    }
</style>

<main class="policy-page">
    <div class="policy-header">
        <p class="small-title">VIBE STUDIO POLICIES</p>
        <h1>Chính sách & <i>Điều khoản</i></h1>
    </div>

    <!-- Thanh chuyển Tab chính sách -->
    <div class="policy-nav">
        <a href="#dat-thue" class="policy-tab active" onclick="switchTab(event, 'dat-thue')">CHÍNH SÁCH ĐẶT THUÊ</a>
        <a href="#huy-don-hoan-tien" class="policy-tab" onclick="switchTab(event, 'huy-don-hoan-tien')">HỦY ĐƠN & HOÀN TIỀN</a>
        <a href="#boi-thuong-hu-hong" class="policy-tab" onclick="switchTab(event, 'boi-thuong-hu-hong')">BỒI THƯỜNG & HƯ HỎNG</a>
        <a href="#bao-mat" class="policy-tab" onclick="switchTab(event, 'bao-mat')">BẢO MẬT THÔNG TIN</a>
    </div>

    <!-- 1. CHÍNH SÁCH ĐẶT THUÊ -->
    <div id="dat-thue" class="policy-content-section active">
        <h2>Chính Sách Đặt Thuê Thiết Bị & Studio</h2>
        <p>Chào mừng bạn đến với <strong>VIBE STUDIO</strong>. Nhằm đảm bảo trải nghiệm thuê máy ảnh, phòng chụp và phụ kiện diễn ra thuận lợi, chúng tôi áp dụng quy trình đặt thuê như sau:</p>
        
        <h3>1. Giấy tờ & Thủ tục thế chấp</h3>
        <ul>
            <li>Khách hàng cá nhân vui lòng xuất trình <strong>CCCD / Hộ chiếu gốc</strong> chính chủ khi nhận thiết bị.</li>
            <li>Trường hợp không gửi lại giấy tờ gốc, quý khách vui lòng đặt cọc tiền mặt hoặc chuyển khoản 50% - 100% giá trị thiết bị (tùy thuộc loại thiết bị).</li>
        </ul>

        <h3>2. Thời gian tính cước thuê</h3>
        <ul>
            <li><strong>Thuê theo giờ (Chỗ chụp/Studio):</strong> Được tính đúng từ khung giờ quý khách đã đăng ký trên hệ thống. Việc đến trễ sẽ không được trừ lùi thời gian.</li>
            <li><strong>Thuê theo ngày (Thiết bị/Trang phục):</strong> 1 ngày thuê được tính tròn 24 tiếng kể từ thời điểm bàn giao sản phẩm.</li>
        </ul>

        <div class="policy-box">
            <strong>Lưu ý quan trọng:</strong> Quý khách vui lòng kiểm tra kỹ tình trạng máy ảnh, phụ kiện, trang phục tại thời điểm bàn giao trước khi rời khỏi studio.
        </div>
    </div>

    <!-- 2. QUY ĐỊNH HỦY ĐƠN & HOÀN TIỀN -->
    <div id="huy-don-hoan-tien" class="policy-content-section">
        <h2>Quy Định Hủy Đơn & Hoàn Tiền</h2>
        <p>VIBE Studio luôn tạo điều kiện tối đa cho quý khách trong việc thay đổi lịch trình công việc. Tuy nhiên, để đảm bảo công tác vận hành, quy định hủy lịch & hoàn tiền được áp dụng như sau:</p>

        <h3>1. Mức hoàn tiền khi hủy lịch</h3>
        <ul>
            <li><strong>Hủy trước 48 giờ:</strong> Hoàn 100% số tiền cọc hoặc tiền đã thanh toán.</li>
            <li><strong>Hủy từ 24 - 48 giờ:</strong> Hoàn 50% số tiền cọc.</li>
            <li><strong>Hủy dưới 24 giờ:</strong> Không hỗ trợ hoàn tiền cọc. Quý khách có thể chọn dời lịch 01 lần trong vòng 30 ngày (nếu báo trước 6 tiếng).</li>
        </ul>

        <h3>2. Thời gian & Phương thức hoàn tiền</h3>
        <p>Tiền sẽ được hoàn trả qua tài khoản ngân hàng chính chủ của quý khách trong vòng <strong>24h - 48h làm việc</strong> kể từ khi xác nhận hủy đơn thành công.</p>
    </div>

    <!-- 3. BỒI THƯỜNG & HƯ HỎNG -->
    <div id="boi-thuong-hu-hong" class="policy-content-section">
        <h2>Bồi Thường & Hư Hỏng Thiết Bị</h2>
        <p>Khách hàng có trách nhiệm bảo quản tài sản của VIBE Studio trong suốt thời hạn hợp đồng đặt thuê.</p>

        <h3>1. Thiết bị hỏng hóc / Trầy xước</h3>
        <ul>
            <li>Nếu thiết bị gặp sự cố phần mềm/phần cứng do lỗi kỹ thuật tự nhiên: Studio đổi máy tương đương ngay lập tức và không thu phụ phí.</li>
            <li>Nếu hỏng hóc do va đập, rơi vỡ, vào nước hoặc sử dụng sai hướng dẫn: Khách hàng chịu toàn bộ chi phí sửa chữa tại Trung tâm bảo hành chính hãng.</li>
        </ul>

        <h3>2. Mất mát thiết bị / Phụ kiện</h3>
        <p>Trường hợp làm mất mát thiết bị hoặc phụ kiện đi kèm (pin, thẻ nhớ, nắp đậy lens, chân máy...), khách hàng có trách nhiệm bồi thường 100% giá trị thị trường hiện tại của linh kiện đó.</p>
    </div>

    <!-- 4. BẢO MẬT THÔNG TIN -->
    <div id="bao-mat" class="policy-content-section">
        <h2>Bảo Mật Thông Tin Khách Hàng</h2>
        <p>VIBE Studio cam kết bảo vệ thông tin cá nhân của quý khách hàng tuyệt đối.</p>

        <h3>1. Mục đích thu thập thông tin</h3>
        <p>Thông tin cá nhân (Họ tên, SĐT, Email, Hình ảnh CCCD) chỉ được dùng cho mục đích xác thực đơn hàng, liên hệ bàn giao sản phẩm và xử lý các vấn đề phát sinh trong đơn thuê.</p>

        <h3>2. Cam kết bảo mật</h3>
        <ul>
            <li>Không chia sẻ, bán hoặc trao đổi thông tin khách hàng cho bất kỳ bên thứ ba nào khác.</li>
            <li>Mọi dữ liệu thanh toán và đơn đặt lịch đều được mã hóa an toàn trên hệ thống của chúng tôi.</li>
        </ul>
    </div>
</main>

<script>
    function switchTab(event, targetId, doScroll = true) {
        if (event) event.preventDefault();
        
        // Bỏ active tất cả các tab & section
        document.querySelectorAll('.policy-tab').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.policy-content-section').forEach(section => section.classList.remove('active'));

        // Active tab và section chọn
        const activeTab = document.querySelector(`.policy-tab[href="#${targetId}"]`);
        const targetSection = document.getElementById(targetId);

        if (activeTab) activeTab.classList.add('active');
        if (targetSection) {
            targetSection.classList.add('active');
            
            // Cuộn mượt đến đúng vị trí mục khi được click
            if (doScroll) {
                targetSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        // Cập nhật URL Hash không reload trang
        history.pushState(null, null, `#${targetId}`);
    }

    // Tự động mở đúng Tab và cuộn tới khi load trang có kèm theo dấu #hashtag
    window.addEventListener('DOMContentLoaded', () => {
        const hash = window.location.hash.replace('#', '');
        if (hash && document.getElementById(hash)) {
            switchTab(null, hash, true);
        }
    });

    // Lắng nghe sự kiện hashchange để cuộn mượt khi người dùng click lại link footer từ trong trang
    window.addEventListener('hashchange', () => {
        const hash = window.location.hash.replace('#', '');
        if (hash && document.getElementById(hash)) {
            switchTab(null, hash, true);
        }
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>