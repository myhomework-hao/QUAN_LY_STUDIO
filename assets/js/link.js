document.querySelectorAll("[data-include]").forEach(async (slot) => {
    const url = slot.dataset.include;
    try {
        const res = await fetch(url);
        if (!res.ok) throw new Error("HTTP " + res.status);
        slot.outerHTML = await res.text();
    } catch (err) {
        console.error("Không tải được " + url, err);
        slot.innerHTML =
            '<p style="padding:16px;background:#fdecea;color:#b3261e;font-size:13px">' +
            "Lỗi nạp " + url + " (" + err.message + ")</p>";
    }
});