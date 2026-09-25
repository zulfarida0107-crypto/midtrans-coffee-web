<?php
// invoice.php
// Halaman Invoice / Bukti Pemesanan Classic Coffee Web

require_once dirname(__FILE__) . '/php/config.php';

$orderId = trim($_GET['order_id'] ?? '');

$pesanan = null;
$detailItems = [];
$errorMsg = '';

if (!empty($orderId)) {
    // 1. Ambil Data Header Pesanan
    $stmtPesanan = mysqli_prepare(
        $conn, 
        "SELECT id_pesanan, midtrans_order_id, tanggal_pesanan, total_harga, nama_pelanggan, email_pelanggan, no_hp_pelanggan, status_pesanan 
         FROM pesanan 
         WHERE midtrans_order_id = ? OR id_pesanan = ?"
    );

    if ($stmtPesanan) {
        $idInt = is_numeric($orderId) ? (int)$orderId : 0;
        mysqli_stmt_bind_param($stmtPesanan, "si", $orderId, $idInt);
        mysqli_stmt_execute($stmtPesanan);
        $res = mysqli_stmt_get_result($stmtPesanan);
        $pesanan = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmtPesanan);
    }

    if ($pesanan) {
        // 2. Ambil Rincian Detail Pesanan
        $stmtDetail = mysqli_prepare(
            $conn,
            "SELECT d.jumlah, d.subtotal, m.nama_menu, m.harga, m.gambar 
             FROM detail_pesanan d
             JOIN menu m ON d.id_menu = m.id_menu
             WHERE d.id_pesanan = ?
             ORDER BY d.id_detail ASC"
        );

        if ($stmtDetail) {
            mysqli_stmt_bind_param($stmtDetail, "i", $pesanan['id_pesanan']);
            mysqli_stmt_execute($stmtDetail);
            $resDetail = mysqli_stmt_get_result($stmtDetail);
            while ($row = mysqli_fetch_assoc($resDetail)) {
                $detailItems[] = $row;
            }
            mysqli_stmt_close($stmtDetail);
        }
    } else {
        $errorMsg = "Pesanan dengan nomor \"$orderId\" tidak ditemukan.";
    }
} else {
    $errorMsg = "Nomor pesanan tidak disertakan. Silakan cek kembali link invoice Anda.";
}

