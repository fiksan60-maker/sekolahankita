<?php
// includes/session_check.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

function checkRole($allowed_role) {
    if ($_SESSION['role'] !== $allowed_role) {
        header('Location: ../login.php');
        exit();
    }
}

// Function to check if voting is open
function isVotingOpen($pdo) {
    $stmt = $pdo->query("SELECT is_open FROM voting_status WHERE id = 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result && $result['is_open'] === '1';
}