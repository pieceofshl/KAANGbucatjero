<?php
require 'config.php';
$pageTitle = 'Explore — GARUTVERSE';

/* ---------- Filter dari URL ---------- */
$activeCat = $_GET['cat'] ?? '';
$q         = trim($_GET['q'] ?? '');
$sort      = $_GET['sort'] ?? 'featured';
$view      = $_GET['view'] ?? 'grid';   // grid | list

$where = ["p.status='published'"];
$params = []; $types = '';

if ($activeCat) {
    $where[] = "c.slug = ?";
    $params[] = $activeCat; $types .= 's';
}
if ($q) {
    $where[] = "(p.name LIKE ? OR p.description LIKE ? OR p.address LIKE ?)";
    $params[] = "%$q%"; $params[] = "%$q%"; $params[] = "%$q%"; $types .= 'sss';
}

/* ---------- Sorting ---------- */
$orderBy = match($sort) {
    'name'    => 'p.name ASC',
    'price'   => 'p.price_min ASC',
    'newest'  => 'p.created_at DESC',
    default   => 'p.is_featured DESC, p.is_hidden_gem DESC, p.name ASC',
};

$sql = "SELECT p.*, c.name AS category_name, c.slug AS category_slug, d.name AS district_name
        FROM places p
        LEFT JOIN categories c ON c.id = p.category_id
        LEFT JOIN districts  d ON d.id = p.district_id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY $orderBy";

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$places = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$ratings = getRatings($conn);

/* ---------- Statistik kecil ---------- */
$totalPlaces = $conn->query("SELECT COUNT(*) c FROM places WHERE status='published'")->fetch_assoc()['c'];
$hiddenCount = $conn->query("SELECT COUNT(*) c FROM places WHERE status='published' AND is_hidden_gem=1")->fetch_assoc()['c'];
$featuredCount = $conn->query("SELECT COUNT(*) c FROM places WHERE status='published' AND is_featured=1")->fetch_assoc()['c'];

include 'includes/header.php';
?>

<!-- ============ EXPLORE HERO (compact, beda dari home) ============ -->
<section class="explore-hero">
  <div class="explore-hero-inner">
    <div class="explore-eyebrow">🧭 Explore Mode</div>
    <h1>Temukan tempat di <em>Garut</em>.</h1>
    <p>Filter, cari, dan jelajahi <?= $totalPlaces ?> tempat — dari wisata alam sampai hidden gem.</p>

    <!-- Search besar -->
    <form class="explore-search" method="get" action="explore.php">
      <?php if ($activeCat): ?><input type="hidden" name="cat" value="<?= e($activeCat) ?>"><?php endif; ?>
      <?php if ($sort !== 'featured'): ?><input type="hidden" name="sort" value="<?= e($sort) ?>"><?php endif; ?>
      <span class="explore-search-icon">🔍</span>
      <input type="text" name="q" value="<?= e($q) ?>" placeholder="Cari tempat, kuliner, alamat...">
      <button type="submit">Cari</button>
    </form>

    <!-- Stats kecil horizontal -->
    <div class="explore-mini-stats">
      <div><b><?= $totalPlaces ?></b><span>Total Places</span></div>
      <div><b><?= $featuredCount ?></b><span>Featured</span></div>
      <div><b><?= $hiddenCount ?></b><span>Hidden Gem</span></div>
      <div><b><?= count($categories) ?></b><span>Kategori</span></div>
    </div>
  </div>
</section>

<!-- ============ FILTER BAR STICKY ============ -->
<div class="filter-bar">
  <div class="filter-bar-inner">
    <div class="filter-pills">
      <?php
        $baseUrl = 'explore.php?';
        $qs = [];
        if ($q) $qs['q'] = $q;
        if ($sort !== 'featured') $qs['sort'] = $sort;
        if ($view !== 'grid') $qs['view'] = $view;
      ?>
      <a href="<?= $baseUrl . http_build_query($qs) ?>"
         class="filter-pill <?= !$activeCat ? 'active' : '' ?>">Semua</a>
      <?php foreach ($categories as $c): ?>
        <?php $qs2 = $qs; $qs2['cat'] = $c['slug']; ?>
        <a href="<?= $baseUrl . http_build_query($qs2) ?>"
           class="filter-pill <?= $activeCat === $c['slug'] ? 'active' : '' ?>">
          <?= e($c['name']) ?>
        </a>
      <?php endforeach; ?>
    </div>

    <div class="filter-tools">
      <select class="filter-select" onchange="location.href=this.value">
        <?php
          $sortQs = $qs;
          $sortQs['cat'] = $activeCat ?: null;
          $sortQs = array_filter($sortQs, fn($v) => $v !== null && $v !== '');
          $mk = function($s) use ($sortQs, $baseUrl) {
              $sortQs['sort'] = $s;
              return $baseUrl . http_build_query($sortQs);
          };
        ?>
        <option value="<?= $mk('featured') ?>" <?= $sort==='featured'?'selected':'' ?>>⭐ Featured</option>
        <option value="<?= $mk('newest')   ?>" <?= $sort==='newest'  ?'selected':'' ?>>🆕 Terbaru</option>
        <option value="<?= $mk('name')     ?>" <?= $sort==='name'    ?'selected':'' ?>>🔤 Nama A-Z</option>
        <option value="<?= $mk('price')    ?>" <?= $sort==='price'   ?'selected':'' ?>>💰 Termurah</option>
      </select>

      <div class="view-toggle">
        <?php $viewQs = $qs; $viewQs['cat'] = $activeCat ?: null; $viewQs = array_filter($viewQs, fn($v)=>$v!==null && $v!==''); ?>
        <?php $viewQs['view'] = 'grid'; ?>
        <a href="<?= $baseUrl . http_build_query($viewQs) ?>" class="view-btn <?= $view==='grid'?'active':'' ?>" title="Grid">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><rect x="1" y="1" width="6" height="6" rx="1"/><rect x="9" y="1" width="6" height="6" rx="1"/><rect x="1" y="9" width="6" height="6" rx="1"/><rect x="9" y="9" width="6" height="6" rx="1"/></svg>
        </a>
        <?php $viewQs['view'] = 'list'; ?>
        <a href="<?= $baseUrl . http_build_query($viewQs) ?>" class="view-btn <?= $view==='list'?'active':'' ?>" title="List">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><rect x="1" y="2" width="14" height="2" rx="1"/><rect x="1" y="7" width="14" height="2" rx="1"/><rect x="1" y="12" width="14" height="2" rx="1"/></svg>
        </a>
      </div>
    </div>
  </div>
