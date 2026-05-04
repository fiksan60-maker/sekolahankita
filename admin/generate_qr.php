<?php
require_once '../includes/session_check.php';
checkRole('admin');
require_once '../config/koneksi.php';

$message = '';
$generated_qr = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['generate_all'])) {
        $users = $pdo->query("SELECT id, nim, nama, kelas FROM users WHERE role='user' AND qr_token IS NULL")->fetchAll();
        
        $stmt = $pdo->prepare("UPDATE users SET qr_token = :token WHERE id = :id");
        
        foreach ($users as $u) {
            $token = bin2hex(random_bytes(16)); // 32 karakter
            $stmt->execute(['token' => $token, 'id' => $u['id']]);
            $generated_qr[] = [
                'nim' => $u['nim'],
                'nama' => $u['nama'],
                'kelas' => $u['kelas'],
                'token' => $token,
                'url' => 'http://' . $_SERVER['HTTP_HOST'] . '/evoting-osis/qr_login.php?token=' . $token
            ];
        }
        
        $message = '<div class="alert alert-success py-2 small">QR Token berhasil digenerate untuk ' . count($users) . ' siswa!</div>';
    }
}

if (isset($_GET['show'])) {
    $user = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $user->execute(['id' => $_GET['show']]);
    $showUser = $user->fetch();
    
    if ($showUser && $showUser['qr_token']) {
        $qr_url = 'http://' . $_SERVER['HTTP_HOST'] . '/evoting-osis/qr_login.php?token=' . $showUser['qr_token'];
    }
}

$users = $pdo->query("SELECT * FROM users WHERE role='user' ORDER BY kelas, nama")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate QR Code - E-Voting OSIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .main-card { background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); border-radius: 20px; }
        .btn-gradient { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; color: white; font-weight: 600; border-radius: 10px; transition: all 0.3s; }
        .btn-gradient:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(102,126,234,0.4); color: white; }
        .sidebar-link { color: #4b5563; border-radius: 8px; padding: 8px 14px; transition: all 0.2s; font-weight: 500; font-size: 13px; display: flex; align-items: center; gap: 8px; text-decoration: none; }
        .sidebar-link:hover, .sidebar-link.active { background: linear-gradient(135deg, #667eea, #764ba2); color: white; font-weight: 600; }
        @media print { .no-print { display: none !important; } body { background: white; } }
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
                    <span class="fw-bold text-dark">Generate QR Code</span>
                </div>
                <a href="../logout.php" class="btn btn-outline-danger btn-sm rounded-3 px-3">Keluar</a>
            </div>

            <div class="row">
                <!-- Sidebar -->
                <div class="col-lg-2 d-none d-lg-block no-print">
                    <div class="card border-0 shadow-sm p-2" style="border-radius: 14px; position: sticky; top: 15px;">
                        <div class="d-flex flex-column gap-1">
                            <a href="dashboard.php" class="sidebar-link">&#x1F3E0; Dashboard</a>
                            <a href="generate_qr.php" class="sidebar-link active">&#x1F4F7; QR Code</a>
                            <a href="generate_password.php" class="sidebar-link">&#x1F511; Password</a>
                        </div>
                    </div>
                </div>

                <!-- Main -->
                <div class="col-lg-10">
                    <?php echo $message; ?>
                    
                    <?php if (isset($showUser) && $showUser['qr_token']): ?>
                    <!-- QR Code Single -->
                    <div class="card border-0 shadow-sm p-4 mb-4 text-center" style="border-radius: 16px;">
                        <h5 class="fw-bold mb-2">QR Code - <?php echo htmlspecialchars($showUser['nama']); ?></h5>
                        <p class="small text-muted">NIM: <?php echo $showUser['nim']; ?> | Kelas: <?php echo $showUser['kelas']; ?></p>
                        <div id="singleQR" class="d-inline-block mb-3"></div>
                        <p class="small text-muted mb-2">Scan QR ini untuk langsung login</p>
                        <button onclick="window.print()" class="btn btn-gradient px-4 no-print">Cetak QR</button>
                    </div>
                    <script>
                        new QRCode(document.getElementById("singleQR"), {
                            text: "<?php echo $qr_url; ?>",
                            width: 200,
                            height: 200
                        });
                    </script>
                    <?php endif; ?>

                    <!-- QR Codes All -->
                    <?php if (!empty($generated_qr)): ?>
                    <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 16px;">
                        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
                            <h5 class="fw-bold mb-0">QR Code yang Baru Digenerate</h5>
                            <button onclick="window.print()" class="btn btn-gradient btn-sm px-4">Cetak Semua</button>
                        </div>
                        <div class="row g-4">
                            <?php foreach ($generated_qr as $i => $qr): ?>
                            <div class="col-md-4 col-lg-3 text-center">
                                <div class="card p-3" style="border-radius: 14px;">
                                    <div id="qr_<?php echo $i; ?>" class="d-inline-block mb-2"></div>
                                    <p class="fw-bold small mb-0"><?php echo htmlspecialchars($qr['nama']); ?></p>
                                    <p class="text-muted small mb-0"><?php echo $qr['nim']; ?> | <?php echo $qr['kelas']; ?></p>
                                </div>
                                <script>
                                    new QRCode(document.getElementById("qr_<?php echo $i; ?>"), {
                                        text: "<?php echo $qr['url']; ?>",
                                        width: 120,
                                        height: 120
                                    });
                                </script>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <p class="text-danger small mt-3 no-print">QR Code hanya ditampilkan sekali. Segera cetak!</p>
                    </div>
                    <?php endif; ?>

                    <!-- Generate Button & Table -->
                    <div class="card border-0 shadow-sm p-4 no-print" style="border-radius: 16px;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0">Daftar Siswa</h5>
                            <form method="POST">
                                <button type="submit" name="generate_all" class="btn btn-gradient px-4">
                                    Generate QR yang Belum Ada
                                </button>
                            </form>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover small mb-0">
                                <thead class="table-light">
                                    <tr><th>NIM</th><th>Nama</th><th>Kelas</th><th>QR Status</th><th>Voting</th><th>Aksi</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $u): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($u['nim']); ?></td>
                                        <td><?php echo htmlspecialchars($u['nama']); ?></td>
                                        <td><?php echo htmlspecialchars($u['kelas']); ?></td>
                                        <td>
                                            <?php if ($u['qr_token']): ?>
                                                <span class="badge bg-success">Ada</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Belum</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($u['status_voting'] === 'sudah'): ?>
                                                <span class="badge bg-info">Sudah</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Belum</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($u['qr_token']): ?>
                                                <a href="?show=<?php echo $u['id']; ?>" class="btn btn-outline-primary btn-sm">Lihat QR</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>