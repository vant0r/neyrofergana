<?php
require_once 'includes/init.php';

$newsId = (int)($_GET['id'] ?? 0);
if ($newsId <= 0) {
    header('Location: yangiliklar.php');
    exit;
}

try {
    $pdo = getDB();
    
    $stmt = $pdo->prepare("SELECT * FROM news WHERE id = :id AND status = 'published'");
    $stmt->execute([':id' => $newsId]);
    $item = $stmt->fetch();
    
    if (!$item) {
        header('Location: 404.php');
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT id, title, image FROM news WHERE id != :id AND status = 'published' ORDER BY created_at DESC LIMIT 3");
    $stmt->execute([':id' => $newsId]);
    $relatedNews = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Yangilik sahifasi xatolik: " . $e->getMessage());
    header('Location: 500.php');
    exit;
}

$siteSettings = getSiteSettings();
$pageTitle = $item['title'] . " - " . ($siteSettings['site_name'] ?? 'Klinika');
require_once 'includes/header.php';
?>

<div class="page-hero">
    <div class="container">
        <span class="badge"><?= htmlspecialchars($item['category']) ?></span>
        <h1><?= htmlspecialchars($item['title']) ?></h1>
        <div class="article-meta">
            <span>📅 <?= date('d.m.Y', strtotime($item['created_at'])) ?></span>
            <?php if ($item['author']): ?>
                <span>✍️ <?= htmlspecialchars($item['author']) ?></span>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container section">
    <article class="article-content glass-card">
        <?php if ($item['image']): ?>
            <div class="article-featured-image">
                <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
            </div>
        <?php endif; ?>
        
        <div class="article-body">
            <?= $item['content'] ?>
        </div>
        
        <div class="article-share">
            <h3>Ulashish:</h3>
            <div class="share-buttons">
                <a href="https://t.me/share/url?url=<?= urlencode($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>&text=<?= urlencode($item['title']) ?>" 
                   class="share-btn telegram" target="_blank" rel="noopener">Telegram</a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" 
                   class="share-btn facebook" target="_blank" rel="noopener">Facebook</a>
                <a href="https://twitter.com/intent/tweet?url=<?= urlencode($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>&text=<?= urlencode($item['title']) ?>" 
                   class="share-btn twitter" target="_blank" rel="noopener">Twitter</a>
                <button onclick="copyLink()" class="share-btn copy">Havolani nusxalash</button>
            </div>
        </div>
    </article>
    
    <?php if (!empty($relatedNews)): ?>
    <section class="related-news">
        <h2>Bog'liq yangiliklar</h2>
        <div class="news-grid">
            <?php foreach ($relatedNews as $news): ?>
                <a href="yangilik.php?id=<?= (int)$news['id'] ?>" class="news-link">
                    <div class="news-card glass-card">
                        <div class="news-image small">
                            <?php if ($news['image']): ?>
                                <img src="<?= htmlspecialchars($news['image']) ?>" alt="<?= htmlspecialchars($news['title']) ?>">
                            <?php else: ?>
                                <div class="news-placeholder">🏥</div>
                            <?php endif; ?>
                        </div>
                        <div class="news-content">
                            <h3><?= htmlspecialchars($news['title']) ?></h3>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
    
    <div class="back-to-list">
        <a href="yangiliklar.php" class="btn btn-outline">← Barcha yangiliklarga qaytish</a>
    </div>
</div>

<style>
.page-hero {
    background: linear-gradient(135deg, var(--primary-color), #5ac8fa);
    color: white;
    padding: clamp(60px, 10vh, 100px) 0;
    text-align: center;
}

.page-hero h1 {
    font-size: clamp(24px, 4vw, 36px);
    margin: 15px 0 10px;
    line-height: 1.3;
}

.badge {
    display: inline-block;
    padding: 6px 16px;
    border-radius: 20px;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    font-size: 14px;
    font-weight: 600;
    text-transform: capitalize;
}

.article-meta {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-top: 15px;
    opacity: 0.9;
    font-size: 14px;
}

.article-content {
    max-width: 800px;
    margin: 0 auto 40px;
    padding: 40px;
}

.article-featured-image {
    margin: -40px -40px 30px;
    overflow: hidden;
    border-radius: 16px 16px 0 0;
}

.article-featured-image img {
    width: 100%;
    height: auto;
    display: block;
}

.article-body {
    font-size: 18px;
    line-height: 1.8;
    color: var(--text-secondary);
}

.article-body p {
    margin-bottom: 20px;
}

.article-body h2,
.article-body h3,
.article-body h4 {
    color: var(--text-primary);
    margin: 30px 0 15px;
    font-weight: 600;
}

.article-body ul,
.article-body ol {
    margin: 20px 0;
    padding-left: 30px;
}

.article-body li {
    margin-bottom: 10px;
}

.article-body img {
    max-width: 100%;
    height: auto;
    border-radius: 12px;
    margin: 20px 0;
}

.article-share {
    margin-top: 40px;
    padding-top: 30px;
    border-top: 1px solid rgba(0,0,0,0.1);
}

.article-share h3 {
    font-size: 18px;
    margin-bottom: 15px;
    color: var(--text-primary);
}

.share-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.share-btn {
    padding: 10px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    font-size: 14px;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.share-btn.telegram {
    background: #0088cc;
    color: white;
}

.share-btn.facebook {
    background: #1877f2;
    color: white;
}

.share-btn.twitter {
    background: #1da1f2;
    color: white;
}

.share-btn.copy {
    background: rgba(0,113,227,0.1);
    color: var(--primary-color);
}

.share-btn:hover {
    transform: translateY(-2px);
    opacity: 0.9;
}

.related-news {
    margin-top: 50px;
}

.related-news h2 {
    font-size: 28px;
    margin-bottom: 25px;
    text-align: center;
}

.news-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}

.news-link {
    text-decoration: none;
}

.news-card {
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.news-link:hover .news-card {
    transform: translateY(-5px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.15);
}

.news-image.small {
    height: 160px;
}

.news-image {
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, #f5f5f7, #e8e8ed);
}

.news-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.news-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 50px;
    background: linear-gradient(135deg, #e3f2fd, #bbdefb);
}

.news-content {
    padding: 15px;
}

.news-content h3 {
    font-size: 16px;
    color: var(--text-primary);
    line-height: 1.4;
    margin: 0;
}

.back-to-list {
    text-align: center;
    margin-top: 40px;
}

@media (max-width: 768px) {
    .article-content {
        padding: 25px;
    }
    
    .article-featured-image {
        margin: -25px -25px 20px;
    }
    
    .article-body {
        font-size: 16px;
    }
    
    .news-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function copyLink() {
    navigator.clipboard.writeText(window.location.href).then(() => {
        showToast('Havola nusxalandi!', 'success');
    }).catch(() => {
        showToast('Xatolik yuz berdi', 'error');
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>
