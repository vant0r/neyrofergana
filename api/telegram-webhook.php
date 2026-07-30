<?php
/**
 * API: Telegram Bot Webhook
 * Telegram'dan kelgan update'larni qabul qiladi va qayta ishlaydi
 */

header('Content-Type: application/json');

// Konfiguratsiya
require_once __DIR__ . '/../includes/config.php';

// Telegram bot tokenini olish
$settings_file = __DIR__ . '/../data/settings.json';
if (!file_exists($settings_file)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Sozlamalar fayli topilmadi']);
    exit;
}

$settings = json_decode(file_get_contents($settings_file), true);
$bot_token = $settings['telegram']['bot_token'] ?? '';

if (empty($bot_token)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Telegram bot token sozlanmagan']);
    exit;
}

// Faqat POST so'rovlar uchun
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Faqat POST so\'rovlari qabul qilinadi']);
    exit;
}

// Telegram'dan kelgan ma'lumotlarni olish
$input = file_get_contents('php://input');
$update = json_decode($input, true);

if (!$update) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Noto\'g\'ri JSON format']);
    exit;
}

// Log yozish (debug uchun)
error_log("Telegram Webhook Update: " . json_encode($update));

try {
    $pdo = getDBConnection();
    
    // Update turini aniqlash
    if (isset($update['message'])) {
        $message = $update['message'];
        $chat_id = $message['chat']['id'];
        $user_id = $message['from']['id'] ?? null;
        $username = $message['from']['username'] ?? null;
        $first_name = $message['from']['first_name'] ?? 'Foydalanuvchi';
        $text = $message['text'] ?? '';
        
        // Foydalanuvchini bazadan topish yoki yangi yaratish
        $stmt = $pdo->prepare("
            SELECT id FROM telegram_users 
            WHERE telegram_id = ?
        ");
        $stmt->execute([$user_id]);
        $tg_user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$tg_user) {
            // Yangi foydalanuvchi
            $stmt = $pdo->prepare("
                INSERT INTO telegram_users 
                (telegram_id, username, first_name, last_name, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $user_id,
                $username,
                $first_name,
                $message['from']['last_name'] ?? null
            ]);
            $tg_user_id = $pdo->lastInsertId();
        } else {
            $tg_user_id = $tg_user['id'];
            
            // Ma'lumotlarni yangilash
            $stmt = $pdo->prepare("
                UPDATE telegram_users 
                SET username = ?, first_name = ?, last_name = ?, updated_at = NOW()
                WHERE telegram_id = ?
            ");
            $stmt->execute([
                $username,
                $first_name,
                $message['from']['last_name'] ?? null,
                $user_id
            ]);
        }
        
        // Xabar matniga qarab javob berish
        if ($text === '/start') {
            // Asosiy menyu
            sendWelcomeMessage($bot_token, $chat_id, $first_name);
        } elseif ($text === '🗓 Navbatga yozilish' || $text === '/appointment') {
            // Web App ochish
            sendWebAppButton($bot_token, $chat_id, 'appointment');
        } elseif ($text === '📋 Mening navbatlarim' || $text === '/appointments') {
            // Web App ochish
            sendWebAppButton($bot_token, $chat_id, 'appointments');
        } elseif ($text === '🧪 Test natijalari' || $text === '/tests') {
            // Web App ochish
            sendWebAppButton($bot_token, $chat_id, 'tests');
        } elseif ($text === '👤 Profil' || $text === '/profile') {
            // Web App ochish
            sendWebAppButton($bot_token, $chat_id, 'profile');
        } elseif ($text === '📞 Aloqa' || $text === '/contact') {
            // Kontakt ma'lumotlarini yuborish
            sendContactInfo($bot_token, $chat_id);
        } elseif ($text === '🏥 Klinika haqida' || $text === '/about') {
            // Klinika haqida ma'lumot
            sendAboutClinic($bot_token, $chat_id);
        } else {
            // Noma'lum buyruq
            sendUnknownCommand($bot_token, $chat_id);
        }
        
    } elseif (isset($update['callback_query'])) {
        // Inline tugma bosildi
        $callback = $update['callback_query'];
        $chat_id = $callback['message']['chat']['id'];
        $data = $callback['data'] ?? '';
        
        handleCallbackQuery($pdo, $bot_token, $chat_id, $data);
    }
    
    echo json_encode(['status' => 'success', 'message' => 'Update qabul qilindi']);
    
} catch (PDOException $e) {
    error_log("Telegram Webhook Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Server xatolik yuz berdi'
    ]);
}

/**
 * Asosiy menyu yuborish
 */
