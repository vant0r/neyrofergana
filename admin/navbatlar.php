<?php
/**
 * admin/navbatlar.php - Navbatlarni boshqarish
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
        $id = (int)$_POST['appointment_id'];
        $status = sanitizeInput($_POST['status']);
        
        try {
            $stmt = $pdo->prepare("UPDATE appointments SET status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$status, $id]);
            
            // Bemorga xabar yuborish (keyinchalik telegram/mail funksiyalari bilan)
            $success = 'Navbat holati o\'zgartirildi';
        } catch (PDOException $e) {
            $error = 'Xatolik: ' . $e->getMessage();
        }
    }
}

// Filtrlar
$filter_status = $_GET['status'] ?? 'all';
$filter_doctor = $_GET['doctor'] ?? 'all';
$filter_date = $_GET['date'] ?? date('Y-m-d');

// Query tuzish
$where = ["1=1"];
$params = [];

if ($filter_status !== 'all') {
    $where[] = "a.status = ?";
    $params[] = $filter_status;
}

if ($filter_doctor !== 'all') {
    $where[] = "a.doctor_id = ?";
    $params[] = $filter_doctor;
}

if (!empty($filter_date)) {
    $where[] = "DATE(a.appointment_date) = ?";
    $params[] = $filter_date;
}

$sql = "SELECT a.*, 
        CONCAT(p.first_name, ' ', p.last_name) as patient_name, 
        p.phone as patient_phone,
        d.full_name as doctor_name,
        s.name as service_name
        FROM appointments a
        LEFT JOIN patients p ON a.patient_id = p.id
        LEFT JOIN doctors d ON a.doctor_id = d.id
        LEFT JOIN services s ON a.service_id = s.id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY a.appointment_date DESC, a.created_at DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $appointments = [];
    $error = 'Ma\'lumotlarni olishda xatolik';
}

// Shifokorlar ro'yxati (filter uchun)
try {
    $stmt = $pdo->query("SELECT id, full_name FROM doctors WHERE status = 1 ORDER BY full_name");
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $doctors = [];
}

require_once 'admin-header.php';
?>

<div class="admin-content">
    <div class="page-header">
        <h1>📅 Navbatlarni Boshqarish</h1>
        <button class="btn btn-primary" onclick="openModal('newAppointmentModal')">+ Yangi navbat</button>
    </div>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <!-- Filter -->
    <div class="card glass mb-3">
        <form method="GET" class="filter-form">
            <div class="form-row">
                <div class="form-group">
                    <label>Sana</label>
                    <input type="date" name="date" value="<?= htmlspecialchars($filter_date) ?>" class="form-control">
                </div>
                
                <div class="form-group">
                    <label>Holat</label>
                    <select name="status" class="form-control">
                        <option value="all">Barchasi</option>
                        <option value="pending" <?= $filter_status === 'pending' ? 'selected' : '' ?>>Kutilmoqda</option>
                        <option value="confirmed" <?= $filter_status === 'confirmed' ? 'selected' : '' ?>>Tasdiqlandi</option>
                        <option value="completed" <?= $filter_status === 'completed' ? 'selected' : '' ?>>Bajarildi</option>
                        <option value="cancelled" <?= $filter_status === 'cancelled' ? 'selected' : '' ?>>Bekor qilindi</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Shifokor</label>
                    <select name="doctor" class="form-control">
                        <option value="all">Barchasi</option>
                        <?php foreach ($doctors as $doc): ?>
                            <option value="<?= $doc['id'] ?>" <?= $filter_doctor == $doc['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($doc['full_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">Filtrlash</button>
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
                        <th>Bemor</th>
                        <th>Telefon</th>
                        <th>Shifokor</th>
                        <th>Xizmat</th>
                        <th>Sana va Vaqt</th>
                        <th>Holat</th>
                        <th>Harakatlar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($appointments)): ?>
                    <tr>
                        <td colspan="8" class="text-center">Navbatlar topilmadi</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($appointments as $apt): ?>
                        <tr>
                            <td><?= $apt['id'] ?></td>
                            <td><?= htmlspecialchars($apt['patient_name'] ?? 'Noma\'lum') ?></td>
                            <td><?= htmlspecialchars($apt['patient_phone'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($apt['doctor_name'] ?? 'Noma\'lum') ?></td>
                            <td><?= htmlspecialchars($apt['service_name'] ?? 'Noma\'lum') ?></td>
                            <td><?= date('d.m.Y H:i', strtotime($apt['appointment_date'])) ?></td>
                            <td>
                                <span class="status-badge status-<?= $apt['status'] ?>">
                                    <?php
                                    $statusLabels = [
                                        'pending' => 'Kutilmoqda',
                                        'confirmed' => 'Tasdiqlandi',
                                        'completed' => 'Bajarildi',
                                        'cancelled' => 'Bekor qilindi'
                                    ];
                                    echo $statusLabels[$apt['status']] ?? $apt['status'];
                                    ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($apt['status'] === 'pending'): ?>
                                    <form method="POST" style="display:inline;">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="appointment_id" value="<?= $apt['id'] ?>">
                                        <input type="hidden" name="status" value="confirmed">
                                        <button type="submit" name="update_status" class="btn btn-sm btn-success">✓</button>
                                    </form>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Bekor qilishni xohlaysizmi?')">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="appointment_id" value="<?= $apt['id'] ?>">
                                        <input type="hidden" name="status" value="cancelled">
                                        <button type="submit" name="update_status" class="btn btn-sm btn-danger">✗</button>
                                    </form>
                                <?php elseif ($apt['status'] === 'confirmed'): ?>
                                    <form method="POST" style="display:inline;">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="appointment_id" value="<?= $apt['id'] ?>">
                                        <input type="hidden" name="status" value="completed">
                                        <button type="submit" name="update_status" class="btn btn-sm btn-primary">Bajarildi</button>
                                    </form>
                                <?php endif; ?>
                                <a href="#" class="btn btn-sm btn-secondary" onclick="alert('Batafsil ma\'lumot tez orada qo\'shiladi')">👁</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Yangi navbat -->
<div id="newAppointmentModal" class="modal">
    <div class="modal-content glass">
        <div class="modal-header">
            <h2>Yangi navbat yaratish</h2>
            <span class="close" onclick="closeModal('newAppointmentModal')">&times;</span>
        </div>
        <form method="POST">
            <?= csrfField() ?>
            
            <div class="form-group">
                <label>Bemor</label>
                <select name="patient_id" class="form-control" required>
                    <option value="">Tanlang...</option>
                    <!-- AJAX orqali to'ldiriladi -->
                </select>
            </div>
            
            <div class="form-group">
                <label>Shifokor</label>
                <select name="doctor_id" class="form-control" required>
                    <option value="">Tanlang...</option>
                    <?php foreach ($doctors as $doc): ?>
                        <option value="<?= $doc['id'] ?>"><?= htmlspecialchars($doc['full_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Xizmat</label>
                <select name="service_id" class="form-control" required>
                    <option value="">Tanlang...</option>
                    <!-- AJAX orqali to'ldiriladi -->
                </select>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Sana</label>
                    <input type="date" name="appointment_date" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Vaqt</label>
                    <input type="time" name="appointment_time" class="form-control" required>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('newAppointmentModal')">Bekor qilish</button>
                <button type="submit" name="create_appointment" class="btn btn-primary">Yaratish</button>
            </div>
        </form>
    </div>
</div>

<style>
.status-pending { background: #ff9500; color: white; }
.status-confirmed { background: #34c759; color: white; }
.status-completed { background: #0071e3; color: white; }
.status-cancelled { background: #ff3b30; color: white; }
</style>

<?php require_once 'admin-footer.php'; ?>
