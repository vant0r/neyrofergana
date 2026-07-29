<?php
/**
 * 404 - Sahifa topilmadi
 * Apple style minimalist dizayn
 */

http_response_code(404);
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sahifa topilmadi - 404</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: #f5f5f7;
            color: #1d1d1f;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .error-container {
            text-align: center;
            max-width: 500px;
        }

        .error-code {
            font-size: clamp(80px, 15vw, 180px);
            font-weight: 600;
            letter-spacing: -0.05em;
            background: linear-gradient(135deg, #0071e3 0%, #0077ed 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 20px;
        }

        .error-title {
            font-size: clamp(24px, 5vw, 32px);
            font-weight: 600;
            letter-spacing: -0.02em;
            margin-bottom: 12px;
        }

        .error-message {
            font-size: clamp(16px, 3vw, 18px);
            color: #86868b;
            line-height: 1.6;
            margin-bottom: 32px;
        }

        .btn {
            display: inline-block;
            padding: 14px 28px;
            background: #0071e3;
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 8px 24px rgba(0, 113, 227, 0.3);
        }

        .btn:hover {
            background: #0077ed;
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(0, 113, 227, 0.4);
        }

        .btn-secondary {
            background: rgba(134, 134, 139, 0.15);
            color: #1d1d1f;
            box-shadow: none;
            margin-left: 12px;
        }

        .btn-secondary:hover {
            background: rgba(134, 134, 139, 0.25);
            transform: translateY(-2px);
        }

        @media (max-width: 480px) {
            .btn-secondary {
                margin-left: 0;
                margin-top: 12px;
            }

            .buttons {
                display: flex;
                flex-direction: column;
                gap: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">404</div>
        <h1 class="error-title">Sahifa topilmadi</h1>
        <p class="error-message">
            Siz qidirgan sahifa mavjud emas yoki ko'chirilgan.<br>
            Iltimos, bosh sahifaga qayting yoki qidiruvdan foydalaning.
        </p>
        <div class="buttons">
            <a href="/" class="btn">Bosh sahifaga qaytish</a>
            <a href="/aloqa.php" class="btn btn-secondary">Aloqa</a>
        </div>
    </div>
</body>
</html>
