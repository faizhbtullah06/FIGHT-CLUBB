<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requirePenonton();

$eventId = (int)($_GET['id'] ?? 0);

$stmt = mysqli_prepare($conn, "SELECT * FROM events WHERE id = ? AND status = 'aktif'");
mysqli_stmt_bind_param($stmt, "i", $eventId);
mysqli_stmt_execute($stmt);
$event = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$event) {
    header('Location: jadwal.php');
    exit;
}

$catStmt = mysqli_prepare($conn, "SELECT * FROM ticket_categories WHERE event_id = ? ORDER BY price ASC");
mysqli_stmt_bind_param($catStmt, "i", $eventId);
mysqli_stmt_execute($catStmt);
$categories = mysqli_stmt_get_result($catStmt);

$pageTitle = $event['name'];
$baseUrl = '';
include 'includes/header.php';
?>

<section class="section container">
  <h2 class="section-title"><?= escape($event['name']) ?></h2>

  <div class="card" style="margin-bottom:24px;">
    <div class="card-poster" style="height:200px;">FIGHT NIGHT</div>
    <div class="card-body">
      <div class="card-meta">📍 <?= escape($event['location']) ?></div>
      <div class="card-meta">🗓️ <?= tanggalIndo($event['event_date']) ?></div>
      <?php if (!empty($event['description'])): ?>
        <p><?= escape($event['description']) ?></p>
      <?php endif; ?>
    </div>
  </div>

  <h2 class="section-title">Order Tiket</h2>

  <?php if (!empty($_SESSION['order_errors'])): ?>
    <?php foreach ($_SESSION['order_errors'] as $err): ?>
      <div class="alert alert-error"><?= escape($err) ?></div>
    <?php endforeach; ?>
    <?php unset($_SESSION['order_errors']); ?>
  <?php endif; ?>

  <form method="POST" action="order.php">
    <input type="hidden" name="event_id" value="<?= $event['id'] ?>">

    <table>
      <tr>
        <th>Pilih</th>
        <th>Kategori</th>
        <th>Harga</th>
        <th>Sisa Kuota</th>
        <th>Jumlah Tiket</th>
      </tr>
      <?php
      $hasAvailable = false;
      while ($c = mysqli_fetch_assoc($categories)):
          $sisa = $c['quota'] - $c['sold'];
          if ($sisa > 0) $hasAvailable = true;
      ?>
        <tr>
          <td>
            <input type="radio" name="category_id" value="<?= $c['id'] ?>" <?= $sisa <= 0 ? 'disabled' : '' ?> required>
          </td>
          <td><?= escape($c['category']) ?></td>
          <td><?= rupiah($c['price']) ?></td>
          <td><?= $sisa > 0 ? $sisa : 'Habis' ?></td>
          <td>
            <input type="number" name="qty[<?= $c['id'] ?>]" min="1" max="<?= max($sisa,1) ?>" value="1" style="width:80px;" <?= $sisa <= 0 ? 'disabled' : '' ?>>
          </td>
        </tr>
      <?php endwhile; ?>
    </table>

    <?php if (!$hasAvailable): ?>
      <div class="alert alert-error" style="margin-top:16px;">Maaf, semua kategori tiket untuk event ini sudah habis.</div>
    <?php else: ?>
      <button type="submit" class="btn" style="margin-top:18px;">Lanjutkan ke Pembayaran</button>
    <?php endif; ?>
  </form>
</section>

<?php include 'includes/footer.php'; ?>
