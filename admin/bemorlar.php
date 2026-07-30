<?php
/**
 * admin/bemorlar.php - Bemorlar bazasi
 */
require_once '../includes/config.php';
require_once '../includes/init.php';
require_once '../includes/auth.php';

if (!isAdmin()) {
    header('Location: ../kirish-royxatdan-otish.php');
    exit;
}

// Qidiruv
$search = $_GET['search'] ?? '';
$filter_status = $_GET['status'] ?? 'all';

$where = ["1=1"];
$params = [];

if (!empty($search)) {
    $where[] = "(first_name LIKE ? OR last_name LIKE ? OR phone LIKE ? OR email LIKE ?)";
    $searchParam = "%$search%";
    $params = [$searchParam, $searchParam, $searchParam, $searchParam];
}

if ($filter_status !== 'all') {
    $where[] = "status = ?";
    $params[] = $filter_status === 'active' ? 1 : 0;
}

$sql = "SELECT * FROM patients WHERE " . implode(' AND ', $where) . " ORDER BY created_at DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $patients = [];
    $error = 'Ma\'lumotlarni olishda xatolik: ' . $e->getMessage();
}

require_once 'admin-header.php';
?>

<div class="admin-content">
    <div class="page-header">
        <h1>👥 Bemorlar Bazasi</h1>
    </div>
    
    <!-- Qidiruv va Filter -->
    <div class="card glass mb-3">
        <form method="GET" class="filter-form">
            <div class="form-row">
                <div class="form-group flex-grow">
                    <label>Qidiruv (ism, telefon, email)</label>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Qidirish..." class="form-control">
                </div>
                
                <div class="form-group">
                    <label>Holat</label>
                    <select name="status" class="form-control">
                        <option value="all">Barchasi</option>
                        <option value="active" <?= $filter_status === 'active' ? 'selected' : '' ?>>Faol</option>
                        <option value="inactive" <?= $filter_status === 'inactive' ? 'selected' : '' ?>>No faol</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">🔍 Qidirish</button>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Jadval -->
    <div class="card glass">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ism Familiya</th>
                        <th>Telefon</th>
                        <th>Email</th>
                        <th>Ro'yxatdan o'tgan</th>
                        <th>Holat</th>
                        <th>Harakatlar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($patients)): ?>
                    <tr>
                        <td colspan="7" class="text-center">Bemorlar topilmadi</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($patients as $patient): ?>
                        <tr>
                            <td><?= $patient['id'] ?></td>
                            <td><?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></td>
                            <td><?= htmlspecialchars($patient['phone']) ?></td>
                            <td><?= htmlspecialchars($patient['email'] ?? '-') ?></td>
                            <td><?= date('d.m.Y', strtotime($patient['created_at'])) ?></td>
                            <td>
                                <span class="status-badge status-<?= $patient['status'] ? 'active' : 'inactive' ?>">
                                    <?= $patient['status'] ? 'Faol' : 'No faol' ?>
                                </span>
                            </td>
                            <td>
                                <a href="bemor.php?id=<?= $patient['id'] ?>" class="btn btn-sm btn-primary">👁 Ko'rish</a>
                                <a href="?deactivate=<?= $patient['id'] ?>" class="btn btn-sm btn-secondary" onclick="return confirm('Holatni o\'zgartirish?')">
                                    <?= $patient['status'] ? '⏸' : '▶' ?>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Statistika -->
    <div class="stats-grid mt-3">
        <?php
        try {
            $total = $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
            $active = $pdo->query("SELECT COUNT(*) FROM patients WHERE status = 1")->fetchColumn();
            $newToday = $pdo->query("SELECT COUNT(*) FROM patients WHERE DATE(created_at) = CURDATE()")->fetchColumn();
        } catch (PDOException $e) {
            $total = $active = $newToday = 0;
        }
        ?>
        <div class="stat-card glass">
            <div class="stat-value"><?= $total ?></div>
            <div class="stat-label">Jami bemorlar</div>
        </div>
        <div class="stat-card glass">
            <div class="stat-value"><?= $active ?></div>
            <div class="stat-label">Faol bemorlar</div>
        </div>
        <div class="stat-card glass">
            <div class="stat-value"><?= $newToday ?></div>
            <div class="stat-label">Bugun qo'shilgan</div>
        </div>
    </div>
</div>

<?php require_once 'admin-footer.php'; ?>
