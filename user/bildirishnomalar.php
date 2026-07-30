<?php
/**
 * user/bildirishnomalar.php - Foydalanuvchi bildirishnomalari
 */
require_once '../includes/config.php';
require_once '../includes/init.php';
require_once '../includes/auth.php';

checkAuth();
checkRole('user');

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Barchasini o'qilgan deb belgilash
if (isset($_POST['mark_all_read'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Xavfsizlik tokeni noto'g'ri.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
            $stmt->execute([$user_id]);
            $success = "Barcha bildirishnomalar o'qilgan deb belgilandi.";
        } catch (PDOException $e) {
            error_log("Mark read error: " . $e->getMessage());
            $error = "Xatolik yuz berdi.";
        }
    }
}

// Bildirishnomani o'chirish
if (isset($_POST['delete_notification'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Xavfsizlik tokeni noto'g'ri.";
    } else {
        $notification_id = (int)($_POST['notification_id'] ?? 0);
        if ($notification_id > 0) {
            try {
                $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
                $stmt->execute([$notification_id, $user_id]);
                $success = "Bildirishnoma o'chirildi.";
            } catch (PDOException $e) {
                error_log("Delete notification error: " . $e->getMessage());
                $error = "Xatolik yuz berdi.";
            }
        }
    }
}

// Filter
$filter = $_GET['filter'] ?? 'all'; // all, unread, read

// Bildirishnomalarni olish
try {
    $sql = "SELECT * FROM notifications WHERE user_id = ?";
    $params = [$user_id];
    
    if ($filter === 'unread') {
        $sql .= " AND is_read = 0";
    } elseif ($filter === 'read') {
        $sql .= " AND is_read = 1";
    }
    
    $sql .= " ORDER BY created_at DESC LIMIT 50";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // O'qilmaganlar soni
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$user_id]);
    $unread_count = $stmt->fetchColumn();
    
} catch (PDOException $e) {
    error_log("Notifications error: " . $e->getMessage());
    $notifications = [];
    $unread_count = 0;
}

$page_title = "Bildirishnomalar";
include '../includes/user-header.php';
?>

<div class="user-container">
    <div class="page-header">
        <h1>🔔 Bildirishnomalar</h1>
        <p><?= $unread_count ?> ta o'qilmagan xabar</p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Filter va amallar -->
    <div class="action-bar" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
        <div class="filter-tabs">
            <a href="?filter=all" class="tab <?= $filter === 'all' ? 'active' : '' ?>">Barchasi</a>
            <a href="?filter=unread" class="tab <?= $filter === 'unread' ? 'active' : '' ?>">
                O'qilmagan <?= $unread_count > 0 ? '<span class="badge">' . $unread_count . '</span>' : '' ?>
            </a>
            <a href="?filter=read" class="tab <?= $filter === 'read' ? 'active' : '' ?>">O'qilgan</a>
        </div>
        
        <?php if ($unread_count > 0): ?>
            <form method="POST" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                <button type="submit" name="mark_all_read" class="btn btn-sm btn-outline">
                    ✅ Barchasini o'qilgan deb belgilash
                </button>
            </form>
        <?php endif; ?>
    </div>

    <!-- Bildirishnomalar ro'yxati -->
    <div class="glass-card">
        <?php if (empty($notifications)): ?>
            <p style="text-align: center; color: #86868b; padding: 40px 0;">
                <?= $filter === 'unread' ? 'O\'qilmagan bildirishnomalar yo\'q.' : ($filter === 'read' ? 'O\'qilgan bildirishnomalar yo\'q.' : 'Bildirishnomalar yo\'q.') ?>
            </p>
        <?php else: ?>
            <div class="notifications-list">
                <?php foreach ($notifications as $notif): ?>
                    <div class="notification-item <?= $notif['is_read'] ? 'read' : 'unread' ?>" 
                         data-id="<?= $notif['id'] ?>"
                         style="padding: 20px; border-bottom: 1px solid rgba(0,0,0,0.1); transition: all 0.3s ease;">
                        
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <div style="flex: 1;">
                                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                                    <span class="notification-icon" style="font-size: 24px;">
                                        <?= $notif['type'] === 'appointment' ? '📅' : ($notif['type'] === 'test' ? '🧪' : ($notif['type'] === 'payment' ? '💰' : '📢')) ?>
                                    </span>
                                    <strong style="font-size: 16px; color: <?= $notif['is_read'] ? '#86868b' : '#1d1d1f' ?>;">
                                        <?= htmlspecialchars($notif['title']) ?>
                                    </strong>
                                    <?php if (!$notif['is_read']): ?>
                                        <span class="badge badge-new">Yangi</span>
                                    <?php endif; ?>
                                </div>
                                
                                <p style="color: #1d1d1f; line-height: 1.6; margin-bottom: 10px; <?= $notif['is_read'] ? 'opacity: 0.7;' : '' ?>">
                                    <?= nl2br(htmlspecialchars($notif['message'])) ?>
                                </p>
                                
                                <small style="color: #86868b;">
                                    📅 <?= date('d.m.Y H:i', strtotime($notif['created_at'])) ?>
                                    
                                    <?php if ($notif['related_url']): ?>
                                        • <a href="<?= htmlspecialchars($notif['related_url']) ?>" style="color: #0071e3; text-decoration: none;">
                                            Batafsil →
                                        </a>
                                    <?php endif; ?>
                                </small>
                            </div>
                            
                            <form method="POST" style="margin-left: 10px;">
                                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                <input type="hidden" name="notification_id" value="<?= $notif['id'] ?>">
                                <button type="submit" name="delete_notification" class="btn btn-sm btn-icon" title="O'chirish" 
                                        onclick="return confirm('Ushbu bildirishnomani o\'chirmoqchimisiz?')">
                                    🗑️
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.filter-tabs {
    display: flex;
    gap: 10px;
}

