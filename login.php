<?php
require_once __DIR__ . '/includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $nip  = trim($_POST['nip'] ?? '');

    if ($nama === '' || $nip === '') {
        $error = 'Nama dan NIP wajib diisi.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM pegawai WHERE nip = ? AND LOWER(nama) = LOWER(?) LIMIT 1");
        $stmt->execute([$nip, $nama]);
        $pegawai = $stmt->fetch();

        if (!$pegawai) {
            $error = 'Nama dan NIP tidak ditemukan / tidak cocok. Periksa kembali data Anda.';
        } else {
            $_SESSION['pegawai_id'] = $pegawai['id'];
            if ($pegawai['sudah_vote']) {
                header('Location: thanks.php');
            } else {
                header('Location: vote.php?step=1');
            }
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — <?= h(APP_NAME) ?></title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="auth-wrap">
  <div class="auth-card">
    <h1><?= h(APP_NAME) ?></h1>
    <p class="subtitle">Masuk menggunakan Nama dan NIP Anda untuk mulai memberikan penilaian.</p>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= h($error) ?></div>
    <?php endif; ?>

    <form method="post" novalidate>
      <label for="nama">Nama Lengkap</label>
      <input type="text" id="nama" name="nama" required autofocus placeholder="Sesuai data kepegawaian">

      <label for="nip">NIP</label>
      <input type="text" id="nip" name="nip" required placeholder="Nomor Induk Pegawai">

      <button type="submit">Masuk</button>
    </form>
  </div>
</div>
</body>
</html>
