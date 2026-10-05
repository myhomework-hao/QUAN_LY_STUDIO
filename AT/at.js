/* =========================
   TRANG SẢN PHẨM
========================= */
//sanpham.js

/* =========================
   LẤY CÁC PHẦN TỬ HTML
========================= */

const categoryButtons =
    document.querySelectorAll(".category-button");

const productCards =
    document.querySelectorAll(".product-card");

const searchInput =
    document.getElementById("search-product");

const searchButton =
    document.getElementById("search-button");

const noProduct =
    document.getElementById("no-product");


/* =========================
   BIẾN LƯU DANH MỤC
========================= */

const nutDangChon = document.querySelector(".category-button.active");
let currentCategory = nutDangChon ? nutDangChon.dataset.category : "all";

/* =========================
   HÀM HIỂN THỊ SẢN PHẨM
========================= */

function hienThiSanPham() {

    const keyword =
        searchInput.value.toLowerCase().trim();

    let count = 0;


    productCards.forEach(function(card) {

        const category =
            card.dataset.category;

        const productName =
            card.querySelector(".product-name")
                .textContent
                .toLowerCase();

        const productDescription =
            card.querySelector(".product-description")
                .textContent
                .toLowerCase();


        /* Kiểm tra danh mục */

        const dungDanhMuc =
            currentCategory === "all" ||
            category === currentCategory;


        /* Kiểm tra từ khóa */

        const dungTuKhoa =
            productName.includes(keyword) ||
            productDescription.includes(keyword);


        /* Hiển thị hoặc ẩn */

        if (dungDanhMuc && dungTuKhoa) {

            card.style.display = "";

            count++;

        } else {

            card.style.display = "none";

        }

    });


    /* =========================
       KHÔNG TÌM THẤY
    ========================== */

    if (count === 0) {

        noProduct.hidden = false;

    } else {

        noProduct.hidden = true;

    }

}


/* =========================
   XỬ LÝ NÚT DANH MỤC
========================= */

categoryButtons.forEach(function(button) {

    button.addEventListener("click", function() {


        /* Xóa active ở tất cả nút */

        categoryButtons.forEach(function(item) {

            item.classList.remove("active");

        });


        /* Thêm active cho nút đang chọn */

        button.classList.add("active");


        /* Lấy danh mục */

        currentCategory =
            button.dataset.category;


        /* Hiển thị lại sản phẩm */

        hienThiSanPham();

    });

});


/* =========================
   TÌM KIẾM KHI NHẬP
========================= */

searchInput.addEventListener(
    "input",
    function() {

        hienThiSanPham();

    }
);


/* =========================
   NÚT TÌM KIẾM
========================= */

searchButton.addEventListener(
    "click",
    function() {

        hienThiSanPham();

    }
);


/* =========================
   ENTER ĐỂ TÌM KIẾM
========================= */

searchInput.addEventListener(
    "keydown",
    function(event) {

        if (event.key === "Enter") {

            hienThiSanPham();

        }

    }
);
hienThiSanPham();