<?php
require_once '../includes/session_check.php';
checkRole('admin');
require_once '../config/koneksi.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_pass = $_POST['old_password'] ?? '';
    $new_pass = $_POST['new_password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';
    
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = :id");
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $admin = $stmt->fetch();
    
    if (!password_verify($old_pass, $admin['password'])) {
        $message = '<div class="alert alert-danger py-2 small">Password lama salah!</div>';
    } elseif ($new_pass !== $confirm_pass) {
        $message = '<div class="alert alert-danger py-2 small">Konfirmasi password tidak cocok!</div>';
    } elseif (strlen($new_pass) < 6) {
        $message = '<div class="alert alert-danger py-2 small">Password minimal 6 karakter!</div>';
    } else {
        $hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = :pass WHERE id = :id");
        $stmt->execute(['pass' => $hash, 'id' => $_SESSION['user_id']]);
        $message = '<div class="alert alert-success py-2 small">Password berhasil diubah! Silakan login ulang.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Password - E-Voting OSIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .main-card { background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); border-radius: 20px; }
        .btn-gradient { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; color: white; font-weight: 600; border-radius: 10px; transition: all 0.3s; }
        .btn-gradient:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(102,126,234,0.4); color: white; }
    </style>
</head>
<body class="p-3 d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="main-card shadow-lg p-4">
                    <div class="text-center mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 mb-3" style="width: 60px; height: 60px;">
                            <span style="font-size: 28px;">&#x1F512;</span>
                        </div>
                        <h5 class="fw-bold">Ubah Password Admin</h5>
                        <p class="text-muted small">Ganti password untuk keamanan</p>
                    </div>
                    
                    <?php echo $message; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Password Lama</label>
                            <input type="password" name="old_password" class="form-control rounded-3" required placeholder="Masukkan password saat ini">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Password Baru</label>
                            <input type="password" name="new_password" class="form-control rounded-3" required placeholder="Minimal 6 karakter">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Konfirmasi Password Baru</label>
                            <input type="password" name="confirm_password" class="form-control rounded-3" required placeholder="Ulangi password baru">
                        </div>
                        <button type="submit" class="btn btn-gradient w-100 py-2">Simpan Perubahan</button>
                        <a href="dashboard.php" class="btn btn-light w-100 mt-2 rounded-3">Kembali ke Dashboard</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>