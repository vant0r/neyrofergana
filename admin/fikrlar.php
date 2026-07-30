<?php
/**
 * admin/fikrlar.php - Sharhlar moderatsiyasi
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

// Holatni o'zgartirish
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Xavfsizlik xatosi';
    } else {
        $id = (int)$_POST['review_id'];
        $status = sanitizeInput($_POST['status']);
        
        try {
            $stmt = $pdo->prepare("UPDATE reviews SET status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$status, $id]);
            $success = 'Sharh holati o\'zgartirildi';
        } catch (PDOException $e) {
            $error = 'Xatolik: ' . $e->getMessage();
        }
    }
}

// O'chirish
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: fikrlar.php?deleted=1');
        exit;
    } catch (PDOException $e) {
        $error = 'O\'chirishda xatolik';
    }
}

// Filter
$filter_status = $_GET['status'] ?? 'pending';

$where = ["1=1"];
$params = [];

if ($filter_status !== 'all') {
    $where[] = "r.status = ?";
    $params[] = $filter_status;
}

$sql = "SELECT r.*, 
        CONCAT(p.first_name, ' ', p.last_name) as patient_name,
        d.full_name as doctor_name
        FROM reviews r
        LEFT JOIN patients p ON r.patient_id = p.id
        LEFT JOIN doctors d ON r.doctor_id = d.id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY r.created_at DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $reviews = [];
    $error = 'Ma\'lumotlarni olishda xatolik';
}

require_once 'admin-header.php';
?>

<div class="admin-content">
    <div class="page-header">
        <h1>⭐ Bemor Fikrlari (Sharhlar)</h1>
    </div>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">Sharh o'chirildi</div>
    <?php endif; ?>
    
    <!-- Filter -->
    <div class="card glass mb-3">
        <form method="GET" class="filter-form">
            <div class="form-row">
                <div class="form-group">
                    <label>Holat</label>
                    <select name="status" class="form-control">
                        <option value="pending" <?= $filter_status === 'pending' ? 'selected' : '' ?>>Kutilmoqda</option>
                        <option value="approved" <?= $filter_status === 'approved' ? 'selected' : '' ?>>Tasdiqlangan</option>
                        <option value="rejected" <?= $filter_status === 'rejected' ? 'selected' : '' ?>>Rad etilgan</option>
                        <option value="all">Barchasi</option>
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
        try {
            $total = $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
            $pending = $pdo->query("SELECT COUNT(*) FROM reviews WHERE status = 'pending'")->fetchColumn();
            $approved = $pdo->query("SELECT COUNT(*) FROM reviews WHERE status = 'approved'")->fetchColumn();
        } catch (PDOException $e) {
            $total = $pending = $approved = 0;
        }
        ?>
        <div class="stat-card glass">
            <div class="stat-value"><?= $total ?></div>
            <div class="stat-label">Jami sharhlar</div>
        </div>
        <div class="stat-card glass">
            <div class="stat-value" style="color: #ff9500"><?= $pending ?></div>
            <div class="stat-label">Kutilmoqda</div>
        </div>
        <div class="stat-card glass">
            <div class="stat-value" style="color: #34c759"><?= $approved ?></div>
            <div class="stat-label">Tasdiqlangan</div>
        </div>
    </div>
    
    <!-- Sharhlar ro'yxati -->
    <div class="reviews-list">
        <?php if (empty($reviews)): ?>
            <div class="card glass">
                <p class="text-center">Sharhlar topilmadi</p>
            </div>
        <?php else: ?>
            <?php foreach ($reviews as $review): ?>
            <div class="card glass review-card">
                <div class="review-header">
                    <div class="review-author">
                        <strong><?= htmlspecialchars($review['patient_name'] ?? 'Noma\'lum') ?></strong>
                        <span class="text-muted">→ <?= htmlspecialchars($review['doctor_name'] ?? 'Umumiy') ?></span>
                    </div>
                    <div class="review-rating">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="star <?= $i <= $review['rating'] ? 'filled' : '' ?>">★</span>
                        <?php endfor; ?>
                    </div>
                    <div class="review-date">
                        <?= date('d.m.Y H:i', strtotime($review['created_at'])) ?>
                    </div>
                </div>
                
                <div class="review-content">
                    <p><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                </div>
                
                <div class="review-footer">
                    <span class="status-badge status-<?= $review['status'] ?>">
                        <?php
                        $statusLabels = ['pending' => 'Kutilmoqda', 'approved' => 'Tasdiqlangan', 'rejected' => 'Rad etilgan'];
                        echo $statusLabels[$review['status']] ?? $review['status'];
                        ?>
                    </span>
                    
                    <div class="review-actions">
                        <?php if ($review['status'] === 'pending'): ?>
                            <form method="POST" style="display:inline;">
                                <?= csrfField() ?>
                                <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                                <input type="hidden" name="status" value="approved">
                                <button type="submit" name="update_status" class="btn btn-sm btn-success">✓ Tasdiqlash</button>
                            </form>
                            <form method="POST" style="display:inline;">
                                <?= csrfField() ?>
                                <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit" name="update_status" class="btn btn-sm btn-danger">✗ Rad etish</button>
                            </form>
                        <?php endif; ?>
                        <a href="?delete=<?= $review['id'] ?>" class="btn btn-sm btn-secondary" onclick="return confirm('O\'chirishni xohlaysizmi?')">🗑 O'chirish</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.reviews-list { display: flex; flex-direction: column; gap: 15px; }
.review-card { padding: 20px; }
.review-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px; }
.review-author { font-size: 16px; }
.review-author strong { display: block; }
.review-rating { display: flex; gap: 2px; }
.star { color: #ddd; font-size: 20px; }
.star.filled { color: #ff9500; }
.review-date { font-size: 13px; color: #86868b; }
.review-content { background: rgba(0,0,0,0.03); padding: 15px; border-radius: 8px; margin-bottom: 15px; }
.review-content p { margin: 0; line-height: 1.6; }
.review-footer { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
.review-actions { display: flex; gap: 8px; }
.status-pending { background: #ff9500; color: white; }
.status-approved { background: #34c759; color: white; }
.status-rejected { background: #ff3b30; color: white; }
</style>

<?php require_once 'admin-footer.php'; ?>
