<?php
/**
 * User Panel - Boshqaruv (Dashboard)
 * Bemor shaxsiy kabineti bosh sahifasi
 */

$pageTitle = "Boshqaruv";
require_once '../includes/user-header.php';

// Statistika ma'lumotlari
$upcomingAppointment = getUpcomingAppointment($user['id']);
$recentTests = getRecentTests($user['id'], 3);
$unreadChats = getUnreadMessagesCount($user['id']);
$unreadNotificationsCount = getUnreadNotificationsCount($user['id']);

// Oxirgi navbatlar
$recentAppointments = getUserAppointments($user['id'], 5);

// To'lovlar summasi
$totalPayments = getUserTotalPayments($user['id']);
?>

<div class="card">
    <div class="card-header">👋 Xush kelibsiz, <?= htmlspecialchars($user['full_name']) ?>!</div>
    <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
        Shaxsiy kabinetingizga xush kelibsiz. Bu yerda siz o'z navbatlaringizni, test natijalaringizni va boshqa ma'lumotlarni ko'rishingiz mumkin.
    </p>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
        <!-- Yaqinlashayotgan navbat -->
        <div class="card" style="margin-bottom: 0; background: rgba(0, 113, 227, 0.1);">
            <div style="font-size: 2rem; margin-bottom: 0.5rem;">🗓️</div>
            <div style="font-size: 0.875rem; color: var(--text-secondary);">Yaqinlashayotgan navbat</div>
            <?php if ($upcomingAppointment): ?>
                <div style="font-weight: 600; margin-top: 0.5rem;"><?= htmlspecialchars($upcomingAppointment['doctor_name']) ?></div>
                <div style="font-size: 0.875rem;"><?= date('d.m.Y', strtotime($upcomingAppointment['appointment_date'])) ?>, <?= date('H:i', strtotime($upcomingAppointment['appointment_time'])) ?></div>
                <a href="navbatlar.php" class="btn btn-primary" style="margin-top: 1rem; font-size: 0.875rem; padding: 0.5rem 1rem;">Batafsil</a>
            <?php else: ?>
                <div style="font-weight: 600; margin-top: 0.5rem;">Navbat yo'q</div>
                <a href="navbat-olish.php" class="btn btn-primary" style="margin-top: 1rem; font-size: 0.875rem; padding: 0.5rem 1rem;">Navbatga yozilish</a>
            <?php endif; ?>
        </div>
        
        <!-- Test natijalari -->
        <div class="card" style="margin-bottom: 0; background: rgba(52, 199, 89, 0.1);">
            <div style="font-size: 2rem; margin-bottom: 0.5rem;">🧪</div>
            <div style="font-size: 0.875rem; color: var(--text-secondary);">Test natijalari</div>
            <div style="font-weight: 600; margin-top: 0.5rem;"><?= count($recentTests) ?> ta yangi</div>
            <a href="testlar.php" class="btn btn-success" style="margin-top: 1rem; font-size: 0.875rem; padding: 0.5rem 1rem;">Ko'rish</a>
        </div>
        
        <!-- Chat -->
        <div class="card" style="margin-bottom: 0; background: rgba(255, 204, 0, 0.1);">
            <div style="font-size: 2rem; margin-bottom: 0.5rem;">💬</div>
            <div style="font-size: 0.875rem; color: var(--text-secondary);">O'qilmagan xabarlar</div>
            <div style="font-weight: 600; margin-top: 0.5rem;"><?= $unreadChats ?> ta</div>
            <a href="chat.php" class="btn" style="margin-top: 1rem; font-size: 0.875rem; padding: 0.5rem 1rem; background: var(--warning); color: var(--text);">Chatga o'tish</a>
        </div>
        
        <!-- Bildirishnomalar -->
        <div class="card" style="margin-bottom: 0; background: rgba(255, 59, 48, 0.1);">
            <div style="font-size: 2rem; margin-bottom: 0.5rem;">🔔</div>
            <div style="font-size: 0.875rem; color: var(--text-secondary);">Bildirishnomalar</div>
            <div style="font-weight: 600; margin-top: 0.5rem;"><?= $unreadNotificationsCount ?> ta</div>
            <a href="bildirishnomalar.php" class="btn btn-danger" style="margin-top: 1rem; font-size: 0.875rem; padding: 0.5rem 1rem;">Ko'rish</a>
        </div>
    </div>
