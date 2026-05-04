<?php
// --- PUSAT DATA (DIKELOLA OLEH PHP) ---

// Data untuk link navigasi dan footer
$navLinks = [
  '#home' => 'Home',
  '#about' => 'Tentang Kami',
  '#menu' => 'Menu',
  '#products' => 'Produk',
  '#contact' => 'Kontak',
];

// Data untuk bagian menu
$menuItems = [
  ['img' => '1.jpg', 'alt' => 'Espresso', 'title' => 'Espresso', 'price' => 'IDR 15K'],
  ['img' => '2.jpg', 'alt' => 'Cappuccino', 'title' => 'Cappuccino', 'price' => 'IDR 25K'],
  ['img' => '3.jpg', 'alt' => 'Latte', 'title' => 'Latte', 'price' => 'IDR 28K'],
  ['img' => '4.jpg', 'alt' => 'Americano', 'title' => 'Americano', 'price' => 'IDR 18K'],
  ['img' => '5.jpg', 'alt' => 'Mocha', 'title' => 'Mocha', 'price' => 'IDR 30K'],
  ['img' => '6.jpg', 'alt' => 'Macchiato', 'title' => 'Macchiato', 'price' => 'IDR 20K'],
];

// $products tidak diperlukan lagi — data produk dikelola di src/app.js (Alpine.data)
// Ini mencegah duplikasi data dan memastikan 'desc' selalu ada

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>classic coffee</title>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
    rel="stylesheet" />

  <script src="https://unpkg.com/feather-icons"></script>

  <link rel="stylesheet" href="css/style.css?v=<?php echo filemtime('css/style.css'); ?>" />

  <!-- app.js HARUS defer dan sebelum Alpine agar alpine:init listener terdaftar duluan -->
  <script defer src="src/app.js?v=<?php echo filemtime('src/app.js'); ?>"></script>

  <!-- Alpine.js CDN — defer, berjalan setelah app.js -->
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

  <!-- Midtrans Snap SDK (Sandbox) -->
  <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="Mid-client-YYeWPxzVuQXn7Idc"></script>
</head>

