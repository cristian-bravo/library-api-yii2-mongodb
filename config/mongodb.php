<?php

declare(strict_types=1);

$apiParams = require __DIR__ . '/api.php';
$dsn = (string) ($apiParams['mongoUri'] ?? 'mongodb://localhost:27017/library_db');

return [
    'class' => yii\mongodb\Connection::class,
    'dsn' => $dsn,
];
