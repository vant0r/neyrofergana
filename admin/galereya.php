<?php
/**
 * admin/galereya.php - Galereya boshqaruvi
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

// Rasm yuklash
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Xavfsizlik xatosi';
    } else {
        $category = sanitizeInput($_POST['category'] ?? 'general');
        
        if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
            $uploaded = 0;
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            
            foreach ($_FILES['images']['name'] as $key => $name) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    
                    if (in_array($ext, $allowed) && $_FILES['images']['size'][$key] <= 5242880) {
                        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
                        $target = '../uploads/gallery/' . $filename;
                        
                        if (move_uploaded_file($_FILES['images']['tmp_name'][$key], $target)) {
                            try {
                                $stmt = $pdo->prepare("INSERT INTO gallery (image_url, category, uploaded_at) VALUES (?, ?, NOW())");
                                $stmt->execute(['/uploads/gallery/' . $filename, $category]);
                                $uploaded++;
                            } catch (PDOException $e) {
                                $error = 'Ma\'lumotlar bazasida xatolik';
                            }
                        }
                    }
                }
            }
            
            if ($uploaded > 0) {
                $success = "$uploaded ta rasm muvaffaqiyatli yuklandi";
            }
        }
    }
}

// O'chirish
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        // Faylni ham o'chirish
        $stmt = $pdo->prepare("SELECT image_url FROM gallery WHERE id = ?");
        $stmt->execute([$id]);
        $img = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($img && file_exists('..' . $img['image_url'])) {
            unlink('..' . $img['image_url']);
        }
        
        $stmt = $pdo->prepare("DELETE FROM gallery WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: galereya.php?deleted=1');
        exit;
    } catch (PDOException $e) {
        $error = 'O\'chirishda xatolik';
    }
}

// Filter
$filter_category = $_GET['category'] ?? 'all';

$where = ["1=1"];
$params = [];

if ($filter_category !== 'all') {
    $where[] = "category = ?";
    $params[] = $filter_category;
}

$sql = "SELECT * FROM gallery WHERE " . implode(' AND ', $where) . " ORDER BY uploaded_at DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $images = [];
    $error = 'Ma\'lumotlarni olishda xatolik';
}

require_once 'admin-header.php';
?>

<div class="admin-content">
    <div class="page-header">
        <h1>🖼️ Fotogalereya</h1>
        <button class="btn btn-primary" onclick="openModal('uploadModal')">+ Rasm yuklash</button>
    </div>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">Rasm o'chirildi</div>
    <?php endif; ?>
    
    <!-- Filter -->
    <div class="card glass mb-3">
        <form method="GET" class="filter-form">
            <div class="form-row">
                <div class="form-group">
                    <label>Kategoriya</label>
                    <select name="category" class="form-control">
                        <option value="all">Barchasi</option>
                        <option value="building" <?= $filter_category === 'building' ? 'selected' : '' ?>>Bino</option>
                        <option value="rooms" <?= $filter_category === 'rooms' ? 'selected' : '' ?>>Xonalar</option>
                        <option value="equipment" <?= $filter_category === 'equipment' ? 'selected' : '' ?>>Uskunalar</option>
                        <option value="team" <?= $filter_category === 'team' ? 'selected' : '' ?>>Jamoa</option>
                        <option value="events" <?= $filter_category === 'events' ? 'selected' : '' ?>>Tadbirlar</option>
                        <option value="general" <?= $filter_category === 'general' ? 'selected' : '' ?>>Umumiy</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">Filtrlash</button>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Statistika -->
    <div class="stats-grid mb-3">
        <?php
        $total = count($images);
        try {
            $total = $pdo->query("SELECT COUNT(*) FROM gallery")->fetchColumn();
        } catch (PDOException $e) {}
        ?>
        <div class="stat-card glass">
            <div class="stat-value"><?= $total ?></div>
            <div class="stat-label">Jami rasmlar</div>
        </div>
    </div>
    
    <!-- Galereya grid -->
    <div class="gallery-grid">
        <?php if (empty($images)): ?>
            <div class="card glass">
                <p class="text-center">Rasmlar topilmadi</p>
            </div>
        <?php else: ?>
            <?php foreach ($images as $img): ?>
            <div class="gallery-item card glass">
                <img src="<?= htmlspecialchars($img['image_url']) ?>" alt="Gallery" loading="lazy">
                <div class="gallery-overlay">
                    <span class="badge"><?= htmlspecialchars($img['category']) ?></span>
                    <div class="gallery-actions">
                        <a href="<?= htmlspecialchars($img['image_url']) ?>" target="_blank" class="btn btn-sm btn-secondary">👁 Ko'rish</a>
                        <a href="?delete=<?= $img['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('O\'chirishni xohlaysizmi?')">🗑</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal: Rasm yuklash -->
<div id="uploadModal" class="modal">
    <div class="modal-content glass">
        <div class="modal-header">
            <h2>Rasm(lar) yuklash</h2>
            <span class="close" onclick="closeModal('uploadModal')">&times;</span>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <?= csrfField() ?>
            
            <div class="form-group">
                <label>Kategoriya</label>
                <select name="category" class="form-control">
                    <option value="general">Umumiy</option>
                    <option value="building">Bino</option>
                    <option value="rooms">Xonalar</option>
                    <option value="equipment">Uskunalar</option>
                    <option value="team">Jamoa</option>
                    <option value="events">Tadbirlar</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Rasmlar (bir nechta tanlash mumkin)</label>
                <input type="file" name="images[]" multiple accept="image/*" class="form-control" required>
                <small>JPG, PNG, WEBP - Maksimum 5MB har biri</small>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('uploadModal')">Bekor qilish</button>
                <button type="submit" name="upload" class="btn btn-primary">Yuklash</button>
            </div>
        </form>
    </div>
</div>

<style>
.gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
.gallery-item { padding: 0; overflow: hidden; position: relative; height: 250px; }
.gallery-item img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s; }
.gallery-item:hover img { transform: scale(1.05); }
.gallery-overlay { position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.8)); padding: 15px; opacity: 0; transition: opacity 0.3s; }
.gallery-item:hover .gallery-overlay { opacity: 1; }
.gallery-actions { display: flex; gap: 8px; margin-top: 10px; }
.badge { background: rgba(255,255,255,0.9); color: #1d1d1f; padding: 4px 8px; border-radius: 4px; font-size: 11px; }
</style>

<?php require_once 'admin-footer.php'; ?>
