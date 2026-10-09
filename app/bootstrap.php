<?php
declare(strict_types=1);

$config = require __DIR__ . '/config.php';
date_default_timezone_set($config['timezone'] ?? 'Asia/Tehran');
$db = $config['db'];
$dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $db['host'], $db['port'], $db['database'], $db['charset']);
try {
    $pdo = new PDO($dsn, $db['username'], $db['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $exception) {
    http_response_code(500);
    exit('<!doctype html><html lang="fa" dir="rtl"><meta charset="utf-8"><title>خطای اتصال</title><body style="font-family:sans-serif;padding:3rem;line-height:2"><h1>اتصال به پایگاه داده برقرار نشد</h1><p>بررسی کن MySQL روشن باشد، پایگاه داده creator_studio ساخته شده باشد و تنظیمات app/config.php درست باشد.</p><p>جزئیات فنی برای امنیت نمایش داده نمی‌شود.</p></body></html>');
}
