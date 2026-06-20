<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requirePenonton();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: jadwal.php');
    exit;
}

$orderId = (int)($_POST['order_id'] ?? 0);
$userId = currentUserId();

$stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE id = ? AND user_id = ? AND status = 'pending'");
mysqli_stmt_bind_param($stmt, "ii", $orderId, $userId);
mysqli_stmt_execute($stmt);
$order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Alternative Flow: Pembayaran gagal -> transaksi dibatalkan (di sini disimulasikan selalu berhasil
// begitu penonton menekan "Sudah Bayar", sesuai requirement Elisitasi Final no.5)
if ($order) {
    mysqli_begin_transaction($conn);
    try {
        // 1. Update status order
        $update = mysqli_prepare($conn, "UPDATE orders SET status = 'sudah_bayar', paid_at = NOW() WHERE id = ?");
        mysqli_stmt_bind_param($update, "i", $orderId);
        mysqli_stmt_execute($update);

        // 2. Tambah jumlah tiket terjual pada kategori terkait
        $updateSold = mysqli_prepare($conn, "UPDATE ticket_categories SET sold = sold + ? WHERE id = ?");
        mysqli_stmt_bind_param($updateSold, "ii", $order['qty'], $order['category_id']);
        mysqli_stmt_execute($updateSold);

        // 3. Generate tiket unik + QR Code untuk setiap tiket yang dibeli
        $insertTicket = mysqli_prepare($conn, "INSERT INTO tickets (order_id, ticket_code) VALUES (?, ?)");
        for ($i = 0; $i < $order['qty']; $i++) {
            $ticketCode = generateUniqueCode('TKT');
            mysqli_stmt_bind_param($insertTicket, "is", $orderId, $ticketCode);
            mysqli_stmt_execute($insertTicket);
        }

        mysqli_commit($conn);
    } catch (Exception $e) {
        mysqli_rollback($conn);
    }
}

header('Location: payment.php?order_id=' . $orderId);
exit;
