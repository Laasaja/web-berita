<?php
session_start();
include "koneksi.php";

// PROTEKSI JURNALIS: hanya Admin atau Jurnalis yang boleh akses. Pembaca/public ditolak server-side.
if (!isset($_SESSION['id_users']) || !in_array($_SESSION['role'] ?? '', ['Jurnalis','Admin'], true)) {
    header("Location: login.php?error=".urlencode('Akses ditolak: hanya Jurnalis/Admin'));
    exit;
}

$id_users = $_SESSION['id_users'];

// Statistik, TAPI hanya milik penulis ini (pakai WHERE id_users)
$stmt = $conn->prepare("SELECT COUNT(*) AS jumlah FROM articles WHERE id_users = ?");
$stmt->bind_param("i", $id_users);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['jumlah'];

$stmt = $conn->prepare("SELECT COUNT(*) AS jumlah FROM articles WHERE id_users = ? AND status = 'published'");
$stmt->bind_param("i", $id_users);
$stmt->execute();
$published = $stmt->get_result()->fetch_assoc()['jumlah'];

$stmt = $conn->prepare("SELECT COUNT(*) AS jumlah FROM articles WHERE id_users = ? AND status = 'draft'");
$stmt->bind_param("i", $id_users);
$stmt->execute();
$draft = $stmt->get_result()->fetch_assoc()['jumlah'];

// Hitung kategori count global untuk info
$cat_res = $conn->query("SELECT COUNT(*) as c FROM kategori");
$cat_total = $cat_res ? $cat_res->fetch_assoc()['c'] : 0;