// Susun link WhatsApp konfirmasi
$waUrl = '#';
if ($pesanan) {
    $tokoWa = defined('TOKO_WA_NUMBER') ? TOKO_WA_NUMBER : '6281234567890';
    $waText = "*KONFIRMASI INVOICE KEDAI KOPI*\n\n" .
              "Halo Admin, saya ingin konfirmasi pembayaran untuk pesanan:\n" .
              "- *ID Pesanan:* #{$pesanan['midtrans_order_id']}\n" .
              "- *Nama Pelanggan:* {$pesanan['nama_pelanggan']}\n" .
              "- *Total Tagihan:* Rp " . number_format($pesanan['total_harga'], 0, ',', '.') . "\n" .
              "- *Status Saat Ini:* {$pesanan['status_pesanan']}\n\n" .
              "Terima kasih!";
    $waUrl = "https://wa.me/{$tokoWa}?text=" . urlencode($waText);
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo $pesanan ? "Invoice #{$pesanan['midtrans_order_id']} - classic coffee" : "Invoice Tidak Ditemukan - classic coffee"; ?></title>

  <!-- Google Fonts: Poppins -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap"
    rel="stylesheet" />

  <!-- Feather Icons -->
  <script src="https://unpkg.com/feather-icons"></script>

  <!-- ZXing Library untuk render QR jika status pending -->
  <script src="https://unpkg.com/@zxing/library@latest"></script>

  <!-- html2canvas Library untuk download invoice dalam format gambar (PNG/JPG) -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

  <style>
    :root {
      --primary: #b6895b;
      --primary-hover: #9c734a;
      --bg: #010101;
      --card-bg: #141414;
      --card-border: #33261a;
      --text: #ffffff;
      --text-muted: #a3a3a3;
      --success: #25d366;
      --warning: #f59e0b;
      --danger: #ef4444;
      --info: #3b82f6;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      background-color: var(--bg);
      color: var(--text);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 2.5rem 1rem;
    }

    .invoice-wrapper {
      width: 100%;
      max-width: 780px;
      background-color: var(--card-bg);
      border: 1px solid var(--card-border);
      border-radius: 16px;
      padding: 2.5rem;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.7);
      position: relative;
    }

    /* Header Invoice */
    .invoice-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      border-bottom: 1px solid #2a2a2a;
      padding-bottom: 1.5rem;
      margin-bottom: 2rem;
      flex-wrap: wrap;
      gap: 1rem;
    }

    .brand-logo {
      font-size: 2rem;
      font-weight: 700;
      font-style: italic;
      color: #fff;
      text-decoration: none;
    }

    .brand-logo span {
      color: var(--primary);
    }

    .brand-subtitle {
      font-size: 0.9rem;
      color: var(--text-muted);
      margin-top: 0.3rem;
    }

    .invoice-meta {
      text-align: right;
    }

    .invoice-title {
      font-size: 1.4rem;
      font-weight: 700;
      letter-spacing: 1px;
      color: var(--primary);
      text-transform: uppercase;
    }

    .invoice-number {
      font-size: 1rem;
      color: #ddd;
      margin-top: 0.2rem;
    }

    .invoice-date {
      font-size: 0.85rem;
      color: var(--text-muted);
      margin-top: 0.2rem;
    }

    /* Status Badge */
    .status-badge {
      display: inline-block;
      padding: 0.35rem 0.9rem;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
      text-transform: uppercase;
      margin-top: 0.5rem;
    }

    .status-Pending {
      background-color: rgba(245, 158, 11, 0.15);
      color: var(--warning);
      border: 1px solid var(--warning);
    }

    .status-Diproses {
      background-color: rgba(59, 130, 246, 0.15);
      color: var(--info);
      border: 1px solid var(--info);
    }

    .status-Selesai {
      background-color: rgba(37, 211, 102, 0.15);
      color: var(--success);
      border: 1px solid var(--success);
    }

    .status-Dibatalkan {
      background-color: rgba(239, 68, 68, 0.15);
      color: var(--danger);
      border: 1px solid var(--danger);
    }

    /* Customer Info Grid */
    .customer-info-box {
      background: #1c1c1c;
      border: 1px solid #2d2d2d;
      border-radius: 10px;
      padding: 1.2rem 1.5rem;
      margin-bottom: 2rem;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
    }

    .info-group label {
      display: block;
      font-size: 0.75rem;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 0.2rem;
    }

    .info-group p {
      font-size: 0.95rem;
      font-weight: 500;
      color: #fff;
    }

    /* Table Items */
    .table-container {
      width: 100%;
      overflow-x: auto;
      margin-bottom: 2rem;
    }

    .invoice-table {
      width: 100%;
      border-collapse: collapse;
      text-align: left;
    }

    .invoice-table th {
      background-color: #202020;
      color: var(--primary);
      padding: 0.9rem 1rem;
      font-size: 0.85rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      border-bottom: 1px solid #333;
    }

    .invoice-table td {
      padding: 1rem;
      font-size: 0.95rem;
      border-bottom: 1px solid #222;
      color: #ddd;
    }

    .invoice-table tr:hover td {
      background-color: #1a1a1a;
    }

    .text-center { text-align: center; }
    .text-right  { text-align: right; }

    /* Total Section */
    .total-section {
      display: flex;
      justify-content: flex-end;
      margin-bottom: 2rem;
    }

    .total-box {
      width: 100%;
      max-width: 320px;
      background: #1c1c1c;
      border: 1px solid #333;
      border-radius: 10px;
      padding: 1.2rem;
    }

    .total-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 0.6rem;
      font-size: 0.95rem;
      color: #aaa;
    }

    .total-row.grand-total {
      margin-top: 0.8rem;
      padding-top: 0.8rem;
      border-top: 1px dashed #444;
      font-size: 1.2rem;
      font-weight: 700;
      color: #fff;
    }

    .total-row.grand-total span:last-child {
      color: var(--primary);
      font-size: 1.35rem;
    }

    /* QR Payment Section (Jika Pending) */
    .qr-payment-section {
      background: #181818;
      border: 1px dashed var(--primary);
      border-radius: 12px;
      padding: 1.8rem;
      margin-bottom: 2rem;
      text-align: center;
    }

    .qr-payment-section h3 {
      font-size: 1.2rem;
      color: var(--primary);
      margin-bottom: 0.4rem;
    }

    .qr-payment-section p {
      font-size: 0.9rem;
      color: var(--text-muted);
      margin-bottom: 1.2rem;
    }

    .qr-canvas-box {
      background: #fff;
      display: inline-block;
      padding: 1rem;
      border-radius: 10px;
      margin-bottom: 1rem;
      box-shadow: 0 4px 15px rgba(0,0,0,0.4);
    }

    #invoice-qrcode svg {
      max-width: 180px;
      max-height: 180px;
      display: block;
    }

    /* Action Buttons */
    .actions-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 1rem;
      border-top: 1px solid #2a2a2a;
      padding-top: 1.5rem;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.8rem 1.4rem;
      font-size: 0.95rem;
      font-weight: 600;
      border-radius: 8px;
      cursor: pointer;
      text-decoration: none;
      transition: all 0.25s ease;
      border: none;
    }

    .btn-back {
      background-color: transparent;
      border: 1px solid #555;
      color: #ccc;
    }

    .btn-back:hover {
      background-color: #2a2a2a;
      color: #fff;
      border-color: #888;
    }

    .btn-download {
      background-color: var(--primary);
      color: #fff;
    }

    .btn-download:hover {
      background-color: var(--primary-hover);
      transform: translateY(-2px);
    }

    /* Error Box */
    .error-card {
      text-align: center;
      padding: 3rem 1.5rem;
    }

    .error-card i {
      color: var(--danger);
      width: 64px;
      height: 64px;
      margin-bottom: 1rem;
    }

    .error-card h2 {
      font-size: 1.5rem;
      margin-bottom: 0.5rem;
    }

    .error-card p {
      color: var(--text-muted);
      margin-bottom: 1.5rem;
    }

    /* Print Styles */
    @media print {
      body {
        background-color: #fff !important;
        color: #000 !important;
        padding: 0;
      }

      .invoice-wrapper {
        border: none !important;
        box-shadow: none !important;
        background: #fff !important;
        color: #000 !important;
        max-width: 100% !important;
        padding: 0 !important;
      }

      .brand-logo, .brand-logo span {
        color: #000 !important;
      }

      .invoice-title {
        color: #333 !important;
      }

      .brand-subtitle, .invoice-date, .info-group label, .total-row {
        color: #555 !important;
      }

      .customer-info-box, .total-box {
        background: #f8f8f8 !important;
        border: 1px solid #ddd !important;
        color: #000 !important;
      }

      .info-group p, .invoice-number, .total-row.grand-total {
        color: #000 !important;
      }

      .invoice-table th {
        background: #f0f0f0 !important;
        color: #000 !important;
        border-bottom: 2px solid #ccc !important;
      }

      .invoice-table td {
        border-bottom: 1px solid #ddd !important;
        color: #000 !important;
      }

      .status-badge {
        border: 1px solid #000 !important;
        color: #000 !important;
        background: transparent !important;
      }

      .actions-bar, .qr-payment-section, .btn {
        display: none !important;
      }
    }

    @media (max-width: 600px) {
      .invoice-wrapper {
        padding: 1.5rem;
      }
      .invoice-header {
        flex-direction: column;
        align-items: flex-start;
      }
      .invoice-meta {
        text-align: left;
      }
      .actions-bar {
        flex-direction: column;
        width: 100%;
      }
      .actions-bar .btn {
        width: 100%;
        justify-content: center;
      }
    }
  </style>
