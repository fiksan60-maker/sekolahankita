<?php
require_once '../includes/session_check.php';
checkRole('admin');
require_once '../config/koneksi.php';

$results = $pdo->query("
    SELECT k.*, COUNT(v.id) as total_suara
    FROM kandidat k
    LEFT JOIN votes v ON k.id = v.kandidat_id
    GROUP BY k.id
    ORDER BY k.no_urut
")->fetchAll();

$total_votes = array_sum(array_column($results, 'total_suara'));
$total_users = $pdo->query("SELECT COUNT(*) as t FROM users WHERE role='user'")->fetch()['t'];
$voted_users = $pdo->query("SELECT COUNT(*) as t FROM users WHERE role='user' AND status_voting='sudah'")->fetch()['t'];

$winner = null;
if ($total_votes > 0) {
    $winner = $results[0];
    foreach ($results as $r) {
        if ($r['total_suara'] > $winner['total_suara']) $winner = $r;
    }
}

$colors = ['#667eea', '#764ba2', '#10b981', '#f59e0b', '#ef4444', '#06b6d4'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Voting - E-Voting OSIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .main-card { background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); border-radius: 20px; }
        .btn-gradient { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; color: white; font-weight: 600; border-radius: 10px; transition: all 0.3s; }
        .btn-gradient:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(102,126,234,0.4); color: white; }
        .sidebar-link { color: #4b5563; border-radius: 10px; padding: 10px 16px; transition: all 0.2s; font-weight: 500; font-size: 14px; display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .sidebar-link:hover, .sidebar-link.active { background: linear-gradient(135deg, #667eea, #764ba2); color: white; font-weight: 600; }
        .winner-card { background: linear-gradient(135deg, #fbbf24, #f59e0b); border-radius: 20px; }
        .stat-card { border-radius: 16px; border: none; transition: all 0.3s; }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .progress-bar-custom { height: 12px; border-radius: 6px; }
        @media print { .no-print { display: none !important; } body { background: white; } .main-card { box-shadow: none; backdrop-filter: none; } }
    </style>
</head>
<body class="p-3">
    <div class="container-fluid">
        <div class="main-card shadow-lg p-4">
            <!-- Top Bar -->
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom no-print">
                <div class="d-flex align-items-center gap-3">
                    <a href="dashboard.php" class="btn btn-light btn-sm rounded-3">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    </a>
                    <span class="fw-bold text-dark">Hasil Voting</span>
                    <span class="badge bg-light text-dark">Admin</span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <button onclick="window.print()" class="btn btn-light btn-sm rounded-3">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Cetak
                    </button>
                    <a href="../logout.php" class="btn btn-outline-danger btn-sm rounded-3 px-3">Keluar</a>
                </div>
            </div>

            <div class="row">
                <!-- Sidebar -->
                <div class="col-lg-2 d-none d-lg-block no-print">
                    <div class="d-flex flex-column gap-1">
                        <a href="dashboard.php" class="sidebar-link">&#x1F3E0; Dashboard</a>
                        <a href="data_kandidat.php" class="sidebar-link">&#x1F465; Data Kandidat</a>
                        <a href="data_user.php" class="sidebar-link">&#x1F393; Data User</a>
                        <a href="generate_password.php" class="sidebar-link">&#x1F511; Generate Password</a>
                        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
                        <a href="hasil_voting.php" class="sidebar-link active">&#x1F4CA; Hasil Voting</a>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="col-lg-10">
                    <!-- Stats Row -->
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-3">
                            <div class="card stat-card bg-white p-3 text-center">
                                <small class="text-muted">Total Pemilih</small>
                                <h3 class="fw-bold text-dark mt-1"><?php echo $total_users; ?></h3>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card stat-card bg-white p-3 text-center">
                                <small class="text-muted">Sudah Memilih</small>
                                <h3 class="fw-bold text-success mt-1"><?php echo $voted_users; ?></h3>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card stat-card bg-white p-3 text-center">
                                <small class="text-muted">Partisipasi</small>
                                <h3 class="fw-bold text-primary mt-1"><?php echo $total_users > 0 ? round(($voted_users/$total_users)*100) : 0; ?>%</h3>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card stat-card bg-white p-3 text-center">
                                <small class="text-muted">Total Suara</small>
                                <h3 class="fw-bold text-info mt-1"><?php echo $total_votes; ?></h3>
                            </div>
                        </div>
                    </div>

                    <!-- Winner Card -->
                    <?php if ($winner): ?>
                    <div class="winner-card p-4 mb-4 text-center text-dark">
                        <div class="d-flex align-items-center justify-content-center gap-3">
                            <span style="font-size: 40px;">&#127942;</span>
                            <div>
                                <p class="small fw-semibold mb-0 text-dark text-opacity-75">Kandidat Terbanyak</p>
                                <h4 class="fw-bold mb-0"><?php echo htmlspecialchars($winner['nama']); ?></h4>
                                <p class="small mb-0">No. Urut <?php echo $winner['no_urut']; ?> | <?php echo $winner['total_suara']; ?> Suara</p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Chart & Table Row -->
                    <div class="row g-4">
                        <!-- Chart -->
                        <div class="col-lg-5">
                            <div class="card stat-card bg-white p-4">
                                <h6 class="fw-bold mb-3">Grafik Perolehan</h6>
                                <div style="height: 300px;">
                                    <canvas id="resultChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="col-lg-7">
                            <div class="card stat-card bg-white p-4">
                                <h6 class="fw-bold mb-3">Detail Perolehan Suara</h6>
                                <?php foreach ($results as $r): 
                                    $pct = $total_votes > 0 ? ($r['total_suara'] / $total_votes) * 100 : 0;
                                    $barColor = $colors[($r['no_urut']-1) % count($colors)];
                                ?>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div>
                                            <span class="fw-semibold small"><?php echo htmlspecialchars($r['nama']); ?></span>
                                            <span class="badge bg-light text-dark ms-2">No. <?php echo $r['no_urut']; ?></span>
                                            <?php if ($winner && $r['total_suara'] == $winner['total_suara'] && $total_votes > 0): ?>
                                                <span class="badge bg-warning text-dark ms-1">Leading</span>
                                            <?php endif; ?>
                                        </div>
                                        <span class="fw-bold small"><?php echo $r['total_suara']; ?> (<?php echo number_format($pct, 1); ?>%)</span>
                                    </div>
                                    <div class="progress progress-bar-custom bg-light">
                                        <div class="progress-bar" style="width: <?php echo $pct; ?>%; background: <?php echo $barColor; ?>;"></div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                
                                <div class="border-top pt-3 mt-3">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-bold small">Total Suara</span>
                                        <span class="fw-bold text-primary"><?php echo $total_votes; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Bottom Nav -->
    <nav class="d-lg-none fixed-bottom bg-white border-top shadow-lg no-print" style="border-radius: 20px 20px 0 0;">
        <div class="d-flex justify-content-around py-2">
            <a href="dashboard.php" class="text-decoration-none text-center small text-muted">&#x1F3E0;<br>Home</a>
            <a href="data_kandidat.php" class="text-decoration-none text-center small text-muted">&#x1F465;<br>Kandidat</a>
            <a href="generate_password.php" class="text-decoration-none text-center small text-muted">&#x1F511;<br>Password</a>
            <a href="hasil_voting.php" class="text-decoration-none text-center small fw-bold" style="color: #667eea;">&#x1F4CA;<br>Hasil</a>
        </div>
    </nav>

    <script>
        const ctx = document.getElementById('resultChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($results, 'nama')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($results, 'total_suara')); ?>,
                    backgroundColor: <?php echo json_encode(array_slice($colors, 0, count($results))); ?>,
                    borderWidth: 3,
                    borderColor: '#fff',
                    hoverBorderWidth: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            pointStyleWidth: 12,
                            font: { size: 12, family: 'Inter' }
                        }
                    }
                }
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>