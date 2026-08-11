-- MySQL dump 10.13  Distrib 8.0.19, for Win64 (x86_64)
--
-- Host: localhost    Database: voting_bps
-- ------------------------------------------------------
-- Server version	8.4.7

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('super_admin','admin') NOT NULL DEFAULT 'super_admin',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin`
--

LOCK TABLES `admin` WRITE;
/*!40000 ALTER TABLE `admin` DISABLE KEYS */;
INSERT INTO `admin` VALUES (1,'admin','$2y$10$UethByvdq02r6PepgAKP4.o/K9yAsQeHgKoLjF2azBdv/.7NsdWha','super_admin',1);
/*!40000 ALTER TABLE `admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catatan_akhir`
--

DROP TABLE IF EXISTS `catatan_akhir`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `catatan_akhir` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pegawai_id` int NOT NULL,
  `kandidat_terpilih_id` int DEFAULT NULL,
  `isi_catatan` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pegawai_id` (`pegawai_id`),
  KEY `kandidat_terpilih_id` (`kandidat_terpilih_id`),
  CONSTRAINT `catatan_akhir_ibfk_1` FOREIGN KEY (`pegawai_id`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE,
  CONSTRAINT `catatan_akhir_ibfk_2` FOREIGN KEY (`kandidat_terpilih_id`) REFERENCES `kandidat` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catatan_akhir`
--

