<?php
session_start();
include "koneksi.php";
if (!isset($_SESSION['id_users']) || !in_array($_SESSION['role'] ?? '', ['Jurnalis','Admin'], true)) {
    header("Location: login.php?error=".urlencode('Akses ditolak: hanya Jurnalis/Admin'));
    exit;
}
$id = $_GET['id'] ?? $_GET['id_article'] ?? null;
if(!is_numeric($id)){ header("Location: dashboard_jurnalis.php?error=ID tidak valid"); exit; }
$id = (int)$id;
$id_users = $_SESSION['id_users'];
$is_admin = ($_SESSION['role'] === 'Admin');

// Admin bisa edit semua artikel, Jurnalis hanya miliknya — server-side check
if($is_admin){
    $stmt = $conn->prepare("SELECT * FROM articles WHERE id_article = ?");
    $stmt->bind_param("i", $id);
} else {
    $stmt = $conn->prepare("SELECT * FROM articles WHERE id_article = ? AND id_users = ?");
    $stmt->bind_param("ii", $id, $id_users);
}
$stmt->execute();
$artikel = $stmt->get_result()->fetch_assoc();
if(!$artikel){
    header("Location: ".($is_admin ? "dashboard_admin.php" : "dashboard_jurnalis.php")."?error=".urlencode('Artikel tidak ditemukan atau bukan milik Anda'));
    exit;
}
$kategori_list = $conn->query("SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Artikel — NEWS</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="topbar">
    <div class="container">
        <div class="topbar__left"><span>Mode <?= htmlspecialchars($_SESSION['role']) ?> — Edit</span></div>
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
                <a href="tambah_artikel.php">Tulis Artikel</a>
            </nav>
            <div class="header__actions">
                <a href="dashboard_jurnalis.php" class="btn btn--ghost btn--sm">Kembali</a>
                <button class="hamburger" id="hamburger">≡</button>
            </div>
        </div>
    </div>
</header>

<div class="container">
    <div class="page-head">
        <p style="font-size:12px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--accent);margin-bottom:6px">EDIT ARTIKEL #<?= $artikel['id_article'] ?></p>
        <h1>Edit artikel</h1>
        <p>Perbarui judul, kategori, atau isi. Perubahan status akan mengatur visibilitas di beranda.</p>
    </div>

    <div class="form-card">
        <?php if(isset($_GET['error'])): ?><div class="alert alert--error"><?= htmlspecialchars($_GET['error']) ?></div><?php endif; ?>
        <form action="proses_edit_artikel.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id_article" value="<?= $artikel['id_article'] ?>">
            <div class="form-group">
                <label for="title">Judul artikel <span style="color:var(--accent)">*</span></label>
                <input class="form-control" type="text" id="title" name="title" value="<?= htmlspecialchars($artikel['title']) ?>" maxlength="255" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="id_kategori">Kategori <span style="color:var(--accent)">*</span></label>
                    <select class="form-control" id="id_kategori" name="id_kategori" required>
                        <?php foreach($kategori_list as $k): ?>
                            <option value="<?= $k['id_kategori'] ?>" <?= (int)$k['id_kategori']===(int)$artikel['id_kategori']?'selected':'' ?>><?= htmlspecialchars($k['nama_kategori']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="status">Status <span style="color:var(--accent)">*</span></label>
                    <select class="form-control" id="status" name="status" required>
                        <option value="draft" <?= $artikel['status']==='draft'?'selected':'' ?>>Draft</option>
                        <option value="published" <?= $artikel['status']==='published'?'selected':'' ?>>Published</option>
                        <option value="review" <?= $artikel['status']==='review'?'selected':'' ?>>Review</option>
                        <option value="archived" <?= $artikel['status']==='archived'?'selected':'' ?>>Archived</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="isi_berita">Isi artikel <span style="color:var(--accent)">*</span></label>
                <textarea class="form-control textarea" id="isi_berita" name="isi_berita" rows="12" required><?= htmlspecialchars($artikel['isi_berita']) ?></textarea>
            </div>
            <div class="form-actions">
                <a href="dashboard_jurnalis.php" class="btn btn--ghost">Batal</a>
                <button type="submit" class="btn btn--primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
<footer class="footer" style="margin-top:30px">
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