</div>

<!-- ============ HASIL ============ -->
<section class="section" style="padding-top:40px;">
  <div class="result-head">
    <div>
      <div class="section-eyebrow">
        <?= $activeCat ? e(ucfirst($activeCat)) : 'All Places' ?>
      </div>
      <h2 class="result-title">
        <?php if ($q): ?>
          Hasil untuk "<?= e($q) ?>"
        <?php elseif ($activeCat): ?>
          Kategori <?= e(ucfirst($activeCat)) ?>
        <?php else: ?>
          Semua tempat di Garut
        <?php endif; ?>
      </h2>
    </div>
    <div class="result-count">
      <b><?= count($places) ?></b> tempat ditemukan
    </div>
  </div>

  <?php if (empty($places)): ?>
    <div class="empty">
      <div class="icon">🔍</div>
      <h3 style="font-size:20px; color:var(--ink); margin-bottom:8px;">Tidak ada hasil</h3>
      <p>Coba ubah kata kunci atau pilih kategori lain.</p>
    </div>
  <?php else: ?>

    <?php if ($view === 'list'): ?>
      <!-- ===== LIST VIEW ===== -->
      <div class="explore-list">
        <?php foreach ($places as $p): ?>
          <?php $rt = $ratings[$p['id']] ?? null; $cover = getCover($conn, $p['id']); ?>
          <a href="detail.php?slug=<?= urlencode($p['slug']) ?>" class="list-item">
            <div class="list-thumb <?= $cover ? '' : 'placeholder' ?>"
                 style="<?= $cover ? "background-image:url('".e($cover)."')" : '' ?>">
              <?php if (!$cover): ?>🏞️<?php endif; ?>
            </div>
            <div class="list-body">
              <div class="list-head">
                <span class="list-tag"><?= e($p['category_name']) ?></span>
                <?php if ($p['is_featured']): ?><span class="list-tag gold">★ Featured</span><?php endif; ?>
                <?php if ($p['is_hidden_gem']): ?><span class="list-tag purple">◆ Hidden</span><?php endif; ?>
              </div>
              <h3><?= e($p['name']) ?></h3>
              <div class="list-loc">📍 <?= e($p['district_name'] ?? 'Garut') ?></div>
              <p class="list-desc"><?= e(mb_strimwidth($p['description'] ?? '', 0, 160, '...')) ?></p>
            </div>
            <div class="list-side">
              <div class="list-price"><?= priceLabel($p['price_min'], $p['price_max']) ?></div>
              <div class="list-rating">
                <?php if ($rt): ?>⭐ <b><?= $rt['avg'] ?></b><span>(<?= $rt['total'] ?>)</span>
                <?php else: ?><span>—</span><?php endif; ?>
              </div>
              <div class="list-arrow">→</div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>

    <?php else: ?>
      <!-- ===== GRID VIEW (seperti home tapi lebih rapat) ===== -->
      <div class="grid">
        <?php foreach ($places as $p): ?>
          <?php $rt = $ratings[$p['id']] ?? null; $cover = getCover($conn, $p['id']); ?>
          <a href="detail.php?slug=<?= urlencode($p['slug']) ?>" class="card">
            <div class="card-img <?= $cover ? '' : 'placeholder' ?>"
                 style="<?= $cover ? "background-image:url('".e($cover)."')" : '' ?>">
              <?php if (!$cover): ?>🏞️<?php endif; ?>

              <?php if ($p['is_featured']): ?>
                <span class="card-badge featured">★ Featured</span>
              <?php elseif ($p['is_hidden_gem']): ?>
                <span class="card-badge hidden">◆ Hidden Gem</span>
              <?php else: ?>
                <span class="card-badge"><?= e($p['category_name']) ?></span>
              <?php endif; ?>
            </div>
            <div class="card-body">
              <h3><?= e($p['name']) ?></h3>
              <div class="card-loc">📍 <?= e($p['district_name'] ?? 'Garut') ?></div>
              <p class="card-desc"><?= e(mb_strimwidth($p['description'] ?? '', 0, 100, '...')) ?></p>
              <div class="card-foot">
                <span class="card-price"><?= priceLabel($p['price_min'], $p['price_max']) ?></span>
                <span class="card-rating">
                  <?php if ($rt): ?>⭐ <b><?= $rt['avg'] ?></b> (<?= $rt['total'] ?>)
                  <?php else: ?>—<?php endif; ?>
                </span>
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  <?php endif; ?>
</section>
<script src="assets/explore.js"></script>
<?php include 'includes/footer.php'; ?>