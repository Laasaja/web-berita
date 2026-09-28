<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — NEWS</title>
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
            <h2>Berita terkini, langsung dari redaksi NEWS.</h2>
            <p>Masuk untuk mengakses dashboard — Admin kelola seluruh sistem, Jurnalis kelola artikel, Pembaca baca berita tanpa batas.</p>
            <div style="margin-top:16px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);border-radius:10px;padding:12px;font-size:12px;line-height:1.6">
                <div style="font-weight:700;color:#fff;margin-bottom:4px">Akun Demo</div>
                <div>Admin: <code>admin@news.local / admin123</code></div>
                <div style="opacity:.8">Jurnalis existing: cek tabel users, atau minta Admin buatkan.</div>
            </div>
        </div>
        <div style="font-size:12px;color:#9CA3AF;line-height:1.6">
            <div style="height:1px;background:rgba(255,255,255,.12);margin-bottom:14px"></div>
            &copy; 2026 NEWS. Portal berita modern · Minimal & Profesional
        </div>
    </div>

    <div class="auth-panel">
        <div class="auth-card">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
                <span class="brand__mark" style="width:30px;height:30px;font-size:13px">N</span>
                <span style="font-weight:800;letter-spacing:.08em;font-size:13px">NEWS</span>
            </div>
            <h1>Masuk</h1>
            <p class="sub">Senang bertemu lagi.</p>

            <?php if (!empty($_GET['error'])): ?>
                <div class="alert alert--error"><?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>
            <?php if (!empty($_GET['success'])): ?>
                <div class="alert alert--success"><?= htmlspecialchars($_GET['success']) ?></div>
            <?php endif; ?>

            <form action="proses_login.php" method="post" novalidate>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input class="form-control" type="email" id="email" name="email" placeholder="nama@email.com" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input class="form-control" type="password" id="password" name="password" placeholder="Kata sandi Anda" required>
                </div>
                <button type="submit" class="btn btn--primary btn--block" style="margin-top:6px">Masuk</button>
            </form>

            <div class="divider"></div>
            <p class="auth-foot">Belum punya akun? <a href="signup.php">Daftar sebagai Pembaca</a></p>
            <p class="auth-foot" style="margin-top:8px"><a href="index.php" style="color:var(--muted);font-weight:500">← Kembali ke berita (tanpa login)</a></p>
        </div>
    </div>
</div>
</body>
</html>
