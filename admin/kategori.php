<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$message = '';
$errors = [];
$categoryToEdit = null;
$indikatorToEdit = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_category') {
        $id = $_POST['id'] ?? null;
        $kode = trim($_POST['kode'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $urutan = (int)($_POST['urutan'] ?? 0);

        if ($kode === '' || $nama === '') {
            $errors[] = 'Kode dan nama kategori wajib diisi.';
        }

        if (empty($errors)) {
            if ($id) {
                $stmt = $pdo->prepare("UPDATE kategori SET kode = ?, nama = ?, deskripsi = ?, urutan = ? WHERE id = ?");
                $stmt->execute([$kode, $nama, $deskripsi ?: null, $urutan, $id]);
                $message = 'Kategori berhasil diperbarui.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO kategori (kode, nama, deskripsi, urutan) VALUES (?, ?, ?, ?)");
                $stmt->execute([$kode, $nama, $deskripsi ?: null, $urutan]);
                $message = 'Kategori baru berhasil ditambahkan.';
            }
        }
    } elseif ($action === 'delete_category') {
        $id = $_POST['id'] ?? null;
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM kategori WHERE id = ?");
            $stmt->execute([$id]);
            $message = 'Kategori dan indikator terkait berhasil dihapus.';
        }
    } elseif ($action === 'save_indikator') {
        $id = $_POST['id'] ?? null;
        $kategoriId = (int)($_POST['kategori_id'] ?? 0);
        $nomor = trim($_POST['nomor'] ?? '');
        $judul = trim($_POST['judul'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $urutan = (int)($_POST['urutan'] ?? 0);

        if (!$kategoriId || $nomor === '' || $judul === '') {
            $errors[] = 'Pilih kategori, nomor, dan judul indikator harus diisi.';
        }

        if (empty($errors)) {
            if ($id) {
                $stmt = $pdo->prepare("UPDATE indikator SET kategori_id = ?, nomor = ?, judul = ?, deskripsi = ?, urutan = ? WHERE id = ?");
                $stmt->execute([$kategoriId, $nomor, $judul, $deskripsi ?: null, $urutan, $id]);
                $message = 'Indikator berhasil diperbarui.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO indikator (kategori_id, nomor, judul, deskripsi, urutan) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$kategoriId, $nomor, $judul, $deskripsi ?: null, $urutan]);
                $message = 'Indikator baru berhasil ditambahkan.';
            }
        }
    } elseif ($action === 'delete_indikator') {
        $id = $_POST['id'] ?? null;
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM indikator WHERE id = ?");
            $stmt->execute([$id]);
            $message = 'Indikator berhasil dihapus.';
        }
    }
}

