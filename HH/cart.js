/* =========================
   CART.JS — dữ liệu sản phẩm + giỏ hàng
   Đặt trong includes/, nạp TRƯỚC common.js
========================= */

const IMG = (id) => `https://images.unsplash.com/${id}?auto=format&fit=crop&w=800&q=80`;

// Sửa tên, giá, ảnh sản phẩm tại đây (giá tính bằng VNĐ)
const PRODUCTS = [
    { id: "portrait-basic", cat: "goi-chup", name: "Gói Portrait cơ bản", price: 990000,
      desc: "1 giờ chụp, 1 trang phục, 15 ảnh chỉnh sửa.", img: IMG("photo-1534528741775-53994a69daeb"), physical: false },
    { id: "couple", cat: "goi-chup", name: "Gói Couple", price: 1890000,
      desc: "2 giờ chụp, 2 trang phục, 30 ảnh chỉnh sửa.", img: IMG("photo-1519741497674-611481863552"), physical: false },
    { id: "product-shoot", cat: "goi-chup", name: "Gói Product", price: 2490000,
      desc: "Chụp tối đa 10 sản phẩm, nền studio, 30 ảnh.", img: IMG("photo-1529139574466-a303027c1d8b"), physical: false },
    { id: "event", cat: "goi-chup", name: "Gói Event nửa ngày", price: 3500000,
      desc: "4 giờ ghi hình sự kiện, giao 100 ảnh chọn lọc.", img: IMG("photo-1554048612-b6a482bc67e5"), physical: false },
    { id: "print-a3", cat: "an-pham", name: "In ảnh Fine Art A3", price: 350000,
      desc: "Giấy mỹ thuật 300gsm, in màu bền trên 100 năm.", img: IMG("photo-1515886657613-9f3515b0c78f"), physical: true },
    { id: "photobook", cat: "an-pham", name: "Photobook 20 trang", price: 890000,
      desc: "Bìa cứng, giấy mờ, thiết kế theo bộ ảnh của bạn.", img: IMG("photo-1524504388940-b1c1722653e1"), physical: true },
    { id: "frame-a4", cat: "an-pham", name: "Khung ảnh gỗ A4", price: 290000,
      desc: "Khung gỗ sồi tối giản, kèm mặt kính.", img: IMG("photo-1496747611176-843222e1e57c"), physical: true },
    { id: "voucher-500", cat: "qua-tang", name: "Voucher quà tặng 500K", price: 500000,
      desc: "Gửi qua email, dùng cho mọi dịch vụ tại VIBE Studio.", img: IMG("photo-1534528741775-53994a69daeb"), physical: false }
];

const CART_KEY = "vibeCart";
const COUPON_KEY = "vibeCoupon";
const COUPONS = { VIBE10: 0.1 };   // mã giảm giá: VIBE10 = giảm 10%
const FREE_SHIP_FROM = 1000000;    // miễn phí ship từ mức này
const SHIP_FEE = 30000;            // phí ship cho sản phẩm cần giao

const fmt = (n) => n.toLocaleString("vi-VN") + "₫";
const findProduct = (id) => PRODUCTS.find((p) => p.id === id);

function read(key, fallback) {
    try { return JSON.parse(localStorage.getItem(key)) ?? fallback; }
    catch (e) { return fallback; }
}
function write(key, value) {
    try { localStorage.setItem(key, JSON.stringify(value)); } catch (e) {}
}

/* ----- Giỏ hàng (chỉ lưu id + số lượng) ----- */
function getCart() {
    return read(CART_KEY, []).filter((i) => findProduct(i.id) && i.qty > 0);
}
function saveCart(cart) {
    write(CART_KEY, cart);
    updateBadge();
}
function addToCart(id, qty = 1) {
    // Kiểm tra nếu chưa đăng nhập thì thông báo và chuyển hướng
    if (typeof IS_LOGGED_IN !== 'undefined' && !IS_LOGGED_IN) {
        alert("Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng!");
        window.location.href = "../auth/login.php"; // Thay đường dẫn trang login của bạn
        return;
    }

    const cart = getCart();
    const item = cart.find((i) => i.id === id);
    item ? (item.qty = Math.min(item.qty + qty, 20)) : cart.push({ id, qty });
    saveCart(cart);
    toast("Đã thêm vào giỏ hàng!");
}

function setQty(id, qty) {
    let cart = getCart();
    cart = qty <= 0
        ? cart.filter((i) => i.id !== id)
        : cart.map((i) => (i.id === id ? { ...i, qty: Math.min(qty, 20) } : i));
    saveCart(cart);
}
function clearCart() {
    saveCart([]);
    localStorage.removeItem(COUPON_KEY);
}

/* ----- Mã giảm giá ----- */
function getCoupon() {
    const code = read(COUPON_KEY, null);
    return code && COUPONS[code] ? code : null;
}
function applyCoupon(code) {
    code = (code || "").trim().toUpperCase();
    if (!COUPONS[code]) return false;
    write(COUPON_KEY, code);
    return true;
}
function removeCoupon() { localStorage.removeItem(COUPON_KEY); }

/* ----- Tính tiền ----- */
function totals() {
    const cart = getCart();
    const subtotal = cart.reduce((s, i) => s + findProduct(i.id).price * i.qty, 0);
    const code = getCoupon();
    const discount = code ? Math.round(subtotal * COUPONS[code]) : 0;
    const needShip = cart.some((i) => findProduct(i.id).physical);
    const shipping = needShip && subtotal > 0 && subtotal - discount < FREE_SHIP_FROM ? SHIP_FEE : 0;
    return { cart, subtotal, code, discount, needShip, shipping, total: subtotal - discount + shipping };
}

/* ----- Số lượng trên icon giỏ hàng ở header ----- */
function updateBadge() {
    const count = getCart().reduce((s, i) => s + i.qty, 0);
    document.querySelectorAll("[data-cart-count]").forEach((el) => {
        el.textContent = count;
        el.classList.toggle("is-empty", count === 0);
    });
}

/* ----- Thông báo nhỏ ----- */
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

window.addEventListener("storage", updateBadge);