<body>
  <nav class="navbar" x-data>
    <a href="#" class="navbar-logo">classic<span>coffee</span>.</a>

    <div class="navbar-nav">
      <?php foreach ($navLinks as $url => $label): ?>
        <a href="<?php echo $url; ?>"><?php echo $label; ?></a>
      <?php endforeach; ?>
    </div>

    <div class="navbar-extra">
      <a href="#" id="search-button"><i data-feather="search"></i></a>
      <a href="#" id="shopping-cart-button">
        <i data-feather="shopping-cart"></i>
        <span class="quantity-badge" x-show="$store.cart.quantity" x-text="$store.cart.quantity"></span>
      </a>
      <button type="button" id="hamburger-menu"
        style="background:none;border:none;cursor:pointer;padding:0;color:inherit;"><i data-feather="menu"></i></button>
    </div>

    <div class="search-form">
      <input type="search" id="search-box" placeholder="search here..." />
      <label for="search-box"><i data-feather="search"></i></label>
    </div>
    <div class="shopping-cart">
      <template x-for="(item, index) in $store.cart.items" x-key="index">
        <div class="cart-item">
          <img :src="`img/products/${item.img}`" :alt="item.name" />
          <div class="item-detail">
            <h3 x-text="item.name"></h3>
            <div class="item-price">
              <span x-text="rupiah(item.price)"></span> ×
              <button id="remove" @click="$store.cart.remove(item.id)">−</button>
              <span x-text="item.quantity"></span>
              <button id="add" @click="$store.cart.add(item)">&plus;</button> &equals;
              <span x-text="rupiah(item.total)"></span>
            </div>
          </div>
        </div>
      </template>
      <h4 x-show="!$store.cart.items.length" style="margin-top: 1rem">Cart is Empty</h4>
      <h4 x-show="$store.cart.items.length">Total : <span x-text="rupiah($store.cart.total)"></span></h4>
      <div class="form-container" x-show="$store.cart.items.length">
        <form action="" id="checkoutForm">
          <input type="hidden" name="items" x-model="JSON.stringify($store.cart.items)" />
          <input type="hidden" name="total" x-model="$store.cart.total" />
          <h5>Customer Detail</h5>
          <label for="name"><span>Name</span><input type="text" name="name" id="name" /></label>
          <label for="email"><span>Email</span><input type="email" name="email" id="email" /></label>
          <label for="phone"><span>Phone</span><input type="number" name="phone" id="phone"
              autocomplete="off" /></label>
          <button class="checkout-button disabled" type="submit" id="checkout-button" value="checkout">Checkout</button>
        </form>
      </div>
    </div>
  </nav>
  <section class="hero" id="home">
    <main class="content">
      <h1>Mari Nikmati Secangkir <span>Kopi</span></h1>
      <p>Kopi yang lezat, momen yang tak terlupakan.</p>
    </main>
  </section>
  <section id="about" class="about">
    <h2><span>Tentang </span>Kami</h2>
    <div class="row">
      <div class="about-img">
        <img src="img/tentang-kami.jpg" alt="Tentang Kami" />
      </div>
      <div class="content">
        <h3>Kenapa memilih kopi kami?</h3>
        <p>Dengan pengalaman dan pengetahuan yang luas, kami berkomitmen untuk menyajikan kopi yang lezat dan memuaskan
          bagi Anda.</p>
        <p>Kami tidak hanya menyajikan kopi, kami menyajikan pengalaman yang autentik dan memuaskan, dengan kualitas
          yang tak terkalahkan dan rasa yang tak terlupakan.</p>
      </div>
    </div>
  </section>
  <section id="menu" class="menu">
    <h2><span>Menu </span>Kami</h2>
    <p>Pilihan kopi terbaik untuk memuaskan selera Anda</p>
    <div class="row">
      <?php foreach ($menuItems as $item): ?>
        <div class="menu-card">
          <img src="img/menu/<?php echo $item['img']; ?>" alt="<?php echo $item['alt']; ?>" class="menu-card-img" />
          <h3 class="menu-card-title">- <?php echo $item['title']; ?> -</h3>
          <p class="menu-card-price"><?php echo $item['price']; ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
  <section class="products" id="products" x-data="products">
    <h2><span>Produk Unggulan</span> Kami</h2>
    <p>Kami dengan bangga mempersembahkan produk kopi unggulan kami yang dibuat dengan cinta dan dedikasi. Setiap biji
      kopi dipilih dengan teliti untuk memastikan rasa yang lezat dan memuaskan.</p>
    <div class="row">
      <template x-for="(item, index) in items" x-key="index">
        <div class="product-card">
          <div class="product-icons">
            <a href="#" @click.prevent="$store.cart.add(item)">
              <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round">
                <use href="img/feather-sprite.svg#shopping-cart" />
              </svg>
            </a>
            <a href="#" @click.prevent="$store.modal.open(item)">
              <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round">
                <use href="img/feather-sprite.svg#eye" />
              </svg>
            </a>
          </div>
          <div class="product-image">
            <img :src="`img/products/${item.img}`" :alt="item.name" />
          </div>
          <div class="product-content">
            <h3 x-text="item.name"></h3>
            <div class="product-stars">
              <svg width="24" height="24" fill="currentColor" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <use href="img/feather-sprite.svg#star" />
              </svg>
              <svg width="24" height="24" fill="currentColor" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <use href="img/feather-sprite.svg#star" />
              </svg>
              <svg width="24" height="24" fill="currentColor" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <use href="img/feather-sprite.svg#star" />
              </svg>
              <svg width="24" height="24" fill="currentColor" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <use href="img/feather-sprite.svg#star" />
              </svg>
              <svg width="24" height="24" fill="currentColor" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <use href="img/feather-sprite.svg#star" />
              </svg>
            </div>
            <div class="product-price"><span x-text="rupiah(item.price)"></span></div>
          </div>
        </div>
      </template>
    </div>
  </section>
  <section id="contact" class="contact">
    <h2><span>Kontak </span>Kami</h2>
    <p>Hubungi kami untuk informasi lebih lanjut atau pertanyaan tentang produk kami.</p>
    <div class="row">
      <iframe
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d253840.4913165482!2d106.66470409102905!3d-6.229720928612766!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f3e945e34b9d%3A0x5371bf0fdad786a2!2sJakarta%2C%20Daerah%20Khusus%20Ibukota%20Jakarta!5e0!3m2!1sid!2sid!4v1749187568029!5m2!1sid!2sid"
        allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" class="map"></iframe>
      <form action="proses_pesan_kontak.php" method="POST">
        <div class="input-group">
          <i data-feather="user"></i>
          <input type="text" name="nama" placeholder="nama" required />
        </div>
        <div class="input-group">
          <i data-feather="mail"></i>
          <input type="email" name="email" placeholder="email" required />
        </div>
        <div class="input-group">
          <i data-feather="phone"></i>
          <input type="text" name="no_hp" placeholder="no hp" required />
        </div>
        <div class="input-group">
          <i data-feather="message-circle"></i>
          <textarea name="isi_pesan" id="isi_pesan" placeholder="tulis pesan Anda..." rows="4" required></textarea>
        </div>
        <button type="submit" class="btn" id="kontak-submit-btn">kirim pesan</button>
      </form>
    </div>
  </section>

  <!-- Toast Notification untuk Kontak -->
  <div id="contact-toast"></div>

  <footer>
    <div class="social">
      <a href="https://www.instagram.com/tomorocoffee.id?igsh=MXBxaWxla3lqdm9haQ==" target="_blank"
        rel="noopener noreferrer"><i data-feather="instagram"></i></a>
      <a href="https://x.com/TomoroCoffee_ID" target="_blank" rel="noopener noreferrer"><i
          data-feather="twitter"></i></a>
      <a href="https://www.facebook.com/tomorocoffee.id/" target="_blank" rel="noopener noreferrer"><i
          data-feather="facebook"></i></a>
    </div>
    <div class="links">
      <?php foreach ($navLinks as $url => $label): ?>
        <a href="<?php echo $url; ?>"><?php echo $label; ?></a>
      <?php endforeach; ?>
    </div>
    <div class="credit">
      <p>Created by <a href="">namiraassalwa & zulfarida</a>. | &copy; <?php echo date('Y'); ?>.</p>
    </div>
  </footer>
  <div class="modal" id="item-detail-modal" x-data :style="$store.modal.show
             ? 'display:flex;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.75);z-index:999999;justify-content:center;align-items:flex-start;overflow-y:auto;'
             : 'display:none;'" x-effect="if ($store.modal.show) { $nextTick(() => feather.replace()); }"
    @click.self="$store.modal.close()">
    <div class="modal-container" @click.stop>
      <button type="button" class="close-icon" @click="$store.modal.close()"><i data-feather="x"></i></button>
      <template x-if="$store.modal.product">
        <div class="modal-content">
          <img :src="`img/products/${$store.modal.product.img}`" :alt="$store.modal.product.name" />
          <div class="product-content">
            <h3 x-text="$store.modal.product.name"></h3>
            <p x-text="$store.modal.product.desc"
              style="font-size:1.1rem;color:#555;margin:0.5rem 0 1rem;line-height:1.6;"></p>
            <div class="product-stars" style="font-size:1.8rem;color:#b6895b;padding:0.5rem 0;">
              ★★★★★
            </div>
            <div class="product-price" x-text="rupiah($store.modal.product.price)"></div>
            <a href="#" @click.prevent="$store.cart.add($store.modal.product)">
              <i data-feather="shopping-cart"></i><span>tambah ke keranjang</span>
            </a>
          </div>
        </div>
      </template>
    </div>
  </div>
  <script>
    feather.replace();
  </script>

  <script src="js/script.js?v=<?php echo filemtime('js/script.js'); ?>"></script>

  <script>
    // =========================================================
    // CONTACT FORM — AJAX Submit + Toast Notification
    // =========================================================
    (function () {
      var contactForm = document.querySelector('#contact form');
      var toast       = document.getElementById('contact-toast');
      var submitBtn   = document.getElementById('kontak-submit-btn');

      if (!contactForm || !toast) return;

      function showToast(message, type) {
        toast.textContent = message;
        toast.className   = 'show ' + type;   // 'show success' or 'show error'
        // Auto-hide setelah 4 detik
        clearTimeout(toast._timer);
        toast._timer = setTimeout(function () {
          toast.className = '';
        }, 4000);
      }

      contactForm.addEventListener('submit', function (e) {
        e.preventDefault();

        submitBtn.disabled    = true;
        submitBtn.textContent = 'mengirim...';

        var formData = new FormData(contactForm);

        fetch('proses_pesan_kontak.php', {
          method: 'POST',
          body: formData
        })
        .then(function (res) { return res.text(); })
        .then(function (response) {
          var txt = response.trim();
          if (txt.toLowerCase().includes('berhasil')) {
            showToast('✅ Pesan berhasil terkirim! Kami akan segera menghubungi Anda.', 'success');
            contactForm.reset();
          } else {
            showToast('❌ Gagal mengirim pesan: ' + txt, 'error');
          }
        })
        .catch(function () {
          showToast('❌ Terjadi kesalahan koneksi. Silakan coba lagi.', 'error');
        })
        .finally(function () {
          submitBtn.disabled    = false;
          submitBtn.textContent = 'kirim pesan';
        });
      });
    })();
  </script>
</body>

</html>