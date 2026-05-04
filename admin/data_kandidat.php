<?php
require_once '../includes/session_check.php';
checkRole('admin');
require_once '../config/koneksi.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'add') {
            $foto = 'default.jpg';
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
                $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
                $foto = 'kandidat_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['foto']['tmp_name'], '../assets/images/' . $foto);
            }
            $stmt = $pdo->prepare("INSERT INTO kandidat (no_urut, nama, visi, misi, foto) VALUES (:no, :nama, :visi, :misi, :foto)");
            $stmt->execute(['no' => $_POST['no_urut'], 'nama' => $_POST['nama'], 'visi' => $_POST['visi'], 'misi' => $_POST['misi'], 'foto' => $foto]);
            $message = '<div class="alert alert-success py-2 small">Kandidat berhasil ditambahkan!</div>';
        } elseif ($_POST['action'] === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM kandidat WHERE id = :id");
            $stmt->execute(['id' => $_POST['id']]);
            $message = '<div class="alert alert-success py-2 small">Kandidat berhasil dihapus!</div>';
        }
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger py-2 small">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

$kandidat = $pdo->query("SELECT * FROM kandidat ORDER BY no_urut")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kandidat - E-Voting OSIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .main-card { background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); border-radius: 20px; }
        .card-kandidat { border-radius: 16px; border: none; overflow: hidden; transition: all 0.3s; }
        .card-kandidat:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.15); }
        .card-kandidat img { height: 200px; object-fit: cover; }
        .btn-gradient { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; color: white; font-weight: 600; border-radius: 10px; transition: all 0.3s; }
        .btn-gradient:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(102,126,234,0.4); color: white; }
        .sidebar-link { color: #4b5563; border-radius: 10px; padding: 10px 16px; transition: all 0.2s; font-weight: 500; font-size: 14px; display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .sidebar-link:hover, .sidebar-link.active { background: linear-gradient(135deg, #667eea, #764ba2); color: white; font-weight: 600; }
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
                    <span class="fw-bold text-dark">Data Kandidat</span>
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
                    <div class="d-flex flex-column gap-1">
                        <a href="dashboard.php" class="sidebar-link">&#x1F3E0; Dashboard</a>
                        <a href="data_kandidat.php" class="sidebar-link active">&#x1F465; Data Kandidat</a>
                        <a href="data_user.php" class="sidebar-link">&#x1F393; Data User</a>
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
                            <h4 class="fw-bold text-dark mb-1">Data Kandidat</h4>
                            <p class="text-muted small mb-0">Kelola kandidat ketua OSIS</p>
                        </div>
                        <button class="btn btn-gradient px-4" data-bs-toggle="modal" data-bs-target="#addModal">+ Tambah Kandidat</button>
                    </div>

                    <div class="row g-4">
                        <?php foreach ($kandidat as $k): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card card-kandidat bg-white">
                                <?php 
                                $fp = '../assets/images/' . $k['foto'];
                                if (!file_exists($fp) || empty($k['foto'])) $fp = '../assets/images/default.jpg';
                                ?>
                                <img src="<?php echo $fp; ?>" alt="<?php echo htmlspecialchars($k['nama']); ?>" onerror="this.src='https://placehold.co/400x200?text=No+Image'">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="badge bg-light text-dark fs-6">No. <?php echo $k['no_urut']; ?></span>
                                        <form method="POST" onsubmit="return confirm('Hapus kandidat ini?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $k['id']; ?>">
                                            <button class="btn btn-outline-danger btn-sm rounded-3">Hapus</button>
                                        </form>
                                    </div>
                                    <h5 class="fw-bold"><?php echo htmlspecialchars($k['nama']); ?></h5>
                                    <p class="small text-muted mb-1"><strong>Visi:</strong> <?php echo htmlspecialchars(substr($k['visi'],0,80)); ?>...</p>
                                    <p class="small text-muted"><strong>Misi:</strong> <?php echo nl2br(htmlspecialchars(substr($k['misi'],0,80))); ?>...</p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Add -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header border-0">
                    <h5 class="fw-bold">Tambah Kandidat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">No. Urut</label>
                            <input type="number" name="no_urut" class="form-control rounded-3" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Nama</label>
                            <input type="text" name="nama" class="form-control rounded-3" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Visi</label>
                            <textarea name="visi" rows="3" class="form-control rounded-3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Misi</label>
                            <textarea name="misi" rows="4" class="form-control rounded-3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Foto</label>
                            <input type="file" name="foto" class="form-control rounded-3" accept="image/*">
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