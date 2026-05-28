<?php
session_start();
if(!isset($_SESSION['user_id'])) die('Чтобы посмотреть историю заявок, надо войти в аккаунт.');
include('db.php');

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['review_text'])) {
    $review = $con->real_escape_string($_POST['review_text']);
    $user_id = (int)$_SESSION['user_id'];
    $request_id = (int)$_POST['request_id'];
    $con->query("UPDATE request SET review='$review' WHERE id='$request_id' AND user_id='$user_id' AND status='Банкет завершен'");
    $review_success = true;
}

$user_id = (int)$_SESSION['user_id'];
$query = $con->query("SELECT * FROM request WHERE user_id='$user_id' ORDER BY date DESC");
if(!$query) die('Ошибка запроса');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои заявки — Банкетам.Нет</title>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { background: #FFFDD0; font-family: 'Oswald', sans-serif; padding: 40px 20px; min-height: 100vh; }
        .container { max-width: 1000px; margin: 0 auto; background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: white; padding: 32px 32px 24px; border-bottom: 1px solid #FFDAB9; text-align: center; }
        .header h1 { font-family: 'Oswald', sans-serif; font-size: 36px; color: #DC143C; margin-bottom: 8px; }
        .header p { color: #006400; font-size: 12px; }
        .nav-buttons { display: flex; gap: 12px; padding: 16px 32px; background: #FFFDD0; border-bottom: 1px solid #FFDAB9; justify-content: space-between; flex-wrap: wrap; }
        .nav-buttons a { padding: 8px 24px; border-radius: 30px; background: #FFDAB9; color: #DAA520; text-decoration: none; font-weight: 500; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; font-size: 16px; }
        .nav-buttons a:hover { background: #DAA520; color: white; transform: translateY(-2px); }
        .welcome { background: #FFFDD0; padding: 20px 32px; border-bottom: 1px solid #FFDAB9; }
        .welcome h2 { font-family: 'Oswald', sans-serif; font-size: 24px; color: #DAA520; }
        .welcome p { color: #006400; margin-top: 6px; font-size: 12px; }
        .success-message { background: #DAA520; color: white; padding: 14px 24px; margin: 20px 32px; border-radius: 20px; text-align: center; font-size: 16px; }
        .stats-mini { display: flex; gap: 16px; padding: 0 32px; margin-bottom: 24px; flex-wrap: wrap; }
        .stat-mini-card { background: #FFFDD0; padding: 12px 20px; border-radius: 20px; border: 1px solid #FFDAB9; flex: 1; min-width: 120px; text-align: center; }
        .stat-mini-number { font-size: 28px; font-weight: 700; color: #DAA520; font-family: 'Oswald', sans-serif; }
        .stat-mini-label { font-size: 12px; color: #006400; margin-top: 4px; }
        .request-list { padding: 0 32px 32px; }
        .request-card { background: white; border: 1px solid #FFDAB9; border-radius: 20px; margin-bottom: 24px; overflow: hidden; transition: all 0.2s; }
        .request-card:hover { box-shadow: 0 10px 25px rgba(0,0,0,0.05); border-color: #DAA520; }
        .request-header { background: #FFFDD0; padding: 18px 24px; border-bottom: 1px solid #FFDAB9; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; }
        .request-number { font-family: 'Oswald', sans-serif; font-size: 18px; font-weight: 600; color: #DC143C; }
        .status-badge { padding: 5px 14px; border-radius: 20px; font-size: 12px; font-weight: 500; }
        .status-new { background: #FFDAB9; color: #DAA520; }
        .status-assigned { background: #DAA520; color: white; }
        .status-completed { background: #006400; color: white; }
        .request-body { padding: 24px; }
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 20px; }
        .info-item { display: flex; align-items: center; gap: 12px; }
        .info-icon { width: 36px; height: 36px; background: #FFFDD0; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #DAA520; font-size: 18px; }
        .info-text { flex: 1; }
        .info-label { font-size: 11px; color: #006400; text-transform: uppercase; letter-spacing: 0.5px; }
        .info-value { font-size: 16px; font-weight: 500; color: #000000; word-wrap: break-word; white-space: pre-wrap; }
        .review-section { margin-top: 20px; padding-top: 20px; border-top: 1px solid #FFDAB9; }
        .review-text { background: #FFFDD0; padding: 14px 18px; border-radius: 15px; color: #000000; font-size: 16px; }
        .review-form { display: flex; gap: 12px; flex-wrap: wrap; }
        .review-form input { flex: 1; padding: 12px 18px; border: 1px solid #FFDAB9; border-radius: 30px; font-size: 14px; font-family: 'Oswald', sans-serif; background: #FFFDD0; }
        .review-form input:focus { outline: none; border-color: #DAA520; }
        .review-form button { padding: 12px 24px; background: #DAA520; color: white; border: none; border-radius: 30px; cursor: pointer; font-weight: 500; transition: all 0.2s; font-family: 'Oswald', sans-serif; font-size: 16px; }
        .review-form button:hover { background: #DC143C; transform: translateY(-2px); }
        .slideshow-container { max-width: 100%; margin-bottom: 30px; border-radius: 15px; overflow: hidden; position: relative; }
        .mySlides { display: none; }
        .mySlides img { width: 100%; height: 250px; object-fit: cover; }
        .prev, .next { position: absolute; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.5); color: white; border: none; cursor: pointer; padding: 8px 12px; border-radius: 50%; }
        .prev { left: 10px; }
        .next { right: 10px; }
        .empty-state { text-align: center; padding: 60px 32px; }
        .empty-state h3 { color: #DC143C; margin-bottom: 8px; font-family: 'Oswald', sans-serif; font-size: 24px; }
        .empty-state p { color: #006400; font-size: 12px; margin-bottom: 24px; }
        .btn-create { background: #DAA520; color: white; padding: 12px 32px; border-radius: 30px; text-decoration: none; font-weight: 500; display: inline-block; transition: all 0.2s; font-size: 16px; }
        .btn-create:hover { background: #DC143C; transform: translateY(-2px); }
        @media (max-width: 650px) { .header h1 { font-size: 28px; } .nav-buttons { flex-direction: column; } .nav-buttons a { justify-content: center; } .request-header { flex-direction: column; align-items: flex-start; } .info-grid { grid-template-columns: 1fr; } .review-form { flex-direction: column; } }
    </style>
</head>
<body>
<div class="container">
    <div class="header"><h1>📋 Мои заявки</h1><p>История ваших бронирований</p></div>
    <div class="nav-buttons"><a href="index.php">🏠 Главная</a><a href="create.php">🎉 Новая заявка</a><a href="?logout=1" onclick="return confirm('Выйти из аккаунта?')" style="background:#FFDAB9; color:#DC143C;">🚪 Выход</a></div>
    
    <!-- Слайдер -->
    <div class="slideshow-container">
        <div class="mySlides fade"><img src="foto/1.jpg" alt="Банкетный зал"></div>
        <div class="mySlides fade"><img src="foto/2.jpg" alt="Ресторан"></div>
        <div class="mySlides fade"><img src="foto/3.jpg" alt="Летняя веранда"></div>
        <div class="mySlides fade"><img src="foto/4.jpg" alt="Закрытая веранда"></div>
        <a class="prev" onclick="plusSlides(-1)">❮</a>
        <a class="next" onclick="plusSlides(1)">❯</a>
    </div>
    
    <div class="welcome"><h2>👤 <?= htmlspecialchars($_SESSION['user_fullname'] ?? $_SESSION['user_login'] ?? 'Пользователь') ?></h2><p>Здесь вы можете отслеживать статус ваших заявок на банкет</p></div>
    <?php if (isset($review_success) && $review_success): ?><div class="success-message">✅ Отзыв успешно добавлен! Спасибо за ваше мнение.</div><?php endif; ?>
    <?php $stats_query = $con->query("SELECT COUNT(*) as total, SUM(CASE WHEN status='Новая' THEN 1 ELSE 0 END) as new_count, SUM(CASE WHEN status='Банкет назначен' THEN 1 ELSE 0 END) as assigned_count, SUM(CASE WHEN status='Банкет завершен' THEN 1 ELSE 0 END) as completed_count FROM request WHERE user_id='$user_id'"); $user_stats = $stats_query->fetch_assoc(); ?>
    <div class="stats-mini"><div class="stat-mini-card"><div class="stat-mini-number"><?= $user_stats['total'] ?></div><div class="stat-mini-label">Всего заявок</div></div><div class="stat-mini-card"><div class="stat-mini-number"><?= $user_stats['new_count'] ?></div><div class="stat-mini-label">🆕 В обработке</div></div><div class="stat-mini-card"><div class="stat-mini-number"><?= $user_stats['assigned_count'] ?></div><div class="stat-mini-label">🍽️ Подтверждены</div></div><div class="stat-mini-card"><div class="stat-mini-number"><?= $user_stats['completed_count'] ?></div><div class="stat-mini-label">✅ Завершены</div></div></div>
    <div class="request-list">
        <?php if ($query->num_rows == 0): ?>
            <div class="empty-state"><h3>У вас пока нет заявок</h3><p>Создайте первую заявку на банкет прямо сейчас!</p><a href="create.php" class="btn-create">🎉 Создать заявку</a></div>
        <?php else: while ($request = $query->fetch_assoc()):
            $status_class = '';
            if ($request['status'] == 'Новая') $status_class = 'status-new';
            elseif ($request['status'] == 'Банкет назначен') $status_class = 'status-assigned';
            elseif ($request['status'] == 'Банкет завершен') $status_class = 'status-completed';
            else $status_class = 'status-new';
        ?>
        <div class="request-card">
            <div class="request-header"><div class="request-number">🎯 Заявка №<?= $request['id'] ?></div><span class="status-badge <?= $status_class ?>"><?= htmlspecialchars($request['status']) ?></span></div>
            <div class="request-body">
                <div class="info-grid">
                    <div class="info-item"><div class="info-icon">📅</div><div class="info-text"><div class="info-label">Дата и время</div><div class="info-value"><?= htmlspecialchars($request['date']) ?></div></div></div>
                    <div class="info-item"><div class="info-icon">🍽️</div><div class="info-text"><div class="info-label">Тип площадки</div><div class="info-value"><?= htmlspecialchars($request['curses']) ?></div></div></div>
                    <div class="info-item"><div class="info-icon">💳</div><div class="info-text"><div class="info-label">Способ оплаты</div><div class="info-value"><?= htmlspecialchars($request['payment']) ?></div></div></div>
                </div>
                <?php if (!empty($request['comment'])): ?>
                <div class="review-section"><div class="review-text">📝 <strong>Ваши пожелания:</strong> <?= htmlspecialchars($request['comment']) ?></div></div>
                <?php endif; ?>
                <?php if (!empty($request['review'])): ?>
                <div class="review-section"><div class="review-text">⭐ <strong>Ваш отзыв:</strong> <?= htmlspecialchars($request['review']) ?></div></div>
                <?php endif; ?>
                <?php if ($request['status'] === 'Банкет завершен' && empty($request['review'])): ?>
                <div class="review-section"><form method="POST" class="review-form"><input type="hidden" name="request_id" value="<?= $request['id'] ?>"><input type="text" name="review_text" placeholder="✍️ Оставьте отзыв о проведённом банкете..." required><button type="submit">⭐ Оставить отзыв</button></form></div>
                <?php endif; ?>
            </div>
        </div>
        <?php endwhile; endif; ?>
    </div>
</div>
<script>
    let slideIndex = 1; showSlides(slideIndex);
    function plusSlides(n) { showSlides(slideIndex += n); }
    function showSlides(n) {
        let slides = document.getElementsByClassName("mySlides");
        if (n > slides.length) slideIndex = 1;
        if (n < 1) slideIndex = slides.length;
        for (let i = 0; i < slides.length; i++) slides[i].style.display = "none";
        if (slides[slideIndex-1]) slides[slideIndex-1].style.display = "block";
    }
</script>
</body>
</html>