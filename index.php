<?php
session_start();

// Выход из системы
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

$is_admin = isset($_SESSION['admin']) && $_SESSION['admin'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Банкетам.Нет — выбор площадки для банкета</title>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            background: #fef9f0;
            color: #2c2b28;
            line-height: 1.5;
        }

        @import url('https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap');

        h1, h2, h3, .logo, .btn, .nav-buttons a {
            font-family: 'Oswald', sans-serif;
            letter-spacing: 0.02em;
        }

        .header {
            background: #ffffff;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid #f0e2cf;
        }

        .nav {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
        }

        .logo {
            font-size: 26px;
            font-weight: 600;
            color: #b45f2b;
            text-decoration: none;
            transition: color 0.2s;
        }

        .logo:hover {
            color: #d47c3a;
        }

        .nav-buttons {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .nav-buttons a {
            padding: 8px 18px;
            border-radius: 40px;
            font-weight: 500;
            font-size: 15px;
            text-decoration: none;
            transition: all 0.2s;
            background: #f5ede2;
            color: #b45f2b;
            border: 1px solid #e6d5bd;
        }

        .nav-buttons a:hover {
            background: #b45f2b;
            color: white;
            border-color: #b45f2b;
            transform: translateY(-2px);
        }

        .slideshow-container {
            max-width: 1200px;
            margin: 40px auto;
            border-radius: 32px;
            overflow: hidden;
            box-shadow: 0 20px 35px -12px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .mySlides {
            display: none;
        }

        .fade {
            animation: fadeIn 1s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0.4; }
            to { opacity: 1; }
        }

        .mySlides img {
            width: 100%;
            height: 480px;
            object-fit: cover;
        }

        .text {
            position: absolute;
            bottom: 30px;
            left: 30px;
            background: rgba(255, 250, 240, 0.9);
            backdrop-filter: blur(4px);
            padding: 10px 24px;
            border-radius: 60px;
            font-family: 'Oswald', sans-serif;
            font-weight: 500;
            font-size: 20px;
            color: #b45f2b;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .prev, .next {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255,250,240,0.8);
            backdrop-filter: blur(4px);
            color: #b45f2b;
            border: none;
            cursor: pointer;
            padding: 12px 18px;
            font-size: 18px;
            border-radius: 60px;
            transition: 0.2s;
        }

        .prev { left: 20px; }
        .next { right: 20px; }

        .prev:hover, .next:hover {
            background: #b45f2b;
            color: white;
        }

        .dot-container {
            text-align: center;
            padding: 20px 0;
        }

        .dot {
            height: 10px;
            width: 10px;
            margin: 0 6px;
            background-color: #e2cfb5;
            border-radius: 10px;
            display: inline-block;
            transition: 0.2s;
            cursor: pointer;
        }

        .dot.active, .dot:hover {
            background-color: #b45f2b;
            width: 24px;
        }

        .features-section {
            max-width: 1280px;
            margin: 60px auto;
            padding: 0 24px;
        }

        .features-title {
            text-align: center;
            font-size: 32px;
            font-weight: 500;
            color: #3a2c1f;
            margin-bottom: 48px;
            letter-spacing: 0.5px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
        }

        .feature-card {
            background: white;
            padding: 32px 24px;
            border-radius: 28px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.02);
            transition: all 0.25s;
            text-align: center;
            border: 1px solid #f3e9de;
        }

        .feature-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 30px -12px rgba(180, 95, 43, 0.12);
            border-color: #eedbc8;
        }

        .feature-card h3 {
            font-size: 24px;
            font-weight: 500;
            color: #b45f2b;
            margin-bottom: 14px;
        }

        .feature-card p {
            color: #5f5548;
            font-size: 16px;
            line-height: 1.45;
        }

        @media (max-width: 768px) {
            .nav { flex-direction: column; height: auto; padding: 16px; gap: 12px; }
            .mySlides img { height: 280px; }
            .text { font-size: 14px; bottom: 16px; left: 16px; padding: 6px 14px; }
            .prev, .next { padding: 8px 12px; font-size: 14px; }
            .features-title { font-size: 26px; }
        }
    </style>
</head>
<body>
<header class="header">
    <div class="nav">
        <a href="index.php" class="logo">🍽️ Банкетам.Нет</a>
        <div class="nav-buttons">
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="login.php">🔐 Войти</a>
                <a href="register.php">📝 Регистрация</a>
            <?php elseif ($is_admin): ?>
                <a href="admin.php">👑 Панель администратора</a>
                <a href="?logout=1">🚪 Выход</a>
            <?php elseif (isset($_SESSION['user_id'])): ?>
                <a href="history.php">📋 Мои заявки</a>
                <a href="create.php">🎉 Новая заявка</a>
                <a href="?logout=1">🚪 Выход</a>
            <?php endif; ?>
        </div>
    </div>
</header>







</body>
</html>