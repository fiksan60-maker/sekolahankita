<?php
require_once '../includes/session_check.php';
checkRole('admin');
require_once '../config/koneksi.php';

// Stats
$total_users = $pdo->query("SELECT COUNT(*) as t FROM users WHERE role='user'")->fetch()['t'];
$voted = $pdo->query("SELECT COUNT(*) as t FROM users WHERE role='user' AND status_voting='sudah'")->fetch()['t'];
$total_votes = $pdo->query("SELECT COUNT(*) as t FROM votes")->fetch()['t'];
$no_pass = $pdo->query("SELECT COUNT(*) as t FROM users WHERE role='user' AND (password='' OR password IS NULL)")->fetch()['t'];
$belum_vote = $total_users - $voted;

// Chart data
$chart = $pdo->query("SELECT k.nama, COUNT(v.id) as t FROM kandidat k LEFT JOIN votes v ON k.id=v.kandidat_id GROUP BY k.id ORDER BY k.no_urut")->fetchAll();
$labels = array_column($chart, 'nama');
$data = array_column($chart, 't');
$pct = $total_users > 0 ? ($voted/$total_users)*100 : 0;

// Voting time settings
$stmt = $pdo->query("SELECT * FROM settings WHERE setting_key IN ('voting_start', 'voting_end')");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
$now = time();
$start = strtotime($settings['voting_start'] ?? '2024-01-01');
$end = strtotime($settings['voting_end'] ?? '2026-12-31');
$voting_open = ($now >= $start && $now <= $end);

