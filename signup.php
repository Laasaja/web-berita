<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEWS — Daftar</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-visual">
        <div>
            <a href="index.php" class="brand" style="color:#fff">
                <span class="brand__mark">N</span>
                <span style="font-size:18px;letter-spacing:.1em;font-weight:800">NEWS</span>
            </a>
            <h2>Bergabung sebagai Pembaca NEWS.</h2>
            <p>Daftar akun Pembaca untuk mengikuti berita terkini. Akun Jurnalis hanya dapat dibuat oleh Admin melalui dashboard — bukan melalui registrasi publik, demi keamanan.</p>
        </div>
        <div style="font-size:12px;color:#9CA3AF;line-height:1.6">
            <div style="height:1px;background:rgba(255,255,255,.12);margin-bottom:14px"></div>
            &copy; 2026 NEWS. Registrasi publik = Pembaca only.
        </div>
    </div>

    <div class="auth-panel">
        <div class="auth-card">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
                <span class="brand__mark" style="width:30px;height:30px;font-size:13px">N</span>
                <span style="font-weight:800;letter-spacing:.08em;font-size:13px">NEWS</span>
            </div>
            <h1>Buat akun</h1>
            <p class="sub">Daftar sebagai Pembaca — gratis, 30 detik.</p>

            <?php if (!empty($_GET['error'])): ?>
                <div class="alert alert--error"><?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>
            <?php if (!empty($_GET['success'])): ?>
                <div class="alert alert--success"><?= htmlspecialchars($_GET['success']) ?></div>
            <?php endif; ?>

            <form action="proses_data.php" method="post" novalidate>
                <div class="form-group">
                    <label for="nama_users">Nama lengkap</label>
                    <input class="form-control" type="text" id="nama_users" name="nama_users" placeholder="Budi Santoso" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input class="form-control" type="email" id="email" name="email" placeholder="nama@email.com" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input class="form-control" type="password" id="password" name="password" placeholder="Minimal 6 karakter" required>
                    <div class="input-hint">Password akan di-hash dengan <code>password_hash()</code> — tidak disimpan plain text.</div>
                </div>
                <!-- Role tidak lagi dipilih user. Server akan set Pembaca otomatis. -->
                <div style="background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:10px 12px;margin-bottom:14px">
                    <div style="font-size:12px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--muted)">Role</div>
                    <div style="font-size:13px;color:var(--text2);margin-top:2px">Pembaca — dapat membaca berita tanpa batas. <span style="color:var(--muted)">Jurnalis/Admin dibuat oleh Admin</span></div>
                </div>
                <button type="submit" class="btn btn--primary btn--block" style="margin-top:6px">Daftar sebagai Pembaca</button>
            </form>

            <div class="divider"></div>
            <p class="auth-foot">Sudah punya akun? <a href="login.php">Masuk</a> · <a href="index.php">← Kembali ke berita</a></p>
            <p class="small muted" style="text-align:center;margin-top:10px">Ingin menjadi Jurnalis? Hubungi Admin: <strong>admin@news.local / admin123</strong></p>
        </div>
    </div>
</div>
</body>
</html>
