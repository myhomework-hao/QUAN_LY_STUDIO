/* ==================================================
   VIBE STUDIO - TRANG CHI TIẾT DỊCH VỤ & ĐẶT THUÊ
   Số lượng + kiểm tra tồn kho + kiểm tra form giỏ hàng
================================================== */

document.addEventListener('DOMContentLoaded', function () {
    // --------------------------------------------------
    // PHẦN 1: SỐ LƯỢNG & KIỂM TRA TỒN KHO THEO THỜI GIAN
    // --------------------------------------------------
    const oSoLuong = document.getElementById('so-luong-gui');

    if (oSoLuong) {
        const soLuongHienThi = document.getElementById('quantity');
        const nutTru = document.getElementById('quantity-minus');
        const nutCong = document.getElementById('quantity-plus');
        const btnDatThue = document.querySelector('.rental-button');
        const nhanTrangThai = document.getElementById('nhan-trang-thai');

        const ngayNhanInput = document.getElementById('ngay-nhan');
        const gioNhanInput = document.getElementById('gio-nhan');
        const ngayTraInput = document.getElementById('ngay-tra');
        const gioTraInput = document.getElementById('gio-tra');
        const serviceId = oSoLuong.form?.querySelector('input[name="service_id"]')?.value;
        const oKichCo = document.getElementById('kich_co_chon');

        const donVi = (oSoLuong.dataset.donVi || '').trim().toLowerCase();
        const khoaDonVi = ['giờ', 'gio'].includes(donVi) ? 'gio'
            : ['ngày', 'ngay'].includes(donVi) ? 'ngay'
            : ['lượt', 'luot'].includes(donVi) ? 'luot'
            : ['bộ', 'bo'].includes(donVi) ? 'bo'
            : donVi;

        const laLuot = khoaDonVi === 'luot';
        const nhanDonVi = { gio: 'giờ', ngay: 'ngày', luot: 'lượt', bo: 'bộ' }[khoaDonVi] || donVi;

        const oGia = Math.max(0, parseInt(oSoLuong.dataset.gia, 10) || 0);
        const tongTienSo = document.getElementById('tong-tien-so');
        const tongTienChiTiet = document.getElementById('tong-tien-chi-tiet');

        const apiKho = oSoLuong.dataset.api || '../api/kiem_tra_kho.php';

        const toiDaBanDau = parseInt(oSoLuong.dataset.toiDa, 10);
        let toiDa = Number.isFinite(toiDaBanDau) ? Math.max(0, toiDaBanDau) : 1;
        let soLuong = toiDa > 0 ? 1 : 0;

        let maYeuCau = 0;
        let khungDaBao = '';

        function capNhatSoLuong() {
            if (soLuongHienThi) soLuongHienThi.textContent = soLuong;
            oSoLuong.value = soLuong;
            capNhatTongTien();
        }

        function batNut(choPhep) {
            if (!btnDatThue) return;

            btnDatThue.disabled = !choPhep;
            btnDatThue.style.opacity = choPhep ? '1' : '0.5';
            btnDatThue.style.cursor = choPhep ? 'pointer' : 'not-allowed';
        }

        function capNhatNhan(noiDung, hetHang) {
            if (!nhanTrangThai) return;

            nhanTrangThai.textContent = noiDung;
            nhanTrangThai.style.backgroundColor = hetHang ? '#ff4d4f' : '';
            nhanTrangThai.style.color = hetHang ? '#ffffff' : '';
        }

        function dinhDangThoiGian(ngay) {
            const hai = (so) => String(so).padStart(2, '0');

            return ngay.getFullYear() + '-' + hai(ngay.getMonth() + 1) + '-' + hai(ngay.getDate())
                + ' ' + hai(ngay.getHours()) + ':' + hai(ngay.getMinutes()) + ':00';
        }

        function dinhDangTien(soTien) {
            return new Intl.NumberFormat('vi-VN').format(soTien) + '₫';
        }

        function tinhSoDonVi(batDau, ketThuc) {
            if (khoaDonVi === 'luot' || khoaDonVi === 'bo') return 1;

            const giay = (ketThuc.getTime() - batDau.getTime()) / 1000;

            if (giay <= 0) return 0;
            if (khoaDonVi === 'gio') return Math.max(1, Math.ceil(giay / 3600));

            return Math.max(1, Math.round(giay / 86400));
        }

        function capNhatTongTien() {
            if (!tongTienSo) return;

            if (!ngayNhanInput || !ngayTraInput || !gioNhanInput || !gioTraInput) {
                tongTienSo.textContent = '—';
                return;
            }

            const batDau = new Date((ngayNhanInput.value + ' ' + (gioNhanInput.value || '08:00') + ':00').replace(' ', 'T'));
            const ketThuc = new Date((ngayTraInput.value + ' ' + (gioTraInput.value || '17:00') + ':00').replace(' ', 'T'));

            if (Number.isNaN(batDau.getTime()) || Number.isNaN(ketThuc.getTime())) {
                tongTienSo.textContent = '—';
                if (tongTienChiTiet) tongTienChiTiet.textContent = 'Chọn thời gian để xem giá';
                return;
            }

            const soDonVi = tinhSoDonVi(batDau, ketThuc);

            if (soDonVi === 0) {
                tongTienSo.textContent = '—';
                if (tongTienChiTiet) tongTienChiTiet.textContent = 'Thời gian trả phải sau thời gian nhận';
                return;
            }

            const daQua = ketThuc.getTime() <= Date.now();
            const batDauQua = batDau.getTime() <= Date.now();

            if (soLuong === 0) {
                tongTienSo.textContent = 'Hết hàng';
                if (tongTienChiTiet) tongTienChiTiet.textContent = 'Chọn khung giờ khác';
                return;
            }

            tongTienSo.textContent = dinhDangTien(oGia * soDonVi * soLuong);

            if (tongTienChiTiet) {
                tongTienChiTiet.textContent = soLuong + ' × ' + soDonVi + ' ' + nhanDonVi
                    + ' × ' + dinhDangTien(oGia) + '/' + nhanDonVi
                    + (batDauQua ? ' · giờ nhận đã qua, vui lòng chọn lại'
                        : daQua ? ' · khung giờ đã qua, vui lòng chọn lại' : '');
            }

            if (batDauQua || daQua) {
                batNut(false);
            }
        }

        capNhatSoLuong();

        if (toiDa === 0) {
            batNut(false);
        }

        if (nutCong) {
            nutCong.addEventListener('click', function () {
                if (soLuong < toiDa) {
                    soLuong++;
                    capNhatSoLuong();
                }
            });
        }

        if (nutTru) {
            nutTru.addEventListener('click', function () {
                if (soLuong > 1) {
                    soLuong--;
                    capNhatSoLuong();
                }
            });
        }

        if (ngayNhanInput && ngayTraInput && gioNhanInput && gioTraInput) {
            ngayNhanInput.addEventListener('change', function () {
                ngayTraInput.min = ngayNhanInput.value;

                if (ngayTraInput.value < ngayNhanInput.value) {
                    ngayTraInput.value = ngayNhanInput.value;
                }

                kiemTraVaGoiAjax(true);
            });

            gioNhanInput.addEventListener('change', function () { kiemTraVaGoiAjax(true); });
            ngayTraInput.addEventListener('change', function () { kiemTraVaGoiAjax(true); });
            gioTraInput.addEventListener('change', function () { kiemTraVaGoiAjax(true); });

            if (oKichCo) {
                oKichCo.addEventListener('change', function () { kiemTraVaGoiAjax(true); });
            }

            function kiemTraVaGoiAjax(baoHetHang) {
                const ngayNhan = ngayNhanInput.value;
                const gioNhan = gioNhanInput.value || '08:00';
                const ngayTra = ngayTraInput.value;
                const gioTra = gioTraInput.value || '17:00';

                if (!ngayNhan || !ngayTra || !serviceId) return;

                capNhatTongTien();

                const batDau = ngayNhan + ' ' + gioNhan + ':00';
                let ketThuc = ngayTra + ' ' + gioTra + ':00';

                const thoiDiemBatDau = new Date(batDau.replace(' ', 'T'));
                let thoiDiemKetThuc = new Date(ketThuc.replace(' ', 'T'));

                if (Number.isNaN(thoiDiemBatDau.getTime()) || Number.isNaN(thoiDiemKetThuc.getTime())) {
                    return;
                }

                if (thoiDiemBatDau.getTime() <= Date.now()) {
                    capNhatNhan('Thời gian nhận đã qua', true);
                    return;
                }

                if (laLuot && thoiDiemKetThuc <= thoiDiemBatDau) {
                    thoiDiemKetThuc = new Date(thoiDiemBatDau.getTime() + 60 * 60 * 1000);
                    ketThuc = dinhDangThoiGian(thoiDiemKetThuc);
                }

                if (!laLuot && thoiDiemKetThuc <= thoiDiemBatDau) {
                    alert('Thời gian trả phải lớn hơn thời gian nhận! Vui lòng chọn lại.');
                    batNut(false);
                    return;
                }

                kiemTraTonKhoTheoThoiGian(batDau, ketThuc, baoHetHang);
            }

            function kiemTraTonKhoTheoThoiGian(batDau, ketThuc, baoHetHang) {
                const yeuCauHienTai = ++maYeuCau;

                const thamSo = new URLSearchParams({
                    id: serviceId,
                    bat_dau: batDau,
                    ket_thuc: ketThuc
                });

                if (oKichCo && oKichCo.value) {
                    thamSo.set('kich_co', oKichCo.value);
                }

                fetch(apiKho + '?' + thamSo.toString())
                    .then(function (res) {
                        if (!res.ok) throw new Error('HTTP ' + res.status);
                        return res.json();
                    })
                    .then(function (data) {
                        if (yeuCauHienTai !== maYeuCau) return;

                        if (data.loi) {
                            batNut(false);
                            capNhatNhan(data.loi, true);
                            return;
                        }

                        if (data.con_lai === undefined) return;

                        toiDa = Math.max(0, parseInt(data.con_lai, 10) || 0);
                        oSoLuong.dataset.toiDa = toiDa;

                        if (toiDa === 0) {
                            if (baoHetHang && khungDaBao !== batDau + ketThuc) {
                                khungDaBao = batDau + ketThuc;
                                alert('Khung giờ này đã hết sạch số lượng sản phẩm. Vui lòng chọn thời gian khác!');
                            }

                            soLuong = 0;
                            batNut(false);
                            capNhatNhan('Hết hàng trong khung giờ này', true);
                        } else {
                            khungDaBao = '';
                            batNut(true);
                            capNhatNhan('Còn ' + toiDa + ' sản phẩm trong khung giờ này', false);

                            if (soLuong > toiDa) soLuong = toiDa;
                            if (soLuong < 1) soLuong = 1;
                        }

                        capNhatSoLuong();
                    })
                    .catch(function (loi) {
                        console.error('Lỗi kiểm tra kho:', loi);
                    });
            }

            kiemTraVaGoiAjax(false);
        }
    }

    // --------------------------------------------------
    // PHẦN 2: KIỂM TRA FORM ĐẶT THUÊ TRƯỚC KHI VÀO GIỎ HÀNG
    // --------------------------------------------------
    const bookingForms = document.querySelectorAll('form[action="cart.php"]');
    bookingForms.forEach(function (form) {
        form.addEventListener('submit', function (e) {
            const serviceIdInput = form.querySelector('input[name="service_id"]');
            const soLuongInput = form.querySelector('input[name="so_luong"]');

            const id = parseInt(serviceIdInput ? serviceIdInput.value : '0', 10) || 0;
            const qty = parseInt(soLuongInput ? soLuongInput.value : '1', 10) || 0;

            if (id <= 0) {
                e.preventDefault();
                alert('Không xác định được dịch vụ.');
                return;
            }

            if (qty <= 0) {
                e.preventDefault();
                alert('Số lượng thuê phải lớn hơn 0.');
            }
        });
    });
});