</div>

<!-- Tezkor harakatlar -->
<div class="card">
    <div class="card-header">⚡ Tezkor harakatlar</div>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
        <a href="navbat-olish.php" class="btn btn-primary" style="text-align: center;">
            <span style="font-size: 1.5rem; display: block; margin-bottom: 0.5rem;">➕</span>
            Yangi navbatga yozilish
        </a>
        <a href="testlar.php" class="btn btn-success" style="text-align: center;">
            <span style="font-size: 1.5rem; display: block; margin-bottom: 0.5rem;">🧪</span>
            Test natijalarim
        </a>
        <a href="chat.php" class="btn" style="text-align: center; background: var(--warning); color: var(--text);">
            <span style="font-size: 1.5rem; display: block; margin-bottom: 0.5rem;">💬</span>
            Qo'llab-quvvatlash
        </a>
        <a href="hisobotlar.php" class="btn" style="text-align: center; background: var(--text-secondary); color: white;">
            <span style="font-size: 1.5rem; display: block; margin-bottom: 0.5rem;">📄</span>
            To'lovlar tarixi
        </a>
    </div>
</div>

<!-- Yaqinlashayotgan navbatlar -->
<div class="card">
    <div class="card-header">📅 Yaqinlashayotgan navbatlar</div>
    <?php if (empty($recentAppointments)): ?>
        <p style="color: var(--text-secondary);">Hozircha navbatlar yo'q.</p>
        <a href="navbat-olish.php" class="btn btn-primary">Navbatga yozilish</a>
    <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Sana</th>
                        <th>Vaqt</th>
                        <th>Shifokor</th>
                        <th>Xizmat</th>
                        <th>Holat</th>
                        <th>Amallar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentAppointments as $appointment): ?>
                        <tr>
                            <td><?= date('d.m.Y', strtotime($appointment['appointment_date'])) ?></td>
                            <td><?= date('H:i', strtotime($appointment['appointment_time'])) ?></td>
                            <td><?= htmlspecialchars($appointment['doctor_name']) ?></td>
                            <td><?= htmlspecialchars($appointment['service_name']) ?></td>
                            <td>
                                <span class="status-badge status-<?= $appointment['status'] ?>">
                                    <?php
                                    $statusLabels = [
                                        'pending' => 'Kutilmoqda',
                                        'confirmed' => 'Tasdiqlandi',
                                        'completed' => 'Bajarildi',
                                        'cancelled' => 'Bekor qilindi'
                                    ];
                                    echo $statusLabels[$appointment['status']] ?? $appointment['status'];
                                    ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($appointment['status'] == 'pending' || $appointment['status'] == 'confirmed'): ?>
                                    <button onclick="cancelAppointment(<?= $appointment['id'] ?>)" class="btn btn-danger" style="padding: 0.5rem 0.75rem; font-size: 0.875rem;">Bekor qilish</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <a href="navbatlar.php" class="btn btn-primary" style="margin-top: 1rem;">Barchasini ko'rish</a>
    <?php endif; ?>
</div>

<script>
    function cancelAppointment(appointmentId) {
        if (!confirm('Haqiqatan ham ushbu navbatni bekor qilmoqchimisiz?')) {
            return;
        }
        
        fetch('../api/cancel-appointment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'appointment_id=' + appointmentId + '&csrf_token=<?= generateCSRFToken() ?>'
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showToast('Navbat bekor qilindi', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(data.message || 'Xatolik yuz berdi', 'error');
            }
        })
        .catch(error => {
            showToast('Xatolik yuz berdi', 'error');
            console.error('Error:', error);
        });
    }
</script>

<?php require_once '../includes/user-footer.php'; ?>
