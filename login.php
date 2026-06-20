<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

if (isAdmin()) { header('Location: admin/dashboard.php'); exit; }
if (isPenonton()) { header('Location: jadwal.php'); exit; }

$errors = [];
$registerSuccess = $_SESSION['register_success'] ?? false;
unset($_SESSION['register_success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = 'Email dan password wajib diisi.';
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id, name, password, role FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        // Alternative Flow: Email/password salah -> tampilkan error
        if (!$user || !password_verify($password, $user['password'])) {
            $errors[] = 'Email atau password salah.';
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header('Location: admin/dashboard.php');
            } else {
                header('Location: jadwal.php');
            }
            exit;
        }
    }
}

$pageTitle = 'Login';
$baseUrl = '';
include 'includes/header.php';
?>

<div class="form-box">
  <h2>Login</h2>

  <?php if ($registerSuccess): ?>
    <div class="alert alert-success">Registrasi berhasil! Silakan login.</div>
  <?php endif; ?>

  <?php foreach ($errors as $err): ?>
    <div class="alert alert-error"><?= escape($err) ?></div>
  <?php endforeach; ?>

  <form method="POST" action="login.php">
    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email" value="<?= escape($_POST['email'] ?? '') ?>" required>
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" required>
    </div>
    <button type="submit" class="btn btn-block">Login</button>
  </form>

  <div class="form-footer">
    Belum punya akun? <a href="register.php">Daftar di sini</a><br>
    <small style="opacity:.6">Login admin pakai akun: admin@fightclub.com / admin123</small>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
