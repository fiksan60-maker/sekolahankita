<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Deteksi posisi folder buat path logout
$current_script = $_SERVER['PHP_SELF'];
if (strpos($current_script, '/admin/') !== false) {
    $logout_link = '../logout.php';
} elseif (strpos($current_script, '/user/') !== false) {
    $logout_link = '../logout.php';
} else {
    $logout_link = 'logout.php';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Voting OSIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background-color: #f3f4f6; }
        .card-hover:hover { transform: scale(1.02); transition: transform 0.2s ease-in-out; }
    </style>
</head>
<body>
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold text-gray-800">E-Voting OSIS</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600">Selamat datang, <strong><?php echo htmlspecialchars($_SESSION['nama']); ?></strong></span>
                    <span class="text-xs bg-gray-100 px-2 py-1 rounded-full text-gray-600"><?php echo ucfirst($_SESSION['role']); ?></span>
                    <a href="<?php echo $logout_link; ?>" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">Keluar</a>
                </div>
            </div>
        </div>
    </nav>
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">