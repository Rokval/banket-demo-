<?php
include('db.php');
session_start();

// Проверка авторизации администратора
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: login.php');
    exit;
}

// Обработка выхода
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

// Допустимые статусы
$valid_statuses = ['Новая', 'Банкет назначен', 'Банкет завершен'];
$status_updated = false;

// Обработка изменения статуса заявки
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_id'])) {
    $request_id = (int)$_POST['request_id'];
    $status = $_POST['status'] ?? '';
    
    if (!in_array($status, $valid_statuses, true)) {
        die('Недопустимый статус заявки');
    }
    
    $stmt = $con->prepare("UPDATE request SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $status, $request_id);
    
    if (!$stmt->execute()) {
        die('Ошибка обновления: ' . $con->error);
    } else {
        $status_updated = true;
    }
}

// Получение заявок с пагинацией
$page = (int)($_GET['page'] ?? 1);
$limit = 10;
$offset = ($page - 1) * $limit;

$query = $con->query("
    SELECT request.*, users.login, users.fullname,
           COUNT(*) OVER() as total_count
    FROM request
    INNER JOIN users ON request.user_id = users.id
    ORDER BY request.date DESC
    LIMIT $limit OFFSET $offset
");

if (!$query) die('Ошибка запроса: ' . $con->error);

// Подсчёт статистики
$stats_query = $con->query("
    SELECT
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Новая' THEN 1 ELSE 0 END) as new_requests,
        SUM(CASE WHEN status = 'Банкет назначен' THEN 1 ELSE 0 END) as assigned,
        SUM(CASE WHEN status = 'Банкет завершен' THEN 1 ELSE 0 END) as completed
    FROM request
");
$stats = $stats_query->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора — Банкетам.Нет</title>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-champagne-glasses"></i> Панель администратора</h1>
            <p>Управление заявками на банкетные площадки</p>
        </div>
        
        <div class="nav-bar">
            <a href="index.php" class="btn-outline">
                <i class="fas fa-home"></i> Главная
            </a>
            <a href="?logout=1" class="btn-outline" onclick="return confirm('Выйти из аккаунта?')">
                <i class="fas fa-sign-out-alt"></i> Выход
            </a>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $stats['total'] ?></div>
                <div class="stat-label">Всего заявок</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #b45f2b;"><?= $stats['new_requests'] ?></div>
                <div class="stat-label">🆕 Новые</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #2b6e3c;"><?= $stats['assigned'] ?></div>
                <div class="stat-label">🍽️ Банкет назначен</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #9b8a74;"><?= $stats['completed'] ?></div>
                <div class="stat-label">✅ Банкет завершен</div>
            </div>
        </div>
        
        <div class="requests-container">
            <h2 class="section-title"><i class="fas fa-clipboard-list"></i> Список заявок</h2>
            
            <?php if ($query->num_rows === 0): ?>
                <div class="empty-state">
                    <i class="fas fa-glass-cheers"></i>
                    <h3>Заявок пока нет</h3>
                    <p>Когда пользователи оставят заявки на банкет, они появятся здесь</p>
                </div>
            <?php else:
                while ($request = $query->fetch_assoc()):
                    // Определяем класс статуса (без использования match для совместимости)
                    $status_class = '';
                    if ($request['status'] == 'Новая') {
                        $status_class = 'status-new';
                    } elseif ($request['status'] == 'Банкет назначен') {
                        $status_class = 'status-assigned';
                    } elseif ($request['status'] == 'Банкет завершен') {
                        $status_class = 'status-completed';
                    } else {
                        $status_class = 'status-new';
                    }
            ?>
                <div class="request-item">
                    <div class="request-header">
                        <div class="user-info">
                            <h3><i class="fas fa-user"></i> <?= htmlspecialchars($request['login']) ?></h3>
                            <p><?= htmlspecialchars($request['fullname']) ?></p>
                        </div>
                        <div class="request-meta">
                            <span class="request-id">Заявка №<?= htmlspecialchars($request['id']) ?></span>
                            <span class="status-badge <?= $status_class ?>"><?= htmlspecialchars($request['status']) ?></span>
                        </div>
                    </div>
                    
                    <div class="request-details">
                        <div class="detail-item">
                            <div class="detail-label"><i class="far fa-calendar-alt"></i> Дата и время</div>
                            <div class="detail-value"><?= htmlspecialchars($request['date']) ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label"><i class="fas fa-utensils"></i> Тип площадки</div>
                            <div class="detail-value"><?= htmlspecialchars($request['curses'] ?? '—') ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label"><i class="fas fa-credit-card"></i> Способ оплаты</div>
                            <div class="detail-value"><?= htmlspecialchars($request['payment'] ?? '—') ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label"><i class="fas fa-comment"></i> Доп. информация</div>
                            <div class="detail-value"><?= htmlspecialchars($request['review'] ?? '—') ?></div>
                        </div>
                    </div>
                    
                    <div class="status-form">
                        <form method="POST">
                            <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                            <div class="form-group">
                                <label class="form-label"><i class="fas fa-tag"></i> Изменить статус заявки:</label>
                                <select name="status" class="form-select">
                                    <option value="Новая" <?= $request['status'] == 'Новая' ? 'selected' : '' ?>>🆕 Новая</option>
                                    <option value="Банкет назначен" <?= $request['status'] == 'Банкет назначен' ? 'selected' : '' ?>>🍽️ Банкет назначен</option>
                                    <option value="Банкет завершен" <?= $request['status'] == 'Банкет завершен' ? 'selected' : '' ?>>✅ Банкет завершен</option>
                                </select>
                            </div>
                            <button type="submit" class="btn-save">
                                <i class="fas fa-save"></i> Сохранить изменения
                            </button>
                        </form>
                    </div>
                </div>
            <?php 
                endwhile;
            endif;
            ?>
        </div>
        
        <?php if ($stats['total'] > $limit): ?>
            <div class="pagination">
                <?php
                $total_pages = ceil($stats['total'] / $limit);
                for ($i = 1; $i <= $total_pages; $i++):
                ?>
                    <a href="?page=<?= $i ?>" class="page-link <?= $page === $i ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if ($status_updated): ?>
        <div class="notification">
            <i class="fas fa-check-circle"></i> Статус заявки успешно обновлён!
        </div>
    <?php endif; ?>
</body>
</html>