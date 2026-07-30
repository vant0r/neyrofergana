# Tibbiy Klinika Veb-Ekotizimi

Zamonaviy tibbiyot klinikasi uchun to'liq veb-ekotizim: rasmiy sayt, bemor va admin panellari, Telegram bot va Web App integratsiyasi.

## 📋 Mundarija

- [Xususiyatlar](#-xususiyatlar)
- [Texnologiyalar](#-texnologiyalar)
- [Tuzilma](#-tuzilma)
- [O'rnatish](#-o'rnatish)
- [Konfiguratsiya](#-konfiguratsiya)
- [Xavfsizlik](#-xavfsizlik)
- [API](#-api)
- [Telegram Bot](#-telegram-bot)
- [License](#-license)

## ✨ Xususiyatlar

### Bemorlar uchun
- 🗓️ Navbatga onlayn yozilish
- 📋 Test natijalarini ko'rish va yuklab olish
- 💬 Live chat orqali qo'llab-quvvatlash
- 🔔 Email va Telegram bildirishnomalar
- 👤 Shaxsiy kabinet
- 📱 Telegram Web App

### Admin panel
- 📊 Dashboard va statistika
- 👨‍⚕️ Shifokorlarni boshqarish
- 🏥 Xizmatlar katalogi
- 📅 Navbatlar jadvali
- 📝 Bemorlar bazasi
- 📰 Yangiliklar va maqolalar
- 🖼️ Galereya boshqaruvi
- ⭐ Sharhlar moderatsiyasi
- 📢 Ommaviy bildirishnomalar
- ⚙️ To'liq sozlamalar (SMTP, Telegram, brending)

### Telegram integratsiyasi
- 🤖 Telegram Bot
- 📲 Web App (navbat olish, profil, testlar)
- 🔐 Majburiy kanal obunasi
- 📣 Broadcast xabarlar

## 🛠 Texnologiyalar

| Qatlam | Texnologiya |
|--------|-------------|
| **Back-end** | PHP 8.1+ (sof PHP, freymvorksiz) |
| **Database** | MySQL 5.7+ / MariaDB 10.3+ |
| **Front-end** | HTML5, CSS3, Vanilla JavaScript |
| **Styl** | Inline CSS (Glassmorphism, Apple style) |
| **Config** | JSON (data/*.json) |
| **Email** | Native mail() / SMTP socket |
| **Telegram** | Bot API (cURL/file_get_contents) |
| **Real-time** | AJAX Polling (3-5 soniya) |

### Qo'llanilmaydi
- ❌ Laravel, Symfony, CodeIgniter
- ❌ jQuery, Bootstrap, Tailwind
- ❌ React, Vue, Angular
- ❌ Tashqi CSS/JS fayllar
- ❌ WebSocket (faqat polling)

## 📁 Tuzilma

```
/
├── index.php                    # Bosh sahifa
├── haqimizda.php                # Klinika haqida
├── xizmatlar.php                # Xizmatlar va narxlar
├── shifokorlar.php              # Shifokorlar jamoasi
├── shifokor.php                 # Yakka shifokor (?id=)
├── yangiliklar.php              # Yangiliklar
├── yangilik.php                 # Yakka yangilik (?id=)
├── aloqa.php                    # Aloqa va manzil
├── savollar.php                 # FAQ
├── galereya.php                 # Fotogalereya
├── kirish-royxatdan-otish.php   # Avtorizatsiya/Ro'yxatdan o'tish
├── maxfiylik.php                # Maxfiylik siyosati
├── shartlar.php                 # Foydalanish shartlari
├── install.php                  # O'rnatuvchi
├── 404.php                      # 404 xatolik
├── 500.php                      # 500 xatolik
│
├── user/                        # Bemor paneli
│   ├── boshqaruv.php            # Dashboard
│   ├── navbatlar.php            # Navbatlar ro'yxati
│   ├── navbat-olish.php         # Yangi navbat olish
│   ├── testlar.php              # Test natijalari
│   ├── profil.php               # Profil
│   ├── sozlamalar.php           # Sozlamalar
│   ├── parol-ozgartirish.php    # Parol o'zgartirish
│   ├── fikrlar.php              # Sharh qoldirish
│   ├── hisobotlar.php           # To'lovlar tarixi
│   ├── bildirishnomalar.php     # Bildirishnomalar
│   └── chat.php                 # Live Chat
│
├── admin/                       # Admin panel
│   ├── boshqaruv.php            # Dashboard + statistika
│   ├── sozlamalar.php           # Umumiy sozlamalar
│   ├── reklama.php              # Reklama boshqaruvi
│   ├── xizmatlar.php            # Xizmatlar CRUD
│   ├── shifokorlar.php          # Shifokorlar CRUD
│   ├── shifokor-tahrirlash.php  # Shifokor qo'shish/tahrirlash
│   ├── navbatlar.php            # Navbatlar boshqaruvi
│   ├── bemorlar.php             # Bemorlar bazasi
│   ├── bemor.php                # Yakka bemor (?id=)
│   ├── test-natijalari.php      # Test natijalarini yuklash
│   ├── fikrlar.php              # Sharhlar moderatsiyasi
│   ├── yangiliklar.php          # Yangiliklar CRUD
│   ├── galereya.php             # Galereya boshqaruvi
│   ├── chat.php                 # Markazlashtirilgan Chat
│   ├── bildirishnomalar.php     # Ommaviy bildirishnomalar
│   ├── foydalanuvchilar.php     # Admin foydalanuvchilar (superadmin)
│   └── htaccess.php             # .htaccess boshqaruvi
│
├── webapp/                      # Telegram Web App
│   ├── index.php                # Web App bosh sahifasi
│   ├── navbatlar.php            # Mening navbatlarim
│   ├── testlar.php              # Test natijalari
│   ├── profil.php               # Profil
│   └── malumotlar.php           # Klinika ma'lumotlari
│
├── includes/                    # Umumiy modullar
│   ├── config.php               # DB ulanish + JSON sozlamalar
│   ├── init.php                 # Sessiya, xavfsizlik, autoload
│   ├── functions.php            # Yordamchi funksiyalar
│   ├── auth.php                 # Autentifikatsiya
│   ├── db.php                   # PDO wrapper
│   ├── mailer.php               # Email yuborish
│   ├── telegram.php             # Telegram Bot API
│   ├── chat.php                 # Live Chat funksiyalari
│   ├── upload.php               # Fayl yuklash
│   ├── validation.php           # Validatsiya
│   ├── security.php             # CSRF, XSS, SQL injection
│   ├── header.php               # Global header + CSS
│   ├── footer.php               # Global footer + JS
│   ├── user-header.php          # User panel header
│   ├── admin-header.php         # Admin panel header
│   ├── pagination.php           # Paginatsiya
│   ├── stats.php                # Statistika funksiyalari
│   └── install-functions.php    # O'rnatish yordamchilari
│
├── uploads/                     # Media fayllar
│   ├── logo/                    # Logotiplar
│   ├── favicon/                 # Favicon
│   ├── banners/                 # Bannerlar
│   ├── doctors/                 # Shifokor rasmlari
│   ├── gallery/                 # Galereya
│   ├── tests/                   # Test natijalari (PDF)
│   ├── avatars/                 # Avatarlar
│   ├── ads/                     # Reklama bannerlari
│   └── chat/                    # Chat fayllari
│
├── data/                        # JSON konfiguratsiyalar
│   ├── settings.json            # Umumiy sozlamalar
│   ├── contacts.json            # Aloqa ma'lumotlari
│   └── ads.json                 # Reklama sozlamalari
│
├── sql/                         # Ma'lumotlar bazasi
│   └── schema.sql               # To'liq DB sxemasi
│
├── cron/                        # Cron skriptlar
│   ├── send_reminders.php       # Navbat eslatmalari
│   └── cleanup_sessions.php     # Sessiyalarni tozalash
│
├── api/                         # API endpointlar
│   ├── chat-poll.php            # Chat xabarlarni olish
│   ├── chat-send.php            # Chat xabar yuborish
│   ├── get-time-slots.php       # Bo'sh vaqtlarni olish
│   ├── cancel-appointment.php   # Navbatni bekor qilish
│   ├── upload-file.php          # Fayl yuklash
│   └── telegram-webhook.php     # Telegram webhook
│
├── .htaccess                    # URL rewriting, xavfsizlik
└── robots.txt                   # SEO robots
```

## 🚀 O'rnatish

### Talablar
- PHP 8.1+
- MySQL 5.7+ yoki MariaDB 10.3+
- Apache/Nginx veb-server
- `pdo`, `mbstring`, `gd`, `curl`, `openssl` kengaytmalari

### Qadamlar

1. **Fayllarni serverga yuklang**
```bash
git clone <repository-url>
cd tibbiy-klinikasi
```

2. **Papkalar huquqlarini sozlang**
```bash
chmod -R 755 uploads/
chmod -R 755 data/
chmod 644 includes/config.php
```

3. **O'rnatish skriptini ishga tushiring**

Brauzerda oching: `https://saytingiz.com/install.php`

O'rnatish jarayoni:
- ✅ Server talablari tekshiruvi
- 🗄️ Ma'lumotlar bazasi ulanishi
- 📊 `schema.sql` ni ishga tushirish
- 👤 Superadmin hisobi yaratish
- ⚙️ Klinika ma'lumotlarini kiritish
- 🤖 Telegram Bot tokenini sozlash

4. **install.php ni o'chiring**
```bash
rm install.php
```

5. **Admin panelga kiring**
`https://saytingiz.com/admin/`

## ⚙️ Konfiguratsiya

### JSON fayllar

**data/settings.json** - Umumiy sozlamalar:
```json
{
  "site_name": "Klinika Nomi",
  "meta_description": "...",
  "smtp_host": "smtp.gmail.com",
  "smtp_port": 587,
  "telegram_bot_token": "BOT_TOKEN"
}
```

**data/contacts.json** - Aloqa ma'lumotlari:
```json
{
  "phones": ["+998901234567"],
  "email": "info@klinika.uz",
  "address": "Manzil",
  "work_time": "Dush-Yak: 08:00-20:00"
}
```

### Admin panel orqali

Admin panel → **Sozlamalar** bo'limida quyidagilarni sozlashingiz mumkin:
- 🏷️ Sayt nomi, meta ma'lumotlar
- 📞 Telefon, email, manzil
- 🌐 Ijtimoiy tarmoqlar
- 🖼️ Logotip va favicon
- 📧 SMTP sozlamalari
- 🤖 Telegram Bot
- 💳 To'lov rekvizitlari

## 🔒 Xavfsizlik

### Himoya qatlamlari

| Xavfsizlik turi | Yechim |
|----------------|--------|
| **CSRF** | Token har bir formada |
| **XSS** | `htmlspecialchars()` barcha outputlarda |
| **SQL Injection** | PDO prepared statements |
| **Session Hijacking** | `session_regenerate_id()`, httpOnly cookies |
| **File Upload** | MIME check, hajm cheklovi, random nom |
| **Parollar** | `password_hash()` (bcrypt, cost=12) |

### Fayl huquqlari

```bash
uploads/        # 755
data/           # 755
includes/       # 755
admin/          # 755
.htaccess       # 644
```

### .htaccess xavfsizligi

- URL rewriting
- X-XSS-Protection headerlari
- Directory listing o'chirilgan
- Admin papka qo'shimcha himoya

## 📡 API

### Chat API

**GET** `/api/chat-poll.php?last_id=123&chat_id=1`
```json
{
  "status": "success",
  "messages": [...]
}
```

**POST** `/api/chat-send.php`
```json
{
  "status": "success",
  "message_id": 124
}
```

### Navbat API

**GET** `/api/get-time-slots.php?doctor_id=1&date=2024-01-15`
```json
{
  "status": "success",
  "slots": ["09:00", "09:30", "10:00"]
}
```

**POST** `/api/cancel-appointment.php`
```json
{
  "status": "success",
  "message": "Navbat bekor qilindi"
}
```

### Fayl yuklash

**POST** `/api/upload-file.php` (multipart/form-data)
```json
{
  "status": "success",
  "file_id": 125,
  "file_url": "/uploads/chat/abc123.jpg"
}
```

## 🤖 Telegram Bot

### O'rnatish

1. [@BotFather](https://t.me/BotFather) orqali yangi bot yarating
2. Token oling
3. Admin panel → Sozlamalar → Telegram Bot bo'limiga tokenni kiriting
4. Webhook avtomatik o'rnatiladi: `https://saytingiz.com/api/telegram-webhook.php`

### Buyruqlar

| Buyruq | Tavsif |
|--------|--------|
| `/start` | Asosiy menyu |
| `Navbatga yozilish` | Web App ochiladi |
| `Mening navbatlarim` | Navbatlar ro'yxati |
| `Test natijalari` | Testlar Web App'da |
| `Aloqa` | Kontakt ma'lumotlar |

### Web App

Telegram bot ichida Web App ochiladi:
- 📱 Mobil optimallashtirilgan dizayn
- ⚡ Tezkor navigatsiya
- 🔐 Telegram orqali avtorizatsiya

### Kanal obunasi

Bot foydalanuvchini majburiy kanalga obuna bo'lishini tekshiradi. Obuna bo'lmagan holda xizmatlardan foydalana olmaydi.

## 🎨 Dizayn

### Apple Style & Glassmorphism

- **Ranglar**: #f5f5f7 (fon), #0071e3 (urg'u), #1d1d1f (matn)
- **Shakllar**: border-radius: 12-24px
- **Effektlar**: backdrop-filter: blur(10px), rgba shaffoflik
- **Soyalar**: box-shadow: 0 8px 32px rgba(0,0,0,0.1)
- **Shriftlar**: System fonts only (SF Pro, Roboto, Arial)

### Responsive

- Mobile-first yondashuv
- Breakpoints: 480px, 768px, 1024px, 1280px
- CSS Grid va Flexbox

## 📊 Ma'lumotlar Bazasi

Jadvallar (to'liq ro'yxat `sql/schema.sql` da):

- `users` - Foydalanuvchilar (bemorlar, adminlar)
- `doctors` - Shifokorlar
- `services` - Xizmatlar
- `appointments` - Navbatlar
- `tests` - Test natijalari
- `reviews` - Sharhlar
- `news` - Yangiliklar
- `gallery` - Galereya
- `chat_messages` - Chat xabarlar
- `notifications` - Bildirishnomalar
- `settings` - Sozlamalar (JSON backup)

## 🔄 Cron vazifalar

### Har 15 daqiqada
```bash
*/15 * * * * php /path/to/cron/send_reminders.php
```
- 24 soat ichidagi navbatlarga eslatma yuboradi

### Har kuni 03:00 da
```bash
0 3 * * * php /path/to/cron/cleanup_sessions.php
```
- Eskirgan sessiyalarni tozalaydi

## 🐛 Xatoliklarni bartaraf qilish

### Log fayllar
- PHP error log: `/var/log/php/error.log`
- Custom log: `logs/app.log` (ixtiyoriy)

### Debug rejimi

`includes/config.php` da:
```php
define('DEBUG', true); // Faqat developmentda!
```

### Umumiy xatolar

| Xatolik | Yechim |
|---------|--------|
| 404 Not Found | .htaccess tekshiring, mod_rewrite yoqing |
| 500 Internal Error | PHP error log ko'ring, permissions tekshiring |
| DB ulanish xatosi | includes/config.php dagi ma'lumotlarni tekshiring |
| Upload ishlamayapti | uploads/ huquqlari 755 ekanligini tekshiring |

## 📈 Performance

### Optimizatsiya

- ✅ Inline CSS/JS (HTTP request kamayadi)
- ✅ Gzip kompressiya (.htaccess)
- ✅ Browser caching static fayllar
- ✅ WEBP rasm formati
- ✅ DB indekslari (foreign key, email, phone, created_at)
- ✅ Paginatsiya barcha ro'yxatlarda
- ✅ JSON cache (filemtime asosida)

### Scaling

- Modulli arxitektura (includes/)
- JSON config (tezkor o'zgarishlar)
- Async cron (og'ir vazifalar)
- AJAX polling (WebSocket o'rniga)

## 📝 License

MIT License - Barcha huquqlar himoyalangan.

---

**Yaratuvchi**: NeyroFergana  
**Sana**: 2024  
**Versiya**: 1.0.0

🌐 [Sayt](https://neyrofergana.uz) | 📧 [Email](mailto:info@neyrofergana.uz) | 📱 [Telegram](https://t.me/neyrofergana)
