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

    // Lấy các input ngày và giờ mới
    const ngayNhanInput = document.getElementById("ngay-nhan");
    const gioNhanInput = document.getElementById("gio-nhan");
    const ngayTraInput = document.getElementById("ngay-tra");
    const gioTraInput = document.getElementById("gio-tra");
    const serviceId = oSoLuong.form?.querySelector('input[name="service_id"]')?.value;

    if (!ngayNhanInput || !ngayTraInput || !gioNhanInput || !gioTraInput) return;

    // Thiết lập min cho ngày trả khi ngày nhận thay đổi
    ngayNhanInput.addEventListener("change", function () {
        ngayTraInput.min = ngayNhanInput.value;
        if (ngayTraInput.value < ngayNhanInput.value) {
            ngayTraInput.value = ngayNhanInput.value;
        }
        kiemTraVaGoiAjax();
    });

    gioNhanInput.addEventListener("change", kiemTraVaGoiAjax);
    ngayTraInput.addEventListener("change", kiemTraVaGoiAjax);
    gioTraInput.addEventListener("change", kiemTraVaGoiAjax);

    function kiemTraVaGoiAjax() {
        const ngayNhan = ngayNhanInput.value;
        const gioNhan = gioNhanInput.value || '08:00';
        const ngayTra = ngayTraInput.value;
        const gioTra = gioTraInput.value || '17:00';

        if (!ngayNhan || !ngayTra) return;

        const thoiGianNhanStr = `${ngayNhan} ${gioNhan}:00`;
        const thoiGianTraStr = `${ngayTra} ${gioTra}:00`;

        const btnDatThuê = document.querySelector('.rental-button');

        // Ràng buộc thời gian trả phải sau thời gian nhận (Có báo lỗi alert)[cite: 11]
        if (new Date(thoiGianTraStr) <= new Date(thoiGianNhanStr)) {
            alert('Thời gian trả phải lớn hơn thời gian nhận! Vui lòng chọn lại.');[cite: 11]
            
            // Khóa nút đặt thuê lại nếu thời gian không hợp lệ[cite: 11]
            if (btnDatThuê) {
                btnDatThuê.disabled = true;
                btnDatThuê.style.opacity = '0.5';
                btnDatThuê.style.cursor = 'not-allowed';
            }
            return;
        }

        kiemTraTonKhoTheoThoiGian(thoiGianNhanStr, thoiGianTraStr);
    }

    // Hàm gọi AJAX kiểm tra tồn kho theo khung giờ thực tế[cite: 11]
    function kiemTraTonKhoTheoThoiGian(batDau, ketThuc) {
        if (!serviceId) return;

        fetch(`kiem_tra_kho.php?id=${serviceId}&bat_dau=${encodeURIComponent(batDau)}&ket_thuc=${encodeURIComponent(ketThuc)}`)
            .then(res => res.json())
            .then(data => {
                const btnDatThuê = document.querySelector('.rental-button'); 
                const nhanTrangThai = document.getElementById('nhan-trang-thai'); 

                if (data.con_lai !== undefined) {
                    toiDa = parseInt(data.con_lai, 10);
                    oSoLuong.setAttribute('data-toi-da', toiDa);

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
