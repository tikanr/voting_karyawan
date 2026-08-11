<?php
require_once __DIR__ . '/../includes/functions.php';
require_super_admin();

$message = '';
$errors = [];
$editAdmin = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'save_admin') {
        $id = $_POST['id'] ?? null;
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'super_admin';
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($username === '') {
            $errors[] = 'Username admin wajib diisi.';
        }

        if (!$id && strlen($password) < 6) {
            $errors[] = 'Password admin minimal 6 karakter.';
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("SELECT id FROM admin WHERE username = ? AND id <> ? LIMIT 1");
            $stmt->execute([$username, $id ?: 0]);
            if ($stmt->fetch()) {
                $errors[] = 'Username admin sudah digunakan.';
            }
        }

        if (empty($errors)) {
            if ($id) {
                if ($password !== '') {
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE admin SET username = ?, password_hash = ?, role = ?, is_active = ? WHERE id = ?");
                    $stmt->execute([$username, $passwordHash, $role, $isActive, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE admin SET username = ?, role = ?, is_active = ? WHERE id = ?");
                    $stmt->execute([$username, $role, $isActive, $id]);
                }
                $message = 'Akun admin berhasil diperbarui.';
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO admin (username, password_hash, role, is_active) VALUES (?, ?, ?, ?)");
                $stmt->execute([$username, $passwordHash, $role, $isActive]);
                $message = 'Akun admin baru berhasil ditambahkan.';
            }
        }
    } elseif ($action === 'toggle_admin') {
        $id = $_POST['id'] ?? null;
        if ($id) {
            $stmt = $pdo->prepare("UPDATE admin SET is_active = NOT is_active WHERE id = ?");
            $stmt->execute([$id]);
            $message = 'Status akun admin berhasil diubah.';
        }
    }
}

if ($_GET['action'] === 'edit' && !empty($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM admin WHERE id = ? LIMIT 1");
    $stmt->execute([$_GET['id']]);
    $editAdmin = $stmt->fetch();
}

$admins = $pdo->query("SELECT * FROM admin ORDER BY role ASC, username ASC")->fetchAll();
$currentAdmin = current_admin($pdo);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kelola Akun Admin — <?= h(APP_NAME) ?></title>
<link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="page-wrap">
  <?php include __DIR__ . '/admin_nav.php'; ?>

  <?php if ($message): ?><div class="alert alert-success"><?= h($message) ?></div><?php endif; ?>
  <?php if ($errors): ?><div class="alert alert-error"><?= h(implode(' ', $errors)) ?></div><?php endif; ?>

  <div class="card">
    <h2>Daftar Admin</h2>
    <table class="data-table">
      <thead>
        <tr>
          <th>Username</th>
          <th>Role</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($admins as $admin): ?>
          <tr>
            <td><?= h($admin['username']) ?></td>
            <td><?= h($admin['role']) ?></td>
            <td><span class="badge-pill <?= $admin['is_active'] ? 'success' : 'muted' ?>"><?= $admin['is_active'] ? 'Aktif' : 'Nonaktif' ?></span></td>
            <td class="table-actions">
              <form method="get" style="display:inline;">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" value="<?= $admin['id'] ?>">
                <button type="submit" class="btn btn-secondary btn-sm">Edit</button>
              </form>
              <?php if ($admin['id'] !== $currentAdmin['id']): ?>
                <form method="post" style="display:inline;">
                  <input type="hidden" name="action" value="toggle_admin">
                  <input type="hidden" name="id" value="<?= $admin['id'] ?>">
                  <button type="submit" class="btn btn-secondary btn-sm"><?= $admin['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?></button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="card">
    <h2><?= $editAdmin ? 'Edit Akun Admin' : 'Tambah Admin Baru' ?></h2>
    <form method="post">
      <input type="hidden" name="action" value="save_admin">
      <?php if ($editAdmin): ?>
        <input type="hidden" name="id" value="<?= $editAdmin['id'] ?>">
      <?php endif; ?>
      <label for="username">Username</label>
      <input type="text" id="username" name="username" required value="<?= h($editAdmin['username'] ?? '') ?>">
      <label for="password">Password <?= $editAdmin ? '(biarkan kosong jika tidak ingin mengubah)' : '' ?></label>
      <input type="password" id="password" name="password" <?= $editAdmin ? '' : 'required' ?>>
      <label for="role">Role</label>
      <select id="role" name="role">
        <option value="super_admin" <?= ($editAdmin['role'] ?? '') === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
        <option value="admin" <?= ($editAdmin['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
      </select>
      <label>
        <input type="checkbox" name="is_active" value="1" <?= ($editAdmin['is_active'] ?? 1) ? 'checked' : '' ?>> Akun aktif
      </label>
      <button type="submit" class="btn btn-primary"><?= $editAdmin ? 'Simpan Perubahan' : 'Tambah Admin' ?></button>
    </form>
  </div>
</div>
</body>
</html>
