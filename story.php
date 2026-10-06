<?php
require 'config.php';
$slug = $_GET['slug'] ?? '';
if (!$slug) { header('Location: stories.php'); exit; }

$stmt = $conn->prepare("
  SELECT s.*, p.name AS place_name, p.slug AS place_slug, c.name AS culture_name
  FROM stories s
  LEFT JOIN places p ON p.id = s.place_id
  LEFT JOIN cultures c ON c.id = s.culture_id
  WHERE s.slug = ? LIMIT 1
");
$stmt->bind_param('s', $slug);
$stmt->execute();
$s = $stmt->get_result()->fetch_assoc();
if (!$s) { http_response_code(404); die('Story tidak ditemukan.'); }
$pageTitle = $s['title'] . ' — GARUTVERSE';

include 'includes/header.php';
?>
<div class="detail-hero" style="
  <?php if ($s['image_url']): ?>
    background: linear-gradient(180deg, rgba(15,23,42,0.5), rgba(15,23,42,0.9)),
                url('<?= e($s['image_url']) ?>') center/cover;
  <?php endif; ?>
">
  <div class="detail-hero-inner">
    <div class="crumb">
      <a href="index.php">Home</a> · <a href="stories.php">Stories</a>
    </div>
    <h1><?= e($s['title']) ?></h1>
    <div class="meta">
      <span>✍️ <?= e($s['author'] ?? 'Anonim') ?></span>
      <span>📅 <?= date('d M Y', strtotime($s['created_at'])) ?></span>
      <?php if ($s['source']): ?><span>📚 <?= e($s['source']) ?></span><?php endif; ?>
    </div>
  </div>
</div>

<section class="detail-body" style="max-width:760px;">
  <?php if ($s['place_name']): ?>
    <p style="margin-bottom:20px;">Terkait tempat: <a href="detail.php?slug=<?= urlencode($s['place_slug']) ?>" style="color:var(--primary);font-weight:600;">📍 <?= e($s['place_name']) ?></a></p>
  <?php endif; ?>
  <?php if ($s['culture_name']): ?>
    <p style="margin-bottom:20px;">Budaya: <b>🎭 <?= e($s['culture_name']) ?></b></p>
  <?php endif; ?>

  <div style="font-size:16px;line-height:1.8;color:#374151;">
    <?= nl2br(e($s['content'])) ?>
  </div>
</section>

<?php include 'includes/footer.php'; ?>