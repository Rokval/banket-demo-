<?php
include('db.php');
session_start();

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

$valid_statuses = ['Новая', 'Банкет назначен', 'Банкет завершен'];
$status_updated = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_id'])) {
    $request_id = (int)$_POST['request_id'];
    $status = $_POST['status'] ?? '';
    if (!in_array($status, $valid_statuses, true)) die('Недопустимый статус');
    $stmt = $con->prepare("UPDATE request SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $status, $request_id);
    if ($stmt->execute()) $status_updated = true;
    else die('Ошибка обновления');
}

$page = (int)($_GET['page'] ?? 1);
$limit = 10;
$offset = ($page - 1) * $limit;
$query = $con->query("SELECT request.*, users.login, users.fullname, COUNT(*) OVER() as total_count FROM request INNER JOIN users ON request.user_id = users.id ORDER BY request.date DESC LIMIT $limit OFFSET $offset");
if (!$query) die('Ошибка запроса');
$stats_query = $con->query("SELECT COUNT(*) as total, SUM(CASE WHEN status='Новая' THEN 1 ELSE 0 END) as new_requests, SUM(CASE WHEN status='Банкет назначен' THEN 1 ELSE 0 END) as assigned, SUM(CASE WHEN status='Банкет завершен' THEN 1 ELSE 0 END) as completed FROM request");
$stats = $stats_query->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора — Банкетам.Нет</title>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { background: #FFFDD0; font-family: 'Oswald', sans-serif; padding: 20px; min-height: 100vh; }
        .container { max-width: 1280px; margin: 0 auto; background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .header { background: white; padding: 28px 32px; border-bottom: 1px solid #FFDAB9; }
        .header h1 { font-family: 'Oswald', sans-serif; font-size: 36px; color: #DC143C; font-weight: 600; }
        .header p { color: #006400; margin-top: 8px; font-size: 12px; }
        .nav-bar { display: flex; justify-content: space-between; align-items: center; padding: 16px 32px; background: #FFFDD0; border-bottom: 1px solid #FFDAB9; }
        .btn-outline { padding: 8px 24px; border-radius: 30px; background: #FFDAB9; color: #DAA520; text-decoration: none; font-weight: 500; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; font-size: 16px; }
        .btn-outline:hover { background: #DAA520; color: white; transform: translateY(-2px); }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; padding: 32px; background: #FFFDD0; }
        .stat-card { background: white; border-radius: 20px; padding: 24px 20px; text-align: center; border: 1px solid #FFDAB9; transition: all 0.2s; }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        .stat-number { font-size: 42px; font-weight: 700; color: #DAA520; font-family: 'Oswald', sans-serif; }
        .stat-label { color: #006400; font-size: 12px; margin-top: 8px; }
        .requests-container { padding: 0 32px 32px; }
        .section-title { font-family: 'Oswald', sans-serif; font-size: 24px; color: #DAA520; margin-bottom: 24px; font-weight: 600; }
        .request-item { background: white; border-radius: 20px; padding: 24px; margin-bottom: 24px; border: 1px solid #FFDAB9; transition: all 0.2s; }
        .request-item:hover { box-shadow: 0 10px 25px rgba(0,0,0,0.05); border-color: #DAA520; }
        .request-header { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; margin-bottom: 20px; gap: 12px; }
        .user-info h3 { color: #000000; font-size: 18px; font-weight: 600; margin-bottom: 4px; }
        .user-info p { color: #006400; font-size: 12px; }
        .request-meta { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }
        .request-id { background: #FFDAB9; padding: 5px 14px; border-radius: 20px; font-size: 12px; font-weight: 500; color: #DAA520; }
        .status-badge { padding: 5px 14px; border-radius: 20px; font-size: 12px; font-weight: 500; }
        .status-new { background: #FFDAB9; color: #DAA520; }
        .status-assigned { background: #DAA520; color: white; }
        .status-completed { background: #006400; color: white; }
        .request-details { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin: 20px 0; }
        .detail-item { background: #FFFDD0; padding: 14px 16px; border-radius: 15px; border: 1px solid #FFDAB9; }
        .detail-label { font-size: 12px; color: #006400; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
        .detail-value { font-size: 16px; color: #000000; font-weight: 500; word-wrap: break-word; white-space: pre-wrap; }
        .status-form { margin-top: 20px; padding-top: 20px; border-top: 1px solid #FFDAB9; }
        .form-group { margin-bottom: 15px; }
        .form-label { display: block; margin-bottom: 8px; font-weight: 500; color: #000000; font-size: 16px; }
        .form-select { width: 100%; padding: 12px 16px; border: 1px solid #FFDAB9; border-radius: 20px; font-size: 14px; font-family: 'Oswald', sans-serif; background: #FFFDD0; transition: all 0.2s; cursor: pointer; }
        .form-select:focus { outline: none; border-color: #DAA520; box-shadow: 0 0 0 3px rgba(218,165,32,0.1); }
        .btn-save { width: 100%; padding: 12px; background: #DAA520; color: white; border: none; border-radius: 30px; cursor: pointer; font-family: 'Oswald', sans-serif; font-size: 16px; font-weight: 500; transition: all 0.2s; }
        .btn-save:hover { background: #DC143C; transform: translateY(-2px); }
        .pagination { display: flex; justify-content: center; gap: 10px; margin-top: 32px; padding-bottom: 10px; }
        .page-link { padding: 8px 16px; border: 1px solid #FFDAB9; border-radius: 20px; text-decoration: none; color: #DAA520; font-weight: 500; transition: all 0.2s; }
        .page-link:hover, .page-link.active { background: #DAA520; color: white; border-color: #DAA520; }
        .empty-state { text-align: center; padding: 60px 20px; background: #FFFDD0; border-radius: 20px; }
        .empty-state h3 { color: #DC143C; margin-bottom: 8px; font-family: 'Oswald', sans-serif; font-size: 24px; }
        .empty-state p { color: #006400; font-size: 12px; }
        .notification { position: fixed; top: 20px; right: 20px; padding: 14px 28px; background: #DAA520; color: white; border-radius: 30px; box-shadow: 0 8px 20px rgba(0,0,0,0.1); z-index: 1000; animation: slideInRight 0.3s ease-out, fadeOut 0.3s ease-out 2.7s forwards; font-weight: 500; }
        @keyframes slideInRight { from { opacity: 0; transform: translateX(100px); } to { opacity: 1; transform: translateX(0); } }
        @keyframes fadeOut { to { opacity: 0; visibility: hidden; } }
        @media (max-width: 768px) { .header h1 { font-size: 28px; } .nav-bar { flex-direction: column; gap: 12px; } .stats-grid { grid-template-columns: 1fr; } .request-header { flex-direction: column; align-items: flex-start; } .requests-container { padding: 0 20px 20px; } }
    </style>
</head>
<body>
<div class="container">
    <div class="header"><h1>🍽️ Панель администратора</h1><p>Управление заявками на банкетные площадки</p></div>
    <div class="nav-bar"><a href="index.php" class="btn-outline">🏠 Главная</a><a href="?logout=1" class="btn-outline" onclick="return confirm('Выйти из аккаунта?')">🚪 Выход</a></div>
    <div class="stats-grid"><div class="stat-card"><div class="stat-number"><?= $stats['total'] ?></div><div class="stat-label">Всего заявок</div></div><div class="stat-card"><div class="stat-number"><?= $stats['new_requests'] ?></div><div class="stat-label">🆕 Новые</div></div><div class="stat-card"><div class="stat-number"><?= $stats['assigned'] ?></div><div class="stat-label">🍽️ Банкет назначен</div></div><div class="stat-card"><div class="stat-number"><?= $stats['completed'] ?></div><div class="stat-label">✅ Банкет завершен</div></div></div>
    <div class="requests-container"><h2 class="section-title">📋 Список заявок</h2>
    <?php if ($query->num_rows === 0): ?>
        <div class="empty-state"><h3>Заявок пока нет</h3><p>Когда пользователи оставят заявки на банкет, они появятся здесь</p></div>
    <?php else: while ($request = $query->fetch_assoc()):
        $status_class = '';
        if ($request['status'] == 'Новая') $status_class = 'status-new';
        elseif ($request['status'] == 'Банкет назначен') $status_class = 'status-assigned';
        elseif ($request['status'] == 'Банкет завершен') $status_class = 'status-completed';
        else $status_class = 'status-new';
        
        // Получаем дополнительные пожелания из поля review
        $additional_info = !empty($request['review']) ? htmlspecialchars($request['review']) : '—';
    ?>
    <div class="request-item">
        <div class="request-header"><div class="user-info"><h3>👤 <?= htmlspecialchars($request['login']) ?></h3><p><?= htmlspecialchars($request['fullname']) ?></p></div><div class="request-meta"><span class="request-id">Заявка №<?= $request['id'] ?></span><span class="status-badge <?= $status_class ?>"><?= htmlspecialchars($request['status']) ?></span></div></div>
        <div class="request-details">
            <div class="detail-item"><div class="detail-label">📅 Дата и время</div><div class="detail-value"><?= htmlspecialchars($request['date']) ?></div></div>
            <div class="detail-item"><div class="detail-label">🍽️ Тип площадки</div><div class="detail-value"><?= htmlspecialchars($request['curses'] ?? '—') ?></div></div>
            <div class="detail-item"><div class="detail-label">💳 Способ оплаты</div><div class="detail-value"><?= htmlspecialchars($request['payment'] ?? '—') ?></div></div>
            <div class="detail-item"><div class="detail-label">📝 Дополнительные пожелания</div><div class="detail-value"><?= $additional_info ?></div></div>
        </div>
        <div class="status-form"><form method="POST"><input type="hidden" name="request_id" value="<?= $request['id'] ?>"><div class="form-group"><label class="form-label">🏷️ Изменить статус заявки:</label><select name="status" class="form-select"><option value="Новая" <?= $request['status']=='Новая' ? 'selected' : '' ?>>🆕 Новая</option><option value="Банкет назначен" <?= $request['status']=='Банкет назначен' ? 'selected' : '' ?>>🍽️ Банкет назначен</option><option value="Банкет завершен" <?= $request['status']=='Банкет завершен' ? 'selected' : '' ?>>✅ Банкет завершен</option></select></div><button type="submit" class="btn-save">💾 Сохранить изменения</button></form></div>
    </div>
    <?php endwhile; endif; ?></div>
    <?php if ($stats['total'] > $limit): $total_pages = ceil($stats['total'] / $limit); ?>
    <div class="pagination"><?php for ($i = 1; $i <= $total_pages; $i++): ?><a href="?page=<?= $i ?>" class="page-link <?= $page === $i ? 'active' : '' ?>"><?= $i ?></a><?php endfor; ?></div>
    <?php endif; ?>
</div>
<?php if ($status_updated): ?><div class="notification">✅ Статус заявки успешно обновлён!</div><?php endif; ?>
</body>
</html>