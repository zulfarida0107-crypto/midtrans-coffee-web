<?php
// php/placeOrder.php
// Pemrosesan Pesanan & Pembuatan Data QR Code ZXing

error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once dirname(__FILE__) . '/config.php';

header('Content-Type: application/json; charset=UTF-8');

// 1. Validasi method dan input dasar
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Metode permintaan tidak diizinkan. Gunakan POST.']);
    exit;
}

$name  = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$items = $_POST['items'] ?? '';

if (empty($name) || empty($email) || empty($phone) || empty($items)) {
    http_response_code(400);
    echo json_encode(['error' => 'Data formulir tidak lengkap. Harap isi Nama, Email, dan No HP.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Format email tidak valid.']);
    exit;
}

$rawItems = json_decode($items, true);
if (!is_array($rawItems) || empty($rawItems)) {
    http_response_code(400);
    echo json_encode(['error' => 'Keranjang belanja Anda masih kosong.']);
    exit;
}

// 2. Validasi harga item dari database untuk mencegah manipulasi harga (Price Tampering)
$verifiedItemDetails = [];
$grossAmount = 0;

$stmtMenu = mysqli_prepare($conn, "SELECT id_menu, nama_menu, harga FROM menu WHERE id_menu = ?");
if (!$stmtMenu) {
    http_response_code(500);
    echo json_encode(['error' => 'Gagal membaca database menu: ' . mysqli_error($conn)]);
    exit;
}

foreach ($rawItems as $item) {
    $itemId = (int)($item['id'] ?? 0);
    $qty    = (int)($item['quantity'] ?? 0);

    if ($itemId <= 0 || $qty <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Data item keranjang tidak valid.']);
        exit;
    }

    mysqli_stmt_bind_param($stmtMenu, "i", $itemId);
    mysqli_stmt_execute($stmtMenu);
    $resMenu = mysqli_stmt_get_result($stmtMenu);
    $rowMenu = mysqli_fetch_assoc($resMenu);

    if (!$rowMenu) {
        http_response_code(400);
        echo json_encode(['error' => "Produk dengan ID {$itemId} tidak ditemukan di database."]);
        exit;
    }

    $dbPrice  = (int)$rowMenu['harga'];
    $subtotal = $dbPrice * $qty;
    $grossAmount += $subtotal;

    $verifiedItemDetails[] = [
        'id'       => (string)$rowMenu['id_menu'],
        'price'    => $dbPrice,
        'quantity' => $qty,
        'name'     => $rowMenu['nama_menu'],
        'subtotal' => $subtotal
    ];
}
mysqli_stmt_close($stmtMenu);

if ($grossAmount <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Total belanja tidak valid.']);
    exit;
}

// 3. Simpan Transaksi ke Database MySQL
mysqli_begin_transaction($conn);

$orderId        = 'ORDER-' . time() . '-' . rand(100, 999);
$tanggalPesanan = date('Y-m-d H:i:s');
$statusPesanan  = 'Pending';

$stmtPesanan = mysqli_prepare(
    $conn,
    "INSERT INTO pesanan (midtrans_order_id, tanggal_pesanan, total_harga, nama_pelanggan, email_pelanggan, no_hp_pelanggan, status_pesanan) 
     VALUES (?, ?, ?, ?, ?, ?, ?)"
);

if (!$stmtPesanan) {
    mysqli_rollback($conn);
    http_response_code(500);
    echo json_encode(['error' => 'Gagal mencatat pesanan: ' . mysqli_error($conn)]);
    exit;
}

mysqli_stmt_bind_param(
    $stmtPesanan,
    "ssdssss",
    $orderId,
    $tanggalPesanan,
    $grossAmount,
    $name,
    $email,
    $phone,
    $statusPesanan
);

if (!mysqli_stmt_execute($stmtPesanan)) {
    mysqli_rollback($conn);
    http_response_code(500);
    echo json_encode(['error' => 'Gagal menyimpan pesanan ke database: ' . mysqli_stmt_error($stmtPesanan)]);
    exit;
}

$idPesanan = mysqli_insert_id($conn);
mysqli_stmt_close($stmtPesanan);

// Simpan detail pesanan
$stmtDetail = mysqli_prepare(
    $conn,
    "INSERT INTO detail_pesanan (id_pesanan, id_menu, jumlah, subtotal) VALUES (?, ?, ?, ?)"
);

if (!$stmtDetail) {
    mysqli_rollback($conn);
    http_response_code(500);
    echo json_encode(['error' => 'Gagal menyiapkan detail pesanan: ' . mysqli_error($conn)]);
    exit;
}

foreach ($verifiedItemDetails as $vItem) {
    $itemMenuId = (int)$vItem['id'];
    $itemQty    = (int)$vItem['quantity'];
    $itemSub    = (float)$vItem['subtotal'];

    mysqli_stmt_bind_param($stmtDetail, "iiid", $idPesanan, $itemMenuId, $itemQty, $itemSub);
    if (!mysqli_stmt_execute($stmtDetail)) {
        mysqli_rollback($conn);
        http_response_code(500);
        echo json_encode(['error' => 'Gagal menyimpan detail pesanan: ' . mysqli_stmt_error($stmtDetail)]);
        exit;
    }
}
mysqli_stmt_close($stmtDetail);

// Sukses simpan ke database
mysqli_commit($conn);

// 4. Siapkan Data QR Code (Format QRIS atau Payload Order)
// Berisi data QRIS toko beserta identitas order
$qrData = defined('DEFAULT_QRIS_DATA') ? DEFAULT_QRIS_DATA : 'QRIS-PAYMENT-' . $orderId;

// 5. Susun Pesan WhatsApp Otomatis
$itemLines = [];
foreach ($verifiedItemDetails as $v) {
    $itemLines[] = "- {$v['name']} ({$v['quantity']}x @ Rp " . number_format($v['price'], 0, ',', '.') . ")";
}
$daftarPesanan = implode("\n", $itemLines);
$totalFormatted = "Rp " . number_format($grossAmount, 0, ',', '.');

$waMessage = "*KONFIRMASI PEMBAYARAN KEDAI KOPI*\n\n" .
             "*ID Pesanan:* #{$orderId}\n" .
             "*Nama Pelanggan:* {$name}\n" .
             "*No HP:* {$phone}\n" .
             "*Tanggal:* {$tanggalPesanan}\n\n" .
             "*Detail Pesanan:*\n{$daftarPesanan}\n\n" .
             "*TOTAL:* {$totalFormatted}\n\n" .
             "Halo Admin, saya sudah melakukan pembayaran melalui scan QR Code. Mohon diproses pesanannya. Terima kasih!";

$waUrl = "https://wa.me/" . TOKO_WA_NUMBER . "?text=" . urlencode($waMessage);

// 6. Kembalikan respons JSON ke Frontend
echo json_encode([
    'success'      => true,
    'order_id'     => $orderId,
    'gross_amount' => $grossAmount,
    'total_rupiah' => $totalFormatted,
    'customer_name'=> $name,
    'qr_data'      => $qrData,
    'wa_url'       => $waUrl,
    'items'        => $verifiedItemDetails
]);
?>
