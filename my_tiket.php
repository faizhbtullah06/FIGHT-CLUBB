<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requirePenonton();

$userId = currentUserId();

$stmt = mysqli_prepare($conn, "SELECT t.*, o.payment_code, o.qty, o.total_price, e.name AS event_name,
                                       e.location, e.event_date, tc.category
                                FROM tickets t
                                JOIN orders o ON o.id = t.order_id
                                JOIN events e ON e.id = o.event_id
                                JOIN ticket_categories tc ON tc.id = o.category_id
                                WHERE o.user_id = ? AND o.status = 'sudah_bayar'
                                ORDER BY t.created_at DESC");
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$tickets = mysqli_stmt_get_result($stmt);

$pageTitle = 'My Tiket';
$baseUrl = '';
include 'includes/header.php';
?>

<section class="section container">
  <h2 class="section-title">My Tiket</h2>

  <?php if (mysqli_num_rows($tickets) === 0): ?>
    <div class="alert alert-info">Lu belum punya tiket. Yuk cek <a href="jadwal.php">jadwal pertandingan</a> dan order tiket sekarang!</div>
  <?php endif; ?>

  <div style="display:flex; flex-direction:column; gap:16px;">
    <?php while ($t = mysqli_fetch_assoc($tickets)): ?>
      <div class="ticket-card">
        <div class="qr-box">
          <img src="<?= escape(qrCodeUrl($t['ticket_code'])) ?>" alt="QR Tiket" width="120" height="120">
        </div>
        <div class="ticket-info">
          <h3><?= escape($t['event_name']) ?></h3>
          <div class="card-meta">📍 <?= escape($t['location']) ?></div>
          <div class="card-meta">🗓️ <?= tanggalIndo($t['event_date']) ?></div>
          <div class="card-meta">Kategori: <?= escape($t['category']) ?></div>
          <div class="card-meta">Kode Tiket: <span class="ticket-code"><?= escape($t['ticket_code']) ?></span></div>
          <div class="card-meta">
            Status:
            <?php if ($t['is_used']): ?>
              <span class="badge" style="background:rgba(154,154,159,.15);color:var(--text-muted);">Sudah Digunakan</span>
            <?php else: ?>
              <span class="badge badge-sudah_bayar">Aktif</span>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
