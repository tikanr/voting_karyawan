<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
$pegawai = current_pegawai($pdo);
if (!$pegawai) {
    header('Location: login.php');
    exit;
}
if (!$pegawai['sudah_vote']) {
    if (voting_is_open($pdo)) {
        header('Location: vote.php?step=1');
        exit;
    }
    $showClosedMessage = true;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Terima Kasih — <?= h(APP_NAME) ?></title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="auth-wrap">
  <div class="auth-card center">
    <?php if (!empty($showClosedMessage)): ?>
      <h1>Voting Ditutup</h1>
      <p class="subtitle">Anda belum memberikan penilaian karena periode voting saat ini ditutup.</p>
      <a href="logout.php" class="btn btn-primary">Keluar</a>
    <?php else: ?>
      <div class="check-icon">&#10003;</div>
      <h1>Terima Kasih, <?= h($pegawai['nama']) ?>!</h1>
      <p class="subtitle">Penilaian Anda untuk <?= h(APP_NAME) ?> telah berhasil disimpan.</p>
      <a href="logout.php" class="btn btn-primary">Keluar</a>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
