<?php
require_once '../includes/session_check.php';
checkRole('admin');
require_once '../config/koneksi.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_user') {
    $nim = trim($_POST['nim'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $kelas = trim($_POST['kelas'] ?? '');
    
    if (empty($nim) || empty($nama) || empty($kelas)) {
        $message = '<div class="alert alert-danger py-2 small">Semua field harus diisi!</div>';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO users (nim, nama, kelas, password, role, status_voting) VALUES (:nim, :nama, :kelas, '', 'user', 'belum')");
            $stmt->execute(['nim' => $nim, 'nama' => $nama, 'kelas' => $kelas]);
            $message = '<div class="alert alert-success py-2 small">User berhasil ditambahkan!</div>';
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $message = '<div class="alert alert-danger py-2 small">NIM sudah ada!</div>';
            } else {
                $message = '<div class="alert alert-danger py-2 small">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
        }
    }
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id AND role = 'user'");
    $stmt->execute(['id' => $_GET['delete']]);
    $message = '<div class="alert alert-success py-2 small">User berhasil dihapus!</div>';
}

$filter_kelas = $_GET['kelas'] ?? '';
$filter_status = $_GET['status'] ?? '';

$query = "SELECT * FROM users WHERE role = 'user'";
$params = [];

if ($filter_kelas) { $query .= " AND kelas LIKE :kelas"; $params['kelas'] = "%$filter_kelas%"; }
if ($filter_status) { $query .= " AND status_voting = :status"; $params['status'] = $filter_status; }

$query .= " ORDER BY kelas, nama";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();

$voted = $pdo->query("SELECT COUNT(*) as t FROM users WHERE role='user' AND status_voting='sudah'")->fetch()['t'];
$total = $pdo->query("SELECT COUNT(*) as t FROM users WHERE role='user'")->fetch()['t'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data User - E-Voting OSIS</title>
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
        .table-card { border-radius: 16px; overflow: hidden; }
        .table-card thead { background: #f8f9fa; }
        .badge-voted { background: linear-gradient(135deg, #10b981, #059669); color: white; }
        .badge-not { background: linear-gradient(135deg, #f59e0b, #d97706); color: white; }
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
                    <span class="fw-bold text-dark">Data User</span>
                    <span class="badge bg-light text-dark">Admin</span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <a href="generate_password.php" class="text-success small text-decoration-none fw-semibold">Generate Password</a>
                    <a href="../logout.php" class="btn btn-outline-danger btn-sm rounded-3 px-3">Keluar</a>
                </div>
            </div>

            <div class="row">
                <!-- Sidebar -->
                <div class="col-lg-2 d-none d-lg-block">
                    <div class="d-flex flex-column gap-1">
                        <a href="dashboard.php" class="sidebar-link">&#x1F3E0; Dashboard</a>
                        <a href="data_kandidat.php" class="sidebar-link">&#x1F465; Data Kandidat</a>
                        <a href="data_user.php" class="sidebar-link active">&#x1F393; Data User</a>
                        <a href="generate_password.php" class="sidebar-link">&#x1F511; Generate Password</a>
                        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
                        <a href="hasil_voting.php" class="sidebar-link">&#x1F4CA; Hasil Voting</a>
                    </div>
                </div>

                <!-- Main -->
                <div class="col-lg-10">
                    <?php echo $message; ?>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="fw-bold text-dark mb-1">Data User / Pemilih</h4>
                            <p class="text-muted small mb-0">Total: <strong><?php echo count($users); ?></strong> user | Sudah voting: <strong class="text-success"><?php echo $voted; ?></strong> / <?php echo $total; ?></p>
                        </div>
                        <button class="btn btn-gradient px-4" data-bs-toggle="modal" data-bs-target="#addUserModal">+ Tambah User</button>
                    </div>

                    <!-- Filter -->
                    <form method="GET" class="row g-2 mb-4">
                        <div class="col-md-4">
                            <input type="text" name="kelas" class="form-control rounded-3" placeholder="Filter kelas..." value="<?php echo htmlspecialchars($filter_kelas); ?>">
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select rounded-3">
                                <option value="">Semua Status</option>
                                <option value="belum" <?php echo $filter_status==='belum'?'selected':''; ?>>Belum Voting</option>
                                <option value="sudah" <?php echo $filter_status==='sudah'?'selected':''; ?>>Sudah Voting</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-gradient w-100">Filter</button>
                        </div>
                        <div class="col-md-2">
                            <a href="data_user.php" class="btn btn-light w-100 rounded-3">Reset</a>
                        </div>
                    </form>

                    <!-- Table -->
                    <div class="table-card bg-white">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr class="small">
                                    <th>NIM</th>
                                    <th>Nama</th>
                                    <th>Kelas</th>
                                    <th>Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u): ?>
                                <tr>
                                    <td class="fw-semibold small"><?php echo htmlspecialchars($u['nim']); ?></td>
                                    <td class="small"><?php echo htmlspecialchars($u['nama']); ?></td>
                                    <td><span class="badge bg-light text-dark"><?php echo htmlspecialchars($u['kelas']); ?></span></td>
                                    <td>
                                        <?php if ($u['status_voting']==='sudah'): ?>
                                            <span class="badge badge-voted rounded-pill small">Sudah Voting</span>
                                        <?php else: ?>
                                            <span class="badge badge-not rounded-pill small">Belum Voting</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="?delete=<?php echo $u['id']; ?>" onclick="return confirm('Hapus user ini?')" class="btn btn-outline-danger btn-sm rounded-3">Hapus</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (count($users)==0): ?>
                                <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada data</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Add User -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header border-0">
                    <h5 class="fw-bold">Tambah User Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_user">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">NIM</label>
                            <input type="text" name="nim" class="form-control rounded-3" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control rounded-3" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Kelas</label>
                            <input type="text" name="kelas" class="form-control rounded-3" required>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-gradient">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>