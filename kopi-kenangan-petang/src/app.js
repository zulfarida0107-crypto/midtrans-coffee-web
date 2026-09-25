document.addEventListener("alpine:init", () => {
    Alpine.data("products", () => ({
        items: (window.PRODUCT_ITEMS && window.PRODUCT_ITEMS.length > 0) ? window.PRODUCT_ITEMS : [
            { id: 1, name: "Robusta Brazil",       img: "1.jpg", price: 20000, desc: "Biji kopi pilihan dari perkebunan Brazil dengan cita rasa nutty dan cokelat yang kuat. Sangrai medium, cocok untuk espresso dan pour-over." },
            { id: 2, name: "Arabika Blend",         img: "2.jpg", price: 25000, desc: "Perpaduan sempurna biji Arabika dari berbagai dataran tinggi Indonesia. Aroma floral yang memikat dengan keasaman lembut dan aftertaste manis." },
            { id: 3, name: "Primo Passo",           img: "3.jpg", price: 30000, desc: "Kopi spesialti single-origin dengan profil rasa fruity dan bright acidity. Sangrai light untuk menonjolkan karakter unik biji pilihan." },
            { id: 4, name: "Aceh Gayo",             img: "4.jpg", price: 35000, desc: "Kopi premium dari dataran tinggi Gayo, Aceh. Rasa earthy yang kompleks, body tebal, dan aroma rempah yang khas. Sangrai medium-dark." },
            { id: 5, name: "Sumatra Mandheling",    img: "5.jpg", price: 40000, desc: "Kopi ikonik dari Sumatera dengan body penuh dan rasa dark chocolate yang dalam. Proses wet-hulled menghasilkan profil rasa yang unik dan bold." },
        ],
    }));

    // Store untuk modal detail produk
    Alpine.store("modal", {
        show: false,
        product: null,
        _savedScrollY: 0,
        open(item) {
            this.product = item;
            this.show = true;
            this._savedScrollY = window.scrollY;
            document.body.style.position = 'fixed';
            document.body.style.top = '-' + this._savedScrollY + 'px';
            document.body.style.left = '0';
            document.body.style.right = '0';
            document.body.style.overflow = 'hidden';
        },
        close() {
            this.show = false;
            this.product = null;
            const savedY = this._savedScrollY;
            document.body.style.position = '';
            document.body.style.top = '';
            document.body.style.left = '';
            document.body.style.right = '';
            document.body.style.overflow = '';
            document.documentElement.style.scrollBehavior = 'auto';
            window.scrollTo(0, savedY);
            requestAnimationFrame(() => {
                document.documentElement.style.scrollBehavior = '';
            });
        }
    });

    Alpine.store("cart", {
        items: [],
        total: 0,
        quantity: 0,
        add(newItem) {
            const cartItem = this.items.find((item) => item.id === newItem.id);
            if (!cartItem) {
                this.items.push({ ...newItem, quantity: 1, total: newItem.price });
                this.quantity++;
                this.total += newItem.price;
            } else {
                this.items = this.items.map((item) => {
                    if (item.id !== newItem.id) {
                        return item;
                    } else {
                        item.quantity++;
                        item.total = item.price * item.quantity;
                        this.quantity++;
                        this.total += item.price;
                        return item;
                    }
                });
            }
        },
        remove(id) {
            const cartItem = this.items.find((item) => item.id === id);
            if (cartItem) {
                if (cartItem.quantity > 1) {
                    this.items = this.items.map((item) => {
                        if (item.id === id) {
                            item.quantity--;
                            item.total = item.price * item.quantity;
                            this.quantity--;
                            this.total -= item.price;
                        }
                        return item;
                    });
                } else if (cartItem.quantity === 1) {
                    this.items = this.items.filter((item) => item.id !== id);
                    this.quantity--;
                    this.total -= cartItem.price;
                }
            }
        },
    });
});

// Konversi ke format Rupiah
const rupiah = (number) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(number);
};

