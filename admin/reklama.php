<?php
/**
 * admin/reklama.php - Reklama va bannerlarni boshqarish
 */
require_once '../includes/config.php';
require_once '../includes/init.php';
require_once '../includes/auth.php';

// Faqat admin kirishi mumkin
if (!isAdmin()) {
    header('Location: ../kirish-royxatdan-otish.php');
    exit;
}

$success = '';
$error = '';

// Yangi reklama qo'shish
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_ad'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Xavfsizlik xatosi: CSRF token noto\'g\'ri';
    } else {
        $name = sanitizeInput($_POST['name'] ?? '');
        $type = sanitizeInput($_POST['type'] ?? 'banner');
        $page = sanitizeInput($_POST['page'] ?? 'index');
        $position = sanitizeInput($_POST['position'] ?? 'top-right');
        $url = sanitizeInput($_POST['url'] ?? '');
        $text = sanitizeInput($_POST['text'] ?? '');
        $start_date = sanitizeInput($_POST['start_date'] ?? date('Y-m-d'));
        $end_date = sanitizeInput($_POST['end_date'] ?? date('Y-m-d', strtotime('+30 days')));
        $interval = (int)($_POST['interval'] ?? 5);
        $status = isset($_POST['status']) ? 1 : 0;
        
        if (empty($name)) {
            $error = 'Reklama nomi kiritilishi shart';
        } else {
            // Fayl yuklash
            $file_url = '';
            if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
                
                if (in_array($ext, $allowed) && $_FILES['file']['size'] <= 10485760) {
                    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
                    $target = '../uploads/ads/' . $filename;
                    
                    if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
                        $file_url = '/uploads/ads/' . $filename;
                    } else {
                        $error = 'Faylni yuklashda xatolik';
                    }
                } else {
                    $error = 'Fayl turi yoki hajmi ruxsat etilmagan';
                }
            }
            
            if (empty($error)) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO ads (name, type, page, position, file_url, url, text, start_date, end_date, view_interval, status, created_at) 
                                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                    $stmt->execute([$name, $type, $page, $position, $file_url, $url, $text, $start_date, $end_date, $interval, $status]);
                    $success = 'Reklama muvaffaqiyatli qo\'shildi';
                } catch (PDOException $e) {
                    $error = 'Ma\'lumotlar bazasida xatolik: ' . $e->getMessage();
                }
            }
        }
    }
}

// Reklamani o'chirish
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM ads WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: reklama.php?deleted=1');
        exit;
    } catch (PDOException $e) {
        $error = 'O\'chirishda xatolik: ' . $e->getMessage();
    }
}

// Reklamalar ro'yxati
try {
    $stmt = $pdo->query("SELECT * FROM ads ORDER BY created_at DESC");
    $ads = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $ads = [];
    $error = 'Ma\'lumotlarni olishda xatolik';
}

require_once 'admin-header.php';
?>

<div class="admin-content">
    <div class="page-header">
        <h1>📢 Reklama va Bannerlar</h1>
        <button class="btn btn-primary" onclick="openModal('adModal')">+ Yangi reklama</button>
    </div>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">Reklama o'chirildi</div>
    <?php endif; ?>
    
    <div class="card glass">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nomi</th>
                        <th>Turi</th>
                        <th>Sahifa</th>
                        <th>Pozitsiya</th>
                        <th>Muddat</th>
                        <th>Holat</th>
                        <th>Harakatlar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ads as $ad): ?>
                    <tr>
                        <td><?= $ad['id'] ?></td>
                        <td><?= htmlspecialchars($ad['name']) ?></td>
                        <td><span class="badge badge-<?= $ad['type'] ?>"><?= htmlspecialchars($ad['type']) ?></span></td>
                        <td><?= htmlspecialchars($ad['page']) ?></td>
                        <td><?= htmlspecialchars($ad['position']) ?></td>
                        <td><?= date('d.m.Y', strtotime($ad['start_date'])) ?> - <?= date('d.m.Y', strtotime($ad['end_date'])) ?></td>
                        <td>
                            <span class="status-badge status-<?= $ad['status'] ? 'active' : 'inactive' ?>">
                                <?= $ad['status'] ? 'Faol' : 'No faol' ?>
                            </span>
                        </td>
                        <td>
                            <a href="?edit=<?= $ad['id'] ?>" class="btn btn-sm btn-secondary">Tahrirlash</a>
                            <a href="?delete=<?= $ad['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('O\'chirishni xohlaysizmi?')">O'chirish</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Yangi reklama -->
<div id="adModal" class="modal">
    <div class="modal-content glass">
        <div class="modal-header">
            <h2>Yangi reklama qo'shish</h2>
            <span class="close" onclick="closeModal('adModal')">&times;</span>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <?= csrfField() ?>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Reklama nomi *</label>
                    <input type="text" name="name" required class="form-control">
                </div>
                
                <div class="form-group">
                    <label>Turi</label>
                    <select name="type" class="form-control">
                        <option value="banner">Banner</option>
                        <option value="popup">Pop-up</option>
                        <option value="broadcast">Telegram Broadcast</option>
                        <option value="video">Video</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Sahifa</label>
                    <select name="page" class="form-control">
                        <option value="index">Bosh sahifa</option>
                        <option value="xizmatlar">Xizmatlar</option>
                        <option value="shifokorlar">Shifokorlar</option>
                        <option value="all">Barcha sahifalar</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Pozitsiya</label>
                    <select name="position" class="form-control">
                        <option value="top-right">Yuqori o'ng</option>
                        <option value="top-left">Yuqori chap</option>
                        <option value="bottom-right">Pastki o'ng</option>
                        <option value="bottom-left">Pastki chap</option>
                        <option value="center">Markaz</option>
                        <option value="sidebar">Yon panel</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>Fayl yuklash (rasm/GIF/video)</label>
                <input type="file" name="file" accept="image/*,video/*" class="form-control">
                <small>Maksimum 10MB</small>
            </div>
            
            <div class="form-group">
                <label>Bosganda ochiladigan URL</label>
                <input type="url" name="url" placeholder="https://..." class="form-control">
            </div>
            
            <div class="form-group">
                <label>Matn (ixtiyoriy)</label>
                <textarea name="text" rows="3" class="form-control"></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Boshlanish sanasi</label>
                    <input type="date" name="start_date" value="<?= date('Y-m-d') ?>" class="form-control">
                </div>
                
                <div class="form-group">
                    <label>Tugash sanasi</label>
                    <input type="date" name="end_date" value="<?= date('Y-m-d', strtotime('+30 days')) ?>" class="form-control">
                </div>
            </div>
            
            <div class="form-group" id="intervalGroup">
                <label>Ko'rish oralig'i (daqiqada, pop-up uchun)</label>
                <input type="number" name="interval" value="5" min="1" class="form-control">
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="status" checked> Darhol faollashtirish
                </label>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('adModal')">Bekor qilish</button>
                <button type="submit" name="add_ad" class="btn btn-primary">Saqlash</button>
            </div>
        </form>
    </div>
</div>

<style>
.badge-banner { background: #0071e3; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
.badge-popup { background: #ff9500; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
.badge-broadcast { background: #34c759; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
.badge-video { background: #af52de; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
</style>

<script>
document.querySelector('select[name="type"]').addEventListener('change', function() {
    const intervalGroup = document.getElementById('intervalGroup');
    intervalGroup.style.display = this.value === 'popup' ? 'block' : 'none';
});
</script>

<?php require_once 'admin-footer.php'; ?>
