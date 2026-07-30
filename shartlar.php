<?php
/**
 * Foydalanish shartlari sahifasi
 * NeuroFergana Tibbiy Klinikasi
 */

require_once 'includes/config.php';
require_once 'includes/init.php';

$page_title = "Foydalanish shartlari";
$meta_description = "NeuroFergana klinikasi veb-saytidan foydalanish shartlari va qoidalari.";

include 'includes/header.php';
?>

<main class="main-content">
    <div class="container">
        <!-- Sahifa sarlavhasi -->
        <section class="page-header">
            <h1>Foydalanish shartlari</h1>
            <p class="subtitle">Saytimizdan foydalanish orqali siz ushbu shartlarga rozilik bildirasiz</p>
        </section>

        <!-- Asosiy kontent -->
        <article class="content-card glass-effect">
            <div class="content-body">
                <section class="terms-section">
                    <h2>1. Umumiy qoidalar</h2>
                    <p>Ushbu veb-sayt ("Sayt") NeuroFergana tibbiy klinikasi ("Klinika") tomonidan taqdim etiladi. Saytdan foydalanish orqali siz ushbu Foydalanish shartlari ("Shartlar") bilan tanishganingiz va ularga to'liq roziligingizni bildirasiz. Agar siz ushbu shartlarga rozi bo'lmasangiz, iltimos, saytdan foydalanmang.</p>
                    
                    <h3>1.1. Yosh cheklovi</h3>
                    <p>Saytdan foydalanish uchun foydalanuvchi kamida 18 yoshda bo'lishi yoki ota-onasi/yoki qonuniy vakili roziligiga ega bo'lishi kerak.</p>
                </section>

                <section class="terms-section">
                    <h2>2. Ro'yxatdan o'tish va hisob ma'lumotlari</h2>
                    <p>Ba'zi xizmatlardan foydalanish uchun saytda ro'yxatdan o'tish talab etilishi mumkin.</p>
                    
                    <h3>2.1. Ma'lumotlarning to'g'riligi</h3>
                    <p>Foydalanuvchi ro'yxatdan o'tish jarayonida haqiqiy, to'liq va dolzarb ma'lumotlarni taqdim etishga majburdir. Noto'g'ri ma'lumotlarni taqdim etish natijasida kelib chiqadigan barcha mas'uliyat foydalanuvchining zimmasiga yuklanadi.</p>
                    
                    <h3>2.2. Hisob xavfsizligi</h3>
                    <p>Foydalanuvchi o'z hisob ma'lumotlari (login va parol) maxfiyligini saqlashga va ulardan ruxsatsiz foydalanish holatlari yuzaga kelganda darhol Klinika xabar berishga majburdir.</p>
                </section>

                <section class="terms-section">
                    <h2>3. Tibbiy xizmatlar va navbatga yozilish</h2>
                    
                    <h3>3.1. Navbatga yozilish tartibi</h3>
                    <p>Sayt orqali navbatga yozilish Klinika qabuliga oldindan yozilish imkonini beradi, ammo bu xizmatdan foydalanish avtomatik ravishda qabul kafolatini bermaydi. Navbat tasdiqlangandan so'ng, bemorga tegishli xabar yuboriladi.</p>
                    
                    <h3>3.2. Bekor qilish va o'zgartirish</h3>
                    <p>Bemor qabul vaqtidan kamida 24 soat oldin navbatni bekor qilishi yoki vaqtini o'zgartirishi mumkin. Aks holda, Klinika belgilagan jarima qoidalari qo'llanilishi mumkin.</p>
                    
                    <h3>3.3. Tibbiy maslahat cheklovi</h3>
                    <p>Saytda joylashtirilgan ma'lumotlar faqat axborot maqsadida taqdim etiladi va professional tibbiy maslahat, diagnostika yoki davolanishning o'rnini bosmaydi. Har doim malakali shifokor bilan maslahatlashing.</p>
                </section>

                <section class="terms-section">
                    <h2>4. Shaxsiy ma'lumotlar va maxfiylik</h2>
                    <p>Saytdan foydalanish jarayonida taqdim etilgan shaxsiy ma'lumotlar <a href="maxfiylik.php" class="link-primary">Maxfiylik siyosati</a>ga muvofiq qayta ishlanadi va himoya qilinadi. Biz bemorlarning shaxsiy ma'lumotlari maxfiyligini ta'minlashga alohida e'tibor qaratamiz.</p>
                </section>

                <section class="terms-section">
                    <h2>5. Intellektual mulk huquqlari</h2>
                    <p>Saytda joylashtirilgan barcha kontent (matnlar, rasmlar, logotiplar, dizayn elementlari, dasturiy kodlar) NeuroFergana klinikasining yoki tegishli huquq egalarining intellektual mulki hisoblanadi. Ruxsatsiz nusxalash, tarqatish yoki tijoriy maqsadlarda foydalanish taqiqlanadi.</p>
                </section>

                <section class="terms-section">
                    <h2>6. Javobgarlikni cheklash</h2>
                    
                    <h3>6.1. Texnik uzilishlar</h3>
                    <p>Klinika saytning uzluksiz ishlashini kafolatlamaydi. Texnik ta'minlash, yangilash yoki boshqa sabablarga ko'ra sayt vaqtincha ishlamasligi mumkin.</p>
                    
                    <h3>6.2. Uchinchi tomon havolalari</h3>
                    <p>Saytda uchinchi tomon veb-saytlariga havolalar bo'lishi mumkin. Klinika ushbu saytlar kontenti, xavfsizligi yoki ishlashi uchun javobgar emas.</p>
                </section>

                <section class="terms-section">
                    <h2>7. Taqiqlangan harakatlar</h2>
                    <p>Foydalanuvchilarga quyidagi harakatlar taqiqlanadi:</p>
                    <ul class="styled-list">
                        <li>Sayt xavfsizligini buzishga urinish;</li>
                        <li>Zararli kodlar, viruslar yoki boshqa zararli dasturlarni yuklash;</li>
                        <li>Boshqa foydalanuvchilar yoki xodimlarga nisbatan haqoratli, tahdidli yoki kamsituvchi xatti-harakatlar;</li>
                        <li>Soxta ma'lumotlarni taqdim etish;</li>
                        <li>Saytdan spam yoki reklama maqsadlarida foydalanish.</li>
                    </ul>
                </section>

                <section class="terms-section">
                    <h2>8. Shartlarni o'zgartirish</h2>
                    <p>Klinika ushbu Foydalanish shartlarini istalgan vaqtda o'zgartirish huquqini o'zida saqlaydi. O'zgartirishlar saytda e'lon qilingan kundan boshlab kuchga kiradi. Foydalanuvchilar muntazam ravishda shartlarni ko'rib chiqishlari tavsiya etiladi.</p>
                </section>

                <section class="terms-section">
                    <h2>9. Aloqa ma'lumotlari</h2>
                    <p>Ushbu shartlar bo'yicha savollaringiz bo'lsa, biz bilan bog'laning:</p>
                    <div class="contact-info-box">
                        <p><strong>Manzil:</strong> <span id="contact-address">Yuklanmoqda...</span></p>
                        <p><strong>Telefon:</strong> <span id="contact-phone">Yuklanmoqda...</span></p>
                        <p><strong>Email:</strong> <span id="contact-email">Yuklanmoqda...</span></p>
                    </div>
                </section>

                <div class="last-updated">
                    <p>Oxirgi yangilanish: <span id="update-date"><?php echo date('d.m.Y'); ?></span></p>
                </div>
            </div>
        </article>
    </div>
