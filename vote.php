<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$pegawai = current_pegawai($pdo);
if (!$pegawai) { header('Location: login.php'); exit; }
if ($pegawai['sudah_vote']) { header('Location: thanks.php'); exit; }
$votingClosed = !voting_is_open($pdo);
if ($votingClosed) {
    $totalStep = 0;
    $finalStep = 0;
} else {
    $totalStep = total_step($pdo);           // jumlah kategori + 1 (final)
    $finalStep = $totalStep;                 // step terakhir = halaman essay
}
$step = (int)($_GET['step'] ?? 1);
if ($step < 1) $step = 1;
if ($step > $totalStep) $step = $totalStep;

$kandidatList = get_all_kandidat($pdo);
$error = '';

// ================= PROSES SIMPAN =================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($votingClosed) {
        $error = 'Periode voting telah ditutup. Anda tidak dapat menyimpan penilaian saat ini.';
    } else if ($step < $finalStep) {
        // Simpan skor untuk kategori pada step ini
        $kategori = get_kategori_by_step($pdo, $step);
        $indikatorList = get_indikator_by_kategori($pdo, $kategori['id']);

        $skorInput = $_POST['skor'] ?? [];
        $lengkap = true;

        foreach ($indikatorList as $ind) {
            foreach ($kandidatList as $k) {
                $val = $skorInput[$ind['id']][$k['id']] ?? null;
                if ($val === null || $val === '') { $lengkap = false; continue; }
                $val = (int)$val;
                if ($val < 1 || $val > 5) { $lengkap = false; continue; }

                $stmt = $pdo->prepare("
                    INSERT INTO penilaian (pegawai_id, kandidat_id, indikator_id, skor)
                    VALUES (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE skor = VALUES(skor)
                ");
                $stmt->execute([$pegawai['id'], $k['id'], $ind['id'], $val]);
            }
        }

        if (!$lengkap) {
            $error = 'Mohon lengkapi semua penilaian (skor 1–5) untuk Kandidat A, B, dan C sebelum lanjut.';
        } else {
            header('Location: vote.php?step=' . ($step + 1));
            exit;
        }

    } else {
        // Step final: essay + kandidat pilihan (opsional)
        $kandidatTerpilih = $_POST['kandidat_terpilih'] ?? null;
        $catatan = trim($_POST['catatan'] ?? '');

        $stmt = $pdo->prepare("
            INSERT INTO catatan_akhir (pegawai_id, kandidat_terpilih_id, isi_catatan)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE kandidat_terpilih_id = VALUES(kandidat_terpilih_id), isi_catatan = VALUES(isi_catatan)
        ");
        $stmt->execute([
            $pegawai['id'],
            $kandidatTerpilih ?: null,
            $catatan ?: null
        ]);

        $pdo->prepare("UPDATE pegawai SET sudah_vote = 1 WHERE id = ?")->execute([$pegawai['id']]);

        header('Location: thanks.php');
        exit;
    }
}

// ================= DATA UNTUK TAMPILAN =================
$isFinalStep = ($step === $finalStep);

if (!$isFinalStep) {
    $kategori = get_kategori_by_step($pdo, $step);
    $indikatorList = get_indikator_by_kategori($pdo, $kategori['id']);

    // ambil skor yang sudah pernah diisi (jaga-jaga user kembali ke step sebelumnya)
    $existing = [];
    $stmt = $pdo->prepare("
        SELECT indikator_id, kandidat_id, skor FROM penilaian
        WHERE pegawai_id = ? AND indikator_id IN (SELECT id FROM indikator WHERE kategori_id = ?)
    ");
    $stmt->execute([$pegawai['id'], $kategori['id']]);
    foreach ($stmt->fetchAll() as $row) {
        $existing[$row['indikator_id']][$row['kandidat_id']] = $row['skor'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $isFinalStep ? 'Catatan Akhir' : h($kategori['nama']) ?> — <?= h(APP_NAME) ?></title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="page-wrap">
  <header class="topbar">
    <div>
      <strong><?= h(APP_NAME) ?></strong><br>
      <span class="muted">Penilai: <?= h($pegawai['nama']) ?> (<?= h($pegawai['nip']) ?>)</span>
    </div>
    <a href="logout.php" class="link-muted">Keluar</a>
  </header>

  <div class="progress">
    <?php for ($i = 1; $i <= $totalStep; $i++): ?>
      <div class="progress-step <?= $i < $step ? 'done' : ($i === $step ? 'active' : '') ?>"><?= $i ?></div>
    <?php endfor; ?>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error"><?= h($error) ?></div>
  <?php endif; ?>

  <?php if ($votingClosed): ?>
    <div class="card">
      <h2>Voting Ditutup</h2>
      <p class="muted">Saat ini periode voting sedang ditutup. Silakan hubungi admin jika diperlukan.</p>
    </div>
  <?php elseif (!$isFinalStep): ?>
    <div class="card">
      <h2><?= h($kategori['nama']) ?></h2>
      <p class="muted"><em>Focus:</em> <?= h($kategori['deskripsi']) ?></p>

      <form method="post">
        <table class="vote-table">
          <thead>
            <tr>
              <th class="col-indikator">Indikator Penilaian</th>
              <?php foreach ($kandidatList as $k): ?>
                <th>Kandidat <?= h($k['kode']) ?><br><span class="muted small"><?= h($k['nama']) ?></span></th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($indikatorList as $ind): ?>
              <tr>
                <td class="col-indikator">
                  <strong><?= h($ind['nomor']) ?> <?= h($ind['judul']) ?>:</strong>
                  <div class="muted small"><?= h($ind['deskripsi']) ?></div>
                </td>
                <?php foreach ($kandidatList as $k):
                  $val = $existing[$ind['id']][$k['id']] ?? null; ?>
                  <td class="col-skor">
                    <div class="skor-group">
                      <?php for ($s = 1; $s <= 5; $s++): ?>
                        <label class="skor-radio">
                          <input type="radio"
                                name="skor[<?= $ind['id'] ?>][<?= $k['id'] ?>]"
                                value="<?= $s ?>"
                                <?= ($val == $s) ? 'checked' : '' ?> required>
                          <?= $s ?>
                        </label>
                      <?php endfor; ?>
                    </div>
                  </td>
                <?php endforeach; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <div class="nav-buttons">
          <?php if ($step > 1): ?>
            <a href="vote.php?step=<?= $step - 1 ?>" class="btn btn-secondary">&larr; Kembali</a>
          <?php else: ?>
            <span></span>
          <?php endif; ?>
          <button type="submit" class="btn btn-primary">Lanjut &rarr;</button>
        </div>
      </form>
    </div>

  <?php else: ?>
    <div class="card">
      <h2>Catatan Tambahan / Essay (Opsional)</h2>
      <p class="muted">Apresiasi atau masukan kualitatif untuk kandidat.</p>

      <form method="post">
        <label>Kandidat terbaik pilihan Anda (opsional)</label>
        <div class="pilih-kandidat">
          <?php foreach ($kandidatList as $k): ?>
            <label class="radio-inline">
              <input type="radio" name="kandidat_terpilih" value="<?= $k['id'] ?>">
              Kandidat <?= h($k['kode']) ?> — <?= h($k['nama']) ?>
            </label>
          <?php endforeach; ?>
        </div>

        <label for="catatan">Tuliskan 1 hal mendasar yang paling menginspirasi Anda dari kandidat terbaik pilihan Anda</label>
        <textarea id="catatan" name="catatan" rows="5" placeholder="Jawaban / catatan Anda..."></textarea>

        <div class="nav-buttons">
          <a href="vote.php?step=<?= $step - 1 ?>" class="btn btn-secondary">&larr; Kembali</a>
          <button type="submit" class="btn btn-primary">Kirim Penilaian Final</button>
        </div>
      </form>
    </div>
  <?php endif; ?>
</div>
</body>
</html>
