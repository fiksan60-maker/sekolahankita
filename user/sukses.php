<?php
require_once '../includes/session_check.php';
checkRole('user');
require_once '../config/koneksi.php';

$results = $pdo->query("
    SELECT k.nama, k.no_urut, COUNT(v.id) as total_suara
    FROM kandidat k
    LEFT JOIN votes v ON k.id = v.kandidat_id
    GROUP BY k.id
    ORDER BY k.no_urut
")->fetchAll();

$total_votes = array_sum(array_column($results, 'total_suara'));

$winner = null;
if ($total_votes > 0) {
    $winner = $results[0];
    foreach ($results as $r) {
        if ($r['total_suara'] > $winner['total_suara']) $winner = $r;
    }
}

$colors = ['#667eea', '#764ba2', '#10b981', '#f59e0b'];
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
        .success-header { background: linear-gradient(135deg, #10b981, #059669); border-radius: 20px; }
        .winner-card { background: linear-gradient(135deg, #fbbf24, #f59e0b); border-radius: 16px; }
        .progress-bar-custom { height: 14px; border-radius: 7px; }
    </style>
</head>
<body class="p-3 d-flex align-items-center justify-content-center">
    <div class="container">
        <div class="main-card shadow-lg p-4 p-md-5">
            
            <!-- Success Header -->
            <div class="success-header text-white text-center p-4 mb-4">
                <div style="font-size: 50px;">&#127881;</div>
                <h3 class="fw-bold">Voting Berhasil!</h3>
                <p class="mb-0 small">Terima kasih <strong><?php echo htmlspecialchars($_SESSION['nama']); ?></strong>! Suara Anda telah tercatat.</p>
            </div>

            <!-- Winner Card -->
            <?php if ($winner && $total_votes > 0): ?>
            <div class="winner-card p-4 mb-4 text-center text-dark">
                <div class="d-flex align-items-center justify-content-center gap-3">
                    <span style="font-size: 35px;">&#127942;</span>
                    <div>
                        <p class="small fw-semibold mb-0">Leading Sementara</p>
                        <h5 class="fw-bold mb-0"><?php echo htmlspecialchars($winner['nama']); ?></h5>
                        <small>No. Urut <?php echo $winner['no_urut']; ?> | <?php echo $winner['total_suara']; ?> Suara</small>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Chart -->
            <div class="row g-4 mb-4">
                <div class="col-lg-5">
                    <div class="bg-white rounded-4 p-3">
                        <h6 class="fw-bold text-center mb-3">Grafik Hasil</h6>
                        <div style="height: 250px;">
                            <canvas id="suksesChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="bg-white rounded-4 p-3">
                        <h6 class="fw-bold mb-3">Detail Perolehan Suara</h6>
                        <?php foreach ($results as $r): 
                            $pct = $total_votes > 0 ? ($r['total_suara'] / $total_votes) * 100 : 0;
                            $barColor = $colors[($r['no_urut']-1) % count($colors)];
                        ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="fw-semibold">No. <?php echo $r['no_urut']; ?> - <?php echo htmlspecialchars($r['nama']); ?></span>
                                <span><?php echo $r['total_suara']; ?> suara (<?php echo number_format($pct, 1); ?>%)</span>
                            </div>
                            <div class="progress progress-bar-custom bg-light">
                                <div class="progress-bar" style="width: <?php echo $pct; ?>%; background: <?php echo $barColor; ?>;"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <div class="border-top pt-3 text-center">
                            <span class="fw-bold">Total Suara: <span class="text-primary"><?php echo $total_votes; ?></span></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center">
                <p class="text-muted small">Hasil dapat berubah seiring bertambahnya suara.</p>
                <a href="../logout.php" class="btn btn-outline-danger rounded-4 px-4">Keluar</a>
            </div>
        </div>

        <p class="text-center text-white text-opacity-75 small mt-3">
            &copy; <?php echo date('Y'); ?> E-Voting OSIS
        </p>
    </div>

    <script>
        const ctx = document.getElementById('suksesChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($results, 'nama')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($results, 'total_suara')); ?>,
                    backgroundColor: <?php echo json_encode(array_slice($colors, 0, count($results))); ?>,
                    borderWidth: 3,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 15, usePointStyle: true, pointStyleWidth: 10, font: { size: 11 } }
                    }
                }
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>