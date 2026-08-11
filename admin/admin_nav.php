<?php
$admin = current_admin($pdo);
$menuItems = [
    ['url' => 'dashboard.php', 'label' => 'Dashboard'],
    ['url' => 'pegawai.php', 'label' => 'Data Pegawai'],
    ['url' => 'kandidat.php', 'label' => 'Kandidat'],
    ['url' => 'kategori.php', 'label' => 'Kategori'],
];

if ($admin && $admin['role'] === 'super_admin') {
    $menuItems[] = ['url' => 'admins.php', 'label' => 'Akun Admin'];
    $menuItems[] = ['url' => 'periode.php', 'label' => 'Periode Voting'];
}

$menuItems[] = ['url' => 'export.php', 'label' => 'Export CSV'];
$currentFile = basename($_SERVER['PHP_SELF']);
?>
<header class="topbar">
  <div>
    <strong><?= h(APP_NAME) ?></strong>
    <div class="admin-badge"><?= h($admin['role'] === 'super_admin' ? 'Super Admin' : 'Admin') ?></div>
    <p class="muted">Kelola polling dan hasil penilaian secara terstruktur.</p>
  </div>
  <div class="topbar-actions">
    <nav class="admin-nav">
      <?php foreach ($menuItems as $item): ?>
        <a href="<?= h($item['url']) ?>" class="admin-nav-link <?= $currentFile === $item['url'] ? 'active' : '' ?>"><?= h($item['label']) ?></a>
      <?php endforeach; ?>
    </nav>
    <a href="logout.php" class="link-muted">Logout</a>
  </div>
</header>
