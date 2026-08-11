-- =========================================================
-- SKEMA DATABASE: Polling Insan Statistik Teladan (IST)
-- Import file ini via phpMyAdmin (Import) atau:
--   mysql -u USERNAME -p NAMA_DATABASE < schema.sql
-- =========================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS pegawai (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(150) NOT NULL,
  nip VARCHAR(30) NOT NULL,
  unit_kerja VARCHAR(150) DEFAULT NULL,
  sudah_vote TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_nip (nip)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS kandidat (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kode CHAR(1) NOT NULL,
  nama VARCHAR(150) NOT NULL,
  gelar VARCHAR(50) DEFAULT NULL,
  urutan INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS kategori (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kode VARCHAR(30) NOT NULL,
  nama VARCHAR(150) NOT NULL,
  deskripsi TEXT,
  urutan INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS indikator (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kategori_id INT NOT NULL,
  nomor VARCHAR(10) NOT NULL,
  judul VARCHAR(150) NOT NULL,
  deskripsi TEXT,
  urutan INT DEFAULT 0,
  FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Jantung sistem: 1 baris = 1 skor (pegawai x kandidat x indikator)
CREATE TABLE IF NOT EXISTS penilaian (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pegawai_id INT NOT NULL,
  kandidat_id INT NOT NULL,
  indikator_id INT NOT NULL,
  skor TINYINT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_vote (pegawai_id, kandidat_id, indikator_id),
  FOREIGN KEY (pegawai_id) REFERENCES pegawai(id) ON DELETE CASCADE,
  FOREIGN KEY (kandidat_id) REFERENCES kandidat(id) ON DELETE CASCADE,
  FOREIGN KEY (indikator_id) REFERENCES indikator(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS catatan_akhir (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pegawai_id INT NOT NULL UNIQUE,
  kandidat_terpilih_id INT DEFAULT NULL,
  isi_catatan TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (pegawai_id) REFERENCES pegawai(id) ON DELETE CASCADE,
  FOREIGN KEY (kandidat_terpilih_id) REFERENCES kandidat(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS admin (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('super_admin','admin') NOT NULL DEFAULT 'super_admin',
  is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pengaturan (
  `kunci` VARCHAR(100) PRIMARY KEY,
  `nilai` TEXT DEFAULT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- SEED DATA
-- =========================================================

INSERT INTO kandidat (kode, nama, gelar, urutan) VALUES
('A', 'Aditya Chandra Yudistira', 'SE', 1),
('B', 'Jama''adi', 'S.Mn.', 2),
('C', 'Elisabet Tri Laksmi', 'SST., MM', 3);

INSERT INTO kategori (kode, nama, deskripsi, urutan) VALUES
('BRAIN', 'BRAIN (Kinerjaku & Inovasi)', 'Kualitas kerja, penguasaan tugas, dan daya cipta.', 1),
('BEAUTY', 'BEAUTY (Komunikasi, Citra Diri, & Pengaruh Positif)', 'Soft skill, keteladanan sikap, dan energi positif di tempat kerja.', 2),
('BEHAVIOR', 'BEHAVIOR (Integritas, Kolaborasi, & Pelayanan)', 'Kesesuaian perilaku dengan Core Values BerAKHLAK dan Budaya Kerja BPS.', 3),
('KETELADANAN', 'KETELADANAN UMUM (Rekomendasi Akhir)', 'Penilaian kelayakan menyeluruh sebagai wujud Insan Statistik Teladan.', 4);

INSERT INTO indikator (kategori_id, nomor, judul, deskripsi, urutan) VALUES
(1, '1.1', 'Kompetensi Teknis', 'Sejauh mana kandidat menguasai bidang tugasnya dan menjadi rujukan/tempat bertanya saat rekan kerja menemui kendala?', 1),
(1, '1.2', 'Inovasi & Inisiatif', 'Sejauh mana kandidat memberikan ide kreatif, cara kerja baru, atau solusi digital yang mempermudah pekerjaan di Satker?', 2),
(1, '1.3', 'Problem Solving', 'Sejauh mana kandidat mampu berpikir objektif, tenang, dan memberikan solusi efektif saat menghadapi tekanan/krisis pekerjaan?', 3),

(2, '2.1', 'Komunikasi Efektif', 'Sejauh mana kandidat mampu menyampaikan gagasan secara jelas, santun, persuasif, serta mau mendengarkan masukan orang lain?', 1),
(2, '2.2', 'Role Model & Profesionalisme', 'Sejauh mana kandidat menampilkan sikap profesional, percaya diri, rapi, dan menjaga citra positif institusi?', 2),
(2, '2.3', 'Daya Pengaruh Positif', 'Sejauh mana kehadiran kandidat mampu memberikan motivasi, inspirasi, dan membangun suasana kerja yang menyenangkan?', 3),

(3, '3.1', 'Integritas & Kedisiplinan', 'Sejauh mana kandidat menunjukkan keselarasan antara ucapan dan tindakan, jujur, bertanggung jawab, serta tepat waktu?', 1),
(3, '3.2', 'Kolaborasi & Kerjasama Tim', 'Sejauh mana kandidat aktif membantu rekan kerja tanpa membeda-bedakan dan mengedepankan kepentingan tim/Satker?', 2),
(3, '3.3', 'Orientation to Service', 'Sejauh mana kandidat bersikap ramah, empati, dan responsif dalam memberikan pelayanan baik internal maupun kepada mitra/pengguna data?', 3),

(4, '4.1', 'Kelayakan Keteladanan', 'Secara keseluruhan, seberapa layak kandidat ini menjadi representasi/sosok "Insan Statistik Teladan" yang membawa nama baik Satker?', 1);

-- Contoh data pegawai (voter). GANTI dengan daftar pegawai asli sebelum go-live.
-- Tambahkan baris sesuai jumlah pegawai yang berhak memilih.
INSERT INTO pegawai (nama, nip, unit_kerja) VALUES
('Contoh Nama Pegawai', '199001012015011001', 'BPS Kabupaten Madiun');
