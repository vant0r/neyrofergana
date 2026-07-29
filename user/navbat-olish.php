<?php
/**
 * User Panel - Navbat olish
 * Yangi navbatga yozilish (3 bosqichli jarayon)
 */

$pageTitle = "Navbatga yozilish";
require_once '../includes/user-header.php';

// Form yuborilganda
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!validateCSRFToken($csrf_token)) {
        $_SESSION['error'] = 'Xavfsizlik tokeni noto\'g\'ri';
        redirect('navbat-olish.php');
    }
    
    $specialty_id = (int)($_POST['specialty_id'] ?? 0);
    $doctor_id = (int)($_POST['doctor_id'] ?? 0);
    $service_id = (int)($_POST['service_id'] ?? 0);
    $appointment_date = sanitizeInput($_POST['appointment_date'] ?? '');
    $appointment_time = sanitizeInput($_POST['appointment_time'] ?? '');
    
    // Validatsiya
    $errors = [];
    if (!$specialty_id) $errors[] = 'Mutaxassislik tanlanmagan';
    if (!$doctor_id) $errors[] = 'Shifokor tanlanmagan';
    if (!$service_id) $errors[] = 'Xizmat tanlanmagan';
    if (!$appointment_date) $errors[] = 'Sana tanlanmagan';
    if (!$appointment_time) $errors[] = 'Vaqt tanlanmagan';
    
    if (empty($errors)) {
        // Navbat yaratish
        $appointmentData = [
            'user_id' => $user['id'],
            'doctor_id' => $doctor_id,
            'service_id' => $service_id,
            'appointment_date' => $appointment_date,
            'appointment_time' => $appointment_time,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $appointmentId = createAppointment($appointmentData);
        
        if ($appointmentId) {
            // Email va Telegram xabar yuborish
            sendAppointmentConfirmation($user['email'], $user['full_name'], $appointmentId);
            
            $_SESSION['success'] = 'Navbat muvaffaqiyatli yaratildi! Tasdiqlash kuting.';
            redirect('navbatlar.php');
        } else {
            $_SESSION['error'] = 'Navbat yaratishda xatolik yuz berdi';
        }
    } else {
        $_SESSION['error'] = implode(', ', $errors);
    }
}

// Barcha mutaxassisliklar
$specialties = getServiceCategories();
// Barcha xizmatlar
$services = getAllServices();
?>

