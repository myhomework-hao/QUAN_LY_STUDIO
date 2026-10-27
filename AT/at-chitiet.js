document.addEventListener('DOMContentLoaded', function () {
    const soLuongHienThi = document.getElementById("quantity");
    const oSoLuong = document.getElementById("so-luong-gui");
    const nutTru = document.getElementById("quantity-minus");
    const nutCong = document.getElementById("quantity-plus");
    
    if (!oSoLuong) return;

    let toiDa = parseInt(oSoLuong.dataset.toiDa, 10) || 1;
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
    const serviceId = oSoLuong.form?.querySelector('input[name="service_id"]')?.value;

    if (!oBatDau || !oKetThuc) return;

    // Ràng buộc giờ mở cửa studio (08:00 đến 21:00) cho input dạng datetime-local
    function kiemTraKhungGio(e) {
        let val = e.target.value;
        if (!val) return;
        let dateObj = new Date(val);
        let hour = dateObj.getHours();

        // Kiểm tra nếu chọn ngoài khung giờ 08:00 - 21:00
        if (hour < 8 || hour > 21) {
            alert('Studio chỉ mở cửa phục vụ từ 08:00 đến 21:00 hàng ngày. Vui lòng chọn lại giờ!');
            e.target.value = '';
        }
    }

    oBatDau.addEventListener("change", function () {
        oKetThuc.min = oBatDau.value;
        if (oKetThuc.value && oKetThuc.value < oBatDau.value) {
            oKetThuc.value = oBatDau.value;
        }
        kiemTraKhungGio(this);
        kiemTraTonKhoTheoThoiGian();
    });

    oKetThuc.addEventListener("change", function (e) {
        kiemTraKhungGio(this);
        kiemTraTonKhoTheoThoiGian();
    });

    // Hàm gọi AJAX kiểm tra tồn kho theo khung giờ thực tế
    function kiemTraTonKhoTheoThoiGian() {
        if (!serviceId || !oBatDau.value || !oKetThuc.value) return;

        fetch(`kiem_tra_kho.php?id=${serviceId}&bat_dau=${encodeURIComponent(oBatDau.value)}&ket_thuc=${encodeURIComponent(oKetThuc.value)}`)
            .then(res => res.json())
            .then(data => {
                const btnDatThuê = document.querySelector('.rental-button'); // Nút Đặt thuê
                const nhanTrangThai = document.getElementById('nhan-trang-thai'); // Nhãn số lượng trên trang chi tiết

                if (data.con_lai !== undefined) {
                    toiDa = parseInt(data.con_lai, 10);
                    oSoLuong.setAttribute('data-toi-da', toiDa);

                    // Cập nhật lại dòng chữ thông báo số lượng trên giao diện
                    if (nhanTrangThai) {
                        nhanTrangThai.innerText = toiDa > 0 ? `Còn ${toiDa} sản phẩm trong khung giờ này` : 'Hết hàng trong khung giờ này';
                        nhanTrangThai.style.backgroundColor = toiDa > 0 ? '' : '#ff4d4f';
                        nhanTrangThai.style.color = toiDa > 0 ? '' : '#fff';
                    }

                    if (toiDa === 0) {
                        alert('Khung giờ này đã hết sạch số lượng sản phẩm. Vui lòng chọn thời gian khác!');
                        soLuong = 0;
                        if (btnDatThuê) {
                            btnDatThuê.disabled = true;
                            btnDatThuê.style.opacity = '0.5';
                            btnDatThuê.style.cursor = 'not-allowed';
                        }
                    } else {
                        if (btnDatThuê) {
                            btnDatThuê.disabled = false;
                            btnDatThuê.style.opacity = '1';
                            btnDatThuê.style.cursor = 'pointer';
                        }
                        if (soLuong > toiDa) {
                            soLuong = toiDa;
                        } else if (soLuong < 1) {
                            soLuong = 1;
                        }
                    }
                    capNhatSoLuong();
                }
            })
            .catch(err => console.error('Lỗi kiểm tra kho:', err));
    }
});