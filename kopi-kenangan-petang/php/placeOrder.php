<?php
// Aktifkan pelaporan error supaya mudah debug
error_reporting(E_ALL);
ini_set('display_errors', 0); // jangan tampilkan di output, tangkap pakai try-catch

require_once dirname(__FILE__) . '/midtrans-php-master/Midtrans.php';

// Set Merchant Server Key (Sandbox)
\Midtrans\Config::$serverKey    = 'YOUR_SERVER_KEY'; // GANTI DENGAN SERVER KEY ANDA (Hapus kunci asli untuk push ke GitHub)
\Midtrans\Config::$isProduction = false;
\Midtrans\Config::$isSanitized  = true;
\Midtrans\Config::$is3ds        = true;

// Validasi input dasar
if (empty($_POST['total']) || empty($_POST['items']) || empty($_POST['name'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Data tidak lengkap.']);
    exit;
}

// item_details sudah di-remap oleh app.js ke {id, price, quantity, name}
$itemDetails = json_decode($_POST['items'], true);

// Hitung ulang gross_amount dari item_details agar pasti cocok
$grossAmount = 0;
foreach ($itemDetails as $item) {
    $grossAmount += (int)$item['price'] * (int)$item['quantity'];
}

$params = array(
    'transaction_details' => array(
        'order_id'     => 'ORDER-' . time() . '-' . rand(100, 999),
        'gross_amount' => $grossAmount,
    ),
    'item_details'     => $itemDetails,
    'customer_details' => array(
        'first_name' => htmlspecialchars($_POST['name']),
        'email'      => htmlspecialchars($_POST['email']),
        'phone'      => htmlspecialchars($_POST['phone']),
    ),
    // FIX 5: Redirect kembali ke halaman sendiri setelah pembayaran (bukan example.com)
    'callbacks' => array(
        'finish' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') .
                    '://' . $_SERVER['HTTP_HOST'] .
                    str_replace('/php/placeOrder.php', '/index.php', $_SERVER['REQUEST_URI'])
    ),
);

try {
    $snapToken = \Midtrans\Snap::getSnapToken($params);
    echo $snapToken;
} catch (Exception $e) {
    http_response_code(500);
    echo 'ERROR: ' . $e->getMessage();
}
?>
