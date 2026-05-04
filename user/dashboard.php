<?php
require_once '../includes/session_check.php';
checkRole('user');
require_once '../config/koneksi.php';

// Ambil waktu voting
$stmt = $pdo->query("SELECT * FROM settings WHERE setting_key IN ('voting_start', 'voting_end')");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
$now = time();
$start = strtotime($settings['voting_start'] ?? '2024-01-01');
$end = strtotime($settings['voting_end'] ?? '2026-12-31');
$voting_open = ($now >= $start && $now <= $end);

$stmt = $pdo->prepare("SELECT status_voting FROM users WHERE id = :id");
$stmt->execute(['id' => $_SESSION['user_id']]);
$user = $stmt->fetch();

$kandidat = $pdo->query("SELECT * FROM kandidat ORDER BY no_urut")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting - E-Voting OSIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .main-card { background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); border-radius: 20px; }
        .card-kandidat { border-radius: 16px; border: 2px solid transparent; transition: all 0.3s; cursor: pointer; }
        .card-kandidat:hover { border-color: #667eea; transform: translateY(-5px); box-shadow: 0 15px 35px rgba(102,126,234,0.2); }
        .card-kandidat.selected { border-color: #667eea; background: #f0f0ff; box-shadow: 0 0 30px rgba(102,126,234,0.3); }
        .card-kandidat img { height: 180px; object-fit: cover; }
        .btn-vote { background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none; color: white; font-weight: 700; font-size: 18px; border-radius: 14px; padding: 14px; transition: all 0.3s; letter-spacing: 1px; }
        .btn-vote:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(16,185,129,0.4); color: white; }
        .btn-vote:disabled { opacity: 0.5; cursor: not-allowed; background: #9ca3af; }
        .radio-custom { width: 22px; height: 22px; accent-color: #667eea; cursor: pointer; }
        .success-card { background: linear-gradient(135deg, #10b981, #059669); border-radius: 20px; }
        .countdown-box { background: rgba(102,126,234,0.1); border-radius: 16px; display: inline-block; padding: 15px 20px; min-width: 70px; text-align: center; }
        .timer-number { font-size: 32px; font-weight: 800; }
    </style>
</head>
<body class="p-2 p-md-3">
    <div class="container">
        <div class="main-card shadow-lg p-3 p-md-5">
            
            <?php if ($user['status_voting'] === 'sudah'): ?>
            <!-- SUDAH VOTING -->
            <div class="text-center">
                <div class="success-card text-white p-5 mb-4">
                    <div style="font-size: 60px;" class="mb-3">&#x2705;</div>
                    <h3 class="fw-bold mb-2">Voting Berhasil!</h3>
                    <p class="mb-3">Terima kasih <strong><?php echo htmlspecialchars($_SESSION['nama']); ?></strong>, suara Anda sudah tercatat.</p>
                </div>
                <a href="sukses.php" class="btn btn-light btn-lg rounded-4 px-5 fw-bold shadow-sm">
                    Lihat Hasil Sementara
                </a>
            </div>

            <?php elseif (!$voting_open && $now < $start): ?>
            <!-- VOTING BELUM DIBUKA -->
            <div class="text-center">
                <div class="mb-4">
                    <div style="font-size: 70px;">&#9200;</div>
                    <h3 class="fw-bold text-dark mt-3">Voting Belum Dibuka</h3>
                    <p class="text-muted">Halo <strong><?php echo htmlspecialchars($_SESSION['nama']); ?></strong>, voting akan dibuka pada:</p>
                    <h4 class="fw-bold text-primary"><?php echo date('l, d M Y H:i', $start); ?> WIB</h4>
                </div>
                
                <!-- Countdown Timer -->
                <div class="d-flex justify-content-center gap-3 mb-4 flex-wrap">
                    <div class="countdown-box">
                        <div class="timer-number text-primary" id="days">00</div>
                        <small class="text-muted fw-semibold">Hari</small>
                    </div>
                    <div class="countdown-box">
                        <div class="timer-number text-primary" id="hours">00</div>
                        <small class="text-muted fw-semibold">Jam</small>
                    </div>
                    <div class="countdown-box">
                        <div class="timer-number text-primary" id="minutes">00</div>
                        <small class="text-muted fw-semibold">Menit</small>
                    </div>
                    <div class="countdown-box">
                        <div class="timer-number text-primary" id="seconds">00</div>
                        <small class="text-muted fw-semibold">Detik</small>
                    </div>
                </div>
                
                <p class="text-muted small">Silakan kembali saat voting sudah dibuka.</p>
<a href="bantuan.php" class="btn btn-outline-primary btn-sm rounded-3 px-3">Bantuan</a>
<a href="../logout.php" class="btn btn-outline-danger btn-sm rounded-3 px-3">Keluar</a>            </div>

            <?php elseif (!$voting_open && $now > $end): ?>
            <!-- VOTING SUDAH DITUTUP -->
            <div class="text-center">
                <div style="font-size: 70px;">&#x1F6AB;</div>
                <h3 class="fw-bold text-dark mt-3">Voting Telah Ditutup</h3>
                <p class="text-muted">Periode voting telah berakhir pada <?php echo date('d M Y H:i', $end); ?> WIB</p>
                <a href="sukses.php" class="btn btn-primary rounded-4 px-5 mt-3 fw-bold">Lihat Hasil Akhir</a>
            </div>

            <?php else: ?>
            <!-- VOTING SEDANG BERLANGSUNG -->
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" 
                     style="width: 70px; height: 70px; background: linear-gradient(135deg, #667eea, #764ba2);">
                    <span style="font-size: 30px;">&#x1F5F3;</span>
                </div>
                <h3 class="fw-bold text-dark">Pilih Kandidat Ketua OSIS</h3>
                <p class="text-muted small">Halo, <strong><?php echo htmlspecialchars($_SESSION['nama']); ?></strong>! Pilih satu kandidat terbaikmu.</p>
                
                <!-- Timer Tutup -->
                <div class="mt-3">
                    <span class="badge bg-warning text-dark">Voting ditutup dalam:</span>
                    <div class="d-flex justify-content-center gap-2 mt-2">
                        <span class="fw-bold" id="endHours">00</span> jam
                        <span class="fw-bold" id="endMinutes">00</span> menit
                        <span class="fw-bold" id="endSeconds">00</span> detik
                    </div>
                </div>
            </div>

            <!-- Form Voting -->
            <form method="POST" action="voting.php" id="votingForm" onsubmit="return confirm('Yakin dengan pilihan Anda? Vote tidak dapat diubah!')">
                <div class="row g-3 mb-4">
                    <?php foreach ($kandidat as $k): ?>
                    <div class="col-md-6 col-lg-4">
                        <label class="card card-kandidat h-100" onclick="selectKandidat(<?php echo $k['id']; ?>)">
                            <?php 
                            $fp = '../assets/images/' . $k['foto'];
                            if (!file_exists($fp) || empty($k['foto'])) $fp = '../assets/images/default.jpg';
                            ?>
                            <img src="<?php echo $fp; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($k['nama']); ?>" 
                                 onerror="this.src='https://placehold.co/400x180?text=No+Image'">
                            <div class="card-body text-center">
                                <span class="badge bg-light text-dark mb-2">No. Urut <?php echo $k['no_urut']; ?></span>
                                <h5 class="fw-bold text-dark"><?php echo htmlspecialchars($k['nama']); ?></h5>
                                <p class="small text-muted mb-2"><?php echo htmlspecialchars(substr($k['visi'], 0, 80)); ?>...</p>
                                <div class="form-check d-flex justify-content-center mt-2">
                                    <input type="radio" name="kandidat_id" value="<?php echo $k['id']; ?>" 
                                           id="kandidat_<?php echo $k['id']; ?>" class="radio-custom" required>
                                </div>
                            </div>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn-vote w-100 w-md-50" id="btnVote" disabled>
                        Submit Vote
                    </button>
                    <small class="text-muted d-block mt-2">Pilih salah satu kandidat terlebih dahulu</small>
                </div>
            </form>
            <?php endif; ?>

        </div>

        <p class="text-center text-white text-opacity-75 small mt-3">
            &copy; <?php echo date('Y'); ?> E-Voting OSIS | <a href="../logout.php" class="text-white">Keluar</a>
        </p>
    </div>

    <?php if (!$voting_open && $now < $start): ?>
    <!-- Countdown Script -->
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
    <?php elseif ($voting_open): ?>
    <!-- Timer Tutup -->
    <script>
        var endDate = new Date("<?php echo date('Y-m-d H:i:s', $end); ?>").getTime();
        var y = setInterval(function() {
            var now = new Date().getTime();
            var distance = endDate - now;
            
            document.getElementById("endHours").innerHTML = String(Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))).padStart(2, '0');
            document.getElementById("endMinutes").innerHTML = String(Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60))).padStart(2, '0');
            document.getElementById("endSeconds").innerHTML = String(Math.floor((distance % (1000 * 60)) / 1000)).padStart(2, '0');
            
            if (distance < 0) { clearInterval(y); location.reload(); }
        }, 1000);
    </script>
    <?php endif; ?>

    <script>
        function selectKandidat(id) {
            document.querySelectorAll('.card-kandidat').forEach(c => c.classList.remove('selected'));
            document.getElementById('kandidat_' + id).checked = true;
            document.getElementById('kandidat_' + id).closest('.card-kandidat').classList.add('selected');
            document.getElementById('btnVote').disabled = false;
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>