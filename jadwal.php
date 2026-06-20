<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requirePenonton();

// Use Case: Melihat Jadwal Pertandingan
$events = mysqli_query($conn, "SELECT * FROM events WHERE status = 'aktif' ORDER BY event_date ASC");

$pageTitle = 'Jadwal Pertandingan';
$baseUrl = '';
include 'includes/header.php';
?>

<section class="section container">
  <h2 class="section-title">Jadwal Pertandingan</h2>

  <div class="grid">
    <?php if (mysqli_num_rows($events) === 0): ?>
      <!-- Alternative Flow: Jadwal kosong -->
      <div class="alert alert-info">Belum ada jadwal pertandingan yang tersedia saat ini.</div>
    <?php endif; ?>

    <?php while ($e = mysqli_fetch_assoc($events)): ?>
      <div class="card">
        <div class="card-poster">FIGHT NIGHT</div>
        <div class="card-body">
          <h3><?= escape($e['name']) ?></h3>
          <div class="card-meta">📍 <?= escape($e['location']) ?></div>
          <div class="card-meta">🗓️ <?= tanggalIndo($e['event_date']) ?></div>
          <?php if (!empty($e['description'])): ?>
            <div class="card-meta"><?= escape($e['description']) ?></div>
          <?php endif; ?>
          <a href="event_detail.php?id=<?= $e['id'] ?>" class="btn btn-sm" style="margin-top:10px;">Lihat Detail & Order Tiket</a>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
