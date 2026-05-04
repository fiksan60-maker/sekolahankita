<?php
require_once '../includes/session_check.php';
checkRole('admin');
require_once '../config/koneksi.php';

$message = '';

// Get current settings
$stmt = $pdo->query("SELECT * FROM settings WHERE setting_key IN ('voting_start', 'voting_end')");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start = $_POST['voting_start'] ?? date('Y-m-d\TH:i');
    $end = $_POST['voting_end'] ?? date('Y-m-d\TH:i', strtotime('+1 year'));
    
    $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'voting_start'")->execute([$start]);
    $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'voting_end'")->execute([$end]);
    
    $message = '<div class="alert alert-success py-2 small">Pengaturan berhasil disimpan!</div>';
    $settings['voting_start'] = $start;
    $settings['voting_end'] = $end;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Voting - E-Voting OSIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .main-card { background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); border-radius: 20px; }
        .btn-gradient { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; color: white; font-weight: 600; border-radius: 10px; transition: all 0.3s; }
        .btn-gradient:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(102,126,234,0.4); color: white; }
        .sidebar-link { color: #4b5563; border-radius: 8px; padding: 8px 14px; transition: all 0.2s; font-weight: 500; font-size: 13px; display: flex; align-items: center; gap: 8px; text-decoration: none; }
        .sidebar-link:hover { background: linear-gradient(135deg, #667eea, #764ba2); color: white; }
        .sidebar-link.active { background: linear-gradient(135deg, #667eea, #764ba2); color: white; font-weight: 600; }
    </style>
</head>
<body class="p-3">
    <div class="container-fluid">
        <div class="main-card shadow-lg p-4">
            <!-- Top Bar -->
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                <div class="d-flex align-items-center gap-3">
                    <a href="dashboard.php" class="btn btn-light btn-sm rounded-3">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    </a>
                    <span class="fw-bold text-dark">Pengaturan Voting</span>
                    <span class="badge bg-light text-dark">Admin</span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <a href="dashboard.php" class="text-muted small text-decoration-none">Dashboard</a>
                    <a href="../logout.php" class="btn btn-outline-danger btn-sm rounded-3 px-3">Keluar</a>
                </div>
            </div>

            <div class="row">
                <!-- Sidebar -->
                <div class="col-lg-2 d-none d-lg-block">
                    <div class="card border-0 shadow-sm p-2" style="border-radius: 14px; position: sticky; top: 15px;">
                        <div class="d-flex flex-column gap-1">
                            <a href="dashboard.php" class="sidebar-link">&#x1F3E0; Dashboard</a>
                            <a href="data_kandidat.php" class="sidebar-link">&#x1F465; Kandidat</a>
                            <a href="data_user.php" class="sidebar-link">&#x1F393; User</a>
                            <a href="generate_password.php" class="sidebar-link">&#x1F511; Password</a>
                            <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
                            <a href="hasil_voting.php" class="sidebar-link">&#x1F4CA; Hasil</a>
                            <a href="settings.php" class="sidebar-link active">&#x2699; Pengaturan</a>
                            <a href="bantuan.php" class="sidebar-link">&#x2753; Bantuan</a>
                            <a href="ubah_password.php" class="sidebar-link">&#x1F512; Password</a>
                        </div>
                    </div>
                </div>

                <!-- Main -->
                <div class="col-lg-10">
                    <?php echo $message; ?>
                    
                    <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 16px;">
                        <h5 class="fw-bold mb-4">Atur Waktu Voting</h5>
                        <form method="POST">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Waktu Mulai Voting</label>
                                    <input type="datetime-local" name="voting_start" class="form-control rounded-3" 
                                           value="<?php echo date('Y-m-d\TH:i', strtotime($settings['voting_start'])); ?>" required>
                                    <small class="text-muted">Siswa baru bisa voting setelah waktu ini</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Waktu Selesai Voting</label>
                                    <input type="datetime-local" name="voting_end" class="form-control rounded-3" 
                                           value="<?php echo date('Y-m-d\TH:i', strtotime($settings['voting_end'])); ?>" required>
                                    <small class="text-muted">Voting ditutup setelah waktu ini</small>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-gradient px-4 mt-3">Simpan Pengaturan</button>
                        </form>
                    </div>

                    <!-- Info Status -->
                    <div class="card border-0 shadow-sm p-4" style="border-radius: 16px; background: #f0f9ff;">
                        <h6 class="fw-bold">Status Voting Saat Ini</h6>
                        <?php
                        $now = time();
                        $start = strtotime($settings['voting_start']);
                        $end = strtotime($settings['voting_end']);
                        ?>
                        <div class="d-flex gap-4 mt-3">
                            <div>
                                <small class="text-muted">Mulai:</small><br>
                                <strong><?php echo date('d M Y, H:i', $start); ?></strong>
                            </div>
                            <div>
                                <small class="text-muted">Selesai:</small><br>
                                <strong><?php echo date('d M Y, H:i', $end); ?></strong>
                            </div>
                            <div>
                                <small class="text-muted">Status:</small><br>
                                <?php if ($now < $start): ?>
                                    <span class="badge bg-warning">Belum Dimulai</span>
                                <?php elseif ($now > $end): ?>
                                    <span class="badge bg-danger">Telah Berakhir</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Sedang Berlangsung</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>