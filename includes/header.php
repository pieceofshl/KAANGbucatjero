<?php
if (!isset($conn)) require_once __DIR__ . '/../config.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle ?? 'GARUTVERSE — Discover Garut') ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,700;1,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<header class="site-header" id="siteHeader">
  <div class="header-inner">
    <a href="index.php" class="logo">
      <span class="logo-mark">G</span>
      <span class="logo-text">GARUT<span>VERSE</span></span>
    </a>
    <nav class="nav">
      <a href="index.php"     class="<?= navActive('index.php') ?>">Home</a>
      <a href="explore.php"   class="<?= navActive('explore.php') ?>">Explore</a>
      <a href="stories.php"   class="<?= navActive('stories.php') ?>">Stories</a>
      <a href="cultures.php"  class="<?= navActive('cultures.php') ?>">Culture</a>
      <a href="culinary.php"  class="<?= navActive('culinary.php') ?>">Culinary</a>
      <a href="umkm.php"      class="<?= navActive('umkm.php') ?>">UMKM</a>
      <a href="events.php"    class="<?= navActive('events.php') ?>">Events</a>
    </nav>
    <div class="header-actions">
      <a href="login.php" class="btn-ghost">Masuk</a>
      <a href="explore.php" class="btn-primary">Mulai Jelajah</a>
    </div>
  </div>
</header>