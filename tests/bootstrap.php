<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

if (class_exists(\Dotenv\Dotenv::class) && file_exists(dirname(__DIR__) . '/.env')) {
    \Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
}

defined('YII_DEBUG') or define('YII_DEBUG', filter_var($_ENV['APP_DEBUG'] ?? 'true', FILTER_VALIDATE_BOOL));
defined('YII_ENV') or define('YII_ENV', $_ENV['APP_ENV'] ?? 'test');

require dirname(__DIR__) . '/vendor/yiisoft/yii2/Yii.php';
