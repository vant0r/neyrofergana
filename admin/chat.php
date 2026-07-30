<?php
/**
 * admin/chat.php - Live Chat (Admin tomoni)
 * Markazlashtirilgan suhbat boshqaruvi
 */

require_once '../includes/config.php';
require_once '../includes/init.php';
require_once '../includes/auth.php';

// Faqat adminlar kirishi mumkin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../kirish-royxatdan-otish.php');
    exit;
}

$success_message = '';
$error_message = '';

// Chat ID olish
$chat_id = isset($_GET['chat_id']) ? (int)$_GET['chat_id'] : 0;

// Barcha chatlarni olish
try {
    $stmt = $pdo->prepare("
        SELECT c.*, 
               u.full_name as user_name,
               u.avatar,
               (SELECT COUNT(*) FROM chat_messages WHERE chat_id = c.id AND is_read = 0 AND sender = 'user') as unread_count,
               (SELECT message FROM chat_messages WHERE chat_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message,
               (SELECT created_at FROM chat_messages WHERE chat_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message_time
        FROM chats c
        LEFT JOIN users u ON c.user_id = u.id
        ORDER BY last_message_time DESC
    ");
    $stmt->execute();
    $chats = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Chatlarni yuklashda xatolik: " . $e->getMessage();
    $chats = [];
}

// Tanlangan chat ma'lumotlari
$current_chat = null;
$messages = [];
if ($chat_id > 0) {
    try {
        $stmt = $pdo->prepare("
            SELECT c.*, u.full_name as user_name, u.avatar, u.phone, u.email
            FROM chats c
            LEFT JOIN users u ON c.user_id = u.id
            WHERE c.id = ?
        ");
        $stmt->execute([$chat_id]);
        $current_chat = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($current_chat) {
            // Xabarlarni olish
            $stmt = $pdo->prepare("
                SELECT * FROM chat_messages 
                WHERE chat_id = ? 
                ORDER BY created_at ASC
            ");
            $stmt->execute([$chat_id]);
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // O'qilmagan xabarlarni o'qilgan deb belgilash
            $stmt = $pdo->prepare("
                UPDATE chat_messages 
                SET is_read = 1 
                WHERE chat_id = ? AND is_read = 0 AND sender = 'user'
            ");
            $stmt->execute([$chat_id]);
        }
    } catch (PDOException $e) {
        $error_message = "Chat ma'lumotlarini yuklashda xatolik";
    }
}

// Xabar yuborish
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error_message = "Xavfsizlik tokeni noto'g'ri";
    } else {
        $message_text = trim($_POST['message'] ?? '');
        $chat_id_post = (int)($_POST['chat_id'] ?? 0);
        
        if (!empty($message_text) && $chat_id_post > 0) {
            try {
                // Fayl yuklash (agar bor bo'lsa)
                $file_url = null;
                if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                    require_once '../includes/upload.php';
                    $upload_result = upload_file($_FILES['file'], 'chat');
                    if ($upload_result['success']) {
                        $file_url = $upload_result['file_url'];
                    }
                }
                
                // Xabarni saqlash
                $stmt = $pdo->prepare("
                    INSERT INTO chat_messages (chat_id, sender, message, file_url, is_read)
                    VALUES (?, 'admin', ?, ?, 0)
                ");
                $stmt->execute([$chat_id_post, $message_text, $file_url]);
                
                // Telegram orqali xabar yuborish (ixtiyoriy)
                $settings = get_settings();
                if (!empty($settings['telegram_bot_token'])) {
                    $stmt = $pdo->prepare("SELECT telegram_id FROM users WHERE id = (SELECT user_id FROM chats WHERE id = ?)");
                    $stmt->execute([$chat_id_post]);
                    $telegram_id = $stmt->fetchColumn();
                    
                    if ($telegram_id) {
                        require_once '../includes/telegram.php';
                        send_telegram_message($telegram_id, $message_text);
                    }
                }
                
                $success_message = "Xabar yuborildi";
                header("Location: chat.php?chat_id=$chat_id_post&success=1");
                exit;
            } catch (PDOException $e) {
                $error_message = "Xabarni yuborishda xatolik";
            }
        } else {
            $error_message = "Xabar matni bo'sh bo'lishi mumkin emas";
        }
    }
}

$page_title = "Live Chat - Admin Panel";
include '../includes/admin-header.php';
?>

<style>
.chat-container {
    display: grid;
    grid-template-columns: 350px 1fr;
    gap: 20px;
    height: calc(100vh - 180px);
    min-height: 600px;
}

@media (max-width: 1024px) {
    .chat-container {
        grid-template-columns: 1fr;
        height: auto;
    }
    
    .chat-list {
        max-height: 300px;
        overflow-y: auto;
    }
}

.chat-list {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-radius: 16px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.chat-list-header {
    padding: 20px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    background: rgba(255, 255, 255, 0.5);
}

.chat-list-header h3 {
    margin: 0;
    color: #1d1d1f;
    font-size: 18px;
    font-weight: 600;
}

.chat-items {
    overflow-y: auto;
    max-height: calc(100vh - 280px);
}

.chat-item {
    padding: 15px 20px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 12px;
}

.chat-item:hover {
    background: rgba(0, 113, 227, 0.1);
}

.chat-item.active {
    background: rgba(0, 113, 227, 0.15);
    border-left: 4px solid #0071e3;
}

.chat-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    object-fit: cover;
    background: #e5e5ea;
    flex-shrink: 0;
}

.chat-info {
    flex: 1;
    min-width: 0;
}

.chat-name {
    font-weight: 600;
    color: #1d1d1f;
    margin-bottom: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.chat-preview {
    color: #86868b;
    font-size: 14px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.chat-meta {
    text-align: right;
    flex-shrink: 0;
}

.chat-time {
    font-size: 12px;
    color: #86868b;
    margin-bottom: 4px;
}

.unread-badge {
    background: #ff3b30;
    color: white;
    font-size: 11px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 10px;
    display: inline-block;
}

.chat-window {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-radius: 16px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.chat-window-header {
    padding: 20px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    background: rgba(255, 255, 255, 0.5);
    display: flex;
    align-items: center;
    gap: 15px;
}

.chat-user-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.chat-user-details h3 {
    margin: 0;
    color: #1d1d1f;
    font-size: 18px;
    font-weight: 600;
}

.chat-user-details p {
    margin: 0;
    color: #86868b;
    font-size: 14px;
}

.messages-container {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.message {
    display: flex;
    gap: 10px;
    max-width: 70%;
}

.message.admin {
    align-self: flex-end;
    flex-direction: row-reverse;
}

.message-avatar {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}

.message-content {
    background: rgba(255, 255, 255, 0.9);
    padding: 12px 16px;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.message.admin .message-content {
    background: #0071e3;
    color: white;
}

.message-text {
    margin: 0;
    line-height: 1.5;
    word-wrap: break-word;
}

.message-file {
    margin-top: 8px;
}

.message-file a {
    color: inherit;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    font-size: 13px;
}

.message.admin .message-file a {
    background: rgba(255, 255, 255, 0.2);
}

.message-time {
    font-size: 11px;
    opacity: 0.7;
    margin-top: 4px;
    text-align: right;
}

.message-input-area {
    padding: 20px;
    border-top: 1px solid rgba(0, 0, 0, 0.1);
    background: rgba(255, 255, 255, 0.5);
}

.message-form {
    display: flex;
    gap: 10px;
    align-items: flex-end;
}

.file-upload-btn {
    padding: 12px;
    background: rgba(0, 113, 227, 0.1);
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.file-upload-btn:hover {
    background: rgba(0, 113, 227, 0.2);
}

.file-upload-btn svg {
    width: 24px;
    height: 24px;
    fill: #0071e3;
}

.message-input-wrapper {
    flex: 1;
}

.message-input {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid rgba(0, 0, 0, 0.1);
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.9);
    font-size: 15px;
    resize: none;
    font-family: inherit;
    transition: all 0.3s ease;
}

.message-input:focus {
    outline: none;
    border-color: #0071e3;
    box-shadow: 0 0 0 3px rgba(0, 113, 227, 0.1);
}

.send-btn {
    padding: 12px 24px;
    background: #0071e3;
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.send-btn:hover {
    background: #0077ed;
    transform: translateY(-2px);
}

.send-btn:active {
    transform: translateY(0);
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #86868b;
    text-align: center;
    padding: 40px;
}

.empty-state svg {
    width: 80px;
    height: 80px;
    fill: #d1d1d6;
    margin-bottom: 20px;
}

.empty-state h3 {
    margin: 0 0 10px 0;
    color: #1d1d1f;
}

.no-chat-selected {
    background: rgba(255, 255, 255, 0.5);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
}
</style>

<div class="page-header">
    <h1>💬 Live Chat</h1>
    <p>Bemorlar bilan muloqot qilish</p>
</div>

<?php if ($success_message): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
<?php endif; ?>

<?php if ($error_message): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error_message) ?></div>
<?php endif; ?>

<div class="chat-container">
    <!-- Chatlar ro'yxati -->
    <div class="chat-list">
        <div class="chat-list-header">
            <h3>Suhbatlar</h3>
        </div>
        <div class="chat-items">
            <?php if (empty($chats)): ?>
                <div class="empty-state">
                    <p>Hech qanday suhbat yo'q</p>
                </div>
            <?php else: ?>
                <?php foreach ($chats as $chat): ?>
                    <div class="chat-item <?= $chat['id'] == $chat_id ? 'active' : '' ?>" 
                         onclick="window.location.href='chat.php?chat_id=<?= $chat['id'] ?>'">
                        <img src="<?= !empty($chat['avatar']) ? htmlspecialchars('../' . $chat['avatar']) : '../uploads/avatars/default.png' ?>" 
                             alt="Avatar" 
                             class="chat-avatar"
                             onerror="this.src='../uploads/avatars/default.png'">
                        <div class="chat-info">
                            <div class="chat-name"><?= htmlspecialchars($chat['user_name'] ?? 'Noma\'lum') ?></div>
                            <div class="chat-preview"><?= htmlspecialchars(mb_substr($chat['last_message'] ?? '', 0, 50)) ?></div>
                        </div>
                        <div class="chat-meta">
                            <div class="chat-time"><?= date('H:i', strtotime($chat['last_message_time'])) ?></div>
                            <?php if ($chat['unread_count'] > 0): ?>
                                <span class="unread-badge"><?= $chat['unread_count'] ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Chat oynasi -->
    <div class="chat-window">
        <?php if ($current_chat): ?>
            <div class="chat-window-header">
                <img src="<?= !empty($current_chat['avatar']) ? htmlspecialchars('../' . $current_chat['avatar']) : '../uploads/avatars/default.png' ?>" 
                     alt="Avatar" 
                     class="chat-avatar"
                     onerror="this.src='../uploads/avatars/default.png'">
                <div class="chat-user-details">
                    <h3><?= htmlspecialchars($current_chat['user_name']) ?></h3>
                    <p><?= htmlspecialchars($current_chat['phone']) ?> • <?= htmlspecialchars($current_chat['email']) ?></p>
                </div>
            </div>

            <div class="messages-container" id="messagesContainer">
                <?php foreach ($messages as $msg): ?>
                    <div class="message <?= $msg['sender'] ?>">
                        <img src="<?= $msg['sender'] === 'admin' ? '../uploads/avatars/admin.png' : (!empty($current_chat['avatar']) ? '../' . $current_chat['avatar'] : '../uploads/avatars/default.png') ?>" 
                             alt="Avatar" 
                             class="message-avatar"
                             onerror="this.src='../uploads/avatars/default.png'">
                        <div class="message-content">
                            <?php if (!empty($msg['message'])): ?>
                                <p class="message-text"><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                            <?php endif; ?>
                            
                            <?php if (!empty($msg['file_url'])): ?>
                                <div class="message-file">
                                    <a href="../<?= htmlspecialchars($msg['file_url']) ?>" target="_blank" download>
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                                        </svg>
                                        Faylni yuklab olish
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <div class="message-time"><?= date('H:i', strtotime($msg['created_at'])) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="message-input-area">
                <form method="POST" enctype="multipart/form-data" class="message-form">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="chat_id" value="<?= $chat_id ?>">
                    
                    <label class="file-upload-btn" title="Fayl yuklash">
                        <input type="file" name="file" style="display: none;" accept="image/*,.pdf,.doc,.docx">
                        <svg viewBox="0 0 24 24">
                            <path d="M16.5 6v11.5c0 2.21-1.79 4-4 4s-4-1.79-4-4V5c0-1.38 1.12-2.5 2.5-2.5s2.5 1.12 2.5 2.5v10.5c0 .55-.45 1-1 1s-1-.45-1-1V6H10v9.5c0 1.38 1.12 2.5 2.5 2.5s2.5-1.12 2.5-2.5V5c0-2.21-1.79-4-4-4S7 2.79 7 5v12.5c0 3.04 2.46 5.5 5.5 5.5s5.5-2.46 5.5-5.5V6h-1.5z"/>
                        </svg>
                    </label>
                    
                    <div class="message-input-wrapper">
                        <textarea name="message" class="message-input" rows="1" placeholder="Xabar yozing..." required></textarea>
                    </div>
                    
                    <button type="submit" name="send_message" class="send-btn">Yuborish</button>
                </form>
            </div>
        <?php else: ?>
            <div class="no-chat-selected">
                <div class="empty-state">
                    <svg viewBox="0 0 24 24">
                        <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/>
                    </svg>
                    <h3>Suhbatni tanlang</h3>
                    <p>Chap tarafdagi ro'yxatdan bemorni tanlang</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Xabarlar konteynerini pastga skroll qilish
const messagesContainer = document.getElementById('messagesContainer');
if (messagesContainer) {
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

// Enter tugmasi bilan xabar yuborish (Shift+Enter yangi qator)
const messageInput = document.querySelector('.message-input');
if (messageInput) {
    messageInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            this.form.submit();
        }
    });
    
    // Avtomatik balandlik
    this.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });
}

// Har 5 soniyada yangilanish (agar chat tanlangan bo'lsa)
<?php if ($chat_id > 0): ?>
setInterval(function() {
    fetch('api/chat-poll.php?chat_id=<?= $chat_id ?>&last_id=<?= end($messages)['id'] ?? 0 ?>')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.messages.length > 0) {
                location.reload();
            }
        })
        .catch(error => console.error('Polling xatoligi:', error));
}, 5000);
<?php endif; ?>
</script>

<?php include '../includes/admin-footer.php'; ?>
