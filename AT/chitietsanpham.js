
/* =========================
   DỮ LIỆU SẢN PHẨM
========================= */

const products = {

    /* =====================
       8 MÁY ẢNH
    ====================== */

    1: {
        name: "Canon EOS R6",
        image: "../images/cam1.png",
        category: "MÁY ẢNH",
        type: "Máy ảnh",
        price: "500.000đ/ngày",
        rentalType: "Thuê theo ngày",
        description: "Máy ảnh Full Frame phù hợp cho chụp chân dung và sự kiện.",
        fullDescription: "Canon EOS R6 là máy ảnh phù hợp cho các buổi chụp chân dung, sự kiện và nhiều nhu cầu chụp ảnh khác tại Studio."
    },

    2: {
        name: "Sony A7 III",
        image: "../images/cam2.png",
        category: "MÁY ẢNH",
        type: "Máy ảnh",
        price: "450.000đ/ngày",
        rentalType: "Thuê theo ngày",
        description: "Máy ảnh đa dụng, chất lượng hình ảnh cao, phù hợp chụp Studio.",
        fullDescription: "Sony A7 III có thiết kế hiện đại, chất lượng hình ảnh tốt và phù hợp với nhiều thể loại chụp ảnh."
    },

    3: {
        name: "Canon EOS 90D",
        image: "../images/cam3.png",
        category: "MÁY ẢNH",
        type: "Máy ảnh",
        price: "350.000đ/ngày",
        rentalType: "Thuê theo ngày",
        description: "Máy ảnh Canon dễ sử dụng, thích hợp cho người mới bắt đầu.",
        fullDescription: "Canon EOS 90D là lựa chọn phù hợp cho người mới làm quen với máy ảnh và các buổi chụp ảnh cá nhân."
    },

    4: {
        name: "Sony A6400",
        image: "../images/cam4.png",
        category: "MÁY ẢNH",
        type: "Máy ảnh",
        price: "300.000đ/ngày",
        rentalType: "Thuê theo ngày",
        description: "Thiết kế nhỏ gọn, tiện lợi cho chụp ảnh cá nhân.",
        fullDescription: "Sony A6400 có kích thước nhỏ gọn, dễ mang theo và phù hợp cho các buổi chụp ảnh cá nhân."
    },

    5: {
        name: "Fujifilm X-T5",
        image: "../images/cam5.png",
        category: "MÁY ẢNH",
        type: "Máy ảnh",
        price: "400.000đ/ngày",
        rentalType: "Thuê theo ngày",
        description: "Máy ảnh phong cách cổ điển, phù hợp chụp chân dung và lifestyle.",
        fullDescription: "Fujifilm X-T5 phù hợp với các concept chụp chân dung, lifestyle và những bộ ảnh mang phong cách riêng."
    },

    6: {
        name: "Canon EOS R10",
        image: "../images/cam6.png",
        category: "MÁY ẢNH",
        type: "Máy ảnh",
        price: "320.000đ/ngày",
        rentalType: "Thuê theo ngày",
        description: "Máy ảnh nhỏ gọn, lấy nét nhanh và dễ dàng sử dụng.",
        fullDescription: "Canon EOS R10 có thiết kế nhỏ gọn và dễ sử dụng, phù hợp cho các buổi chụp ảnh tại Studio."
    },

    7: {
        name: "Nikon Z6 II",
        image: "../images/cam7.png",
        category: "MÁY ẢNH",
        type: "Máy ảnh",
        price: "450.000đ/ngày",
        rentalType: "Thuê theo ngày",
        description: "Máy ảnh Full Frame phù hợp cho nhiều thể loại chụp.",
        fullDescription: "Nikon Z6 II phù hợp với nhiều nhu cầu chụp ảnh khác nhau, từ chân dung đến chụp sự kiện."
    },

    8: {
        name: "Fujifilm X-S20",
        image: "../images/cam8.png",
        category: "MÁY ẢNH",
        type: "Máy ảnh",
        price: "380.000đ/ngày",
        rentalType: "Thuê theo ngày",
        description: "Máy ảnh nhỏ gọn, phù hợp chụp ảnh và quay video.",
        fullDescription: "Fujifilm X-S20 là lựa chọn phù hợp cho cả chụp ảnh và quay video với thiết kế nhỏ gọn."
    },


    /* =====================
       8 CHỖ CHỤP
    ====================== */

    9: {
        name: "Phòng Studio Trắng",
        image: "../images/phong1.png",
        category: "STUDIO",
        type: "Chỗ chụp ảnh",
        price: "200.000đ/giờ",
        rentalType: "Thuê theo giờ",
        description: "Không gian trắng tối giản, phù hợp chụp chân dung và sản phẩm.",
        fullDescription: "Phòng Studio Trắng có không gian đơn giản, sạch sẽ và phù hợp cho chụp chân dung, sản phẩm và nhiều concept khác."
    },

    10: {
        name: "Phòng Vintage",
        image: "../images/phong2.png",
        category: "STUDIO",
        type: "Chỗ chụp ảnh",
        price: "250.000đ/giờ",
        rentalType: "Thuê theo giờ",
        description: "Không gian mang phong cách vintage, ấm áp và độc đáo.",
        fullDescription: "Phòng Vintage được thiết kế theo phong cách cổ điển, thích hợp cho những bộ ảnh nhẹ nhàng và cá tính."
    },

    11: {
        name: "Phòng Concept Hàn Quốc",
        image: "../images/phong3.png",
        category: "STUDIO",
        type: "Chỗ chụp ảnh",
        price: "220.000đ/giờ",
        rentalType: "Thuê theo giờ",
        description: "Không gian sáng, nhẹ nhàng phù hợp chụp ảnh cá nhân.",
        fullDescription: "Phòng Concept Hàn Quốc có không gian sáng và nhẹ nhàng, phù hợp với các concept chụp ảnh cá nhân."
    },

    12: {
        name: "Khu Chụp Ngoài Trời",
        image: "../images/phong4.png",
        category: "STUDIO",
        type: "Chỗ chụp ảnh",
        price: "300.000đ/giờ",
        rentalType: "Thuê theo giờ",
        description: "Không gian ngoài trời với nhiều góc chụp tự nhiên.",
        fullDescription: "Khu chụp ngoài trời có nhiều góc chụp tự nhiên, phù hợp với các bộ ảnh lifestyle và ngoại cảnh."
    },

    13: {
        name: "Phòng Couple",
        image: "../images/phong5.png",
        category: "STUDIO",
        type: "Chỗ chụp ảnh",
        price: "280.000đ/giờ",
        rentalType: "Thuê theo giờ",
        description: "Không gian dành cho các concept chụp ảnh đôi.",
        fullDescription: "Phòng Couple được thiết kế dành cho các concept chụp ảnh đôi, kỷ niệm và các bộ ảnh tình cảm."
    },

    14: {
        name: "Phòng Concept Hoa",
        image: "../images/phong6.png",
        category: "STUDIO",
        type: "Chỗ chụp ảnh",
        price: "250.000đ/giờ",
        rentalType: "Thuê theo giờ",
        description: "Không gian nhiều hoa và màu sắc phù hợp chụp concept.",
        fullDescription: "Phòng Concept Hoa có nhiều hoa và màu sắc, tạo không gian phù hợp cho các bộ ảnh sáng tạo."
    },

    15: {
        name: "Phòng Minimal",
        image: "../images/phong7.png",
        category: "STUDIO",
        type: "Chỗ chụp ảnh",
        price: "180.000đ/giờ",
        rentalType: "Thuê theo giờ",
        description: "Không gian tối giản, phù hợp nhiều concept khác nhau.",
        fullDescription: "Phòng Minimal có thiết kế tối giản, dễ dàng kết hợp với nhiều loại trang phục và concept chụp ảnh."
    },

    16: {
        name: "Phòng Luxury",
        image: "../images/phong8.png",
        category: "STUDIO",
        type: "Chỗ chụp ảnh",
        price: "350.000đ/giờ",
        rentalType: "Thuê theo giờ",
        description: "Không gian sang trọng, thích hợp chụp ảnh thời trang.",
        fullDescription: "Phòng Luxury mang phong cách sang trọng, phù hợp cho các bộ ảnh thời trang và concept cao cấp."
    },


    /* =====================
       8 BỘ ĐỒ
    ====================== */

    17: {
        name: "Váy Vintage",
        image: "../images/bo1.png",
        category: "TRANG PHỤC",
        type: "Bộ đồ",
        price: "150.000đ/bộ",
        rentalType: "Thuê theo bộ",
        description: "Trang phục phong cách vintage dành cho các concept nhẹ nhàng.",
        fullDescription: "Váy Vintage phù hợp với những concept nhẹ nhàng, cổ điển và chụp ảnh trong Studio."
    },

    18: {
        name: "Áo Dài Truyền Thống",
        image: "../images/bo2.png",
        category: "TRANG PHỤC",
        type: "Bộ đồ",
        price: "180.000đ/bộ",
        rentalType: "Thuê theo bộ",
        description: "Áo dài phù hợp chụp ảnh cá nhân và kỷ niệm.",
        fullDescription: "Áo Dài Truyền Thống phù hợp với các bộ ảnh cá nhân, ảnh kỷ niệm và những dịp đặc biệt."
    },

    19: {
        name: "Hanbok Hàn Quốc",
        image: "../images/bo3.png",
        category: "TRANG PHỤC",
        type: "Bộ đồ",
        price: "200.000đ/bộ",
        rentalType: "Thuê theo bộ",
        description: "Trang phục Hanbok nhiều màu cho concept Hàn Quốc.",
        fullDescription: "Hanbok Hàn Quốc phù hợp với các concept mang phong cách Hàn Quốc và chụp ảnh cá nhân."
    },

    20: {
        name: "Vest Nam",
        image: "../images/bo4.png",
        category: "TRANG PHỤC",
        type: "Bộ đồ",
        price: "200.000đ/bộ",
        rentalType: "Thuê theo bộ",
        description: "Bộ vest lịch sự phù hợp chụp ảnh cá nhân và sự kiện.",
        fullDescription: "Vest Nam mang phong cách lịch sự, phù hợp cho ảnh cá nhân, ảnh thời trang và các sự kiện."
    },

    21: {
        name: "Váy Công Chúa",
        image: "../images/bo5.png",
        category: "TRANG PHỤC",
        type: "Bộ đồ",
        price: "220.000đ/bộ",
        rentalType: "Thuê theo bộ",
        description: "Váy phong cách nữ tính, phù hợp chụp ảnh concept.",
        fullDescription: "Váy Công Chúa phù hợp với những concept nữ tính, nhẹ nhàng và các bộ ảnh sáng tạo."
    },

    22: {
        name: "Bộ Đồ Retro",
        image: "../images/bo6.png",
        category: "TRANG PHỤC",
        type: "Bộ đồ",
        price: "160.000đ/bộ",
        rentalType: "Thuê theo bộ",
        description: "Trang phục mang phong cách retro trẻ trung và cá tính.",
        fullDescription: "Bộ Đồ Retro mang phong cách trẻ trung, cá tính và phù hợp với các concept chụp ảnh độc đáo."
    },

    23: {
        name: "Bộ Bohemian",
        image: "../images/bo7.png",
        category: "TRANG PHỤC",
        type: "Bộ đồ",
        price: "170.000đ/bộ",
        rentalType: "Thuê theo bộ",
        description: "Trang phục phong cách tự do, phù hợp chụp ngoại cảnh.",
        fullDescription: "Bộ Bohemian phù hợp với những bộ ảnh ngoại cảnh, phong cách tự nhiên và phóng khoáng."
    },

    24: {
        name: "Bộ Trang Phục Hiện Đại",
        image: "../images/bo8.png",
        category: "TRANG PHỤC",
        type: "Bộ đồ",
        price: "180.000đ/bộ",
        rentalType: "Thuê theo bộ",
        description: "Trang phục hiện đại, phù hợp chụp ảnh thời trang.",
        fullDescription: "Bộ Trang Phục Hiện Đại phù hợp với các concept thời trang và những bộ ảnh mang phong cách hiện đại."
    }

};


