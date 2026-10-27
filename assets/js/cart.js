/* =========================================================
   CART.JS — Xử lý tương tác AJAX giỏ hàng client-side
   Đặt trong includes/, nạp TRƯỚC common.js
========================================================= */

/**
 * Hàm gửi request AJAX thêm sản phẩm vào giỏ hàng PHP Session
 * @param {number|string} serviceId - ID dịch vụ/sản phẩm từ database
 * @param {number} qty - Số lượng cần thêm (mặc định: 1)
 * @param {string} ngayDat - Ngày đặt lịch (nếu có)
 * @param {string} gioDat - Giờ đặt lịch (nếu có)
 */
function addToCartAjax(serviceId, qty = 1, ngayDat = '', gioDat = '') {
    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('id', serviceId);
    formData.append('qty', qty);
    if (ngayDat) formData.append('ngay_dat', ngayDat);
    if (gioDat) formData.append('gio_dat', gioDat);

    fetch('../api/cart_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartBadge(data.total_qty);
            toast(data.message || 'Đã thêm sản phẩm vào giỏ hàng!');
        } else {
            toast(data.message || 'Có lỗi xảy ra, vui lòng thử lại!');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        toast('Không thể kết nối tới máy chủ!');
    });
}

/**
 * Cập nhật số lượng trên Icon giỏ hàng ở Header
 * @param {number} count - Tổng số lượng sản phẩm
 */
function updateCartBadge(count) {
    document.querySelectorAll("[data-cart-count]").forEach((el) => {
        el.textContent = count;
        el.classList.toggle("is-empty", count === 0);
    });
}

/**
 * Hiển thị thông báo Toast nhỏ ở góc màn hình
 * @param {string} message - Nội dung thông báo
 */
let toastTimer;
function toast(message) {
    let el = document.getElementById("toast");
    if (!el) {
        el = document.createElement("div");
        el.id = "toast";
        el.setAttribute("role", "status");
        document.body.appendChild(el);
    }
    el.textContent = message;
    el.classList.add("show");
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => el.classList.remove("show"), 2200);
}

// Bắt sự kiện click vào các nút có thuộc tính `data-add-cart` trên toàn trang
document.addEventListener('click', function (e) {
    const btn = e.target.closest('[data-add-cart]');
    if (btn) {
        e.preventDefault();
        const serviceId = btn.dataset.addCart || btn.dataset.id;
        const qtyInput = document.querySelector('[name="quantity"]');
        const qty = qtyInput ? parseInt(qtyInput.value) || 1 : 1;
        
        const ngayDatInput = document.querySelector('[name="ngay_dat"]');
        const gioDatInput = document.querySelector('[name="gio_dat"]');
        const ngayDat = ngayDatInput ? ngayDatInput.value : '';
        const gioDat = gioDatInput ? gioDatInput.value : '';

        if (serviceId) {
            addToCartAjax(serviceId, qty, ngayDat, gioDat);
        }
    }
});