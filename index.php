<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Kalau sudah login, arahkan ke halaman yang sesuai
if (isAdmin()) {
    header('Location: admin/dashboard.php');
    exit;
}
if (isPenonton()) {
    header('Location: jadwal.php');
    exit;
}

$pageTitle = 'Home';
$baseUrl = '';
include 'includes/header.php';

// Ambil 3 event aktif terbaru untuk ditampilkan di landing page
$events = mysqli_query($conn, "SELECT * FROM events WHERE status='aktif' ORDER BY event_date ASC LIMIT 3");
?>

<section class="hero">
  <div class="container">
    <h1>WELCOME TO <span>FIGHT CLUB</span></h1>
    <p>Pesan tiket pertandingan combat sport favorit lu langsung dari HP. Cepat, praktis, dan tiket terverifikasi otomatis lewat QR Code.</p>
    <a href="register.php" class="btn">Daftar Sekarang</a>
    <a href="login.php" class="btn btn-outline">Login</a>
  </div>
</section>

<section class="section container">
  <h2 class="section-title">Event Terdekat</h2>
  <div class="grid">
    <?php if (mysqli_num_rows($events) === 0): ?>
      <p style="color:var(--text-muted)">Belum ada event aktif saat ini.</p>
    <?php endif; ?>
    <?php while ($e = mysqli_fetch_assoc($events)): ?>
      <div class="card">
        <div class="card-poster">FIGHT NIGHT</div>
        <div class="card-body">
          <h3><?= escape($e['name']) ?></h3>
          <div class="card-meta">📍 <?= escape($e['location']) ?></div>
          <div class="card-meta">🗓️ <?= tanggalIndo($e['event_date']) ?></div>
          <a href="login.php" class="btn btn-sm" style="margin-top:10px;">Beli Tiket</a>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
