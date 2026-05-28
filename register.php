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
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: #fef9f0;
            font-family: 'Inter', sans-serif;
            padding: 40px 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .container {
            max-width: 560px;
            width: 100%;
            margin: 0 auto;
            background: white;
            border-radius: 40px;
            box-shadow: 0 25px 45px -12px rgba(0, 0, 0, 0.1);
            padding: 40px 32px;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .logo h1 {
            font-family: 'Oswald', sans-serif;
            font-size: 34px;
            background: linear-gradient(135deg, #b45f2b, #e6a05e);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 28px;
        }
        
        .form-header h2 {
            color: #3a2c1f;
            font-weight: 600;
            font-size: 26px;
            margin-bottom: 8px;
        }
        
        .form-header p {
            color: #9b8a74;
            font-size: 14px;
        }
        
        .error-message {
            background: #ffe6e5;
            color: #b13b2d;
            padding: 14px 20px;
            border-radius: 28px;
            margin-bottom: 24px;
            text-align: center;
            font-size: 14px;
        }
        
        .success-message {
            background: #e2f3e4;
            color: #2b6e3c;
            padding: 14px 20px;
            border-radius: 28px;
            margin-bottom: 24px;
            text-align: center;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #3a2c1f;
            font-size: 14px;
        }
        
        .form-group input {
            width: 100%;
            padding: 14px 18px;
            border: 1.5px solid #e5d5c0;
            border-radius: 32px;
            font-size: 15px;
            background: #fefaf5;
            transition: all 0.2s;
            font-family: 'Inter', sans-serif;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #b45f2b;
            box-shadow: 0 0 0 3px rgba(180, 95, 43, 0.1);
        }
        
        .form-group input:hover {
            border-color: #d4b48c;
        }
        
        .hint {
            font-size: 12px;
            color: #9b8a74;
            margin-top: 6px;
            display: block;
        }
        
        .btn-register {
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
            transition: all 0.2s;
            margin-top: 12px;
        }
        
        .btn-register:hover {
            background: #9c4f22;
            transform: translateY(-2px);
        }
        
        .form-footer {
            margin-top: 28px;
            text-align: center;
            border-top: 1px solid #f0e2cf;
            padding-top: 24px;
        }
        
        .form-footer p {
            color: #6b5a48;
            margin-bottom: 12px;
        }
        
        .form-footer a {
            color: #b45f2b;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        
        .form-footer a:hover {
            color: #9c4f22;
            text-decoration: underline;
        }
        
        @media (max-width: 550px) {
            .container {
                padding: 28px 20px;
            }
            .logo h1 {
                font-size: 28px;
            }
            .form-header h2 {
                font-size: 22px;
            }
        }
    </style>
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