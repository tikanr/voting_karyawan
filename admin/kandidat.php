<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$message = '';
$errors = [];
$editCandidate = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'save_kandidat') {
        $id = $_POST['id'] ?? null;
        $kode = strtoupper(trim($_POST['kode'] ?? ''));
        $nama = trim($_POST['nama'] ?? '');
        $gelar = trim($_POST['gelar'] ?? '');
        $urutan = (int)($_POST['urutan'] ?? 0);

        if ($kode === '' || $nama === '') {
            $errors[] = 'Kode dan nama kandidat wajib diisi.';
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("SELECT id FROM kandidat WHERE kode = ? AND id <> ? LIMIT 1");
            $stmt->execute([$kode, $id ?: 0]);
            if ($stmt->fetch()) {
                $errors[] = 'Kode kandidat sudah digunakan.';
            }
        }

        if (empty($errors)) {
            if ($id) {
                $stmt = $pdo->prepare("UPDATE kandidat SET kode = ?, nama = ?, gelar = ?, urutan = ? WHERE id = ?");
                $stmt->execute([$kode, $nama, $gelar ?: null, $urutan, $id]);
                $message = 'Kandidat berhasil diperbarui.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO kandidat (kode, nama, gelar, urutan) VALUES (?, ?, ?, ?)");
                $stmt->execute([$kode, $nama, $gelar ?: null, $urutan]);
                $message = 'Kandidat baru berhasil ditambahkan.';
            }
        }
    } elseif ($action === 'delete_kandidat') {
        $id = $_POST['id'] ?? null;
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM kandidat WHERE id = ?");
            $stmt->execute([$id]);
            $message = 'Kandidat berhasil dihapus.';
        }
    }
}

if ($_GET['action'] === 'edit' && !empty($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM kandidat WHERE id = ? LIMIT 1");
    $stmt->execute([$_GET['id']]);
    $editCandidate = $stmt->fetch();
}

$candidates = $pdo->query("SELECT * FROM kandidat ORDER BY urutan ASC, kode ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kelola Kandidat — <?= h(APP_NAME) ?></title>
<link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="page-wrap">
  <?php include __DIR__ . '/admin_nav.php'; ?>

  <?php if ($message): ?><div class="alert alert-success"><?= h($message) ?></div><?php endif; ?>
  <?php if ($errors): ?><div class="alert alert-error"><?= h(implode(' ', $errors)) ?></div><?php endif; ?>

  <div class="card">
    <h2>Daftar Kandidat</h2>
    <table class="data-table">
      <thead>
        <tr>
          <th>Kode</th>
          <th>Nama</th>
          <th>Gelar</th>
          <th>Urutan</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($candidates as $candidate): ?>
          <tr>
            <td><?= h($candidate['kode']) ?></td>
            <td><?= h($candidate['nama']) ?></td>
            <td><?= h($candidate['gelar']) ?></td>
            <td><?= h($candidate['urutan']) ?></td>
            <td class="table-actions">
              <form method="get" style="display:inline;">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" value="<?= $candidate['id'] ?>">
                <button type="submit" class="btn btn-secondary btn-sm">Edit</button>
              </form>
              <form method="post" style="display:inline;" onsubmit="return confirm('Hapus kandidat ini? Data penilaian terkait akan otomatis terhapus.');">
                <input type="hidden" name="action" value="delete_kandidat">
                <input type="hidden" name="id" value="<?= $candidate['id'] ?>">
                <button type="submit" class="btn btn-secondary btn-sm">Hapus</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="card">
    <h2><?= $editCandidate ? 'Edit Kandidat' : 'Tambah Kandidat Baru' ?></h2>
    <form method="post">
      <input type="hidden" name="action" value="save_kandidat">
      <?php if ($editCandidate): ?>
        <input type="hidden" name="id" value="<?= $editCandidate['id'] ?>">
      <?php endif; ?>
      <label for="kode">Kode</label>
      <input type="text" id="kode" name="kode" required value="<?= h($editCandidate['kode'] ?? '') ?>">
      <label for="nama">Nama Kandidat</label>
      <input type="text" id="nama" name="nama" required value="<?= h($editCandidate['nama'] ?? '') ?>">
      <label for="gelar">Gelar</label>
      <input type="text" id="gelar" name="gelar" value="<?= h($editCandidate['gelar'] ?? '') ?>">
      <label for="urutan">Urutan Tampil</label>
      <input type="number" id="urutan" name="urutan" value="<?= h($editCandidate['urutan'] ?? '0') ?>">
      <button type="submit" class="btn btn-primary"><?= $editCandidate ? 'Simpan Perubahan' : 'Tambah Kandidat' ?></button>
    </form>
  </div>
</div>
</body>
</html>
