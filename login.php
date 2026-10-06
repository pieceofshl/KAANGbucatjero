<?php
require 'config.php';
$pageTitle = 'Login — GARUTVERSE';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    if ($email && $pass) {
        $stmt = $conn->prepare("SELECT id, name, password_hash, role FROM profiles WHERE email=? AND is_active=1 LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        if ($user && password_verify($pass, $user['password_hash'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            header('Location: index.php'); exit;
        } else {
            $error = 'Email atau password salah.';
        }
    } else {
        $error = 'Email dan password wajib diisi.';
    }
}
include 'includes/header.php';
?>

<div class="form-box">
  <h1>Masuk GARUTVERSE</h1>
  <p class="sub">Kelola kontribusi, review, dan itinerary kamu.</p>

  <?php if ($error): ?>
    <div style="background:#FEE2E2;color:#991B1B;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">
      <?= e($error) ?>
    </div>
  <?php endif; ?>

  <form method="post">
    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" required>
    </div>
    <button class="btn-primary" type="submit">Masuk</button>
  </form>

  <p style="text-align:center;font-size:12px;color:var(--muted);margin-top:20px;">
    Demo: admin@garutverse.id / password123
  </p>
</div>

<?php include 'includes/footer.php'; ?>