</head>

<body>

  <div class="invoice-wrapper">
    <?php if (!empty($errorMsg)): ?>
      <div class="error-card">
        <i data-feather="alert-circle"></i>
        <h2>Invoice Tidak Dapat Ditampilkan</h2>
        <p><?php echo htmlspecialchars($errorMsg); ?></p>
        <a href="index.php" class="btn btn-back">
          <i data-feather="arrow-left"></i> Kembali ke Beranda
        </a>
      </div>
    <?php else: ?>
      <!-- Header -->
      <header class="invoice-header">
        <div>
          <a href="index.php" class="brand-logo">classic<span>coffee</span>.</a>
          <p class="brand-subtitle">Kopi Nikmat, Momen Tak Terlupakan</p>
          <p class="brand-subtitle" style="font-size:0.8rem;">Jakarta, Indonesia | info@classiccoffee.id</p>
        </div>
        <div class="invoice-meta">
          <div class="invoice-title">INVOICE PEMESANAN</div>
          <div class="invoice-number">#<?php echo htmlspecialchars($pesanan['midtrans_order_id']); ?></div>
          <div class="invoice-date">
            <i data-feather="calendar" style="width:14px;height:14px;vertical-align:middle;"></i>
            <?php echo date('d M Y, H:i', strtotime($pesanan['tanggal_pesanan'])); ?> WIB
          </div>
          <div>
            <span class="status-badge status-<?php echo htmlspecialchars($pesanan['status_pesanan']); ?>">
              ● <?php echo htmlspecialchars($pesanan['status_pesanan']); ?>
            </span>
          </div>
        </div>
      </header>

      <!-- Customer Detail -->
      <section class="customer-info-box">
        <div class="info-group">
          <label>Nama Pelanggan</label>
          <p><?php echo htmlspecialchars($pesanan['nama_pelanggan']); ?></p>
        </div>
        <div class="info-group">
          <label>Email</label>
          <p><?php echo htmlspecialchars($pesanan['email_pelanggan']); ?></p>
        </div>
        <div class="info-group">
          <label>Nomor Telepon</label>
          <p><?php echo htmlspecialchars($pesanan['no_hp_pelanggan']); ?></p>
        </div>
        <div class="info-group">
          <label>Metode Pembayaran</label>
          <p>QR Code / QRIS</p>
        </div>
      </section>

      <!-- Table Items -->
      <div class="table-container">
        <table class="invoice-table">
          <thead>
            <tr>
              <th class="text-center" style="width: 50px;">No</th>
              <th>Menu Kopi</th>
              <th class="text-right">Harga Satuan</th>
              <th class="text-center" style="width: 80px;">Jumlah</th>
              <th class="text-right">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            $no = 1;
            foreach ($detailItems as $item): 
            ?>
              <tr>
                <td class="text-center"><?php echo $no++; ?></td>
                <td><strong><?php echo htmlspecialchars($item['nama_menu']); ?></strong></td>
                <td class="text-right">Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?></td>
                <td class="text-center"><?php echo (int)$item['jumlah']; ?></td>
                <td class="text-right">Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Total Calculation -->
      <div class="total-section">
        <div class="total-box">
          <div class="total-row">
            <span>Subtotal:</span>
            <span>Rp <?php echo number_format($pesanan['total_harga'], 0, ',', '.'); ?></span>
          </div>
          <div class="total-row">
            <span>Pajak & Biaya Layanan:</span>
            <span>Rp 0</span>
          </div>
          <div class="total-row grand-total">
            <span>TOTAL BAYAR:</span>
            <span>Rp <?php echo number_format($pesanan['total_harga'], 0, ',', '.'); ?></span>
          </div>
        </div>
      </div>

      <!-- Section QR Code jika masih Pending -->
      <?php if ($pesanan['status_pesanan'] === 'Pending'): ?>
        <section class="qr-payment-section">
          <h3>Scan QR Code Pembayaran</h3>
          <p>Gunakan aplikasi m-Banking atau e-Wallet favorit Anda untuk menyelesaikan pembayaran.</p>
          <div class="qr-canvas-box">
            <div id="invoice-qrcode"></div>
          </div>
          <p style="font-size:0.8rem;color:#888;">QRIS • BCA • Mandiri • BRI • GoPay • OVO • DANA</p>
        </section>
      <?php endif; ?>

      <!-- Actions Bar -->
      <footer class="actions-bar">
        <a href="index.php" class="btn btn-back">
          <i data-feather="arrow-left"></i> <span>Kembali ke Menu</span>
        </a>

        <div style="display:flex;gap:0.8rem;flex-wrap:wrap;">
          <button type="button" id="btn-download-png" class="btn btn-download">
            <i data-feather="download"></i> <span>Download Gambar (PNG)</span>
          </button>
        </div>
      </footer>
    <?php endif; ?>
  </div>

  <script>
    feather.replace();

    // Render QR Code pada invoice jika ada wadah #invoice-qrcode
    <?php if ($pesanan && $pesanan['status_pesanan'] === 'Pending'): ?>
      (function() {
        const qrBox = document.getElementById("invoice-qrcode");
        const qrString = "<?php echo defined('DEFAULT_QRIS_DATA') ? DEFAULT_QRIS_DATA : 'QRIS-' . $pesanan['midtrans_order_id']; ?>";

        if (qrBox && typeof ZXing !== "undefined" && ZXing.BrowserQRCodeSvgWriter) {
          try {
            const svgWriter = new ZXing.BrowserQRCodeSvgWriter();
            svgWriter.writeToDom(qrBox, qrString, 180, 180);
          } catch(e) {
            qrBox.innerHTML = '<img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' + encodeURIComponent(qrString) + '" alt="QRIS" style="max-width:180px;" />';
          }
        } else if (qrBox) {
          qrBox.innerHTML = '<img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' + encodeURIComponent(qrString) + '" alt="QRIS" style="max-width:180px;" />';
        }
      })();
    <?php endif; ?>

    // Download Invoice sebagai Gambar (PNG)
    document.getElementById("btn-download-png")?.addEventListener("click", function() {
      const invoiceElement = document.querySelector(".invoice-wrapper");
      const btn = this;
      const originalHtml = btn.innerHTML;

      btn.disabled = true;
      btn.innerHTML = '<i data-feather="loader"></i> <span>Menyimpan...</span>';
      if (window.feather) feather.replace();

      // Sembunyikan actions-bar saat capture agar tombol tidak ikut terfoto
      const actionsBar = document.querySelector(".actions-bar");
      if (actionsBar) actionsBar.style.display = "none";

      html2canvas(invoiceElement, {
        scale: 2, // Resolusi tinggi tajam (HD)
        backgroundColor: "#141414", // Pertahankan latar gelap
        useCORS: true,
        logging: false
      }).then(function(canvas) {
        if (actionsBar) actionsBar.style.display = "flex";
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        if (window.feather) feather.replace();

        // Buat tautan download otomatis
        const link = document.createElement("a");
        link.download = "Invoice-<?php echo $pesanan ? htmlspecialchars($pesanan['midtrans_order_id']) : 'pesanan'; ?>.png";
        link.href = canvas.toDataURL("image/png");
        link.click();
      }).catch(function(err) {
        if (actionsBar) actionsBar.style.display = "flex";
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        if (window.feather) feather.replace();
        alert("Gagal mengunduh gambar invoice: " + err.message);
      });
    });
  </script>
</body>

</html>
