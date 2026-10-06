<?php
require 'config.php';
$pageTitle = 'Culinary — GARUTVERSE';

$items = $conn->query("
  SELECT cd.*, p.name AS place_name, p.slug AS place_slug, p.id AS place_id
  FROM culinary_details cd
  LEFT JOIN places p ON p.id = cd.place_id
  ORDER BY cd.food_name
")->fetch_all(MYSQLI_ASSOC);

include 'includes/header.php';
?>

<div class="page-header">
  <div class="page-header-inner">
    <h1>Kuliner Khas Garut</h1>
    <p>Makanan dan minuman khas dari Garut, lengkap dengan ceritanya.</p>
  </div>
</div>

<section class="section">
  <div class="grid-sm">
    <?php foreach ($items as $c): ?>
      <div class="info-card">
        <span class="tag">🍜 Kuliner</span>
        <h3><?= e($c['food_name']) ?></h3>
        <p><?= e($c['description']) ?></p>
        <div class="meta">
          Tersedia di:
          <a href="detail.php?slug=<?= urlencode($c['place_slug']) ?>" style="color:var(--primary);font-weight:600;">
            <?= e($c['place_name']) ?>
          </a>
        </div>
        <div class="meta" style="margin-top:6px;">
          Harga: <b><?= priceLabel($c['price_min'], $c['price_max']) ?></b>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<?php include 'includes/footer.php'; ?>