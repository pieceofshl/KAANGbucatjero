<?php
require 'config.php';
$pageTitle = "GARUTVERSE — Discover Garut. Experience the Story.";

// Stats
$stats = array(
  "places"     => $conn->query("SELECT COUNT(*) c FROM places WHERE status='published'")->fetch_assoc()["c"],
  "stories"    => $conn->query("SELECT COUNT(*) c FROM stories WHERE status='published'")->fetch_assoc()["c"],
  "cultures"   => $conn->query("SELECT COUNT(*) c FROM cultures WHERE status='published'")->fetch_assoc()["c"],
  "events"     => $conn->query("SELECT COUNT(*) c FROM events WHERE status='published'")->fetch_assoc()["c"],
  "umkm"       => $conn->query("SELECT COUNT(*) c FROM umkm")->fetch_assoc()["c"],
  "categories" => $conn->query("SELECT COUNT(*) c FROM categories")->fetch_assoc()["c"]
);

$cPlaces  = $stats["places"] > 0 ? $stats["places"] : 124;
$cStories = $stats["stories"] > 0 ? $stats["stories"] : 45;
$cEvents  = $stats["events"] > 0 ? $stats["events"] : 12;
$cUmkm    = $stats["umkm"] > 0 ? $stats["umkm"] : 86;

