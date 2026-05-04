<?php
require_once '../includes/session_check.php';
checkRole('user');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bantuan - E-Voting OSIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .main-card { background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); border-radius: 20px; }
        .guide-card { border-radius: 16px; border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: all 0.3s; }
        .guide-card:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .step-num { width: 35px; height: 35px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 16px; margin-right: 12px; flex-shrink: 0; }
        .faq-card { border-radius: 14px; border: none; transition: all 0.2s; }
        .faq-card:hover { background: #f8f9fa; }
    </style>
</head>
<body class="p-2 p-md-3">
    <div class="container">
        <div class="main-card shadow-lg p-3 p-md-5">
            
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                <div class="d-flex align-items-center gap-3">
                    <a href="dashboard.php" class="btn btn-light btn-sm rounded-3">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    </a>
                    <h5 class="fw-bold mb-0">Bantuan & Panduan</h5>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted small"><?php echo htmlspecialchars($_SESSION['nama']); ?></span>
                    <a href="../logout.php" class="btn btn-outline-danger btn-sm rounded-3 px-3">Keluar</a>
                </div>
            </div>

            <div class="row g-4">
                
                <!-- Cara Voting -->
                <div class="col-lg-7">
                    <h5 class="fw-bold mb-3 text-dark">Cara Melakukan Voting</h5>
                    
                    <div class="d-flex align-items-start mb-4">
                        <span class="step-num bg-primary text-white">1</span>
                        <div>
                            <strong class="d-block">Dapatkan Password</strong>
                            <p class="small text-muted mb-0">Password diberikan oleh panitia/admin OSIS. Setiap siswa mendapat password unik 6 karakter. Jika belum punya, hubungi panitia.</p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-start mb-4">
                        <span class="step-num bg-success text-white">2</span>
                        <div>
                            <strong class="d-block">Login ke Sistem</strong>
                            <p class="small text-muted mb-0">Masuk ke halaman login, masukkan <strong>NIM</strong> dan <strong>Password</strong> dari panitia. Klik tombol Masuk.</p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-start mb-4">
                        <span class="step-num bg-info text-white">3</span>
                        <div>
                            <strong class="d-block">Pilih Kandidat</strong>
                            <p class="small text-muted mb-0">Klik card/foto kandidat yang Anda pilih. Card akan berubah border ungu menandakan sudah dipilih. Anda hanya bisa memilih <strong>SATU</strong> kandidat.</p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-start mb-4">
                        <span class="step-num bg-warning text-white">4</span>
                        <div>
                            <strong class="d-block">Konfirmasi & Submit</strong>
                            <p class="small text-muted mb-0">Klik tombol <strong>"Submit Vote"</strong>. Akan muncul konfirmasi. Klik OK jika sudah yakin. <span class="text-danger">Vote tidak dapat diubah setelah disubmit!</span></p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-start">
                        <span class="step-num bg-danger text-white">5</span>
                        <div>
                            <strong class="d-block">Lihat Hasil</strong>
                            <p class="small text-muted mb-0">Setelah berhasil voting, Anda otomatis diarahkan ke halaman hasil sementara. Anda juga bisa logout setelah selesai.</p>
                        </div>
                    </div>
                </div>

                <!-- FAQ & Tips -->
                <div class="col-lg-5">
                    <h5 class="fw-bold mb-3 text-dark">Pertanyaan Umum (FAQ)</h5>
                    
                    <div class="card faq-card p-3 mb-2">
                        <p class="fw-semibold small mb-1">Apa itu E-Voting OSIS?</p>
                        <p class="small text-muted mb-0">Sistem pemilihan ketua OSIS secara digital. Anda bisa memilih kandidat langsung dari HP atau laptop tanpa kertas.</p>
                    </div>
                    
                    <div class="card faq-card p-3 mb-2">
                        <p class="fw-semibold small mb-1">Berapa kali bisa voting?</p>
                        <p class="small text-muted mb-0">Setiap siswa hanya bisa voting <strong class="text-danger">1 (satu) kali</strong>. Setelah vote, Anda tidak bisa login lagi.</p>
                    </div>
                    
                    <div class="card faq-card p-3 mb-2">
                        <p class="fw-semibold small mb-1">Lupa password?</p>
                        <p class="small text-muted mb-0">Hubungi panitia/admin untuk mereset password Anda. Password baru akan diberikan oleh admin.</p>
                    </div>
                    
                    <div class="card faq-card p-3 mb-2">
                        <p class="fw-semibold small mb-1">Apakah pilihan saya aman?</p>
                        <p class="small text-muted mb-0">Ya. Sistem tidak mencatat siapa yang memilih kandidat mana. Suara Anda <strong>anonim</strong>.</p>
                    </div>
                    
                    <div class="card faq-card p-3 mb-2">
                        <p class="fw-semibold small mb-1">Bisa ganti pilihan?</p>
                        <p class="small text-muted mb-0"><strong class="text-danger">Tidak.</strong> Setelah submit, vote bersifat permanen dan tidak bisa diubah.</p>
                    </div>
                    
                    <div class="card faq-card p-3 mb-2">
                        <p class="fw-semibold small mb-1">Kapan voting dibuka?</p>
                        <p class="small text-muted mb-0">Sesuai jadwal dari panitia. Jika voting belum dibuka, akan muncul countdown timer di dashboard.</p>
                    </div>
                    
                    <div class="card faq-card p-3">
                        <p class="fw-semibold small mb-1">Masalah teknis?</p>
                        <p class="small text-muted mb-0">Hubungi panitia OSIS atau guru yang bertugas. Jangan share password Anda ke siapapun.</p>
                    </div>
                </div>

                <!-- Tips -->
                <div class="col-12">
                    <div class="card p-4" style="background: #fef3c7; border-radius: 16px; border: none;">
                        <h6 class="fw-bold text-warning">&#x1F4A1; Tips Penting</h6>
                        <ul class="small mb-0">
                            <li>Pastikan koneksi internet stabil sebelum voting.</li>
                            <li>Baca visi & misi kandidat dengan teliti sebelum memilih.</li>
                            <li>Jangan share password ke orang lain.</li>
                            <li>Vote tidak bisa diulang, jadi pastikan pilihan sudah tepat.</li>
                            <li>Jika terjadi error, screenshot dan laporkan ke panitia.</li>
                            <li>Gunakan hak suara Anda dengan bijak!</li>
                        </ul>
                    </div>
                </div>

                <!-- Tombol Kembali -->
                <div class="col-12 text-center mt-2">
                    <a href="dashboard.php" class="btn btn-gradient px-5 py-2 fw-bold" style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; border-radius: 14px;">
                        Kembali ke Halaman Voting
                    </a>
                </div>

            </div>
        </div>

        <p class="text-center text-white text-opacity-75 small mt-3">
            &copy; <?php echo date('Y'); ?> E-Voting OSIS | <a href="../logout.php" class="text-white">Keluar</a>
        </p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>