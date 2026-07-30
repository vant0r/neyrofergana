<?php
/**
 * admin/bemor.php - Yakka bemor profili
 */
require_once '../includes/config.php';
require_once '../includes/init.php';
require_once '../includes/auth.php';

if (!isAdmin()) {
    header('Location: ../kirish-royxatdan-otish.php');
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: bemorlar.php');
    exit;
}

// Bemor ma'lumotlari
try {
    $stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
    $stmt->execute([$id]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$patient) {
        die('Bemor topilmadi');
    }
} catch (PDOException $e) {
    die('Xatolik: ' . $e->getMessage());
}

// Tab tanlash
$tab = $_GET['tab'] ?? 'info';

// Navbatlar tarixi
if ($tab === 'appointments') {
    try {
        $stmt = $pdo->prepare("
            SELECT a.*, d.full_name as doctor_name, s.name as service_name
            FROM appointments a
            LEFT JOIN doctors d ON a.doctor_id = d.id
            LEFT JOIN services s ON a.service_id = s.id
            WHERE a.patient_id = ?
            ORDER BY a.appointment_date DESC
        ");
        $stmt->execute([$id]);
        $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $appointments = [];
    }
}

// Test natijalari
if ($tab === 'tests') {
    try {
        $stmt = $pdo->prepare("
            SELECT t.*, d.full_name as doctor_name
            FROM test_results t
            LEFT JOIN doctors d ON t.doctor_id = d.id
            WHERE t.patient_id = ?
            ORDER BY t.submitted_date DESC
        ");
        $stmt->execute([$id]);
        $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $tests = [];
    }
}

// To'lovlar tarixi
if ($tab === 'payments') {
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, s.name as service_name
            FROM payments p
            LEFT JOIN services s ON p.service_id = s.id
            WHERE p.patient_id = ?
            ORDER BY p.created_at DESC
        ");
        $stmt->execute([$id]);
        $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $payments = [];
    }
}

require_once 'admin-header.php';
?>

<div class="admin-content">
    <div class="page-header">
        <h1>👤 Bemor: <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></h1>
        <a href="bemorlar.php" class="btn btn-secondary">← Orqaga</a>
    </div>
    
    <!-- Asosiy ma'lumotlar -->
    <div class="card glass mb-3">
        <div class="profile-header">
            <div class="avatar-large">
                <?php if (!empty($patient['avatar'])): ?>
                    <img src="<?= htmlspecialchars($patient['avatar']) ?>" alt="Avatar">
                <?php else: ?>
                    <div class="avatar-placeholder"><?= strtoupper(substr($patient['first_name'], 0, 1)) ?></div>
                <?php endif; ?>
            </div>
            <div class="profile-info">
                <h2><?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></h2>
                <p class="text-muted">ID: <?= $patient['id'] ?> | Ro'yxatdan o'tgan: <?= date('d.m.Y', strtotime($patient['created_at'])) ?></p>
                <span class="status-badge status-<?= $patient['status'] ? 'active' : 'inactive' ?>">
                    <?= $patient['status'] ? 'Faol' : 'No faol' ?>
                </span>
            </div>
        </div>
        
        <div class="info-grid mt-3">
            <div class="info-item">
                <label>Telefon</label>
                <div><?= htmlspecialchars($patient['phone']) ?></div>
            </div>
            <div class="info-item">
                <label>Email</label>
                <div><?= htmlspecialchars($patient['email'] ?? 'Kiritilmagan') ?></div>
            </div>
            <div class="info-item">
                <label>Tug'ilgan sana</label>
                <div><?= $patient['birth_date'] ? date('d.m.Y', strtotime($patient['birth_date'])) : 'Noma\'lum' ?></div>
            </div>
            <div class="info-item">
                <label>Jinsi</label>
                <div><?= $patient['gender'] === 'male' ? 'Erkak' : ($patient['gender'] === 'female' ? 'Ayol' : 'Noma\'lum') ?></div>
            </div>
            <div class="info-item">
                <label>Qon guruhi</label>
                <div><?= htmlspecialchars($patient['blood_type'] ?? 'Noma\'lum') ?></div>
            </div>
        </div>
        
        <div class="mt-3">
            <button class="btn btn-primary" onclick="alert('Tahrirlash funksiyasi tez orada qo\'shiladi')">✏️ Tahrirlash</button>
        </div>
    </div>
    
    <!-- Tabs -->
    <div class="tabs">
        <a href="?id=<?= $id ?>&tab=info" class="tab <?= $tab === 'info' ? 'active' : '' ?>">Ma'lumotlar</a>
        <a href="?id=<?= $id ?>&tab=appointments" class="tab <?= $tab === 'appointments' ? 'active' : '' ?>">Navbatlar (<?= count($appointments ?? []) ?>)</a>
        <a href="?id=<?= $id ?>&tab=tests" class="tab <?= $tab === 'tests' ? 'active' : '' ?>">Testlar (<?= count($tests ?? []) ?>)</a>
        <a href="?id=<?= $id ?>&tab=payments" class="tab <?= $tab === 'payments' ? 'active' : '' ?>">To'lovlar</a>
    </div>
    
    <!-- Tab Content -->
    <div class="tab-content mt-3">
        <?php if ($tab === 'info'): ?>
            <div class="card glass">
                <h3>Qo'shimcha ma'lumotlar</h3>
                <p>Bu yerda bemorning qo'shimcha ma'lumotlari ko'rsatiladi.</p>
            </div>
            
        <?php elseif ($tab === 'appointments'): ?>
            <div class="card glass">
                <h3>Navbatlar tarixi</h3>
                <?php if (empty($appointments)): ?>
                    <p>Navbatlar yo'q</p>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Sana</th>
                                <th>Shifokor</th>
                                <th>Xizmat</th>
                                <th>Holat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appointments as $apt): ?>
                            <tr>
                                <td><?= date('d.m.Y H:i', strtotime($apt['appointment_date'])) ?></td>
                                <td><?= htmlspecialchars($apt['doctor_name'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($apt['service_name'] ?? '-') ?></td>
                                <td><span class="status-badge status-<?= $apt['status'] ?>"><?= $apt['status'] ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            
        <?php elseif ($tab === 'tests'): ?>
            <div class="card glass">
                <h3>Test natijalari</h3>
                <?php if (empty($tests)): ?>
                    <p>Test natijalari yo'q</p>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nomi</th>
                                <th>Shifokor</th>
                                <th>Sana</th>
                                <th>Holat</th>
                                <th>Fayl</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tests as $test): ?>
                            <tr>
                                <td><?= htmlspecialchars($test['test_name']) ?></td>
                                <td><?= htmlspecialchars($test['doctor_name'] ?? '-') ?></td>
                                <td><?= date('d.m.Y', strtotime($test['submitted_date'])) ?></td>
                                <td><span class="status-badge status-<?= $test['status'] == 'ready' ? 'confirmed' : 'pending' ?>"><?= $test['status'] ?></span></td>
                                <td>
                                    <?php if ($test['file_path']): ?>
                                        <a href="<?= htmlspecialchars($test['file_path']) ?>" target="_blank" class="btn btn-sm btn-primary">📄 Yuklab olish</a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            
        <?php elseif ($tab === 'payments'): ?>
            <div class="card glass">
                <h3>To'lovlar tarixi</h3>
                <?php if (empty($payments)): ?>
                    <p>To'lovlar yo'q</p>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Sana</th>
                                <th>Xizmat</th>
                                <th>Summa</th>
                                <th>Holat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><?= date('d.m.Y', strtotime($payment['created_at'])) ?></td>
                                <td><?= htmlspecialchars($payment['service_name'] ?? '-') ?></td>
                                <td><?= number_format($payment['amount'], 2) ?> so'm</td>
                                <td><span class="status-badge status-<?= $payment['status'] === 'paid' ? 'confirmed' : 'pending' ?>"><?= $payment['status'] ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.profile-header { display: flex; align-items: center; gap: 20px; }
.avatar-large { width: 100px; height: 100px; border-radius: 50%; overflow: hidden; background: #e0e0e0; }
.avatar-large img { width: 100%; height: 100%; object-fit: cover; }
.avatar-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 40px; font-weight: bold; color: #666; }
.profile-info h2 { margin: 0 0 5px 0; }
.info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
.info-item label { font-size: 12px; color: #86868b; display: block; margin-bottom: 5px; }
.info-item div { font-size: 16px; font-weight: 500; }
.tabs { display: flex; gap: 10px; border-bottom: 1px solid rgba(0,0,0,0.1); padding-bottom: 10px; }
.tab { padding: 10px 20px; border-radius: 8px; text-decoration: none; color: #1d1d1f; transition: all 0.3s; }
.tab:hover { background: rgba(0,0,0,0.05); }
.tab.active { background: #0071e3; color: white; }
</style>

<?php require_once 'admin-footer.php'; ?>
