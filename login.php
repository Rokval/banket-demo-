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
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600&display=swap');
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            background: #fef9f0;
            font-family: 'Inter', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 460px;
            width: 100%;
            background: white;
            border-radius: 40px;
            box-shadow: 0 25px 45px -12px rgba(0,0,0,0.1);
            padding: 40px 32px;
        }
        .logo { text-align: center; margin-bottom: 24px; }
        .logo h1 {
            font-family: 'Oswald', sans-serif;
            font-size: 36px;
            background: linear-gradient(135deg, #b45f2b, #e6a05e);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .form-header { text-align: center; margin-bottom: 32px; }
        .form-header h2 { color: #3a2c1f; font-weight: 600; font-size: 28px; margin-bottom: 8px; }
        .error-message {
            background: #ffe6e5;
            color: #b13b2d;
            padding: 14px;
            border-radius: 28px;
            margin-bottom: 24px;
            text-align: center;
            font-size: 14px;
        }
        .form-group { margin-bottom: 24px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #3a2c1f; }
        .form-group input {
            width: 100%;
            padding: 14px 18px;
            border: 1.5px solid #e5d5c0;
            border-radius: 32px;
            font-size: 16px;
            transition: 0.2s;
            background: #fefaf5;
        }
        .form-group input:focus {
            outline: none;
            border-color: #b45f2b;
            box-shadow: 0 0 0 3px rgba(180,95,43,0.1);
        }
        .btn-login {
            width: 100%;
            background: #b45f2b;
            color: white;
            border: none;
            padding: 14px;
            border-radius: 40px;
            font-size: 18px;
            font-weight: 600;
            font-family: 'Oswald', sans-serif;
            cursor: pointer;
            transition: 0.2s;
        }
        .btn-login:hover { background: #9c4f22; transform: translateY(-2px); }
        .form-footer { margin-top: 28px; text-align: center; border-top: 1px solid #f0e2cf; padding-top: 24px; }
        .form-footer a { color: #b45f2b; text-decoration: none; font-weight: 500; }
        .form-footer a:hover { text-decoration: underline; }
    </style>
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