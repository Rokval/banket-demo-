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
    <!-- Подключение шрифта Oswald (только он, по руководству) -->
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Цветовая схема ПО РУКОВОДСТВУ */
        :root {
            --gold: #DAA520;        /* Золотой */
            --rose-gold: #FFDAB9;   /* Розово-золотистый */
            --cream: #FFFDD0;       /* Кремовый */
            --crimson: #DC143C;     /* Насыщенно-красный */
            --forest-green: #006400; /* Тёмно-зелёный */
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Oswald', sans-serif;  /* Только Oswald, по руководству */
            background: var(--cream);            /* Кремовый фон */
            color: #000000;                      /* Основной текст — чёрный */
            line-height: 1.5;
        }

        /* Шапка сайта */
        .header {
            background: #ffffff;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 2px solid var(--rose-gold);
        }

        .nav {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 80px;
        }

        .logo {
            font-size: 36px;           /* H1 размер */
            font-weight: 700;
            color: var(--crimson);      /* Насыщенно-красный */
            text-decoration: none;
            transition: color 0.2s;
        }

        .logo:hover {
            color: var(--gold);
        }

        .nav-buttons {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .nav-buttons a {
            padding: 8px 20px;
            border-radius: 30px;
            font-weight: 500;
            font-size: 16px;            /* Основной текст 16px */
            text-decoration: none;
            transition: all 0.2s;
            background: var(--rose-gold);
            color: var(--gold);
            border: 1px solid var(--gold);
        }

        .nav-buttons a:hover {
            background: var(--gold);
            color: white;
            border-color: var(--gold);
            transform: translateY(-2px);
        }

        /* Слайдер */
        .slideshow-container {
            max-width: 1200px;
            position: relative;
            margin: 40px auto;
            overflow: hidden;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
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
            height: 500px;
            object-fit: cover;
        }

        .text {
            position: absolute;
            bottom: 30px;
            left: 30px;
            background: rgba(0, 0, 0, 0.6);
            padding: 10px 24px;
            border-radius: 30px;
            font-family: 'Oswald', sans-serif;
            font-weight: 500;
            font-size: 18px;
            color: var(--gold);
        }

        .prev, .next {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.5);
            color: var(--gold);
            border: none;
            cursor: pointer;
            padding: 12px 18px;
            font-size: 18px;
            border-radius: 50%;
            transition: 0.2s;
        }

        .prev { left: 20px; }
        .next { right: 20px; }

        .prev:hover, .next:hover {
            background: var(--gold);
            color: white;
        }

        .dot-container {
            text-align: center;
            padding: 20px 0;
        }

        .dot {
            height: 12px;
            width: 12px;
            margin: 0 6px;
            background-color: var(--rose-gold);
            border-radius: 50%;
            display: inline-block;
            transition: 0.2s;
            cursor: pointer;
        }

        .dot.active, .dot:hover {
            background-color: var(--gold);
        }

        /* Секция преимуществ */
        .features-section {
            max-width: 1280px;
            margin: 60px auto;
            padding: 0 24px;
        }

        .features-title {
            text-align: center;
            font-size: 24px;            /* H2 размер */
            font-weight: 600;
            color: var(--gold);          /* Золотой */
            margin-bottom: 48px;
            letter-spacing: 1px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
        }

        .feature-card {
            background: white;
            padding: 32px 24px;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.25s;
            text-align: center;
            border: 1px solid var(--rose-gold);
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(218, 165, 32, 0.1);
            border-color: var(--gold);
        }

        .feature-card h3 {
            font-size: 18px;            /* H3 размер */
            font-weight: 600;
            color: var(--gold);          /* Золотой */
            margin-bottom: 14px;
        }

        .feature-card p {
            color: #000000;              /* Основной текст — чёрный */
            font-size: 16px;            /* 16px */
            line-height: 1.45;
        }

        /* Вспомогательный текст (если понадобится) */
        .hint-text {
            font-size: 12px;
            color: var(--forest-green);
        }

        @media (max-width: 768px) {
            .nav { flex-direction: column; height: auto; padding: 16px; gap: 12px; }
            .logo { font-size: 28px; }
            .mySlides img { height: 280px; }
            .text { font-size: 14px; bottom: 16px; left: 16px; padding: 6px 14px; }
            .prev, .next { padding: 8px 12px; font-size: 14px; }
            .features-title { font-size: 20px; }
            .feature-card h3 { font-size: 16px; }
            .feature-card p { font-size: 14px; }
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

<div class="slideshow-container">
    <div class="mySlides fade">
        <img src="foto/1.jpg" alt="Банкетный зал">
        <div class="text">🏛️ Просторный банкетный зал</div>
    </div>
    <div class="mySlides fade">
        <img src="foto/2.jpg" alt="Ресторан">
        <div class="text">🍷 Изысканный ресторан</div>
    </div>
    <div class="mySlides fade">
        <img src="foto/3.jpg" alt="Летняя веранда">
        <div class="text">🌞 Уютная летняя веранда</div>
    </div>
    <div class="mySlides fade">
        <img src="foto/4.jpg" alt="Закрытая веранда">
        <div class="text">🏠 Тёплая закрытая веранда</div>
    </div>
    <a class="prev" onclick="plusSlides(-1)">❮</a>
    <a class="next" onclick="plusSlides(1)">❯</a>
</div>

<div class="dot-container">
    <span class="dot" onclick="currentSlide(1)"></span>
    <span class="dot" onclick="currentSlide(2)"></span>
    <span class="dot" onclick="currentSlide(3)"></span>
    <span class="dot" onclick="currentSlide(4)"></span>
</div>

<section class="features-section">
    <h2 class="features-title">✨ Почему выбирают «Банкетам.Нет»?</h2>
    <div class="features-grid">
        <div class="feature-card">
            <h3>🏛️ Лучшие залы и рестораны</h3>
            <p>Подберём идеальное место для вашего торжества — от камерных залов до больших ресторанов.</p>
        </div>
        <div class="feature-card">
            <h3>🌿 Летние и закрытые веранды</h3>
            <p>Организуем банкет на свежем воздухе или в уютной закрытой веранде в любое время года.</p>
        </div>
        <div class="feature-card">
            <h3>🤝 Помощь с выбором</h3>
            <p>Наши менеджеры помогут выбрать помещение под любой бюджет и количество гостей.</p>
        </div>
    </div>
</section>

<script>
    let slideIndex = 1;
    showSlides(slideIndex);
    function plusSlides(n) { showSlides(slideIndex += n); }
    function currentSlide(n) { showSlides(slideIndex = n); }
    function showSlides(n) {
        let slides = document.getElementsByClassName("mySlides");
        let dots = document.getElementsByClassName("dot");
        if (n > slides.length) slideIndex = 1;
        if (n < 1) slideIndex = slides.length;
        for (let i = 0; i < slides.length; i++) slides[i].style.display = "none";
        for (let i = 0; i < dots.length; i++) dots[i].className = dots[i].className.replace(" active", "");
        slides[slideIndex-1].style.display = "block";
        dots[slideIndex-1].className += " active";
    }
    let slideInterval = setInterval(() => plusSlides(1), 4000);
    const container = document.querySelector('.slideshow-container');
    container?.addEventListener('mouseenter', () => clearInterval(slideInterval));
    container?.addEventListener('mouseleave', () => slideInterval = setInterval(() => plusSlides(1), 4000));
</script>
</body>
</html>