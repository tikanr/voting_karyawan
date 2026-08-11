<?php
require_once __DIR__ . '/db.php';

function ensure_admin_schema(PDO $pdo) {
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;

    try {
        $hasRole = $pdo->query("SHOW COLUMNS FROM admin LIKE 'role'")->fetch();
        if (!$hasRole) {
            $pdo->exec("ALTER TABLE admin ADD COLUMN role ENUM('super_admin','admin') NOT NULL DEFAULT 'super_admin'");
        }

        $hasActive = $pdo->query("SHOW COLUMNS FROM admin LIKE 'is_active'")->fetch();
        if (!$hasActive) {
            $pdo->exec("ALTER TABLE admin ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1");
        }
    } catch (PDOException $e) {
        // Jika tabel admin belum tersedia atau tidak dapat diubah, lanjutkan tanpa memblokir halaman.
    }
}

function ensure_settings_table(PDO $pdo) {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS pengaturan (
            `kunci` VARCHAR(100) PRIMARY KEY,
            `nilai` TEXT DEFAULT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    } catch (PDOException $e) {
        // Abaikan jika tidak bisa membuat, fungsi default akan menangani tanpa error.
    }
}

function require_login() {
    if (empty($_SESSION['pegawai_id'])) {
        header('Location: login.php');
        exit;
    }
}

function require_admin() {
    global $pdo;
    if (empty($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }

    ensure_admin_schema($pdo);
    $admin = current_admin($pdo);
    if (!$admin) {
        unset($_SESSION['admin_id']);
        header('Location: login.php');
        exit;
    }
}

function require_super_admin() {
    global $pdo;
    require_admin();
    $admin = current_admin($pdo);
    if (!$admin || $admin['role'] !== 'super_admin') {
        header('Location: dashboard.php');
        exit;
    }
}

function current_admin(PDO $pdo) {
    if (empty($_SESSION['admin_id'])) {
        return null;
    }

    ensure_admin_schema($pdo);
    $stmt = $pdo->prepare("SELECT * FROM admin WHERE id = ? AND is_active = 1 LIMIT 1");
    $stmt->execute([$_SESSION['admin_id']]);
    return $stmt->fetch();
}

function current_pegawai(PDO $pdo) {
    if (empty($_SESSION['pegawai_id'])) {
        return null;
    }
    $stmt = $pdo->prepare("SELECT * FROM pegawai WHERE id = ?");
    $stmt->execute([$_SESSION['pegawai_id']]);
    return $stmt->fetch();
}

function get_kategori_by_step(PDO $pdo, int $step) {
    $stmt = $pdo->prepare("SELECT * FROM kategori WHERE urutan = ? LIMIT 1");
    $stmt->execute([$step]);
    return $stmt->fetch();
}

function get_indikator_by_kategori(PDO $pdo, int $kategoriId) {
    $stmt = $pdo->prepare("SELECT * FROM indikator WHERE kategori_id = ? ORDER BY urutan ASC");
    $stmt->execute([$kategoriId]);
    return $stmt->fetchAll();
}

function get_all_kandidat(PDO $pdo) {
    return $pdo->query("SELECT * FROM kandidat ORDER BY urutan ASC")->fetchAll();
}

function total_step(PDO $pdo) {
    $count = $pdo->query("SELECT COUNT(*) c FROM kategori")->fetch()['c'];
    return (int)$count + 1;
}

function get_setting(PDO $pdo, string $key, $default = null) {
    ensure_settings_table($pdo);
    try {
        $stmt = $pdo->prepare("SELECT nilai FROM pengaturan WHERE kunci = ? LIMIT 1");
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        return $row ? $row['nilai'] : $default;
    } catch (PDOException $e) {
        return $default;
    }
}

function set_setting(PDO $pdo, string $key, string $value) {
    ensure_settings_table($pdo);
    $stmt = $pdo->prepare("REPLACE INTO pengaturan (kunci, nilai) VALUES (?, ?)");
    $stmt->execute([$key, $value]);
}

function voting_is_open(PDO $pdo): bool {
    return get_setting($pdo, 'voting_status', 'open') === 'open';
}

function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
