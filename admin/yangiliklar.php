<?php
/**
 * admin/yangiliklar.php - Yangiliklar CRUD
 */
require_once '../includes/config.php';
require_once '../includes/init.php';
require_once '../includes/auth.php';

if (!isAdmin()) {
    header('Location: ../kirish-royxatdan-otish.php');
    exit;
}

$success = '';
$error = '';

// Yangi yangilik qo'shish
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_news'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Xavfsizlik xatosi';
    } else {
        $title = sanitizeInput($_POST['title']);
        $category = sanitizeInput($_POST['category'] ?? 'news');
        $excerpt = sanitizeInput($_POST['excerpt']);
        $content = $_POST['content']; // HTML ruxsat etiladi
        $published = isset($_POST['published']) ? 1 : 0;
        $publish_date = sanitizeInput($_POST['publish_date'] ?? date('Y-m-d'));
        
        if (empty($title)) {
            $error = 'Sarlavha kiritilishi shart';
        } else {
            // Rasm yuklash
            $image_url = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                
                if (in_array($ext, $allowed) && $_FILES['image']['size'] <= 5242880) {
                    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
                    $target = '../uploads/news/' . $filename;
                    
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                        $image_url = '/uploads/news/' . $filename;
                    } else {
                        $error = 'Rasmni yuklashda xatolik';
                    }
                } else {
                    $error = 'Faqat JPG, PNG, WEBP formatlari (max 5MB)';
                }
            }
            
            if (empty($error)) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO news (title, category, excerpt, content, image_url, published, publish_date, created_at) 
                                          VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                    $stmt->execute([$title, $category, $excerpt, $content, $image_url, $published, $publish_date]);
                    $success = 'Yangilik muvaffaqiyatli qo\'shildi';
                } catch (PDOException $e) {
                    $error = 'Ma\'lumotlar bazasida xatolik: ' . $e->getMessage();
                }
            }
        }
    }
}

// O'chirish
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: yangiliklar.php?deleted=1');
        exit;
    } catch (PDOException $e) {
        $error = 'O\'chirishda xatolik';
    }
}

// Filter
$filter_category = $_GET['category'] ?? 'all';
$filter_status = $_GET['status'] ?? 'all';

$where = ["1=1"];
$params = [];

if ($filter_category !== 'all') {
    $where[] = "category = ?";
    $params[] = $filter_category;
}

if ($filter_status !== 'all') {
    $where[] = "published = ?";
    $params[] = $filter_status === 'published' ? 1 : 0;
}

$sql = "SELECT * FROM news WHERE " . implode(' AND ', $where) . " ORDER BY publish_date DESC, created_at DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $news = [];
    $error = 'Ma\'lumotlarni olishda xatolik';
}

require_once 'admin-header.php';
?>

