<?php
require_once 'includes/init.php';

$category = $_GET['category'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 9;

try {
    $pdo = getDB();
    
    $sql = "SELECT * FROM news WHERE status = 'published'";
    $params = [];
    
    if ($category) {
        $sql .= " AND category = :category";
        $params[':category'] = $category;
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    $countSql = str_replace('*', 'COUNT(*) as cnt', $sql);
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $total = $stmt->fetch()['cnt'];
    
    $sql .= " LIMIT :offset, :limit";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':offset', ($page - 1) * $limit, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $news = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Yangiliklar xatolik: " . $e->getMessage());
    $news = [];
    $total = 0;
}

$siteSettings = getSiteSettings();
$pageTitle = "Yangiliklar va Maqolalar - " . ($siteSettings['site_name'] ?? 'Klinika');
require_once 'includes/header.php';
?>

<div class="page-hero">
    <div class="container">
        <h1>Yangiliklar va Maqolalar</h1>
        <p>Klinikamiz hayotidan so'nggi yangiliklar</p>
    </div>
</div>

<div class="container section">
    <div class="category-filter glass-card">
        <a href="yangiliklar.php" class="filter-chip <?= !$category ? 'active' : '' ?>">Barchasi</a>
        <a href="?category=aksiya" class="filter-chip <?= $category === 'aksiya' ? 'active' : '' ?>">Aksiyalar</a>
        <a href="?category=maqola" class="filter-chip <?= $category === 'maqola' ? 'active' : '' ?>">Maqolalar</a>
        <a href="?category=yangilik" class="filter-chip <?= $category === 'yangilik' ? 'active' : '' ?>">Yangiliklar</a>
    </div>
    
    <?php if (empty($news)): ?>
        <div class="empty-state glass-card">
            <div class="empty-icon">📰</div>
            <h3>Yangiliklar topilmadi</h3>
            <p>Tez orada yangi ma'lumotlar qo'shiladi</p>
        </div>
    <?php else: ?>
        <div class="news-grid">
            <?php foreach ($news as $item): ?>
                <article class="news-card glass-card">
                    <div class="news-image">
                        <?php if ($item['image']): ?>
                            <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                        <?php else: ?>
                            <div class="news-placeholder">🏥</div>
                        <?php endif; ?>
                        <span class="news-category badge"><?= htmlspecialchars($item['category']) ?></span>
                    </div>
                    <div class="news-content">
                        <h3><?= htmlspecialchars($item['title']) ?></h3>
                        <p class="news-excerpt"><?= htmlspecialchars(mb_substr(strip_tags($item['content']), 0, 150)) ?>...</p>
                        <div class="news-meta">
                            <span class="news-date">📅 <?= date('d.m.Y', strtotime($item['created_at'])) ?></span>
                        </div>
                        <a href="yangilik.php?id=<?= (int)$item['id'] ?>" class="btn btn-primary">Batafsil o'qish</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        
        <?php if ($total > $limit): ?>
            <div class="pagination">
                <?php
                $totalPages = ceil($total / $limit);
                if ($page > 1): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="btn btn-outline">← Oldingi</a>
                <?php endif; ?>
                
                <span class="page-info"><?= $page ?> / <?= $totalPages ?></span>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="btn btn-outline">Keyingi →</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
.page-hero {
    background: linear-gradient(135deg, var(--primary-color), #5ac8fa);
    color: white;
    padding: clamp(60px, 10vh, 100px) 0;
    text-align: center;
}

.page-hero h1 {
    font-size: clamp(28px, 5vw, 42px);
    margin-bottom: 10px;
}

.page-hero p {
    font-size: clamp(16px, 3vw, 20px);
    opacity: 0.9;
}

.category-filter {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    padding: 20px;
    margin-bottom: 30px;
    justify-content: center;
}

.filter-chip {
    padding: 10px 20px;
    border-radius: 25px;
    background: rgba(0,113,227,0.1);
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
}

.filter-chip:hover,
.filter-chip.active {
    background: var(--primary-color);
    color: white;
}

.news-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 24px;
}

.news-card {
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.news-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.15);
}

.news-image {
    height: 200px;
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, #f5f5f7, #e8e8ed);
}

.news-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.news-card:hover .news-image img {
    transform: scale(1.05);
}

.news-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 60px;
    background: linear-gradient(135deg, #e3f2fd, #bbdefb);
}

.news-category {
    position: absolute;
    top: 15px;
    left: 15px;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    background: var(--primary-color);
    color: white;
    text-transform: capitalize;
}

.news-content {
    padding: 20px;
}

.news-content h3 {
    font-size: 18px;
    margin-bottom: 10px;
    color: var(--text-primary);
    line-height: 1.4;
}

.news-excerpt {
    color: var(--text-secondary);
    line-height: 1.6;
    margin-bottom: 15px;
    font-size: 14px;
}

.news-meta {
    margin-bottom: 15px;
}

.news-date {
    font-size: 13px;
    color: var(--text-secondary);
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-icon {
    font-size: 80px;
    margin-bottom: 20px;
}

@media (max-width: 768px) {
    .news-grid {
        grid-template-columns: 1fr;
    }
    
    .category-filter {
        justify-content: flex-start;
        overflow-x: auto;
        flex-wrap: nowrap;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>
