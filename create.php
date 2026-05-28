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
    
    $query = $con->query("INSERT INTO request (review, date, curses, payment, user_id, status) 
                          VALUES ('$review', '$date', '$venue', '$payment', '$user_id', '$status')");
    
    if (!$query) {
        $error = true;
        $error_msg = 'Ошибка: ' . $con->error;
    } else {
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Бронирование площадки — Банкетам.Нет</title>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@300;400;500;600;700&display=swap" rel="stylesheet">
 
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Бронирование площадки</h1>
            <p>Заполните форму, и мы свяжемся с вами</p>
        </div>
        
        <div class="nav-buttons">
            <a href="index.php">🏠 Главная</a>
            <a href="history.php">📋 Мои заявки</a>
        </div>
        
        <?php if ($success): ?>
            <div class="success-message">
                ✅ Заявка успешно отправлена!<br><br>
                <a href="history.php">📋 Перейти к истории моих заявок →</a>
                <br><br>
                🍽️ Спасибо, что выбрали нас! Мы свяжемся с вами в ближайшее время.
            </div>
        <?php elseif ($error): ?>
            <div class="error-message">
                ❌ Ошибка при отправке заявки: <?php echo htmlspecialchars($error_msg); ?><br>
                <a href="javascript:history.back()" style="color:#b13b2d;">◀ Попробовать снова</a>
            </div>
        <?php endif; ?>
        
        <?php if (!$success): ?>
        <div class="form-container">
            <form method="POST">
                <div class="form-group">
                    <label><i class="fas fa-utensils"></i> 🍽️ Тип помещения</label>
                    <select name="venue" required>
                        <option value="Банкетный зал">🏛️ Банкетный зал</option>
                        <option value="Ресторан">🍷 Ресторан</option>
                        <option value="Летняя веранда">🌞 Летняя веранда</option>
                        <option value="Закрытая веранда">🏠 Закрытая веранда</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-calendar-alt"></i> 📅 Дата и время проведения</label>
                    <input type="datetime-local" name="date" required>
                    <span class="hint">Выберите удобную дату и время для банкета</span>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-credit-card"></i> 💳 Способ оплаты</label>
                    <select name="payment" required>
                        <option value="наличные">💵 Наличные</option>
                        <option value="перевод">🏦 Переводом по номеру</option>
                        <option value="карта">💳 Банковской картой</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-comment"></i> 📝 Дополнительные пожелания</label>
                    <textarea name="review" placeholder="Опишите особые пожелания: меню, декор, музыкальное сопровождение и т.д..."></textarea>
                    <span class="hint">Расскажите, как мы можем сделать ваш праздник особенным</span>
                </div>
                
                <button type="submit" class="btn-submit">
                    🎉 Забронировать
                </button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>