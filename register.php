<?php
session_start();

if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['admin']) && $_SESSION['admin']) header('Location: admin.php');
    else header('Location: create.php');
    exit;
}

$error = false;
$error_message = '';
$success = false;
$form_data = [];
$field_errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = trim($_POST['login']);
    $password = $_POST['password'];
    $fullname = trim($_POST['fullname']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    
    $form_data = compact('login', 'fullname', 'phone', 'email');
    
    // Валидация
    if (empty($login)) $field_errors['login'] = 'Логин обязателен';
    elseif (!preg_match('/^[a-zA-Z0-9]{6,}$/', $login)) $field_errors['login'] = 'Только латиница+цифры, мин. 6 символов';
    
    if (empty($password)) $field_errors['password'] = 'Пароль обязателен';
    elseif (strlen($password) < 8) $field_errors['password'] = 'Минимум 8 символов';
    
    if (empty($fullname)) $field_errors['fullname'] = 'ФИО обязательно';
    elseif (strlen($fullname) < 5) $field_errors['fullname'] = 'Введите полное ФИО';
    
    if (empty($phone)) $field_errors['phone'] = 'Телефон обязателен';
    elseif (!preg_match('/^\+7\(\d{3}\)\d{3}-\d{2}-\d{2}$/', $phone)) $field_errors['phone'] = 'Формат: +7(XXX)XXX-XX-XX';
    
    if (empty($email)) $field_errors['email'] = 'Email обязателен';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $field_errors['email'] = 'Введите корректный email';
    
    if (empty($field_errors)) {
        include('db.php');
        $stmt = $con->prepare("SELECT id FROM users WHERE login = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $field_errors['login'] = 'Логин уже существует';
        } else {
            $stmt = $con->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $field_errors['email'] = 'Email уже существует';
            } else {
                $stmt = $con->prepare("INSERT INTO users (login, password, fullname, phone, email, is_admin) VALUES (?, ?, ?, ?, ?, 0)");
                $stmt->bind_param("sssss", $login, $password, $fullname, $phone, $email);
                if ($stmt->execute()) { $success = true; header('refresh:2;url=login.php'); }
                else { $error = true; $error_message = 'Ошибка регистрации'; }
                $stmt->close();
            }
        }
        $stmt->close();
    } else {
        $error = true;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация — Банкетам.Нет</title>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { background: #FFFDD0; font-family: 'Oswald', sans-serif; padding: 40px 20px; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .container { max-width: 560px; width: 100%; background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); padding: 40px 32px; }
        .logo { text-align: center; margin-bottom: 20px; }
        .logo h1 { font-family: 'Oswald', sans-serif; font-size: 36px; color: #DC143C; }
        .form-header { text-align: center; margin-bottom: 28px; }
        .form-header h2 { color: #DAA520; font-weight: 600; font-size: 24px; margin-bottom: 8px; }
        .form-header p { color: #006400; font-size: 12px; }
        .error-message { background: #FFDAB9; color: #DC143C; padding: 14px 20px; border-radius: 20px; margin-bottom: 24px; text-align: center; font-size: 16px; }
        .success-message { background: #DAA520; color: white; padding: 14px 20px; border-radius: 20px; margin-bottom: 24px; text-align: center; font-size: 16px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #000000; font-size: 16px; }
        .form-group input { width: 100%; padding: 14px 18px; border: 1px solid #FFDAB9; border-radius: 20px; font-size: 16px; background: #FFFDD0; transition: all 0.2s; font-family: 'Oswald', sans-serif; }
        .form-group input:focus { outline: none; border-color: #DAA520; box-shadow: 0 0 0 3px rgba(218,165,32,0.1); }
        .form-group input.error-input { border-color: #DC143C; background: #ffe6e5; }
        .error-hint { font-size: 12px; color: #DC143C; margin-top: 5px; display: block; }
        .hint { font-size: 12px; color: #006400; margin-top: 5px; display: block; }
        .btn-register { width: 100%; background: #DAA520; color: white; border: none; padding: 14px; border-radius: 30px; font-size: 18px; font-weight: 600; font-family: 'Oswald', sans-serif; cursor: pointer; transition: all 0.2s; margin-top: 12px; }
        .btn-register:hover { background: #DC143C; transform: translateY(-2px); }
        .form-footer { margin-top: 28px; text-align: center; border-top: 1px solid #FFDAB9; padding-top: 24px; }
        .form-footer p { color: #006400; font-size: 12px; margin-bottom: 12px; }
        .form-footer a { color: #DAA520; text-decoration: none; font-weight: 500; font-size: 16px; }
        .form-footer a:hover { color: #DC143C; }
        @media (max-width: 550px) { .container { padding: 28px 20px; } .logo h1 { font-size: 28px; } }
    </style>
</head>
<body>
<div class="container">
    <div class="logo"><h1>🍽️ Банкетам.Нет</h1></div>
    <div class="form-header"><h2>Создание аккаунта</h2><p>Заполните форму для регистрации</p></div>
    <?php if ($error && !$field_errors): ?><div class="error-message">⚠️ <?= $error_message ?></div><?php endif; ?>
    <?php if ($success): ?><div class="success-message">✅ Регистрация успешна! Перенаправление...</div><?php endif; ?>
    <?php if (!$success): ?>
    <form method="POST" id="registerForm">
        <div class="form-group"><label>👤 ФИО</label><input type="text" name="fullname" id="fullname" value="<?= htmlspecialchars($form_data['fullname']??'') ?>" placeholder="Иванов Иван Иванович"><span id="fullname-error" class="error-hint"><?= $field_errors['fullname'] ?? '' ?></span></div>
        <div class="form-group"><label>📱 Телефон</label><input type="tel" name="phone" id="phone" placeholder="+7(XXX)XXX-XX-XX" pattern="\+7\(\d{3}\)\d{3}-\d{2}-\d{2}" value="<?= htmlspecialchars($form_data['phone']??'') ?>"><span id="phone-error" class="error-hint"><?= $field_errors['phone'] ?? '' ?></span><span class="hint">Формат: +7(XXX)XXX-XX-XX</span></div>
        <div class="form-group"><label>📧 Email</label><input type="email" name="email" id="email" value="<?= htmlspecialchars($form_data['email']??'') ?>" placeholder="example@mail.com"><span id="email-error" class="error-hint"><?= $field_errors['email'] ?? '' ?></span></div>
        <div class="form-group"><label>🔑 Логин</label><input type="text" name="login" id="login" pattern="[a-zA-Z0-9]{6,}" value="<?= htmlspecialchars($form_data['login']??'') ?>" placeholder="ivan123"><span id="login-error" class="error-hint"><?= $field_errors['login'] ?? '' ?></span><span class="hint">Только латиница+цифры, мин. 6 символов</span></div>
        <div class="form-group"><label>🔒 Пароль</label><input type="password" name="password" id="password" minlength="8" placeholder="Минимум 8 символов"><span id="password-error" class="error-hint"><?= $field_errors['password'] ?? '' ?></span><span class="hint">Минимум 8 символов</span></div>
        <button type="submit" class="btn-register">🎉 Зарегистрироваться</button>
    </form>
    <?php endif; ?>
    <div class="form-footer"><p>Уже есть аккаунт? <a href="login.php">Войти →</a></p><a href="index.php">← Вернуться на главную</a></div>
</div>
<script>
    function validateField(fieldId, errorId, validationFn, errorMsg) {
        const field = document.getElementById(fieldId);
        const errorSpan = document.getElementById(errorId);
        if (!field) return;
        const value = field.value.trim();
        if (value === '') { errorSpan.textContent = 'Поле обязательно'; field.classList.add('error-input'); return false; }
        if (validationFn && !validationFn(value)) { errorSpan.textContent = errorMsg; field.classList.add('error-input'); return false; }
        errorSpan.textContent = ''; field.classList.remove('error-input'); return true;
    }
    document.getElementById('fullname')?.addEventListener('input', function() { validateField('fullname', 'fullname-error', v=>v.length>=5, 'Введите полное ФИО'); });
    document.getElementById('phone')?.addEventListener('input', function() { validateField('phone', 'phone-error', v=>/^\+7\(\d{3}\)\d{3}-\d{2}-\d{2}$/.test(v), 'Формат: +7(XXX)XXX-XX-XX'); });
    document.getElementById('email')?.addEventListener('input', function() { validateField('email', 'email-error', v=>/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v), 'Введите корректный email'); });
    document.getElementById('login')?.addEventListener('input', function() { validateField('login', 'login-error', v=>/^[a-zA-Z0-9]{6,}$/.test(v), 'Только латиница+цифры, мин. 6 символов'); });
    document.getElementById('password')?.addEventListener('input', function() { validateField('password', 'password-error', v=>v.length>=8, 'Минимум 8 символов'); });
</script>
</body>
</html>