<?php
/**
 * User Panel: Test Natijalari
 * Bemor o'z tahlil natijalarini ko'radi va yuklab oladi
 */
require_once '../includes/init.php';
requireAuth(); // Faqat ro'yxatdan o'tgan bemorlar

$user_id = $_SESSION['user_id'];
$page_title = "Test Natijalari";

// Test natijalarini olish
$tests = getMyTests($user_id);

ob_start();
?>
<div class="dashboard-container">
    <div class="page-header">
        <h1>🧪 Mening Test Natijalarim</h1>
        <p>Tahlil va tekshiruv natijalaringiz</p>
    </div>

    <?php if (empty($tests)): ?>
        <div class="empty-state glass-card">
            <div class="empty-icon">📋</div>
            <h3>Hozircha test natijalari yo'q</h3>
            <p>Shifokor qabulida bo'lgandan so'ng natijalar shu yerda paydo bo'ladi.</p>
            <a href="navbat-olish.php" class="btn btn-primary">Navbatga yozilish</a>
        </div>
    <?php else: ?>
        <div class="tests-grid">
            <?php foreach ($tests as $test): ?>
                <div class="test-card glass-card <?= $test['status'] == 'ready' ? 'status-ready' : 'status-pending' ?>">
                    <div class="test-header">
                        <div class="test-icon">
                    <?php if ($test['file_type'] == 'pdf'): ?>
                        📄
                    <?php else: ?>
                        🖼️
                    <?php endif; ?>
                </div>
                        <div class="test-status">
                            <?php if ($test['status'] == 'ready'): ?>
                                <span class="badge badge-success">Tayyor</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Jarayonda</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="test-body">
                        <h3><?= htmlspecialchars($test['test_name']) ?></h3>
                        <div class="test-meta">
                            <span>📅 <?= date('d.m.Y', strtotime($test['created_at'])) ?></span>
                            <span>👨‍⚕️ <?= htmlspecialchars($test['doctor_name']) ?></span>
                        </div>
                        <p class="test-description"><?= htmlspecialchars($test['description'] ?? 'Tahlil natijasi') ?></p>
                    </div>
                    
                    <div class="test-actions">
                        <?php if ($test['status'] == 'ready'): ?>
                            <?php if ($test['file_type'] == 'pdf'): ?>
                                <a href="<?= htmlspecialchars($test['file_path']) ?>" class="btn btn-primary" target="_blank" download>
                                    📥 Yuklab olish
                                </a>
                            <?php else: ?>
                                <button class="btn btn-primary" onclick="openImageModal('<?= htmlspecialchars($test['file_path']) ?>')">
                                    👁️ Ko'rish
                                </button>
                            <?php endif; ?>
                        <?php else: ?>
                            <button class="btn btn-secondary" disabled>Natija kutilmoqda</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Rasm Modal -->
<div id="imageModal" class="modal" style="display: none;">
    <div class="modal-content glass-card" style="max-width: 90%; max-height: 90vh;">
        <span class="close-modal" onclick="closeImageModal()">&times;</span>
        <img id="modalImage" src="" alt="Test natijasi" style="width: 100%; height: auto; border-radius: 12px;">
    </div>
</div>

<style>
.dashboard-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.page-header {
    margin-bottom: 2rem;
}

.page-header h1 {
    font-size: clamp(1.5rem, 4vw, 2rem);
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.page-header p {
    color: var(--text-secondary);
    font-size: 1rem;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
}

.empty-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.tests-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1.5rem;
}

.test-card {
    display: flex;
    flex-direction: column;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.test-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.15);
}

.test-card.status-ready {
    border-left: 4px solid var(--success);
}

.test-card.status-pending {
    border-left: 4px solid var(--warning);
    opacity: 0.8;
}

.test-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.test-icon {
    font-size: 2rem;
}

.test-body h3 {
    font-size: 1.25rem;
    margin-bottom: 0.75rem;
    color: var(--text-primary);
}

.test-meta {
    display: flex;
    gap: 1rem;
    font-size: 0.875rem;
    color: var(--text-secondary);
    margin-bottom: 0.75rem;
}

.test-description {
    color: var(--text-secondary);
    font-size: 0.9rem;
    line-height: 1.5;
}

.test-actions {
    margin-top: auto;
    padding-top: 1rem;
}

.test-actions .btn {
    width: 100%;
    justify-content: center;
}

.modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(5px);
}

.modal-content {
    position: relative;
    padding: 1rem;
}

.close-modal {
    position: absolute;
    top: -40px;
    right: 0;
    color: white;
    font-size: 2rem;
    font-weight: bold;
    cursor: pointer;
    transition: color 0.3s;
}

.close-modal:hover {
    color: var(--primary);
}

@media (max-width: 768px) {
    .dashboard-container {
        padding: 1rem;
    }
    
    .tests-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function openImageModal(src) {
    document.getElementById('modalImage').src = src;
    document.getElementById('imageModal').style.display = 'flex';
}

function closeImageModal() {
    document.getElementById('imageModal').style.display = 'none';
    document.getElementById('modalImage').src = '';
}

// Modal tashqarisiga bosganda yopish
window.onclick = function(event) {
    const modal = document.getElementById('imageModal');
    if (event.target == modal) {
        closeImageModal();
    }
}
</script>

<?php
$content = ob_get_clean();
require_once '../includes/user-header.php';
echo $content;
require_once '../includes/user-footer.php';
?>