/* =========================
   LẤY ID TRÊN URL
========================= */

const urlParams = new URLSearchParams(window.location.search);
const productId = urlParams.get("id");


/* =========================
   LẤY SẢN PHẨM
========================= */

const product = products[productId];


/* =========================
   LẤY CÁC PHẦN TỬ HTML
========================= */

const detailImage = document.getElementById("detail-image");

const detailName = document.getElementById("detail-name");

const detailCategory = document.getElementById("detail-category");

const detailPrice = document.getElementById("detail-price");

const detailDescription =
    document.getElementById("detail-description");

const detailType =
    document.getElementById("detail-type");

const detailRentalType =
    document.getElementById("detail-rental-type");

const breadcrumbName =
    document.getElementById("breadcrumb-name");

const fullDescription =
    document.getElementById("detail-full-description");


/* =========================
   HIỂN THỊ SẢN PHẨM
========================= */

if (product) {

    detailImage.src = product.image;

    detailImage.alt = product.name;

    detailName.textContent = product.name;

    detailCategory.textContent = product.category;

    detailPrice.textContent = product.price;

    detailDescription.textContent = product.description;

    detailType.textContent = product.type;

    detailRentalType.textContent = product.rentalType;

    breadcrumbName.textContent = product.name;

    fullDescription.textContent = product.fullDescription;

} else {

    detailName.textContent = "Không tìm thấy sản phẩm";

    detailDescription.textContent =
        "Sản phẩm bạn đang tìm kiếm không tồn tại.";

    detailPrice.textContent = "";

}


/* =========================
   SỐ LƯỢNG
========================= */

let quantity = 1;

const quantityText =
    document.getElementById("quantity");

const quantityMinus =
    document.getElementById("quantity-minus");

const quantityPlus =
    document.getElementById("quantity-plus");


quantityPlus.addEventListener("click", function () {

    quantity++;

    quantityText.textContent = quantity;

});


quantityMinus.addEventListener("click", function () {

    if (quantity > 1) {

        quantity--;

        quantityText.textContent = quantity;

    }

});


/* =========================
   NÚT ĐẶT THUÊ
========================= */

const rentalButton =
    document.getElementById("rental-button");

const rentalMessage =
    document.getElementById("rental-message");


rentalButton.addEventListener("click", function () {

    if (!product) {
        return;
    }

    rentalMessage.hidden = false;

    rentalMessage.textContent =
        "Đã chọn " +
        quantity +
        " " +
        product.name +
        ". Bạn có thể tiếp tục đặt thuê.";

});

