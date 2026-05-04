<?php
require_once '../includes/session_check.php';
checkRole('user');
require_once '../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['kandidat_id'])) {
    header('Location: dashboard.php');
    exit();
}

$stmt = $pdo->prepare("SELECT status_voting FROM users WHERE id = :id");
$stmt->execute(['id' => $_SESSION['user_id']]);
$user = $stmt->fetch();

if ($user['status_voting'] === 'sudah') {
    header('Location: sukses.php');
    exit();
}

try {
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("INSERT INTO votes (user_id, kandidat_id) VALUES (:uid, :kid)");
    $stmt->execute(['uid' => $_SESSION['user_id'], 'kid' => $_POST['kandidat_id']]);
    
    $stmt = $pdo->prepare("UPDATE users SET status_voting = 'sudah' WHERE id = :id");
    $stmt->execute(['id' => $_SESSION['user_id']]);
    
    $pdo->commit();
    
    header('Location: sukses.php');
    exit();
} catch (PDOException $e) {
    $pdo->rollBack();
    die("Error: " . $e->getMessage());
}
?>