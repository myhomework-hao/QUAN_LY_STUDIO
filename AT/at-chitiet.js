const soLuongHienThi = document.getElementById("quantity");
const oSoLuong = document.getElementById("so-luong-gui");
const nutTru = document.getElementById("quantity-minus");
const nutCong = document.getElementById("quantity-plus");
const toiDa = parseInt(oSoLuong.dataset.toiDa, 10) || 1;

let soLuong = 1;

function capNhatSoLuong() {
    soLuongHienThi.textContent = soLuong;
    oSoLuong.value = soLuong;
}

nutCong.addEventListener("click", function () {
    if (soLuong < toiDa) {
        soLuong++;
        capNhatSoLuong();
    }
});

nutTru.addEventListener("click", function () {
    if (soLuong > 1) {
        soLuong--;
        capNhatSoLuong();
    }
});

const oBatDau = document.getElementById("bat-dau");
const oKetThuc = document.getElementById("ket-thuc");

oBatDau.addEventListener("change", function () {
    oKetThuc.min = oBatDau.value;
    if (oKetThuc.value && oKetThuc.value < oBatDau.value) {
        oKetThuc.value = oBatDau.value;
    }
});