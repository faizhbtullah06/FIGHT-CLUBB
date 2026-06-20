<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requirePenonton();

$orderId = (int)($_GET['order_id'] ?? 0);
$userId = currentUserId();

$stmt = mysqli_prepare($conn, "SELECT o.*, e.name AS event_name, tc.category
                                FROM orders o
                                JOIN events e ON e.id = o.event_id
                                JOIN ticket_categories tc ON tc.id = o.category_id
                                WHERE o.id = ? AND o.user_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $orderId, $userId);
mysqli_stmt_execute($stmt);
$order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$order) {
    header('Location: jadwal.php');
    exit;
}

$pageTitle = 'Pembayaran Tiket';
$baseUrl = '';
include 'includes/header.php';

// Data yang di-encode ke QR pembayaran (simulasi payment gateway)
$qrData = "FIGHTCLUB-PAYMENT|{$order['payment_code']}|TOTAL:{$order['total_price']}";
?>

<section class="section container">
  <h2 class="section-title">Pembayaran Tiket</h2>

  <div class="form-box" style="text-align:center;">
    <h3 style="margin-top:0;"><?= escape($order['event_name']) ?></h3>
    <p class="card-meta">Kategori: <?= escape($order['category']) ?> &times; <?= $order['qty'] ?></p>
    <p style="font-size:22px;font-weight:800;color:var(--gold);"><?= rupiah($order['total_price']) ?></p>

    <?php if ($order['status'] === 'pending'): ?>
      <div class="qr-box">
        <img src="<?= escape(qrCodeUrl($qrData)) ?>" alt="QR Pembayaran" width="220" height="220">
      </div>
      <p class="card-meta" style="margin-top:14px;">Kode Pembayaran: <span class="ticket-code"><?= escape($order['payment_code']) ?></span></p>
      <p class="card-meta">Scan QR di atas menggunakan aplikasi e-wallet/m-banking lu, lalu tekan tombol di bawah setelah pembayaran selesai.</p>

      <form method="POST" action="konfirmasi_bayar.php" style="margin-top:18px;">
        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
        <button type="submit" class="btn btn-block btn-gold">Sudah Bayar</button>
      </form>
    <?php elseif ($order['status'] === 'sudah_bayar'): ?>
      <div class="alert alert-success">Pembayaran sudah dikonfirmasi. Tiket lu sudah bisa dilihat di halaman My Tiket.</div>
      <a href="my_tiket.php" class="btn btn-block">Lihat My Tiket</a>
    <?php else: ?>
      <div class="alert alert-error">Transaksi ini sudah dibatalkan.</div>
    <?php endif; ?>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
