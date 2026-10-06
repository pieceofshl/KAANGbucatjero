<?php
require 'config.php';
$slug = $_GET['slug'] ?? '';
if (!$slug) { header('Location: explore.php'); exit; }

$stmt = $conn->prepare("
  SELECT p.*, c.name AS category_name, d.name AS district_name, v.name AS village_name
  FROM places p
  LEFT JOIN categories c ON c.id = p.category_id
  LEFT JOIN districts  d ON d.id = p.district_id
  LEFT JOIN villages   v ON v.id = p.village_id
  WHERE p.slug = ? LIMIT 1
");
$stmt->bind_param('s', $slug);
$stmt->execute();
$place = $stmt->get_result()->fetch_assoc();

if (!$place) { http_response_code(404); die('Place tidak ditemukan.'); }
$pageTitle = $place['name'] . ' — GARUTVERSE';

// Media
$media = $conn->prepare("SELECT * FROM place_media WHERE place_id=? ORDER BY sort_order");
$media->bind_param('i', $place['id']); $media->execute();
$mediaList = $media->get_result()->fetch_all(MYSQLI_ASSOC);

// Tags
$tags = $conn->prepare("SELECT t.* FROM tags t JOIN place_tags pt ON pt.tag_id=t.id WHERE pt.place_id=?");
$tags->bind_param('i', $place['id']); $tags->execute();
$tagList = $tags->get_result()->fetch_all(MYSQLI_ASSOC);

// Reviews
$rev = $conn->prepare("SELECT r.*, pr.name AS user_name FROM reviews r JOIN profiles pr ON pr.id=r.user_id WHERE r.place_id=? AND r.status='approved' ORDER BY r.created_at DESC");
$rev->bind_param('i', $place['id']); $rev->execute();
$reviews = $rev->get_result()->fetch_all(MYSQLI_ASSOC);

// Rating avg
$ra = $conn->prepare("SELECT ROUND(AVG(rating),1) avg, COUNT(*) total FROM reviews WHERE place_id=? AND status='approved'");
$ra->bind_param('i', $place['id']); $ra->execute();
$ratingAgg = $ra->get_result()->fetch_assoc();

// Culinary detail
$cd = $conn->prepare("SELECT * FROM culinary_details WHERE place_id=?");
$cd->bind_param('i', $place['id']); $cd->execute();
$culinary = $cd->get_result()->fetch_assoc();

// UMKM + products
$um = $conn->prepare("SELECT * FROM umkm WHERE place_id=?");
$um->bind_param('i', $place['id']); $um->execute();
$umkm = $um->get_result()->fetch_assoc();
$products = [];
if ($umkm) {
    $pr = $conn->prepare("SELECT * FROM products WHERE umkm_id=? AND status='active'");
    $pr->bind_param('i', $umkm['id']); $pr->execute();
    $products = $pr->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Stories terkait
$st = $conn->prepare("SELECT * FROM stories WHERE place_id=? AND status='published'");
$st->bind_param('i', $place['id']); $st->execute();
$stories = $st->get_result()->fetch_all(MYSQLI_ASSOC);

// Events terkait
$ev = $conn->prepare("SELECT * FROM events WHERE place_id=? AND status='published' ORDER BY start_datetime");
$ev->bind_param('i', $place['id']); $ev->execute();
$events = $ev->get_result()->fetch_all(MYSQLI_ASSOC);

include 'includes/header.php';
?>

<div class="detail-hero">
  <div class="detail-hero-inner">
    <div class="crumb">
      <a href="index.php">Home</a> · <a href="explore.php">Explore</a> · <?= e($place['category_name']) ?>
    </div>
    <h1><?= e($place['name']) ?></h1>
    <div class="meta">
      <span>📍 <?= e($place['district_name'] ?? '-') ?><?= $place['village_name'] ? ', ' . e($place['village_name']) : '' ?></span>
      <span>💰 <?= priceLabel($place['price_min'], $place['price_max']) ?></span>
      <span>🕒 <?= e($place['opening_hours'] ?? '-') ?></span>
      <?php if ($ratingAgg['total'] > 0): ?>
        <span>⭐ <?= $ratingAgg['avg'] ?> (<?= $ratingAgg['total'] ?> review)</span>
      <?php endif; ?>
    </div>
  </div>
</div>

<section class="detail-body">
  <div class="detail-grid">

    <!-- KIRI -->
    <div>
      <div class="detail-section">
        <h2>Deskripsi</h2>
        <p><?= nl2br(e($place['description'] ?? '-')) ?></p>
        <?php if ($place['address']): ?><p><b>Alamat:</b> <?= e($place['address']) ?></p><?php endif; ?>
        <?php if ($place['phone']): ?><p><b>Telepon:</b> <?= e($place['phone']) ?></p><?php endif; ?>
        <?php if ($place['website']): ?><p><b>Website:</b> <a href="<?= e($place['website']) ?>" target="_blank" style="color:var(--primary)"><?= e($place['website']) ?></a></p><?php endif; ?>
      </div>

      <?php if ($tagList): ?>
        <div class="detail-section">
          <h2>Tags</h2>
          <div class="tag-list">
            <?php foreach ($tagList as $t): ?><span><?= e($t['name']) ?></span><?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <?php if ($culinary): ?>
        <div class="detail-section">
          <h2>🍜 Kuliner Khas</h2>
          <p><b><?= e($culinary['food_name']) ?></b></p>
          <p><?= nl2br(e($culinary['description'])) ?></p>
          <?php if ($culinary['history']): ?><p><b>Sejarah:</b> <?= nl2br(e($culinary['history'])) ?></p><?php endif; ?>
          <?php if ($culinary['ingredients']): ?><p><b>Bahan:</b> <?= e($culinary['ingredients']) ?></p><?php endif; ?>
          <p><b>Kisaran harga:</b> <?= priceLabel($culinary['price_min'], $culinary['price_max']) ?></p>
        </div>
      <?php endif; ?>

      <?php if ($umkm): ?>
        <div class="detail-section">
          <h2>🏪 UMKM</h2>
          <p><b>Pemilik:</b> <?= e($umkm['owner_name'] ?? '-') ?> <?= $umkm['verified'] ? '<span style="color:#10b981;">✓ Terverifikasi</span>' : '' ?></p>
          <p><?= e($umkm['description']) ?></p>
          <?php if ($umkm['contact']): ?><p><b>Kontak:</b> <?= e($umkm['contact']) ?></p><?php endif; ?>
          <?php if ($umkm['social_media']): ?><p><b>Sosial Media:</b> <?= e($umkm['social_media']) ?></p><?php endif; ?>

          <?php if ($products): ?>
            <h2 style="margin-top:24px;">Produk</h2>
            <table class="table">
              <thead><tr><th>Nama</th><th>Deskripsi</th><th>Harga</th></tr></thead>
              <tbody>
                <?php foreach ($products as $pr): ?>
                  <tr><td><b><?= e($pr['name']) ?></b></td><td><?= e($pr['description']) ?></td><td>Rp<?= number_format($pr['price'],0,',','.') ?></td></tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <?php if ($stories): ?>
        <div class="detail-section">
          <h2>📖 Stories Terkait</h2>
          <?php foreach ($stories as $s): ?>
            <div class="info-card" style="margin-bottom:12px;">
              <h3 style="font-size:16px;"><?= e($s['title']) ?></h3>
              <p><?= e(mb_strimwidth($s['content'], 0, 180, '...')) ?></p>
              <a href="story.php?slug=<?= urlencode($s['slug']) ?>" class="btn-primary" style="font-size:13px;">Baca →</a>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if ($events): ?>
        <div class="detail-section">
          <h2>🎉 Events di Sini</h2>
          <?php foreach ($events as $ev): ?>
            <div class="info-card" style="margin-bottom:12px;">
              <h3 style="font-size:16px;"><?= e($ev['name']) ?></h3>
              <p><?= e($ev['description']) ?></p>
              <div class="meta"><?= date('d M Y', strtotime($ev['start_datetime'])) ?> · <?= e($ev['organizer']) ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="detail-section">
        <h2>💬 Reviews (<?= count($reviews) ?>)</h2>
        <?php if (empty($reviews)): ?>
          <p style="color:var(--muted);">Belum ada review.</p>
        <?php else: ?>
          <?php foreach ($reviews as $r): ?>
            <div class="review">
              <div class="review-head">
                <b><?= e($r['user_name']) ?></b>
                <span class="stars"><?= str_repeat('★', (int)$r['rating']) . str_repeat('☆', 5 - (int)$r['rating']) ?></span>
              </div>
              <p><?= e($r['comment'] ?? '') ?></p>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- KANAN -->
    <aside>
      <div class="sidebar-box">
        <h3>Informasi</h3>
        <ul>
          <li><span>Kategori</span><b><?= e($place['category_name']) ?></b></li>
          <li><span>Kecamatan</span><b><?= e($place['district_name'] ?? '-') ?></b></li>
          <li><span>Desa</span><b><?= e($place['village_name'] ?? '-') ?></b></li>
          <li><span>Harga</span><b><?= priceLabel($place['price_min'], $place['price_max']) ?></b></li>
          <li><span>Jam Buka</span><b><?= e($place['opening_hours'] ?? '-') ?></b></li>
          <?php if ($place['latitude']): ?>
            <li><span>Koordinat</span><b style="font-size:11px;"><?= $place['latitude'] ?>, <?= $place['longitude'] ?></b></li>
          <?php endif; ?>
        </ul>
      </div>
      <div class="sidebar-box">
        <h3>Status</h3>
        <ul>
          <li><span>Featured</span><b><?= $place['is_featured'] ? 'Ya' : 'Tidak' ?></b></li>
          <li><span>Hidden Gem</span><b><?= $place['is_hidden_gem'] ? 'Ya' : 'Tidak' ?></b></li>
        </ul>
      </div>
    </aside>

  </div>
</section>

<?php include 'includes/footer.php'; ?>