<?php
require 'config.php';
$pageTitle = 'UMKM — GARUTVERSE';

$umkms = $conn->query("
  SELECT u.*, p.name AS place_name, p.slug AS place_slug
  FROM umkm u
  LEFT JOIN places p ON p.id = u.place_id
  ORDER BY u.verified DESC, u.owner_name
")->fetch_all(MYSQLI_ASSOC);

include 'includes/header.php';
?>

<div class="page-header">
  <div class="page-header-inner">
    <h1>UMKM Garut</h1>
    <p>Usaha Mikro, Kecil, dan Menengah lokal Garut.</p>
  </div>
</div>

<section class="section">
  <div class="grid-sm">
    <?php foreach ($umkms as $u): ?>
      <?php
        $pr = $conn->prepare("SELECT * FROM products WHERE umkm_id=? AND status='active'");
        $pr->bind_param('i', $u['id']); $pr->execute();
        $products = $pr->get_result()->fetch_all(MYSQLI_ASSOC);
      ?>
      <div class="info-card">
        <span class="tag"><?= $u['verified'] ? '✓ Terverifikasi' : 'UMKM' ?></span>
        <h3><?= e($u['owner_name'] ?? 'Tanpa Nama') ?></h3>
        <p><?= e($u['description']) ?></p>
        <div class="meta">
          📍 <a href="detail.php?slug=<?= urlencode($u['place_slug']) ?>" style="color:var(--primary);font-weight:600;"><?= e($u['place_name']) ?></a>
        </div>
        <?php if ($u['contact']): ?><div class="meta" style="margin-top:6px;">📞 <?= e($u['contact']) ?></div><?php endif; ?>
        <?php if ($products): ?>
          <div class="meta" style="margin-top:8px;">
            <b>Produk:</b> <?= count($products) ?> item — <?= e($products[0]['name']) ?> dll.
          </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<?php include 'includes/footer.php'; ?>