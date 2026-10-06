<?php
require 'config.php';
$pageTitle = 'Stories — GARUTVERSE';

$stories = $conn->query("
  SELECT s.*, p.name AS place_name, c.name AS culture_name
  FROM stories s
  LEFT JOIN places p ON p.id = s.place_id
  LEFT JOIN cultures c ON c.id = s.culture_id
  WHERE s.status='published'
  ORDER BY s.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

include 'includes/header.php';
?>

<div class="page-header">
  <div class="page-header-inner">
    <h1>Garut Stories</h1>
    <p>Cerita di balik tempat, makanan, budaya, dan masyarakat Garut.</p>
  </div>
</div>

<section class="section">
  <div class="grid-sm">
    <?php foreach ($stories as $s): ?>
      <a href="story.php?slug=<?= urlencode($s['slug']) ?>" class="info-card" style="padding:0; overflow:hidden;">

  <!-- FOTO COVER -->
  <div style="aspect-ratio:16/10; background:#F1F5F9 center/cover;
              <?= $s['image_url'] ? "background-image:url('".e($s['image_url'])."')" : '' ?>;">
    <?php if (!$s['image_url']): ?>
      <div style="display:grid; place-items:center; height:100%; font-size:48px; color:#CBD5E1;">📖</div>
    <?php endif; ?>
  </div>

  <div style="padding:24px 28px;">
    <span class="tag">
      <?php if ($s['place_name']): ?>📍 <?= e($s['place_name']) ?>
      <?php elseif ($s['culture_name']): ?>🎭 <?= e($s['culture_name']) ?>
      <?php else: ?>📖 Story<?php endif; ?>
    </span>
    <h3><?= e($s['title']) ?></h3>
    <p><?= e(mb_strimwidth($s['content'], 0, 140, '...')) ?></p>
    <div class="meta">
      Oleh <b><?= e($s['author'] ?? 'Anonim') ?></b>
      · <?= date('d M Y', strtotime($s['created_at'])) ?>
    </div>
  </div>
</a>
    <?php endforeach; ?>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
<?php
require 'config.php';
$pageTitle = 'Stories — GARUTVERSE';

/* ---- Search & filter ---- */
$q = trim($_GET['q'] ?? '');
$where = ["s.status = 'published'"];
$params = []; $types = '';

if ($q) {
    $where[] = "(s.title LIKE ? OR s.content LIKE ? OR s.author LIKE ?)";
    $params[] = "%$q%"; $params[] = "%$q%"; $params[] = "%$q%";
    $types .= 'sss';
}

$sql = "SELECT s.*,
               p.name AS place_name, p.slug AS place_slug,
               c.name AS culture_name
        FROM stories s
        LEFT JOIN places   p ON p.id = s.place_id
        LEFT JOIN cultures c ON c.id = s.culture_id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY s.created_at DESC";
$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$stories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

/* ---- Story terbaru untuk highlight ---- */
$featured = $stories[0] ?? null;

include 'includes/header.php';
?>

<!-- PAGE HEADER -->
<div class="page-header" style="--header-img: url('https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?w=2000');">
  <div class="page-header-inner">
    <div class="hero-eyebrow" style="margin-bottom:24px;">Stories</div>
    <h1>Garut Stories</h1>
    <p>Cerita di balik tempat, makanan, budaya, dan masyarakat Garut.</p>
  </div>
</div>

<section class="section">

  <!-- SEARCH -->
  <form method="get" style="display:flex; gap:10px; max-width:640px; margin-bottom:40px;">
    <input type="text" name="q" value="<?= e($q) ?>"
           placeholder="Cari cerita, penulis, atau topik..."
           style="flex:1; padding:14px 20px; border:1.5px solid var(--border); border-radius:999px; font-size:14px; font-family:inherit; background:white;">
    <button class="btn-primary" type="submit" style="padding:14px 28px;">Cari</button>
  </form>

  <?php if ($q): ?>
    <div style="margin-bottom:24px; color:var(--muted); font-size:14px;">
      Hasil pencarian untuk "<b style="color:var(--ink);"><?= e($q) ?></b>":
      <?= count($stories) ?> cerita ditemukan.
    </div>
  <?php endif; ?>

  <?php if (empty($stories)): ?>
    <div class="empty">
      <div class="icon">📖</div>
      <h3 style="font-size:20px; color:var(--ink); margin-bottom:8px;">Belum ada cerita</h3>
      <p>Cerita akan muncul di sini begitu ditambahkan.</p>
    </div>
  <?php else: ?>

    <!-- FEATURED STORY -->
    <?php if (!$q && $featured): ?>
      <div class="section-head" style="margin-bottom:24px;">
        <div class="title-block">
          <div class="section-eyebrow">Featured Story</div>
          <h2>Cerita utama</h2>
        </div>
      </div>

     <a href="story.php?slug=<?= urlencode($featured['slug']) ?>"
   class="info-card" style="display:block; padding:0; margin-bottom:64px; background:var(--secondary); color:white; border:none; overflow:hidden; position:relative; min-height:420px;">
  
  <?php if ($featured['image_url']): ?>
    <div style="position:absolute; inset:0; background:linear-gradient(180deg, rgba(15,23,42,0.3), rgba(15,23,42,0.9)), url('<?= e($featured['image_url']) ?>') center/cover;"></div>
  <?php endif; ?>

  <div style="position:relative; padding:48px; z-index:1;">
    <span class="tag" style="background:var(--accent); color:white;">
      <?= $featured['place_name'] ? '📍 ' . e($featured['place_name']) : ($featured['culture_name'] ? '🎭 ' . e($featured['culture_name']) : '📖 Story') ?>
    </span>
    <h3 style="font-family:'Playfair Display',serif; font-size:clamp(24px,3vw,40px); line-height:1.15; margin:16px 0 14px; color:white; letter-spacing:-1px;">
      <?= e($featured['title']) ?>
    </h3>
    <p style="color:rgba(255,255,255,0.75); font-size:15px; max-width:640px; margin-bottom:24px;">
      <?= e(mb_strimwidth($featured['content'], 0, 220, '...')) ?>
    </p>
    <div style="color:rgba(255,255,255,0.6); font-size:13px;">
      Oleh <b style="color:var(--accent);"><?= e($featured['author'] ?? 'Anonim') ?></b>
      · <?= date('d M Y', strtotime($featured['created_at'])) ?>
      · <span style="color:var(--accent); font-weight:700;">Baca cerita →</span>
    </div>
  </div>
</a>
        <span class="tag" style="background:var(--accent); color:white;">
          <?= $featured['place_name'] ? '📍 ' . e($featured['place_name']) : ($featured['culture_name'] ? '🎭 ' . e($featured['culture_name']) : '📖 Story') ?>
        </span>
        <h3 style="font-family:'Playfair Display',serif; font-size:clamp(24px,3vw,40px); line-height:1.15; margin:16px 0 14px; color:white; letter-spacing:-1px;">
          <?= e($featured['title']) ?>
        </h3>
        <p style="color:rgba(255,255,255,0.75); font-size:15px; max-width:640px; margin-bottom:24px;">
          <?= e(mb_strimwidth($featured['content'], 0, 220, '...')) ?>
        </p>
        <div style="color:rgba(255,255,255,0.6); font-size:13px;">
          Oleh <b style="color:var(--accent);"><?= e($featured['author'] ?? 'Anonim') ?></b>
          · <?= date('d M Y', strtotime($featured['created_at'])) ?>
          · <span style="color:var(--accent); font-weight:700;">Baca cerita →</span>
        </div>
      </a>
    <?php endif; ?>

    <!-- ALL STORIES -->
    <?php if (!$q): ?>
      <div class="section-head" style="margin-bottom:24px;">
        <div class="title-block">
          <div class="section-eyebrow">All Stories</div>
          <h2>Semua cerita</h2>
        </div>
      </div>
    <?php endif; ?>

    <div class="grid-sm">
      <?php foreach ($stories as $i => $s): ?>
        <?php if (!$q && $i === 0) continue; /* skip featured */ ?>
        <a href="story.php?slug=<?= urlencode($s['slug']) ?>" class="info-card" style="padding:0; overflow:hidden;">
  
  <!-- Foto cover -->
  <div style="aspect-ratio:16/10; background:#F1F5F9 center/cover; position:relative;
              <?= $s['image_url'] ? "background-image:url('".e($s['image_url'])."')" : '' ?>;">
    <?php if (!$s['image_url']): ?>
      <div style="display:grid; place-items:center; height:100%; font-size:48px; color:#CBD5E1;">📖</div>
    <?php endif; ?>
  </div>

  <div style="padding:24px 28px;">
    <span class="tag">
      <?php if ($s['place_name']): ?>📍 <?= e($s['place_name']) ?>
      <?php elseif ($s['culture_name']): ?>🎭 <?= e($s['culture_name']) ?>
      <?php else: ?>📖 Story<?php endif; ?>
    </span>
    <h3><?= e($s['title']) ?></h3>
    <p><?= e(mb_strimwidth($s['content'], 0, 140, '...')) ?></p>
    <div class="meta">
      Oleh <b><?= e($s['author'] ?? 'Anonim') ?></b>
      · <?= date('d M Y', strtotime($s['created_at'])) ?>
    </div>
  </div>
</a>
      <?php endforeach; ?>
    </div>

  <?php endif; ?>

</section>

<?php include 'includes/footer.php'; ?>