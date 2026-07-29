<?php
require_once 'includes/init.php';

$category = $_GET['category'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 12;

try {
    $pdo = getDB();
    
    $sql = "SELECT * FROM gallery WHERE status = 'active'";
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
    $images = $stmt->fetchAll();
    
    $stmt = $pdo->query("SELECT DISTINCT category FROM gallery WHERE status = 'active'");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
} catch (Exception $e) {
    error_log("Galereya xatolik: " . $e->getMessage());
    $images = [];
    $categories = [];
    $total = 0;
}

$siteSettings = getSiteSettings();
$pageTitle = "Fotogalereya - " . ($siteSettings['site_name'] ?? 'Klinika');
require_once 'includes/header.php';
?>

<div class="page-hero">
    <div class="container">
        <h1>Fotogalereya</h1>
        <p>Klinikamiz hayotidan suratkalar</p>
    </div>
</div>

<div class="container section">
    <?php if (!empty($categories)): ?>
    <div class="category-filter glass-card">
        <a href="galereya.php" class="filter-chip <?= !$category ? 'active' : '' ?>">Barchasi</a>
        <?php foreach ($categories as $cat): ?>
            <a href="?category=<?= urlencode($cat) ?>" class="filter-chip <?= $category === $cat ? 'active' : '' ?>"><?= htmlspecialchars($cat) ?></a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <?php if (empty($images)): ?>
        <div class="empty-state glass-card">
            <div class="empty-icon">📷</div>
            <h3>Rasmlar hozircha qo'shilmagan</h3>
            <p>Tez orada yangi rasmlar qo'shiladi</p>
        </div>
    <?php else: ?>
        <div class="gallery-grid">
            <?php foreach ($images as $image): ?>
                <div class="gallery-item" data-src="<?= htmlspecialchars($image['image']) ?>" data-title="<?= htmlspecialchars($image['title'] ?? '') ?>" data-category="<?= htmlspecialchars($image['category'] ?? '') ?>">
                    <div class="gallery-thumbnail">
                        <img src="<?= htmlspecialchars($image['image']) ?>" alt="<?= htmlspecialchars($image['title'] ?? 'Rasm') ?>" loading="lazy">
                        <div class="gallery-overlay">
                            <span class="zoom-icon">🔍</span>
                        </div>
                    </div>
                    <?php if ($image['title']): ?>
                    <div class="gallery-caption">
                        <p><?= htmlspecialchars($image['title']) ?></p>
                        <?php if ($image['category']): ?>
                        <span class="gallery-category"><?= htmlspecialchars($image['category']) ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
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

<div class="lightbox" id="lightbox" onclick="closeLightbox(event)">
    <button class="lightbox-close" onclick="closeLightbox()">✕</button>
    <div class="lightbox-content">
        <img id="lightbox-img" src="" alt="">
        <div class="lightbox-info">
            <h3 id="lightbox-title"></h3>
            <span id="lightbox-category"></span>
        </div>
    </div>
    <button class="lightbox-nav prev" onclick="navigateLightbox(-1)">‹</button>
    <button class="lightbox-nav next" onclick="navigateLightbox(1)">›</button>
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

.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}

.gallery-item {
    cursor: pointer;
    transition: transform 0.3s ease;
}

.gallery-item:hover {
    transform: scale(1.02);
}

.gallery-thumbnail {
    position: relative;
    height: 220px;
    overflow: hidden;
    border-radius: 16px;
    background: linear-gradient(135deg, #f5f5f7, #e8e8ed);
}

.gallery-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.gallery-item:hover .gallery-thumbnail img {
    transform: scale(1.1);
}

.gallery-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.gallery-item:hover .gallery-overlay {
    opacity: 1;
}

.zoom-icon {
    font-size: 40px;
    color: white;
}

.gallery-caption {
    padding: 15px 5px;
}

.gallery-caption p {
    font-size: 15px;
    color: var(--text-primary);
    margin-bottom: 5px;
    font-weight: 500;
}

.gallery-category {
    font-size: 12px;
    color: var(--primary-color);
    font-weight: 600;
}

.lightbox {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.9);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 10000;
    backdrop-filter: blur(10px);
}

.lightbox.active {
    display: flex;
}

.lightbox-close {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.lightbox-close:hover {
    background: rgba(255,255,255,0.3);
    transform: rotate(90deg);
}

.lightbox-content {
    max-width: 90%;
    max-height: 90%;
    text-align: center;
}

.lightbox-content img {
    max-width: 100%;
    max-height: 80vh;
    object-fit: contain;
    border-radius: 12px;
}

.lightbox-info {
    margin-top: 20px;
    color: white;
}

.lightbox-info h3 {
    font-size: 22px;
    margin-bottom: 8px;
}

#lightbox-category {
    font-size: 14px;
    opacity: 0.8;
}

.lightbox-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    font-size: 30px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.lightbox-nav:hover {
    background: rgba(255,255,255,0.3);
}

.lightbox-nav.prev {
    left: 20px;
}

.lightbox-nav.next {
    right: 20px;
}

@media (max-width: 768px) {
    .gallery-grid {
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 15px;
    }
    
    .gallery-thumbnail {
        height: 160px;
    }
    
    .lightbox-nav {
        width: 50px;
        height: 50px;
        font-size: 24px;
    }
    
    .lightbox-nav.prev {
        left: 10px;
    }
    
    .lightbox-nav.next {
        right: 10px;
    }
    
    .category-filter {
        justify-content: flex-start;
        overflow-x: auto;
        flex-wrap: nowrap;
    }
}
</style>

<script>
let currentIndex = 0;
const galleryItems = document.querySelectorAll('.gallery-item');

galleryItems.forEach((item, index) => {
    item.addEventListener('click', () => openLightbox(index));
});

function openLightbox(index) {
    currentIndex = index;
    const item = galleryItems[index];
    const img = item.querySelector('img');
    const title = item.getAttribute('data-title');
    const category = item.getAttribute('data-category');
    
    document.getElementById('lightbox-img').src = img.src;
    document.getElementById('lightbox-title').textContent = title;
    document.getElementById('lightbox-category').textContent = category;
    
    document.getElementById('lightbox').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeLightbox(event) {
    if (event && event.target !== event.currentTarget) return;
    
    document.getElementById('lightbox').classList.remove('active');
    document.body.style.overflow = '';
}

function navigateLightbox(direction) {
    currentIndex += direction;
    
    if (currentIndex < 0) {
        currentIndex = galleryItems.length - 1;
    } else if (currentIndex >= galleryItems.length) {
        currentIndex = 0;
    }
    
    openLightbox(currentIndex);
}

document.addEventListener('keydown', (e) => {
    if (!document.getElementById('lightbox').classList.contains('active')) return;
    
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') navigateLightbox(-1);
    if (e.key === 'ArrowRight') navigateLightbox(1);
});
</script>

<?php require_once 'includes/footer.php'; ?>
