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