// Fungsi Render QR Code menggunakan library ZXing
function renderZXingQRCode(containerEl, qrContent) {
    if (!containerEl) return;
    containerEl.innerHTML = "";

    try {
        if (typeof ZXing !== "undefined" && ZXing.BrowserQRCodeSvgWriter) {
            const svgWriter = new ZXing.BrowserQRCodeSvgWriter();
            svgWriter.writeToDom(containerEl, qrContent, 220, 220);
        } else {
            // Fallback gambar jika ZXing belum termuat
            containerEl.innerHTML = `<img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=${encodeURIComponent(qrContent)}" alt="QR Code Pembayaran" style="max-width:220px;" />`;
        }
    } catch (err) {
        console.warn("ZXing fallback to image:", err);
        containerEl.innerHTML = `<img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=${encodeURIComponent(qrContent)}" alt="QR Code Pembayaran" style="max-width:220px;" />`;
    }
}

// Handler Checkout & QR Payment Modal
document.addEventListener('DOMContentLoaded', function () {
    const checkoutButton = document.querySelector(".checkout-button");
    const form           = document.querySelector("#checkoutForm");
    const qrModal        = document.getElementById("qr-payment-modal");
    const closeQrBtn     = document.getElementById("close-qr-modal");
    const finishQrBtn    = document.getElementById("qr-finish-btn");

    if (!form || !checkoutButton) return;

    // Fungsi validasi formulir pelanggan
    function checkFormValidity() {
        const nameInput  = form.querySelector("input[name='name']");
        const emailInput = form.querySelector("input[name='email']");
        const phoneInput = form.querySelector("input[name='phone']");

        const isFilled = nameInput && emailInput && phoneInput &&
                         nameInput.value.trim().length > 0 &&
                         emailInput.value.trim().length > 0 &&
                         phoneInput.value.trim().length > 0;

        if (isFilled) {
            checkoutButton.disabled = false;
            checkoutButton.classList.remove("disabled");
        } else {
            checkoutButton.disabled = true;
            checkoutButton.classList.add("disabled");
        }
    }

    form.addEventListener("input", checkFormValidity);
    form.addEventListener("change", checkFormValidity);
    form.addEventListener("keyup", checkFormValidity);

    // Tutup QR Modal
    function closeQrPaymentModal() {
        if (qrModal) {
            qrModal.style.display = "none";
        }
    }

    if (closeQrBtn) closeQrBtn.addEventListener("click", closeQrPaymentModal);
    if (finishQrBtn) {
        finishQrBtn.addEventListener("click", function () {
            closeQrPaymentModal();
            window.location.hash = "#home";
        });
    }
    if (qrModal) {
        qrModal.addEventListener("click", function (e) {
            if (e.target === qrModal) {
                closeQrPaymentModal();
            }
        });
    }

    // Klik tombol Checkout
    checkoutButton.addEventListener("click", async function (e) {
        e.preventDefault();
        
        if (checkoutButton.disabled) {
            alert('Harap lengkapi semua detail pelanggan (Nama, Email, dan No HP)!');
            return;
        }

        const formData = new FormData(form);
        const data = new URLSearchParams(formData);
        const objData = Object.fromEntries(data);

        // Validasi keranjang
        if (!objData.items || objData.items === '[]' || objData.items === '') {
            alert('Keranjang belanja Anda masih kosong! Silakan pilih kopi terlebih dahulu.');
            return;
        }

        const originalBtnText = checkoutButton.textContent;
        checkoutButton.disabled = true;
        checkoutButton.textContent = 'Membuat QR...';

        try {
            const response = await fetch("php/placeOrder.php", {
                method: "POST",
                body: data,
            });
            
            const result = await response.json();
            console.log("placeOrder response:", result);

            if (!response.ok || !result.success) {
                const errMsg = result.error || "Gagal memproses pesanan. Silakan coba lagi.";
                console.error("Gagal checkout:", errMsg);
                alert("Checkout gagal: " + errMsg);
            } else {
                // Kosongkan keranjang belanja setelah checkout sukses
                const cartStore = Alpine.store("cart");
                if (cartStore) {
                    cartStore.items = [];
                    cartStore.total = 0;
                    cartStore.quantity = 0;
                }
                form.reset();

                // Langsung arahkan ke halaman Invoice pesanan
                window.location.href = "invoice.php?order_id=" + encodeURIComponent(result.order_id);
            }
        } catch (err) {
            console.error("Error during checkout:", err);
            alert("Terjadi kesalahan saat memproses pesanan. Silakan cek koneksi Anda.");
        } finally {
            checkoutButton.disabled = false;
            checkoutButton.textContent = originalBtnText;
            checkFormValidity();
        }
    });
});