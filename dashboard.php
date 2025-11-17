<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

if (empty($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$userId   = (int)$_SESSION['user']['id'];
$userRole = $_SESSION['user']['role'] ?? 'user';
$fullName = $_SESSION['user']['full_name'] ?? $_SESSION['user']['login'];
$errors = [];
$ok = '';

// создать заявку
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $problem = trim($_POST['problem'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $phone   = trim($_POST['phone']   ?? '');

    if ($problem === '' || $address === '' || $phone === '') {
        $errors[] = 'Заполните все поля заявки.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO requests (user_id, phone, address, problem) VALUES (?, ?, ?, ?)');
        $stmt->execute([$userId, $phone, $address, $problem]);
        $ok = 'Заявка отправлена администратору.';
        $_POST = [];
    }
}

// удалить заявку (только админ)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete' && $userRole === 'admin') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = $pdo->prepare('DELETE FROM requests WHERE id = ?');
        $stmt->execute([$id]);
        $ok = 'Заявка удалена.';
    }
}

// список заявок
if ($userRole === 'admin') {
    $stmt = $pdo->query(
        'SELECT r.id, u.login, u.full_name, r.phone, r.address, r.problem, r.created_at
         FROM requests r JOIN users u ON u.id = r.user_id
         ORDER BY r.id DESC'
    );
} else {
    $stmt = $pdo->prepare(
        'SELECT r.id, u.login, u.full_name, r.phone, r.address, r.problem, r.created_at
         FROM requests r JOIN users u ON u.id = r.user_id
         WHERE r.user_id = ?
         ORDER BY r.id DESC'
    );
    $stmt->execute([$userId]);
}
$rows = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8" />
  <title>Заявки — Электрика</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="style.css" />
</head>
<body>
<header class="topbar">
  <div class="brand">Электрика</div>
  <div class="user">
    <span class="hello">Здравствуйте, <?= htmlspecialchars($fullName) ?></span>
    <a class="btn-out" href="logout.php">Выйти</a>
  </div>
</header>

<main class="wrap">
  <section class="card">
    <h1>Создать заявку</h1>
    <p class="sub">Опишите проблему и укажите контактные данные</p>

    <?php if ($ok): ?><div class="alert ok"><?= htmlspecialchars($ok) ?></div><?php endif; ?>
    <?php if ($errors): ?><div class="alert err"><?php foreach ($errors as $e): ?><div><?= htmlspecialchars($e) ?></div><?php endforeach; ?></div><?php endif; ?>

    <form method="post" action="" class="form">
      <input type="hidden" name="action" value="create">
      <div class="grid">
        <div class="field span-2">
          <label for="problem">Что случилось</label>
          <textarea id="problem" name="problem" rows="3" placeholder="Например: выбивает автомат, не работает розетка…" required><?= htmlspecialchars($_POST['problem'] ?? '') ?></textarea>
        </div>
        <div class="field">
          <label for="address">Адрес</label>
          <input type="text" id="address" name="address" placeholder="ул. Лесная, 15, кв. 24" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>" required>
        </div>
        <div class="field">
          <label for="phone">Телефон</label>
          <input type="text" id="phone" name="phone" placeholder="+7 900 000-00-00" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
        </div>
      </div>
      <div class="actions"><button class="btn" type="submit">Отправить заявку</button></div>
    </form>
  </section>

  <section class="card">
    <h2>Заявки <?= $userRole === 'admin' ? '(все)' : '(мои)' ?></h2>

    <div class="table">
      <div class="tr th">
        <div>ID</div><div>Логин</div><div>ФИО</div><div>Телефон</div><div>Адрес</div><div>Заявка</div><div>Дата/время</div>
        <?php if ($userRole === 'admin'): ?><div>Действия</div><?php endif; ?>
      </div>

      <?php if ($rows): foreach ($rows as $r): ?>
        <div class="tr">
          <div><?= (int)$r['id'] ?></div>
          <div><?= htmlspecialchars($r['login']) ?></div>
          <div><?= htmlspecialchars($r['full_name']) ?></div>
          <div><?= htmlspecialchars($r['phone']) ?></div>
          <div><?= htmlspecialchars($r['address']) ?></div>
          <div><?= htmlspecialchars($r['problem']) ?></div>
          <div><?= htmlspecialchars($r['created_at']) ?></div>

          <?php if ($userRole === 'admin'): ?>
            <div>
              <form method="post" action="" onsubmit="return confirm('Удалить заявку #<?= (int)$r['id'] ?>?');">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                <button class="btn danger" type="submit">Удалить</button>
              </form>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; else: ?>
        <div class="empty">Заявок пока нет</div>
      <?php endif; ?>
    </div>
  </section>
</main>
</body>
</html>
