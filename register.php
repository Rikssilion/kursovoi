<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

$errors = [];
$ok = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name']  ?? '');
    $login = trim($_POST['login'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $p1    = (string)($_POST['password']  ?? '');
    $p2    = (string)($_POST['password2'] ?? '');

    if ($name === '' || $login === '' || $p1 === '' || $p2 === '') {
        $errors[] = 'Заполните все обязательные поля.';
    }
    if ($p1 !== $p2) {
        $errors[] = 'Пароли не совпадают.';
    }
    if (!preg_match('/^[a-z0-9_]{3,32}$/iu', $login)) {
        $errors[] = 'Логин должен быть 3–32 символа (латиница/цифры/подчёркивание).';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE login = ?');
        $stmt->execute([$login]);
        if ($stmt->fetch()) {
            $errors[] = 'Такой логин уже зарегистрирован.';
        }
    }

    if (!$errors) {
        $hash = password_hash($p1, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare(
            'INSERT INTO users (login, password_hash, full_name, phone, role)
             VALUES (:login, :hash, :name, :phone, :role)'
        );
        $stmt->execute([
            ':login' => $login,
            ':hash'  => $hash,
            ':name'  => $name,
            ':phone' => ($phone !== '' ? $phone : null),
            ':role'  => 'user',
        ]);
        $ok = 'Регистрация успешна! Теперь вы можете войти.';
        $_POST = [];
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8" />
  <title>Регистрация</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <div class="auth-center">
    <form class="login-card" action="" method="post">
      <h1>Регистрация</h1>

      <?php if ($ok): ?><div class="alert ok"><?= htmlspecialchars($ok) ?></div><?php endif; ?>
      <?php if ($errors): ?>
        <div class="alert err"><?php foreach ($errors as $e): ?><div><?= htmlspecialchars($e) ?></div><?php endforeach; ?></div>
      <?php endif; ?>

      <label for="name">ФИО:</label>
      <input type="text" id="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>

      <label for="login">Логин:</label>
      <input type="text" id="login" name="login" value="<?= htmlspecialchars($_POST['login'] ?? '') ?>" required>

      <label for="phone">Телефон (необязательно):</label>
      <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">

      <label for="password">Пароль:</label>
      <div class="password-wrap">
        <input type="password" id="password" name="password" required>
        <button type="button" class="toggle-pass" onclick="toggle('password', this)">👁</button>
      </div>

      <label for="password2">Повторите пароль:</label>
      <div class="password-wrap">
        <input type="password" id="password2" name="password2" required>
        <button type="button" class="toggle-pass" onclick="toggle('password2', this)">👁</button>
      </div>

      <button type="submit">Зарегистрироваться</button>

      <p class="bottom-link">Уже есть аккаунт? <a href="index.php">Войти</a></p>
    </form>
  </div>

<script>
function toggle(id, btn){
  const i=document.getElementById(id);
  if(i.type==='password'){i.type='text'; btn.textContent='🙈';}
  else{ i.type='password'; btn.textContent='👁';}
}
</script>
</body>
</html>
