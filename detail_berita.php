<?php
session_start();
include "koneksi.php";
$is_logged = isset($_SESSION['id_users']);
$role_user = $_SESSION['role'] ?? null;

// Terima berbagai nama param: id, id_article, id_berita
$id = $_GET['id'] ?? $_GET['id_article'] ?? $_GET['id_berita'] ?? null;
$id = is_numeric($id) ? (int)$id : null;

if(!$id){
    http_response_code(404);
    $notfound = true;
} else {
    $stmt = $conn->prepare("
        SELECT a.*, k.nama_kategori, u.nama_users, u.email
        FROM articles a
        LEFT JOIN kategori k ON k.id_kategori = a.id_kategori
        LEFT JOIN users u ON u.id_users = a.id_users
        WHERE a.id_article = ?
        LIMIT 1
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $artikel = $stmt->get_result()->fetch_assoc();
    $pemilik_sendiri = $is_logged && $artikel && (int)$artikel['id_users'] === (int)($_SESSION['id_users'] ?? 0);
    if(!$artikel || ($artikel['status'] !== 'published' && !$pemilik_sendiri)){
        // Artikel tidak ada, atau belum published dan yang membuka bukan pemiliknya
        http_response_code(404);
        $notfound = true;
    } else {
        $notfound = false;
        // Artikel terkait: kategori sama, exclude current, published
        $rel = $conn->prepare("
            SELECT a.id_article, a.title, a.published_at, a.isi_berita, k.nama_kategori
            FROM articles a
            LEFT JOIN kategori k ON k.id_kategori = a.id_kategori
            WHERE a.id_kategori = ? AND a.id_article != ? AND a.status='published'
            ORDER BY a.published_at DESC LIMIT 4
        ");
        $rel->bind_param("ii", $artikel['id_kategori'], $artikel['id_article']);
        $rel->execute();
        $related = $rel->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
function tgl_id($dt){ return $dt ? date('d M Y, H:i', strtotime($dt)) : '—'; }
function tgl_short($dt){ return $dt ? date('d M Y', strtotime($dt)) : '—'; }
function img_seed($title,$w=1200,$h=675){ $seed=substr(md5($title),0,8); return "https://picsum.photos/seed/{$seed}/{$w}/{$h}"; }
$cat = $notfound ? '' : ($artikel['nama_kategori'] ?? 'Berita');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $notfound ? 'Artikel tidak ditemukan — NEWS' : htmlspecialchars($artikel['title']).' — NEWS' ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <meta name="description" content="<?= $notfound ? 'Artikel tidak ditemukan' : htmlspecialchars(substr(strip_tags($artikel['isi_berita']),0,160)) ?>">
</head>
<body>

<div class="topbar">
    <div class="container">
        <div class="topbar__left"><span><?= date('l, d M Y') ?></span><span style="opacity:.35">|</span><span>Portal Berita</span></div>
        <div class="topbar__right">
            <?php if($is_logged): ?>
                <?php if($role_user==='Admin'): ?><a href="dashboard_admin.php" style="color:#fff;font-weight:600">Admin</a><?php endif; ?>
                <?php if($role_user==='Jurnalis'): ?><a href="dashboard_jurnalis.php" style="color:#fff;font-weight:600">Dashboard</a><?php endif; ?>
                <?php if($role_user==='Pembaca'): ?><span style="color:#9CA3AF"><?= htmlspecialchars($_SESSION['nama_users'] ?? '') ?></span><?php endif; ?>
                <a href="logout.php">Keluar</a>
            <?php else: ?>
                <a href="login.php">Masuk</a><a href="signup.php" style="color:#fff;font-weight:600">Daftar</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<header class="header">
    <div class="container">
        <div class="header__inner">
            <a href="dashboard_user.php" class="brand"><span class="brand__mark">N</span><span class="brand__text">NEWS</span></a>
            <nav class="nav" id="mainNav">
                <a href="dashboard_user.php">Home</a>
                <a href="dashboard_user.php?kategori=Politik">Politik</a>
                <a href="dashboard_user.php?kategori=Teknologi">Teknologi</a>
                <a href="dashboard_user.php?kategori=Olahraga">Olahraga</a>
                <a href="dashboard_user.php?kategori=Peristiwa">Lifestyle</a>
            </nav>
            <div class="header__actions">
                <form class="search" action="dashboard_user.php" method="get"><input type="search" name="q" placeholder="Cari berita..."><button type="submit">⌕</button></form>
                <a href="dashboard_user.php" class="btn btn--ghost btn--sm">Beranda</a>
                <button class="hamburger" id="hamburger">≡</button>
            </div>
        </div>
    </div>
</header>

<div class="container">
<?php if($notfound): ?>
    <div style="max-width:760px;margin:40px auto;text-align:center;background:#fff;border:1px solid var(--border);border-radius:12px;padding:40px">
        <p style="font-size:48px;margin-bottom:12px">📰</p>
        <h1 style="font-size:20px;font-weight:800;margin-bottom:8px">Artikel tidak ditemukan</h1>
        <p class="muted small" style="margin-bottom:16px">Tautan mungkin salah atau artikel telah dihapus.</p>
        <a href="dashboard_user.php" class="btn btn--primary btn--sm">Kembali ke Beranda</a>
    </div>
<?php else: ?>
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="dashboard_user.php">Home</a><span class="sep">/</span>
        <a href="dashboard_user.php?kategori=<?= urlencode($cat) ?>"><?= htmlspecialchars($cat) ?></a><span class="sep">/</span>
        <span style="color:var(--text);font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:420px;display:inline-block;vertical-align:bottom"><?= htmlspecialchars($artikel['title']) ?></span>
    </nav>

    <div class="article-head">
        <div class="article-kicker">
            <span class="cat"><?= htmlspecialchars($cat) ?></span>
            <span class="small muted"><?= tgl_short($artikel['published_at'] ?? $artikel['created_at']) ?></span>
        </div>
        <h1 class="article-title"><?= htmlspecialchars($artikel['title']) ?></h1>
        <div class="article-meta">
            <span>Oleh <strong><?= htmlspecialchars($artikel['nama_users'] ?? 'Redaksi NEWS') ?></strong></span>
            <span class="dot"></span>
            <time datetime="<?= htmlspecialchars($artikel['published_at']) ?>"><?= tgl_id($artikel['published_at'] ?? $artikel['created_at']) ?></time>
            <span class="dot"></span>
            <span><?= htmlspecialchars($artikel['email'] ?? '') ?></span>
            <span class="share" style="margin-left:auto">
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']) ?>" target="_blank" rel="noopener" title="Share Facebook">f</a>
                <a href="https://twitter.com/intent/tweet?text=<?= urlencode($artikel['title']) ?>&url=<?= urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']) ?>" target="_blank" rel="noopener" title="Share X">𝕏</a>
                <a href="https://wa.me/?text=<?= urlencode($artikel['title'].' http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']) ?>" target="_blank" rel="noopener" title="Share WhatsApp">WA</a>
                <a href="#" onclick="navigator.clipboard.writeText(window.location.href);alert('Link disalin');return false" title="Salin link">⎘</a>
            </span>
        </div>
        <div class="article-hero">
            <img src="<?= img_seed($artikel['title']) ?>" alt="<?= htmlspecialchars($artikel['title']) ?>" loading="eager">
        </div>
    </div>

    <article class="article-body">
        <?= nl2br(htmlspecialchars($artikel['isi_berita'])) ?>
    </article>

    <?php if(!empty($related)): ?>
    <section class="related">
        <div class="section-head" style="margin-top:26px">
            <h2>Artikel Terkait</h2>
            <a href="dashboard_user.php?kategori=<?= urlencode($cat) ?>">Lihat semua</a>
        </div>
        <div class="related-grid">
            <?php foreach($related as $r): ?>
            <article class="card">
                <a href="detail_berita.php?id=<?= $r['id_article'] ?>" class="card__media" style="aspect-ratio:16/10">
                    <img src="<?= img_seed($r['title'], 600, 375) ?>" alt="" loading="lazy">
                </a>
                <div class="card__body">
                    <span class="cat" style="align-self:flex-start"><?= htmlspecialchars($r['nama_kategori'] ?? $cat) ?></span>
                    <h3 class="card__title" style="font-size:15px"><a href="detail_berita.php?id=<?= $r['id_article'] ?>"><?= htmlspecialchars($r['title']) ?></a></h3>
                    <div class="meta"><time><?= tgl_short($r['published_at']) ?></time></div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

<?php endif; ?>
</div>

<footer class="footer" style="margin-top:40px">
    <div class="container">
        <div class="footer__inner">
            <div>
                <a href="dashboard_user.php" class="brand footer__brand"><span class="brand__mark">N</span><span class="brand__text">NEWS</span></a>
                <p style="margin-top:10px;font-size:13px;line-height:1.6;color:#9CA3AF;max-width:360px">Portal berita modern, minimalis, dan terpercaya. Fokus pada isi, cepat diakses, nyaman di semua perangkat.</p>
            </div>
            <div>
                <h5>Navigasi</h5>
                <a href="dashboard_user.php">Home</a>
                <a href="dashboard_user.php?kategori=Politik">Politik</a>
                <a href="dashboard_user.php?kategori=Teknologi">Teknologi</a>
                <a href="dashboard_user.php?kategori=Olahraga">Olahraga</a>
            </div>
            <div>
                <h5>Redaksi</h5>
                <a href="index.php">Masuk</a>
                <a href="signup.php">Daftar</a>
                <a href="logout.php">Keluar</a>
            </div>
        </div>
        <div class="footer__bottom">
            <span>&copy; <?= date('Y') ?> NEWS. All rights reserved.</span>
            <div style="display:flex;gap:12px"><a href="#" style="color:#9CA3AF">Facebook</a><a href="#" style="color:#9CA3AF">X</a><a href="#" style="color:#9CA3AF">Instagram</a></div>
        </div>
    </div>
</footer>

<script>document.getElementById('hamburger')?.addEventListener('click',()=>document.getElementById('mainNav')?.classList.toggle('open'))</script>
</body>
</html>
