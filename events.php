<?php
require 'config.php';
$pageTitle = 'Events — GARUTVERSE';

$events = $conn->query("
  SELECT e.*, p.name AS place_name, p.slug AS place_slug
  FROM events e
  LEFT JOIN places p ON p.id = e.place_id
  WHERE e.status='published'
  ORDER BY e.start_datetime ASC
")->fetch_all(MYSQLI_ASSOC);

include 'includes/header.php';
?>

<div class="page-header">
  <div class="page-header-inner">
    <h1>Events Garut</h1>
    <p>Festival, pameran, dan acara di Garut.</p>
  </div>
</div>

<section class="section">
  <div class="grid-sm">
    <?php foreach ($events as $e): ?>
      <div class="info-card">
        <span class="tag">🎉 <?= date('d M Y', strtotime($e['start_datetime'])) ?></span>
        <h3><?= e($e['name']) ?></h3>
        <p><?= e($e['description']) ?></p>
        <?php if ($e['place_name']): ?>
          <div class="meta">📍 <a href="detail.php?slug=<?= urlencode($e['place_slug']) ?>" style="color:var(--primary);font-weight:600;"><?= e($e['place_name']) ?></a></div>
        <?php endif; ?>
        <div class="meta" style="margin-top:6px;">
          🗓 <?= date('d M Y H:i', strtotime($e['start_datetime'])) ?> – <?= date('d M Y H:i', strtotime($e['end_datetime'])) ?>
        </div>
        <?php if ($e['organizer']): ?><div class="meta" style="margin-top:6px;">Oleh <b><?= e($e['organizer']) ?></b></div><?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<?php include 'includes/footer.php'; ?>