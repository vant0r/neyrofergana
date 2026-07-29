<?php
/**
 * Maxfiylik Siyosati
 * Foydalanuvchilar ma'lumotlarini himoya qilish siyosati
 */

define('ACCESS_ALLOWED', true);
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$settings = getSettings();
$pageTitle = 'Maxfiylik Siyosati';

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="max-width: 800px; margin: 60px auto; padding: 0 20px;">
    <div class="card" style="background: var(--card-bg); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border-radius: 24px; padding: 40px; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08); border: 1px solid rgba(255, 255, 255, 0.3);">
        <h1 style="font-size: clamp(28px, 5vw, 36px); font-weight: 600; letter-spacing: -0.02em; margin-bottom: 24px; color: var(--text);">Maxfiylik Siyosati</h1>
        
        <div style="font-size: 14px; color: var(--text-secondary); margin-bottom: 32px;">
            Oxirgi yangilanish: <?= date('d.m.Y') ?>
        </div>

        <section style="margin-bottom: 32px;">
            <h2 style="font-size: 20px; font-weight: 600; margin-bottom: 16px; color: var(--text);">1. Umumiy qoidalar</h2>
            <p style="line-height: 1.8; color: var(--text); margin-bottom: 16px;">
                Ushbu maxfiylik siyosati <?= htmlspecialchars($settings['site_name'] ?? 'Klinika') ?> (keyingi o'rinlarda "Klinika") tomonidan foydalanuvchilarning shaxsiy ma'lumotlarini qanday to'plash, saqlash va himoya qilishini tushuntiradi.
            </p>
            <p style="line-height: 1.8; color: var(--text);">
                Biz foydalanuvchilarning maxfiyligini hurmat qilamiz va shaxsiy ma'lumotlarni himoya qilish bo'yicha qonunchilik talablariga rioya etamiz.
            </p>
        </section>

        <section style="margin-bottom: 32px;">
            <h2 style="font-size: 20px; font-weight: 600; margin-bottom: 16px; color: var(--text);">2. Qanday ma'lumotlar to'planadi</h2>
            <p style="line-height: 1.8; color: var(--text); margin-bottom: 16px;">Biz quyidagi ma'lumotlarni to'plashimiz mumkin:</p>
            <ul style="line-height: 2; color: var(--text); padding-left: 20px;">
                <li>Ism, familiya va otasining ismi</li>
                <li>Telefon raqami va email manzili</li>
                <li>Tug'ilgan sana va jins</li>
                <li>Tibbiy ma'lumotlar (qon guruhi, allergiyalar va h.k.)</li>
                <li>Navbat va qabul ma'lumotlari</li>
                <li>To'lov tarixi va hisob-fakturalar</li>
            </ul>
        </section>

        <section style="margin-bottom: 32px;">
            <h2 style="font-size: 20px; font-weight: 600; margin-bottom: 16px; color: var(--text);">3. Ma'lumotlardan foydalanish</h2>
            <p style="line-height: 1.8; color: var(--text); margin-bottom: 16px;">Sizning ma'lumotlaringiz quyidagi maqsadlarda ishlatiladi:</p>
            <ul style="line-height: 2; color: var(--text); padding-left: 20px;">
                <li>Navbatga yozilish va qabulni tashkil qilish</li>
                <li>Tibbiy xizmatlar ko'rsatish</li>
                <li>Test natijalarini yuborish</li>
                <li>Eslatma va bildirishnomalar yuborish</li>
                <li>Xizmat sifatini yaxshilash</li>
                <li>Qonuniy talablarni bajarish</li>
            </ul>
        </section>

        <section style="margin-bottom: 32px;">
            <h2 style="font-size: 20px; font-weight: 600; margin-bottom: 16px; color: var(--text);">4. Ma'lumotlarni himoya qilish</h2>
            <p style="line-height: 1.8; color: var(--text);">
                Biz sizning shaxsiy ma'lumotlaringizni himoya qilish uchun zamonaviy xavfsizlik choralarini qo'llaymiz, jumladan:
            </p>
            <ul style="line-height: 2; color: var(--text); padding-left: 20px; margin-top: 12px;">
                <li>SSL shifrlash texnologiyasi</li>
                <li>Ma'lumotlar bazasini himoya qilish</li>
                <li>Faqat vakolatli xodimlarga kirish huquqi</li>
                <li>Muntazam xavfsizlik tekshirovlari</li>
            </ul>
        </section>

        <section style="margin-bottom: 32px;">
            <h2 style="font-size: 20px; font-weight: 600; margin-bottom: 16px; color: var(--text);">5. Huquqlaringiz</h2>
            <p style="line-height: 1.8; color: var(--text);">
                Siz quyidagi huquqlarga egasiz:
            </p>
            <ul style="line-height: 2; color: var(--text); padding-left: 20px; margin-top: 12px;">
                <li>Shaxsiy ma'lumotlaringizni ko'rish</li>
                <li>Ma'lumotlarni tahrirlash va yangilash</li>
                <li>Ma'lumotlarni o'chirishni so'rash</li>
                <li>Ma'lumotlarni qayta ishlashga rozilikni bekor qilish</li>
            </ul>
        </section>

        <section style="margin-bottom: 32px;">
            <h2 style="font-size: 20px; font-weight: 600; margin-bottom: 16px; color: var(--text);">6. Aloqa</h2>
            <p style="line-height: 1.8; color: var(--text);">
                Maxfiylik siyosati bo'yicha savollaringiz bo'lsa, biz bilan bog'laning:
            </p>
            <div style="margin-top: 16px; padding: 20px; background: rgba(0, 113, 227, 0.08); border-radius: 12px;">
                <p style="line-height: 1.8; color: var(--text); margin-bottom: 8px;">
                    <strong>Telefon:</strong> <?= htmlspecialchars($contacts['phones'][0] ?? '+998 00 000-00-00') ?>
                </p>
                <p style="line-height: 1.8; color: var(--text); margin-bottom: 8px;">
                    <strong>Email:</strong> <?= htmlspecialchars($contacts['email'] ?? 'info@klinika.uz') ?>
                </p>
                <p style="line-height: 1.8; color: var(--text);">
                    <strong>Manzil:</strong> <?= htmlspecialchars($contacts['address'] ?? 'Toshkent shahri') ?>
                </p>
            </div>
        </section>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
