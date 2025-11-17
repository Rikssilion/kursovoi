<?php
require __DIR__ . '/config.php';

$stmt = $pdo->query("SELECT COUNT(*) AS total_users FROM users");
$row = $stmt->fetch();

echo "Подключение к БД успешно! 👌<br>";
echo "Пользователей в таблице: " . $row['total_users'];
