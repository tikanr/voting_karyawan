<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$message = '';
$errors = [];
$pegawaiToEdit = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_pegawai') {
        $id = $_POST['id'] ?? null;
        $nama = trim($_POST['nama'] ?? '');
        $nip = trim($_POST['nip'] ?? '');
        $unit = trim($_POST['unit_kerja'] ?? '');

        if ($nama === '' || $nip === '') {
            $errors[] = 'Nama dan NIP wajib diisi.';
        }

        if (empty($errors)) {
            try {
                if ($id) {
                    $stmt = $pdo->prepare("SELECT id FROM pegawai WHERE nip = ? AND id <> ? LIMIT 1");
                    $stmt->execute([$nip, $id]);
                    if ($stmt->fetch()) {
                        $errors[] = 'NIP sudah terdaftar untuk pegawai lain.';
                    }
                } else {
                    $stmt = $pdo->prepare("SELECT id FROM pegawai WHERE nip = ? LIMIT 1");
                    $stmt->execute([$nip]);
                    if ($stmt->fetch()) {
                        $errors[] = 'NIP sudah terdaftar.';
                    }
                }
            } catch (PDOException $e) {
                $errors[] = 'Terjadi kesalahan database saat memeriksa NIP.';
            }
        }

        if (empty($errors)) {
            if ($id) {
                $stmt = $pdo->prepare("UPDATE pegawai SET nama = ?, nip = ?, unit_kerja = ? WHERE id = ?");
                $stmt->execute([$nama, $nip, $unit ?: null, $id]);
                $message = 'Data pegawai berhasil diperbarui.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO pegawai (nama, nip, unit_kerja) VALUES (?, ?, ?)");
                $stmt->execute([$nama, $nip, $unit ?: null]);
                $message = 'Pegawai baru berhasil ditambahkan.';
            }
        }
    } elseif ($action === 'delete_pegawai') {
        $id = $_POST['id'] ?? null;
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM pegawai WHERE id = ?");
            $stmt->execute([$id]);
            $message = 'Pegawai berhasil dihapus beserta data penilaiannya.';
        }
    } elseif ($action === 'reset_vote') {
        $id = $_POST['id'] ?? null;
        if ($id) {
            $stmt = $pdo->prepare("UPDATE pegawai SET sudah_vote = 0 WHERE id = ?");
            $stmt->execute([$id]);
            $stmt = $pdo->prepare("DELETE FROM penilaian WHERE pegawai_id = ?");
            $stmt->execute([$id]);
            $stmt = $pdo->prepare("DELETE FROM catatan_akhir WHERE pegawai_id = ?");
            $stmt->execute([$id]);
            $message = 'Status voting pegawai telah direset.';
        }
    } elseif ($action === 'import_csv') {
        if (empty($_FILES['csv_file']['name'])) {
            $errors[] = 'File CSV wajib dipilih.';
        } else {
            $file = $_FILES['csv_file']['tmp_name'];
            if (!is_uploaded_file($file)) {
                $errors[] = 'Gagal mengunggah file CSV.';
            } else {
                $added = 0;
                $skipped = 0;
                $failed = 0;
                $existingNip = [];
                foreach ($pdo->query("SELECT nip FROM pegawai") as $row) {
                    $existingNip[strtolower($row['nip'])] = true;
                }

                if (($handle = fopen($file, 'r')) !== false) {
                    while (($row = fgetcsv($handle)) !== false) {
                        if (count($row) < 2) {
                            continue;
                        }
                        $nama = trim($row[0]);
                        $nip = trim($row[1]);
                        $unit = trim($row[2] ?? '');
                        if ($nama === '' || $nip === '') {
                            $failed++;
                            continue;
                        }
                        if (isset($existingNip[strtolower($nip)])) {
                            $skipped++;
                            continue;
                        }
                        try {
                            $stmt = $pdo->prepare("INSERT INTO pegawai (nama, nip, unit_kerja) VALUES (?, ?, ?)");
                            $stmt->execute([$nama, $nip, $unit ?: null]);
                            $existingNip[strtolower($nip)] = true;
                            $added++;
                        } catch (PDOException $e) {
                            $skipped++;
                        }
                    }
                    fclose($handle);
                }

                $message = "Import selesai: $added berhasil, $skipped dilewati (duplikat atau NIP sudah ada), $failed gagal karena data tidak lengkap.";
            }
        }
    }
}

$editId = $_GET['action'] === 'edit' ? ($_GET['id'] ?? null) : null;
if ($editId) {
    $stmt = $pdo->prepare("SELECT * FROM pegawai WHERE id = ? LIMIT 1");
    $stmt->execute([$editId]);
    $pegawaiToEdit = $stmt->fetch();
}

$statusFilter = $_GET['status'] ?? '';
$search = trim($_GET['q'] ?? '');
$where = [];
$params = [];
$sql = "SELECT * FROM pegawai";

