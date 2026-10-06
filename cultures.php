<?php
require 'config.php';
$pageTitle = 'Culture — GARUTVERSE';
$cultures = $conn->query("SELECT * FROM cultures WHERE status='published' ORDER BY name")->fetch_all(MYSQLI_ASSOC);
include 'includes/header.php';
?>

<div class="page-header">
  <div class="page-header-inner">
    <h1>Budaya Garut</h1>
    <p>Seni, tradisi, permainan, pakaian, dan kesenian khas Garut.</p>
  </div>
</div>

<section class="section">
  <div class="grid-sm">
    <?php foreach ($cultures as $c): ?>
      <div class="info-card">
        <span class="tag"><?= e(strtoupper($c['category'])) ?></span>
        <h3><?= e($c['name']) ?></h3>
        <p><?= e($c['description']) ?></p>
        <?php if ($c['location']): ?><div class="meta">📍 <?= e($c['location']) ?></div><?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<?php include 'includes/footer.php'; ?>