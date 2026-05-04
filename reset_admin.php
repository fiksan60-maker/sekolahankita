<?php
// reset_admin.php
require_once 'config/koneksi.php';

// Hapus admin lama
$pdo->exec("DELETE FROM users WHERE nim = 'admin'");

// Buat admin baru
$pass = 'admin123';
$hash = password_hash($pass, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO users (nim, nama, kelas, password, role, status_voting) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute(['admin', 'Administrator', 'Admin', $hash, 'admin', 'belum']);

echo "<h2 style='color:green;'>Admin berhasil direset!</h2>";
echo "<p>Username: <b>admin</b></p>";
echo "<p>Password: <b>admin123</b></p>";
echo "<p>Hash: <code>" . $hash . "</code></p>";
echo "<p><a href='login.php'>Login sekarang</a></p>";
?>