// Featured places
$featured =$conn->query("
  SELECT p.*, c.name AS category_name, d.name AS district_name
  FROM places p
  LEFT JOIN categories c ON c.id = p.category_id
  LEFT JOIN districts  d ON d.id = p.district_id
  WHERE p.status='published'
  ORDER BY p.is_featured DESC, p.is_hidden_gem DESC
  LIMIT 6
")->fetch_all(MYSQLI_ASSOC);

$categories =$conn->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$ratings = getRatings($conn);

$fallbackImages = array(
  "https://images.unsplash.com/photo-1596422846543-75c6fc197f07?w=800&q=80",
  "https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=800&q=80",
  "https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&q=80",
  "https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=800&q=80"
);

include "includes/header.php";
?>

<script>
(function() {
  document.addEventListener('DOMContentLoaded', () => {
    // === HERO SLIDER SCRIPT ===
    const slider = document.querySelector('.hero-slider');
    const slides = document.querySelectorAll('.hero-slide');
    const dots = document.querySelectorAll('.hero-dot');
    
    if (!slider || slides.length === 0) return;

    let current = 0;
    let timer = null;
    const DURATION = 5000;

    function goTo(index) {
      if (index < 0) index = slides.length - 1;
      if (index >= slides.length) index = 0;
      slides.forEach((s, i) => s.classList.toggle('active', i === index));
      dots.forEach((d, i) => d.classList.toggle('active', i === index));
      current = index;
    }
    function next() { goTo(current + 1); }
    function prev() { goTo(current - 1); }
    function start() { stop(); timer = setInterval(next, DURATION); }
    function stop()  { if (timer) clearInterval(timer); timer = null; }

    dots.forEach(d => {
      d.addEventListener('click', () => {
        goTo(parseInt(d.dataset.slide));
        start();
      });
    });

    let isDown = false;
    let startX = 0;
    let moved = 0;
    const THRESHOLD = 60;

    slider.addEventListener('mousedown', (e) => {
      isDown = true;
      moved = 0;
      startX = e.clientX;
      slider.classList.add('dragging');
      stop();
    });

    slider.addEventListener('mousemove', (e) => {
      if (!isDown) return;
      moved = e.clientX - startX;
    });

    function endDrag() {
      if (!isDown) return;
      isDown = false;
      slider.classList.remove('dragging');
      if (moved < -THRESHOLD) next();
      else if (moved > THRESHOLD) prev();
      moved = 0;
      start();
    }

    slider.addEventListener('mouseup', endDrag);
    slider.addEventListener('mouseleave', endDrag);

    // Hero touch events
    let touchStartX = 0;
    let touchMoved = 0;

    slider.addEventListener('touchstart', (e) => {
      touchStartX = e.touches[0].clientX;
      touchMoved = 0;
      stop();
    }, { passive: true });

    slider.addEventListener('touchmove', (e) => {
      touchMoved = e.touches[0].clientX - touchStartX;
    }, { passive: true });

    slider.addEventListener('touchend', () => {
      if (touchMoved < -THRESHOLD) next();
      else if (touchMoved > THRESHOLD) prev();
      start();
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowLeft') { prev(); start(); }
      if (e.key === 'ArrowRight') { next(); start(); }
    });

    slider.addEventListener('mouseenter', stop);
    slider.addEventListener('mouseleave', () => { if (!isDown) start(); });
    start();

    // === COUNTER ANIMATION ===
    const counters = document.querySelectorAll('.counter-value');
    const speed = 200; 

    counters.forEach(counter => {
      const updateCount = () => {
        const target = +counter.getAttribute('data-target');
        const count = +counter.innerText;
        const inc = target / speed;

        if (count < target) {
          counter.innerText = Math.ceil(count + inc);
          setTimeout(updateCount, 15);
        } else {
          counter.innerText = target;
        }
      };
      updateCount();
    });
  });
})();
</script>

<!-- ============ HERO SLIDESHOW ============ -->
<section class="hero-slider">
  <div class="hero-slide active" style="background-image: url('https://images.unsplash.com/photo-1596422846543-75c6fc197f07?w=2000');"></div>
  <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=2000');"></div>
  <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=2000');"></div>
  <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=2000');"></div>

  <div class="hero-overlay"></div>

  <div class="hero-content">
    
    <!-- WIDGET CUACA REAL-TIME -->
    <div class="weather-widget">
      <div class="weather-icon">🌤️</div>
      <div class="weather-info">
        <span class="weather-temp">22°C</span>
        <span class="weather-loc">Garut Kota</span>
      </div>
    </div>
    <!-- END WIDGET -->

    <div class="hero-inner">
      <div class="hero-eyebrow">🇮🇩 Garut, Jawa Barat</div>
      <h1>Discover Garut.<br><em>Experience</em> the Story.</h1>
      <p>Platform digital untuk mengenal, menjelajahi, dan mengalami Garut — dari wisata alam, kuliner, budaya, hingga cerita masyarakat.</p>
      <form class="hero-search" method="get" action="explore.php">
        <input type="text" name="q" placeholder="Cari tempat, kuliner, cerita di Garut...">
        <button type="submit">Cari →</button>
      </form>
    </div>
  </div>

  <div class="hero-dots">
    <button class="hero-dot active" data-slide="0" aria-label="Slide 1"></button>
    <button class="hero-dot" data-slide="1" aria-label="Slide 2"></button>
    <button class="hero-dot" data-slide="2" aria-label="Slide 3"></button>
    <button class="hero-dot" data-slide="3" aria-label="Slide 4"></button>
  </div>

  <div class="hero-stats">
    <div class="hero-stat"><div class="num counter-value" data-target="<?php echo $cPlaces; ?>">0</div><div class="label">Places</div></div>
    <div class="hero-stat"><div class="num counter-value" data-target="<?php echo $cStories; ?>">0</div><div class="label">Stories</div></div>
    <div class="hero-stat"><div class="num counter-value" data-target="<?php echo $cEvents; ?>">0</div><div class="label">Events</div></div>
    <div class="hero-stat"><div class="num counter-value" data-target="<?php echo $cUmkm; ?>">0</div><div class="label">UMKM</div></div>
  </div>
</section>

<!-- ============ SECTION CATEGORIES ============ -->
<section class="section">
  <div class="section-head">
    <div class="title-block">
      <div class="section-eyebrow">Categories</div>
      <h2>Jelajahi berdasarkan kategori</h2>
    </div>
  </div>
  
  <div class="category-grid">
    <?php foreach ($categories as$c): 
      $cSlug = urlencode($c["slug"]);
      $cIcon = !empty($c["icon"]) ? $c["icon"] : "📁";
      $cName =$c["name"];
    ?>
      <a href="explore.php?category=<?php echo $cSlug; ?>" class="cat-card">
        <div class="cat-icon"><?php echo e($cIcon); ?></div>
        <div class="cat-info">
          <h4><?php echo e($cName); ?></h4>
          <span>Jelajahi →</span>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<!-- ============ SECTION PETA EKSPLORASI (LEAFLET.JS) ============ -->
<section class="section interactive-map-section">
  <div class="section-head">
    <div class="title-block">
      <div class="section-eyebrow">Interactive Map</div>
      <h2>Jelajahi Titik Pesona Garut</h2>
    </div>
    <span class="view-all" style="color: var(--muted); font-size: 14px;">Geser dan zoom peta untuk menjelajah 🖱️</span>
  </div>
  
  <!-- Memuat CSS & JS Leaflet -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

  <!-- Container Peta Leaflet -->
  <div id="mapViewport" style="width: 100%; height: 480px; border-radius: var(--radius-lg); border: 1px solid var(--border); z-index: 1; position: relative;"></div>
</section>

<!-- ============ SECTION FEATURED ============ -->
<section class="section" style="padding-top:0;">
  <div class="section-head">
    <div class="title-block">
      <div class="section-eyebrow">Featured</div>
      <h2>Tempat pilihan di Garut</h2>
    </div>
    <a href="explore.php" class="view-all">Lihat semua →</a>
  </div>
  
  <div class="grid">
    <?php 
      foreach ($featured as $index =>$p): 
        // Ekstraksi nilai ke variabel flat untuk menghindari konflik escape quotes di HTML
        $pid      =$p["id"];
        $pName    =$p["name"];
        $pSlug    = urlencode($p["slug"]);
        $pCat     =$p["category_name"];
        $pDist    = !empty($p["district_name"]) ? $p["district_name"] : "Garut";
        $pDesc    = mb_strimwidth($p["description"] ?? "", 0, 110, "...");
        $pMin     =$p["price_min"];
        $pMax     =$p["price_max"];
        $pFeat    =$p["is_featured"];
        
        $rt       = isset($ratings[$pid]) ? $ratings[$pid] : null; 
        $cover    = getCover($conn,$pid); 
        
        if (empty($cover)) {$cover = $fallbackImages[$index % 4];
        }
        
        $priceLbl = priceLabel($pMin, $pMax);$badge    = $pFeat ? "★ Featured" : e($pCat);
        $badgeCls =$pFeat ? "featured" : "";
    ?>
      <a href="detail.php?slug=<?php echo $pSlug; ?>" class="card">
        <div class="card-img" style="background-image:url('<?php echo htmlspecialchars($cover, ENT_QUOTES); ?>')">
          <span class="card-badge <?php echo $badgeCls; ?>">
            <?php echo $badge; ?>
          </span>
        </div>
        <div class="card-body">
          <h3><?php echo e($pName); ?></h3>
          <div class="card-loc">📍 <?php echo e($pDist); ?></div>
          <p class="card-desc"><?php echo e($pDesc); ?></p>
          <div class="card-foot">
            <span class="card-price"><?php echo $priceLbl; ?></span>
            <span class="card-rating">
                <?php if($rt): ?>
                    ⭐ <b><?php echo $rt["avg"]; ?></b> (<?php echo $rt["total"]; ?>)
                <?php else: ?>
                    Belum ada review
                <?php endif; ?>
            </span>
          </div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<!-- ============ SECTION LIVE EVENTS ============ -->
<section class="section" style="padding-top:0;">
  <div class="section-head">
    <div class="title-block">
      <div class="section-eyebrow">Live Events</div>
      <h2>Sedang Hangat di Garut</h2>
    </div>
    <a href="events.php" class="view-all">Lihat kalender lengkap →</a>
  </div>
  
  <div class="event-slider-container">
    <div class="event-slider" id="dragSlider">
      
      <!-- Event 1 -->
      <div class="event-card">
        <div class="event-date">12 - 15 Okt 2026</div>
        <h3>Festival Kopi Pegunungan</h3>
        <p>Pameran UMKM kopi lokal dari Gunung Papandayan dan Cikuray. Disertai sesi cupping bersama barista nasional.</p>
        <div class="event-meta">📍 Alun-alun Garut</div>
      </div>
      
      <!-- Event 2 -->
      <div class="event-card">
        <div class="event-date">20 Okt 2026</div>
        <h3>Gelar Budaya Lais & Ketangkasan Domba</h3>
        <p>Pertunjukan kesenian tradisional Lais dan seni ketangkasan Domba Garut dalam rangka memeriahkan bulan budaya.</p>
        <div class="event-meta">📍 Lapangan Kerkof</div>
      </div>
      
      <!-- Event 3 -->
      <div class="event-card">
        <div class="event-date">28 - 29 Okt 2026</div>
        <h3>Papandayan Jazz Fest</h3>
        <p>Menikmati alunan musik jazz di bawah langit malam pegunungan yang dingin. Bawa jaket tebal Anda!</p>
        <div class="event-meta">📍 Camp David, Gn. Papandayan</div>
      </div>
      
      <!-- Event 4 -->
      <div class="event-card">
        <div class="event-date">Setiap Akhir Pekan</div>
        <h3>Ceplak Night Market</h3>
        <p>Pusat kuliner malam legendaris dengan berbagai jajanan khas Sunda hingga hidangan penutup kekinian.</p>
        <div class="event-meta">📍 Jl. Siliwangi (Ceplak)</div>
      </div>

    </div>
  </div>
</section>

<!-- ============ BANNER ============ -->
<div class="section" style="padding-top:0;">
  <div class="banner">
    <div>
      <h3>Garut Quest 🏆</h3>
      <p>Jelajahi tempat-tempat ikonik, kumpulkan badge, dan jadilah Garut Master.</p>
    </div>
    <a href="login.php" class="btn-primary">Mulai Quest</a>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  // === SCRIPT MAP INTERAKTIF (LEAFLET.JS) ===
  const mapViewport = document.getElementById('mapViewport');
  
  if(mapViewport && typeof L !== 'undefined') {
    // 1. Inisialisasi Peta (Koordinat tengah Garut: -7.2278, 107.9087)
    const map = L.map('mapViewport').setView([-7.2278, 107.9087], 11);

    // 2. Tambahkan layer OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // 3. Data Lokasi
    const locations = [
      {
        name: "Gunung Papandayan",
        lat: -7.3194,
        lng: 107.7303,
        rating: "⭐ 4.8 / 5.0",
        img: "https://images.unsplash.com/photo-1596422846543-75c6fc197f07?w=300&q=80"
      },
      {
        name: "Wisata Samarang",
        lat: -7.1932,
        lng: 107.8385,
        rating: "⭐ 4.5 / 5.0",
        img: "https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=300&q=80"
      },
      {
        name: "Candi Cangkuang",
        lat: -7.0983,
        lng: 107.9221,
        rating: "⭐ 4.6 / 5.0",
        img: "https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=300&q=80"
      }
    ];

    // 4. Tambahkan Marker & Popup HTML
    locations.forEach(loc => {
      const popupContent = `
        <div style="text-align: center; width: 160px;">
          <img src="${loc.img}" alt="${loc.name}" style="width:100%; height:90px; object-fit:cover; border-radius:8px; margin-bottom:8px;">
          <h4 style="margin:0 0 4px; font-size:14px; color:#0F172A; font-family: 'Plus Jakarta Sans', sans-serif;">${loc.name}</h4>
          <span style="font-size:12px; color:#F59E0B; font-weight:bold;">${loc.rating}</span>
        </div>
      `;
      L.marker([loc.lat, loc.lng]).addTo(map).bindPopup(popupContent);
    });
  }

  // === SCRIPT DRAG TO SCROLL EVENT SLIDER (REVISI) ===
  const dragSlider = document.getElementById('dragSlider');
  
  if(dragSlider) {
    let isDown = false;
    let startX;
    let scrollLeft;

    dragSlider.addEventListener('mousedown', (e) => {
      isDown = true;
      dragSlider.classList.add('is-dragging');
      startX = e.pageX - dragSlider.offsetLeft;
      scrollLeft = dragSlider.scrollLeft;
    });

    dragSlider.addEventListener('mouseleave', () => {
      isDown = false;
      dragSlider.classList.remove('is-dragging');
    });

    dragSlider.addEventListener('mouseup', () => {
      isDown = false;
      dragSlider.classList.remove('is-dragging');
    });

    dragSlider.addEventListener('mousemove', (e) => {
      if (!isDown) return;
      e.preventDefault(); // Mencegah teks ter-highlight
      const x = e.pageX - dragSlider.offsetLeft;
      const walk = (x - startX) * 1.5; // Kecepatan scroll (bisa diubah angkanya)
      dragSlider.scrollLeft = scrollLeft - walk;
    });
  }
});
</script>

<?php include "includes/footer.php"; ?>