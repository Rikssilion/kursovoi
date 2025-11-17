<?php
header('Content-Type: text/plain; charset=UTF-8');
echo "Loaded ini: ".(php_ini_loaded_file() ?: '—').PHP_EOL;
echo "Extension dir: ".ini_get('extension_dir').PHP_EOL;
echo "PDO drivers: ".implode(', ', class_exists('PDO') ? PDO::getAvailableDrivers() : []);
?>