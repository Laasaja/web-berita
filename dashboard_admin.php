<?php
session_start();
include "koneksi.php";
if (!isset($_SESSION['id_users']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    header("Location: login.php?error=".urlencode('Akses ditolak: hanya Admin'));
    exit;
}
$admin_id = $_SESSION['id_users'];

// Stats
$total = $conn->query("SELECT COUNT(*) as c FROM articles")->fetch_assoc()['c'] ?? 0;
$published = $conn->query("SELECT COUNT(*) as c FROM articles WHERE status='published'")->fetch_assoc()['c'] ?? 0;
$draft = $conn->query("SELECT COUNT(*) as c FROM articles WHERE status='draft'")->fetch_assoc()['c'] ?? 0;
$cat_total = $conn->query("SELECT COUNT(*) as c FROM kategori")->fetch_assoc()['c'] ?? 0;
$user_total = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'] ?? 0;
$jurnalis_total = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='Jurnalis'")->fetch_assoc()['c'] ?? 0;
$pembaca_total = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='Pembaca'")->fetch_assoc()['c'] ?? 0;

// Daftar semua artikel (join)
$artikel = $conn->query("
    SELECT a.id_article, a.title, a.status, a.published_at, a.updated_at, k.nama_kategori, u.nama_users, u.email
    FROM articles a
    LEFT JOIN kategori k ON k.id_kategori=a.id_kategori
    LEFT JOIN users u ON u.id_users=a.id_users
    ORDER BY a.updated_at DESC
")->fetch_all(MYSQLI_ASSOC);

// Kategori dengan jumlah artikel
$kategori = $conn->query("SELECT k.*, (SELECT COUNT(*) FROM articles a WHERE a.id_kategori=k.id_kategori) as jml FROM kategori k ORDER BY k.nama_kategori")->fetch_all(MYSQLI_ASSOC);

// Users
$users = $conn->query("SELECT id_users, nama_users, email, role FROM users ORDER BY id_users DESC")->fetch_all(MYSQLI_ASSOC);

$notif_sukses = $_GET['sukses'] ?? $_GET['success'] ?? null;
$notif_error = $_GET['error'] ?? null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin — NEWS</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="admin-layout">
    <aside class="admin-sidebar">
        <a href="dashboard_admin.php" class="admin-brand"><span class="brand__mark">N</span> NEWS <span style="font-size:11px;background:#991B1B;padding:2px 6px;border-radius:999px;margin-left:6px">ADMIN</span></a>
        <div style="background:#1F2937;border-radius:10px;padding:12px;margin-bottom:16px;display:flex;gap:10px;align-items:center">
            <div style="width:36px;height:36px;border-radius:999px;background:var(--accent);display:grid;place-items:center;color:#fff;font-weight:800;font-size:13px"><?= strtoupper(substr($_SESSION['nama_users'],0,1)) ?></div>
            <div style="min-width:0">
                <div style="font-size:13px;font-weight:600;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($_SESSION['nama_users']) ?></div>
                <div style="font-size:11px;color:#9CA3AF">Admin · ID #<?= (int)$admin_id ?></div>
            </div>
        </div>
        <nav class="admin-nav">
            <a href="dashboard_admin.php" class="active">⊞ Dashboard</a>
            <a href="tambah_artikel.php">✎ Tulis Artikel</a>
            <a href="index.php">📰 Portal Publik</a>
            <a href="#kategori">🏷️ Kategori</a>
            <a href="#users">👥 Kelola Jurnalis</a>
        </nav>
        <div class="admin-sep"></div>
        <div style="font-size:11px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:#6B7280;margin-bottom:8px">Ringkasan</div>
        <div style="display:flex;flex-direction:column;gap:6px;font-size:13px">
            <div style="display:flex;justify-content:space-between"><span style="color:#9CA3AF">Artikel</span><strong style="color:#fff"><?= $total ?></strong></div>
            <div style="display:flex;justify-content:space-between"><span style="color:#9CA3AF">Published</span><strong style="color:#86EFAC"><?= $published ?></strong></div>
            <div style="display:flex;justify-content:space-between"><span style="color:#9CA3AF">Draft</span><strong style="color:#FDE68A"><?= $draft ?></strong></div>
            <div style="display:flex;justify-content:space-between"><span style="color:#9CA3AF">Jurnalis</span><strong style="color:#fff"><?= $jurnalis_total ?></strong></div>
            <div style="display:flex;justify-content:space-between"><span style="color:#9CA3AF">Pembaca</span><strong style="color:#fff"><?= $pembaca_total ?></strong></div>
        </div>
        <div class="admin-sep"></div>
        <a href="logout.php" style="display:flex;align-items:center;gap:8px;padding:9px 10px;border-radius:8px;font-size:13px;color:#FCA5A5;border:1px solid #1F2937">↪ Keluar</a>
    </aside>

    <main class="admin-main">
        <div class="admin-top">
            <div>
                <p style="font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--accent);margin-bottom:4px">DASHBOARD · ADMIN</p>
                <h1>Halo, <?= htmlspecialchars(explode(' ', $_SESSION['nama_users'])[0]) ?> — kontrol penuh CMS</h1>
                <p class="small muted">Kelola artikel, kategori, dan akun Jurnalis. Semua aksi dilindungi server-side <code>$_SESSION['role']==='Admin'</code>.</p>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap">
                <a href="index.php" class="btn btn--ghost btn--sm">Lihat Portal</a>
                <a href="tambah_artikel.php" class="btn btn--primary">+ Tulis Artikel</a>
            </div>
        </div>

        <?php if($notif_sukses): ?><div class="alert alert--success"><?= htmlspecialchars($notif_sukses) ?></div><?php endif; ?>
        <?php if($notif_error): ?><div class="alert alert--error"><?= htmlspecialchars($notif_error) ?></div><?php endif; ?>

        <div class="stats">
            <div class="stat"><div class="stat__label">Total Artikel</div><div class="stat__value"><?= $total ?></div><div class="stat__sub">Semua penulis</div></div>
            <div class="stat"><div class="stat__label">Published</div><div class="stat__value" style="color:#166534"><?= $published ?></div><div class="stat__sub">Tampil publik</div></div>
            <div class="stat"><div class="stat__label">Draft</div><div class="stat__value" style="color:#92400E"><?= $draft ?></div><div class="stat__sub">Butuh review</div></div>
            <div class="stat"><div class="stat__label">Kategori</div><div class="stat__value"><?= $cat_total ?></div><div class="stat__sub"><?= $cat_total ?> kategori</div></div>
            <div class="stat"><div class="stat__label">Total Users</div><div class="stat__value"><?= $user_total ?></div><div class="stat__sub">Jurnalis <?= $jurnalis_total ?> · Pembaca <?= $pembaca_total ?></div></div>
            <div class="stat"><div class="stat__label">Akses</div><div class="stat__value" style="font-size:18px">Admin ✓</div><div class="stat__sub">Role-based auth aktif</div></div>
        </div>

        <div class="table-wrap">
            <div class="table-head">
                <h3>Semua Artikel — <?= count($artikel) ?> entri</h3>
                <a href="tambah_artikel.php" class="btn btn--primary btn--xs">+ Tambah</a>
            </div>
            <div style="overflow:auto">
            <table class="table">
                <thead><tr><th>Judul</th><th>Kategori</th><th>Penulis</th><th>Status</th><th>Update</th><th>Aksi</th></tr></thead>
                <tbody>
                <?php if(!$artikel): ?>
                    <tr><td colspan="6"><div class="empty">Belum ada artikel.</div></td></tr>
                <?php else: foreach($artikel as $row): ?>
                    <tr>
                        <td style="font-weight:600;max-width:320px"><div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($row['title']) ?></div><div class="small muted">ID #<?= $row['id_article'] ?></div></td>
                        <td><span class="badge" style="background:#F3F4F6;border-color:#E5E7EB;color:#374151"><?= htmlspecialchars($row['nama_kategori'] ?? '—') ?></span></td>
                        <td class="small"><div style="font-weight:600"><?= htmlspecialchars($row['nama_users'] ?? '—') ?></div><div class="muted" style="font-size:11px"><?= htmlspecialchars($row['email'] ?? '') ?></div></td>
                        <td><?php $st=$row['status']; $cls=$st==='published'?'badge--published':($st==='draft'?'badge--draft':($st==='review'?'badge--review':'badge--archived')); ?><span class="badge <?= $cls ?>"><?= htmlspecialchars($st) ?></span></td>
                        <td class="small muted" style="white-space:nowrap"><?= htmlspecialchars(date('d M Y, H:i', strtotime($row['updated_at'] ?? 'now'))) ?></td>
                        <td><div class="actions"><a href="detail_berita.php?id=<?= $row['id_article'] ?>" class="btn btn--ghost btn--xs">Lihat</a><a href="edit_artikel.php?id=<?= $row['id_article'] ?>" class="btn btn--ghost btn--xs">Edit</a><a href="hapus.php?id=<?= $row['id_article'] ?>" onclick="return confirm('Hapus artikel ini?')" class="btn btn--ghost btn--xs" style="color:var(--accent);border-color:var(--accent-border)">Hapus</a></div></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
            </div>
        </div>

        <div id="kategori" style="margin-top:28px">
            <div class="section-head"><h2>Manajemen Kategori</h2></div>
            <div style="display:grid;grid-template-columns:1fr 340px;gap:18px">
                <div class="table-wrap">
                    <div class="table-head"><h3>Daftar Kategori</h3></div>
                    <div style="overflow:auto">
                    <table class="table">
                        <thead><tr><th>Nama</th><th>Deskripsi</th><th>Artikel</th><th>Aksi</th></tr></thead>
                        <tbody>
                        <?php foreach($kategori as $k): ?>
                            <tr>
                                <td style="font-weight:600"><?= htmlspecialchars($k['nama_kategori']) ?></td>
                                <td class="small muted" style="max-width:280px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($k['description'] ?? '—') ?></td>
                                <td class="small"><span class="cat-count"><?= $k['jml'] ?></span></td>
                                <td><div class="actions"><a href="proses_kategori.php?action=delete&id=<?= $k['id_kategori'] ?>" onclick="return confirm('Hapus kategori ini? Artikel terkait akan jadi tanpa kategori.')" class="btn btn--ghost btn--xs" style="color:var(--accent)">Hapus</a></div></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                </div>
                <div class="widget" style="height:fit-content">
                    <div class="widget__title">Tambah Kategori</div>
                    <form action="proses_kategori.php" method="post">
                        <input type="hidden" name="action" value="add">
                        <div class="form-group"><label>Nama Kategori *</label><input class="form-control" type="text" name="nama_kategori" placeholder="Contoh: Teknologi" required maxlength="50"></div>
                        <div class="form-group"><label>Deskripsi</label><textarea class="form-control" name="description" rows="3" placeholder="Deskripsi singkat..."></textarea></div>
                        <button type="submit" class="btn btn--primary btn--block">Simpan Kategori</button>
                    </form>
                </div>
            </div>
        </div>

        <div id="users" style="margin-top:28px">
            <div class="section-head"><h2>Manajemen Akun — Buat Jurnalis (Opsi A)</h2><span class="small muted">Hanya Admin yang bisa buat Jurnalis. Publik hanya dapat daftar sebagai Pembaca.</span></div>
            <div style="display:grid;grid-template-columns:1fr 380px;gap:18px">
                <div class="table-wrap">
                    <div class="table-head"><h3>Daftar Users</h3><span class="small muted">Role tidak diambil dari POST publik — server set.</span></div>
                    <div style="overflow:auto">
                    <table class="table">
                        <thead><tr><th>ID</th><th>Nama</th><th>Email</th><th>Role</th><th>Aksi</th></tr></thead>
                        <tbody>
                        <?php foreach($users as $u): ?>
                            <tr>
                                <td class="small muted">#<?= $u['id_users'] ?></td>
                                <td style="font-weight:600"><?= htmlspecialchars($u['nama_users']) ?></td>
                                <td class="small"><?= htmlspecialchars($u['email']) ?></td>
                                <td><span class="badge <?= $u['role']==='Admin'?'badge--published':($u['role']==='Jurnalis'?'badge--review':'badge--archived') ?>"><?= htmlspecialchars($u['role']) ?></span></td>
                                <td>
                                    <?php if((int)$u['id_users'] !== (int)$admin_id): ?>
                                        <a href="proses_user_admin.php?action=delete&id=<?= $u['id_users'] ?>" onclick="return confirm('Hapus user ini? Artikelnya tetap ada.')" class="btn btn--ghost btn--xs" style="color:var(--accent)">Hapus</a>
                                    <?php else: ?>
                                        <span class="small muted">Anda</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                </div>
                <div class="widget" style="height:fit-content">
                    <div class="widget__title">Buat Akun Jurnalis</div>
                    <form action="proses_user_admin.php" method="post">
                        <input type="hidden" name="action" value="create_jurnalis">
                        <div class="form-group"><label>Nama lengkap *</label><input class="form-control" type="text" name="nama_users" placeholder="Nama Jurnalis" required></div>
                        <div class="form-group"><label>Email *</label><input class="form-control" type="email" name="email" placeholder="jurnalis@news.local" required></div>
                        <div class="form-group"><label>Password *</label><input class="form-control" type="password" name="password" placeholder="Minimal 6 karakter" required></div>
                        <div class="form-group"><label>Konfirmasi Password *</label><input class="form-control" type="password" name="password2" placeholder="Ulangi password" required></div>
                        <!-- Role fixed: Jurnalis, tidak ada pilihan Admin di form — server force -->
                        <div style="background:var(--accent-light);border:1px solid var(--accent-border);border-radius:8px;padding:8px 10px;margin-bottom:12px;font-size:12px"><strong>Role:</strong> Jurnalis (otomatis — tidak bisa pilih Admin dari form ini)</div>
                        <button type="submit" class="btn btn--primary btn--block">Buat Jurnalis</button>
                    </form>
                    <div class="small muted" style="margin-top:12px;line-height:1.5">Keamanan: role tidak diambil dari <code>$_POST['role']</code>. Server set <code>$role='Jurnalis'</code> secara hard-coded. Manipulasi POST tidak akan mendapat privilege Admin.</div>
                </div>
            </div>
        </div>

    </main>
</div>
</body>
</html>
