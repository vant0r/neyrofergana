<?php
/**
 * admin/test-natijalari.php - Tahlil natijalarini yuklash va boshqarish
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

// Yangi test natijasi yuklash
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_test'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Xavfsizlik xatosi';
    } else {
        $patient_id = (int)$_POST['patient_id'];
        $doctor_id = (int)$_POST['doctor_id'];
        $test_name = sanitizeInput($_POST['test_name']);
        $submitted_date = sanitizeInput($_POST['submitted_date'] ?? date('Y-m-d'));
        $status = sanitizeInput($_POST['status'] ?? 'processing');
        
        if (empty($test_name) || empty($patient_id)) {
            $error = 'Bemor va test nomi majburiy';
        } else {
            // Fayl yuklash
            $file_path = '';
            if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
                $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
                
                if (in_array($ext, $allowed) && $_FILES['file']['size'] <= 10485760) {
                    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
                    $target = '../uploads/tests/' . $filename;
                    
                    if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
                        $file_path = '/uploads/tests/' . $filename;
                    } else {
                        $error = 'Faylni yuklashda xatolik';
                    }
                } else {
                    $error = 'Faqat PDF, JPG, PNG formatlari ruxsat etiladi (max 10MB)';
                }
            }
            
            if (empty($error)) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO test_results (patient_id, doctor_id, test_name, submitted_date, file_path, status, created_at) 
                                          VALUES (?, ?, ?, ?, ?, ?, NOW())");
                    $stmt->execute([$patient_id, $doctor_id, $test_name, $submitted_date, $file_path, $status]);
                    $success = 'Test natijasi muvaffaqiyatli yuklandi';
                    
                    // Agar tayyor bo'lsa, bemorga xabar yuborish (keyinchalik)
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
        $stmt = $pdo->prepare("DELETE FROM test_results WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: test-natijalari.php?deleted=1');
        exit;
    } catch (PDOException $e) {
        $error = 'O\'chirishda xatolik';
    }
}

// Filterlar
$filter_status = $_GET['status'] ?? 'all';
$filter_patient = $_GET['patient'] ?? '';

$where = ["1=1"];
$params = [];

if ($filter_status !== 'all') {
    $where[] = "t.status = ?";
    $params[] = $filter_status;
}

if (!empty($filter_patient)) {
    $where[] = "(p.first_name LIKE ? OR p.last_name LIKE ?)";
    $searchParam = "%$filter_patient%";
    $params = [$searchParam, $searchParam];
}

$sql = "SELECT t.*, 
        CONCAT(p.first_name, ' ', p.last_name) as patient_name,
        d.full_name as doctor_name
        FROM test_results t
        LEFT JOIN patients p ON t.patient_id = p.id
        LEFT JOIN doctors d ON t.doctor_id = d.id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY t.submitted_date DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $tests = [];
    $error = 'Ma\'lumotlarni olishda xatolik';
}

// Bemorlar va shifokorlar ro'yxati (modal uchun)
try {
    $patients = $pdo->query("SELECT id, first_name, last_name FROM patients ORDER BY last_name")->fetchAll(PDO::FETCH_ASSOC);
    $doctors = $pdo->query("SELECT id, full_name FROM doctors WHERE status = 1 ORDER BY full_name")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $patients = $doctors = [];
}

require_once 'admin-header.php';
?>

<div class="admin-content">
    <div class="page-header">
        <h1>🧪 Test Natijalari</h1>
        <button class="btn btn-primary" onclick="openModal('addTestModal')">+ Yangi natija</button>
    </div>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">Natija o'chirildi</div>
    <?php endif; ?>
    
    <!-- Filter -->
    <div class="card glass mb-3">
        <form method="GET" class="filter-form">
            <div class="form-row">
                <div class="form-group">
                    <label>Holat</label>
                    <select name="status" class="form-control">
                        <option value="all">Barchasi</option>
                        <option value="processing" <?= $filter_status === 'processing' ? 'selected' : '' ?>>Jarayonda</option>
                        <option value="ready" <?= $filter_status === 'ready' ? 'selected' : '' ?>>Tayyor</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Bemor</label>
                    <input type="text" name="patient" value="<?= htmlspecialchars($filter_patient) ?>" placeholder="Qidirish..." class="form-control">
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
                        <th>Test nomi</th>
                        <th>Shifokor</th>
                        <th>Sana</th>
                        <th>Holat</th>
                        <th>Fayl</th>
                        <th>Harakatlar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tests)): ?>
                    <tr>
                        <td colspan="8" class="text-center">Test natijalari topilmadi</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($tests as $test): ?>
                        <tr>
                            <td><?= $test['id'] ?></td>
                            <td><?= htmlspecialchars($test['patient_name'] ?? 'Noma\'lum') ?></td>
                            <td><?= htmlspecialchars($test['test_name']) ?></td>
                            <td><?= htmlspecialchars($test['doctor_name'] ?? '-') ?></td>
                            <td><?= date('d.m.Y', strtotime($test['submitted_date'])) ?></td>
                            <td>
                                <span class="status-badge status-<?= $test['status'] === 'ready' ? 'confirmed' : 'pending' ?>">
                                    <?= $test['status'] === 'ready' ? 'Tayyor' : 'Jarayonda' ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($test['file_path']): ?>
                                    <a href="<?= htmlspecialchars($test['file_path']) ?>" target="_blank" class="btn btn-sm btn-secondary">📄 Ko'rish</a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="?edit=<?= $test['id'] ?>" class="btn btn-sm btn-primary">✏️</a>
                                <a href="?delete=<?= $test['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('O\'chirishni xohlaysizmi?')">🗑</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Yangi test -->
<div id="addTestModal" class="modal">
    <div class="modal-content glass">
        <div class="modal-header">
            <h2>Yangi test natijasi yuklash</h2>
            <span class="close" onclick="closeModal('addTestModal')">&times;</span>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <?= csrfField() ?>
            
            <div class="form-group">
                <label>Bemor *</label>
                <select name="patient_id" class="form-control" required>
                    <option value="">Tanlang...</option>
                    <?php foreach ($patients as $patient): ?>
                        <option value="<?= $patient['id'] ?>"><?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Shifokor</label>
                <select name="doctor_id" class="form-control">
                    <option value="">Tanlang...</option>
                    <?php foreach ($doctors as $doc): ?>
                        <option value="<?= $doc['id'] ?>"><?= htmlspecialchars($doc['full_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Test nomi *</label>
                <input type="text" name="test_name" required class="form-control" placeholder="Masalan: Qon tahlili">
            </div>
            
            <div class="form-group">
                <label>Topshirilgan sana</label>
                <input type="date" name="submitted_date" value="<?= date('Y-m-d') ?>" class="form-control">
            </div>
            
            <div class="form-group">
                <label>Fayl yuklash (PDF, JPG, PNG - max 10MB)</label>
                <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png" class="form-control">
            </div>
            
            <div class="form-group">
                <label>Holat</label>
                <select name="status" class="form-control">
                    <option value="processing">Jarayonda</option>
                    <option value="ready">Tayyor</option>
                </select>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addTestModal')">Bekor qilish</button>
                <button type="submit" name="add_test" class="btn btn-primary">Saqlash</button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'admin-footer.php'; ?>
