<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'garutverse';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die('Koneksi gagal: ' . $conn->connect_error);
$conn->set_charset('utf8mb4');

// Helper: format harga
function priceLabel($min, $max) {
    if ($min === null || $max === null) return '-';
    if ((int)$min === 0 && (int)$max === 0) return 'Gratis';
    return 'Rp' . number_format($min,0,',','.') . ' – Rp' . number_format($max,0,',','.');
}

// Helper: ambil cover image
function getCover($conn, $placeId) {
    $stmt = $conn->prepare("SELECT file_url FROM place_media WHERE place_id=? AND media_type='image' ORDER BY sort_order LIMIT 1");
    $stmt->bind_param('i', $placeId);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    return $r['file_url'] ?? null;
}

// Helper: rating rata-rata
function getRatings($conn) {
    $map = [];
    $q = $conn->query("SELECT place_id, ROUND(AVG(rating),1) avg, COUNT(*) total FROM reviews WHERE status='approved' GROUP BY place_id");
    while ($r = $q->fetch_assoc()) $map[$r['place_id']] = $r;
    return $map;
}

// Helper: active nav
function navActive($page) {
    $current = basename($_SERVER['PHP_SELF']);
    return $current === $page ? 'active' : '';
}

// Helper: escape
function e($str) { return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8'); }