if ($statusFilter === 'voted') {
    $where[] = 'sudah_vote = 1';
} elseif ($statusFilter === 'not_voted') {
    $where[] = 'sudah_vote = 0';
}

if ($search !== '') {
    $where[] = '(nama LIKE ? OR nip LIKE ? OR unit_kerja LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY sudah_vote ASC, nama ASC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pegawaiList = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kelola Akun User — <?= h(APP_NAME) ?></title>
<link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="page-wrap">
  <?php include __DIR__ . '/admin_nav.php'; ?>

  <?php if ($message): ?><div class="alert alert-success"><?= h($message) ?></div><?php endif; ?>
  <?php if ($errors): ?><div class="alert alert-error"><?= h(implode(' ', $errors)) ?></div><?php endif; ?>

  <div class="card">
    <h2>Filter & Pencarian</h2>
    <form method="get" class="form-inline">
      <label for="status">Status Vote</label>
      <select id="status" name="status">
        <option value="">Semua</option>
        <option value="voted" <?= $statusFilter === 'voted' ? 'selected' : '' ?>>Sudah vote</option>
        <option value="not_voted" <?= $statusFilter === 'not_voted' ? 'selected' : '' ?>>Belum vote</option>
      </select>
      <label for="q">Cari</label>
      <input type="text" id="q" name="q" value="<?= h($search) ?>" placeholder="Nama, NIP, unit kerja">
      <button type="submit" class="btn btn-primary">Terapkan</button>
    </form>
  </div>

  <div class="card">
    <h2>Daftar Pegawai (<?= count($pegawaiList) ?>)</h2>
    <table class="data-table">
      <thead>
        <tr>
          <th>Nama</th>
          <th>NIP</th>
          <th>Unit Kerja</th>
          <th>Status Vote</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($pegawaiList as $pegawai): ?>
          <tr>
            <td><?= h($pegawai['nama']) ?></td>
            <td><?= h($pegawai['nip']) ?></td>
            <td><?= h($pegawai['unit_kerja']) ?></td>
            <td><span class="badge-pill <?= $pegawai['sudah_vote'] ? 'success' : 'error' ?>"><?= $pegawai['sudah_vote'] ? 'Sudah' : 'Belum' ?></span></td>
            <td class="table-actions">
              <form method="get" style="display:inline;">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" value="<?= $pegawai['id'] ?>">
                <button type="submit" class="btn btn-secondary btn-sm">Edit</button>
              </form>
              <form method="post" style="display:inline;" onsubmit="return confirm('Hapus pegawai ini? Semua data penilaian akan dihapus.');">
                <input type="hidden" name="action" value="delete_pegawai">
                <input type="hidden" name="id" value="<?= $pegawai['id'] ?>">
                <button type="submit" class="btn btn-secondary btn-sm">Hapus</button>
              </form>
              <form method="post" style="display:inline;" onsubmit="return confirm('Reset status vote pegawai ini sehingga bisa memilih ulang?');">
                <input type="hidden" name="action" value="reset_vote">
                <input type="hidden" name="id" value="<?= $pegawai['id'] ?>">
                <button type="submit" class="btn btn-secondary btn-sm">Reset Vote</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="card">
    <h2><?= $pegawaiToEdit ? 'Edit Pegawai' : 'Tambah Pegawai Baru' ?></h2>
    <form method="post">
      <input type="hidden" name="action" value="save_pegawai">
      <?php if ($pegawaiToEdit): ?>
        <input type="hidden" name="id" value="<?= $pegawaiToEdit['id'] ?>">
      <?php endif; ?>
      <label for="nama">Nama</label>
      <input type="text" id="nama" name="nama" required value="<?= h($pegawaiToEdit['nama'] ?? '') ?>">
      <label for="nip">NIP</label>
      <input type="text" id="nip" name="nip" required value="<?= h($pegawaiToEdit['nip'] ?? '') ?>">
      <label for="unit_kerja">Unit Kerja</label>
      <input type="text" id="unit_kerja" name="unit_kerja" value="<?= h($pegawaiToEdit['unit_kerja'] ?? '') ?>">
      <button type="submit" class="btn btn-primary"><?= $pegawaiToEdit ? 'Simpan Perubahan' : 'Tambah Pegawai' ?></button>
    </form>
  </div>

  <div class="card">
    <h2>Import Pegawai dari CSV</h2>
    <p class="muted">Format CSV: nama,nip,unit_kerja. Baris duplikat NIP akan dilewati.</p>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="action" value="import_csv">
      <label for="csv_file">Pilih file CSV</label>
      <input type="file" id="csv_file" name="csv_file" accept="text/csv" required>
      <button type="submit" class="btn btn-primary">Import CSV</button>
    </form>
  </div>
</div>
</body>
</html>
