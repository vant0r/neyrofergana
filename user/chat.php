<?php
/**
 * user/chat.php - Bemor uchun Live Chat
 */
require_once '../includes/config.php';
require_once '../includes/init.php';
require_once '../includes/auth.php';

checkAuth();
checkRole('user');

$user_id = $_SESSION['user_id'];

// Foydalanuvchi chat ID sini olish yoki yangi yaratish
try {
    $stmt = $pdo->prepare("SELECT id FROM chats WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $chat = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$chat) {
        // Yangi chat yaratish
        $stmt = $pdo->prepare("INSERT INTO chats (user_id, status, created_at) VALUES (?, 'active', NOW())");
        $stmt->execute([$user_id]);
        $chat_id = $pdo->lastInsertId();
    } else {
        $chat_id = $chat['id'];
    }
} catch (PDOException $e) {
    error_log("Chat init error: " . $e->getMessage());
    $chat_id = null;
}

$page_title = "Qo'llab-quvvatlash xizmati";
include '../includes/user-header.php';
?>

<div class="user-container" style="max-width: 900px;">
    <div class="page-header">
        <h1>💬 Qo'llab-quvvatlash xizmati</h1>
        <p>Savollaringiz bormi? Biz bilan bog'laning</p>
    </div>

    <!-- Chat holati -->
    <div class="chat-status glass-card" style="margin-bottom: 20px; padding: 15px 20px; display: flex; align-items: center; gap: 10px;">
        <span class="status-dot" style="width: 10px; height: 10px; background: #34c759; border-radius: 50%; display: inline-block;"></span>
        <span style="color: #1d1d1f; font-weight: 500;">Operator onlayn</span>
        <span style="color: #86868b; margin-left: auto; font-size: 14px;">Javob vaqti: ~5 daqiqa</span>
    </div>

    <!-- Chat oynasi -->
    <div class="glass-card" style="padding: 0; overflow: hidden;">
        <!-- Xabarlar maydoni -->
        <div id="chatMessages" class="chat-messages" style="height: 500px; overflow-y: auto; padding: 20px; background: rgba(255,255,255,0.3);">
            <div class="typing-indicator" id="typingIndicator" style="display: none; padding: 10px 0;">
                <span style="color: #86868b; font-style: italic;">Operator yozmoqda...</span>
            </div>
        </div>

        <!-- Fayl yuklash hududi -->
        <div id="filePreview" style="display: none; padding: 10px 20px; background: rgba(0,113,227,0.05); border-top: 1px solid rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 20px;">📎</span>
                    <span id="fileName" style="color: #1d1d1f;"></span>
                </div>
                <button onclick="removeFile()" class="btn btn-sm btn-icon" style="background: rgba(255,59,48,0.1);">✕</button>
            </div>
        </div>

        <!-- Xabar yozish formasi -->
        <form id="chatForm" class="chat-form" style="padding: 20px; background: rgba(255,255,255,0.5); border-top: 1px solid rgba(0,0,0,0.1);">
            <input type="hidden" name="chat_id" value="<?= $chat_id ?>">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            
            <div style="display: flex; gap: 10px; align-items: flex-end;">
                <!-- Fayl yuklash tugmasi -->
                <label for="fileUpload" class="btn btn-icon" title="Fayl yuklash" style="cursor: pointer; margin-bottom: 0;">
                    📎
                    <input type="file" id="fileUpload" name="file" accept="image/*,.pdf" style="display: none;" onchange="handleFileSelect(this)">
                </label>
                
                <!-- Xabar inputi -->
                <textarea 
                    id="messageInput" 
                    name="message" 
                    rows="1" 
                    placeholder="Xabaringizni yozing..." 
                    style="flex: 1; padding: 12px 16px; border: 1px solid rgba(0,0,0,0.1); border-radius: 12px; background: rgba(255,255,255,0.8); resize: none; max-height: 120px; font-size: 16px;"
                    onkeydown="if(event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); sendMessage(); }"
                ></textarea>
                
                <!-- Yuborish tugmasi -->
                <button type="submit" class="btn btn-primary" style="padding: 12px 24px; border-radius: 12px;">
                    ➤
                </button>
            </div>
            
            <div style="margin-top: 10px; font-size: 12px; color: #86868b;">
                💡 Maslahat: Fayl yuklash uchun 📎 tugmasini bosing. Rasm va PDF formatlari qo'llab-quvvatlanadi.
            </div>
        </form>
    </div>

    <!-- Tezkor savollar -->
    <div class="glass-card" style="margin-top: 20px;">
        <h3 style="margin-bottom: 15px;">⚡ Tezkor savollar</h3>
        <div style="display: flex; flex-wrap: wrap; gap: 10px;">
            <button onclick="sendQuickMessage('Navbatga qanday yozilaman?')" class="btn btn-sm btn-outline">📅 Navbatga yozilish</button>
            <button onclick="sendQuickMessage('Test natijalarim qachon tayyor bo\'ladi?')" class="btn btn-sm btn-outline">🧪 Test natijalari</button>
            <button onclick="sendQuickMessage('Qabul vaqtimni qanday o\'zgartirsam bo\'ladi?')" class="btn btn-sm btn-outline">🔄 Vaqtni o'zgartirish</button>
            <button onclick="sendQuickMessage('To\'lov usullari qanday?')" class="btn btn-sm btn-outline">💰 To'lov usullari</button>
            <button onclick="sendQuickMessage('Ish vaqtingiz qachon?')" class="btn btn-sm btn-outline">🕐 Ish vaqti</button>
        </div>
    </div>
