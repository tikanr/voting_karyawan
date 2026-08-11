<?php
// =========================================================
// KONFIGURASI
// =========================================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'voting_bps');
define('DB_USER', 'root');
define('DB_PASS', '');

define('APP_NAME', 'Polling Insan Statistik Teladan (IST) Kabupaten Madiun');

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1); // sementara aktifkan dulu untuk lihat error saat testing
date_default_timezone_set('Asia/Jakarta');