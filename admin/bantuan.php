<?php
require_once '../includes/session_check.php';
checkRole('admin');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bantuan - E-Voting OSIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .main-card { background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); border-radius: 20px; }
        .guide-card { border-radius: 16px; border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: all 0.3s; }
        .guide-card:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .step-num { width: 30px; height: 30px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; margin-right: 10px; }
    </style>
</head>
<body class="p-3">
    <div class="container">
        <div class="main-card shadow-lg p-4 p-md-5">
            
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                <div class="d-flex align-items-center gap-3">
                    <a href="dashboard.php" class="btn btn-light btn-sm rounded-3">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    </a>
                    <span class="fw-bold text-dark">Panduan Penggunaan</span>
                </div>
                <a href="../logout.php" class="btn btn-outline-danger btn-sm rounded-3 px-3">Keluar</a>
            </div>

            <div class="row g-4">
                <!-- Admin Guide -->
                <div class="col-lg-6">
                    <div class="card guide-card p-4 h-100" style="border-left: 4px solid #667eea;">
                        <h5 class="fw-bold mb-3 text-primary">Untuk Admin / Panitia</h5>
                        <div class="space-y-3">
                            <div class="d-flex align-items-start mb-3">
                                <span class="step-num bg-primary text-white">1</span>
                                <div>
                                    <strong>Tambah Kandidat</strong>
                                    <p class="small text-muted mb-0">Menu <a href="data_kandidat.php">Data Kandidat</a> → Klik Tambah → Isi form → Upload foto</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-start mb-3">
                                <span class="step-num bg-primary text-white">2</span>
                                <div>
                                    <strong>Tambah User/Siswa</strong>
                                    <p class="small text-muted mb-0">Menu <a href="data_user.php">Data User</a> → Klik Tambah User → Input NIM, Nama, Kelas</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-start mb-3">
                                <span class="step-num bg-primary text-white">3</span>
                                <div>
                                    <strong>Generate Password</strong>
                                    <p class="small text-muted mb-0">Menu <a href="generate_password.php">Generate Password</a> → Reset Semua → Cetak/Save password</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-start mb-3">
                                <span class="step-num bg-primary text-white">4</span>
                                <div>
                                    <strong>Atur Waktu Voting</strong>
                                    <p class="small text-muted mb-0">Menu <a href="settings.php">Pengaturan</a> → Set waktu mulai & selesai voting</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-start">
                                <span class="step-num bg-primary text-white">5</span>
                                <div>
                                    <strong>Monitoring Hasil</strong>
                                    <p class="small text-muted mb-0">Menu <a href="hasil_voting.php">Hasil Voting</a> → Lihat realtime & cetak</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Guide -->
                <div class="col-lg-6">
                    <div class="card guide-card p-4 h-100" style="border-left: 4px solid #10b981;">
                        <h5 class="fw-bold mb-3 text-success">Untuk Siswa / Pemilih</h5>
                        <div class="space-y-3">
                            <div class="d-flex align-items-start mb-3">
                                <span class="step-num bg-success text-white">1</span>
                                <div>
                                    <strong>Dapatkan Password</strong>
                                    <p class="small text-muted mb-0">Minta password ke panitia/admin. Password unik untuk setiap siswa.</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-start mb-3">
                                <span class="step-num bg-success text-white">2</span>
                                <div>
                                    <strong>Login</strong>
                                    <p class="small text-muted mb-0">Masuk ke <a href="../login.php">halaman login</a> → Input NIM & Password</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-start mb-3">
                                <span class="step-num bg-success text-white">3</span>
                                <div>
                                    <strong>Pilih Kandidat</strong>
                                    <p class="small text-muted mb-0">Klik card kandidat yang diinginkan → Card berubah border ungu</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-start mb-3">
                                <span class="step-num bg-success text-white">4</span>
                                <div>
                                    <strong>Submit Vote</strong>
                                    <p class="small text-muted mb-0">Klik Submit Vote → Konfirmasi → Suara tercatat</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-start">
                                <span class="step-num bg-success text-white">5</span>
                                <div>
                                    <strong>Lihat Hasil</strong>
                                    <p class="small text-muted mb-0">Setelah voting, bisa lihat hasil sementara</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Important Notes -->
                <div class="col-12">
                    <div class="card p-4" style="background: #fef3c7; border-radius: 16px; border: none;">
                        <h6 class="fw-bold text-warning">&#x26A0; Hal Penting yang Perlu Diperhatikan</h6>
                        <ul class="small mb-0">
                            <li>Setiap siswa hanya bisa <strong>voting 1 kali</strong>. Setelah vote, tidak bisa login lagi.</li>
                            <li>Password hanya ditampilkan <strong>SEKALI</strong> saat admin meng-generate. Segera catat!</li>
                            <li>Hasil voting bersifat <strong>realtime</strong> dan bisa dilihat semua pihak.</li>
                            <li>Jika siswa lupa password, admin harus mereset password melalui menu Generate Password.</li>
                            <li>Voting hanya bisa dilakukan dalam rentang waktu yang sudah ditentukan di Pengaturan.</li>
                            <li>Gunakan tombol <strong>Cetak</strong> untuk menyimpan hasil voting sebagai bukti.</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>