</div>

<style>
.chat-messages {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.message {
    max-width: 70%;
    padding: 12px 16px;
    border-radius: 12px;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.message.user {
    align-self: flex-end;
    background: #0071e3;
    color: white;
    border-bottom-right-radius: 4px;
}

.message.admin {
    align-self: flex-start;
    background: rgba(255,255,255,0.8);
    color: #1d1d1f;
    border-bottom-left-radius: 4px;
}

.message-time {
    font-size: 11px;
    opacity: 0.7;
    margin-top: 5px;
    text-align: right;
}

.message-file {
    margin-top: 10px;
    padding: 10px;
    background: rgba(255,255,255,0.2);
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.message-file img {
    max-width: 200px;
    max-height: 200px;
    border-radius: 8px;
    cursor: pointer;
}

.message-file a {
    color: inherit;
    text-decoration: underline;
    font-weight: 500;
}

.btn-icon {
    width: 40px;
    height: 40px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: rgba(0,113,227,0.1);
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 18px;
}

.btn-icon:hover {
    background: rgba(0,113,227,0.2);
    transform: scale(1.05);
}

.typing-indicator {
    padding: 10px 0;
}

/* Scrollbar dizayni */
.chat-messages::-webkit-scrollbar {
    width: 6px;
}

.chat-messages::-webkit-scrollbar-track {
    background: rgba(0,0,0,0.05);
}

.chat-messages::-webkit-scrollbar-thumb {
    background: rgba(0,0,0,0.2);
    border-radius: 3px;
}

.chat-messages::-webkit-scrollbar-thumb:hover {
    background: rgba(0,0,0,0.3);
}

@media (max-width: 768px) {
    .message {
        max-width: 85%;
    }
    
    .chat-messages {
        height: 400px;
    }
}
</style>

<script>
let chatId = <?= $chat_id ? $chat_id : 'null' ?>;
let lastMessageId = 0;
let selectedFile = null;
let pollingInterval = null;

// Chat xabarlarni yuklash
function loadMessages() {
    if (!chatId) return;
    
    fetch('../api/chat-poll.php?chat_id=' + chatId + '&last_id=' + lastMessageId)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.messages.length > 0) {
                const messagesContainer = document.getElementById('chatMessages');
                const typingIndicator = document.getElementById('typingIndicator');
                
                data.messages.forEach(msg => {
                    const messageDiv = document.createElement('div');
                    messageDiv.className = `message ${msg.sender}`;
                    
                    let content = `<div>${escapeHtml(msg.message)}</div>`;
                    
                    if (msg.file) {
                        if (msg.file_type && msg.file_type.startsWith('image/')) {
                            content += `<div class="message-file"><img src="${msg.file}" onclick="openImage(this.src)" alt="Yuklangan rasm"></div>`;
                        } else {
                            content += `<div class="message-file">📄 <a href="${msg.file}" target="_blank">Faylni yuklab olish</a></div>`;
                        }
                    }
                    
                    content += `<div class="message-time">${formatTime(msg.created_at)}</div>`;
                    
                    messageDiv.innerHTML = content;
                    messagesContainer.insertBefore(messageDiv, typingIndicator);
                    
                    lastMessageId = Math.max(lastMessageId, msg.id);
                });
                
                // Eng pastga skroll
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
                
                // Bildirishnoma
                if (data.messages.length > 0 && data.messages[0].sender === 'admin') {
                    playNotificationSound();
                }
            }
        })
        .catch(err => console.error('Load messages error:', err));
}

