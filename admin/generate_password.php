<?php
require_once '../includes/session_check.php';
checkRole('admin');
require_once '../config/koneksi.php';

$message = '';
$generated = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['reset_all'])) {
        $users = $pdo->query("SELECT id, nim, nama, kelas FROM users WHERE role='user'")->fetchAll();
        $stmt = $pdo->prepare("UPDATE users SET password=:pass WHERE id=:id");
        foreach ($users as $u) {
            $pass = substr(bin2hex(random_bytes(3)), 0, 6);
            $stmt->execute(['pass' => password_hash($pass, PASSWORD_DEFAULT), 'id' => $u['id']]);
            $generated[] = ['nim' => $u['nim'], 'nama' => $u['nama'], 'kelas' => $u['kelas'], 'password' => $pass];
        }
        $message = '<div class="alert alert-success py-2 small">Password semua siswa berhasil direset!</div>';
    }
    if (isset($_POST['reset_one']) && isset($_POST['user_id'])) {
        $pass = substr(bin2hex(random_bytes(3)), 0, 6);
        $stmt = $pdo->prepare("UPDATE users SET password=:pass WHERE id=:id");
        $stmt->execute(['pass' => password_hash($pass, PASSWORD_DEFAULT), 'id' => $_POST['user_id']]);
        $u = $pdo->query("SELECT nim, nama, kelas FROM users WHERE id=" . (int)$_POST['user_id'])->fetch();
        $generated[] = ['nim' => $u['nim'], 'nama' => $u['nama'], 'kelas' => $u['kelas'], 'password' => $pass];
        $message = '<div class="alert alert-success py-2 small">Password berhasil direset!</div>';
    }
}

$users = $pdo->query("SELECT * FROM users WHERE role='user' ORDER BY kelas, nama")->fetchAll();
$empty = $pdo->query("SELECT COUNT(*) as t FROM users WHERE role='user' AND (password='' OR password IS NULL)")->fetch()['t'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Password - E-Voting OSIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .main-card { background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); border-radius: 20px; }
        .btn-gradient { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; color: white; font-weight: 600; border-radius: 10px; transition: all 0.3s; }
        .btn-gradient:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(102,126,234,0.4); color: white; }
        .sidebar-link { color: #4b5563; border-radius: 10px; padding: 10px 16px; transition: all 0.2s; font-weight: 500; font-size: 14px; display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .sidebar-link:hover, .sidebar-link.active { background: linear-gradient(135deg, #667eea, #764ba2); color: white; font-weight: 600; }
        .password-box { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 20px; }
        @media print { .no-print { display: none; } body { background: white; } .main-card { box-shadow: none; } }
    </style>
</head>
<body class="p-3">
    <div class="container-fluid">
        <div class="main-card shadow-lg p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom no-print">
                <div class="d-flex align-items-center gap-3">
                    <a href="dashboard.php" class="btn btn-light btn-sm rounded-3">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    </a>
                    <span class="fw-bold text-dark">Generate Password</span>
                    <span class="badge bg-light text-dark">Admin</span>
                </div>
                <div class="d-flex align-items-center gap-3 no-print">
                    <?php if (!empty($generated)): ?>
                    <button onclick="window.print()" class="btn btn-light btn-sm rounded-3">Cetak</button>
                    <?php endif; ?>
                    <a href="../logout.php" class="btn btn-outline-danger btn-sm rounded-3 px-3">Keluar</a>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-2 d-none d-lg-block no-print">
                    <div class="d-flex flex-column gap-1">
                        <a href="dashboard.php" class="sidebar-link">&#x1F3E0; Dashboard</a>
                        <a href="data_kandidat.php" class="sidebar-link">&#x1F465; Data Kandidat</a>
                        <a href="data_user.php" class="sidebar-link">&#x1F393; Data User</a>
                        <a href="generate_password.php" class="sidebar-link active">&#x1F511; Generate Password</a>
                        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
                        <a href="hasil_voting.php" class="sidebar-link">&#x1F4CA; Hasil Voting</a>
                    </div>
                </div>

                <div class="col-lg-10">
                    <?php echo $message; ?>

                    <!-- Password Generated -->
                    <?php if (!empty($generated)): ?>
                    <div class="password-box text-white p-4 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
                            <h5 class="fw-bold mb-0">Daftar Password Baru</h5>
                            <button onclick="window.print()" class="btn btn-light btn-sm rounded-3 fw-semibold">Cetak</button>
                        </div>
                        <div class="table-responsive bg-white rounded-3">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr class="small"><th>No</th><th>NIM</th><th>Nama</th><th>Kelas</th><th>Password</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($generated as $i => $g): ?>
                                    <tr>
                                        <td class="small"><?php echo $i+1; ?></td>
                                        <td class="small fw-semibold"><?php echo htmlspecialchars($g['nim']); ?></td>
                                        <td class="small"><?php echo htmlspecialchars($g['nama']); ?></td>
                                        <td class="small"><?php echo htmlspecialchars($g['kelas']); ?></td>
                                        <td><code class="bg-light px-3 py-1 rounded fw-bold text-dark"><?php echo htmlspecialchars($g['password']); ?></code></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <small class="text-white text-opacity-75 mt-2 d-block no-print">Password hanya tampil sekali. Segera simpan/cetak!</small>
                    </div>
                    <?php endif; ?>

                    <!-- Actions -->
                    <div class="row g-3 mb-4 no-print">
                        <div class="col-md-6">
                            <form method="POST">
                                <button name="reset_all" onclick="return confirm('Reset password SEMUA siswa?')" class="btn btn-gradient w-100 py-3 fw-bold">
                                    Reset & Generate Semua Ulang
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Table Users -->
                    <div class="table-responsive bg-white rounded-3 no-print">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr class="small"><th>NIM</th><th>Nama</th><th>Kelas</th><th>Password</th><th>Voting</th><th>Aksi</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u): ?>
                                <tr>
                                    <td class="small fw-semibold"><?php echo htmlspecialchars($u['nim']); ?></td>
                                    <td class="small"><?php echo htmlspecialchars($u['nama']); ?></td>
                                    <td class="small"><?php echo htmlspecialchars($u['kelas']); ?></td>
                                    <td><?php echo empty($u['password']) ? '<span class="badge bg-danger">Belum</span>' : '<span class="badge bg-success">Ada</span>'; ?></td>
                                    <td><?php echo $u['status_voting']==='sudah' ? '<span class="badge bg-info">Sudah</span>' : '<span class="badge bg-warning">Belum</span>'; ?></td>
                                    <td>
                                        <form method="POST">
                                            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                            <button name="reset_one" class="btn btn-outline-primary btn-sm rounded-3">Reset</button>
                                        </form>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>