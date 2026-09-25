<?php
// php/notification.php
// Webhook endpoint untuk menerima notifikasi status pembayaran dari Midtrans

require_once dirname(__FILE__) . '/config.php';

header('Content-Type: application/json; charset=UTF-8');

try {
    $notif = new \Midtrans\Notification();

    $transaction = $notif->transaction_status;
    $type        = $notif->payment_type;
    $orderId     = $notif->order_id;
    $fraud       = $notif->fraud_status;

    $newStatus = 'Pending';

    if ($transaction == 'capture') {
        if ($type == 'credit_card') {
            if ($fraud == 'challenge') {
                $newStatus = 'Pending';
            } else {
                $newStatus = 'Diproses';
            }
        }
    } else if ($transaction == 'settlement') {
        $newStatus = 'Diproses';
    } else if ($transaction == 'pending') {
        $newStatus = 'Pending';
    } else if ($transaction == 'deny' || $transaction == 'expire' || $transaction == 'cancel') {
        $newStatus = 'Dibatalkan';
    }

    $stmt = mysqli_prepare($conn, "UPDATE pesanan SET status_pesanan = ? WHERE midtrans_order_id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $newStatus, $orderId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    echo json_encode([
        'status'  => 'success',
        'message' => 'Status pesanan berhasil diperbarui',
        'order_id' => $orderId,
        'order_status' => $newStatus
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
