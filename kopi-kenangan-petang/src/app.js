document.addEventListener("alpine:init", () => {
    Alpine.data("products", () => ({
        items: [
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
            // Simpan posisi scroll saat ini sebelum mengunci
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
            // Pulihkan body
            document.body.style.position = '';
            document.body.style.top = '';
            document.body.style.left = '';
            document.body.style.right = '';
            document.body.style.overflow = '';
            // Nonaktifkan smooth-scroll sementara agar tidak animasi dari Y=0 ke savedY
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
            // cek apakah ada barang yang sama di cart
            const cartItem = this.items.find((item) => item.id === newItem.id);

            // jika belum ada / cart masih kosong
            if (!cartItem) {
                this.items.push({ ...newItem, quantity: 1, total: newItem.price });
                this.quantity++;
                this.total += newItem.price;
            } else {
                // jika barang sudah ada, update item yang bersangkutan
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
            // ambil item yang mau diremove berdasarkan id nya
            const cartItem = this.items.find((item) => item.id === id);

            // Jika item ditemukan
            if (cartItem) {
                // jika item lebih dari 1
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
                    // jika barangnya sisa 1, hapus dari array
                    this.items = this.items.filter((item) => item.id !== id);
                    this.quantity--;
                    this.total -= cartItem.price;
                }
            }
        },
    });
});

// konversi ke Rupiah (DIPINDAHKAN KE ATAS AGAR DAPAT DIAKSES OLEH formatMessage)
const rupiah = (number) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(number);
};

// kirim data ketika tombol checkout diklik
document.addEventListener('DOMContentLoaded', function () {
    // Form Validation
    const checkoutButton = document.querySelector(".checkout-button");
    const form = document.querySelector("#checkoutForm");

    if (!form || !checkoutButton) return;

    // PERBAIKAN KRITIS 1: Logika Validasi Form
    form.addEventListener("keyup", function () {
        let allFilled = true;
        
        // Iterasi melalui input form (name, email, phone)
        // Mulai dari 0 untuk input hidden dan 3 untuk input terlihat
        for (let i = 3; i < form.elements.length - 1; i++) { // -1 karena elemen terakhir adalah tombol submit
            if (form.elements[i].value.length === 0) {
                allFilled = false;
                break; // Hentikan loop segera setelah menemukan input kosong
            }
        }

        if (allFilled) {
            checkoutButton.disabled = false;
            checkoutButton.classList.remove("disabled");
        } else {
            checkoutButton.disabled = true;
            checkoutButton.classList.add("disabled");
        }
    });

    checkoutButton.addEventListener("click", async function (e) {
        e.preventDefault();
        
        if (checkoutButton.disabled) {
            alert('Harap isi semua detail pelanggan!');
            return;
        }

        const formData = new FormData(form);
        const data = new URLSearchParams(formData);
        const objData = Object.fromEntries(data);

        // Remap item_details ke format yang diterima Midtrans: {id, price, quantity, name}
        const rawItems = JSON.parse(objData.items);
        const midtransItems = rawItems.map(item => ({
            id:       String(item.id),
            price:    parseInt(item.price),
            quantity: parseInt(item.quantity),
            name:     item.name
        }));
        data.set('items', JSON.stringify(midtransItems));

        try {
            const response = await fetch("php/placeOrder.php", {
                method: "POST",
                body: data,
            });
            
            const token = await response.text();
            const trimmedToken = token.trim();
            
            console.log("Midtrans token response:", trimmedToken);

            if (!trimmedToken || trimmedToken.startsWith('ERROR:') || trimmedToken.startsWith('{')) {
                console.error("Gagal mendapatkan token:", trimmedToken);
                alert("Checkout gagal: " + (trimmedToken || "Tidak ada respons dari server."));
            } else if (typeof window.snap === 'undefined') {
                console.error("Midtrans Snap SDK belum dimuat.");
                alert("Checkout gagal: Midtrans Snap SDK tidak tersedia. Cek koneksi internet.");
            } else {
                // Tutup shopping cart sebelum Snap buka
                document.querySelector(".shopping-cart")?.classList.remove("active");

                // Simpan posisi scroll sebelum Snap buka
                const snapScrollY = window.scrollY;

                // Fungsi untuk membebaskan scroll dan mengembalikan posisi secara instan
                function forceUnlockScroll() {
                    document.body.style.position = '';
                    document.body.style.top = '';
                    document.body.style.left = '';
                    document.body.style.right = '';
                    document.body.style.overflow = '';
                    document.body.style.overflowY = '';
                    // Midtrans Snap sering memodifikasi html element juga
                    document.documentElement.style.overflow = '';
                    document.documentElement.style.overflowY = '';
                    // Kembalikan posisi scroll secara INSTAN (tanpa smooth animation)
                    document.documentElement.style.scrollBehavior = 'auto';
                    window.scrollTo(0, snapScrollY);
                    requestAnimationFrame(() => {
                        document.documentElement.style.scrollBehavior = '';
                    });
                }

                // Snap.pay dengan callbacks lengkap
                window.snap.pay(trimmedToken, {
                    onSuccess: function (result) {
                        forceUnlockScroll();
                        console.log('Pembayaran berhasil:', result);
                        alert('Pembayaran berhasil! Terima kasih telah berbelanja.');
                        window.location.href = window.location.pathname;
                    },
                    onPending: function (result) {
                        forceUnlockScroll();
                        console.log('Pembayaran pending:', result);
                        alert('Pembayaran sedang diproses. Silakan selesaikan pembayaran Anda.');
                        window.location.href = window.location.pathname;
                    },
                    onError: function (result) {
                        forceUnlockScroll();
                        console.error('Pembayaran error:', result);
                        alert('Terjadi kesalahan saat pembayaran. Silakan coba lagi.');
                    },
                    onClose: function () {
                        // Tutup Snap → pulihkan scroll ke posisi semula secara instan
                        forceUnlockScroll();
                        console.log('Snap ditutup oleh user.');
                    }
                });
            }
        } catch (err) {
            console.error("Error during checkout:", err);
            alert("Terjadi kesalahan saat melakukan checkout. Cek console untuk detail.");
        }
    });
});

// PERBAIKAN KRITIS 2: Format pesan whatsapp
const formatMessage = (obj) => {
    // Pastikan obj.items di-parse dengan benar
    const items = JSON.parse(obj.items);
    
    // Gunakan map dengan string template yang benar dan fungsi rupiah()
    const itemDetails = items.map(
        (item) => `${item.name} (${item.quantity} x ${rupiah(item.price)}) = ${rupiah(item.total)}`
    ).join('\n '); // Pisahkan setiap item dengan baris baru

    return `*Data Customer*
Nama: ${obj.name}
Email: ${obj.email}
No HP: ${obj.phone}

*Data Pesanan*
 ${itemDetails}

*TOTAL:* ${rupiah(obj.total)}
Terima kasih.`;
};