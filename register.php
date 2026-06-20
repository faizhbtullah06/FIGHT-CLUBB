<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    // Validasi data (Alternative Flow: data tidak lengkap/invalid)
    if ($name === '' || $email === '' || $phone === '' || $password === '') {
        $errors[] = 'Semua field wajib diisi.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password minimal 6 karakter.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Konfirmasi password tidak cocok.';
    }

    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors[] = 'Email sudah terdaftar. Silakan login.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = mysqli_prepare($conn, "INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, 'penonton')");
            mysqli_stmt_bind_param($insert, "ssss", $name, $email, $phone, $hash);

            if (mysqli_stmt_execute($insert)) {
                $_SESSION['register_success'] = true;
                header('Location: login.php');
                exit;
            } else {
                $errors[] = 'Terjadi kesalahan, silakan coba lagi.';
            }
        }
        mysqli_stmt_close($stmt);
    }
}

$pageTitle = 'Daftar Akun';
$baseUrl = '';
include 'includes/header.php';
?>

<div class="form-box">
  <h2>Daftar Akun</h2>

  <?php foreach ($errors as $err): ?>
    <div class="alert alert-error"><?= escape($err) ?></div>
  <?php endforeach; ?>

  <form method="POST" action="register.php">
    <div class="form-group">
      <label>Nama Lengkap</label>
      <input type="text" name="name" value="<?= escape($_POST['name'] ?? '') ?>" required>
    </div>
    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email" value="<?= escape($_POST['email'] ?? '') ?>" required>
    </div>
    <div class="form-group">
      <label>Nomor HP</label>
      <input type="tel" name="phone" value="<?= escape($_POST['phone'] ?? '') ?>" required>
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" required>
    </div>
    <div class="form-group">
      <label>Konfirmasi Password</label>
      <input type="password" name="confirm_password" required>
    </div>
    <button type="submit" class="btn btn-block">Daftar</button>
  </form>

  <div class="form-footer">
    Sudah punya akun? <a href="login.php">Login di sini</a>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