// Daftar artikel milik penulis ini saja
$stmt = $conn->prepare("
    SELECT a.id_article, a.title, a.status, a.published_at, a.updated_at, k.nama_kategori
    FROM articles a
    LEFT JOIN kategori k ON k.id_kategori = a.id_kategori
    WHERE a.id_users = ?
    ORDER BY a.updated_at DESC
");
$stmt->bind_param("i", $id_users);
$stmt->execute();
$artikel = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$notif_sukses = $_GET['sukses'] ?? $_GET['success'] ?? null;
$notif_error = $_GET['error'] ?? null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — NEWS</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="admin-layout">
    <aside class="admin-sidebar">
        <a href="dashboard_jurnalis.php" class="admin-brand"><span class="brand__mark">N</span> NEWS</a>
        <div style="background:#1F2937;border-radius:10px;padding:12px;margin-bottom:16px;display:flex;gap:10px;align-items:center">
            <div style="width:36px;height:36px;border-radius:999px;background:var(--accent);display:grid;place-items:center;color:#fff;font-weight:800;font-size:13px"><?= strtoupper(substr($_SESSION['nama_users'],0,1)) ?></div>
            <div style="min-width:0">
                <div style="font-size:13px;font-weight:600;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($_SESSION['nama_users']) ?></div>
                <div style="font-size:11px;color:#9CA3AF">Jurnalis · ID #<?= (int)$id_users ?></div>
            </div>
        </div>
        <nav class="admin-nav" aria-label="Navigasi admin">
            <a href="dashboard_jurnalis.php" class="active">⊞ Dashboard</a>
            <a href="tambah_artikel.php">✎ Tulis Artikel</a>
            <a href="dashboard_user.php">📰 Lihat Berita</a>
            <a href="edit_artikel.php" style="opacity:.5;pointer-events:none">— Edit Artikel</a>
        </nav>
        <div class="admin-sep"></div>
        <div style="font-size:11px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:#6B7280;margin-bottom:8px">Statistik</div>
        <div style="display:flex;flex-direction:column;gap:6px;font-size:13px">
            <div style="display:flex;justify-content:space-between"><span style="color:#9CA3AF">Total</span><strong style="color:#fff"><?= $total ?></strong></div>
            <div style="display:flex;justify-content:space-between"><span style="color:#9CA3AF">Published</span><strong style="color:#86EFAC"><?= $published ?></strong></div>
            <div style="display:flex;justify-content:space-between"><span style="color:#9CA3AF">Draft</span><strong style="color:#FDE68A"><?= $draft ?></strong></div>
            <div style="display:flex;justify-content:space-between"><span style="color:#9CA3AF">Kategori</span><strong style="color:#fff"><?= $cat_total ?></strong></div>
        </div>
        <div class="admin-sep"></div>
        <a href="logout.php" style="display:flex;align-items:center;gap:8px;padding:9px 10px;border-radius:8px;font-size:13px;color:#FCA5A5;border:1px solid #1F2937">↪ Keluar</a>
        <div style="margin-top:16px;font-size:11px;color:#4B5563;line-height:1.5">&copy; <?= date('Y') ?> NEWS<br>Panel Jurnalis — minimal & fokus.</div>
    </aside>

    <main class="admin-main">
        <div class="admin-top">
            <div>
                <p style="font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--accent);margin-bottom:4px">DASHBOARD · JURNALIS</p>
                <h1>Halo, <?= htmlspecialchars(explode(' ', $_SESSION['nama_users'])[0]) ?> — kelola tulisan Anda</h1>
                <p class="small muted" style="margin-top:4px">Artikel terbaru Anda berada di bawah. Tetap konsisten menulis dengan gaya yang jernih dan faktual.</p>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap">
                <a href="dashboard_user.php" class="btn btn--ghost btn--sm">Lihat sebagai Pembaca</a>
                <a href="tambah_artikel.php" class="btn btn--primary">+ Tulis Artikel Baru</a>
            </div>
        </div>

        <?php if($notif_sukses): ?><div class="alert alert--success"><?= htmlspecialchars($notif_sukses) ?></div><?php endif; ?>
        <?php if($notif_error): ?><div class="alert alert--error"><?= htmlspecialchars($notif_error) ?></div><?php endif; ?>

        <div class="stats">
            <div class="stat">
                <div class="stat__label">Total Artikel Saya</div>
                <div class="stat__value"><?= $total ?></div>
                <div class="stat__sub"><?= $total ? 'Semua status' : 'Belum ada artikel' ?></div>
            </div>
            <div class="stat">
                <div class="stat__label">Published</div>
                <div class="stat__value" style="color:#166534"><?= $published ?></div>
                <div class="stat__sub">Tampil di beranda pembaca</div>
            </div>
            <div class="stat">
                <div class="stat__label">Draft</div>
                <div class="stat__value" style="color:#92400E"><?= $draft ?></div>
                <div class="stat__sub">Belum dipublish</div>
            </div>
        </div>

        <div class="table-wrap">
            <div class="table-head">
                <h3>Artikel Saya — <?= count($artikel) ?> entri</h3>
                <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
                    <span class="small muted">Urut: terbaru update</span>
                    <a href="tambah_artikel.php" class="btn btn--primary btn--xs">+ Tambah</a>
                </div>
            </div>
            <div style="overflow:auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Judul</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th>Terakhir Update</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$artikel): ?>
                    <tr><td colspan="5"><div class="empty">Belum ada artikel. Yuk mulai menulis! <a href="tambah_artikel.php" style="color:var(--accent);font-weight:600">Tulis artikel pertama →</a></div></td></tr>
                <?php else: foreach ($artikel as $row): ?>
                    <tr>
                        <td style="font-weight:600;max-width:360px">
                            <div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($row['title']) ?></div>
                            <div class="small muted" style="font-weight:400">ID #<?= $row['id_article'] ?> · <?= htmlspecialchars($row['nama_kategori'] ?? '—') ?></div>
                        </td>
                        <td><span class="badge" style="background:#F3F4F6;border-color:#E5E7EB;color:#374151"><?= htmlspecialchars($row['nama_kategori'] ?? '—') ?></span></td>
                        <td>
                            <?php $st = $row['status']; $cls = $st==='published'?'badge--published':($st==='draft'?'badge--draft':($st==='review'?'badge--review':'badge--archived')); ?>
                            <span class="badge <?= $cls ?>"><?= htmlspecialchars($st) ?></span>
                        </td>
                        <td class="small muted" style="white-space:nowrap"><?= htmlspecialchars(date('d M Y, H:i', strtotime($row['updated_at'] ?? $row['published_at'] ?? 'now'))) ?></td>
                        <td>
                            <div class="actions">
                                <a href="detail_berita.php?id=<?= $row['id_article'] ?>" class="btn btn--ghost btn--xs" title="Pratinjau">Lihat</a>
                                <a href="edit_artikel.php?id=<?= $row['id_article'] ?>" class="btn btn--ghost btn--xs">Edit</a>
                                <a href="hapus.php?id=<?= $row['id_article'] ?>" onclick="return confirm('Yakin hapus artikel ini?')" class="btn btn--ghost btn--xs" style="color:var(--accent);border-color:var(--accent-border)">Hapus</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
            </div>
        </div>

        <div style="margin-top:18px;display:flex;gap:12px;flex-wrap:wrap;align-items:center" class="small muted">
            <span>Butuh bantuan?</span>
            <a href="tambah_artikel.php" style="color:var(--accent);font-weight:600">Tulis artikel</a>
            <span>·</span>
            <a href="dashboard_user.php" style="color:var(--muted)">Pratinjau portal</a>
        </div>
    </main>
</div>
</body>
</html>