// Recent votes
$recent = $pdo->query("SELECT u.nama, u.kelas, k.nama as kandidat, v.waktu_vote FROM votes v JOIN users u ON v.user_id=u.id JOIN kandidat k ON v.kandidat_id=k.id ORDER BY v.waktu_vote DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - E-Voting OSIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .main-card { background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); border-radius: 20px; }
        .stat-card { border-radius: 14px; border: none; transition: all 0.2s; }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        .sidebar-link { color: #4b5563; border-radius: 8px; padding: 8px 14px; transition: all 0.2s; font-weight: 500; font-size: 13px; display: flex; align-items: center; gap: 8px; text-decoration: none; }
        .sidebar-link:hover, .sidebar-link.active { background: linear-gradient(135deg, #667eea, #764ba2); color: white; font-weight: 600; }
        .quick-link { border-radius: 12px; border: none; transition: all 0.3s; text-decoration: none; display: block; }
        .quick-link:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .status-badge { font-size: 11px; padding: 4px 10px; border-radius: 20px; }
        .icon-stat { font-size: 24px; }
    </style>
</head>
<body class="p-2 p-md-3">
    <div class="container-fluid">
        <div class="main-card shadow-lg p-3 p-md-4">
            
            <!-- Top Bar -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-3 border-bottom gap-2">
                <div class="d-flex align-items-center gap-3">
                    <span class="fw-bold text-dark fs-5">E-Voting OSIS</span>
                    <span class="badge bg-light text-dark">Admin Panel</span>
                    <?php if ($voting_open): ?>
                    <span class="status-badge bg-success text-white">Voting Aktif</span>
                    <?php elseif ($now < $start): ?>
                    <span class="status-badge bg-warning text-dark">Belum Dimulai</span>
                    <?php else: ?>
                    <span class="status-badge bg-danger text-white">Telah Ditutup</span>
                    <?php endif; ?>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted small"><?php echo htmlspecialchars($_SESSION['nama']); ?></span>
                    <a href="../logout.php" class="btn btn-outline-danger btn-sm rounded-3 px-3">Keluar</a>
                </div>
            </div>

            <div class="row">
                <!-- Sidebar -->
                <div class="col-lg-2 d-none d-lg-block">
                    <div class="card border-0 shadow-sm p-2" style="border-radius: 14px; position: sticky; top: 15px;">
                        <div class="d-flex flex-column gap-1">
                            <a href="dashboard.php" class="sidebar-link active">&#x1F3E0; Dashboard</a>
                            <a href="data_kandidat.php" class="sidebar-link">&#x1F465; Data Kandidat</a>
                            <a href="data_user.php" class="sidebar-link">&#x1F393; Data User</a>
                            <a href="generate_password.php" class="sidebar-link">&#x1F511; Password</a>
                            <a href="generate_qr.php" class="sidebar-link">&#x1F4F7; QR Code</a>
                            <a href="hasil_voting.php" class="sidebar-link">&#x1F4CA; Hasil Voting</a>
                            <hr class="my-1">
                            <a href="settings.php" class="sidebar-link">&#x2699; Pengaturan</a>
                            <a href="ubah_password.php" class="sidebar-link">&#x1F512; Ubah Password</a>
                            <a href="../user/bantuan.php" class="sidebar-link">&#x2753; Panduan</a>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="col-lg-10">
                    
                    <!-- Stats Row -->
                    <div class="row g-2 mb-3">
                        <div class="col-6 col-xl-3">
                            <div class="card stat-card bg-white p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <small class="text-muted" style="font-size: 11px;">TOTAL PEMILIH</small>
                                        <h4 class="fw-bold mb-0 mt-1"><?php echo $total_users; ?></h4>
                                    </div>
                                    <span class="icon-stat">&#x1F465;</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-xl-3">
                            <div class="card stat-card bg-white p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <small class="text-muted" style="font-size: 11px;">SUDAH VOTING</small>
                                        <h4 class="fw-bold mb-0 mt-1 text-success"><?php echo $voted; ?></h4>
                                    </div>
                                    <span class="icon-stat">&#x2705;</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-xl-3">
                            <div class="card stat-card bg-white p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <small class="text-muted" style="font-size: 11px;">BELUM VOTING</small>
                                        <h4 class="fw-bold mb-0 mt-1 text-warning"><?php echo $belum_vote; ?></h4>
                                    </div>
                                    <span class="icon-stat">&#x23F3;</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-xl-3">
                            <div class="card stat-card bg-white p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <small class="text-muted" style="font-size: 11px;">BLM PASSWORD</small>
                                        <h4 class="fw-bold mb-0 mt-1 text-danger"><?php echo $no_pass; ?></h4>
                                    </div>
                                    <span class="icon-stat">&#x1F511;</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chart + Info Row -->
                    <div class="row g-3 mb-3">
                        <div class="col-lg-7">
                            <div class="card stat-card bg-white p-3 h-100">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-bold mb-0">Grafik Perolehan Suara</h6>
                                    <small class="text-muted">Total: <?php echo $total_votes; ?> suara</small>
                                </div>
                                <div style="height: 220px;">
                                    <canvas id="voteChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <div class="d-flex flex-column gap-3 h-100">
                                <div class="card stat-card bg-white p-3">
                                    <h6 class="fw-bold mb-2">Progress Partisipasi</h6>
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span><?php echo $voted; ?> / <?php echo $total_users; ?></span>
                                        <span class="fw-bold"><?php echo round($pct); ?>%</span>
                                    </div>
                                    <div class="progress" style="height: 22px; border-radius: 11px;">
                                        <div class="progress-bar fw-bold" style="width: <?php echo $pct; ?>%; background: linear-gradient(135deg, #667eea, #764ba2); font-size: 11px;">
                                            <?php echo round($pct); ?>%
                                        </div>
                                    </div>
                                </div>

                                <div class="card stat-card bg-white p-3">
                                    <h6 class="fw-bold mb-2">Status Voting</h6>
                                    <?php if ($voting_open): ?>
                                        <div class="text-center">
                                            <span class="badge bg-success mb-2">Sedang Berlangsung</span>
                                            <p class="small text-muted mb-1">Ditutup dalam:</p>
                                            <div class="d-flex justify-content-center gap-2">
                                                <span class="fw-bold fs-5" id="endHours">00</span>:<span class="fw-bold fs-5" id="endMinutes">00</span>:<span class="fw-bold fs-5" id="endSeconds">00</span>
                                            </div>
                                        </div>
                                    <?php elseif ($now < $start): ?>
                                        <div class="text-center">
                                            <span class="badge bg-warning text-dark mb-2">Belum Dimulai</span>
                                            <p class="small text-muted mb-1">Dibuka dalam:</p>
                                            <div class="d-flex justify-content-center gap-2">
                                                <span class="fw-bold fs-5" id="startDays">00</span>h <span class="fw-bold fs-5" id="startHours">00</span>j
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center">
                                            <span class="badge bg-danger mb-2">Telah Ditutup</span>
                                            <p class="small text-muted"><?php echo date('d M Y H:i', $end); ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Votes + Quick Links -->
                    <div class="row g-3">
                        <div class="col-lg-7">
                            <div class="card stat-card bg-white p-3">
                                <h6 class="fw-bold mb-2">Suara Terbaru</h6>
                                <?php if (count($recent) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm small mb-0">
                                        <thead class="table-light">
                                            <tr><th>Nama</th><th>Kelas</th><th>Memilih</th><th>Waktu</th></tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent as $r): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($r['nama']); ?></td>
                                                <td><?php echo htmlspecialchars($r['kelas']); ?></td>
                                                <td class="fw-semibold text-primary"><?php echo htmlspecialchars($r['kandidat']); ?></td>
                                                <td><?php echo date('H:i', strtotime($r['waktu_vote'])); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else: ?>
                                <p class="text-muted text-center small py-3">Belum ada suara masuk</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <div class="d-flex flex-column gap-2">
                                <?php if ($no_pass > 0): ?>
                                <a href="generate_password.php" class="quick-link card p-3" style="border-left: 5px solid #f59e0b;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong class="text-warning">Generate Password</strong>
                                            <p class="small text-muted mb-0"><?php echo $no_pass; ?> siswa belum punya password</p>
                                        </div>
                                        <span class="fs-4">&#x1F511;</span>
                                    </div>
                                </a>
                                <?php endif; ?>
                                
                                <a href="generate_qr.php" class="quick-link card p-3" style="border-left: 5px solid #8b5cf6;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong style="color: #8b5cf6;">Generate QR Code</strong>
                                            <p class="small text-muted mb-0">Buat QR untuk login cepat</p>
                                        </div>
                                        <span class="fs-4">&#x1F4F7;</span>
                                    </div>
                                </a>
                                
                                <a href="hasil_voting.php" class="quick-link card p-3" style="border-left: 5px solid #10b981;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong class="text-success">Hasil Voting</strong>
                                            <p class="small text-muted mb-0">Lihat hasil realtime & cetak</p>
                                        </div>
                                        <span class="fs-4">&#x1F4CA;</span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Bottom Nav -->
    <nav class="d-lg-none fixed-bottom bg-white border-top shadow-lg" style="border-radius: 16px 16px 0 0;">
        <div class="d-flex justify-content-around py-1">
            <a href="dashboard.php" class="text-decoration-none text-center fw-bold" style="font-size: 10px; color: #667eea;">&#x1F3E0;<br>Home</a>
            <a href="data_kandidat.php" class="text-decoration-none text-center text-muted" style="font-size: 10px;">&#x1F465;<br>Kandidat</a>
            <a href="generate_password.php" class="text-decoration-none text-center text-muted" style="font-size: 10px;">&#x1F511;<br>Password</a>
            <a href="hasil_voting.php" class="text-decoration-none text-center text-muted" style="font-size: 10px;">&#x1F4CA;<br>Hasil</a>
        </div>
    </nav>

    <?php if ($voting_open): ?>
    <script>
        var endDate = new Date("<?php echo date('Y-m-d H:i:s', $end); ?>").getTime();
        setInterval(function() {
            var now = new Date().getTime();
            var dist = endDate - now;
            document.getElementById("endHours").innerHTML = String(Math.floor((dist % (1000*60*60*24)) / (1000*60*60))).padStart(2,'0');
            document.getElementById("endMinutes").innerHTML = String(Math.floor((dist % (1000*60*60)) / (1000*60))).padStart(2,'0');
            document.getElementById("endSeconds").innerHTML = String(Math.floor((dist % (1000*60)) / 1000)).padStart(2,'0');
        }, 1000);
    </script>
    <?php elseif ($now < $start): ?>
    <script>
        var startDate = new Date("<?php echo date('Y-m-d H:i:s', $start); ?>").getTime();
        setInterval(function() {
            var now = new Date().getTime();
            var dist = startDate - now;
            document.getElementById("startDays").innerHTML = Math.floor(dist / (1000*60*60*24));
            document.getElementById("startHours").innerHTML = String(Math.floor((dist % (1000*60*60*24)) / (1000*60*60))).padStart(2,'0');
        }, 1000);
    </script>
    <?php endif; ?>

    <script>
        new Chart(document.getElementById('voteChart'), {
            type: 'bar',
            data: { labels: <?php echo json_encode($labels); ?>, datasets: [{ data: <?php echo json_encode($data); ?>, backgroundColor: '#667eea', borderRadius: 6 }] },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }, plugins: { legend: { display: false } } }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>