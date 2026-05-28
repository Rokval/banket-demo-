<?php
session_start();

// Если пользователь уже авторизован, перенаправляем
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['admin']) && $_SESSION['admin']) {
        header('Location: admin.php');
    } else {
        header('Location: create.php');
    }
    exit;
}

$error = false;
$error_message = '';
$success = false;
$form_data = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = trim($_POST['login']);
    $password = $_POST['password'];
    $fullname = trim($_POST['fullname']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    
    $form_data = compact('login', 'fullname', 'phone', 'email');
    
    // Валидация данных
    $errors = [];
    
    if (empty($login)) {
        $errors[] = 'Логин обязателен для заполнения';
    } elseif (!preg_match('/^[a-zA-Z0-9]{6,}$/', $login)) {
        $errors[] = 'Логин должен содержать только латиницу и цифры, минимум 6 символов';
    }
    
    if (empty($password)) {
        $errors[] = 'Пароль обязателен для заполнения';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Пароль должен содержать минимум 8 символов';
    }
    
    if (empty($fullname)) {
        $errors[] = 'ФИО обязательно для заполнения';
    } elseif (strlen($fullname) < 5) {
        $errors[] = 'Введите полное ФИО';
    }
    
    if (empty($phone)) {
        $errors[] = 'Телефон обязателен для заполнения';
    } elseif (!preg_match('/^\+7\(\d{3}\)\d{3}-\d{2}-\d{2}$/', $phone)) {
        $errors[] = 'Телефон должен быть в формате +7(XXX)XXX-XX-XX';
    }
    
    if (empty($email)) {
        $errors[] = 'Email обязателен для заполнения';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Введите корректный email';
    }
    
    if (empty($errors)) {
        include('db.php');
        
        // Проверка на существование логина
        $stmt = $con->prepare("SELECT id FROM users WHERE login = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = true;
            $error_message = 'Пользователь с таким логином уже существует';
        } else {
            // Проверка на существование email
            $stmt = $con->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = true;
                $error_message = 'Пользователь с таким email уже существует';
            } else {
                // Добавляем пользователя (is_admin по умолчанию 0)
                $stmt = $con->prepare("INSERT INTO users (login, password, fullname, phone, email, is_admin) VALUES (?, ?, ?, ?, ?, 0)");
                $stmt->bind_param("sssss", $login, $password, $fullname, $phone, $email);
                
                if ($stmt->execute()) {
                    $success = true;
                    header('refresh:2;url=login.php');
                } else {
                    $error = true;
                    $error_message = 'Ошибка при регистрации: ' . $con->error;
                }
                $stmt->close();
            }
        }
        $stmt->close();
    } else {
        $error = true;
        $error_message = implode('<br>', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация — Банкетам.Нет</title>
   
  
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>🍽️ Банкетам.Нет</h1>
        </div>
        
        <div class="form-header">
            <h2>Создание аккаунта</h2>
            <p>Заполните форму для регистрации</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error-message">
                ⚠️ <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success-message">
                ✅ Регистрация успешно завершена!<br>
                <small>Перенаправление на страницу входа...</small>
            </div>
        <?php endif; ?>
        
        <?php if (!$success): ?>
        <form method="POST" action="">
            <div class="form-group">
                <label>👤 ФИО</label>
                <input type="text" name="fullname" value="<?php echo htmlspecialchars($form_data['fullname'] ?? ''); ?>" placeholder="Иванов Иван Иванович" required>
            </div>
            
            <div class="form-group">
                <label>📱 Телефон</label>
                <input type="tel" name="phone" placeholder="+7(XXX)XXX-XX-XX" pattern="\+7\(\d{3}\)\d{3}-\d{2}-\d{2}" value="<?php echo htmlspecialchars($form_data['phone'] ?? ''); ?>" required>
                <span class="hint">Формат: +7(XXX)XXX-XX-XX</span>
            </div>
            
            <div class="form-group">
                <label>📧 Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>" placeholder="example@mail.com" required>
            </div>
            
            <div class="form-group">
                <label>🔑 Логин</label>
                <input type="text" name="login" pattern="[a-zA-Z0-9]{6,}" value="<?php echo htmlspecialchars($form_data['login'] ?? ''); ?>" placeholder="ivan123" required>
                <span class="hint">Только латиница и цифры, минимум 6 символов</span>
            </div>
            
            <div class="form-group">
                <label>🔒 Пароль</label>
                <input type="password" name="password" minlength="8" placeholder="Минимум 8 символов" required>
                <span class="hint">Минимум 8 символов</span>
            </div>
            
            <button type="submit" class="btn-register">🎉 Зарегистрироваться</button>
        </form>
        <?php endif; ?>
        
        <div class="form-footer">
            <p>Уже есть аккаунт? <a href="login.php">Войти →</a></p>
            <a href="index.php">← Вернуться на главную</a>
        </div>
    </div>
</body>
</html>