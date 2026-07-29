<?php
// admin/shifokor-tahrirlash.php - Shifokor qo'shish/tahrirlash
require_once '../includes/init.php';
require_once '../includes/auth.php';

checkAdminAuth();

$success = '';
$error = '';
$db = getDB();

$doctor = null;
$isEdit = false;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $isEdit = true;
    $stmt = $db->prepare("SELECT * FROM doctors WHERE id = ?");
    $stmt->execute([(int)$_GET['id']]);
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$doctor) {
        header('Location: shifokorlar.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Xavfsizlik xatosi: CSRF token noto'g'ri";
    } else {
        $full_name = cleanInput($_POST['full_name'] ?? '');
        $specialty = cleanInput($_POST['specialty'] ?? '');
        $experience_years = (int)($_POST['experience_years'] ?? 0);
        $biography = cleanInput($_POST['biography'] ?? '');
        $phone = cleanInput($_POST['phone'] ?? '');
        $email = cleanInput($_POST['email'] ?? '');
        $room_number = cleanInput($_POST['room_number'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Qabul kunlari
        $reception_days = $_POST['reception_days'] ?? [];
        
        // Qabul vaqtlari
        $reception_times = [
            'start_morning' => cleanInput($_POST['start_morning'] ?? '09:00'),
            'end_morning' => cleanInput($_POST['end_morning'] ?? '13:00'),
            'start_afternoon' => cleanInput($_POST['start_afternoon'] ?? '14:00'),
            'end_afternoon' => cleanInput($_POST['end_afternoon'] ?? '18:00')
        ];
        
        $image = '';
        if (!empty($_FILES['image']['name'])) {
            $uploadDir = '../uploads/doctors/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $result = uploadFile($_FILES['image'], $uploadDir, ['jpg', 'jpeg', 'png', 'webp'], 2 * 1024 * 1024);
            if ($result['success']) {
                $image = $result['filename'];
            } else {
                $error = "Rasm yuklashda xatolik: " . $result['error'];
            }
        }
        
        if (empty($error)) {
            $reception_days_json = json_encode($reception_days, JSON_UNESCAPED_UNICODE);
            $reception_times_json = json_encode($reception_times, JSON_UNESCAPED_UNICODE);
            
            if ($isEdit) {
                $id = (int)$_POST['id'];
                if ($image) {
                    $stmt = $db->prepare("UPDATE doctors SET full_name=?, specialty=?, experience_years=?, biography=?, phone=?, email=?, room_number=?, price=?, reception_days=?, reception_times=?, image=?, is_active=? WHERE id=?");
                    $stmt->execute([$full_name, $specialty, $experience_years, $biography, $phone, $email, $room_number, $price, $reception_days_json, $reception_times_json, $image, $is_active, $id]);
                } else {
                    $stmt = $db->prepare("UPDATE doctors SET full_name=?, specialty=?, experience_years=?, biography=?, phone=?, email=?, room_number=?, price=?, reception_days=?, reception_times=?, is_active=? WHERE id=?");
                    $stmt->execute([$full_name, $specialty, $experience_years, $biography, $phone, $email, $room_number, $price, $reception_days_json, $reception_times_json, $is_active, $id]);
                }
                $success = "Shifokor ma'lumotlari yangilandi";
            } else {
                $stmt = $db->prepare("INSERT INTO doctors (full_name, specialty, experience_years, biography, phone, email, room_number, price, reception_days, reception_times, image, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$full_name, $specialty, $experience_years, $biography, $phone, $email, $room_number, $price, $reception_days_json, $reception_times_json, $image, $is_active])) {
                    $success = "Shifokor qo'shildi";
                    header("Location: shifokor-tahrirlash.php?id=" . $db->lastInsertId() . "&success=1");
                    exit;
                } else {
                    $error = "Qo'shishda xatolik";
                }
            }
            
            if ($success) {
                $stmt = $db->prepare("SELECT * FROM doctors WHERE id = ?");
                $stmt->execute([$isEdit ? (int)$_POST['id'] : $db->lastInsertId()]);
                $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        }
    }
}

if (isset($_GET['success'])) {
    $success = "Shifokor muvaffaqiyatli saqlandi";
}

$daysOfWeek = ['Dushanba', 'Seshanba', 'Chorshanba', 'Payshanba', 'Juma', 'Shanba', 'Yakshanba'];
$selectedDays = $doctor ? json_decode($doctor['reception_days'] ?? '[]', true) : [];
$receptionTimes = $doctor ? json_decode($doctor['reception_times'] ?? '{}', true) : [
    'start_morning' => '09:00',
    'end_morning' => '13:00',
    'start_afternoon' => '14:00',
    'end_afternoon' => '18:00'
];

include '../includes/admin-header.php';
?>

<div class="admin-content">
    <div class="page-header">
        <h1><?php echo $isEdit ? '✏️ Shifokorni Tahrirlash' : '➕ Yangi Shifokor Qo\'shish'; ?></h1>
    </div>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data" class="glass-form" style="max-width: 800px;">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?php echo $doctor['id']; ?>">
        <?php endif; ?>
        
        <div class="form-row">
            <div class="form-group">
                <label>F.I.Sh. *</label>
                <input type="text" name="full_name" value="<?php echo htmlspecialchars($doctor['full_name'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Mutaxassisligi *</label>
                <select name="specialty" required>
                    <option value="">Tanlang</option>
                    <?php 
                    $specialties = ['Terapevt', 'Xirurg', 'Kardiolog', 'Nevrolog', 'Dermatolog', 'Ginekolog', 'Urolog', 'Oftalmolog', 'ENT', 'Travmatolog', 'Endokrinolog', 'Gastroenterolog', 'Stomatolog', 'Pediatr', 'Onkolog', 'Boshqa'];
                    foreach ($specialties as $spec): 
                    ?>
                        <option value="<?php echo htmlspecialchars($spec); ?>" <?php echo ($doctor['specialty'] ?? '') === $spec ? 'selected' : ''; ?>><?php echo htmlspecialchars($spec); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Ish tajribasi (yil) *</label>
                <input type="number" name="experience_years" value="<?php echo htmlspecialchars($doctor['experience_years'] ?? 0); ?>" min="0" max="60" required>
            </div>
            <div class="form-group">
                <label>Xona raqami</label>
                <input type="text" name="room_number" value="<?php echo htmlspecialchars($doctor['room_number'] ?? ''); ?>" placeholder="101">
            </div>
        </div>
        
        <div class="form-group">
            <label>Biografiya</label>
            <textarea name="biography" rows="5" placeholder="Shifokor haqida batafsil ma'lumot..."><?php echo htmlspecialchars($doctor['biography'] ?? ''); ?></textarea>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Telefon</label>
                <input type="tel" name="phone" value="<?php echo htmlspecialchars($doctor['phone'] ?? ''); ?>" placeholder="+998 90 123 45 67">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($doctor['email'] ?? ''); ?>" placeholder="doctor@clinic.com">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Ko'rik narxi (so'm)</label>
                <input type="number" name="price" value="<?php echo htmlspecialchars($doctor['price'] ?? 0); ?>" placeholder="100000">
            </div>
            <div class="form-group">
                <label>Rasm</label>
                <?php if (!empty($doctor['image'])): ?>
                    <img src="../uploads/doctors/<?php echo htmlspecialchars($doctor['image']); ?>" alt="" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; margin-bottom: 10px;">
                <?php endif; ?>
                <input type="file" name="image" accept="image/*">
                <small>PNG, JPG, WEBP (maks 2MB)</small>
            </div>
        </div>
        
        <div class="form-group">
            <label>Qabul kunlari</label>
            <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                <?php foreach ($daysOfWeek as $day): ?>
                    <label style="display: flex; align-items: center; gap: 5px; padding: 8px 12px; background: rgba(255,255,255,0.5); border-radius: 8px;">
                        <input type="checkbox" name="reception_days[]" value="<?php echo htmlspecialchars($day); ?>" <?php echo in_array($day, $selectedDays) ? 'checked' : ''; ?>>
                        <?php echo htmlspecialchars($day); ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="form-group">
            <label>Qabul vaqtlari</label>
            <div class="form-row">
                <div class="form-group">
                    <label>Ertalab (boshlanish)</label>
                    <input type="time" name="start_morning" value="<?php echo htmlspecialchars($receptionTimes['start_morning'] ?? '09:00'); ?>">
                </div>
                <div class="form-group">
                    <label>Ertalab (tugash)</label>
                    <input type="time" name="end_morning" value="<?php echo htmlspecialchars($receptionTimes['end_morning'] ?? '13:00'); ?>">
                </div>
                <div class="form-group">
                    <label>Tushdan keyin (boshlanish)</label>
                    <input type="time" name="start_afternoon" value="<?php echo htmlspecialchars($receptionTimes['start_afternoon'] ?? '14:00'); ?>">
                </div>
                <div class="form-group">
                    <label>Tushdan keyin (tugash)</label>
                    <input type="time" name="end_afternoon" value="<?php echo htmlspecialchars($receptionTimes['end_afternoon'] ?? '18:00'); ?>">
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <label>
                <input type="checkbox" name="is_active" <?php echo ($doctor['is_active'] ?? 1) ? 'checked' : ''; ?>> Faol
            </label>
        </div>
        
        <button type="submit" class="btn btn-primary"><?php echo $isEdit ? 'Yangilash' : 'Qo\'shish'; ?></button>
        <a href="shifokorlar.php" class="btn btn-secondary">Bekor qilish</a>
    </form>
</div>

<?php include '../includes/admin-footer.php'; ?>