function sendWelcomeMessage($bot_token, $chat_id, $first_name) {
    $url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
    
    $keyboard = json_encode([
        'keyboard' => [
            [['text' => '🗓 Navbatga yozilish', 'callback_data' => 'appointment']],
            [['text' => '📋 Mening navbatlarim', 'callback_data' => 'appointments']],
            [['text' => '🧪 Test natijalari', 'callback_data' => 'tests']],
            [['text' => '👤 Profil', 'callback_data' => 'profile']],
            [['text' => '📞 Aloqa', 'callback_data' => 'contact'], ['text' => '🏥 Klinika haqida', 'callback_data' => 'about']]
        ],
        'resize_keyboard' => true,
        'one_time_keyboard' => false
    ]);
    
    $data = [
        'chat_id' => $chat_id,
        'text' => "Assalomu alaykum, {$first_name}! 👋\n\nTibbiy Klinikamizga xush kelibsiz!\n\nQuyidagi tugmalardan birini tanlang:",
        'reply_markup' => $keyboard
    ];
    
    sendTelegramRequest($url, $data);
}

/**
 * Web App tugmasini yuborish
 */
function sendWebAppButton($bot_token, $chat_id, $page) {
    $url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
    
    $webapp_url = BASE_URL . "/webapp/{$page}.php";
    
    $keyboard = json_encode([
        'inline_keyboard' => [[
            [
                'text' => '➡️ Ochish',
                'web_app' => ['url' => $webapp_url]
            ]
        ]]
    ]);
    
    $data = [
        'chat_id' => $chat_id,
        'text' => 'Web App ochilmoqda...',
        'reply_markup' => $keyboard
    ];
    
    sendTelegramRequest($url, $data);
}

/**
 * Kontakt ma'lumotlarini yuborish
 */
function sendContactInfo($bot_token, $chat_id) {
    $contacts_file = __DIR__ . '/../data/contacts.json';
    $contacts = file_exists($contacts_file) ? json_decode(file_get_contents($contacts_file), true) : [];
    
    $text = "📞 **Aloqa ma'lumotlari**:\n\n";
    $text .= "📍 Manzil: " . ($contacts['address'] ?? 'Ma\'lum emas') . "\n";
    $text .= "📱 Telefon: " . ($contacts['phone'] ?? 'Ma\'lum emas') . "\n";
    $text .= "📧 Email: " . ($contacts['email'] ?? 'Ma\'lum emas') . "\n";
    $text .= "🕐 Ish vaqti: " . ($contacts['work_hours'] ?? 'Ma\'lum emas');
    
    $url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
    $data = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => 'Markdown'
    ];
    
    sendTelegramRequest($url, $data);
}

/**
 * Klinika haqida ma'lumot
 */
function sendAboutClinic($bot_token, $chat_id) {
    $url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
    
    $text = "🏥 **Klinikamiz haqida**:\n\n";
    $text .= "Biz zamonaviy tibbiyot xizmatlari ko\'rsatuvchi klinikamiz.\n\n";
    $text .= "✅ Malakali shifokorlar\n";
    $text .= "✅ Zamonaviy uskunalar\n";
    $text .= "✅ Qulay sharoitlar\n\n";
    $text .= "Batafsil ma\'lumot uchun veb-saytimizga tashrif buyuring.";
    
    $data = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => 'Markdown'
    ];
    
    sendTelegramRequest($url, $data);
}

/**
 * Noma'lum buyruq
 */
function sendUnknownCommand($bot_token, $chat_id) {
    $url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
    
    $keyboard = json_encode([
        'keyboard' => [
            [['text' => '🗓 Navbatga yozilish']],
            [['text' => '📋 Mening navbatlarim']],
            [['text' => '🧪 Test natijalari']],
            [['text' => '👤 Profil']],
            [['text' => '📞 Aloqa']]
        ],
        'resize_keyboard' => true
    ]);
    
    $data = [
        'chat_id' => $chat_id,
        'text' => "Noto'g'ri buyruq. Quyidagi tugmalardan foydalaning:",
        'reply_markup' => $keyboard
    ];
    
    sendTelegramRequest($url, $data);
}

/**
 * Callback query handler
 */
function handleCallbackQuery($pdo, $bot_token, $chat_id, $data) {
    // Callback ma'lumotlarini qayta ishlash
    switch ($data) {
        case 'appointment':
        case 'appointments':
        case 'tests':
        case 'profile':
        case 'contact':
        case 'about':
            sendWebAppButton($bot_token, $chat_id, $data);
            break;
    }
}

/**
 * Telegram API ga so'rov yuborish
 */
function sendTelegramRequest($url, $data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200) {
        error_log("Telegram API Error: HTTP {$http_code}, Response: {$result}");
    }
    
    return $result;
}