</main>

<script>
// Kontakt ma'lumotlarini yuklash
document.addEventListener('DOMContentLoaded', function() {
    fetch('data/contacts.json')
        .then(response => response.json())
        .then(data => {
            document.getElementById('contact-address').textContent = data.address || '';
            document.getElementById('contact-phone').textContent = data.phone || '';
            document.getElementById('contact-email').textContent = data.email || '';
        })
        .catch(error => {
            console.error('Kontakt ma\'lumotlarini yuklashda xatolik:', error);
        });
});
</script>

<style>
/* Sahifa uchun maxsus stillar */
.page-header {
    text-align: center;
    margin-bottom: 3rem;
    padding: 2rem 0;
}

.page-header h1 {
    font-size: clamp(2rem, 5vw, 3rem);
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 1rem;
    letter-spacing: -0.02em;
}

.page-header .subtitle {
    font-size: 1.1rem;
    color: var(--text-secondary);
    max-width: 600px;
    margin: 0 auto;
}

.content-card {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-radius: 24px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
    padding: 3rem;
    margin-bottom: 3rem;
}

.content-body {
    line-height: 1.8;
    color: var(--text-primary);
}

.terms-section {
    margin-bottom: 2.5rem;
}

.terms-section h2 {
    font-size: 1.75rem;
    font-weight: 600;
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid rgba(0, 113, 227, 0.1);
}

.terms-section h3 {
    font-size: 1.3rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 1.5rem 0 1rem;
}

.terms-section p {
    margin-bottom: 1rem;
    font-size: 1.05rem;
}

.styled-list {
    list-style: none;
    padding-left: 0;
}

.styled-list li {
    position: relative;
    padding-left: 1.5rem;
    margin-bottom: 0.8rem;
}

.styled-list li::before {
    content: "•";
    color: var(--primary-color);
    font-weight: bold;
    font-size: 1.2rem;
    position: absolute;
    left: 0;
    top: -2px;
}

.contact-info-box {
    background: rgba(0, 113, 227, 0.05);
    border-radius: 16px;
    padding: 1.5rem;
    margin-top: 1rem;
    border: 1px solid rgba(0, 113, 227, 0.1);
}

.contact-info-box p {
    margin-bottom: 0.5rem;
}

.last-updated {
    margin-top: 3rem;
    padding-top: 2rem;
    border-top: 1px solid rgba(0, 0, 0, 0.1);
    text-align: center;
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.link-primary {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
    transition: color 0.3s ease;
}

.link-primary:hover {
    color: var(--primary-hover);
    text-decoration: underline;
}

/* Mobil moslashuvchanlik */
@media (max-width: 768px) {
    .content-card {
        padding: 1.5rem;
    }
    
    .terms-section h2 {
        font-size: 1.5rem;
    }
    
    .terms-section h3 {
        font-size: 1.2rem;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
