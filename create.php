<?php
session_start();
if (!isset($_SESSION['user_id'])) die('Чтобы оставить заявку, надо войти в аккаунт.');

$success = false;
$error = false;
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $review = $_POST['review'];
    $date = $_POST['date'];
    $venue = $_POST['venue'];
    $payment = $_POST['payment'];
    $status = 'Новая';
    include('db.php');
    $user_id = (int)$_SESSION['user_id'];
    $review = $con->real_escape_string($review);
    $venue = $con->real_escape_string($venue);
    $payment = $con->real_escape_string($payment);
    $query = $con->query("INSERT INTO request (review, date, curses, payment, user_id, status) VALUES ('$review', '$date', '$venue', '$payment', '$user_id', '$status')");
    if (!$query) { $error = true; $error_msg = 'Ошибка: ' . $con->error; }
    else { $success = true; }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Бронирование площадки — Банкетам.Нет</title>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { background: #FFFDD0; font-family: 'Oswald', sans-serif; padding: 40px 20px; min-height: 100vh; }
        .container { max-width: 680px; margin: 0 auto; background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: white; padding: 32px 32px 24px; border-bottom: 1px solid #FFDAB9; text-align: center; }
        .header h1 { font-family: 'Oswald', sans-serif; font-size: 36px; color: #DC143C; margin-bottom: 8px; }
        .header p { color: #006400; font-size: 12px; }
        .nav-buttons { display: flex; gap: 12px; padding: 16px 32px; background: #FFFDD0; border-bottom: 1px solid #FFDAB9; justify-content: center; }
        .nav-buttons a { padding: 8px 24px; border-radius: 30px; background: #FFDAB9; color: #DAA520; text-decoration: none; font-weight: 500; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; font-size: 16px; }
        .nav-buttons a:hover { background: #DAA520; color: white; transform: translateY(-2px); }
        .success-message { background: #DAA520; color: white; padding: 16px 24px; margin: 24px 32px; border-radius: 20px; text-align: center; font-size: 16px; }
        .success-message a { color: white; text-decoration: underline; }
        .error-message { background: #FFDAB9; color: #DC143C; padding: 16px 24px; margin: 24px 32px; border-radius: 20px; text-align: center; font-size: 16px; }
        .form-container { padding: 32px; }
        .form-group { margin-bottom: 28px; }
        .form-group label { display: block; margin-bottom: 10px; font-weight: 600; color: #000000; font-size: 16px; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 14px 18px; border: 1px solid #FFDAB9; border-radius: 20px; font-size: 16px;
            font-family: 'Oswald', sans-serif; background: #FFFDD0; transition: all 0.2s;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none; border-color: #DAA520; box-shadow: 0 0 0 3px rgba(218, 165, 32, 0.1);
        }
        .form-group textarea { resize: vertical; min-height: 100px; }
        .hint { font-size: 12px; color: #006400; margin-top: 8px; display: block; }
        .btn-submit { width: 100%; background: #DAA520; color: white; border: none; padding: 16px; border-radius: 30px; font-size: 18px; font-weight: 600; font-family: 'Oswald', sans-serif; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 10px; }
        .btn-submit:hover { background: #DC143C; transform: translateY(-2px); }
        @media (max-width: 550px) { .header h1 { font-size: 28px; } .form-container { padding: 24px; } .nav-buttons { flex-direction: column; align-items: center; } .nav-buttons a { width: 100%; justify-content: center; } }
    </style>
</head>
<body>
<div class="container">
    <div class="header"><h1>🎉 Бронирование площадки</h1><p>Заполните форму, и мы свяжемся с вами</p></div>
    <div class="nav-buttons"><a href="index.php">🏠 Главная</a><a href="history.php">📋 Мои заявки</a></div>
    <?php if ($success): ?>
        <div class="success-message">✅ Заявка успешно отправлена!<br><br><a href="history.php">📋 Перейти к истории моих заявок →</a><br><br>🍽️ Спасибо, что выбрали нас! Мы свяжемся с вами в ближайшее время.</div>
    <?php elseif ($error): ?>
        <div class="error-message">❌ Ошибка при отправке заявки: <?= htmlspecialchars($error_msg); ?><br><a href="javascript:history.back()" style="color:#DC143C;">◀ Попробовать снова</a></div>
    <?php endif; ?>
    <?php if (!$success): ?>
    <div class="form-container">
        <form method="POST">
            <div class="form-group"><label>🍽️ Тип помещения</label><select name="venue" required><option value="Банкетный зал">🏛️ Банкетный зал</option><option value="Ресторан">🍷 Ресторан</option><option value="Летняя веранда">🌞 Летняя веранда</option><option value="Закрытая веранда">🏠 Закрытая веранда</option></select></div>
            <div class="form-group"><label>📅 Дата и время проведения</label><input type="datetime-local" name="date" required><span class="hint">Выберите удобную дату и время для банкета</span></div>
            <div class="form-group"><label>💳 Способ оплаты</label><select name="payment" required><option value="наличные">💵 Наличные</option><option value="перевод">🏦 Переводом по номеру</option><option value="карта">💳 Банковской картой</option></select></div>
            <div class="form-group"><label>📝 Дополнительные пожелания</label><textarea name="review" placeholder="Опишите особые пожелания: меню, декор, музыкальное сопровождение и т.д..."></textarea><span class="hint">Расскажите, как мы можем сделать ваш праздник особенным</span></div>
            <button type="submit" class="btn-submit">🎉 Забронировать</button>
        </form>
    </div>
    <?php endif; ?>
</div>
</body>
</html>