.tab {
    padding: 8px 16px;
    border-radius: 20px;
    text-decoration: none;
    color: #86868b;
    font-weight: 500;
    transition: all 0.3s ease;
    background: rgba(255,255,255,0.3);
}

.tab:hover {
    background: rgba(255,255,255,0.5);
    color: #1d1d1f;
}

.tab.active {
    background: #0071e3;
    color: white;
}

.tab .badge {
    background: #ff3b30;
    color: white;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 11px;
    margin-left: 5px;
}

.notification-item {
    background: rgba(255,255,255,0.2);
    border-radius: 12px;
    margin-bottom: 10px;
}

.notification-item:hover {
    background: rgba(255,255,255,0.4);
    transform: translateX(5px);
}

.notification-item.unread {
    background: rgba(0, 113, 227, 0.08);
    border-left: 4px solid #0071e3;
}

.notification-item.read {
    opacity: 0.8;
}

.badge-new {
    background: #ff3b30;
    color: white;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

.btn-icon {
    width: 36px;
    height: 36px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: rgba(255,59,48,0.1);
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-icon:hover {
    background: rgba(255,59,48,0.2);
    transform: scale(1.1);
}

@media (max-width: 768px) {
    .action-bar {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-tabs {
        justify-content: center;
    }
    
    .notification-item div:last-child {
        margin-top: 15px;
        margin-left: 0 !important;
    }
}
</style>

<script>
// Bildirishnomani bosganda o'qilgan deb belgilash
document.querySelectorAll('.notification-item[data-id]').forEach(item => {
    item.addEventListener('click', function(e) {
        // Agar link yoki tugma bosilgan bo'lmasa
        if (e.target.tagName !== 'A' && e.target.tagName !== 'BUTTON' && !e.target.closest('form')) {
            const notifId = this.dataset.id;
            
            fetch('../api/mark-notification-read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ notification_id: notifId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    this.classList.remove('unread');
                    this.classList.add('read');
                    this.querySelector('.badge-new')?.remove();
                    
                    // Counter yangilash
                    const counterEl = document.querySelector('.tab[href="?filter=unread"] .badge');
                    if (counterEl) {
                        const count = parseInt(counterEl.textContent) - 1;
                        if (count > 0) {
                            counterEl.textContent = count;
                        } else {
                            counterEl.remove();
                        }
                    }
                }
            })
            .catch(err => console.error('Error:', err));
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>