// Xabar yuborish
function sendMessage(file = null) {
    const messageInput = document.getElementById('messageInput');
    const message = messageInput.value.trim();
    
    if (!message && !file) return;
    
    const formData = new FormData();
    formData.append('chat_id', chatId);
    formData.append('message', message);
    formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
    
    if (file) {
        formData.append('file', file);
    }
    
    fetch('../api/chat-send.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            messageInput.value = '';
            selectedFile = null;
            document.getElementById('filePreview').style.display = 'none';
            loadMessages();
        } else {
            showToast(data.message || 'Xatolik yuz berdi', 'error');
        }
    })
    .catch(err => {
        console.error('Send message error:', err);
        showToast('Xatolik yuz berdi', 'error');
    });
}

// Fayl tanlanganda
function handleFileSelect(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Hajm tekshiruvi (10MB)
        if (file.size > 10 * 1024 * 1024) {
            showToast('Fayl hajmi 10MB dan oshmasligi kerak', 'error');
            input.value = '';
            return;
        }
        
        selectedFile = file;
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('filePreview').style.display = 'block';
    }
}

// Faylni olib tashlash
function removeFile() {
    selectedFile = null;
    document.getElementById('fileUpload').value = '';
    document.getElementById('filePreview').style.display = 'none';
}

// Tezkor xabar yuborish
function sendQuickMessage(text) {
    document.getElementById('messageInput').value = text;
    sendMessage();
}

// Vaqtni formatlash
function formatTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleTimeString('uz-UZ', { hour: '2-digit', minute: '2-digit' });
}

// HTML escape
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Bildirishnoma ovozi
function playNotificationSound() {
    // Oddiy beep sound (browser restrictions tufayli faqat user interaction dan keyin ishlaydi)
    try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        
        oscillator.frequency.value = 800;
        oscillator.type = 'sine';
        gainNode.gain.value = 0.1;
        
        oscillator.start();
        setTimeout(() => oscillator.stop(), 150);
    } catch (e) {
        // Audio API mavjud emas
    }
}

// Rasmni ochish
function openImage(src) {
    const modal = document.createElement('div');
    modal.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); display: flex; align-items: center; justify-content: center; z-index: 10000; cursor: pointer;';
    modal.onclick = () => modal.remove();
    
    const img = document.createElement('img');
    img.src = src;
    img.style.cssText = 'max-width: 90%; max-height: 90%; object-fit: contain;';
    
    modal.appendChild(img);
    document.body.appendChild(modal);
}

// Formani yuborish
document.getElementById('chatForm').addEventListener('submit', function(e) {
    e.preventDefault();
    sendMessage(selectedFile);
});

// Polling boshlash
pollingInterval = setInterval(loadMessages, 5000);

// Sahifa yuklanganda dastlabki xabarlarni olish
loadMessages();

// Sahifadan chiqishda polling ni to'xtatish
window.addEventListener('beforeunload', () => {
    if (pollingInterval) {
        clearInterval(pollingInterval);
    }
});
</script>

<?php include '../includes/footer.php'; ?>
