<?php
require_once __DIR__ . '/../includes/functions.php';
require_super_admin();

$message = '';
$status = voting_is_open($pdo) ? 'open' : 'closed';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newStatus = $_POST['status'] === 'open' ? 'open' : 'closed';
    set_setting($pdo, 'voting_status', $newStatus);
    $status = $newStatus;
    $message = $status === 'open' ? 'Periode voting dibuka.' : 'Periode voting ditutup.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Periode Voting — <?= h(APP_NAME) ?></title>
<link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="page-wrap">
  <?php include __DIR__ . '/admin_nav.php'; ?>

  <?php if ($message): ?><div class="alert alert-success"><?= h($message) ?></div><?php endif; ?>

  <div class="card">
    <h2>Status Saat Ini</h2>
    <p class="muted">Periode voting saat ini: <strong><?= $status === 'open' ? 'Buka' : 'Tutup' ?></strong></p>
    <form method="post">
      <input type="hidden" name="status" value="<?= $status === 'open' ? 'closed' : 'open' ?>">
      <button type="submit" class="btn btn-primary"><?= $status === 'open' ? 'Tutup Voting' : 'Buka Voting' ?></button>
    </form>
  </div>

  <div class="card">
    <h2>Catatan</h2>
    <p class="muted">Jika voting ditutup, pegawai yang belum submit tidak bisa melanjutkan form vote.</p>
  </div>
</div>
</body>
</html>
