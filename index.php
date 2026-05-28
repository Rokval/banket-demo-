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