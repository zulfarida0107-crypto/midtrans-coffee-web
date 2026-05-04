// ==========================================================
// BODY SCROLL LOCK
// ==========================================================
(function () {
  var _scrollY = 0;
  window._lockScroll = function () {
    _scrollY = window.scrollY;
    document.body.style.position = 'fixed';
    document.body.style.top = '-' + _scrollY + 'px';
    document.body.style.left = '0';
    document.body.style.right = '0';
    document.body.style.overflow = 'hidden';
  };
  window._unlockScroll = function () {
    document.body.style.position = '';
    document.body.style.top = '';
    document.body.style.left = '';
    document.body.style.right = '';
    document.body.style.overflow = '';
    window.scrollTo(0, _scrollY);
  };
})();

// ==========================================================
// SEARCH HIGHLIGHT — highlight glow pada card yang cocok
// ==========================================================

// Style inline langsung ke elemen agar tidak bisa di-override CSS apapun
var STYLE_HIGHLIGHT = [
  'outline: 3px solid #b6895b',
  'box-shadow: 0 0 0 3px #b6895b, 0 0 18px 6px rgba(182,137,91,0.65), 0 0 36px 12px rgba(182,137,91,0.3)',
  'transform: scale(1.04)',
  'transition: all 0.3s ease',
  'z-index: 2',
  'position: relative',
].join(';');

var STYLE_DIMMED = [
  'opacity: 0.35',
  'filter: grayscale(60%) brightness(0.7)',
  'transition: all 0.3s ease',
].join(';');

var STYLE_NORMAL = [
  'outline: none',
  'box-shadow: none',
  'transform: scale(1)',
  'opacity: 1',
  'filter: none',
  'transition: all 0.3s ease',
  'z-index: auto',
  'position: relative',
].join(';');

function applySearchHighlight(keyword) {
  var cards = document.querySelectorAll('.product-card, .menu-card');

  if (!keyword || keyword.length === 0) {
    // Reset semua ke normal
    cards.forEach(function (card) {
      card.setAttribute('style', STYLE_NORMAL);
    });
    return;
  }

  var foundAny = false;

  cards.forEach(function (card) {
    // Ambil teks dari nama produk saja (lebih akurat)
    var nameEl = card.querySelector('h3, .menu-card-title');
    var text = nameEl ? nameEl.innerText.toLowerCase() : card.innerText.toLowerCase();

    if (text.includes(keyword.toLowerCase())) {
      card.setAttribute('style', STYLE_HIGHLIGHT);
      foundAny = true;
    } else {
      card.setAttribute('style', STYLE_DIMMED);
    }
  });

  // Scroll ke card pertama yang cocok (bisa di #menu atau #products)
  if (foundAny) {
    var firstMatch = document.querySelector('.product-card[style*="outline"], .menu-card[style*="outline"]');
    if (firstMatch) {
      firstMatch.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  }
}

function resetAllHighlight() {
  var cards = document.querySelectorAll('.product-card, .menu-card');
  cards.forEach(function (card) {
    card.setAttribute('style', STYLE_NORMAL);
  });
}

// ==========================================================
// Tunggu Alpine selesai render product cards via MutationObserver
// lalu jalankan highlight jika ada pending keyword
// ==========================================================
var pendingKeyword = '';

function observeProductsAndSearch() {
  var productsRow = document.querySelector('.products .row');
  if (!productsRow) return;

  var observer = new MutationObserver(function () {
    if (pendingKeyword) {
      applySearchHighlight(pendingKeyword);
    }
  });

  observer.observe(productsRow, { childList: true, subtree: true });
}

// ==========================================================
// NAVBAR — elemen
// ==========================================================
var navbarNav   = document.querySelector('.navbar-nav');
var searchForm  = document.querySelector('.search-form');
var searchBox   = document.querySelector('#search-box');
var shoppingCart = document.querySelector('.shopping-cart');
var hmBtn       = document.querySelector('#hamburger-menu');
var sbBtn       = document.querySelector('#search-button');
var scBtn       = document.querySelector('#shopping-cart-button');

// ==========================================================
// HAMBURGER — tidak scroll ke Home
// ==========================================================
hmBtn.addEventListener('click', function (e) {
  e.preventDefault();
  e.stopPropagation();
  navbarNav.classList.toggle('active');
  searchForm.classList.remove('active');
  shoppingCart.classList.remove('active');
});

// ==========================================================
// SEARCH BUTTON — buka/tutup form
// ==========================================================
sbBtn.addEventListener('click', function (e) {
  e.preventDefault();
  e.stopPropagation();

  var isOpening = !searchForm.classList.contains('active');
  searchForm.classList.toggle('active');
  navbarNav.classList.remove('active');
  shoppingCart.classList.remove('active');

  if (isOpening) {
    searchBox.focus();
    // Mulai observe untuk Alpine cards
    observeProductsAndSearch();
  } else {
    // Tutup search → reset
    searchBox.value = '';
    pendingKeyword = '';
    resetAllHighlight();
  }
});

// ==========================================================
// SEARCH INPUT — real-time highlight
// ==========================================================
searchBox.addEventListener('input', function () {
  var keyword = this.value.trim();
  pendingKeyword = keyword;

  // Jalankan sekarang (untuk menu cards yang sudah di DOM)
  applySearchHighlight(keyword);

  // Jalankan lagi setelah 150ms (untuk Alpine product cards)
  setTimeout(function () {
    applySearchHighlight(pendingKeyword);
  }, 150);

  // Dan sekali lagi setelah 400ms untuk memastikan
  setTimeout(function () {
    applySearchHighlight(pendingKeyword);
  }, 400);
});

// Enter → scroll ke produk
searchBox.addEventListener('keydown', function (e) {
  if (e.key === 'Enter') {
    e.preventDefault();
    var firstMatch = document.querySelector('.product-card[style*="outline"], .menu-card[style*="outline"]');
    if (firstMatch) {
      firstMatch.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  }
  // Escape → tutup & reset
  if (e.key === 'Escape') {
    searchForm.classList.remove('active');
    searchBox.value = '';
    pendingKeyword = '';
    resetAllHighlight();
  }
});

// ==========================================================
// SHOPPING CART
// ==========================================================
scBtn.addEventListener('click', function (e) {
  e.preventDefault();
  e.stopPropagation();
  shoppingCart.classList.toggle('active');
  navbarNav.classList.remove('active');
  searchForm.classList.remove('active');
});

// ==========================================================
// Klik di luar → tutup semua panel
// ==========================================================
document.addEventListener('click', function (e) {
  if (!hmBtn.contains(e.target) && !navbarNav.contains(e.target)) {
    navbarNav.classList.remove('active');
  }

  if (!sbBtn.contains(e.target) && !searchForm.contains(e.target)) {
    if (searchForm.classList.contains('active')) {
      searchBox.value = '';
      pendingKeyword = '';
      resetAllHighlight();
    }
    searchForm.classList.remove('active');
  }

  if (!scBtn.contains(e.target) && !shoppingCart.contains(e.target)) {
    shoppingCart.classList.remove('active');
  }
});
