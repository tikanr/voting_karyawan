<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$currentAdmin = current_admin($pdo);

// Statistik partisipasi
$totalPegawai = $pdo->query("SELECT COUNT(*) c FROM pegawai")->fetch()['c'];
$sudahVote = $pdo->query("SELECT COUNT(*) c FROM pegawai WHERE sudah_vote = 1")->fetch()['c'];

// Rata-rata skor per kandidat per kategori
$rekap = $pdo->query("
    SELECT kat.id AS kategori_id, kat.nama AS kategori_nama, kat.urutan,
           kand.id AS kandidat_id, kand.kode, kand.nama AS kandidat_nama,
           ROUND(AVG(p.skor), 2) AS rata2, COUNT(p.id) AS jumlah_penilaian
    FROM penilaian p
    JOIN indikator i ON i.id = p.indikator_id
    JOIN kategori kat ON kat.id = i.kategori_id
    JOIN kandidat kand ON kand.id = p.kandidat_id
    GROUP BY kat.id, kand.id
    ORDER BY kat.urutan ASC, kand.urutan ASC
")->fetchAll();

$grouped = [];
foreach ($rekap as $r) {
    $grouped[$r['kategori_id']]['nama'] = $r['kategori_nama'];
    $grouped[$r['kategori_id']]['kandidat'][$r['kode']] = $r['rata2'];
}

// Skor total keseluruhan per kandidat (rata-rata semua indikator)
$totalSkor = $pdo->query("
    SELECT kand.kode, kand.nama, ROUND(AVG(p.skor), 2) AS rata2
    FROM penilaian p
    JOIN kandidat kand ON kand.id = p.kandidat_id
    GROUP BY kand.id
    ORDER BY rata2 DESC
")->fetchAll();
$winner = $totalSkor[0] ?? null;

// Catatan essay
$catatan = $pdo->query("
    SELECT c.isi_catatan, k.kode AS kode_pilihan, k.nama AS nama_pilihan, peg.nama AS nama_penilai
    FROM catatan_akhir c
    JOIN pegawai peg ON peg.id = c.pegawai_id
    LEFT JOIN kandidat k ON k.id = c.kandidat_terpilih_id
    ORDER BY c.created_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Admin — <?= h(APP_NAME) ?></title>
<link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="page-wrap">
  <?php include __DIR__ . '/admin_nav.php'; ?>

  <div class="stat-row">
    <div class="stat-card">
      <div class="stat-num"><?= $sudahVote ?> / <?= $totalPegawai ?></div>
      <div class="stat-label">Pegawai sudah memberikan penilaian</div>
    </div>
    <?php if ($winner): ?>
      <div class="stat-card">
        <div class="stat-num"><?= h($winner['rata2']) ?></div>
        <div class="stat-label">Peringkat 1: Kandidat <?= h($winner['kode']) ?> — <?= h($winner['nama']) ?></div>
      </div>
    <?php endif; ?>
    <?php foreach ($totalSkor as $i => $t): ?>
      <div class="stat-card">
        <div class="stat-num"><?= h($t['rata2']) ?></div>
        <div class="stat-label">Rata-rata Kandidat <?= h($t['kode']) ?> — <?= h($t['nama']) ?></div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="card">
    <h2>Rata-rata Skor per Kategori</h2>
    <table class="vote-table">
      <thead>
        <tr>
          <th class="col-indikator">Kategori</th>
          <th>Kandidat A</th>
          <th>Kandidat B</th>
          <th>Kandidat C</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($grouped as $g): ?>
          <tr>
            <td class="col-indikator"><?= h($g['nama']) ?></td>
            <td><?= h($g['kandidat']['A'] ?? '-') ?></td>
            <td><?= h($g['kandidat']['B'] ?? '-') ?></td>
            <td><?= h($g['kandidat']['C'] ?? '-') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="card">
    <h2>Catatan / Essay dari Penilai (<?= count($catatan) ?>)</h2>
    <?php if (!$catatan): ?>
      <p class="muted">Belum ada catatan.</p>
    <?php endif; ?>
    <?php foreach ($catatan as $c): ?>
      <?php if (!$c['isi_catatan'] && !$c['kode_pilihan']) continue; ?>
      <div class="catatan-item">
        <strong><?= h($c['nama_penilai']) ?></strong>
        <?php if ($c['kode_pilihan']): ?>
          <span class="badge">Pilihan: Kandidat <?= h($c['kode_pilihan']) ?> — <?= h($c['nama_pilihan']) ?></span>
        <?php endif; ?>
        <?php if ($c['isi_catatan']): ?>
          <p><?= nl2br(h($c['isi_catatan'])) ?></p>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>
</body>
</html>