<div class="admin-content">
    <div class="page-header">
        <h1>📰 Yangiliklar va Maqolalar</h1>
        <button class="btn btn-primary" onclick="openModal('addNewsModal')">+ Yangi yangilik</button>
    </div>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">Yangilik o'chirildi</div>
    <?php endif; ?>
    
    <!-- Filter -->
    <div class="card glass mb-3">
        <form method="GET" class="filter-form">
            <div class="form-row">
                <div class="form-group">
                    <label>Kategoriya</label>
                    <select name="category" class="form-control">
                        <option value="all">Barchasi</option>
                        <option value="news" <?= $filter_category === 'news' ? 'selected' : '' ?>>Yangilik</option>
                        <option value="article" <?= $filter_category === 'article' ? 'selected' : '' ?>>Maqola</option>
                        <option value="promo" <?= $filter_category === 'promo' ? 'selected' : '' ?>>Aksiya</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Holat</label>
                    <select name="status" class="form-control">
                        <option value="all">Barchasi</option>
                        <option value="published" <?= $filter_status === 'published' ? 'selected' : '' ?>>Chop etilgan</option>
                        <option value="draft" <?= $filter_status === 'draft' ? 'selected' : '' ?>>Qoralama</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">Filtrlash</button>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Yangiliklar ro'yxati -->
    <div class="news-grid">
        <?php if (empty($news)): ?>
            <div class="card glass">
                <p class="text-center">Yangiliklar topilmadi</p>
            </div>
        <?php else: ?>
            <?php foreach ($news as $item): ?>
            <div class="card glass news-card">
                <?php if ($item['image_url']): ?>
                    <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" class="news-image">
                <?php else: ?>
                    <div class="news-image-placeholder">📰</div>
                <?php endif; ?>
                
                <div class="news-content">
                    <span class="badge badge-<?= $item['category'] ?>"><?= $item['category'] ?></span>
                    <h3><?= htmlspecialchars($item['title']) ?></h3>
                    <p class="excerpt"><?= htmlspecialchars(mb_substr(strip_tags($item['excerpt']), 0, 100)) ?>...</p>
                    <div class="news-meta">
                        <span>📅 <?= date('d.m.Y', strtotime($item['publish_date'])) ?></span>
                        <span class="status-badge status-<?= $item['published'] ? 'active' : 'inactive' ?>">
                            <?= $item['published'] ? 'Chop etilgan' : 'Qoralama' ?>
                        </span>
                    </div>
                    <div class="news-actions">
                        <a href="?edit=<?= $item['id'] ?>" class="btn btn-sm btn-primary">✏️ Tahrirlash</a>
                        <a href="?delete=<?= $item['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('O\'chirishni xohlaysizmi?')">🗑 O'chirish</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal: Yangi yangilik -->
<div id="addNewsModal" class="modal">
    <div class="modal-content glass modal-large">
        <div class="modal-header">
            <h2>Yangi yangilik qo'shish</h2>
            <span class="close" onclick="closeModal('addNewsModal')">&times;</span>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <?= csrfField() ?>
            
            <div class="form-row">
                <div class="form-group flex-grow">
                    <label>Sarlavha *</label>
                    <input type="text" name="title" required class="form-control">
                </div>
                
                <div class="form-group">
                    <label>Kategoriya</label>
                    <select name="category" class="form-control">
                        <option value="news">Yangilik</option>
                        <option value="article">Maqola</option>
                        <option value="promo">Aksiya</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>Qisqacha matn (excerpt)</label>
                <textarea name="excerpt" rows="2" class="form-control" placeholder="150-200 belgi"></textarea>
            </div>
            
            <div class="form-group">
                <label>To'liq matn (HTML ruxsat etiladi)</label>
                <textarea name="content" rows="10" class="form-control" placeholder="Matnni kiriting..."></textarea>
            </div>
            
            <div class="form-group">
                <label>Rasm yuklash (JPG, PNG, WEBP - max 5MB)</label>
                <input type="file" name="image" accept="image/*" class="form-control">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Chop etish sanasi</label>
                    <input type="date" name="publish_date" value="<?= date('Y-m-d') ?>" class="form-control">
                </div>
                
                <div class="form-group">
                    <label>&nbsp;</label>
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" name="published" checked> Darhol chop etish
                    </label>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addNewsModal')">Bekor qilish</button>
                <button type="submit" name="add_news" class="btn btn-primary">Saqlash</button>
            </div>
        </form>
    </div>
</div>

<style>
.news-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
.news-card { overflow: hidden; padding: 0; }
.news-image { width: 100%; height: 200px; object-fit: cover; }
.news-image-placeholder { width: 100%; height: 200px; background: rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: center; font-size: 60px; }
.news-content { padding: 20px; }
.news-content h3 { margin: 10px 0; font-size: 18px; }
.excerpt { color: #86868b; font-size: 14px; line-height: 1.5; }
.news-meta { display: flex; justify-content: space-between; align-items: center; margin: 15px 0; font-size: 13px; color: #86868b; }
.news-actions { display: flex; gap: 8px; }
.badge-news { background: #0071e3; color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px; }
.badge-article { background: #5ac8fa; color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px; }
.badge-promo { background: #ff9500; color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px; }
.modal-large { max-width: 800px; }
</style>

<?php require_once 'admin-footer.php'; ?>