<div class="card">
    <div class="card-header">📝 Yangi navbatga yozilish</div>
    
    <!-- Progress indicator -->
    <div style="display: flex; justify-content: space-between; margin-bottom: 2rem; position: relative;">
        <div style="position: absolute; top: 50%; left: 0; right: 0; height: 2px; background: var(--border); transform: translateY(-50%); z-index: 1;"></div>
        <div class="step-indicator active" data-step="1" style="position: relative; z-index: 2; background: var(--primary); color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600;">1</div>
        <div class="step-indicator" data-step="2" style="position: relative; z-index: 2; background: var(--card-bg); color: var(--text-secondary); width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; border: 2px solid var(--border);">2</div>
        <div class="step-indicator" data-step="3" style="position: relative; z-index: 2; background: var(--card-bg); color: var(--text-secondary); width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; border: 2px solid var(--border);">3</div>
    </div>
    
    <form id="appointmentForm" method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
        
        <!-- Bosqich 1: Mutaxassislik tanlash -->
        <div class="step" data-step="1" style="display: block;">
            <h3 style="margin-bottom: 1.5rem;">1️⃣ Mutaxassislikni tanlang</h3>
            <div class="form-group">
                <label class="form-label" for="specialty_id">Mutaxassislik *</label>
                <select class="form-control" id="specialty_id" name="specialty_id" required onchange="loadDoctors()">
                    <option value="">Tanlang...</option>
                    <?php foreach ($specialties as $specialty): ?>
                        <option value="<?= $specialty['id'] ?>"><?= htmlspecialchars($specialty['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="button" class="btn btn-primary" onclick="nextStep(2)">Keyingi bosqich</button>
        </div>
        
        <!-- Bosqich 2: Shifokor va xizmat tanlash -->
        <div class="step" data-step="2" style="display: none;">
            <h3 style="margin-bottom: 1.5rem;">2️⃣ Shifokor va xizmatni tanlang</h3>
            
            <div class="form-group">
                <label class="form-label" for="doctor_id">Shifokor *</label>
                <select class="form-control" id="doctor_id" name="doctor_id" required onchange="loadDoctorInfo()">
                    <option value="">Avval mutaxassislikni tanlang</option>
                </select>
                <div id="doctor_info" style="margin-top: 1rem; display: none;"></div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="service_id">Xizmat *</label>
                <select class="form-control" id="service_id" name="service_id" required>
                    <option value="">Xizmatni tanlang...</option>
                    <?php foreach ($services as $service): ?>
                        <option value="<?= $service['id'] ?>" data-price="<?= $service['price'] ?>">
                            <?= htmlspecialchars($service['name']) ?> - <?= number_format($service['price'], 0, ',', ' ') ?> so'm
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <button type="button" class="btn" style="background: var(--card-bg);" onclick="prevStep(1)">Ortga</button>
                <button type="button" class="btn btn-primary" onclick="nextStep(3)">Keyingi bosqich</button>
            </div>
        </div>
        
        <!-- Bosqich 3: Sana va vaqt tanlash -->
        <div class="step" data-step="3" style="display: none;">
            <h3 style="margin-bottom: 1.5rem;">3️⃣ Sana va vaqtni tanlang</h3>
            
            <div class="form-group">
                <label class="form-label" for="appointment_date">Sana *</label>
                <input type="date" class="form-control" id="appointment_date" name="appointment_date" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>" max="<?= date('Y-m-d', strtotime('+30 days')) ?>" onchange="loadTimeSlots()">
            </div>
            
            <div class="form-group">
                <label class="form-label" for="appointment_time">Vaqt *</label>
                <select class="form-control" id="appointment_time" name="appointment_time" required disabled>
                    <option value="">Avval sana tanlang</option>
                </select>
                <div id="time_slots_info" style="margin-top: 0.5rem; color: var(--text-secondary); font-size: 0.875rem;"></div>
            </div>
            
            <!-- Tanlangan ma'lumotlar ko'rsatish -->
            <div id="summary" class="card" style="margin-top: 1.5rem; background: rgba(0, 113, 227, 0.05);">
                <h4 style="margin-bottom: 1rem;">📋 Tanlangan ma'lumotlar</h4>
                <div id="summary_content"></div>
            </div>
            
            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <button type="button" class="btn" style="background: var(--card-bg);" onclick="prevStep(2)">Ortga</button>
                <button type="submit" class="btn btn-success">✅ Navbatni tasdiqlash</button>
            </div>
        </div>
    </form>
</div>

<script>
    let currentStep = 1;
    let selectedDoctor = null;
    
    function nextStep(step) {
        // Validatsiya
        if (currentStep === 1) {
            const specialty = document.getElementById('specialty_id').value;
            if (!specialty) {
                showToast('Mutaxassislikni tanlang', 'error');
                return;
            }
            loadDoctors();
        }
        
        if (currentStep === 2) {
            const doctor = document.getElementById('doctor_id').value;
            const service = document.getElementById('service_id').value;
            if (!doctor || !service) {
                showToast('Shifokor va xizmatni tanlang', 'error');
                return;
            }
            updateSummary();
        }
        
        document.querySelector('.step[data-step="' + currentStep + '"]').style.display = 'none';
        currentStep = step;
        document.querySelector('.step[data-step="' + currentStep + '"]').style.display = 'block';
        
        // Update indicators
        document.querySelectorAll('.step-indicator').forEach((indicator, index) => {
            if (index + 1 <= currentStep) {
                indicator.classList.add('active');
                indicator.style.background = 'var(--primary)';
                indicator.style.color = 'white';
                indicator.style.borderColor = 'var(--primary)';
            } else {
                indicator.classList.remove('active');
                indicator.style.background = 'var(--card-bg)';
                indicator.style.color = 'var(--text-secondary)';
                indicator.style.borderColor = 'var(--border)';
            }
        });
    }
    
    function prevStep(step) {
        document.querySelector('.step[data-step="' + currentStep + '"]').style.display = 'none';
        currentStep = step;
        document.querySelector('.step[data-step="' + currentStep + '"]').style.display = 'block';
        
        // Update indicators
        document.querySelectorAll('.step-indicator').forEach((indicator, index) => {
            if (index + 1 <= currentStep) {
                indicator.classList.add('active');
                indicator.style.background = 'var(--primary)';
                indicator.style.color = 'white';
                indicator.style.borderColor = 'var(--primary)';
            } else {
                indicator.classList.remove('active');
                indicator.style.background = 'var(--card-bg)';
                indicator.style.color = 'var(--text-secondary)';
                indicator.style.borderColor = 'var(--border)';
            }
        });
    }
    
    function loadDoctors() {
        const specialtyId = document.getElementById('specialty_id').value;
        const doctorSelect = document.getElementById('doctor_id');
        
        if (!specialtyId) {
            doctorSelect.innerHTML = '<option value="">Avval mutaxassislikni tanlang</option>';
            return;
        }
        
        // AJAX orqali shifokorlarni yuklash (hozircha static)
        fetch('../api/get-doctors-by-specialty.php?specialty_id=' + specialtyId)
            .then(response => response.json())
            .then(data => {
                doctorSelect.innerHTML = '<option value="">Shifokorni tanlang...</option>';
                if (data.status === 'success' && data.doctors.length > 0) {
                    data.doctors.forEach(doctor => {
                        const option = document.createElement('option');
                        option.value = doctor.id;
                        option.textContent = doctor.full_name + ' (' + doctor.experience + ' yil tajriba)';
                        option.dataset.info = JSON.stringify(doctor);
                        doctorSelect.appendChild(option);
                    });
                } else {
                    doctorSelect.innerHTML = '<option value="">Ushbu mutaxassislik bo\'yicha shifokorlar yo\'q</option>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                doctorSelect.innerHTML = '<option value="">Xatolik yuz berdi</option>';
            });
    }
    
    function loadDoctorInfo() {
        const doctorSelect = document.getElementById('doctor_id');
        const selectedOption = doctorSelect.options[doctorSelect.selectedIndex];
        
        if (selectedOption.value && selectedOption.dataset.info) {
            selectedDoctor = JSON.parse(selectedOption.dataset.info);
            const doctorInfo = document.getElementById('doctor_info');
            doctorInfo.innerHTML = `
                <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: rgba(0, 113, 227, 0.05); border-radius: 12px;">
                    <div style="width: 60px; height: 60px; border-radius: 50%; background: var(--primary); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; font-weight: 600;">
                        ${selectedDoctor.full_name.charAt(0)}
                    </div>
                    <div>
                        <div style="font-weight: 600;">${selectedDoctor.full_name}</div>
                        <div style="color: var(--text-secondary); font-size: 0.875rem;">${selectedDoctor.specialty} • ${selectedDoctor.experience} yil tajriba</div>
                        <div style="color: var(--primary); font-size: 0.875rem; margin-top: 0.25rem;">Qabul narxi: ${Number(selectedDoctor.price).toLocaleString()} so'm</div>
                    </div>
                </div>
            `;
            doctorInfo.style.display = 'block';
        } else {
            document.getElementById('doctor_info').style.display = 'none';
        }
    }
    
    function loadTimeSlots() {
        const date = document.getElementById('appointment_date').value;
        const doctorId = document.getElementById('doctor_id').value;
        const timeSelect = document.getElementById('appointment_time');
        
        if (!date || !doctorId) {
            timeSelect.innerHTML = '<option value="">Avval sana va shifokor tanlang</option>';
            timeSelect.disabled = true;
            return;
        }
        
        timeSelect.disabled = true;
        timeSelect.innerHTML = '<option value="">Yuklanmoqda...</option>';
        
        fetch('../api/get-time-slots.php?doctor_id=' + doctorId + '&date=' + date)
            .then(response => response.json())
            .then(data => {
                timeSelect.disabled = false;
                timeSelect.innerHTML = '<option value="">Vaqt tanlang...</option>';
                
                if (data.status === 'success' && data.slots.length > 0) {
                    data.slots.forEach(slot => {
                        const option = document.createElement('option');
                        option.value = slot;
                        option.textContent = slot;
                        timeSelect.appendChild(option);
                    });
                    document.getElementById('time_slots_info').textContent = data.slots.length + ' ta bo\'sh vaqt mavjud';
                } else {
                    timeSelect.innerHTML = '<option value="">Ushbu kun uchun bo\'sh vaqtlar yo\'q</option>';
                    document.getElementById('time_slots_info').textContent = 'Boshqa sana tanlang';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                timeSelect.innerHTML = '<option value="">Xatolik yuz berdi</option>';
                document.getElementById('time_slots_info').textContent = '';
            });
    }
    
    function updateSummary() {
        const specialtySelect = document.getElementById('specialty_id');
        const doctorSelect = document.getElementById('doctor_id');
        const serviceSelect = document.getElementById('service_id');
        const dateInput = document.getElementById('appointment_date');
        const timeSelect = document.getElementById('appointment_time');
        
        const summaryContent = document.getElementById('summary_content');
        summaryContent.innerHTML = `
            <div style="display: grid; gap: 0.75rem;">
                <div><strong>Mutaxassislik:</strong> ${specialtySelect.options[specialtySelect.selectedIndex]?.text || '-'}</div>
                <div><strong>Shifokor:</strong> ${doctorSelect.options[doctorSelect.selectedIndex]?.text || '-'}</div>
                <div><strong>Xizmat:</strong> ${serviceSelect.options[serviceSelect.selectedIndex]?.text || '-'}</div>
                <div><strong>Sana:</strong> ${dateInput.value ? new Date(dateInput.value).toLocaleDateString('uz-UZ') : '-'}</div>
                <div><strong>Vaqt:</strong> ${timeSelect.value || '-'}</div>
            </div>
        `;
    }
</script>

<?php require_once '../includes/user-footer.php'; ?>
