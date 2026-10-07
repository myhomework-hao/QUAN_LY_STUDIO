document.addEventListener("DOMContentLoaded", function () {
    const categoryButtons = document.querySelectorAll(".category-button");
    const categorySections = document.querySelectorAll(".category-section-box");
    const searchInput = document.getElementById("search-product");
    const searchButton = document.getElementById("search-button");
    const noProductMessage = document.getElementById("no-product");

    // Hàm thực hiện lọc theo danh mục và từ khóa tìm kiếm
    function applyFilter() {
        const activeBtn = document.querySelector(".category-button.active");
        const selectedCategory = activeBtn ? activeBtn.getAttribute("data-category") : "all";
        const keyword = searchInput ? searchInput.value.trim().toLowerCase() : "";

        let totalVisibleProducts = 0;

        categorySections.forEach(section => {
            const sectionCategory = section.getAttribute("data-category");
            const productCards = section.querySelectorAll(".product-card");
            let sectionVisibleCount = 0;

            // Kiểm tra xem Khung này có khớp với Danh mục đang chọn hay không
            const isCategoryMatch = (selectedCategory === "all" || selectedCategory === sectionCategory);

            if (isCategoryMatch) {
                productCards.forEach(card => {
                    const name = card.querySelector(".product-name")?.textContent.toLowerCase() || "";
                    const desc = card.querySelector(".product-description")?.textContent.toLowerCase() || "";
                    
                    const isKeywordMatch = (keyword === "" || name.includes(keyword) || desc.includes(keyword));

                    if (isKeywordMatch) {
                        card.style.display = "";
                        sectionVisibleCount++;
                    } else {
                        card.style.display = "none";
                    }
                });
            }

            // Nếu khớp danh mục VÀ có sản phẩm hiển thị thì HIỆN khung lớn, ngược lại ẨN hẳn
            if (isCategoryMatch && sectionVisibleCount > 0) {
                section.style.display = "block";
                totalVisibleProducts += sectionVisibleCount;
            } else {
                section.style.display = "none";
            }
        });

        // Hiển thị dòng thông báo nếu không tìm thấy sản phẩm nào
        if (noProductMessage) {
            if (totalVisibleProducts === 0) {
                noProductMessage.removeAttribute("hidden");
                noProductMessage.style.display = "block";
            } else {
                noProductMessage.setAttribute("hidden", "true");
                noProductMessage.style.display = "none";
            }
        }
    }

    // Sự kiện khi bấm vào các nút Danh mục (Tất cả / Máy ảnh / Chỗ chụp ảnh / Bộ đồ)
    categoryButtons.forEach(button => {
        button.addEventListener("click", function (e) {
            e.preventDefault();

            categoryButtons.forEach(btn => btn.classList.remove("active"));
            this.classList.add("active");

            // Reset ô tìm kiếm khi chuyển danh mục
            if (searchInput) searchInput.value = "";

            applyFilter();
        });
    });

    // Sự kiện khi gõ vào ô tìm kiếm
    if (searchInput) {
        searchInput.addEventListener("input", applyFilter);
    }

    if (searchButton) {
        searchButton.addEventListener("click", function (e) {
            e.preventDefault();
            applyFilter();
        });
    }

    // Chạy lọc 1 lần đầu tiên khi vừa tải trang
    applyFilter();
});
