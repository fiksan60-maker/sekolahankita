<?php
require_once 'config/koneksi.php';
session_start();

$token = $_GET['token'] ?? '';

if (empty($token)) {
    die("Token tidak valid!");
}

// Cek token
$stmt = $pdo->prepare("SELECT u.* FROM users u WHERE u.qr_token = :token AND u.role = 'user'");
$stmt->execute(['token' => $token]);
$user = $stmt->fetch();

if (!$user) {
    die("QR Code tidak valid atau sudah digunakan!");
}

// Cek apakah sudah voting
if ($user['status_voting'] === 'sudah') {
    die("Anda sudah menggunakan hak suara. Tidak dapat login kembali.");
}

// Cek apakah voting sedang berlangsung
$stmt = $pdo->query("SELECT * FROM settings WHERE setting_key IN ('voting_start', 'voting_end')");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
$now = time();
$start = strtotime($settings['voting_start'] ?? '2024-01-01');
$end = strtotime($settings['voting_end'] ?? '2026-12-31');

if ($now < $start) {
    die("Voting belum dibuka. Silakan kembali saat waktunya.");
}
if ($now > $end) {
    die("Voting sudah ditutup.");
}

// Hapus QR token setelah dipakai (sekali pakai)
$stmt = $pdo->prepare("UPDATE users SET qr_token = NULL WHERE id = :id");
$stmt->execute(['id' => $user['id']]);

// Set session
$_SESSION['user_id'] = $user['id'];
$_SESSION['nama'] = $user['nama'];
$_SESSION['role'] = 'user';

// Redirect ke dashboard user
header('Location: user/dashboard.php');
exit();
?>