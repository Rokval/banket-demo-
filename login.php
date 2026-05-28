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
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            background: #FFFDD0;
            font-family: 'Oswald', sans-serif;
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
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 40px 32px;
        }
        .logo { text-align: center; margin-bottom: 24px; }
        .logo h1 {
            font-family: 'Oswald', sans-serif;
            font-size: 36px;
            color: #DC143C;
        }
        .form-header { text-align: center; margin-bottom: 32px; }
        .form-header h2 { color: #DAA520; font-weight: 600; font-size: 24px; margin-bottom: 8px; }
        .error-message {
            background: #FFDAB9;
            color: #DC143C;
            padding: 14px;
            border-radius: 20px;
            margin-bottom: 24px;
            text-align: center;
            font-size: 16px;
        }
        .form-group { margin-bottom: 24px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #000000; font-size: 16px; }
        .form-group input {
            width: 100%;
            padding: 14px 18px;
            border: 1px solid #FFDAB9;
            border-radius: 20px;
            font-size: 16px;
            transition: 0.2s;
            background: #FFFDD0;
            font-family: 'Oswald', sans-serif;
        }
        .form-group input:focus {
            outline: none;
            border-color: #DAA520;
            box-shadow: 0 0 0 3px rgba(218, 165, 32, 0.1);
        }
        .btn-login {
            width: 100%;
            background: #DAA520;
            color: white;
            border: none;
            padding: 14px;
            border-radius: 30px;
            font-size: 18px;
            font-weight: 600;
            font-family: 'Oswald', sans-serif;
            cursor: pointer;
            transition: 0.2s;
        }
        .btn-login:hover { background: #DC143C; transform: translateY(-2px); }
        .form-footer { margin-top: 28px; text-align: center; border-top: 1px solid #FFDAB9; padding-top: 24px; }
        .form-footer a { color: #DAA520; text-decoration: none; font-weight: 500; font-size: 16px; }
        .form-footer a:hover { color: #DC143C; text-decoration: underline; }
        .form-footer p { color: #006400; font-size: 12px; margin-bottom: 12px; }
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