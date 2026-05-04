<?php
session_start();
require_once 'config/koneksi.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: user/dashboard.php');
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nim = trim($_POST['nim'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($nim) || empty($password)) {
        $error = 'NIM dan Password harus diisi!';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE nim = :nim");
        $stmt->execute(['nim' => $nim]);
        $user = $stmt->fetch();

        if ($user) {
            if (password_verify($password, $user['password'])) {
                if ($user['role'] === 'user' && $user['status_voting'] === 'sudah') {
                    $error = 'Anda sudah menggunakan hak suara. Tidak dapat login kembali.';
                } else {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['nama'] = $user['nama'];
                    $_SESSION['role'] = $user['role'];
                    
                    if ($user['role'] === 'admin') {
                        header('Location: admin/dashboard.php');
                    } else {
                        header('Location: user/dashboard.php');
                    }
                    exit();
                }
            } else {
                $error = 'Password salah!';
            }
        } else {
            $error = 'NIM tidak ditemukan!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - E-Voting OSIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .login-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
        .input-group-text {
            background: transparent;
            border-right: none;
        }
        .form-control {
            border-left: none;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #667eea;
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center p-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="login-card rounded-4 shadow-lg p-5">
                    <!-- Logo / Header -->
                    <div class="text-center mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 mb-3" style="width: 80px; height: 80px;">
                            <svg class="text-primary" width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h2 class="fw-bold text-dark mb-1">E-Voting OSIS</h2>
                        <p class="text-muted small">Silakan login untuk melanjutkan</p>
                    </div>

                    <!-- Error Alert -->
                    <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show small py-2" role="alert">
                        <svg width="18" height="18" fill="currentColor" class="me-2" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                        <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>

                    <!-- Form -->
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">NIM</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <svg width="18" height="18" fill="none" stroke="#6b7280" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </span>
                                <input type="text" name="nim" class="form-control" placeholder="Masukkan NIM" required autofocus>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-semibold text-secondary">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <svg width="18" height="18" fill="none" stroke="#6b7280" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                </span>
                                <input type="password" name="password" class="form-control" placeholder="Password dari admin" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-login text-white w-100 py-2 rounded-3">
                            Masuk
                        </button>
                    </form>

                    <!-- Info -->
                    <div class="mt-4 pt-4 border-top">
                        <div class="d-flex justify-content-between text-muted small">
                            <span>Admin: <code class="bg-light px-2 py-1 rounded">admin</code></span>
                            <span>Pass: <code class="bg-light px-2 py-1 rounded">admin123</code></span>
                        </div>
                    </div>
                </div>

                <p class="text-center text-white text-opacity-75 small mt-4">
                    &copy; <?php echo date('Y'); ?> E-Voting OSIS
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>