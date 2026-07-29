<?php
/**
 * 500 - Server xatolik
 * Apple style minimalist dizayn
 */

http_response_code(500);
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vaqtincha ishlamayapti - 500</title>
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

        .error-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 24px;
            background: linear-gradient(135deg, #ff9500 0%, #ffac33 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 12px 40px rgba(255, 149, 0, 0.3);
        }

        .error-icon svg {
            width: 40px;
            height: 40px;
            fill: white;
        }

        .error-code {
            font-size: clamp(60px, 12vw, 120px);
            font-weight: 600;
            letter-spacing: -0.05em;
            background: linear-gradient(135deg, #ff3b30 0%, #ff453a 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 12px;
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
        <div class="error-icon">
            <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
        </div>
        <div class="error-code">500</div>
        <h1 class="error-title">Vaqtincha ishlamayapti</h1>
        <p class="error-message">
            Serverda vaqtinchalik muammo yuz berdi.<br>
            Iltimos, bir necha daqiqadan so'ng qayta urinib ko'ring.
        </p>
        <div class="buttons">
            <a href="/" class="btn">Qayta urinib ko'ring</a>
            <a href="/aloqa.php" class="btn btn-secondary">Aloqa</a>
        </div>
    </div>
</body>
</html>
