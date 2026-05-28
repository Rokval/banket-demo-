<?php
session_start();
if(!isset($_SESSION['user_id'])) die('Чтобы посмотреть историю заявок, надо войти в аккаунт.');
include('db.php');

// Обработка отзыва
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['review'])) {
    $review = $con->real_escape_string($_POST['review']);
    $user_id = (int)$_SESSION['user_id'];
    $request_id = (int)$_POST['request_id'];
    $con->query("UPDATE request SET review='$review' WHERE id='$request_id' AND user_id='$user_id'");
    $review_success = true;
}

// Получение заявок пользователя
$user_id = (int)$_SESSION['user_id'];
$query = $con->query("SELECT * FROM request WHERE user_id='$user_id' ORDER BY date DESC");
if(!$query) die('Ошибка запроса: ' . $con->error);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои заявки — Банкетам.Нет</title>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 40px;
            box-shadow: 0 25px 45px -12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        /* Шапка */
        .header {
            background: linear-gradient(135deg, #ffffff, #fefaf5);
            padding: 32px 32px 24px;
            border-bottom: 1px solid #f0e2cf;
            text-align: center;
        }
        
        .header h1 {
            font-family: 'Oswald', sans-serif;
            font-size: 32px;
            color: #b45f2b;
            margin-bottom: 8px;
        }
        
        .header p {
            color: #9b8a74;
            font-size: 14px;
        }
        
        /* Навигация */
        .nav-buttons {
            display: flex;
            gap: 12px;
            padding: 16px 32px;
            background: #fefaf5;
            border-bottom: 1px solid #f0e2cf;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        
        .nav-buttons a {
            padding: 8px 24px;
            border-radius: 40px;
            background: #f5ede2;
            color: #b45f2b;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .nav-buttons a:hover {
            background: #b45f2b;
            color: white;
            transform: translateY(-2px);
        }
        
        /* Приветствие */
        .welcome {
            background: #fefaf5;
            padding: 20px 32px;
            border-bottom: 1px solid #f0e2cf;
        }
        
        .welcome h2 {
            font-family: 'Oswald', sans-serif;
            font-size: 22px;
            color: #3a2c1f;
        }
        
        .welcome h2 i {
            color: #b45f2b;
            margin-right: 8px;
        }
        
        .welcome p {
            color: #9b8a74;
            margin-top: 6px;
        }
        
        /* Сообщение об успехе */
        .success-message {
            background: #e2f3e4;
            color: #2b6e3c;
            padding: 14px 24px;
            margin: 20px 32px;
            border-radius: 28px;
            text-align: center;
        }
        
        /* Статистика */
        .stats-mini {
            display: flex;
            gap: 16px;
            padding: 0 32px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }
        
        .stat-mini-card {
            background: #fefaf5;
            padding: 12px 20px;
            border-radius: 28px;
            border: 1px solid #f0e2cf;
            flex: 1;
            min-width: 120px;
            text-align: center;
        }
        
        .stat-mini-number {
            font-size: 28px;
            font-weight: 700;
            color: #b45f2b;
            font-family: 'Oswald', sans-serif;
        }
        
        .stat-mini-label {
            font-size: 12px;
            color: #9b8a74;
            margin-top: 4px;
        }
        
        /* Карточка заявки */
        .request-list {
            padding: 0 32px 32px;
        }
        
        .request-card {
            background: white;
            border: 1px solid #f0e2cf;
            border-radius: 28px;
            margin-bottom: 24px;
            overflow: hidden;
            transition: all 0.2s;
        }
        
        .request-card:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.05);
            border-color: #e6d5bd;
        }
        
        .request-header {
            background: #fefaf5;
            padding: 18px 24px;
            border-bottom: 1px solid #f0e2cf;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }
        
        .request-number {
            font-family: 'Oswald', sans-serif;
            font-size: 18px;
            font-weight: 600;
            color: #b45f2b;
        }
        
        .request-number i {
            margin-right: 8px;
        }
        
        .status-badge {
            padding: 5px 14px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-new {
            background: #fff0e0;
            color: #b45f2b;
        }
        
        .status-assigned {
            background: #e2f3e4;
            color: #2b6e3c;
        }
        
        .status-completed {
            background: #e8e4d8;
            color: #6b5a48;
        }
        
        .request-body {
            padding: 24px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .info-icon {
            width: 36px;
            height: 36px;
            background: #fefaf5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #b45f2b;
        }
        
        .info-text {
            flex: 1;
        }
        
        .info-label {
            font-size: 11px;
            color: #9b8a74;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-value {
            font-size: 15px;
            font-weight: 500;
            color: #3a2c1f;
        }
        
        /* Отзыв */
        .review-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #f0e2cf;
        }
        
        .review-text {
            background: #fefaf5;
            padding: 14px 18px;
            border-radius: 20px;
            color: #3a2c1f;
            font-size: 14px;
        }
        
        .review-text i {
            color: #b45f2b;
            margin-right: 8px;
        }
        
        .review-form {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        .review-form input {
            flex: 1;
            padding: 12px 18px;
            border: 1.5px solid #e5d5c0;
            border-radius: 40px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            background: #fefaf5;
        }
        
        .review-form input:focus {
            outline: none;
            border-color: #b45f2b;
        }
        
        .review-form button {
            padding: 12px 24px;
            background: #b45f2b;
            color: white;
            border: none;
            border-radius: 40px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .review-form button:hover {
            background: #9c4f22;
            transform: translateY(-2px);
        }
        
        /* Пустое состояние */
        .empty-state {
            text-align: center;
            padding: 60px 32px;
        }
        
        .empty-state i {
            font-size: 64px;
            color: #e2cfb5;
            margin-bottom: 16px;
        }
        
        .empty-state h3 {
            color: #3a2c1f;
            margin-bottom: 8px;
            font-family: 'Oswald', sans-serif;
        }
        
        .empty-state p {
            color: #9b8a74;
            margin-bottom: 24px;
        }
        
        .btn-create {
            background: #b45f2b;
            color: white;
            padding: 12px 32px;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 500;
            display: inline-block;
            transition: all 0.2s;
        }
        
        .btn-create:hover {
            background: #9c4f22;
            transform: translateY(-2px);
        }
        
        /* Адаптивность */
        @media (max-width: 650px) {
            .header h1 { font-size: 26px; }
            .nav-buttons { flex-direction: column; }
            .nav-buttons a { justify-content: center; }
            .request-header { flex-direction: column; align-items: flex-start; }
            .info-grid { grid-template-columns: 1fr; }
            .review-form { flex-direction: column; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 Мои заявки</h1>
            <p>История ваших бронирований</p>
        </div>
        
        <div class="nav-buttons">
            <a href="index.php">🏠 Главная</a>
            <a href="create.php">🎉 Новая заявка</a>
            <a href="?logout=1" onclick="return confirm('Выйти из аккаунта?')" style="background:#ffe6e5; color:#b13b2d;">🚪 Выход</a>
        </div>
        
        <div class="welcome">
            <h2><i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['user_fullname'] ?? $_SESSION['user_login'] ?? 'Пользователь') ?></h2>
            <p>Здесь вы можете отслеживать статус ваших заявок на банкет</p>
        </div>
        
        <?php if (isset($review_success) && $review_success): ?>
            <div class="success-message">
                ✅ Отзыв успешно добавлен! Спасибо за ваше мнение.
            </div>
        <?php endif; ?>
        
        <?php
        // Подсчёт статистики для пользователя
        $stats_query = $con->query("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'Новая' THEN 1 ELSE 0 END) as new_count,
            SUM(CASE WHEN status = 'Банкет назначен' THEN 1 ELSE 0 END) as assigned_count,
            SUM(CASE WHEN status = 'Банкет завершен' THEN 1 ELSE 0 END) as completed_count
            FROM request WHERE user_id='$user_id'");
        $user_stats = $stats_query->fetch_assoc();
        ?>
        
        <div class="stats-mini">
            <div class="stat-mini-card">
                <div class="stat-mini-number"><?= $user_stats['total'] ?></div>
                <div class="stat-mini-label">Всего заявок</div>
            </div>
            <div class="stat-mini-card">
                <div class="stat-mini-number" style="color:#b45f2b;"><?= $user_stats['new_count'] ?></div>
                <div class="stat-mini-label">В обработке</div>
            </div>
            <div class="stat-mini-card">
                <div class="stat-mini-number" style="color:#2b6e3c;"><?= $user_stats['assigned_count'] ?></div>
                <div class="stat-mini-label">Подтверждены</div>
            </div>
            <div class="stat-mini-card">
                <div class="stat-mini-number" style="color:#9b8a74;"><?= $user_stats['completed_count'] ?></div>
                <div class="stat-mini-label">Завершены</div>
            </div>
        </div>
        
        <div class="request-list">
            <?php if ($query->num_rows == 0): ?>
                <div class="empty-state">
                    <i class="fas fa-glass-cheers"></i>
                    <h3>У вас пока нет заявок</h3>
                    <p>Создайте первую заявку на банкет прямо сейчас!</p>
                    <a href="create.php" class="btn-create">🎉 Создать заявку</a>
                </div>
            <?php else:
                while ($request = $query->fetch_assoc()):
                    $status_class = match($request['status']) {
                        'Новая' => 'status-new',
                        'Банкет назначен' => 'status-assigned',
                        'Банкет завершен' => 'status-completed',
                        default => 'status-new'
                    };
            ?>
                <div class="request-card">
                    <div class="request-header">
                        <div class="request-number">
                            <i class="fas fa-receipt"></i> Заявка №<?= $request['id'] ?>
                        </div>
                        <span class="status-badge <?= $status_class ?>">
                            <?= htmlspecialchars($request['status']) ?>
                        </span>
                    </div>
                    
                    <div class="request-body">
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-icon"><i class="fas fa-calendar-alt"></i></div>
                                <div class="info-text">
                                    <div class="info-label">Дата и время</div>
                                    <div class="info-value"><?= htmlspecialchars($request['date']) ?></div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon"><i class="fas fa-utensils"></i></div>
                                <div class="info-text">
                                    <div class="info-label">Тип площадки</div>
                                    <div class="info-value"><?= htmlspecialchars($request['curses']) ?></div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon"><i class="fas fa-credit-card"></i></div>
                                <div class="info-text">
                                    <div class="info-label">Способ оплаты</div>
                                    <div class="info-value"><?= htmlspecialchars($request['payment']) ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!empty($request['review'])): ?>
                            <div class="review-section">
                                <div class="review-text">
                                    <i class="fas fa-star"></i> 
                                    <strong>Ваш отзыв:</strong> <?= htmlspecialchars($request['review']) ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($request['status'] === 'Банкет завершен'): ?>
                            <div class="review-section">
                                <form method="POST" class="review-form">
                                    <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                    <input type="text" name="review" placeholder="✍️ Оставьте отзыв о проведённом банкете..." value="<?= htmlspecialchars($request['review'] ?? '') ?>">
                                    <button type="submit"><i class="fas fa-star"></i> Оставить отзыв</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php 
                endwhile;
            endif;
            ?>
        </div>
    </div>
</body>
</html>