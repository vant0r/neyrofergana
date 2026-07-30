```text
/
├── install.php                        # O'rnatuvchi (DB yaratish, admin yaratish, konfiguratsiya)
├── index.php                          # Bosh sahifa
├── haqimizda.php                      # Klinika haqida
├── xizmatlar.php                      # Xizmatlar va narxlar
├── shifokorlar.php                    # Shifokorlar jamoasi
├── shifokor.php                       # Yakka shifokor sahifasi (GET: ?id=)
├── yangiliklar.php                    # Yangiliklar va maqolalar
├── yangilik.php                       # Yakka yangilik sahifasi (GET: ?id=)
├── aloqa.php                          # Aloqa va manzil
├── savollar.php                       # FAQ
├── kirish-royxatdan-otish.php         # Avtorizatsiya/ro'yxatdan o'tish
├── galereya.php                       # Fotogalereya
├── maxfiylik.php                      # Maxfiylik siyosati
├── shartlar.php                       # Foydalanish shartlari
├── 404.php                            # 404 xatolik sahifasi
├── 500.php                            # 500 server xatolik sahifasi
│
├── user/                              # Bemor shaxsiy kabineti
│   ├── index.php                      # /user/ ga kirganda boshqaruvga yo'naltirish
│   ├── boshqaruv.php                  # Dashboard
│   ├── navbatlar.php                  # Navbatlar ro'yxati
│   ├── navbat-olish.php               # Yangi navbat olish
│   ├── testlar.php                    # Test va tahlil natijalari
│   ├── profil.php                     # Profil ma'lumotlari
│   ├── sozlamalar.php                 # Bildirishnoma sozlamalari
│   ├── parol-ozgartirish.php          # Parol o'zgartirish
│   ├── fikrlar.php                    # Sharh qoldirish
│   ├── hisobotlar.php                 # To'lovlar va cheklar tarixi
│   ├── bildirishnomalar.php           # Bildirishnomalar
│   └── chat.php                       # Live Chat (bemor tomoni)
│
├── admin/                             # Admin panel
│   ├── index.php                      # /admin/ ga kirganda boshqaruvga yo'naltirish
│   ├── boshqaruv.php                  # Dashboard + statistika
│   ├── sozlamalar.php                 # Umumiy sozlamalar (brending, aloqa, SMTP, Telegram, logotip, favicon, bannerlar yuklash)
│   ├── reklama.php                    # Reklama va bannerlar boshqaruvi
│   ├── xizmatlar.php                  # Xizmatlar katalogi CRUD
│   ├── shifokorlar.php                # Shifokorlar CRUD
│   ├── shifokor-tahrirlash.php        # Shifokor qo'shish/tahrirlash (GET: ?id=)
│   ├── navbatlar.php                  # Navbatlar boshqaruvi
│   ├── bemorlar.php                   # Bemorlar bazasi
│   ├── bemor.php                      # Yakka bemor tarixi (GET: ?id=)
│   ├── test-natijalari.php            # Tahlil natijalarini yuklash
│   ├── fikrlar.php                    # Sharhlar moderatsiyasi
│   ├── yangiliklar.php                # Yangiliklar CRUD
│   ├── galereya.php                   # Galereya boshqaruvi
│   ├── chat.php                       # Markazlashtirilgan Live Chat (admin tomoni)
│   ├── bildirishnomalar.php           # Ommaviy bildirishnomalar yuborish
│   ├── foydalanuvchilar.php           # Admin foydalanuvchilarni boshqarish (superadmin uchun)
│   └── htaccess.php                   # .htaccess va parol himoyasi holati
│
├── webapp/                            # Telegram Web App
│   ├── index.php                      # Web App bosh sahifasi (navbat olish)
│   ├── navbatlar.php                  # Mening navbatlarim
│   ├── testlar.php                    # Test natijalari
│   ├── profil.php                     # Profil
│   └── malumotlar.php                 # Klinika ma'lumotlari (aloqa, manzil)
│
├── includes/                          # Umumiy funksiyalar va modullar
│   ├── config.php                     # DB ulanish + JSON sozlamalarni o'qish
│   ├── init.php                       # Sessiya boshlash, xavfsizlik, avtomatik yuklash
│   ├── functions.php                  # Umumiy yordamchi funksiyalar
│   ├── auth.php                       # Autentifikatsiya va avtorizatsiya funksiyalari
│   ├── db.php                         # PDO wrapper (CRUD operatsiyalar, query builder)
│   ├── mailer.php                     # SMTP Email yuborish funksiyalari
│   ├── telegram.php                   # Telegram Bot API wrapper
│   ├── chat.php                       # Live Chat funksiyalari (DB operatsiyalar)
│   ├── upload.php                     # Fayl yuklash va validatsiya
│   ├── validation.php                 # Kiruvchi ma'lumotlarni tozalash/validatsiya
│   ├── security.php                   # CSRF, XSS, SQL injection himoya
│   ├── header.php                     # <head> + inline CSS (barcha stillar shu yerda) + yuqori menyu
│   ├── footer.php                     # Pastki menyu + inline JS (asosiy skriptlar) + </body>
│   ├── user-header.php                # User panel uchun alohida header (dashboard menyusi)
│   ├── admin-header.php               # Admin panel uchun alohida header
│   ├── pagination.php                 # Paginatsiya (sahifalash) funksiyasi
│   ├── stats.php                      # Statistika hisoblash funksiyalari (admin dashboard)
│   └── install-functions.php          # O'rnatuvchi uchun yordamchi funksiyalar
│
├── uploads/                           # Barcha media admin panel orqali yuklanadi
│   ├── logo/                          # Klinika logosi (asosiy, oq fonli, mobil)
│   ├── favicon/                       # Favicon
│   ├── banners/                       # Sahifa bannerlari, hero rasmlar
│   ├── doctors/                       # Shifokor rasmlari
│   ├── gallery/                       # Galereya
│   ├── tests/                         # Test natijalari (PDF, rasm)
│   ├── avatars/                       # Bemor avatarlari
│   ├── ads/                           # Reklama bannerlari va GIF lar
│   └── chat/                          # Chat orqali yuborilgan fayllar
│
├── data/                              # JSON konfiguratsiya fayllari
│   ├── settings.json                  # Umumiy sozlamalar (sayt nomi, SMTP, Telegram token, to'lov rekvizitlari)
│   ├── contacts.json                  # Telefon, manzil, ish vaqti, ijtimoiy tarmoqlar
│   └── ads.json                       # Reklama sozlamalari (qaysi sahifa, qaysi burchak, vaqt interval)
│
├── sql/
│   └── schema.sql                     # To'liq DB sxemasi (barcha CREATE TABLE lar)
│
├── cron/
│   ├── send_reminders.php             # Navbat eslatmalari (Email + Telegram)
│   └── cleanup_sessions.php           # Eskirgan sessiyalarni tozalash
│
├── api/                               # AJAX/API endpointlar
│   ├── chat-poll.php                  # Chat xabarlarni olish (GET, polling)
│   ├── chat-send.php                  # Chat xabar yuborish (POST)
│   ├── get-time-slots.php             # Shifokorning bo'sh vaqtlarini olish (GET, AJAX)
│   ├── cancel-appointment.php         # Navbatni bekor qilish (POST, AJAX)
│   ├── upload-file.php                # Chat orqali fayl yuklash (POST, AJAX)
│   └── telegram-webhook.php           # Telegram Bot webhook endpoint (POST)
│
├── .htaccess                          # URL rewriting, xavfsizlik headerlari, admin papka himoyasi
└── robots.txt                         # SEO uchun


================================================================================
1. LOYIHANING ASOSIY MAQSADI
================================================================================

Loyiha zamonaviy tibbiyot klinikasi uchun mo'ljallangan to'liq ekotizimni yaratishni maqsad qilgan. Ushbu ekotizim uchta asosiy ustunga tayanadi:

1. Rasmiy Veb-sayt
   - Bemorlarga klinika, shifokorlar, xizmatlar va narxlar haqida to'liq, ishonchli va vizual jozibador ma'lumot beradi.

2. Shaxsiy Kabinetlar (User va Admin)
   - Bemorlarga navbatga yozilish, test natijalarini ko'rish va qo'llab-quvvatlash xizmati bilan chatlashish imkonini beradi.
   - Adminlarga esa klinikaning butun ish jarayonini boshqarish imkonini yaratadi.

3. Telegram Bot va Web App Integratsiyasi
   - Bemorlarga botning o'zida yoki uning ichida ochiladigan qulay Web App orqali xizmatlardan tezkor foydalanish imkonini beradi.
   - Majburiy kanallarga obuna bo'lish hamda reklama va bildirishnomalarni olish imkonini beradi.


================================================================================
2. TEXNIK CHEKLOVLAR VA QOIDALAR
================================================================================

BACK-END:
- PHP 8.1+ (sof PHP, hech qanday freymvork ishlatilmaydi: Laravel, Symfony, CodeIgniter va hokazo taqiqlanadi)
- MySQL 5.7+ yoki MariaDB 10.3+ ma'lumotlar bazasi
- Tezkor o'zgaruvchan konfiguratsiyalar uchun JSON formatidan foydalaniladi (data/settings.json, data/contacts.json, data/ads.json)
- Barcha SQL so'rovlar PDO orqali prepared statements bilan bajariladi
- Sessiya boshqaruvi PHP native session orqali

FRONT-END:
- Barcha HTML, CSS va JavaScript kodlari to'g'ridan-to'g'ri PHP fayllar ichida joylashadi
- Tashqi CSS fayllari YO'Q — barcha stillar <style> tegi ichida inline
- Tashqi JS fayllari YO'Q — barcha skriptlar <script> tegi ichida inline
- Tashqi kutubxonalar YO'Q — jQuery, Bootstrap, Tailwind, React, Vue va boshqa hech qanday tashqi kutubxona ishlatilmaydi
- Tashqi shriftlar YO'Q — faqat tizim shriftlari (system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial)
- SVG ikonkalar YO'Q — barcha media (logotip, favicon, ikonkalar, bannerlar) admin panel orqali PNG/JPG/GIF/WEBP formatda yuklanadi
- Barcha media URL lar dinamik, includes/config.php orqali JSON yoki DB dan o'qiladi

EMAIL TIZIMI:
- PHPMailer yoki SwiftMailer ishlatilmaydi
- PHP native mail() funksiyasi yoki to'g'ridan-to'g'ri SMTP socket ulanish orqali yuboriladi
- Sozlamalar admin panel orqali boshqariladi, data/settings.json da saqlanadi

LIVE CHAT:
- Server tomoni: PHP, MySQL
- Client tomoni: vanilla JavaScript, fetch() API
- Real-time uchun qisqa intervalda polling (har 3-5 soniyada api/chat-poll.php ga so'rov)
- WebSocket ishlatilmaydi
- SSE (Server-Sent Events) ixtiyoriy, lekin polling asosiy yechim

TELEGRAM BOT:
- PHP orqali to'g'ridan-to'g'ri Telegram Bot API ga file_get_contents() yoki cURL so'rovlari
- Webhook orqali ishlaydi (api/telegram-webhook.php)
- Bot tokeni va sozlamalari data/settings.json da saqlanadi
- Hech qanday tashqi Telegram bot kutubxonasi ishlatilmaydi

XAVFSIZLIK:
- Barcha kiruvchi ma'lumotlar includes/validation.php orqali tozalanadi va validatsiya qilinadi
- CSRF token har bir formada qo'llaniladi (includes/security.php)
- XSS himoya: barcha chiqariladigan ma'lumotlar htmlspecialchars() dan o'tkaziladi
- SQL injection himoya: barcha so'rovlar PDO prepared statements orqali
- Parollar password_hash() (bcrypt) bilan hashlanadi
- Sessiya xavfsizligi: session_regenerate_id(), httpOnly, secure cookie flaglari
- Fayl yuklash: faqat ruxsat etilgan formatlar, fayl hajmi cheklovi, MIME tekshiruvi
- Admin panel admin/ papkasi .htaccess orqali qo'shimcha parol himoyasi (ixtiyoriy)


================================================================================
3. VIZUAL VA DIZAYN KONSEPSIYASI (APPLE STYLE & GLASSMORPHISM)
================================================================================

ASOSIY DIZAYN PRINSIPLARI:
- Silliq va Yumaloq Shakllar: Barcha bloklar, tugmalar va kartochkalar chekkalari border-radius: 12px dan 24px gacha
- Glassmorphic & Blur Effekti: Elementlar yarim shaffof shisha uslubida, background: rgba(255,255,255,0.25), backdrop-filter: blur(10px), -webkit-backdrop-filter: blur(10px), border: 1px solid rgba(255,255,255,0.3)
- Box Shadow: Nozik soyalar box-shadow: 0 8px 32px rgba(0,0,0,0.1)
- Silliq o'tishlar: Barcha hover va interaktiv elementlarda transition: all 0.3s ease

RANGLAR PALITRASI:
- Asosiy fon: #f5f5f7 (Apple ochiq kulrang)
- Kartochka fon: rgba(255,255,255,0.7) shisha effekti bilan
- Asosiy matn: #1d1d1f (Apple qora)
- Ikkilamchi matn: #86868b (Apple kulrang)
- Urg'u rang (Primary): #0071e3 (Apple ko'k)
- Urg'u hover: #0077ed
- Yashil (tasdiqlash): #34c759
- Qizil (xato/bekor): #ff3b30
- To'q havorang (tibbiy): #5ac8fa
- Och yashil (tibbiy): #e8f5e9

TIPOGRAFIYA:
- Sarlavhalar: font-weight: 600, letter-spacing: -0.02em
- Matn: font-weight: 400, line-height: 1.6
- O'lchamlar: clamp() funksiyasi bilan moslashuvchan (16px-48px oralig'ida)
- Faqat tizim shriftlari

MOSLASHUVCHANLIK (RESPONSIVE):
- Mobile-first yondashuv
- Breakpointlar: 480px, 768px, 1024px, 1280px
- CSS Grid va Flexbox asosiy layout
- Web App (Telegram) uchun: faqat 480px gacha optimallashtirilgan


================================================================================
4. BAZA SAHIFALARI VA UMUMIY STRUKTURA
================================================================================

UMUMIY STRUKTURA:
- Barcha sahifalar includes/header.php va includes/footer.php ni require qiladi

includes/header.php TARKIBI:
- <!DOCTYPE html> dan <body> gacha
- <head> ichida:
  - Meta teglar (charset, viewport, description, keywords — barchasi JSON dan olinadi)
  - Dinamik favicon URL (data/settings.json yoki DB dan)
  - <style> tegi ichida barcha umumiy CSS (Glassmorphism, ranglar, shriftlar, grid, header, footer, tugmalar, kartochkalar, modal oynalar, responsivlik — 100% inline)
- <body> ochilishi
- Yuqori menyu (header):
  - Klinika logosi (chapda)
  - Asosiy menyu: Bosh sahifa, Klinika haqida, Xizmatlar, Shifokorlar, Yangiliklar, Aloqa
  - O'ng tomonda: telefon raqami, "Kirish" / "Profil" tugmasi (sessiyaga qarab)
  - Mobil uchun "gamburger" menyu (CSS only, JS bilan ochiladigan)

includes/footer.php TARKIBI:
- Pastki blok (footer):
  - Klinika logosi va qisqacha ma'lumot
  - Tezkor havolalar
  - Aloqa ma'lumotlari
  - Ijtimoiy tarmoqlar havolalari (admin paneldan boshqariladi)
  - Copyright
- <script> tegi ichida barcha umumiy JavaScript:
  - Mobil menyu ochish/yopish
  - Sahifaga silliq skroll
  - Modal oyna ochish/yopish
  - Forma validatsiyasi
  - AJAX so'rovlar uchun yordamchi funksiyalar
  - Bildirishnoma (toast) chiqarish funksiyasi
- </body></html>


================================================================================
5. BOSH SAHIFALAR VA UMUMIY SAYT BO'LIMLARI
================================================================================

5.1 index.php — Bosh sahifa
Hero Banner:
- To'liq ekran yoki katta banner (admin paneldan yuklangan rasm)
- Glassmorphism uslubidagi yarim shaffof blok ichida sarlavha, tagline
- Ikkita CTA tugma: "Navbatga yozilish" (asosiy, ko'k) va "Xizmatlar" (ikkilamchi, shaffof)
- Mobil versiyada banner balandligi 60vh

Klinika haqida qisqacha:
- 2-3 ta Glassmorphism kartochka (bino, shifokorlar, uskunalar)
- Har bir kartochka ichida rasm (orqa fon), blur effekti, matn

Ommabop xizmatlar:
- 4-6 ta xizmat kartochkasi (3 ta ustun desktop, 2 ta planshet, 1 ta mobil)
- Har birida: ikonka (yuklangan rasm), xizmat nomi, qisqacha ta'rif, narx
- "Batafsil" tugmasi

Top Shifokorlar:
- 3-4 ta shifokor kartochkasi (slayder yoki grid)
- Har birida: shifokor rasmi (dumaloq), F.I.Sh., mutaxassisligi, tajriba yili
- "Qabulga yozilish" tugmasi

Bemorlar fikri:
- 3-6 ta sharh kartochkasi
- Har birida: mijoz rasmi (yoki avatar ikonkasi), ismi, yulduzchalar (1-5), sharh matni, sana
- Faqat admin tasdiqlagan sharhlar chiqadi

Reklama va Aksiyalar:
- Admin paneldan belgilangan pozitsiyada chiqadi
- Pop-up, banner yoki yon panel shaklida
- Vaqt intervali data/ads.json dan o'qiladi


5.2 haqimizda.php — Klinika haqida
- Klinika tarixi va missiyasi (keng matn bloki, admin tahrirlay oladi)
- Sertifikat va litsenziyalar (bosganda kattalashuvchi rasmlar, lightbox effekti)
- Fotogalereya havola bloki (oxirgi 6 ta rasm + "Barchasini ko'rish" tugmasi)
- Statistik ko'rsatkichlar: jami bemorlar, shifokorlar soni, ish yillari (JSON dan)


5.3 xizmatlar.php — Xizmatlar va Narxlar
- Kategoriyalar bo'yicha filter (Diagnostika, Stomatologiya, Terapiya va h.k.)
- Qidiruv input (jonli filter, JS bilan)
- Xizmat kartochkalari grid:
  - Xizmat nomi
  - Qisqacha ta'rif
  - Davomiyligi
  - Narxi (katta shriftda)
  - "Navbatga yozilish" tugmasi
- Paginatsiya (includes/pagination.php)


5.4 shifokorlar.php — Shifokorlar Jamoasi
- Mutaxassislik bo'yicha filter
- Shifokor kartochkalari grid:
  - Rasm (dumaloq yoki to'rtburchak yumaloq burchakli)
  - F.I.Sh.
  - Mutaxassisligi
  - Ish tajribasi (yil)
  - Qabul kunlari
  - "Batafsil" va "Navbatga yozilish" tugmalari
- Paginatsiya


5.5 shifokor.php — Yakka Shifokor Sahifasi (GET: ?id=)
- Katta rasm
- To'liq ism, mutaxassislik
- Batafsil biografiya (admin kiritgan matn)
- Ish tajribasi, sertifikatlar
- Qabul kunlari va vaqtlari (jadval)
- "Navbatga yozilish" tugmasi (modal yoki alohida sahifaga yo'naltirish)
- Ushbu shifokor haqidagi bemor sharhlari


5.6 yangiliklar.php — Yangiliklar va Maqolalar
- Kartochkalar grid:
  - Rasm (thumbnail)
  - Sarlavha
  - Qisqacha matn (birinchi 150 belgi)
  - Sana
  - "Batafsil o'qish" tugmasi
- Kategoriyalar bo'yicha filter (Aksiya, Maqola, Yangilik)
- Paginatsiya


5.7 yangilik.php — Yakka Yangilik Sahifasi (GET: ?id=)
- Katta rasm
- Sarlavha
- To'liq matn (HTML formatda, admin CKEditor yoki shunga o'xshash oddiy editor orqali kiritgan)
- Sana, muallif
- Ijtimoiy tarmoqlarga ulashish tugmalari


5.8 aloqa.php — Aloqa va Manzil
- Aloqa ma'lumotlari (chapda yoki yuqorida):
  - Telefon raqamlar (bosiladigan)
  - Email manzil
  - Ish vaqti
  - Manzil va mo'ljal
- Interaktiv xarita (Yandex yoki Google Maps iframe, admin sozlamalardan URL o'zgartiriladi)
- Qaytari aloqa shakli:
  - Ism
  - Telefon
  - Xabar matni
  - "Yuborish" tugmasi
- Admin emailiga yuboriladi, DB ga saqlanadi


5.9 savollar.php — FAQ
- Akkordeon menyu (CSS + JS bilan):
  - Savol (bosilganda ochiladi)
  - Javob (yashirin, ochilganda ko'rinadi)
- Kategoriyalar bo'yicha guruhlangan
- Admin panel orqali qo'shiladi/tahrirlanadi


5.10 kirish-royxatdan-otish.php — Avtorizatsiya va Ro'yxatdan O'tish
- Ikkita tab: "Kirish" va "Ro'yxatdan o'tish"

Kirish shakli:
- Email yoki telefon
- Parol
- "Eslab qolish" checkbox
- "Parolni unutdingizmi?" havolasi

Ro'yxatdan o'tish shakli:
- Ism, Familiya
- Telefon
- Email
- Parol va Parolni tasdiqlash
- "Ro'yxatdan o'tish" tugmasi

Email verification:
- Ro'yxatdan o'tgandan so'ng emailga tasdiqlash kodi/havolasi yuboriladi

Parolni tiklash:
- Email kiritiladi, reset link yuboriladi

- Glassmorphism uslubidagi karta, markazlashtirilgan


5.11 galereya.php — Fotogalereya
- Grid layout (4 ustun desktop, 2 ta planshet, 1 ta mobil)
- Har bir rasm: thumbnail, bosganda lightbox (to'liq ekran)
- Kategoriyalar bo'yicha filter (admin tomonidan belgilanadi)
- Paginatsiya yoki "Ko'proq yuklash" tugmasi


5.12 maxfiylik.php va shartlar.php
- Oddiy matn sahifasi
- Admin panel orqali tahrirlanadigan matn (HTML formatda)
- data/settings.json yoki DB dan o'qiladi


5.13 404.php va 500.php
404:
- Sahifa topilmadi — Apple uslubidagi minimalist dizayn
- Katta "404" raqami
- "Sahifa topilmadi" matni
- "Bosh sahifaga qaytish" tugmasi

500:
- Server xatolik
- "Vaqtincha ishlamayapti" matni
- "Qayta urinib ko'ring" tugmasi


================================================================================
6. USER PANELI (user/)
================================================================================

KIRISH SHARTI:
- Barcha user/*.php fayllar boshida includes/auth.php orqali sessiya tekshiruvi
- $_SESSION['user_id'] mavjud bo'lmasa, kirish-royxatdan-otish.php ga yo'naltiriladi

includes/user-header.php:
- User panel uchun alohida navigatsiya (chap sidebar yoki yuqori menyu)
- Profil rasmi, ism, "Chiqish" tugmasi
- Menyu: Boshqaruv, Navbatlar, Test natijalari, Profil, Sozlamalar, Chat, Bildirishnomalar


6.1 user/boshqaruv.php — Dashboard
Widgetlar (Glassmorphism kartochkalar):
- Yaqinlashayotgan navbat (sana, vaqt, shifokor ismi, xizmat)
- Oxirgi tayyor test natijalari soni (bog'lanma)
- O'qilmagan xabarlar soni (Chat)
- O'qilmagan bildirishnomalar soni

Tezkor harakatlar tugmalari:
- "Yangi navbatga yozilish"
- "Test natijalarim"
- "Qo'llab-quvvatlash bilan bog'lanish"


6.2 user/navbatlar.php — Navbatlarim
Ikkita tab: "Faol navbatlar" va "O'tgan navbatlar"

Faol navbatlar jadval/ro'yxat:
- Sana, vaqt
- Shifokor ismi, xonasi
- Xizmat nomi, narxi
- Holat (Kutilmoqda — sariq, Tasdiqlandi — yashil, Bekor qilindi — qizil)
- "Bekor qilish" tugmasi (faqat navbat vaqtidan 24 soat oldin faol)
- "Vaqtini o'zgartirish" tugmasi (ixtiyoriy)

O'tgan navbatlar:
- Xuddi shunday, lekin tugmalarsiz
- "Yangi navbatga yozilish" tugmasi (yuqorida)


6.3 user/navbat-olish.php — Yangi Navbat Olish
3 bosqichli jarayon (JS bilan, bir sahifada):
1. Mutaxassislik tanlash (kategoriyalar ro'yxati)
2. Shifokor tanlash (ushbu mutaxassislik bo'yicha shifokorlar, rasm, ism, tajriba)
3. Vaqt tanlash (sana, bo'sh time-slotlar — API orqali olinadi api/get-time-slots.php)

- Tanlangan ma'lumotlar ko'rinib turadi (pastki qismda yoki sidebar)
- "Tasdiqlash" tugmasi
- Tasdiqlangandan so'ng bemorga Email va Telegram xabar yuboriladi


6.4 user/testlar.php — Test Natijalari
Ro'yxat/jadval:
- Tahlil nomi
- Topshirilgan sana
- Shifokor ismi
- Holat (Jarayonda — sariq, Tayyor — yashil)
- "Yuklab olish" tugmasi (faqat Tayyor bo'lsa, PDF ochiladi yoki yuklanadi)
- "Ko'rish" tugmasi (agar rasm bo'lsa, modal oynada ochiladi)


6.5 user/profil.php — Profil
Profil kartochkasi:
- Avatar (yuklangan rasm yoki default ikonka)
- F.I.Sh.
- Tug'ilgan sana
- Jinsi
- Qon guruhi
- Telefon
- Email
- "Tahrirlash" tugmasi — forma ochiladi, ma'lumotlar yangilanadi
- Avatar yuklash (fayl tanlash input, preview, crop imkoniyati ixtiyoriy)


6.6 user/sozlamalar.php — Sozlamalar
- Email orqali bildirishnoma olish: checkbox
- Telegram orqali bildirishnoma olish: checkbox
- "Saqlash" tugmasi


6.7 user/parol-ozgartirish.php — Parol O'zgartirish
- Amaldagi parol
- Yangi parol
- Yangi parolni tasdiqlash
- "O'zgartirish" tugmasi


6.8 user/fikrlar.php — Sharh Qoldirish
- Shifokor tanlash (faqat bemor qabulida bo'lgan shifokorlar dropdown)
- Yulduzcha reyting (1-5, JS bilan interaktiv)
- Sharh matni
- "Yuborish" tugmasi
- Admin tasdiqlaganidan so'ng bosh sahifada chiqadi


6.9 user/hisobotlar.php — To'lovlar Tarixi
Jadval:
- Sana
- Xizmat nomi
- Shifokor
- Summa
- To'lov holati
- Filter (sana oralig'i)


6.10 user/bildirishnomalar.php — Bildirishnomalar
Bildirishnomalar ro'yxati:
- Sana/vaqt
- Matn
- Holat (O'qilmagan — qalin, O'qilgan — normal)
- "Barchasini o'qilgan deb belgilash" tugmasi
- Har biriga bosganda batafsil yoki yo'naltirish


6.11 user/chat.php — Live Chat (Bemor)
- Suhbat ro'yxati (chapda yoki tepada)
- Xabarlar oynasi:
  - Yuborilgan xabarlar (o'ngda, ko'k)
  - Qabul qilingan xabarlar (chapda, kulrang)
  - Vaqt ko'rsatilgan
  - O'qildi/O'qilmadi statusi (ikkita ko'k belgi)
- Xabar yozish inputi + "Yuborish" tugmasi
- Fayl yuklash tugmasi (rasm, PDF)
- Har 3 soniyada avtomatik yangilanish (polling)


================================================================================
7. ADMIN PANELI (admin/)
================================================================================

KIRISH SHARTI:
- Barcha admin/*.php fayllar boshida includes/auth.php va includes/admin-auth.php orqali sessiya va rol tekshiruvi
- $_SESSION['user_id'] va $_SESSION['user_role'] = 'admin' yoki 'superadmin' bo'lmasa, kirish-royxatdan-otish.php ga yo'naltiriladi

includes/admin-header.php:
- Admin panel uchun alohida navigatsiya (chap sidebar)
- Klinika logosi (kichik)
- Profil rasmi, ism, "Chiqish" tugmasi
- Menyu: Dashboard, Sozlamalar, Reklama, Xizmatlar, Shifokorlar, Navbatlar, Bemorlar, Test natijalari, Fikrlar, Yangiliklar, Galereya, Chat, Bildirishnomalar, Foydalanuvchilar (faqat superadmin)


7.1 admin/boshqaruv.php — Dashboard
Statistika widgetlari (Glassmorphism kartochkalar):
- Jami bemorlar soni
- Bugungi navbatlar soni
- Kunlik tushum
- O'rtacha navbat soniGrafiklar (oddiy CSS/JS yoki Canvas):
- Haftalik tushum grafigi
- Shifokorlar bo'yicha navbat yuklamasi

So'nggi faoliyat:
- Oxirgi 5 ta navbat
- Oxirgi 5 ta ro'yxatdan o'tgan bemorlar
- Yangi chat xabarlar (soni va oldindan ko'rish)

Tezkor harakatlar:
- "Yangi navbat yaratish"
- "Yangi shifokor qo'shish"
- "Yangi xizmat qo'shish"


7.2 admin/sozlamalar.php — Umumiy Sozlamalar
Tabs (JS bilan):

1. Umumiy:
   - Sayt nomi
   - Sayt tavsifi (meta description)
   - Kalit so'zlar (meta keywords)
   - Klinika ochilgan yil (statistika uchun)

2. Aloqa:
   - Telefon raqamlari (1-3 dona)
   - Email manzil
   - Ish vaqti (dushanba-juma, shanba, yakshanba)
   - Manzil (to'liq)
   - Mo'ljal (binary yo'nalish)
   - Xarita URL (Google Maps embed)

3. Ijtimoiy tarmoqlar:
   - Telegram
   - Instagram
   - Facebook
   - YouTube
   - (har biri uchun URL yoki username)

4. Logotip va Favicon:
   - Asosiy logotip yuklash (PNG/WEBP)
   - Oq fonli logotip yuklash (footer uchun)
   - Favicon yuklash (PNG, 16x16/32x32/48x48)

5. Email (SMTP):
   - SMTP server
   - SMTP port
   - SMTP username
   - SMTP password
   - "Test email yuborish" tugmasi

6. Telegram Bot:
   - Bot token
   - Webhook URL (avtomatik generatsiya)
   - "Webhook o'rnatish" tugmasi

7. To'lov rekvizitlari:
   - Bank nomi
   - Hisob raqam
   - INN
   - MFO

"Saqlash" tugmasi — hamma JSON/DB ga yoziladi


7.3 admin/reklama.php — Reklama Boshqaruvi
Reklamalar ro'yxati (jadval):
- ID
- Nomi
- Joylashuvi (sahifa nomi, pozitsiya)
- Turi (Banner, Pop-up, Broadcast)
- Holat (Faol/No faol)
- Amal qilish muddati
- Harakatlar (Tahrirlash, O'chirish)

"Yangi reklama qo'shish" tugmasi (modal yoki alohida sahifa):
- Reklama nomi
- Turi (Banner, Pop-up, Broadcast, Video)
- Sahifa (index, xizmatlar, shifokorlar va h.k.)
- Pozitsiya (yuqori o'ng, pastki o'ng, markaz, yon panel)
- Fayl yuklash (rasm/GIF/video)
- Matn (agar matnli reklama bo'lsa)
- URL (bosganda ochiladigan havola)
- Boshlanish sanasi
- Tugash sanasi
- Ko'rish oralig'i (pop-up uchun: har 1 daqiqa, 5 daqiqa, 10 daqiqa)
- "Saqlash" tugmasi

Telegram Broadcast:
- Xabar turi (Matn, Rasm, Video)
- Kontent (matn yoki fayl)
- "Yuborish" tugmasi (barcha bot foydalanuvchilariga)


7.4 admin/xizmatlar.php — Xizmatlar Katalogi
Xizmatlar ro'yxati (jadval):
- ID
- Nomi
- Kategoriya (Diagnostika, Stomatologiya, Terapiya, va h.k.)
- Narxi
- Davomiyligi (daqiqa)
- Holati (Faol/No faol)
- Harakatlar (Tahrirlash, O'chirish)

"Yangi xizmat qo'shish" tugmasi (modal):
- Nomi
- Kategoriya (dropdown)
- Qisqacha ta'rif
- To'liq ta'rif
- Narxi
- Davomiyligi (daqiqa)
- Rasm yuklash (ixtiyoriy)
- "Saqlash" tugmasi

Qidiruv va filtr (kategoriya bo'yicha)


7.5 admin/shifokorlar.php — Shifokorlar Jamoasi
Shifokorlar ro'yxati (jadval):
- ID
- Rasm
- F.I.Sh.
- Mutaxassisligi
- Tajriba (yil)
- Telefon
- Email
- Holati (Faol/No faol)
- Harakatlar (Tahrirlash, O'chirish)

"Yangi shifokor qo'shish" tugmasi (shifokor-tahrirlash.php ga yo'naltiriladi)


7.6 admin/shifokor-tahrirlash.php — Shifokor Qo'shish/Tahrirlash
Forma:
- F.I.Sh.
- Mutaxassislik
- Tajriba (yil)
- Biografiya (textarea)
- Telefon
- Email
- Qabul kunlari (dushanba-juma, shanba)
- Qabul vaqtlari (dan:gacha format, masalan: 09:00-13:00, 14:00-18:00)
- Xona raqami
- Narxi (ko'rik narxi)
- Rasm yuklash (JPG/PNG/WEBP)
- Holat (Faol/No faol)
- "Saqlash" tugmasi

Time-slot avtomatik generatsiya (30 daqiqa intervallar)


7.7 admin/navbatlar.php — Navbatlar Boshqaruvi
Navbatlar ro'yxati (jadval):
- ID
- Bemor (ism, telefon)
- Shifokor (ism)
- Xizmat (nomi)
- Sana va vaqt
- Holati (Kutilmoqda, Tasdiqlandi, Bajarildi, Bekor qilindi)
- Yaratilgan sana
- Harakatlar (Tasdiqlash, Bekor qilish, Bajarildi deb belgilash)

Filter:
- Sana oralig'i
- Shifokor (dropdown)
- Holat (dropdown)

"Yangi navbat yaratish" tugmasi (modal):
- Bemor tanlash (dropdown yoki qidiruv)
- Shifokor tanlash
- Xizmat tanlash
- Sana va vaqt tanlash
- "Yaratish" tugmasi

Status o'zgarganda bemorga Email va Telegram xabar yuboriladi


7.8 admin/bemorlar.php — Bemorlar Bazasi
Bemorlar ro'yxati (jadval):
- ID
- Ism, Familiya
- Telefon
- Email
- Ro'yxatdan o'tgan sana
- Holat (Faol/No faol)
- Harakatlar (Ko'rish, Tahrirlash, O'chirish)

Qidiruv (ism, telefon, email bo'yicha)


7.9 admin/bemor.php — Yakka Bemor Sahifasi (GET: ?id=)
Bemor ma'lumotlari:
- F.I.Sh.
- Telefon
- Email
- Tug'ilgan sana
- Jinsi
- Qon guruhi
- Ro'yxatdan o'tgan sana

Tabs:
1. Navbatlar tarixi (barcha navbatlar)
2. Test natijalari (barcha testlar)
3. To'lovlar tarixi

"Tahrirlash" tugmasi (bemor ma'lumotlarini o'zgartirish)
7.10 admin/test-natijalari.php — Tahlil Natijalarini Yuklash
Test natijalari ro'yxati (jadval):
- ID
- Bemor (ism)
- Shifokor (ism)
- Test nomi
- Topshirilgan sana
- Holati (Jarayonda, Tayyor)
- Harakatlar (Yuklab olish, Tahrirlash, O'chirish)

"Yangi natija yuklash" tugmasi (modal):
- Bemor tanlash (dropdown)
- Shifokor tanlash (dropdown)
- Test nomi
- Topshirilgan sana
- Fayl yuklash (PDF, JPG, PNG - maks 10MB)
- Holati (Jarayonda / Tayyor)
- "Saqlash" tugmasi

Fayl yuklanganidan so'ng bemorga Email va Telegram xabar yuboriladi


7.11 admin/fikrlar.php — Sharhlar Moderatsiyasi
Sharhlar ro'yxati (jadval):
- ID
- Bemor (ism)
- Shifokor (ism)
- Reyting (yulduzchalar)
- Sharh matni
- Sana
- Holati (Kutilmoqda, Tasdiqlangan, Rad etilgan)
- Harakatlar (Tasdiqlash, Rad etish, O'chirish)

Filter: holat bo'yicha


7.12 admin/yangiliklar.php — Yangiliklar CRUD
Yangiliklar ro'yxati (jadval):
- ID
- Rasm
- Sarlavha
- Kategoriya (Aksiya, Maqola, Yangilik)
- Sana
- Holati (Chop etilgan/Qoralama)
- Harakatlar (Tahrirlash, O'chirish)

"Yangi yangilik qo'shish" tugmasi (modal):
- Sarlavha
- Kategoriya (dropdown)
- Qisqacha matn
- To'liq matn (textarea, HTML tags ruxsat etilgan)
- Rasm yuklash
- Sana (avtomatik yoki tanlash)
- Holati (Qoralama / Chop etilgan)
- "Saqlash" tugmasi


7.13 admin/galereya.php — Galereya Boshqaruvi
Rasmlar ro'yxati (grid view):
- Thumbnail
- Kategoriya
- Yuklangan sana
- Harakatlar (O'chirish)

"Yangi rasm yuklash" tugmasi (modal):
- Kategoriya tanlash (xonalar, uskunalar, jamoa, va h.k.)
- Rasm yuklash (JPG/PNG/WEBP - maks 5MB)
- "Yuklash" tugmasi


7.14 admin/chat.php — Markazlashtirilgan Live Chat
Chap panel: Suhbatlar ro'yxati (bemorlar)
- Bemor ismi
- Oxirgi xabar vaqti
- O'qilmagan xabarlar soni (qizil doira)

O'ng panel: Xabarlar oynasi
- Bemor bilan to'liq suhbat (xronologik)
- Xabar yozish inputi + "Yuborish" tugmasi
- Fayl yuklash tugmasi

"Yangi suhbat boshlash" tugmasi (bemor qidiruv)


7.15 admin/bildirishnomalar.php — Ommaviy Bildirishnomalar
Forma:
- Bildirishnoma turi (Email, Telegram, Ikkalasi)
- Qabul qiluvchilar (Barcha, Faqat faol, Tanlangan)
- Sarlavha (email uchun subject)
- Matn (xabar matni)
- "Yuborish" tugmasi

Yuborilgan bildirishnomalar tarixi (jadval):
- Sana
- Turi
- Qabul qiluvchilar soni
- Holat (Yuborilgan, Xatolik)


7.16 admin/foydalanuvchilar.php — Admin Foydalanuvchilar (faqat superadmin)
Foydalanuvchilar ro'yxati (jadval):
- ID
- Ism
- Email
- Rol (Admin / Superadmin)
- Holat (Faol/No faol)
- Harakatlar (Tahrirlash, O'chirish)

"Yangi admin qo'shish" tugmasi (modal):
- Ism
- Email
- Parol
- Rol (Admin / Superadmin)
- "Yaratish" tugmasi


7.17 admin/htaccess.php — .htaccess Boshqaruvi (faqat superadmin)
- Admin papkasini parol bilan himoyalash
- IP oq ro'yxat
- "Saqlash" tugmasi (faylga yozadi)


================================================================================
8. TELEGRAM WEB APP (webapp/)
================================================================================

KIRISH SHARTI:
- Web App ichida Telegram'dan kelgan foydalanuvchi ma'lumotlari (Telegram WebApp SDK) orqali avtorizatsiya qilinadi
- $_SESSION['webapp_user_id'] yoki Telegram ma'lumotlariga asoslanib ishlaydi


8.1 webapp/index.php — Web App Bosh Sahifasi
- Klinika logosi (kichik)
- Foydalanuvchi ismi
- Tezkor tugmalar:
  - 🗓 Navbatga yozilish (webapp/navbat-olish.php ga)
  - 📋 Mening navbatlarim (webapp/navbatlar.php ga)
  - 🧪 Test natijalari (webapp/testlar.php ga)
  - 👤 Profil (webapp/profil.php ga)
  - 📞 Klinika ma'lumotlari (webapp/malumotlar.php ga)


8.2 webapp/navbatlar.php — Mening Navbatlarim
- Faol va o'tgan navbatlar ro'yxati (sodda, mobil optimallashtirilgan)
- Har bir navbat: sana, vaqt, shifokor, holat
- "Yangi navbatga yozilish" tugmasi


8.3 webapp/testlar.php — Test Natijalari
- Testlar ro'yxati (nomi, sana, holat)
- "Yuklab olish" tugmasi (agar tayyor bo'lsa)


8.4 webapp/profil.php — Profil
- Avatar (agar mavjud bo'lsa)
- Ism, telefon, email
- "Tahrirlash" tugmasi


8.5 webapp/malumotlar.php — Klinika Ma'lumotlari
- Manzil
- Telefon raqamlar
- Ish vaqti
- Xarita (embed)
- "Aloqa" tugmasi (telefon raqamini terish)


================================================================================
9. API ENDPOINTLAR (api/)
================================================================================

9.1 api/chat-poll.php — Chat xabarlarni olish (GET)
Parametrlar:
- last_id (oxirgi olingan xabar ID si)
- chat_id (suhbat ID si)

Javob: JSON
{
  "status": "success",
  "messages": [
    {
      "id": 123,
      "sender": "user|admin",
      "message": "Salom!",
      "file": null,
      "created_at": "2024-01-01 12:00:00",
      "is_read": 1
    }
  ]
}


9.2 api/chat-send.php — Chat xabar yuborish (POST)
Parametrlar:
- chat_id
- message (matn)
- file (base64 yoki URL)

Javob: JSON
{
  "status": "success",
  "message_id": 124
}


9.3 api/get-time-slots.php — Shifokorning bo'sh vaqtlarini olish (GET)
Parametrlar:
- doctor_id
- date (Y-m-d format)

Javob: JSON
{
  "status": "success",
  "slots": [
    "09:00",
    "09:30",
    "10:00"
  ]
}


9.4 api/cancel-appointment.php — Navbatni bekor qilish (POST)
Parametrlar:
- appointment_id
- csrf_token

Javob: JSON
{
  "status": "success",
  "message": "Navbat bekor qilindi"
}


9.5 api/upload-file.php — Chat orqali fayl yuklash (POST)
Parametrlar:
- chat_id
- file (multipart/form-data)

Javob: JSON
{
  "status": "success",
  "file_id": 125,
  "file_url": "/uploads/chat/abc123.jpg"
}


9.6 api/telegram-webhook.php — Telegram Bot webhook (POST)
Telegram'dan kelgan update'larni qabul qiladi:
- /start -> Asosiy menyu
- "Navbatga yozilish" -> Web App ochish
- "Mening navbatlarim" -> Web App ochish
- "Test natijalari" -> Web App ochish
- "Aloqa" -> Kontakt ma'lumotlar
- Kanal obunasi tekshiruvi


================================================================================
10. CRON SKRIPTLAR (cron/)
================================================================================

10.1 cron/send_reminders.php — Navbat eslatmalari
Ish vaqti: Har 15 daqiqada

Vazifa:
- 24 soat ichida bo'ladigan navbatlarni topadi
- Bemorlarga Email va Telegram orqali eslatma yuboradi
- SQL: UPDATE appointments SET reminder_sent = 1 WHERE ...


10.2 cron/cleanup_sessions.php — Eskirgan sessiyalarni tozalash
Ish vaqti: Har kuni soat 03:00

Vazifa:
- session.gc_maxlifetime dan oshgan sessiya fayllarini o'chiradi
- DB session jadvalini tozalaydi (agar bo'lsa)


================================================================================
11. MA'LUMOTLAR BAZASI SXEMASI (sql/schema.sql)
================================================================================

11.1 users
- id (int, PK, AUTO_INCREMENT)
- first_name (varchar 100)
- last_name (varchar 100)
- email (varchar 255, UNIQUE)
- phone (varchar 20, UNIQUE)
- password (varchar 255)
- role (enum: 'user', 'admin', 'superadmin')
- birth_date (date, NULL)
- gender (enum: 'male', 'female', NULL)
- blood_type (varchar 5, NULL)
- avatar (varchar 255, NULL)
- email_verified (tinyint 1, default 0)
- telegram_id (bigint 20, NULL, UNIQUE)
- telegram_username (varchar 255, NULL)
- notification_email (tinyint 1, default 1)
- notification_telegram (tinyint 1, default 0)
- status (enum: 'active', 'inactive', 'blocked', default 'active')
- created_at (datetime, default CURRENT_TIMESTAMP)
- updated_at (datetime, NULL)

11.2 doctors
- id (int, PK, AUTO_INCREMENT)
- first_name (varchar 100)
- last_name (varchar 100)
- specialization (varchar 255)
- experience (int) - yillarda
- bio (text, NULL)
- phone (varchar 20)
- email (varchar 255)
- room (varchar 50)
- consultation_fee (decimal 10,2)
- photo (varchar 255, NULL)
- work_days (varchar 255) - JSON formatda
- work_hours (varchar 255) - JSON formatda
- status (enum: 'active', 'inactive', default 'active')
- created_at (datetime, default CURRENT_TIMESTAMP)
- updated_at (datetime, NULL)

11.3 services
- id (int, PK, AUTO_INCREMENT)
- name (varchar 255)
- category (varchar 100)
- short_description (varchar 255)
- full_description (text, NULL)
- price (decimal 10,2)
- duration (int) - daqiqalarda
- image (varchar 255, NULL)
- status (enum: 'active', 'inactive', default 'active')
- created_at (datetime, default CURRENT_TIMESTAMP)
- updated_at (datetime, NULL)

11.4 appointments
- id (int, PK, AUTO_INCREMENT)
- user_id (int, FOREIGN KEY -> users.id)
- doctor_id (int, FOREIGN KEY -> doctors.id)
- service_id (int, FOREIGN KEY -> services.id)
- appointment_date (date)
- appointment_time (time)
- status (enum: 'pending', 'confirmed', 'completed', 'cancelled', default 'pending')
- notes (text, NULL)
- reminder_sent (tinyint 1, default 0)
- created_at (datetime, default CURRENT_TIMESTAMP)
- updated_at (datetime, NULL)

11.5 test_results
- id (int, PK, AUTO_INCREMENT)
- user_id (int, FOREIGN KEY -> users.id)
- doctor_id (int, FOREIGN KEY -> doctors.id)
- test_name (varchar 255)
- test_date (date)
- file_path (varchar 255)
- status (enum: 'processing', 'ready', default 'processing')
- created_at (datetime, default CURRENT_TIMESTAMP)
- updated_at (datetime, NULL)

11.6 reviews
- id (int, PK, AUTO_INCREMENT)
- user_id (int, FOREIGN KEY -> users.id)
- doctor_id (int, FOREIGN KEY -> doctors.id)
- rating (int, 1-5)
- comment (text)
- status (enum: 'pending', 'approved', 'rejected', default 'pending')
- created_at (datetime, default CURRENT_TIMESTAMP)
- updated_at (datetime, NULL)

11.7 messages (chat)
- id (int, PK, AUTO_INCREMENT)
- chat_id (int, FOREIGN KEY -> chats.id)
- sender_id (int, FOREIGN KEY -> users.id)
- sender_type (enum: 'user', 'admin')
- message (text)
- file_path (varchar 255, NULL)
- is_read (tinyint 1, default 0)
- created_at (datetime, default CURRENT_TIMESTAMP)

11.8 chats
- id (int, PK, AUTO_INCREMENT)
- user_id (int, FOREIGN KEY -> users.id)
- admin_id (int, FOREIGN KEY -> users.id, NULL)
- status (enum: 'open', 'closed', default 'open')
- created_at (datetime, default CURRENT_TIMESTAMP)
- updated_at (datetime, NULL)

11.9 faq
- id (int, PK, AUTO_INCREMENT)
- category (varchar 100)
- question (varchar 255)
- answer (text)
- sort_order (int, default 0)
- created_at (datetime, default CURRENT_TIMESTAMP)
- updated_at (datetime, NULL)

11.10 contacts
- id (int, PK, AUTO_INCREMENT)
- name (varchar 100)
- phone (varchar 20)
- message (text)
- status (enum: 'new', 'read', 'replied', default 'new')
- created_at (datetime, default CURRENT_TIMESTAMP)

11.11 gallery
- id (int, PK, AUTO_INCREMENT)
- category (varchar 100)
- file_path (varchar 255)
- title (varchar 255, NULL)
- sort_order (int, default 0)
- created_at (datetime, default CURRENT_TIMESTAMP)

11.12 ads
- id (int, PK, AUTO_INCREMENT)
- name (varchar 255)
- type (enum: 'banner', 'popup', 'broadcast', 'video')
- page (varchar 100, NULL)
- position (varchar 100, NULL)
- file_path (varchar 255, NULL)
- content (text, NULL)
- url (varchar 255, NULL)
- start_date (date)
- end_date (date)
- interval_minutes (int, default 0)
- status (enum: 'active', 'inactive', default 'active')
- created_at (datetime, default CURRENT_TIMESTAMP)
- updated_at (datetime, NULL)

11.13 notifications
- id (int, PK, AUTO_INCREMENT)
- user_id (int, FOREIGN KEY -> users.id)
- type (enum: 'appointment', 'test_result', 'promotion', 'system')
- title (varchar 255)
- message (text)
- is_read (tinyint 1, default 0)
- link (varchar 255, NULL)
- created_at (datetime, default CURRENT_TIMESTAMP)

11.14 payments
- id (int, PK, AUTO_INCREMENT)
- user_id (int, FOREIGN KEY -> users.id)
- appointment_id (int, FOREIGN KEY -> appointments.id)
- amount (decimal 10,2)
- status (enum: 'pending', 'paid', 'failed', default 'pending')
- payment_method (varchar 50, NULL)
- transaction_id (varchar 255, NULL)
- created_at (datetime, default CURRENT_TIMESTAMP)
- updated_at (datetime, NULL)

11.15 news
- id (int, PK, AUTO_INCREMENT)
- title (varchar 255)
- category (enum: 'promotion', 'article', 'news')
- short_content (varchar 255)
- full_content (text)
- image (varchar 255, NULL)
- status (enum: 'draft', 'published', default 'draft')
- views (int, default 0)
- created_at (datetime, default CURRENT_TIMESTAMP)
- updated_at (datetime, NULL)

11.16 settings (JSON fayllar bilan ham, DB da ham saqlanishi mumkin)
- id (int, PK, AUTO_INCREMENT)
- key (varchar 255, UNIQUE)
- value (text)
- category (varchar 100)
- created_at (datetime, default CURRENT_TIMESTAMP)
- updated_at (datetime, NULL)
================================================================================
12. XAVFSIZLIK QOIDALARI
================================================================================

CSRF HIMOYA:
- Har bir formada CSRF token qo'llaniladi
- Token includes/security.php da generatsiya qilinadi
- POST so'rovlarda token tekshiriladi

XSS HIMOYA:
- Barcha chiqariladigan ma'lumotlar htmlspecialchars() dan o'tkaziladi
- ENT_QUOTES | ENT_HTML5 flaglari bilan

SQL INJECTION HIMOYA:
- Barcha SQL so'rovlar PDO prepared statements orqali
- Hech qanday dinamik SQL qo'shilmaydi

SESSION XAVFSIZLIGI:
- session_regenerate_id() har bir muhim harakatda chaqiriladi
- session.cookie_httponly = 1
- session.cookie_secure = 1 (HTTPS da)
- session.use_only_cookies = 1

FILE UPLOAD XAVFSIZLIGI:
- Faqat ruxsat etilgan formatlar: JPG, PNG, WEBP, GIF, PDF
- MIME tekshiruvi
- Fayl hajmi cheklovi: 10MB (rasmlar), 5MB (avatarlar)
- Fayl nomi random_string() bilan almashtiriladi
- uploads/ papkasi .htaccess bilan himoyalanadi

PASSWORD HIMOYA:
- password_hash() (bcrypt, cost=12)
- password_verify() orqali tekshirish
- Parol minimal uzunligi: 8 belgi
- Katta/kichik harf, raqam va maxsus belgi talabi


================================================================================
13. PERFORMANCE VA SCALABILITY QOIDALARI
================================================================================

DATABASE:
- Barcha jadvallarda indekslar: foreign key, email, phone, status, created_at
- SELECT so'rovlarda faqat kerakli ustunlar olinadi (SELECT * ishlatilmaydi)
- Paginatsiya (LIMIT + OFFSET) barcha ro'yxatlarda qo'llaniladi

CACHING:
- JSON fayllar (data/) o'qilganda filemtime() asosida cache
- Admin panel ma'lumotlari uchun simple file caching (ixtiyoriy)

OPTIMIZATSIYA:
- CSS va JS inline bo'lgani uchun HTTP requestlar soni minimal
- Rasmlar optimallashtirilgan formatda (WEBP)
- Gzip kompressiya .htaccess orqali yoqilgan
- Browser caching static fayllar uchun (favicon va uploads dagi rasmlar)

SCALABILITY:
- Modulli struktura (includes/) — har bir funksiya alohida faylda
- JSON konfiguratsiyalar — tizimni qayta ishga tushirmasdan o'zgartirish mumkin
- Cron skriptlar — og'ir yuklamalarni asinxron bajaradi
- API polling — WebSocket o'rniga polling ishlatilganligi uchun server yuki kam

MONITORING:
- Error logging: error_log() yoki custom log fayli
- 500.php va 404.php da error kodlari qayd etiladi
- Admin dashboardda real-time statistika


================================================================================
14. O'RNATISH JARAYONI (install.php)
================================================================================

install.php VAZIFALARI:
1. Server talablarini tekshirish:
   - PHP versiyasi 8.1+
   - PDO, mbstring, GD, cURL, OpenSSL kengaytmalari
   - MySQL/MariaDB mavjudligi

2. Papka va fayl huquqlarini tekshirish:
   - uploads/ papkasi yozish huquqi (755)
   - data/ papkasi yozish huquqi (755)
   - includes/config.php fayli yozish huquqi (644)

3. Ma'lumotlar bazasi ulanish ma'lumotlarini kiritish:
   - DB host, name, user, password
   - schema.sql ni ishga tushirish

4. Superadmin hisobini yaratish:
   - Email, parol, ism
   - password_hash() bilan saqlash

5. Klinika asosiy ma'lumotlarini kiritish:
   - Klinika nomi
   - Telefon, manzil
   - SMTP sozlamalari

6. Telegram Bot tokenini kiritish:
   - Bot token
   - Webhook o'rnatish (api/telegram-webhook.php)

7. includes/config.php fayliga sozlamalarni yozish:
   - DB ulanish ma'lumotlari
   - JSON fayllar yo'li
   - Asosiy konstantalar

8. O'rnatish tugagach:
   - install.php ni avtomatik o'chirish yoki bloklash
   - admin/ va user/ papkalari uchun default sozlamalar

INSTALL BOSQICHLARI (JS bilan, bir sahifada):
1. Bosqich: Server talablari
2. Bosqich: Ma'lumotlar bazasi
3. Bosqich: Admin hisob
4. Bosqich: Klinika ma'lumotlari
5. Bosqich: Telegram Bot
6. Bosqich: O'rnatish yakunlandi


================================================================================
15. ESKIZ VA QO'SHIMCHA TAVSIYALAR
================================================================================

1. Barcha sahifalarda meta title va description dinamik ravishda data/settings.json dan olinadi

2. Mobil versiyada header menyusi "hamburger" ikonkasi bilan yopiq holatda, bosganda ochiladi

3. Barcha tugmalarda :hover va :active effektlari mavjud

4. Glassmorphism effekti uchun fon rasm yoki gradient fon ishlatilishi kerak

5. 404 va 500 sahifalarida header/footer ishlatilmaydi (alohida dizayn)

6. Barcha form ma'lumotlari client-side va server-side validatsiyadan o'tadi

7. Xatoliklar foydalanuvchiga tushunarli qilib ko'rsatiladi (toast yoki modal)

8. Barcha vaqtlar server vaqti bilan emas, foydalanuvchi vaqti bilan ko'rsatiladi

9. Telegram botda majburiy kanal obunasi tekshiruvi mavjud

10. Admin panelda barcha CRUD operatsiyalardan so'ng audit log saqlanadi (ixtiyoriy)
```