if ($_GET['action'] === 'edit_category' && !empty($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM kategori WHERE id = ? LIMIT 1");
    $stmt->execute([$_GET['id']]);
    $categoryToEdit = $stmt->fetch();
}

if ($_GET['action'] === 'edit_indikator' && !empty($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM indikator WHERE id = ? LIMIT 1");
    $stmt->execute([$_GET['id']]);
    $indikatorToEdit = $stmt->fetch();
}

$categories = $pdo->query("SELECT * FROM kategori ORDER BY urutan ASC, nama ASC")->fetchAll();
$indicators = $pdo->query("SELECT i.*, k.nama AS kategori_nama FROM indikator i JOIN kategori k ON k.id = i.kategori_id ORDER BY k.urutan ASC, i.urutan ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kelola Kategori & Indikator — <?= h(APP_NAME) ?></title>
<link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="page-wrap">
  <?php include __DIR__ . '/admin_nav.php'; ?>

  <?php if ($message): ?><div class="alert alert-success"><?= h($message) ?></div><?php endif; ?>
  <?php if ($errors): ?><div class="alert alert-error"><?= h(implode(' ', $errors)) ?></div><?php endif; ?>

  <div class="card">
    <h2>Daftar Kategori</h2>
    <table class="data-table">
      <thead>
        <tr>
          <th>Kode</th>
          <th>Nama</th>
          <th>Urutan</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($categories as $kategori): ?>
          <tr>
            <td><?= h($kategori['kode']) ?></td>
            <td><?= h($kategori['nama']) ?></td>
            <td><?= h($kategori['urutan']) ?></td>
            <td class="table-actions">
              <form method="get" style="display:inline;">
                <input type="hidden" name="action" value="edit_category">
                <input type="hidden" name="id" value="<?= $kategori['id'] ?>">
                <button type="submit" class="btn btn-secondary btn-sm">Edit</button>
              </form>
              <form method="post" style="display:inline;" onsubmit="return confirm('Hapus kategori dan semua indikator terkait?');">
                <input type="hidden" name="action" value="delete_category">
                <input type="hidden" name="id" value="<?= $kategori['id'] ?>">
                <button type="submit" class="btn btn-secondary btn-sm">Hapus</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="card">
    <h2><?= $categoryToEdit ? 'Edit Kategori' : 'Tambah Kategori Baru' ?></h2>
    <form method="post">
      <input type="hidden" name="action" value="save_category">
      <?php if ($categoryToEdit): ?>
        <input type="hidden" name="id" value="<?= $categoryToEdit['id'] ?>">
      <?php endif; ?>
      <label for="kode">Kode</label>
      <input type="text" id="kode" name="kode" required value="<?= h($categoryToEdit['kode'] ?? '') ?>">
      <label for="nama">Nama Kategori</label>
      <input type="text" id="nama" name="nama" required value="<?= h($categoryToEdit['nama'] ?? '') ?>">
      <label for="deskripsi">Deskripsi</label>
      <textarea id="deskripsi" name="deskripsi" rows="3"><?= h($categoryToEdit['deskripsi'] ?? '') ?></textarea>
      <label for="urutan">Urutan Tampil</label>
      <input type="number" id="urutan" name="urutan" value="<?= h($categoryToEdit['urutan'] ?? '0') ?>">
      <button type="submit" class="btn btn-primary"><?= $categoryToEdit ? 'Simpan Perubahan' : 'Tambah Kategori' ?></button>
    </form>
  </div>

  <div class="card">
    <h2>Daftar Indikator</h2>
    <table class="data-table">
      <thead>
        <tr>
          <th>Kategori</th>
          <th>Nomor</th>
          <th>Judul</th>
          <th>Urutan</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($indicators as $indikator): ?>
          <tr>
            <td><?= h($indikator['kategori_nama']) ?></td>
            <td><?= h($indikator['nomor']) ?></td>
            <td><?= h($indikator['judul']) ?></td>
            <td><?= h($indikator['urutan']) ?></td>
            <td class="table-actions">
              <form method="get" style="display:inline;">
                <input type="hidden" name="action" value="edit_indikator">
                <input type="hidden" name="id" value="<?= $indikator['id'] ?>">
                <button type="submit" class="btn btn-secondary btn-sm">Edit</button>
              </form>
              <form method="post" style="display:inline;" onsubmit="return confirm('Hapus indikator ini?');">
                <input type="hidden" name="action" value="delete_indikator">
                <input type="hidden" name="id" value="<?= $indikator['id'] ?>">
                <button type="submit" class="btn btn-secondary btn-sm">Hapus</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="card">
    <h2><?= $indikatorToEdit ? 'Edit Indikator' : 'Tambah Indikator Baru' ?></h2>
    <form method="post">
      <input type="hidden" name="action" value="save_indikator">
      <?php if ($indikatorToEdit): ?>
        <input type="hidden" name="id" value="<?= $indikatorToEdit['id'] ?>">
      <?php endif; ?>
      <label for="kategori_id">Kategori</label>
      <select id="kategori_id" name="kategori_id" required>
        <option value="">Pilih kategori</option>
        <?php foreach ($categories as $kategori): ?>
          <option value="<?= $kategori['id'] ?>" <?= ($indikatorToEdit['kategori_id'] ?? '') == $kategori['id'] ? 'selected' : '' ?>><?= h($kategori['nama']) ?></option>
        <?php endforeach; ?>
      </select>
      <label for="nomor">Nomor Indikator</label>
      <input type="text" id="nomor" name="nomor" required value="<?= h($indikatorToEdit['nomor'] ?? '') ?>">
      <label for="judul">Judul Indikator</label>
      <input type="text" id="judul" name="judul" required value="<?= h($indikatorToEdit['judul'] ?? '') ?>">
      <label for="deskripsi">Deskripsi</label>
      <textarea id="deskripsi" name="deskripsi" rows="3"><?= h($indikatorToEdit['deskripsi'] ?? '') ?></textarea>
      <label for="urutan">Urutan Tampil</label>
      <input type="number" id="urutan" name="urutan" value="<?= h($indikatorToEdit['urutan'] ?? '0') ?>">
      <button type="submit" class="btn btn-primary"><?= $indikatorToEdit ? 'Simpan Perubahan' : 'Tambah Indikator' ?></button>
    </form>
  </div>
</div>
</body>
</html>
