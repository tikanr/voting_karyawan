<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$rows = $pdo->query("
    SELECT peg.nama AS nama_penilai, peg.nip,
           kat.nama AS kategori, ind.nomor, ind.judul AS indikator,
           kand.kode AS kandidat, kand.nama AS nama_kandidat,
           p.skor
    FROM penilaian p
    JOIN pegawai peg ON peg.id = p.pegawai_id
    JOIN indikator ind ON ind.id = p.indikator_id
    JOIN kategori kat ON kat.id = ind.kategori_id
    JOIN kandidat kand ON kand.id = p.kandidat_id
    ORDER BY peg.nama, kat.urutan, ind.urutan, kand.urutan
")->fetchAll();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=rekap_penilaian_ist_' . date('Ymd_His') . '.csv');

$out = fopen('php://output', 'w');
fputs($out, "\xEF\xBB\xBF"); // BOM agar Excel baca UTF-8 dengan benar
fputcsv($out, ['Nama Penilai', 'NIP', 'Kategori', 'No. Indikator', 'Indikator', 'Kandidat', 'Nama Kandidat', 'Skor']);
foreach ($rows as $r) {
    fputcsv($out, [
        $r['nama_penilai'], $r['nip'], $r['kategori'], $r['nomor'],
        $r['indikator'], $r['kandidat'], $r['nama_kandidat'], $r['skor']
    ]);
}
fclose($out);
exit;
