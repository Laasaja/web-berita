<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['id_users']) || !in_array($_SESSION['role'] ?? '', ['Jurnalis','Admin'], true)) {
    header("Location: login.php?error=".urlencode('Akses ditolak: hanya Jurnalis/Admin'));
    exit;
}

$kategori_list = $conn->query("SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori")->fetch_all(MYSQLI_ASSOC);
$is_logged = true;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tulis Artikel — NEWS</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="topbar">
    <div class="container">
        <div class="topbar__left"><span>Mode <?= htmlspecialchars($_SESSION['role']) ?></span></div>
        <div class="topbar__right"><span><?= htmlspecialchars($_SESSION['nama_users']) ?></span><?php if($_SESSION['role']==='Admin'): ?><a href="dashboard_admin.php">Admin</a><?php else: ?><a href="dashboard_jurnalis.php">Dashboard</a><?php endif; ?><a href="logout.php">Keluar</a></div>
    </div>
</div>

<header class="header">
    <div class="container">
        <div class="header__inner">
            <a href="dashboard_user.php" class="brand"><span class="brand__mark">N</span><span class="brand__text">NEWS</span></a>
            <nav class="nav" id="mainNav">
                <a href="dashboard_user.php">Home</a>
                <a href="dashboard_jurnalis.php">Dashboard</a>
                <a href="tambah_artikel.php" class="active">Tulis Artikel</a>
            </nav>
            <div class="header__actions">
                <a href="dashboard_user.php" class="btn btn--ghost btn--sm">Lihat Berita</a>
                <button class="hamburger" id="hamburger">≡</button>
            </div>
        </div>
    </div>
</header>

<div class="container">
    <div class="page-head">
        <p style="font-size:12px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--accent);margin-bottom:6px">JURNALIS · TULIS ARTIKEL</p>
        <h1>Tulis artikel baru</h1>
        <p>Judul yang kuat, kategori yang tepat, dan isi yang jelas. Tulisan Anda akan tampil seperti portal profesional setelah dipublish.</p>
    </div>

    <div class="form-card">
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert--error"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert--success"><?= htmlspecialchars($_GET['success']) ?></div>
        <?php endif; ?>

        <!-- PENTING: name, action, method, enctype dipertahankan agar backend tetap berjalan -->
        <form action="proses_tambah_artikel.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Judul artikel <span style="color:var(--accent)">*</span></label>
                <input class="form-control" type="text" id="title" name="title" maxlength="255" placeholder="Contoh: 1.200 Ton Sampah Bandung Diolah Jadi Sumber Energi" required>
                <div class="help">Maks 255 karakter. Buat judul yang jelas dan menarik.</div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="id_kategori">Kategori <span style="color:var(--accent)">*</span></label>
                    <select class="form-control" id="id_kategori" name="id_kategori" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php foreach ($kategori_list as $k): ?>
                            <option value="<?= $k['id_kategori'] ?>"><?= htmlspecialchars($k['nama_kategori']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="status">Status <span style="color:var(--accent)">*</span></label>
                    <select class="form-control" id="status" name="status" required>
                        <option value="draft">Simpan sebagai Draft</option>
                        <option value="published">Publish Sekarang</option>
                    </select>
                    <div class="help">Draft tidak tampil di beranda pembaca.</div>
                </div>
            </div>

            <div class="form-group">
                <label for="isi_berita">Isi artikel <span style="color:var(--accent)">*</span></label>
                <textarea class="form-control textarea" id="isi_berita" name="isi_berita" rows="12" placeholder="Tulis isi berita dengan paragraf yang rapi. Gunakan bahasa yang jernih dan faktual..." required></textarea>
                <div class="help">Tip: pisahkan paragraf dengan baris kosong agar mudah dibaca.</div>
            </div>

            <div class="form-actions">
                <a href="dashboard_jurnalis.php" class="btn btn--ghost">Batal</a>
                <button type="submit" class="btn btn--primary">Simpan Artikel</button>
            </div>
        </form>
    </div>

    <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;margin-bottom:30px" class="muted small">
        <span>Butuh bantuan?</span>
        <a href="dashboard_jurnalis.php" style="color:var(--accent);font-weight:600">Kembali ke Dashboard</a>
        <span>·</span>
        <a href="dashboard_user.php" style="color:var(--muted)">Pratinjau sebagai pembaca</a>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <div class="footer__bottom">
            <span>&copy; <?= date('Y') ?> NEWS</span>
            <a href="dashboard_jurnalis.php" style="color:#9CA3AF">← Dashboard</a>
        </div>
    </div>
</footer>

<script>document.getElementById('hamburger')?.addEventListener('click',()=>document.getElementById('mainNav')?.classList.toggle('open'))</script>
</body>
</html>
