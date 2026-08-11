<?php
// =========================================================
// JALANKAN SEKALI SAJA untuk membuat akun admin,
// lalu HAPUS file ini dari server setelah selesai.
// Akses via browser: https://domainkamu.com/setup_admin.php
// =========================================================
require_once __DIR__ . '/includes/db.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (strlen($password) < 6) {
        $message = 'Password minimal 6 karakter.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO admin (username, password_hash, role) VALUES (?, ?, 'super_admin')
            ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), role = VALUES(role)
        ");
        $stmt->execute([$username, $hash]);
        $message = "Akun admin '$username' berhasil dibuat/diperbarui. SEGERA HAPUS file setup_admin.php ini dari server!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><title>Setup Admin</title>
<link rel="stylesheet" href="assets/style.css"></head>
<body>
<div class="auth-wrap">
  <div class="auth-card">
    <h1>Setup Akun Admin</h1>
    <?php if ($message): ?><div class="alert alert-error"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <form method="post">
      <label>Username</label>
      <input type="text" name="username" required>
      <label>Password</label>
      <input type="password" name="password" required>
      <button type="submit">Buat / Update Admin</button>
    </form>
  </div>
</div>
</body>
</html>
