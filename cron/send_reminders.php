<?php
/**
 * Navbat eslatmalari yuborish skripti
 * Har 15 daqiqada ishga tushiriladi (cron orqali)
 * 
 * Foydalanish:
 * */15 * * * * php /path/to/cron/send_reminders.php
 */

// Loyiha konfiguratsiyasini yuklash
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/mailer.php';
require_once __DIR__ . '/../includes/telegram.php';

// Skript faqat CLI orqali ishga tushirilishi kerak
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die('Bu skript faqat CLI orqali ishga tushirilishi mumkin.');
}

// Log fayli
$log_file = __DIR__ . '/cron.log';

function log_message($message, $log_file) {
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] $message\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);
    echo $log_entry;
}

log_message("=== Eslatma yuborish boshlandi ===", $log_file);

try {
    // Ma'lumotlar bazasiga ulanish
    $db = getDB();
    
    // Sozlamalarni olish
    $settings = getSettings();
    $contacts = getContacts();
    
    // 24 soat ichida bo'ladigan navbatlarni topish
    $now = new DateTime();
    $tomorrow = clone $now;
    $tomorrow->modify('+24 hours');
    
    $stmt = $db->prepare("
        SELECT 
            a.id,
            a.appointment_date,
            a.start_time,
            a.status,
            u.id as user_id,
            u.name as patient_name,
            u.email as patient_email,
            u.phone as patient_phone,
            d.full_name as doctor_name,
            d.specialty as doctor_specialty,
            s.name as service_name
        FROM appointments a
        JOIN users u ON a.patient_id = u.id
        JOIN doctors d ON a.doctor_id = d.id
        LEFT JOIN services s ON a.service_id = s.id
        WHERE 
            a.appointment_date BETWEEN ? AND ?
            AND a.status IN ('pending', 'confirmed')
            AND a.reminder_sent = 0
        ORDER BY a.appointment_date ASC, a.start_time ASC
    ");
    
    $stmt->execute([$now->format('Y-m-d H:i:s'), $tomorrow->format('Y-m-d H:i:s')]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    log_message("Jami eslatma yuboriladigan navbatlar: " . count($appointments), $log_file);
    
    $sent_count = 0;
    $failed_count = 0;
    
    foreach ($appointments as $appointment) {
        try {
            $appointment_date = new DateTime($appointment['appointment_date']);
            $appointment_time = new DateTime($appointment['start_time']);
            
            $appointment_date_str = $appointment_date->format('d.m.Y');
            $appointment_time_str = $appointment_time->format('H:i');
            
            // Email matni
            $email_subject = "Navbat eslatmasi - {$appointment['doctor_name']}";
            $email_body = "
                <html>
                <head>
                    <style>
                        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background: #0071e3; color: white; padding: 20px; border-radius: 12px 12px 0 0; }
                        .content { background: #f5f5f7; padding: 30px; border-radius: 0 0 12px 12px; }
                        .info-box { background: white; padding: 20px; border-radius: 12px; margin: 20px 0; }
                        .info-row { margin: 10px 0; }
                        .label { color: #86868b; font-size: 14px; }
                        .value { color: #1d1d1f; font-size: 16px; font-weight: 600; }
                        .footer { text-align: center; margin-top: 30px; color: #86868b; font-size: 14px; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h2 style='margin: 0;'>🏥 Navbat Eslatmasi</h2>
                        </div>
                        <div class='content'>
                            <p>Hurmatli {$appointment['patient_name']},</p>
                            <p>Sizning navbatingiz yaqinlashmoqda. Iltimos, vaqtida kelishingizni so'raymiz.</p>
                            
                            <div class='info-box'>
                                <div class='info-row'>
                                    <div class='label'>Shifokor:</div>
                                    <div class='value'>{$appointment['doctor_name']}</div>
                                </div>
                                <div class='info-row'>
                                    <div class='label'>Mutaxassisligi:</div>
                                    <div class='value'>{$appointment['doctor_specialty']}</div>
                                </div>
                                <div class='info-row'>
                                    <div class='label'>Sana:</div>
                                    <div class='value'>{$appointment_date_str}</div>
                                </div>
                                <div class='info-row'>
                                    <div class='label'>Vaqt:</div>
                                    <div class='value'>{$appointment_time_str}</div>
                                </div>
                                <div class='info-row'>
                                    <div class='label'>Xizmat:</div>
                                    <div class='value'>" . ($appointment['service_name'] ?? 'Ko'rik') . "</div>
                                </div>
                            </div>
                            
                            <p>Iltimos, qabulga 10-15 daqiqa oldin yetib boring.</p>
                            
                            <div class='footer'>
                                <p>{$contacts['clinic_name'] ?? 'Klinika'}</p>
                                <p>Tel: {$contacts['phone_1'] ?? ''}</p>
                                <p>Manzil: {$contacts['address'] ?? ''}</p>
                            </div>
                        </div>
                    </div>
                </body>
                </html>
            ";
            
            // Telegram xabar matni
            $telegram_message = "
🏥 *Navbat Eslatmasi*

Hurmatli {$appointment['patient_name']},

Sizning navbatingiz yaqinlashmoqda!

👨‍⚕️ *Shifokor:* {$appointment['doctor_name']}
📋 *Mutaxassisligi:* {$appointment['doctor_specialty']}
📅 *Sana:* {$appointment_date_str}
⏰ *Vaqt:* {$appointment_time_str}
💼 *Xizmat:* " . ($appointment['service_name'] ?? 'Ko\'rik') . "

Iltimos, qabulga 10-15 daqiqa oldin yetib boring.

📞 Aloqa: {$contacts['phone_1'] ?? ''}
📍 Manzil: {$contacts['address'] ?? ''}
            ";
            
            // Email yuborish
            $email_sent = false;
            if (!empty($appointment['patient_email'])) {
                $email_sent = sendEmail(
                    $appointment['patient_email'],
                    $email_subject,
                    $email_body,
                    $settings
                );
                
                if ($email_sent) {
                    log_message("✅ Email yuborildi: {$appointment['patient_email']}", $log_file);
                } else {
                    log_message("❌ Email xato: {$appointment['patient_email']}", $log_file);
                }
            }
            
            // Telegram yuborish
            $telegram_sent = false;
            $telegram_chat_id = getTelegramChatId($db, $appointment['user_id']);
            
            if ($telegram_chat_id && !empty($settings['telegram_bot_token'])) {
                $telegram_sent = sendTelegramMessage(
                    $telegram_chat_id,
                    $telegram_message,
                    $settings['telegram_bot_token']
                );
                
                if ($telegram_sent) {
                    log_message("✅ Telegram yuborildi: ID {$telegram_chat_id}", $log_file);
                } else {
                    log_message("❌ Telegram xato: ID {$telegram_chat_id}", $log_file);
                }
            }
            
            // Agar kamida biri yuborilgan bo'lsa, reminder_sent ni yangilash
            if ($email_sent || $telegram_sent) {
                $update_stmt = $db->prepare("UPDATE appointments SET reminder_sent = 1 WHERE id = ?");
                $update_stmt->execute([$appointment['id']]);
                $sent_count++;
            } else {
                $failed_count++;
            }
            
        } catch (Exception $e) {
            log_message("❌ Xatolik (ID: {$appointment['id']}): " . $e->getMessage(), $log_file);
            $failed_count++;
        }
    }
    
    log_message("=== Yakun ===", $log_file);
    log_message("✅ Muvaffaqiyatli: {$sent_count}", $log_file);
    log_message("❌ Xatoliklar: {$failed_count}", $log_file);
    
} catch (PDOException $e) {
    log_message("❌ DB Xatolik: " . $e->getMessage(), $log_file);
    exit(1);
} catch (Exception $e) {
    log_message("❌ Umumiy xatolik: " . $e->getMessage(), $log_file);
    exit(1);
}

log_message("=== Skript tugadi ===", $log_file);
exit(0);
