<?php
session_start();

if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['admin']) && $_SESSION['admin']) header('Location: admin.php');
    else header('Location: create.php');
    exit;
}

$error = false;
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = trim($_POST['login']);
    $password = $_POST['password'];
    if (empty($login) || empty($password)) {
        $error = true;
        $error_message = 'Пожалуйста, заполните все поля';
    } else {
        include('db.php');
        $stmt = $con->prepare("SELECT * FROM users WHERE login = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $error = true; $error_message = 'Неверный логин или пароль';
        } else {
            $user = $result->fetch_assoc();
            if ($password !== $user['password']) {
                $error = true; $error_message = 'Неверный логин или пароль';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_login'] = $user['login'];
                $_SESSION['user_fullname'] = $user['fullname'];
                // проверяем поле is_admin в БД
                if ($user['is_admin'] == 1) {
                    $_SESSION['admin'] = true;
                    header('Location: admin.php');
                } else {
                    header('Location: create.php');
                }
                exit;
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход — Банкетам.Нет</title>
  
   
</head>
<body>
<div class="container">
    <div class="logo"><h1>Банкетам.Нет</h1></div>
    <div class="form-header"><h2>Добро пожаловать!</h2></div>
    <?php if ($error): ?>
        <div class="error-message">⚠️ <?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="form-group"><label>👤 Логин</label><input type="text" name="login" required autofocus></div>
        <div class="form-group"><label>🔒 Пароль</label><input type="password" name="password" required></div>
        <button type="submit" class="btn-login">🎉 Войти</button>
    </form>
    <div class="form-footer"><p>Нет аккаунта? <a href="register.php">Зарегистрироваться →</a></p><a href="index.php">← Вернуться на главную</a></div>
</div>
</body>
</html>