LOCK TABLES `catatan_akhir` WRITE;
/*!40000 ALTER TABLE `catatan_akhir` DISABLE KEYS */;
INSERT INTO `catatan_akhir` VALUES (1,1,1,'jkl;llhvcv','2026-08-11 04:55:47');
/*!40000 ALTER TABLE `catatan_akhir` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `indikator`
--

DROP TABLE IF EXISTS `indikator`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `indikator` (
  `id` int NOT NULL AUTO_INCREMENT,
  `kategori_id` int NOT NULL,
  `nomor` varchar(10) NOT NULL,
  `judul` varchar(150) NOT NULL,
  `deskripsi` text,
  `urutan` int DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `kategori_id` (`kategori_id`),
  CONSTRAINT `indikator_ibfk_1` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `indikator`
--

LOCK TABLES `indikator` WRITE;
/*!40000 ALTER TABLE `indikator` DISABLE KEYS */;
INSERT INTO `indikator` VALUES (1,1,'1.1','Kompetensi Teknis','Sejauh mana kandidat menguasai bidang tugasnya dan menjadi rujukan/tempat bertanya saat rekan kerja menemui kendala?',1),(2,1,'1.2','Inovasi & Inisiatif','Sejauh mana kandidat memberikan ide kreatif, cara kerja baru, atau solusi digital yang mempermudah pekerjaan di Satker?',2),(3,1,'1.3','Problem Solving','Sejauh mana kandidat mampu berpikir objektif, tenang, dan memberikan solusi efektif saat menghadapi tekanan/krisis pekerjaan?',3),(4,2,'2.1','Komunikasi Efektif','Sejauh mana kandidat mampu menyampaikan gagasan secara jelas, santun, persuasif, serta mau mendengarkan masukan orang lain?',1),(5,2,'2.2','Role Model & Profesionalisme','Sejauh mana kandidat menampilkan sikap profesional, percaya diri, rapi, dan menjaga citra positif institusi?',2),(6,2,'2.3','Daya Pengaruh Positif','Sejauh mana kehadiran kandidat mampu memberikan motivasi, inspirasi, dan membangun suasana kerja yang menyenangkan?',3),(7,3,'3.1','Integritas & Kedisiplinan','Sejauh mana kandidat menunjukkan keselarasan antara ucapan dan tindakan, jujur, bertanggung jawab, serta tepat waktu?',1),(8,3,'3.2','Kolaborasi & Kerjasama Tim','Sejauh mana kandidat aktif membantu rekan kerja tanpa membeda-bedakan dan mengedepankan kepentingan tim/Satker?',2),(9,3,'3.3','Orientation to Service','Sejauh mana kandidat bersikap ramah, empati, dan responsif dalam memberikan pelayanan baik internal maupun kepada mitra/pengguna data?',3),(10,4,'4.1','Kelayakan Keteladanan','Secara keseluruhan, seberapa layak kandidat ini menjadi representasi/sosok \"Insan Statistik Teladan\" yang membawa nama baik Satker?',1);
/*!40000 ALTER TABLE `indikator` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kandidat`
--

DROP TABLE IF EXISTS `kandidat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kandidat` (
  `id` int NOT NULL AUTO_INCREMENT,
  `kode` char(1) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `gelar` varchar(50) DEFAULT NULL,
  `urutan` int DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kandidat`
--

LOCK TABLES `kandidat` WRITE;
/*!40000 ALTER TABLE `kandidat` DISABLE KEYS */;
INSERT INTO `kandidat` VALUES (1,'A','Aditya Chandra Yudistira','SE',1),(2,'B','Jama\'adi','S.Mn.',2),(3,'C','Elisabet Tri Laksmi','SST., MM',3);
/*!40000 ALTER TABLE `kandidat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kategori`
--

DROP TABLE IF EXISTS `kategori`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kategori` (
  `id` int NOT NULL AUTO_INCREMENT,
  `kode` varchar(30) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `deskripsi` text,
  `urutan` int DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kategori`
--

LOCK TABLES `kategori` WRITE;
/*!40000 ALTER TABLE `kategori` DISABLE KEYS */;
INSERT INTO `kategori` VALUES (1,'BRAIN','BRAIN (Kinerjaku & Inovasi)','Kualitas kerja, penguasaan tugas, dan daya cipta.',1),(2,'BEAUTY','BEAUTY (Komunikasi, Citra Diri, & Pengaruh Positif)','Soft skill, keteladanan sikap, dan energi positif di tempat kerja.',2),(3,'BEHAVIOR','BEHAVIOR (Integritas, Kolaborasi, & Pelayanan)','Kesesuaian perilaku dengan Core Values BerAKHLAK dan Budaya Kerja BPS.',3),(4,'KETELADANAN','KETELADANAN UMUM (Rekomendasi Akhir)','Penilaian kelayakan menyeluruh sebagai wujud Insan Statistik Teladan.',4);
/*!40000 ALTER TABLE `kategori` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pegawai`
--

DROP TABLE IF EXISTS `pegawai`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pegawai` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(150) NOT NULL,
  `nip` varchar(30) NOT NULL,
  `unit_kerja` varchar(150) DEFAULT NULL,
  `sudah_vote` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_nip` (`nip`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pegawai`
--

LOCK TABLES `pegawai` WRITE;
/*!40000 ALTER TABLE `pegawai` DISABLE KEYS */;
INSERT INTO `pegawai` VALUES (1,'Contoh Nama Pegawai','199001012015011001','BPS Kabupaten Madiun',1,'2026-08-11 04:05:32'),(2,'permata','1234567890','Prakom',0,'2026-08-11 05:02:37');
/*!40000 ALTER TABLE `pegawai` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pengaturan`
--

DROP TABLE IF EXISTS `pengaturan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengaturan` (
  `kunci` varchar(100) NOT NULL,
  `nilai` text,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`kunci`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengaturan`
--

LOCK TABLES `pengaturan` WRITE;
/*!40000 ALTER TABLE `pengaturan` DISABLE KEYS */;
INSERT INTO `pengaturan` VALUES ('voting_status','open','2026-08-11 04:51:54');
/*!40000 ALTER TABLE `pengaturan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `penilaian`
--

DROP TABLE IF EXISTS `penilaian`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `penilaian` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pegawai_id` int NOT NULL,
  `kandidat_id` int NOT NULL,
  `indikator_id` int NOT NULL,
  `skor` tinyint NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_vote` (`pegawai_id`,`kandidat_id`,`indikator_id`),
  KEY `kandidat_id` (`kandidat_id`),
  KEY `indikator_id` (`indikator_id`),
  CONSTRAINT `penilaian_ibfk_1` FOREIGN KEY (`pegawai_id`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE,
  CONSTRAINT `penilaian_ibfk_2` FOREIGN KEY (`kandidat_id`) REFERENCES `kandidat` (`id`) ON DELETE CASCADE,
  CONSTRAINT `penilaian_ibfk_3` FOREIGN KEY (`indikator_id`) REFERENCES `indikator` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `penilaian`
--

LOCK TABLES `penilaian` WRITE;
/*!40000 ALTER TABLE `penilaian` DISABLE KEYS */;
INSERT INTO `penilaian` VALUES (1,1,1,1,1,'2026-08-11 04:54:46'),(2,1,2,1,1,'2026-08-11 04:54:46'),(3,1,3,1,1,'2026-08-11 04:54:46'),(4,1,1,2,5,'2026-08-11 04:54:46'),(5,1,2,2,2,'2026-08-11 04:54:46'),(6,1,3,2,2,'2026-08-11 04:54:46'),(7,1,1,3,5,'2026-08-11 04:54:46'),(8,1,2,3,2,'2026-08-11 04:54:46'),(9,1,3,3,2,'2026-08-11 04:54:46'),(10,1,1,4,1,'2026-08-11 04:55:06'),(11,1,2,4,3,'2026-08-11 04:55:06'),(12,1,3,4,3,'2026-08-11 04:55:06'),(13,1,1,5,5,'2026-08-11 04:55:06'),(14,1,2,5,3,'2026-08-11 04:55:06'),(15,1,3,5,3,'2026-08-11 04:55:06'),(16,1,1,6,2,'2026-08-11 04:55:06'),(17,1,2,6,3,'2026-08-11 04:55:06'),(18,1,3,6,1,'2026-08-11 04:55:06'),(19,1,1,7,5,'2026-08-11 04:55:29'),(20,1,2,7,5,'2026-08-11 04:55:29'),(21,1,3,7,1,'2026-08-11 04:55:29'),(22,1,1,8,1,'2026-08-11 04:55:29'),(23,1,2,8,4,'2026-08-11 04:55:29'),(24,1,3,8,2,'2026-08-11 04:55:29'),(25,1,1,9,5,'2026-08-11 04:55:29'),(26,1,2,9,1,'2026-08-11 04:55:29'),(27,1,3,9,3,'2026-08-11 04:55:29'),(28,1,1,10,5,'2026-08-11 04:55:36'),(29,1,2,10,3,'2026-08-11 04:55:36'),(30,1,3,10,2,'2026-08-11 04:55:36');
/*!40000 ALTER TABLE `penilaian` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'voting_bps'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-11 13:18:05
