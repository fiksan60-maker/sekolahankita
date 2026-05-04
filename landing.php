<?php
session_start();
require_once 'config/koneksi.php';

$total_kandidat = $pdo->query("SELECT COUNT(*) as t FROM kandidat")->fetch()['t'];
$total_pemilih = $pdo->query("SELECT COUNT(*) as t FROM users WHERE role='user'")->fetch()['t'];
$kandidat = $pdo->query("SELECT * FROM kandidat ORDER BY no_urut LIMIT 3")->fetchAll();

// Ambil waktu voting
$settings = [];
$stmt = $pdo->query("SELECT * FROM settings WHERE setting_key IN ('voting_start', 'voting_end')");
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
$now = time();
$start = strtotime($settings['voting_start'] ?? '2024-01-01');
$end = strtotime($settings['voting_end'] ?? '2026-12-31');
$voting_open = ($now >= $start && $now <= $end);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Voting OSIS - Pemilihan Ketua OSIS 2024/2025</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .glass-card { background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); border-radius: 20px; }
        .candidate-card { border-radius: 16px; overflow: hidden; transition: all 0.3s; border: none; }
        .candidate-card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(0,0,0,0.2); }
        .candidate-card img { height: 220px; object-fit: cover; }
        .btn-glow { border-radius: 14px; font-weight: 700; padding: 14px 35px; transition: all 0.3s; letter-spacing: 0.5px; font-size: 18px; }
        .btn-glow:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(0,0,0,0.3); }
        .step-number { width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 22px; margin: 0 auto 15px; }
        .hero-text { text-shadow: 0 2px 10px rgba(0,0,0,0.2); }
        .countdown-box { background: rgba(255,255,255,0.15); backdrop-filter: blur(5px); border-radius: 16px; display: inline-block; padding: 10px 20px; min-width: 60px; }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg" style="background: rgba(255,255,255,0.08); backdrop-filter: blur(15px);">
        <div class="container">
            <a class="navbar-brand fw-bold text-white" href="#">
                <span style="font-size: 24px;">&#x1F5F3;</span> E-Voting OSIS
            </a>
            <div class="ms-auto d-flex gap-2">
                <a href="login.php" class="btn btn-outline-light rounded-4 px-4 fw-semibold">Login</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="text-white text-center py-5">
        <div class="container py-4">
            <h1 class="display-4 fw-bold hero-text mb-2">Pemilihan Ketua OSIS</h1>
            <p class="lead mb-3 hero-text opacity-75">Periode 2024/2025</p>
            
            <?php if (!$voting_open && $now < $start): ?>
            <div class="my-4">
                <p class="mb-2">Voting dibuka dalam:</p>
                <div class="d-flex justify-content-center gap-3" id="countdown">
                    <div class="countdown-box"><h3 class="mb-0 fw-bold" id="days">00</h3><small>Hari</small></div>
                    <div class="countdown-box"><h3 class="mb-0 fw-bold" id="hours">00</h3><small>Jam</small></div>
                    <div class="countdown-box"><h3 class="mb-0 fw-bold" id="minutes">00</h3><small>Menit</small></div>
                    <div class="countdown-box"><h3 class="mb-0 fw-bold" id="seconds">00</h3><small>Detik</small></div>
                </div>
            </div>
            <?php elseif ($voting_open): ?>
            <div class="my-4">
                <span class="badge bg-success fs-6 px-4 py-2">Voting Sedang Berlangsung</span>
            </div>
            <?php else: ?>
            <div class="my-4">
                <span class="badge bg-danger fs-6 px-4 py-2">Voting Telah Ditutup</span>
            </div>
            <?php endif; ?>

            <div class="d-flex justify-content-center gap-5 mb-4">
                <div class="text-center">
                    <h2 class="fw-bold mb-0"><?php echo $total_kandidat; ?></h2>
                    <small class="opacity-75">Kandidat</small>
                </div>
                <div class="text-center">
                    <h2 class="fw-bold mb-0"><?php echo $total_pemilih; ?></h2>
                    <small class="opacity-75">Pemilih</small>
                </div>
            </div>
            
            <?php if ($voting_open): ?>
            <a href="login.php" class="btn btn-light btn-glow btn-lg">Gunakan Hak Suara Sekarang</a>
            <?php elseif ($now > $end): ?>
            <a href="login.php" class="btn btn-outline-light btn-glow btn-lg">Lihat Hasil Akhir</a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Candidates Preview -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-white text-center fw-bold mb-2">Kandidat Ketua OSIS</h2>
            <p class="text-white text-center opacity-75 mb-4">Mengenal para calon pemimpin</p>
            <div class="row g-4 justify-content-center">
                <?php foreach ($kandidat as $k): ?>
                <div class="col-md-4">
                    <div class="card candidate-card">
                        <?php 
                        $fp = 'assets/images/' . $k['foto'];
                        if (!file_exists($fp) || empty($k['foto'])) $fp = 'assets/images/default.jpg';
                        ?>
                        <img src="<?php echo $fp; ?>" alt="<?php echo htmlspecialchars($k['nama']); ?>" 
                             onerror="this.src='https://placehold.co/400x220?text=No+Image'">
                        <div class="card-body text-center">
                            <span class="badge bg-primary mb-2">No. Urut <?php echo $k['no_urut']; ?></span>
                            <h4 class="fw-bold"><?php echo htmlspecialchars($k['nama']); ?></h4>
                            <p class="small text-muted"><?php echo htmlspecialchars(substr($k['visi'], 0, 100)); ?>...</p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Cara Voting -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-white text-center fw-bold mb-4">Cara Melakukan Voting</h2>
            <div class="row g-4 justify-content-center">
                <div class="col-md-4">
                    <div class="glass-card p-4 text-center h-100">
                        <div class="step-number bg-primary text-white">1</div>
                        <h5 class="fw-bold">Login</h5>
                        <p class="small text-muted">Login menggunakan NIM dan password yang diberikan oleh panitia</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="glass-card p-4 text-center h-100">
                        <div class="step-number bg-success text-white">2</div>
                        <h5 class="fw-bold">Pilih Kandidat</h5>
                        <p class="small text-muted">Pilih satu kandidat ketua OSIS yang menurutmu paling tepat</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="glass-card p-4 text-center h-100">
                        <div class="step-number bg-info text-white">3</div>
                        <h5 class="fw-bold">Submit & Selesai</h5>
                        <p class="small text-muted">Konfirmasi pilihanmu dan suara akan tercatat secara permanen</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="text-white text-center py-4" style="background: rgba(0,0,0,0.1);">
        <div class="container">
            <p class="mb-0 small opacity-75">&copy; <?php echo date('Y'); ?> E-Voting OSIS. All rights reserved. | <a href="login.php" class="text-white">Login</a></p>
        </div>
    </footer>

    <?php if (!$voting_open && $now < $start): ?>
    <script>
        var countDownDate = new Date("<?php echo date('Y-m-d H:i:s', $start); ?>").getTime();
        var x = setInterval(function() {
            var now = new Date().getTime();
            var distance = countDownDate - now;
            document.getElementById("days").innerHTML = String(Math.floor(distance / (1000 * 60 * 60 * 24))).padStart(2, '0');
            document.getElementById("hours").innerHTML = String(Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))).padStart(2, '0');
            document.getElementById("minutes").innerHTML = String(Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60))).padStart(2, '0');
            document.getElementById("seconds").innerHTML = String(Math.floor((distance % (1000 * 60)) / 1000)).padStart(2, '0');
            if (distance < 0) { clearInterval(x); location.reload(); }
        }, 1000);
    </script>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>