<?php
declare(strict_types=1);


session_start();


require __DIR__ . '/config.php';

$errors = [];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $pass  = (string)($_POST['password'] ?? '');

    if ($login === '' || $pass === '') {
        $errors[] = 'Введите логин и пароль.';
    } else {
        try {
            
            $stmt = $pdo->prepare('SELECT id, login, full_name, role, password_hash FROM users WHERE login = ?');
            $stmt->execute([$login]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            
            if (!$user || !password_verify($pass, $user['password_hash'])) {
                $errors[] = 'Неверный логин или пароль.';
            } else {
                
                session_regenerate_id(true);
                $_SESSION['user'] = [
                    'id'        => (int)$user['id'],
                    'login'     => $user['login'],
                    'full_name' => $user['full_name'],
                    'role'      => $user['role'],
                ];

                
                header('Location: dashboard.php');
                exit;
            }
        } catch (PDOException $e) {
            $errors[] = 'Ошибка подключения к базе данных: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8" />
  <title>Вход</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <div class="auth-center">
    <form class="login-card" action="" method="post">
      <h1>Вход</h1>

      <?php if ($errors): ?>
        <div class="alert err">
          <?php foreach ($errors as $e): ?>
            <div><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <label for="login">Логин:</label>
      <input type="text" id="login" name="login" required autofocus />

      <label for="password">Пароль:</label>
      <div class="password-wrap">
        <input type="password" id="password" name="password" required />
        <button type="button" class="toggle-pass" onclick="togglePass()">👁</button>
      </div>

      <button type="submit">Войти</button>

      <div class="divider"></div>

      
      <a href="register.php" class="btn secondary">Зарегистрироваться</a>
    </form>
  </div>

<script>
function togglePass(){
  const i=document.getElementById('password');
  const b=document.querySelector('.toggle-pass');
  if(i.type==='password'){
    i.type='text'; b.textContent='🙈';
  } else {
    i.type='password'; b.textContent='👁';
  }
}
</script>
</body>
</html>
